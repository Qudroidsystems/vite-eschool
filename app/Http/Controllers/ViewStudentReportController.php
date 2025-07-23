<?php

namespace App\Http\Controllers;

use App\Models\Schoolclass;
use App\Models\Schoolsession;
use App\Models\Schoolarm;
use App\Models\Studentclass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ViewStudentReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View student-report', ['only' => ['index', 'show', 'registeredClasses', 'classBroadsheet']]);
        $this->middleware('permission:Create student-report', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update student-report', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete student-report', ['only' => ['destroy']]);
    }

    public function index(Request $request): View
    {
        $pagetitle = "Student Terminal Report Management";
        $current = "Current";

        // Fetch data for filters
        $schoolsessions = Schoolsession::where('status', 'Current')->get();
        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm']);

        // Initialize $classes as null for non-AJAX requests to keep table empty
        $classes = null;

        // Handle AJAX requests for filtering
        if ($request->ajax()) {
            $query = Studentclass::query()
                ->join('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
                ->where('schoolsession.status', '=', $current);

            // Apply filters
            if ($request->has('search') && $request->input('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('schoolclass.schoolclass', 'like', "%{$search}%")
                      ->orWhere('schoolarm.arm', 'like', "%{$search}%");
                });
            }
            if ($request->has('class_id') && $request->input('class_id') !== 'ALL') {
                $query->where('schoolclass.id', $request->input('class_id'));
            }
            if ($request->has('session_id') && $request->input('session_id') !== 'ALL') {
                $query->where('schoolsession.id', $request->input('session_id'));
            }

            // Paginate results with sorting
            $classes = $query->distinct()->orderBy('schoolclass.schoolclass')->paginate(10, [
                'schoolclass.schoolclass as schoolclass',
                'studentclass.sessionid as sessionid',
                'schoolarm.arm as schoolarm',
                'schoolsession.session as session',
                'studentclass.updated_at as updated_at',
                'schoolclass.id as schoolclassID'
            ]);

            if (config('app.debug')) {
                Log::info('Classes fetched:', $classes->toArray());
            }

            return view('studentreports.partials.class_rows', compact('classes'));
        }

        if (config('app.debug')) {
            Log::info('Sessions for select:', $schoolsessions->toArray());
            Log::info('School classes for select:', $schoolclasses->toArray());
        }

        return view('studentreports.index', compact('classes', 'schoolsessions', 'schoolclasses', 'pagetitle'));
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
        // Placeholder: Fetch broadsheet data for the given class, session, and term
        $class = Schoolclass::findOrFail($schoolclassid);
        $session = Schoolsession::findOrFail($sessionid);
        $term = $termid; // Assuming termid is 1, 2, or 3 for First, Second, Third Term
        $pagetitle = "Broadsheet for {$class->schoolclass} - {$session->session} - Term {$term}";

        // Add logic to fetch broadsheet data (e.g., student scores)
        // This is a placeholder; replace with actual query
        $data = [
            'class' => $class,
            'session' => $session,
            'term' => $term,
            'pagetitle' => $pagetitle
        ];

        return view('myclass.broadsheet', $data);
    }
}