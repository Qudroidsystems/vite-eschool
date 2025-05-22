@extends('layouts.master')
@section('content')
<?php
use Spatie\Permission\Models\Role;
use App\Models\User;
use Spatie\Permission\Models\Permission;
?>

<div class="main-content">
   
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Role Management</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Role Management</a></li>
                                <li class="breadcrumb-item active">Role Details</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end page title -->
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
        
        @if (\Session::has('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ \Session::get('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        @if (\Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ \Session::get('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center g-2">
                        <div class="col-lg-3 me-auto">
                            {{-- <h6 class="card-title mb-0">role<span class="badge bg-primary ms-1 align-baseline">1452</span></h6> --}}
                        </div><!--end col-->
                       
                        <div class="col-lg-auto">
                            <div class="hstack gap-2">
                                <a href="{{ route('roles.index') }}"    data-bs-target="#addRoleModalgrid" class="btn btn-secondary"><< Back</a>
                               
                            </div>
                        </div><!--end col-->
                    </div>
                </div>
            </div><!--end card-->

            <div class="row">
                <div class="col-12">
                    <h5 class="text-decoration-underline mb-3 pb-1">View Role Detais</h5>
                </div>
            </div>
            <!-- end row-->

            <div class="row">
                <div class="col-xl-3 col-lg-6">
                    <div class="card" id="networks">
                        <div class="card-header d-flex">
                            <h5 class="card-title mb-0 flex-grow-1" {{ $role->badge }}>{{ $role->name }}</h5>
                           
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    
                                    <tbody class="list">
                                        @foreach ( $rolePermissions as $rm )
                                                <tr>
                                                    <td>
                                                    
                                                            ---
                                                      
                                                    </td>
                                                    <td class="click text-center">
                                                        {{$rm->name}}
                                                    </td>
                                                
                                                </tr>
                                        @endforeach
                                        
                                          
                                                <button type="button" class="btn btn-light btn-active-primary" data-bs-toggle="modal" data-bs-target="#editRoleModalgrid">Edit Role</button>
                                          
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div><!--end col-->
                <div class="col-xxl-8 col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4 class="card-title mb-0 flex-grow-1">Users Assigned : ({{ $userRoleCount }})</h4>
                            <div class="flex-shrink-0">
                                <div class="nav nav-pills gap-1" id="popularProperty" role="tablist" aria-orientation="vertical">
                                    @can('Update user-role')
                                    <a href="{{ route('roles.adduser',$role->id) }}" class="btn btn-light btn-sm btn-active-success my-1" >Add User </a>
                                    @endcan
                                  
                                </div>
                            </div>
                        </div><!--end header-->
                        <div class="card-body">
                            <div class="tab-content" id="popularPropertyContent">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-centered align-middle table-nowrap mb-0" id="userList">
                                            <thead class="table-active">
                                                <tr>
                                                    <th>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="option" id="checkAll">
                                                            <label class="form-check-label" for="checkAll"></label>
                                                        </div>
                                                    </th>
                                                    <th class="sort cursor-pointer" data-sort="name">User</th>
                                                    <th class="sort cursor-pointer" data-sort="datereg">Joined Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody class="list form-check-all">
                                                @forelse ($usersWithRole as $user)
                                                    <tr data-id="{{ $user->id }}">
                                                        <td class="id" data-id="{{ $user->id }}">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="chk_child">
                                                                <label class="form-check-label"></label>
                                                            </div>
                                                        </td>
                                                        <td class="name">
                                                            <div class="d-flex align-items-center">
                                                                <div>
                                                                    <h6 class="mb-0">
                                                                        <a href="{{ route('users.show', $user->id) }}" class="text-reset products">{{ $user->username }}</a>
                                                                    </h6>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="datereg">{{ $user->created_at->format('Y-m-d') }}</td>
                                                        <td>
                                                            <ul class="d-flex gap-2 list-unstyled mb-0">
                                                                @can('Remove user-role')
                                                                    <li>
                                                                        <a class="dropdown-item remove-item-btn" href="javascript:void(0);" 
                                                                           data-bs-toggle="modal" 
                                                                           data-bs-target="#deleteRecordModal"
                                                                           data-url="{{ route('roles.removeuserrole', ['userid' => $user->id, 'roleid' => $user->roleid]) }}">
                                                                            <i class="bi bi-trash3 me-1 align-baseline"></i> Remove User
                                                                        </a>
                                                                    </li>
                                                                @endcan
                                                            </ul>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="noresult" style="display: block;">No results found</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row mt-3 align-items-center" id="pagination-element">
                                        <div class="col-sm">
                                            <div class="text-muted text-center text-sm-start">
                                                Showing <span class="fw-semibold">{{ $usersWithRole->count() }}</span> of <span class="fw-semibold">{{ $usersWithRole->total() }}</span> Results
                                            </div>
                                        </div>
                                        <div class="col-sm-auto mt-3 mt-sm-0">
                                            <div class="pagination-wrap hstack gap-2 justify-content-center">
                                                <a class="page-item pagination-prev {{ $usersWithRole->onFirstPage() ? 'disabled' : '' }}" href="javascript:void(0);" data-url="{{ $usersWithRole->previousPageUrl() }}">
                                                    <i class="mdi mdi-chevron-left align-middle"></i>
                                                </a>
                                                <ul class="pagination listjs-pagination mb-0">
                                                    @foreach ($usersWithRole->links()->elements[0] as $page => $url)
                                                        <li class="page-item {{ $usersWithRole->currentPage() == $page ? 'active' : '' }}">
                                                            <a class="page-link" href="javascript:void(0);" data-url="{{ $url }}">{{ $page }}</a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                <a class="page-item pagination-next {{ $usersWithRole->hasMorePages() ? '' : 'disabled' }}" href="javascript:void(0);" data-url="{{ $usersWithRole->nextPageUrl() }}">
                                                    <i class="mdi mdi-chevron-right align-middle"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                   
                                   
                                </div>
                              
                            </div>
                        </div><!--end card-->
                    </div><!--end card-->
                </div><!--end col-->
               
            </div><!--end col-->

         
             <!-- Grids in modals -->
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
        </div> <!-- container-fluid -->
    </div><!-- End Page-content -->
    <script>

        document.addEventListener("DOMContentLoaded", function () {
            const selectAllCheckbox = document.getElementById("kt_roles_select_all");
            const permissionCheckboxes = document.querySelectorAll('input[name="permission[]"]');

            // Handle Select All checkbox change
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener("change", function () {
                    console.log("Select All toggled, state:", this.checked);
                    permissionCheckboxes.forEach((checkbox) => {
                        checkbox.checked = this.checked;
                    });
                });
            }

            // Handle individual permission checkbox changes
            permissionCheckboxes.forEach((checkbox) => {
                checkbox.addEventListener("change", function () {
                    console.log("Permission checkbox toggled, value:", this.value, "state:", this.checked);
                    // No update to selectAllCheckbox state
                });
            });
        });

    </script>

    <script>
    var perPage = 5,
    editlist = false,
    checkAll = document.getElementById("checkAll"),
    options = {
        valueNames: ["id", "name", "datereg"],
        page: perPage,
        pagination: true
    },
    userList = new List("userList", options);

console.log("Initial userList items:", userList.items.length);

function fetchPage(url) {
    if (!url) return;
    axios.get(url, {
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    }).then(function (response) {
        var tbody = document.querySelector("#userList tbody");
        tbody.innerHTML = response.data.html;
        var paginationElement = document.getElementById("pagination-element");
        paginationElement.outerHTML = response.data.pagination;
        userList = new List("userList", options);
        refreshCallbacks();
        ischeckboxcheck();
        document.querySelector("#pagination-element .text-muted").innerHTML =
            `Showing <span class="fw-semibold">${response.data.count}</span> of <span class="fw-semibold">${response.data.total}</span> Results`;
        console.log("Fetched page, updated userList with", userList.items.length, "items");
    }).catch(function (error) {
        console.error("Error fetching page:", error);
        Swal.fire({
            position: "center",
            icon: "error",
            title: "Error loading page",
            text: error.response?.data?.message || "An error occurred",
            showConfirmButton: true
        });
    });
}

document.addEventListener("click", function (e) {
    if (e.target.closest(".pagination-prev, .pagination-next, .pagination .page-link")) {
        e.preventDefault();
        var url = e.target.closest("a").getAttribute("data-url");
        console.log("Pagination clicked, fetching URL:", url);
        fetchPage(url);
    }
});

userList.on("updated", function (e) {
    console.log("List.js updated, matching items:", e.matchingItems.length, "total items:", userList.items.length);
    document.querySelector(".noresult").style.display = e.matchingItems.length === 0 ? "block" : "none";
    setTimeout(() => {
        refreshCallbacks();
        ischeckboxcheck();
    }, 100);
});

document.addEventListener("DOMContentLoaded", function () {
    console.log("DOM loaded, initializing List.js...");
    console.log("Initial userList items:", userList.items.length);
    refreshCallbacks();
    ischeckboxcheck();
});

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

var addIdField = document.getElementById("add-id-field"),
    addNameField = document.getElementById("name"),
    addEmailField = document.getElementById("email"),
    addRoleField = document.getElementById("role"),
    addPasswordField = document.getElementById("password"),
    addPasswordConfirmField = document.getElementById("password_confirmation"),
    editIdField = document.getElementById("edit-id-field"),
    editNameField = document.getElementById("edit-name"),
    editEmailField = document.getElementById("edit-email"),
    editRoleField = document.getElementById("edit-role"),
    editPasswordField = document.getElementById("edit-password"),
    editPasswordConfirmField = document.getElementById("edit-password_confirmation");

var addRoleVal = typeof Choices !== 'undefined' ? new Choices(addRoleField, { searchEnabled: true, removeItemButton: true }) : null;
var editRoleVal = typeof Choices !== 'undefined' ? new Choices(editRoleField, { searchEnabled: true, removeItemButton: true }) : null;
var roleFilterVal = typeof Choices !== 'undefined' ? new Choices(document.getElementById("idRole"), { searchEnabled: true }) : null;
var emailFilterVal = typeof Choices !== 'undefined' ? new Choices(document.getElementById("idEmail"), { searchEnabled: true }) : null;

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
        var deleteUrl = e.target.closest("a").getAttribute("data-url");
        var deleteButton = document.getElementById("delete-record");
        var row = e.target.closest("tr");
        console.log("Remove button clicked for URL:", deleteUrl);

        // Store delete info for the modal's confirm button
        deleteButton.dataset.deleteUrl = deleteUrl;
        deleteButton.dataset.rowId = row.getAttribute("data-id");

        // Remove any existing listeners to avoid duplicates
        deleteButton.removeEventListener("click", handleDeleteConfirm);
        deleteButton.addEventListener("click", handleDeleteConfirm, { once: true });
    } catch (error) {
        console.error("Error in handleRemoveClick:", error);
    }
}

function handleDeleteConfirm(e) {
    var deleteUrl = e.target.dataset.deleteUrl;
    var rowId = e.target.dataset.rowId;
    var modal = bootstrap.Modal.getInstance(document.getElementById("deleteRecordModal"));

    if (!deleteUrl || !rowId) {
        console.error("Delete URL or row ID missing");
        Swal.fire({
            position: "center",
            icon: "error",
            title: "Configuration error",
            text: "Delete URL or row ID is missing",
            showConfirmButton: true
        });
        if (modal) modal.hide();
        return;
    }

    if (typeof axios === 'undefined') {
        console.error("Axios is not defined. Please include Axios library.");
        Swal.fire({
            position: "center",
            icon: "error",
            title: "Configuration error",
            text: "Axios library is missing",
            showConfirmButton: true
        });
        if (modal) modal.hide();
        return;
    }

    axios.delete(deleteUrl, {
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    }).then(function (response) {
        console.log("Deleted user role via:", deleteUrl, "Response:", response.data);
        var row = document.querySelector(`tr[data-id="${rowId}"]`);
        if (row) {
            userList.remove("id", rowId);
            row.remove();
        }
        if (modal) modal.hide();
        Swal.fire({
            position: "center",
            icon: "success",
            title: response.data.message || "User role removed successfully!",
            showConfirmButton: false,
            timer: 2000,
            showCloseButton: true
        });
        // Update noresult visibility
        document.querySelector(".noresult").style.display = userList.items.length === 0 ? "block" : "none";
        // Fetch previous page if current page is empty
        if (userList.items.length === 0 && document.querySelector("#pagination-element .pagination-prev")) {
            var prevUrl = document.querySelector("#pagination-element .pagination-prev").getAttribute("data-url");
            console.log("Current page empty, fetching previous page:", prevUrl);
            fetchPage(prevUrl);
        }
    }).catch(function (error) {
        console.error("Error deleting user role:", error);
        Swal.fire({
            position: "center",
            icon: "error",
            title: "Error removing user role",
            text: error.response?.data?.message || "An error occurred",
            showConfirmButton: true
        });
        if (modal) modal.hide();
    });
}

function handleEditClick(e) {
    e.preventDefault();
    try {
        var itemId = e.target.closest("tr").querySelector(".id").getAttribute("data-id");
        console.log("Edit button clicked for ID:", itemId);
        var tr = e.target.closest("tr");
        editlist = true;
        editIdField.value = itemId;
        editNameField.value = tr.querySelector(".name a").innerText;
        console.log("Opening editModal...");
        var modal = new bootstrap.Modal(document.getElementById("editModal"));
        modal.show();
    } catch (error) {
        console.error("Error in edit-item-btn click:", error);
    }
}

function clearAddFields() {
    addIdField.value = "";
    addNameField.value = "";
    addEmailField.value = "";
    addPasswordField.value = "";
    addPasswordConfirmField.value = "";
    if (addRoleVal) {
        addRoleVal.setChoiceByValue([]);
    } else {
        Array.from(addRoleField.options).forEach(option => option.selected = false);
    }
}

function clearEditFields() {
    editIdField.value = "";
    editNameField.value = "";
    editEmailField.value = "";
    editPasswordField.value = "";
    editPasswordConfirmField.value = "";
    if (editRoleVal) {
        editRoleVal.setChoiceByValue([]);
    } else {
        Array.from(editRoleField.options).forEach(option => option.selected = false);
    }
}

function deleteMultiple() {
    const ids_array = [];
    const urls_array = [];
    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
    checkboxes.forEach((checkbox) => {
        if (checkbox.checked) {
            const row = checkbox.closest("tr");
            const id = row.getAttribute("data-id");
            const url = row.querySelector(".remove-item-btn").getAttribute("data-url");
            ids_array.push(id);
            urls_array.push(url);
        }
    });
    if (urls_array.length > 0) {
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
                if (typeof axios === 'undefined') {
                    console.error("Axios is not defined. Please include Axios library.");
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Configuration error",
                        text: "Axios library is missing",
                        showConfirmButton: true
                    });
                    return;
                }
                Promise.all(urls_array.map((url) => {
                    return axios.delete(url, {
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    });
                })).then(() => {
                    urls_array.forEach((url, index) => {
                        userList.remove("id", ids_array[index]);
                        document.querySelector(`tr[data-id="${ids_array[index]}"]`)?.remove();
                    });
                    Swal.fire({
                        title: "Deleted!",
                        text: "User roles have been removed.",
                        icon: "success",
                        confirmButtonClass: "btn btn-info w-xs mt-2",
                        buttonsStyling: false
                    });
                    document.querySelector(".noresult").style.display = userList.items.length === 0 ? "block" : "none";
                    if (userList.items.length === 0 && document.querySelector("#pagination-element .pagination-prev")) {
                        fetchPage(document.querySelector("#pagination-element .pagination-prev").getAttribute("data-url"));
                    }
                }).catch((error) => {
                    console.error("Error deleting user roles:", error);
                    Swal.fire({
                        title: "Error!",
                        text: error.response?.data?.message || "Failed to delete user roles",
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
    var searchInput = document.querySelector(".search-box input.search")?.value.toLowerCase() || "";
    var roleSelect = document.getElementById("idRole");
    var emailSelect = document.getElementById("idEmail");
    var selectedRole = roleFilterVal ? roleFilterVal.getValue(true) : (roleSelect?.value || "all");
    var selectedEmail = emailFilterVal ? emailFilterVal.getValue(true) : (emailSelect?.value || "all");

    console.log("Filtering with:", { search: searchInput, role: selectedRole, email: selectedEmail });

    var url = new URL(window.location.href);
    url.searchParams.set("search", searchInput);
    url.searchParams.set("role", selectedRole);
    url.searchParams.set("email", selectedEmail);
    fetchPage(url.toString());
}

document.getElementById("add-user-form").addEventListener("submit", function (e) {
    e.preventDefault();
    var errorMsg = document.getElementById("alert-error-msg");
    errorMsg.classList.remove("d-none");
    setTimeout(() => errorMsg.classList.add("d-none"), 2000);

    if (addNameField.value === "") {
        errorMsg.innerHTML = "Please enter a name";
        return false;
    }
    if (addEmailField.value === "") {
        errorMsg.innerHTML = "Please enter an email";
        return false;
    }
    if (!addRoleField.selectedOptions.length) {
        errorMsg.innerHTML = "Please select at least one role";
        return false;
    }
    if (addPasswordField.value === "") {
        errorMsg.innerHTML = "Please enter a password";
        return false;
    }
    if (addPasswordField.value !== addPasswordConfirmField.value) {
        errorMsg.innerHTML = "Passwords do not match";
        return false;
    }

    if (typeof axios === 'undefined') {
        console.error("Axios is not defined. Please include Axios library.");
        errorMsg.innerHTML = "Configuration error: Axios library is missing";
        return false;
    }

    var roles = Array.from(addRoleField.selectedOptions).map(option => option.value);
    axios.post('/users', {
        name: addNameField.value,
        email: addEmailField.value,
        roles: roles,
        password: addPasswordField.value,
        password_confirmation: addPasswordConfirmField.value,
        _token: document.querySelector('meta[name="csrf-token"]').content
    }).then(function (response) {
        fetchPage(window.location.href);
        Swal.fire({
            position: "center",
            icon: "success",
            title: "User added successfully!",
            showConfirmButton: false,
            timer: 2000,
            showCloseButton: true
        });
    }).catch(function (error) {
        console.error("Error adding user:", error);
        errorMsg.innerHTML = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat().join(", ") || "Error adding user";
    });
});

document.getElementById("edit-user-form").addEventListener("submit", function (e) {
    e.preventDefault();
    var errorMsg = document.getElementById("alert-error-msg");
    errorMsg.classList.remove("d-none");
    setTimeout(() => errorMsg.classList.add("d-none"), 2000);

    if (editNameField.value === "") {
        errorMsg.innerHTML = "Please enter a name";
        return false;
    }
    if (editEmailField.value === "") {
        errorMsg.innerHTML = "Please enter an email";
        return false;
    }
    if (!editRoleField.selectedOptions.length) {
        errorMsg.innerHTML = "Please select at least one role";
        return false;
    }
    if (editPasswordField.value !== "" && editPasswordField.value !== editPasswordConfirmField.value) {
        errorMsg.innerHTML = "Passwords do not match";
        return false;
    }

    if (typeof axios === 'undefined') {
        console.error("Axios is not defined. Please include Axios library.");
        errorMsg.innerHTML = "Configuration error: Axios library is missing";
        return false;
    }

    var roles = Array.from(editRoleField.selectedOptions).map(option => option.value);
    axios.put(`/users/${editIdField.value}`, {
        name: editNameField.value,
        email: editEmailField.value,
        roles: roles,
        password: editPasswordField.value || undefined,
        password_confirmation: editPasswordConfirmField.value || undefined,
        _token: document.querySelector('meta[name="csrf-token"]').content
    }).then(function (response) {
        fetchPage(window.location.href);
        Swal.fire({
            position: "center",
            icon: "success",
            title: "User updated successfully!",
            showConfirmButton: false,
            timer: 2000,
            showCloseButton: true
        });
    }).catch(function (error) {
        console.error("Error updating user:", error);
        errorMsg.innerHTML = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat().join(", ") || "Error updating user";
    });
});

document.getElementById("showModal").addEventListener("show.bs.modal", function (e) {
    if (e.relatedTarget.classList.contains("add-btn")) {
        console.log("Opening showModal for adding user...");
        document.getElementById("exampleModalLabel").innerHTML = "Add User";
        document.getElementById("add-btn").innerHTML = "Add User";
    }
});

document.getElementById("editModal").addEventListener("show.bs.modal", function (e) {
    console.log("Opening editModal...");
    document.getElementById("exampleModalLabel").innerHTML = "Edit User";
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