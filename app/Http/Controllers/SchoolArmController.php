<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Schoolarm;


class SchoolArmController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:school_arm-list|school_arm-create|school_arm-edit|school_arm-delete', ['only' => ['index','store']]);
         $this->middleware('permission:school_arm-create', ['only' => ['create','store']]);
         $this->middleware('permission:school_arm-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:school_arm-delete', ['only' => ['destroy']]);
    }


    public function index()
    {
        //
        $all_arms = Schoolarm::all()->sortBy('arm');

        return View('arm.index')->with('all_arms',$all_arms);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //

        return View('arm.create');
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
        $sarm = new Schoolarm();
        $validator = Validator::make($request->all(), [
            'arm' => 'required|min:1|unique:schoolarm',
            'remark' =>'required',
        ]

    );

        if ($validator->fails()) {
            //$errors = $validator->errors();
          // return $errors->toJson();
           return redirect()->back()->withErrors($validator)
                                    ->withInput();

        } else{
        $sarm->arm = $request->arm;
        $sarm->description = $request->remark;
        $sarm->save();
        if($sarm != null){
            return redirect()->back()->with('status', 'School class Arm Registered Successfully!');
            }else{
               echo "something went wrong...";
            }
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
        $sarm = Schoolarm::find($id);

        return view('arm.edit')->with('sarm',$sarm);
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
        $this->validate($request, [
            'arm' => 'required|min:1|unique:schoolarm',
             'description'=>'required',
        ]);

        $input = $request->all();
        //print_r( $input);

       $sarm = Schoolarm::find($id);
        //print($sarm);
        $sarm->update($input);

        return redirect()->route('schoolarm.index')
          ->with('success', 'Class Arm  has been updated successfully.');

    }

    public function updatearm(Request $request)
    {


        $this->validate($request, [
            'arm' => 'required|min:1|unique:schoolarm',
             'remark'=>'required',
        ]);

         $input = $request->all();
         $sarm = Schoolarm::find($request->id);
        //print($sarm);
        $sarm->update($input);

        return redirect()->back()->with('success', 'Record has been successfully updated!');
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
        Schoolarm::find($id)->delete();

        return redirect()->route('schoolarm.index')
            ->with('success', 'Arm deleted successfully.');

    }

    public function deletearm(Request $request)
    {
        Schoolarm::find($request->armid)->delete();
        //check data deleted or not
        if ($request->armid) {
            $success = true;
            $message = "Arm has been removed";
        } else {
            $success = true;
            $message = "Arm not found";
        }

        //  return response
        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);

    }
}
