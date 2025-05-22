<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schoolsession;
use Illuminate\Support\Facades\Validator;

class SchoolsessionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View session|Create session|Update session|Delete session', ['only' => ['index', 'store']]);
        $this->middleware('permission:Create session', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update session', ['only' => ['edit', 'update', 'updatesession']]);
        $this->middleware('permission:Delete session', ['only' => ['destroy', 'deletesession']]);
    }

    public function index()
    {
        $pagetitle = "Session Management";
        $data = Schoolsession::paginate(5);
        return view('session.index', compact('data', 'pagetitle'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session' => 'required|string|unique:schoolsessions,session',
            'sessionstatus' => 'required|in:Current,Past',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->sessionstatus === 'Current' && Schoolsession::where('status', 'Current')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'A session with CURRENT status already exists.',
            ], 422);
        }

        $session = Schoolsession::create([
            'session' => $request->session,
            'status' => $request->sessionstatus, // Map sessionstatus to status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'School Session added successfully.',
            'session' => [
                'id' => $session->id,
                'session' => $session->session,
                'sessionstatus' => $session->status, // Return sessionstatus for frontend
                'updated_at' => $session->updated_at,
            ],
        ], 201);
    }

    public function updatesession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:schoolsessions,id',
            'session' => 'required|string|unique:schoolsessions,session,' . $request->id,
            'sessionstatus' => 'required|in:Current,Past',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->sessionstatus === 'Current' && Schoolsession::where('status', 'Current')->where('id', '!=', $request->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'A session with CURRENT status already exists.',
            ], 422);
        }

        $session = Schoolsession::find($request->id);
        $session->update([
            'session' => $request->session,
            'status' => $request->sessionstatus, // Map sessionstatus to status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'School Session updated successfully.',
            'session' => [
                'id' => $session->id,
                'session' => $session->session,
                'sessionstatus' => $session->status, // Return sessionstatus for frontend
                'updated_at' => $session->updated_at,
            ],
        ]);
    }

    public function deletesession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionid' => 'required|exists:schoolsessions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        Schoolsession::find($request->sessionid)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Session has been removed',
        ]);
    }

    public function create()
    {
        $pagetitle = "Session Management";
        return view('session.create')->with('pagetitle', $pagetitle);
    }

    public function edit($id)
    {
        $pagetitle = "Session Management";
        $session = Schoolsession::findOrFail($id);
        return view('session.edit')->with('session', $session)->with('pagetitle', $pagetitle);
    }

    public function update(Request $request, $id)
    {
        $pagetitle = "Session Management";
        $request->validate([
            'session' => 'required|string|unique:schoolsessions,session,' . $id,
            'sessionstatus' => 'required|in:Current,Past',
        ]);

        if ($request->sessionstatus === 'Current' && Schoolsession::where('status', 'Current')->where('id', '!=', $id)->exists()) {
            return redirect()->route('session.index')
                ->with('danger', 'A session with CURRENT status already exists.')
                ->with('pagetitle', $pagetitle);
        }

        $session = Schoolsession::findOrFail($id);
        $session->update([
            'session' => $request->session,
            'status' => $request->sessionstatus,
        ]);
        return redirect()->route('session.index')
            ->with('success', 'School Session updated successfully.')
            ->with('pagetitle', $pagetitle);
    }

    public function destroy($id)
    {
        $pagetitle = "Session Management";
        Schoolsession::findOrFail($id)->delete();
        return redirect()->route('session.index')
            ->with('success', 'School Session deleted successfully.')
            ->with('pagetitle', $pagetitle);
    }
}


