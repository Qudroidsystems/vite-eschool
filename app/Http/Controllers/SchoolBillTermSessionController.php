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
    public function __construct()
    {
        $this->middleware('permission:View schoolbill|Create schoolbill|Update schoolbill|Delete schoolbill', ['only' => ['index', 'store']]);
        $this->middleware('permission:Create schoolbill', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update schoolbill', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete schoolbill', ['only' => ['destroy', 'deleteschoolbilltermsession']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pagetitle = "School Bill Term Session Management";

        $terms = Schoolterm::all();
        $sessions = Schoolsession::all();
        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id as id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
            ->orderBy('schoolclass')
            ->get();
        $schoolbills = SchoolBillModel::all();

        $schoolbillclasstermsessions = SchoolBillTermSession::leftJoin('school_bill', 'school_bill.id', '=', 'school_bill_class_term_session.bill_id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'school_bill_class_term_session.class_id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'school_bill_class_term_session.termid_id')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'school_bill_class_term_session.session_id')
            ->leftJoin('users', 'users.id', '=', 'school_bill_class_term_session.createdBy')
            ->select([
                'school_bill_class_term_session.id as id',
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.arm as schoolarm',
                'schoolterm.term as schoolterm',
                'schoolsession.session as schoolsession',
                'users.name as createdBy',
                'school_bill.title as schoolbill',
                'school_bill_class_term_session.updated_at as updated_at'
            ])
            ->paginate(100); // Paginate with 10 records per page

        return view('schoolbilltermsession.index')
            ->with('schoolbills', $schoolbills)
            ->with('schoolclasses', $schoolclasses)
            ->with('terms', $terms)
            ->with('schoolsessions', $sessions)
            ->with('schoolbillclasstermsessions', $schoolbillclasstermsessions)
            ->with('pagetitle', $pagetitle);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('schoolbilltermsession.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bill_id' => 'required|exists:school_bill,id',
            'class_id' => 'required|exists:schoolclass,id',
            'termid_id' => 'required|exists:schoolterm,id',
            'session_id' => 'required|exists:schoolsession,id',
            'bill_id|class_id|termid_id|session_id' => 'unique:school_bill_class_term_session,bill_id,NULL,id,class_id,$request->class_id,termid_id,$request->termid_id,session_id,$request->session_id'
        ], [
            'bill_id.required' => 'Please select a school bill!',
            'bill_id.exists' => 'Selected school bill does not exist!',
            'class_id.required' => 'Please select a class!',
            'class_id.exists' => 'Selected class does not exist!',
            'termid_id.required' => 'Please select a term!',
            'termid_id.exists' => 'Selected term does not exist!',
            'session_id.required' => 'Please select a session!',
            'session_id.exists' => 'Selected session does not exist!',
            'bill_id_class_id_termid_id_session_id.unique' => 'This combination of bill, class, term, and session already exists!'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $schoolbillclasstermsessions = SchoolBillTermSession::create([
            'bill_id' => $request->bill_id,
            'class_id' => $request->class_id,
            'termid_id' => $request->termid_id,
            'session_id' => $request->session_id,
            'createdBy' => auth()->id() // Use authenticated user's ID
        ]);

        return response()->json([
            'success' => true,
            'message' => 'School Bill Term Session created successfully!',
            'data' => $schoolbillclasstermsessions
        ], 201);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $schoolbillclasstermsessions = SchoolBillTermSession::where('school_bill_class_term_session.id', $id)
            ->leftJoin('school_bill', 'school_bill.id', '=', 'school_bill_class_term_session.bill_id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'school_bill_class_term_session.class_id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'school_bill_class_term_session.termid_id')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'school_bill_class_term_session.session_id')
            ->leftJoin('users', 'users.id', '=', 'school_bill_class_term_session.createdBy')
            ->select([
                'school_bill_class_term_session.id as id',
                'school_bill_class_term_session.bill_id as bill_id',
                'school_bill_class_term_session.class_id as class_id',
                'school_bill_class_term_session.termid_id as termid_id',
                'school_bill_class_term_session.session_id as session_id',
                'schoolclass.schoolclass as schoolclass',
                'schoolclass.id as schoolclassid',
                'schoolarm.arm as schoolarm',
                'schoolterm.term as schoolterm',
                'schoolterm.id as schooltermid',
                'schoolsession.id as schoolsessionid',
                'schoolsession.session as schoolsession',
                'users.name as createdBy',
                'school_bill.title as schoolbill',
                'school_bill.id as schoolbill_id',
                'school_bill_class_term_session.updated_at as updated_at'
            ])
            ->first();

        if (!$schoolbillclasstermsessions) {
            return redirect()->route('schoolbilltermsession.index')->with('danger', 'School Bill Term Session not found.');
        }

        $terms = Schoolterm::all();
        $sessions = Schoolsession::all();
        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id as id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
            ->orderBy('schoolclass')
            ->get();
        $schoolbills = SchoolBillModel::all();

        return view('schoolbilltermsession.edit')
            ->with('schoolbills', $schoolbills)
            ->with('sclasses', $schoolclasses)
            ->with('schoolterms', $terms)
            ->with('schoolsessions', $sessions)
            ->with('schoolbillclasstermsessions', $schoolbillclasstermsessions);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $schoolbillclasstermsessions = SchoolBillTermSession::find($id);
        if (!$schoolbillclasstermsessions) {
            return response()->json([
                'success' => false,
                'message' => 'School Bill Term Session not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'bill_id' => 'required|exists:school_bill,id',
            'class_id' => 'required|exists:schoolclass,id',
            'termid_id' => 'required|exists:schoolterm,id',
            'session_id' => 'required|exists:schoolsession,id',
            'bill_id|class_id|termid_id|session_id' => 'unique:school_bill_class_term_session,bill_id,' . $id . ',id,class_id,$request->class_id,termid_id,$request->termid_id,session_id,$request->session_id'
        ], [
            'bill_id.required' => 'Please select a school bill!',
            'bill_id.exists' => 'Selected school bill does not exist!',
            'class_id.required' => 'Please select a class!',
            'class_id.exists' => 'Selected class does not exist!',
            'termid_id.required' => 'Please select a term!',
            'termid_id.exists' => 'Selected term does not exist!',
            'session_id.required' => 'Please select a session!',
            'session_id.exists' => 'Selected session does not exist!',
            'bill_id_class_id_termid_id_session_id.unique' => 'This combination of bill, class, term, and session already exists!'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $schoolbillclasstermsessions->update([
            'bill_id' => $request->bill_id,
            'class_id' => $request->class_id,
            'termid_id' => $request->termid_id,
            'session_id' => $request->session_id,
            'createdBy' => auth()->id() // Update with current user's ID
        ]);

        return response()->json([
            'success' => true,
            'message' => 'School Bill Term Session updated successfully!',
            'data' => $schoolbillclasstermsessions
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $schoolbillclasstermsessions = SchoolBillTermSession::find($id);
        if (!$schoolbillclasstermsessions) {
            return response()->json([
                'success' => false,
                'message' => 'School Bill Term Session not found.'
            ], 404);
        }

        $schoolbillclasstermsessions->delete();

        return response()->json([
            'success' => true,
            'message' => 'School Bill Term Session deleted successfully.'
        ], 200);
    }

    /**
     * Custom delete method for AJAX.
     */
    public function deleteschoolbilltermsession(Request $request)
    {
        return $this->destroy($request->schoolbilltermsessionid);
    }
}