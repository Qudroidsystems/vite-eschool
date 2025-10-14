<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Student;
use Illuminate\Support\Str;
use App\Models\Studentclass;
use App\Models\Studenthouse;
use App\Models\StudentStatus;
use App\Models\Studentpicture;
use App\Models\PromotionStatus;
use App\Models\StudentBatchModel;
use App\Models\ParentRegistration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\ToModel;
use App\Models\Studentpersonalityprofile;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithUpsertColumns; // Added for better performance on large files

class StudentsImport implements ToModel, WithProgressBar, WithStartRow, WithUpsertColumns, WithUpserts, WithValidation, WithChunkReading
{
    use Importable;

    public $id = 0;

    public $_sclassid = 0;

    public $_teremid = 0;

    public $_sessionid = 0;

    public $_batchid = 0;

    /**
     * Chunk size for reading large files.
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * Handle a single row of the Excel file and map it to models.
     */
    public function model(array $row)
    {
        // Helper function to return "N/A" for null, empty, or whitespace-only values
        $naIfEmpty = function ($value) {
            return (is_null($value) || trim($value) === '') ? 'N/A' : trim($value);
        };

        // Retrieve session data with "N/A" fallback
        $schoolclassid = $naIfEmpty(Session::get('sclassid'));
        $termid = $naIfEmpty(Session::get('tid'));
        $sessionid = $naIfEmpty(Session::get('sid'));
        $batchid = $naIfEmpty(Session::get('batchid'));

        // Map row data with "N/A" for missing/empty values
        $admissionno = $naIfEmpty($row[0] ?? null);
        $surname = $naIfEmpty($row[1] ?? null);
        $firstname = $naIfEmpty($row[2] ?? null);
        $othername = $naIfEmpty($row[3] ?? null);
        $rawGender = $naIfEmpty($row[4] ?? null);
        $futureAmbition = $naIfEmpty($row[5] ?? null);
        $rawDob = $naIfEmpty($row[6] ?? null);
        $rawAge = $naIfEmpty($row[7] ?? null);
        $placeofbirth = $naIfEmpty($row[8] ?? null);
        $nationality = $naIfEmpty($row[9] ?? null);
        $state = $naIfEmpty($row[10] ?? null);
        $local = $naIfEmpty($row[11] ?? null);
        $religion = $naIfEmpty($row[12] ?? null);
        $lastschool = $naIfEmpty($row[13] ?? null);
        $lastclass = $naIfEmpty($row[14] ?? null);

        $father_title = $naIfEmpty(Str::limit($row[18] ?? '', 3, ''));
        $father = $naIfEmpty(Str::substr($row[18] ?? '', 3));
        $father_phone = $naIfEmpty($row[19] ?? null);
        $office_address = $naIfEmpty($row[20] ?? null);
        $father_occupation = $naIfEmpty($row[21] ?? null);
        $mother_title = $naIfEmpty(Str::limit($row[22] ?? '', 3, ''));
        $mother = $naIfEmpty(Str::substr($row[22] ?? '', 3));
        $mother_phone = $naIfEmpty($row[23] ?? null);
        $mother_occupation = $naIfEmpty($row[24] ?? null);
        $mother_office_address = $naIfEmpty($row[25] ?? null);
        $parent_address = $naIfEmpty($row[26] ?? null);
        $parent_religion = $naIfEmpty($row[27] ?? null);

        // Normalize gender
        $gender = ucfirst(strtolower($rawGender));
        if (!in_array($gender, ['Male', 'Female'])) {
            $gender = 'N/A'; // Fallback if invalid
        }

        // Parse date of birth (handle common Excel date formats)
        $parsedDob = null;
        try {
            $parsedDob = Carbon::parse($rawDob);
        } catch (\Exception $e) {
            $parsedDob = Carbon::now()->subYears((int) ($rawAge === 'N/A' ? 11 : $rawAge))->startOfYear(); // Fallback based on age, default 11 if N/A
        }
        if ($parsedDob && $parsedDob->isFuture()) {
            $parsedDob = Carbon::now()->subYears((int) ($rawAge === 'N/A' ? 11 : $rawAge))->startOfYear();
        }

        // Set age: if 'N/A', use null or calculate from DOB; else cast to int
        $age = ($rawAge === 'N/A' || !is_numeric($rawAge)) ? null : (int) $rawAge;

        // Validate required fields
        if (in_array($admissionno, ['N/A', ''], true) || in_array($surname, ['N/A', ''], true) || in_array($firstname, ['N/A', ''], true)) {
            throw new \Exception("Required fields (admissionno, surname, firstname) cannot be empty or 'N/A' in row " . ($this->startRow() + $this->id));
        }

        // Validate session-based fields
        if (in_array($schoolclassid, ['N/A', ''], true) || in_array($termid, ['N/A', ''], true) || in_array($sessionid, ['N/A', ''], true) || in_array($batchid, ['N/A', ''], true)) {
            throw new \Exception("Session data (schoolclassid, termid, sessionid, batchid) cannot be empty or 'N/A' in row " . ($this->startRow() + $this->id));
        }

        // Initialize models
        $studentbiodata = new Student();
        $studentclass = new Studentclass();
        $promotion = new PromotionStatus();
        $parent = new ParentRegistration();
        $studenthouse = new Studenthouse();
        $picture = new Studentpicture();
        $studentpersonalityprofile = new Studentpersonalityprofile();
        $studentStatus = StudentStatus::where('status', 'old')->first();

        // Use transaction to ensure data consistency
        return \DB::transaction(function () use (
            $studentbiodata, $studentclass, $promotion, $parent, $studenthouse, $picture, $studentpersonalityprofile, $studentStatus,
            $admissionno, $surname, $firstname, $othername, $gender, $futureAmbition, $parsedDob, $age, $placeofbirth, $nationality, $state, $local, $religion, $lastschool, $lastclass,
            $father_title, $father, $father_phone, $office_address, $father_occupation, $mother_title, $mother, $mother_phone, $mother_occupation, $mother_office_address, $parent_address, $parent_religion,
            $schoolclassid, $termid, $sessionid, $batchid
        ) {
            // Populate student biodata
            $studentbiodata->admissionNo = $admissionno;
            $studentbiodata->title = 'N/A'; // Hardcoded as per original
            $studentbiodata->firstname = $firstname;
            $studentbiodata->lastname = $surname;
            $studentbiodata->othername = $othername;
            $studentbiodata->gender = $gender;
            $studentbiodata->future_ambition = $futureAmbition; // FIXED: Changed from home_address
            $studentbiodata->home_address2 = 'N/A'; // Consider mapping to permanent_address if updated in model
            $studentbiodata->dateofbirth = $parsedDob;
            $studentbiodata->age = $age;
            $studentbiodata->placeofbirth = $placeofbirth;
            $studentbiodata->religion = $religion;
            $studentbiodata->nationality = $nationality;
            $studentbiodata->state = $state;
            $studentbiodata->local = $local;
            $studentbiodata->last_school = $lastschool;
            $studentbiodata->last_class = $lastclass;
            $studentbiodata->registeredBy = Auth::user()->id ?? 'N/A';
            $studentbiodata->batchid = $batchid;
            $studentbiodata->statusId = $studentStatus ? $studentStatus->id : 'N/A';
            $studentbiodata->save();
            $studentId = $studentbiodata->id;

            // Populate parent data
            $parent->studentId = $studentId;
            $parent->father_title = $father_title;
            $parent->father = $father;
            $parent->father_phone = $father_phone;
            $parent->office_address = $office_address;
            $parent->father_occupation = $father_occupation;
            $parent->mother_title = $mother_title;
            $parent->mother = $mother;
            $parent->mother_phone = $mother_phone;
            $parent->mother_occupation = $mother_occupation;
            $parent->mother_office_address = $mother_office_address;
            $parent->parent_address = $parent_address;
            $parent->religion = $parent_religion;
            $parent->save();

            // Populate student picture
            $picture->studentid = $studentId;
            $picture->picture = 'unnamed.jpg'; // Updated to match store method default
            $picture->save();

            // Populate student class
            $studentclass->studentId = $studentId;
            $studentclass->schoolclassid = $schoolclassid;
            $studentclass->termid = $termid;
            $studentclass->sessionid = $sessionid;
            $studentclass->save();

            // Populate promotion status
            $promotion->studentId = $studentId;
            $promotion->schoolclassid = $schoolclassid;
            $promotion->termid = $termid;
            $promotion->sessionid = $sessionid;
            $promotion->promotionStatus = 'PROMOTED';
            $promotion->classstatus = 'CURRENT';
            $promotion->save();

            // Populate student house
            $studenthouse->studentid = $studentId;
            $studenthouse->termid = $termid;
            $studenthouse->sessionid = $sessionid;
            $studenthouse->schoolhouse = null; // Set to null instead of 'N/A' if it's an ID field
            $studenthouse->save();

            // Populate student personality profile
            $studentpersonalityprofile->studentid = $studentId;
            $studentpersonalityprofile->schoolclassid = $schoolclassid;
            $studentpersonalityprofile->termid = $termid;
            $studentpersonalityprofile->sessionid = $sessionid;
            $studentpersonalityprofile->save();

            $this->id++; // Increment row counter

            return $studentbiodata;
        });
    }

