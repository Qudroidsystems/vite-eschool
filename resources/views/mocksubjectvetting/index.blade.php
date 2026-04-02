@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Mock Subject Vetting Management</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Academics</a></li>
                                <li class="breadcrumb-item active">Mock Subject Vetting</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-checkbox-circle-line me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('danger'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ri-error-warning-line me-2"></i>
                    {{ session('danger') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <style>
                .stats-card { transition: all 0.3s ease; border: none; border-radius: 1rem; overflow: hidden; cursor: pointer; }
                .stats-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
                .stats-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 24px; }
                .badge-status { padding: 6px 12px; border-radius: 20px; font-weight: 500; font-size: 11px; }
                .badge-pending { background-color: #ffe5e5; color: #dc3545; }
                .badge-completed { background-color: #e3f5ec; color: #28a745; }
                .badge-rejected { background-color: #fff4e5; color: #ffc107; }
                .table-row-pending { background-color: #fff5f5 !important; border-left: 3px solid #dc3545; }
                .table-row-completed { background-color: #f0fff4 !important; border-left: 3px solid #28a745; }
                .table-row-rejected { background-color: #fffbf0 !important; border-left: 3px solid #ffc107; }
                .action-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s ease; }
                .action-btn:hover { transform: scale(1.1); }
                .filter-card { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border: none; border-radius: 1rem; }
                @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
                .animate-fade-in-up { animation: fadeInUp 0.5s ease-out; }
                .selected-subject-item { transition: all 0.2s ease; }
                .selected-subject-item:hover { background-color: #f8f9fa; transform: translateX(5px); }
                .subject-search-item { cursor: pointer; transition: all 0.2s ease; }
                .subject-search-item:hover { background-color: #f8f9fa; }
            </style>

            <div id="mockSubjectVettingList">

                <!-- Filter Row -->
                <div class="row mb-4 animate-fade-in-up">
                    <div class="col-12">
                        <div class="card filter-card">
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold text-muted mb-2">
                                            <i class="ri-calendar-line me-1"></i> Select Term
                                        </label>
                                        <select class="form-select form-select-lg" id="mock-term-filter-stats">
                                            <option value="">All Terms</option>
                                            @foreach ($terms as $term)
                                                <option value="{{ $term->id }}">{{ $term->term }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold text-muted mb-2">
                                            <i class="ri-calendar-event-line me-1"></i> Select Session
                                        </label>
                                        <select class="form-select form-select-lg" id="mock-session-filter-stats">
                                            <option value="">All Sessions</option>
                                            @foreach ($sessions as $session)
                                                <option value="{{ $session->id }}">{{ $session->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button class="btn btn-secondary w-100" id="mock-reset-stats-btn">
                                            <i class="ri-refresh-line me-1"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row g-4 mb-4 animate-fade-in-up" id="mockStatsCardsRow">
                    <div class="col-xl-3 col-md-6">
                        <div class="card stats-card stat-card-clickable" data-status="all">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1 text-uppercase fw-semibold fs-12">Total Assignments</p>
                                        <h2 class="mb-0 fw-bold" id="mock-stat-total">0</h2>
                                    </div>
                                    <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                                        <i class="ri-file-list-line fs-24"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stats-card stat-card-clickable" data-status="pending">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1 text-uppercase fw-semibold fs-12">Pending</p>
                                        <h2 class="mb-0 fw-bold text-danger" id="mock-stat-pending">0</h2>
                                    </div>
                                    <div class="stats-icon bg-danger bg-opacity-10 text-danger">
                                        <i class="ri-timer-line fs-24"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stats-card stat-card-clickable" data-status="completed">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1 text-uppercase fw-semibold fs-12">Completed</p>
                                        <h2 class="mb-0 fw-bold text-success" id="mock-stat-completed">0</h2>
                                    </div>
                                    <div class="stats-icon bg-success bg-opacity-10 text-success">
                                        <i class="ri-checkbox-circle-line fs-24"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stats-card stat-card-clickable" data-status="rejected">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1 text-uppercase fw-semibold fs-12">Rejected</p>
                                        <h2 class="mb-0 fw-bold text-warning" id="mock-stat-rejected">0</h2>
                                    </div>
                                    <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                                        <i class="ri-close-circle-line fs-24"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search & Actions -->
                <div class="row mb-4 animate-fade-in-up">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-6">
                                        <div class="search-box">
                                            <label class="form-label text-muted mb-2 fw-semibold">Search Assignments</label>
                                            <div class="position-relative">
                                                <input type="text" class="form-control search" placeholder="Search by staff, subject, class, teacher...">
                                                <i class="ri-search-line search-icon" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%);"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        @can('Create mock-subject-vettings')
                                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addMockSubjectVettingModal">
                                                <i class="ri-add-line me-1"></i> Create Assignment
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table View -->
                <div class="row animate-fade-in-up">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" id="kt_mock_subject_vetting_table">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="w-10px pe-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="checkAll" />
                                                    </div>
                                                </th>
                                                <th>#</th>
                                                <th>Vetting Staff</th>
                                                <th>Subject</th>
                                                <th>Class</th>
                                                <th>Arm</th>
                                                <th>Teacher</th>
                                                <th>Term</th>
                                                <th>Session</th>
                                                <th>Status</th>
                                                <th>Updated</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list">
                                            @php $i = 0 @endphp
                                            @forelse ($mocksubjectvettings as $sv)
                                                @php
                                                    $statusClass = match ($sv->status ?? 'pending') {
                                                        'completed' => 'badge-completed',
                                                        'pending' => 'badge-pending',
                                                        'rejected' => 'badge-rejected',
                                                        default => 'badge-pending'
                                                    };
                                                    $rowStatusClass = match ($sv->status ?? 'pending') {
                                                        'completed' => 'table-row-completed',
                                                        'pending' => 'table-row-pending',
                                                        'rejected' => 'table-row-rejected',
                                                        default => ''
                                                    };
                                                @endphp
                                                <tr data-id="{{ $sv->svid }}" data-status="{{ $sv->status ?? 'pending' }}"
                                                    data-term="{{ $sv->termid }}" data-session="{{ $sv->sessionid }}"
                                                    class="table-row-hover {{ $rowStatusClass }}">
                                                    <td><div class="form-check"><input class="form-check-input" type="checkbox" name="chk_child" value="{{ $sv->svid }}" /></div></td>
                                                    <td class="sn fw-bold">{{ ++$i }}</td>
                                                    <td class="vetting_username" data-vetting_userid="{{ $sv->vetting_userid }}">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0">
                                                                <div class="avatar-sm rounded-circle bg-light d-flex align-items-center justify-content-center">
                                                                    <img src="{{ asset('storage/staff_avatars/' . ($sv->vetting_picture ?? 'unnamed.jpg')) }}"
                                                                        alt="{{ $sv->vetting_username ?? 'Unknown' }}"
                                                                        class="rounded-circle avatar-xs"
                                                                        style="width:38px;height:38px;object-fit:cover;" />
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1 ms-3">
                                                                <h6 class="mb-0">{{ $sv->vetting_username ?? 'N/A' }}</h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="subjectname" data-subjectclassid="{{ $sv->subjectclassid }}">{{ $sv->subjectname ?? 'N/A' }}</td>
                                                    <td class="sclass">{{ $sv->sclass ?? 'N/A' }}</td>
                                                    <td class="schoolarm">{{ $sv->schoolarm ?? 'N/A' }}</td>
                                                    <td class="teachername">{{ $sv->teachername ?? 'N/A' }}</td>
                                                    <td class="termname" data-termid="{{ $sv->termid }}">{{ $sv->termname ?? 'N/A' }}</td>
                                                    <td class="sessionname" data-sessionid="{{ $sv->sessionid }}">{{ $sv->sessionname ?? 'N/A' }}</td>
                                                    <td class="status">
                                                        <span class="badge-status {{ $statusClass }}">
                                                            {{ ucfirst($sv->status ?? 'pending') }}
                                                        </span>
                                                    </td>
                                                    <td class="datereg">{{ $sv->updated_at ? $sv->updated_at->format('d M, Y') : 'N/A' }}</td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            @can('Update mock-subject-vettings')
                                                                <a href="javascript:void(0);" class="action-btn btn btn-light btn-sm edit-item-btn" title="Edit">
                                                                    <i class="ri-pencil-line"></i>
                                                                </a>
                                                            @endcan
                                                            @can('Delete mock-subject-vettings')
                                                                <a href="javascript:void(0);" class="action-btn btn btn-light btn-sm remove-item-btn text-danger" title="Delete">
                                                                    <i class="ri-delete-bin-line"></i>
                                                                </a>
                                                            @endcan
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr class="noresult">
                                                    <td colspan="12" class="text-center py-5">
                                                        <i class="ri-inbox-line fs-48 text-muted"></i>
                                                        <h5 class="mt-3">No Mock Subject Vetting Assignments Found</h5>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row mt-4 align-items-center" id="pagination-element">
                                    <div class="col-sm">
                                        <div class="text-muted text-center text-sm-start">
                                            Showing <span id="showing-records">0</span> of <span id="total-records-footer">{{ $mocksubjectvettings->count() }}</span> Results
                                        </div>
                                    </div>
                                    <div class="col-sm-auto mt-3 mt-sm-0">
                                        <div class="pagination-wrap">
                                            <ul class="pagination listjs-pagination mb-0"></ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Modal -->
                <div id="addMockSubjectVettingModal" class="modal fade" tabindex="-1" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered modal-xl">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title"><i class="ri-add-circle-line me-2"></i>Add Mock Subject Vetting Assignment</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form id="add-mocksubjectvetting-form">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Vetting Staff <span class="text-danger">*</span></label>
                                                <select name="userid" id="mock-userid" class="form-select select2" required>
                                                    <option value="">Select Staff</option>
                                                    @foreach ($staff as $staff_member)
                                                        <option value="{{ $staff_member->id }}">{{ $staff_member->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
                                                <select name="sessionid" id="mock-sessionid" class="form-select" required>
                                                    <option value="">Select Session</option>
                                                    @foreach ($sessions as $session)
                                                        <option value="{{ $session->id }}">{{ $session->session }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Terms <span class="text-danger">*</span></label>
                                        <div class="p-3 bg-light rounded">
                                            @foreach ($terms as $term)
                                                <div class="form-check form-check-inline me-3">
                                                    <input class="form-check-input" type="checkbox" name="termid[]" value="{{ $term->id }}" id="term-{{ $term->id }}">
                                                    <label class="form-check-label" for="term-{{ $term->id }}">{{ $term->term }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- AJAX Subject Search Section -->
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Subject-Class Assignments <span class="text-danger">*</span></label>

                                        <div class="input-group mb-3">
                                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                                            <input type="text" id="mockSubjectSearchInput" class="form-control"
                                                   placeholder="Search by subject, class, teacher, term, or session... (min 2 characters)">
                                            <button type="button" id="mockClearSearchBtn" class="btn btn-outline-secondary" style="display: none;">
                                                <i class="ri-close-line"></i>
                                            </button>
                                        </div>

                                        <div id="mockSearchResults" class="list-group mb-3" style="max-height: 300px; overflow-y: auto; display: none;"></div>
                                        <div id="mockSearchLoading" class="text-center p-3" style="display: none;">
                                            <div class="spinner-border spinner-border-sm text-primary"></div>
                                            <span class="ms-2">Searching...</span>
                                        </div>

                                        <div class="mt-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="mb-0">Selected Subjects (<span id="mockSelectedCount">0</span>)</h6>
                                                <button type="button" id="mockClearAllSelectedBtn" class="btn btn-sm btn-danger" style="display: none;">
                                                    <i class="ri-delete-bin-line me-1"></i>Clear All
                                                </button>
                                            </div>
                                            <div id="mockSelectedSubjectsContainer" class="border rounded p-2" style="min-height: 100px; max-height: 300px; overflow-y: auto;">
                                                <div class="text-center text-muted py-3">No subjects selected</div>
                                            </div>
                                            <input type="hidden" name="subjectclassid" id="mockSelectedSubjectIds">
                                        </div>
                                    </div>

                                    <div class="alert alert-danger d-none" id="mock-alert-error-msg"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary" id="mock-add-btn">Add Assignment(s)</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Edit Modal -->
                <div id="editMockModal" class="modal fade" tabindex="-1" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title"><i class="ri-edit-line me-2"></i>Edit Mock Subject Vetting</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form id="edit-mocksubjectvetting-form">
                                <div class="modal-body">
                                    <input type="hidden" name="id" id="mock-edit-id-field">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Vetting Staff <span class="text-danger">*</span></label>
                                        <select name="userid" id="mock-edit-userid" class="form-select select2" required>
                                            <option value="">Select Staff</option>
                                            @foreach ($staff as $staff_member)
                                                <option value="{{ $staff_member->id }}">{{ $staff_member->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Term <span class="text-danger">*</span></label>
                                        <select name="termid" id="mock-edit-termid" class="form-select" required>
                                            <option value="">Select Term</option>
                                            @foreach ($terms as $term)
                                                <option value="{{ $term->id }}">{{ $term->term }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
                                        <select name="sessionid" id="mock-edit-sessionid" class="form-select" required>
                                            <option value="">Select Session</option>
                                            @foreach ($sessions as $session)
                                                <option value="{{ $session->id }}">{{ $session->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Subject-Class <span class="text-danger">*</span></label>
                                        <select name="subjectclassid" id="mock-edit-subjectclassid" class="form-select" required>
                                            <option value="">Select Subject-Class</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                        <select name="status" id="mock-edit-status" class="form-select" required>
                                            <option value="pending">Pending</option>
                                            <option value="completed">Completed</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                    </div>
                                    <div class="alert alert-danger d-none" id="mock-edit-alert-error-msg"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Delete Modal -->
                <div id="deleteRecordModal" class="modal fade" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body text-center p-4">
                                <i class="ri-delete-bin-line text-danger fs-48 mb-3 d-block"></i>
                                <h4 class="mb-2">Are you sure?</h4>
                                <p class="text-muted mb-4">You won't be able to revert this!</p>
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-danger" id="delete-record">Yes, Delete It!</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js"></script>

<script>
// Escape HTML helper
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Main Mock Subject Vetting Management
let mockSelectedSubjects = new Map();
let mockSubjectVettingList = null;
let mockDeleteId = null;
let mockDeleteUrl = null;

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeMockListJS();
    initializeMockFilters();
    initializeMockSearch();
    initializeMockAddForm();
    initializeMockEditForm();
    initializeMockDelete();
    initializeMockBulkDelete();
    initializeSelect2();
    updateStatsFromList();
});

function initializeMockListJS() {
    try {
        mockSubjectVettingList = new List('mockSubjectVettingList', {
            valueNames: ['sn', 'vetting_username', 'subjectname', 'sclass', 'schoolarm', 'teachername', 'termname', 'sessionname', 'status', 'datereg'],
            page: 10,
            pagination: { paginationClass: 'listjs-pagination' }
        });
        mockSubjectVettingList.on('updated', updateStatsFromList);
    } catch(e) { console.error('ListJS init error:', e); }
}

function updateStatsFromList() {
    if (!mockSubjectVettingList) return;
    const items = mockSubjectVettingList.matchingItems;
    let total = items.length, pending = 0, completed = 0, rejected = 0;
    items.forEach(item => {
        const status = (item.elm.getAttribute('data-status') || 'pending').toLowerCase();
        if (status === 'pending') pending++;
        else if (status === 'completed') completed++;
        else if (status === 'rejected') rejected++;
    });
    document.getElementById('mock-stat-total').textContent = total;
    document.getElementById('mock-stat-pending').textContent = pending;
    document.getElementById('mock-stat-completed').textContent = completed;
    document.getElementById('mock-stat-rejected').textContent = rejected;
    const showingEl = document.getElementById('showing-records');
    if (showingEl) showingEl.textContent = Math.min(total, 10);
    const totalEl = document.getElementById('total-records-footer');
    if (totalEl) totalEl.textContent = total;
}

function initializeMockFilters() {
    const termFilter = document.getElementById('mock-term-filter-stats');
    const sessionFilter = document.getElementById('mock-session-filter-stats');
    const resetBtn = document.getElementById('mock-reset-stats-btn');
    if (termFilter) {
        termFilter.addEventListener('change', () => applyFilters());
    }
    if (sessionFilter) {
        sessionFilter.addEventListener('change', () => applyFilters());
    }
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            if (termFilter) termFilter.value = '';
            if (sessionFilter) sessionFilter.value = '';
            applyFilters();
        });
    }
    // Stat card filters
    document.querySelectorAll('#mockStatsCardsRow .stat-card-clickable').forEach(card => {
        card.addEventListener('click', () => {
            const status = card.getAttribute('data-status');
            if (!mockSubjectVettingList) return;
            if (status === 'all') {
                mockSubjectVettingList.filter();
            } else {
                mockSubjectVettingList.filter(item => {
                    const itemStatus = (item.elm.getAttribute('data-status') || 'pending').toLowerCase();
                    return itemStatus === status;
                });
            }
        });
    });
}

function applyFilters() {
    if (!mockSubjectVettingList) return;
    const termFilter = document.getElementById('mock-term-filter-stats').value;
    const sessionFilter = document.getElementById('mock-session-filter-stats').value;
    if (!termFilter && !sessionFilter) {
        mockSubjectVettingList.filter();
    } else {
        mockSubjectVettingList.filter(item => {
            const rowTerm = item.elm.getAttribute('data-term') || '';
            const rowSession = item.elm.getAttribute('data-session') || '';
            const termOk = !termFilter || rowTerm === termFilter;
            const sessionOk = !sessionFilter || rowSession === sessionFilter;
            return termOk && sessionOk;
        });
    }
}

function initializeMockSearch() {
    const searchInput = document.querySelector('.search-box input.search');
    if (searchInput && mockSubjectVettingList) {
        searchInput.addEventListener('input', () => {
            mockSubjectVettingList.search(searchInput.value);
        });
    }
}

// ========== AJAX SUBJECT SEARCH ==========
function initializeMockAddForm() {
    const form = document.getElementById('add-mocksubjectvetting-form');
    if (!form) return;

    // Initialize search
    const searchInput = document.getElementById('mockSubjectSearchInput');
    const resultsDiv = document.getElementById('mockSearchResults');
    const loadingDiv = document.getElementById('mockSearchLoading');
    const clearSearchBtn = document.getElementById('mockClearSearchBtn');
    let searchTimeout;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            clearTimeout(searchTimeout);
            if (query.length < 2) {
                resultsDiv.style.display = 'none';
                clearSearchBtn.style.display = 'none';
                return;
            }
            clearSearchBtn.style.display = 'block';
            loadingDiv.style.display = 'block';
            resultsDiv.style.display = 'none';

            searchTimeout = setTimeout(() => {
                const excludeIds = Array.from(mockSelectedSubjects.keys()).join(',');
                fetch(`/api/subject-classes/search?q=${encodeURIComponent(query)}&exclude_ids=${excludeIds}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(response => {
                    loadingDiv.style.display = 'none';
                    if (!response.success) {
                        resultsDiv.innerHTML = `<div class="list-group-item text-danger">${response.message || 'Search failed'}</div>`;
                        resultsDiv.style.display = 'block';
                        return;
                    }
                    const data = response.data;
                    if (data.length === 0) {
                        resultsDiv.innerHTML = '<div class="list-group-item text-muted">No results found</div>';
                        resultsDiv.style.display = 'block';
                        return;
                    }
                    resultsDiv.innerHTML = data.map(item => `
                        <div class="list-group-item subject-search-item" data-id="${item.id}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="fw-bold">${escapeHtml(item.subjectname)} ${item.subjectcode ? `<span class="text-muted">(${escapeHtml(item.subjectcode)})</span>` : ''}</div>
                                    <div class="small text-muted mt-1">
                                        <i class="ri-group-line me-1"></i> Class: ${escapeHtml(item.sclass)} ${item.schoolarm ? `(${escapeHtml(item.schoolarm)})` : ''}<br>
                                        <i class="ri-user-line me-1"></i> Teacher: ${escapeHtml(item.teachername)}<br>
                                        <i class="ri-calendar-line me-1"></i> Term: ${escapeHtml(item.termname)} | Session: ${escapeHtml(item.sessionname)}
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary add-subject-btn">
                                    <i class="ri-add-line me-1"></i>Add
                                </button>
                            </div>
                        </div>
                    `).join('');
                    resultsDiv.style.display = 'block';
                })
                .catch(error => {
                    loadingDiv.style.display = 'none';
                    resultsDiv.innerHTML = '<div class="list-group-item text-danger">Network error</div>';
                    resultsDiv.style.display = 'block';
                });
            }, 500);
        });
    }

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', () => {
            searchInput.value = '';
            resultsDiv.style.display = 'none';
            clearSearchBtn.style.display = 'none';
        });
    }

    // Add subject handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.add-subject-btn')) {
            const btn = e.target.closest('.add-subject-btn');
            const item = btn.closest('.subject-search-item');
            if (item) {
                const id = item.dataset.id;
                const name = item.querySelector('.fw-bold')?.innerText || '';
                if (!mockSelectedSubjects.has(id)) {
                    const details = item.querySelector('.small')?.innerHTML || '';
                    mockSelectedSubjects.set(id, { id, name, details });
                    updateSelectedSubjectsDisplay();
                    item.remove();
                    showTempMessage('Subject added', 'success');
                    if (resultsDiv.children.length === 0) {
                        resultsDiv.style.display = 'none';
                        searchInput.value = '';
                    }
                } else {
                    showTempMessage('Already selected', 'warning');
                }
            }
        }
    });

    // Clear all button
    const clearAllBtn = document.getElementById('mockClearAllSelectedBtn');
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', () => {
            if (confirm('Clear all selected subjects?')) {
                mockSelectedSubjects.clear();
                updateSelectedSubjectsDisplay();
            }
        });
    }

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        submitAddForm();
    });

    // Reset on modal close
    document.getElementById('addMockSubjectVettingModal')?.addEventListener('hidden.bs.modal', () => {
        form.reset();
        mockSelectedSubjects.clear();
        updateSelectedSubjectsDisplay();
        if (searchInput) searchInput.value = '';
        if (resultsDiv) resultsDiv.style.display = 'none';
        const errorEl = document.getElementById('mock-alert-error-msg');
        if (errorEl) errorEl.classList.add('d-none');
    });
}

function updateSelectedSubjectsDisplay() {
    const container = document.getElementById('mockSelectedSubjectsContainer');
    const countSpan = document.getElementById('mockSelectedCount');
    const hiddenInput = document.getElementById('mockSelectedSubjectIds');
    const clearAllBtn = document.getElementById('mockClearAllSelectedBtn');
    const count = mockSelectedSubjects.size;

    if (countSpan) countSpan.textContent = count;
    if (hiddenInput) hiddenInput.value = Array.from(mockSelectedSubjects.keys()).join(',');
    if (clearAllBtn) clearAllBtn.style.display = count > 0 ? 'block' : 'none';

    if (count === 0) {
        if (container) container.innerHTML = '<div class="text-center text-muted py-3">No subjects selected</div>';
        return;
    }

    let html = '';
    for (let [id, subject] of mockSelectedSubjects) {
        html += `
            <div class="selected-subject-item d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded">
                <div>
                    <strong>${escapeHtml(subject.name)}</strong>
                    <div class="small text-muted">${subject.details || ''}</div>
                </div>
                <button type="button" class="btn btn-sm btn-link text-danger remove-subject-btn" data-id="${id}">
                    <i class="ri-close-line"></i>
                </button>
            </div>
        `;
    }
    if (container) container.innerHTML = html;

    // Attach remove handlers
    document.querySelectorAll('.remove-subject-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            mockSelectedSubjects.delete(id);
            updateSelectedSubjectsDisplay();
            showTempMessage('Subject removed', 'info');
        });
    });
}

function submitAddForm() {
    const form = document.getElementById('add-mocksubjectvetting-form');
    const errorEl = document.getElementById('mock-alert-error-msg');
    const submitBtn = document.getElementById('mock-add-btn');

    const formData = new FormData(form);
    const selectedIds = Array.from(mockSelectedSubjects.keys());

    if (!formData.get('userid')) {
        showError(errorEl, 'Please select a vetting staff member.');
        return;
    }
    if (!formData.get('sessionid')) {
        showError(errorEl, 'Please select a session.');
        return;
    }
    const terms = formData.getAll('termid[]');
    if (terms.length === 0) {
        showError(errorEl, 'Please select at least one term.');
        return;
    }
    if (selectedIds.length === 0) {
        showError(errorEl, 'Please select at least one subject-class assignment.');
        return;
    }

    // Append selected IDs to form data
    selectedIds.forEach(id => {
        formData.append('subjectclassid[]', id);
    });

    if (errorEl) errorEl.classList.add('d-none');
    if (submitBtn) submitBtn.disabled = true;

    fetch('/mock-subject-vettings', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('addMockSubjectVettingModal'));
            if (modal) modal.hide();
            showToast(data.message || 'Assignment(s) added successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            let msg = data.message || 'An error occurred';
            if (data.errors) {
                msg = Object.values(data.errors).flat().join('<br>');
            }
            showError(errorEl, msg);
        }
    })
    .catch(error => {
        showError(errorEl, 'Network error. Please try again.');
    })
    .finally(() => {
        if (submitBtn) submitBtn.disabled = false;
    });
}

// ========== EDIT FORM ==========
function initializeMockEditForm() {
    // Table edit buttons
    document.querySelector('#kt_mock_subject_vetting_table tbody')?.addEventListener('click', function(e) {
        const btn = e.target.closest('.edit-item-btn');
        if (!btn) return;
        const row = btn.closest('tr');
        if (row) populateEditModal(row);
    });

    const form = document.getElementById('edit-mocksubjectvetting-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitEditForm();
        });
    }
}

function populateEditModal(row) {
    const id = row.getAttribute('data-id');
    const vettingUserId = row.querySelector('.vetting_username')?.getAttribute('data-vetting_userid') || '';
    const termid = row.querySelector('.termname')?.getAttribute('data-termid') || '';
    const sessionid = row.querySelector('.sessionname')?.getattribute('data-sessionid') || '';
    const subjectclassid = row.querySelector('.subjectname')?.getAttribute('data-subjectclassid') || '';
    const status = row.getAttribute('data-status') || 'pending';

    document.getElementById('mock-edit-id-field').value = id;
    document.getElementById('mock-edit-userid').value = vettingUserId;
    document.getElementById('mock-edit-termid').value = termid;
    document.getElementById('mock-edit-sessionid').value = sessionid;
    document.getElementById('mock-edit-status').value = status;

    // Load subject classes for dropdown
    fetch('/api/subject-classes/search?q=&limit=500', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        const select = document.getElementById('mock-edit-subjectclassid');
        if (select && data.success) {
            select.innerHTML = '<option value="">Select Subject-Class</option>';
            data.data.forEach(sc => {
                const option = document.createElement('option');
                option.value = sc.id;
                option.textContent = `${sc.subjectname} - ${sc.sclass} ${sc.schoolarm ? `(${sc.schoolarm})` : ''} - ${sc.teachername}`;
                if (sc.id == subjectclassid) option.selected = true;
                select.appendChild(option);
            });
        }
    });

    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#mock-edit-userid').trigger('change');
    }

    const modal = new bootstrap.Modal(document.getElementById('editMockModal'));
    modal.show();
}

function submitEditForm() {
    const id = document.getElementById('mock-edit-id-field').value;
    const errorEl = document.getElementById('mock-edit-alert-error-msg');
    const form = document.getElementById('edit-mocksubjectvetting-form');
    const formData = new FormData(form);
    formData.append('_method', 'PUT');

    fetch(`/mock-subject-vettings/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('editMockModal'));
            if (modal) modal.hide();
            showToast('Assignment updated successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            let msg = data.message || 'Update failed';
            if (data.errors) msg = Object.values(data.errors).flat().join('<br>');
            if (errorEl) {
                errorEl.innerHTML = msg;
                errorEl.classList.remove('d-none');
            }
        }
    })
    .catch(error => {
        if (errorEl) {
            errorEl.innerHTML = 'Network error. Please try again.';
            errorEl.classList.remove('d-none');
        }
    });
}

// ========== DELETE ==========
function initializeMockDelete() {
    // Table delete buttons
    document.querySelector('#kt_mock_subject_vetting_table tbody')?.addEventListener('click', function(e) {
        const btn = e.target.closest('.remove-item-btn');
        if (!btn) return;
        const row = btn.closest('tr');
        if (row) {
            mockDeleteId = row.getAttribute('data-id');
            const modal = new bootstrap.Modal(document.getElementById('deleteRecordModal'));
            modal.show();
        }
    });

    document.getElementById('delete-record')?.addEventListener('click', function() {
        if (!mockDeleteId) return;
        this.disabled = true;

        fetch(`/mock-subject-vettings/${mockDeleteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteRecordModal'));
            if (modal) modal.hide();
            if (data.success) {
                showToast('Assignment deleted successfully!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message || 'Delete failed', 'danger');
            }
        })
        .catch(() => showToast('Network error', 'danger'))
        .finally(() => { this.disabled = false; mockDeleteId = null; });
    });
}

// ========== BULK DELETE ==========
function initializeMockBulkDelete() {
    const checkAll = document.getElementById('checkAll');
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            document.querySelectorAll('input[name="chk_child"]').forEach(cb => cb.checked = this.checked);
            toggleRemoveBtn();
        });
    }
    document.querySelector('#kt_mock_subject_vetting_table tbody')?.addEventListener('change', function(e) {
        if (e.target.name === 'chk_child') toggleRemoveBtn();
    });
}

function toggleRemoveBtn() {
    const anyChecked = document.querySelectorAll('input[name="chk_child"]:checked').length > 0;
    const removeBtn = document.getElementById('remove-actions');
    if (removeBtn) removeBtn.classList.toggle('d-none', !anyChecked);
}

window.deleteMultiple = function() {
    const ids = Array.from(document.querySelectorAll('input[name="chk_child"]:checked')).map(cb => cb.value);
    if (!ids.length) return;
    if (!confirm(`Delete ${ids.length} record(s)?`)) return;

    fetch('/mock-subject-vettings/bulk-delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ids })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(`${ids.length} record(s) deleted.`, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Delete failed', 'danger');
        }
    })
    .catch(() => showToast('Network error', 'danger'));
};

// ========== HELPER FUNCTIONS ==========
function showError(el, message) {
    if (!el) return;
    el.innerHTML = message;
    el.classList.remove('d-none');
    setTimeout(() => el.classList.add('d-none'), 5000);
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = 9999;
    toast.style.minWidth = '280px';
    toast.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function showTempMessage(message, type = 'info') {
    const div = document.createElement('div');
    div.className = `alert alert-${type} alert-dismissible fade show position-fixed bottom-0 end-0 m-3`;
    div.style.zIndex = 9999;
    div.innerHTML = message;
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 2000);
}

function initializeSelect2() {
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#mock-userid').select2({ dropdownParent: $('#addMockSubjectVettingModal'), placeholder: 'Select Staff', width: '100%' });
        $('#mock-edit-userid').select2({ dropdownParent: $('#editMockModal'), placeholder: 'Select Staff', width: '100%' });
    }
}
</script>
@endsection
