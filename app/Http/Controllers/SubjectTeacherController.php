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
            'termid' => 'required|exists:schoolterm,id',
            'sessionid' => 'required|exists:schoolsession,id',
        ], [
            'staffid.required' => 'Please select a subject teacher!',
            'staffid.exists' => 'Selected teacher does not exist!',
            'subjectids.required' => 'Please select at least one subject!',
            'subjectids.array' => 'Subjects must be an array!',
            'subjectids.min' => 'Please select at least one subject!',
            'subjectids.*.exists' => 'One or more selected subjects do not exist!',
            'termid.required' => 'Please select a term!',
            'termid.exists' => 'Selected term does not exist!',
            'sessionid.required' => 'Please select a session!',
            'sessionid.exists' => 'Selected session does not exist!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $staffid = $request->input('staffid');
        $subjectids = $request->input('subjectids');
        $termid = $request->input('termid');
        $sessionid = $request->input('sessionid');

        // Check for existing assignments
        $existing = SubjectTeacher::where('staffid', $staffid)
            ->whereIn('subjectid', $subjectids)
            ->where('termid', $termid)
            ->where('sessionid', $sessionid)
            ->pluck('subjectid')
            ->toArray();

        if (!empty($existing)) {
            $existingSubjects = Subject::whereIn('id', $existing)->pluck('subject')->toArray();
            return response()->json([
                'success' => false,
                'message' => 'The teacher is already assigned to: ' . implode(', ', $existingSubjects) . ' for this term and session.'
            ], 422);
        }

        // Create a record for each subject
        $createdRecords = [];
        foreach ($subjectids as $subjectid) {
            $subjectteacher = SubjectTeacher::create([
                'staffid' => $staffid,
                'subjectid' => $subjectid,
                'termid' => $termid,
                'sessionid' => $sessionid,
            ]);
            $createdRecords[] = $subjectteacher;
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
            'termid' => 'required|exists:schoolterm,id',
            'sessionid' => 'required|exists:schoolterm,id',
            'subjects_to_remove' => 'nullable|array',
            'subjects_to_remove.*' => 'exists:subject,id',
        ], [
            'staffid.required' => 'Please select a subject teacher!',
            'staffid.exists' => 'Selected teacher does not exist!',
            'subjectids.required' => 'Please select at least one subject!',
            'subjectids.array' => 'Subjects must be an array!',
            'subjectids.min' => 'Please select at least one subject!',
            'subjectids.*.exists' => 'One or more selected subjects do not exist!',
            'termid.required' => 'Please select a term!',
            'termid.exists' => 'Selected term does not exist!',
            'sessionid.required' => 'Please select a session!',
            'sessionid.exists' => 'Selected session does not exist!',
            'subjects_to_remove.*.exists' => 'One or more subjects to remove do not exist!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $staffid = $request->input('staffid');
        $subjectids = $request->input('subjectids');
        $termid = $request->input('termid');
        $sessionid = $request->input('sessionid');
        $subjectsToRemove = $request->input('subjects_to_remove', []);

        // Check for conflicting assignments excluding the current record
        $existingConflicts = SubjectTeacher::where('staffid', $staffid)
            ->whereIn('subjectid', $subjectids)
            ->where('termid', $termid)
            ->where('sessionid', $sessionid)
            ->where('id', '!=', $id)
            ->pluck('subjectid')
            ->toArray();

        if (!empty($existingConflicts)) {
            $existingSubjects = Subject::whereIn('id', $existingConflicts)->pluck('subject')->toArray();
            return response()->json([
                'success' => false,
                'message' => 'The teacher is already assigned to: ' . implode(', ', $existingSubjects) . ' for this term and session.'
            ], 422);
        }

        // Delete explicitly removed subjects
        if (!empty($subjectsToRemove)) {
            SubjectTeacher::where('staffid', $staffid)
                ->whereIn('subjectid', $subjectsToRemove)
                ->where('termid', $termid)
                ->where('sessionid', $sessionid)
                ->delete();
        }

        // Create or update records for selected subjects
        $updatedRecords = [];
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

        // Update related broadsheet and registration status
        $sub = SubjectTeacher::whereIn('subjectteacher.id', array_column($updatedRecords, 'subjectteacher.id'))
            ->leftJoin('subjectclass', 'subjectclass.subjectteacherid', '=', 'subjectteacher.id')
            ->leftJoin('broadsheet', 'broadsheet.subjectclassid', '=', 'subjectclass.id')
            ->get(['broadsheet.staffid as bstaffid', 'broadsheet.subjectclassid as subclass', 'broadsheet.termid as term', 'broadsheet.session as session']);

        foreach ($sub as $value) {
            Broadsheet::where('subjectclassid', $value->subclass)
                ->where('termid', $value->term)
                ->where('session', $value->session)
                ->update(['staffid' => $staffid]);

            SubjectRegistrationStatus::where('subjectclassid', $value->subclass)
                ->where('termid', $value->term)
                ->where('sessionid', $value->session)
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
            'termid' => 'required|exists:schoolterm,id',
            'sessionid' => 'required|exists:schoolsession,id',
            'subjects_to_remove' => 'nullable|array',
            'subjects_to_remove.*' => 'exists:subject,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $staffid = $request->input('staffid');
        $subjectids = $request->input('subjectids');
        $termid = $request->input('termid');
        $sessionid = $request->input('sessionid');
        $subjectsToRemove = $request->input('subjects_to_remove', []);

        // Check for conflicts
        $existing = SubjectTeacher::where('staffid', $staffid)
            ->whereIn('subjectid', $subjectids)
            ->where('termid', $termid)
            ->where('sessionid', $sessionid)
            ->where('id', '!=', $request->id)
            ->exists();

        if ($existing) {
            return redirect()->back()->with('danger', 'This teacher is already assigned to one or more selected subjects for this term and session.');
        }

        // Delete explicitly removed subjects
        if (!empty($subjectsToRemove)) {
            SubjectTeacher::where('staffid', $staffid)
                ->whereIn('subjectid', $subjectsToRemove)
                ->where('termid', $termid)
                ->where('sessionid', $sessionid)
                ->delete();
        }

        // Create or update selected subjects
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
        $subjectteacher = SubjectTeacher::find($request->subjectteacherid);
        if (!$subjectteacher) {
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
        $subjectteacher = SubjectTeacher::where('id', $id)->first();
        if (!$subjectteacher) {
            return response()->json([
                'success' => false,
                'message' => 'Subject Teacher not found.'
            ], 404);
        }

        $subjectIds = SubjectTeacher::where('staffid', $subjectteacher->staffid)
            ->where('termid', $subjectteacher->termid)
            ->where('sessionid', $subjectteacher->sessionid)
            ->pluck('subjectid')
            ->toArray();

        return response()->json([
            'success' => true,
            'subjectIds' => $subjectIds
        ], 200);
    }
}