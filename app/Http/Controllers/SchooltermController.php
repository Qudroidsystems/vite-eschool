<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schoolterm;

class SchooltermController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View term|Create term|Update term|Delete term', ['only' => ['index']]);
        $this->middleware('permission:Create term', ['only' => ['store']]);
        $this->middleware('permission:Update term', ['only' => ['update', 'updateterm']]);
        $this->middleware('permission:Delete term', ['only' => ['destroy', 'deleteterm']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $pagetitle = "Term Management";
        $query = Schoolterm::query();

        if ($request->has('search')) {
            $query->where('term', 'like', '%' . $request->query('search') . '%');
        }

        $terms = $query->paginate(10);

        if ($request->ajax()) {
            return response()->json(['terms' => $terms->items()]);
        }

        return view('term.index')->with('terms', $terms)->with('pagetitle', $pagetitle);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(['term' => 'required|string|max:255']);

        $checkterm = Schoolterm::where('term', $request->input('term'))->exists();
        if ($checkterm) {
            return response()->json(['success' => false, 'message' => 'Term is already taken'], 422);
        }

        Schoolterm::create($request->only('term'));
        return response()->json(['success' => true, 'message' => 'Term has been created successfully']);
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
        $request->validate(['term' => 'required|string|max:255']);

        $checkterm = Schoolterm::where('term', $request->input('term'))->where('id', '!=', $id)->exists();
        if ($checkterm) {
            return response()->json(['success' => false, 'message' => 'Term is already taken'], 422);
        }

        $term = Schoolterm::findOrFail($id);
        $term->update($request->only('term'));
        return response()->json(['success' => true, 'message' => 'Term has been updated successfully']);
    }

    /**
     * Update term via AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateterm(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:schoolterms,id',
            'term' => 'required|string|max:255'
        ]);

        $checkterm = Schoolterm::where('term', $request->input('term'))->where('id', '!=', $request->id)->exists();
        if ($checkterm) {
            return response()->json(['success' => false, 'message' => 'Term is already taken'], 422);
        }

        $term = Schoolterm::findOrFail($request->id);
        $term->update($request->only('term'));
        return response()->json(['success' => true, 'message' => 'Term has been updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $term = Schoolterm::findOrFail($id);
        $term->delete();
        return response()->json(['success' => true, 'message' => 'Term has been deleted successfully']);
    }

    /**
     * Delete term via AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteterm(Request $request)
    {
        $request->validate(['termid' => 'required|exists:schoolterms,id']);
        $term = Schoolterm::findOrFail($request->termid);
        $term->delete();
        return response()->json(['success' => true, 'message' => 'Term has been deleted successfully']);
    }
}