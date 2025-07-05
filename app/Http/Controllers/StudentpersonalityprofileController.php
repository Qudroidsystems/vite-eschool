<?php

namespace App\Http\Controllers;

use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Schoolclass;
use App\Models\Student;
use App\Models\Studentpersonalityprofile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentpersonalityprofileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function studentpersonalityprofile($id, $schoolclassid, $sessionid, $termid)
    {
        $pagetitle = "Student Personality Profile";

        $students = Student::where('studentRegistration.id', $id)
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->get([
                'studentRegistration.id as id',
                'studentRegistration.admissionNo as admissionNo',
                'studentRegistration.firstname as fname',
                'studentRegistration.home_address as homeaddress',
                'studentRegistration.lastname as lastname',
                'studentRegistration.othername as othername',
                'studentRegistration.dateofbirth as dateofbirth',
                'studentRegistration.gender as gender',
                'studentRegistration.updated_at as updated_at',
                'studentpicture.picture as picture'
            ]);

        $studentpp = Studentpersonalityprofile::where('studentid', $id)
            ->where('schoolclassid', $schoolclassid)
            ->where('sessionid', $sessionid)
            ->where('termid', $termid)
            ->get();

        $schoolclass = Schoolclass::where('id', $schoolclassid)->first(['schoolclass', 'arm']);
        $schoolterm = Schoolterm::where('id', $termid)->value('term') ?? 'N/A';
        $schoolsession = Schoolsession::where('id', $sessionid)->value('session') ?? 'N/A';

        return view('studentpersonalityprofile.edit')
            ->with('students', $students)
            ->with('studentpp', $studentpp)
            ->with('staffid', Auth::user()->id)
            ->with('studentid', $id)
            ->with('schoolclassid', $schoolclassid)
            ->with('sessionid', $sessionid)
            ->with('termid', $termid)
            ->with('pagetitle', $pagetitle)
            ->with('schoolclass', $schoolclass)
            ->with('schoolterm', $schoolterm)
            ->with('schoolsession', $schoolsession);
    }

    public function save(Request $request)
    {
        $request->validate([
            'studentid' => 'required|exists:studentregistration,id',
            'schoolclassid' => 'required|exists:schoolclass,id',
            'termid' => 'required|exists:schoolterm,id',
            'sessionid' => 'required|exists:schoolsession,id',
            'staffid' => 'nullable|exists:users,id',
            'punctuality' => 'nullable|in:Excellent,Very Good,Good,Fairly Good,Poor',
            'neatness' => 'nullable|in:Excellent,Very Good,Good,Fairly Good,Poor',
            'leadership' => 'nullable|in:Excellent,Very Good,Good,Fairly Good,Poor',
            'attitude' => 'nullable|in:Excellent,Very Good,Good,Fairly Good,Poor',
            'reading' => 'nullable|in:Excellent,Very Good,Good,Fairly Good,Poor',
            'honesty' => 'nullable|in:Excellent,Very Good,Good,Fairly Good,Poor',
            'cooperation' => 'nullable|in:Excellent,Very Good,Good,Fairly Good,Poor',
            'selfcontrol' => 'nullable|in:Excellent,Very Good,Good,Fairly Good,Poor',
            'politeness' => 'nullable|in:Excellent,Very Good,Good,Fairly Good,Poor',
            'physicalhealth' => 'nullable|in:Excellent,Very Good,Good,Fairly Good,Poor',
            'stability' => 'nullable|in:Excellent,Very Good,Good,Fairly Good,Poor',
            'gamesandsports' => 'nullable|in:Excellent,Very Good,Good,Fairly Good,Poor',
            'attendance' => 'nullable|integer|min:0|max:100',
            'attentiveness_in_class' => 'nullable|in:Excellent,Very Good,Good,Fairly Good,Poor',
            'class_participation' => 'nullable|in:Excellent,Very Good,Good,Fairly Good,Poor',
            'relationship_with_others' => 'nullable|in:Excellent,Very Good,Good,Fairly Good,Poor',
            'doing_assignment' => 'nullable|in:Excellent,Very Good,Good,Fairly Good,Poor',
            'writing_skill' => 'nullable|in:Excellent,Very Good,Good,Fairly Good,Poor',
            'reading_skill' => 'nullable|in:Excellent,Very Good,Good,Fairly Good,Poor',
            'spoken_english_communication' => 'nullable|in:Excellent,Very Good,Good,Fairly Good,Poor',
            'hand_writing' => 'nullable|in:Excellent,Very Good,Good,Fairly Good,Poor',
            'club' => 'nullable|in:Excellent,Very Good,Good,Fairly Good,Poor',
            'music' => 'nullable|in:Excellent,Very Good,Good,Fairly Good,Poor',
            // 'principalscomment' => 'nullable|string|max:1000',
            // 'classteacherscomment' => 'nullable|string|max:1000',
        ]);

        $studentpp = Studentpersonalityprofile::where('studentid', $request->studentid)
            ->where('schoolclassid', $request->schoolclassid)
            ->where('termid', $request->termid)
            ->where('sessionid', $request->sessionid)
            ->first();

        if ($studentpp) {
            try {
                $input = $request->only([
                    'studentid',
                    'staffid',
                    'schoolclassid',
                    'termid',
                    'sessionid',
                    'punctuality',
                    'neatness',
                    'leadership',
                    'attitude',
                    'reading',
                    'honesty',
                    'cooperation',
                    'selfcontrol',
                    'politeness',
                    'physicalhealth',
                    'stability',
                    'gamesandsports',
                    'attendance',
                    'attentiveness_in_class',
                    'class_participation',
                    'relationship_with_others',
                    'doing_assignment',
                    'writing_skill',
                    'reading_skill',
                    'spoken_english_communication',
                    'hand_writing',
                    'club',
                    'music',
                    // 'principalscomment',
                    // 'classteacherscomment',
                ]);
                $studentpp->update($input);
                return redirect()->back()->with('success', 'Student Personality Profile Updated successfully');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Failed to update profile: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'Student Personality Profile not found');
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
}