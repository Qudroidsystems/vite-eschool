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
                ])
                ->orderBy('studentRegistration.lastname', 'asc')
                ->get();

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
            
            $promotionStatus = PromotionStatus::where('studentId', $id)
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
                    return 'school_logos/LUYWInGbX6ypLQO4fEWue9jHx3VwaKJG5hPLsQmt.jpg';
                }
            ];

            // Log image paths for debugging
            if ($students->isNotEmpty() && $students->first()->picture) {
                $imagePath = public_path('storage/' . $students->first()->picture);
                Log::info('Student image path', ['path' => $imagePath, 'exists' => file_exists($imagePath)]);
            }
            $logoPath = public_path('storage/' . $schoolInfo->getLogoUrlAttribute());
            Log::info('School logo path:', ['path' => $logoPath, 'exists' => file_exists($logoPath)]);

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
                $promotionStatusValue = ''; // Renamed to avoid conflict with $promotionStatus object
                $totalCompulsorySubjects = count($compulsorySubjects);
                if ($totalCompulsorySubjects > 0 && $compulsoryCreditCount === $totalCompulsorySubjects && $creditCount >= 5) {
                    $principalComment = 'Excellent performance. Promoted to the next class.';
                    $promotionStatusValue = 'PROMOTED';
                } elseif ($creditCount >= 5 && $compulsoryCreditCount > 0) {
                    $principalComment = $isSenior || !$hasNonCompulsoryDOrF 
                        ? 'Good performance but needs improvement in some compulsory subjects. Promoted on trial.'
                        : 'Credits in compulsory subjects but poor performance in other subjects. Parents to see the Principal.';
                    $promotionStatusValue = $isSenior || !$hasNonCompulsoryDOrF ? 'PROMOTED' : 'PARENTS TO SEE PRINCIPAL';
                } elseif ($creditCount >= 5) {
                    $principalComment = 'Achieved credits but none in compulsory subjects. Parents to see the Principal.';
                    $promotionStatusValue = 'PARENTS TO SEE PRINCIPAL';
                } elseif ($failCount === count($scores) && count($scores) > 0) {
                    $principalComment = 'Poor performance across all subjects. Advice to repeat the class. Parents to see the Principal.';
                    $promotionStatusValue = 'REPEAT';
                } else {
                    $principalComment = 'Inconsistent performance or incomplete grades. Parents to see the Principal for further discussion.';
                    $promotionStatusValue = 'REPEAT';
                }

                Log::info("Promotion Decision for Student ID: {$id}", [
                    'principal_comment' => $principalComment,
                    'promotion_status' => $promotionStatusValue,
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
                        'promotionStatus' => $promotionStatusValue,
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
                'schoolInfo' => $schoolInfo,
                'promotionStatus' => $promotionStatus
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

    

    public function studentresult($id, $schoolclassid, $sessionid, $termid)
    {
        $pagetitle = "Student Personality Profile";
        $data = $this->getStudentResultData($id, $schoolclassid, $sessionid, $termid);
        
        
        return view('studentreports.studentresult')->with($data)->with('pagetitle', $pagetitle);
    }

    public function exportStudentResultPdf($id, $schoolclassid, $sessionid, $termid)
    {
        try {
            ini_set('max_execution_time', 600);
            ini_set('memory_limit', '1024M');

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
                    'defaultFont' => 'DejaVu Sans',
                    'isRemoteEnabled' => false, // Disabled since using local paths
                    'isHtml5ParserEnabled' => true,
                    'isFontSubsettingEnabled' => true,
                    'isPhpEnabled' => false,
                    'chroot' => [public_path(), storage_path()], // Include storage path
                    'fontCache' => storage_path('fonts/'),
                    'logOutputFile' => storage_path('logs/dompdf.log'),
                    'debugCss' => config('app.debug', false),
                    'debugLayout' => config('app.debug', false),
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


    public function exportClassResultsPdf(Request $request)
    {
        try {
            ini_set('max_execution_time', 1200);
            ini_set('memory_limit', '2048M');

            $schoolclassid = $request->input('schoolclassid');
            $sessionid = $request->input('sessionid');
            $termid = $request->input('termid', 3);
            $studentIds = $request->input('studentIds', []); // Get student IDs from request

            Log::info('Starting class results PDF generation', [
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
                'studentIds' => $studentIds,
            ]);

            if (!is_numeric($schoolclassid) || !is_numeric($sessionid) || !is_numeric($termid)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid parameters provided. All IDs must be numeric.'
                ], 400);
            }

            if (!Schoolclass::find($schoolclassid) || !Schoolsession::find($sessionid) || !Schoolterm::find($termid)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid class, session, or term ID.'
                ], 400);
            }

            // Fetch students based on provided studentIds or all students in the class
            $query = Studentclass::where('schoolclassid', $schoolclassid)
                ->where('sessionid', $sessionid)
                ->join('studentRegistration', 'studentRegistration.id', '=', 'studentclass.studentId')
                ->join('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
                ->where('schoolsession.status', '=', 'Current')
                ->select('studentRegistration.id', 'studentRegistration.firstname', 'studentRegistration.lastname')
                ->orderBy('studentRegistration.lastname', 'asc');
               // ->orderBy('studentRegistration.firstname', 'asc');

            if (!empty($studentIds)) {
                $query->whereIn('studentRegistration.id', $studentIds);
            }

            $students = $query->get();

            if ($students->isEmpty()) {
                Log::warning('No students found for class or selected students', [
                    'schoolclassid' => $schoolclassid,
                    'sessionid' => $sessionid,
                    'studentIds' => $studentIds,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No students found for the selected class, session, or selected students.'
                ], 404);
            }

            Log::info('Processing students for PDF', ['student_count' => $students->count()]);

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
                            'student_name' => $student->firstname . ' ' . $student->lastname,
                            'schoolclassid' => $schoolclassid,
                            'sessionid' => $sessionid,
                            'termid' => $termid,
                        ]);
                    }
                } catch (Exception $e) {
                    $skippedStudents++;
                    Log::error('Error processing student data', [
                        'student_id' => $student->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            if (empty($allStudentData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid student data found for PDF generation.'
                ], 404);
            }

            Log::info('Student data collection completed', [
                'processed' => $processedStudents,
                'skipped' => $skippedStudents,
                'total' => $students->count(),
            ]);

            $this->fixImagePaths($allStudentData);

            $schoolclass = Schoolclass::where('id', $schoolclassid)->with('armRelation')->first(['schoolclass', 'arm']);
            $schoolsession = Schoolsession::where('id', $sessionid)->value('session') ?? 'N/A';
            $term = $this->getTermName($termid);
            $className = $schoolclass ? ($schoolclass->schoolclass . ($schoolclass->armRelation ? $schoolclass->armRelation->arm : '')) : 'Class';
            $filename = 'Class_Results_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $className) . '_' . 
                        preg_replace('/[^A-Za-z0-9_-]/', '_', $schoolsession) . '_' . $term . '.pdf';

            Log::info('Preparing PDF data', [
                'filename' => $filename,
                'class_name' => $className,
                'session' => $schoolsession,
                'term' => $term,
            ]);

            $viewName = 'studentreports.class_results_pdf';
            if (!view()->exists($viewName)) {
                Log::error('PDF view not found', ['view' => $viewName]);
                return response()->json([
                    'success' => false,
                    'message' => 'PDF template view not found: ' . $viewName,
                ], 500);
            }

            $viewData = [
                'allStudentData' => $allStudentData,
                'metadata' => [
                    'class_name' => $className,
                    'session' => $schoolsession,
                    'term' => $term,
                    'generation_date' => now()->format('Y-m-d H:i:s'),
                    'student_count' => count($allStudentData),
                ],
            ];

            try {
                $viewContent = view($viewName, $viewData)->render();
                Log::info('View rendered successfully', ['content_length' => strlen($viewContent)]);
            } catch (Exception $e) {
                Log::error('View rendering failed', [
                    'view' => $viewName,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to render PDF template: ' . $e->getMessage(),
                ], 500);
            }

            $this->ensureDirectoriesExist();

            Log::info('Starting PDF generation with DomPDF');

            $pdf = Pdf::loadView($viewName, $viewData)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'dpi' => 96,
                    'defaultFont' => 'DejaVu Sans',
                    'isRemoteEnabled' => false,
                    'isHtml5ParserEnabled' => true,
                    'isFontSubsettingEnabled' => true,
                    'isPhpEnabled' => false,
                    'chroot' => [public_path(), storage_path()],
                    'tempDir' => storage_path('app/temp/'),
                    'fontCache' => storage_path('fonts/'),
                    'logOutputFile' => storage_path('logs/dompdf.log'),
                    'isJavascriptEnabled' => false,
                    'enable_css_float' => true,
                    'debugLayout' => false,
                    'debugCss' => false,
                    'debugKeepTemp' => false,
                ])
                ->setWarnings(true);

            Log::info('PDF object created successfully');

            $pdfContent = $pdf->output();
            Log::info('PDF content generated', ['size' => strlen($pdfContent)]);

            if (empty($pdfContent)) {
                Log::error('PDF content is empty');
                return response()->json([
                    'success' => false,
                    'message' => 'Generated PDF content is empty',
                    'error_code' => 'EMPTY_PDF_CONTENT',
                ], 500);
            }

            if (!str_starts_with($pdfContent, '%PDF')) {
                Log::error('Invalid PDF content generated', [
                    'content_start' => substr($pdfContent, 0, 100),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid PDF content generated',
                    'error_code' => 'INVALID_PDF_CONTENT',
                ], 500);
            }

            Log::info('PDF validation successful');

            $responseMethod = $request->input('response_method', 'base64');

            switch ($responseMethod) {
                case 'save_and_redirect':
                    return $this->saveAndRedirectResponse($pdfContent, $filename);
                case 'base64':
                    return $this->base64Response($pdfContent, $filename);
                case 'chunked':
                    return $this->chunkedResponse($pdfContent, $filename);
                case 'download':
                    return $this->downloadResponse($pdfContent, $filename);
                case 'inline':
                    return $this->inlineResponse($pdfContent, $filename);
                default:
                    return $this->base64Response($pdfContent, $filename);
            }
        } catch (Exception $e) {
            Log::error('Class PDF Export Error', [
                'schoolclassid' => $schoolclassid ?? 'N/A',
                'sessionid' => $sessionid ?? 'N/A',
                'termid' => $termid ?? 'N/A',
                'studentIds' => $studentIds ?? [],
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: ' . $e->getMessage(),
                'error_code' => 'PDF_EXPORT_FAILED',
            ], 500);
        }
    }

    private function inlineResponse($pdfContent, $filename)
    {
        Log::info('Sending inline PDF response', ['size' => strlen($pdfContent)]);
        
        try {
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            Log::info('Output buffers cleared');
            
            if (headers_sent($headerFile, $headerLine)) {
                Log::error('Headers already sent', [
                    'file' => $headerFile,
                    'line' => $headerLine
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Headers already sent. Cannot deliver PDF directly.',
                    'error_code' => 'HEADERS_ALREADY_SENT'
                ], 500);
            }
            
            Log::info('Headers check passed, sending PDF response');
            
            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->header('Content-Length', strlen($pdfContent))
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        } catch (Exception $e) {
            Log::error('Inline response failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send inline response: ' . $e->getMessage(),
                'error_code' => 'INLINE_RESPONSE_FAILED'
            ], 500);
        }
    }

    private function downloadResponse($pdfContent, $filename)
    {
        Log::info('Sending download PDF response', ['size' => strlen($pdfContent)]);
        
        try {
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($pdfContent),
            ]);
        } catch (Exception $e) {
            Log::error('Download response failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send download response: ' . $e->getMessage(),
                'error_code' => 'DOWNLOAD_RESPONSE_FAILED'
            ], 500);
        }
    }

    private function saveAndRedirectResponse($pdfContent, $filename)
    {
        Log::info('Saving PDF and returning URL');
        
        try {
            $publicPath = public_path('temp_pdfs');
            if (!file_exists($publicPath)) {
                mkdir($publicPath, 0755, true);
            }
            
            $filePath = $publicPath . '/' . $filename;
            file_put_contents($filePath, $pdfContent);
            
            $publicUrl = url('temp_pdfs/' . $filename);
            
            Log::info('PDF saved successfully', [
                'file_path' => $filePath,
                'public_url' => $publicUrl,
                'file_size' => filesize($filePath)
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'PDF generated successfully',
                'pdf_url' => $publicUrl,
                'filename' => $filename,
                'size' => strlen($pdfContent)
            ]);
        } catch (Exception $e) {
            Log::error('Save and redirect failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save PDF: ' . $e->getMessage(),
                'error_code' => 'SAVE_RESPONSE_FAILED'
            ], 500);
        }
    }

    private function base64Response($pdfContent, $filename)
    {
        Log::info('Sending base64 PDF response');
        
        try {
            return response()->json([
                'success' => true,
                'pdf_base64' => base64_encode($pdfContent),
                'filename' => $filename,
                'size' => strlen($pdfContent),
                'message' => 'PDF generated successfully as base64'
            ]);
        } catch (Exception $e) {
            Log::error('Base64 response failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create base64 response: ' . $e->getMessage(),
                'error_code' => 'BASE64_RESPONSE_FAILED'
            ], 500);
        }
    }

    private function chunkedResponse($pdfContent, $filename)
    {
        Log::info('Sending chunked PDF response', ['size' => strlen($pdfContent)]);
        
        try {
            return response()->stream(function() use ($pdfContent) {
                $chunkSize = 8192;
                $length = strlen($pdfContent);
                $offset = 0;
                
                while ($offset < $length) {
                    echo substr($pdfContent, $offset, $chunkSize);
                    $offset += $chunkSize;
                    if (ob_get_level()) {
                        ob_flush();
                    }
                    flush();
                }
            }, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
                'Content-Length' => strlen($pdfContent),
                'Transfer-Encoding' => 'chunked',
            ]);
        } catch (Exception $e) {
            Log::error('Chunked response failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send chunked response: ' . $e->getMessage(),
                'error_code' => 'CHUNKED_RESPONSE_FAILED'
            ], 500);
        }
    }

    private function fixImagePaths(&$studentData)
    {
        foreach ($studentData as &$student) {
            // Handle student image
            if (isset($student['students']) && $student['students']->isNotEmpty() && $student['students']->first()->picture) {
                $student['student_image_path'] = $this->sanitizeImagePath($student['students']->first()->picture);
                Log::info('Student image path set', [
                    'student_id' => $student['students']->first()->id,
                    'path' => $student['student_image_path'],
                    'exists' => file_exists($student['student_image_path'])
                ]);
            } else {
                $student['student_image_path'] = public_path('storage/student_avatars/unnamed.jpg');
                Log::info('Using default student image', ['path' => $student['student_image_path']]);
            }
            
            // Handle school logo
            if (isset($student['schoolInfo'])) {
                $logoPath = $student['schoolInfo']->getLogoUrlAttribute();
                $student['school_logo_path'] = $this->sanitizeImagePath($logoPath);
                Log::info('School logo path set', [
                    'path' => $student['school_logo_path'],
                    'exists' => file_exists($student['school_logo_path'])
                ]);
            } else {
                $student['school_logo_path'] = public_path('storage/school_logos/default.jpg');
                Log::info('Using default school logo', ['path' => $student['school_logo_path']]);
            }
        }
    }

    private function sanitizeImagePath($path)
    {
        if (empty($path)) {
            Log::warning('Empty image path provided');
            return null;
        }

        // Normalize path separators for Windows
        $path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
        
        // Remove any URL prefixes
        $path = preg_replace('/^(http:\/\/|https:\/\/|\/\/)[^\/]+/', '', $path);
        
        // Remove leading slashes and ensure storage prefix
        $path = ltrim($path, DIRECTORY_SEPARATOR);
        if (!preg_match('/^(storage|school_logos|student_avatars)/', $path)) {
            $path = 'storage/' . $path;
        }
        
        // Build absolute path
        $fullPath = public_path($path);
        
        // Normalize path to prevent duplication
        $fullPath = realpath($fullPath) ?: $fullPath;
        
        // Verify file existence
        if (file_exists($fullPath)) {
            Log::info('Sanitized image path', ['original' => $path, 'sanitized' => $fullPath]);
            return $fullPath;
        }
        
        Log::warning('Image file does not exist', ['path' => $fullPath]);
        return null;
    }

    private function ensureDirectoriesExist()
    {
        $directories = [
            storage_path('app/temp'),
            storage_path('fonts'),
            storage_path('logs'),
            public_path('temp_pdfs')
        ];

        foreach ($directories as $all) {
            if (!file_exists($all)) {
                mkdir($all, 0755, true);
                Log::info('Created directory', ['path' => $all]);
            }
        }
    }

    private function getTermName($termid)
    {
        $terms = [
            1 => 'First_Term',
            2 => 'Second_Term',
            3 => 'Third_Term'
        ];
        
        return $terms[$termid] ?? 'Unknown_Term';
    }

    private function validateStudentData($studentData): bool
    {
        if (empty($studentData)) {
            return false;
        }

        if (empty($studentData['students']) || !$studentData['students']) {
            return false;
        }

        if (!isset($studentData['scores'])) {
            return false;
        }

        return true;
    }

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
            ])->latest('studentclass.created_at')->paginate(100);
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
                schoolarm.arm as name_arm,
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