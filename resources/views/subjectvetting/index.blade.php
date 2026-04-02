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

                /* Search highlight */
                .highlight-text {
                    background-color: #fff3cd;
                    padding: 1px 4px;
                    border-radius: 3px;
                    font-weight: 600;
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
                    cursor: pointer;
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

                /* Table Row Status Background Colors */
                .table-row-pending {
                    background-color: #fff5f5 !important;
                    border-left: 3px solid #dc3545;
                }

                .table-row-completed {
                    background-color: #f0fff4 !important;
                    border-left: 3px solid #28a745;
                }

                .table-row-rejected {
                    background-color: #fffbf0 !important;
                    border-left: 3px solid #ffc107;
                }

                .table-row-hover {
                    transition: all 0.2s ease;
                }

                .table-row-hover:hover {
                    transform: scale(1.01);
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
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

                /* Card Header Gradient */
                .card-header-gradient {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                }

                /* Filter Card */
                .filter-card {
                    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
                    border: none;
                    border-radius: 1rem;
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

                /* Card View Styles */
                .card-view-container {
                    display: none;
                }

                .card-view-container.active {
                    display: block;
                }

                .table-view-container {
                    display: block;
                }

                .table-view-container.hide {
                    display: none;
                }

                .vetting-card {
                    background: white;
                    border-radius: 1rem;
                    padding: 1.25rem;
                    margin-bottom: 1rem;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                    transition: all 0.3s ease;
                    border-left: 4px solid;
                    cursor: pointer;
                }

                .vetting-card:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 4px 15px rgba(0,0,0,0.12);
                }

                .vetting-card.pending-card {
                    border-left-color: #dc3545;
                    background: linear-gradient(135deg, #fff 0%, #fff5f5 100%);
                }

                .vetting-card.completed-card {
                    border-left-color: #28a745;
                    background: linear-gradient(135deg, #fff 0%, #f0fff4 100%);
                }

                .vetting-card.rejected-card {
                    border-left-color: #ffc107;
                    background: linear-gradient(135deg, #fff 0%, #fffbf0 100%);
                }

                .card-header-info {
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    margin-bottom: 1rem;
                    padding-bottom: 0.75rem;
                    border-bottom: 1px solid #e9ecef;
                }

                .staff-info-card {
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                }

                .staff-avatar-card {
                    width: 48px;
                    height: 48px;
                    border-radius: 50%;
                    object-fit: cover;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-weight: bold;
                    font-size: 18px;
                }

                .card-details {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 0.75rem;
                    margin-bottom: 1rem;
                }

                .detail-item {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    font-size: 0.875rem;
                }

                .detail-item i {
                    width: 20px;
                    color: #6c757d;
                }

                .card-actions {
                    display: flex;
                    justify-content: flex-end;
                    gap: 0.5rem;
                    padding-top: 0.75rem;
                    border-top: 1px solid #e9ecef;
                }

                .view-toggle-btn {
                    cursor: pointer;
                    transition: all 0.2s ease;
                }

                .view-toggle-btn.active {
                    background-color: #0d6efd;
                    color: white;
                    border-color: #0d6efd;
                }

                .view-toggle-btn.active i {
                    color: white;
                }

                /* Dynamic Stats Cards */
                .stat-card-clickable {
                    cursor: pointer;
                    transition: all 0.3s ease;
                }

                .stat-card-clickable:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
                }

                .stat-card-clickable.active-stat {
                    border: 2px solid #0d6efd;
                    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
                }
            </style>

            <div id="subjectVettingList">
                <!-- Term and Session Filter Row -->
                <div class="row mb-4 animate-fade-in-up">
                    <div class="col-12">
                        <div class="card filter-card">
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-5">
                                        <label for="term-filter-stats" class="form-label fw-semibold text-muted mb-2">
                                            <i class="ri-calendar-line me-1"></i> Select Term
                                        </label>
                                        <select class="form-select form-select-lg" id="term-filter-stats">
                                            <option value="">All Terms</option>
                                            @foreach ($terms as $term)
                                                <option value="{{ $term->id }}" class="term-{{ $term->term }}">
                                                    {{ $term->term }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label for="session-filter-stats" class="form-label fw-semibold text-muted mb-2">
                                            <i class="ri-calendar-event-line me-1"></i> Select Session
                                        </label>
                                        <select class="form-select form-select-lg" id="session-filter-stats">
                                            <option value="">All Sessions</option>
                                            @foreach ($sessions as $session)
                                                <option value="{{ $session->id }}" {{ $currentSession && $currentSession->id == $session->id ? 'selected' : '' }}>
                                                    {{ $session->session }} @if($session->status == 'Current') (Current) @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button class="btn btn-secondary w-100" id="reset-stats-btn">
                                            <i class="ri-refresh-line me-1"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Stats Cards Row -->
                <div class="row g-4 mb-4 animate-fade-in-up" id="statsCardsRow">
                    <div class="col-xl-3 col-md-6">
                        <div class="card stats-card stat-card-clickable" data-status="all">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1 text-uppercase fw-semibold fs-12">Total Assignments</p>
                                        <h2 class="mb-0 fw-bold" id="stat-total">0</h2>
                                        <p class="text-muted mb-0 mt-2 fs-13">
                                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                                <i class="ri-file-list-line me-1"></i>All Records
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
                        <div class="card stats-card stat-card-clickable" data-status="pending">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1 text-uppercase fw-semibold fs-12">Pending</p>
                                        <h2 class="mb-0 fw-bold text-danger" id="stat-pending">0</h2>
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
                        <div class="card stats-card stat-card-clickable" data-status="completed">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1 text-uppercase fw-semibold fs-12">Completed</p>
                                        <h2 class="mb-0 fw-bold text-success" id="stat-completed">0</h2>
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
                        <div class="card stats-card stat-card-clickable" data-status="rejected">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1 text-uppercase fw-semibold fs-12">Rejected</p>
                                        <h2 class="mb-0 fw-bold text-warning" id="stat-rejected">0</h2>
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

                <!-- Filter Card for Search and View Toggle -->
                <div class="row mb-4 animate-fade-in-up" style="animation-delay: 0.2s;">
                    <div class="col-lg-12">
                        <div class="card filter-card">
                            <div class="card-body">
                                <div class="row g-3 align-items-center">
                                    <div class="col-xxl-4">
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
                                            <label class="form-label text-muted mb-2 fw-semibold">View Mode</label>
                                            <div class="btn-group w-100" role="group">
                                                <button type="button" class="btn btn-light view-toggle-btn active" id="tableViewBtn">
                                                    <i class="ri-table-view me-1"></i> Table View
                                                </button>
                                                <button type="button" class="btn btn-light view-toggle-btn" id="cardViewBtn">
                                                    <i class="ri-layout-grid-line me-1"></i> Card View
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-5">
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
                <div class="row animate-fade-in-up table-view-container" style="animation-delay: 0.3s;">
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
                                    <table class="table table-hover align-middle mb-0" id="kt_subject_vetting_table">
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
                                                $rowStatusClass = match ($sv->status ?? 'pending') {
                                                    'completed' => 'table-row-completed',
                                                    'pending' => 'table-row-pending',
                                                    'rejected' => 'table-row-rejected',
                                                    default => ''
                                                };
                                                ?>
                                                <tr data-url="{{ route('subjectvetting.destroy', $sv->svid) }}"
                                                    data-id="{{ $sv->svid }}"
                                                    data-status="{{ $sv->status ?? 'pending' }}"
                                                    data-term="{{ $sv->termid }}"
                                                    data-session="{{ $sv->sessionid }}"
                                                    class="table-row-hover {{ $rowStatusClass }}">
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="chk_child" value="{{ $sv->svid }}" />
                                                        </div>
                                                    </td>
                                                    <td class="sn fw-bold">{{ ++$i }}</td>
                                                    <td class="vetting_username" data-vetting_userid="{{ $sv->vetting_userid }}" data-vetting-name="{{ $sv->vetting_username ?? 'N/A' }}">
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
                <div class="row card-view-container" id="cardViewContainer">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header bg-transparent pt-4 pb-0">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div>
                                        <h5 class="card-title mb-1 fw-bold">Subject Vetting Assignments</h5>
                                        <p class="text-muted mb-0">Card view of all subject vetting assignments</p>
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
                                <div id="cardsContainer" class="row g-3"></div>
                                <div class="row mt-4 align-items-center" id="card-pagination-element">
                                    <div class="col-sm">
                                        <div class="text-muted text-center text-sm-start">
                                            Showing <span id="card-showing-records">0</span> of <span id="card-total-records">{{ $subjectvettings->count() }}</span> Results
                                        </div>
                                    </div>
                                    <div class="col-sm-auto mt-3 mt-sm-0">
                                        <div class="pagination-wrap">
                                            <nav aria-label="Page navigation">
                                                <ul class="pagination card-pagination mb-0"></ul>
                                            </nav>
                                        </div>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    window.vettingStatusCounts = @json($statusCounts);
    window.currentSessionId = @json($currentSession ? $currentSession->id : null);
    window.subjectVettingsData = @json($subjectvettings);
    window.allSubjectVettings = @json($subjectvettings);
    console.log('Initial vettingStatusCounts:', window.vettingStatusCounts);
    console.log('Current Session ID:', window.currentSessionId);
</script>

@endsection
<script>
console.log("subjectvetting.init.js - FIXED & SIMPLIFIED VERSION");

let currentView = 'table';
let currentCardPage = 1;
const cardsPerPage = 9;

let currentTermFilter = '';
let currentSessionFilter = '';

let subjectVettingList = null;
let vettingStatusChart = null;

// ====================== UPDATE STATS & CHART ======================
function updateAll() {
    updateStatsFromTable();
    updateChartFromTable();

    if (currentView === 'card' && subjectVettingList) {
        setTimeout(() => renderCardView(subjectVettingList.items), 80);
    }
}

function updateStatsFromTable() {
    const visibleRows = document.querySelectorAll('#kt_subject_vetting_table tbody tr:not(.noresult):not([style*="display: none"])');

    let total = visibleRows.length;
    let pending = 0, completed = 0, rejected = 0;

    visibleRows.forEach(row => {
        const status = (row.getAttribute('data-status') || 'pending').toLowerCase().trim();
        if (status === 'pending') pending++;
        else if (status === 'completed') completed++;
        else if (status === 'rejected') rejected++;
    });

    // Update Stats Cards
    const totalEl = document.getElementById('stat-total');
    const pendingEl = document.getElementById('stat-pending');
    const completedEl = document.getElementById('stat-completed');
    const rejectedEl = document.getElementById('stat-rejected');

    if (totalEl) totalEl.textContent = total;
    if (pendingEl) pendingEl.textContent = pending;
    if (completedEl) completedEl.textContent = completed;
    if (rejectedEl) rejectedEl.textContent = rejected;

    // Safe footer update
    const totalFooter = document.getElementById('total-records-footer');
    const showingEl = document.getElementById('showing-records');
    if (totalFooter) totalFooter.textContent = total;
    if (showingEl) showingEl.textContent = Math.min(total, 10);
}

function updateChartFromTable() {
    if (!vettingStatusChart) return;

    const visibleRows = document.querySelectorAll('#kt_subject_vetting_table tbody tr:not(.noresult):not([style*="display: none"])');

    let pending = 0, completed = 0, rejected = 0;

    visibleRows.forEach(row => {
        const status = (row.getAttribute('data-status') || 'pending').toLowerCase().trim();
        if (status === 'pending') pending++;
        else if (status === 'completed') completed++;
        else if (status === 'rejected') rejected++;
    });

    vettingStatusChart.data.datasets[0].data = [pending, completed, rejected];
    vettingStatusChart.update('none');
}

// ====================== FILTER FUNCTION ======================
function filterTableByTermAndSession() {
    const rows = document.querySelectorAll('#kt_subject_vetting_table tbody tr:not(.noresult)');

    rows.forEach(row => {
        const rowTerm = row.getAttribute('data-term');
        const rowSession = row.getAttribute('data-session');

        let show = true;
        if (currentTermFilter && rowTerm !== currentTermFilter) show = false;
        if (currentSessionFilter && rowSession !== currentSessionFilter && show) show = false;

        row.style.display = show ? '' : 'none';
    });

    if (subjectVettingList) {
        subjectVettingList.update();
    }

    updateAll();
    updatePaginationVisibility();
}

function updatePaginationVisibility() {
    const visibleCount = document.querySelectorAll('#kt_subject_vetting_table tbody tr:not(.noresult):not([style*="display: none"])').length;
    const paginationEl = document.getElementById('pagination-element');
    if (paginationEl) paginationEl.style.display = visibleCount > 10 ? '' : 'none';
}

// ====================== CHART ======================
function initializeVettingStatusChart() {
    const ctx = document.getElementById('vettingStatusChart')?.getContext('2d');
    if (!ctx) return;

    if (vettingStatusChart) vettingStatusChart.destroy();

    vettingStatusChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Pending', 'Completed', 'Rejected'],
            datasets: [{
                label: 'Vetting Assignments',
                data: [0, 0, 0],
                backgroundColor: ['#dc3545', '#28a745', '#ffc107'],
                borderWidth: 1,
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } }
            },
            plugins: { legend: { display: false } }
        }
    });
}

// ====================== LIST.JS ======================
function initializeListJS() {
    if (subjectVettingList) subjectVettingList.clear();

    try {
        subjectVettingList = new List('subjectVettingList', {
            valueNames: ['sn', 'vetting_username', 'subjectname', 'sclass', 'schoolarm', 'teachername', 'termname', 'sessionname', 'status', 'datereg'],
            page: 10,
            pagination: { paginationClass: "listjs-pagination" }
        });

        subjectVettingList.on('updated', function () {
            updateAll();
        });

        console.log("List.js initialized successfully");
    } catch (e) {
        console.error("List.js initialization failed:", e);
    }
}

// ====================== CARD VIEW ======================
function renderCardView(items = []) {
    const container = document.getElementById('cardsContainer');
    if (!container) return;

    const visibleItems = items.filter(item => {
        const row = item.elm;
        return row && row.style.display !== 'none';
    });

    container.innerHTML = '';

    if (visibleItems.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="ri-inbox-line fs-48 text-muted"></i>
                <h5 class="mt-3">No Subject Vetting Assignments Found</h5>
            </div>`;
        return;
    }

    const start = (currentCardPage - 1) * cardsPerPage;
    const currentItems = visibleItems.slice(start, start + cardsPerPage);

    currentItems.forEach(item => {
        const status = (item._values.status || 'Pending').toLowerCase();
        const statusClass = status.includes('completed') ? 'completed' :
                           (status.includes('pending') ? 'pending' : 'rejected');
        const icon = status.includes('completed') ? 'ri-checkbox-circle-line' :
                    (status.includes('pending') ? 'ri-time-line' : 'ri-close-circle-line');

        const html = `
            <div class="col-md-6 col-xl-4">
                <div class="vetting-card ${statusClass}-card">
                    <div class="card-header-info">
                        <div class="staff-info-card">
                            <div class="staff-avatar-card">${(item._values.vetting_username || 'U')[0].toUpperCase()}</div>
                            <div><h6 class="mb-0">${item._values.vetting_username || 'N/A'}</h6></div>
                        </div>
                        <span class="badge-status ${statusClass === 'pending' ? 'badge-pending' : statusClass === 'completed' ? 'badge-completed' : 'badge-rejected'}">
                            <i class="${icon} me-1"></i>${item._values.status || 'Pending'}
                        </span>
                    </div>
                    <div class="card-details">
                        <div class="detail-item"><i class="ri-book-open-line"></i> <strong>Subject:</strong> ${item._values.subjectname || 'N/A'}</div>
                        <div class="detail-item"><i class="ri-group-line"></i> <strong>Class:</strong> ${item._values.sclass || 'N/A'}</div>
                        <div class="detail-item"><i class="ri-user-line"></i> <strong>Teacher:</strong> ${item._values.teachername || 'N/A'}</div>
                        <div class="detail-item"><i class="ri-calendar-line"></i> <strong>Term:</strong> ${item._values.termname || 'N/A'}</div>
                        <div class="detail-item"><i class="ri-calendar-event-line"></i> <strong>Session:</strong> ${item._values.sessionname || 'N/A'}</div>
                    </div>
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
    });
}

