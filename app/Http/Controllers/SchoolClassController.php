<?php

namespace App\Http\Controllers;

use App\Models\Classcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Schoolclass;
use App\Models\Schoolarm;
use App\Models\ClassTeacher;




class SchoolClassController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    function __construct()
    {
         $this->middleware('permission:school_class-list|school_class-create|school_class-edit|school_class-delete', ['only' => ['index','store']]);
         $this->middleware('permission:school_class-create', ['only' => ['create','store']]);
         $this->middleware('permission:school_class-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:school_class-delete', ['only' => ['destroy']]);
    }


    public function index()
    {
        //
        $all_classes = Schoolclass::all();
        $all_classes = Schoolclass::leftJoin('classcategories', 'classcategories.id','=','schoolclass.classcategoryid')
        ->leftJoin('schoolarm', 'schoolarm.id','=','schoolclass.arm')
        ->get(['classcategories.id as categoryid','classcategories.category as classcategory',
              'schoolclass.id as id','schoolclass as schoolclass','schoolarm.arm as arm','schoolclass.updated_at as updated_at']);

        $arms = Schoolarm::all();
        $classcategories = Classcategory::all();
        return View('schoolclass.index')->with('all_classes',$all_classes)
                                        ->with('arms',$arms)
                                        ->with('classcategories',$classcategories);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $arms = Schoolarm::all();
        return view('schoolclass.create')->with('arms',$arms);;
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
        $sclass = new Schoolclass();
         $validator = Validator::make($request->all(), [
             'schoolclass' => 'required',
             'arm' => 'required',

         ]
     );

         if ($validator->fails()) {
             //$errors = $validator->errors();
           // return $errors->toJson();
            return redirect()->back()->withErrors($validator)
                                     ->withInput();

         } else{

            $schoolclasscheck = Schoolclass::where('schoolclass',$request->schoolclass)
                                           ->where('arm','none',$request->arm)
                                           ->where('classcategoryid',$request->classcategoryid)
                                           ->exists();

            if ($schoolclasscheck){

            return redirect()->back()->with('danger', 'Ooops! Record already exist!');
            }else{

                $sclass->schoolclass = $request->schoolclass;
                $sclass->arm = $request->arm;
                $sclass->description = "Null";
                $sclass->classcategoryid = $request->classcategoryid;
                $sclass->save();

            if($sclass != null){

            return redirect()->back()->with('success', 'School classs Registered Successfully!!');

            }else{

            return redirect()->back()->with('status', 'Something went wrong!');
            }
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

        $all_classes = Schoolclass::where('schoolclass.id',$id)
         ->leftJoin('classcategories', 'classcategories.id','=','schoolclass.schoolclassid')
         ->get(['schoolclass.schoolclassid as categoryid','classcategories.category as schoolclass',
              'schoolclass.id as id','schoolclass as schoolclass','schoolclass.arm as arm','schoolclass.updated_at as updated_at']);
      // foreach($sclass)
      $arms = Schoolarm::all();
      $classcategories = schoolclass::all();
      return View('schoolclass.edit')->with('all_classes',$all_classes)
                                      ->with('arms',$arms)
                                      ->with('classcategories',$classcategories);
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
            'schoolclass' => 'required',
            'arm' => 'required|min:1|',
             'schoolclassid'=>'required',
        ]);

        $input = $request->all();


        $sclass = Schoolclass::find($id);
        $sclass->update($input);

        return redirect()->route('schoolclass.index')
            ->with('success', 'School class updated successfully.');

    }

    public function updateschoolclass(Request $request)
    {

        //  $this->validate($request, [
        //     'house' => 'required',
        //     'housecolour' => 'required'
        // ]);

        DB::table('schoolclass')->updateOrInsert(
            ['id'=>$request->id],
            ['house'=>$request->house,
                    'housecolour'=>$request->housecolour,
                    'housemasterid'=>$request->update_housemasterid,
                    'termid'=>$request->update_termid,
                    'sessionid'=>$request->update_sessionid,]);

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
        Schoolclass::find($id)->delete();
        Classteacher::where('schoolclassid',$id)->delete();

        return redirect()->route('schoolclass.index')
            ->with('success', 'School class deleted successfully.');
    }

    public function deleteschoolclass(Request $request)
    {
        schoolclass::find($request->schoolclassid)->delete();
        //check data deleted or not
        if ($request->schoolclassid) {
            $success = true;
            $message = "School Class has been removed";
        } else {
            $success = true;
            $message = "School Class not found";
        }

        //  return response
        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);

    }
}
