@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">My Classes</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('myclass.index') }}">Class Management</a></li>
                                <li class="breadcrumb-item active">My Classes</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            <!-- Classes by Term Chart -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Classes by Term (Current Session)</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="classesByTermChart" height="100"></canvas>
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

            <div id="classList">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-3 col-sm-6">
                                        <select class="form-control" id="idclass" name="schoolclassid" data-choices data-choices-search-true data-choices-removeItem>
                                            <option value="ALL">Select Class</option>
                                            @foreach ($schoolclasses as $class)
                                                <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <select class="form-control" id="idsession" name="sessionid" data-choices data-choices-search-true data-choices-removeItem>
                                            <option value="ALL">Select Session</option>
                                            @foreach ($schoolsessions as $session)
                                                <option value="{{ $session->id }}">{{ $session->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" id="searchInput" name="search" placeholder="Search classes...">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
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
                            <div class="card-header d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">Classes <span class="badge bg-dark-subtle text-dark ms-1" id="classcount">{{ $myclass ? $myclass->total() : 0 }}</span></h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="d-flex flex-wrap align-items-start gap-2">
                                        <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button>
                                        @can('Create class')
                                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#showModal"><i class="bi bi-plus-circle align-baseline me-1"></i> Add Class Setting</button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-centered align-middle table-nowrap mb-0" id="classListTable">
                                        <thead class="table-active">
                                            <tr>
                                                <th><div class="form-check"><input class="form-check-input" type="checkbox" id="checkAll"><label class="form-check-label" for="checkAll"></label></div></th>
                                                <th class="sort cursor-pointer" data-sort="schoolclass">Class</th>
                                                <th class="sort cursor-pointer" data-sort="schoolarm">Arm</th>
                                                <th class="sort cursor-pointer" data-sort="term">Term</th>
                                                <th class="sort cursor-pointer" data-sort="session">Session</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list form-check-all" id="classTableBody">
                                            @include('myclass.partials.class_rows')
                                        </tbody>
                                    </table>
                                    <div class="d-flex justify-content-end mt-3" id="pagination-container">
                                        {{ $myclass ? $myclass->links('pagination::bootstrap-5') : '' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Class Setting Modal -->
            <div id="showModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="addModalLabel" class="modal-title">Add Class Setting</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="tablelist-form" autocomplete="off" id="add-class-form">
                            <div class="modal-body">
                                <input type="hidden" id="add-id-field" name="id">
                                <input type="hidden" id="staffid" name="staffid" value="{{ auth()->user()->id }}">
                                <div class="mb-3">
                                    <label for="vschoolclassid" class="form-label">Class</label>
                                    <select id="vschoolclassid" name="vschoolclassid" class="form-control" required>
                                        @foreach ($schoolclasses as $class)
                                            <option value="{{ $class->id }}">{{ $class->schoolclass }} ({{ $class->arm }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="termid" class="form-label">Term</label>
                                    <select id="termid" name="termid" class="form-control" required>
                                        @foreach ($terms as $term)
                                            <option value="{{ $term->id }}">{{ $term->term }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="sessionid" class="form-label">Session</label>
                                    <select id="sessionid" name="sessionid" class="form-control" required>
                                        @foreach ($schoolsessions as $session)
                                            <option value="{{ $session->id }}">{{ $session->session }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="noschoolopened" class="form-label">Number of School Days Opened</label>
                                    <input type="number" id="noschoolopened" name="noschoolopened" class="form-control" placeholder="Enter number of school days">
                                </div>
                                <div class="mb-3">
                                    <label for="termends" class="form-label">Term Ends</label>
                                    <input type="date" id="termends" name="termends" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="nexttermbegins" class="form-label">Next Term Begins</label>
                                    <input type="date" id="nexttermbegins" name="nexttermbegins" class="form-control">
                                </div>
                                <div class="alert alert-danger d-none" id="alert-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="add-btn">Add Class Setting</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Class Setting Modal -->
            <div id="editModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="editModalLabel" class="modal-title">Edit Class Setting</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="tablelist-form" autocomplete="off" id="edit-class-form">
                            <div class="modal-body">
                                <input type="hidden" id="edit-id-field" name="id">
                                <input type="hidden" id="edit-staffid" name="staffid" value="{{ auth()->user()->id }}">
                                <div class="mb-3">
                                    <label for="edit-vschoolclassid" class="form-label">Class</label>
                                    <select id="edit-vschoolclassid" name="vschoolclassid" class="form-control" required>
                                        @foreach ($schoolclasses as $class)
                                            <option value="{{ $class->id }}">{{ $class->schoolclass }} ({{ $class->arm }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-termid" class="form-label">Term</label>
                                    <select id="edit-termid" name="termid" class="form-control" required>
                                        @foreach ($terms as $term)
                                            <option value="{{ $term->id }}">{{ $term->term }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-sessionid" class="form-label">Session</label>
                                    <select id="edit-sessionid" name="sessionid" class="form-control" required>
                                        @foreach ($schoolsessions as $session)
                                            <option value="{{ $session->id }}">{{ $session->session }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-noschoolopened" class="form-label">Number of School Days Opened</label>
                                    <input type="number" id="edit-noschoolopened" name="noschoolopened" class="form-control" placeholder="Enter number of school days">
                                </div>
                                <div class="mb-3">
                                    <label for="edit-termends" class="form-label">Term Ends</label>
                                    <input type="date" id="edit-termends" name="termends" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="edit-nexttermbegins" class="form-label">Next Term Begins</label>
                                    <input type="date" id="edit-nexttermbegins" name="nexttermbegins" class="form-control">
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

            <!-- Delete Class Setting Modal -->
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
                                    <p class="text-muted fs-lg mx-3 mb-0">Are you sure you want to remove this class setting?</p>
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

        <!-- Scripts -->
        {{-- <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> --}}

        <script>
            // Chart Initialization
            document.addEventListener("DOMContentLoaded", function () {
                var ctx = document.getElementById("classesByTermChart").getContext("2d");
                new Chart(ctx, {
                    type: "bar",
                    data: {
                        labels: @json(array_keys($term_counts)),
                        datasets: [{
                            label: "Classes by Term",
                            data: @json(array_values($term_counts)),
                            backgroundColor: ["#4e73df", "#1cc88a", "#36b9cc"],
                            borderColor: ["#4e73df", "#1cc88a", "#36b9cc"],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: "Number of Classes"
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: "Terms"
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

            // List.js Initialization
            var perPage = 5,
                editlist = false,
                checkAll = document.getElementById("checkAll"),
                options = {
                    valueNames: ["id", "schoolclass", "schoolarm", "term", "session"],
                    page: perPage,
                    pagination: true
                },
                classList = new List("classList", options);

            console.log("Initial classList items:", classList.items.length);

            classList.on("updated", function (e) {
                console.log("List.js updated, matching items:", e.matchingItems.length, "total items:", classList.items.length);
                document.getElementsByClassName("noresult")[0].style.display = e.matchingItems.length === 0 ? "block" : "none";
                setTimeout(() => {
                    refreshCallbacks();
                    ischeckboxcheck();
                }, 100);
            });

            document.addEventListener("DOMContentLoaded", function () {
                console.log("DOM loaded, initializing List.js...");
                console.log("Initial classList items:", classList.items.length);
                refreshCallbacks();
                ischeckboxcheck();

                // Initialize Choices.js
                if (typeof Choices !== 'undefined') {
                    var classFilterVal = new Choices(document.getElementById("idclass"), { searchEnabled: true });
                    var sessionFilterVal = new Choices(document.getElementById("idsession"), { searchEnabled: true });
                } else {
                    console.warn("Choices.js not available, falling back to native select");
                }
            });

            // Checkbox Handling
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
                        axios.delete(`/myclass/${itemId}`, {
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                        }).then(function () {
                            console.log("Deleted class setting ID:", itemId);
                            window.location.reload();
                            Swal.fire({
                                position: "center",
                                icon: "success",
                                title: "Class setting deleted successfully!",
                                showConfirmButton: false,
                                timer: 2000,
                                showCloseButton: true
                            });
                        }).catch(function (error) {
                            console.error("Error deleting class setting:", error);
                            Swal.fire({
                                position: "center",
                                icon: "error",
                                title: "Error deleting class setting",
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
                    console.log("Edit button clicked for ID:", itemId);
                    axios.get(`/myclass/${itemId}/edit`).then(function (response) {
                        var setting = response.data.setting;
                        editlist = true;
                        editIdField.value = setting.id;
                        editSchoolClassIdField.value = setting.vschoolclassid;
                        editTermIdField.value = setting.termid;
                        editSessionIdField.value = setting.sessionid;
                        editNoSchoolOpenedField.value = setting.noschoolopened || '';
                        editTermEndsField.value = setting.termends || '';
                        editNextTermBeginsField.value = setting.nexttermbegins || '';
                        var modal = new bootstrap.Modal(document.getElementById("editModal"));
                        modal.show();
                    }).catch(function (error) {
                        console.error("Error fetching class setting:", error);
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Failed to load class setting",
                            showConfirmButton: true
                        });
                    });
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

            function clearAddFields() {
                addIdField.value = "";
                addSchoolClassIdField.value = "";
                addTermIdField.value = "";
                addSessionIdField.value = "";
                addNoSchoolOpenedField.value = "";
                addTermEndsField.value = "";
                addNextTermBeginsField.value = "";
            }

            function clearEditFields() {
                editIdField.value = "";
                editSchoolClassIdField.value = "";
                editTermIdField.value = "";
                editSessionIdField.value = "";
                editNoSchoolOpenedField.value = "";
                editTermEndsField.value = "";
                editNextTermBeginsField.value = "";
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
                                return axios.delete(`/myclass/${id}`, {
                                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                                });
                            })).then(() => {
                                window.location.reload();
                                Swal.fire({
                                    title: "Deleted!",
                                    text: "Your data has been deleted.",
                                    icon: "success",
                                    confirmButtonClass: "btn btn-info w-xs mt-2",
                                    buttonsStyling: false
                                });
                            }).catch((error) => {
                                console.error("Error deleting class settings:", error);
                                Swal.fire({
                                    title: "Error!",
                                    text: error.response?.data?.message || "Failed to delete class settings",
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
                console.log("filterData called");
                if (!ensureAxios()) return;

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
                    document.getElementById('classTableBody').innerHTML = '<tr><td colspan="6" class="text-center">Select class and session to view classes.</td></tr>';
                    document.getElementById('pagination-container').innerHTML = '';
                    document.getElementById('classcount').innerText = '0';
                    Swal.fire({
                        icon: "warning",
                        title: "Missing Selection",
                        text: "Please select a valid class and session.",
                        showConfirmButton: true
                    });
                    return;
                }

                console.log("Sending AJAX request with:", { search: searchValue, schoolclassid: classValue, sessionid: sessionValue });

                const tableBody = document.getElementById('classTableBody');
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Loading...</td></tr>';

                axios.get('{{ route("myclass.index") }}', {
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
                    document.getElementById('classTableBody').innerHTML = response.data.tableBody || '<tr><td colspan="6" class="text-center">No classes found.</td></tr>';

                    // Update pagination
                    document.getElementById('pagination-container').innerHTML = response.data.pagination || '';

                    // Update class count
                    document.getElementById('classcount').innerText = response.data.classCount || '0';

                    setupPaginationLinks();

                    if (response.data.tableBody.includes('No classes found') || response.data.tableBody.includes('Select class and session')) {
                        Swal.fire({
                            icon: "info",
                            title: "No Results",
                            text: "No classes found for the selected class and session.",
                            showConfirmButton: true
                        });
                    }
                }).catch(function (error) {
                    console.error("AJAX error:", error);
                    tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: error.response?.data?.message || "Failed to fetch class data.",
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
                const tableBody = document.getElementById('classTableBody');
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Loading...</td></tr>';

                axios.get(url, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(function (response) {
                    console.log("Page load response:", response.data);

                    // Update table body
                    document.getElementById('classTableBody').innerHTML = response.data.tableBody || '<tr><td colspan="6" class="text-center">No classes found.</td></tr>';

                    // Update pagination
                    document.getElementById('pagination-container').innerHTML = response.data.pagination || '';

                    // Update class count
                    document.getElementById('classcount').innerText = response.data.classCount || '0';

                    setupPaginationLinks();
                }).catch(function (error) {
                    console.error("Page load error:", error);
                    tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: error.response?.data?.message || "Failed to fetch class data.",
                        showConfirmButton: true
                    });
                });
            }

            // Form Submission Handlers
            document.getElementById("add-class-form").addEventListener("submit", function (e) {
                e.preventDefault();
                var errorMsg = document.getElementById("alert-error-msg");
                errorMsg.classList.remove("d-none");
                setTimeout(() => errorMsg.classList.add("d-none"), 5000);

                if (addSchoolClassIdField.value === "") {
                    errorMsg.innerHTML = "Please select a class";
                    return false;
                }
                if (addTermIdField.value === "") {
                    errorMsg.innerHTML = "Please select a term";
                    return false;
                }
                if (addSessionIdField.value === "") {
                    errorMsg.innerHTML = "Please select a session";
                    return false;
                }

                if (!ensureAxios()) return;

                axios.post('/myclass', {
                    staffid: document.getElementById("staffid").value,
                    vschoolclassid: addSchoolClassIdField.value,
                    termid: addTermIdField.value,
                    sessionid: addSessionIdField.value,
                    noschoolopened: addNoSchoolOpenedField.value,
                    termends: addTermEndsField.value,
                    nexttermbegins: addNextTermBeginsField.value,
                    _token: document.querySelector('meta[name="csrf-token"]').content
                }).then(function (response) {
                    window.location.reload();
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: "Class setting added successfully!",
                        showConfirmButton: false,
                        timer: 2000,
                        showCloseButton: true
                    });
                }).catch(function (error) {
                    console.error("Error adding class setting:", error);
                    var message = error.response?.data?.message || "Error adding class setting";
                    if (error.response?.status === 422) {
                        message = Object.values(error.response.data.errors || {}).flat().join(", ");
                    }
                    errorMsg.innerHTML = message;
                });
            });

            document.getElementById("edit-class-form").addEventListener("submit", function (e) {
                e.preventDefault();
                var errorMsg = document.getElementById("alert-error-msg");
                errorMsg.classList.remove("d-none");
                setTimeout(() => errorMsg.classList.add("d-none"), 5000);

                if (editSchoolClassIdField.value === "") {
                    errorMsg.innerHTML = "Please select a class";
                    return false;
                }
                if (editTermIdField.value === "") {
                    errorMsg.innerHTML = "Please select a term";
                    return false;
                }
                if (editSessionIdField.value === "") {
                    errorMsg.innerHTML = "Please select a session";
                    return false;
                }

                if (!ensureAxios()) return;

                axios.put(`/myclass/${editIdField.value}`, {
                    staffid: document.getElementById("edit-staffid").value,
                    vschoolclassid: editSchoolClassIdField.value,
                    termid: editTermIdField.value,
                    sessionid: editSessionIdField.value,
                    noschoolopened: editNoSchoolOpenedField.value,
                    termends: editTermEndsField.value,
                    nexttermbegins: editNextTermBeginsField.value,
                    _token: document.querySelector('meta[name="csrf-token"]').content
                }).then(function (response) {
                    window.location.reload();
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: "Class setting updated successfully!",
                        showConfirmButton: false,
                        timer: 2000,
                        showCloseButton: true
                    });
                }).catch(function (error) {
                    console.error("Error updating class setting:", error);
                    var message = error.response?.data?.message || "Error updating class setting";
                    if (error.response?.status === 422) {
                        message = Object.values(error.response.data.errors || {}).flat().join(", ");
                    }
                    errorMsg.innerHTML = message;
                });
            });

            document.getElementById("showModal").addEventListener("show.bs.modal", function (e) {
                if (e.relatedTarget.classList.contains("add-btn")) {
                    console.log("Opening showModal for adding class setting...");
                    document.getElementById("addModalLabel").innerHTML = "Add Class Setting";
                    document.getElementById("add-btn").innerHTML = "Add Class Setting";
                }
            });

            document.getElementById("editModal").addEventListener("show.bs.modal", function () {
                console.log("Opening editModal...");
                document.getElementById("editModalLabel").innerHTML = "Edit Class Setting";
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

            // Form Field References
            var addIdField = document.getElementById("add-id-field"),
                addSchoolClassIdField = document.getElementById("vschoolclassid"),
                addTermIdField = document.getElementById("termid"),
                addSessionIdField = document.getElementById("sessionid"),
                addNoSchoolOpenedField = document.getElementById("noschoolopened"),
                addTermEndsField = document.getElementById("termends"),
                addNextTermBeginsField = document.getElementById("nexttermbegins"),
                editIdField = document.getElementById("edit-id-field"),
                editSchoolClassIdField = document.getElementById("edit-vschoolclassid"),
                editTermIdField = document.getElementById("edit-termid"),
                editSessionIdField = document.getElementById("edit-sessionid"),
                editNoSchoolOpenedField = document.getElementById("edit-noschoolopened"),
                editTermEndsField = document.getElementById("edit-termends"),
                editNextTermBeginsField = document.getElementById("edit-nexttermbegins");
        </script>
    </div>
</div>
@endsection