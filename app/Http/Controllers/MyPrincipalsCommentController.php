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

        if (!$isAssigned) {
            abort(403, 'You are not authorized to enter Principal comments for this class in this session and term.');
        }

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

        $scores = Broadsheets::where('broadsheet_records.schoolclass_id', $schoolclassid)
            ->where('broadsheets.term_id', $termid)
            ->where('broadsheet_records.session_id', $sessionid)
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->get([
                'broadsheet_records.student_id',
                'subject.subject as subject_name',
                'broadsheets.total',
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

        $rawGrades = Broadsheets::where('broadsheet_records.schoolclass_id', $schoolclassid)
            ->where('broadsheets.term_id', $termid)
            ->where('broadsheet_records.session_id', $sessionid)
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->select([
                'broadsheet_records.student_id',
                'subject.subject as subject_name',
                'broadsheets.total',
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

            $gradeLetter = 'F';
            if ($isSenior) {
                if ($total >= 75) $gradeLetter = 'A';
                elseif ($total >= 70) $gradeLetter = 'B';
                elseif ($total >= 60) $gradeLetter = 'C';
                elseif ($total >= 50) $gradeLetter = 'D';
                elseif ($total >= 40) $gradeLetter = 'E';
            } else {
                if ($total >= 70) $gradeLetter = 'A';
                elseif ($total >= 60) $gradeLetter = 'B';
                elseif ($total >= 50) $gradeLetter = 'C';
                elseif ($total >= 40) $gradeLetter = 'D';
            }

            $studentGrades[$studentId][] = [
                'subject' => $subjectName,
                'score'   => $total,
                'grade'   => $gradeLetter,
                'grade_letter' => $gradeLetter
            ];

            $studentGradeAnalysis[$studentId]['grades'][] = [
                'subject' => $subjectName,
                'score' => $total,
                'grade' => $gradeLetter,
                'grade_letter' => $gradeLetter
            ];

            $studentGradeAnalysis[$studentId]['counts'][$gradeLetter]++;

            if (in_array($gradeLetter, ['C', 'D', 'E', 'F'])) {
                $studentGradeAnalysis[$studentId]['weak_subjects'][] = [
                    'subject' => $subjectName,
                    'grade' => $gradeLetter
                ];
            }
        }

        // Generate original intelligent comments (kept for optional use)
        $intelligentComments = [];
        foreach ($students as $student) {
            $studentId = $student->id;
            $studentFirstName = $student->fname;
            $analysis = $studentGradeAnalysis[$studentId] ?? ['counts' => [], 'weak_subjects' => []];

            $gradeParts = [];
            foreach (['A', 'B', 'C', 'D', 'F'] as $g) {
                $count = $analysis['counts'][$g] ?? 0;
                if ($count > 0) $gradeParts[] = "$count " . $g . ($count > 1 ? "'s" : '');
            }

            $gradeSummary = !empty($gradeParts)
                ? (count($gradeParts) == 1 ? $gradeParts[0] : implode(', ', array_slice($gradeParts, 0, -1)) . ' and ' . end($gradeParts))
                : 'no grades recorded';

            $totalGrades = array_sum($analysis['counts']);
            $goodGrades = ($analysis['counts']['A'] ?? 0) + ($analysis['counts']['B'] ?? 0);
            $percentageGood = $totalGrades > 0 ? ($goodGrades / $totalGrades) * 100 : 0;

            $baseComment = "Wake up and be serious.";
            if ($percentageGood >= 80) $baseComment = "Excellent result, keep it up!";
            elseif ($percentageGood >= 70) $baseComment = "A very good result, keep it up!";
            elseif ($percentageGood >= 60) $baseComment = "Good result, keep it up!";
            elseif ($percentageGood >= 50) $baseComment = "Average result, there's still room for improvement next term.";
            elseif ($percentageGood >= 40) $baseComment = "You can do better next term.";
            elseif ($percentageGood >= 30) $baseComment = "You need to sit up and be serious.";

            $intelligentComment = "$studentFirstName has $gradeSummary. $baseComment";

            $weakSubjects = $analysis['weak_subjects'] ?? [];
            if (!empty($weakSubjects)) {
                $subjectList = array_map(fn($ws) => $ws['subject'] . " (" . $ws['grade'] . ")", $weakSubjects);
                $advice = count($subjectList) == 1
                    ? "$studentFirstName should work harder in " . $subjectList[0] . " to improve."
                    : "$studentFirstName should work harder in " . (count($subjectList) == 2 ? implode(' and ', $subjectList) : implode(', ', array_slice($subjectList, 0, -1)) . " and " . end($subjectList)) . " to improve.";
                $intelligentComment .= "\n\n" . $advice;
            }

            $intelligentComments[$studentId] = $intelligentComment;
        }

        // Generate personalized standard comments (with name + multiple weak subjects advice)
        $personalizedStandardComments = [];

        $standardTemplates = [
            80 => "Excellent result {NAME}, keep it up!",
            70 => "A very good result {NAME}, keep it up!",
            60 => "Good result {NAME}, keep it up!",
            50 => "Average result {NAME}, there's still room for improvement next term.",
            40 => "You can do better next term, {NAME}.",
            30 => "You need to sit up and be serious, {NAME}.",
            0  => "Wake up and be serious, {NAME}.",
        ];

        foreach ($students as $student) {
            $studentId = $student->id;
            $firstName = strtoupper($student->fname);
            $analysis = $studentGradeAnalysis[$studentId] ?? ['counts' => [], 'weak_subjects' => []];

            $totalGrades = array_sum($analysis['counts']);
            $goodGrades = ($analysis['counts']['A'] ?? 0) + ($analysis['counts']['B'] ?? 0);
            $percentageGood = $totalGrades > 0 ? ($goodGrades / $totalGrades) * 100 : 0;

            // Select base template
            $key = 0;
            if ($percentageGood >= 80) $key = 80;
            elseif ($percentageGood >= 70) $key = 70;
            elseif ($percentageGood >= 60) $key = 60;
            elseif ($percentageGood >= 50) $key = 50;
            elseif ($percentageGood >= 40) $key = 40;
            elseif ($percentageGood >= 30) $key = 30;

            $baseComment = $standardTemplates[$key];
            $baseComment = str_replace('{NAME}', $firstName, $baseComment);

            // Add advice for multiple weak subjects
            $weakSubjects = $analysis['weak_subjects'] ?? [];

            if (!empty($weakSubjects)) {
                // Sort by severity: F → E → D → C
                usort($weakSubjects, function($a, $b) {
                    $order = ['F' => 0, 'E' => 1, 'D' => 2, 'C' => 3];
                    return $order[$a['grade']] <=> $order[$b['grade']];
                });

                $subjectList = array_map(function($ws) {
                    return strtoupper($ws['subject']) . " (" . $ws['grade'] . ")";
                }, $weakSubjects);

                if (count($subjectList) == 1) {
                    $list = $subjectList[0];
                    $advice = "$firstName should work harder in $list to improve.";
                } elseif (count($subjectList) == 2) {
                    $list = implode(' and ', $subjectList);
                    $advice = "$firstName should work harder in $list to improve.";
                } else {
                    $last = array_pop($subjectList);
                    $list = implode(', ', $subjectList) . " and $last";
                    $advice = "$firstName should work harder in $list to improve.";
                }

                $baseComment .= "\n\n" . $advice;
            }

            $personalizedStandardComments[$studentId] = $baseComment;
        }

        // Student Analytics: Total, Average, Position
        $studentTotals = [];
        foreach ($students as $student) {
            $sid = $student->id;
            $total = 0;
            $count = 0;
            foreach ($subjects as $subject) {
                $score = $scores->where('student_id', $sid)->where('subject_name', $subject)->first();
                if ($score) {
                    $total += $score->total;
                    $count++;
                }
            }
            $average = $count > 0 ? round($total / $count, 1) : 0;
            $studentTotals[$sid] = ['total' => $total, 'average' => $average, 'subjects' => $count];
        }

        // Sort students by average for position
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

        // Class Average
        $classTotalScore = array_sum(array_column($studentTotals, 'total'));
        $classTotalSubjects = array_sum(array_column($studentTotals, 'subjects'));
        $classAverage = $classTotalSubjects > 0 ? round($classTotalScore / $classTotalSubjects, 1) : 0;

        $classAnalytics = [
            'average' => $classAverage,
            'total_students' => $students->count(),
        ];

        // Final Student Analytics
        $studentAnalytics = [];
        foreach ($students as $student) {
            $sid = $student->id;
            $analysis = $studentGradeAnalysis[$sid] ?? ['counts' => []];
            $totals = $studentTotals[$sid];
            $position = $positions[$sid] ?? null;
            $studentAnalytics[$sid] = [
                'total_score' => $totals['total'],
                'average' => $totals['average'],
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
                'personalizedStandardComments',  // New variable
                'studentAnalytics',
                'classAnalytics'
            ));
    }

    public function updateComments(Request $request, $schoolclassid, $sessionid, $termid)
    {
        $isAssigned = Principalscomment::where('staffId', Auth::id())
            ->where('schoolclassid', $schoolclassid)
            ->where('sessionid', $sessionid)
            ->where('termid', $termid)
            ->exists();

        if (!$isAssigned) {
            return $request->ajax() || $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Unauthorized'], 403)
                : redirect()->back()->with('error', 'Unauthorized');
        }

        $request->validate(['teacher_comments.*' => 'nullable|string|max:2000']);

        $comments = $request->input('teacher_comments', []);
        $updatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($comments as $studentId => $comment) {
                $comment = $comment ? trim($comment) : null;

                $existing = Studentpersonalityprofile::where('studentid', $studentId)
                    ->where('schoolclassid', $schoolclassid)
                    ->where('sessionid', $sessionid)
                    ->where('termid', $termid)
                    ->first();

                if ($existing) {
                    if ($existing->principalscomment !== $comment) {
                        $existing->update(['staffid' => Auth::id(), 'principalscomment' => $comment]);
                        $updatedCount++;
                    }
                } elseif ($comment) {
                    Studentpersonalityprofile::create([
                        'studentid' => $studentId,
                        'schoolclassid' => $schoolclassid,
                        'sessionid' => $sessionid,
                        'termid' => $termid,
                        'staffid' => Auth::id(),
                        'principalscomment' => $comment,
                    ]);
                    $updatedCount++;
                }
            }

            DB::commit();

            $message = $updatedCount > 0 ? "$updatedCount comment(s) saved" : "No changes";

            return $request->ajax() || $request->wantsJson()
                ? response()->json(['success' => true, 'message' => $message])
                : redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return $request->ajax() || $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Error saving'], 500)
                : redirect()->back()->with('error', 'Error saving comments');
        }
    }
}