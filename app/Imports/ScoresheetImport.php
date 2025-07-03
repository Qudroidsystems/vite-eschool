<?php

namespace App\Imports;

use App\Models\Broadsheets;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithUpsertColumns;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ScoresheetImport implements ToModel, WithStartRow, WithUpsertColumns, WithUpserts, WithValidation
{
    use Importable;

    protected $data;
    protected $updatedBroadsheets = [];
    protected $failures = [];

    public function __construct($importData)
    {
        $this->data = $importData;

        // Validate term_id
        if (!in_array($this->data['term_id'], [1, 2, 3])) {
            Log::error('ScoresheetImport: Invalid term_id', ['term_id' => $this->data['term_id']]);
            throw new \Exception('Invalid term ID provided. Must be 1, 2, or 3.');
        }

        Session::put('subjectclass_id', $this->data['subjectclass_id']);
        Session::put('staff_id', $this->data['staff_id']);
        Session::put('term_id', $this->data['term_id']);
        Session::put('session_id', $this->data['session_id']);
        Session::put('schoolclass_id', $this->data['schoolclass_id']);

        Log::info('ScoresheetImport: Initialized', ['data' => $this->data]);
    }

    public function model(array $row)
    {
        try {
            $subjectclass_id = $this->data['subjectclass_id'];
            $staff_id = $this->data['staff_id'];
            $term_id = $this->data['term_id'];
            $session_id = $this->data['session_id'];
            $schoolclass_id = $this->data['schoolclass_id'];

            $rowNumber = $row[0] ?? 'Unknown';
            $admission_no = strtoupper(trim($row[1] ?? ''));

            Log::debug('ScoresheetImport: Processing row', [
                'row_number' => $rowNumber,
                'admission_no' => $admission_no,
                'raw_row' => array_slice($row, 0, 15),
            ]);

            // Manual validation
            $validationErrors = $this->validateRow($row, $rowNumber);
            if (!empty($validationErrors)) {
                $this->failures[] = [
                    'row' => $rowNumber,
                    'attribute' => 'validation',
                    'errors' => $validationErrors,
                    'values' => array_slice($row, 0, 8),
                ];
                Log::warning('ScoresheetImport: Validation failed', [
                    'row' => $rowNumber,
                    'errors' => $validationErrors,
                ]);
                return null;
            }

            if (empty($admission_no)) {
                Log::info('ScoresheetImport: Skipping row with empty admission number', ['row_number' => $rowNumber]);
                return null;
            }

            // Parse and cap scores
            $ca1 = $this->parseScore($row[3] ?? null);
            $ca2 = $this->parseScore($row[4] ?? null);
            $ca3 = $this->parseScore($row[5] ?? null);
            $exam = $this->parseScore($row[7] ?? null);

            $maxCa1 = $this->getMaxScore($subjectclass_id, 'ca1score');
            $maxCa2 = $this->getMaxScore($subjectclass_id, 'ca2score');
            $maxCa3 = $this->getMaxScore($subjectclass_id, 'ca3score');
            $maxExam = $this->getMaxScore($subjectclass_id, 'examscore');

            $ca1 = min($ca1, $maxCa1);
            $ca2 = min($ca2, $maxCa2);
            $ca3 = min($ca3, $maxCa3);
            $exam = min($exam, $maxExam);

            Log::debug('ScoresheetImport: Parsed scores', [
                'row_number' => $rowNumber,
                'admission_no' => $admission_no,
                'ca1' => $ca1,
                'ca2' => $ca2,
                'ca3' => $ca3,
                'exam' => $exam,
                'max_scores' => compact('maxCa1', 'maxCa2', 'maxCa3', 'maxExam'),
            ]);

            // Find or create broadsheet record
            $broadsheetData = DB::table('broadsheets')
                ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
                ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
                ->where('studentRegistration.admissionNO', $admission_no)
                ->where('broadsheets.subjectclass_id', $subjectclass_id)
                ->where('broadsheets.staff_id', $staff_id)
                ->where('broadsheets.term_id', $term_id)
                ->where('broadsheet_records.session_id', $session_id)
                ->select(
                    'broadsheets.id as broadsheet_id',
                    'broadsheet_records.student_id',
                    'broadsheet_records.subject_id'
                )
                ->first();

            if (!$broadsheetData) {
                $student = DB::table('studentRegistration')
                    ->where('admissionNO', $admission_no)
                    ->select('id')
                    ->first();

                if (!$student) {
                    $this->failures[] = [
                        'row' => $rowNumber,
                        'attribute' => 'admission_no',
                        'errors' => ['Student not found with admission number: ' . $admission_no],
                        'values' => ['admission_no' => $admission_no],
                    ];
                    Log::warning('ScoresheetImport: Student not found', [
                        'admission_no' => $admission_no,
                        'row_number' => $rowNumber,
                    ]);
                    return null;
                }

                $subjectclass = \App\Models\Subjectclass::find($subjectclass_id);
                if (!$subjectclass) {
                    $this->failures[] = [
                        'row' => $rowNumber,
                        'attribute' => 'subjectclass_id',
                        'errors' => ['Subjectclass not found: ' . $subjectclass_id],
                        'values' => ['subjectclass_id' => $subjectclass_id],
                    ];
                    Log::warning('ScoresheetImport: Subjectclass not found', [
                        'subjectclass_id' => $subjectclass_id,
                        'row_number' => $rowNumber,
                    ]);
                    return null;
                }

                if (!$subjectclass->schoolclassid) {
                    $this->failures[] = [
                        'row' => $rowNumber,
                        'attribute' => 'subjectclass_id',
                        'errors' => ['Subjectclass missing schoolclassid: ' . $subjectclass_id],
                        'values' => ['subjectclass_id' => $subjectclass_id],
                    ];
                    Log::warning('ScoresheetImport: Subjectclass missing schoolclassid', [
                        'subjectclass_id' => $subjectclass_id,
                        'row_number' => $rowNumber,
                    ]);
                    return null;
                }

                $broadsheetRecordId = DB::table('broadsheet_records')->insertGetId([
                    'student_id' => $student->id,
                    'subject_id' => $subjectclass->subjectid,
                    'schoolclass_id' => $subjectclass->schoolclassid,
                    'session_id' => $session_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $broadsheetId = DB::table('broadsheets')->insertGetId([
                    'broadsheet_record_id' => $broadsheetRecordId,
                    'subjectclass_id' => $subjectclass_id,
                    'staff_id' => $staff_id,
                    'term_id' => $term_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $broadsheetData = (object)[
                    'broadsheet_id' => $broadsheetId,
                    'student_id' => $student->id,
                    'subject_id' => $subjectclass->subjectid,
                ];

                Log::info('ScoresheetImport: Created new broadsheet', [
                    'broadsheet_id' => $broadsheetId,
                    'admission_no' => $admission_no,
                    'row_number' => $rowNumber,
                ]);
            }

            $ca_average = ($ca1 + $ca2 + $ca3) / 3;
            $total = round(($ca_average + $exam) / 2, 1);
            $bf = $this->getPreviousTermCum($broadsheetData->student_id, $broadsheetData->subject_id, $term_id, $session_id);
            $cum = $term_id == 1 ? $total : round(($bf + $total) / 2, 2);
            $grade = $this->calculateGrade($cum);
            $remark = $this->getRemark($grade);

            // Update broadsheet within a transaction
            $updated = DB::transaction(function () use ($broadsheetData, $ca1, $ca2, $ca3, $exam, $total, $bf, $cum, $grade, $remark) {
                return DB::table('broadsheets')
                    ->where('id', $broadsheetData->broadsheet_id)
                    ->update([
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
            });

            Log::info('ScoresheetImport: Update result', [
                'broadsheet_id' => $broadsheetData->broadsheet_id,
                'updated_rows' => $updated,
                'admission_no' => $admission_no,
                'row_number' => $rowNumber,
                'data' => compact('ca1', 'ca2', 'ca3', 'exam', 'total', 'bf', 'cum', 'grade', 'remark'),
            ]);

            if ($updated) {
                // Fetch updated broadsheet with position
                $broadsheet = Broadsheets::with([
                    'broadsheetRecord.student',
                    'broadsheetRecord.subject'
                ])->find($broadsheetData->broadsheet_id);

                if ($broadsheet) {
                    $this->updatedBroadsheets[] = [
                        'id' => $broadsheet->id,
                        'admissionno' => $admission_no,
                        'fname' => $broadsheet->broadsheetRecord->student->firstname ?? null,
                        'lname' => $broadsheet->broadsheetRecord->student->lastname ?? null,
                        'mname' => $broadsheet->broadsheetRecord->student->middlename ?? null,
                        'picture' => $broadsheet->broadsheetRecord->student->picture ?? 'none',
                        'ca1' => $broadsheet->ca1,
                        'ca2' => $broadsheet->ca2,
                        'ca3' => $broadsheet->ca3,
                        'exam' => $broadsheet->exam,
                        'total' => $broadsheet->total,
                        'bf' => $broadsheet->bf,
                        'cum' => $broadsheet->cum,
                        'grade' => $broadsheet->grade,
                        'avg' => $broadsheet->avg,
                        'position' => $broadsheet->subject_position_class ?? '-',
                        'remark' => $broadsheet->remark,
                    ];
                }
            } else {
                Log::info('ScoresheetImport: No changes needed for broadsheet', [
                    'id' => $broadsheetData->broadsheet_id,
                    'admission_no' => $admission_no,
                    'row_number' => $rowNumber,
                ]);
            }

            return null;

        } catch (\Exception $e) {
            $this->failures[] = [
                'row' => $rowNumber ?? 'Unknown',
                'attribute' => 'general',
                'errors' => ['Error processing row: ' . $e->getMessage()],
                'values' => array_slice($row, 0, 8),
            ];
            Log::error('ScoresheetImport: Error processing row', [
                'admission_no' => $admission_no ?? 'Unknown',
                'row_number' => $rowNumber ?? 'Unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    protected function validateRow(array $row, $rowNumber)
    {
        $errors = [];
        $subjectclass_id = $this->data['subjectclass_id'];

        $admission_no = strtoupper(trim($row[1] ?? ''));
        if (empty($admission_no)) {
            $errors[] = 'The admission number field is required.';
        }

        $ca1 = $this->parseScore($row[3] ?? null);
        $ca2 = $this->parseScore($row[4] ?? null);
        $ca3 = $this->parseScore($row[5] ?? null);
        $exam = $this->parseScore($row[7] ?? null);

        $maxCa1 = $this->getMaxScore($subjectclass_id, 'ca1score');
        $maxCa2 = $this->getMaxScore($subjectclass_id, 'ca2score');
        $maxCa3 = $this->getMaxScore($subjectclass_id, 'ca3score');
        $maxExam = $this->getMaxScore($subjectclass_id, 'examscore');

        if ($ca1 > $maxCa1) {
            Log::warning('ScoresheetImport: CA1 score exceeds max, capping', [
                'row' => $rowNumber,
                'ca1' => $ca1,
                'max_ca1' => $maxCa1,
            ]);
            $ca1 = $maxCa1;
        }

        if ($ca2 > $maxCa2) {
            Log::warning('ScoresheetImport: CA2 score exceeds max, capping', [
                'row' => $rowNumber,
                'ca2' => $ca2,
                'max_ca2' => $maxCa2,
            ]);
            $ca2 = $maxCa2;
        }

        if ($ca3 > $maxCa3) {
            Log::warning('ScoresheetImport: CA3 score exceeds max, capping', [
                'row' => $rowNumber,
                'ca3' => $ca3,
                'max_ca3' => $maxCa3,
            ]);
            $ca3 = $maxCa3;
        }

        if ($exam > $maxExam) {
            Log::warning('ScoresheetImport: Exam score exceeds max, capping', [
                'row' => $rowNumber,
                'exam' => $exam,
                'max_exam' => $maxExam,
            ]);
            $exam = $maxExam;
        }

        return $errors;
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->failures[] = [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values(),
            ];
            Log::warning('ScoresheetImport: Validation failure', [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values(),
            ]);
        }
    }

    protected function parseScore($value)
    {
        if (is_null($value) || $value === '' || !is_numeric($value)) {
            return 0;
        }
        $numericValue = floatval($value);
        return ($numericValue >= 0) ? $numericValue : 0;
    }

    protected function getPreviousTermCum($studentId, $subjectId, $termId, $sessionId)
    {
        if ($termId == 1) {
            Log::debug('ScoresheetImport: Term 1, bf set to 0', [
                'student_id' => $studentId,
                'subject_id' => $subjectId,
            ]);
            return 0;
        }

        $previousTerm = Broadsheets::where('broadsheet_records.student_id', $studentId)
            ->where('broadsheet_records.subject_id', $subjectId)
            ->where('broadsheets.term_id', $termId - 1)
            ->where('broadsheet_records.session_id', $sessionId)
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->value('broadsheets.cum');

        if (is_null($previousTerm)) {
            Log::warning('ScoresheetImport: No previous term cum found', [
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'term_id' => $termId - 1,
                'session_id' => $sessionId,
            ]);
            return 0;
        }

        $cum = round($previousTerm, 2);
        Log::debug('ScoresheetImport: Fetched previous cum', [
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'term_id' => $termId - 1,
            'cum' => $cum,
        ]);

        return $cum;
    }

    protected function calculateGrade($score)
    {
        if ($score >= 70) return 'A';
        elseif ($score >= 60) return 'B';
        elseif ($score >= 50) return 'C';
        elseif ($score >= 40) return 'D';
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

    public function rules(): array
    {
        return [
            '1' => ['required', function ($attribute, $value, $fail) {
                $value = strtoupper(trim((string) $value));
                if (empty($value)) {
                    $fail('The admission number field is required.');
                }
            }],
            '3' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== '' && (!is_numeric($value) || $value < 0)) {
                    $fail('CA1 score must be a non-negative number.');
                }
            }],
            '4' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== '' && (!is_numeric($value) || $value < 0)) {
                    $fail('CA2 score must be a non-negative number.');
                }
            }],
            '5' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== '' && (!is_numeric($value) || $value < 0)) {
                    $fail('CA3 score must be a non-negative number.');
                }
            }],
            '7' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== '' && (!is_numeric($value) || $value < 0)) {
                    $fail('Exam score must be a non-negative number.');
                }
            }],
        ];
    }

    protected function getMaxScore($subjectclass_id, $scoreType)
    {
        $subjectclass = \App\Models\Subjectclass::find($subjectclass_id);
        if (!$subjectclass) {
            Log::error('ScoresheetImport: Subjectclass not found', ['subjectclass_id' => $subjectclass_id]);
            return 100;
        }

        if (!$subjectclass->schoolclassid) {
            Log::error('ScoresheetImport: Subjectclass missing schoolclassid', [
                'subjectclass_id' => $subjectclass_id,
            ]);
            return 100;
        }

        $schoolclass = \App\Models\Schoolclass::find($subjectclass->schoolclassid);
        if (!$schoolclass) {
            Log::error('ScoresheetImport: Schoolclass not found', [
                'schoolclass_id' => $subjectclass->schoolclassid,
                'subjectclass_id' => $subjectclass_id,
            ]);
            return 100;
        }

        $classcategory = \App\Models\Classcategory::find($schoolclass->classcategoryid);
        if (!$classcategory) {
            Log::error('ScoresheetImport: Classcategory not found', [
                'classcategoryid' => $schoolclass->classcategoryid,
                'subjectclass_id' => $subjectclass_id,
            ]);
            return 100;
        }

        $score = $classcategory->$scoreType ?? 100;
        Log::debug('ScoresheetImport: Retrieved max score', [
            'score_type' => $scoreType,
            'max_score' => $score,
            'subjectclass_id' => $subjectclass_id,
            'schoolclass_id' => $subjectclass->schoolclassid,
        ]);
        return $score;
    }

    public function startRow(): int
    {
        return 7; // Data starts on row 7
    }

    public function upsertColumns()
    {
        return ['ca1', 'ca2', 'ca3', 'exam', 'total', 'bf', 'cum', 'grade', 'remark', 'subject_position_class'];
    }

    public function uniqueBy()
    {
        return ['id']; // Upsert based on broadsheet ID
    }

    public function afterImport()
    {
        if (empty($this->updatedBroadsheets)) {
            Log::warning('ScoresheetImport: No records updated, skipping afterImport');
            return;
        }

        try {
            $subjectclass_id = $this->data['subjectclass_id'];
            $staff_id = $this->data['staff_id'];
            $term_id = $this->data['term_id'];
            $session_id = $this->data['session_id'];
            $schoolclass_id = $this->data['schoolclass_id'];

            Log::info('ScoresheetImport: Starting afterImport', [
                'subjectclass_id' => $subjectclass_id,
                'staff_id' => $staff_id,
                'term_id' => $term_id,
                'session_id' => $session_id,
                'schoolclass_id' => $schoolclass_id,
                'updated_broadsheets' => count($this->updatedBroadsheets),
                'failures' => count($this->failures),
            ]);

            DB::transaction(function () use ($subjectclass_id, $staff_id, $term_id, $session_id, $schoolclass_id) {
                // Update class metrics
                $metrics = Broadsheets::where('subjectclass_id', $subjectclass_id)
                    ->where('staff_id', $staff_id)
                    ->where('term_id', $term_id)
                    ->selectRaw('MIN(cum) as min_cum, MAX(cum) as max_cum, AVG(cum) as avg_cum')
                    ->first();

                Log::info('ScoresheetImport: Calculated class metrics', [
                    'min_cum' => $metrics->min_cum,
                    'max_cum' => $metrics->max_cum,
                    'avg_cum' => $metrics->avg_cum,
                ]);

                $classMin = $metrics->min_cum ?? 0;
                $classMax = $metrics->max_cum ?? 0;
                $classAvg = $metrics->avg_cum ? round($metrics->avg_cum, 1) : 0;

                Broadsheets::where('subjectclass_id', $subjectclass_id)
                    ->where('staff_id', $staff_id)
                    ->where('term_id', $term_id)
                    ->update([
                        'cmin' => $classMin,
                        'cmax' => $classMax,
                        'avg' => $classAvg,
                    ]);

                // Update subject positions (class-wide)
                $classPos = Broadsheets::where('broadsheets.subjectclass_id', $subjectclass_id)
                    ->where('broadsheets.staff_id', $staff_id)
                    ->where('broadsheets.term_id', $term_id)
                    ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
                    ->where('broadsheet_records.schoolclass_id', $schoolclass_id)
                    ->where('broadsheet_records.session_id', $session_id)
                    ->orderBy('broadsheets.cum', 'DESC')
                    ->orderBy('broadsheets.id', 'ASC') // Stable sort for ties
                    ->select('broadsheets.id', 'broadsheets.cum')
                    ->get();

                $rank = 0;
                $lastScore = null;
                $rows = 0;

                foreach ($classPos as $row) {
                    $rows++;
                    if ($lastScore !== $row->cum) {
                        $lastScore = $row->cum;
                        $rank = $rows;
                    }
                    $position = $row->cum > 0 ? ($rank . match ($rank) {
                        1 => 'st',
                        2 => 'nd',
                        3 => 'rd',
                        default => 'th',
                    }) : '0th';

                    Broadsheets::where('id', $row->id)->update(['subject_position_class' => $position]);
                }

                Log::info('ScoresheetImport: Updated subject positions', [
                    'subjectclass_id' => $subjectclass_id,
                    'term_id' => $term_id,
                    'total_records' => $rows,
                ]);

                // Refresh updatedBroadsheets with new positions
                $this->updatedBroadsheets = array_map(function ($broadsheet) use ($subjectclass_id, $term_id, $session_id, $schoolclass_id) {
                    $updatedRecord = Broadsheets::where('broadsheets.id', $broadsheet['id'])
                        ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
                        ->leftJoin('studentRegistration', 'broadsheet_records.student_id', '=', 'studentRegistration.id')
                        ->where('broadsheets.subjectclass_id', $subjectclass_id)
                        ->where('broadsheets.term_id', $term_id)
                        ->where('broadsheet_records.session_id', $session_id)
                        ->where('broadsheet_records.schoolclass_id', $schoolclass_id)
                        ->select(
                            'broadsheets.*',
                            'studentRegistration.firstname as fname',
                            'studentRegistration.lastname as lname',
                            'studentRegistration.middlename as mname',
                            'studentRegistration.picture'
                        )
                        ->first();

                    if ($updatedRecord) {
                        return [
                            'id' => $updatedRecord->id,
                            'admissionno' => $broadsheet['admissionno'],
                            'fname' => $updatedRecord->fname,
                            'lname' => $updatedRecord->lname,
                            'mname' => $updatedRecord->mname,
                            'picture' => $updatedRecord->picture ?? 'none',
                            'ca1' => $updatedRecord->ca1,
                            'ca2' => $updatedRecord->ca2,
                            'ca3' => $updatedRecord->ca3,
                            'exam' => $updatedRecord->exam,
                            'total' => $updatedRecord->total,
                            'bf' => $updatedRecord->bf,
                            'cum' => $updatedRecord->cum,
                            'grade' => $updatedRecord->grade,
                            'avg' => $updatedRecord->avg,
                            'position' => $updatedRecord->subject_position_class ?? '-',
                            'remark' => $updatedRecord->remark,
                        ];
                    }
                    return $broadsheet;
                }, $this->updatedBroadsheets);

                // Update subjectstotalscores in promotion_status
                $students = \App\Models\PromotionStatus::where('schoolclassid', $schoolclass_id)
                    ->where('termid', $term_id)
                    ->where('sessionid', $session_id)
                    ->pluck('studentid');

                foreach ($students as $studentId) {
                    $totalCum = Broadsheets::where('broadsheet_records.student_id', $studentId)
                        ->where('broadsheets.term_id', $term_id)
                        ->where('broadsheet_records.session_id', $session_id)
                        ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
                        ->sum('broadsheets.cum');

                    \App\Models\PromotionStatus::where('studentid', $studentId)
                        ->where('schoolclassid', $schoolclass_id)
                        ->where('termid', $term_id)
                        ->where('sessionid', $session_id)
                        ->update(['subjectstotalscores' => round($totalCum, 2)]);
                }

                // Update class positions
                $pos = \App\Models\PromotionStatus::where('schoolclassid', $schoolclass_id)
                    ->where('termid', $term_id)
                    ->where('sessionid', $session_id)
                    ->orderBy('subjectstotalscores', 'DESC')
                    ->orderBy('id', 'ASC') // Stable sort for ties
                    ->select('id', 'subjectstotalscores')
                    ->get();

                $rank = 0;
                $lastScore = null;
                $rows = 0;

                foreach ($pos as $row) {
                    $rows++;
                    if ($lastScore !== $row->subjectstotalscores) {
                        $lastScore = $row->subjectstotalscores;
                        $rank = $rows;
                    }
                    $position = $row->subjectstotalscores > 0 ? ($rank . match ($rank) {
                        1 => 'st',
                        2 => 'nd',
                        3 => 'rd',
                        default => 'th',
                    }) : '0th';

                    \App\Models\PromotionStatus::where('id', $row->id)->update(['position' => $position]);
                }

                Log::info('ScoresheetImport: Updated class positions', [
                    'schoolclass_id' => $schoolclass_id,
                    'term_id' => $term_id,
                    'total_records' => $rows,
                ]);
            });

            Log::info('ScoresheetImport: afterImport completed', [
                'updated_broadsheets' => count($this->updatedBroadsheets),
                'failures' => count($this->failures),
            ]);

        } catch (\Exception $e) {
            Log::error('ScoresheetImport: Error in afterImport', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function getUpdatedBroadsheets()
    {
        return $this->updatedBroadsheets;
    }

    public function getFailures()
    {
        return $this->failures;
    }
}