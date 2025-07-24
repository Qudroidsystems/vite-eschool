<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Studentclass;
use App\Models\Schoolclass;
use App\Models\StudentRegistration;
use App\Models\StudentPicture;
use App\Models\BroadsheetsMock;
use App\Models\BroadsheetRecordsMock;
use App\Models\SchoolInformation;
use App\Models\Studentpersonalityprofile;
use App\Models\Subject;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class ViewStudentMockReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View student-mock-report', ['only' => ['index', 'show', 'registeredClasses', 'classBroadsheet', 'studentmockresult']]);
        $this->middleware('permission:Create student-mock-report', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update student-mock-report', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete student-mock-report', ['only' => ['destroy']]);
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
     * Calculate subject positions and class averages for the entire class (all arms) for each subject in mock results.
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

        // Fetch all broadsheet mock records for the students, term, and session
        $broadsheets = BroadsheetsMock::whereIn('broadsheet_records_mock.student_id', $students)
            ->where('broadsheetmock.term_id', $termid)
            ->where('broadsheet_records_mock.session_id', $sessionid)
            ->whereIn('broadsheet_records_mock.schoolclass_id', $classIds)
            ->join('broadsheet_records_mock', 'broadsheet_records_mock.id', '=', 'broadsheetmock.broadsheet_records_mock_id')
            ->join('subject', 'subject.id', '=', 'broadsheet_records_mock.subject_id')
            ->join('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records_mock.student_id')
            ->select([
                'broadsheetmock.id',
                'broadsheet_records_mock.student_id',
                'broadsheet_records_mock.subject_id',
                'subject.subject as subject_name',
                'studentRegistration.admissionNo as admission_no',
                'broadsheetmock.total',
                'broadsheetmock.cum',
                'broadsheetmock.subject_position_class',
                'broadsheetmock.avg',
            ])
            ->get();

        if ($broadsheets->isEmpty()) {
            Log::warning('No broadsheet mock records found for class', [
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

            // Update broadsheet mock records with class average and positions
            foreach ($subjectRecords as $record) {
                $newPosition = $record->cum == 0 ? '-' : ($positionMap[$record->id] ?? null);
                if ($newPosition !== '-') {
                    $newPosition = $this->formatOrdinal($newPosition);
                }

                if ($record->avg != $classAvg || $record->subject_position_class != $newPosition) {
                    BroadsheetsMock::where('id', $record->id)->update([
                        'avg' => $classAvg,
                        'subject_position_class' => $newPosition,
                    ]);

                    Log::info('Updated broadsheet mock metrics', [
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

            Log::info('Calculated metrics for subject (mock)', [
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

        Log::info('Completed class metrics calculation (mock)', [
            'class_name' => $className,
            'schoolclassids' => $classIds,
            'sessionid' => $sessionid,
            'termid' => $termid,
            'total_subjects' => $subjectGroups->count(),
            'total_students' => count($students),
        ]);
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

    /**
     * Display the student's mock result for a specific class, session, and term.
     *
     * @param int $id
     * @param int $schoolclassid
     * @param int $sessionid
     * @param int $termid
     * @return View
     */
    public function studentmockresult($id, $schoolclassid, $sessionid, $termid)
    {
        // Validate input parameters
        if (!is_numeric($id) || !is_numeric($schoolclassid) || !is_numeric($sessionid) || !is_numeric($termid)) {
            abort(404, 'Invalid parameters');
        }

        $pagetitle = "Student Mock Result";

        // Fetch student details
        $students = StudentRegistration::where('studentRegistration.id', $id)
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

        // Fetch mock report scores for the specific student and schoolclassid
        $mockScores = BroadsheetsMock::where('broadsheet_records_mock.student_id', $id)
            ->where('broadsheetmock.term_id', $termid)
            ->where('broadsheet_records_mock.session_id', $sessionid)
            ->where('broadsheet_records_mock.schoolclass_id', $schoolclassid)
            ->leftJoin('broadsheet_records_mock', 'broadsheet_records_mock.id', '=', 'broadsheetmock.broadsheet_records_mock_id')
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records_mock.subject_id')
            ->orderBy('subject.subject')
            ->get([
                'subject.id as subject_id',
                'subject.subject as subject_name',
                'subject.subject_code',
                'broadsheetmock.exam',
                'broadsheetmock.total',
                'broadsheetmock.cum',
                'broadsheetmock.grade',
                'broadsheetmock.subject_position_class as position',
                'broadsheetmock.avg as class_average',
            ]);

        // Fetch class, term, session, and number of students
        $schoolclass = Schoolclass::where('id', $schoolclassid)->with('armRelation')->first(['schoolclass', 'arm']);
        $schoolterm = Schoolterm::where('id', $termid)->value('term') ?? 'N/A';
        $schoolsession = Schoolsession::where('id', $sessionid)->value('session') ?? 'N/A';
        $numberOfStudents = Studentclass::whereIn('schoolclassid', 
            Schoolclass::where('schoolclass', $schoolclass->schoolclass)->pluck('id'))
            ->where('sessionid', $sessionid)
            ->count();
        $schoolInfo = SchoolInformation::getActiveSchool();

        return view('studentreports.studentmockresult')
            ->with('students', $students)
            ->with('studentpp', $studentpp)
            ->with('mockScores', $mockScores)
            ->with('studentid', $id)
            ->with('schoolclassid', $schoolclassid)
            ->with('sessionid', $sessionid)
            ->with('termid', $termid)
            ->with('pagetitle', $pagetitle)
            ->with('schoolclass', $schoolclass)
            ->with('schoolterm', $schoolterm)
            ->with('schoolsession', $schoolsession)
            ->with('numberOfStudents', $numberOfStudents)
            ->with('schoolInfo', $schoolInfo);
    }

    /**
     * Placeholder for show method (implement as needed)
     */
    public function show($id)
    {
        // Implement as needed
    }

    /**
     * Placeholder for create method (implement as needed)
     */
    public function create()
    {
        // Implement as needed
    }

    /**
     * Placeholder for store method (implement as needed)
     */
    public function store(Request $request)
    {
        // Implement as needed
    }

    /**
     * Placeholder for edit method (implement as needed)
     */
    public function edit($id)
    {
        // Implement as needed
    }

    /**
     * Placeholder for update method (implement as needed)
     */
    public function update(Request $request, $id)
    {
        // Implement as needed
    }

    /**
     * Placeholder for destroy method (implement as needed)
     */
    public function destroy($id)
    {
        // Implement as needed
    }
}