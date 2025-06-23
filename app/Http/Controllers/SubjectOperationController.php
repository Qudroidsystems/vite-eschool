<?php

namespace App\Http\Controllers;

use App\Models\Broadsheet;
use App\Models\BroadsheetRecord;
use App\Models\BroadsheetRecordMock;
use App\Models\Broadsheets;
use App\Models\BroadsheetsMock;
use App\Models\Schoolclass;
use App\Models\SchoolFirstTerm;
use App\Models\SchoolFirstTermMock;
use App\Models\SchoolSecondTerm;
use App\Models\SchoolSecondTermMock;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\SchoolThirdTerm;
use App\Models\SchoolThirdTermMock;
use App\Models\Student;
use App\Models\Studentpicture;
use App\Models\StudentSubjectRecord;
use App\Models\Subjectclass;
use App\Models\SubjectRegistrationStatus;
use App\Models\SubjectTeacher;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubjectOperationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View subject-operation|Create subject-operation|Update subject-operation|Delete subject-operation', ['only' => ['index', 'subjectinfo', 'getRegisteredClasses']]);
        $this->middleware('permission:Create subject-operation', ['only' => ['store']]);
        $this->middleware('permission:Delete subject-operation', ['only' => ['destroy']]);
    }

    /**
     * Display a list of students for subject registration with filters.
     */
    public function index(Request $request): \Illuminate\View\View|\Illuminate\Http\Response
    {
        $pagetitle = "Subject Operation Management";

        // Fetch dropdown data
        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id as id', 'schoolarm.arm as schoolarm', 'schoolclass.schoolclass as schoolclass'])
            ->orderBy('schoolclass.schoolclass')
            ->get();
        $schoolterms = Schoolterm::all();
        $schoolsessions = Schoolsession::all();

        $staffs = User::whereHas('roles', function ($q) {
            $q->where('name', '!=', 'Student');
        })->get(['users.id as userid', 'users.name as name', 'users.avatar as avatar']);

        $students = null;
        $subjectTeachers = null;

        // Check if filtering is requested
        if ($request->filled(['class_id', 'session_id']) && 
            $request->input('class_id') !== 'ALL' && 
            $request->input('session_id') !== 'ALL') {
            
            // Fetch subject teachers for the selected class and session
            $subjectTeachers = SubjectTeacher::leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
                ->leftJoin('subjectclass', 'subjectclass.subjectteacherid', '=', 'subjectteacher.id')
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->where('subjectteacher.sessionid', $request->input('session_id'))
                ->where('subjectclass.schoolclassid', $request->input('class_id'))
                ->select([
                    'subjectteacher.id as id',
                    'subjectclass.id as subjectclassid',
                    'users.id as userid',
                    'users.name as staffname',
                    'users.avatar as avatar',
                    'subject.id as subjectid',
                    'subject.subject as subjectname',
                    'subject.subject_code as subjectcode',
                    'schoolterm.id as termid',
                    'schoolterm.term as termname',
                    'schoolsession.id as sessionid',
                    'schoolsession.session as sessionname',
                    'schoolclass.schoolclass as class_name',
                    'schoolarm.arm as arm_name',
                    'subjectteacher.updated_at'
                ])
                ->get();

            // Fetch students
            $query = Student::leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
                ->leftJoin('studentclass', 'studentclass.studentid', '=', 'studentRegistration.id')
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm');

            // Apply filters
            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('studentRegistration.admissionno', 'like', "%{$search}%")
                    ->orWhere('studentRegistration.firstname', 'like', "%{$search}%")
                    ->orWhere('studentRegistration.lastname', 'like', "%{$search}%");
                });
            }
            
            if ($gender = $request->input('gender')) {
                if ($gender !== 'ALL') {
                    $query->where('studentRegistration.gender', $gender);
                }
            }
            
            if ($admissionNo = $request->input('admissionno')) {
                if ($admissionNo !== 'ALL') {
                    $query->where('studentRegistration.admissionno', $admissionNo);
                }
            }
            
            // Required filters
            $query->where('studentclass.schoolclassid', $request->input('class_id'))
                ->where('studentclass.sessionid', $request->input('session_id'));

            $students = $query->select([
                'studentRegistration.id as id',
                'studentRegistration.admissionno as admissionno',
                'studentRegistration.firstname',
                'studentRegistration.lastname',
                'studentRegistration.gender',
                'studentRegistration.updated_at',
                'studentpicture.picture',
                'studentclass.studentid as studentid',
                'studentclass.schoolclassid as schoolclassid',
                'studentclass.sessionid',
                'schoolclass.schoolclass as class_name',
                'schoolarm.arm as arm_name'
            ])->paginate(100)->appends($request->query());

            if (config('app.debug')) {
                Log::info('Students fetched', [
                    'count' => $students->count(),
                    'student_ids' => $students->pluck('id')->toArray(),
                    'filters' => $request->only(['class_id', 'session_id', 'search', 'gender', 'admissionno']),
                ]);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return view('subjectoperation.index', compact('students', 'subjectTeachers', 'pagetitle', 'schoolclass', 'schoolterms', 'schoolsessions'));
        }

        return view('subjectoperation.index', compact('students', 'subjectTeachers', 'pagetitle', 'schoolclass', 'schoolterms', 'schoolsessions'));
    }
        
    // Add this new method to handle subject teachers AJAX request
    public function getSubjectTeachers(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Invalid request'], 400);
        }
    
        $classId = $request->input('class_id');
        $termId = $request->input('term_id');
        $sessionId = $request->input('session_id');
    
        if (!$classId || !$termId || !$sessionId || 
            $classId === 'ALL' || $termId === 'ALL' || $sessionId === 'ALL') {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
    
        $subjectTeachers = SubjectTeacher::leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->leftJoin('subjectclass', 'subjectclass.subjectteacherid', '=', 'subjectteacher.id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->where('subjectteacher.termid', $termId)
            ->where('subjectteacher.sessionid', $sessionId)
            ->where('subjectclass.schoolclassid', $classId)
            ->select([
                'subjectteacher.id as id',
                'subjectclass.id as subjectclassid',
                'users.id as userid',
                'users.name as staffname',
                'users.avatar as avatar',
                'subject.id as subjectid',
                'subject.subject as subjectname',
                'subject.subject_code as subjectcode',
                'schoolterm.id as termid',
                'schoolterm.term as termname',
                'schoolsession.id as sessionid',
                'schoolsession.session as sessionname',
                'schoolclass.schoolclass as class_name',
                'schoolarm.arm as arm_name'
            ])
            ->get();
    
        return response()->json([
            'success' => true,
            'data' => $subjectTeachers,
            'count' => $subjectTeachers->count()
        ]);
    }

    /**
     * Display subject information for a specific student.
     */
    public function subjectinfo(Request $request, $id, $schoolclassid, $termid, $sessionid): \Illuminate\View\View|\Illuminate\Http\JsonResponse
    {
        $current = "Current";

        try {
            $pagetitle = "Subject Operation Management";

            Log::info('Fetching subject info for student', [
                'student_id' => $id,
                'schoolclassid' => $schoolclassid,
                //'termid' => $termid,
                'sessionid' => $sessionid,
            ]);

            $studentdata = Student::where('id', $id)->get();
            if ($studentdata->isEmpty()) {
                Log::error('Student not found', ['student_id' => $id]);
                return response()->json(['success' => false, 'message' => 'Student not found'], 404);
            }

            $studentpic = Studentpicture::where('studentid', $id)->select(['studentid', 'picture as avatar'])->get();

            $subjectclass = Subjectclass::query()
                ->where('subjectclass.schoolclassid', $schoolclassid)
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
                ->where('schoolterm.id', $termid)
                ->where('schoolsession.id', $sessionid)
                ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
                ->leftJoin('staffbioinfo', 'staffbioinfo.userid', '=', 'users.id')
                ->leftJoin('staffpicture', 'staffpicture.staffid', '=', 'users.id')
                ->groupBy([
                    'subject.id',
                    'users.id',
                    'staffbioinfo.title',
                    'users.name',
                    'staffpicture.picture',
                    'subject.subject',
                    'subject.subject_code',
                    'subjectclass.id',
                    'schoolterm.term',
                    'schoolterm.id',
                    'schoolsession.session',
                    'schoolsession.id'
                ])
                ->select([
                    'subject.id as subjectid',
                    'staffbioinfo.title',
                    'users.name',
                    'staffpicture.picture as picture',
                    'subject.subject',
                    'users.id as staffid',
                    'subject.subject_code as subjectcode',
                    'subjectclass.id as subjectclassid',
                    'schoolterm.term',
                    'schoolterm.id as termid',
                    'schoolsession.session',
                    'schoolsession.id as sessionid'
                ])
                ->get();

            if ($subjectclass->isEmpty()) {
                Log::warning('No subjects found for the given class, term, and session', [
                    'schoolclassid' => $schoolclassid,
                    'termid' => $termid,
                    'sessionid' => $sessionid,
                ]);
            }

            $subjectRegistrations = [];
            foreach ($subjectclass as $sc) {
                $subjectRegistrations[$sc->subjectid][$sc->staffid] = [
                    'subjectclassid' => $sc->subjectclassid,
                    'status' => StudentSubjectRecord::where([
                        'studentId' => $id,
                        'subjectclassid' => $sc->subjectclassid,
                        'staffid' => $sc->staffid,
                       // 'term' => $termid, // Re-enable term filter
                        'session' => $sessionid,
                    ])->exists() ? ['status' => 'Registered', 'broadsheetid' => SubjectRegistrationStatus::where([
                        'studentid' => $id,
                        'subjectclassid' => $sc->subjectclassid,
                        'staffid' => $sc->staffid,
                       // 'termid' => $termid, // Re-enable termid filter
                    ])->value('broadsheetid')] : ['status' => 'Not Registered', 'broadsheetid' => null],
                ];
            }

            $totalreg = Subjectclass::where('subjectclass.schoolclassid', $schoolclassid)
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
                ->where('schoolterm.id', $termid)
                ->where('schoolsession.id', $sessionid)
                ->distinct('subjectteacher.subjectid')
                ->count('subjectteacher.subjectid');

            $regcount = StudentSubjectRecord::where('student_subject_register_record.studentId', $id)
                ->leftJoin('subjectclass', 'subjectclass.id', '=', 'student_subject_register_record.subjectclassid')
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'student_subject_register_record.session')
                ->where('schoolterm.id', $termid)
                ->where('schoolsession.status', $current)
                ->count();

            $noregcount = $totalreg - $regcount;

            $classname = Schoolclass::where('schoolclass.id', $schoolclassid)
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->select(['schoolclass.id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
                ->get();

            $terms = Schoolterm::all();

            if (config('app.debug')) {
                Log::info('Subject info for student ID: ' . $id, ['subjects' => $subjectclass->toArray()]);
            }

            return view('subjectoperation.subjectinfo', compact(
                'studentpic',
                'classname',
                'subjectclass',
                'subjectRegistrations',
                'studentdata',
                'id',
                'termid',
                'sessionid',
                'totalreg',
                'regcount',
                'noregcount',
                'pagetitle',
                'terms'
            ));
        } catch (\Exception $error) {
            Log::error('Error fetching subject info', [
                'student_id' => $id,
                'schoolclassid' => $schoolclassid,
                'termid' => $termid,
                'sessionid' => $sessionid,
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subject information: ' . $error->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created subject registration for one or multiple students.
     */
    
 public function store(Request $request): array
{
    $validated = $request->validate([
        'studentid' => ['required', 'array'],
        'studentid.*' => ['required', 'exists:studentRegistration,id'],
        'subjectclassid' => ['required', 'exists:subjectclass,id'],
        'staffid' => ['required', 'exists:users,id'],
        'termid' => ['required', 'exists:schoolterm,id'],
        'sessionid' => ['required', 'exists:schoolsession,id'],
    ]);

    $studentCount = count($validated['studentid']);
    
    // Configuration thresholds
    $batchThreshold = 50; // Use batch processing if more than 50 students
    $largeDatasetThreshold = 500; // Special handling for very large datasets
    
    Log::info('Subject Registration Started', [
        'student_count' => $studentCount,
        'processing_method' => $studentCount > $batchThreshold ? 'batch' : 'individual',
        'subjectclassid' => $validated['subjectclassid'],
        'termid' => $validated['termid'],
    ]);

    // Choose processing method based on dataset size
    if ($studentCount <= $batchThreshold) {
        return $this->processIndividually($validated);
    } elseif ($studentCount <= $largeDatasetThreshold) {
        return $this->processBatch($validated);
    } else {
        return $this->processLargeDataset($validated);
    }
}

/**
 * Process students individually - Best for small datasets (≤50 students)
 * Provides detailed error handling and precise duplicate detection
 */
private function processIndividually(array $validated): array
{
    $results = [];
    $successCount = 0;
    $errors = [];
    $skippedCount = 0;

    try {
        DB::beginTransaction();

        $subjectclass = Subjectclass::findOrFail($validated['subjectclassid']);
        $subjectId = $subjectclass->subjectid;
        $schoolclassId = $subjectclass->schoolclassid;

        // Pre-check for existing registrations
        $existingRegistrations = SubjectRegistrationStatus::where([
            'subjectclassid' => $validated['subjectclassid'],
            'termid' => $validated['termid'],
            'sessionid' => $validated['sessionid'],
        ])->whereIn('studentid', $validated['studentid'])
          ->pluck('studentid')
          ->toArray();

        $studentsToProcess = array_diff($validated['studentid'], $existingRegistrations);
        $skippedCount = count($existingRegistrations);

        foreach ($existingRegistrations as $existingStudentId) {
            $errors[] = "Student ID {$existingStudentId} is already registered";
        }

        if (empty($studentsToProcess)) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'All students are already registered for this subject.',
                'errors' => $errors,
                'skipped_count' => $skippedCount,
            ];
        }

        foreach ($studentsToProcess as $studentId) {
            try {
                // Create or find BroadsheetRecord
                $record = BroadsheetRecord::firstOrCreate([
                    'student_id' => $studentId,
                    'subject_id' => $subjectId,
                    'schoolclass_id' => $schoolclassId,
                    'session_id' => $validated['sessionid'],
                ]);

                $recordmock = BroadsheetRecordMock::firstOrCreate([
                    'student_id' => $studentId,
                    'subject_id' => $subjectId,
                    'schoolclass_id' => $schoolclassId,
                    'session_id' => $validated['sessionid'],
                ]);

                // Create dependent records if they don't exist
                $this->createDependentRecords($record->id, $recordmock->id, $studentId, $validated);

                $successCount++;
                $results[] = "Successfully registered student ID {$studentId}";

            } catch (\Exception $e) {
                Log::error("Error processing student {$studentId}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $errors[] = "Failed to register student ID {$studentId}: " . $e->getMessage();
                continue;
            }
        }

        if ($successCount > 0) {
            DB::commit();
            return [
                'success' => true,
                'message' => "Individual processing: {$successCount} students registered successfully",
                'method' => 'individual',
                'results' => $results,
                'errors' => $errors,
                'success_count' => $successCount,
                'skipped_count' => $skippedCount,
            ];
        } else {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'No students were registered.',
                'errors' => $errors,
                'skipped_count' => $skippedCount,
            ];
        }

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Individual processing error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        return [
            'success' => false,
            'message' => 'Individual processing failed: ' . $e->getMessage(),
            'errors' => [$e->getMessage()],
        ];
    }
}

/**
 * Process students in batch - Best for medium datasets (51-500 students)
 * Balances performance with error handling
 */
private function processBatch(array $validated): array
{
    try {
        DB::beginTransaction();

        $subjectclass = Subjectclass::findOrFail($validated['subjectclassid']);
        $subjectId = $subjectclass->subjectid;
        $schoolclassId = $subjectclass->schoolclassid;
        $now = now();

        // Filter out already registered students
        $existingRegistrations = SubjectRegistrationStatus::where([
            'subjectclassid' => $validated['subjectclassid'],
            'termid' => $validated['termid'],
            'sessionid' => $validated['sessionid'],
        ])->whereIn('studentid', $validated['studentid'])
          ->pluck('studentid')
          ->toArray();

        $studentsToProcess = array_diff($validated['studentid'], $existingRegistrations);
        $skippedCount = count($existingRegistrations);

        if (empty($studentsToProcess)) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'All students are already registered.',
                'skipped_count' => $skippedCount,
            ];
        }

        // Prepare bulk insert data for BroadsheetRecords
        $broadsheetRecords = [];
        $broadsheetRecordsMock = [];
        
        foreach ($studentsToProcess as $studentId) {
            $broadsheetRecords[] = [
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'schoolclass_id' => $schoolclassId,
                'session_id' => $validated['sessionid'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $broadsheetRecordsMock[] = [
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'schoolclass_id' => $schoolclassId,
                'session_id' => $validated['sessionid'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Bulk insert BroadsheetRecords
        BroadsheetRecord::insertOrIgnore($broadsheetRecords);
        BroadsheetRecordMock::insertOrIgnore($broadsheetRecordsMock);

        // Get the created records with their IDs
        $createdRecords = BroadsheetRecord::where([
            'subject_id' => $subjectId,
            'schoolclass_id' => $schoolclassId,
            'session_id' => $validated['sessionid'],
        ])->whereIn('student_id', $studentsToProcess)
          ->get()
          ->keyBy('student_id');

        $createdRecordsMock = BroadsheetRecordMock::where([
            'subject_id' => $subjectId,
            'schoolclass_id' => $schoolclassId,
            'session_id' => $validated['sessionid'],
        ])->whereIn('student_id', $studentsToProcess)
          ->get()
          ->keyBy('student_id');

        // Prepare and insert dependent records
        $this->bulkCreateDependentRecords($createdRecords, $createdRecordsMock, $studentsToProcess, $validated, $now);

        DB::commit();

        return [
            'success' => true,
            'message' => "Batch processing: " . count($studentsToProcess) . " students registered successfully",
            'method' => 'batch',
            'success_count' => count($studentsToProcess),
            'skipped_count' => $skippedCount,
        ];

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Batch processing error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        return [
            'success' => false,
            'message' => 'Batch processing failed: ' . $e->getMessage(),
            'errors' => [$e->getMessage()],
        ];
    }
}

/**
 * Process very large datasets in chunks - Best for large datasets (>500 students)
 * Optimized for memory efficiency and performance
 */
private function processLargeDataset(array $validated): array
{
    try {
        DB::beginTransaction();

        $subjectclass = Subjectclass::findOrFail($validated['subjectclassid']);
        $subjectId = $subjectclass->subjectid;
        $schoolclassId = $subjectclass->schoolclassid;
        
        $chunkSize = 200; // Process in chunks of 200 students
        $totalStudents = count($validated['studentid']);
        $totalProcessed = 0;
        $totalSkipped = 0;
        $chunks = array_chunk($validated['studentid'], $chunkSize);

        Log::info("Large dataset processing started", [
            'total_students' => $totalStudents,
            'chunks' => count($chunks),
            'chunk_size' => $chunkSize,
        ]);

        foreach ($chunks as $chunkIndex => $studentChunk) {
            Log::info("Processing chunk " . ($chunkIndex + 1) . "/" . count($chunks));

            // Filter already registered students for this chunk
            $existingInChunk = SubjectRegistrationStatus::where([
                'subjectclassid' => $validated['subjectclassid'],
                'termid' => $validated['termid'],
                'sessionid' => $validated['sessionid'],
            ])->whereIn('studentid', $studentChunk)
              ->pluck('studentid')
              ->toArray();

            $studentsToProcess = array_diff($studentChunk, $existingInChunk);
            $totalSkipped += count($existingInChunk);

            if (empty($studentsToProcess)) {
                continue; // Skip this chunk if all students are already registered
            }

            // Process this chunk
            $this->processChunk($studentsToProcess, $validated, $subjectId, $schoolclassId);
            $totalProcessed += count($studentsToProcess);

            // Clear memory periodically
            if (($chunkIndex + 1) % 5 == 0) {
                gc_collect_cycles();
            }
        }

        DB::commit();

        return [
            'success' => true,
            'message' => "Large dataset processing: {$totalProcessed} students registered successfully",
            'method' => 'large_dataset_chunks',
            'success_count' => $totalProcessed,
            'skipped_count' => $totalSkipped,
            'total_chunks' => count($chunks),
        ];

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Large dataset processing error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        return [
            'success' => false,
            'message' => 'Large dataset processing failed: ' . $e->getMessage(),
            'errors' => [$e->getMessage()],
        ];
    }
}

/**
 * Process a single chunk of students
 */
private function processChunk(array $students, array $validated, int $subjectId, int $schoolclassId): void
{
    $now = now();

    // Bulk insert BroadsheetRecords for this chunk
    $broadsheetRecords = [];
    $broadsheetRecordsMock = [];
    
    foreach ($students as $studentId) {
        $broadsheetRecords[] = [
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'schoolclass_id' => $schoolclassId,
            'session_id' => $validated['sessionid'],
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $broadsheetRecordsMock[] = [
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'schoolclass_id' => $schoolclassId,
            'session_id' => $validated['sessionid'],
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    BroadsheetRecord::insertOrIgnore($broadsheetRecords);
    BroadsheetRecordMock::insertOrIgnore($broadsheetRecordsMock);

    // Get created records and create dependent records
    $createdRecords = BroadsheetRecord::where([
        'subject_id' => $subjectId,
        'schoolclass_id' => $schoolclassId,
        'session_id' => $validated['sessionid'],
    ])->whereIn('student_id', $students)
      ->get()
      ->keyBy('student_id');

    $createdRecordsMock = BroadsheetRecordMock::where([
        'subject_id' => $subjectId,
        'schoolclass_id' => $schoolclassId,
        'session_id' => $validated['sessionid'],
    ])->whereIn('student_id', $students)
      ->get()
      ->keyBy('student_id');

    $this->bulkCreateDependentRecords($createdRecords, $createdRecordsMock, $students, $validated, $now);
}

/**
 * Create dependent records for individual processing
 */
private function createDependentRecords(int $recordId, int $recordMockId, int $studentId, array $validated): void
{
    // Create Broadsheet if it doesn't exist
    Broadsheets::firstOrCreate([
        'broadsheet_record_id' => $recordId,
        'term_id' => $validated['termid'],
        'subjectclass_id' => $validated['subjectclassid'],
    ], [
        'staff_id' => $validated['staffid'],
    ]);

    // Create BroadsheetMock if it doesn't exist
    BroadsheetsMock::firstOrCreate([
        'broadsheet_records_mock_id' => $recordMockId,
        'term_id' => $validated['termid'],
        'subjectclass_id' => $validated['subjectclassid'],
    ], [
        'staff_id' => $validated['staffid'],
    ]);

    // Create SubjectRegistrationStatus if it doesn't exist
    SubjectRegistrationStatus::firstOrCreate([
        'studentid' => $studentId,
        'subjectclassid' => $validated['subjectclassid'],
        'termid' => $validated['termid'],
        'sessionid' => $validated['sessionid'],
    ], [
        'staffid' => $validated['staffid'],
        'broadsheetid' => $recordId,
        'Status' => 1,
    ]);

    // Create StudentSubjectRecord if it doesn't exist
    StudentSubjectRecord::firstOrCreate([
        'studentId' => $studentId,
        'subjectclassid' => $validated['subjectclassid'],
        //'term' => $validated['termid'],
        'session' => $validated['sessionid'],
    ], [
        'staffid' => $validated['staffid'],
        'broadsheetid' => $recordId,
    ]);
}

/**
 * Bulk create dependent records for batch processing
 */
private function bulkCreateDependentRecords($createdRecords, $createdRecordsMock, array $students, array $validated, $now): void
{
    $broadsheets = [];
    $broadsheetsMock = [];
    $subjectRegistrations = [];
    $studentSubjectRecords = [];

    foreach ($students as $studentId) {
        $record = $createdRecords->get($studentId);
        $recordMock = $createdRecordsMock->get($studentId);

        if (!$record || !$recordMock) {
            Log::error("Could not find broadsheet record for student {$studentId}");
            continue;
        }

        $broadsheets[] = [
            'broadsheet_record_id' => $record->id,
            'term_id' => $validated['termid'],
            'subjectclass_id' => $validated['subjectclassid'],
            'staff_id' => $validated['staffid'],
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $broadsheetsMock[] = [
            'broadsheet_records_mock_id' => $recordMock->id,
            'term_id' => $validated['termid'],
            'subjectclass_id' => $validated['subjectclassid'],
            'staff_id' => $validated['staffid'],
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $subjectRegistrations[] = [
            'studentid' => $studentId,
            'subjectclassid' => $validated['subjectclassid'],
            'staffid' => $validated['staffid'],
            'termid' => $validated['termid'],
            'sessionid' => $validated['sessionid'],
            'broadsheetid' => $record->id,
            'Status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $studentSubjectRecords[] = [
            'studentId' => $studentId,
            'subjectclassid' => $validated['subjectclassid'],
            'staffid' => $validated['staffid'],
           // 'term' => $validated['termid'],
            'session' => $validated['sessionid'],
            'broadsheetid' => $record->id,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    // Bulk insert all dependent records
    if (!empty($broadsheets)) {
        Broadsheets::insertOrIgnore($broadsheets);
    }
    if (!empty($broadsheetsMock)) {
        BroadsheetsMock::insertOrIgnore($broadsheetsMock);
    }
    if (!empty($subjectRegistrations)) {
        SubjectRegistrationStatus::insertOrIgnore($subjectRegistrations);
    }
    if (!empty($studentSubjectRecords)) {
        StudentSubjectRecord::insertOrIgnore($studentSubjectRecords);
    }
}
 
    public function registeredClasses(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Validate parameters
            $validated = $request->validate([
                'class_id' => ['required', 'integer', 'exists:schoolclass,id'],
                'session_id' => ['required', 'integer', 'exists:schoolsession,id'],
                'term_id' => ['nullable', 'integer', 'exists:schoolterm,id'],
            ]);

            Log::info('Fetching registered classes', [
                'class_id' => $validated['class_id'],
                'session_id' => $validated['session_id'],
                'term_id' => $validated['term_id'],
            ]);

            DB::statement('SET SESSION group_concat_max_len = 1000000');

            $query = SubjectRegistrationStatus::query()
                ->join('subjectclass', 'subjectclass.id', '=', 'subject_registration_status.subjectclassid')
                ->join('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->join('schoolsession', 'schoolsession.id', '=', 'subject_registration_status.sessionid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subject_registration_status.termid')
                ->leftJoin('broadsheet', 'broadsheet.id', '=', 'subject_registration_status.broadsheetid')
                ->leftJoin('subject', 'subject.id', '=', 'broadsheet.subjectid') // Direct subjectid
                ->leftJoin('subjectteacher', 'subjectteacher.subjectid', '=', 'subject.id')
                ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
                ->where('subjectclass.schoolclassid', $validated['class_id'])
                ->where('subject_registration_status.sessionid', $validated['session_id'])
                ->when($validated['term_id'], function ($query, $termId) {
                    return $query->where('subject_registration_status.termid', $termId);
                }, function ($query) {
                    return $query->whereExists(function ($subQuery) {
                        $subQuery->select(DB::raw(1))
                                ->from('schoolterm')
                                ->whereColumn('schoolterm.id', 'subject_registration_status.termid')
                                ->where('schoolterm.currentterm', 1);
                    });
                })
                ->groupBy([
                    'schoolclass.id',
                    'schoolarm.id',
                    'schoolsession.id',
                    'schoolterm.id',
                    'schoolclass.schoolclass',
                    'schoolarm.arm',
                    'schoolsession.session',
                    'schoolterm.term',
                ])
                ->select([
                    'schoolclass.id as class_id',
                    'schoolclass.schoolclass as class_name',
                    \DB::raw('COALESCE(schoolarm.arm, "None") as arm_name'),
                    \DB::raw('COALESCE(schoolsession.session, "Unknown") as session_name'),
                    \DB::raw('COALESCE(schoolterm.term, "Unknown") as term_name'),
                    \DB::raw('COUNT(DISTINCT subject_registration_status.studentid) as student_count'),
                    \DB::raw('COUNT(DISTINCT subject_registration_status.subjectclassid) as subject_count'),
                    \DB::raw('COALESCE(GROUP_CONCAT(DISTINCT subject.subject ORDER BY subject.subject SEPARATOR ", "), "None") as subjects'),
                    \DB::raw('COALESCE(GROUP_CONCAT(DISTINCT users.name ORDER BY users.name SEPARATOR ", "), "None") as teachers'),
                ]);

            Log::debug('Registered classes query', ['query' => $query->toSql(), 'bindings' => $query->getBindings()]);

            $rawData = DB::select($query->toSql(), $query->getBindings());
            Log::debug('Registered classes raw data', ['raw_data' => json_encode($rawData)]);

            $classes = $query->get();

            Log::debug('Registered classes results', ['data' => $classes->toArray()]);

            return response()->json([
                'success' => true,
                'data' => $classes,
            ]);
        } catch (\ValidationException $e) {
            Log::warning('Validation failed', [
                'errors' => $e->errors(),
                'request' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid class or session.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error fetching registered classes', [
                'request' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch registered classes: ' . $e->getMessage(),
            ], 500);
        }
    }
        
    /**
     * Remove a subject registration.
     */
    public function destroy(Request $request): array
    {
        $validated = $request->validate([
            'studentid' => ['required', 'array'],
            'studentid.*' => ['required', 'exists:studentRegistration,id'],
            'subjectclassid' => ['required', 'exists:subjectclass,id'],
            'termid' => ['required', 'exists:schoolterm,id'],
            'sessionid' => ['required', 'exists:schoolsession,id'],
            'staffid' => ['nullable', 'exists:users,id'], // Optional, for extra validation
        ]);

        $results = [];
        $successCount = 0;
        $errors = [];

        try {
            DB::beginTransaction();

            foreach ($validated['studentid'] as $studentId) {
                Log::info('Processing removal of subject registration for student ID', [
                    'studentId' => $studentId,
                    'subjectclassid' => $validated['subjectclassid'],
                    'termid' => $validated['termid'],
                    'sessionid' => $validated['sessionid'],
                ]);

                // Find Broadsheet record
                $broadsheetQuery = Broadsheet::where([
                    'studentId' => $studentId,
                    'subjectclassid' => $validated['subjectclassid'],
                    'termid' => $validated['termid'],
                    'session' => $validated['sessionid'],
                ]);

                if (isset($validated['staffid'])) {
                    $broadsheetQuery->where('staffid', $validated['staffid']);
                }

                $broadsheet = $broadsheetQuery->first();

                if (!$broadsheet) {
                    Log::warning("No Broadsheet record found for student ID {$studentId}, subjectclassid {$validated['subjectclassid']}, term {$validated['termid']}");
                    $errors[] = "No registration found for student ID {$studentId} in term ID {$validated['termid']}";
                    continue;
                }

                // Delete related records
                Log::info('Deleting SubjectRegistrationStatus for student ID', ['studentId' => $studentId]);
                SubjectRegistrationStatus::where([
                    'studentid' => $studentId,
                    'subjectclassid' => $validated['subjectclassid'],
                    'termid' => $validated['termid'],
                    'sessionid' => $validated['sessionid'],
                    'broadsheetid' => $broadsheet->id,
                ])->delete();

                Log::info('Deleting StudentSubjectRecord for student ID', ['studentId' => $studentId]);
                StudentSubjectRecord::where([
                    'studentId' => $studentId,
                    'subjectclassid' => $validated['subjectclassid'],
                    'term' => $validated['termid'],
                    'session' => $validated['sessionid'],
                    'broadsheetid' => $broadsheet->id,
                ])->delete();

                // Delete term-specific records
                $termid = (int)$validated['termid'];
                switch ($termid) {
                    case 1:
                        Log::info('Deleting SchoolFirstTerm and SchoolFirstTermMock for student ID', ['studentId' => $studentId]);
                        SchoolFirstTerm::where([
                            'schoolbroadsheetId' => $broadsheet->id,
                            'studentId' => $studentId,
                            'subjectclassid' => $validated['subjectclassid'],
                            'termid' => $termid,
                            'session' => $validated['sessionid'],
                        ])->delete();

                        SchoolFirstTermMock::where([
                            'schoolbroadsheetId' => $broadsheet->id,
                            'studentId' => $studentId,
                            'subjectclassid' => $validated['subjectclassid'],
                            'termid' => $termid,
                            'session' => $validated['sessionid'],
                        ])->delete();
                        break;

                    case 2:
                        Log::info('Deleting SchoolSecondTerm and SchoolSecondTermMock for student ID', ['studentId' => $studentId]);
                        SchoolSecondTerm::where([
                            'schoolbroadsheetId' => $broadsheet->id,
                            'studentId' => $studentId,
                            'subjectclassid' => $validated['subjectclassid'],
                            'termid' => $termid,
                            'session' => $validated['sessionid'],
                        ])->delete();

                        SchoolSecondTermMock::where([
                            'schoolbroadsheetId' => $broadsheet->id,
                            'studentId' => $studentId,
                            'subjectclassid' => $validated['subjectclassid'],
                            'termid' => $termid,
                            'session' => $validated['sessionid'],
                        ])->delete();
                        break;

                    case 3:
                        Log::info('Deleting SchoolThirdTerm and SchoolThirdTermMock for student ID', ['studentId' => $studentId]);
                        SchoolThirdTerm::where([
                            'schoolbroadsheetId' => $broadsheet->id,
                            'studentId' => $studentId,
                            'subjectclassid' => $validated['subjectclassid'],
                            'termid' => $termid,
                            'session' => $validated['sessionid'],
                        ])->delete();

                        SchoolThirdTermMock::where([
                            'schoolbroadsheetId' => $broadsheet->id,
                            'studentId' => $studentId,
                            'subjectclassid' => $validated['subjectclassid'],
                            'termid' => $termid,
                            'session' => $validated['sessionid'],
                        ])->delete();
                        break;
                }

                // Delete Broadsheet record
                Log::info('Deleting Broadsheet for student ID', ['studentId' => $studentId]);
                $broadsheet->delete();

                $successCount++;
                $results[] = "Successfully removed registration for student ID {$studentId} for subjectclassid {$validated['subjectclassid']} in term ID {$validated['termid']}";
            }

            DB::commit();

            if ($successCount === count($validated['studentid'])) {
                return [
                    'success' => true,
                    'message' => "Successfully removed {$successCount} student(s) registration(s) for the subject in term ID {$validated['termid']}.",
                    'results' => $results,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Removed {$successCount} student(s) registration(s), but encountered errors.",
                    'results' => $results,
                    'errors' => $errors,
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error removing subject registrations', [
                'request' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'Failed to remove subject registrations: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Fetch registered classes for the modal.
     */
    public function getRegisteredClasses(Request $request): JsonResponse
    {
        try {
            $registeredClasses = Subjectclass::query()
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->leftJoin('student_subject_register_record', 'student_subject_register_record.subjectclassid', '=', 'subjectclass.id')
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                ->select([
                    'schoolclass.id as class_id',
                    'schoolclass.schoolclass as class_name',
                    'schoolarm.arm as arm_name',
                    'schoolsession.session as session_name',
                    'schoolterm.term as term_name',
                    DB::raw('COUNT(DISTINCT student_subject_register_record.studentId) as student_count'),
                    DB::raw('COUNT(DISTINCT subject.id) as subject_count')
                ])
                ->groupBy(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm', 'schoolsession.session', 'schoolterm.term'])
                ->whereNotNull('student_subject_register_record.studentId')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $registeredClasses
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error fetching registered classes: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch registered classes: ' . $e->getMessage()
            ], 500);
        }
    }
}