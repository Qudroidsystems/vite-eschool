<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassTeacher;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Staffclasssetting;
use Illuminate\Support\Facades\Auth;

class MyClassController extends Controller
{

    function __construct()
    {
         $this->middleware('permission:myclass-list', ['only' => ['index','store']]);

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

       $myclass = ClassTeacher::where('staffid',$user->id)
       ->leftJoin('users', 'users.id','=','classteacher.staffid')
       ->leftJoin('schoolclass', 'schoolclass.id','=','classteacher.schoolclassid')
       ->leftJoin('schoolarm', 'schoolarm.id','=','schoolclass.arm')
       ->leftJoin('schoolterm', 'schoolterm.id','=','classteacher.termid')
       ->leftJoin('schoolsession', 'schoolsession.id','=','classteacher.sessionid')
       ->where('schoolsession.status','=',$current)
       ->get(['classteacher.id as id','users.id as userid','users.name as staffname',
            'schoolclass.schoolclass as schoolclass','classteacher.termid as termid','classteacher.sessionid as sessionid','schoolarm.arm as schoolarm',
           'schoolclass.description as classcategory','schoolterm.term as term',
           'schoolsession.session as session','classteacher.updated_at as updated_at','schoolclass.id as schoolclassID'])->sortBy('schoolclass');


    $myclasshistory = ClassTeacher::where('staffid',$user->id)
    ->leftJoin('users', 'users.id','=','classteacher.staffid')
    ->leftJoin('schoolclass', 'schoolclass.id','=','classteacher.schoolclassid')
    ->leftJoin('schoolterm', 'schoolterm.id','=','classteacher.termid')
    ->leftJoin('schoolsession', 'schoolsession.id','=','classteacher.sessionid')
    ->get(['classteacher.id as id','users.id as userid','users.name as staffname',
        'schoolclass.schoolclass as schoolclass','classteacher.termid as termid','classteacher.sessionid as sessionid','schoolclass.arm as schoolarm',
        'schoolclass.description as classcategory','schoolterm.term as term',
        'schoolsession.session as session','classteacher.updated_at as updated_at','schoolclass.id as schoolclassID'])->sortBy('session');

    $classsetting = Staffclasssetting::where('staffid',$user->id)
    ->leftJoin('users', 'users.id','=','staffclasssettings.staffid')
    ->leftJoin('schoolclass', 'schoolclass.id','=','staffclasssettings.vschoolclassid')
    ->leftJoin('schoolterm', 'schoolterm.id','=','staffclasssettings.termid')
    ->leftJoin('schoolsession', 'schoolsession.id','=','staffclasssettings.sessionid')
    ->get(['users.id as userid','users.name as staffname','staffclasssettings.id as id',
         'staffclasssettings.created_at as created_at', 'schoolclass.id as schoolclassid',
         'staffclasssettings.noschoolopened as noschoolopened','staffclasssettings.termends as termends',
         'staffclasssettings.nexttermbegins as nexttermbegins','schoolterm.term as term',
         'schoolsession.session as session',]);
    $myclasses = ClassTeacher::where('staffid',$user->id)
         ->leftJoin('users', 'users.id','=','classteacher.staffid')
         ->leftJoin('schoolclass', 'schoolclass.id','=','classteacher.schoolclassid')
         ->leftJoin('schoolterm', 'schoolterm.id','=','classteacher.termid')
         ->leftJoin('schoolsession', 'schoolsession.id','=','classteacher.sessionid')
         ->where('schoolsession.status','=',$current)
         ->get(['classteacher.id as id','users.id as userid','users.name as staffname',
              'schoolclass.schoolclass as schoolclass','classteacher.termid as termid','classteacher.sessionid as sessionid','schoolclass.arm as schoolarm',
             'schoolclass.description as classcategory','schoolterm.term as term',
             'schoolsession.session as session','classteacher.updated_at as updated_at','schoolclass.id as schoolclassID'])->sortBy('schoolclass');

        $vschoolclassid  = "";
         foreach ($myclasshistory as $key => $value) {
          $vschoolclassid =  $value->schoolclassID;
         }

    $terms = Schoolterm::all();
    $schoolsessions = Schoolsession::where('status',"Current")->get();




        return view('myclass.index')->with('sfid', $user->id)
                                     ->with('myclass',$myclass)
                                     ->with('myclasshistory',$myclasshistory)
                                     ->with('classsetting',$classsetting)
                                     ->with('myclasses', $myclasses)
                                     ->with('terms',$terms)
                                     ->with('schoolsessions',$schoolsessions);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function create()
    {


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {



        $check = Staffclasssetting::where('staffid',$request->staffid)
          ->where('vschoolclassid',$request->vschoolclassid)
          ->where('termid',$request->termid)
          ->where('sessionid',$request->sessionid)
        ->exists();

        if($check) {
          return redirect()->route('myclass.index')
            ->with('danger', 'You have already made Class Settings for this term and session.
            To make a new one, you must delete the previous setting');

        }else{
           $input = $request->all();
          Staffclasssetting::create($input);
            return redirect()->route('myclass.index')
            ->with('success', 'Your Class Settings have been Stored.');


        }


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
