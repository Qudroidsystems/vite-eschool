<?php

namespace App\Http\Controllers;

use App\Models\ClassTeacher;
use App\Models\Schoolclass;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Staffclasssetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class MyClassController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View my-class|Create my-class|Update my-class|Delete my-class', ['only' => ['index', 'store']]);
        $this->middleware('permission:Create my-class', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update my-class', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete my-class', ['only' => ['destroy']]);
    }

    public function index(Request $request): View|JsonResponse
    {
        $pagetitle = "Class Management";
        $user = auth()->user();
        $current = "Current";

        $myclass = new LengthAwarePaginator([], 0, 5);

        if ($request->filled('schoolclassid') && $request->filled('sessionid') && $request->input('schoolclassid') !== 'ALL' && $request->input('sessionid') !== 'ALL') {
            $query = ClassTeacher::where('staffid', $user->id)
                ->leftJoin('users', 'users.id', '=', 'classteacher.staffid')
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'classteacher.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'classteacher.sessionid')
                ->where('schoolsession.status', '=', $current)
                ->where('schoolclass.id', $request->input('schoolclassid'))
                ->where('schoolsession.id', $request->input('sessionid'));

            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('schoolclass.schoolclass', 'like', "%{$search}%")
                      ->orWhere('schoolarm.arm', 'like', "%{$search}%");
                });
            }

            $myclass = $query->select([
                'classteacher.id as id',
                'users.id as userid',
                'users.name as staffname',
                'schoolclass.schoolclass as schoolclass',
                'classteacher.termid as termid',
                'classteacher.sessionid as sessionid',
                'schoolarm.arm as schoolarm',
                'schoolclass.description as classcategory',
                'schoolterm.term as term',
                'schoolsession.session as session',
                'classteacher.updated_at as updated_at',
                'schoolclass.id as schoolclassID'
            ])->leftJoin('schoolterm', 'schoolterm.id', '=', 'classteacher.termid')
            ->latest('classteacher.created_at')
            ->paginate(5);
        }

        $terms = Schoolterm::all();
        $schoolsessions = Schoolsession::where('status', 'Current')->get();
        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm']);

        // Calculate classes per term for chart
        $term_counts = [];
        foreach ($terms as $term) {
            $term_counts[$term->term] = ClassTeacher::where('staffid', $user->id)
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'classteacher.termid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'classteacher.sessionid')
                ->where('schoolsession.status', 'Current')
                ->where('schoolterm.term', $term->term)
                ->count();
        }

        if (config('app.debug')) {
            Log::info('Terms for select:', $terms->toArray());
            Log::info('Sessions for select:', $schoolsessions->toArray());
            Log::info('Classes fetched:', $myclass->toArray());
        }

        if ($request->ajax()) {
            return response()->json([
                'tableBody' => view('myclass.partials.class_rows', compact('myclass'))->render(),
                'pagination' => $myclass->links('pagination::bootstrap-5')->render(),
                'classCount' => $myclass->total(),
            ]);
        }

        return view('myclass.index', compact('myclass', 'terms', 'schoolsessions', 'schoolclasses', 'pagetitle', 'term_counts'))
            ->with('sfid', $user->id);
    }

    public function create(): View
    {
        $pagetitle = "Create my-Class Setting";
        $terms = Schoolterm::all();
        $schoolsessions = Schoolsession::where('status', 'Current')->get();
        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm']);
        return view('myclass.create', compact('terms', 'schoolsessions', 'schoolclasses', 'pagetitle'));
    }

    public function store(Request $request): JsonResponse
    {
        Log::debug("Creating class setting", $request->all());

        if (!auth()->user()->hasPermissionTo('Create my-class')) {
            Log::warning("User ID " . auth()->user()->id . " attempted to create my-class setting without 'Create my-class' permission");
            return response()->json([
                'success' => false,
                'message' => 'User does not have the right permissions',
            ], 403);
        }

        try {
            $validated = $request->validate([
                'staffid' => 'required|exists:users,id',
                'vschoolclassid' => 'required|exists:schoolclass,id',
                'termid' => 'required|exists:schoolterm,id',
                'sessionid' => 'required|exists:schoolsession,id',
                'noschoolopened' => 'nullable|integer|min:0',
                'termends' => 'nullable|date',
                'nexttermbegins' => 'nullable|date',
            ]);

            // Check for existing setting
            $check = Staffclasssetting::where('staffid', $validated['staffid'])
                ->where('vschoolclassid', $validated['vschoolclassid'])
                ->where('termid', $validated['termid'])
                ->where('sessionid', $validated['sessionid'])
                ->exists();

            if ($check) {
                return response()->json([
                    'success' => false,
                    'message' => 'Class setting already exists for this term and session.',
                ], 422);
            }

            $setting = Staffclasssetting::create($validated);

            Log::debug("Class setting created successfully: ID {$setting->id}");
            return response()->json([
                'success' => true,
                'message' => 'Class setting created successfully',
                'setting' => $setting,
            ], 201);
        } catch (\Exception $e) {
            Log::error("Create my-class setting error: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to create my-class setting: ' . $e->getMessage(),
                'errors' => $e->errors ?? [],
            ], 422);
        }
    }

    public function show($id): View
    {
        $pagetitle = "Class Overview";
        $class = ClassTeacher::where('classteacher.id', $id)
            ->leftJoin('users', 'users.id', '=', 'classteacher.staffid')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'classteacher.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'classteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'classteacher.sessionid')
            ->first([
                'classteacher.id as id',
                'users.id as userid',
                'users.name as staffname',
                'schoolclass.schoolclass as schoolclass',
                'classteacher.termid as termid',
                'classteacher.sessionid as sessionid',
                'schoolarm.arm as schoolarm',
                'schoolclass.description as classcategory',
                'schoolterm.term as term',
                'schoolsession.session as session',
                'classteacher.updated_at as updated_at',
                'schoolclass.id as schoolclassID'
            ]);

        return view('myclass.show', compact('class', 'pagetitle'));
    }

    public function edit($id): JsonResponse
    {
        try {
            $setting = Staffclasssetting::findOrFail($id);
            return response()->json([
                'success' => true,
                'setting' => $setting,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Fetch class setting error for ID {$id}: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch class setting: ' . $e->getMessage(),
            ], 404);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        Log::debug("Updating class setting ID: {$id}", $request->all());

        try {
            $validated = $request->validate([
                'staffid' => 'required|exists:users,id',
                'vschoolclassid' => 'required|exists:schoolclass,id',
                'termid' => 'required|exists:schoolterm,id',
                'sessionid' => 'required|exists:schoolsession,id',
                'noschoolopened' => 'nullable|integer|min:0',
                'termends' => 'nullable|date',
                'nexttermbegins' => 'nullable|date',
            ]);

            $setting = Staffclasssetting::findOrFail($id);

            // Check for existing setting (excluding current ID)
            $check = Staffclasssetting::where('staffid', $validated['staffid'])
                ->where('vschoolclassid', $validated['vschoolclassid'])
                ->where('termid', $validated['termid'])
                ->where('sessionid', $validated['sessionid'])
                ->where('id', '!=', $id)
                ->exists();

            if ($check) {
                return response()->json([
                    'success' => false,
                    'message' => 'Class setting already exists for this term and session.',
                ], 422);
            }

            $setting->update($validated);

            Log::debug("Class setting ID: {$id} updated successfully");
            return response()->json([
                'success' => true,
                'message' => 'Class setting updated successfully',
                'setting' => $setting,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Update my-class setting error for ID {$id}: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to update my-class setting: ' . $e->getMessage(),
                'errors' => $e->errors ?? [],
            ], 422);
        }
    }

    public function destroy($id): JsonResponse
    {
        Log::debug("Attempting to delete my-class setting ID: {$id}");
        try {
            $setting = Staffclasssetting::findOrFail($id);
            $setting->delete();

            Log::debug("Class setting ID: {$id} deleted successfully");
            return response()->json([
                'success' => true,
                'message' => 'Class setting deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error("Delete my-class setting error for ID {$id}: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete my-class setting: ' . $e->getMessage(),
            ], 500);
        }
    }
}