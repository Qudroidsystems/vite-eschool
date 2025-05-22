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





    function __construct()
    {
        //ini_set('memory_limit','15024M');
         $this->middleware('permission:subject_teacher-list|subject_teacher-assign|subject_teacher-edit|subject_teacher-delete', ['only' => ['index','store']]);
         $this->middleware('permission:subject_teacher-assign', ['only' => ['create','store']]);
         $this->middleware('permission:subject_teacher-edit', ['only' => ['edit','update','updatesubjectteacher']]);
         $this->middleware('permission:subject_teacher-delete', ['only' => ['destroy','destroysubjectteacher']]);
    }




    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //

        $schoolterms = Schoolterm::all();
        $schoolsessions = Schoolsession::all();
        $subjects = Subject::all();
        $staffs = User::whereHas('roles', function($q){ $q->where('name', '!=','Student'); })
        ->get(['users.id as userid','users.name as name','users.avatar as avatar']);


        // $subjectteachers = SubjectTeacher::leftJoin('users', 'users.id','=','subjectteacher.staffid')
        // ->leftJoin('subject', 'subject.id','=','subjectteacher.subjectid')
        // ->leftJoin('schoolterm', 'schoolterm.id','=','subjectteacher.termid')
        // ->leftJoin('schoolsession', 'schoolsession.id','=','subjectteacher.sessionid')
        // ->get(['subjectteacher.id as id','users.id as userid','users.name as staffname','users.avatar as avatar','subject.subject as subjectname',
        // 'subject.subject_code as subjectcode',
        // 'schoolterm.term as termname','schoolsession.session as sessionname','subjectteacher.updated_at as date']);


        $subjectteachers = SubjectTeacher::leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
           //->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->selectRaw('subjectteacher.subjectid, subjectteacher.sessionid,
                        GROUP_CONCAT(DISTINCT users.id) as userids,
                        GROUP_CONCAT(DISTINCT users.name SEPARATOR ", ") as staffnames,
                        GROUP_CONCAT(DISTINCT users.avatar SEPARATOR ", ") as avatars,
                        subject.subject as subjectname, subject.subject_code as subjectcode,
                        schoolsession.session as sessionname,
                        COUNT(subjectteacher.id) as id,
                        MAX(subjectteacher.updated_at) as date')
            ->groupBy('subjectteacher.subjectid',
                        'subjectteacher.sessionid',
                        'subject.subject',
                        'subject.subject_code',
                        'schoolsession.session')
            ->get();


    return view('subjectteacher.index')->with('subjectteacher',$subjectteachers)
                    ->with('success', 'Welcome To Subject Teacher Management Account')
                    ->with('terms',$schoolterms)
                    ->with('schoolsessions',$schoolsessions)
                    ->with('staffs',$staffs)
                    ->with('subjects',$subjects);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $schoolterms = Schoolterm::all();
        $schoolsessions = Schoolsession::all();
        $subjects = Subject::all();
        $staffs = User::whereHas('roles', function($q){ $q->where('name', '!=','Student'); })
        ->get(['users.id as userid','users.name as name']);

        return view('subjectteacher.create')
                    ->with('terms',$schoolterms)
                    ->with('schoolsessions',$schoolsessions)
                    ->with('staffs',$staffs)
                    ->with('subjects',$subjects);
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
        echo $request->termid;

            $validator = Validator::make($request->all(), [
                'staffid' => 'required',
                'subjectid' => 'required',
                'termid' => 'required',
                'sessionid'=>'required',
            ],
            ['staffid.required'=>'Select a Subject Teacher!',
            'subjectid.required'=>'Select a subject !',
            'termid.required'=>'Select School Term',
            'session.required'=>'Select School Session!',
            ]
                );
                if ($validator->fails()) {

                    return redirect()->back()->withErrors($validator)
                                            ->withInput();

                }



            for ($i = 1; $i < 4; $i++) {
                // Check if the record already exists for this staff, subject, term, and session
                $subjectteachercheck = Subjectteacher::where('staffid', $request->staffid)
                    ->where('subjectid', $request->subjectid)
                    ->where('termid', $i) // Include the term in the check
                    ->where('sessionid', $request->sessionid)
                    ->exists();

                if ($subjectteachercheck) {
                    // Return an error if the record already exists for any term
                    return redirect()->back()->with('danger', "Ooops! Record already exists for term ID: $i!");
                }

                // Create a new SubjectTeacher instance if the record doesn't exist
                $subt = new SubjectTeacher();
                $subt->staffid = $request->staffid;
                $subt->subjectid = $request->subjectid;
                $subt->termid = $i; // Current term ID from the loop
                $subt->sessionid = $request->sessionid;
                $subt->save();



            }

            // Return a success message
            return redirect()->back()->with('success', 'Subject Teacher has been created successfully!');


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
        $subjectteachers = SubjectTeacher::where('subjectteacher.id',$id)
        ->leftJoin('users', 'users.id','=','subjectteacher.staffid')
        ->leftJoin('subject', 'subject.id','=','subjectteacher.subjectid')
        ->leftJoin('schoolterm', 'schoolterm.id','=','subjectteacher.termid')
        ->leftJoin('schoolsession', 'schoolsession.id','=','subjectteacher.sessionid')
        ->get(['subjectteacher.id as id','users.id as userid','users.name as staffname','subject.subject as subjectname',
        'schoolterm.term as termname','schoolsession.session as sessionname','subjectteacher.updated_at as editdate',
        'subjectteacher.created_at as date','subject.id as subid','schoolsession.id as sessionid','schoolterm.id as termid']);


        $schoolterms = Schoolterm::all();
        $schoolsessions = Schoolsession::all();
        $subjects = Subject::all();
        $staffs = User::whereHas('roles', function($q){ $q->where('name', '!=','Student'); })
        ->get(['users.id as userid','users.name as name']);



        return View('subjectteacher.edit')->with('subjectteachers',$subjectteachers)
                                          ->with('terms',$schoolterms)
                                          ->with('schoolsessions',$schoolsessions)
                                          ->with('subjects',$subjects)
                                          ->with('staffs',$staffs);
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


       $subjectteachercheck = Subjectteacher::where('staffid',$request->staffid)
        ->where('subjectid',$request->subjectid)
        ->where('termid',$request->termid)
        ->where('sessionid',$request->sessionid)
        ->exists();
            if ($subjectteachercheck){

            return redirect()->back()->with('danger', 'Ooops! Record already exist!');
            }
            else{

                $input = $request->all();
                $subjectteacher = Subjectteacher::find($id);
                $subjectteacher->update($input);



                $sub = Subjectteacher::where('subjectteacher.staffid',$request->staffid)
                ->where('subjectteacher.subjectid',$request->subjectid)
                ->where('subjectteacher.termid',$request->termid)
                ->where('subjectteacher.sessionid',$request->sessionid)
                ->leftJoin('subjectclass','subjectclass.subjectteacherid','=','subjectteacher.id')
                ->leftJoin('broadsheet','broadsheet.subjectclassid','=','subjectclass.id')
                ->get(['broadsheet.staffid as bstaffid','broadsheet.subjectclassid as subclass',
                'broadsheet.termid as term','broadsheet.session as session']);

                   foreach ($sub as $key => $value) {
                    //updating broadsheet...
                    $bupdate = Broadsheet::where('broadsheet.subjectclassid',$value->subclass)
                    ->where('broadsheet.termid',$value->term)
                    ->where('broadsheet.session',$value->session);
                    $bupdate->update(['staffid'=> $request->staffid]);
                    //  echo $value->bstaffid." ".$value->subclass."<br>";

                    //updating subject registration status...
                    $sub = SubjectRegistrationStatus::where('subjectclassid',$value->subclass)
                    ->where('termid',$value->term)
                    ->where('sessionid',$value->session);
                    $sub->update(['staffid'=> $request->staffid]);

                    //updating subject teacher...
                    $subt = Subjectteacher::where('staffid',$value->staffid)
                    ->where('termid',$value->term)
                    ->where('sessionid',$value->session);
                    $subt->update(['staffid'=> $request->staffid]);

                   }



          if($subjectteacher != null){

                return redirect()->route('subjectteacher.edit',[$id])
                ->with('success', 'Subject Teacher updated successfully.');

            }else{


                return redirect()->route('subjectteacher.edit',[$id])
                ->with('danger', 'Hi, It seems this record can not be updated.');
               }

            }

    }

    public function updatesubjectteacher(Request $request)
    {
        //


       $subjectteachercheck = Subjectteacher::where('staffid',$request->update_housemasterid)
        ->where('subjectid',$request->update_subjectid)
        ->where('termid',$request->update_termid)
        ->where('sessionid',$request->update_sessionid)
        ->exists();
            if ($subjectteachercheck){

            return redirect()->back()->with('danger', 'Ooops! Record already exist!');
            }
            else{

                $input = $request->all();
                $subjectteacher = Subjectteacher::find($request->id);
                $subjectteacher->update($input);



                $sub = Subjectteacher::where('subjectteacher.staffid',$request->update_housemasterid)
                ->where('subjectteacher.subjectid',$request->update_subjectid)
                ->where('subjectteacher.termid',$request->update_termid)
                ->where('subjectteacher.sessionid',$request->update_sessionid)
                ->leftJoin('subjectclass','subjectclass.subjectteacherid','=','subjectteacher.id')
                ->leftJoin('broadsheet','broadsheet.subjectclassid','=','subjectclass.id')
                ->get(['broadsheet.staffid as bstaffid','broadsheet.subjectclassid as subclass',
                'broadsheet.termid as term','broadsheet.session as session']);

                   foreach ($sub as $key => $value) {
                    //updating broadsheet...
                    $bupdate = Broadsheet::where('broadsheet.subjectclassid',$value->subclass)
                    ->where('broadsheet.termid',$value->term)
                    ->where('broadsheet.session',$value->session);
                    $bupdate->update(['staffid'=> $request->housemasterid]);
                    //  echo $value->bstaffid." ".$value->subclass."<br>";

                    //updating subject registration status...
                    $sub = SubjectRegistrationStatus::where('subjectclassid',$value->subclass)
                    ->where('termid',$value->term)
                    ->where('sessionid',$value->session);
                    $sub->update(['staffid'=> $request->housemasterid]);

                    //updating subject teacher...
                    $subt = Subjectteacher::where('staffid',$value->staffid)
                    ->where('termid',$value->term)
                    ->where('sessionid',$value->session);
                    $subt->update(['staffid'=> $request->housemasterid]);

                   }



          if($subjectteacher != null){

                return redirect()->route('subjectteacher.index',[$request->id])
                ->with('success', 'Subject Teacher updated successfully.');

            }else{


                return redirect()->route('subjectteacher.index',[$request->id])
                ->with('danger', 'Hi, It seems this record can not be updated.');
               }

            }

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
        Subjectteacher::find($id)->delete();

          // return redirect()->route('subjectteacher.index')
          // ->with('success', 'Subject Teacher deleted successfully.');
          return response()->json(['message' => $id]);
    }

    public function deletesubjectteacher(Request $request)
    {
        Subjectteacher::find($request->subjectteacherid)->delete();
        //check data deleted or not
        if ($request->subjectteacherid) {
            $success = true;
            $message = "Subject Teacher has been removed";
        } else {
            $success = true;
            $message = "Subject Teacher not found";
        }

        //  return response
        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);

    }
}
