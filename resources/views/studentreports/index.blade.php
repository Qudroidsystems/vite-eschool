@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ $pagetitle }}</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item active">Student Reports</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

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

            @if (session('status') || session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') ?? session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div id="studentList">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-4 col-sm-6">
                                        <select class="form-control" id="idclass" name="schoolclassid">
                                            <option value="ALL">Select Class</option>
                                            @foreach ($schoolclasses as $class)
                                                <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-4 col-sm-6">
                                        <select class="form-control" id="idsession" name="sessionid">
                                            <option value="ALL">Select Session</option>
                                            @foreach ($schoolsessions as $session)
                                                <option value="{{ $session->id }}">{{ $session->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-2 col-sm-6">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" id="searchInput" name="search" placeholder="Search students...">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                    <div class="col-xxl-2 col-sm-6">
                                        <button type="button" class="btn btn-secondary w-100" onclick="filterData()"><i class="bi bi-search align-baseline me-1"></i> Search</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Students <span class="badge bg-dark-subtle text-dark ms-1" id="studentcount">{{ $allstudents ? $allstudents->total() : 0 }}</span></h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-centered align-middle table-nowrap mb-0" id="studentListTable">
                                        <thead class="table-active">
                                            <tr>
                                                <th><div class="form-check"><input class="form-check-input" type="checkbox" id="checkAll"><label class="form-check-label" for="checkAll"></label></div></th>
                                                <th>Admission No</th>
                                                <th>First Name</th>
                                                <th>Last Name</th>
                                                <th>Other Name</th>
                                                <th>Gender</th>
                                                <th>Picture</th>
                                                <th>Class</th>
                                                <th>Arm</th>
                                                <th>Session</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="studentTableBody">
                                            @include('studentreports.partials.student_rows')
                                        </tbody>
                                    </table>
                                    <div class="d-flex justify-content-end mt-3" id="pagination-container">
                                        {{ $allstudents ? $allstudents->links('pagination::bootstrap-5') : '' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image View Modal -->
                <div id="imageViewModal" class="modal fade" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Student Image</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img id="enlargedImage" src="" alt="Student Image" class="img-fluid" onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'; console.log('Enlarged image failed to load');">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    console.log("Script loaded at", new Date().toISOString());

    function filterData() {
        console.log("filterData called");
        if (typeof axios === 'undefined') {
            console.error("Axios is not defined");
            Swal.fire({
                icon: "error",
                title: "Configuration Error",
                text: "Axios library is missing.",
                showConfirmButton: true
            });
            return;
        }

        const classSelect = document.getElementById("idclass");
        const sessionSelect = document.getElementById("idsession");
        const searchInput = document.getElementById("searchInput");

        if (!classSelect || !sessionSelect) {
            console.error("Class or session select elements not found");
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Required filter elements not found.",
                showConfirmButton: true
            });
            return;
        }

        const classValue = classSelect.value;
        const sessionValue = sessionSelect.value;
        const searchValue = searchInput ? searchInput.value.trim() : '';

        if (classValue === 'ALL' || sessionValue === 'ALL') {
            document.getElementById('studentTableBody').innerHTML = '<tr><td colspan="11" class="text-center">Select class and session to view students.</td></tr>';
            document.getElementById('pagination-container').innerHTML = '';
            document.getElementById('studentcount').innerText = '0';
            Swal.fire({
                icon: "warning",
                title: "Missing Selection",
                text: "Please select a valid class and session.",
                showConfirmButton: true
            });
            return;
        }

        console.log("Sending AJAX request with:", { search: searchValue, schoolclassid: classValue, sessionid: sessionValue });

        const tableBody = document.getElementById('studentTableBody');
        tableBody.innerHTML = '<tr><td colspan="11" class="text-center">Loading...</td></tr>';

        axios.get('{{ route("studentreports.index") }}', {
            params: {
                search: searchValue,
                schoolclassid: classValue,
                sessionid: sessionValue
            },
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (response) {
            console.log("AJAX response received:", response.data);

            // Update table body
            document.getElementById('studentTableBody').innerHTML = response.data.tableBody || '<tr><td colspan="11" class="text-center">No students found.</td></tr>';

            // Update pagination
            document.getElementById('pagination-container').innerHTML = response.data.pagination || '';

            // Update student count
            document.getElementById('studentcount').innerText = response.data.studentCount || '0';

            setupPaginationLinks();

            if (response.data.tableBody.includes('No students found') || response.data.tableBody.includes('Select class and session')) {
                Swal.fire({
                    icon: "info",
                    title: "No Results",
                    text: "No students found for the selected class and session.",
                    showConfirmButton: true
                });
            }
        }).catch(function (error) {
            console.error("AJAX error:", error);
            tableBody.innerHTML = '<tr><td colspan="11" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
            Swal.fire({
                icon: "error",
                title: "Error",
                text: error.response?.data?.message || "Failed to fetch student data.",
                showConfirmButton: true
            });
        });
    }

    function setupPaginationLinks() {
        const paginationLinks = document.querySelectorAll('#pagination-container a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.href;
                if (url && !this.classList.contains('disabled')) {
                    loadPage(url);
                }
            });
        });
    }

    function loadPage(url) {
        console.log("Loading page:", url);
        const tableBody = document.getElementById('studentTableBody');
        tableBody.innerHTML = '<tr><td colspan="11" class="text-center">Loading...</td></tr>';

        axios.get(url, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (response) {
            console.log("Page load response:", response.data);

            // Update table body
            document.getElementById('studentTableBody').innerHTML = response.data.tableBody || '<tr><td colspan="11" class="text-center">No students found.</td></tr>';

            // Update pagination
            document.getElementById('pagination-container').innerHTML = response.data.pagination || '';

            // Update student count
            document.getElementById('studentcount').innerText = response.data.studentCount || '0';

            setupPaginationLinks();
        }).catch(function (error) {
            console.error("Page load error:", error);
            tableBody.innerHTML = '<tr><td colspan="11" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
            Swal.fire({
                icon: "error",
                title: "Error",
                text: error.response?.data?.message || "Failed to fetch student data.",
                showConfirmButton: true
            });
        });
    }

    // Initialize checkboxes and image modal
    document.addEventListener("DOMContentLoaded", function () {
        console.log("DOM loaded");

        // Checkbox handling
        const checkAll = document.getElementById("checkAll");
        if (checkAll) {
            checkAll.addEventListener("click", function () {
                const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = this.checked;
                    const row = checkbox.closest("tr");
                    row.classList.toggle("table-active", this.checked);
                });
            });
        }

        // Individual checkbox handling
        document.querySelectorAll('tbody input[name="chk_child"]').forEach(checkbox => {
            checkbox.addEventListener("change", function () {
                const row = this.closest("tr");
                row.classList.toggle("table-active", this.checked);
                const allCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]');
                const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
                document.getElementById("checkAll").checked = allCheckboxes.length === checkedCount && allCheckboxes.length > 0;
            });
        });

        // Image modal
        const modal = document.getElementById('imageViewModal');
        if (modal) {
            modal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const imageSrc = button.getAttribute('data-image');
                const modalImage = modal.querySelector('#enlargedImage');
                modalImage.src = imageSrc || '{{ asset('storage/student_avatars/unnamed.jpg') }}';
            });
        }
    });
</script>
@endsection