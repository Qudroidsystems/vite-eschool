<?php

namespace App\Http\Controllers;

use App\Models\Schoolterm;
use App\Models\Schoolclass;
use Illuminate\Http\Request;
use App\Models\Schoolsession;
use App\Models\SchoolBillModel;
use Illuminate\Support\Facades\DB;
use App\Models\SchoolBillTermSession;
use Illuminate\Support\Facades\Validator;


class SchoolBillTermSessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $terms = Schoolterm::all();
        $session = SchoolSession::all();
        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id','=','schoolclass.arm')
        ->get(['schoolclass.id as id','schoolclass.schoolclass as schoolclass','schoolarm.arm as arm'])
         ->sortBy('sdesc');
        $schoolbills = SchoolBillModel::all();

        $schoolbillclasstermsessions = SchoolBillTermSession::leftjoin('school_bill','school_bill.id','=','school_bill_class_term_session.bill_id')
        ->leftjoin('schoolclass','schoolclass.id','=','school_bill_class_term_session.class_id')
        ->leftjoin('schoolarm','schoolarm.id','=','schoolclass.arm')
        ->leftjoin('schoolterm','schoolterm.id','=','school_bill_class_term_session.termid_id')
        ->leftjoin('schoolsession','schoolsession.id','=','school_bill_class_term_session.session_id')
        ->leftjoin('users','users.id','=','school_bill_class_term_session.createdBy')
        ->get(['school_bill_class_term_session.id as id','schoolclass.schoolclass as schoolclass','schoolarm.arm as schoolarm','schoolterm.term as schoolterm',
               'schoolsession.session as schoolsession','users.name as createdBy','school_bill.title as schoolbill',
               'school_bill_class_term_session.updated_at as updated_at']);

         return view('schoolbilltermsession.index')->with('schoolbills',$schoolbills)
                                                    ->with('schoolclasses',$schoolclass)
                                                    ->with('terms',$terms)
                                                    ->with('schoolsessions',$session)
                                                    ->with('schoolbillclasstermsessions',$schoolbillclasstermsessions);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $schoolbillclasstermsessions = new SchoolBillTermSession();
        $validator = Validator::make($request->all(), [
            // 'bill_id' => 'required|min:1|unique:school_bill_class_term_session',
            // 'class_id' => 'required|min:1|unique:school_bill_class_term_session',
            // 'termid_id' =>'required|min:1|unique:school_bill_class_term_session',
            // 'session_id' =>'required|min:1|unique:school_bill_class_term_session',
        ] );

        if ($validator->fails()) {
            //$errors = $validator->errors();
          // return $errors->toJson();
           return redirect()->back()->withErrors($validator)
                                    ->withInput();

        } else{
        $schoolbillclasstermsessions->bill_id = $request->bill_id;
        $schoolbillclasstermsessions->class_id = $request->class_id;
        $schoolbillclasstermsessions->termid_id = $request->termid_id;
        $schoolbillclasstermsessions->session_id = $request->session_id;
        $schoolbillclasstermsessions->createdBy = $request->createdBy;
        $schoolbillclasstermsessions->save();
        if($schoolbillclasstermsessions != null){
            return redirect()->back()->with('status', 'School Bill assigned  Successfully!');
            }else{
               echo "something went wrong...";
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $terms = Schoolterm::all();
        $sessions = SchoolSession::all();
        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id','=','schoolclass.arm')
        ->get(['schoolclass.id as id','schoolclass.schoolclass as schoolclass','schoolarm.arm as arm'])
         ->sortBy('sdesc');
        $schoolbills = SchoolBillModel::all();

        $schoolbillclasstermsessions = SchoolBillTermSession::where('school_bill_class_term_session.id',$id)
         ->leftjoin('school_bill','school_bill.id','=','school_bill_class_term_session.bill_id')
        ->leftjoin('schoolclass','schoolclass.id','=','school_bill_class_term_session.class_id')
        ->leftjoin('schoolarm','schoolarm.id','=','schoolclass.arm')
        ->leftjoin('schoolterm','schoolterm.id','=','school_bill_class_term_session.termid_id')
        ->leftjoin('schoolsession','schoolsession.id','=','school_bill_class_term_session.session_id')
        ->leftjoin('users','users.id','=','school_bill_class_term_session.createdBy')
        ->get(['school_bill_class_term_session.id as id','schoolclass.schoolclass as schoolclass','schoolclass.id as schoolclassid','schoolarm.arm as schoolarm',
             'schoolterm.term as schoolterm', 'schoolterm.id as schooltermid','schoolsession.id as schoolsessionid',
               'schoolsession.session as schoolsession','users.name as createdBy','school_bill.title as schoolbill','school_bill.id as schoolbill_id',
               'school_bill_class_term_session.updated_at as updated_at']);

         return view('schoolbilltermsession.edit')->with('schoolbills',$schoolbills)
                                                    ->with('sclasses',$schoolclass)
                                                    ->with('schoolterms',$terms)
                                                    ->with('schoolsessions',$sessions)
                                                    ->with('schoolbillclasstermsessions',$schoolbillclasstermsessions);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $input = $request->all();
        //print_r($input);
        $schoolbillclasstermsession = schoolBillTermSession::find($id);
        $schoolbillclasstermsession ->update($input);
        return  redirect()->back()->with('success', 'School Bill updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function deleteschoolbilltermsession(Request $request)
    {

        print($request->schoolbilltermsessionid);
        $delete =  DB::table('school_bill_class_term_session')
        // ->leftJoin('student_bill_payment', 'student_bill_payment_record.student_bill_payment_id', '=', 'student_bill_payment.id')
        ->where('school_bill_class_term_session.id', $request->schoolbilltermsessionid)
        ->delete();
        //check data deleted or not
        if ($delete) {
            $success = true;
            $message = 'school bill for class, term and session been removed';
        } else {
            $success = false;
            $message = 'school bill for class , term and session not found';
        }

        //  return response
        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);

    }
}
