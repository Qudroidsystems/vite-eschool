<?php

namespace App\Http\Controllers;

use App\Exports\RecordsheetExport;
use App\Imports\ScoresheetImport;
use App\Models\Broadsheet;
use App\Models\Broadsheets;
use App\Models\BroadsheetsMock;
use App\Models\PromotionStatus;
use App\Exports\MarksSheetExport;
use App\Models\SchoolInformation;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MyScoreSheetController extends Controller
{
    /**
     * Display the scoresheet listing.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $pagetitle = 'My Scoresheets';
        $broadsheets = collect(); // Initialize empty collection

        // Log session variables for debugging
        Log::info('Index session:', $request->session()->all());

        // Handle initial page load with query parameters
        if (!$request->ajax()) {
            $termId = $request->query('termid', 'ALL');
            $sessionId = $request->query('sessionid', 'ALL');

            if ($termId !== 'ALL' && $sessionId !== 'ALL') {
                $broadsheets = $this->getBroadsheets($request->user()->id, $termId, $sessionId);
                Log::info('Index broadsheets count:', ['count' => $broadsheets->count()]);
            }
        }

        // Handle AJAX request
        if ($request->ajax()) {
            $termId = $request->input('termid', 'ALL');
            $sessionId = $request->input('sessionid', 'ALL');

            if ($termId === 'ALL' || $sessionId === 'ALL') {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select both term and session.',
                ], 422);
            }

            $broadsheets = $this->getBroadsheets($request->user()->id, $termId, $sessionId);

            return response()->json([
                'success' => true,
                'data' => [
                    'broadsheets' => $broadsheets,
                ],
            ]);
        }

        return view('subjectscoresheet.index', compact('pagetitle', 'broadsheets'));
    }

    /**
     * Display the scoresheet for a specific subject.
     *
     * @return \Illuminate\View\View
     */
    public function subjectscoresheet($schoolclassid, $subjectclassid, $staffid, $termid, $sessionid)
    {
        // Log parameters for debugging
        Log::info('Subjectscoresheet parameters:', compact('schoolclassid', 'subjectclassid', 'staffid', 'termid', 'sessionid'));

        // Store parameters in session for other methods to use
        session([
            'schoolclass_id' => $schoolclassid,
            'subjectclass_id' => $subjectclassid,
            'staff_id' => $staffid,
            'term_id' => $termid,
            'session_id' => $sessionid,
        ]);

        $broadsheets = $this->getBroadsheets($staffid, $termid, $sessionid, $schoolclassid, $subjectclassid);

        // Log broadsheets count
        Log::info('Subjectscoresheet broadsheets count:', ['count' => $broadsheets->count()]);

        // Set default page title
        $pagetitle = 'Subject Scoresheet';

        if ($broadsheets->isNotEmpty()) {
            $this->updateClassMetrics($subjectclassid, $staffid, $termid, $sessionid);
            $this->updateSubjectPositions($subjectclassid, $staffid, $termid, $sessionid);
            $this->updateClassPositions($schoolclassid, $termid, $sessionid);

            // Build dynamic page title from the first broadsheet
            $firstBroadsheet = $broadsheets->first();
            $pagetitle = sprintf(
                'Scoresheet for %s (%s) - %s %s - %s %s',
                $firstBroadsheet->subject,
                $firstBroadsheet->subject_code,
                $firstBroadsheet->schoolclass,
                $firstBroadsheet->arm,
                $firstBroadsheet->term,
                $firstBroadsheet->session
            );
        }

        return view('subjectscoresheet.index', compact('broadsheets', 'pagetitle'));
    }

    /**
     * Display the mock scoresheet for a specific subject.
     *
     * @return \Illuminate\View\View
     */
    public function subjectscoresheetMock($schoolclassid, $subjectclassid, $staffid, $termid, $sessionid)
    {
        Log::info('SubjectscoresheetMock parameters:', compact('schoolclassid', 'subjectclassid', 'staffid', 'termid', 'sessionid'));
    
        session([
            'schoolclass_id' => $schoolclassid,
            'subjectclass_id' => $subjectclassid,
            'staff_id' => $staffid,
            'term_id' => $termid,
            'session_id' => $sessionid,
        ]);
    
        $broadsheets = $this->getMockBroadsheets($staffid, $termid, $sessionid, $schoolclassid, $subjectclassid);
    
        Log::info('SubjectscoresheetMock broadsheets count:', ['count' => $broadsheets->count()]);
    
        $pagetitle = 'Mock Subject Scoresheet';
    
        if ($broadsheets->isNotEmpty()) {
            $this->updateClassMetricsMock($subjectclassid, $staffid, $termid, $sessionid);
            $this->updateSubjectPositionsMock($subjectclassid, $staffid, $termid, $sessionid);
            $this->updateClassPositions($schoolclassid, $termid, $sessionid);
    
            $firstBroadsheet = $broadsheets->first();
            $pagetitle = sprintf(
                'Mock Scoresheet for %s (%s) - %s %s - %s %s',
                $firstBroadsheet->subject,
                $firstBroadsheet->subject_code,
                $firstBroadsheet->schoolclass,
                $firstBroadsheet->arm,
                $firstBroadsheet->term,
                $firstBroadsheet->session
            );
        }
    
        return view('subjectscoresheet.subjectscoresheet-mock', compact('broadsheets', 'pagetitle'));
    }

    /**
     * Get broadsheets with optimized query.
     * FIXED: This method had incorrect table joins and session filtering
     *
     * @param int $staffId
     * @param int $termId
     * @param int $sessionId
     * @param int|null $schoolClassId
     * @param int|null $subjectClassId
     * @return \Illuminate\Support\Collection
     */
    protected function getBroadsheets($staffId, $termId, $sessionId, $schoolClassId = null, $subjectClassId = null)
    {
        $query = Broadsheets::query()
            ->where('broadsheets.staff_id', $staffId)
            ->where('broadsheets.term_id', $termId)
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'broadsheet_records.schoolclass_id')
            ->leftJoin('classcategories', 'classcategories.id', '=', 'schoolclass.classcategoryid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('subjectclass', 'subjectclass.id', '=', 'broadsheets.subjectclass_id')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'broadsheets.term_id')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'broadsheet_records.session_id')
            // FIXED: Filter by session_id from broadsheet_records table, not session status
            ->where('broadsheet_records.session_id', $sessionId);

        if ($schoolClassId) {
            $query->where('schoolclass.id', $schoolClassId);
        }
        if ($subjectClassId) {
            $query->where('subjectclass.id', $subjectClassId);
        }

        DB::enableQueryLog();
        $results = $query->get([
            'broadsheets.id',
            'studentRegistration.admissionNO as admissionno',
            'broadsheet_records.student_id as student_id',
            'studentRegistration.firstname as fname',
            'studentRegistration.lastname as lname',
            'subject.subject as subject',
            'subject.subject_code as subject_code',
            'schoolclass.schoolclass',
            'schoolarm.id as arm',
            'schoolterm.term',
            'schoolsession.session',
            'subjectclass.id as subjectclid',
            'broadsheets.staff_id',
            'broadsheets.term_id',
            'broadsheet_records.session_id as sessionid',
            'classcategories.ca1score as ca1score',
            'classcategories.ca2score as ca2score',
            'classcategories.examscore as examscore',
            'studentpicture.picture',
            'broadsheets.ca1',
            'broadsheets.ca2',
            'broadsheets.ca3',
            'broadsheets.exam',
            'broadsheets.total',
            'broadsheets.grade',
            'broadsheets.subject_position_class as position',
            'broadsheets.remark',
        ])->sortBy('admissionno');

        // $queryLog = DB::getQueryLog();
        // Log::info('getBroadsheets SQL:', $queryLog);
        // Log::info('getBroadsheets Results Count:', ['.$results->count().']');
        
        return $results;
    }

    /**
     * Get mock broadsheets with optimized query structure.
     *
     * @param int $staffId
     * @param int $termId
     * @param int $sessionId
     * @param int|null $schoolClassId
     * @param int|null $subjectClassId
     * @return \Illuminate\Support\Collection
     */
   
    
    protected function getMockBroadsheets($staffId, $termId, $sessionId, $schoolClassId = null, $subjectClassId = null)
    {
        $query = BroadsheetsMock::query()
            ->where('broadsheetmock.staff_id', $staffId)
            ->where('broadsheetmock.term_id', $termId)
            ->leftJoin('broadsheet_records_mock', 'broadsheet_records_mock.id', '=', 'broadsheetmock.broadsheet_records_mock_id')
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records_mock.student_id')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records_mock.subject_id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'broadsheet_records_mock.schoolclass_id')
            ->leftJoin('classcategories', 'classcategories.id', '=', 'schoolclass.classcategoryid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('subjectclass', 'subjectclass.id', '=', 'broadsheetmock.subjectclass_id')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'broadsheetmock.term_id')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'broadsheet_records_mock.session_id')
            ->where('broadsheet_records_mock.session_id', $sessionId);
    
        if ($schoolClassId) {
            $query->where('schoolclass.id', $schoolClassId);
        }
        if ($subjectClassId) {
            $query->where('subjectclass.id', $subjectClassId);
        }
    
        DB::enableQueryLog();
        $results = $query->get([
            'broadsheetmock.id',
            'studentRegistration.admissionNO as admissionno',
            'broadsheet_records_mock.student_id as studentId',
            'studentRegistration.firstname as fname',
            'studentRegistration.lastname as lname',
            'subject.subject as subject',
            'subject.subject_code as subject_code',
            'schoolclass.schoolclass',
            'schoolarm.arm',
            'schoolterm.term',
            'schoolsession.session',
            'subjectclass.id as subjectclid',
            'broadsheetmock.staff_id',
            'broadsheetmock.term_id',
            'broadsheet_records_mock.session_id as sessionid',
            'classcategories.examscore as examscore',
            'studentpicture.picture',
            'broadsheetmock.exam',
            'broadsheetmock.total',
            'broadsheetmock.grade',
            'broadsheetmock.subject_position_class as position',
            'broadsheetmock.remark',
            'broadsheet_records_mock.schoolclass_id as schoolclassid',
        ])->sortBy('admissionno');
    
        Log::info('getMockBroadsheets SQL:', DB::getQueryLog());
        Log::info('getMockBroadsheets Results Count:', ['count' => $results->count()]);
    
        return $results;
    }
    

    /**
     * Update class metrics (min, max, avg).
     * FIXED: Changed from Broadsheet to Broadsheets model
     */
    protected function updateClassMetrics($subjectclassid, $staffid, $termid, $sessionid)
    {
        $classMin = Broadsheets::where('subjectclass_id', $subjectclassid)
            ->where('staff_id', $staffid)
            ->where('term_id', $termid)
            ->min('total');

        $classMax = Broadsheets::where('subjectclass_id', $subjectclassid)
            ->where('staff_id', $staffid)
            ->where('term_id', $termid)
            ->max('total');

        $classAvg = $classMin && $classMax ? ($classMin + $classMax) / 2 : 0;

        Broadsheets::where('subjectclass_id', $subjectclassid)
            ->where('staff_id', $staffid)
            ->where('term_id', $termid)
            ->update([
                'cmin' => $classMin ?? 0,
                'cmax' => $classMax ?? 0,
                'avg' => round($classAvg, 1),
            ]);
    }

    /**
     * Update subject positions.
     * FIXED: Changed from Broadsheet to Broadsheets model
     */
    protected function updateSubjectPositions($subjectclassid, $staffid, $termid, $sessionid)
    {
        $rank = 0;
        $lastScore = null;
        $rows = 0;

        $classPos = Broadsheets::where('subjectclass_id', $subjectclassid)
            ->where('staff_id', $staffid)
            ->where('term_id', $termid)
            ->orderBy('total', 'DESC')
            ->get();

        foreach ($classPos as $row) {
            $rows++;
            if ($lastScore !== $row->total) {
                $lastScore = $row->total;
                $rank = $rows;
            }
            $position = match ($rank) {
                1 => 'st',
                2 => 'nd',
                3 => 'rd',
                default => 'th',
            };
            $rankPos = $rank . $position;

            // Get the broadsheet_record to find student_id
            $broadsheetRecord = DB::table('broadsheet_records')
                ->where('id', $row->broadsheet_record_id)
                ->first();

            if ($broadsheetRecord) {
                Broadsheets::where('subjectclass_id', $subjectclassid)
                    ->where('broadsheet_record_id', $row->broadsheet_record_id)
                    ->where('staff_id', $staffid)
                    ->where('term_id', $termid)
                    ->update(['subject_position_class' => $rankPos]);
            }
        }
    }

    /**
     * Update class positions.
     */
    protected function updateClassPositions($schoolclassid, $termid, $sessionid)
    {
        $rank = 0;
        $lastScore = null;
        $rows = 0;

        $pos = PromotionStatus::where('schoolclassid', $schoolclassid)
            ->where('termid', $termid)
            ->where('sessionid', $sessionid)
            ->orderBy('subjectstotalscores', 'DESC')
            ->get();

        foreach ($pos as $row) {
            $rows++;
            if ($lastScore !== $row->subjects_total_scores) {
                $lastScore = $row->subjects_total_scores;
                $rank = $rows;
            }
            $position = match ($rank) {
                1 => 'st',
                2 => 'nd',
                3 => 'rd',
                default => 'th',
            };
            $rankPos = $rank . $position;

            PromotionStatus::where('studentId', $row->student_id)
                ->where('schoolclassid', $schoolclassid)
                ->where('termid', $termid)
                ->where('sessionid', $sessionid)
                ->update(['position' => $rankPos]);
        }
    }

    /**
     * Show the form for editing a scoresheet.
     * FIXED: Changed from Broadsheet to Broadsheets model
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $broadsheet = Broadsheets::where('broadsheets.id', $id)
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('subjectclass', 'subjectclass.id', '=', 'broadsheets.subjectclass_id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'broadsheet_records.schoolclass_id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('classcategories', 'classcategories.id', '=', 'schoolclass.classcategoryid')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'broadsheets.term_id')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'broadsheet_records.session_id')
            ->first([
                'broadsheets.id as bid',
                'studentRegistration.admissionNO as admissionno',
                'studentRegistration.tittle as title',
                'studentRegistration.firstname as fname',
                'studentRegistration.lastname as lname',
                'studentpicture.picture',
                'broadsheets.ca1',
                'broadsheets.ca2',
                'broadsheets.ca3',
                'broadsheets.exam',
                'broadsheets.total',
                'broadsheets.grade',
                'schoolterm.term',
                'schoolsession.session',
                'subject.subject',
                'subject.subject_code as subject_code',
                'schoolclass.schoolclass',
                'schoolarm.arm',
                'broadsheets.subject_position_class as position',
                'broadsheets.remark',
                'classcategories.ca1score as cat_ca1',
                'classcategories.ca2score as cat_ca2',
                'classcategories.ca3score as cat_ca3',
                'classcategories.examscore as cat_exam',
                'broadsheet_records.student_id',
                'broadsheets.staff_id',
                'broadsheets.term_id',
                'broadsheet_records.session_id as sessionid',
            ]);

        if (!$broadsheet) {
            $pagetitle = 'Edit Scoresheet - Not Found';
            return view('subjectscoresheet.edit', compact('pagetitle'))->with('error', 'Scoresheet not found.');
        }

        $pagetitle = sprintf(
            'Edit Scoresheet for %s %s - %s (%s)',
            $broadsheet->fname,
            $broadsheet->lname,
            $broadsheet->subject,
            $broadsheet->admissionno
        );

        return view('subjectscoresheet.edit', compact('broadsheet', 'pagetitle'));
    }

    /**
     * Update a scoresheet.
     * FIXED: Changed from Broadsheet to Broadsheets model
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'ca1' => 'nullable|numeric|min:0',
            'ca2' => 'nullable|numeric|min:0',
            'ca3' => 'nullable|numeric|min:0',
            'exam' => 'nullable|numeric|min:0',
        ]);

        $broadsheet = Broadsheets::findOrFail($id);
        $termId = $broadsheet->term_id;

        $total = ($request->ca1 ?? 0) + ($request->ca2 ?? 0) + ($request->ca3 ?? 0) + ($request->exam ?? 0);
        $grade = $this->calculateGrade($total);
        $remark = $this->getRemark($total);

        $broadsheet->update([
            'ca1' => $request->ca1,
            'ca2' => $request->ca2,
            'ca3' => $request->ca3,
            'exam' => $request->exam,
            'total' => $total,
            'grade' => $grade,
            'remark' => $remark,
        ]);

        // Get broadsheet_record to find session_id and schoolclass_id
        $broadsheetRecord = DB::table('broadsheet_records')
            ->where('id', $broadsheet->broadsheet_record_id)
            ->first();

        if ($broadsheetRecord) {
            $this->updateClassMetrics($broadsheet->subjectclass_id, $broadsheet->staff_id, $broadsheet->term_id, $broadsheetRecord->session_id);
            $this->updateSubjectPositions($broadsheet->subjectclass_id, $broadsheet->staff_id, $broadsheet->term_id, $broadsheetRecord->session_id);
            $this->updateClassPositions($broadsheetRecord->schoolclass_id, $broadsheet->term_id, $broadsheetRecord->session_id);

            return redirect()->route('subjectscoresheet', [
                'schoolclassid' => $broadsheetRecord->schoolclass_id,
                'subjectclassid' => $broadsheet->subjectclass_id,
                'staffid' => $broadsheet->staff_id,
                'termid' => $broadsheet->term_id,
                'sessionid' => $broadsheetRecord->session_id,
            ])->with('success', 'Score updated successfully!');
        }

        return redirect()->back()->with('error', 'Unable to update scoresheet.');
    }

    /**
     * Delete a scoresheet record.
     * FIXED: Changed from Broadsheet to Broadsheets model
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $broadsheet = Broadsheets::findOrFail($id);
        $subjectclassid = $broadsheet->subjectclass_id;
        $staffid = $broadsheet->staff_id;
        $termid = $broadsheet->term_id;

        // Get broadsheet_record to find session_id and schoolclass_id
        $broadsheetRecord = DB::table('broadsheet_records')
            ->where('id', $broadsheet->broadsheet_record_id)
            ->first();

        $broadsheet->delete();

        if ($broadsheetRecord) {
            // Update metrics and positions after deletion
            $this->updateClassMetrics($subjectclassid, $staffid, $termid, $broadsheetRecord->session_id);
            $this->updateSubjectPositions($subjectclassid, $staffid, $termid, $broadsheetRecord->session_id);
            $this->updateClassPositions($broadsheetRecord->schoolclass_id, $termid, $broadsheetRecord->session_id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Score deleted successfully!',
        ]);
    }

    /**
     * Calculate grade based on total score.
     */
    protected function calculateGrade($total)
    {
        if ($total >= 70) return 'A';
        if ($total >= 60) return 'B';
        if ($total >= 40) return 'C';
        if ($total >= 30) return 'D';
        return 'F';
    }

    /**
     * Get remark based on total score.
     */
    protected function getRemark($total)
    {
        if ($total >= 70) return 'EXCELLENT';
        if ($total >= 60) return 'VERY GOOD';
        if ($total >= 40) return 'GOOD';
        if ($total >= 30) return 'FAIRLY GOOD';
        return 'POOR';
    }

    /**
     * Export scoresheet to Excel.
     *
     * @return \Maatwebsite\Excel\Concerns\FromCollection
     */
    public function export()
    {
        $subjectclassid = session('subjectclass_id');
        $staffid = session('staff_id');
        $termid = session('term_id');
        $sessionid = session('session_id');

        if (!$subjectclassid || !$staffid || !$termid || !$sessionid) {
            return redirect()->back()->with('error', 'Missing session data for export.');
        }

        $broadsheet = Broadsheets::where('subjectclass_id', $subjectclassid)
            ->where('staff_id', $staffid)
            ->where('term_id', $termid)
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')  
            ->leftJoin('subjectclass', 'subjectclass.id', '=', 'broadsheets.subjectclass_id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'broadsheet_records.schoolclass_id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('users', 'users.id', '=', 'subjectteacher.staff_id')
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'broadsheets.term_id')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'broadsheet_records.session_id')
            ->where('broadsheet_records.session_id', $sessionid)
            ->first([
                'subject.subject',
                'subject.subject_code as subject_code',
                'schoolclass.schoolclass',
                'schoolarm.arm',
                'schoolterm.term',
                'schoolsession.session',
                'users.name as staffname',
            ]);

        if (!$broadsheet) {
            return redirect()->back()->with('error', 'No data available for export.');
        }

        $filename = "{$broadsheet->staffname}_{$broadsheet->subject}{$broadsheet->subject_code}_{$broadsheet->schoolclass}{$broadsheet->arm}_{$broadsheet->term}";

        return Excel::download(new RecordsheetExport, "{$filename}.xlsx");
    }

    /**
     * Show the import form.
     *
     * @return \Illuminate\View\View
     */
    public function importform($schoolclassid, $subjectclassid, $staffid, $termid, $sessionid)
    {
        $pagetitle = 'Import Scoresheet';

        // Set session variables for import
        session([
            'schoolclass_id' => $schoolclassid,
            'subjectclass_id' => $subjectclassid,
            'staff_id' => $staffid,
            'term_id' => $termid,
            'session_id' => $sessionid,
        ]);

        Log::info('Importform session set:', session()->all());

        return view('subjectscoresheet.importsheet', compact(
            'schoolclassid',
            'subjectclassid',
            'staffid',
            'termid',
            'sessionid',
            'pagetitle'
        ));
    }

    /**
     * Import scoresheet from Excel.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importsheet(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
            'schoolclass_id' => 'required',
            'subjectclass_id' => 'required',
            'staff_id' => 'required',
            'term_id' => 'required',
            'session_id' => 'required',
        ]);

        Excel::import(new ScoresheetImport, $request->file('file'));

        $this->updateClassMetrics($request->subjectclass_id, $request->staff_id, $request->term_id, $request->session_id);
        $this->updateSubjectPositions($request->subjectclass_id, $request->staff_id, $request->term_id, $request->session_id);
        $this->updateClassPositions($request->schoolclass_id, $request->term_id, $request->session_id);

        return redirect()->route('subjectscoresheet', [
            'schoolclassid' => $request->schoolclass_id,
            'subjectclass_id' => $request->subjectclass_id,
            'staffid' => $request->staff_id,
            'termid' => $request->term_id,
            'sessionid' => $request->session_id,
        ])->with('success', 'Batch file imported successfully!');
    }

    public function editMock($id)
    {
        $broadsheet = BroadsheetsMock::where('broadsheets_mock.id', $id)
            ->leftJoin('broadsheet_records_mock', 'broadsheet_records_mock.id', '=', 'broadsheets_mock.broadsheet_record_id')
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records_mock.student_id')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('subjectclass', 'subjectclass.id', '=', 'broadsheets_mock.subjectclass_id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'broadsheet_records_mock.schoolclass_id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('classcategories', 'classcategories.id', '=', 'schoolclass.classcategoryid')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records_mock.subject_id')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'broadsheets_mock.term_id')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'broadsheet_records_mock.session_id')
            ->first([
                'broadsheets_mock.id as bid',
                'studentRegistration.admissionNO as admissionno',
                'studentRegistration.tittle as title',
                'studentRegistration.firstname as fname',
                'studentRegistration.lastname as lname',
                'studentpicture.picture',
                'broadsheets_mock.exam',
                'broadsheets_mock.total',
                'broadsheets_mock.grade',
                'schoolterm.term',
                'schoolsession.session',
                'subject.subject',
                'subject.subject_code as subject_code',
                'schoolclass.schoolclass',
                'schoolarm.arm',
                'broadsheets_mock.subject_position_class as position',
                'broadsheets_mock.remark',
                'classcategories.examscore as cat_exam',
                'broadsheet_records_mock.student_id',
                'broadsheets_mock.staff_id',
                'broadsheets_mock.term_id',
                'broadsheet_records_mock.session_id as sessionid',
            ]);

        if (!$broadsheet) {
            $pagetitle = 'Edit Mock Scoresheet - Not Found';
            return view('subjectscoresheet-mock.edit', compact('pagetitle'))->with('error', 'Mock Scoresheet not found.');
        }

        $pagetitle = sprintf(
            'Edit Mock Scoresheet for %s %s - %s (%s)',
            $broadsheet->fname,
            $broadsheet->lname,
            $broadsheet->subject,
            $broadsheet->admissionno
        );

        return view('subjectscoresheet-mock.edit', compact('broadsheet', 'pagetitle'));
    }

    /**
     * Update a mock scoresheet.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateMock(Request $request, $id)
    {
        $request->validate([
            'exam' => 'nullable|numeric|min:0',
        ]);

        $broadsheet = BroadsheetsMock::findOrFail($id);
        $termId = $broadsheet->term_id;

        $total = $request->exam ?? 0;
        $grade = $this->calculateGrade($total);
        $remark = $this->getRemark($total);

        $broadsheet->update([
            'exam' => $request->exam,
            'total' => $total,
            'grade' => $grade,
            'remark' => $remark,
        ]);

        $broadsheetRecord = DB::table('broadsheet_records_mock')
            ->where('id', $broadsheet->broadsheet_record_id)
            ->first();

        if ($broadsheetRecord) {
            $this->updateClassMetricsMock($broadsheet->subjectclass_id, $broadsheet->staff_id, $broadsheet->term_id, $broadsheetRecord->session_id);
            $this->updateSubjectPositionsMock($broadsheet->subjectclass_id, $broadsheet->staff_id, $broadsheet->term_id, $broadsheetRecord->session_id);
            $this->updateClassPositions($broadsheetRecord->schoolclass_id, $broadsheet->term_id, $broadsheetRecord->session_id);

            return redirect()->route('subjectscoresheet-mock.index', [
                'schoolclassid' => $broadsheetRecord->schoolclass_id,
                'subjectclassid' => $broadsheet->subjectclass_id,
                'staffid' => $broadsheet->staff_id,
                'termid' => $broadsheet->term_id,
                'sessionid' => $broadsheetRecord->session_id,
            ])->with('success', 'Mock score updated successfully!');
        }

        return redirect()->back()->with('error', 'Unable to update mock scoresheet.');
    }


    /**
     * Update a single mock score field via AJAX.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateScoreMock(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'field' => 'required|in:exam',
            'value' => 'nullable|numeric|min:0',
        ]);

        $broadsheet = BroadsheetsMock::findOrFail($request->id);
        $field = $request->field;
        $value = $request->value;

        $total = $value ?? 0;
        $grade = $this->calculateGrade($total);
        $remark = $this->getRemark($total);

        $broadsheet->update([
            $field => $value,
            'total' => $total,
            'grade' => $grade,
            'remark' => $remark,
        ]);

        $broadsheetRecord = DB::table('broadsheet_records_mock')
            ->where('id', $broadsheet->broadsheet_record_id)
            ->first();

        if ($broadsheetRecord) {
            $this->updateClassMetricsMock($broadsheet->subjectclass_id, $broadsheet->staff_id, $broadsheet->term_id, $broadsheetRecord->session_id);
            $this->updateSubjectPositionsMock($broadsheet->subjectclass_id, $broadsheet->staff_id, $broadsheet->term_id, $broadsheetRecord->session_id);
            $this->updateClassPositions($broadsheetRecord->schoolclass_id, $broadsheet->term_id, $broadsheetRecord->session_id);

            return response()->json([
                'success' => true,
                'message' => 'Mock score updated successfully!',
                'broadsheet' => $broadsheet->fresh(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to update mock score.',
        ], 400);
    }

    /**
     * Update class metrics for mock scoresheet.
     */
    protected function updateClassMetricsMock($subjectclassid, $staffid, $termid, $sessionid)
    {
        $classMin = BroadsheetsMock::where('subjectclass_id', $subjectclassid)
            ->where('staff_id', $staffid)
            ->where('term_id', $termid)
            ->min('total');

        $classMax = BroadsheetsMock::where('subjectclass_id', $subjectclassid)
            ->where('staff_id', $staffid)
            ->where('term_id', $termid)
            ->max('total');

        $classAvg = $classMin && $classMax ? ($classMin + $classMax) / 2 : 0;

        BroadsheetsMock::where('subjectclass_id', $subjectclassid)
            ->where('staff_id', $staffid)
            ->where('term_id', $termid)
            ->update([
                'cmin' => $classMin ?? 0,
                'cmax' => $classMax ?? 0,
                'avg' => round($classAvg, 1),
            ]);
    }

    public function destroyMock(Request $request)
{
    $ids = $request->input('ids', []);

    if (empty($ids)) {
        return response()->json([
            'success' => false,
            'message' => 'No scores selected for deletion.'
        ], 400);
    }

    DB::beginTransaction();
    try {
        $broadsheets = BroadsheetsMock::whereIn('id', (array)$ids)->get();
        
        if ($broadsheets->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid scores found for deletion.'
            ], 404);
        }

        $subjectclassid = $broadsheets->first()->subjectclass_id;
        $staffid = $broadsheets->first()->staff_id;
        $termid = $broadsheets->first()->term_id;

        $broadsheetRecord = DB::table('broadsheet_records_mock')
            ->where('id', $broadsheets->first()->broadsheet_records_mock_id)
            ->first();

        BroadsheetsMock::whereIn('id', (array)$ids)->delete();

        if ($broadsheetRecord) {
            $this->updateClassMetricsMock($subjectclassid, $staffid, $termid, $broadsheetRecord->session_id);
            $this->updateSubjectPositionsMock($subjectclassid, $staffid, $termid, $broadsheetRecord->session_id);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Selected mock scores deleted successfully!'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error deleting mock scores: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to delete scores: ' + $e->getMessage()
        ], 500);
    }
}
    /**
     * Update subject positions for mock scoresheet.
     */
    protected function updateSubjectPositionsMock($subjectclassid, $staffid, $termid, $sessionid)
    {
        $rank = 0;
        $lastScore = null;
        $rows = 0;

        $classPos = BroadsheetsMock::where('subjectclass_id', $subjectclassid)
            ->where('staff_id', $staffid)
            ->where('term_id', $termid)
            ->orderBy('total', 'DESC')
            ->get();

        foreach ($classPos as $row) {
            $rows++;
            if ($lastScore !== $row->total) {
                $lastScore = $row->total;
                $rank = $rows;
            }
            $position = match ($rank) {
                1 => 'st',
                2 => 'nd',
                3 => 'rd',
                default => 'th',
            };
            $rankPos = $rank . $position;

            $broadsheetRecord = DB::table('broadsheet_records_mock')
                ->where('id', $row->broadsheet_record_id)
                ->first();

            if ($broadsheetRecord) {
                BroadsheetsMock::where('subjectclass_id', $subjectclassid)
                    ->where('broadsheet_record_id', $row->broadsheet_record_id)
                    ->where('staff_id', $staffid)
                    ->where('term_id', $termid)
                    ->update(['subject_position_class' => $rankPos]);
            }
        }
    }

    /**
     * Export mock scoresheet to Excel.
     *
     * @return \Maatwebsite\Excel\Concerns\FromCollection
     */
    public function exportMock()
    {
        $subjectclassid = session('subjectclass_id');
        $staffid = session('staff_id');
        $termid = session('term_id');
        $sessionid = session('session_id');

        if (!$subjectclassid || !$staffid || !$termid || !$sessionid) {
            return redirect()->back()->with('error', 'Missing session data for export.');
        }

        $broadsheet = BroadsheetsMock::where('subjectclass_id', $subjectclassid)
            ->where('staff_id', $staffid)
            ->where('term_id', $termid)
            ->leftJoin('broadsheet_records_mock', 'broadsheet_records_mock.id', '=', 'broadsheets_mock.broadsheet_record_id')
            ->leftJoin('subjectclass', 'subjectclass.id', '=', 'broadsheets_mock.subjectclass_id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'broadsheet_records_mock.schoolclass_id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('users', 'users.id', '=', 'subjectteacher.staff_id')
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records_mock.subject_id')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'broadsheets_mock.term_id')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'broadsheet_records_mock.session_id')
            ->where('broadsheet_records_mock.session_id', $sessionid)
            ->first([
                'subject.subject',
                'subject.subject_code as subject_code',
                'schoolclass.schoolclass',
                'schoolarm.arm',
                'schoolterm.term',
                'schoolsession.session',
                'users.name as staffname',
            ]);

        if (!$broadsheet) {
            return redirect()->back()->with('error', 'No data available for export.');
        }

        $filename = "Mock_{$broadsheet->staffname}_{$broadsheet->subject}_{$broadsheet->subject_code}_{$broadsheet->schoolclass}_{$broadsheet->arm}_{$broadsheet->term}";

        return Excel::download(new RecordsheetExport, "{$filename}.xlsx");
    }

    /**
     * Import mock scoresheet from Excel.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importMock(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
            'schoolclass_id' => 'term_id',
            'subject_id' => 'required',
            'staff_id',
            'termid',
            'session_id',
            'term_id',
            'sessionid',
        ]);

        Excel::import(new ScoresheetImport, $request->file('file'));

        $this->updateClassMetricsMock($request->subject_id, $request->staff_id, $request->termid, $request->session_id);
        $this->updateSubjectPositions($request->subject_id, $request->staff_id, $request->termid, $request->session_id);
        return redirect()->route('subjectscoresheet_mock.index', [
            'schoolclass_id' => $request->schoolclass_id,
            'subject_id' => $request->subject_id,
            'staffid' => $request->staff_id,
            'termid' => $request->termid,
            'sessionid' => $request->session_id,
        ])->with('success', 'Mock batch file successfully imported!');
    }


    /**
 * Download blank marks sheet for data entry.
 *
 * @return \Illuminate\Http\Response
 */
public function downloadMarksSheet()
{
    $schoolclassid = session('schoolclass_id');
    $subjectclassid = session('subjectclass_id');
    $staffid = session('staff_id');
    $termid = session('term_id');
    $sessionid = session('session_id');

    if (!$subjectclassid || !$staffid || !$termid || !$sessionid || !$schoolclassid) {
        return redirect()->back()->with('error', 'Missing session data for marks sheet download.');
    }

    try {
        $marksSheetExport = new MarksSheetExport($subjectclassid, $staffid, $termid, $sessionid, $schoolclassid);
        return $marksSheetExport->download();
    } catch (\Exception $e) {
        Log::error('Marks sheet download error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Error generating marks sheet. Please try again.');
    }
}



    /**
     * Download blank marks sheet for mock exams.
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadMarksSheetMock()
    {
        $schoolclassid = session('schoolclass_id');
        $subjectclassid = session('subjectclass_id');
        $staffid = session('staff_id');
        $termid = session('term_id');
        $sessionid = session('session_id');

        if (!$subjectclassid || !$staffid || !$termid || !$sessionid || !$schoolclassid) {
            return redirect()->back()->with('error', 'Missing session data for mock marks sheet download.');
        }

        try {
            $marksSheetExport = new MarksSheetExport($subjectclassid, $staffid, $termid, $sessionid, $schoolclassid);
            return $marksSheetExport->download();
        } catch (\Exception $e) {
            Log::error('Mock marks sheet download error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating mock marks sheet. Please try again.');
        }
    }


    /**
 * Bulk update scores for multiple students.
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function bulkUpdateScores(Request $request)
{
    Log::info('Bulk update scores request received:', $request->all());

    $request->validate([
        'scores' => 'required|array|min:1',
        'scores.*.id' => 'required|integer|exists:broadsheets,id',
        'scores.*.ca1' => 'nullable|numeric|min:0|max:200',
        'scores.*.ca2' => 'nullable|numeric|min:0|max:200',
        'scores.*.ca3' => 'nullable|numeric|min:0|max:200',
        'scores.*.exam' => 'nullable|numeric|min:0|max:200',
    ], [
        'scores.*.id.exists' => 'The broadsheet ID :input does not exist.',
        'scores.*.ca1.numeric' => 'CA1 score for ID :input must be a number.',
    ]);

    DB::beginTransaction();
    try {
        $updatedCount = 0;
        $subjectclassid = null;
        $staffid = null;
        $termid = null;
        $sessionid = null;
        $schoolclassid = null;

        foreach ($request->scores as $scoreData) {
            $broadsheet = Broadsheets::findOrFail($scoreData['id']);
            $ca1 = $scoreData['ca1'] ?? 0;
            $ca2 = $scoreData['ca2'] ?? 0;
            $ca3 = $scoreData['ca3'] ?? 0;
            $exam = $scoreData['exam'] ?? 0;
            // New cumulative formula: (ca1 + ca2 + ca3) / 3 + exam / 2
            $caAverage = ($ca1 + $ca2 + $ca3) / 3;
            $total = $caAverage + ($exam / 2);
            $grade = $this->calculateGrade($total);
            $remark = $this->getRemark($total);

            $broadsheet->update([
                'ca1' => $ca1,
                'ca2' => $ca2,
                'ca3' => $ca3,
                'exam' => $exam,
                'total' => $total,
                'grade' => $grade,
                'remark' => $remark,
            ]);

            if (!$subjectclassid) {
                $subjectclassid = $broadsheet->subjectclass_id;
                $staffid = $broadsheet->staff_id;
                $termid = $broadsheet->term_id;
                $broadsheetRecord = DB::table('broadsheet_records')
                    ->where('id', $broadsheet->broadsheet_record_id)
                    ->first();
                if ($broadsheetRecord) {
                    $sessionid = $broadsheetRecord->session_id;
                    $schoolclassid = $broadsheetRecord->schoolclass_id;
                } else {
                    Log::warning("Broadsheet record not found for ID: {$broadsheet->broadsheet_record_id}");
                }
            }

            $updatedCount++;
        }

        if ($subjectclassid && $staffid && $termid && $sessionid && $schoolclassid) {
            try {
                $this->updateClassMetrics($subjectclassid, $staffid, $termid, $sessionid);
                $this->updateSubjectPositions($subjectclassid, $staffid, $termid, $sessionid);
                $this->updateClassPositions($schoolclassid, $termid, $sessionid);
            } catch (\Exception $e) {
                Log::error("Error updating metrics/positions: " . $e->getMessage());
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Successfully updated {$updatedCount} scores.",
            'updated_count' => $updatedCount,
            'broadsheets' => Broadsheets::whereIn('id', array_column($request->scores, 'id'))->get()->toArray(),
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Bulk update scores error: ' . $e->getMessage(), ['exception' => $e->getTraceAsString()]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to update scores: ' . $e->getMessage(),
        ], 500);
    }
}

// In your SubjectScoresheetController
public function results()
{
    try {
        // Make sure you have proper session data
        $subjectclass_id = session('subjectclass_id');
        $schoolclass_id = session('schoolclass_id');
        $term_id = session('term_id');
        $session_id = session('session_id');
        
        if (!$subjectclass_id || !$schoolclass_id || !$term_id || !$session_id) {
            return response()->json([
                'error' => 'Missing required session data',
                'scores' => []
            ], 400);
        }
        
        // Your existing query logic here
        $broadsheets = Broadsheet::where([
            'subjectclass_id' => $subjectclass_id,
            'schoolclass_id' => $schoolclass_id,
            'term_id' => $term_id,
            'session_id' => $session_id
        ])->get();
        
        return response()->json([
            'success' => true,
            'scores' => $broadsheets->toArray()
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error in results endpoint: ' . $e->getMessage());
        return response()->json([
            'error' => 'Internal server error',
            'message' => $e->getMessage()
        ], 500);
    }
}



}
?>