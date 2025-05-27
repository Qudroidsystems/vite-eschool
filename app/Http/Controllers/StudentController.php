<?php

namespace App\Http\Controllers;

use App\Imports\StudentsImport;
use App\Models\Broadsheet;
use App\Models\Parentregistration;
use App\Models\PromotionStatus;
use App\Models\Schoolclass;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Student;
use App\Models\StudentBatchModel;
use App\Models\Studentclass;
use App\Models\Studenthouse;
use App\Models\Studentpersonalityprofile;
use App\Models\Studentpicture;
use App\Models\SubjectRegistrationStatus;
use App\Traits\ImageManager as TraitsImageManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    use TraitsImageManager;

    public function __construct()
    {
        $this->middleware("permission:View student|Create student|Update student|Delete student", ["only" => ["index", "store"]]);
        $this->middleware("permission:Create student", ["only" => ["create", "store"], ]);
        $this->middleware("permission:Update student", [ "only" => ["edit", "update"], ]);
        $this->middleware("permission:Delete student", ["only" => ["destroy", "deletestudent"], ]);
        $this->middleware("permission:student-bulk-upload", ["only" => ["bulkupload"], ]);
        $this->middleware("permission:student-bulk-uploadsave", [ "only" => ["bulkuploadsave"],]);
    }

    public function index(Request $request)
    {
          // Page title
          $pagetitle = "User Management";

        $data = Student::leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('studentclass', 'studentclass.studentId', '=', 'studentRegistration.id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select([
                'studentRegistration.id',
                'studentRegistration.admissionNo',
                'studentRegistration.firstname',
                'studentRegistration.lastname',
                'studentRegistration.gender',
                'studentRegistration.statusId',
                'studentRegistration.created_at',
                'studentpicture.picture',
                'schoolclass.schoolclass',
                'schoolarm.arm',
                'studentclass.schoolclassid'
            ])
            ->latest()
            ->paginate(10);

        $schoolclass = Schoolclass::all();
        $schoolterm = Schoolterm::all();
        $schoolsession = Schoolsession::all();

        $status_counts = Student::groupBy('statusId')
            ->selectRaw("CASE WHEN statusId = 1 THEN 'Old Student' ELSE 'New Student' END as student_status, COUNT(*) as student_count")
            ->pluck('student_count', 'student_status')
            ->toArray();
        $status_counts = [
            'Old Student' => $status_counts['Old Student'] ?? 0,
            'New Student' => $status_counts['New Student'] ?? 0
        ];

        return view('student.index', compact('data', 'schoolclass', 'schoolterm', 'schoolsession', 'status_counts','pagetitle'))
            ->with('i', ($request->input('page', 1) - 1) * 10);
    }

    public function create()
    {
        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id as id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
            ->sortBy('sdesc');
        $schoolterm = Schoolterm::all();
        $schoolsession = Schoolsession::all();

        return view('student.create')
            ->with('schoolclass', $schoolclass)
            ->with('schoolterm', $schoolterm)
            ->with('schoolsession', $schoolsession);
    }

    public function store(Request $request): JsonResponse
    {
        Log::debug('Creating new student', $request->all());

        try {
            // Load states and LGAs for validation
            $statesLgas = json_decode(file_get_contents(public_path('states_lgas.json')), true);
            $states = array_column($statesLgas, 'state');
            $lgas = collect($statesLgas)->pluck('lgas', 'state')->toArray();

            $validator = Validator::make($request->all(), [
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'admissionNo' => 'required|unique:studentRegistration,admissionNo',
                'title' => 'required|in:Mr,Mrs,Miss',
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'gender' => 'required|in:Male,Female',
                'nationality' => 'required|string|max:255',
                'state' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) use ($states) {
                    if (!in_array($value, $states)) {
                        $fail('The selected state is invalid.');
                    }
                }],
                'local' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) use ($request, $lgas) {
                    $state = $request->input('state');
                    if (!isset($lgas[$state]) || !in_array($value, $lgas[$state])) {
                        $fail('The selected local government is invalid for the chosen state.');
                    }
                }],
                'religion' => 'required|in:Christianity,Islam,Others',
                'dateofbirth' => 'required|date|before:today',
                'bloodgroup' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
                'genotype' => 'nullable|in:AA,AS,SS,AC',
                'schoolclassid' => 'required|exists:schoolclass,id',
                'termid' => 'required|exists:schoolterm,id',
                'sessionid' => 'required|exists:schoolsession,id',
                'statusId' => 'required|in:1,2'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            DB::beginTransaction();

            $student = new Student();
            $student->admissionNo = $request->admissionNo;
            $student->title = $request->title; // Corrected from 'tittle'
            $student->firstname = $request->firstname;
            $student->lastname = $request->lastname;
            $student->gender = $request->gender;
            $student->nationality = $request->nationality;
            $student->state = $request->state;
            $student->local = $request->local;
            $student->religion = $request->religion;
            $student->dateofbirth = $request->dateofbirth;
            $student->bloodgroup = $request->bloodgroup ?: null;
            $student->genotype = $request->genotype ?: null;
            $student->statusId = $request->statusId;
            $student->registeredBy = auth()->user()->id;
            $student->save();

            $studentId = $student->id;

            $studentClass = new Studentclass();
            $studentClass->studentId = $studentId;
            $studentClass->schoolclassid = $request->schoolclassid;
            $studentClass->termid = $request->termid;
            $studentClass->sessionid = $request->sessionid;
            $studentClass->save();

            $promotion = new PromotionStatus();
            $promotion->studentId = $studentId;
            $promotion->schoolclassid = $request->schoolclassid;
            $promotion->termid = $request->termid;
            $promotion->sessionid = $request->sessionid;
            $promotion->promotionStatus = 'PROMOTED';
            $promotion->classstatus = 'CURRENT';
            $promotion->save();

            $parent = new Parentregistration();
            $parent->studentId = $studentId;
            $parent->save();

            $picture = new Studentpicture();
            $picture->studentid = $studentId;
            if ($request->hasFile('avatar')) {
                $filename = $studentId . '_' . $request->file('avatar')->getClientOriginalName();
                $path = $request->file('avatar')->storeAs('public/images/studentavatar', $filename);
                $picture->picture = str_replace('public/', '', $path);
            }
            $picture->save();

            $studenthouse = new Studenthouse();
            $studenthouse->studentid = $studentId;
            $studenthouse->termid = $request->termid;
            $studenthouse->sessionid = $request->sessionid;
            $studenthouse->save();

            $studentpersonalityprofile = new Studentpersonalityprofile();
            $studentpersonalityprofile->studentid = $studentId;
            $studentpersonalityprofile->schoolclassid = $request->schoolclassid;
            $studentpersonalityprofile->termid = $request->termid;
            $studentpersonalityprofile->sessionid = $request->sessionid;
            $studentpersonalityprofile->save();

            DB::commit();

            Log::debug("Student created successfully: ID {$studentId}");
            return response()->json([
                'success' => true,
                'message' => 'Student created successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error creating student: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to create student: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id): JsonResponse
    {
        try {
            $student = Student::leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
                ->leftJoin('studentclass', 'studentclass.studentId', '=', 'studentRegistration.id')
                ->select([
                    'studentRegistration.id',
                    'studentRegistration.admissionNo',
                    'studentRegistration.title',
                    'studentRegistration.firstname',
                    'studentRegistration.lastname',
                    'studentRegistration.gender',
                    'studentRegistration.nationality',
                    'studentRegistration.state',
                    'studentRegistration.local',
                    'studentRegistration.religion',
                    'studentRegistration.dateofbirth',
                    'studentRegistration.bloodgroup',
                    'studentRegistration.genotype',
                    'studentRegistration.statusId',
                    'studentpicture.picture',
                    'studentclass.schoolclassid',
                    'studentclass.termid',
                    'studentclass.sessionid'
                ])
                ->where('studentRegistration.id', $id)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'student' => $student
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error fetching student: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch student: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        Log::debug("Updating student ID {$id}", $request->all());

        try {
            // Load states and LGAs for validation
            $statesLgas = json_decode(file_get_contents(public_path('states_lgas.json')), true);
            $states = array_column($statesLgas, 'state');
            $lgas = collect($statesLgas)->pluck('lgas', 'state')->toArray();

            $validator = Validator::make($request->all(), [
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'admissionNo' => "required|unique:studentRegistration,admissionNo,{$id}",
                'title' => 'required|in:Mr,Mrs,Miss',
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'gender' => 'required|in:Male,Female',
                'nationality' => 'required|string|max:255',
                'state' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) use ($states) {
                    if (!in_array($value, $states)) {
                        $fail('The selected state is invalid.');
                    }
                }],
                'local' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) use ($request, $lgas) {
                    $state = $request->input('state');
                    if (!isset($lgas[$state]) || !in_array($value, $lgas[$state])) {
                        $fail('The selected local government is invalid for the chosen state.');
                    }
                }],
                'religion' => 'required|in:Christianity,Islam,Others',
                'dateofbirth' => 'required|date|before:today',
                'bloodgroup' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
                'genotype' => 'nullable|in:AA,AS,SS,AC',
                'schoolclassid' => 'required|exists:schoolclass,id',
                'termid' => 'required|exists:schoolterm,id',
                'sessionid' => 'required|exists:schoolsession,id',
                'statusId' => 'required|in:1,2'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            DB::beginTransaction();

            $student = Student::findOrFail($id);
            $student->admissionNo = $request->admissionNo;
            $student->title = $request->title; // Corrected from 'tittle'
            $student->firstname = $request->firstname;
            $student->lastname = $request->lastname;
            $student->gender = $request->gender;
            $student->nationality = $request->nationality;
            $student->state = $request->state;
            $student->local = $request->local;
            $student->religion = $request->religion;
            $student->dateofbirth = $request->dateofbirth;
            $student->bloodgroup = $request->bloodgroup ?: null;
            $student->genotype = $request->genotype ?: null;
            $student->statusId = $request->statusId;
            $student->registeredBy = auth()->user()->id;
            $student->save();

            Studentclass::updateOrCreate(
                ['studentId' => $id],
                [
                    'schoolclassid' => $request->schoolclassid,
                    'termid' => $request->termid,
                    'sessionid' => $request->sessionid
                ]
            );

            if ($request->hasFile('avatar')) {
                $picture = Studentpicture::where('studentid', $id)->first();
                if ($picture && $picture->picture) {
                    Storage::delete('public/' . $picture->picture);
                }
                $filename = $id . '_' . $request->file('avatar')->getClientOriginalName();
                $path = $request->file('avatar')->storeAs('public/images/studentavatar', $filename);
                Studentpicture::updateOrCreate(
                    ['studentid' => $id],
                    ['picture' => str_replace('public/', '', $path)]
                );
            }

            DB::commit();

            Log::debug("Student updated successfully: ID {$id}");
            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating student: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to update student: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        Log::debug("Deleting student ID {$id}");

        try {
            DB::beginTransaction();

            $student = Student::findOrFail($id);
            $picture = Studentpicture::where('studentid', $id)->first();
            if ($picture && $picture->picture) {
                Storage::delete('public/' . $picture->picture);
            }

            Studentclass::where('studentId', $id)->delete();
            PromotionStatus::where('studentId', $id)->delete();
            Parentregistration::where('studentId', $id)->delete();
            Studentpicture::where('studentid', $id)->delete();
            Broadsheet::where('studentId', $id)->delete();
            SubjectRegistrationStatus::where('studentId', $id)->delete();
            Studenthouse::where('studentid', $id)->delete();
            Studentpersonalityprofile::where('studentid', $id)->delete();
            $student->delete();

            DB::commit();

            Log::debug("Student deleted successfully: ID {$id}");
            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting student: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyMultiple(Request $request): JsonResponse
    {
        Log::debug('Bulk deleting students', $request->all());

        try {
            $ids = $request->validate(['ids' => 'required|array|exists:studentRegistration,id'])['ids'];
            DB::beginTransaction();

            foreach ($ids as $id) {
                $picture = Studentpicture::where('studentid', $id)->first();
                if ($picture && $picture->picture) {
                    Storage::delete('public/' . $picture->picture);
                }

                Studentclass::where('studentId', $id)->delete();
                PromotionStatus::where('studentId', $id)->delete();
                Parentregistration::where('studentId', $id)->delete();
                Studentpicture::where('studentid', $id)->delete();
                Broadsheet::where('studentId', $id)->delete();
                SubjectRegistrationStatus::where('studentId', $id)->delete();
                Studenthouse::where('studentid', $id)->delete();
                Studentpersonalityprofile::where('studentid', $id)->delete();
            }

            Student::whereIn('id', $ids)->delete();

            DB::commit();

            Log::debug('Bulk deleted students: ' . implode(',', $ids));
            return response()->json([
                'success' => true,
                'message' => 'Students deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Bulk delete error: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete students: ' . $e->getMessage()
            ], 500);
        }
    }

 



    public function deletestudent(Request $request)
    {
        Student::find($s)->delete();
        Studentclass::where("studentId", $s)->delete();
        Studenthouse::where("studentid", $s)->delete();
        Promotionstatus::where("studentId", $s)->delete();
        ParentRegistration::where("studentId", $s)->delete();
        Studentpicture::where("studentid", $s)->delete();
        Broadsheet::where("studentId", $s)->delete();
        SubjectRegistrationStatus::where("studentId", $s)->delete();
        // check data deleted or not
        if ($s) {
            $success = true;
            $message = "Student has been removed";
        } else {
            $success = true;
            $message = "Student not found";
        }

        //  return response
        return response()->json([
            "success" => $success,
            "message" => $message,
        ]);
    }

    public function deletestudentbatch(Request $request)
    {
        // StudentBatchModel::find($request->studentbatchid)->delete();
        $batch = StudentBatchModel::where("id", $request->studentbatchid)
            ->pluck("id")
            ->first();
        $sc = Student::where("batchid", $batch)->pluck("id");

        foreach ($sc as $s) {
            Studentclass::where("studentId", $s)->delete();
            Studenthouse::where("studentid", $s)->delete();
            PromotionStatus::where("studentId", $s)->delete();
            ParentRegistration::where("studentId", $s)->delete();
            Studentpicture::where("studentid", $s)->delete();
            Broadsheet::where("studentId", $s)->delete();
            SubjectRegistrationStatus::where("studentId", $s)->delete();
            Studentpersonalityprofile::where("studentId", $s)->delete();
        }
        StudentBatchModel::where("id", $request->studentbatchid)->delete();
        Student::where("batchid", $batch)->delete();
        //check data deleted or not
        if ($request->studentbatchid) {
            $success = true;
            $message = "Batch Upload has been removed";
        } else {
            $success = true;
            $message = "Batch Upload not found";
        }

        //  return response
        return response()->json([
            "success" => $success,
            "message" => $message,
        ]);
    }



    public function bulkupload()
    {
        $schoolclass = Schoolclass::leftJoin("schoolarm", "schoolarm.id", "=", "schoolclass.arm")
            ->get(["schoolclass.id as id", "schoolclass.schoolclass as schoolclass", "schoolarm.arm as arm"])
            ->sortBy("sdesc");
        $schoolterm = Schoolterm::all();
        $schoolsession = Schoolsession::all();

        return view("student.bulkupload")
            ->with("schoolclass", $schoolclass)
            ->with("schoolterm", $schoolterm)
            ->with("schoolsession", $schoolsession);
    }

    public function batchindex()
    {
        $batch = StudentBatchModel::leftJoin("schoolclass", "schoolclass.id", "=", "student_batch_upload.schoolclassid")
            ->leftJoin("schoolsession", "schoolsession.id", "=", "student_batch_upload.session")
            ->leftJoin("schoolterm", "schoolterm.id", "=", "student_batch_upload.termid")
            ->leftJoin("schoolarm", "schoolarm.id", "=", "schoolclass.arm")
            ->orderBy("upload_date", "desc")
            ->get([
                "student_batch_upload.id as id",
                "student_batch_upload.title as title",
                "schoolclass.schoolclass as schoolclass",
                "schoolterm.term as term",
                "schoolsession.session as session",
                "schoolarm.arm as arm",
                "student_batch_upload.status as status",
                "student_batch_upload.updated_at as upload_date",
            ]);

        return view("student.batchindex")->with("batch", $batch);
    }

    public function bulkuploadsave(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "filesheet" => "required|mimes:xlsx, csv, xls",
            "title" => "required",
            "termid" => "required",
            "sessionid" => "required",
            "schoolclassid" => "required",
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }
        $batchchk = StudentBatchModel::where("title", $request->title)->exists();
        if ($batchchk) {
            return redirect()
                ->back()
                ->with("success", "Title  is already choosen,  Please choosen another Title for this Batch Upload");
        } else {
            //echo  $request->schoolclassid;

            $batch = new StudentBatchModel();
            $batch->title = $request->title;
            $batch->schoolclassid = $request->schoolclassid;
            $batch->termid = $request->termid;
            $batch->session = $request->sessionid;
            $batch->status = "";
            $batch->save();

            Session::put("sclassid", $request->schoolclassid);
            Session::put("tid", $request->termid);
            Session::put("sid", $request->sessionid);
            Session::put("batchid", $batch->id);
            $file = $request->file("filesheet");
            //Excel::import(new StudentsImport(),$file );
            $import = new StudentsImport();

            try {
                $import->import($file, null, \Maatwebsite\Excel\Excel::XLSX);
                StudentBatchModel::where("id", $batch->id)->update([
                    "Status" => "Success",
                ]);

                return redirect()
                    ->back()
                    ->with("success", "Student Batch File Imported  Successfully");
                // $import->import('import-users.xlsx');
            } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                StudentBatchModel::where("id", $batch->id)->update([
                    "Status" => "Failed",
                ]);
                $failures = $e->failures();

                foreach ($failures as $failure) {
                    $failure->row(); // row that went wrong
                    $failure->attribute(); // either heading key (if using heading row concern) or column index
                    $fail = $failure->errors(); // Actual error messages from Laravel validator
                    $failure->values(); // The values of the row that has failed.
                }

                return redirect()
                    ->back()
                    ->with("status", implode(" ", $fail));
            }
        }
    }
}