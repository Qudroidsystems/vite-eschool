<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\Studentpersonalityprofile;
use Schoolclass;

class StudentpersonalityprofileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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

    public function studentpersonalityprofile ($id,$schoolclassid,$sessionid,$termid)
    {




       $students = Student::where('studentRegistration.id',$id)
        ->leftJoin('studentpicture','studentpicture.studentid','=','studentRegistration.id')
        ->get(['studentRegistration.id as id','studentRegistration.admissionNo as admissionNo',
             'studentRegistration.firstname as fname','studentRegistration.home_address as homeaddress',
             'studentRegistration.lastname as lastname','studentRegistration.dateofbirth as dateofbirth',
             'studentRegistration.gender as gender','studentRegistration.updated_at as updated_at',
             'studentpicture.picture as picture']);

             $studentpp = Studentpersonalityprofile::where('studentid',$id)
                                                ->where('schoolclassid',$schoolclassid)
                                                ->where('sessionid',$sessionid)
                                                ->where('termid',$termid)
                                                ->get();

        return view('studentpersonalityprofile.edit')->with('students',$students)
                                                    ->with('studentpp',$studentpp)
                                                    ->with('staffid',Auth::user()->id)
                                                    ->with('studentid',$id)
                                                    ->with('schoolclassid',$schoolclassid)
                                                    ->with('sessionid',$sessionid)
                                                    ->with('termid',$termid);
}


    public function save(Request $request)
    {
         $studentpp = Studentpersonalityprofile::where('studentid',$request->studentid)
                                                ->where('schoolclassid',$request->schoolclassid)
                                                ->where('termid',$request->termid)
                                                ->where('sessionid',$request->sessionid);

            if ($studentpp) {
                # code...

                $input = $request->all();

               $studentpp->update($input);

         return  redirect()->back()->with('success', 'Student Personality Profile Updated successfuly');

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
