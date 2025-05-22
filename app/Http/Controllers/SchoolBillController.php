<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\SchoolBillModel;

class SchoolBillController extends Controller
{

    // function __construct()
    // {
    //      $this->middleware('permission:school_bill-list|school_bill-create|school_bill-edit|school_bill-delete', ['only' => ['index','store']]);
    //      $this->middleware('permission:school_bill-create', ['only' => ['create','store']]);
    //      $this->middleware('permission:school_bill-edit', ['only' => ['edit','update']]);
    //      $this->middleware('permission:school_bill-delete', ['only' => ['destroy']]);
    // }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $schoolbills = SchoolBillModel::leftJoin('student_status','student_status.id','=','school_bill.id')
        // ->get(['school_bill.id as id','school_bill.title as title',
        //      'school_bill.description as description','school_bill.bill_amount as bill_amount',
        //      'student_status.id as statusId','school_bill.updated_at as updated_at']);

        $schoolbills = SchoolBillModel::leftJoin('student_status', 'student_status.id', '=', 'school_bill.statusId')
                                        ->whereIn('student_status.id', [1, 2])
                                        ->get([
                                            'school_bill.id as id',
                                            'school_bill.title as title',
                                            'school_bill.description as description',
                                            'school_bill.bill_amount as bill_amount',
                                            'student_status.id as statusId',
                                            'school_bill.updated_at as updated_at'
                                        ]);

        return view('schoolbill.index')->with('schoolbills',$schoolbills);
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
        $sbill = new SchoolBillModel();
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:1|unique:school_bill',
            'bill_amount' => 'required|min:1|unique:school_bill',
            'description' =>'required',
            'statusId' =>'required',
        ] );

        if ($validator->fails()) {
            //$errors = $validator->errors();
          // return $errors->toJson();
           return redirect()->back()->withErrors($validator)
                                    ->withInput();

        }
        else{
            $formattedNumber = $request->bill_amount;

            // Step 1: Remove the currency symbol (₦) and commas
            $plainNumberString = str_replace(['₦', ','], '', $formattedNumber);

            // Step 2: Convert the string to a number
            $number = floatval($plainNumberString);

            // The number is now ready to be stored in the database
            echo $number; // Outputs: 34000
            $sbill->title = $request->title;
            $sbill->bill_amount = $number ;
            $sbill->description = $request->description;
            $sbill->statusId = $request->statusId;
            $sbill->save();
            if($sbill != null){
                return redirect()->back()->with('status', 'School Bill created Successfully!');
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }


    public function updatebill(Request $request)
    {


        // $this->validate($request, [
        //     'title' => 'required|min:1|unique:school_bill',
        //     'bill_amount'=> 'required|min:1|unique:school_bill',
        //     'description'=>'required',
        // ]);

         $input = $request->all();
         $sbill = SchoolBillModel::find($request->id);
        $sbill->update($input);

        return redirect()->back()->with('success', 'SchooL Bill  successfully updated!');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function deletebill(Request $request)
    {
        // Schoolarm::find($request->armid)->delete();
        // //check data deleted or not
        // if ($request->armid) {
        //     $success = true;
        //     $message = "Arm has been removed";
        // } else {
        //     $success = true;
        //     $message = "Arm not found";
        // }

        // //  return response
        // return response()->json([
        //     'success' => $success,
        //     'message' => $message,
        // ]);

    }

}
