<?php

namespace App\Http\Controllers;

use App\Exports\MarksSheetExport;
use App\Exports\RecordsheetExport;
use App\Exports\MockMarksSheetExport;
use App\Exports\MockRecordsheetExport;
use App\Imports\ScoresheetImport;
use App\Models\Broadsheets;
use App\Models\BroadsheetsMock;
use App\Models\BroadsheetRecordMock;
use App\Models\PromotionStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class MyScoreSheetController extends Controller
{
    public function index(Request $request)
    {
        $pagetitle = 'My Scoresheets';
        $broadsheets = collect();

        Log::info('Index session:', $request->session()->all());

        if (!$request->ajax()) {
            $termId = $request->query('termid', 'ALL');
            $sessionId = $request->query('sessionid', 'ALL');

            if ($termId !== 'ALL' && $sessionId !== 'ALL') {
                $broadsheets = $this->getBroadsheets($request->user()->id, $termId, $sessionId);
                Log::info('Index broadsheets count:', ['count' => $broadsheets->count()]);
            }
        }

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

    public function subjectscoresheet($schoolclassid, $subjectclassid, $staffid, $termid, $sessionid)
    {
        Log::info('Subjectscoresheet parameters:', compact('schoolclassid', 'subjectclassid', 'staffid', 'termid', 'sessionid'));

        session([
            'schoolclass_id' => $schoolclassid,
            'subjectclass_id' => $subjectclassid,
            'staff_id' => $staffid,
            'term_id' => $termid,
            'session_id' => $sessionid,
        ]);

        $broadsheets = $this->getBroadsheets($staffid, $termid, $sessionid, $schoolclassid, $subjectclassid);

        Log::info('Subjectscoresheet broadsheets count:', ['count' => $broadsheets->count()]);

        $pagetitle = 'Subject Scoresheet';

        if ($broadsheets->isNotEmpty()) {
            $this->updateClassMetrics($subjectclassid, $staffid, $termid, $sessionid);
            $this->updateSubjectPositions($subjectclassid, $staffid, $termid, $sessionid);
            $this->updateClassPositions($schoolclassid, $termid, $sessionid);

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
            ->where('broadsheet_records.session_id', $sessionId);

        if ($schoolClassId) {
            $query->where('schoolclass.id', $schoolClassId);
        }
        if ($subjectClassId) {
            $query->where('subjectclass.id', $subjectClassId);
        }

        $results = $query->get([
            'broadsheets.id',
            'studentRegistration.admissionNO as admissionno',
            'broadsheet_records.student_id as student_id',
            'studentRegistration.firstname as fname',
            'studentRegistration.lastname as lname',
            'studentRegistration.othername as mname',
            'subject.subject as subject',
            'subject.subject_code as subject_code',
            'broadsheet_records.subject_id',
            'schoolclass.schoolclass',
            'schoolarm.arm',
            'schoolterm.term',
            'schoolsession.session',
            'subjectclass.id as subjectclid',
            'broadsheets.staff_id',
            'broadsheets.term_id',
            'broadsheet_records.session_id as sessionid',
            'classcategories.ca1score as ca1score',
            'classcategories.ca2score as ca2score',
            'classcategories.ca3score as ca3score',
            'classcategories.examscore as examscore',
            'studentpicture.picture',
            'broadsheets.ca1',
            'broadsheets.ca2',
            'broadsheets.ca3',
            'broadsheets.exam',
            'broadsheets.total',
            'broadsheets.bf',
            'broadsheets.cum',
            'broadsheets.grade',
            'broadsheets.subject_position_class as position',
            'broadsheets.remark',
        ])->sortBy('lastname');

        foreach ($results as $broadsheet) {
            $ca1 = $broadsheet->ca1 ?? 0;
            $ca2 = $broadsheet->ca2 ?? 0;
            $ca3 = $broadsheet->ca3 ?? 0;
            $exam = $broadsheet->exam ?? 0;
            $caAverage = ($ca1 + $ca2 + $ca3) / 3;
            $newTotal = round(($caAverage + $exam) / 2, 1);

            $newBf = $this->getPreviousTermCum(
                $broadsheet->student_id,
                $broadsheet->subject_id,
                $termId,
                $sessionId
            );

            $newCum = $termId == 1 ? $newTotal : round(($newBf + $newTotal) / 2, 2);

            $newGrade = $this->calculateGrade($newCum);
            $newRemark = $this->getRemark($newGrade);

            $significantChange = abs($broadsheet->bf - $newBf) > 0.01 ||
                                abs($broadsheet->total - $newTotal) > 0.01 ||
                                abs($broadsheet->cum - $newCum) > 0.01 ||
                                $broadsheet->grade !== $newGrade ||
                                $broadsheet->remark !== $newRemark;

            if ($significantChange) {
                Log::info("Updating broadsheet {$broadsheet->id} due to significant changes", [
                    'old_values' => [
                        'bf' => $broadsheet->bf,
                        'total' => $broadsheet->total,
                        'cum' => $broadsheet->cum,
                        'grade' => $broadsheet->grade,
                        'remark' => $broadsheet->remark,
                    ],
                    'new_values' => [
                        'bf' => $newBf,
                        'total' => $newTotal,
                        'cum' => $newCum,
                        'grade' => $newGrade,
                        'remark' => $newRemark,
                    ],
                ]);

                $broadsheet->bf = $newBf;
                $broadsheet->total = $newTotal;
                $broadsheet->cum = $newCum;
                $broadsheet->grade = $newGrade;
                $broadsheet->remark = $newRemark;
                $broadsheet->save();
            }
        }

        return $results;
    }

    public function results()
    {
        try {
            $subjectclass_id = session('subjectclass_id');
            $schoolclass_id = session('schoolclass_id');
            $term_id = session('term_id');
            $session_id = session('session_id');

            if (!$subjectclass_id || !$schoolclass_id || !$term_id || !$session_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required session data',
                    'scores' => [],
                ], 400);
            }

            $broadsheets = Broadsheets::where([
                'subjectclass_id' => $subjectclass_id,
                'term_id' => $term_id,
            ])
                ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
                ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
                ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
                ->where('broadsheet_records.session_id', $session_id)
                ->get([
                    'broadsheets.id',
                    'studentRegistration.admissionNO as admissionno',
                    'studentRegistration.firstname as fname',
                    'studentRegistration.lastname as lname',
                    'broadsheets.ca1',
                    'broadsheets.ca2',
                    'broadsheets.ca3',
                    'broadsheets.exam',
                    'broadsheets.total',
                    'broadsheets.bf',
                    'broadsheets.cum',
                    'broadsheets.grade',
                    'broadsheets.subject_position_class as position',
                    'broadsheets.term_id',
                ]);

            return response()->json([
                'success' => true,
                'scores' => $broadsheets->toArray(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in results endpoint: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function getPreviousTermCum($studentId, $subjectId, $termId, $sessionId)
    {
        if ($termId == 1) {
            Log::info("Term ID is 1, setting bf to 0 for student_id: {$studentId}, subject_id: {$subjectId}");
            return 0;
        }

        $previousTermCum = Broadsheets::where('broadsheet_records.student_id', $studentId)
            ->where('broadsheet_records.subject_id', $subjectId)
            ->where('broadsheets.term_id', $termId - 1)
            ->where('broadsheet_records.session_id', $sessionId)
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->value('broadsheets.cum');

        if (is_null($previousTermCum)) {
            Log::warning("No cumulative score found for previous term", [
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'term_id' => $termId - 1,
                'session_id' => $sessionId,
            ]);
            return 0;
        }

        Log::info("Fetched previous term cumulative score", [
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'term_id' => $termId - 1,
            'cum' => $previousTermCum,
        ]);

        return round($previousTermCum, 2);
    }

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

        $classAvg = $classMin && $classMax ? round(($classMin + $classMax) / 2, 1) : 0;

        Broadsheets::where('subjectclass_id', $subjectclassid)
            ->where('staff_id', $staffid)
            ->where('term_id', $termid)
            ->update([
                'cmin' => $classMin ?? 0,
                'cmax' => $classMax ?? 0,
                'avg' => $classAvg,
            ]);
    }

    protected function updateSubjectPositions($subjectclassid, $staffid, $termid, $sessionid)
    {
        $rank = 0;
        $lastScore = null;
        $rows = 0;

        $broadsheets = Broadsheets::where('broadsheets.subjectclass_id', $subjectclassid)
            ->where('broadsheets.staff_id', $staffid)
            ->where('broadsheets.term_id', $termid)
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->where('broadsheet_records.session_id', $sessionid)
            ->orderBy('broadsheets.cum', 'DESC')
            ->get(['broadsheets.id', 'broadsheets.cum', 'broadsheets.broadsheet_record_id']);

        foreach ($broadsheets as $row) {
            $rows++;
            if ($lastScore !== $row->cum) {
                $lastScore = $row->cum;
                $rank = $rows;
            }
            $position = match ($rank) {
                1 => 'st',
                2 => 'nd',
                3 => 'rd',
                default => 'th',
            };
            $rankPos = $rank . $position;

            $broadsheetRecord = DB::table('broadsheet_records')
                ->where('id', $row->broadsheet_record_id)
                ->first();

            if ($broadsheetRecord) {
                Broadsheets::where('id', $row->id)
                    ->update(['subject_position_class' => $rankPos]);
            }
        }

        Log::info('Updated subject positions for regular exams across entire class', [
            'subjectclass_id' => $subjectclassid,
            'staff_id' => $staffid,
            'term_id' => $termid,
            'session_id' => $sessionid,
            'total_records' => $rows,
        ]);
    }

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
            if ($lastScore !== $row->subjectstotalscores) {
                $lastScore = $row->subjectstotalscores;
                $rank = $rows;
            }
            $position = match ($rank) {
                1 => 'st',
                2 => 'nd',
                3 => 'rd',
                default => 'th',
            };
            $rankPos = $rank . $position;

            PromotionStatus::where('id', $row->id)
                ->update(['position' => $rankPos]);
        }
    }

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
                'studentRegistration.title',
                'studentRegistration.firstname as fname',
                'studentRegistration.lastname as lname',
                'studentpicture.picture',
                'broadsheets.ca1',
                'broadsheets.ca2',
                'broadsheets.ca3',
                'broadsheets.exam',
                'broadsheets.total',
                'broadsheets.bf',
                'broadsheets.cum',
                'broadsheets.grade',
                'schoolterm.term',
                'schoolsession.session',
                'subject.subject',
                'subject.subject_code',
                'schoolclass.schoolclass',
                'schoolarm.id',
                'broadsheets.subject_position_class as position',
                'broadsheets.remark',
                'classcategories.ca1id as id1',
                'classcategories.ca2id as id2',
                'classcategories.ca3id as id3',
                'classcategories.examid as id4',
                'broadsheet_records.student_id',
                'broadsheets.staff_id',
                'broadsheets.term_id',
                'broadsheet_records.session_id as sessionid',
            ]);

        if (!$broadsheet) {
            return view('error', [
                'id' => $id,
                'title' => 'Not Found',
                'message' => 'Score not found.',
            ]);
        }

        $pagetitle = sprintf(
            'Edit Score for %s %s - %s (%s)',
            $broadsheet->fname,
            $broadsheet->lname,
            $broadsheet->subject,
            $id
        );

        return view('scoresheet.edit', compact('broadsheet', 'pagetitle'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'ca1' => 'nullable|numeric|min:0|max:100',
            'ca2' => 'nullable|numeric|min:0|max:100',
            'ca3' => 'nullable|numeric|min:0|max:100',
            'exam' => 'nullable|numeric|min:0|max:100',
        ]);

        $broadsheet = Broadsheets::findOrFail($id);
        $termId = $broadsheet->term_id;
        $broadsheetRecord = DB::table('broadsheet_records')
            ->where('id', $broadsheet->broadsheet_record_id)
            ->first();

        if (!$broadsheetRecord) {
            return redirect()->back()->with('error', 'Broadsheet record not found.');
        }

        $ca1 = $request->ca1 ?? 0;
        $ca2 = $request->ca2 ?? 0;
        $ca3 = $request->ca3 ?? 0;
        $exam = $request->exam ?? 0;
        $caAverage = ($ca1 + $ca2 + $ca3) / 3;
        $total = round(($caAverage + $exam) / 2, 1);
        $bf = $this->getPreviousTermCum(
            $broadsheetRecord->student_id,
            $broadsheetRecord->subject_id,
            $termId,
            $broadsheetRecord->session_id
        );
        $cum = $termId == 1 ? $total : round(($bf + $total) / 2, 2);
        $grade = $this->calculateGrade($cum);
        $remark = $this->getRemark($grade);

        $broadsheet->update([
            'ca1' => $ca1,
            'ca2' => $ca2,
            'ca3' => $ca3,
            'exam' => $exam,
            'total' => $total,
            'bf' => $bf,
            'cum' => $cum,
            'grade' => $grade,
            'remark' => $remark,
        ]);

        $this->updateClassMetrics($broadsheet->subjectclass_id, $broadsheet->staff_id, $broadsheet->term_id, $broadsheetRecord->session_id);
        $this->updateSubjectPositions($broadsheet->subjectclass_id, $broadsheet->staff_id, $broadsheet->term_id, $broadsheetRecord->session_id);
        $this->updateClassPositions($broadsheetRecord->schoolclass_id, $broadsheet->term_id, $broadsheetRecord->session_id);

        return redirect()->action(
            [self::class, 'subjectscoresheet'],
            [
                'schoolclassid' => $broadsheetRecord->schoolclass_id,
                'subjectclassid' => $broadsheet->subjectclass_id,
                'staffid' => $broadsheet->staff_id,
                'termid' => $termId,
                'sessionid' => $broadsheetRecord->session_id,
            ]
        )->with('success', 'Score updated successfully!');
    }

    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $broadsheet = Broadsheets::findOrFail($id);
        $subjectclassid = $broadsheet->subjectclass_id;
        $staffid = $broadsheet->staff_id;
        $termid = $broadsheet->term_id;

        $broadsheetRecord = DB::table('broadsheet_records')
            ->where('id', $broadsheet->broadsheet_record_id)
            ->first();

        $broadsheet->delete();

        if ($broadsheetRecord) {
            $this->updateClassMetrics($subjectclassid, $staffid, $termid, $broadsheetRecord->session_id);
            $this->updateSubjectPositions($subjectclassid, $staffid, $termid, $broadsheetRecord->session_id);
            $this->updateClassPositions($broadsheetRecord->schoolclass_id, $termid, $broadsheetRecord->session_id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Score deleted successfully!',
        ]);
    }

    protected function calculateGrade($score)
    {
        if ($score >= 70) {
            return 'A';
        } elseif ($score >= 60) {
            return 'B';
        } elseif ($score >= 50) {
            return 'C';
        } elseif ($score >= 40) {
            return 'D';
        }
        return 'F';
    }

    protected function getRemark($grade)
    {
        $remarks = [
            'A' => 'Excellent',
            'B' => 'Very Good',
            'C' => 'Good',
            'D' => 'Pass',
            'F' => 'Fail',
        ];

        return $remarks[$grade] ?? 'Unknown';
    }

    public function bulkUpdateScores(Request $request)
    {
        $scores = $request->input('scores', []);
        $term_id = $request->input('term_id');
        $session_id = $request->input('session_id');
        $subjectclass_id = $request->input('subjectclass_id');
        $staff_id = $request->input('staff_id');
        $schoolclass_id = $request->input('schoolclass_id');

        if (!$term_id || !$session_id || !$subjectclass_id || !$staff_id || !$schoolclass_id) {
            Log::error('Missing required parameters for bulk update', [
                'term_id' => $term_id,
                'session_id' => $session_id,
                'subjectclass_id' => $subjectclass_id,
                'staff_id' => $staff_id,
                'schoolclass_id' => $schoolclass_id,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Missing required parameters',
            ], 400);
        }

        Log::info('Starting bulk update scores', [
            'scores_count' => count($scores),
            'term_id' => $term_id,
            'session_id' => $session_id,
        ]);

        DB::transaction(function () use ($scores, $term_id, $session_id, $subjectclass_id, $staff_id, $schoolclass_id) {
            foreach ($scores as $score) {
                $broadsheet = Broadsheets::find($score['id']);
                if (!$broadsheet) {
                    Log::warning('Broadsheet not found', ['id' => $score['id']]);
                    continue;
                }

                $ca1 = floatval($score['ca1'] ?? 0);
                $ca2 = floatval($score['ca2'] ?? 0);
                $ca3 = floatval($score['ca3'] ?? 0);
                $exam = floatval($score['exam'] ?? 0);

                $ca_average = ($ca1 + $ca2 + $ca3) / 3;
                $total = round(($ca_average + $exam) / 2, 1);

                $bf = $this->getPreviousTermCum(
                    $broadsheet->broadsheetRecord->student_id,
                    $broadsheet->broadsheetRecord->subject_id,
                    $term_id,
                    $session_id
                );

                $cum = $term_id == 1 ? $total : round(($bf + $total) / 2, 2);

                Log::info('Score calculation', [
                    'id' => $score['id'],
                    'ca_average' => $ca_average,
                    'total' => $total,
                    'bf' => $bf,
                    'cum' => $cum,
                    'term_id' => $term_id,
                ]);

                $grade = $this->calculateGrade($cum);
                $remark = $this->getRemark($grade);

                $broadsheet->update([
                    'ca1' => $ca1,
                    'ca2' => $ca2,
                    'ca3' => $ca3,
                    'exam' => $exam,
                    'total' => $total,
                    'bf' => $bf,
                    'cum' => $cum,
                    'grade' => $grade,
                    'remark' => $remark,
                    'updated_at' => now(),
                ]);
            }

            $this->updateClassMetrics($subjectclass_id, $staff_id, $term_id, $session_id);
            $this->updateSubjectPositions($subjectclass_id, $staff_id, $term_id, $session_id);
            $this->updateClassPositions($schoolclass_id, $term_id, $session_id);
        });

        $updatedBroadsheets = Broadsheets::where('broadsheets.subjectclass_id', $subjectclass_id)
            ->where('broadsheets.term_id', $term_id)
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
            ->select([
                'broadsheets.*',
                'studentRegistration.admissionNO as admissionno',
                'studentRegistration.firstname as fname',
                'studentRegistration.lastname as lname',
            ])
            ->orderBy('broadsheets.cum', 'DESC')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'broadsheets' => $updatedBroadsheets,
            ],
        ]);
    }

    public function import(Request $request)
    {
        Log::info('Import: Request received', [
            'user_id' => $request->user()->id,
            'has_file' => $request->hasFile('file'),
        ]);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
            'schoolclass_id' => 'required|integer',
            'subjectclass_id' => 'required|integer',
            'staff_id' => 'required|integer',
            'term_id' => 'required|integer',
            'session_id' => 'required|integer',
        ]);

        try {
            $importData = [
                'schoolclass_id' => $request->schoolclass_id,
                'subjectclass_id' => $request->subjectclass_id,
                'staff_id' => $request->staff_id,
                'term_id' => $request->term_id,
                'session_id' => $request->session_id,
            ];

            Log::debug('Import: Starting import', $importData);

            $import = new ScoresheetImport($importData);
            Excel::import($import, $request->file('file'));

            $updatedBroadsheets = $import->getUpdatedBroadsheets();
            $failures = $import->getFailures();

            Log::info('Import: Success', [
                'updated_broadsheets_count' => count($updatedBroadsheets),
                'failures_count' => count($failures),
            ]);

            $message = "Scores imported successfully! Updated " . count($updatedBroadsheets) . " records.";
            if ($failures) {
                $message .= " Skipped " . count($failures) . " rows due to validation errors.";
            }

            return redirect()->back()->with('success', 'Batch File Imported Successfully');
        } catch (\Exception $e) {
            Log::error('Import: Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to import scores: ' . $e->getMessage(),
            ], 500);
        }
    }


    // public function import(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'file' => 'required|mimes:xlsx,xls',
    //             'schoolclass_id' => 'required|integer',
    //             'subjectclass_id' => 'required|integer',
    //             'staff_id' => 'required|integer',
    //             'term_id' => 'required|integer|in:1,2,3',
    //             'session_id' => 'required|integer',
    //         ]);

    //         $import = new ScoresheetImport($request->all());
    //         Excel::import($import, $request->file('file'));

    //         return response()->json([
    //             'success' => true,
    //             'broadsheets' => $import->getUpdatedBroadsheets(),
    //             'failures' => $import->getFailures(),
    //             'message' => 'Scores imported successfully.'
    //         ]);
    //     } catch (\Exception $e) {
    //         \Log::error('Scoresheet import failed: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to import scores: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function export()
    {
        $schoolclassId = session('schoolclass_id');
        $subjectclassId = session('subjectclass_id');
        $termId = session('term_id');
        $sessionId = session('session_id');
        $staffId = session('staff_id');

        if (!$schoolclassId || !$subjectclassId || !$termId || !$sessionId || !$staffId) {
            return redirect()->back()->with('error', 'Missing required data for export.');
        }

        $broadsheet = Broadsheets::where('broadsheets.subjectclass_id', $subjectclassId)
            ->where('broadsheets.staff_id', $staffId)
            ->where('broadsheets.term_id', $termId)
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'broadsheet_records.schoolclass_id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'broadsheets.term_id')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'broadsheet_records.session_id')
            ->leftJoin('subjectclass', 'subjectclass.id', '=', 'broadsheets.subjectclass_id')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->where('broadsheet_records.session_id', $sessionId)
            ->first([
                'subject.subject',
                'subject.subject_code',
                'schoolclass.schoolclass',
                'schoolarm.arm',
                'schoolterm.term',
                'schoolsession.session',
                'users.name as staff_name',
            ]);

        if (!$broadsheet) {
            return redirect()->back()->with('error', 'No data found for export.');
        }

        $staffName = str_replace([' ', '.', ',', "'", '"'], '_', $broadsheet->staff_name);
        $subject = str_replace([' ', '.', ',', "'", '"', '&'], '_', $broadsheet->subject);
        $subjectCode = str_replace([' ', '.', ',', "'", '"'], '_', $broadsheet->subject_code);
        $schoolClass = str_replace([' ', '.', ',', "'", '"'], '_', $broadsheet->schoolclass);
        $arm = str_replace([' ', '.', ',', "'", '"'], '_', $broadsheet->arm);
        $term = str_replace([' ', '.', ',', "'", '"'], '_', $broadsheet->term);
        $session = str_replace([' ', '.', ',', "'", '"', '/', '-'], '', $broadsheet->session);

        $filename = sprintf(
            'Scores_Sheet_%s_%s_%s_%s_%s_%s_%s.xlsx',
            $staffName,
            $subject,
            $subjectCode,
            $schoolClass,
            $arm,
            $term,
            $session
        );

        return Excel::download(
            new RecordsheetExport($schoolclassId, $subjectclassId, $termId, $sessionId, $staffId),
            $filename
        );
    }

    public function downloadMarkSheet()
    {
        $schoolclassId = session('schoolclass_id');
        $subjectclassId = session('subjectclass_id');
        $termId = session('term_id');
        $sessionId = session('session_id');
        $staffId = session('staff_id');

        if (!$schoolclassId || !$subjectclassId || !$termId || !$sessionId || !$staffId) {
            return response()->json([
                'error' => 'Missing required data for download.',
                'session_data' => session()->all(),
            ], 400);
        }

        try {
            $export = new MarksSheetExport($subjectclassId, $staffId, $termId, $sessionId, $schoolclassId);
            return $export->download();
        } catch (\Exception $e) {
            Log::error('Marksheet download error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to generate marksheet: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function mockIndex(Request $request)
    {
        $pagetitle = 'My Mock Scoresheets';
        $broadsheets = collect();

        Log::info('Mock Index session:', $request->session()->all());

        if (!$request->ajax()) {
            $termId = $request->query('termid', 'ALL');
            $sessionId = $request->query('sessionid', 'ALL');

            if ($termId !== 'ALL' && $sessionId !== 'ALL') {
                $broadsheets = $this->getMockBroadsheets($request->user()->id, $termId, $sessionId);
                Log::info('Mock Index broadsheets count:', ['count' => $broadsheets->count()]);
            }
        }

        if ($request->ajax()) {
            $termId = $request->input('termid', 'ALL');
            $sessionId = $request->input('sessionid', 'ALL');

            if ($termId === 'ALL' || $sessionId === 'ALL') {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select both term and session.',
                ], 422);
            }

            $broadsheets = $this->getMockBroadsheets($request->user()->id, $termId, $sessionId);

            return response()->json([
                'success' => true,
                'data' => [
                    'broadsheets' => $broadsheets,
                ],
            ]);
        }

        return view('subjectscoresheet.mock_index', compact('pagetitle', 'broadsheets'));
    }

    public function mockSubjectscoresheet($schoolclassid, $subjectclassid, $staffid, $termid, $sessionid)
    {
        Log::info('Mock Subjectscoresheet parameters:', compact('schoolclassid', 'subjectclassid', 'staffid', 'termid', 'sessionid'));

        session([
            'schoolclass_id' => $schoolclassid,
            'subjectclass_id' => $subjectclassid,
            'staff_id' => $staffid,
            'term_id' => $termid,
            'session_id' => $sessionid,
        ]);

        $broadsheets = $this->getMockBroadsheets($staffid, $termid, $sessionid, $schoolclassid, $subjectclassid);

        Log::info('Mock Subjectscoresheet broadsheets count:', ['count' => $broadsheets->count()]);

        $pagetitle = 'Mock Subject Scoresheet';

        if ($broadsheets->isNotEmpty()) {
            $this->updateMockClassMetrics($subjectclassid, $staffid, $termid, $sessionid);
            $this->updateMockSubjectPositions($subjectclassid, $staffid, $termid, $sessionid);

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

    public function mockEdit($id)
    {
        $broadsheet = BroadsheetsMock::where('broadsheetmock.id', $id)
            ->leftJoin('broadsheet_records_mock', 'broadsheet_records_mock.id', '=', 'broadsheetmock.broadsheet_records_mock_id')
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records_mock.student_id')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('subjectclass', 'subjectclass.id', '=', 'broadsheetmock.subjectclass_id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'broadsheet_records_mock.schoolclass_id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records_mock.subject_id')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'broadsheetmock.term_id')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'broadsheet_records_mock.session_id')
            ->first([
                'broadsheetmock.id as bid',
                'studentRegistration.admissionNO as admissionno',
                'studentRegistration.title',
                'studentRegistration.firstname as fname',
                'studentRegistration.lastname as lname',
                'studentpicture.picture',
                'broadsheetmock.exam',
                'broadsheetmock.total',
                'broadsheetmock.grade',
                'schoolterm.term',
                'schoolsession.session',
                'subject.subject',
                'subject.subject_code',
                'schoolclass.schoolclass',
                'schoolarm.id',
                'broadsheetmock.subject_position_class as position',
                'broadsheetmock.remark',
                'broadsheet_records_mock.student_id',
                'broadsheetmock.staff_id',
                'broadsheetmock.term_id',
                'broadsheet_records_mock.session_id as sessionid',
            ]);

        if (!$broadsheet) {
            return view('error', [
                'id' => $id,
                'title' => 'Not Found',
                'message' => 'Mock score not found.',
            ]);
        }

        $pagetitle = sprintf(
            'Edit Mock Score for %s %s - %s (%s)',
            $broadsheet->fname,
            $broadsheet->lname,
            $broadsheet->subject,
            $id
        );

        return view('scoresheet.mock_edit', compact('broadsheet', 'pagetitle'));
    }

    public function mockUpdate(Request $request, $id)
    {
        $request->validate([
            'exam' => 'nullable|numeric|min:0|max:100',
        ]);

        $broadsheet = BroadsheetsMock::findOrFail($id);
        $termId = $broadsheet->term_id;
        $broadsheetRecord = BroadsheetRecordMock::where('id', $broadsheet->broadsheet_records_mock_id)->first();

        if (!$broadsheetRecord) {
            return redirect()->back()->with('error', 'Mock broadsheet record not found.');
        }

        $exam = $request->exam ?? 0;
        $total = $exam;
        $grade = $this->calculateGrade($total);
        $remark = $this->getRemark($grade);

        $broadsheet->update([
            'exam' => $exam,
            'total' => $total,
            'grade' => $grade,
            'remark' => $remark,
        ]);

        $this->updateMockClassMetrics($broadsheet->subjectclass_id, $broadsheet->staff_id, $broadsheet->term_id, $broadsheetRecord->session_id);
        $this->updateMockSubjectPositions($broadsheet->subjectclass_id, $broadsheet->staff_id, $broadsheet->term_id, $broadsheetRecord->session_id);

        return redirect()->action(
            [self::class, 'mockSubjectscoresheet'],
            [
                'schoolclassid' => $broadsheetRecord->schoolclass_id,
                'subjectclassid' => $broadsheet->subjectclass_id,
                'staffid' => $broadsheet->staff_id,
                'termid' => $termId,
                'sessionid' => $broadsheetRecord->session_id,
            ]
        )->with('success', 'Mock score updated successfully!');
    }

    public function mockDestroy(Request $request)
    {
        $id = $request->input('id');
        $broadsheet = BroadsheetsMock::findOrFail($id);
        $subjectclassid = $broadsheet->subjectclass_id;
        $staffid = $broadsheet->staff_id;
        $termid = $broadsheet->term_id;

        $broadsheetRecord = BroadsheetRecordMock::where('id', $broadsheet->broadsheet_records_mock_id)->first();

        $broadsheet->delete();

        if ($broadsheetRecord) {
            $this->updateMockClassMetrics($subjectclassid, $staffid, $termid, $broadsheetRecord->session_id);
            $this->updateMockSubjectPositions($subjectclassid, $staffid, $termid, $broadsheetRecord->session_id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Mock score deleted successfully!',
        ]);
    }

    public function mockBulkUpdateScores(Request $request)
    {
        $scores = $request->input('scores', []);
        $term_id = $request->input('term_id');
        $session_id = $request->input('session_id');
        $subjectclass_id = $request->input('subjectclass_id');
        $staff_id = $request->input('staff_id');
        $schoolclass_id = $request->input('schoolclass_id');

        if (!$term_id || !$session_id || !$subjectclass_id || !$staff_id || !$schoolclass_id) {
            Log::error('Missing required parameters for mock bulk update', [
                'term_id' => $term_id,
                'session_id' => $session_id,
                'subjectclass_id' => $subjectclass_id,
                'staff_id' => $staff_id,
                'schoolclass_id' => $schoolclass_id,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Missing required parameters',
            ], 400);
        }

        Log::info('Starting mock bulk update scores', [
            'scores_count' => count($scores),
            'term_id' => $term_id,
            'session_id' => $session_id,
        ]);

        DB::transaction(function () use ($scores, $term_id, $session_id, $subjectclass_id, $staff_id, $schoolclass_id) {
            foreach ($scores as $score) {
                $broadsheet = BroadsheetsMock::find($score['id']);
                if (!$broadsheet) {
                    Log::warning('Mock broadsheet not found', ['id' => $score['id']]);
                    continue;
                }

                $exam = floatval($score['exam'] ?? 0);
                $total = $exam;

                Log::info('Mock score calculation', [
                    'id' => $score['id'],
                    'total' => $total,
                    'term_id' => $term_id,
                ]);

                $grade = $this->calculateGrade($total);
                $remark = $this->getRemark($grade);

                $broadsheet->update([
                    'exam' => $exam,
                    'total' => $total,
                    'grade' => $grade,
                    'remark' => $remark,
                    'updated_at' => now(),
                ]);
            }

            $this->updateMockClassMetrics($subjectclass_id, $staff_id, $term_id, $session_id);
            $this->updateMockSubjectPositions($subjectclass_id, $staff_id, $term_id, $session_id);
        });

        $updatedBroadsheets = BroadsheetsMock::where('broadsheetmock.subjectclass_id', $subjectclass_id)
            ->where('broadsheetmock.term_id', $term_id)
            ->leftJoin('broadsheet_records_mock', 'broadsheet_records_mock.id', '=', 'broadsheetmock.broadsheet_records_mock_id')
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records_mock.student_id')
            ->select([
                'broadsheetmock.*',
                'studentRegistration.admissionNO as admissionno',
                'studentRegistration.firstname as fname',
                'studentRegistration.lastname as lname',
            ])
            ->orderBy('broadsheetmock.total', 'DESC')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'broadsheets' => $updatedBroadsheets,
            ],
        ]);
    }

    public function mockImport(Request $request)
    {
        Log::info('Mock import: Request received', [
            'user_id' => $request->user()->id,
            'has_file' => $request->hasFile('file'),
        ]);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
            'schoolclass_id' => 'required|integer',
            'subjectclass_id' => 'required|integer',
            'staff_id' => 'required|integer',
            'term_id' => 'required|integer',
            'session_id' => 'required|integer',
        ]);

        try {
            $importData = [
                'schoolclass_id' => $request->schoolclass_id,
                'subjectclass_id' => $request->subjectclass_id,
                'staff_id' => $request->staff_id,
                'term_id' => $request->term_id,
                'session_id' => $request->session_id,
            ];

            Log::debug('Mock import: Starting import', $importData);

            $import = new ScoresheetImport($importData, true);
            Excel::import($import, $request->file('file'));

            $updatedBroadsheets = $import->getUpdatedBroadsheets();
            $failures = $import->getFailures();

            Log::info('Mock import: Success', [
                'updated_broadsheets_count' => count($updatedBroadsheets),
                'failures_count' => count($failures),
            ]);

            $message = "Mock scores imported successfully! Updated " . count($updatedBroadsheets) . " records.";
            if ($failures) {
                $message .= " Skipped " . count($failures) . " rows due to validation errors.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'broadsheets' => $updatedBroadsheets,
                'errors' => $failures,
            ]);
        } catch (\Exception $e) {
            Log::error('Mock import: Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to import mock scores: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function mockResults()
    {
        try {
            $subjectclass_id = session('subjectclass_id');
            $schoolclass_id = session('schoolclass_id');
            $term_id = session('term_id');
            $session_id = session('session_id');

            if (!$subjectclass_id || !$schoolclass_id || !$term_id || !$session_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required session data',
                    'scores' => [],
                ], 400);
            }

            $broadsheets = BroadsheetsMock::where([
                'subjectclass_id' => $subjectclass_id,
                'term_id' => $term_id,
            ])
                ->leftJoin('broadsheet_records_mock', 'broadsheet_records_mock.id', '=', 'broadsheetmock.broadsheet_records_mock_id')
                ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records_mock.student_id')
                ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records_mock.subject_id')
                ->where('broadsheet_records_mock.session_id', $session_id)
                ->get([
                    'broadsheetmock.id',
                    'studentRegistration.admissionNO as admissionno',
                    'studentRegistration.firstname as fname',
                    'studentRegistration.lastname as lname',
                    'broadsheetmock.exam',
                    'broadsheetmock.total',
                    'broadsheetmock.grade',
                    'broadsheetmock.subject_position_class as position',
                    'broadsheetmock.term_id',
                ]);

            return response()->json([
                'success' => true,
                'scores' => $broadsheets->toArray(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in mock results endpoint: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function mockExport()
    {
        $schoolclassId = session('schoolclass_id');
        $subjectclassId = session('subjectclass_id');
        $termId = session('term_id');
        $sessionId = session('session_id');
        $staffId = session('staff_id');

        if (!$schoolclassId || !$subjectclassId || !$termId || !$sessionId || !$staffId) {
            return redirect()->back()->with('error', 'Missing required data for mock export.');
        }

        $broadsheet = BroadsheetsMock::where('broadsheetmock.subjectclass_id', $subjectclassId)
            ->where('broadsheetmock.staff_id', $staffId)
            ->where('broadsheetmock.term_id', $termId)
            ->leftJoin('broadsheet_records_mock', 'broadsheet_records_mock.id', '=', 'broadsheetmock.broadsheet_records_mock_id')
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records_mock.subject_id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'broadsheet_records_mock.schoolclass_id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'broadsheetmock.term_id')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'broadsheet_records_mock.session_id')
            ->leftJoin('subjectclass', 'subjectclass.id', '=', 'broadsheetmock.subjectclass_id')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->where('broadsheet_records_mock.session_id', $sessionId)
            ->first([
                'subject.subject',
                'subject.subject_code',
                'schoolclass.schoolclass',
                'schoolarm.arm',
                'schoolterm.term',
                'schoolsession.session',
                'users.name as staff_name',
            ]);

        if (!$broadsheet) {
            return redirect()->back()->with('error', 'No data found for mock export.');
        }

        $staffName = str_replace([' ', '.', ',', "'", '"'], '_', $broadsheet->staff_name);
        $subject = str_replace([' ', '.', ',', "'", '"', '&'], '_', $broadsheet->subject);
        $subjectCode = str_replace([' ', '.', ',', "'", '"'], '_', $broadsheet->subject_code);
        $schoolClass = str_replace([' ', '.', ',', "'", '"'], '_', $broadsheet->schoolclass);
        $arm = str_replace([' ', '.', ',', "'", '"'], '_', $broadsheet->arm);
        $term = str_replace([' ', '.', ',', "'", '"'], '_', $broadsheet->term);
        $session = str_replace([' ', '.', ',', "'", '"', '/', '-'], '', $broadsheet->session);

        $filename = sprintf(
            'Mock_Scores_Sheet_%s_%s_%s_%s_%s_%s_%s.xlsx',
            $staffName,
            $subject,
            $subjectCode,
            $schoolClass,
            $arm,
            $term,
            $session
        );

        return Excel::download(
            new MockRecordsheetExport($schoolclassId, $subjectclassId, $termId, $sessionId, $staffId, true),
            $filename
        );
    }

    public function mockDownloadMarkSheet()
    {
        $schoolclassId = session('schoolclass_id');
        $subjectclassId = session('subjectclass_id');
        $termId = session('term_id');
        $sessionId = session('session_id');
        $staffId = session('staff_id');

        if (!$schoolclassId || !$subjectclassId || !$termId || !$sessionId || !$staffId) {
            return redirect()->back()->with('error', 'Missing required data for mock download.');
        }

        try {
            $export = new MockMarksSheetExport($subjectclassId, $staffId, $termId, $sessionId, $schoolclassId, true);
            return $export->download();
        } catch (\Exception $e) {
            Log::error('Mock marksheet download error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate mock marksheet: ' . $e->getMessage());
        }
    }

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

        $results = $query->get([
            'broadsheetmock.id',
            'studentRegistration.admissionNO as admissionno',
            'broadsheet_records_mock.student_id as student_id',
            'studentRegistration.firstname as fname',
            'studentRegistration.lastname as lname',
            'studentRegistration.othername as mname',
            'subject.subject as subject',
            'subject.subject_code as subject_code',
            'broadsheet_records_mock.subject_id',
            'schoolclass.schoolclass',
            'schoolarm.arm',
            'schoolterm.term',
            'schoolsession.session',
            'subjectclass.id as subjectclid',
            'broadsheetmock.staff_id',
            'broadsheetmock.term_id',
            'broadsheet_records_mock.session_id as sessionid',
            'studentpicture.picture',
            'broadsheetmock.exam',
            'broadsheetmock.total',
            'broadsheetmock.grade',
            'broadsheetmock.subject_position_class as position',
            'broadsheetmock.remark',
        ])->sortBy('admissionno');

        foreach ($results as $broadsheet) {
            $exam = $broadsheet->exam ?? 0;
            $newTotal = $exam;
            $newGrade = $this->calculateGrade($newTotal);
            $newRemark = $this->getRemark($newGrade);

            $significantChange = abs($broadsheet->total - $newTotal) > 0.01 ||
                                $broadsheet->grade !== $newGrade ||
                                $broadsheet->remark !== $newRemark;

            if ($significantChange) {
                Log::info("Updating mock broadsheet {$broadsheet->id} due to significant changes", [
                    'old_values' => [
                        'total' => $broadsheet->total,
                        'grade' => $broadsheet->grade,
                        'remark' => $broadsheet->remark,
                    ],
                    'new_values' => [
                        'total' => $newTotal,
                        'grade' => $newGrade,
                        'remark' => $newRemark,
                    ],
                ]);

                $broadsheet->total = $newTotal;
                $broadsheet->grade = $newGrade;
                $broadsheet->remark = $newRemark;
                $broadsheet->save();
            }
        }

        return $results;
    }

    protected function updateMockClassMetrics($subjectclassid, $staffid, $termid, $sessionid)
    {
        $classMin = BroadsheetsMock::where('subjectclass_id', $subjectclassid)
            ->where('staff_id', $staffid)
            ->where('term_id', $termid)
            ->min('total');

        $classMax = BroadsheetsMock::where('subjectclass_id', $subjectclassid)
            ->where('staff_id', $staffid)
            ->where('term_id', $termid)
            ->max('total');

        $classAvg = $classMin && $classMax ? round(($classMin + $classMax) / 2, 1) : 0;

        BroadsheetsMock::where('subjectclass_id', $subjectclassid)
            ->where('staff_id', $staffid)
            ->where('term_id', $termid)
            ->update([
                'cmin' => $classMin ?? 0,
                'cmax' => $classMax ?? 0,
                'avg' => $classAvg,
            ]);
    }

    protected function updateMockSubjectPositions($subjectclassid, $staffid, $termid, $sessionid)
    {
        $rank = 0;
        $lastScore = null;
        $rows = 0;

        $classPos = BroadsheetsMock::where('broadsheetmock.subjectclass_id', $subjectclassid)
            ->where('broadsheetmock.staff_id', $staffid)
            ->where('broadsheetmock.term_id', $termid)
            ->leftJoin('broadsheet_records_mock', 'broadsheet_records_mock.id', '=', 'broadsheetmock.broadsheet_records_mock_id')
            ->where('broadsheet_records_mock.session_id', $sessionid)
            ->orderBy('broadsheetmock.total', 'DESC')
            ->get(['broadsheetmock.id', 'broadsheetmock.total', 'broadsheetmock.broadsheet_records_mock_id']);

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

            $broadsheetRecord = BroadsheetRecordMock::where('id', $row->broadsheet_records_mock_id)->first();

            if ($broadsheetRecord) {
                BroadsheetsMock::where('id', $row->id)
                    ->update(['subject_position_class' => $rankPos]);
            }
        }

        Log::info('Updated subject positions for mock exams across entire class', [
            'subjectclass_id' => $subjectclassid,
            'staff_id' => $staffid,
            'term_id' => $termid,
            'session_id' => $sessionid,
            'total_records' => $rows,
        ]);
    }
}