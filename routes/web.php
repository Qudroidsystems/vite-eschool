<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\BiodataController;
use App\Http\Controllers\ClasscategoryController;
use App\Http\Controllers\ClassOperationController;
use App\Http\Controllers\ClassTeacherController;
use App\Http\Controllers\MyClassController;
use App\Http\Controllers\MyresultroomController;
use App\Http\Controllers\MyScoreSheetController;
use App\Http\Controllers\MySubjectController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\SchoolArmController;
use App\Http\Controllers\SchoolBillController;
use App\Http\Controllers\SchoolBillTermSessionController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SchoolHouseController;
use App\Http\Controllers\SchoolPaymentController;
use App\Http\Controllers\SchoolsessionController;
use App\Http\Controllers\SchooltermController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffImageUploadController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentHouseController;
use App\Http\Controllers\StudentImageUploadController;
use App\Http\Controllers\StudentpersonalityprofileController;
use App\Http\Controllers\StudentResultsController;
use App\Http\Controllers\SubjectClassController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SubjectOperationController;
use App\Http\Controllers\SubjectTeacherController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ViewStudentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\CBTController;
use App\Http\Controllers\ResultController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();
Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::group(['middleware' => ['auth']], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/all', [UserController::class, 'allUsers'])->name('users.all');
    Route::get('/users/paginate', [UserController::class, 'paginate'])->name('users.paginate');
    Route::get('/users/roles', [UserController::class, 'roles']);
    Route::resource('permissions', PermissionController::class);

    Route::get('users/add-student', [UserController::class, 'createFromStudentForm'])->name('users.add-student-form');
    Route::post('users/create-from-student', [UserController::class, 'createFromStudent'])->name('users.createFromStudent');
    Route::get('/get-students', [UserController::class, 'getStudents'])->name('get.students');

    Route::resource('biodata', BiodataController::class);
    Route::get('/overview/{id}', [OverviewController::class, 'show'])->name('user.overview');
    Route::get('/settings/{id}', [BiodataController::class, 'show'])->name('user.settings');
    Route::post('ajaxemailupdate', [BiodataController::class, 'ajaxemailupdate']);
    Route::post('ajaxpasswordupdate', [BiodataController::class, 'ajaxpasswordupdate']);

    Route::get('/adduser/{id}', [RoleController::class, 'adduser'])->name('roles.adduser');
    Route::post('/updateuserrole', [RoleController::class, 'updateuserrole'])->name('roles.updateuserrole');
    Route::delete('roles/removeuserrole/{userid}/{roleid}', [RoleController::class, 'removeuserrole'])->name('roles.removeuserrole');

    Route::resource('subject', SubjectController::class);
    Route::get('/subjectid/{subjectid}', [SubjectController::class, 'deletesubject'])->name('subject.deletesubject');
    Route::post('subjectid', [SubjectController::class, 'updatesubject'])->name('subject.updatesubject');

    Route::resource('subjectclass', SubjectClassController::class);
    Route::delete('subjectclass/deletesubjectclass/{subjectclassid}', [SubjectClassController::class, 'deletesubjectclass'])->name('subjectclass.deletesubjectclass');
    Route::get('/subjectclass/assignments/{subjectteacherid}', [SubjectClassController::class, 'assignments'])->name('subjectclass.assignments');
    Route::get('/subjectclass/assignments-by-teacher/{subjectTeacherId}', [SubjectClassController::class, 'assignmentsBySubjectTeacher'])->name('subjectclass.assignmentsByTeacher');
  

    Route::resource('staff', StaffController::class);

    // Route::resource('subjectteacher', SubjectTeacherController::class);
    // Route::get('subjectteacher/{subjectteacher}/subjects', [SubjectTeacherController::class, 'getSubjects'])->name('subjectteacher.subjects');
    // Route::get('/subjectteacherid/{subjectteacherid}', [SubjectTeacherController::class, 'deletesubjectteacher'])->name('subjectteacher.deletesubjectteacher');
    // Route::post('subjectteacherid', [SubjectTeacherController::class, 'updatesubjectteacher'])->name('subjectteacher.updatesubjectteacher');
    // Route::get('subjectteacher/{id}/subjects', [SubjectTeacherController::class, 'getSubjects'])->name('subjectteacher.subjects');



    Route::resource('subjectteacher', SubjectTeacherController::class)->except(['update']);
    Route::match(['put', 'post'], 'subjectteacher/{id}', [SubjectTeacherController::class, 'update'])->name('subjectteacher.update');
    Route::get('subjectteacher/{id}/subjects', [SubjectTeacherController::class, 'getSubjects'])->name('subjectteacher.subjects');
    Route::post('subjectteacher/delete', [SubjectTeacherController::class, 'deletesubjectteacher'])->name('subjectteacher.delete');

    Route::resource('classteacher', ClassTeacherController::class);
    Route::get('/classteacher/assignments/{staffId}/{termId}/{sessionId}', [ClassTeacherController::class, 'assignments'])->name('classteacher.assignments');
    Route::post('/classteacher/delete', [ClassTeacherController::class, 'deleteMultiple'])->name('classteacher.deleteMultiple');


    Route::resource('session', SchoolsessionController::class);
    Route::get('/sessionid/{sessionid}', [SchoolsessionController::class, 'deletesession'])->name('session.deletesession');
    Route::post('updatesessionid', [SchoolsessionController::class, 'updatesession'])->name('session.updatesession');

    Route::resource('schoolhouse', SchoolHouseController::class);
    Route::post('schoolhouse/deletehouse', [SchoolHouseController::class, 'deletehouse'])->name('schoolhouse.deletehouse');
    Route::post('schoolhouse/updatehouse', [SchoolHouseController::class, 'updatehouse'])->name('schoolhouse.updatehouse');

    Route::resource('term', SchooltermController::class);
    Route::post('term/deleteterm', [SchooltermController::class, 'deleteterm'])->name('term.deleteterm');
    Route::post('term/updateterm', [SchooltermController::class, 'updateterm'])->name('term.updateterm');

    Route::resource('schoolarm', SchoolArmController::class);
    Route::post('schoolarm/deletearm', [SchoolArmController::class, 'deletearm'])->name('schoolarm.deletearm');
    Route::post('schoolarm/updatearm', [SchoolArmController::class, 'updatearm'])->name('schoolarm.updatearm');
    Route::post('/schoolclass/deletes-schoolclass', [SchoolClassController::class, 'deleteschoolclass'])->name('schoolclass.deleteschoolclass');
    Route::get('/schoolclasses/{getArms}/arms', [SchoolClassController::class, 'getArms'])->name('schoolclass.getArms');

    Route::get('schoolclass', [SchoolClassController::class, 'index'])->name('schoolclass.index');
    Route::post('schoolclass', [SchoolClassController::class, 'store'])->name('schoolclass.store');
    Route::put('schoolclass/{schoolclass}', [SchoolClassController::class, 'update'])->name('schoolclass.update');
    Route::delete('schoolclass/{schoolclass}', [SchoolClassController::class, 'destroy'])->name('schoolclass.destroy');
    Route::post('schoolclass/deleteschoolclass', [SchoolClassController::class, 'deleteschoolclass'])->name('schoolclass.deleteschoolclass');
    Route::get('schoolclass/{schoolclass}/arms', [SchoolClassController::class, 'getArms'])->name('schoolclass.getarms');
    Route::put('/schoolclass/{id}', [SchoolClassController::class, 'update'])->name('schoolclass.update');

    Route::resource('student', StudentController::class);
    Route::get('/studentid/{studentid}', [StudentController::class, 'deletestudent'])->name('student.deletestudent');
    Route::get('/studentoverview/{id}', [StudentController::class, 'overview'])->name('student.overview');
    Route::get('/studentsettings/{id}', [StudentController::class, 'setting'])->name('student.settings');
    Route::get('/studentbulkupload', [StudentController::class, 'bulkupload'])->name('student.bulkupload');
    Route::post('/studentbulkuploadsave', [StudentController::class, 'bulkuploadsave'])->name('student.bulkuploadsave');
    Route::get('/batchindex', [StudentController::class, 'batchindex'])->name('student.batchindex');
    Route::get('/studentbatchid/{studentbatchid}', [StudentController::class, 'deletestudentbatch'])->name('student.deletestudentbatch');

    Route::resource('classoperation', ClassOperationController::class);

    Route::resource('classcategories', ClasscategoryController::class);
    Route::get('/classcategoryid/{classcategoryid}', [ClasscategoryController::class, 'deleteclasscategory'])->name('classcategories.deleteclasscategory');
    Route::post('updateclasscategoryid', [ClasscategoryController::class, 'updateclasscategory'])->name('classcategories.updateclasscategory');

    Route::resource('subjectoperation', SubjectOperationController::class);
    Route::resource('parent', ParentController::class);
    Route::resource('studentImageUpload', StudentImageUploadController::class);
    Route::resource('myclass', MyClassController::class);
    Route::resource('mysubject', MySubjectController::class);

    Route::get('term_results', [MyresultroomController::class, 'term'])->name('myresultroom.term');

    Route::resource('myresultroom', MyresultroomController::class);
    Route::resource('studentresults', StudentResultsController::class);
    Route::resource('subjectscoresheet', MyScoreSheetController::class);

    Route::resource('schoolbill', SchoolBillController::class);
    Route::get('/billid/{billid}', [SchoolBillController::class, 'deletebill'])->name('schoolbill.deletebill');
    Route::post('billid', [SchoolBillController::class, 'updatebill'])->name('schoolbill.updateschoolbill');

    Route::resource('schoolbilltermsession', SchoolBillTermSessionController::class);
    Route::get('/schoolbilltermsessionid/{schoolbilltermsessionid}', [SchoolBillTermSessionController::class, 'deleteschoolbilltermsession'])->name('schoolbilltermsession.deleteschoolbilltermsession');
    Route::post('schoolbilltermsessionbid', [SchoolBillTermSessionController::class, 'updateschoolbilltermsession'])->name('schoolbilltermsession.updateschoolbilltermsession');
    Route::get('/schoolbilltermsession/{id}/related', 'App\Http\Controllers\SchoolBillTermSessionController@getRelated')->name('schoolbilltermsession.related');

    Route::resource('schoolpayment', SchoolPaymentController::class);
    Route::get('/termsession/{studentid}', [SchoolPaymentController::class, 'termSession'])->name('schoolpayment.termsession');
    Route::get('termsessionpayments', [SchoolPaymentController::class, 'termSessionPayments'])->name('schoolpayment.termsessionpayments');
    Route::get('/studentinvoice/{studentid}/{schoolclassid}/{termid}/{sessionid}', [SchoolPaymentController::class, 'invoice'])->name('schoolpayment.invoice');
    Route::get('/deletestudentpayment/{paymentid}/', [SchoolPaymentController::class, 'deletestudentpayment'])->name('schoolpayment.deletestudentpayment');

    Route::get('/viewstudent/{id}/{termid}/{sessionid}', [ViewStudentController::class, 'show'])->name('viewstudent');
    Route::get('/subjectscoresheet/{schoolclassid}/{subjectclassid}/{staffid}/{termid}/{sessionid}', [MyScoreSheetController::class, 'subjectscoresheet'])->name('subjectscoresheet');

    Route::resource('subjectoperation', SubjectOperationController::class);
    Route::get('/subjectinfo/{id}/{schid}/{sessid}/{termid}', [SubjectOperationController::class, 'subjectinfo'])->name('subjectoperation.subjectinfo');
    Route::get('/viewresults/{id}/{schoolclassid}/{sessid}/{termid}', [StudentResultsController::class, 'viewresults']);
    Route::get('/studentpersonalityprofile/{id}/{schoolclassid}/{sessid}/{termid}', [StudentpersonalityprofileController::class, 'studentpersonalityprofile'])->name('myclass.studentpersonalityprofile');
    Route::post('save', [StudentpersonalityprofileController::class, 'save'])->name('save');
    Route::get('export', [MyScoreSheetController::class, 'export']);
    Route::post('classsetting', [MyScoreSheetController::class, 'importsheet'])->name('import.post');
    Route::post('importsheet', [MyScoreSheetController::class, 'importsheet'])->name('import.post.sheet');
    Route::get('/importform/{schoolclassid}/{subjectclassid}/{staffid}/{termid}/{sessionid}', [MyScoreSheetController::class, 'importform']);

    Route::get('image-upload', [StaffImageUploadController::class, 'imageUpload'])->name('image.upload');
    Route::post('image-upload', [StaffImageUploadController::class, 'imageUploadPost'])->name('image.upload.post');

    Route::resource('exams', ExamController::class);

    Route::resource('questions', QuestionController::class);
    Route::get('/questions/{question}/details', [QuestionController::class, 'showDetails']);
    Route::get('/{question}/details', [QuestionController::class, 'details'])->name('questions.details');
    Route::get('/questions/{question}/edit', [QuestionController::class, 'edit'])->name('questions.edit');

    Route::resource('cbt', CBTController::class);
    Route::get('/cbt/{examid}/takecbt', [CBTController::class, 'takeCBT'])->name('cbt.take');
    Route::post('/cbt/submit', [CBTController::class, 'submit'])->name('cbt.submit');
});