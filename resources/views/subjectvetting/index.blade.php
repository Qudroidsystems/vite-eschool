@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Subject Vetting Management</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Academics</a></li>
                                <li class="breadcrumb-item active">Subject Vetting</li>
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
                /* Search box styling */
                .subject-class-search-box {
                    position: relative;
                    margin-bottom: 15px;
                }

                .subject-class-search-box .search-icon {
                    position: absolute;
                    right: 15px;
                    top: 50%;
                    transform: translateY(-50%);
                    color: #6c757d;
                    pointer-events: none;
                }

                .subject-class-search-box input {
                    width: 100%;
                    padding: 8px 40px 8px 15px;
                    border: 1px solid #ced4da;
                    border-radius: 0.375rem;
                    font-size: 14px;
                }

                .subject-class-search-box input:focus {
                    outline: none;
                    border-color: #86b7fe;
                    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
                }

                /* Selection summary styling */
                #subjectClassSelectionSummary {
                    font-size: 0.875rem;
                    border-left: 3px solid #0d6efd;
                    margin-top: 10px;
                }

                #clearSelectionBtn {
                    text-decoration: none;
                    font-size: 0.875rem;
                    cursor: pointer;
                }

                #clearSelectionBtn:hover {
                    text-decoration: underline;
                }

                /* Non-current session items */
                .non-current-session {
                    opacity: 0.6;
                    pointer-events: none;
                }

                .non-current-session input[type="checkbox"] {
                    display: none !important;
                }

                .non-current-session label {
                    color: #6c757d !important;
                    cursor: not-allowed;
                }

                /* Current session items */
                .current-session-item {
                    margin-bottom: 8px;
                }

                /* Checkbox group styling */
                .subject-class-checkbox-group {
                    max-height: 300px;
                    overflow-y: auto;
                    border: 1px solid #dee2e6;
                    border-radius: 0.375rem;
                    padding: 15px;
                    background-color: #f8f9fa;
                }

                /* No results message */
                .no-results-message {
                    display: none;
                    text-align: center;
                    padding: 20px;
                    color: #6c757d;
                    font-style: italic;
                    background-color: #f8f9fa;
                    border-radius: 0.375rem;
                    margin-bottom: 10px;
                }

                /* Checkbox styling */
                .form-check-input:disabled {
                    background-color: #e9ecef;
                    border-color: #ced4da;
                }

                .form-check-input:disabled ~ .form-check-label {
                    color: #6c757d;
                    cursor: not-allowed;
                }

                /* Term color styling */
                .term-first {
                    color: #198754 !important;
                    font-weight: 500;
                }

                .term-second {
                    color: #0d6efd !important;
                    font-weight: 500;
                }

                .term-third {
                    color: #ffc107 !important;
                    font-weight: 500;
                }

                .form-check-label.term-third {
                    color: #ffc107 !important;
                    text-shadow: 0 0 2px rgba(0,0,0,0.3);
                }

                select option.term-first {
                    color: #198754;
                    font-weight: 500;
                }

                select option.term-second {
                    color: #0d6efd;
                    font-weight: 500;
                }

                select option.term-third {
                    color: #ffc107;
                    font-weight: 500;
                    background-color: #2c3034;
                }

                /* Stats Card Hover Effects */
                .stats-card {
                    transition: all 0.3s ease;
                    border: none;
                    border-radius: 1rem;
                    overflow: hidden;
                }

                .stats-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
                }

                .stats-icon {
                    width: 48px;
                    height: 48px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 12px;
                    font-size: 24px;
                }

                /* Status Badge Styles */
                .badge-status {
                    padding: 6px 12px;
                    border-radius: 20px;
                    font-weight: 500;
                    font-size: 11px;
                }

                .badge-pending {
                    background-color: #ffe5e5;
                    color: #dc3545;
                }

                .badge-completed {
                    background-color: #e3f5ec;
                    color: #28a745;
                }

                .badge-rejected {
                    background-color: #fff4e5;
                    color: #ffc107;
                }

                /* Table Row Background Colors */
                .row-pending {
                    background-color: #ffe5e5 !important;
                }

                .row-completed {
                    background-color: #e3f5ec !important;
                }

                .row-rejected {
                    background-color: #fff4e5 !important;
                }

                .table-row-hover:hover {
                    filter: brightness(0.97);
                }

                /* Action Buttons */
                .action-btn {
                    width: 32px;
                    height: 32px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 8px;
                    transition: all 0.2s ease;
                }

                .action-btn:hover {
                    transform: scale(1.1);
                }

                /* Session Badge */
                .session-badge {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 8px 16px;
                    border-radius: 12px;
                    font-weight: 500;
                }

                /* Filter Card */
                .filter-card {
                    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
                    border: none;
                    border-radius: 1rem;
                }

                /* View Toggle Buttons */
                .view-toggle-btn {
                    padding: 8px 16px;
                    border-radius: 8px;
                    transition: all 0.2s ease;
                    cursor: pointer;
                }

                .view-toggle-btn.active {
                    background-color: #0d6efd;
                    color: white;
                }

                .view-toggle-btn:not(.active):hover {
                    background-color: #e9ecef;
                }

                /* Card View Styles */
                .card-view-container {
                    display: none;
                }

                .card-view-container.active-view {
                    display: block;
                }

                .table-view-container.active-view {
                    display: block;
                }

                .table-view-container {
                    display: block;
                }

                .vetting-card {
                    transition: all 0.3s ease;
                    border-radius: 1rem;
                    overflow: hidden;
                }

                .vetting-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
                }

                .card-status-badge {
                    position: absolute;
                    top: 15px;
                    right: 15px;
                }

                /* Animations */
                @keyframes fadeInUp {
                    from {
                        opacity: 0;
                        transform: translateY(20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                .animate-fade-in-up {
                    animation: fadeInUp 0.5s ease-out;
                }

                /* Pagination Styles */
                .listjs-pagination {
                    display: flex;
                    gap: 5px;
                    margin-bottom: 0;
                }

                .listjs-pagination li {
                    list-style: none;
                }

                .listjs-pagination li a {
                    display: block;
                    padding: 6px 12px;
                    border-radius: 6px;
                    color: #0d6efd;
                    text-decoration: none;
                    transition: all 0.2s;
                }

                .listjs-pagination li a:hover {
                    background-color: #e9ecef;
                }

                .listjs-pagination li.active a {
                    background-color: #0d6efd;
                    color: white;
                }
            </style>

            <div id="subjectVettingList">
                <!-- Stats Cards Row -->
                <div class="row g-4 mb-4 animate-fade-in-up">
                    <div class="col-xl-3 col-md-6">
                        <div class="card stats-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1 text-uppercase fw-semibold fs-12">Total Assignments</p>
                                        <h2 class="mb-0 fw-bold" id="stat-total">{{ $subjectvettings->count() }}</h2>
                                        <p class="text-muted mb-0 mt-2 fs-13">
                                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                                <i class="ri-file-list-line me-1"></i>Active Session
                                            </span>
                                        </p>
                                    </div>
                                    <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                                        <i class="ri-file-list-line fs-24"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card stats-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1 text-uppercase fw-semibold fs-12">Pending</p>
                                        <h2 class="mb-0 fw-bold text-danger" id="stat-pending">{{ $statusCounts['pending'] }}</h2>
                                        <p class="text-muted mb-0 mt-2 fs-13">
                                            <span class="badge bg-danger bg-opacity-10 text-danger">
                                                <i class="ri-time-line me-1"></i>Awaiting Review
                                            </span>
                                        </p>
                                    </div>
                                    <div class="stats-icon bg-danger bg-opacity-10 text-danger">
                                        <i class="ri-timer-line fs-24"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card stats-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1 text-uppercase fw-semibold fs-12">Completed</p>
                                        <h2 class="mb-0 fw-bold text-success" id="stat-completed">{{ $statusCounts['completed'] }}</h2>
                                        <p class="text-muted mb-0 mt-2 fs-13">
                                            <span class="badge bg-success bg-opacity-10 text-success">
                                                <i class="ri-checkbox-circle-line me-1"></i>Approved
                                            </span>
                                        </p>
                                    </div>
                                    <div class="stats-icon bg-success bg-opacity-10 text-success">
                                        <i class="ri-checkbox-circle-line fs-24"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card stats-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1 text-uppercase fw-semibold fs-12">Rejected</p>
                                        <h2 class="mb-0 fw-bold text-warning" id="stat-rejected">{{ $statusCounts['rejected'] }}</h2>
                                        <p class="text-muted mb-0 mt-2 fs-13">
                                            <span class="badge bg-warning bg-opacity-10 text-warning">
                                                <i class="ri-close-circle-line me-1"></i>Needs Revision
                                            </span>
                                        </p>
                                    </div>
                                    <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                                        <i class="ri-close-circle-line fs-24"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bar Chart Card -->
                <div class="row mb-4 animate-fade-in-up" style="animation-delay: 0.1s;">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header bg-transparent border-0 pt-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5 class="card-title mb-1 fw-bold">Vetting Status Distribution</h5>
                                        <p class="text-muted mb-0">Overview of all vetting assignments by status</p>
                                    </div>
                                    @if($currentSession)
                                        <div class="session-badge">
                                            <i class="ri-calendar-line me-2"></i>
                                            {{ $currentSession->session }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="position: relative; height: 320px; width: 100%;">
                                    <canvas id="vettingStatusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Card -->
                <div class="row mb-4 animate-fade-in-up" style="animation-delay: 0.2s;">
                    <div class="col-lg-12">
                        <div class="card filter-card">
                            <div class="card-body">
                                <div class="row g-3 align-items-center">
                                    <div class="col-xxl-3">
                                        <div class="search-box">
                                            <label class="form-label text-muted mb-2 fw-semibold">Search Assignments</label>
                                            <div class="position-relative">
                                                <input type="text" class="form-control search" placeholder="Search by staff, subject, class, teacher...">
                                                <i class="ri-search-line search-icon"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3">
                                        <div class="filter-group">
                                            <label for="session-filter" class="form-label text-muted fw-semibold">Filter by Session</label>
                                            <select class="form-select" id="session-filter">
                                                <option value="">All Sessions</option>
                                                @foreach ($sessions as $session)
                                                    <option value="{{ $session->id }}" {{ $currentSession && $currentSession->id == $session->id ? 'selected' : '' }}>
                                                        {{ $session->session }} @if($session->status == 'Current') (Current) @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3">
                                        <div class="view-toggle">
                                            <label class="form-label text-muted mb-2 fw-semibold">View Mode</label>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="view-toggle-btn active" id="tableViewBtn">
                                                    <i class="ri-table-view me-1"></i> Table View
                                                </button>
                                                <button type="button" class="view-toggle-btn" id="cardViewBtn">
                                                    <i class="ri-layout-grid-line me-1"></i> Card View
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3">
                                        <div class="current-session-info">
                                            <div class="alert alert-light border-0 mb-0 shadow-sm">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <i class="ri-information-line text-primary fs-18"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2">
                                                        Currently viewing: <strong class="text-primary">{{ $currentSession ? $currentSession->session : 'No active session' }}</strong>
                                                        @if($currentSession && $currentSession->status == 'Current')
                                                            <span class="badge bg-success ms-2">Current Session</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table View -->
                <div class="row animate-fade-in-up table-view-container active-view" id="tableView" style="animation-delay: 0.3s;">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header bg-transparent pt-4 pb-0">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div>
                                        <h5 class="card-title mb-1 fw-bold">Subject Vetting Assignments</h5>
                                        <p class="text-muted mb-0">Manage and monitor all subject vetting assignments</p>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()">
                                            <i class="ri-delete-bin-2-line me-1"></i> Delete Selected
                                        </button>
                                        @can('Create subject-vettings')
                                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addSubjectVettingModal" id="create-subject-vettings-btn">
                                                <i class="ri-add-line me-1"></i> Create Assignment
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-4">
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0" id="kt_subject_vetting_table">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="w-10px pe-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="checkAll" />
                                                    </div>
                                                </th>
                                                <th class="sort cursor-pointer" data-sort="sn">#</th>
                                                <th class="sort cursor-pointer" data-sort="vetting_username">Vetting Staff</th>
                                                <th class="sort cursor-pointer" data-sort="subjectname">Subject</th>
                                                <th class="sort cursor-pointer" data-sort="sclass">Class</th>
                                                <th class="sort cursor-pointer" data-sort="schoolarm">Arm</th>
                                                <th class="sort cursor-pointer" data-sort="teachername">Teacher</th>
                                                <th class="sort cursor-pointer" data-sort="termname">Term</th>
                                                <th class="sort cursor-pointer" data-sort="sessionname">Session</th>
                                                <th class="sort cursor-pointer" data-sort="status">Status</th>
                                                <th class="sort cursor-pointer" data-sort="datereg">Updated</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list">
                                            @php $i = 0 @endphp
                                            @forelse ($subjectvettings as $sv)
                                                <?php
                                                $picture = $sv->vetting_picture ?? 'unnamed.jpg';
                                                $imagePath = asset('storage/staff_avatars/' . $picture);
                                                $fileExists = file_exists(storage_path('app/public/staff_avatars/' . $picture));
                                                $defaultImageExists = file_exists(storage_path('app/public/staff_avatars/unnamed.jpg'));
                                                $statusClass = match ($sv->status ?? 'pending') {
                                                    'completed' => 'badge-completed',
                                                    'pending' => 'badge-pending',
                                                    'rejected' => 'badge-rejected',
                                                    default => 'badge-pending'
                                                };
                                                $statusIcon = match ($sv->status ?? 'pending') {
                                                    'completed' => 'ri-checkbox-circle-line',
                                                    'pending' => 'ri-time-line',
                                                    'rejected' => 'ri-close-circle-line',
                                                    default => 'ri-time-line'
                                                };
                                                $rowBgClass = match ($sv->status ?? 'pending') {
                                                    'completed' => 'row-completed',
                                                    'pending' => 'row-pending',
                                                    'rejected' => 'row-rejected',
                                                    default => ''
                                                };
                                                ?>
                                                <tr data-url="{{ route('subjectvetting.destroy', $sv->svid) }}" class="table-row-hover {{ $rowBgClass }}">
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="chk_child" />
                                                        </div>
                                                    </td>
                                                    <td class="sn fw-bold">{{ ++$i }}</td>
                                                    <td class="vetting_username" data-vetting_userid="{{ $sv->vetting_userid }}">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0">
                                                                <div class="avatar-sm rounded-circle bg-light d-flex align-items-center justify-content-center">
                                                                    <img src="{{ $imagePath }}"
                                                                        alt="{{ $sv->vetting_username ?? 'Unknown Staff' }}"
                                                                        class="rounded-circle avatar-xs staff-image"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#imageViewModal"
                                                                        data-image="{{ $imagePath }}"
                                                                        data-picture="{{ $sv->vetting_picture ?? 'none' }}"
                                                                        data-teachername="{{ $sv->vetting_username ?? 'Unknown Staff' }}"
                                                                        data-file-exists="{{ $fileExists ? 'true' : 'false' }}"
                                                                        data-default-exists="{{ $defaultImageExists ? 'true' : 'false' }}"
                                                                        style="width: 38px; height: 38px; object-fit: cover; cursor: pointer;"
                                                                        onerror="this.src='{{ asset('storage/staff_avatars/unnamed.jpg') }}';" />
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1 ms-3">
                                                                <h6 class="mb-0">{{ $sv->vetting_username ?? 'N/A' }}</h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="subjectname" data-subjectclassid="{{ $sv->subjectclassid }}">
                                                        <span class="fw-medium">{{ $sv->subjectname ?? 'N/A' }}</span>
                                                        @if($sv->subjectcode)
                                                            <small class="text-muted d-block">{{ $sv->subjectcode }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="sclass" data-schoolclassid="{{ $sv->schoolclassid }}">{{ $sv->sclass ?? 'N/A' }}</td>
                                                    <td class="schoolarm">{{ $sv->schoolarm ?? 'N/A' }}</td>
                                                    <td class="teachername" data-subtid="{{ $sv->subtid }}">{{ $sv->teachername ?? 'N/A' }}</td>
                                                    <td class="termname" data-termid="{{ $sv->termid }}">{{ $sv->termname ?? 'N/A' }}</td>
                                                    <td class="sessionname" data-sessionid="{{ $sv->sessionid }}">{{ $sv->sessionname ?? 'N/A' }}</td>
                                                    <td class="status">
                                                        <span class="badge-status {{ $statusClass }}">
                                                            <i class="{{ $statusIcon }} me-1 fs-10"></i>
                                                            {{ ucfirst($sv->status ?? 'pending') }}
                                                        </span>
                                                    </td>
                                                    <td class="datereg">
                                                        <small>{{ $sv->updated_at ? $sv->updated_at->format('d M, Y') : 'N/A' }}</small>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            @can('Update subject-vettings')
                                                                <a href="javascript:void(0);" class="action-btn btn btn-light btn-sm edit-item-btn" title="Edit">
                                                                    <i class="ri-pencil-line"></i>
                                                                </a>
                                                            @endcan
                                                            @can('Delete subject-vettings')
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
                                                        <div class="text-center">
                                                            <i class="ri-inbox-line fs-48 text-muted"></i>
                                                            <h5 class="mt-3">No Subject Vetting Assignments Found</h5>
                                                            <p class="text-muted">No assignments found for the selected session.</p>
                                                            @can('Create subject-vettings')
                                                                <button type="button" class="btn btn-primary add-btn mt-2" data-bs-toggle="modal" data-bs-target="#addSubjectVettingModal">
                                                                    <i class="ri-add-line me-1"></i> Create Your First Assignment
                                                                </button>
                                                            @endcan
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="row mt-4 align-items-center" id="pagination-element">
                                    <div class="col-sm">
                                        <div class="text-muted text-center text-sm-start">
                                            Showing <span id="showing-records">0</span> of <span id="total-records-footer">{{ $subjectvettings->count() }}</span> Results
                                        </div>
                                    </div>
                                    <div class="col-sm-auto mt-3 mt-sm-0">
                                        <div class="pagination-wrap">
                                            <nav aria-label="Page navigation">
                                                <ul class="pagination listjs-pagination mb-0"></ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card View -->
                <div class="row animate-fade-in-up card-view-container" id="cardView" style="animation-delay: 0.3s;">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header bg-transparent pt-4 pb-0">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div>
                                        <h5 class="card-title mb-1 fw-bold">Subject Vetting Assignments</h5>
                                        <p class="text-muted mb-0">Manage and monitor all subject vetting assignments</p>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @can('Create subject-vettings')
                                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addSubjectVettingModal">
                                                <i class="ri-add-line me-1"></i> Create Assignment
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-4">
                                <div class="row" id="cardViewContainer">
                                    @forelse ($subjectvettings as $sv)
                                        <?php
                                        $picture = $sv->vetting_picture ?? 'unnamed.jpg';
                                        $imagePath = asset('storage/staff_avatars/' . $picture);
                                        $cardBgClass = match ($sv->status ?? 'pending') {
                                            'completed' => 'border-success',
                                            'pending' => 'border-danger',
                                            'rejected' => 'border-warning',
                                            default => ''
                                        };
                                        $cardHeaderBg = match ($sv->status ?? 'pending') {
                                            'completed' => 'bg-success bg-opacity-10',
                                            'pending' => 'bg-danger bg-opacity-10',
                                            'rejected' => 'bg-warning bg-opacity-10',
                                            default => ''
                                        };
                                        ?>
                                        <div class="col-xl-4 col-md-6 mb-4 vetting-card-item" data-svid="{{ $sv->svid }}">
                                            <div class="card vetting-card h-100 {{ $cardBgClass }} border-2">
                                                <div class="card-header {{ $cardHeaderBg }} border-0">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center">
                                                            <img src="{{ $imagePath }}"
                                                                 alt="{{ $sv->vetting_username ?? 'Unknown' }}"
                                                                 class="rounded-circle me-2 staff-image-card"
                                                                 style="width: 45px; height: 45px; object-fit: cover; cursor: pointer;"
                                                                 data-bs-toggle="modal"
                                                                 data-bs-target="#imageViewModal"
                                                                 data-image="{{ $imagePath }}"
                                                                 data-picture="{{ $sv->vetting_picture ?? 'none' }}"
                                                                 data-teachername="{{ $sv->vetting_username ?? 'Unknown Staff' }}"
                                                                 data-file-exists="{{ file_exists(storage_path('app/public/staff_avatars/' . $picture)) ? 'true' : 'false' }}"
                                                                 data-default-exists="{{ file_exists(storage_path('app/public/staff_avatars/unnamed.jpg')) ? 'true' : 'false' }}"
                                                                 onerror="this.src='{{ asset('storage/staff_avatars/unnamed.jpg') }}';">
                                                            <div>
                                                                <h6 class="mb-0">{{ $sv->vetting_username ?? 'N/A' }}</h6>
                                                                <small class="text-muted">Vetting Officer</small>
                                                            </div>
                                                        </div>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                                                <i class="ri-more-2-fill"></i>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                @can('Update subject-vettings')
                                                                    <li>
                                                                        <a class="dropdown-item edit-item-btn" href="javascript:void(0);" data-id="{{ $sv->svid }}">
                                                                            <i class="ri-pencil-line me-2"></i> Edit
                                                                        </a>
                                                                    </li>
                                                                @endcan
                                                                @can('Delete subject-vettings')
                                                                    <li>
                                                                        <a class="dropdown-item text-danger remove-item-btn" href="javascript:void(0);" data-id="{{ $sv->svid }}" data-url="{{ route('subjectvetting.destroy', $sv->svid) }}">
                                                                            <i class="ri-delete-bin-line me-2"></i> Delete
                                                                        </a>
                                                                    </li>
                                                                @endcan
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <span class="badge-status {{ $sv->status == 'completed' ? 'badge-completed' : ($sv->status == 'pending' ? 'badge-pending' : 'badge-rejected') }} position-absolute top-0 end-0 mt-3 me-3">
                                                            <i class="{{ $sv->status == 'completed' ? 'ri-checkbox-circle-line' : ($sv->status == 'pending' ? 'ri-time-line' : 'ri-close-circle-line') }} me-1"></i>
                                                            {{ ucfirst($sv->status ?? 'pending') }}
                                                        </span>
                                                    </div>
                                                    <div class="info-item mb-3 pb-2 border-bottom">
                                                        <label class="text-muted mb-1 fs-12">Subject</label>
                                                        <p class="mb-0 fw-semibold">{{ $sv->subjectname ?? 'N/A' }} {{ $sv->subjectcode ? '(' . $sv->subjectcode . ')' : '' }}</p>
                                                    </div>
                                                    <div class="info-item mb-3 pb-2 border-bottom">
                                                        <label class="text-muted mb-1 fs-12">Class & Arm</label>
                                                        <p class="mb-0 fw-semibold">{{ $sv->sclass ?? 'N/A' }} {{ $sv->schoolarm ? '(' . $sv->schoolarm . ')' : '' }}</p>
                                                    </div>
                                                    <div class="info-item mb-3 pb-2 border-bottom">
                                                        <label class="text-muted mb-1 fs-12">Teacher</label>
                                                        <p class="mb-0 fw-semibold">{{ $sv->teachername ?? 'N/A' }}</p>
                                                    </div>
                                                    <div class="info-item mb-3 pb-2 border-bottom">
                                                        <label class="text-muted mb-1 fs-12">Term & Session</label>
                                                        <p class="mb-0 fw-semibold">{{ $sv->termname ?? 'N/A' }} - {{ $sv->sessionname ?? 'N/A' }}</p>
                                                    </div>
                                                    <div class="info-item">
                                                        <label class="text-muted mb-1 fs-12">Last Updated</label>
                                                        <p class="mb-0">{{ $sv->updated_at ? $sv->updated_at->format('d M, Y') : 'N/A' }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center py-5">
                                            <i class="ri-inbox-line fs-48 text-muted"></i>
                                            <h5 class="mt-3">No Subject Vetting Assignments Found</h5>
                                            <p class="text-muted">No assignments found for the selected session.</p>
                                            @can('Create subject-vettings')
                                                <button type="button" class="btn btn-primary add-btn mt-2" data-bs-toggle="modal" data-bs-target="#addSubjectVettingModal">
                                                    <i class="ri-add-line me-1"></i> Create Your First Assignment
                                                </button>
                                            @endcan
                                        </div>
                                    @endforelse
                                </div>
                                <div class="row mt-3" id="cardPagination">
                                    <div class="col-sm-12 text-center">
                                        <nav>
                                            <ul class="pagination justify-content-center" id="cardPaginationList"></ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Subject Vetting Modal -->
                <div id="addSubjectVettingModal" class="modal fade" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered modal-xl">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 id="addModalLabel" class="modal-title">
                                    <i class="ri-add-circle-line me-2"></i>Add Subject Vetting Assignment
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form class="tablelist-form" autocomplete="off" id="add-subjectvetting-form">
                                <div class="modal-body">
                                    <input type="hidden" id="add-id-field" name="id">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="userid" class="form-label fw-semibold">Vetting Staff <span class="text-danger">*</span></label>
                                                <select name="userid" id="userid" class="form-select" required>
                                                    <option value="">Select Staff</option>
                                                    @foreach ($staff as $staff_member)
                                                        <option value="{{ $staff_member->id }}">{{ $staff_member->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="sessionid" class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
                                                <select name="sessionid" id="sessionid" class="form-select" required>
                                                    <option value="">Select Session</option>
                                                    @foreach ($sessions as $session)
                                                        <option value="{{ $session->id }}" {{ $currentSession && $currentSession->id == $session->id ? 'selected' : '' }}>
                                                            {{ $session->session }} @if($session->status == 'Current') (Current Session) @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Terms <span class="text-danger">*</span></label>
                                        <div class="checkbox-group p-3 bg-light rounded" style="max-height: 100px; overflow-y: auto;">
                                            @foreach ($terms as $term)
                                                <div class="form-check form-check-inline me-3">
                                                    <input class="form-check-input modal-checkbox" type="checkbox" name="termid[]" id="add-term-{{ $term->id }}" value="{{ $term->id }}">
                                                    <label class="form-check-label" for="add-term-{{ $term->id }}">
                                                        {{ $term->term }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Subject-Class Assignments <span class="text-danger">*</span></label>

                                        <div class="subject-class-search-box">
                                            <input type="text" class="form-control" id="subjectClassSearch" placeholder="Search by subject, class, teacher, or session...">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>

                                        <div class="no-results-message" id="noResultsMessage">
                                            <i class="ri-search-eye-line me-2"></i>No matching subject-class assignments found.
                                        </div>

                                        <div class="subject-class-checkbox-group" id="subjectClassList">
                                            @foreach ($subjectclasses as $sc)
                                                @php
                                                    $isCurrentSession = $currentSession && $currentSession->id == $sc->sessionid;
                                                    $checkboxId = "add-subjectclass-{$sc->scid}";
                                                    $itemClass = $isCurrentSession ? 'current-session-item' : 'non-current-session';

                                                    $termColor = '';
                                                    $termId = $sc->termid ?? 0;
                                                    if ($termId == 1) {
                                                        $termColor = 'term-first';
                                                    } elseif ($termId == 2) {
                                                        $termColor = 'term-second';
                                                    } elseif ($termId == 3) {
                                                        $termColor = 'term-third';
                                                    }

                                                    $displayText = ($sc->subjectname ?? 'N/A') .
                                                                   ($sc->subjectcode ? ' (' . $sc->subjectcode . ')' : '') .
                                                                   ' - ' . ($sc->sclass ?? 'N/A') .
                                                                   ($sc->schoolarm ? ' (' . $sc->schoolarm . ')' : '') .
                                                                   ' - ' . ($sc->teachername ?? 'N/A') .
                                                                   ' -- ' . ($sc->sessionname ?? 'N/A') .
                                                                   '--' . ($sc->termname ?? 'N/A');
                                                    $searchableText = strtolower(($sc->subjectname ?? '') . ' ' . ($sc->subjectcode ?? '') . ' ' . ($sc->sclass ?? '') . ' ' . ($sc->schoolarm ?? '') . ' ' . ($sc->teachername ?? '') . ' ' . ($sc->sessionname ?? '') . ' ' . ($sc->termname ?? ''));
                                                @endphp
                                                <div class="form-check subject-class-item {{ $itemClass }}" data-search="{{ $searchableText }}">
                                                    @if($isCurrentSession)
                                                        <input class="form-check-input modal-checkbox"
                                                               type="checkbox"
                                                               name="subjectclassid[]"
                                                               id="{{ $checkboxId }}"
                                                               value="{{ $sc->scid }}"
                                                               data-termid="{{ $sc->termid }}">
                                                        <label class="form-check-label {{ $termColor }}" for="{{ $checkboxId }}">
                                                            {{ $displayText }}
                                                        </label>
                                                    @else
                                                        <div class="text-muted">
                                                            {{ $displayText }}
                                                            <small class="text-danger d-block mt-1">(Not available for current session)</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="alert alert-light mt-3 p-2 border" id="subjectClassSelectionSummary">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><i class="ri-checkbox-line me-1"></i> Selected: <strong id="selectedCount">0</strong> of <span id="totalCount">{{ count($subjectclasses->where('sessionid', $currentSession ? $currentSession->id : null)) }}</span> current session items</span>
                                                <button type="button" class="btn btn-sm btn-link p-0" id="clearSelectionBtn">Clear All</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-info">
                                        <i class="ri-information-line me-2"></i>
                                        <strong>Note:</strong> Only subject-class assignments from the current session ({{ $currentSession ? $currentSession->session : 'No active session' }}) are available for selection. Other sessions are disabled.
                                    </div>
                                    <div class="alert alert-danger d-none" id="alert-error-msg"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                        <i class="ri-close-line me-1"></i>Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary" id="add-btn">
                                        <i class="ri-save-line me-1"></i>Add Assignment
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Edit Subject Vetting Modal -->
                <div id="editModal" class="modal fade" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-warning text-dark">
                                <h5 id="editModalLabel" class="modal-title">
                                    <i class="ri-edit-line me-2"></i>Edit Subject Vetting Assignment
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form class="tablelist-form" autocomplete="off" id="edit-subjectvetting-form">
                                <div class="modal-body">
                                    <input type="hidden" id="edit-id-field" name="id">
                                    <div class="mb-3">
                                        <label for="edit-userid" class="form-label fw-semibold">Vetting Staff <span class="text-danger">*</span></label>
                                        <select name="userid" id="edit-userid" class="form-select" required>
                                            <option value="">Select Staff</option>
                                            @foreach ($staff as $staff_member)
                                                <option value="{{ $staff_member->id }}">{{ $staff_member->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-termid" class="form-label fw-semibold">Term <span class="text-danger">*</span></label>
                                        <select name="termid" id="edit-termid" class="form-select" required>
                                            <option value="">Select Term</option>
                                            @foreach ($terms as $term)
                                                <option value="{{ $term->id }}">{{ $term->term }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-sessionid" class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
                                        <select name="sessionid" id="edit-sessionid" class="form-select" required>
                                            <option value="">Select Session</option>
                                            @foreach ($sessions as $session)
                                                <option value="{{ $session->id }}">
                                                    {{ $session->session }} @if($session->status == 'Current') (Current Session) @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-subjectclassid" class="form-label fw-semibold">Subject-Class <span class="text-danger">*</span></label>
                                        <select name="subjectclassid" id="edit-subjectclassid" class="form-select" required>
                                            <option value="">Select Subject-Class</option>
                                            @foreach ($subjectclasses as $sc)
                                                @php
                                                    $isCurrentSession = $currentSession && $currentSession->id == $sc->sessionid;
                                                    $termColor = '';
                                                    $termId = $sc->termid ?? 0;
                                                    if ($termId == 1) $termColor = 'term-first';
                                                    elseif ($termId == 2) $termColor = 'term-second';
                                                    elseif ($termId == 3) $termColor = 'term-third';

                                                    $displayText = ($sc->subjectname ?? 'N/A') .
                                                                   ($sc->subjectcode ? ' (' . $sc->subjectcode . ')' : '') .
                                                                   ' - ' . ($sc->sclass ?? 'N/A') .
                                                                   ($sc->schoolarm ? ' (' . $sc->schoolarm . ')' : '') .
                                                                   ' - ' . ($sc->teachername ?? 'N/A') .
                                                                   ' -- ' . ($sc->sessionname ?? 'N/A') .
                                                                   '--' . ($sc->termname ?? 'N/A');
                                                @endphp
                                                <option value="{{ $sc->scid }}" class="{{ $termColor }}" @if(!$isCurrentSession) disabled @endif>
                                                    {{ $displayText }}
                                                    @if(!$isCurrentSession) (Not available) @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Note: Only current session subject-classes are available for editing</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                        <select name="status" id="edit-status" class="form-select" required>
                                            <option value="pending">Pending</option>
                                            <option value="completed">Completed</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                    </div>
                                    <div class="alert alert-danger d-none" id="edit-alert-error-msg"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                        <i class="ri-close-line me-1"></i>Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary" id="update-btn">
                                        <i class="ri-save-line me-1"></i>Update
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Delete Confirmation Modal -->
                <div id="deleteRecordModal" class="modal fade" tabindex="-1" aria-labelledby="deleteRecordModalLabel" aria-hidden="true">
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

                <!-- Image Preview Modal -->
                <div id="imageViewModal" class="modal fade" tabindex="-1" aria-labelledby="imageViewModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 id="imageViewModalLabel" class="modal-title">Staff Image Preview</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img id="preview-image" src="" alt="Staff Image" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;" />
                                <p id="preview-teachername" class="mt-3 fw-bold mb-0"></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    window.vettingStatusCounts = @json($statusCounts);
    window.currentSessionId = @json($currentSession ? $currentSession->id : null);
    window.subjectVettingsData = @json($subjectvettings);
    console.log('Initial vettingStatusCounts:', window.vettingStatusCounts);
    console.log('Current Session ID:', window.currentSessionId);
</script>

@endsection
