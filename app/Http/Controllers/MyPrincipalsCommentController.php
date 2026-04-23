<?php

namespace App\Http\Controllers;

use App\Models\Schoolterm;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use App\Models\Studentclass;
use Illuminate\Http\Request;
use App\Models\Schoolsession;
use App\Models\Principalscomment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Studentpersonalityprofile;

class MyPrincipalsCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View my-principals-comment',   ['only' => ['index']]);
        $this->middleware('permission:Update my-principals-comment', ['only' => ['classBroadsheet', 'updateComments']]);
    }

    // =========================================================================
    // INDEX – list of assigned classes
    // =========================================================================

    public function index()
    {
        $pagetitle = "My Principal's Comment Assignments";

        $assignments = Principalscomment::where('staffId', Auth::id())
            ->join('schoolclass',    'principalscomments.schoolclassid', '=', 'schoolclass.id')
            ->leftJoin('schoolarm',  'schoolarm.id',    '=', 'schoolclass.arm')
            ->leftJoin('schoolsession', 'principalscomments.sessionid', '=', 'schoolsession.id')
            ->leftJoin('schoolterm', 'principalscomments.termid',   '=', 'schoolterm.id')
            ->select([
                'principalscomments.id',
                'schoolclass.id as schoolclassid',
                'schoolclass.schoolclass as sclass',
                'schoolarm.arm as schoolarm',
                'schoolsession.session as session_name',
                'schoolterm.term as term_name',
                'principalscomments.updated_at',
            ])
            ->orderBy('schoolclass.schoolclass')
            ->orderBy('schoolarm.arm')
            ->get();

        $currentSession = Schoolsession::where('status', 'Current')->first()
            ?? Schoolsession::latest()->first();
        $currentTerm    = Schoolterm::latest()->first();

        return view('myprincipalscomment.index')
            ->with(compact('assignments', 'pagetitle', 'currentSession', 'currentTerm'));
    }

    // =========================================================================
    // CLASS BROADSHEET
    // =========================================================================

    public function classBroadsheet($schoolclassid, $sessionid, $termid)
    {
        $pagetitle = "Principal's Comment & Class Broadsheet";

        // ── Students ──────────────────────────────────────────────────────────
        $students = Studentclass::where('schoolclassid', $schoolclassid)
            ->where('sessionid', $sessionid)
            ->join('studentRegistration', 'studentRegistration.id', '=', 'studentclass.studentId')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->orderBy('studentRegistration.lastname')
            ->orderBy('studentRegistration.firstname')
            ->get([
                'studentRegistration.id as id',
                'studentRegistration.admissionNo as admissionNo',
                'studentRegistration.firstname as fname',
                'studentRegistration.lastname as lastname',
                'studentRegistration.othername as othername',
                'studentRegistration.gender as gender',
                'studentpicture.picture as picture',
            ]);

        // ── Subjects ──────────────────────────────────────────────────────────
        $subjects = Broadsheets::where('broadsheet_records.schoolclass_id', $schoolclassid)
            ->where('broadsheets.term_id', $termid)
            ->where('broadsheet_records.session_id', $sessionid)
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->distinct()
            ->orderBy('subject.subject')
            ->pluck('subject.subject')
            ->toArray();

        // ── Term scores (current term only) ───────────────────────────────────
        $termScores = Broadsheets::where('broadsheet_records.schoolclass_id', $schoolclassid)
            ->where('broadsheets.term_id', $termid)
            ->where('broadsheet_records.session_id', $sessionid)
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->get([
                'broadsheet_records.student_id',
                'subject.subject as subject_name',
                'broadsheets.total as total',
            ]);

        // ── Cumulative scores ─────────────────────────────────────────────────
        $cumulativeScores = Broadsheets::where('broadsheet_records.schoolclass_id', $schoolclassid)
            ->where('broadsheets.term_id', $termid)
            ->where('broadsheet_records.session_id', $sessionid)
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->get([
                'broadsheet_records.student_id',
                'subject.subject as subject_name',
                'broadsheets.cum as total',
            ]);

        // Alias for blade compatibility
        $scores = $cumulativeScores;

        // ── Existing comments ─────────────────────────────────────────────────
        $profiles = Studentpersonalityprofile::where('schoolclassid', $schoolclassid)
            ->where('termid', $termid)
            ->where('sessionid', $sessionid)
            ->pluck('principalscomment', 'studentid')
            ->toArray();

        // ── Class meta ────────────────────────────────────────────────────────
        $schoolclass             = Schoolclass::with('arm')->findOrFail($schoolclassid);
        $schoolclass->arm_name   = $schoolclass->arm?->arm ?? '';
        $schoolterm              = Schoolterm::find($termid)?->term    ?? 'N/A';
        $schoolsession           = Schoolsession::find($sessionid)?->session ?? 'N/A';
        $classCategory           = $schoolclass->classcategory()->first();
        $isSenior                = $classCategory?->is_senior ?? false;

        // ── Grade analysis ────────────────────────────────────────────────────
        $rawGrades = Broadsheets::where('broadsheet_records.schoolclass_id', $schoolclassid)
            ->where('broadsheets.term_id', $termid)
            ->where('broadsheet_records.session_id', $sessionid)
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->select([
                'broadsheet_records.student_id',
                'subject.subject as subject_name',
                'broadsheets.cum as total',
            ])
            ->get();

        $studentGrades        = [];
        $studentGradeAnalysis = [];

        foreach ($rawGrades as $row) {
            $total       = $row->total      ?? 0;
            $studentId   = $row->student_id;
            $subjectName = $row->subject_name;

            if (!isset($studentGradeAnalysis[$studentId])) {
                $studentGradeAnalysis[$studentId] = [
                    'grades'        => [],
                    'counts'        => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0],
                    'weak_subjects' => [],
                ];
            }

            [$grade, $gradeLetter] = $this->calculateGrade($total, $isSenior);

            $termScore  = $termScores->where('student_id', $studentId)->where('subject_name', $subjectName)->first();
            $termTotal  = $termScore?->total ?? 0;

            $gradeEntry = [
                'subject'      => $subjectName,
                'score'        => $total,
                'term_score'   => $termTotal,
                'grade'        => $grade,
                'grade_letter' => $gradeLetter,
            ];

            $studentGrades[$studentId][]                              = $gradeEntry;
            $studentGradeAnalysis[$studentId]['grades'][]             = $gradeEntry;
            $studentGradeAnalysis[$studentId]['counts'][$gradeLetter]++;

            if (in_array($gradeLetter, ['C', 'D', 'E', 'F'])) {
                $studentGradeAnalysis[$studentId]['weak_subjects'][] = [
                    'subject'          => $subjectName,
                    'grade'            => $grade,
                    'grade_letter'     => $gradeLetter,
                    'cumulative_score' => $total,
                    'term_score'       => $termTotal,
                ];
            }
        }

        // ── Standard personalised comments ────────────────────────────────────
        $baseTemplates = [
            "Excellent result {NAME}, keep it up!",
            "A very good result {NAME}, keep it up!",
            "Good result {NAME}, keep it up!",
            "Average result {NAME}, there's still room for improvement next term.",
            "{NAME}, you can do better next term.",
            "{NAME}, you need to sit up and be serious.",
            "{NAME}, wake up and be serious.",
        ];

        $standardPersonalizedComments = [];

        foreach ($students as $student) {
            $studentId   = $student->id;
            $firstName   = $student->fname;
            $weakSubjects= $studentGradeAnalysis[$studentId]['weak_subjects'] ?? [];

            // ── FIX: use correct second-person pronouns ────────────────────
            // Comments speak directly TO the student, so always use "you/your"
            $advice = '';
            if (!empty($weakSubjects)) {
                usort($weakSubjects, function ($a, $b) {
                    $order = ['F' => 0, 'E' => 1, 'D' => 2, 'C' => 3];
                    return $order[$a['grade_letter']] <=> $order[$b['grade_letter']];
                });

                $subjectList  = array_map(fn($ws) => strtoupper($ws['subject']) . ' (' . $ws['grade'] . ')', $weakSubjects);
                $subjectsText = $this->formatList($subjectList);

                // Correct: "You should work harder … to improve your performance."
                $advice = "\n\nYou should work harder in {$subjectsText} to improve your performance.";
            }

            $options = [];
            foreach ($baseTemplates as $template) {
                $comment   = str_replace('{NAME}', $firstName, $template);
                $options[] = $comment . $advice;
            }

            $standardPersonalizedComments[$studentId] = $options;
        }

        // ── Intelligent comments (third-person) ───────────────────────────────
        $intelligentComments = [];

        foreach ($students as $student) {
            $studentId   = $student->id;
            $firstName   = $student->fname;
            $analysis    = $studentGradeAnalysis[$studentId] ?? ['counts' => [], 'weak_subjects' => []];

            // Grade summary sentence
            $gradeParts = [];
            foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $g) {
                $count = $analysis['counts'][$g] ?? 0;
                if ($count > 0) $gradeParts[] = "$count {$g}" . ($count > 1 ? "'s" : '');
            }
            $gradeSummary = !empty($gradeParts)
                ? $this->formatList($gradeParts)
                : 'no grades recorded';

            // Term context
            $termInfo = '';
            if (in_array($schoolterm, ['2nd Term', 'Second Term'])) {
                $termInfo = ' (Cumulative average of 1st and 2nd terms)';
            } elseif (in_array($schoolterm, ['3rd Term', 'Third Term'])) {
                $termInfo = ' (Cumulative average of 1st, 2nd and 3rd terms)';
            }

            // Base comment from performance %
            $totalGrades  = array_sum($analysis['counts']);
            $goodGrades   = ($analysis['counts']['A'] ?? 0) + ($analysis['counts']['B'] ?? 0);
            $percentGood  = $totalGrades > 0 ? ($goodGrades / $totalGrades) * 100 : 0;

            $baseComment = match (true) {
                $percentGood >= 80 => "Excellent result {NAME}, keep it up!",
                $percentGood >= 70 => "A very good result {NAME}, keep it up!",
                $percentGood >= 60 => "Good result {NAME}, keep it up!",
                $percentGood >= 50 => "Average result {NAME}, there's still room for improvement next term.",
                $percentGood >= 40 => "{NAME}, you can do better next term.",
                $percentGood >= 30 => "{NAME}, you need to sit up and be serious.",
                default            => "{NAME}, wake up and be serious.",
            };

            $comment = "{$firstName} has {$gradeSummary}{$termInfo}. " . str_replace('{NAME}', $firstName, $baseComment);

            // ── FIX: correct third-person pronouns based on gender ────────────
            $isMale     = strtoupper($student->gender) === 'MALE';
            $pronoun    = $isMale ? 'He'  : 'She';
            $possessive = $isMale ? 'his' : 'her';

            $weakSubjects = $analysis['weak_subjects'] ?? [];
            if (!empty($weakSubjects)) {
                usort($weakSubjects, function ($a, $b) {
                    $order = ['F' => 0, 'E' => 1, 'D' => 2, 'C' => 3];
                    return $order[$a['grade_letter']] <=> $order[$b['grade_letter']];
                });

                $subjectList  = array_map(fn($ws) => $ws['subject'] . ' (' . $ws['grade'] . ')', $weakSubjects);
                $subjectsText = $this->formatList($subjectList);

                // Correct: "He/She should work harder … to improve his/her performance."
                $comment .= "\n\n{$pronoun} should work harder in {$subjectsText} to improve {$possessive} performance.";
            }

            $intelligentComments[$studentId] = $comment;
        }

        // ── Student totals & positions ────────────────────────────────────────
        $studentTotals     = [];
        $studentTermTotals = [];

        foreach ($students as $student) {
            $sid      = $student->id;
            $totalCum = 0;
            $totalTerm= 0;
            $count    = 0;

            foreach ($subjects as $subject) {
                $cumRow  = $cumulativeScores->where('student_id', $sid)->where('subject_name', $subject)->first();
                $termRow = $termScores->where('student_id', $sid)->where('subject_name', $subject)->first();

                if ($cumRow)  { $totalCum  += $cumRow->total;  $count++; }
                if ($termRow) { $totalTerm += $termRow->total; }
            }

            $studentTotals[$sid] = [
                'total'    => $totalCum,
                'average'  => $count > 0 ? round($totalCum  / $count, 1) : 0,
                'subjects' => $count,
            ];
            $studentTermTotals[$sid] = [
                'total'   => $totalTerm,
                'average' => $count > 0 ? round($totalTerm / $count, 1) : 0,
            ];
        }

        // Positions (by cumulative average)
        $sortedStudents = $students->sortByDesc(fn($s) => $studentTotals[$s->id]['average'] ?? 0)->values();
        $positions      = [];
        $rank           = 1;
        $prevAvg        = null;

        foreach ($sortedStudents as $index => $student) {
            $avg = $studentTotals[$student->id]['average'];
            if ($index > 0 && $avg < $prevAvg) $rank = $index + 1;
            $positions[$student->id] = $rank;
            $prevAvg = $avg;
        }

        // Class analytics
        $classTotalScore    = array_sum(array_column($studentTotals, 'total'));
        $classTotalSubjects = array_sum(array_column($studentTotals, 'subjects'));
        $classAnalytics     = [
            'average'        => $classTotalSubjects > 0 ? round($classTotalScore / $classTotalSubjects, 1) : 0,
            'total_students' => $students->count(),
        ];

        // Per-student analytics
        $studentAnalytics = [];
        foreach ($students as $student) {
            $sid      = $student->id;
            $analysis = $studentGradeAnalysis[$sid] ?? ['counts' => []];
            $totals   = $studentTotals[$sid];
            $termTots = $studentTermTotals[$sid];
            $position = $positions[$sid] ?? null;

            $studentAnalytics[$sid] = [
                'total_score'   => $totals['total'],
                'average'       => $totals['average'],
                'term_total'    => $termTots['total'],
                'term_average'  => $termTots['average'],
                'subjects'      => $totals['subjects'],
                'position'      => $position,
                'position_text' => $position ? $this->ordinal($position) : '-',
                'grade_counts'  => $analysis['counts'],
            ];
        }

        return view('myprincipalscomment.classbroadsheet')->with(compact(
            'students',
            'subjects',
            'termScores',
            'scores',
            'profiles',
            'schoolclass',
            'schoolterm',
            'schoolsession',
            'schoolclassid',
            'sessionid',
            'termid',
            'pagetitle',
            'studentGrades',
            'studentGradeAnalysis',
            'intelligentComments',
            'standardPersonalizedComments',
            'studentAnalytics',
            'classAnalytics',
            'isSenior'
        ));
    }

    // =========================================================================
    // UPDATE COMMENTS
    // =========================================================================

    public function updateComments(Request $request, $schoolclassid, $sessionid, $termid)
    {
        Log::info('Update Comments Request Received', [
            'schoolclassid'  => $schoolclassid,
            'sessionid'      => $sessionid,
            'termid'         => $termid,
            'auth_id'        => Auth::id(),
            'request_method' => $request->method(),
            'ajax'           => $request->ajax(),
        ]);

        $request->validate(['teacher_comments.*' => 'nullable|string|max:5000']);

        $comments      = $request->input('teacher_comments', []);
        $updatedCount  = 0;
        $createdCount  = 0;
        $skippedCount  = 0;

        DB::beginTransaction();
        try {
            foreach ($comments as $studentId => $comment) {
                if (is_null($comment) || trim($comment) === '') {
                    $skippedCount++;
                    continue;
                }

                $comment = trim(strip_tags($comment));
                $comment = html_entity_decode($comment, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                $existing = Studentpersonalityprofile::where('studentid',    $studentId)
                    ->where('schoolclassid', $schoolclassid)
                    ->where('sessionid',     $sessionid)
                    ->where('termid',        $termid)
                    ->first();

                if ($existing) {
                    if ($existing->principalscomment !== $comment) {
                        $existing->update([
                            'staffid'           => Auth::id(),
                            'principalscomment' => $comment,
                        ]);
                        $updatedCount++;
                    }
                } else {
                    Studentpersonalityprofile::create([
                        'studentid'         => $studentId,
                        'schoolclassid'     => $schoolclassid,
                        'sessionid'         => $sessionid,
                        'termid'            => $termid,
                        'staffid'           => Auth::id(),
                        'principalscomment' => $comment,
                    ]);
                    $createdCount++;
                }
            }

            DB::commit();

            $totalProcessed = $updatedCount + $createdCount;
            $message = $totalProcessed > 0
                ? "Successfully saved: {$updatedCount} updated, {$createdCount} created. Skipped: {$skippedCount} empty."
                : "No changes detected. {$skippedCount} empty comments skipped.";

            Log::info('Update completed', [
                'updated' => $updatedCount,
                'created' => $createdCount,
                'skipped' => $skippedCount,
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'updated' => $updatedCount,
                'created' => $createdCount,
                'skipped' => $skippedCount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving principals comments', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Calculate grade and grade letter from a score.
     */
    private function calculateGrade(float $total, bool $isSenior): array
    {
        if ($isSenior) {
            return match (true) {
                $total >= 75 => ['A1', 'A'],
                $total >= 70 => ['B2', 'B'],
                $total >= 65 => ['B3', 'B'],
                $total >= 60 => ['C4', 'C'],
                $total >= 55 => ['C5', 'C'],
                $total >= 50 => ['C6', 'C'],
                $total >= 45 => ['D7', 'D'],
                $total >= 40 => ['E8', 'E'],
                default      => ['F9', 'F'],
            };
        }

        return match (true) {
            $total >= 70 => ['A', 'A'],
            $total >= 60 => ['B', 'B'],
            $total >= 50 => ['C', 'C'],
            $total >= 40 => ['D', 'D'],
            default      => ['F', 'F'],
        };
    }

    /**
     * Format an array into a human-readable list.
     * e.g. ['a','b','c'] → "a, b and c"
     */
    private function formatList(array $items): string
    {
        if (count($items) === 1) return $items[0];
        if (count($items) === 2) return implode(' and ', $items);
        return implode(', ', array_slice($items, 0, -1)) . ' and ' . end($items);
    }

    /**
     * Return ordinal suffix for a number (1st, 2nd, 3rd …).
     */
    private function ordinal(int $num): string
    {
        if ($num % 100 >= 11 && $num % 100 <= 13) return $num . 'th';
        return $num . match ($num % 10) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };
    }
}
