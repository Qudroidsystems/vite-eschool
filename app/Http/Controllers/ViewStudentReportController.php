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
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Exception;

class ViewStudentReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View student-report', ['only' => [
            'index', 'show', 'registeredClasses', 'classBroadsheet',
            'studentresult', 'studentmockresult', 'exportStudentResultPdf', 'exportClassResultsPdf'
        ]]);
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
        $cacheKey = "class_metrics_{$schoolclassid}_{$sessionid}_{$termid}";
        if (Cache::has($cacheKey)) {
            return;
        }

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

        $classIds = Schoolclass::where('schoolclass', $className)->pluck('id')->toArray();
        if (empty($classIds)) {
            Log::warning('No schoolclass IDs found for class name', [
                'class_name' => $className,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
            ]);
            return;
        }

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

        $subjectGroups = $broadsheets->groupBy('subject_id');

        foreach ($subjectGroups as $subjectId => $subjectRecords) {
            $subjectName = $subjectRecords->first()->subject_name;
            $validRecords = $subjectRecords->filter(function ($record) {
                return $record->cum != 0;
            });
            $totalScores = $validRecords->sum('total');
            $studentCount = $validRecords->count();
            $classAvg = $studentCount > 0 ? round($totalScores / $studentCount, 1) : 0;

            $sortedRecords = $validRecords->sortByDesc('total')->values();

            $rank = 0;
            $lastTotal = null;
            $lastPosition = 0;
            $positionMap = [];

            foreach ($sortedRecords as $record) {
                $rank++;
                if ($lastTotal !== null && $record->total == $lastTotal) {
                    $positionMap[$record->id] = $lastPosition;
                } else {
                    $lastPosition = $rank;
                    $lastTotal = $record->total;
                    $positionMap[$record->id] = $lastPosition;
                }
            }

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

        Cache::put($cacheKey, true, now()->addHours(1));

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
        try {
            if (!is_numeric($id) || !is_numeric($schoolclassid) || !is_numeric($sessionid) || !is_numeric($termid)) {
                Log::error('Invalid parameters in getStudentResultData', [
                    'student_id' => $id,
                    'schoolclassid' => $schoolclassid,
                    'sessionid' => $sessionid,
                    'termid' => $termid,
                ]);
                return [];
            }

            $students = Student::where('studentRegistration.id', $id)
                ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
                ->select([
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
                ])->get();

            if ($students->isEmpty()) {
                Log::warning('No active student found for ID', [
                    'student_id' => $id,
                    'schoolclassid' => $schoolclassid,
                    'sessionid' => $sessionid,
                    'termid' => $termid,
                ]);
                $students = collect([]);
            }

            $this->calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid);

            $studentpp = Studentpersonalityprofile::where('studentid', $id)
                ->where('schoolclassid', $schoolclassid)
                ->where('sessionid', $sessionid)
                ->where('termid', $termid)
                ->first();

            $scores = Broadsheets::where('broadsheet_records.student_id', $id)
                ->where('broadsheets.term_id', $termid)
                ->where('broadsheet_records.session_id', $sessionid)
                ->where('broadsheet_records.schoolclass_id', $schoolclassid)
                ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
                ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
                ->orderBy('subject.subject')
                ->select([
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
                ])->get();

            $schoolclass = Schoolclass::with('armRelation')->find($schoolclassid, ['id', 'schoolclass', 'arm', 'classcategoryid']) ?? (object)[
                'schoolclass' => 'N/A',
                'armRelation' => (object)['arm' => 'N/A'],
                'classcategoryid' => null
            ];
            $schoolterm = Schoolterm::where('id', $termid)->value('term') ?? 'N/A';
            $schoolsession = Schoolsession::where('id', $sessionid)->value('session') ?? 'N/A';
            $numberOfStudents = Studentclass::whereIn('schoolclassid', 
                Schoolclass::where('schoolclass', $schoolclass->schoolclass ?? 'N/A')->pluck('id'))
                ->where('sessionid', $sessionid)
                ->count();
            $schoolInfo = SchoolInformation::getActiveSchool() ?? (object)[
                'school_name' => 'QUODOROID CODING ACADEMY',
                'school_motto' => 'N/A',
                'school_address' => 'N/A',
                'school_website' => null,
                'getLogoUrlAttribute' => function () {
                    return public_path('assets/tp.png');
                }
            ];

            // Log image paths for debugging
            if ($students->isNotEmpty() && $students->first()->picture) {
                $imagePath = public_path('storage/' . $students->first()->picture);
                Log::info('Student image path', ['path' => $imagePath, 'exists' => file_exists($imagePath)]);
            }
            $logoPath = public_path($schoolInfo->getLogoUrlAttribute() ?? 'assets/tp.png');
            Log::info('School logo path', ['path' => $logoPath, 'exists' => file_exists($logoPath)]);

            if ($termid == 3) {
                $classCategory = Classcategory::find($schoolclass->classcategoryid, ['is_senior']);
                $isSenior = $classCategory ? $classCategory->is_senior : false;

                $compulsorySubjects = CompulsorySubjectClass::where('schoolclassid', $schoolclassid)
                    ->join('subject', 'compulsory_subject_classes.subjectId', '=', 'subject.id')
                    ->select(['compulsory_subject_classes.subjectId', 'subject.subject as subject_name'])
                    ->get();

                $compulsorySubjectLog = [];
                $compulsoryCreditCount = 0;
                $creditCount = 0;
                $failCount = 0;
                $hasNonCompulsoryDOrF = false;
                $nonCompulsorySubjectLog = [];
                $missingCompulsorySubjects = [];

                $creditGrades = $isSenior ? ['A1', 'B2', 'B3', 'C4', 'C5', 'C6'] : ['A', 'B', 'C'];
                $failGrades = $isSenior ? ['F9', 'E8'] : ['F'];

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

                $principalComment = '';
                $promotionStatus = '';
                $totalCompulsorySubjects = count($compulsorySubjects);
                if ($totalCompulsorySubjects > 0 && $compulsoryCreditCount === $totalCompulsorySubjects && $creditCount >= 5) {
                    $principalComment = 'Excellent performance in all compulsory subjects. Promoted to the next class.';
                    $promotionStatus = 'PROMOTED';
                } elseif ($creditCount >= 5 && $compulsoryCreditCount > 0) {
                    $principalComment = $isSenior || !$hasNonCompulsoryDOrF 
                        ? 'Good performance but needs improvement in some compulsory subjects. Promoted on trial.'
                        : 'Credits in compulsory subjects but poor performance in other subjects. Parents to see the Principal.';
                    $promotionStatus = $isSenior || !$hasNonCompulsoryDOrF ? 'PROMOTED ON TRIAL' : 'PARENTS TO SEE PRINCIPAL';
                } elseif ($creditCount >= 5) {
                    $principalComment = 'Achieved credits but none in compulsory subjects. Parents to see the Principal.';
                    $promotionStatus = 'PARENTS TO SEE PRINCIPAL';
                } elseif ($failCount === count($scores) && count($scores) > 0) {
                    $principalComment = 'Poor performance across all subjects. Advice to repeat the class. Parents to see the Principal.';
                    $promotionStatus = 'ADVICE TO REPEAT/PARENTS TO SEE PRINCIPAL';
                } else {
                    $principalComment = 'Inconsistent performance or incomplete grades. Parents to see the Principal for further discussion.';
                    $promotionStatus = 'PARENTS TO SEE PRINCIPAL';
                }

                Log::info("Promotion Decision for Student ID: {$id}", [
                    'principal_comment' => $principalComment,
                    'promotion_status' => $promotionStatus,
                ]);

                Studentpersonalityprofile::updateOrCreate(
                    [
                        'studentid' => $id,
                        'schoolclassid' => $schoolclassid,
                        'sessionid' => $sessionid,
                        'termid' => $termid,
                    ],
                    ['principalscomment' => $principalComment]
                );

                PromotionStatus::updateOrCreate(
                    [
                        'studentId' => $id,
                        'schoolclassid' => $schoolclassid,
                        'sessionid' => $sessionid,
                        'termid' => $termid,
                    ],
                    [
                        'promotionStatus' => $promotionStatus,
                        'position' => null,
                        'classstatus' => 'CURRENT',
                    ]
                );
            }

            return [
                'students' => $students,
                'studentpp' => collect([$studentpp]),
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
        } catch (Exception $e) {
            Log::error('Error fetching student result data', [
                'student_id' => $id,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return [];
        }
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
        
        return view('studentreports.studentresult')->with($data)->with('pagetitle', $pagetitle);
    }

    /**
     * Export single student result as PDF.
     *
     * @param int $id Student ID
     * @param int $schoolclassid School class ID
     * @param int $sessionid School session ID
     * @param int $termid School term ID
     * @return \Illuminate\Http\Response
     */
    public function exportStudentResultPdf($id, $schoolclassid, $sessionid, $termid)
    {
        try {
            ini_set('max_execution_time', 600); // Increased for complex PDFs
            ini_set('memory_limit', '1024M'); // Increased memory limit

            Log::info('Generating single student PDF', [
                'student_id' => $id,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
            ]);

            $data = $this->getStudentResultData($id, $schoolclassid, $sessionid, $termid);

            if (empty($data) || empty($data['students']) || $data['students']->isEmpty()) {
                Log::error('No valid student data for PDF generation', [
                    'student_id' => $id,
                    'schoolclassid' => $schoolclassid,
                    'sessionid' => $sessionid,
                    'termid' => $termid,
                ]);
                return back()->with('error', 'No student data found for the provided parameters.');
            }

            $student = $data['students']->first();
            $studentName = $student ? $student->fname . '_' . $student->lastname : 'Student';
            $filename = 'Terminal_Report_' . $studentName . '_' . $data['schoolsession'] . '_Term_' . $data['termid'] . '.pdf';

            $pdf = Pdf::loadView('studentreports.studentresult_pdf', ['data' => $data])
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'dpi' => 150,
                    'defaultFont' => 'Times New Roman',
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'isFontSubsettingEnabled' => true,
                    'isPhpEnabled' => false,
                    'chroot' => public_path(),
                    'fontCache' => storage_path('fonts/'),
                    'logOutputFile' => storage_path('logs/dompdf.log'), // Log DomPDF errors
                ]);

            return $pdf->download($filename);
        } catch (Exception $e) {
            Log::error('Single Student PDF Export Error', [
                'student_id' => $id,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

 public function exportClassResultsPdf(Request $request): JsonResponse
{
    try {
        // Set execution limits for large PDF generation
        ini_set('max_execution_time', 1200); // 20 minutes
        ini_set('memory_limit', '2048M'); // 2GB memory limit

        // Validate and sanitize input parameters
        $schoolclassid = $request->query('schoolclassid');
        $sessionid = $request->query('sessionid');
        $termid = $request->query('termid', 3);

        Log::info('Starting class results PDF generation', [
            'schoolclassid' => $schoolclassid,
            'sessionid' => $sessionid,
            'termid' => $termid,
        ]);

        // Validate parameters
        if (!is_numeric($schoolclassid) || !is_numeric($sessionid) || !is_numeric($termid)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid parameters provided. All IDs must be numeric.'
            ], 400);
        }

        // Get all students in the class for the session
        $students = Studentclass::where('schoolclassid', $schoolclassid)
            ->where('sessionid', $sessionid)
            ->join('studentRegistration', 'studentRegistration.id', '=', 'studentclass.studentId')
            ->join('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
            ->where('schoolsession.status', '=', 'Current')
            ->select('studentRegistration.id', 'studentRegistration.firstname', 'studentRegistration.lastname')
            ->orderBy('studentRegistration.lastname', 'asc')
            ->orderBy('studentRegistration.firstname', 'asc')
            ->get();

        if ($students->isEmpty()) {
            Log::warning('No students found for class', [
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'No students found for the selected class and session.'
            ], 404);
        }

        Log::info('Processing students for PDF', ['student_count' => $students->count()]);

        // Collect all student data
        $allStudentData = [];
        $processedStudents = 0;
        $skippedStudents = 0;

        foreach ($students as $student) {
            try {
                $studentData = $this->getStudentResultData($student->id, $schoolclassid, $sessionid, $termid);
                
                if ($this->validateStudentData($studentData)) {
                    $allStudentData[] = $studentData;
                    $processedStudents++;
                } else {
                    $skippedStudents++;
                    Log::warning('Skipping student due to invalid/missing data', [
                        'student_id' => $student->id,
                        'student_name' => $student->fname . ' ' . $student->lastname,
                        'schoolclassid' => $schoolclassid,
                        'sessionid' => $sessionid,
                        'termid' => $termid,
                    ]);
                }
            } catch (Exception $e) {
                $skippedStudents++;
                Log::error('Error processing student data', [
                    'student_id' => $student->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        if (empty($allStudentData)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid student data found for PDF generation. All students were skipped due to missing data.'
            ], 404);
        }

        Log::info('Student data collection completed', [
            'processed' => $processedStudents,
            'skipped' => $skippedStudents,
            'total' => $students->count()
        ]);

        // Get class and session information for filename
        $schoolclass = Schoolclass::where('id', $schoolclassid)->with('armRelation')->first(['schoolclass', 'arm']);
        $schoolsession = Schoolsession::where('id', $sessionid)->value('session') ?? 'N/A';
        $term = $this->getTermName($termid);
        
        // Generate clean filename
        $className = $schoolclass ? ($schoolclass->schoolclass . ($schoolclass->armRelation ? $schoolclass->armRelation->arm : '')) : 'Class';
        $filename = 'Class_Results_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $className) . '_' . 
                   preg_replace('/[^A-Za-z0-9_-]/', '_', $schoolsession) . '_' . $term . '.pdf';

        // Generate PDF with optimized settings
        $pdf = Pdf::loadView('studentreports.class_results_pdf', [
                'allStudentData' => $allStudentData,
                'metadata' => [
                    'class_name' => $className,
                    'session' => $schoolsession,
                    'term' => $term,
                    'generation_date' => now()->format('Y-m-d H:i:s'),
                    'student_count' => count($allStudentData)
                ]
            ])
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'dpi' => 150,
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'isFontSubsettingEnabled' => true,
                'isPhpEnabled' => false,
                'chroot' => public_path(),
                'fontCache' => storage_path('fonts/'),
                'logOutputFile' => storage_path('logs/dompdf.log'),
                'tempDir' => storage_path('app/temp/'),
                'fontDir' => storage_path('fonts/'),
                'fontCache' => storage_path('fonts/'),
                'isJavascriptEnabled' => false,
                'debugKeepTemp' => false,
                'debugCss' => false,
                'debugLayout' => false,
                'debugLayoutLines' => false,
                'debugLayoutBlocks' => false,
                'debugLayoutInline' => false,
                'debugLayoutPaddingBox' => false,
            ]);

        Log::info('Starting PDF generation');
        
        $pdfContent = $pdf->output();
        
        if (!$pdfContent || strlen($pdfContent) < 1000) {
            throw new Exception('Generated PDF content is empty or too small');
        }

        Log::info('PDF generated successfully', [
            'size_bytes' => strlen($pdfContent),
            'filename' => $filename
        ]);

        $base64Pdf = base64_encode($pdfContent);

        return response()->json([
            'success' => true,
            'pdfBase64' => $base64Pdf,
            'filename' => $filename,
            'metadata' => [
                'studentCount' => count($allStudentData),
                'processedStudents' => $processedStudents,
                'skippedStudents' => $skippedStudents,
                'totalStudents' => $students->count(),
                'className' => $className,
                'session' => $schoolsession,
                'term' => $term,
                'fileSize' => strlen($pdfContent),
                'generationTime' => now()->format('Y-m-d H:i:s')
            ]
        ]);

    } catch (Exception $e) {
        Log::error('Class PDF Export Error', [
            'schoolclassid' => $schoolclassid ?? 'N/A',
            'sessionid' => $sessionid ?? 'N/A',
            'termid' => $termid ?? 'N/A',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to generate PDF: ' . $e->getMessage(),
            'error_code' => 'PDF_GENERATION_FAILED'
        ], 500);
    }
}

/**
 * Validate student data structure
 */
private function validateStudentData($studentData): bool
{
    if (empty($studentData)) {
        return false;
    }

    // Check if required data exists
    if (empty($studentData['students']) || !$studentData['students']->isNotEmpty()) {
        return false;
    }

    // Check if scores exist (optional but recommended)
    if (!isset($studentData['scores'])) {
        return false;
    }

    return true;
}

/**
 * Get term name from term ID
 */
private function getTermName($termid): string
{
    $termNames = [
        1 => 'First_Term',
        2 => 'Second_Term',
        3 => 'Third_Term'
    ];

    return $termNames[$termid] ?? 'Term_' . $termid;
}

// /**
//  * Alternative method to stream large PDFs directly (for very large classes)
//  */
// public function exportClassResultsPdfStream(Request $request)
// {
//     try {
//         // Same validation and data collection as above...
//         // ... (truncated for brevity)

//         // For streaming large PDFs
//         $pdf = Pdf::loadView('studentreports.class_results_pdf', [
//             'allStudentData' => $allStudentData
//         ])
//         ->setPaper('A4', 'portrait');

//         return $pdf->stream($filename, [
//             'Attachment' => false // Set to true to force download
//         ]);

//     } catch (Exception $e) {
//         Log::error('PDF Stream Error: ' . $e->getMessage());
//         return response()->json(['error' => 'Failed to generate PDF'], 500);
//     }
// }
    public function index(Request $request): View|JsonResponse 
    {
        $pagetitle = "Student Terminal Report Management";
        $current = "Current";

        $allstudents = new LengthAwarePaginator([], 0, 10);

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

            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('studentRegistration.admissionNo', 'like', "%{$search}%")
                      ->orWhere('studentRegistration.firstname', 'like', "%{$search}%")
                      ->orWhere('studentRegistration.lastname', 'like', "%{$search}%")
                      ->orWhere('studentRegistration.othername', 'like', "%{$search}%");
                });
            }

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

        $schoolsessions = Schoolsession::where('status', 'Current')->get();
        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm']);

        if (config('app.debug')) {
            Log::info('Sessions for select:', $schoolsessions->toArray());
            Log::info('Students fetched:', $allstudents->toArray());
        }

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
        $term = $termid;
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