    /**
     * Validation rules for the Excel import.
     * Note: Column indices are 1-based (A=1, B=2, etc.).
     */
    public function rules(): array
    {
        $this->_sclassid = Session::get('sclassid') ?? 'N/A';
        $this->_termid = Session::get('tid') ?? 'N/A';
        $this->_sessionid = Session::get('sid') ?? 'N/A';
        $this->_batchid = Session::get('batchid') ?? 'N/A';

        return [
            '*.1' => 'required|string|max:255', // admissionno (column A)
            '*.2' => 'required|string|max:255', // surname (column B)
            '*.3' => 'required|string|max:255', // firstname (column C)
            '*.5' => [
                function ($attribute, $value, $onFailure) {
                    if ($value === 'N/A' || trim($value ?? '') === '') {
                        return; // Allow N/A or empty for gender
                    }
                    $normalized = ucfirst(strtolower(trim($value)));
                    if (!in_array($normalized, ['Male', 'Female'])) {
                        $onFailure('Gender must be Male or Female.');
                    }
                }
            ], // gender (column E) - normalized validation, allow N/A
            '*.7' => [
                function ($attribute, $value, $onFailure) {
                    if ($value === 'N/A' || trim($value ?? '') === '') {
                        return; // Allow N/A or empty for DOB
                    }
                    try {
                        $parsed = Carbon::parse($value);
                        if ($parsed->gte(Carbon::today())) {
                            $onFailure('Date of birth must be a valid date before today.');
                        }
                    } catch (\Exception $e) {
                        $onFailure('Date of birth must be a valid date before today.');
                    }
                }
            ], // dateofbirth (column G) - flexible parsing validation, allow N/A
            '*.8' => [
                function ($attribute, $value, $onFailure) {
                    if ($value === 'N/A' || trim($value ?? '') === '') {
                        return; // Allow N/A or empty for age
                    }
                    if (!is_numeric($value) || (int)$value < 1 || (int)$value > 100) {
                        $onFailure('Age must be a number between 1 and 100.');
                    }
                }
            ], // age (column H) - allow N/A, validate if present
            // Note: Removed mismatched '15','16','17' as they don't align with Excel columns; session data is from PHP session, not Excel
            // If Excel has columns for class/term/session IDs, add them here with proper indices (e.g., '*.15' for column O)
        ];
    }

