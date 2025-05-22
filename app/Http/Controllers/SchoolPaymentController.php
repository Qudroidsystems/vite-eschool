<?php

namespace App\Http\Controllers;

//use Barryvdh\DomPDF\Facade as PDF;
use PDF; // Add at the top with other use statements
use Illuminate\Support\Facades\View;
use App\Models\SchoolBillTermSession;
use App\Models\Schoolclass;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Student;
use App\Models\StudentBillInvoice;
use App\Models\StudentBillPayment;
use App\Models\StudentBillPaymentBook;
use App\Models\StudentBillPaymentRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class SchoolPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $student = Student::leftJoin('parentRegistration', 'parentRegistration.id', '=', 'studentRegistration.id')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('promotionStatus', 'promotionStatus.studentId', '=', 'studentRegistration.id')
            ->get(['studentRegistration.id as id', 'studentRegistration.admissionNo as admissionNo', 'studentRegistration.firstname as firstname',
                'studentRegistration.lastname as lastname', 'studentRegistration.dateofbirth as dateofbirth', 'studentRegistration.gender as gender',
                'studentRegistration.updated_at as updated_at', 'studentpicture.picture as picture', 'promotionStatus.studentId as studentID',
                'promotionStatus.schoolclassid as schoolclassid', 'promotionStatus.termid as termid', 'promotionStatus.sessionid as sessionid',
                'promotionStatus.promotionStatus as pstatus', 'promotionStatus.classstatus as cstatus']);

        $schoolclass = Schoolclass::all();
        $schoolterm = Schoolterm::all();
        $schoolsession = Schoolsession::all();

        return view('schoolpayment.index')->with('student', $student)
            ->with('schoolclass', $schoolclass)
            ->with('schoolterm', $schoolterm)
            ->with('schoolsession', $schoolsession);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {



            $formatteBillAmount = $request->actualAmount;
            $formattedPaymentAmount = $request->payment_amount;
            $formattedlastAmountPaid = $request->last_amount_paid;

            // Step 1: Remove the currency symbol (₦) and commas
            $plainNumberString = str_replace(['₦', ','], '', $formatteBillAmount);
            $plainNumberString2 = str_replace(['₦', ','], '', $formattedPaymentAmount);
            $plainNumberString3 = str_replace(['₦', ','], '', $formattedlastAmountPaid);
            // Step 2: Convert the string to a number
            $amount = floatval($plainNumberString);
            $paymentAmount = floatval($plainNumberString2);
            $lastPaidamount = floatval($plainNumberString3);
            $status = '';
            $balance = $request->balance2;
            // $balance = 0;
            $amountPaid = $paymentAmount;
            $total_payment = $lastPaidamount + $amountPaid;
            $newBalance = $amount - $total_payment;

            // echo 'Amount: '.$amount;
            // echo ' Amount Paid: '.$amountPaid;
            // echo ' Last Amount Paid: '.$lastPaidamount;
            // echo ' initial Balance : '.$balance;
            // echo ' New Balance: '.$newBalance;
            // echo ' Total amount paid : '.$total_payment;

            if ($total_payment < $amount) {
                $status = 'Uncompleted';
            } elseif ($total_payment == $amount) {
                $status = 'Completed';
            }


            DB::beginTransaction();

            try {
                // Store student bill payment
                $studentpaymentbill = StudentBillPayment::create([
                    'student_id' => $request->student_id,
                    'school_bill_id' => $request->school_bill_id,
                    'status' => $status,
                    'payment_method' => $request->payment_method2,
                    'class_id' => $request->class_id,
                    'termid_id' => $request->term_id,
                    'session_id' => $request->session_id,
                    'generated_by' => Auth::user()->id,
                    'delete_status' => '1',
                ]);

                // Store student bill payment record
                $studentBillPaymentRecord = StudentBillPaymentRecord::create([
                    'student_bill_payment_id' => $studentpaymentbill->id,
                    'total_bill' => $amount,
                    'amount_paid' => $amountPaid,
                    'last_payment' => $amountPaid,
                    'amount_owed' => $newBalance,
                    'complete_payment' => '',
                    'class_id' => $request->class_id,
                    'termid_id' => $request->term_id,
                    'session_id' => $request->session_id,
                    'generated_by' => Auth::user()->id,
                ]);

                // Commit the transaction
                DB::commit();

                // Optionally, you can return a success response
               // return response()->json(['message' => 'Payment processed successfully'], 200);
                return redirect()->back()->with('status', 'Payment Successful!');

            } catch (\Exception $e) {
                // Rollback the transaction in case of an error
                DB::rollBack();

                // Optionally, you can return an error response
                return response()->json(['message' => 'Payment processing failed', 'error' => $e->getMessage()], 500);
            }





    }




    public function termSession(string $id){

        $schoolterm = Schoolterm::all();
        $schoolsession = Schoolsession::all();


            return view('schoolpayment.termSession')->with('id',$id)
                                ->with('schoolterms', $schoolterm)
                                ->with('schoolsessions', $schoolsession);

    }

    public function termSessionPayments(Request $request){


        $schoolclassid = '';
        $termid = '';
        $sessionid = '';

            $student = Student::where('studentRegistration.id', $request->studentId)
                ->leftJoin('parentRegistration', 'parentRegistration.id', '=', 'studentRegistration.id')
                ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
                ->leftJoin('studentclass', 'studentclass.studentId', '=', 'studentRegistration.id')
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'studentclass.termid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
                // ->where('schoolterm.id', $request->termid) // Uncommented and added
                // ->where('schoolsession.id', $request->sessionid) // Uncommented and added
                ->where('schoolsession.status', 'Current') // Uncommented and added
                ->get([
                    'studentRegistration.id as id',
                    'studentRegistration.admissionNo as admissionNo',
                    'studentRegistration.firstname as firstname',
                    'studentRegistration.lastname as lastname',
                    'studentRegistration.dateofbirth as dateofbirth',
                    'studentRegistration.gender as gender',
                    'studentRegistration.updated_at as updated_at',
                    'studentpicture.picture as avatar',
                    'schoolclass.schoolclass as schoolclass',
                    'schoolarm.arm as arm',
                    'schoolterm.term as term',
                    'schoolsession.session as session',
                    'schoolclass.id as schoolclassid',
                    'schoolterm.id as termid',
                    'schoolsession.id as sessionid',
                    'studentRegistration.statusId as statusId'
                ]);


            foreach ($student as $value) {

                $schoolclassid = $value->schoolclassid;
                $termid = $value->termid;
                $sessionid = $value->sessionid;
            }

            $student_bill_info = SchoolBillTermSession::where('school_bill_class_term_session.class_id', $schoolclassid)
            ->where('school_bill_class_term_session.termid_id', $request->termid)
            ->where('school_bill_class_term_session.session_id', $request->sessionid)
            ->leftJoin('school_bill', 'school_bill.id', '=', 'school_bill_class_term_session.bill_id')
            ->leftJoin('student_status', 'student_status.id', '=', 'school_bill.statusId')
            ->where('student_status.id', 1)
            ->get(['school_bill_class_term_session.id as id',
                    'school_bill.id as schoolbillid',
                    'school_bill.title as title',
                    'school_bill.description as description',
                    'student_status.id as statusId',
                    'school_bill.bill_amount as amount']);

            // print_r($student_bill_info);

            $student_bill_info_count = SchoolBillTermSession::where('class_id', $schoolclassid)
            ->where('termid_id', $request->termid)
            ->where('session_id', $request->sessionid)
            ->leftJoin('school_bill', 'school_bill.id', '=', 'school_bill_class_term_session.bill_id')
            ->leftJoin('student_status', 'student_status.id', '=', 'school_bill.statusId')
            ->where('student_status.id', 1) // Use `where` instead of `whereIn` for a single value
            ->count();

             //student school bill payment record...
            $studentpaymentbill = StudentBillPayment::where('student_bill_payment.student_id', $request->studentId)
            ->where('student_bill_payment.class_id', $schoolclassid)
            ->where('student_bill_payment.termid_id', $request->termid)
            ->where('student_bill_payment.session_id', $request->sessionid)
            ->where('student_bill_payment.delete_status', '1')
            ->leftJoin('student_bill_payment_record', 'student_bill_payment_record.student_bill_payment_id', '=', 'student_bill_payment.id')
            ->leftJoin('school_bill', 'school_bill.id', '=', 'student_bill_payment.school_bill_id')
            ->leftJoin('users', 'users.id', '=', 'student_bill_payment.generated_by')
            ->get([
                'student_bill_payment.id as paymentid',
                'student_bill_payment.status as paymentStatus',
                'student_bill_payment.payment_method as paymentMethod',
                'users.name as recievedBy',
                'student_bill_payment.created_at as recievedDate',
                'school_bill.title as title',
                'school_bill.description as description',
                'school_bill.bill_amount as billAmount',
                'student_bill_payment_record.amount_paid as totalAmountPaid',
                'student_bill_payment_record.last_payment as lastPayment',
                'student_bill_payment_record.amount_owed as balance'
            ]);



        //student school bill payment record book...
        $studentpaymentbillbook = StudentBillPaymentBook::where('student_id', $request->studentId)
                            ->where('class_id', $schoolclassid)
                            ->where('term_id', $request->termid)
                            ->where('session_id', $request->sessionid)
                            ->get();


       // print_r($studentpaymentbillbook);


        $schoolclass = Schoolclass::all();
        $schoolterm = Schoolterm::where('id',$request->termid)
        ->first([
            'schoolterm.term as term',
        ]);



        $schoolsession = Schoolsession::where('id',$request->sessionid)
        ->first([
            'schoolsession.session as session',
        ]);




        return view('schoolpayment.studentpayment')->with('studentdata', $student)
            ->with('student_bill_info', $student_bill_info)
            ->with('studentpaymentbill', $studentpaymentbill)
            ->with('studentpaymentbillbook', $studentpaymentbillbook)
            ->with('student_bill_info_count', $student_bill_info_count)
            ->with('schoolclass', $schoolclass)
            ->with('schoolterm', $schoolterm->term)
            ->with('schoolsession', $schoolsession->session)
            ->with('studentId', $request->studentId)
            ->with('schoolclassId', $schoolclassid)
            ->with('schooltermId', $request->termid)
            ->with('schoolsessionId', $request->sessionid);



    }

    /**
     * Display the specified resource.
     */
    public function show(String $id)
    {


    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }



    public function invoice($studentid, $schoolclassid, $termid, $sessionid)
    {
        $totalAmountPaid = 0;
        $totalPaidArray = [];
        $paymentStatus = '';
    
        // Fetch student data (this part seems fine)
        $student = Student::where('studentRegistration.id', $studentid)
            ->leftJoin('parentRegistration', 'parentRegistration.id', '=', 'studentRegistration.id')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('studentclass', 'studentclass.studentId', '=', 'studentRegistration.id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'studentclass.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
            ->where('schoolsession.status', 'Current')
            ->get([
                'studentRegistration.id as id',
                'studentRegistration.admissionNo as admissionNo',
                'studentRegistration.firstname as firstname',
                'studentRegistration.lastname as lastname',
                'studentRegistration.dateofbirth as dateofbirth',
                'studentRegistration.gender as gender',
                'studentRegistration.updated_at as updated_at',
                'studentpicture.picture as avatar',
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.arm as arm',
                'schoolterm.term as term',
                'schoolsession.session as session',
                'schoolclass.id as schoolclassid',
                'schoolterm.id as termid',
                'schoolsession.id as sessionid',
                'studentRegistration.statusId as statusId'
            ]);
    
        // Generate invoice number
        $invoiceNumber = $this->generateInvoiceNumber();
        $invoice = StudentBillInvoice::create([
            'invoice_no' => $invoiceNumber,
            'student_id' => $studentid,
            'school_bill_id' => 'none',
            'status' => 'NONE',
            'payment_method' => 'none',
            'class_id' => $schoolclassid,
            'termid_id' => $termid,
            'session_id' => $sessionid,
            'generated_by' => auth()->user()->id,
        ]);
    
        // Fix ambiguity in studentpaymentbill query
        $studentpaymentbill = StudentBillPayment::where('student_bill_payment.student_id', $studentid) // Specify table
            ->where('student_bill_payment.class_id', $schoolclassid)
            ->where('student_bill_payment.termid_id', $termid)
            ->where('student_bill_payment.session_id', $sessionid)
            ->where('student_bill_payment.delete_status', '1')
            ->leftJoin('student_bill_payment_record', 'student_bill_payment_record.student_bill_payment_id', '=', 'student_bill_payment.id')
            ->leftJoin('school_bill', 'school_bill.id', '=', 'student_bill_payment.school_bill_id')
            ->leftJoin('users', 'users.id', '=', 'student_bill_payment.generated_by')
            ->whereDate('student_bill_payment.created_at', Carbon::today())
            ->get([
                'student_bill_payment.status as paymentStatus',
                'student_bill_payment.payment_method as paymentMethod',
                'users.name as recievedBy',
                'student_bill_payment.created_at as recievedDate',
                'school_bill.title as title',
                'school_bill.description as description',
                'school_bill.bill_amount as amount',
                'student_bill_payment_record.amount_paid as amountPaid',
                'student_bill_payment_record.last_payment as lastPayment',
                'student_bill_payment_record.amount_owed as balance'
            ]);
    
        // Fix ambiguity in studentpaymentbill_updating query
        $studentpaymentbill_updating = StudentBillPayment::where('student_bill_payment.student_id', $studentid) // Specify table
            ->where('student_bill_payment.class_id', $schoolclassid)
            ->where('student_bill_payment.termid_id', $termid)
            ->where('student_bill_payment.session_id', $sessionid)
            ->whereDate('student_bill_payment.created_at', Carbon::today())
            ->get();
    
        foreach ($studentpaymentbill_updating as $key) {
            $key->delete_status = '0';
            $key->invoiceNo = $invoiceNumber;
            $key->save();
        }
    
        // Fix ambiguity in studentpaymentbill_total_bills query
        $studentpaymentbill_total_bills = StudentBillPayment::where('student_bill_payment.student_id', $studentid) // Specify table
            ->where('student_bill_payment.class_id', $schoolclassid)
            ->where('student_bill_payment.termid_id', $termid)
            ->where('student_bill_payment.session_id', $sessionid)
            ->get();
    
        // Process totals and payment status
        $totalBillAmount = 0;
        $totalPaid = 0;
        $totalOutstanding = 0;
    
        foreach ($studentpaymentbill_total_bills as $key) {
            $relatedData = StudentBillPayment::select(
                'student_bill_payment.school_bill_id',
                'student_bill_payment_record.total_bill AS totalBill',
                'student_bill_payment.class_id',
                'student_bill_payment.termid_id',
                'student_bill_payment.session_id',
                DB::raw('SUM(CAST(student_bill_payment_record.amount_paid AS FLOAT)) as totalAmountPaid')
            )
            ->leftJoin('student_bill_payment_record', 'student_bill_payment_record.student_bill_payment_id', '=', 'student_bill_payment.id')
            ->where('student_bill_payment.student_id', $studentid) // Specify table
            ->where('student_bill_payment.school_bill_id', $key->school_bill_id)
            ->where('student_bill_payment.class_id', $schoolclassid)
            ->where('student_bill_payment.termid_id', $termid)
            ->where('student_bill_payment.session_id', $sessionid)
            ->groupBy(
                'student_bill_payment.school_bill_id',
                'student_bill_payment.class_id',
                'student_bill_payment.termid_id',
                'student_bill_payment.session_id',
                'student_bill_payment_record.total_bill'
            )
            ->first();
    
            if ($relatedData && isset($relatedData->totalAmountPaid)) {
                $totalAmountPaid = $relatedData->totalAmountPaid;
                if ($totalAmountPaid < $relatedData->totalBill) {
                    $paymentStatus = 'Uncomplete';
                } elseif ($totalAmountPaid == $relatedData->totalBill) {
                    $paymentStatus = 'Complete';
                }
            }
    
            $schoolpaymentbillBook = StudentBillPaymentBook::updateOrCreate(
                [
                    'student_id' => $studentid,
                    'school_bill_id' => $key->school_bill_id,
                    'class_id' => $schoolclassid,
                    'term_id' => $termid,
                    'session_id' => $sessionid,
                ],
                [
                    'amount_paid' => $totalAmountPaid,
                    'amount_owed' => $relatedData->totalBill - $totalAmountPaid,
                    'payment_status' => $paymentStatus,
                    'generated_by' => Auth::user()->id,
                ]
            );
    
            $totalBillAmount += $relatedData->totalBill ?? 0;
            $totalPaid += $totalAmountPaid;
            $totalOutstanding += ($relatedData->totalBill - $totalAmountPaid);
        }
    
        $schoolterm = Schoolterm::where('id', $termid)->first(['schoolterm.term as term']);
        $schoolsession = Schoolsession::where('id', $sessionid)->first(['schoolsession.session as session']);
    
        // PDF download logic
        if (request()->has('download_pdf')) {
            $data = [
                'studentdata' => $student,
                'studentpaymentbill' => $studentpaymentbill,
                'invoiceNumber' => $invoiceNumber,
                'schooltermId' => $termid,
                'schoolterm' => $schoolterm->term,
                'schoolsession' => $schoolsession->session,
                'schoolsessionId' => $sessionid,
                'totalBillAmount' => $totalBillAmount,
                'totalPaid' => $totalPaid,
                'totalOutstanding' => $totalOutstanding,
            ];
            $pdf = PDF::loadView('schoolpayment.studentinvoice', $data);
            return $pdf->download('invoice_' . $invoiceNumber . '.pdf');
        }
    
        // Return view
        return view('schoolpayment.studentinvoice')->with([
            'studentdata' => $student,
            'studentpaymentbill' => $studentpaymentbill,
            'invoiceNumber' => $invoiceNumber,
            'schooltermId' => $termid,
            'schoolterm' => $schoolterm->term,
            'schoolsession' => $schoolsession->session,
            'schoolsessionId' => $sessionid,
            'totalBillAmount' => $totalBillAmount,
            'totalPaid' => $totalPaid,
            'totalOutstanding' => $totalOutstanding,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function generateInvoiceNumber()
    {
        do {
            $date = date('Ymd');
            $randomDigits = mt_rand(100, 999);
            $uniqueId = strtoupper(substr(uniqid(), -4));
            $invoiceNumber = "TNT-{$date}-{$randomDigits}{$uniqueId}";
        } while (StudentBillInvoice::where('invoice_no', $invoiceNumber)->exists());

        return $invoiceNumber;
    }


    public function deletestudentpayment(Request $request)
    {
        // Then, delete the main record in student_bill_payment
        $deletePayment1 = StudentBillPayment::where('id', $request->paymentid)
            ->whereDate('created_at', Carbon::today())
            ->delete();

        // delete related records in student_bill_payment_record
        $deletePayment2 = StudentBillPaymentRecord::where('student_bill_payment_id', $request->paymentid)
            ->whereDate('created_at', Carbon::today())
            ->delete();

        //check data deleted or not
        if ($deletePayment1 && $deletePayment2) {
            $success = true;
            $message = 'Record Deleted';
        } else {
            $success = true;
            $message = 'Record not found';
        }

        //  return response
        return response()->json([
            'success' => $success,
            'message' => $request,
        ]);

    }
}
