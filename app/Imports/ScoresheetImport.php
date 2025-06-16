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

            $rowNumber = $row[0] ?? 'Unknown';
            $admission_no = trim($row[1] ?? '');

            Log::debug('ScoresheetImport: Processing row', [
                'row_number' => $rowNumber,
                'admission_no' => $admission_no,
                'raw_admission_no' => $row[1],
                'raw_row' => array_slice($row, 0, 15)
            ]);

            // Manual validation before processing
            $validationErrors = $this->validateRow($row, $rowNumber);
            if (!empty($validationErrors)) {
                $this->failures[] = [
                    'row' => $rowNumber,
                    'attribute' => 'validation',
                    'errors' => $validationErrors,
                    'values' => array_slice($row, 0, 8)
                ];
                Log::warning('ScoresheetImport: Validation failed', [
                    'row' => $rowNumber,
                    'errors' => $validationErrors
                ]);
                return null;
            }

            if (empty($admission_no)) {
                Log::info('ScoresheetImport: Skipping row with empty admission number', ['row_number' => $rowNumber]);
                return null;
            }

            $ca1 = $this->parseScore($row[3] ?? null);
            $ca2 = $this->parseScore($row[4] ?? null);
            $ca3 = $this->parseScore($row[5] ?? null);
            $exam = $this->parseScore($row[7] ?? null);

            // Find the broadsheet record
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
                $this->failures[] = [
                    'row' => $rowNumber,
                    'attribute' => 'admission_no',
                    'errors' => ['Student not found with admission number: ' . $admission_no],
                    'values' => ['admission_no' => $admission_no]
                ];
                Log::warning('ScoresheetImport: No broadsheet found', [
                    'admission_no' => $admission_no,
                    'subjectclass_id' => $subjectclass_id,
                    'staff_id' => $staff_id,
                    'term_id' => $term_id,
                    'session_id' => $session_id,
                    'row_number' => $rowNumber
                ]);
                return null;
            }

            $ca_average = ($ca1 + $ca2 + $ca3) / 3;
            $total = round(($ca_average + $exam) / 2, 1);
            $bf = $this->getPreviousTermCum($broadsheetData->student_id, $broadsheetData->subject_id, $term_id, $session_id);
            $cum = $term_id == 1 ? $total : round(($bf + $total) / 2, 2);
            $grade = $this->calculateGrade($cum); // Grade based on cum
            $remark = $this->getRemark($grade);

            // Update the broadsheet directly
            $updated = DB::table('broadsheets')
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

            if ($updated) {
                // Get the updated broadsheet for response
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
                        'ca1' => $broadsheet->ca1,
                        'ca2' => $broadsheet->ca2,
                        'ca3' => $broadsheet->ca3,
                        'exam' => $broadsheet->exam,
                        'total' => $broadsheet->total,
                        'bf' => $broadsheet->bf,
                        'cum' => $broadsheet->cum,
                        'grade' => $broadsheet->grade,
                        'avg' => $broadsheet->avg,
                        'position' => $broadsheet->subject_position_class,
                        'remark' => $broadsheet->remark,
                    ];
                }

                Log::info('ScoresheetImport: Updated broadsheet', [
                    'id' => $broadsheetData->broadsheet_id,
                    'admission_no' => $admission_no,
                    'scores' => compact('ca1', 'ca2', 'ca3', 'exam', 'total', 'bf', 'cum', 'grade', 'remark'),
                    'row_number' => $rowNumber
                ]);
            } else {
                Log::warning('ScoresheetImport: No changes applied to broadsheet', [
                    'id' => $broadsheetData->broadsheet_id,
                    'admission_no' => $admission_no,
                    'row_number' => $rowNumber
                ]);
            }

            return null;

        } catch (\Exception $e) {
            $this->failures[] = [
                'row' => $rowNumber ?? 'Unknown',
                'attribute' => 'general',
                'errors' => ['Error processing row: ' . $e->getMessage()],
                'values' => array_slice($row, 0, 8)
            ];
            Log::error('ScoresheetImport: Error processing row', [
                'admission_no' => $admission_no ?? 'Unknown',
                'row_number' => $rowNumber ?? 'Unknown',
                'error' => $e->getMessage(),
                'row' => array_slice($row, 0, 15)
            ]);
            return null;
        }
    }

    protected function validateRow(array $row, $rowNumber)
    {
        $errors = [];
        $subjectclass_id = $this->data['subjectclass_id'];

        // Validate admission number
        $admission_no = trim($row[1] ?? '');
        if (empty($admission_no)) {
            $errors[] = 'The admission number field is required.';
        }

        // Validate CA1 score
        $ca1 = $row[3] ?? null;
        if ($ca1 !== '' && $ca1 !== null) {
            if (!is_numeric($ca1) || $ca1 < 0 || $ca1 > $this->getMaxScore($subjectclass_id, 'ca1score')) {
                $errors[] = "CA1 score must be between 0 and {$this->getMaxScore($subjectclass_id, 'ca1score')}.";
            }
        }

        // Validate CA2 score
        $ca2 = $row[4] ?? null;
        if ($ca2 !== '' && $ca2 !== null) {
            if (!is_numeric($ca2) || $ca2 < 0 || $ca2 > $this->getMaxScore($subjectclass_id, 'ca2score')) {
                $errors[] = "CA2 score must be between 0 and {$this->getMaxScore($subjectclass_id, 'ca2score')}.";
            }
        }

        // Validate CA3 score
        $ca3 = $row[5] ?? null;
        if ($ca3 !== '' && $ca3 !== null) {
            if (!is_numeric($ca3) || $ca3 < 0 || $ca3 > $this->getMaxScore($subjectclass_id, 'ca3score')) {
                $errors[] = "CA3 score must be between 0 and {$this->getMaxScore($subjectclass_id, 'ca3score')}.";
            }
        }

        // Validate Exam score
        $exam = $row[7] ?? null;
        if ($exam !== '' && $exam !== null) {
            if (!is_numeric($exam) || $exam < 0 || $exam > $this->getMaxScore($subjectclass_id, 'examscore')) {
                $errors[] = "Exam score must be between 0 and {$this->getMaxScore($subjectclass_id, 'examscore')}.";
            }
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
                'values' => $failure->values()
            ];
            Log::warning('ScoresheetImport: Validation failure', [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values()
            ]);
        }
    }

    protected function parseScore($value)
    {
        if (is_null($value) || $value === '' || !is_numeric($value)) {
            return 0;
        }
        $numericValue = floatval($value);
        return ($numericValue >= 0 && $numericValue <= 100) ? $numericValue : 0;
    }

    protected function getPreviousTermCum($studentId, $subjectId, $termId, $sessionId)
    {
        if ($termId == 1) {
            Log::debug('ScoresheetImport: Term 1, bf set to 0', [
                'student_id' => $studentId,
                'subject_id' => $subjectId
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
                'session_id' => $sessionId
            ]);
            return 0;
        }

        $cum = round($previousTerm, 2);
        Log::debug('ScoresheetImport: Fetched previous cum', [
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'term_id' => $termId - 1,
            'cum' => $cum
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
        $subjectclass_id = $this->data['subjectclass_id'];

        return [
            '1' => ['required', function ($attribute, $value, $fail) {
                $value = trim((string) $value);
                if (empty($value)) {
                    $fail('The admission number field is required.');
                }
            }],
            '3' => ['nullable', function ($attribute, $value, $fail) use ($subjectclass_id) {
                if ($value !== '' && (!is_numeric($value) || $value < 0 || $value > $this->getMaxScore($subjectclass_id, 'ca1score'))) {
                    $fail("CA1 score must be between 0 and {$this->getMaxScore($subjectclass_id, 'ca1score')}.");
                }
            }],
            '4' => ['nullable', function ($attribute, $value, $fail) use ($subjectclass_id) {
                if ($value !== '' && (!is_numeric($value) || $value < 0 || $value > $this->getMaxScore($subjectclass_id, 'ca2score'))) {
                    $fail("CA2 score must be between 0 and {$this->getMaxScore($subjectclass_id, 'ca2score')}.");
                }
            }],
            '5' => ['nullable', function ($attribute, $value, $fail) use ($subjectclass_id) {
                if ($value !== '' && (!is_numeric($value) || $value < 0 || $value > $this->getMaxScore($subjectclass_id, 'ca3score'))) {
                    $fail("CA3 score must be between 0 and {$this->getMaxScore($subjectclass_id, 'ca3score')}.");
                }
            }],
            '7' => ['nullable', function ($attribute, $value, $fail) use ($subjectclass_id) {
                if ($value !== '' && (!is_numeric($value) || $value < 0 || $value > $this->getMaxScore($subjectclass_id, 'examscore'))) {
                    $fail("Exam score must be between 0 and {$this->getMaxScore($subjectclass_id, 'examscore')}.");
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

        $schoolclass = \App\Models\Schoolclass::find($subjectclass->schoolclass_id);
        if (!$schoolclass) {
            Log::error('ScoresheetImport: Schoolclass not found', ['schoolclass_id' => $subjectclass->schoolclass_id]);
            return 100;
        }

        $classcategory = \App\Models\Classcategories::find($schoolclass->classcategoryid);
        if (!$classcategory) {
            Log::error('ScoresheetImport: Classcategory not found', ['classcategoryid' => $schoolclass->classcategoryid]);
            return 100;
        }

        $score = $classcategory->$scoreType ?? 100;
        Log::debug('ScoresheetImport: Retrieved max score', [
            'score_type' => $scoreType,
            'max_score' => $score
        ]);
        return $score;
    }

    public function startRow(): int
    {
        return 7; // Data starts on row 7
    }

    public function upsertColumns()
    {
        return ['ca1', 'ca2', 'ca3', 'exam', 'total', 'bf', 'cum', 'grade', 'remark'];
    }

    public function uniqueBy()
    {
        return ['id']; // Upsert based on broadsheet ID
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

            DB::transaction(function () use ($subjectclass_id, $staff_id, $term_id, $session_id, $schoolclass_id) {
                // Update class metrics based on cum
                $metrics = Broadsheets::where('subjectclass_id', $subjectclass_id)
                    ->where('staff_id', $staff_id)
                    ->where('term_id', $term_id)
                    ->selectRaw('MIN(cum) as min_cum, MAX(cum) as max_cum, AVG(cum) as avg_cum')
                    ->first();

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

                // Update subject positions based on cum
                $classPos = Broadsheets::where('subjectclass_id', $subjectclass_id)
                    ->where('staff_id', $staff_id)
                    ->where('term_id', $term_id)
                    ->orderBy('cum', 'DESC')
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
                    $position = match ($rank) {
                        1 => 'st',
                        2 => 'nd',
                        3 => 'rd',
                        default => 'th',
                    };
                    $rankPos = $rank . $position;

                    Broadsheets::where('id', $row->id)->update(['subject_position_class' => $rankPos]);
                }

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

                // Update class positions based on subjectstotalscores
                $pos = \App\Models\PromotionStatus::where('schoolclassid', $schoolclass_id)
                    ->where('termid', $term_id)
                    ->where('sessionid', $session_id)
                    ->orderBy('subjectstotalscores', 'DESC')
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
                    $position = match ($rank) {
                        1 => 'st',
                        2 => 'nd',
                        3 => 'rd',
                        default => 'th',
                    };
                    $rankPos = $rank . $position;

                    \App\Models\PromotionStatus::where('id', $row->id)->update(['position' => $rankPos]);
                }
            });

            Log::info('ScoresheetImport: afterImport completed', [
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

    public function getUpdatedBroadsheets()
    {
        return $this->updatedBroadsheets;
    }

    public function getFailures()
    {
        return $this->failures;
    }
}