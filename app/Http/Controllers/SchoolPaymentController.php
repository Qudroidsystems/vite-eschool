<?php

namespace App\Http\Controllers;

use App\Models\SchoolBillTermSession;
use App\Models\Schoolclass;
use App\Models\SchoolInformation;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use PDF;

class SchoolPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pagetitle = 'School Bill Payments';
        $students = collect();

        Log::info('Index session:', $request->session()->all());

        if (!$request->ajax()) {
            $students = $this->getStudents();
            Log::info('Index students count:', ['count' => $students->count()]);

            $schoolclass = Schoolclass::all();
            $schoolterm = Schoolterm::all();
            $schoolsession = Schoolsession::all();

            return view('schoolpayment.index')
                ->with('student', $students)
                ->with('schoolclass', $schoolclass)
                ->with('schoolterm', $schoolterm)
                ->with('schoolsession', $schoolsession)
                ->with('pagetitle', $pagetitle);
        }

        if ($request->ajax()) {
            $students = $this->getStudents();

            return response()->json([
                'success' => true,
                'data' => [
                    'students' => $students->map(function ($sc) {
                        return [
                            'id' => $sc->id,
                            'admissionNo' => $sc->admissionNo,
                            'firstname' => $sc->firstname,
                            'lastname' => $sc->lastname,
                            'gender' => $sc->gender,
                            'picture' => $sc->picture ? asset('storage/images/studentavatar/' . $sc->picture) : asset('storage/images/studentavatar/unnamed.png'),
                            'paymentUrl' => route('schoolpayment.termsession', $sc->id)
                        ];
                    }),
                    'count' => $students->count()
                ]
            ], 200);
        }
    }

    /**
     * Get students without filtering by term and session.
     */
    protected function getStudents()
    {
        return Student::leftJoin('parentRegistration', 'parentRegistration.id', '=', 'studentRegistration.id')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('promotionStatus', 'promotionStatus.studentId', '=', 'studentRegistration.id')
            ->where('promotionStatus.classstatus', 'CURRENT')
            ->get([
                'studentRegistration.id as id',
                'studentRegistration.admissionNo as admissionNo',
                'studentRegistration.firstname as firstname',
                'studentRegistration.lastname as lastname',
                'studentRegistration.dateofbirth as dateofbirth',
                'studentRegistration.gender as gender',
                'studentRegistration.updated_at as updated_at',
                'studentpicture.picture as picture',
                'promotionStatus.studentId as studentID',
                'promotionStatus.schoolclassid as schoolclassid',
                'promotionStatus.termid as termid',
                'promotionStatus.sessionid as sessionid',
                'promotionStatus.promotionStatus as pstatus',
                'promotionStatus.classstatus as cstatus'
            ])->sortBy('admissionNo');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'student_id' => 'required|exists:studentRegistration,id',
            'school_bill_id' => 'required|exists:school_bill,id',
            'class_id' => 'required',
            'term_id' => 'required',
            'session_id' => 'required',
            'payment_amount2' => 'required|numeric|min:0.01',
            'payment_method2' => 'required|string',
            'actualAmount' => 'required',
            'balance2' => 'required',
        ]);

        // Clean and format amounts
        $formatteBillAmount = $request->actualAmount;
        $formattedPaymentAmount = $request->payment_amount2 ?? $request->payment_amount;
        $formattedlastAmountPaid = $request->last_amount_paid ?? '0';
        $formattedBalance = $request->balance2;

        // Remove formatting characters
        $plainNumberString = str_replace(['₦', ','], '', $formatteBillAmount);
        $plainNumberString2 = str_replace(['₦', ','], '', $formattedPaymentAmount);
        $plainNumberString3 = str_replace(['₦', ','], '', $formattedlastAmountPaid);
        $plainBalance = str_replace(['₦', ','], '', $formattedBalance);
        
        // Convert to float values
        $amount = floatval($plainNumberString);
        $paymentAmount = is_numeric($formattedPaymentAmount) ? floatval($formattedPaymentAmount) : floatval($plainNumberString2);
        $lastPaidamount = floatval($plainNumberString3);
        $currentBalance = floatval($plainBalance);

        // CRITICAL VALIDATION: Check if payment amount exceeds available balance
        if ($paymentAmount > $currentBalance) {
            return redirect()->back()->withErrors([
                'payment_amount' => 'Payment amount of ₦' . number_format($paymentAmount, 2) . ' cannot exceed outstanding balance of ₦' . number_format($currentBalance, 2)
            ])->withInput();
        }

        // Validate payment amount is positive
        if ($paymentAmount <= 0) {
            return redirect()->back()->withErrors([
                'payment_amount' => 'Payment amount must be greater than zero.'
            ])->withInput();
        }

        // Check if trying to pay more than the original bill amount
        if ($paymentAmount > $amount) {
            return redirect()->back()->withErrors([
                'payment_amount' => 'Payment amount cannot exceed the original bill amount of ₦' . number_format($amount, 2)
            ])->withInput();
        }

        // Calculate totals
        $total_payment = $lastPaidamount + $paymentAmount;
        $newBalance = max(0, $amount - $total_payment);

        // Determine payment status
        $status = $total_payment < $amount ? 'Uncompleted' : 'Completed';

        DB::beginTransaction();

        try {
            // Check if there's already a payment record for this bill
            $existingPayment = StudentBillPayment::where('student_id', $request->student_id)
                ->where('school_bill_id', $request->school_bill_id)
                ->where('class_id', $request->class_id)
                ->where('termid_id', $request->term_id)
                ->where('session_id', $request->session_id)
                ->where('delete_status', '1')
                ->first();

            if ($existingPayment) {
                // Update existing payment record
                $existingPayment->update([
                    'status' => $status,
                    'payment_method' => $request->payment_method2,
                ]);

                $studentpaymentbill = $existingPayment;
            } else {
                // Create new payment record
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
            }

            // Create new payment record entry
            $studentBillPaymentRecord = StudentBillPaymentRecord::create([
                'student_bill_payment_id' => $studentpaymentbill->id,
                'total_bill' => $amount,
                'amount_paid' => $paymentAmount,
                'last_payment' => $paymentAmount,
                'amount_owed' => $newBalance,
                'complete_payment' => $status === 'Completed' ? 'Yes' : 'No',
                'class_id' => $request->class_id,
                'termid_id' => $request->term_id,
                'session_id' => $request->session_id,
                'generated_by' => Auth::user()->id,
                'payment_date' => now(),
                'received_by' => Auth::user()->name ?? 'System',
            ]);

            DB::commit();

            $message = 'Payment of ₦' . number_format($paymentAmount, 2) . ' recorded successfully!';
            if ($status === 'Completed') {
                $message .= ' Bill has been fully paid.';
            } else {
                $message .= ' Outstanding balance: ₦' . number_format($newBalance, 2);
            }

            return redirect()->back()->with('status', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Payment processing failed: ' . $e->getMessage());
            
            return redirect()->back()->withErrors([
                'error' => 'Payment processing failed. Please try again. If the problem persists, contact the administrator.'
            ])->withInput();
        }
    }

    /**
     * Helper method to get current balance for a student's bill
     */
    private function getCurrentBalance($studentId, $schoolBillId, $classId, $termId, $sessionId)
    {
        // Get the original bill amount
        $schoolBill = DB::table('school_bill')->where('id', $schoolBillId)->first();
        
        if (!$schoolBill) {
            return 0;
        }

        // Calculate total amount paid so far
        $totalPaid = StudentBillPaymentRecord::whereHas('studentBillPayment', function($query) use ($studentId, $schoolBillId, $classId, $termId, $sessionId) {
            $query->where('student_id', $studentId)
                  ->where('school_bill_id', $schoolBillId)
                  ->where('class_id', $classId)
                  ->where('termid_id', $termId)
                  ->where('session_id', $sessionId)
                  ->where('delete_status', '1');
        })->sum('amount_paid');

        return max(0, $schoolBill->amount - $totalPaid);
    }

    /**
     * Validate payment before processing
     */
    private function validatePayment($paymentAmount, $currentBalance, $originalAmount)
    {
        $errors = [];

        if ($paymentAmount <= 0) {
            $errors[] = 'Payment amount must be greater than zero.';
        }

        if ($paymentAmount > $currentBalance) {
            $errors[] = 'Payment amount cannot exceed outstanding balance of ₦' . number_format($currentBalance, 2);
        }

        if ($paymentAmount > $originalAmount) {
            $errors[] = 'Payment amount cannot exceed original bill amount of ₦' . number_format($originalAmount, 2);
        }

        return $errors;
    }

    public function termSession(string $id)
    {
        $pagetitle = 'School Bill Payments';

        $schoolterm = Schoolterm::all();
        $schoolsession = Schoolsession::all();

        return view('schoolpayment.termSession', compact('pagetitle'))->with('id', $id)
            ->with('schoolterms', $schoolterm)
            ->with('schoolsessions', $schoolsession);
    }

    public function termSessionPayments(Request $request)
    {
        $pagetitle = 'School Bill Payments';

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
            ->get([
                'school_bill_class_term_session.id as id',
                'school_bill.id as schoolbillid',
                'school_bill.title as title',
                'school_bill.description as description',
                'student_status.id as statusId',
                'school_bill.bill_amount as amount'
            ]);

        $student_bill_info_count = SchoolBillTermSession::where('class_id', $schoolclassid)
            ->where('termid_id', $request->termid)
            ->where('session_id', $request->sessionid)
            ->leftJoin('school_bill', 'school_bill.id', '=', 'school_bill_class_term_session.bill_id')
            ->leftJoin('student_status', 'student_status.id', '=', 'school_bill.statusId')
            ->where('student_status.id', 1)
            ->count();

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

        $studentpaymentbillbook = StudentBillPaymentBook::where('student_id', $request->studentId)
            ->where('class_id', $schoolclassid)
            ->where('term_id', $request->termid)
            ->where('session_id', $request->sessionid)
            ->get();

        $schoolclass = Schoolclass::all();
        $schoolterm = Schoolterm::where('id', $request->termid)
            ->first(['schoolterm.term as term']);
        $schoolsession = Schoolsession::where('id', $request->sessionid)
            ->first(['schoolsession.session as session']);

        return view('schoolpayment.studentpayment', compact('pagetitle'))->with([
            'studentdata' => $student,
            'student_bill_info' => $student_bill_info,
            'studentpaymentbill' => $studentpaymentbill,
            'studentpaymentbillbook' => $studentpaymentbillbook,
            'student_bill_info_count' => $student_bill_info_count,
            'schoolclass' => $schoolclass,
            'schoolterm' => $schoolterm->term,
            'schoolsession' => $schoolsession->session,
            'studentId' => $request->studentId,
            'schoolclassId' => $schoolclassid,
            'schooltermId' => $request->termid,
            'schoolsessionId' => $request->sessionid
        ]);
    }

  public function invoice($studentid, $schoolclassid, $termid, $sessionid)
    {
        $pagetitle = 'Student Invoice';

        // Fetch student data
        $student = Student::where('studentRegistration.id', $studentid)
            ->leftJoin('parentRegistration', 'parentRegistration.studentId', '=', 'studentRegistration.id')
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
                'studentRegistration.statusId as statusId',
                'studentRegistration.home_address as homeadd',
                'parentRegistration.father_phone as phone',
            ]);

        // Fetch active school information
        $schoolInfo = SchoolInformation::getActiveSchool();

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

        // Fetch all bills for the class, term, and session using relationships
        $allBills = SchoolBillTermSession::where('class_id', $schoolclassid)
            ->where('termid_id', $termid)
            ->where('session_id', $sessionid)
            ->with(['bill' => function ($query) {
                $query->where('statusId', 1);
            }])
            ->get()
            ->filter(function ($billTermSession) {
                return !is_null($billTermSession->bill);
            })
            ->map(function ($billTermSession) {
                return (object) [
                    'schoolbillid' => $billTermSession->bill->id,
                    'title' => $billTermSession->bill->title,
                    'description' => $billTermSession->bill->description,
                    'amount' => $billTermSession->bill->bill_amount,
                ];
            });

        // Initialize totals
        $totalBillAmount = 0;
        $totalPaid = 0;
        $totalOutstanding = 0;
        $studentpaymentbill = collect();

        // Process each bill
        foreach ($allBills as $bill) {
            // Get payment details from StudentBillPaymentBook
            $paymentBook = StudentBillPaymentBook::where('student_id', $studentid)
                ->where('school_bill_id', $bill->schoolbillid)
                ->where('class_id', $schoolclassid)
                ->where('term_id', $termid)
                ->where('session_id', $sessionid)
                ->first();

            // Get total payments from StudentBillPaymentRecord
            $totalPayments = StudentBillPaymentRecord::whereHas('studentBillPayment', function ($query) use ($studentid, $bill, $schoolclassid, $termid, $sessionid) {
                $query->where('student_id', $studentid)
                      ->where('school_bill_id', $bill->schoolbillid)
                      ->where('class_id', $schoolclassid)
                      ->where('termid_id', $termid)
                      ->where('session_id', $sessionid)
                      ->where('delete_status', '1');
            })->sum('amount_paid');

            // Get latest payment details
            $latestPayment = StudentBillPayment::where('student_bill_payment.student_id', $studentid)
                ->where('school_bill_id', $bill->schoolbillid)
                ->where('student_bill_payment.class_id', $schoolclassid)
                ->where('student_bill_payment.termid_id', $termid)
                ->where('student_bill_payment.session_id', $sessionid)
                ->where('delete_status', '1')
                ->leftJoin('student_bill_payment_record', 'student_bill_payment_record.student_bill_payment_id', '=', 'student_bill_payment.id')
                ->leftJoin('users', 'users.id', '=', 'student_bill_payment.generated_by')
                ->orderBy('student_bill_payment_record.created_at', 'desc')
                ->first([
                    'student_bill_payment.status as paymentStatus',
                    'student_bill_payment.payment_method as paymentMethod',
                    'users.name as recievedBy',
                    'student_bill_payment.created_at as recievedDate',
                    'student_bill_payment_record.last_payment as lastPayment',
                ]);

            // Calculate balance
            $amountPaid = $paymentBook ? $paymentBook->amount_paid : $totalPayments;
            $balance = $bill->amount - $amountPaid;

            // Create bill object for invoice
            $billData = (object) [
                'schoolbillid' => $bill->schoolbillid,
                'title' => $bill->title,
                'description' => $bill->description,
                'amount' => $bill->amount,
                'amountPaid' => $amountPaid,
                'lastPayment' => $latestPayment ? $latestPayment->lastPayment : 0,
                'balance' => $balance,
                'paymentStatus' => $paymentBook ? $paymentBook->payment_status : ($balance == 0 ? 'Completed' : 'Uncompleted'),
                'paymentMethod' => $latestPayment ? $latestPayment->paymentMethod : 'N/A',
                'recievedBy' => $latestPayment ? $latestPayment->recievedBy : null,
                'recievedDate' => $latestPayment ? $latestPayment->recievedDate : null,
            ];

            $studentpaymentbill->push($billData);

            // Update totals
            $totalBillAmount += $bill->amount;
            $totalPaid += $amountPaid;
            $totalOutstanding += $balance;

            // Update payment book
            StudentBillPaymentBook::updateOrCreate(
                [
                    'student_id' => $studentid,
                    'school_bill_id' => $bill->schoolbillid,
                    'class_id' => $schoolclassid,
                    'term_id' => $termid,
                    'session_id' => $sessionid,
                ],
                [
                    'amount_paid' => $amountPaid,
                    'amount_owed' => $balance,
                    'payment_status' => $billData->paymentStatus,
                    'generated_by' => Auth::user()->id,
                ]
            );
        }

        // Update existing payments to associate with invoice
        $studentPayments = StudentBillPayment::where('student_id', $studentid)
            ->where('class_id', $schoolclassid)
            ->where('termid_id', $termid)
            ->where('session_id', $sessionid)
            ->whereDate('created_at', Carbon::today())
            ->get();
        foreach ($studentPayments as $payment) {
            $payment->update([
                'delete_status' => '0',
                'invoiceNo' => $invoiceNumber,
            ]);
        }

        $schoolterm = Schoolterm::where('id', $termid)->first(['schoolterm.term as term']);
        $schoolsession = Schoolsession::where('id', $sessionid)->first(['schoolsession.session as session']);

        // Prepare data
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
            'studentId' => $studentid,
            'schoolclassId' => $schoolclassid,
            'termId' => $termid,
            'sessionId' => $sessionid,
            'schoolInfo' => $schoolInfo, // Pass school information
        ];

        if (request()->has('download_pdf')) {
            $pdf = PDF::loadView('schoolpayment.studentinvoice', $data)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'dpi' => 150,
                    'defaultFont' => 'sans-serif',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                ]);
            return $pdf->download('invoice_' . $invoiceNumber . '.pdf');
        }

        return view('schoolpayment.studentinvoice', compact('pagetitle'))->with($data);
    }
    public function deletestudentpayment(Request $request, $paymentid)
    {
        if (!StudentBillPayment::where('id', $paymentid)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment record not found.'
            ], 404);
        }

        $deletePayment1 = StudentBillPayment::where('id', $paymentid)
            ->whereDate('created_at', Carbon::today())
            ->delete();

        $deletePayment2 = StudentBillPaymentRecord::where('student_bill_payment_id', $paymentid)
            ->whereDate('created_at', Carbon::today())
            ->delete();

        if ($deletePayment1 || $deletePayment2) {
            return response()->json([
                'success' => true,
                'message' => 'Record deleted successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Record not found or could not be deleted.'
        ], 404);
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
}
