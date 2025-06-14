<?php

namespace App\Http\Controllers;

use App\Exports\MarksSheetExport;
use App\Exports\RecordsheetExport;
use App\Imports\ScoresheetImport;
use App\Models\Broadsheets;
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
        ])->sortBy('admissionno');

        foreach ($results as $broadsheet) {
            $newBf = $this->getPreviousTermCum(
                $broadsheet->student_id,
                $broadsheet->subject_id,
                $termId,
                $sessionId
            );
            $caAverage = ($broadsheet->ca1 + $broadsheet->ca2 + $broadsheet->ca3) / 3;
            $newTotal = round(($caAverage + $broadsheet->exam) / 2, 1);
            $newCum = round(($newBf + $newTotal) / 2, 2);
            $newGrade = $this->calculateGrade($newTotal);
            $newRemark = $this->getRemark($newGrade);

            if (
                $broadsheet->bf != $newBf ||
                $broadsheet->total != $newTotal ||
                $broadsheet->cum != $newCum ||
                $broadsheet->grade != $newGrade ||
                $broadsheet->remark != $newRemark
            ) {
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

    protected function getPreviousTermCum($studentId, $subjectId, $termId, $sessionId)
    {
        if ($termId == 1) {
            Log::info("Term ID is 1, setting bf to 0 for student_id: {$studentId}, subject_id: {$subjectId}");
            return 0;
        }

        $previousTerm = Broadsheets::where('broadsheet_records.student_id', $studentId)
            ->where('broadsheet_records.subject_id', $subjectId)
            ->where('broadsheets.term_id', $termId - 1)
            ->where('broadsheet_records.session_id', $sessionId)
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->value('broadsheets.cum');

        if (is_null($previousTerm)) {
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
            'cum' => $previousTerm,
        ]);

        return round($previousTerm, 2);
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

            $broadsheetRecord = DB::table('broadsheet_records')
                ->where('id', $row->broadsheet_record_id)
                ->first();

            if ($broadsheetRecord) {
                Broadsheets::where('id', $row->id)
                    ->update(['subject_position_class' => $rankPos]);
            }
        }
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
        $cum = round(($bf + $total) / 2, 2);
        $grade = $this->calculateGrade($total);
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

    public function destroy($id)
    {
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
        Log::info('bulkUpdateScores: Request received', [
            'scores_count' => count($request->input('scores', [])),
            'user_id' => $request->user()->id
        ]);

        $request->validate([
            'scores' => 'required|array|min:1',
            'scores.*.id' => 'required|integer|exists:broadsheets,id',
            'scores.*.ca1' => 'nullable|numeric|min:0|max:100',
            'scores.*.ca2' => 'nullable|numeric|min:0|max:100',
            'scores.*.ca3' => 'nullable|numeric|min:0|max:100',
            'scores.*.exam' => 'nullable|numeric|min:0|max:100',
        ], [
            'scores.*.id.exists' => 'Broadsheet ID :input does not exist.',
            'scores.required' => 'No scores provided for update.',
        ]);

        DB::beginTransaction();
        try {
            $updatedCount = 0;
            $subjectclassId = null;
            $staffId = null;
            $termId = null;
            $sessionId = null;
            $schoolclassId = null;
            $updatedBroadsheets = [];

            foreach ($request->scores as $index => $scoreData) {
                $broadsheet = Broadsheets::find($scoreData['id']);
                if (!$broadsheet) {
                    Log::warning('bulkUpdateScores: Broadsheet not found', [
                        'id' => $scoreData['id'],
                        'index' => $index
                    ]);
                    continue;
                }

                $broadsheetRecord = DB::table('broadsheet_records')
                    ->where('id', $broadsheet->broadsheet_record_id)
                    ->first(['student_id', 'subject_id', 'session_id', 'schoolclass_id']);

                if (!$broadsheetRecord) {
                    Log::error('bulkUpdateScores: Broadsheet record not found', [
                        'broadsheet_id' => $scoreData['id'],
                        'broadsheet_record_id' => $broadsheet->broadsheet_record_id
                    ]);
                    continue;
                }

                $ca1 = $scoreData['ca1'] ?? 0;
                $ca2 = $scoreData['ca2'] ?? 0;
                $ca3 = $scoreData['ca3'] ?? 0;
                $exam = $scoreData['exam'] ?? 0;
                $caAverage = ($ca1 + $ca2 + $ca3) / 3;
                $total = round(($caAverage + $exam) / 2, 1);
                $bf = $this->getPreviousTermCum(
                    $broadsheetRecord->student_id,
                    $broadsheetRecord->subject_id,
                    $broadsheet->term_id,
                    $broadsheetRecord->session_id
                );
                $cum = round(($bf + $total) / 2, 2);
                $grade = $this->calculateGrade($total);
                $remark = $this->getRemark($grade);

                Log::debug('bulkUpdateScores: Updating broadsheet', [
                    'id' => $broadsheet->id,
                    'scores' => compact('ca1', 'ca2', 'ca3', 'exam', 'total', 'bf', 'cum', 'grade', 'remark')
                ]);

                $updated = $broadsheet->update([
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

                if ($updated) {
                    $updatedCount++;
                    $freshBroadsheet = $broadsheet->fresh()->load([
                        'broadsheetRecord.student',
                        'broadsheetRecord.subject'
                    ]);
                    $updatedBroadsheets[] = [
                        'id' => $freshBroadsheet->id,
                        'admissionno' => $freshBroadsheet->broadsheetRecord->student->admissionNO ?? null,
                        'fname' => $freshBroadsheet->broadsheetRecord->student->firstname ?? null,
                        'lname' => $freshBroadsheet->broadsheetRecord->student->lastname ?? null,
                        'ca1' => $freshBroadsheet->ca1,
                        'ca2' => $freshBroadsheet->ca2,
                        'ca3' => $freshBroadsheet->ca3,
                        'exam' => $freshBroadsheet->exam,
                        'total' => $freshBroadsheet->total,
                        'bf' => $freshBroadsheet->bf,
                        'cum' => $freshBroadsheet->cum,
                        'grade' => $freshBroadsheet->grade,
                        'position' => $freshBroadsheet->subject_position_class,
                    ];
                }

                if (!$subjectclassId) {
                    $subjectclassId = $broadsheet->subjectclass_id;
                    $staffId = $broadsheet->staff_id;
                    $termId = $broadsheet->term_id;
                    $sessionId = $broadsheetRecord->session_id;
                    $schoolclassId = $broadsheetRecord->schoolclass_id;
                }
            }

            if ($updatedCount === 0) {
                DB::rollBack();
                Log::warning('bulkUpdateScores: No scores updated');
                return response()->json([
                    'success' => false,
                    'message' => 'No scores were updated.',
                ], 400);
            }

            if ($subjectclassId && $staffId && $termId && $sessionId && $schoolclassId) {
                $this->updateClassMetrics($subjectclassId, $staffId, $termId, $sessionId);
                $this->updateSubjectPositions($subjectclassId, $staffId, $termId, $sessionId);
                $this->updateClassPositions($schoolclassId, $termId, $sessionId);
            }

            DB::commit();

            Log::info('bulkUpdateScores: Success', [
                'updated_count' => $updatedCount,
                'broadsheets_count' => count($updatedBroadsheets)
            ]);

            return response()->json([
                'success' => true,
                'message' => "Updated {$updatedCount} scores.",
                'updated_count' => $updatedCount,
                'broadsheets' => $updatedBroadsheets,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('bulkUpdateScores: Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update scores: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function import(Request $request)
    {
        Log::info('import: Request received', [
            'user_id' => $request->user()->id,
            'has_file' => $request->hasFile('file')
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

            Log::debug('import: Starting import', $importData);

            $import = new ScoresheetImport($importData);
            Excel::import($import, $request->file('file'));

            $updatedBroadsheets = $import->getUpdatedBroadsheets();
            $failures = $import->getFailures();

            Log::info('import: Success', [
                'updated_broadsheets_count' => count($updatedBroadsheets),
                'failures_count' => count($failures)
            ]);

            $message = "Scores imported successfully! Updated " . count($updatedBroadsheets) . " records.";
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
            Log::error('import: Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to import scores: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function afterImport()
{
    try {
        $subjectclass_id = $this->data['subjectclass_id'];
        $staff_id = $this->data['staff_id'];
        $term_id = $this->data['term_id'];
        $session_id = $this->data['session_id'];
        $schoolclass_id = $this->data['schoolclass_id'];

        Log::info('ScoresheetImport: Running afterImport', [
            'subjectclass_id' => $subjectclass_id,
            'staff_id' => $staff_id,
            'term_id' => $term_id,
            'session_id' => $session_id,
            'schoolclass_id' => $schoolclass_id,
            'updated_broadsheets' => count($this->updatedBroadsheets),
            'failures' => count($this->failures)
        ]);

        // Use DB transaction for better performance and data integrity
        DB::transaction(function () use ($subjectclass_id, $staff_id, $term_id, $session_id, $schoolclass_id) {
            
            // Update class metrics with single query
            $metrics = Broadsheets::where('subjectclass_id', $subjectclass_id)
                ->where('staff_id', $staff_id)
                ->where('term_id', $term_id)
                ->selectRaw('MIN(total) as min_total, MAX(total) as max_total, AVG(total) as avg_total')
                ->first();

            $classMin = $metrics->min_total ?? 0;
            $classMax = $metrics->max_total ?? 0;
            $classAvg = $metrics->avg_total ? round($metrics->avg_total, 1) : 0;

            // Bulk update class metrics
            Broadsheets::where('subjectclass_id', $subjectclass_id)
                ->where('staff_id', $staff_id)
                ->where('term_id', $term_id)
                ->update([
                    'cmin' => $classMin,
                    'cmax' => $classMax,
                    'avg' => $classAvg,
                ]);

            // Update subject positions using window functions (more efficient)
            DB::statement("
                UPDATE broadsheets b1 
                JOIN (
                    SELECT id,
                        CASE 
                            WHEN ROW_NUMBER() OVER (ORDER BY total DESC) = 1 THEN CONCAT(ROW_NUMBER() OVER (ORDER BY total DESC), 'st')
                            WHEN ROW_NUMBER() OVER (ORDER BY total DESC) = 2 THEN CONCAT(ROW_NUMBER() OVER (ORDER BY total DESC), 'nd')
                            WHEN ROW_NUMBER() OVER (ORDER BY total DESC) = 3 THEN CONCAT(ROW_NUMBER() OVER (ORDER BY total DESC), 'rd')
                            ELSE CONCAT(ROW_NUMBER() OVER (ORDER BY total DESC), 'th')
                        END as position
                    FROM broadsheets 
                    WHERE subjectclass_id = ? AND staff_id = ? AND term_id = ?
                    ORDER BY total DESC
                ) b2 ON b1.id = b2.id 
                SET b1.subject_position_class = b2.position
            ", [$subjectclass_id, $staff_id, $term_id]);

            // Update class positions with similar optimization
            DB::statement("
                UPDATE promotion_status p1 
                JOIN (
                    SELECT id,
                        CASE 
                            WHEN ROW_NUMBER() OVER (ORDER BY subjectstotalscores DESC) = 1 THEN CONCAT(ROW_NUMBER() OVER (ORDER BY subjectstotalscores DESC), 'st')
                            WHEN ROW_NUMBER() OVER (ORDER BY subjectstotalscores DESC) = 2 THEN CONCAT(ROW_NUMBER() OVER (ORDER BY subjectstotalscores DESC), 'nd')
                            WHEN ROW_NUMBER() OVER (ORDER BY subjectstotalscores DESC) = 3 THEN CONCAT(ROW_NUMBER() OVER (ORDER BY subjectstotalscores DESC), 'rd')
                            ELSE CONCAT(ROW_NUMBER() OVER (ORDER BY subjectstotalscores DESC), 'th')
                        END as position
                    FROM promotion_status 
                    WHERE schoolclassid = ? AND termid = ? AND sessionid = ?
                    ORDER BY subjectstotalscores DESC
                ) p2 ON p1.id = p2.id 
                SET p1.position = p2.position
            ", [$schoolclass_id, $term_id, $session_id]);
        });

        Log::info('ScoresheetImport: afterImport completed successfully', [
            'updated_broadsheets' => count($this->updatedBroadsheets),
            'failures' => count($this->failures)
        ]);

    } catch (\Exception $e) {
        Log::error('ScoresheetImport: Error in afterImport', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e; // Re-throw to ensure the import fails if afterImport fails
    }
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
                'users.name as staff_name'
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
            return redirect()->back()->with('error', 'Missing required data for download.');
        }
    
        try {
            $export = new MarksSheetExport($subjectclassId, $staffId, $termId, $sessionId, $schoolclassId);
            return $export->download();
            
        } catch (\Exception $e) {
            Log::error('Marksheet download error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate marksheet: ' . $e->getMessage());
        }
    }
}