    /**
     * Custom validation messages.
     */
    public function customValidationMessages()
    {
        return [
            '*.1.required' => 'Admission number is required.',
            '*.2.required' => 'Surname is required.',
            '*.3.required' => 'First name is required.',
            // Messages for closures are handled directly in $onFailure
        ];
    }

    /**
     * Custom validation attribute names.
     */
    public function customValidationAttributes()
    {
        return [
            '*.1' => 'admissionno',
            '*.2' => 'surname',
            '*.3' => 'firstname',
            '*.5' => 'gender',
            '*.7' => 'dateofbirth',
            '*.8' => 'age',
            // Adjust as needed
        ];
    }

    /**
     * Start reading from row 2 (skip header).
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
     * Unique identifier for upserts.
     */
    public function uniqueBy()
    {
        return ['admissionNo'];
    }

    /**
     * Columns to update during upserts.
     */
    public function upsertColumns()
    {
        return [
            'title',
            'firstname',
            'lastname',
            'othername',
            'gender',
            'future_ambition', // FIXED: Changed from home_address
            'home_address2',
            'dateofbirth',
            'age',
            'placeofbirth',
            'religion',
            'nationality',
            'state',
            'local',
            'last_school',
            'last_class',
            'registeredBy',
            'batchid',
            'statusId',
        ];
    }

    /**
     * Handle validation failures.
     */
    public function onFailure(Failure ...$failures)
    {
        // Update batch status only if all rows fail; for partial, handle differently
        StudentBatchModel::where('id', $this->_batchid)->update(['status' => 'Failed']); // Fixed column name to 'status'
        foreach ($failures as $failure) {
            \Log::error('Excel Import Failure', [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values(),
            ]);
        }
        // For partial imports, don't throw; let it continue. Remove throw if desired.
        // throw new \Exception('Validation failed for row ' . $failures[0]->row() . ': ' . implode(', ', $failures[0]->errors()));
    }

    /**
     * Handle exceptions during import.
     */
    public function onError(\Throwable $e)
    {
        StudentBatchModel::where('id', $this->_batchid)->update(['status' => 'Failed']); // Fixed column name to 'status'
        \Log::error('Excel Import Error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        // Re-throw to stop import on critical errors
        throw $e;
    }
}