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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $pagetitle = "Subject Teacher Management";

        $schoolterms = Schoolterm::all();
        $schoolsessions = Schoolsession::all();
        $subjects = Subject::all();
        $staffs = User::whereHas('roles', function ($q) {
            $q->where('name', '!=', 'Student');
        })->get(['users.id as userid', 'users.name as name', 'users.avatar as avatar']);

        $subjectteachers = SubjectTeacher::leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
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
            ->paginate(10); // Paginate 10 items per page

        if ($request->ajax()) {
            return response()->json([
                'html' => view('subjectteacher.index', compact('subjectteachers', 'schoolterms', 'schoolsessions', 'subjects', 'staffs', 'pagetitle'))->render(),
                'count' => $subjectteachers->count(),
                'total' => $subjectteachers->total(),
            ]);
        }

        return view('subjectteacher.index')
            ->with('subjectteacher', $subjectteachers)
            ->with('terms', $schoolterms)
            ->with('schoolsessions', $schoolsessions)
            ->with('staffs', $staffs)
            ->with('subjects', $subjects)
            ->with('pagetitle', $pagetitle);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $schoolterms = Schoolterm::all();
        $schoolsessions = Schoolsession::all();
        $subjects = Subject::all();
        $staffs = User::whereHas('roles', function ($q) {
            $q->where('name', '!=', 'Student');
        })->get(['users.id as userid', 'users.name as name']);

        return view('subjectteacher.create')
            ->with('terms', $schoolterms)
            ->with('schoolsessions', $schoolsessions)
            ->with('staffs', $staffs)
            ->with('subjects', $subjects);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staffid' => 'required|exists:users,id',
            'subjectid' => 'required|exists:subject,id',
            'termid' => 'required|exists:schoolterm,id',
            'sessionid' => 'required|exists:schoolsession,id',
        ], [
            'staffid.required' => 'Please select a subject teacher!',
            'staffid.exists' => 'Selected teacher does not exist!',
            'subjectid.required' => 'Please select a subject!',
            'subjectid.exists' => 'Selected subject does not exist!',
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

        $exists = SubjectTeacher::where('staffid', $request->input('staffid'))
            ->where('subjectid', $request->input('subjectid'))
            ->where('termid', $request->input('termid'))
            ->where('sessionid', $request->input('sessionid'))
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This teacher is already assigned to the selected subject, term, and session.'
            ], 422);
        }

        $subjectteacher = SubjectTeacher::create([
            'staffid' => $request->input('staffid'),
            'subjectid' => $request->input('subjectid'),
            'termid' => $request->input('termid'),
            'sessionid' => $request->input('sessionid'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subject Teacher added successfully.',
            'data' => $subjectteacher
        ], 201);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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

        $schoolterms = Schoolterm::all();
        $schoolsessions = Schoolsession::all();
        $subjects = Subject::all();
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'staffid' => 'required|exists:users,id',
            'subjectid' => 'required|exists:subject,id',
            'termid' => 'required|exists:schoolterm,id',
            'sessionid' => 'required|exists:schoolsession,id',
        ], [
            'staffid.required' => 'Please select a subject teacher!',
            'staffid.exists' => 'Selected teacher does not exist!',
            'subjectid.required' => 'Please select a subject!',
            'subjectid.exists' => 'Selected subject does not exist!',
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

        $exists = SubjectTeacher::where('staffid', $request->input('staffid'))
            ->where('subjectid', $request->input('subjectid'))
            ->where('termid', $request->input('termid'))
            ->where('sessionid', $request->input('sessionid'))
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This teacher is already assigned to the selected subject, term, and session.'
            ], 422);
        }

        $subjectteacher = SubjectTeacher::find($id);
        if (!$subjectteacher) {
            return response()->json([
                'success' => false,
                'message' => 'Subject Teacher not found.'
            ], 404);
        }

        $subjectteacher->update([
            'staffid' => $request->input('staffid'),
            'subjectid' => $request->input('subjectid'),
            'termid' => $request->input('termid'),
            'sessionid' => $request->input('sessionid'),
        ]);

        // Update related records
        $sub = SubjectTeacher::where('subjectteacher.id', $id)
            ->leftJoin('subjectclass', 'subjectclass.subjectteacherid', '=', 'subjectteacher.id')
            ->leftJoin('broadsheet', 'broadsheet.subjectclassid', '=', 'subjectclass.id')
            ->get(['broadsheet.staffid as bstaffid', 'broadsheet.subjectclassid as subclass', 'broadsheet.termid as term', 'broadsheet.session as session']);

        foreach ($sub as $value) {
            Broadsheet::where('subjectclassid', $value->subclass)
                ->where('termid', $value->term)
                ->where('session', $value->session)
                ->update(['staffid' => $request->input('staffid')]);

            SubjectRegistrationStatus::where('subjectclassid', $value->subclass)
                ->where('termid', $value->term)
                ->where('sessionid', $value->session)
                ->update(['staffid' => $request->input('staffid')]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Subject Teacher updated successfully.',
            'data' => $subjectteacher
        ], 200);
    }

    /**
     * Legacy update method for non-AJAX requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatesubjectteacher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staffid' => 'required|exists:users,id',
            'subjectid' => 'required|exists:subject,id',
            'termid' => 'required|exists:schoolterm,id',
            'sessionid' => 'required|exists:schoolsession,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $exists = SubjectTeacher::where('staffid', $request->input('staffid'))
            ->where('subjectid', $request->input('subjectid'))
            ->where('termid', $request->input('termid'))
            ->where('sessionid', $request->input('sessionid'))
            ->where('id', '!=', $request->id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('danger', 'This teacher is already assigned to the selected subject, term, and session.');
        }

        $subjectteacher = SubjectTeacher::find($request->id);
        if (!$subjectteacher) {
            return redirect()->back()->with('danger', 'Subject Teacher not found.');
        }

        $subjectteacher->update([
            'staffid' => $request->input('staffid'),
            'subjectid' => $request->input('subjectid'),
            'termid' => $request->input('termid'),
            'sessionid' => $request->input('sessionid'),
        ]);

        return redirect()->route('subjectteacher.index')->with('success', 'Subject Teacher updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Handle AJAX delete request for subject teacher.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
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
}