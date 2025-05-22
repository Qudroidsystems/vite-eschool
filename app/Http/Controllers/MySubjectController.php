<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subjectteacher;

class MySubjectController extends Controller
{

    function __construct()
    {
         $this->middleware('permission:mysubject-list', ['only' => ['index','store']]);
        // $this->middleware('permission:add-school-class', ['only' => ['create','store']]);
        // $this->middleware('permission:edit-school-class', ['only' => ['edit','update']]);
        // $this->middleware('permission:delete-school-class', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //

        $user = auth()->user();
        $current = "Current";

       $mysubjects = Subjectteacher::where('staffid',$user->id)
       ->leftJoin('users', 'users.id','=','subjectteacher.staffid')
       ->leftJoin('subjectclass', 'subjectclass.subjectteacherid','=','subjectteacher.id')
       ->leftJoin('schoolclass', 'schoolclass.id','=','subjectclass.schoolclassid')
       ->leftJoin('schoolarm', 'schoolarm.id','=','schoolclass.arm')
       ->leftJoin('subject', 'subject.id','=','subjectteacher.subjectid')
       ->leftJoin('schoolterm', 'schoolterm.id','=','subjectteacher.termid')
       ->leftJoin('schoolsession', 'schoolsession.id','=','subjectteacher.sessionid')
       ->where('schoolsession.status','=',$current)
       ->get(['subjectteacher.id as id','users.id as userid','users.name as staffname',
            'subject.subject as subject','subject.subject_code as subjectcode','schoolclass.schoolclass as schoolclass','schoolarm.arm as arm',
            'subjectteacher.termid as termid','subjectteacher.sessionid as sessionid',
          'schoolterm.term as term','schoolsession.session as session'])->sortBy('subject');

          $mysubjectshistory = Subjectteacher::where('staffid',$user->id)
          ->leftJoin('users', 'users.id','=','subjectteacher.staffid')
          ->leftJoin('subjectclass', 'subjectclass.subjectteacherid','=','subjectteacher.id')
          ->leftJoin('subject', 'subject.id','=','subjectteacher.subjectid')
          ->leftJoin('schoolterm', 'schoolterm.id','=','subjectteacher.termid')
          ->leftJoin('schoolsession', 'schoolsession.id','=','subjectteacher.sessionid')
          ->leftJoin('schoolclass', 'schoolclass.id','=','subjectclass.schoolclassid')
          ->leftJoin('schoolarm', 'schoolarm.id','=','schoolclass.arm')
          ->get(['subjectteacher.id as id','users.id as userid','users.name as staffname',
               'subject.subject as subject','subject.subject_code as subjectcode',
               'subjectteacher.termid as termid','schoolclass.schoolclass as schoolclass','schoolarm.arm as arm',
               'subjectteacher.sessionid as sessionid',
             'schoolterm.term as term','schoolsession.session as session'])->sortBy('session');

         return view('mysubject.index')->with('mysubjects',$mysubjects)
                                       ->with('mysubjectshistory',$mysubjectshistory);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
