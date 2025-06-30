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
                        <h4 class="mb-sm-0">My Class Students</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('myclass.index') }}">Class Management</a></li>
                                <li class="breadcrumb-item active">Students</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            <!-- Students by Gender Chart -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Students by Gender</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="studentsByGenderChart" height="100"></canvas>
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
                                            <input type="text" class="form-control search" placeholder="Search students">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <div>
                                            <select class="form-control" id="idGender" data-choices data-choices-search-false data-choices-removeItem>
                                                <option value="all">Select Gender</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <div>
                                            <select class="form-control" id="idAdmissionNo" data-choices data-choices-search-false data-choices-removeItem>
                                                <option value="all">Select Admission No</option>
                                                @foreach ($allstudents as $student)
                                                    <option value="{{ $student->admissionno }}">{{ $student->admissionno }}</option>
                                                @endforeach
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
                                    <h5 class="card-title mb-0">Students <span class="badge bg-dark-subtle text-dark ms-1">{{ $allstudents->total() }}</span></h5>
                                    <p class="text-muted mb-0">Class: {{ $schoolclass[0]->schoolclass }} {{ $schoolclass[0]->arm }} | Term: {{ $term[0]->term }} | Session: {{ $session[0]->session }}</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="d-flex flex-wrap align-items-start gap-2">
                                        <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button>
                                       
                                        <a href="{{ route('myclass.index') }}" class="btn btn-light">Back</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-centered align-middle table-nowrap mb-0" id="studentListTable">
                                        <thead class="table-active">
                                            <tr>
                                                <th><div class="form-check"><input class="form-check-input" type="checkbox" value="option" id="checkAll"><label class="form-check-label" for="checkAll"></label></div></th>
                                                <th class="sort cursor-pointer" data-sort="sn">SN</th>
                                                <th class="sort cursor-pointer" data-sort="admissionno">Admission No</th>
                                                <th class="sort cursor-pointer" data-sort="name">Student Name</th>
                                                <th class="sort cursor-pointer" data-sort="gender">Gender</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list form-check-all">
                                            @forelse ($allstudents as $key => $student)
                                                <tr>
                                                    <td class="id" data-id="{{ $student->stid }}">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="chk_child">
                                                            <label class="form-check-label"></label>
                                                        </div>
                                                    </td>
                                                    <td class="sn">{{ $key + 1 }}</td>
                                                    <td class="admissionno" data-admissionno="{{ $student->admissionno }}">{{ $student->admissionno }}</td>
                                                    <td class="name" data-name="{{ $student->firstname }} {{ $student->lastname }} {{ $student->othername }}">
                                                        <div class="d-flex align-items-center">
                                                            <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                                                <a href="{{ route('myclass.studentpersonalityprofile', [$student->stid, $schoolclassid, $termid, $sessionid]) }}">
                                                                    <div class="symbol-label">
                                                                        <img src="{{ Storage::url('images/studentavatar/' . ($student->picture ?? 'unnamed.png')) }}" alt="{{ $student->firstname }} {{ $student->lastname }}" class="w-100" />
                                                                    </div>
                                                                </a>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0"><a href="{{ route('myclass.studentpersonalityprofile', [$student->stid, $schoolclassid, $termid, $sessionid]) }}" class="text-reset">{{ $student->firstname }} {{ $student->lastname }} {{ $student->othername }}</a></h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="gender" data-gender="{{ $student->gender }}">{{ $student->gender }}</td>
                                                    <td>
                                                        <ul class="d-flex gap-2 list-unstyled mb-0">
                                                            @can('View student')
                                                                <li>
                                                                    <a href="{{ route('myclass.studentpersonalityprofile', [$student->stid, $schoolclassid, $termid, $sessionid]) }}" class="btn btn-subtle-primary btn-icon btn-sm"><i class="ph-eye"></i></a>
                                                                </li>
                                                            @endcan
                                                            @can('Update student')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn"><i class="ph-pencil"></i></a>
                                                                </li>
                                                            @endcan
                                                            @can('Delete student')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn"><i class="ph-trash"></i></a>
                                                                </li>
                                                            @endcan
                                                        </ul>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="noresult" style="display: block;">No results found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row mt-3 align-items-center" id="pagination-element">
                                    <div class="col-sm">
                                        <div class="text-muted text-center text-sm-start">
                                            Showing <span class="fw-semibold">{{ $allstudents->count() }}</span> of <span class="fw-semibold">{{ $allstudents->total() }}</span> Results
                                        </div>
                                    </div>
                                    <div class="col-sm-auto mt-3 mt-sm-0">
                                        {{ $allstudents->appends(['termid' => $termid, 'sessionid' => $sessionid])->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Student Modal -->
        <div id="showModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="addModalLabel" class="modal-title">Add Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form class="tablelist-form" autocomplete="off" id="add-student-form">
                        <div class="modal-body">
                            <input type="hidden" id="add-id-field" name="id">
                            <input type="hidden" name="schoolclassid" value="{{ $schoolclassid }}">
                            <input type="hidden" name="termid" value="{{ $termid }}">
                            <input type="hidden" name="sessionid" value="{{ $sessionid }}">
                            <div class="mb-3">
                                <label for="admissionno" class="form-label">Admission No</label>
                                <input type="text" id="admissionno" name="admissionno" class="form-control" placeholder="Enter admission number" required>
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
                                <label for="othername" class="form-label">Other Name (Optional)</label>
                                <input type="text" id="othername" name="othername" class="form-control" placeholder="Enter other name">
                            </div>
                            <div class="mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select id="gender" name="gender" class="form-control" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="alert alert-danger d-none" id="add-alert-error-msg"></div>
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
        <div id="editModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="editModalLabel" class="modal-title">Edit Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form class="tablelist-form" autocomplete="off" id="edit-student-form">
                        <div class="modal-body">
                            <input type="hidden" id="edit-id-field" name="id">
                            <input type="hidden" name="schoolclassid" value="{{ $schoolclassid }}">
                            <input type="hidden" name="termid" value="{{ $termid }}">
                            <input type="hidden" name="sessionid" value="{{ $sessionid }}">
                            <div class="mb-3">
                                <label for="edit-admissionno" class="form-label">Admission No</label>
                                <input type="text" id="edit-admissionno" name="admissionno" class="form-control" placeholder="Enter admission number" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit-firstname" class="form-label">First Name</label>
                                <input type="text" id="edit-firstname" name="firstname" class="form-control" placeholder="Enter first name" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit-lastname" class="form-label">Last Name</label>
                                <input type="text" id="edit-lastname" name="lastname" class="form-control" placeholder="Enter last name" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit-othername" class="form-label">Other Name (Optional)</label>
                                <input type="text" id="edit-othername" name="othername" class="form-control" placeholder="Enter other name">
                            </div>
                            <div class="mb-3">
                                <label for="edit-gender" class="form-label">Gender</label>
                                <select id="edit-gender" name="gender" class="form-control" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="alert alert-danger d-none" id="edit-alert-error-msg"></div>
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
            var ctx = document.getElementById("studentsByGenderChart").getContext("2d");
            new Chart(ctx, {
                type: "bar",
                data: {
                    labels: ["Male", "Female"],
                    datasets: [{
                        label: "Students by Gender",
                        data: [{{ $male }}, {{ $female }}],
                        backgroundColor: ["#4e73df", "#e74a3b"],
                        borderColor: ["#4e73df", "#e74a3b"],
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
                                text: "Gender"
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
    </script>
</div>
@endsection