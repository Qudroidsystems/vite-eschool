<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ClassTeacher;
use App\Models\Schoolclass;
use App\Models\User;
use App\Models\Schoolterm;
use App\Models\Schoolsession;

class ClassTeacherController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View class-teacher|Create class-teacher|Update class-teacher|Delete class-teacher', ['only' => ['index', 'store']]);
        $this->middleware('permission:Create class-teacher', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update class-teacher', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete class-teacher', ['only' => ['destroy', 'deleteclassteacher']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        $pagetitle = "Class Teacher Management";

        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id as id', 'schoolarm.arm as schoolarm', 'schoolclass.schoolclass as schoolclass']);

        $subjectteachers = User::whereHas('roles', function ($q) {
            $q->where('name', '!=', 'Student');
        })->get(['users.id as userid', 'users.name as name']);

        $schoolterm = Schoolterm::all();
        $schoolsession = Schoolsession::all();

        $classteachers = ClassTeacher::leftJoin('users', 'users.id', '=', 'classteacher.staffid')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'classteacher.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'classteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'classteacher.sessionid')
            ->select([
                'classteacher.id as id',
                'users.id as userid',
                'users.name as staffname',
                'users.avatar as avatar',
                'schoolclass.id as schoolclassid',
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.id as schoolarmid',
                'schoolarm.arm as schoolarm',
                'schoolterm.id as termid',
                'schoolterm.term as term',
                'schoolsession.id as sessionid',
                'schoolsession.session as session',
                'classteacher.updated_at as updated_at'
            ])
            ->orderBy('staffname')
            ->paginate(10); // Paginate 10 items per page

        if ($request->ajax()) {
            return response()->json([
                'html' => view('classteacher.index', compact('classteachers', 'schoolclass', 'subjectteachers', 'schoolterm', 'schoolsession', 'pagetitle'))->render(),
                'count' => $classteachers->count(),
                'total' => $classteachers->total(),
            ]);
        }

        return view('classteacher.index')
            ->with('classteachers', $classteachers)
            ->with('schoolclass', $schoolclass)
            ->with('subjectteachers', $subjectteachers)
            ->with('schoolterms', $schoolterm)
            ->with('schoolsessions', $schoolsession)
            ->with('pagetitle', $pagetitle);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id as id', 'schoolarm.arm as schoolarm', 'schoolclass.schoolclass as schoolclass']);

        $subjectteachers = User::whereHas('roles', function ($q) {
            $q->where('name', '!=', 'Student');
        })->get(['users.id as userid', 'users.name as name']);

        $schoolterm = Schoolterm::all();
        $schoolsession = Schoolsession::all();

        return view('classteacher.create')
            ->with('schoolclass', $schoolclass)
            ->with('subjectteachers', $subjectteachers)
            ->with('schoolterms', $schoolterm)
            ->with('schoolsessions', $schoolsession);
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
            'schoolclassid' => 'required|exists:schoolclass,id',
            'termid' => 'required|exists:schoolterm,id',
            'sessionid' => 'required|exists:schoolsession,id',
        ], [
            'staffid.required' => 'Please select a teacher!',
            'staffid.exists' => 'Selected teacher does not exist!',
            'schoolclassid.required' => 'Please select a class!',
            'schoolclassid.exists' => 'Selected class does not exist!',
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

        $exists = ClassTeacher::where('staffid', $request->input('staffid'))
            ->where('schoolclassid', $request->input('schoolclassid'))
            ->where('termid', $request->input('termid'))
            ->where('sessionid', $request->input('sessionid'))
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This teacher is already assigned to the selected class, term, and session.'
            ], 422);
        }

        $classteacher = ClassTeacher::create([
            'staffid' => $request->input('staffid'),
            'schoolclassid' => $request->input('schoolclassid'),
            'termid' => $request->input('termid'),
            'sessionid' => $request->input('sessionid'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Class Teacher added successfully.',
            'data' => $classteacher
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
        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id as id', 'schoolarm.arm as schoolarm', 'schoolclass.schoolclass as schoolclass']);

        $subjectteachers = User::whereHas('roles', function ($q) {
            $q->where('name', '!=', 'Student');
        })->get(['users.id as userid', 'users.name as name']);

        $schoolterm = Schoolterm::all();
        $schoolsession = Schoolsession::all();

        $classteacher = ClassTeacher::where('classteacher.id', $id)
            ->leftJoin('users', 'users.id', '=', 'classteacher.staffid')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'classteacher.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'classteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'classteacher.sessionid')
            ->first([
                'classteacher.id as ctid',
                'users.id as userid',
                'users.name as staffname',
                'schoolclass.id as classid',
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.id as schoolarmid',
                'schoolarm.arm as schoolarm',
                'schoolterm.id as termid',
                'schoolterm.term as term',
                'schoolsession.id as sessionid',
                'schoolsession.session as session',
                'classteacher.updated_at as updated_at'
            ]);

        if (!$classteacher) {
            return redirect()->route('classteacher.index')->with('danger', 'Class Teacher not found.');
        }

        return view('classteacher.edit')
            ->with('classteachers', collect([$classteacher]))
            ->with('teachers', $subjectteachers)
            ->with('schoolclasses', $schoolclass)
            ->with('schoolterms', $schoolterm)
            ->with('schoolsessions', $schoolsession);
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
            'schoolclassid' => 'required|exists:schoolclass,id',
            'termid' => 'required|exists:schoolterm,id',
            'sessionid' => 'required|exists:schoolsession,id',
        ], [
            'staffid.required' => 'Please select a teacher!',
            'staffid.exists' => 'Selected teacher does not exist!',
            'schoolclassid.required' => 'Please select a class!',
            'schoolclassid.exists' => 'Selected class does not exist!',
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

        $exists = ClassTeacher::where('staffid', $request->input('staffid'))
            ->where('schoolclassid', $request->input('schoolclassid'))
            ->where('termid', $request->input('termid'))
            ->where('sessionid', $request->input('sessionid'))
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This teacher is already assigned to the selected class, term, and session.'
            ], 422);
        }

        $classteacher = ClassTeacher::find($id);
        if (!$classteacher) {
            return response()->json([
                'success' => false,
                'message' => 'Class Teacher not found.'
            ], 404);
        }

        $classteacher->update([
            'staffid' => $request->input('staffid'),
            'schoolclassid' => $request->input('schoolclassid'),
            'termid' => $request->input('termid'),
            'sessionid' => $request->input('sessionid'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Class Teacher updated successfully.',
            'data' => $classteacher
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $classteacher = ClassTeacher::find($id);
        if (!$classteacher) {
            return response()->json([
                'success' => false,
                'message' => 'Class Teacher not found.'
            ], 404);
        }

        $classteacher->delete();

        return response()->json([
            'success' => true,
            'message' => 'Class Teacher deleted successfully.'
        ], 200);
    }

    /**
     * Handle AJAX delete request for class teacher.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteclassteacher(Request $request)
    {
        $classteacher = ClassTeacher::find($request->classteacherid);
        if (!$classteacher) {
            return response()->json([
                'success' => false,
                'message' => 'Class Teacher not found.'
            ], 404);
        }

        $classteacher->delete();

        return response()->json([
            'success' => true,
            'message' => 'Class Teacher has been removed.'
        ], 200);
    }
}