<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Schoolclass;
use App\Models\Subjectclass;
use App\Models\Broadsheet;
use App\Models\SubjectRegistrationStatus;
use App\Models\SubjectTeacher;
use App\Models\Schoolterm;
use App\Models\Schoolsession;

class SubjectClassController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View subject-class|Create subject-class|Update subject-class|Delete subject-class', ['only' => ['index', 'store']]);
        $this->middleware('permission:Create subject-class', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update subject-class', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete subject-class', ['only' => ['destroy', 'deletesubjectclass']]);
    }

    public function index()
    {
        $pagetitle = "Subject Class Management";

        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id as id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
            ->sortBy('schoolclass');

        $subjectteachers = SubjectTeacher::leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->get([
                'subjectteacher.id as id',
                'subjectteacher.staffid as subtid',
                'subjectteacher.subjectid as subid',
                'subject.id as subjectid',
                'subject.subject as subject',
                'subject.subject_code as subjectcode',
                'users.name as teachername',
                'schoolterm.id as termid',
                'schoolterm.term as termname',
                'schoolsession.id as sessionid',
                'schoolsession.session as sessionname'
            ])
            ->sortBy('subject');

        $subjectclasses = Subjectclass::leftJoin('schoolclass', 'subjectclass.schoolclassid', '=', 'schoolclass.id')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get([
                'subjectclass.id as scid',
                'schoolclass.id as schoolclassid',
                'schoolclass.schoolclass as sclass',
                'schoolarm.arm as schoolarm',
                'subjectteacher.id as subteacherid',
                'subjectteacher.staffid as subtid',
                'subjectteacher.subjectid as subid',
                'subject.id as subjectid',
                'subject.subject as subjectname',
                'subject.subject_code as subjectcode',
                'users.name as teachername',
                'users.avatar as picture',
                'schoolterm.id as termid',
                'schoolterm.term as termname',
                'schoolsession.id as sessionid',
                'schoolsession.session as sessionname',
                'subjectclass.updated_at'
            ])
            ->sortBy('sclass');

        return view('subjectclass.index')
            ->with('subjectclasses', $subjectclasses)
            ->with('schoolclasses', $schoolclasses)
            ->with('subjectteacher', $subjectteachers)
            ->with('pagetitle', $pagetitle);
    }

    public function create()
    {
        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id as id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
            ->sortBy('schoolclass');

        $subjectteachers = SubjectTeacher::leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->get([
                'subjectteacher.id as id',
                'subjectteacher.staffid as subtid',
                'subjectteacher.subjectid as subid',
                'subject.id as subjectid',
                'subject.subject as subject',
                'subject.subject_code as subjectcode',
                'users.name as teachername',
                'schoolterm.id as termid',
                'schoolterm.term as termname',
                'schoolsession.id as sessionid',
                'schoolsession.session as sessionname'
            ])
            ->sortBy('subject');

        return view('subjectclass.create')
            ->with('schoolclasses', $schoolclasses)
            ->with('subjectteacher', $subjectteachers);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schoolclassid' => 'required|exists:schoolclass,id',
            'subjectteacherid' => 'required|exists:subjectteacher,id',
        ], [
            'schoolclassid.required' => 'Please select a class!',
            'schoolclassid.exists' => 'Selected class does not exist!',
            'subjectteacherid.required' => 'Please select a subject teacher!',
            'subjectteacherid.exists' => 'Selected subject teacher does not exist!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $subjectTeacher = SubjectTeacher::find($request->input('subjectteacherid'));
        if (!$subjectTeacher) {
            return response()->json([
                'success' => false,
                'message' => 'Subject teacher not found.'
            ], 404);
        }

        $exists = Subjectclass::where('schoolclassid', $request->input('schoolclassid'))
            ->where('subjectteacherid', $request->input('subjectteacherid'))
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This subject teacher is already assigned to the selected class.'
            ], 422);
        }

        $subjectclass = Subjectclass::create([
            'schoolclassid' => $request->input('schoolclassid'),
            'subjectteacherid' => $request->input('subjectteacherid'),
            'subjectid' => $subjectTeacher->subjectid,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subject Class added successfully.',
            'data' => $subjectclass
        ], 201);
    }

    public function edit($id)
    {
        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id as id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
            ->sortBy('schoolclass');

        $subjectteachers = SubjectTeacher::leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->get([
                'subjectteacher.id as id',
                'subjectteacher.staffid as subtid',
                'subjectteacher.subjectid as subid',
                'subject.id as subjectid',
                'subject.subject as subject',
                'subject.subject_code as subjectcode',
                'users.name as teachername',
                'schoolterm.id as termid',
                'schoolterm.term as termname',
                'schoolsession.id as sessionid',
                'schoolsession.session as sessionname'
            ])
            ->sortBy('subject');

        $subjectclasses = Subjectclass::where('subjectclass.id', $id)
            ->leftJoin('schoolclass', 'subjectclass.schoolclassid', '=', 'schoolclass.id')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->first([
                'subjectclass.id as scid',
                'schoolclass.id as schoolclassid',
                'schoolclass.schoolclass as sclass',
                'schoolarm.arm as schoolarm',
                'subjectteacher.id as subteacherid',
                'subjectteacher.staffid as subtid',
                'subjectteacher.subjectid as subid',
                'subject.id as subjectid',
                'subject.subject as subjectname',
                'subject.subject_code as subjectcode',
                'users.name as teachername',
                'users.avatar as picture',
                'schoolterm.id as termid',
                'schoolterm.term as termname',
                'schoolsession.id as sessionid',
                'schoolsession.session as sessionname',
                'subjectclass.updated_at'
            ]);

        if (!$subjectclasses) {
            return redirect()->route('subjectclass.index')->with('danger', 'Subject Class not found.');
        }

        return view('subjectclass.edit')
            ->with('subjectclasses', collect([$subjectclasses]))
            ->with('schoolclasses', $schoolclasses)
            ->with('subjectteachers', $subjectteachers);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'schoolclassid' => 'required|exists:schoolclass,id',
            'subjectteacherid' => 'required|exists:subjectteacher,id',
        ], [
            'schoolclassid.required' => 'Please select a class!',
            'schoolclassid.exists' => 'Selected class does not exist!',
            'subjectteacherid.required' => 'Please select a subject teacher!',
            'subjectteacherid.exists' => 'Selected subject teacher does not exist!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $subjectTeacher = SubjectTeacher::find($request->input('subjectteacherid'));
        if (!$subjectTeacher) {
            return response()->json([
                'success' => false,
                'message' => 'Subject teacher not found.'
            ], 404);
        }

        $exists = Subjectclass::where('schoolclassid', $request->input('schoolclassid'))
            ->where('subjectteacherid', $request->input('subjectteacherid'))
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This subject teacher is already assigned to the selected class.'
            ], 422);
        }

        $subjectclass = Subjectclass::find($id);
        if (!$subjectclass) {
            return response()->json([
                'success' => false,
                'message' => 'Subject Class not found.'
            ], 404);
        }

        $subjectclass->update([
            'schoolclassid' => $request->input('schoolclassid'),
            'subjectteacherid' => $request->input('subjectteacherid'),
            'subjectid' => $subjectTeacher->subjectid,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subject Class updated successfully.',
            'data' => $subjectclass
        ], 200);
    }

    public function destroy($id)
    {
        $subjectclass = Subjectclass::find($id);
        if (!$subjectclass) {
            return response()->json([
                'success' => false,
                'message' => 'Subject Class not found.'
            ], 404);
        }

        Broadsheet::where('subjectclassid', $id)->delete();
        SubjectRegistrationStatus::where('subjectclassid', $id)->delete();
        $subjectclass->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subject Class deleted successfully.'
        ], 200);
    }

    public function deletesubjectclass(Request $request)
    {
        $subjectclass = Subjectclass::find($request->subjectclassid);
        if (!$subjectclass) {
            return response()->json([
                'success' => false,
                'message' => 'Subject Class not found.'
            ], 404);
        }

        Broadsheet::where('subjectclassid', $request->subjectclassid)->delete();
        SubjectRegistrationStatus::where('subjectclassid', $request->subjectclassid)->delete();
        $subjectclass->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subject Class has been removed.'
        ], 200);
    }
}