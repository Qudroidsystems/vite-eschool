<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\SubjectTeacher;
use App\Models\Schoolterm;
use App\Models\Subject;
use App\Models\Schoolsession;
use App\Models\User;
use App\Models\Broadsheet;
use App\Models\Subjectclass;
use App\Models\SubjectRegistrationStatus;
use Illuminate\Support\Facades\Log;

class SubjectTeacherController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View subject-teacher|Create subject-teacher|Update subject-teacher|Delete subject-teacher', ['only' => ['index', 'store']]);
        $this->middleware('permission:Create subject-teacher', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update subject-teacher', ['only' => ['edit', 'update', 'updatesubjectteacher']]);
        $this->middleware('permission:Delete subject-teacher', ['only' => ['destroy', 'deletesubjectteacher']]);
    }

    public function index(Request $request)
    {
        $pagetitle = "Subject Teacher Management";

        $terms = Schoolterm::orderBy('term', 'asc')->get();
        $schoolsessions = Schoolsession::orderBy('session', 'asc')->get();
        $subjects = Subject::orderBy('subject', 'asc')->get();
        $staffs = User::whereHas('roles', function ($q) {
            $q->where('name', '!=', 'Student');
        })->get(['users.id as userid', 'users.name as name', 'users.avatar as avatar']);

        $subjectteacher = SubjectTeacher::leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->select([
                'subjectteacher.id as id',
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
                'subjectteacher.updated_at'
            ])
            ->orderBy('staffname')
            ->paginate(200);

        Log::info('SubjectTeacher Index Query', [
            'count' => $subjectteacher->count(),
            'total' => $subjectteacher->total(),
            'items' => $subjectteacher->items()
        ]);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('subjectteacher.index', compact('subjectteacher', 'terms', 'schoolsessions', 'subjects', 'staffs', 'pagetitle'))->render(),
                'count' => $subjectteacher->count(),
                'total' => $subjectteacher->total(),
            ]);
        }

        return view('subjectteacher.index')
            ->with('subjectteacher', $subjectteacher)
            ->with('terms', $terms)
            ->with('schoolsessions', $schoolsessions)
            ->with('staffs', $staffs)
            ->with('subjects', $subjects)
            ->with('pagetitle', $pagetitle);
    }

    public function create()
    {
        $schoolterms = Schoolterm::orderBy('term', 'asc')->get();
        $schoolsessions = Schoolsession::orderBy('session', 'asc')->get();
        $subjects = Subject::orderBy('subject', 'asc')->get();
        $staffs = User::whereHas('roles', function ($q) {
            $q->where('name', '!=', 'Student');
        })->get(['users.id as userid', 'users.name as name']);

        return view('subjectteacher.create')
            ->with('terms', $schoolterms)
            ->with('schoolsessions', $schoolsessions)
            ->with('staffs', $staffs)
            ->with('subjects', $subjects);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staffid' => 'required|exists:users,id',
            'subjectids' => 'required|array|min:1',
            'subjectids.*' => 'exists:subject,id',
            'termid' => 'required|array|min:1',
            'termid.*' => 'exists:schoolterm,id',
            'sessionid' => 'required|exists:schoolsession,id',
        ], [
            'staffid.required' => 'Please select a teacher.',
            'staffid.exists' => 'Selected teacher does not exist.',
            'subjectids.required' => 'Please select at least one subject.',
            'subjectids.array' => 'Subjects must be an array.',
            'subjectids.min' => 'Please select at least one subject.',
            'subjectids.*.exists' => 'One or more selected subjects do not exist.',
            'termid.required' => 'Please select at least one term.',
            'termid.array' => 'Terms must be an array.',
            'termid.min' => 'Please select at least one term.',
            'termid.*.exists' => 'One or more selected terms do not exist.',
            'sessionid.required' => 'Please select a session.',
            'sessionid.exists' => 'Selected session does not exist.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please select a teacher, at least one subject, at least one term, and a session.',
                'errors' => $validator->errors()
            ], 422);
        }

        $staffid = $request->input('staffid');
        $subjectids = $request->input('subjectids');
        $termids = $request->input('termid');
        $sessionid = $request->input('sessionid');

        $existing = SubjectTeacher::where('staffid', $staffid)
            ->whereIn('subjectid', $subjectids)
            ->whereIn('termid', $termids)
            ->where('sessionid', $sessionid)
            ->pluck('subjectid')
            ->toArray();

        if (!empty($existing)) {
            $existingSubjects = Subject::whereIn('id', $existing)->pluck('subject')->toArray();
            return response()->json([
                'success' => false,
                'message' => 'The teacher is already assigned to: ' . implode(', ', $existingSubjects) . ' for one or more selected terms and session.'
            ], 422);
        }

        $createdRecords = [];
        foreach ($termids as $termid) {
            foreach ($subjectids as $subjectid) {
                $subjectteacher = SubjectTeacher::create([
                    'staffid' => $staffid,
                    'subjectid' => $subjectid,
                    'termid' => $termid,
                    'sessionid' => $sessionid,
                ]);
                $createdRecords[] = $subjectteacher;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Subject Teacher(s) added successfully.',
            'data' => $createdRecords
        ], 201);
    }

    public function edit($id)
    {
        $subjectteachers = SubjectTeacher::where('subjectteacher.id', $id)
            ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->first([
                'subjectteacher.id as id',
                'users.id as userid',
                'users.name as staffname',
                'users.avatar as avatar',
                'subject.id as subid',
                'subject.subject as subjectname',
                'schoolterm.id as termid',
                'schoolterm.term as termname',
                'schoolsession.id as sessionid',
                'schoolsession.session as sessionname',
                'subjectteacher.updated_at as editdate',
                'subjectteacher.created_at as date'
            ]);

        if (!$subjectteachers) {
            return redirect()->route('subjectteacher.index')->with('danger', 'Subject Teacher not found.');
        }

        $schoolterms = Schoolterm::orderBy('term', 'asc')->get();
        $schoolsessions = Schoolsession::orderBy('session', 'asc')->get();
        $subjects = Subject::orderBy('subject', 'asc')->get();
        $staffs = User::whereHas('roles', function ($q) {
            $q->where('name', '!=', 'Student');
        })->get(['users.id as userid', 'users.name as name']);

        return view('subjectteacher.edit')
            ->with('subjectteachers', collect([$subjectteachers]))
            ->with('terms', $schoolterms)
            ->with('schoolsessions', $schoolsessions)
            ->with('staffs', $staffs)
            ->with('subjects', $subjects);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'staffid' => 'required|exists:users,id',
            'subjectids' => 'required|array|min:1',
            'subjectids.*' => 'exists:subject,id',
            'termid' => 'required|array|min:1',
            'termid.*' => 'exists:schoolterm,id',
            'sessionid' => 'required|exists:schoolsession,id',
            'subjects_to_remove' => 'nullable|array',
            'subjects_to_remove.*' => 'exists:subject,id',
        ], [
            'staffid.required' => 'Please select a teacher.',
            'staffid.exists' => 'Selected teacher does not exist.',
            'subjectids.required' => 'Please select at least one subject.',
            'subjectids.array' => 'Subjects must be an array.',
            'subjectids.min' => 'Please select at least one subject.',
            'subjectids.*.exists' => 'One or more selected subjects do not exist.',
            'termid.required' => 'Please select at least one term.',
            'termid.array' => 'Terms must be an array.',
            'termid.min' => 'Please select at least one term.',
            'termid.*.exists' => 'One or more selected terms do not exist.',
            'sessionid.required' => 'Please select a session.',
            'sessionid.exists' => 'Selected session does not exist.',
            'subjects_to_remove.*.exists' => 'One or more subjects to remove do not exist.',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please select a teacher, at least one subject, at least one term, and a session.',
                'errors' => $validator->errors()
            ], 422);
        }
    
        $staffid = $request->input('staffid');
        $subjectids = $request->input('subjectids');
        $termids = $request->input('termid');
        $sessionid = $request->input('sessionid');
        $subjectsToRemove = $request->input('subjects_to_remove', []);
    
        // Check for conflicts excluding the current record
        $existingConflicts = SubjectTeacher::where('staffid', $staffid)
            ->whereIn('subjectid', $subjectids)
            ->whereIn('termid', $termids)
            ->where('sessionid', $sessionid)
            ->where('id', '!=', $id)
            ->pluck('subjectid')
            ->toArray();
    
        if (!empty($existingConflicts)) {
            $existingSubjects = Subject::whereIn('id', $existingConflicts)->pluck('subject')->toArray();
            return response()->json([
                'success' => false,
                'message' => 'The teacher is already assigned to: ' . implode(', ', $existingSubjects) . ' for one or more selected terms and session.'
            ], 422);
        }
    
        // Remove subjects for specified terms if provided
        if (!empty($subjectsToRemove)) {
            SubjectTeacher::where('staffid', $staffid)
                ->whereIn('subjectid', $subjectsToRemove)
                ->whereIn('termid', $termids)
                ->where('sessionid', $sessionid)
                ->delete();
        }
    
        $updatedRecords = [];
        foreach ($termids as $termid) {
            foreach ($subjectids as $subjectid) {
                $subjectteacher = SubjectTeacher::updateOrCreate(
                    [
                        'staffid' => $staffid,
                        'subjectid' => $subjectid,
                        'termid' => $termid,
                        'sessionid' => $sessionid,
                    ],
                    [
                        'staffid' => $staffid,
                        'subjectid' => $subjectid,
                        'termid' => $termid,
                        'sessionid' => $sessionid,
                    ]
                );
                $updatedRecords[] = $subjectteacher;
            }
        }
    
        // Update related records in Broadsheet and SubjectRegistrationStatus
        $sub = SubjectTeacher::whereIn('subjectteacher.id', array_column($updatedRecords, 'id'))
            ->leftJoin('subjectclass', 'subjectclass.subjectteacherid', '=', 'subjectteacher.id')
            ->leftJoin('broadsheet', 'broadsheet.subjectclassid', '=', 'subjectclass.id')
            ->get(['broadsheet.staffid as bstaffid', 'broadsheet.subjectclassid as subclass', 'broadsheet.termid as term', 'broadsheet.session_id as session']);
    
        foreach ($sub as $value) {
            // Update Broadsheet with the correct session_id column
            Broadsheet::where('subjectclassid', $value->subclass)
                ->where('termid', $value->term)
                ->where('session_id', $value->session) // Changed from sessionid to session_id
                ->update(['staffid' => $staffid]);
    
            // Update SubjectRegistrationStatus with the correct session_id column
            SubjectRegistrationStatus::where('subjectclassid', $value->subclass)
                ->where('termid', $value->term)
                ->where('session_id', $value->session) // Changed from sessionid to session_id
                ->update(['staffid' => $staffid]);
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Subject Teacher(s) updated successfully.',
            'data' => $updatedRecords
        ], 200);
    }

    public function updatesubjectteacher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staffid' => 'required|exists:users,id',
            'subjectids' => 'required|array|min:1',
            'subjectids.*' => 'exists:subject,id',
            'termid' => 'required|array|min:1',
            'termid.*' => 'exists:schoolterm,id',
            'sessionid' => 'required|exists:schoolsession,id',
            'subjects_to_remove' => 'nullable|array',
            'subjects_to_remove.*' => 'exists:subject,id',
        ], [
            'staffid.required' => 'Please select a teacher.',
            'staffid.exists' => 'Selected teacher does not exist.',
            'subjectids.required' => 'Please select at least one subject.',
            'subjectids.array' => 'Subjects must be an array.',
            'subjectids.min' => 'Please select at least one subject.',
            'subjectids.*.exists' => 'One or more selected subjects do not exist.',
            'termid.required' => 'Please select at least one term.',
            'termid.array' => 'Terms must be an array.',
            'termid.min' => 'Please select at least one term.',
            'termid.*.exists' => 'One or more selected terms do not exist.',
            'sessionid.required' => 'Please select a session.',
            'sessionid.exists' => 'Selected session does not exist.',
            'subjects_to_remove.*.exists' => 'One or more subjects to remove do not exist.',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        $staffid = $request->input('staffid');
        $subjectids = $request->input('subjectids');
        $termids = $request->input('termid');
        $sessionid = $request->input('sessionid');
        $subjectsToRemove = $request->input('subjects_to_remove', []);
    
        $existing = SubjectTeacher::where('staffid', $staffid)
            ->whereIn('subjectid', $subjectids)
            ->whereIn('termid', $termids)
            ->where('sessionid', $sessionid)
            ->exists();
    
        if ($existing) {
            return redirect()->back()->with('danger', 'This teacher is already assigned to one or more selected subjects for one or more selected terms and session.');
        }
    
        if (!empty($subjectsToRemove)) {
            SubjectTeacher::where('staffid', $staffid)
                ->whereIn('subjectid', $subjectsToRemove)
                ->whereIn('termid', $termids)
                ->where('sessionid', $sessionid)
                ->delete();
        }
    
        foreach ($termids as $termid) {
            foreach ($subjectids as $subjectid) {
                SubjectTeacher::updateOrCreate(
                    [
                        'staffid' => $staffid,
                        'subjectid' => $subjectid,
                        'termid' => $termid,
                        'sessionid' => $sessionid,
                    ],
                    [
                        'staffid' => $staffid,
                        'subjectid' => $subjectid,
                        'termid' => $termid,
                        'sessionid' => $sessionid,
                    ]
                );
            }
        }
    
        return redirect()->route('subjectteacher.index')->with('success', 'Subject Teacher updated successfully.');
    }

    public function destroy($id)
    {
        $subjectteacher = SubjectTeacher::find($id);
        if (!$subjectteacher) {
            return response()->json([
                'success' => false,
                'message' => 'Subject Teacher not found.'
            ], 404);
        }

        $subjectteacher->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subject Teacher deleted successfully.'
        ], 200);
    }

    public function deletesubjectteacher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subjectteacherid' => 'required|exists:subjectteacher,id',
        ]);

        if ($validator->fails()) {
            Log::error('Delete Subject Teacher Validation Failed', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid subject teacher ID.'
            ], 422);
        }

        $subjectteacher = SubjectTeacher::find($request->subjectteacherid);
        if (!$subjectteacher) {
            Log::error('Subject Teacher not found', ['subjectteacherid' => $request->subjectteacherid]);
            return response()->json([
                'success' => false,
                'message' => 'Subject Teacher not found.'
            ], 404);
        }

        $subjectteacher->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subject Teacher has been removed.'
        ], 200);
    }


    public function getSubjects($id)
    {
        \Log::info('getSubjects called', ['id' => $id]);

        $subjectteacher = SubjectTeacher::where('id', $id)->first();
        if (!$subjectteacher) {
            \Log::error('Subject Teacher not found', ['id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Subject Teacher not found.'
            ], 404);
        }

        // Fetch subjects and terms for the same staff, subject, and session
        $subjectTeachers = SubjectTeacher::where('staffid', $subjectteacher->staffid)
            ->where('subjectid', $subjectteacher->subjectid)
            ->where('sessionid', $subjectteacher->sessionid)
            ->select('subjectid', 'termid')
            ->get();

        \Log::info('Subject Teachers fetched', [
            'staffid' => $subjectteacher->staffid,
            'subjectid' => $subjectteacher->subjectid,
            'sessionid' => $subjectteacher->sessionid,
            'count' => $subjectTeachers->count()
        ]);

        $subjectIds = $subjectTeachers->pluck('subjectid')->unique()->toArray();
        $termIds = $subjectTeachers->pluck('termid')->unique()->toArray();

        \Log::info('Subject and Term IDs', [
            'subjectIds' => $subjectIds,
            'termIds' => $termIds
        ]);

        return response()->json([
            'success' => true,
            'staffid' => $subjectteacher->staffid,
            'termIds' => $termIds ?: [],
            'sessionid' => $subjectteacher->sessionid,
            'subjectIds' => $subjectIds ?: []
        ], 200);
    }

}