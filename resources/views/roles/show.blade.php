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
                    <h5 class="text-decoration-underline mb-3 pb-1">View Role Details</h5>
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

         
                <!-- Delete confirmation modal -->
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
    
                <!-- Edit role modal -->
                <div class="modal fade" id="editRoleModalgrid" tabindex="-1" aria-labelledby="exampleModalgridLabel" aria-modal="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalgridLabel">Edit Role</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('roles.update', $role->id) }}" method="POST" class="form" id="kt_modal_update_role_form">
                                    @csrf
                                    @method('PATCH')
                                    <div class="row g-3">
                                        <div class="col-xxl-6">
                                            <label for="name" class="form-label">Role Name</label>
                                            <input type="text" class="form-control" placeholder="Enter a role name" name="name" value="{{ old('name', $role->name) }}" required>
                                        </div>
                                        <div class="col-xxl-6">
                                            <label for="badge" class="form-label">Role Badge</label>
                                            <select name="badge" class="form-control" data-kt-select2="true" data-placeholder="Select option" data-allow-clear="true">
                                                <option></option>
                                                <option value="badge bg-light" {{ $role->badge == 'badge bg-light' ? 'selected' : '' }}>Light grey</option>
                                                <option value="badge bg-dark" {{ $role->badge == 'badge bg-dark' ? 'selected' : '' }}>Dark</option>
                                                <option value="badge bg-primary" {{ $role->badge == 'badge bg-primary' ? 'selected' : '' }}>Blue</option>
                                                <option value="badge bg-secondary" {{ $role->badge == 'badge bg-secondary' ? 'selected' : '' }}>Light blue</option>
                                                <option value="badge bg-success" {{ $role->badge == 'badge bg-success' ? 'selected' : '' }}>Light green</option>
                                                <option value="badge bg-info" {{ $role->badge == 'badge bg-info' ? 'selected' : '' }}>Purple</option>
                                                <option value="badge bg-warning" {{ $role->badge == 'badge bg-warning' ? 'selected' : '' }}>Yellow</option>
                                                <option value="badge bg-danger" {{ $role->badge == 'badge bg-danger' ? 'selected' : '' }}>Red</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="table-responsive">
                                                <table class="table align-middle table-row-dashed fs-6 gy-5">
                                                    <tbody class="text-gray-600 fw-semibold">
                                                        <tr>
                                                            <td class="text-gray-800">
                                                                Administrator Access
                                                                <span class="ms-2" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-content="Allows a full access to the system">
                                                                    <i class="ki-duotone ki-information fs-7"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <label class="form-check form-check-custom form-check-solid me-9">
                                                                    <input class="form-check-input" type="checkbox" value="" id="kt_roles_select_all" />
                                                                    <span class="form-check-label" for="kt_roles_select_all">Select all</span>
                                                                </label>
                                                            </td>
                                                        </tr>
                                                        @foreach (array_unique($perm_title) as $value)
                                                            @php
                                                                $permission = \Spatie\Permission\Models\Permission::where('title', $value)->get();
                                                            @endphp
                                                            <tr>
                                                                <td class="text-gray-800">{{ $value }}</td>
                                                                @foreach ($permission as $v)
                                                                    @php
                                                                        $word = '';
                                                                        if (str_contains($v->name, 'View ')) {
                                                                            $word = 'View';
                                                                        } elseif (str_contains($v->name, 'Create ')) {
                                                                            $word = 'Create';
                                                                        } elseif (str_contains($v->name, 'Update ')) {
                                                                            $word = 'Edit';
                                                                        } elseif (str_contains($v->name, 'Delete ')) {
                                                                            $word = 'Delete';
                                                                        } elseif (str_contains($v->name, 'Update user-role')) {
                                                                            $word = 'Update user role';
                                                                        } elseif (str_contains($v->name, 'Add user-role')) {
                                                                            $word = 'Add user role';
                                                                        } elseif (str_contains($v->name, 'Remove user-role')) {
                                                                            $word = 'Remove user role';
                                                                        }
                                                                    @endphp
                                                                    <td>
                                                                        <div class="d-flex">
                                                                            <div class="form-check form-check-outline form-check-primary mb-3">
                                                                                <input class="form-check-input" type="checkbox" value="{{ $v->id }}" name="permission[]"
                                                                                    {{ $role->hasPermissionTo($v->name) ? 'checked' : '' }}>
                                                                                <label class="form-check-label">{{ $word }}</label>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                @endforeach
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="hstack gap-2 justify-content-end">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Submit</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
        </div> <!-- container-fluid -->
    </div><!-- End Page-content -->
     <!-- JavaScript -->
     <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Role permission checkboxes
            const selectAllCheckbox = document.getElementById("kt_roles_select_all");
            const permissionCheckboxes = document.querySelectorAll('input[name="permission[]"]');

            // Check if all permissions are pre-checked on page load
            const allChecked = Array.from(permissionCheckboxes).every(checkbox => checkbox.checked);
            if (allChecked) {
                selectAllCheckbox.checked = true;
            }

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

            // Initialize List.js for user table
            const userList = new List("userList", {
                valueNames: ["id", "name", "datereg"],
                page: 5,
                pagination: true
            });

            // Update noresult visibility
            userList.on("updated", function (e) {
                document.querySelector(".noresult").style.display = e.matchingItems.length === 0 ? "block" : "none";
                refreshCallbacks();
                ischeckboxcheck();
            });

            // Fetch page for pagination
            function fetchPage(url) {
                if (!url) return;
                axios.get(url, {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                }).then(function (response) {
                    document.querySelector("#userList tbody").innerHTML = response.data.html;
                    document.getElementById("pagination-element").outerHTML = response.data.pagination;
                    userList.reIndex();
                    refreshCallbacks();
                    ischeckboxcheck();
                    document.querySelector("#pagination-element .text-muted").innerHTML =
                        `Showing <span class="fw-semibold">${response.data.count}</span> of <span class="fw-semibold">${response.data.total}</span> Results`;
                }).catch(function (error) {
                    Swal.fire({
                        icon: "error",
                        title: "Error loading page",
                        text: error.response?.data?.message || "An error occurred"
                    });
                });
            }

            // Handle pagination clicks
            document.addEventListener("click", function (e) {
                const paginationLink = e.target.closest(".pagination-prev, .pagination-next, .pagination .page-link");
                if (paginationLink) {
                    e.preventDefault();
                    fetchPage(paginationLink.getAttribute("data-url"));
                }
            });

            // Handle Select All checkbox for users
            const checkAll = document.getElementById("checkAll");
            if (checkAll) {
                checkAll.addEventListener("click", function () {
                    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
                    checkboxes.forEach((checkbox) => {
                        checkbox.checked = this.checked;
                        const row = checkbox.closest("tr");
                        row.classList.toggle("table-active", this.checked);
                    });
                    document.getElementById("remove-actions").classList.toggle("d-none", !this.checked);
                });
            }

            // Handle individual user checkbox changes
            function ischeckboxcheck() {
                const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
                checkboxes.forEach((checkbox) => {
                    checkbox.removeEventListener("change", handleCheckboxChange);
                    checkbox.addEventListener("change", handleCheckboxChange);
                });
            }

            function handleCheckboxChange(e) {
                const row = e.target.closest("tr");
                row.classList.toggle("table-active", e.target.checked);
                const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
                document.getElementById("remove-actions").classList.toggle("d-none", checkedCount === 0);
                document.getElementById("checkAll").checked = checkedCount === document.querySelectorAll('tbody input[name="chk_child"]').length;
            }

            // Handle remove user role
            function refreshCallbacks() {
                const removeButtons = document.getElementsByClassName("remove-item-btn");
                Array.from(removeButtons).forEach((btn) => {
                    btn.removeEventListener("click", handleRemoveClick);
                    btn.addEventListener("click", handleRemoveClick);
                });
            }

            function handleRemoveClick(e) {
                e.preventDefault();
                const deleteUrl = e.target.closest("a").getAttribute("data-url");
                const rowId = e.target.closest("tr").getAttribute("data-id");
                const deleteButton = document.getElementById("delete-record");
                deleteButton.dataset.deleteUrl = deleteUrl;
                deleteButton.dataset.rowId = rowId;
                deleteButton.removeEventListener("click", handleDeleteConfirm);
                deleteButton.addEventListener("click", handleDeleteConfirm, { once: true });
            }

            function handleDeleteConfirm(e) {
                const deleteUrl = e.target.dataset.deleteUrl;
                const rowId = e.target.dataset.rowId;
                const modal = bootstrap.Modal.getInstance(document.getElementById("deleteRecordModal"));

                if (!deleteUrl || !rowId) {
                    Swal.fire({ icon: "error", title: "Configuration error", text: "Delete URL or row ID is missing" });
                    if (modal) modal.hide();
                    return;
                }

                axios.delete(deleteUrl, {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                }).then(function (response) {
                    userList.remove("id", rowId);
                    document.querySelector(`tr[data-id="${rowId}"]`)?.remove();
                    if (modal) modal.hide();
                    Swal.fire({
                        icon: "success",
                        title: response.data.message || "User role removed successfully!",
                        showConfirmButton: false,
                        timer: 2000
                    });
                    document.querySelector(".noresult").style.display = userList.items.length === 0 ? "block" : "none";
                    if (userList.items.length === 0 && document.querySelector("#pagination-element .pagination-prev")) {
                        fetchPage(document.querySelector("#pagination-element .pagination-prev").getAttribute("data-url"));
                    }
                }).catch(function (error) {
                    Swal.fire({
                        icon: "error",
                        title: "Error removing user role",
                        text: error.response?.data?.message || "An error occurred"
                    });
                    if (modal) modal.hide();
                });
            }
        });
    </script>

</div>
@endsection