@extends('layouts.master')

@section('content')
<?php
use Spatie\Permission\Models\Role;
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Students</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Student Management</a></li>
                                <li class="breadcrumb-item active">Students</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            <!-- Students by Status Chart -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Students by Status</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="studentsByStatusChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div id="studentList">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-3">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" placeholder="Search by name or admission no">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <div>
                                            <select class="form-control" id="idClass" data-choices data-choices-search-false data-choices-removeItem>
                                                <option value="all">Select Class</option>
                                                @foreach ($schoolclass as $class)
                                                    <option value="{{ $class->id }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <div>
                                            <select class="form-control" id="idStatus" data-choices data-choices-search-false data-choices-removeItem>
                                                <option value="all">Select Status</option>
                                                <option value="1">Old Student</option>
                                                <option value="2">New Student</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xxl-2 col-sm-6">
                                        <div>
                                            <select class="form-control" id="idGender" data-choices data-choices-search-false data-choices-removeItem>
                                                <option value="all">Select Gender</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xxl-1 col-sm-6">
                                        <button type="button" class="btn btn-secondary w-100" onclick="filterData();"><i class="bi bi-funnel align-baseline me-1"></i> Filters</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">Students <span class="badge bg-dark-subtle text-dark ms-1">{{ $data->total() }}</span></h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="d-flex flex-wrap align-items-start gap-2">
                                        @can('student-delete')
                                            <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button>
                                        @endcan
                                        @can('student-create')
                                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addStudentModal"><i class="bi bi-plus-circle align-baseline me-1"></i> Add Student</button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-centered align-middle table-nowrap mb-0" id="studentList">
                                        <thead class="table-active">
                                            <tr>
                                                <th><div class="form-check"><input class="form-check-input" type="checkbox" value="option" id="checkAll"><label class="form-check-label" for="checkAll"></label></div></th>
                                                <th class="sort cursor-pointer" data-sort="name">Student</th>
                                                <th class="sort cursor-pointer" data-sort="admissionNo">Admission No</th>
                                                <th class="sort cursor-pointer" data-sort="class">Class</th>
                                                <th class="sort cursor-pointer" data-sort="status">Status</th>
                                                <th class="sort cursor-pointer" data-sort="gender">Gender</th>
                                                <th class="sort cursor-pointer" data-sort="datereg">Registered</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list form-check-all">
                                            @forelse ($data as $student)
                                                <tr>
                                                    <td class="id" data-id="{{ $student->id }}">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="chk_child">
                                                            <label class="form-check-label"></label>
                                                        </div>
                                                    </td>
                                                    <td class="name" data-name="{{ $student->firstname }} {{ $student->lastname }}">
                                                        <div class="d-flex align-items-center">
                                                            <div class="symbol symbol-50px me-3">
                                                                <img src="{{ $student->picture ? asset('storage/' . $student->picture) : asset('theme/layouts/assets/media/avatars/blank.png') }}" alt="" />
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0"><a href="{{ route('student.show', $student->id) }}" class="text-reset products">{{ $student->firstname }} {{ $student->lastname }}</a></h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="admissionNo" data-admissionNo="{{ $student->admissionNo }}">{{ $student->admissionNo }}</td>
                                                    <td class="class" data-class="{{ $student->schoolclassid }}">{{ $student->schoolclass }} - {{ $student->arm }}</td>
                                                    <td class="status" data-status="{{ $student->statusId }}">{{ $student->statusId == 1 ? 'Old Student' : 'New Student' }}</td>
                                                    <td class="gender" data-gender="{{ $student->gender }}">{{ $student->gender }}</td>
                                                    <td class="datereg">{{ $student->created_at->format('Y-m-d') }}</td>
                                                    <td>
                                                        <ul class="d-flex gap-2 list-unstyled mb-0">
                                                            @can('student-show')
                                                                <li>
                                                                    <a href="{{ route('student.show', $student->id) }}" class="btn btn-subtle-primary btn-icon btn-sm"><i class="ph-eye"></i></a>
                                                                </li>
                                                            @endcan
                                                            @can('student-edit')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn" data-bs-toggle="modal" data-bs-target="#editStudentModal" data-id="{{ $student->id }}"><i class="ph-pencil"></i></a>
                                                                </li>
                                                            @endcan
                                                            @can('student-delete')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn" data-id="{{ $student->id }}"><i class="ph-trash"></i></a>
                                                                </li>
                                                            @endcan
                                                        </ul>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="noresult" style="display: block;">No results found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row mt-3 align-items-center" id="pagination-element">
                                    <div class="col-sm">
                                        <div class="text-muted text-center text-sm-start">
                                            Showing <span class="fw-semibold">{{ $data->count() }}</span> of <span class="fw-semibold">{{ $data->total() }}</span> Results
                                        </div>
                                    </div>
                                    <div class="col-sm-auto mt-3 mt-sm-0">
                                        <div class="pagination-wrap hstack gap-2 justify-content-center">
                                            <a class="page-item pagination-prev {{ $data->onFirstPage() ? 'disabled' : '' }}" href="{{ $data->previousPageUrl() }}">
                                                <i class="mdi mdi-chevron-left align-middle"></i>
                                            </a>
                                            <ul class="pagination listjs-pagination mb-0">
                                                @foreach ($data->links()->elements[0] as $page => $url)
                                                    <li class="page-item {{ $data->currentPage() == $page ? 'active' : '' }}">
                                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <a class="page-item pagination-next {{ $data->hasMorePages() ? '' : 'disabled' }}" href="{{ $data->nextPageUrl() }}">
                                                <i class="mdi mdi-chevron-right align-middle"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Student Modal -->
        <div id="addStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered mw-650px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title">Add Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form class="tablelist-form" id="addStudentForm" enctype="multipart/form-data" autocomplete="off">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="avatar" class="form-label">Avatar</label>
                                <input type="file" id="avatar" name="avatar" class="form-control" accept=".png,.jpg,.jpeg">
                            </div>
                            <div class="mb-3">
                                <label for="admissionNo" class="form-label">Admission No</label>
                                <input type="text" id="admissionNo" name="admissionNo" class="form-control" placeholder="Enter admission number" required>
                            </div>
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <select id="title" name="title" class="form-control" required>
                                    <option value="">Select Title</option>
                                    <option value="Mr">Mr</option>
                                    <option value="Mrs">Mrs</option>
                                    <option value="Miss">Miss</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="firstname" class="form-label">First Name</label>
                                <input type="text" id="firstname" name="firstname" class="form-control" placeholder="Enter first name" required>
                            </div>
                            <div class="mb-3">
                                <label for="lastname" class="form-label">Last Name</label>
                                <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Enter last name" required>
                            </div>
                            <div class="mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select id="gender" name="gender" class="form-control" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="nationality" class="form-label">Nationality</label>
                                <input type="text" id="nationality" name="nationality" class="form-control" placeholder="Enter nationality" required>
                            </div>
                            <div class="mb-3">
                                <label for="state" class="form-label">State</label>
                                <select id="addState" name="state" class="form-control" required>
                                    <option value="">Select State</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="local" class="form-label">Local Government</label>
                                <select id="addLocal" name="local" class="form-control" required>
                                    <option value="">Select Local Government</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="religion" class="form-label">Religion</label>
                                <select id="religion" name="religion" class="form-control" required>
                                    <option value="">Select Religion</option>
                                    <option value="Christianity">Christianity</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="dateofbirth" class="form-label">Date of Birth</label>
                                <input type="date" id="addDOB" name="dateofbirth" class="form-control" required onchange="showage(this.value)">
                                <span id="addAge" class="text-muted"></span>
                            </div>
                            <div class="mb-3">
                                <label for="bloodgroup" class="form-label">Blood Group</label>
                                <select id="bloodgroup" name="bloodgroup" class="form-control">
                                    <option value="">Select Blood Group</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="genotype" class="form-label">Genotype</label>
                                <select id="genotype" name="genotype" class="form-control">
                                    <option value="">Select Genotype</option>
                                    <option value="AA">AA</option>
                                    <option value="AS">AS</option>
                                    <option value="SS">SS</option>
                                    <option value="AC">AC</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="schoolclassid" class="form-label">Class</label>
                                <select id="schoolclassid" name="schoolclassid" class="form-control" required>
                                    <option value="">Select Class</option>
                                    @foreach ($schoolclass as $class)
                                        <option value="{{ $class->id }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="termid" class="form-label">Term</label>
                                <select id="termid" name="termid" class="form-control" required>
                                    <option value="">Select Term</option>
                                    @foreach ($schoolterm as $term)
                                        <option value="{{ $term->id }}">{{ $term->term }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="sessionid" class="form-label">Session</label>
                                <select id="sessionid" name="sessionid" class="form-control" required>
                                    <option value="">Select Session</option>
                                    @foreach ($schoolsession as $session)
                                        <option value="{{ $session->id }}">{{ $session->session }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="statusId" class="form-label">Student Status</label>
                                <select id="statusId" name="statusId" class="form-control" required>
                                    <option value="">Select Status</option>
                                    <option value="1">Old Student</option>
                                    <option value="2">New Student</option>
                                </select>
                            </div>
                            <div class="alert alert-danger d-none" id="alert-error-msg"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="add-btn">Add Student</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Student Modal -->
        <div id="editStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered mw-650px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title">Edit Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form class="tablelist-form" id="editStudentForm" enctype="multipart/form-data" autocomplete="off">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <input type="hidden" id="editStudentId" name="id">
                            <div class="mb-3">
                                <label for="editAvatar" class="form-label">Avatar</label>
                                <input type="file" id="editAvatar" name="avatar" class="form-control" accept=".png,.jpg,.jpeg">
                                <img id="editStudentAvatar" src="" alt="Avatar" style="max-width: 100px; margin-top: 10px;" />
                            </div>
                            <div class="mb-3">
                                <label for="editAdmissionNo" class="form-label">Admission No</label>
                                <input type="text" id="editAdmissionNo" name="admissionNo" class="form-control" placeholder="Enter admission number" required>
                            </div>
                            <div class="mb-3">
                                <label for="editTitle" class="form-label">Title</label>
                                <select id="editTitle" name="title" class="form-control" required>
                                    <option value="">Select Title</option>
                                    <option value="Mr">Mr</option>
                                    <option value="Mrs">Mrs</option>
                                    <option value="Miss">Miss</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editFirstname" class="form-label">First Name</label>
                                <input type="text" id="editFirstname" name="firstname" class="form-control" placeholder="Enter first name" required>
                            </div>
                            <div class="mb-3">
                                <label for="editLastname" class="form-label">Last Name</label>
                                <input type="text" id="editLastname" name="lastname" class="form-control" placeholder="Enter last name" required>
                            </div>
                            <div class="mb-3">
                                <label for="editGender" class="form-label">Gender</label>
                                <select id="editGender" name="gender" class="form-control" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editNationality" class="form-label">Nationality</label>
                                <input type="text" id="editNationality" name="nationality" class="form-control" placeholder="Enter nationality" required>
                            </div>
                            <div class="mb-3">
                                <label for="editState" class="form-label">State</label>
                                <select id="editState" name="state" class="form-control" required>
                                    <option value="">Select State</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editLocal" class="form-label">Local Government</label>
                                <select id="editLocal" name="local" class="form-control" required>
                                    <option value="">Select Local Government</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editReligion" class="form-label">Religion</label>
                                <select id="editReligion" name="religion" class="form-control" required>
                                    <option value="">Select Religion</option>
                                    <option value="Christianity">Christianity</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editDOB" class="form-label">Date of Birth</label>
                                <input type="date" id="editDOB" name="dateofbirth" class="form-control" required onchange="showage(this.value, 'editAge')">
                                <span id="editAge" class="text-muted"></span>
                            </div>
                            <div class="mb-3">
                                <label for="editBloodgroup" class="form-label">Blood Group</label>
                                <select id="editBloodgroup" name="bloodgroup" class="form-control">
                                    <option value="">Select Blood Group</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editGenotype" class="form-label">Genotype</label>
                                <select id="editGenotype" name="genotype" class="form-control">
                                    <option value="">Select Genotype</option>
                                    <option value="AA">AA</option>
                                    <option value="AS">AS</option>
                                    <option value="SS">SS</option>
                                    <option value="AC">AC</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editSchoolclassid" class="form-label">Class</label>
                                <select id="editSchoolclassid" name="schoolclassid" class="form-control" required>
                                    <option value="">Select Class</option>
                                    @foreach ($schoolclass as $class)
                                        <option value="{{ $class->id }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editTermid" class="form-label">Term</label>
                                <select id="editTermid" name="termid" class="form-control" required>
                                    <option value="">Select Term</option>
                                    @foreach ($schoolterm as $term)
                                        <option value="{{ $term->id }}">{{ $term->term }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editSessionid" class="form-label">Session</label>
                                <select id="editSessionid" name="sessionid" class="form-control" required>
                                    <option value="">Select Session</option>
                                    @foreach ($schoolsession as $session)
                                        <option value="{{ $session->id }}">{{ $session->session }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editStatusId" class="form-label">Student Status</label>
                                <select id="editStatusId" name="statusId" class="form-control" required>
                                    <option value="">Select Status</option>
                                    <option value="1">Old Student</option>
                                    <option value="2">New Student</option>
                                </select>
                            </div>
                            <div class="alert alert-danger d-none" id="alert-error-msg"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="update-btn">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Student Modal -->
        <div id="deleteRecordModal" class="modal fade zoomIn" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="btn-close" id="deleteRecord-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-md-5">
                        <div class="text-center">
                            <div class="text-danger">
                                <i class="bi bi-trash display-4"></i>
                            </div>
                            <div class="mt-4">
                                <h3 class="mb-2">Are you sure?</h3>
                                <p class="text-muted fs-lg mx-3 mb-0">Are you sure you want to remove this record?</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                            <button type="button" class="btn w-sm btn-light btn-hover" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn w-sm btn-danger btn-hover" id="delete-record">Yes, Delete It!</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Page-content -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var ctx = document.getElementById("studentsByStatusChart").getContext("2d");
            new Chart(ctx, {
                type: "bar",
                data: {
                    labels: ["Old Student", "New Student"],
                    datasets: [{
                        label: "Students by Status",
                        data: @json(array_values($status_counts)),
                        backgroundColor: ["#4e73df", "#1cc88a"],
                        borderColor: ["#4e73df", "#1cc88a"],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: "Number of Students"
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: "Status"
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: "top"
                        }
                    }
                }
            });
        });

        function showage(dob, targetId = 'addAge') {
            if (!dob) return;
            const birthDate = new Date(dob);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            document.getElementById(targetId).textContent = `Age: ${age} years`;
        }
    </script>
</div>
@endsection