// ====================== FILTERS ======================
function initializeTermAndSessionFilters() {
    const termFilter = document.getElementById('term-filter-stats');
    const sessionFilter = document.getElementById('session-filter-stats');
    const resetBtn = document.getElementById('reset-stats-btn');

    if (termFilter) termFilter.addEventListener('change', () => {
        currentTermFilter = termFilter.value;
        filterTableByTermAndSession();
    });

    if (sessionFilter) sessionFilter.addEventListener('change', () => {
        currentSessionFilter = sessionFilter.value;
        filterTableByTermAndSession();
    });

    if (resetBtn) resetBtn.addEventListener('click', () => {
        currentTermFilter = currentSessionFilter = '';
        if (termFilter) termFilter.value = '';
        if (sessionFilter) sessionFilter.value = '';
        filterTableByTermAndSession();
    });
}

// ====================== VIEW TOGGLE ======================
function initializeViewToggle() {
    const tableBtn = document.getElementById('tableViewBtn');
    const cardBtn = document.getElementById('cardViewBtn');
    const tableContainer = document.querySelector('.table-view-container');
    const cardContainer = document.getElementById('cardViewContainer');

    if (!tableBtn || !cardBtn) return;

    tableBtn.addEventListener('click', () => {
        currentView = 'table';
        tableBtn.classList.add('active');
        cardBtn.classList.remove('active');
        tableContainer.classList.remove('hide');
        cardContainer.classList.remove('active');
    });

    cardBtn.addEventListener('click', () => {
        currentView = 'card';
        cardBtn.classList.add('active');
        tableBtn.classList.remove('active');
        tableContainer.classList.add('hide');
        cardContainer.classList.add('active');
        if (subjectVettingList) renderCardView(subjectVettingList.items);
    });
}

// ====================== DOM READY ======================
document.addEventListener('DOMContentLoaded', function () {
    initializeListJS();
    initializeVettingStatusChart();
    initializeTermAndSessionFilters();
    initializeViewToggle();

    // Initial update
    setTimeout(() => {
        updateAll();
    }, 300);

    // Search input
    const searchInput = document.querySelector(".search-box input.search");
    if (searchInput && subjectVettingList) {
        searchInput.addEventListener("input", () => {
            subjectVettingList.search(searchInput.value);
            setTimeout(updateAll, 50);
        });
    }

    console.log("✅ Subject Vetting initialized with fixed card updates");
});
</script>
