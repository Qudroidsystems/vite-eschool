<?php

namespace App\Http\Controllers;

use App\Exports\RecordsheetExport;
use App\Imports\ScoresheetImport;
use App\Models\Broadsheet;
use App\Models\PromotionStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class MyScoreSheetController extends Controller
{
    /**
     * Display a listing of the resource.s
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function subjectscoresheet($schoolclassid, $subjectclassid, $staffid, $termid, $sessionid)
    {

        $current = 'Current';

       // echo $schoolclassid;
        $Broadsheets = Broadsheet::where('subjectclassid', $subjectclassid)
            ->where('broadsheet.staffid', $staffid)
            ->where('broadsheet.termid', $termid)
            ->where('broadsheet.session', $sessionid)
            ->leftJoin('subjectclass', 'subjectclass.id', '=', 'broadsheet.subjectclassid')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('classcategories', 'classcategories.id', '=', 'schoolclass.classcategoryid')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->where('schoolsession.status', '=', $current)
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet.studentId')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->get(['broadsheet.id as id', 'studentRegistration.admissionNO as admissionno','broadsheet.studentId as studentId', 'studentRegistration.firstname as fname', 'studentRegistration.lastname as lname',
                'subject.subject as subject', 'subject.subject_code as subjectcode', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm',
                'schoolterm.term as term', 'schoolsession.session as session', 'subjectclass.id as subjectclid', 'broadsheet.staffid as staffid',
                'broadsheet.termid as termid', 'broadsheet.session as sessionid', 'classcategories.ca2score as ca2',
                'classcategories.ca1score as ca1', 'classcategories.examscore as exam',
                'studentpicture.picture as picture', 'broadsheet.ca1 as ca1', 'broadsheet.ca2 as ca2', 'broadsheet.ca3 as ca3', 'broadsheet.exam as exam', 'broadsheet.total  as total', 'broadsheet.grade as grade',
                'broadsheet.subjectpositionclass as position', 'broadsheet.remark as remark'])->sortBy('admissionno');

        if ($Broadsheets) {
            foreach ($Broadsheets as $r) {
                $r->Broadsheetid;
                $r->subjectclid;
                $r->staffid;
                $r->termid;
                $r->sessionid;
            }

            //get minimun score....
            $classmin = Broadsheet::where('subjectclassid', $subjectclassid)
                ->where('broadsheet.staffid', $staffid)
                ->where('broadsheet.termid', $termid)
                ->where('broadsheet.session', $sessionid)
                ->min('total');

            // echo (float)$classmin;

            Broadsheet::where('subjectclassid', $subjectclassid)
                ->where('broadsheet.staffid', $staffid)
                ->where('broadsheet.termid', $termid)
                ->where('broadsheet.session', $sessionid)
                ->update(['cmin' => $classmin]);

            //get maximun score....
            $classmax = Broadsheet::where('subjectclassid', $subjectclassid)
                ->where('broadsheet.staffid', $staffid)
                ->where('broadsheet.termid', $termid)
                ->where('broadsheet.session', $sessionid)
                ->max('total');
            Broadsheet::where('subjectclassid', $subjectclassid)
                ->where('broadsheet.staffid', $staffid)
                ->where('broadsheet.termid', $termid)
                ->where('broadsheet.session', $sessionid)
                ->update(['cmax' => $classmax]);

            //get average score...
            $classavg = ($classmin + $classmax) / 2;
            Broadsheet::where('subjectclassid', $subjectclassid)
                ->where('broadsheet.staffid', $staffid)
                ->where('broadsheet.termid', $termid)
                ->where('broadsheet.session', $sessionid)
                ->update(['avg' => round($classavg, 1)]);

            //calculating position in subject per class...
            $rank = 0;
            $last_score = false;
            $rows = 0;
            $position = '';

            $classpos = Broadsheet::select('*')
                ->where('subjectclassid', $subjectclassid)
                ->where('broadsheet.staffid', $staffid)
                ->where('broadsheet.termid', $termid)
                ->where('broadsheet.session', $sessionid)
                       // ->orderBy('studentId','DESC')
                ->orderBy('total', 'DESC')
                ->get();

            foreach ($classpos as $row) {
                $studentId = $row->studentId;
                $rows++;
                if ($last_score != $row->total) {
                    $last_score = $row->total;
                    $rank = $rows;
                }
                if ($rank == 1) {
                    $position = 'st';
                } elseif ($rank == 2) {
                    $position = 'nd';
                } elseif ($rank == 3) {
                    $position = 'rd';
                } else {
                    $position = 'th';
                }

                $rank_pos = $rank.$position;
                //  echo "<br>";
                // echo $row->studentId." "."$row->total"." ".gettype(strval($rank_pos))." "."<br>";

                Broadsheet::where('subjectclassid', $subjectclassid)
                    ->where('broadsheet.studentId', $studentId)
                    ->where('broadsheet.staffid', $staffid)
                    ->where('broadsheet.termid', $termid)
                    ->where('broadsheet.session', $sessionid)
                    ->update(['subjectpositionclass' => $rank_pos]);

            }

            //calculating position  per class...
            $rank2 = 0;
            $last_score2 = false;
            $rows2 = 0;
            $position2 = '';

            $pos = PromotionStatus::where('schoolclassid', $schoolclassid)
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'promotionStatus.schoolclassid')
                ->where('termid', $termid)
                ->where('sessionid', $sessionid)
                ->orderBy('subjectstotalscores', 'DESC')
                ->get();

            foreach ($pos as $row2) {
                $studentId2 = $row2->studentId;
                $rows2++;
                if ($last_score2 != $row2->subjectstotalscores) {
                    $last_score2 = $row2->subjectstotalscores;
                    $rank2 = $rows2;
                }
                if ($rank2 == 1) {
                    $position2 = 'st';
                } elseif ($rank2 == 2) {
                    $position2 = 'nd';
                } elseif ($rank2 == 3) {
                    $position2 = 'rd';
                } else {
                    $position2 = 'th';
                }

                $rank_pos2 = $rank2.$position2;
                // echo "<br>";
                // echo $row2->studentId2." "."$row2->subjectstotalscores"." ".gettype(strval($rank_pos2))." "."<br>";

                PromotionStatus::where('studentId', $studentId2)
                    ->leftJoin('schoolclass', 'schoolclass.id', '=', 'promotionStatus.schoolclassid')
                    ->where('schoolclassid', $schoolclassid)
                    ->where('termid', $termid)
                    ->where('sessionid', $sessionid)
                    ->update(['position' => $rank_pos2]);

            }

            Session::put('Broadsheetid', $r->Broadsheetid);
            Session::put('schoolclassid', $schoolclassid);
            Session::put('subjectclassid', $r->subjectclid);
            Session::put('staffid', $r->staffid);
            Session::put('termid', $r->termid);
            Session::put('sessionid', $r->sessionid);

        } else {

            echo 'ERROR 112O';
        }

        return view('subjectscoresheet.index')
            ->with('broadsheets', $Broadsheets)
            ->with('s_Broadsheetid', Session::put('broadsheetid', $r->id))
            ->with('s_subjectclassid', Session::put('subjectclassid', $r->subjectclid))
            ->with('s_staffid', Session::put('staffid', $r->staffid))
            ->with('s_termid', Session::put('termid', $r->termid))
            ->with('s_sessionid', Session::put('sessionid', $r->sessionid));

    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        echo 'scoresheet here...';
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

        $current = 'Current';
        $Broadsheets = Broadsheet::where('broadsheet.id', $id)
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet.studentId')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('subjectclass', 'subjectclass.id', '=', 'broadsheet.subjectclassid')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('classcategories', 'classcategories.id', '=', 'schoolclass.classcategoryid')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->where('schoolsession.status', '=', $current)
            ->get(['broadsheet.id as bid', 'studentRegistration.admissionNO as admissionno', 'studentRegistration.tittle as title',
                'studentRegistration.firstname as fname', 'studentRegistration.lastname as lname',
                'studentpicture.picture as picture', 'broadsheet.ca1 as ca1', 'broadsheet.ca2 as ca2', 'broadsheet.ca3 as ca3', 'broadsheet.exam as exam',
                'broadsheet.bf as bf','broadsheet.cum as cum',
                'broadsheet.total  as total', 'broadsheet.grade as grade', 'schoolterm.term as term', 'schoolsession.session as session',
                'subject.subject as subject', 'subject.subject_code as subjectcode', 'schoolclass.schoolclass as schoolclass',
                'schoolarm.arm as arm', 'broadsheet.subjectpositionclass as position', 'broadsheet.remark as remark',
                'classcategories.ca2score as cat_ca2', 'classcategories.ca1score as cat_ca1', 'classcategories.ca3score as cat_ca3','classcategories.examscore as cat_exam', ])->sortBy('fname');

        if ($Broadsheets) {
            $Broadsheet = Broadsheet::find($id);
            $studentid = $Broadsheet->studentId;
            $staffid = $Broadsheet->staffid;
            $subjectclassid = $Broadsheet->subjectclassid;
            $termid = $Broadsheet->termid;
            $sessionid = $Broadsheet->session;
            $ca1 = $Broadsheet->ca1;
            $ca2 = $Broadsheet->ca2;
            $ca3 = $Broadsheet->ca3;
            $tca = $Broadsheet->tca;
            $exam = $Broadsheet->exam;
            $bf = $Broadsheet->bf;
            $cum = $Broadsheet->cum;
            $total = $Broadsheet->total;
            $remark = $Broadsheet->remark;
            $grade = $Broadsheet->grade;

        } else {
            echo 'oppss..';
        }

        //get total score per subject per student and update...
        $all_subjects_total_score = Broadsheet::where('studentId', $studentid)
            ->where('staffid', $staffid)
            ->where('termid', $termid)
            ->where('session', $sessionid)->get();

        $search = Broadsheet::where('studentId', $studentid)
            ->where('termid', $termid)
            ->where('session', $sessionid)->sum('total');

        Broadsheet::where('studentId', $studentid)
            ->where('termid', $termid)
            ->where('session', $sessionid)
            ->update(['allsubjectstotalscores' => $search]);

        PromotionStatus::where('studentId', $studentid)
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'promotionStatus.schoolclassid')
            ->join('subjectclass', function ($join) {
                $join->on('schoolclass.id', '=', 'subjectclass.schoolclassid');
            })
            ->where('promotionStatus.termid', $termid)
            ->where('promotionStatus.sessionid', $sessionid)
            ->update(['subjectstotalscores' => $search]);

        return view('subjectscoresheet.edit')->with('broadsheets', $Broadsheets)
            ->with('session', Session::get('session'))
            ->with('studentid', $studentid)
            ->with('ca1', $ca1)
            ->with('ca2', $ca2)
            ->with('ca3', $ca3)
            ->with('tca', $tca)
            ->with('exam', $exam)
            ->with('bf', $bf)
            ->with('cum', $cum)
            ->with('total', $total)
            ->with('grade', $grade)
            ->with('remark', $remark);
    }

    /**
     * Update the specified resource in storage.
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {


        $Broadsheet = Broadsheet::find($id);
        $bf = 0;
        $term = "";
        $term_bf =  Broadsheet::where('broadsheet.id', $id)
        // ->where('subjectclassid', Session::get('subjectclassid'))
        // ->where('broadsheet.termid', Session::get('termid'))
        // ->where('broadsheet.session', Session::get('sessionid'))
        //->select('bf')
        // ->select('termid')
        ->get();

        foreach ($term_bf as $key => $value) {
            $term =  $value->termid;
            $bf = $value->bf;
        }

        if ($term == 1){
             $bf = 0;
        }elseif ($term == 2) {
            # code...
        }elseif ($term == 3) {
            # code...
        }

        echo $term;
        //  // Update specific fields from the request
        // $broadsheet->ca1 = $request->ca1;
        // $broadsheet->ca2 = $request->ca2;
        // $broadsheet->ca3 = $request->ca3;
        // $broadsheet->exam = $request->exam;

        // // Save the updated model
        // $broadsheet->save();

        // $Broadsheet->update($request->all());
        // $this->subjectscoresheet(Session::get('schoolclassid'), Session::get('subjectclassid'), Session::get('staffid'), Session::get('termid'), Session::get('sessionid'));

        // return redirect()->back()->with('success', 'Score Editted  Successfully!!');

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

    public function export()
    {
        $current = 'Current';
        $subjectclassid = Session::get('subjectclassid');
        $staffid = Session::get('staffid');
        $termid = Session::get('termid');
        $sessionid = Session::get('sessionid');

        $Broadsheets = Broadsheet::where('subjectclassid', $subjectclassid)
            ->where('broadsheet.staffid', $staffid)
            ->where('broadsheet.termid', $termid)
            ->where('broadsheet.session', $sessionid)
            ->leftJoin('subjectclass', 'subjectclass.id', '=', 'broadsheet.subjectclassid')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->where('schoolsession.status', '=', $current)
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet.studentId')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->get(['broadsheet.id as bid', 'subject.subject as subject', 'subject.subject_code as subjectcode', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm',
                'schoolterm.term as term', 'schoolsession.session as session', 'users.id as userid', 'users.name as staffname'])->sortBy('fname');

        foreach ($Broadsheets as $r) {
            $r->schoolclass;
            $r->arm;
            $r->staffname;
            $r->term;
            $r->session;
            $r->subject;
            $r->subjecode;
        }
        $filename = $r->staffname.'_'.$r->subject.$r->subjeccode.'_'.$r->schoolclass.$r->arm.'_'.$r->term;

        return Excel::download(new RecordsheetExport, $filename.'.xlsx');
        // return redirect()->back()->with('success', 'Score Sheet downloaded successfully');

    }

    public function importform($schoolclassid, $subjectclassid, $staffid, $termid, $sessionid)
    {

        // echo $subjectclassid. $staffid .$termid .$sessionid;
        return view('subjectscoresheet.importsheet')
                        //    ->with('Broadsheetid',$Broadsheetid)
            ->with('schoolclassid', $schoolclassid)
            ->with('subjectclassid', $subjectclassid)
            ->with('staffid', $staffid)
            ->with('termid', $termid)
            ->with('sessionid', $sessionid);

    }

    public function importsheet(Request $request)
    {
        // $Broadsheetid  = $request->Broadsheetid;

        $this->validate($request, [
            'file' => 'required',
        ]);
        $schoolclassid = $request->input('schoolclassid');
        $subjectclassid = $request->input('subjectclassid');
        $staffid = $request->input('staffid');
        $termid = $request->input('termid');
        $sessionid = $request->input('sessionid');
        $file = $request->file('file');
        Excel::import(new ScoresheetImport(), $file);
        $this->subjectscoresheetpos($schoolclassid, $subjectclassid, $staffid, $termid, $sessionid);

        return redirect()->back()->with('success', 'Batch File Imported  Successfully');

    }

    public function subjectscoresheetpos($schoolclassid, $subjectclassid, $staffid, $termid, $sessionid)
    {

        $current = 'Current';

        // echo $schoolclassid;
        $Broadsheets = Broadsheet::where('subjectclassid', $subjectclassid)
            ->where('broadsheet.staffid', $staffid)
            ->where('broadsheet.termid', $termid)
            ->where('broadsheet.session', $sessionid)
            ->leftJoin('subjectclass', 'subjectclass.id', '=', 'broadsheet.subjectclassid')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->where('schoolsession.status', '=', $current)
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet.studentId')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->get(['broadsheet.id as id', 'studentRegistration.admissionNO as admissionno', 'studentRegistration.firstname as fname', 'studentRegistration.lastname as lname',
                'subject.subject as subject', 'subject.subject_code as subjectcode', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm',
                'schoolterm.term as term', 'schoolsession.session as session', 'subjectclass.id as subjectclid', 'broadsheet.staffid as staffid',
                'broadsheet.termid as termid', 'broadsheet.session as sessionid',
                'studentpicture.picture as picture', 'broadsheet.ca1 as ca1', 'broadsheet.ca2 as ca2', 'broadsheet.ca3 as ca3', 'broadsheet.exam as exam',
                'broadsheet.total  as total', 'broadsheet.grade as grade',
                'broadsheet.subjectpositionclass as position', 'broadsheet.remark as remark'])->sortBy('admissionno');

        //calculating position in subject per class...
        $rank = 0;
        $last_score = false;
        $rows = 0;
        $position = '';

        $classpos = Broadsheet::select('total')
            ->where('subjectclassid', $subjectclassid)
            ->where('broadsheet.staffid', $staffid)
            ->where('broadsheet.termid', $termid)
            ->where('broadsheet.session', $sessionid)
            ->orderBy('total', 'DESC')->get();

        foreach ($classpos as $row) {

            $studentId = $row->studentId;
            $rows++;
            if ($last_score != $row->total) {

                $last_score = $row->total;
                $rank = $rows;

            }
            if ($rank == 1) {
                $position = 'st';

            } elseif ($rank == 2) {
                $position = 'nd';

            } elseif ($rank == 3) {
                $position = 'rd';

            } else {
                $position = 'th';
            }

            $rank_pos = $rank.$position;

            //echo $studentId." "." ".$row->total.$rank_pos." "."<br>";
            Broadsheet::where('subjectclassid', $subjectclassid)
                ->where('broadsheet.studentId', $studentId)
                ->where('broadsheet.staffid', $staffid)
                ->where('broadsheet.termid', $termid)
                ->where('broadsheet.session', $sessionid)
                ->update(['subjectpositionclass' => $rank_pos]);

        }

        //calculating position  per class...
        $rank2 = 0;
        $last_score2 = false;
        $rows2 = 0;
        $position2 = '';

        $pos = PromotionStatus::where('schoolclassid', $schoolclassid)
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'promotionStatus.schoolclassid')
            ->where('termid', $termid)
            ->where('sessionid', $sessionid)
            ->orderBy('subjectstotalscores', 'DESC')
            ->get();

        foreach ($pos as $row2) {
            $studentId2 = $row2->studentId;
            $rows2++;
            if ($last_score2 != $row2->subjectstotalscores) {
                $last_score2 = $row2->subjectstotalscores;
                $rank2 = $rows2;
            }
            if ($rank2 == 1) {
                $position2 = 'st';
            } elseif ($rank2 == 2) {
                $position2 = 'nd';
            } elseif ($rank2 == 3) {
                $position2 = 'rd';
            } else {
                $position2 = 'th';
            }

            $rank_pos2 = $rank2.$position2;
            // echo "<br>";
            // echo $row2->studentId2." "."$row2->subjectstotalscores"." ".gettype(strval($rank_pos2))." "."<br>";

            PromotionStatus::where('studentId', $studentId2)
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'promotionStatus.schoolclassid')
                ->where('schoolclassid', $schoolclassid)
                ->where('termid', $termid)
                ->where('sessionid', $sessionid)
                ->update(['position' => $rank_pos2]);

        }

    }
}
