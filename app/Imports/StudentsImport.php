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
use Maatwebsite\Excel\Concerns\WithUpsertColumns;

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
     * Parse Excel date with support for serial dates and multiple formats
     * Returns only the date part without time
     */
    private function parseExcelDate($rawDate)
    {
        // Handle empty values
        if (empty($rawDate) || $rawDate === 'N/A' || trim($rawDate) === '') {
            return null;
        }
        
        // Handle Excel serial dates (numbers)
        if (is_numeric($rawDate)) {
            $excelBaseDate = Carbon::create(1899, 12, 30); // Excel Windows base date
            $parsedDate = $excelBaseDate->addDays((int)$rawDate);
            
            // For students, allow dates back to 1940 (for very old teachers/staff, but adjust as needed)
            if ($parsedDate->isFuture()) {
                // If date is in future, subtract 100 years (common Excel issue with dates)
                $parsedDate = $parsedDate->subYears(100);
            }
            
            // Return only date part without time
            return $parsedDate->startOfDay();
        }
        
        // Handle string dates - be more flexible with parsing
        try {
            $parsedDate = Carbon::parse($rawDate);
            
            // If it's a datetime string like "2012-02-07 00:00:00", extract only the date part
            if (strpos($rawDate, ' ') !== false || strpos($rawDate, 'T') !== false) {
                // Extract date part from datetime string
                $datePart = explode(' ', $rawDate)[0];
                $datePart = explode('T', $datePart)[0];
                $parsedDate = Carbon::parse($datePart);
            }
            
            // Return only date part without time
            return $parsedDate->startOfDay();
        } catch (\Exception $e) {
            // Try common date formats more aggressively
            $formats = [
                'd/m/Y', 'm/d/Y', 'Y-m-d', 'd-m-Y', 'm-d-Y',
                'd/M/Y', 'M/d/Y', 'd.M.Y', 'M d, Y', 'd F Y',
                'd/m/y', 'm/d/y', 'y-m-d', 'd-m-y', 'm-d-y',
                'd-M-Y', 'd.M.y', 'm/d', 'd/m', 'Ymd', 'dmY'
            ];
            
            foreach ($formats as $format) {
                try {
                    $parsedDate = Carbon::createFromFormat($format, $rawDate);
                    // If year is 2-digit, assume it's in 1900-1999 range
                    if ($parsedDate->year < 100) {
                        $parsedDate = $parsedDate->addYears(1900);
                    }
                    // Return only date part without time
                    return $parsedDate->startOfDay();
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            // If all parsing fails, try to extract date components manually
            preg_match('/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})/', $rawDate, $matches);
            if (count($matches) === 4) {
                $day = (int)$matches[1];
                $month = (int)$matches[2];
                $year = (int)$matches[3];
                
                // Handle 2-digit years
                if ($year < 100) {
                    $year += 1900;
                }
                
                if (checkdate($month, $day, $year)) {
                    return Carbon::create($year, $month, $day)->startOfDay();
                }
            }
            
            // Last attempt: try to extract YYYY-MM-DD from datetime string
            preg_match('/(\d{4}-\d{2}-\d{2})/', $rawDate, $matches);
            if (count($matches) === 2) {
                try {
                    return Carbon::parse($matches[1])->startOfDay();
                } catch (\Exception $e) {
                    // Continue to throw exception
                }
            }
            
            throw new \Exception("Unable to parse date: {$rawDate}");
        }
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

        // Parse date of birth with improved logic
        $parsedDob = null;
        $age = null;
        
        try {
            if ($rawDob !== 'N/A') {
                $parsedDob = $this->parseExcelDate($rawDob);
                
                // If date is in future, adjust it (common Excel issue)
                if ($parsedDob && $parsedDob->isFuture()) {
                    $parsedDob = $parsedDob->subYears(100);
                }
                
                // If date is too old (before 1940), use age to estimate
                if ($parsedDob && $parsedDob->year < 1940) {
                    if ($rawAge !== 'N/A' && is_numeric($rawAge)) {
                        $age = (int)$rawAge;
                        $estimatedBirthYear = Carbon::now()->subYears($age)->year;
                        $parsedDob = Carbon::create($estimatedBirthYear, 1, 1);
                        \Log::warning("DOB too old ({$parsedDob->format('Y-m-d')}), using estimated DOB from age: {$parsedDob->format('Y-m-d')}");
                    }
                }
            }
        } catch (\Exception $e) {
            // If DOB parsing fails, use age to estimate birth year
            if ($rawAge !== 'N/A' && is_numeric($rawAge)) {
                $age = (int)$rawAge;
                $estimatedBirthYear = Carbon::now()->subYears($age)->year;
                $parsedDob = Carbon::create($estimatedBirthYear, 1, 1);
                
                \Log::warning("DOB parsing failed for '{$rawDob}', using estimated DOB from age: {$parsedDob->format('Y-m-d')}");
            } else {
                // If both DOB and age are invalid, use reasonable default for students (11 years old)
                $parsedDob = Carbon::now()->subYears(11)->startOfYear();
                \Log::warning("Both DOB and age invalid, using default DOB: {$parsedDob->format('Y-m-d')}");
            }
        }

        // Set age: if 'N/A', calculate from DOB; else use provided age
        if ($rawAge === 'N/A' || !is_numeric($rawAge)) {
            $age = $parsedDob ? $parsedDob->diffInYears(Carbon::now()) : null;
        } else {
            $age = (int)$rawAge;
        }

        // Validate required fields
        if (in_array($admissionno, ['N/A', ''], true) || in_array($surname, ['N/A', ''], true) || in_array($firstname, ['N/A', ''], true)) {
            throw new \Exception("Required fields (admissionno, surname, firstname) cannot be empty or 'N/A' in row " . ($this->startRow() + $this->id));
        }

        // Validate session-based fields
        if (in_array($schoolclassid, ['N/A', ''], true) || in_array($termid, ['N/A', ''], true) || in_array($sessionid, ['N/A', ''], true) || in_array($batchid, ['N/A', ''], true)) {
            throw new \Exception("Session data (schoolclassid, termid, sessionid, batchid) cannot be empty or 'N/A' in row " . ($this->startRow() + $this->id));
        }

        // Debug logging for first few rows to help with troubleshooting
        if ($this->id < 5) {
            \Log::info("Import Debug - Row {$this->id}:", [
                'raw_dob' => $rawDob,
                'raw_age' => $rawAge,
                'parsed_dob' => $parsedDob ? $parsedDob->format('Y-m-d') : 'null',
                'final_age' => $age
            ]);
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
            $studentbiodata->future_ambition = $futureAmbition;
            $studentbiodata->home_address2 = 'N/A';
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
            $picture->picture = 'unnamed.jpg';
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
            $studenthouse->schoolhouse = null;
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
            ], // gender (column E)
            '*.7' => [
                function ($attribute, $value, $onFailure) {
                    if ($value === 'N/A' || trim($value ?? '') === '') {
                        return; // Allow N/A or empty for DOB
                    }
                    
                    // Be more lenient with date validation - just check if it's somewhat parsable
                    try {
                        // For validation, just try basic parsing without strict range checks
                        if (is_numeric($value)) {
                            // It's an Excel serial number - accept it
                            return;
                        } else {
                            // Try basic date parsing
                            Carbon::parse($value);
                        }
                    } catch (\Exception $e) {
                        $onFailure('Date of birth must be in a recognizable date format.');
                    }
                }
            ], // dateofbirth (column G) - more lenient validation
            '*.8' => [
                function ($attribute, $value, $onFailure) {
                    if ($value === 'N/A' || trim($value ?? '') === '') {
                        return; // Allow N/A or empty for age
                    }
                    if (!is_numeric($value) || (int)$value < 1 || (int)$value > 100) {
                        $onFailure('Age must be a number between 1 and 100.');
                    }
                }
            ], // age (column H)
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
            'future_ambition',
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
        StudentBatchModel::where('id', $this->_batchid)->update(['status' => 'Failed']);
        foreach ($failures as $failure) {
            \Log::error('Excel Import Failure', [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values(),
            ]);
        }
    }

    /**
     * Handle exceptions during import.
     */
    public function onError(\Throwable $e)
    {
        StudentBatchModel::where('id', $this->_batchid)->update(['status' => 'Failed']);
        \Log::error('Excel Import Error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        throw $e;
    }
}