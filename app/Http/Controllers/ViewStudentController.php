<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Studentclass;
use App\Models\Schoolclass;



class ViewStudentController extends Controller
{

    function __construct()
    {
         $this->middleware('permission:view-student-list', ['only' => ['index','store']]);

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        echo "show me";

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
    public function show($id,$termid,$sessionid)
    {
        //dd([$id,$termid,$sessionid]);

       $allstudents = Studentclass::where('schoolclassid', $id)
       ->where('termid',$termid)
       ->where('sessionid',$sessionid)
       ->leftJoin('studentRegistration', 'studentRegistration.id','=','studentclass.studentId')
       ->leftJoin('studentpicture','studentpicture.studentid','=','studentRegistration.id')
       ->get(['studentRegistration.admissionNo as admissionno','studentRegistration.firstname as firstname',
              'studentRegistration.lastname as lastname','studentRegistration.id as stid',
              'studentRegistration.othername as othername','studentRegistration.gender as gender','studentpicture.picture as picture']);

        $studentcount = Studentclass::where('schoolclassid', $id)
        ->where('sessionid',$sessionid)
        ->where('termid',$termid) ->count();

        $male = Studentclass::where('schoolclassid', $id)
        ->leftJoin('studentRegistration', 'studentRegistration.id','=','studentclass.studentId')
        ->where('sessionid',$sessionid)
        ->where('termid',$termid)
        ->where('gender',"Male")->count();


        $female = Studentclass::where('schoolclassid', $id)
        ->leftJoin('studentRegistration', 'studentRegistration.id','=','studentclass.studentId')
        ->where('sessionid',$sessionid)
        ->where('termid',$termid)
        ->where('gender',"Female")->count();


        $session = Schoolsession::where('id',$sessionid)->get();
        $term = Schoolterm::where('id',$termid)->get();
        $schoolclass = Schoolclass::where('schoolclass.id',$id)
        ->leftJoin('schoolarm','schoolarm.id','=','schoolclass.arm')
        ->get(['schoolclass.schoolclass as schoolclass','schoolarm.arm as arm']);
        return view('viewstudents.index')
                                        ->with('allstudents',$allstudents)
                                        ->with('term',$term)
                                        ->with('session',$session)
                                        ->with('termid',$termid)
                                        ->with('sessionid',$sessionid)
                                        ->with('schoolclass',$schoolclass)
                                        ->with('schoolclassid',$id)
                                        ->with('studentcount',$studentcount)
                                        ->with('male',$male)
                                        ->with('female',$female);


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
