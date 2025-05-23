<?php

namespace App\Http\Controllers;

use App\Models\Classcategory;
use App\Models\Schoolclass;
use App\Models\Schoolarm;
use App\Models\Classteacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SchoolClassController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View school-class|Create school-class|Update school-class|Delete school-class', ['only' => ['index']]);
        $this->middleware('permission:Create school-class', ['only' => ['store']]);
        $this->middleware('permission:Update school-class', ['only' => ['update']]);
        $this->middleware('permission:Delete school-class', ['only' => ['destroy', 'deleteschoolclass']]);
    }

    public function index(Request $request)
    {
        Log::info('Index School Class Request:', $request->all());
        $pagetitle = "School Class Management";

        $query = Schoolclass::query()
            ->leftJoin('classcategories', 'classcategories.id', '=', 'schoolclass.classcategoryid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(
                'schoolclass.id',
                'schoolclass.schoolclass',
                'schoolarm.arm as arm_name',
                'schoolclass.arm as arm_id',
                'classcategories.category as classcategory',
                'classcategories.id as classcategoryid',
                'schoolclass.updated_at'
            );

        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('schoolclass.schoolclass', 'like', '%' . $search . '%')
                  ->orWhere('schoolarm.arm', 'like', '%' . $search . '%')
                  ->orWhere('classcategories.category', 'like', '%' . $search . '%');
            });
        }

        $all_classes = $query->orderBy('schoolclass.schoolclass')->get();
        $arms = Schoolarm::all();
        $classcategories = Classcategory::all();

        if ($request->ajax()) {
            return response()->json(['classes' => $all_classes]);
        }

        return view('schoolclass.index')
            ->with('all_classes', $all_classes)
            ->with('arms', $arms)
            ->with('classcategories', $classcategories)
            ->with('pagetitle', $pagetitle);
    }

    public function store(Request $request)
    {
        Log::info('Store School Class Request:', $request->all());
    
        // Validate input
        $validator = Validator::make($request->all(), [
            'schoolclass' => 'required|string|max:255',
            'arm_id' => 'required|exists:schoolarm,id',
            'classcategoryid' => 'required|exists:classcategories,id',
        ]);
    
        $validator->after(function ($validator) use ($request) {
            if ($request->arm_id && !Schoolarm::where('id', $request->arm_id)->exists()) {
                $validator->errors()->add('arm_id', 'The selected arm ID ' . $request->arm_id . ' does not exist in schoolarm.');
            }
            if ($request->classcategoryid && !Classcategory::where('id', $request->classcategoryid)->exists()) {
                $validator->errors()->add('classcategoryid', 'The selected category ID ' . $request->classcategoryid . ' does not exist in classcategories.');
            }
            $exists = Schoolclass::where('schoolclass', $request->schoolclass)
                ->where('arm', $request->arm_id)
                ->where('classcategoryid', $request->classcategoryid)
                ->exists();
            if ($exists) {
                $validator->errors()->add('schoolclass', 'The combination of class "' . $request->schoolclass . '", arm ID ' . $request->arm_id . ', and category ID ' . $request->classcategoryid . ' already exists.');
            }
        });
    
        if ($validator->fails()) {
            Log::error('Validation failed for store school class:', ['errors' => $validator->errors()->all(), 'input' => $request->all()]);
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        try {
            $schoolclass = new Schoolclass();
            $schoolclass->schoolclass = $request->schoolclass;
            $schoolclass->arm = $request->arm_id; // Store arm_id in arm column
            $schoolclass->classcategoryid = $request->classcategoryid;
            $schoolclass->description = $request->description ?? 'Null';
            $schoolclass->save();
    
            // Include arm and category names in response
            $arm = Schoolarm::find($schoolclass->arm);
            $category = Classcategory::find($schoolclass->classcategoryid);
    
            $responseData = [
                'id' => $schoolclass->id,
                'schoolclass' => $schoolclass->schoolclass,
                'arm' => $schoolclass->arm,
                'arm_id' => $schoolclass->arm,
                'arm_name' => $arm ? $arm->arm : 'Unknown',
                'classcategoryid' => $schoolclass->classcategoryid,
                'classcategory' => $category ? $category->category : 'Unknown',
                'description' => $schoolclass->description,
                'updated_at' => $schoolclass->updated_at->toISOString(),
                'created_at' => $schoolclass->created_at->toISOString()
            ];
    
            Log::info('School class stored successfully:', $responseData);
    
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'School class added successfully!',
                    'schoolclass' => $responseData
                ], 200);
            }
    
            return redirect()->back()->with('success', 'School class registered successfully!');
        } catch (\Exception $e) {
            Log::error('Error storing school class:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Error storing school class',
                    'error' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error storing school class');
        }
    }

    public function update(Request $request, $id)
    {
        Log::info('Update School Class Request:', ['id' => $id, 'data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'schoolclass' => 'required|string|max:255',
            'arm_id' => 'required|exists:schoolarm,id',
            'classcategoryid' => 'required|exists:classcategories,id',
        ]);

        $validator->after(function ($validator) use ($request, $id) {
            $exists = Schoolclass::where('schoolclass', $request->schoolclass)
                ->where('arm', $request->arm_id)
                ->where('classcategoryid', $request->classcategoryid)
                ->where('id', '!=', $id)
                ->exists();
            if ($exists) {
                $validator->errors()->add('schoolclass', 'This class, arm, and category combination already exists.');
            }
        });

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $schoolclass = Schoolclass::findOrFail($id);
        $schoolclass->schoolclass = $request->schoolclass;
        $schoolclass->arm = $request->arm_id; // Store arm_id in arm column
        $schoolclass->classcategoryid = $request->classcategoryid;
        $schoolclass->description = $request->description ?? 'Null';
        $schoolclass->save();

        // Include arm and category names in response
        $schoolclass->arm_name = Schoolarm::find($schoolclass->arm)->arm;
        $schoolclass->arm_id = $schoolclass->arm;
        $schoolclass->classcategory = Classcategory::find($schoolclass->classcategoryid)->category;

        if ($request->ajax()) {
            return response()->json(['message' => 'School class updated successfully!', 'schoolclass' => $schoolclass]);
        }

        return redirect()->route('schoolclass.index')->with('success', 'School class updated successfully.');
    }

    public function destroy($id)
    {
        Log::info('Delete School Class Request:', ['id' => $id]);

        $schoolclass = Schoolclass::findOrFail($id);
        Classteacher::where('schoolclassid', $id)->delete();
        $schoolclass->delete();

        return response()->json(['message' => 'School class deleted successfully!']);
    }

    public function deleteschoolclass(Request $request)
    {
        Log::info('Delete School Class AJAX Request:', ['schoolclassid' => $request->schoolclassid]);

        $schoolclass = Schoolclass::find($request->schoolclassid);
        if ($schoolclass) {
            Classteacher::where('schoolclassid', $request->schoolclassid)->delete();
            $schoolclass->delete();
            return response()->json([
                'success' => true,
                'message' => 'School class has been removed'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'School class not found'
        ], 404);
    }
}