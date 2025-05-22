<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Broadsheet;
use App\Models\Schoolterm;
use App\Models\Schoolclass;
use App\Models\Subjectclass;
use Illuminate\Http\Request;
use App\Models\Schoolsession;
use App\Models\Studentpicture;
use Illuminate\Support\Facades\DB;
use App\Models\StudentSubjectRecord;
use App\Models\SubjectRegistrationStatus;


class SubjectOperationController extends Controller
{



    function __construct()
    {
         $this->middleware('permission:subject_operation-list|subject_operation-create|subject_operation-edit|subject_operation-delete', ['only' => ['index','store']]);
         $this->middleware('permission:subject_operation-create', ['only' => ['create','store']]);
         $this->middleware('permission:subject_operation-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:subject_operation-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $student = Student::leftJoin('parentRegistration', 'parentRegistration.id','=','studentRegistration.id')
                         ->leftJoin('studentpicture','studentpicture.studentid','=','studentRegistration.id')
                         ->leftJoin('promotionStatus','promotionStatus.studentId','=','studentRegistration.id')
                        ->get(['studentRegistration.id as id','studentRegistration.admissionNo as admissionNo','studentRegistration.firstname as firstname',
                        'studentRegistration.lastname as lastname','studentRegistration.dateofbirth as dateofbirth','studentRegistration.gender as gender',
                        'studentRegistration.updated_at as updated_at','studentpicture.picture as picture','promotionStatus.studentId as studentID',
                        'promotionStatus.schoolclassid as schoolclassid','promotionStatus.termid as termid','promotionStatus.sessionid as sessionid',
                        'promotionStatus.promotionStatus as pstatus','promotionStatus.classstatus as cstatus']);



        $schoolclass = Schoolclass::all();
        $schoolterm = Schoolterm::all();
        $schoolsession = Schoolsession::all();

        return view('subjectoperation.index')->with('student',$student)
                                    ->with('schoolclass',$schoolclass)
                                    ->with('schoolterm',$schoolterm)
                                    ->with('schoolsession', $schoolsession);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function subjectinfo($id,$schoolclassid,$sessionid,$termid){
        $staffid="";
        $subjectclassid="";
        $current = "Current";

        $studentpic = Studentpicture::where(['studentid.picture as picture']);


        // $subjectclass = Subjectclass::where('schoolclassid', $schoolclassid)
        // ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
        // ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
        // ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
        // ->where('schoolsession.status', '=', $current)
        // ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
        // ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
        // ->leftJoin('staffbioinfo', 'staffbioinfo.userid', '=', 'users.id')
        // ->leftJoin('staffpicture', 'staffpicture.staffid', '=', 'users.id')
        // ->select(
        //     'subject.id as subjectid',
        //     'subject.subject as subject',
        //     DB::raw('COUNT(subject.id) as total_count'), // Count rows for each subjectid
        //     DB::raw('GROUP_CONCAT(DISTINCT users.id) as user_ids'), // Group staff IDs
        //     DB::raw('GROUP_CONCAT(DISTINCT users.name) as user_names'), // Group staff names
        //     DB::raw('MAX(users.avatar) as picture'), // Get any avatar (e.g., the latest one)
        //     DB::raw('MAX(subject.subject_code) as subjectcode'),
        //     DB::raw('MAX(schoolsession.session) as session'),
        //     DB::raw('MAX(schoolsession.id) as sessionid'),
        //     DB::raw('MAX(schoolterm.id) as termid'),
        //     DB::raw('MAX(schoolterm.term) as term'),
        //     DB::raw('MAX(users.id) as userid'),
        //     DB::raw('MAX(staffbioinfo.title) as title'),
        //     DB::raw('MAX(users.name) as name'),
        //     DB::raw('MAX(users.avatar) as picture'),
        //     DB::raw('MAX(subject.id) as subjectid'),
        //     DB::raw('MAX(subject.subject) as subject'),
        //     DB::raw('MAX(users.id) as staffid'),
        //     DB::raw('MAX(subject.subject_code) as subjectcode'),
        //     DB::raw('MAX(subjectclass.id) as subjectclassid'),
        //     DB::raw('MAX(schoolsession.session) as session'),
        //     DB::raw('MAX(schoolsession.id) as sessionid'),
        //     DB::raw('MAX(schoolterm.id) as termid'),
        //     DB::raw('MAX(schoolterm.term) as term')
        // )
        // ->groupBy('subject.id') // Group by subjectid to avoid duplicate counts
        // ->get();


        $subjectclass = Subjectclass::where('schoolclassid',$schoolclassid )

        ->leftJoin('subjectteacher','subjectteacher.id','=','subjectclass.subjectteacherid')
        ->leftJoin('subject','subject.id','=','subjectteacher.subjectid')
        ->leftJoin('schoolsession','schoolsession.id','=','subjectteacher.sessionid')
        ->where('schoolsession.status','=',$current)
        ->leftJoin('schoolterm','schoolterm.id','=','subjectteacher.termid')
        ->leftJoin('users','users.id','=','subjectteacher.staffid')
        ->leftJoin('staffbioinfo', 'staffbioinfo.userid','=','users.id')
        ->leftJoin('staffpicture', 'staffpicture.staffid','=','users.id')
        ->get(['users.id as userid','staffbioinfo.title as title','users.name as name','users.avatar as picture',
        'subject.id as subjectid','subject.subject as subject','users.id as staffid',
        'subject.subject_code as subjectcode','schoolterm.term as term','subjectclass.id as subjectclassid',
        'schoolsession.session as session', 'schoolsession.id as sessionid','schoolterm.id as termid']);

        // $totalreg = subjectclass::where('schoolclassid',$schoolclassid)
        // ->leftJoin('subjectteacher','subjectteacher.id','=','subjectclass.subjectteacherid')
        // ->leftJoin('subject','subject.id','=','subjectteacher.subjectid')
        // ->leftJoin('schoolsession','schoolsession.id','=','subjectteacher.sessionid')
        // ->leftJoin('schoolterm','schoolterm.id','=','subjectteacher.termid')
        // ->where('schoolsession.status','=',$current)->count();

        $totalreg = subjectclass::where('schoolclassid', $schoolclassid)
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                ->where('schoolsession.status', '=', $current)
                ->distinct('subjectteacher.subjectid') // Ensure only distinct subject IDs are counted
                ->count('subjectteacher.subjectid'); // Specify the column to count



        $reg = StudentSubjectRecord::where('student_subject_register_record.studentId', $id)
        ->leftJoin('subjectclass', 'subjectclass.id', '=', 'student_subject_register_record.subjectclassid')
        ->leftJoin('schoolsession', 'schoolsession.id', '=', 'student_subject_register_record.session')
        ->where('schoolsession.status', '=', $current)
        ->count();



         $noreg = $totalreg - $reg;



        $studentdata = Student::where('id',$id)->get();
        $classname = Schoolclass::where('schoolclass.id',$schoolclassid)
        ->leftJoin('schoolarm','schoolarm.id','=','schoolclass.arm')->get(['schoolclass.schoolclass as schoolclass','schoolarm.arm as arm']);
        //$schoolsession = schoolsession::where('id',$sessionid)->get();


        return view('subjectoperation.subjectinfo')
                     ->with('studentpic',$studentpic)
                     ->with('classname',$classname)
                     ->with('subjectclass',$subjectclass)
                    ->with('schoolterm',$termid)
                    ->with('schoolsession', $sessionid)
                    ->with('student', $id)
                    ->with('studentdata', $studentdata)
                    ->with('totalreg', $totalreg)
                    ->with('reg', $reg)
                    ->with('noreg', $noreg);


    }


    public function create(Request $request)
    {
        //
       echo  $request->input('sessionid');

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

      // echo  $request->input('subjectclassid');
        $broadsheetchk = Broadsheet::where('studentId',$request->input('studentid'))
                                    ->where('staffid',$request->input('staffid'))
                                    ->where('subjectclassid',$request->input('subjectclassid'))
                                    ->where('termid',$request->input('termid'))
                                    ->where('session',$request->input('sessionid'))
                                    ->exists();
        // for ($i = 1; $i < 4; $i++) {
        //     //broadsheet for first term



        try {
            DB::beginTransaction(); // Start transaction

            // Create Broadsheet entry
            $broadsheet = new Broadsheet();
            $broadsheet->studentId = $request->input('studentid');
            $broadsheet->staffid = $request->input('staffid');
            $broadsheet->subjectclassid = $request->input('subjectclassid');
            $broadsheet->termid = $request->input('termid');
            $broadsheet->session = $request->input('sessionid');
            $broadsheet->save();

            // Create Subject Registration Status entry
            $subjectregstatus = new SubjectRegistrationStatus();
            $subjectregstatus->broadsheetid = $broadsheet->id; // Link to Broadsheet
            $subjectregstatus->studentId = $request->input('studentid');
            $subjectregstatus->staffid = $request->input('staffid');
            $subjectregstatus->subjectclassid = $request->input('subjectclassid');
            $subjectregstatus->termid = $request->input('termid'); // Fixed the missing assignment
            $subjectregstatus->sessionid = $request->input('sessionid');
            $subjectregstatus->Status = 1;
            $subjectregstatus->save();

            // Create Student Subject Record entry
            $subject_record = new StudentSubjectRecord();
            $subject_record->studentId = $request->input('studentid');
            $subject_record->subjectclassid = $request->input('subjectclassid');
            $subject_record->staffid = $request->input('staffid');
            $subject_record->session = $request->input('sessionid');
            $subject_record->save();

            DB::commit(); // Commit transaction if everything is successful

            // return response()->json(['message' => 'Data saved successfully'], 200);
            return redirect()->back()->with('success', 'Subject has been registered.');

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            return response()->json(['error' => 'Failed to save data', 'message' => $e->getMessage()], 500);
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
    public function destroy(Request $request, $id)
    {
        //
        SubjectRegistrationStatus::where('broadsheetid',$id)->delete();
        Broadsheet::find($id)->delete();
        StudentSubjectRecord::where('subjectclassid',$request->subjectclassid)->delete();

        return redirect()->back()->with('danger', 'Subject has been uregistered successfully!.');
    }
}
