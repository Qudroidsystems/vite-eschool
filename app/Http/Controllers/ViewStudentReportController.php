<?php

namespace App\Http\Controllers;

use App\Models\Broadsheets;
use App\Models\Classcategory;
use App\Models\CompulsorySubjectClass;
use App\Models\PromotionStatus;
use App\Models\Schoolarm;
use App\Models\Schoolclass;
use App\Models\SchoolInformation;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Student;
use App\Models\Studentclass;
use App\Models\Studentpersonalityprofile;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ViewStudentReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View student-report', ['only' => ['index', 'show', 'registeredClasses', 'classBroadsheet', 'studentresult', 'studentmockresult', 'exportStudentResultPdf']]);
        $this->middleware('permission:Create student-report', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update student-report', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete student-report', ['only' => ['destroy']]);
    }

    /**
     * Format a number as an ordinal string (e.g., 1st, 2nd, 3rd, 4th).
     *
     * @param int $number
     * @return string
     */
    protected function formatOrdinal($number)
    {
        if (!is_numeric($number) || $number <= 0) {
            return '-';
        }

        $lastDigit = $number % 10;
        $lastTwoDigits = $number % 100;

        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 13) {
            return $number . 'th';
        }

        return $number . match ($lastDigit) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };
    }

    /**
     * Calculate subject positions and class averages for the entire class (all arms) for each subject.
     *
     * @param int $schoolclassid
     * @param int $sessionid
     * @param int $termid
     * @return void
     */
    protected function calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid)
    {
        // Fetch the schoolclass name for the given schoolclassid
        $schoolclass = Schoolclass::where('id', $schoolclassid)->first(['schoolclass']);
        if (!$schoolclass) {
            Log::warning('Schoolclass not found', [
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
            ]);
            return;
        }
        $className = $schoolclass->schoolclass;

        // Fetch all schoolclassids with the same schoolclass name
        $classIds = Schoolclass::where('schoolclass', $className)
            ->pluck('id')
            ->toArray();

        if (empty($classIds)) {
            Log::warning('No schoolclass IDs found for class name', [
                'class_name' => $className,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
            ]);
            return;
        }

        // Fetch all students across all schoolclassids for the given session
        $students = Studentclass::whereIn('schoolclassid', $classIds)
            ->where('sessionid', $sessionid)
            ->pluck('studentId')
            ->toArray();

        if (empty($students)) {
            Log::warning('No students found for class', [
                'class_name' => $className,
                'schoolclassids' => $classIds,
                'sessionid' => $sessionid,
                'termid' => $termid,
            ]);
            return;
        }

        // Fetch all broadsheet records for the students, term, and session
        $broadsheets = Broadsheets::whereIn('broadsheet_records.student_id', $students)
            ->where('broadsheets.term_id', $termid)
            ->where('broadsheet_records.session_id', $sessionid)
            ->whereIn('broadsheet_records.schoolclass_id', $classIds)
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->join('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
            ->select([
                'broadsheets.id',
                'broadsheet_records.student_id',
                'broadsheet_records.subject_id',
                'subject.subject as subject_name',
                'studentRegistration.admissionNo as admission_no',
                'broadsheets.total',
                'broadsheets.cum',
                'broadsheets.subject_position_class',
                'broadsheets.avg',
            ])
            ->get();

        if ($broadsheets->isEmpty()) {
            Log::warning('No broadsheet records found for class', [
                'class_name' => $className,
                'schoolclassids' => $classIds,
                'sessionid' => $sessionid,
                'termid' => $termid,
            ]);
            return;
        }

        // Group broadsheets by subject to calculate averages and positions
        $subjectGroups = $broadsheets->groupBy('subject_id');

        foreach ($subjectGroups as $subjectId => $subjectRecords) {
            $subjectName = $subjectRecords->first()->subject_name;

            // Calculate class average (sum of total scores / number of students with non-zero cum)
            $validRecords = $subjectRecords->filter(function ($record) {
                return $record->cum != 0;
            });
            $totalScores = $validRecords->sum('total');
            $studentCount = $validRecords->count();
            $classAvg = $studentCount > 0 ? round($totalScores / $studentCount, 1) : 0;

            // Sort valid records by total score (descending) for position calculation
            $sortedRecords = $validRecords->sortByDesc('total')->values();

            // Calculate subject positions, handling ties
            $rank = 0;
            $lastTotal = null;
            $lastPosition = 0;
            $positionMap = [];

            foreach ($sortedRecords as $record) {
                $rank++;
                if ($lastTotal !== null && $record->total == $lastTotal) {
                    // Tied score, use the same position
                    $positionMap[$record->id] = $lastPosition;
                } else {
                    // New position
                    $lastPosition = $rank;
                    $lastTotal = $record->total;
                    $positionMap[$record->id] = $lastPosition;
                }
            }

            // Update broadsheet records with class average and positions
            foreach ($subjectRecords as $record) {
                $newPosition = $record->cum == 0 ? '-' : ($positionMap[$record->id] ?? null);
                if ($newPosition !== '-') {
                    $newPosition = $this->formatOrdinal($newPosition);
                }

                if ($record->avg != $classAvg || $record->subject_position_class != $newPosition) {
                    Broadsheets::where('id', $record->id)->update([
                        'avg' => $classAvg,
                        'subject_position_class' => $newPosition,
                    ]);

                    Log::info('Updated broadsheet metrics', [
                        'broadsheet_id' => $record->id,
                        'student_id' => $record->student_id,
                        'admission_no' => $record->admission_no,
                        'subject_id' => $subjectId,
                        'subject_name' => $subjectName,
                        'class_avg' => $classAvg,
                        'subject_position_class' => $newPosition,
                        'class_name' => $className,
                        'cum' => $record->cum,
                    ]);
                }
            }

            Log::info('Calculated metrics for subject', [
                'subject_id' => $subjectId,
                'subject_name' => $subjectName,
                'class_name' => $className,
                'schoolclassids' => $classIds,
                'sessionid' => $sessionid,
                'termid' => $termid,
                'class_avg' => $classAvg,
                'student_count' => $studentCount,
                'total_scores' => $totalScores,
            ]);
        }

        Log::info('Completed class metrics calculation', [
            'class_name' => $className,
            'schoolclassids' => $classIds,
            'sessionid' => $sessionid,
            'termid' => $termid,
            'total_subjects' => $subjectGroups->count(),
            'total_students' => count($students),
        ]);
    }

    /**
     * Get student result data for both view and PDF export
     *
     * @param int $id
     * @param int $schoolclassid
     * @param int $sessionid
     * @param int $termid
     * @return array
     */
    private function getStudentResultData($id, $schoolclassid, $sessionid, $termid)
    {
        // Validate input parameters
        if (!is_numeric($id) || !is_numeric($schoolclassid) || !is_numeric($sessionid) || !is_numeric($termid)) {
            abort(404, 'Invalid parameters');
        }

        // Fetch student details
        $students = Student::where('studentRegistration.id', $id)
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->get([
                'studentRegistration.id as id',
                'studentRegistration.admissionNo as admissionNo',
                'studentRegistration.firstname as fname',
                'studentRegistration.home_address as homeaddress',
                'studentRegistration.lastname as lastname',
                'studentRegistration.othername as othername',
                'studentRegistration.dateofbirth as dateofbirth',
                'studentRegistration.gender as gender',
                'studentRegistration.updated_at as updated_at',
                'studentpicture.picture as picture'
            ]);

        // Calculate subject positions and class averages for the entire class
        $this->calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid);

        // Fetch personality profile
        $studentpp = Studentpersonalityprofile::where('studentid', $id)
            ->where('schoolclassid', $schoolclassid)
            ->where('sessionid', $sessionid)
            ->where('termid', $termid)
            ->get();

        // Fetch terminal report scores for the specific student and schoolclassid
        $scores = Broadsheets::where('broadsheet_records.student_id', $id)
            ->where('broadsheets.term_id', $termid)
            ->where('broadsheet_records.session_id', $sessionid)
            ->where('broadsheet_records.schoolclass_id', $schoolclassid)
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->orderBy('subject.subject')
            ->get([
                'subject.id as subject_id',
                'subject.subject as subject_name',
                'subject.subject_code',
                'broadsheets.ca1',
                'broadsheets.ca2',
                'broadsheets.ca3',
                'broadsheets.exam',
                'broadsheets.total',
                'broadsheets.bf',
                'broadsheets.cum',
                'broadsheets.grade',
                'broadsheets.subject_position_class as position',
                'broadsheets.avg as class_average',
            ]);

        // Fetch class, term, session, and number of students
        $schoolclass = Schoolclass::where('id', $schoolclassid)->with('armRelation')->first(['schoolclass', 'arm', 'classcategoryid']);
        $schoolterm = Schoolterm::where('id', $termid)->value('term') ?? 'N/A';
        $schoolsession = Schoolsession::where('id', $sessionid)->value('session') ?? 'N/A';
        $numberOfStudents = Studentclass::whereIn('schoolclassid', 
            Schoolclass::where('schoolclass', $schoolclass->schoolclass)->pluck('id'))
            ->where('sessionid', $sessionid)
            ->count();
        $schoolInfo = SchoolInformation::getActiveSchool();

        // Automate principal's comment and promotion status for third term
        if ($termid == 3) {
            // Fetch class category
            $classCategory = Classcategory::where('id', $schoolclass->classcategoryid)->first(['is_senior']);
            $isSenior = $classCategory ? $classCategory->is_senior : false;

            // Fetch compulsory subjects for the specific schoolclassid
            $compulsorySubjects = CompulsorySubjectClass::where('schoolclassid', $schoolclassid)
                ->join('subject', 'compulsory_subject_classes.subjectId', '=', 'subject.id')
                ->get(['compulsory_subject_classes.subjectId', 'subject.subject as subject_name']);

            // Log compulsory subjects and their grades
            $compulsorySubjectLog = [];
            $compulsoryCreditCount = 0;
            $creditCount = 0;
            $failCount = 0;
            $hasNonCompulsoryDOrF = false;
            $nonCompulsorySubjectLog = [];
            $missingCompulsorySubjects = [];

            // Define credit and fail grades based on category
            $creditGrades = $isSenior ? ['A1', 'B2', 'B3', 'C4', 'C5', 'C6'] : ['A', 'B', 'C'];
            $failGrades = $isSenior ? ['F9', 'E8'] : ['F'];

            // Analyze compulsory subjects
            $compulsorySubjectIds = $compulsorySubjects->pluck('subjectId')->toArray();
            foreach ($compulsorySubjects as $compulsorySubject) {
                $subjectId = $compulsorySubject->subjectId;
                $subjectName = $compulsorySubject->subject_name;
                $score = $scores->firstWhere('subject_id', $subjectId);
                $grade = $score ? $score->grade : 'N/A';
                $compulsorySubjectLog[] = [
                    'subject_id' => $subjectId,
                    'subject_name' => $subjectName,
                    'grade' => $grade,
                ];
                if ($score && in_array($grade, $creditGrades)) {
                    $compulsoryCreditCount++;
                } elseif (!$score) {
                    $missingCompulsorySubjects[] = $subjectName;
                }
            }

            // Analyze all scores for total credits and non-compulsory subjects
            foreach ($scores as $score) {
                $isCompulsory = in_array($score->subject_id, $compulsorySubjectIds);
                $grade = $score->grade;

                if (in_array($grade, $creditGrades)) {
                    $creditCount++;
                } elseif (in_array($grade, $failGrades)) {
                    $failCount++;
                    if (!$isCompulsory) {
                        $hasNonCompulsoryDOrF = true;
                    }
                } elseif (!$isSenior && $grade === 'D' && !$isCompulsory) {
                    $hasNonCompulsoryDOrF = true;
                }

                if (!$isCompulsory) {
                    $nonCompulsorySubjectLog[] = [
                        'subject_id' => $score->subject_id,
                        'subject_name' => $score->subject_name,
                        'grade' => $grade,
                    ];
                }
            }

            // Log detailed information
            Log::info("Student Result Analysis for Student ID: {$id}, Class ID: {$schoolclassid}, Session ID: {$sessionid}, Term ID: {$termid}", [
                'student_name' => $students->isNotEmpty() ? $students->first()->fname . ' ' . $students->first()->lastname : 'N/A',
                'class_name' => $schoolclass->schoolclass,
                'class_category' => $isSenior ? 'Senior' : 'Junior',
                'total_subjects' => count($scores),
                'total_compulsory_subjects' => count($compulsorySubjects),
                'compulsory_subjects' => $compulsorySubjectLog,
                'compulsory_credits' => $compulsoryCreditCount,
                'total_credits' => $creditCount,
                'total_failing_grades' => $failCount,
                'non_compulsory_subjects' => $nonCompulsorySubjectLog,
                'has_non_compulsory_d_or_f' => $hasNonCompulsoryDOrF,
                'missing_compulsory_subjects' => $missingCompulsorySubjects,
            ]);

            // Determine principal's comment and promotion status
            $principalComment = '';
            $promotionStatus = '';

            $totalCompulsorySubjects = count($compulsorySubjects);
            if ($totalCompulsorySubjects > 0 && $compulsoryCreditCount === $totalCompulsorySubjects && $creditCount >= 5) {
                // Credits in all compulsory subjects and at least 5 total credits
                $principalComment = 'Excellent performance in all compulsory subjects. Promoted to the next class.';
                $promotionStatus = 'PROMOTED';
            } elseif ($creditCount >= 5 && $compulsoryCreditCount > 0) {
                // At least 5 credits with at least one in compulsory subjects
                if ($isSenior || !$hasNonCompulsoryDOrF) {
                    $principalComment = 'Good performance but needs improvement in some compulsory subjects. Promoted on trial.';
                    $promotionStatus = 'PROMOTED ON TRIAL';
                } else {
                    // Junior category with D/F in non-compulsory subjects
                    $principalComment = 'Credits in compulsory subjects but poor performance in other subjects. Parents to see the Principal.';
                    $promotionStatus = 'PARENTS TO SEE PRINCIPAL';
                }
            } elseif ($creditCount >= 5) {
                // At least 5 credits but none in compulsory subjects
                $principalComment = 'Achieved credits but none in compulsory subjects. Parents to see the Principal.';
                $promotionStatus = 'PARENTS TO SEE PRINCIPAL';
            } elseif ($failCount === count($scores) && count($scores) > 0) {
                // All subjects are failing grades
                $principalComment = 'Poor performance across all subjects. Advice to repeat the class. Parents to see the Principal.';
                $promotionStatus = 'ADVICE TO REPEAT/PARENTS TO SEE THE PRINCIPAL';
            } else {
                // Default case for other scenarios or missing grades
                $principalComment = 'Inconsistent performance or incomplete grades. Parents to see the Principal for further discussion.';
                $promotionStatus = 'PARENTS TO SEE PRINCIPAL';
            }

            // Log the final principal comment and promotion status
            Log::info("Promotion Decision for Student ID: {$id}", [
                'principal_comment' => $principalComment,
                'promotion_status' => $promotionStatus,
            ]);

            // Update or create Studentpersonalityprofile
            Studentpersonalityprofile::updateOrCreate(
                [
                    'studentid' => $id,
                    'schoolclassid' => $schoolclassid,
                    'sessionid' => $sessionid,
                    'termid' => $termid,
                ],
                [
                    'principalscomment' => $principalComment,
                ]
            );

            // Update or create PromotionStatus
            PromotionStatus::updateOrCreate(
                [
                    'studentId' => $id,
                    'schoolclassid' => $schoolclassid,
                    'sessionid' => $sessionid,
                    'termid' => $termid,
                ],
                [
                    'promotionStatus' => $promotionStatus,
                    'position' => null, // Update if position is available
                    'classstatus' => 'CURRENT',
                ]
            );
        }

        return [
            'students' => $students,
            'studentpp' => $studentpp,
            'scores' => $scores,
            'studentid' => $id,
            'schoolclassid' => $schoolclassid,
            'sessionid' => $sessionid,
            'termid' => $termid,
            'schoolclass' => $schoolclass,
            'schoolterm' => $schoolterm,
            'schoolsession' => $schoolsession,
            'numberOfStudents' => $numberOfStudents,
            'schoolInfo' => $schoolInfo
        ];
    }

    /**
     * Display the student's result for a specific class, session, and term.
     *
     * @param int $id
     * @param int $schoolclassid
     * @param int $sessionid
     * @param int $termid
     * @return View
     */
    public function studentresult($id, $schoolclassid, $sessionid, $termid)
    {
        $pagetitle = "Student Personality Profile";
        $data = $this->getStudentResultData($id, $schoolclassid, $sessionid, $termid);
        
        return view('studentreports.studentresult')
            ->with($data)
            ->with('pagetitle', $pagetitle);
    }

    /**
     * Export student result as PDF
     *
     * @param int $id
     * @param int $schoolclassid
     * @param int $sessionid
     * @param int $termid
     * @return Response
     */
    public function exportStudentResultPdf($id, $schoolclassid, $sessionid, $termid)
    {
        try {
            $data = $this->getStudentResultData($id, $schoolclassid, $sessionid, $termid);
            
            // Generate filename
            $student = $data['students']->first();
            $studentName = $student ? $student->fname . '_' . $student->lastname : 'Student';
            $filename = 'Terminal_Report_' . $studentName . '_' . $data['schoolsession'] . '_Term_' . $data['termid'] . '.pdf';
            
            // Configure PDF options for A4 size
            $pdf = Pdf::loadView('studentreports.studentresult_pdf', $data)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'dpi' => 96,
                    'defaultFont' => 'DejaVu Sans',
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'isFontSubsettingEnabled' => true,
                    'debugKeepTemp' => false,
                    'debugCss' => false,
                    'debugLayout' => false,
                    'debugLayoutLines' => false,
                    'debugLayoutBlocks' => false,
                    'debugLayoutInline' => false,
                    'debugLayoutPaddingBox' => false,
                ]);

            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            Log::error('PDF Export Error', [
                'student_id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return back()->with('error', 'Failed to generate PDF. Please try again.');
        }
    }

    public function index(Request $request): View|JsonResponse 
    {
        $pagetitle = "Student Terminal Report Management";
        $current = "Current";

        // Initialize empty collection for students
        $allstudents = new LengthAwarePaginator([], 0, 10);

        // Fetch students only if class and session are selected
        if ($request->filled('schoolclassid') && $request->filled('sessionid') && $request->input('schoolclassid') !== 'ALL' && $request->input('sessionid') !== 'ALL') {
            $query = Studentclass::query()
                ->where('schoolclassid', $request->input('schoolclassid'))
                ->where('sessionid', $request->input('sessionid'))
                ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'studentclass.studentId')
                ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
                ->where('schoolsession.status', '=', $current);

            // Apply search filter if provided
            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('studentRegistration.admissionNo', 'like', "%{$search}%")
                      ->orWhere('studentRegistration.firstname', 'like', "%{$search}%")
                      ->orWhere('studentRegistration.lastname', 'like', "%{$search}%")
                      ->orWhere('studentRegistration.othername', 'like', "%{$search}%");
                });
            }

            // Fetch students with pagination
            $allstudents = $query->select([
                'studentRegistration.admissionNo as admissionno',
                'studentRegistration.firstname as firstname',
                'studentRegistration.lastname as lastname',
                'studentRegistration.othername as othername',
                'studentRegistration.gender as gender',
                'studentRegistration.id as stid',
                'studentpicture.picture as picture',
                'studentclass.schoolclassid as schoolclassID',
                'studentclass.sessionid as sessionid',
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.arm as schoolarm',
                'schoolsession.session as session',
            ])->latest('studentclass.created_at')->paginate(10);
        }

        // Fetch data for filters
        $schoolsessions = Schoolsession::where('status', 'Current')->get();
        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm']);

        if (config('app.debug')) {
            Log::info('Sessions for select:', $schoolsessions->toArray());
            Log::info('Students fetched:', $allstudents->toArray());
        }

        // Check if the request is AJAX
        if ($request->ajax()) {
            return response()->json([
                'tableBody' => view('studentreports.partials.student_rows', compact('allstudents'))->render(),
                'pagination' => $allstudents->links('pagination::bootstrap-5')->render(),
                'studentCount' => $allstudents->total(),
            ]);
        }

        return view('studentreports.index', compact('allstudents', 'schoolsessions', 'schoolclasses', 'pagetitle'));
    }

    public function registeredClasses(Request $request)
    {
        $classId = $request->query('class_id');
        $sessionId = $request->query('session_id');

        if (!$classId || !$sessionId || $classId === 'ALL' || $sessionId === 'ALL') {
            return response()->json([
                'success' => false,
                'message' => 'Please select a valid class and session.'
            ], 400);
        }

        $classes = Studentclass::query()
            ->join('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->join('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
            ->where('schoolclass.id', $classId)
            ->where('schoolsession.id', $sessionId)
            ->where('schoolsession.status', 'Current')
            ->groupBy('schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm', 'schoolsession.session')
            ->selectRaw('
                schoolclass.schoolclass as class_name,
                schoolarm.arm as arm_name,
                schoolsession.session as session_name,
                COUNT(DISTINCT studentclass.studentId) as student_count
            ')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $classes
        ]);
    }

    public function classBroadsheet($schoolclassid, $sessionid, $termid): View
    {
        $class = Schoolclass::findOrFail($schoolclassid);
        $session = Schoolsession::findOrFail($sessionid);
        $term = $termid; // Assuming termid is 1, 2, or 3 for First, Second, Third Term
        $pagetitle = "Broadsheet for {$class->schoolclass} - {$session->session} - Term {$term}";

        $data = [
            'class' => $class,
            'session' => $session,
            'term' => $term,
            'pagetitle' => $pagetitle
        ];

        return view('studentreports.broadsheet', $data);
    }


}