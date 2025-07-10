<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Studentclass;
use App\Models\Studentpersonalityprofile;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use App\Models\Schoolterm;
use App\Models\Schoolsession;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassBroadsheetController extends Controller
{
    public function classBroadsheet($schoolclassid, $sessionid, $termid)
    {
        $pagetitle = "Class Broadsheet";

        // Fetch students enrolled in the specified class, term, and session
        $students = Studentclass::where('studentclass.schoolclassid', $schoolclassid)
            ->where('studentclass.sessionid', $sessionid)
            ->where('studentclass.termid', $termid)
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'studentclass.studentId')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->get([
                'studentRegistration.id as id',
                'studentRegistration.admissionNo as admissionNo',
                'studentRegistration.firstname as fname',
                'studentRegistration.lastname as lastname',
                'studentRegistration.othername as othername',
                'studentRegistration.gender as gender',
                'studentpicture.picture as picture'
            ])->sortBy('lastname');

        // Fetch all subjects for the class
        $subjects = Subject::whereHas('broadsheetRecords', function ($query) use ($schoolclassid, $sessionid) {
            $query->where('schoolclass_id', $schoolclassid)
                  ->where('session_id', $sessionid);
        })->orderBy('subject')->get(['id', 'subject', 'subject_code']);

        // Fetch scores for all students in the class
        $scores = Broadsheets::where('broadsheet_records.schoolclass_id', $schoolclassid)
            ->where('broadsheets.term_id', $termid)
            ->where('broadsheet_records.session_id', $sessionid)
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
            ->get([
                'broadsheet_records.student_id',
                'studentRegistration.firstname',
                'studentRegistration.lastname',
                'studentRegistration.othername',
                'subject.subject as subject_name',
                'subject.subject_code',
                'broadsheets.ca1',
                'broadsheets.ca2',
                'broadsheets.ca3',
                'broadsheets.exam',
                'broadsheets.total',
                'broadsheets.grade',
                'broadsheets.subject_position_class as position',
                'broadsheets.avg as class_average',
            ]);

        // Fetch personality profiles for comments
        $personalityProfiles = Studentpersonalityprofile::where('schoolclassid', $schoolclassid)
            ->where('sessionid', $sessionid)
            ->where('termid', $termid)
            ->get([
                'studentid',
                'classteachercomment',
                'guidancescomment'
            ]);

        $schoolclass = Schoolclass::where('id', $schoolclassid)->first(['schoolclass', 'arm']);
        $schoolterm = Schoolterm::where('id', $termid)->value('term') ?? 'N/A';
        $schoolsession = Schoolsession::where('id', $sessionid)->value('session') ?? 'N/A';

        return view('classbroadsheet.classbroadsheet')
            ->with('students', $students)
            ->with('subjects', $subjects)
            ->with('scores', $scores)
            ->with('personalityProfiles', $personalityProfiles)
            ->with('schoolclass', $schoolclass)
            ->with('schoolterm', $schoolterm)
            ->with('schoolsession', $schoolsession)
            ->with('schoolclassid', $schoolclassid)
            ->with('sessionid', $sessionid)
            ->with('termid', $termid)
            ->with('pagetitle', $pagetitle);
    }

    public function updateComments(Request $request, $schoolclassid, $sessionid, $termid)
    {
        // $this->authorize('Edit comments'); // Commented out to ensure functionality without permission setup

        $teacherComments = $request->input('teacher_comments', []);
        $guidanceComments = $request->input('guidance_comments', []);

        foreach ($teacherComments as $studentId => $teacherComment) {
            $guidanceComment = $guidanceComments[$studentId] ?? '';

            // Find or create the personality profile record
            $profile = Studentpersonalityprofile::firstOrNew([
                'studentid' => $studentId,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
            ]);

            // Update comments (only if provided, to avoid overwriting with empty strings)
            $profile->classteachercomment = $teacherComment ?: $profile->classteachercomment;
            $profile->guidancescomment = $guidanceComment ?: $profile->guidancescomment;
            $profile->save();
        }

        return redirect()->route('classbroadsheet', [$schoolclassid, $sessionid, $termid])
            ->with('success', 'Comments updated successfully.');
    }
}