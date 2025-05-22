<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schoolterm;

class SchooltermController extends Controller
{


    function __construct()
    {
         $this->middleware('permission:term-list|term-create|term-edit|term-delete', ['only' => ['index','store']]);
         $this->middleware('permission:term-create', ['only' => ['create','store']]);
         $this->middleware('permission:term-edit', ['only' => ['edit','update','updateterm']]);
         $this->middleware('permission:term-delete', ['only' => ['destroy','deleteterm']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $terms = Schoolterm::all();
        return view('term.index')->with('terms',$terms);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('term.create');
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
        $input = $request->all();
        $checkterm = Schoolterm::where('term',$request->input('term'))->exists();
        if($checkterm){
            return redirect()->route('term.index')
            ->with('danger', 'Ooops, Term is already taken');

        }else{
            Schoolterm::create($input);
            return redirect()->route('term.index')
            ->with('success', 'Term has been Created Successfuly');
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
        $term = Schoolterm::find($id);

        return view('term.edit')->with('term',$term);
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

        $checkterm = Schoolterm::where('term',$request->input('term'))->exists();
        if($checkterm){
            return redirect()->route('create.index')
            ->with('danger', 'Ooops, Term is already taken');

        }else{

            $term = Schoolterm::find($id);
            $input= $request->all();
            $term->update($input);
            return redirect()->route('create.index')
            ->with('success', 'Term has been Edited Successfuly');
        }




    }


    public function updateterm(Request $request)
    {

            //echo $request->id;
        $checkterm = Schoolterm::where('term',$request->input('term'))->exists();
        if($checkterm){
            return redirect()->route('term.index')
            ->with('danger', 'Ooops, Term is already taken');

        }else{

            $term = Schoolterm::find($request->id);
            $input= $request->all();
            $term->update($input);
            return redirect()->route('term.index')
            ->with('success', 'Term has been Edited Successfuly');
        }



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
        Schoolterm::find($id)->delete();

        return redirect()->route('term.index')
            ->with('success', 'School Term deleted successfully.');
    }


    public function deleteterm(Request $request)
    {
        Schoolterm::find($request->termid)->delete();
        //check data deleted or not
        if ($request->termid) {
            $success = true;
            $message = "Term has been removed";
        } else {
            $success = true;
            $message = "Term not found";
        }

        //  return response
        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);

    }
}
