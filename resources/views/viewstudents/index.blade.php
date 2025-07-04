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
                                    <h5 class="card-title mb-0">Students <span class="badge bg-dark-subtle text-dark ms-1">{{ $allstudents->count() }}</span></h5>
                                    <p class="text-muted mb-0">Class: {{ $schoolclass[0]->schoolclass }} {{ $schoolclass[0]->arm }} | Term: {{ $term[0]->term }} | Session: {{ $session[0]->session }}</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="d-flex flex-wrap align-items-start gap-2">
                                        <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button>
                                        <a href="{{ route('myclass.index') }}" class="btn btn-light">Back</a>
                                        @can('Create student')
                                            <button class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#showModal">Add Student</button>
                                        @endcan
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
                                                                        <img src="{{ asset('storage/student_avatars/' . ($student->picture ? basename($student->picture) : 'unnamed.jpg')) }}"
                                                                             alt="{{ $student->firstname }} {{ $student->lastname }}"
                                                                             class="rounded avatar-sm student-image"
                                                                             data-bs-toggle="modal"
                                                                             data-bs-target="#imageViewModal"
                                                                             data-image="{{ asset('storage/student_avatars/' . ($student->picture ? basename($student->picture) : 'unnamed.jpg')) }}"
                                                                             data-picture="{{ $student->picture ?? 'none' }}"
                                                                             onerror="handleImageError(this, '{{ $student->admissionno }}', '{{ $student->picture ?? 'none' }}')" />
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
                                            Showing <span class="fw-semibold" id="list-count">0</span> of <span class="fw-semibold">{{ $allstudents->count() }}</span> Results
                                        </div>
                                    </div>
                                    <div class="col-sm-auto mt-3 mt-sm-0">
                                        <ul class="pagination" id="studentListPagination"></ul>
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

        <!-- Image View Modal -->
        <div id="imageViewModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Student Picture</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img id="enlargedImage" src="" alt="Student Picture" class="img-fluid" style="max-height: 400px;" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Page-content -->

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // List.js configuration
        var perPage = 5,
            editlist = false,
            checkAll = document.getElementById("checkAll"),
            options = {
                valueNames: ["id", "sn", "admissionno", "name", "gender"],
                page: perPage,
                pagination: {
                    innerWindow: 2,
                    outerWindow: 1,
                    left: 0,
                    right: 0,
                    item: '<li class="page-item"><a class="page-link" href="#"></a></li>'
                }
            },
            studentList = new List("studentList", options);

        console.log("Initial studentList items:", studentList.items.length);

        studentList.on("updated", function (e) {
            console.log("List.js updated, matching items:", e.matchingItems.length, "total items:", studentList.items.length);
            document.getElementsByClassName("noresult")[0].style.display = e.matchingItems.length === 0 ? "block" : "none";
            document.getElementById("list-count").innerText = e.matchingItems.length;
            setTimeout(() => {
                refreshCallbacks();
                ischeckboxcheck();
            }, 100);
        });

        // Handle image loading errors
        function handleImageError(img, admissionno, picture) {
            img.src = '/storage/student_avatars/unnamed.jpg';
            img.dataset.picture = 'none';
            console.log(`Image failed to load for admissionno: ${admissionno}, picture: ${picture}, attempted URL: ${img.src}`);
        }

        document.addEventListener("DOMContentLoaded", function () {
            // Initialize Chart.js for Students by Gender
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

            // Force reload images to catch errors
            const images = document.querySelectorAll('.student-image');
            images.forEach(img => {
                const src = img.src;
                img.src = ''; // Reset to trigger reload
                img.src = src;
                console.log(`Forcing image load: ${src}`);
            });

            // Handle image view modal
            const imageViewModal = document.getElementById('imageViewModal');
            if (imageViewModal) {
                imageViewModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const imageSrc = button.getAttribute('data-image') || '/storage/student_avatars/unnamed.jpg';
                    const pictureName = button.getAttribute('data-picture') || 'none';
                    const modalImage = this.querySelector('#enlargedImage');
                    console.log(`ImageViewModal: Setting image src=${imageSrc}, picture=${pictureName}`);
                    modalImage.src = imageSrc;
                    modalImage.onerror = () => {
                        modalImage.src = '/storage/student_avatars/unnamed.jpg';
                        console.log(`Enlarged image failed to load, picture: ${pictureName}, attempted URL: ${imageSrc}`);
                    };
                });
            } else {
                console.warn('imageViewModal not found in DOM');
            }

            // Initialize Choices.js
            if (typeof Choices !== 'undefined') {
                var genderFilterVal = new Choices(document.getElementById("idGender"), { searchEnabled: true });
                var admissionNoFilterVal = new Choices(document.getElementById("idAdmissionNo"), { searchEnabled: true });
            } else {
                console.warn("Choices.js not available, falling back to native select");
            }
        });

        // Checkbox handling
        if (checkAll) {
            checkAll.onclick = function () {
                console.log("checkAll clicked");
                var checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
                console.log("checkAll clicked, checkboxes found:", checkboxes.length);
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = this.checked;
                    const row = checkbox.closest("tr");
                    if (checkbox.checked) {
                        row.classList.add("table-active");
                    } else {
                        row.classList.remove("table-active");
                    }
                });
                const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
                document.getElementById("remove-actions").classList.toggle("d-none", checkedCount === 0);
            };
        }

        // Form field references
        var addIdField = document.getElementById("add-id-field"),
            addAdmissionNoField = document.getElementById("admissionno"),
            addFirstNameField = document.getElementById("firstname"),
            addLastNameField = document.getElementById("lastname"),
            addOtherNameField = document.getElementById("othername"),
            addGenderField = document.getElementById("gender"),
            editIdField = document.getElementById("edit-id-field"),
            editAdmissionNoField = document.getElementById("edit-admissionno"),
            editFirstNameField = document.getElementById("edit-firstname"),
            editLastNameField = document.getElementById("edit-lastname"),
            editOtherNameField = document.getElementById("edit-othername"),
            editGenderField = document.getElementById("edit-gender");

        function ensureAxios() {
            if (typeof axios === 'undefined') {
                console.error("Axios is not defined. Please include Axios library.");
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Configuration error",
                    text: "Axios library is missing",
                    showConfirmButton: true
                });
                return false;
            }
            return true;
        }

        function ischeckboxcheck() {
            const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
            checkboxes.forEach((checkbox) => {
                checkbox.removeEventListener("change", handleCheckboxChange);
                checkbox.addEventListener("change", handleCheckboxChange);
            });
        }

        function handleCheckboxChange(e) {
            const row = e.target.closest("tr");
            if (e.target.checked) {
                row.classList.add("table-active");
            } else {
                row.classList.remove("table-active");
            }
            const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
            document.getElementById("remove-actions").classList.toggle("d-none", checkedCount === 0);
            const allCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]');
            document.getElementById("checkAll").checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCount;
        }

        function refreshCallbacks() {
            console.log("refreshCallbacks executed at", new Date().toISOString());
            var removeButtons = document.getElementsByClassName("remove-item-btn");
            var editButtons = document.getElementsByClassName("edit-item-btn");
            console.log("Attaching event listeners to", removeButtons.length, "remove buttons and", editButtons.length, "edit buttons");

            Array.from(removeButtons).forEach(function (btn) {
                btn.removeEventListener("click", handleRemoveClick);
                btn.addEventListener("click", handleRemoveClick);
            });

            Array.from(editButtons).forEach(function (btn) {
                btn.removeEventListener("click", handleEditClick);
                btn.addEventListener("click", handleEditClick);
            });
        }

        function handleRemoveClick(e) {
            e.preventDefault();
            try {
                var itemId = e.target.closest("tr").querySelector(".id").getAttribute("data-id");
                console.log("Remove button clicked for ID:", itemId);
                document.getElementById("delete-record").addEventListener("click", function () {
                    if (!ensureAxios()) return;
                    axios.delete(`/students/${itemId}`, {
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    }).then(function () {
                        console.log("Deleted student ID:", itemId);
                        studentList.remove("id", itemId);
                        Swal.fire({
                            position: "center",
                            icon: "success",
                            title: "Student deleted successfully!",
                            showConfirmButton: false,
                            timer: 2000,
                            showCloseButton: true
                        });
                    }).catch(function (error) {
                        console.error("Error deleting student:", error);
                        Swal.fire({
                            position: "center",
                            icon: "error",
                            title: "Error deleting student",
                            text: error.response?.data?.message || "An error occurred",
                            showConfirmButton: true
                        });
                    });
                }, { once: true });
                var modal = new bootstrap.Modal(document.getElementById("deleteRecordModal"));
                modal.show();
            } catch (error) {
                console.error("Error in remove-item-btn click:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Failed to initiate delete",
                    showConfirmButton: true
                });
            }
        }

        function handleEditClick(e) {
            e.preventDefault();
            try {
                var itemId = e.target.closest("tr").querySelector(".id").getAttribute("data-id");
                var tr = e.target.closest("tr");
                console.log("Edit button clicked for ID:", itemId);
                editlist = true;
                editIdField.value = itemId;
                editAdmissionNoField.value = tr.querySelector(".admissionno").innerText;
                var nameParts = tr.querySelector(".name a").innerText.trim().split(" ");
                editFirstNameField.value = nameParts[0];
                editLastNameField.value = nameParts[1] || "";
                editOtherNameField.value = nameParts[2] || "";
                editGenderField.value = tr.querySelector(".gender").innerText;
                var modal = new bootstrap.Modal(document.getElementById("editModal"));
                modal.show();
            } catch (error) {
                console.error("Error in edit-item-btn click:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Failed to populate edit modal",
                    showConfirmButton: true
                });
            }
        }

        function clearAddFields() {
            addIdField.value = "";
            addAdmissionNoField.value = "";
            addFirstNameField.value = "";
            addLastNameField.value = "";
            addOtherNameField.value = "";
            addGenderField.value = "Male";
        }

        function clearEditFields() {
            editIdField.value = "";
            editAdmissionNoField.value = "";
            editFirstNameField.value = "";
            editLastNameField.value = "";
            editOtherNameField.value = "";
            editGenderField.value = "Male";
        }

        function deleteMultiple() {
            const ids_array = [];
            const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
            checkboxes.forEach((checkbox) => {
                if (checkbox.checked) {
                    const id = checkbox.closest("tr").querySelector(".id").getAttribute("data-id");
                    ids_array.push(id);
                }
            });
            if (ids_array.length > 0) {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn btn-primary w-xs me-2 mt-2",
                    cancelButtonClass: "btn btn-danger w-xs mt-2",
                    confirmButtonText: "Yes, delete it!",
                    buttonsStyling: false,
                    showCloseButton: true
                }).then((result) => {
                    if (result.value) {
                        if (!ensureAxios()) return;
                        Promise.all(ids_array.map((id) => {
                            return axios.delete(`/students/${id}`, {
                                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                            });
                        })).then(() => {
                            ids_array.forEach(id => studentList.remove("id", id));
                            Swal.fire({
                                title: "Deleted!",
                                text: "Your data has been deleted.",
                                icon: "success",
                                confirmButtonClass: "btn btn-info w-xs mt-2",
                                buttonsStyling: false
                            });
                        }).catch((error) => {
                            console.error("Error deleting students:", error);
                            Swal.fire({
                                title: "Error!",
                                text: error.response?.data?.message || "Failed to delete students",
                                icon: "error",
                                confirmButtonClass: "btn btn-info w-xs mt-2",
                                buttonsStyling: false
                            });
                        });
                    }
                });
            } else {
                Swal.fire({
                    title: "Please select at least one checkbox",
                    confirmButtonClass: "btn btn-info",
                    buttonsStyling: false,
                    showCloseButton: true
                });
            }
        }

        function filterData() {
            var searchInput = document.querySelector(".search-box input.search").value.toLowerCase();
            var genderSelect = document.getElementById("idGender");
            var admissionNoSelect = document.getElementById("idAdmissionNo");
            var selectedGender = typeof Choices !== 'undefined' && genderFilterVal ? genderFilterVal.getValue(true) : genderSelect.value;
            var selectedAdmissionNo = typeof Choices !== 'undefined' && admissionNoFilterVal ? admissionNoFilterVal.getValue(true) : admissionNoSelect.value;

            console.log("Filtering with:", { search: searchInput, gender: selectedGender, admissionNo: selectedAdmissionNo });

            studentList.filter(function (item) {
                var nameMatch = item.values().name.toLowerCase().includes(searchInput);
                var admissionNoMatch = item.values().admissionno.toLowerCase().includes(searchInput);
                var genderMatch = selectedGender === "all" || item.values().gender === selectedGender;
                var admissionNoSelectMatch = selectedAdmissionNo === "all" || item.values().admissionno === selectedAdmissionNo;

                return (nameMatch || admissionNoMatch) && genderMatch && admissionNoSelectMatch;
            });
        }

        document.getElementById("add-student-form").addEventListener("submit", function (e) {
            e.preventDefault();
            var errorMsg = document.getElementById("add-alert-error-msg");
            errorMsg.classList.remove("d-none");
            setTimeout(() => errorMsg.classList.add("d-none"), 2000);

            if (addAdmissionNoField.value === "") {
                errorMsg.innerHTML = "Please enter an admission number";
                return false;
            }
            if (addFirstNameField.value === "") {
                errorMsg.innerHTML = "Please enter a first name";
                return false;
            }
            if (addLastNameField.value === "") {
                errorMsg.innerHTML = "Please enter a last name";
                return false;
            }
            if (addGenderField.value === "") {
                errorMsg.innerHTML = "Please select a gender";
                return false;
            }

            if (!ensureAxios()) return;

            axios.post('/students', {
                admissionno: addAdmissionNoField.value,
                firstname: addFirstNameField.value,
                lastname: addLastNameField.value,
                othername: addOtherNameField.value,
                gender: addGenderField.value,
                schoolclassid: document.querySelector('input[name="schoolclassid"]').value,
                termid: document.querySelector('input[name="termid"]').value,
                sessionid: document.querySelector('input[name="sessionid"]').value,
                _token: document.querySelector('meta[name="csrf-token"]').content
            }).then(function (response) {
                const student = response.data.student;
                studentList.add({
                    id: student.id,
                    sn: studentList.items.length + 1,
                    admissionno: student.admissionno,
                    name: `${student.firstname} ${student.lastname} ${student.othername || ''}`.trim(),
                    gender: student.gender
                });
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Student added successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
                bootstrap.Modal.getInstance(document.getElementById("showModal")).hide();
            }).catch(function (error) {
                console.error("Error adding student:", error);
                var message = error.response?.data?.message || "Error adding student";
                if (error.response?.status === 422) {
                    message = Object.values(error.response.data.errors || {}).flat().join(", ");
                }
                errorMsg.innerHTML = message;
            });
        });

        document.getElementById("edit-student-form").addEventListener("submit", function (e) {
            e.preventDefault();
            var errorMsg = document.getElementById("edit-alert-error-msg");
            errorMsg.classList.remove("d-none");
            setTimeout(() => errorMsg.classList.add("d-none"), 2000);

            if (editAdmissionNoField.value === "") {
                errorMsg.innerHTML = "Please enter an admission number";
                return false;
            }
            if (editFirstNameField.value === "") {
                errorMsg.innerHTML = "Please enter a first name";
                return false;
            }
            if (editLastNameField.value === "") {
                errorMsg.innerHTML = "Please enter a last name";
                return false;
            }
            if (editGenderField.value === "") {
                errorMsg.innerHTML = "Please select a gender";
                return false;
            }

            if (!ensureAxios()) return;

            axios.put(`/students/${editIdField.value}`, {
                admissionno: editAdmissionNoField.value,
                firstname: editFirstNameField.value,
                lastname: editLastNameField.value,
                othername: editOtherNameField.value,
                gender: editGenderField.value,
                schoolclassid: document.querySelector('input[name="schoolclassid"]').value,
                termid: document.querySelector('input[name="termid"]').value,
                sessionid: document.querySelector('input[name="sessionid"]').value,
                _token: document.querySelector('meta[name="csrf-token"]').content
            }).then(function (response) {
                const student = response.data.student;
                studentList.updateItem("id", student.id, {
                    id: student.id,
                    admissionno: student.admissionno,
                    name: `${student.firstname} ${student.lastname} ${student.othername || ''}`.trim(),
                    gender: student.gender
                });
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Student updated successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
                bootstrap.Modal.getInstance(document.getElementById("editModal")).hide();
            }).catch(function (error) {
                console.error("Error updating student:", error);
                var message = error.response?.data?.message || "Error updating student";
                if (error.response?.status === 422) {
                    message = Object.values(error.response.data.errors || {}).flat().join(", ");
                }
                errorMsg.innerHTML = message;
            });
        });

        document.getElementById("showModal").addEventListener("show.bs.modal", function (e) {
            if (e.relatedTarget.classList.contains("add-btn")) {
                console.log("Opening showModal for adding student...");
                document.getElementById("addModalLabel").innerHTML = "Add Student";
                document.getElementById("add-btn").innerHTML = "Add Student";
            }
        });

        document.getElementById("editModal").addEventListener("show.bs.modal", function () {
            console.log("Opening editModal...");
            document.getElementById("editModalLabel").innerHTML = "Edit Student";
            document.getElementById("update-btn").innerHTML = "Update";
        });

        document.getElementById("showModal").addEventListener("hidden.bs.modal", function () {
            console.log("showModal closed, clearing fields...");
            clearAddFields();
        });

        document.getElementById("editModal").addEventListener("hidden.bs.modal", function () {
            console.log("editModal closed, clearing fields...");
            clearEditFields();
        });
    </script>
</div>
@endsection
