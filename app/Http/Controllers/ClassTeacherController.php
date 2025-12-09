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
            ->paginate(1000);

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

    // ──────────────────────────────────────────────────────────────
    // STORE – Allow same class in different term/session
    // ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staffid' => 'required|exists:users,id',
            'schoolclassid' => 'required|array',
            'schoolclassid.*' => 'exists:schoolclass,id',
            'termid' => 'required|exists:schoolterm,id',
            'sessionid' => 'required|exists:schoolsession,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $staffId     = $request->staffid;
        $termId      = $request->termid;
        $sessionId   = $request->sessionid;
        $classIds    = $request->schoolclassid;

        $duplicateClasses = [];   // Teacher already has this class in THIS term/session
        $conflictClasses  = [];   // Class already assigned to ANOTHER teacher in THIS term/session

        foreach ($classIds as $classId) {
            $className = Schoolclass::find($classId)->schoolclass ?? "Class ID: $classId";

            // 1. Teacher already assigned to this class in this term/session?
            $alreadyAssigned = ClassTeacher::where('staffid', $staffId)
                ->where('schoolclassid', $classId)
                ->where('termid', $termId)
                ->where('sessionid', $sessionId)
                ->exists();

            if ($alreadyAssigned) {
                $duplicateClasses[] = $className;
                continue;
            }

            // 2. Some OTHER teacher has this class in this term/session?
            $takenByOther = ClassTeacher::where('schoolclassid', $classId)
                ->where('termid', $termId)
                ->where('sessionid', $sessionId)
                ->where('staffid', '!=', $staffId)
                ->exists();

            if ($takenByOther) {
                $conflictClasses[] = $className;
            }
        }

        if (!empty($duplicateClasses)) {
            return response()->json([
                'success' => false,
                'message' => 'This teacher is already assigned to: ' . implode(', ', $duplicateClasses)
            ], 422);
        }

        if (!empty($conflictClasses)) {
            return response()->json([
                'success' => false,
                'message' => 'The following class(es) are already assigned to another teacher for the selected term and session: ' . implode(', ', $conflictClasses)
            ], 422);
        }

        // All good — create records
        foreach ($classIds as $classId) {
            ClassTeacher::create([
                'staffid'       => $staffId,
                'schoolclassid' => $classId,
                'termid'        => $termId,
                'sessionid'     => $sessionId,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Class teacher(s) assigned successfully.'
        ], 201);
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE – Fixed for multi-term/session + reassigning
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'staffid' => 'required|exists:users,id',
            'schoolclassid' => 'required|array',
            'schoolclassid.*' => 'exists:schoolclass,id',
            'termid' => 'required|exists:schoolterm,id',
            'sessionid' => 'required|exists:schoolsession,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $primary = ClassTeacher::findOrFail($id);

        $oldStaffId    = $primary->staffid;
        $oldTermId     = $primary->termid;
        $oldSessionId  = $primary->sessionid;

        $newStaffId    = $request->staffid;
        $newTermId     = $request->termid;
        $newSessionId  = $request->sessionid;
        $newClassIds   = $request->schoolclassid;

        $duplicateClasses = [];
        $conflictClasses  = [];

        foreach ($newClassIds as $classId) {
            $className = Schoolclass::find($classId)->schoolclass ?? "Class ID: $classId";

            // 1. New teacher already has this class in the NEW term/session?
            $newTeacherHasIt = ClassTeacher::where('staffid', $newStaffId)
                ->where('schoolclassid', $classId)
                ->where('termid', $newTermId)
                ->where('sessionid', $newSessionId)
                ->where('staffid', '!=', $oldStaffId) // ignore old records (will be deleted)
                ->exists();

            if ($newTeacherHasIt) {
                $duplicateClasses[] = $className;
                continue;
            }

            // 2. Some third teacher has this class in the NEW term/session?
            $takenByOther = ClassTeacher::where('schoolclassid', $classId)
                ->where('termid', $newTermId)
                ->where('sessionid', $newSessionId)
                ->whereNotIn('staffid', [$oldStaffId, $newStaffId])
                ->exists();

            if ($takenByOther) {
                $conflictClasses[] = $className;
            }
        }

        if (!empty($duplicateClasses) || !empty($conflictClasses)) {
            $msg = '';
            if ($duplicateClasses) {
                $msg .= 'This teacher is already assigned to: ' . implode(', ', $duplicateClasses) . '. ';
            }
            if ($conflictClasses) {
                $msg .= 'These classes are already taken by another teacher: ' . implode(', ', $conflictClasses);
            }

            return response()->json(['success' => false, 'message' => trim($msg)], 422);
        }

        // Delete old assignments for this teacher + old term/session
        ClassTeacher::where('staffid', $oldStaffId)
            ->where('termid', $oldTermId)
            ->where('sessionid', $oldSessionId)
            ->delete();

        // Create new ones
        foreach ($newClassIds as $classId) {
            ClassTeacher::create([
                'staffid'       => $newStaffId,
                'schoolclassid' => $classId,
                'termid'        => $newTermId,
                'sessionid'     => $newSessionId,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Class teacher assignment updated successfully.'
        ]);
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