<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Schoolterm;
use App\Models\Schoolclass;
use App\Models\ClassTeacher;
use Illuminate\Http\Request;
use App\Models\Schoolsession;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ClassTeacherController extends Controller
{
    public function index(Request $request)
    {
        $pagetitle = "Class Teacher Management";

        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id as id', 'schoolarm.arm as schoolarm', 'schoolclass.schoolclass as schoolclass'])
            ->orderBy('schoolclass.schoolclass')
            ->get();

        $subjectteachers = User::whereHas('roles', function ($q) {
            $q->where('name', '!=', 'Student');
        })->get(['users.id as userid', 'users.name as name']);

        $schoolterms = Schoolterm::all();
        $schoolsessions = Schoolsession::all();

        $classteachers = ClassTeacher::leftJoin('users', 'users.id', '=', 'classteacher.staffid')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'classteacher.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'classteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'classteacher.sessionid')
            ->select([
                'classteacher.id as id',
                'users.id as userid',
                'users.name as staffname',
                'users.avatar as avatar',
                'schoolclass.id as schoolclassid',
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.id as schoolarmid',
                'schoolarm.arm as schoolarm',
                'schoolterm.id as termid',
                'schoolterm.term as term',
                'schoolsession.id as sessionid',
                'schoolsession.session as session',
                'classteacher.updated_at as updated_at'
            ])
            ->orderBy('schoolclass.schoolclass')
            ->orderBy('users.name')
            ->paginate(100);

        if ($request->ajax()) {
            $html = view('classteacher.index', compact(
                'classteachers',
                'schoolclass',
                'subjectteachers',
                'schoolterms',
                'schoolsessions',
                'pagetitle'
            ))->render();

            if (empty($html)) {
                Log::error("Empty HTML response in ClassTeacherController::index for AJAX request", ['url' => $request->fullUrl()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to render view',
                ], 500);
            }

            Log::info("AJAX response generated", [
                'html_length' => strlen($html),
                'count' => $classteachers->count(),
                'total' => $classteachers->total()
            ]);

            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $classteachers->count(),
                'total' => $classteachers->total(),
            ]);
        }

        return view('classteacher.index')
            ->with('classteachers', $classteachers)
            ->with('schoolclass', $schoolclass)
            ->with('subjectteachers', $subjectteachers)
            ->with('schoolterms', $schoolterms)
            ->with('schoolsessions', $schoolsessions)
            ->with('pagetitle', $pagetitle);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staffid' => 'required|exists:users,id',
            'schoolclassid' => 'required|array',
            'schoolclassid.*' => 'exists:schoolclass,id',
            'termid' => 'required|exists:schoolterm,id',
            'sessionid' => 'required|exists:schoolsession,id',
        ], [
            'staffid.required' => 'Please select a teacher!',
            'staffid.exists' => 'Selected teacher does not exist!',
            'schoolclassid.required' => 'Please select at least one class!',
            'schoolclassid.*.exists' => 'Selected class does not exist!',
            'termid.required' => 'Please select a term!',
            'termid.exists' => 'Selected term does not exist!',
            'sessionid.required' => 'Please select a session!',
            'sessionid.exists' => 'Selected session does not exist!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $staffId = $request->input('staffid');
        $termId = $request->input('termid');
        $sessionId = $request->input('sessionid');
        $classIds = $request->input('schoolclassid');

        $duplicateClasses = [];
        $assignedClasses = [];

        foreach ($classIds as $classId) {
            // Check if this teacher already has this class in this term/session
            $exists = ClassTeacher::where('staffid', $staffId)
                ->where('schoolclassid', $classId)
                ->where('termid', $termId)
                ->where('sessionid', $sessionId)
                ->exists();

            if ($exists) {
                $className = Schoolclass::find($classId)?->schoolclass ?? $classId;
                $duplicateClasses[] = $className;
                continue;
            }

            // Check if another teacher has this class
            $otherTeacher = ClassTeacher::where('schoolclassid', $classId)
                ->where('termid', $termId)
                ->where('sessionid', $sessionId)
                ->where('staffid', '!=', $staffId)
                ->exists();

            if ($otherTeacher) {
                $className = Schoolclass::find($classId)?->schoolclass ?? $classId;
                $assignedClasses[] = $className;
            }
        }

        if (!empty($duplicateClasses)) {
            return response()->json([
                'success' => false,
                'message' => 'This teacher is already assigned to: ' . implode(', ', $duplicateClasses)
            ], 422);
        }

        if (!empty($assignedClasses)) {
            return response()->json([
                'success' => false,
                'message' => 'The following class(es) are already assigned to another teacher: ' . implode(', ', $assignedClasses)
            ], 422);
        }

        $createdRecords = [];
        foreach ($classIds as $classId) {
            $createdRecords[] = ClassTeacher::create([
                'staffid' => $staffId,
                'schoolclassid' => $classId,
                'termid' => $termId,
                'sessionid' => $sessionId,
            ]);
        }

        Log::info("Class teacher(s) added", ['records' => count($createdRecords), 'staffid' => $staffId]);

        return response()->json([
            'success' => true,
            'message' => 'Class Teacher(s) added successfully.',
            'data' => $createdRecords
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'staffid' => 'required|exists:users,id',
            'schoolclassid' => 'required|array',
            'schoolclassid.*' => 'exists:schoolclass,id',
            'termid' => 'required|exists:schoolterm,id',
            'sessionid' => 'required|exists:schoolsession,id',
        ], [
            'staffid.required' => 'Please select a teacher!',
            'staffid.exists' => 'Selected teacher does not exist!',
            'schoolclassid.required' => 'Please select at least one class!',
            'schoolclassid.*.exists' => 'Selected class does not exist!',
            'termid.required' => 'Please select a term!',
            'termid.exists' => 'Selected term does not exist!',
            'sessionid.required' => 'Please select a session!',
            'sessionid.exists' => 'Selected session does not exist!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $primaryRecord = ClassTeacher::find($id);
        if (!$primaryRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Class Teacher record not found.'
            ], 404);
        }

        $oldStaffId = $primaryRecord->staffid;
        $oldTermId = $primaryRecord->termid;
        $oldSessionId = $primaryRecord->sessionid;

        $newStaffId = $request->input('staffid');
        $newTermId = $request->input('termid');
        $newSessionId = $request->input('sessionid');
        $newClassIds = $request->input('schoolclassid');

        $duplicateClasses = [];
        $assignedToOthers = [];

        foreach ($newClassIds as $classId) {
            // 1. Prevent: New teacher already has this class (outside the group we're replacing)
            $alreadyWithNewTeacher = ClassTeacher::where('staffid', $newStaffId)
                ->where('schoolclassid', $classId)
                ->where('termid', $newTermId)
                ->where('sessionid', $newSessionId)
                ->where('staffid', '!=', $oldStaffId) // Exclude old teacher's records (they will be deleted)
                ->exists();

            if ($alreadyWithNewTeacher) {
                $className = Schoolclass::find($classId)?->schoolclass ?? $classId;
                $duplicateClasses[] = $className;
                continue;
            }

            // 2. Prevent: Class is assigned to a completely different teacher (not old, not new)
            $assignedToThirdParty = ClassTeacher::where('schoolclassid', $classId)
                ->where('termid', $newTermId)
                ->where('sessionid', $newSessionId)
                ->whereNotIn('staffid', [$oldStaffId, $newStaffId])
                ->exists();

            if ($assignedToThirdParty) {
                $className = Schoolclass::find($classId)?->schoolclass ?? $classId;
                $assignedToOthers[] = $className;
            }
        }

        if (!empty($duplicateClasses)) {
            return response()->json([
                'success' => false,
                'message' => 'This teacher is already assigned to the following class(es): ' . implode(', ', $duplicateClasses)
            ], 422);
        }

        if (!empty($assignedToOthers)) {
            return response()->json([
                'success' => false,
                'message' => 'The following class(es) are already assigned to another teacher: ' . implode(', ', $assignedToOthers)
            ], 422);
        }

        // Safe to delete old assignments and create new ones
        ClassTeacher::where('staffid', $oldStaffId)
            ->where('termid', $oldTermId)
            ->where('sessionid', $oldSessionId)
            ->delete();

        $createdRecords = [];
        foreach ($newClassIds as $classId) {
            $createdRecords[] = ClassTeacher::create([
                'staffid' => $newStaffId,
                'schoolclassid' => $classId,
                'termid' => $newTermId,
                'sessionid' => $newSessionId,
            ]);
        }

        Log::info("Class teacher assignment updated successfully", [
            'old_staff' => $oldStaffId,
            'new_staff' => $newStaffId,
            'term' => $newTermId,
            'session' => $newSessionId,
            'classes' => $newClassIds
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Class teacher assignment updated successfully.',
            'data' => $createdRecords
        ], 200);
    }

    public function destroy($id)
    {
        $classteacher = ClassTeacher::find($id);
        if (!$classteacher) {
            return response()->json([
                'success' => false,
                'message' => 'Class Teacher not found.'
            ], 404);
        }

        $classteacher->delete();

        Log::info("Class teacher deleted", ['id' => $id]);

        return response()->json([
            'success' => true,
            'message' => 'Class Teacher deleted successfully.'
        ], 200);
    }

    public function deleteMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No class teachers selected for deletion.'
            ], 400);
        }

        $deleted = ClassTeacher::whereIn('id', $ids)->delete();

        Log::info("Multiple class teachers deleted", ['count' => $deleted, 'ids' => $ids]);

        return response()->json([
            'success' => true,
            'message' => "$deleted class teacher(s) deleted successfully."
        ], 200);
    }

    public function assignments($staffId, $termId, $sessionId)
    {
        $classIds = ClassTeacher::where('staffid', $staffId)
            ->where('termid', $termId)
            ->where('sessionid', $sessionId)
            ->pluck('schoolclassid')
            ->toArray();

        return response()->json([
            'success' => true,
            'classIds' => $classIds
        ], 200);
    }
}