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
use App\Models\Studentpersonalityprofile;

class MyPrincipalsCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View my-principals-comment', ['only' => ['index']]);
        $this->middleware('permission:Update my-principals-comment', ['only' => ['classBroadsheet', 'updateComments']]);
    }

    public function index()
    {
        $pagetitle = "My Principal's Comment Assignments";

        $assignments = Principalscomment::where('staffId', Auth::id())
            ->join('schoolclass', 'principalscomments.schoolclassid', '=', 'schoolclass.id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('schoolsession', 'principalscomments.sessionid', '=', 'schoolsession.id')
            ->leftJoin('schoolterm', 'principalscomments.termid', '=', 'schoolterm.id')
            ->select([
                'principalscomments.id',
                'schoolclass.id as schoolclassid',
                'schoolclass.schoolclass as sclass',
                'schoolarm.arm as schoolarm',
                'schoolsession.session as session_name',
                'schoolterm.term as term_name',
                'principalscomments.updated_at'
            ])
            ->orderBy('schoolclass.schoolclass')
            ->orderBy('schoolarm.arm')
            ->get();

        $currentSession = Schoolsession::where('status', 'Current')->first() ?? Schoolsession::latest()->first();
        $currentTerm = Schoolterm::latest()->first();

        return view('myprincipalscomment.index')
            ->with(compact('assignments', 'pagetitle', 'currentSession', 'currentTerm'));
    }

 public function classBroadsheet($schoolclassid, $sessionid, $termid)
{
    $isAssigned = Principalscomment::where('staffId', Auth::id())
        ->where('schoolclassid', $schoolclassid)
        ->where('sessionid', $sessionid)
        ->where('termid', $termid)
        ->exists();

    $pagetitle = "Principal's Comment & Class Broadsheet";

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

    $subjects = Broadsheets::where('broadsheet_records.schoolclass_id', $schoolclassid)
        ->where('broadsheets.term_id', $termid)
        ->where('broadsheet_records.session_id', $sessionid)
        ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
        ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
        ->distinct()
        ->orderBy('subject.subject')
        ->pluck('subject.subject')
        ->toArray();

    // Get TERM scores (current term only)
    $termScores = Broadsheets::where('broadsheet_records.schoolclass_id', $schoolclassid)
        ->where('broadsheets.term_id', $termid)
        ->where('broadsheet_records.session_id', $sessionid)
        ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
        ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
        ->get([
            'broadsheet_records.student_id',
            'subject.subject as subject_name',
            'broadsheets.total as total',  // Term total score
        ]);

    // Get CUMULATIVE scores (includes previous terms)
    $cumulativeScores = Broadsheets::where('broadsheet_records.schoolclass_id', $schoolclassid)
        ->where('broadsheets.term_id', $termid)
        ->where('broadsheet_records.session_id', $sessionid)
        ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
        ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
        ->get([
            'broadsheet_records.student_id',
            'subject.subject as subject_name',
            'broadsheets.cum as total',  // Cumulative score (averaged across terms)
        ]);

    $profiles = Studentpersonalityprofile::where('schoolclassid', $schoolclassid)
        ->where('termid', $termid)
        ->where('sessionid', $sessionid)
        ->pluck('principalscomment', 'studentid')
        ->toArray();

    $schoolclass = Schoolclass::with('arm')->findOrFail($schoolclassid);
    $schoolclass->arm_name = $schoolclass->arm?->arm ?? '';

    $schoolterm = Schoolterm::find($termid)?->term ?? 'N/A';
    $schoolsession = Schoolsession::find($sessionid)?->session ?? 'N/A';

    $classCategory = $schoolclass->classcategory()->first();
    $isSenior = $classCategory?->is_senior ?? false;

    // Prepare raw grades for cumulative analysis
    $rawGrades = Broadsheets::where('broadsheet_records.schoolclass_id', $schoolclassid)
        ->where('broadsheets.term_id', $termid)
        ->where('broadsheet_records.session_id', $sessionid)
        ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
        ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
        ->select([
            'broadsheet_records.student_id',
            'subject.subject as subject_name',
            'broadsheets.cum as total',  // Use cumulative for grade calculation
        ])
        ->get();

    $studentGrades = [];
    $studentGradeAnalysis = [];

    foreach ($rawGrades as $row) {
        $total = $row->total ?? 0;
        $studentId = $row->student_id;
        $subjectName = $row->subject_name;

        if (!isset($studentGradeAnalysis[$studentId])) {
            $studentGradeAnalysis[$studentId] = [
                'grades' => [],
                'counts' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0],
                'weak_subjects' => []
            ];
        }

        // Grade calculation based on CUMULATIVE score
        $grade = 'F';
        $gradeLetter = 'F';

        if ($isSenior) {
            if ($total >= 75 && $total <= 100) {
                $grade = 'A1';
                $gradeLetter = 'A';
            } elseif ($total >= 70) {
                $grade = 'B2';
                $gradeLetter = 'B';
            } elseif ($total >= 65) {
                $grade = 'B3';
                $gradeLetter = 'B';
            } elseif ($total >= 60) {
                $grade = 'C4';
                $gradeLetter = 'C';
            } elseif ($total >= 55) {
                $grade = 'C5';
                $gradeLetter = 'C';
            } elseif ($total >= 50) {
                $grade = 'C6';
                $gradeLetter = 'C';
            } elseif ($total >= 45) {
                $grade = 'D7';
                $gradeLetter = 'D';
            } elseif ($total >= 40) {
                $grade = 'E8';
                $gradeLetter = 'E';
            } else {
                $grade = 'F9';
                $gradeLetter = 'F';
            }
        } else {
            if ($total >= 70 && $total <= 100) {
                $grade = 'A';
                $gradeLetter = 'A';
            } elseif ($total >= 60) {
                $grade = 'B';
                $gradeLetter = 'B';
            } elseif ($total >= 50) {
                $grade = 'C';
                $gradeLetter = 'C';
            } elseif ($total >= 40) {
                $grade = 'D';
                $gradeLetter = 'D';
            } else {
                $grade = 'F';
                $gradeLetter = 'F';
            }
        }

        // Also get term score for display
        $termScore = $termScores->where('student_id', $studentId)->where('subject_name', $subjectName)->first();
        $termTotal = $termScore?->total ?? 0;

        $studentGrades[$studentId][] = [
            'subject' => $subjectName,
            'score' => $total,  // Cumulative score
            'term_score' => $termTotal,  // Term score
            'grade' => $grade,
            'grade_letter' => $gradeLetter
        ];

        $studentGradeAnalysis[$studentId]['grades'][] = [
            'subject' => $subjectName,
            'score' => $total,
            'term_score' => $termTotal,
            'grade' => $grade,
            'grade_letter' => $gradeLetter
        ];

        $studentGradeAnalysis[$studentId]['counts'][$gradeLetter]++;

        if (in_array($gradeLetter, ['C', 'D', 'E', 'F'])) {
            $studentGradeAnalysis[$studentId]['weak_subjects'][] = [
                'subject' => $subjectName,
                'grade' => $grade,
                'grade_letter' => $gradeLetter,
                'cumulative_score' => $total,
                'term_score' => $termTotal
            ];
        }
    }

    // Generate standard personalized comments
    $standardPersonalizedComments = [];

    $baseTemplates = [
        "Excellent result {NAME}, keep it up!",
        "A very good result {NAME}, keep it up!",
        "Good result {NAME}, keep it up!",
        "Average result {NAME}, there's still room for improvement next term.",
        "{NAME}, you can do better next term.",
        "{NAME}, you need to sit up and be serious.",
        "{NAME}, wake up and be serious.",
    ];

    foreach ($students as $student) {
        $studentId = $student->id;
        $firstName = $student->fname;

        $pronoun = strtoupper($student->gender) === 'MALE' ? 'You' : 'You';
        $possessive = strtoupper($student->gender) === 'MALE' ? 'You' : 'You';

        $weakSubjects = $studentGradeAnalysis[$studentId]['weak_subjects'] ?? [];
        $advice = '';

        if (!empty($weakSubjects)) {
            usort($weakSubjects, function($a, $b) {
                $order = ['F' => 0, 'E' => 1, 'D' => 2, 'C' => 3];
                return $order[$a['grade_letter']] <=> $order[$b['grade_letter']];
            });

            $subjectList = array_map(fn($ws) => strtoupper($ws['subject']) . " (" . $ws['grade'] . ")", $weakSubjects);

            $subjectsText = count($subjectList) == 1
                ? $subjectList[0]
                : (count($subjectList) == 2 ? implode(' and ', $subjectList) : implode(', ', array_slice($subjectList, 0, -1)) . " and " . end($subjectList));

            $advice = "\n\n$pronoun should work harder in $subjectsText to improve $possessive performance.";
        }

        $options = [];
        foreach ($baseTemplates as $template) {
            $comment = str_replace('{NAME}', $firstName, $template);
            $options[] = $comment . $advice;
        }

        $standardPersonalizedComments[$studentId] = $options;
    }

    // Intelligent comments based on CUMULATIVE performance
    $intelligentComments = [];
    foreach ($students as $student) {
        $studentId = $student->id;
        $firstName = $student->fname;
        $analysis = $studentGradeAnalysis[$studentId] ?? ['counts' => [], 'weak_subjects' => []];

        $gradeParts = [];
        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $g) {
            $count = $analysis['counts'][$g] ?? 0;
            if ($count > 0) $gradeParts[] = "$count " . $g . ($count > 1 ? "'s" : '');
        }

        $gradeSummary = !empty($gradeParts)
            ? (count($gradeParts) == 1 ? $gradeParts[0] : implode(', ', array_slice($gradeParts, 0, -1)) . ' and ' . end($gradeParts))
            : 'no grades recorded';

        $totalGrades = array_sum($analysis['counts']);
        $goodGrades = ($analysis['counts']['A'] ?? 0) + ($analysis['counts']['B'] ?? 0);
        $percentageGood = $totalGrades > 0 ? ($goodGrades / $totalGrades) * 100 : 0;

        $baseComment = "{NAME}, wake up and be serious.";
        if ($percentageGood >= 80) $baseComment = "Excellent result {NAME}, keep it up!";
        elseif ($percentageGood >= 70) $baseComment = "A very good result {NAME}, keep it up!";
        elseif ($percentageGood >= 60) $baseComment = "Good result {NAME}, keep it up!";
        elseif ($percentageGood >= 50) $baseComment = "Average result {NAME}, there's still room for improvement next term.";
        elseif ($percentageGood >= 40) $baseComment = "{NAME}, you can do better next term.";
        elseif ($percentageGood >= 30) $baseComment = "{NAME}, you need to sit up and be serious.";

        // Add term info to comment
        $termInfo = "";
        if ($schoolterm == '2nd Term' || $schoolterm == 'Second Term') {
            $termInfo = " (Cumulative average of 1st and 2nd terms)";
        } elseif ($schoolterm == '3rd Term' || $schoolterm == 'Third Term') {
            $termInfo = " (Cumulative average of 1st, 2nd and 3rd terms)";
        }

        $comment = "$firstName has $gradeSummary$termInfo. " . str_replace('{NAME}', $firstName, $baseComment);

        $pronoun = strtoupper($student->gender) === 'MALE' ? 'He' : 'She';
        $possessive = strtoupper($student->gender) === 'MALE' ? 'his' : 'her';

        $weakSubjects = $analysis['weak_subjects'] ?? [];
        if (!empty($weakSubjects)) {
            usort($weakSubjects, function($a, $b) {
                $order = ['F' => 0, 'E' => 1, 'D' => 2, 'C' => 3];
                return $order[$a['grade_letter']] <=> $order[$b['grade_letter']];
            });

            $subjectList = array_map(fn($ws) => $ws['subject'] . " (" . $ws['grade'] . ")", $weakSubjects);

            $subjectsText = count($subjectList) == 1
                ? $subjectList[0]
                : (count($subjectList) == 2 ? implode(' and ', $subjectList) : implode(', ', array_slice($subjectList, 0, -1)) . " and " . end($subjectList));

            $comment .= "\n\n$pronoun should work harder in $subjectsText to improve $possessive performance.";
        }

        $intelligentComments[$studentId] = $comment;
    }

    // Student Analytics based on CUMULATIVE scores
    $studentTotals = [];
    $studentTermTotals = [];

    foreach ($students as $student) {
        $sid = $student->id;
        $totalCum = 0;
        $totalTerm = 0;
        $count = 0;

        foreach ($subjects as $subject) {
            $cumScore = $cumulativeScores->where('student_id', $sid)->where('subject_name', $subject)->first();
            $termScore = $termScores->where('student_id', $sid)->where('subject_name', $subject)->first();

            if ($cumScore) {
                $totalCum += $cumScore->total;
                $count++;
            }
            if ($termScore) {
                $totalTerm += $termScore->total;
            }
        }

        $cumAverage = $count > 0 ? round($totalCum / $count, 1) : 0;
        $termAverage = $count > 0 ? round($totalTerm / $count, 1) : 0;

        $studentTotals[$sid] = [
            'total' => $totalCum,
            'average' => $cumAverage,
            'subjects' => $count
        ];

        $studentTermTotals[$sid] = [
            'total' => $totalTerm,
            'average' => $termAverage
        ];
    }

    // Calculate positions based on CUMULATIVE averages
    $sortedStudents = $students->sortByDesc(fn($s) => $studentTotals[$s->id]['average'] ?? 0)->values();

    $positions = [];
    $rank = 1;
    $prevAvg = null;
    foreach ($sortedStudents as $index => $student) {
        $avg = $studentTotals[$student->id]['average'];
        if ($index > 0 && $avg < $prevAvg) $rank = $index + 1;
        $positions[$student->id] = $rank;
        $prevAvg = $avg;
    }

    function getPositionSuffix($num) {
        if ($num % 100 >= 11 && $num % 100 <= 13) return $num . 'th';
        return match ($num % 10) {
            1 => $num . 'st',
            2 => $num . 'nd',
            3 => $num . 'rd',
            default => $num . 'th',
        };
    }

    $classTotalScore = array_sum(array_column($studentTotals, 'total'));
    $classTotalSubjects = array_sum(array_column($studentTotals, 'subjects'));
    $classAverage = $classTotalSubjects > 0 ? round($classTotalScore / $classTotalSubjects, 1) : 0;

    $classAnalytics = [
        'average' => $classAverage,
        'total_students' => $students->count(),
    ];

    $studentAnalytics = [];
    foreach ($students as $student) {
        $sid = $student->id;
        $analysis = $studentGradeAnalysis[$sid] ?? ['counts' => []];
        $totals = $studentTotals[$sid];
        $termTotals = $studentTermTotals[$sid];
        $position = $positions[$sid] ?? null;

        $studentAnalytics[$sid] = [
            'total_score' => $totals['total'],
            'average' => $totals['average'],
            'term_total' => $termTotals['total'],
            'term_average' => $termTotals['average'],
            'subjects' => $totals['subjects'],
            'position' => $position,
            'position_text' => $position ? getPositionSuffix($position) : '-',
            'grade_counts' => $analysis['counts'],
        ];
    }

    return view('myprincipalscomment.classbroadsheet')
        ->with(compact(
            'students',
            'subjects',
            'termScores',      // Term scores for display
            'scores',          // Cumulative scores (renamed from $cumulativeScores for blade compatibility)
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

    public function updateComments(Request $request, $schoolclassid, $sessionid, $termid)
    {
        \Log::info('Update Comments Request Received', [
            'schoolclassid' => $schoolclassid,
            'sessionid' => $sessionid,
            'termid' => $termid,
            'auth_id' => Auth::id(),
            'request_method' => $request->method(),
            'ajax' => $request->ajax()
        ]);

        $isAssigned = Principalscomment::where('staffId', Auth::id())
            ->where('schoolclassid', $schoolclassid)
            ->where('sessionid', $sessionid)
            ->where('termid', $termid)
            ->exists();

        if (!$isAssigned) {
            \Log::warning('Unauthorized access attempt', ['staffId' => Auth::id()]);
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: You are not assigned to enter comments for this class.'
            ], 403);
        }

        $request->validate(['teacher_comments.*' => 'nullable|string|max:5000']);

        $comments = $request->input('teacher_comments', []);

        \Log::info('Processing comments', [
            'comments_count' => count($comments),
            'student_ids' => array_keys($comments)
        ]);

        $updatedCount = 0;
        $createdCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($comments as $studentId => $comment) {
                if (is_null($comment) || trim($comment) === '') {
                    $skippedCount++;
                    \Log::info("Skipping empty comment for student", ['student_id' => $studentId]);
                    continue;
                }

                $comment = trim(strip_tags($comment));
                $comment = html_entity_decode($comment, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                \Log::info("Processing principal comment", [
                    'student_id' => $studentId,
                    'comment_length' => strlen($comment),
                    'comment_preview' => substr($comment, 0, 100)
                ]);

                $existing = Studentpersonalityprofile::where('studentid', $studentId)
                    ->where('schoolclassid', $schoolclassid)
                    ->where('sessionid', $sessionid)
                    ->where('termid', $termid)
                    ->first();

                if ($existing) {
                    if ($existing->principalscomment !== $comment) {
                        $existing->update([
                            'staffid' => Auth::id(),
                            'principalscomment' => $comment
                        ]);
                        $updatedCount++;
                        \Log::info("Updated existing comment", ['student_id' => $studentId]);
                    } else {
                        \Log::info("No change for student", ['student_id' => $studentId]);
                    }
                } else {
                    Studentpersonalityprofile::create([
                        'studentid' => $studentId,
                        'schoolclassid' => $schoolclassid,
                        'sessionid' => $sessionid,
                        'termid' => $termid,
                        'staffid' => Auth::id(),
                        'principalscomment' => $comment,
                    ]);
                    $createdCount++;
                    \Log::info("Created new comment", ['student_id' => $studentId]);
                }
            }

            DB::commit();

            $totalProcessed = $updatedCount + $createdCount;
            $message = $totalProcessed > 0
                ? "Successfully saved: $updatedCount updated, $createdCount created. Skipped: $skippedCount empty comments."
                : "No changes detected. $skippedCount empty comments skipped.";

            \Log::info('Update completed', [
                'updated' => $updatedCount,
                'created' => $createdCount,
                'skipped' => $skippedCount
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'updated' => $updatedCount,
                'created' => $createdCount,
                'skipped' => $skippedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving principals comments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
}
