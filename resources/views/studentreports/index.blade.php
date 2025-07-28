@extends('layouts.master')

@section('content')
<style>
    #alertContainer {
        position: fixed;
        top: 10px;
        left: 50%;
        transform: translateX(-50%);
        width: 80%;
        max-width: 600px;
        z-index: 1050;
    }
    #alertContainer .alert {
        margin-bottom: 0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }
    /* Select2 styling to match Bootstrap 5 form-control */
    .select2-container {
        width: 100% !important;
    }
    .select2-container--default .select2-selection--single {
        height: calc(1.5em + 0.75rem + 2px); /* Bootstrap form-control height */
        padding: 0.375rem 2.25rem 0.375rem 0.75rem; /* Match padding, space for arrow */
        font-size: 1rem; /* Bootstrap font size */
        font-weight: 400; /* Match form-control */
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .select2-container--default .select2-selection--single:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #495057;
        line-height: 1.5;
        padding-left: 0;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + 0.75rem);
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #495057 transparent transparent transparent;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #6c757d; /* Bootstrap placeholder color */
    }
    /* Dropdown menu */
    .select2-container--default .select2-dropdown {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        background-color: #fff;
        margin-top: 0.25rem;
    }
    .select2-container--default .select2-results__option {
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        color: #495057;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #0d6efd;
        color: #fff;
    }
    /* Search input in dropdown */
    .select2-container--default .select2-search--dropdown .select2-search__field {
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        width: 100%;
        box-sizing: border-box;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    /* Ensure buttons align to the right */
    .button-group {
        display: flex;
        gap: 0.5rem;
    }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
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

            <div id="alertContainer" aria-live="polite"></div>

            <div id="studentList">
                <div class="row g-3 align-items-end">
                    <div class="col-xxl-3 col-sm-6">
                        <select class="form-control" id="idclass" name="schoolclassid">
                            <option value="ALL">Select Class</option>
                            @foreach ($schoolclasses as $class)
                                <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xxl-3 col-sm-6">
                        <select class="form-control" id="idsession" name="sessionid">
                            <option value="ALL">Select Session</option>
                            @foreach ($schoolsessions as $session)
                                <option value="{{ $session->id }}">{{ $session->session }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xxl-3 col-sm-6">
                        <select class="form-control" id="idterm" name="termid">
                            <option value="ALL">Select Term</option>
                            <option value="1">First Term</option>
                            <option value="2">Second Term</option>
                            <option value="3">Third Term</option>
                        </select>
                    </div>
                    <div class="col-xxl-3 col-sm-6">
                        <div class="search-box">
                            <input type="text" class="form-control search" id="searchInput" name="search" placeholder="Search students...">
                            <i class="ri-search-line search-icon"></i>
                        </div>
                    </div>
                    <div class="col-auto ms-auto button-group">
                        <button type="button" class="btn btn-secondary" onclick="filterData()"><i class="bi bi-search align-baseline me-1"></i> Search</button>
                        <button type="button" class="btn btn-primary" id="printAllBtn" style="display: none;" onclick="printAllResults()"><i class="bi bi-printer align-baseline me-1"></i> Print Selected Results</button>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script>
    console.log("Script loaded at", new Date().toISOString());

    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    function updateSelectionAlert() {
        const classSelect = document.getElementById("idclass");
        const sessionSelect = document.getElementById("idsession");
        const termSelect = document.getElementById("idterm");
        const checkedCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]:checked');

        const classText = classSelect && classSelect.value !== 'ALL' ? classSelect.options[classSelect.selectedIndex].text : 'None';
        const sessionText = sessionSelect && sessionSelect.value !== 'ALL' ? sessionSelect.options[sessionSelect.selectedIndex].text : 'None';
        const termText = termSelect && termSelect.value !== 'ALL' ? termSelect.options[termSelect.selectedIndex].text : 'None';
        const studentCount = checkedCheckboxes.length;

        const messages = [];
        if (classText !== 'None') messages.push(`Class: ${classText}`);
        if (sessionText !== 'None') messages.push(`Session: ${sessionText}`);
        if (termText !== 'None') messages.push(`Term: ${termText}`);
        if (studentCount > 0) messages.push(`Selected: ${studentCount} student${studentCount === 1 ? '' : 's'}`);

        const alertContainer = document.getElementById('alertContainer');
        const alertId = 'alert-selection';

        if (messages.length === 0) {
            const existingAlert = document.getElementById(alertId);
            if (existingAlert) existingAlert.remove();
            return;
        }

        const alertMessage = messages.join(' | ');
        const alertHtml = `
            <div id="${alertId}" class="alert alert-info alert-dismissible fade show" role="alert">
                ${alertMessage}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        const existingAlert = document.getElementById(alertId);
        if (existingAlert) existingAlert.remove();

        alertContainer.innerHTML = alertHtml;

        setTimeout(() => {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.classList.remove('show');
                alert.classList.add('fade');
                setTimeout(() => alert.remove(), 150);
            }
        }, 10000);
    }

    function updatePrintButtonVisibility() {
        const printAllBtn = document.getElementById("printAllBtn");
        const checkedCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]:checked');
        printAllBtn.style.display = checkedCheckboxes.length > 0 ? 'block' : 'none';
        updateSelectionAlert();
    }

    function filterData() {
        console.log("filterData called at", new Date().toISOString());
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
        const termSelect = document.getElementById("idterm");
        const searchInput = document.getElementById("searchInput");

        if (!classSelect || !sessionSelect || !termSelect || !searchInput) {
            console.error("Required elements not found:", { classSelect, sessionSelect, termSelect, searchInput });
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
        const termValue = termSelect.value;
        const searchValue = searchInput.value.trim();

        console.log("Sending AJAX request with:", { search: searchValue, schoolclassid: classValue, sessionid: sessionValue, termid: termValue });

        if (classValue === 'ALL' || sessionValue === 'ALL' || termValue === 'ALL') {
            document.getElementById('studentTableBody').innerHTML = '<tr><td colspan="10" class="text-center">Select class, session, and term to view students.</td></tr>';
            document.getElementById('pagination-container').innerHTML = '';
            document.getElementById('studentcount').innerText = '0';
            document.getElementById('printAllBtn').style.display = 'none';
            document.getElementById('alertContainer').innerHTML = '';
            Swal.fire({
                icon: "warning",
                title: "Missing Selection",
                text: "Please select a valid class, session, and term.",
                showConfirmButton: true
            });
            return;
        }

        const tableBody = document.getElementById('studentTableBody');
        tableBody.innerHTML = '<tr><td colspan="10" class="text-center">Loading...</td></tr>';

        axios.get('{{ route("studentreports.index") }}', {
            params: {
                search: searchValue,
                schoolclassid: classValue,
                sessionid: sessionValue,
                termid: termValue
            },
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (response) {
            console.log("AJAX response received:", response.data);
            if (!response.data || typeof response.data !== 'object') {
                throw new Error("Invalid response format");
            }

            document.getElementById('studentTableBody').innerHTML = response.data.tableBody || '<tr><td colspan="10" class="text-center">No students found.</td></tr>';
            document.getElementById('pagination-container').innerHTML = response.data.pagination || '';
            document.getElementById('studentcount').innerText = response.data.studentCount || '0';

            setupPaginationLinks();
            setupCheckboxListeners();
            setupDropdownListeners();
            updatePrintButtonVisibility();
            updateSelectionAlert();

            if (response.data.tableBody.includes('No students found')) {
                Swal.fire({
                    icon: "info",
                    title: "No Results",
                    text: "No students found for the selected filters.",
                    showConfirmButton: true
                });
            }
        }).catch(function (error) {
            console.error("AJAX error:", error);
            tableBody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Error loading data: ' + (error.message || 'Unknown error') + '</td></tr>';
            Swal.fire({
                icon: "error",
                title: "Error",
                text: error.response?.data?.message || "Failed to fetch student data. Check console for details.",
                showConfirmButton: true
            });
        });
    }

    const debouncedFilterData = debounce(filterData, 500);

    function printAllResults() {
        const classSelect = document.getElementById("idclass");
        const sessionSelect = document.getElementById("idsession");
        const termSelect = document.getElementById("idterm");
        const classValue = classSelect.value;
        const sessionValue = sessionSelect.value;
        const termValue = termSelect.value;

        const checkedCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]:checked');
        const selectedStudentIds = Array.from(checkedCheckboxes).map(checkbox => checkbox.value);

        console.log('Preparing to generate PDF with params:', { schoolclassid: classValue, sessionid: sessionValue, termid: termValue, studentIds: selectedStudentIds });

        if (classValue === 'ALL' || sessionValue === 'ALL' || termValue === 'ALL') {
            Swal.fire({
                icon: "warning",
                title: "Missing Selection",
                text: "Please select a valid class, session, and term.",
                showConfirmButton: true
            });
            return;
        }

        if (selectedStudentIds.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "No Students Selected",
                text: "Please select at least one student to generate the PDF.",
                showConfirmButton: true
            });
            return;
        }

        const classText = classSelect.options[classSelect.selectedIndex].text;
        const sessionText = sessionSelect.options[sessionSelect.selectedIndex].text;
        const termText = termSelect.options[termSelect.selectedIndex].text;
        const studentCount = selectedStudentIds.length;

        Swal.fire({
            title: 'Confirm Print',
            html: `
                <p>You are about to print results for:</p>
                <ul style="text-align: left;">
                    <li><strong>Class:</strong> ${classText}</li>
                    <li><strong>Session:</strong> ${sessionText}</li>
                    <li><strong>Term:</strong> ${termText}</li>
                    <li><strong>Selected:</strong> ${studentCount} student${studentCount === 1 ? '' : 's'}</li>
                </ul>
                <p>Do you want to proceed?</p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Confirm',
            cancelButtonText: 'Cancel',
            buttonsStyling: true,
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-secondary'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Generating PDF...',
                    text: 'Please wait while the PDF is being generated.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                axios.post('{{ route("studentreports.exportClassResultsPdf") }}', {
                    schoolclassid: classValue,
                    sessionid: sessionValue,
                    termid: termValue,
                    studentIds: selectedStudentIds,
                    response_method: 'base64'
                }, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    responseType: 'json'
                }).then(function (response) {
                    console.log("PDF response:", response.data);
                    Swal.close();
                    if (response.data.success && response.data.pdf_base64) {
                        const byteCharacters = atob(response.data.pdf_base64);
                        const byteNumbers = new Array(byteCharacters.length);
                        for (let i = 0; i < byteCharacters.length; i++) {
                            byteNumbers[i] = byteCharacters.charCodeAt(i);
                        }
                        const byteArray = new Uint8Array(byteNumbers);
                        const blob = new Blob([byteArray], { type: 'application/pdf' });
                        const pdfUrl = URL.createObjectURL(blob);
                        window.open(pdfUrl, '_blank');
                        setTimeout(() => URL.revokeObjectURL(pdfUrl), 30000);
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: response.data.message || "Failed to generate PDF.",
                            showConfirmButton: true
                        });
                    }
                }).catch(function (error) {
                    Swal.close();
                    console.error("PDF generation error:", error);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: error.response?.data?.message || "Failed to generate PDF.",
                        showConfirmButton: true
                    });
                });
            }
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
        tableBody.innerHTML = '<tr><td colspan="10" class="text-center">Loading...</td></tr>';

        axios.get(url, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (response) {
            console.log("Page load response:", response.data);
            if (!response.data || typeof response.data !== 'object') {
                throw new Error("Invalid response format");
            }
            document.getElementById('studentTableBody').innerHTML = response.data.tableBody || '<tr><td colspan="10" class="text-center">No students found.</td></tr>';
            document.getElementById('pagination-container').innerHTML = response.data.pagination || '';
            document.getElementById('studentcount').innerText = response.data.studentCount || '0';
            setupPaginationLinks();
            setupCheckboxListeners();
            setupDropdownListeners();
            updatePrintButtonVisibility();
            updateSelectionAlert();
        }).catch(function (error) {
            console.error("Page load error:", error);
            tableBody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Error loading data: ' + (error.message || 'Unknown error') + '</td></tr>';
            Swal.fire({
                icon: "error",
                title: "Error",
                text: error.response?.data?.message || "Failed to fetch student data.",
                showConfirmButton: true
            });
        });
    }

    function setupCheckboxListeners() {
        const checkAll = document.getElementById("checkAll");
        const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');

        if (checkAll) {
            checkAll.addEventListener("change", function () {
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = this.checked;
                    const row = checkbox.closest("tr");
                    row.classList.toggle("table-active", this.checked);
                });
                updatePrintButtonVisibility();
            });
        }

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener("change", function () {
                const row = this.closest("tr");
                row.classList.toggle("table-active", this.checked);
                const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
                const allCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]').length;
                document.getElementById("checkAll").checked = checkedCount === allCheckboxes && allCheckboxes > 0;
                updatePrintButtonVisibility();
            });
        });
    }

    function setupDropdownListeners() {
        const classSelect = document.getElementById("idclass");
        const sessionSelect = document.getElementById("idsession");
        const termSelect = document.getElementById("idterm");

        if (classSelect) {
            classSelect.addEventListener("change", function() {
                console.log("Native class select changed to:", classSelect.value);
                updateSelectionAlert();
            });
        }
        if (sessionSelect) {
            sessionSelect.addEventListener("change", function() {
                console.log("Native session select changed to:", sessionSelect.value);
                updateSelectionAlert();
            });
        }
        if (termSelect) {
            termSelect.addEventListener("change", function() {
                console.log("Native term select changed to:", termSelect.value);
                updateSelectionAlert();
            });
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        console.log("DOM loaded at", new Date().toISOString());

        if (jQuery && jQuery.fn && jQuery.fn.select2) {
            // Initialize Select2 for all selects
            jQuery('#idclass').select2({
                placeholder: "Select Class",
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: 1
            });
            jQuery('#idsession').select2({
                placeholder: "Select Session",
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: 1
            });
            jQuery('#idterm').select2({
                placeholder: "Select Term",
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: 1
            });

            // Trigger filterData on select changes
            jQuery('#idclass').on('select2:select select2:unselect', function () {
                console.log("Select2 class changed to:", jQuery('#idclass').val());
                updateSelectionAlert();
                filterData();
            });
            jQuery('#idsession').on('select2:select select2:unselect', function () {
                console.log("Select2 session changed to:", jQuery('#idsession').val());
                updateSelectionAlert();
                filterData();
            });
            jQuery('#idterm').on('select2:select select2:unselect', function () {
                console.log("Select2 term changed to:", jQuery('#idterm').val());
                updateSelectionAlert();
                filterData();
            });
        } else {
            console.error("Select2 or jQuery is not defined");
            Swal.fire({
                icon: "error",
                title: "Configuration Error",
                text: "Select2 or jQuery library is missing.",
                showConfirmButton: true
            });
        }

        const searchInput = document.getElementById("searchInput");
        if (searchInput) {
            searchInput.addEventListener("input", function () {
                console.log("Search input changed to:", searchInput.value);
                debouncedFilterData();
            });
        } else {
            console.error("Search input element not found");
        }

        setupCheckboxListeners();
        setupDropdownListeners();
        updateSelectionAlert();

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
