<?php

namespace App\Http\Controllers;


use App\Models\Schoolterm;
use Illuminate\Http\Request;
use App\Models\SubjectTeacher;

class MyresultroomController extends Controller
{

    function __construct()
    {
        //  $this->middleware('permission:myresultroom-list|myresultroom-create|myresultroom-edit|myresultroom-delete', ['only' => ['index','store','term']]);
        //  $this->middleware('permission:myresultroom-create', ['only' => ['create','store']]);
        //  $this->middleware('permission:myresultroom-edit', ['only' => ['edit','update']]);
        //  $this->middleware('permission:myresultroom-delete', ['only' => ['destroy']]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //

    }

    public function term(){

        $terms = Schoolterm::all();

        return view('myresultroom.term')->with('terms',$terms);;

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
        $user = auth()->user();
        $current = "Current";

                $mysubjects = SubjectTeacher::where('subjectteacher.staffid', $user->id)
                    ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
                    ->leftJoin('subjectclass', 'subjectclass.subjectteacherid', '=', 'subjectteacher.id')
                    ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
                    ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                    ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                    ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                    ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
                    ->where('schoolsession.status', $current)
                    ->where('schoolterm.id', $request->termid)
                    ->whereNotNull('subjectclass.id') // Ensures subjectteacher.id exists in subjectclass
                    ->orderBy('schoolclass.schoolclass')
                    ->orderBy('schoolarm.arm')
                    ->get([
                        'subjectteacher.id as id',
                        'users.id as userid',
                        'users.name as staffname',
                        'subject.subject as subject',
                        'subject.subject_code as subjectcode',
                        'schoolterm.id as termid',
                        'subjectclass.id as subjectclassid',
                        'schoolclass.id as schoolclassid',
                        'subjectteacher.sessionid as sessionid',
                        'schoolclass.schoolclass as schoolclass',
                        'schoolarm.arm as arm',
                        'schoolterm.term as term',
                        'schoolsession.session as session',
                    ]);




            $mysubjectshistory = Subjectteacher::where('staffid', $user->id)
                ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
                ->get([
                    'subjectteacher.id as id',
                    'users.id as userid',
                    'users.name as staffname',
                    'subject.subject as subject',
                    'subject.subject_code as subjectcode',
                    'subjectteacher.sessionid as sessionid',
                    'schoolsession.session as session',
                ])
                ->sortBy('session');


         return view('myresultroom.index')->with('mysubjects',$mysubjects)
                                       ->with('mysubjectshistory',$mysubjectshistory);
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
