<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Classcategory;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Calculation\Category;

class ClasscategoryController extends Controller
{


    function __construct()
    {
         $this->middleware('permission:classcategory-list|classcategory-create|classcategory-edit|classcategory-delete', ['only' => ['index','store']]);
         $this->middleware('permission:classcategory-create', ['only' => ['create','store']]);
         $this->middleware('permission:classcategory-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:classcategory-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $classcategories = Classcategory::all();
         return view('classcategories.index')->with('classcategories',$classcategories);


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
    public function store(Request $request)
    {
        $classcategories =  classcategory::where('category',$request->category)
        ->exists();

            if($classcategories){
            return redirect()->back()->with('danger', 'Record already exists');
            }else{

            $input = $request->all();
            Classcategory::create($input);
            return redirect()->back()->with('success', 'Record has been successfully created!');

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


        $cat = Classcategory::find($id);
        return View('classcategories.edit')->with('cat',$cat);
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
        $input = $request->all();
        $sclass = classcategory::find($id);
        $sclass->update($input);

        return redirect()->route('classcategories.index')
            ->with('success', 'Class category updated successfully.');
    }

    public function updateclasscategory(Request $request)
    {
        $this->Validate($request, [
           // 'category' => 'required|numeric',
            'ca1score' => 'required|numeric',
            'ca2score' => 'required|numeric',
            'ca3score' => 'required|numeric',
            'examscore' => 'required|numeric',

        ]
    );


        $input = $request->all();
        $sclass = classcategory::find($request->id);
        $sclass->update($input);

        return redirect()->route('classcategories.index')
            ->with('success', 'Class category updated successfully.');
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

    public function deleteclasscategory(Request $request)
    {
        classcategory::find($request->classcategoryid)->delete();
        //check data deleted or not
        if ($request->classcategoryid) {
            $success = true;
            $message = "Class Category has been removed";
        } else {
            $success = true;
            $message = "Class Category not found";
        }

        //  return response
        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);

    }

}
