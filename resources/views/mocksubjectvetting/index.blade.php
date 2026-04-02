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
                    box-shadow: 0 0 0 0.25rem rgba(13,110,253,0.25);
                }
                #mockSubjectClassSelectionSummary {
                    font-size: 0.875rem;
                    border-left: 3px solid #0d6efd;
                    margin-top: 10px;
                }
                #mockClearSelectionBtn {
                    text-decoration: none;
                    font-size: 0.875rem;
                    cursor: pointer;
                }
                #mockClearSelectionBtn:hover { text-decoration: underline; }

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
                    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.02);
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
                .badge-status { padding: 6px 12px; border-radius: 20px; font-weight: 500; font-size: 11px; }
                .badge-pending   { background-color: #ffe5e5; color: #dc3545; }
                .badge-completed { background-color: #e3f5ec; color: #28a745; }
                .badge-rejected  { background-color: #fff4e5; color: #ffc107; }

                /* Table Row Status */
                .table-row-pending   { background-color: #fff5f5 !important; border-left: 3px solid #dc3545; }
                .table-row-completed { background-color: #f0fff4 !important; border-left: 3px solid #28a745; }
                .table-row-rejected  { background-color: #fffbf0 !important; border-left: 3px solid #ffc107; }
                .table-row-hover { transition: all 0.2s ease; }
                .table-row-hover:hover { transform: scale(1.01); box-shadow: 0 2px 8px rgba(0,0,0,0.05); }

                /* Action Buttons */
                .action-btn {
                    width: 32px; height: 32px;
                    display: inline-flex; align-items: center; justify-content: center;
                    border-radius: 8px; transition: all 0.2s ease;
                }
                .action-btn:hover { transform: scale(1.1); }

                /* Session Badge */
                .session-badge {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white; padding: 8px 16px; border-radius: 12px; font-weight: 500;
                }

                /* Filter Card */
                .filter-card {
                    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
                    border: none; border-radius: 1rem;
                }

                /* Animations */
                @keyframes fadeInUp {
                    from { opacity: 0; transform: translateY(20px); }
                    to   { opacity: 1; transform: translateY(0); }
                }
                .animate-fade-in-up { animation: fadeInUp 0.5s ease-out; }

                /* Card View */
                .card-view-container { display: none; }
                .card-view-container.active { display: block; }
                .table-view-container { display: block; }
                .table-view-container.hide { display: none; }

                .vetting-card {
                    background: white; border-radius: 1rem; padding: 1.25rem;
                    margin-bottom: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                    transition: all 0.3s ease; border-left: 4px solid; cursor: pointer;
                }
                .vetting-card:hover { transform: translateY(-3px); box-shadow: 0 4px 15px rgba(0,0,0,0.12); }
                .vetting-card.pending-card   { border-left-color: #dc3545; background: linear-gradient(135deg,#fff 0%,#fff5f5 100%); }
                .vetting-card.completed-card { border-left-color: #28a745; background: linear-gradient(135deg,#fff 0%,#f0fff4 100%); }
                .vetting-card.rejected-card  { border-left-color: #ffc107; background: linear-gradient(135deg,#fff 0%,#fffbf0 100%); }

                .card-header-info {
                    display: flex; justify-content: space-between; align-items: flex-start;
                    margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid #e9ecef;
                }
                .staff-info-card { display: flex; align-items: center; gap: 0.75rem; }
                .staff-avatar-card {
                    width: 48px; height: 48px; border-radius: 50%;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    display: flex; align-items: center; justify-content: center;
                    color: white; font-weight: bold; font-size: 18px;
                }
                .card-details {
                    display: grid; grid-template-columns: repeat(auto-fit,minmax(200px,1fr));
                    gap: 0.75rem; margin-bottom: 1rem;
                }
                .detail-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; }
                .detail-item i { width: 20px; color: #6c757d; }
                .card-actions {
                    display: flex; justify-content: flex-end; gap: 0.5rem;
                    padding-top: 0.75rem; border-top: 1px solid #e9ecef;
                }

                /* View toggle */
                .view-toggle-btn { cursor: pointer; transition: all 0.2s ease; }
                .view-toggle-btn.active { background-color: #0d6efd; color: white; border-color: #0d6efd; }
                .view-toggle-btn.active i { color: white; }

                /* Stat card clickable */
                .stat-card-clickable { cursor: pointer; transition: all 0.3s ease; }
                .stat-card-clickable:hover { transform: translateY(-5px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
                .stat-card-clickable.active-stat { border: 2px solid #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,0.1); }

                /* Term colors */
                .term-first  { color: #198754 !important; font-weight: 500; }
                .term-second { color: #0d6efd !important; font-weight: 500; }
                .term-third  { color: #ffc107 !important; font-weight: 500; }

                /* Checkbox group */
                .subject-class-checkbox-group {
                    max-height: 300px; overflow-y: auto;
                    border: 1px solid #dee2e6; border-radius: 0.375rem;
                    padding: 15px; background-color: #f8f9fa;
                }
                .no-results-message {
                    display: none; text-align: center; padding: 20px;
                    color: #6c757d; font-style: italic;
                    background-color: #f8f9fa; border-radius: 0.375rem; margin-bottom: 10px;
                }
                .current-session-item { margin-bottom: 8px; }
                .non-current-session { opacity: 0.6; pointer-events: none; }
                .non-current-session input[type="checkbox"] { display: none !important; }
                .non-current-session label { color: #6c757d !important; cursor: not-allowed; }
            </style>

            <div id="mockSubjectVettingList">

                <!-- Term and Session Filter Row -->
                <div class="row mb-4 animate-fade-in-up">
                    <div class="col-12">
                        <div class="card filter-card">
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-5">
                                        <label for="mock-term-filter-stats" class="form-label fw-semibold text-muted mb-2">
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
                                        <label for="mock-session-filter-stats" class="form-label fw-semibold text-muted mb-2">
                                            <i class="ri-calendar-event-line me-1"></i> Select Session
                                        </label>
                                        <select class="form-select form-select-lg" id="mock-session-filter-stats">
                                            <option value="">All Sessions</option>
                                            @foreach ($sessions as $session)
                                                <option value="{{ $session->id }}">
                                                    {{ $session->session }}
                                                </option>
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
                                        <h2 class="mb-0 fw-bold text-danger" id="mock-stat-pending">0</h2>
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
                                        <h2 class="mb-0 fw-bold text-success" id="mock-stat-completed">0</h2>
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
                                        <h2 class="mb-0 fw-bold text-warning" id="mock-stat-rejected">0</h2>
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

                <!-- Bar Chart -->
                <div class="row mb-4 animate-fade-in-up" style="animation-delay:0.1s;">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header bg-transparent border-0 pt-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5 class="card-title mb-1 fw-bold">Mock Vetting Status Distribution</h5>
                                        <p class="text-muted mb-0">Overview of all mock vetting assignments by status</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="position:relative;height:320px;width:100%;">
                                    <canvas id="mockVettingStatusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search & View Toggle -->
                <div class="row mb-4 animate-fade-in-up" style="animation-delay:0.2s;">
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
                                                <button type="button" class="btn btn-light view-toggle-btn active" id="mockTableViewBtn">
                                                    <i class="ri-table-view me-1"></i> Table View
                                                </button>
                                                <button type="button" class="btn btn-light view-toggle-btn" id="mockCardViewBtn">
                                                    <i class="ri-layout-grid-line me-1"></i> Card View
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-5">
                                        <div class="alert alert-light border-0 mb-0 shadow-sm">
                                            <div class="d-flex align-items-center">
                                                <i class="ri-information-line text-primary fs-18 me-2"></i>
                                                <div>Mock Subject Vetting assignments management</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table View -->
                <div class="row animate-fade-in-up table-view-container" style="animation-delay:0.3s;">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header bg-transparent pt-4 pb-0">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div>
                                        <h5 class="card-title mb-1 fw-bold">Mock Subject Vetting Assignments</h5>
                                        <p class="text-muted mb-0">Manage and monitor all mock subject vetting assignments</p>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()">
                                            <i class="ri-delete-bin-2-line me-1"></i> Delete Selected
                                        </button>
                                        @can('Create mock-subject-vettings')
                                            <button type="button" class="btn btn-primary add-btn"
                                                data-bs-toggle="modal" data-bs-target="#addMockSubjectVettingModal"
                                                id="create-mock-subject-vettings-btn">
                                                <i class="ri-add-line me-1"></i> Create Assignment
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-4">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" id="kt_mock_subject_vetting_table">
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
                                            @forelse ($mocksubjectvettings as $sv)
                                                @php
                                                    $picture = $sv->vetting_picture ?? 'unnamed.jpg';
                                                    $imagePath = asset('storage/staff_avatars/' . $picture);
                                                    $fileExists = file_exists(storage_path('app/public/staff_avatars/' . $picture));
                                                    $defaultImageExists = file_exists(storage_path('app/public/staff_avatars/unnamed.jpg'));
                                                    $statusClass = match ($sv->status ?? 'pending') {
                                                        'completed' => 'badge-completed',
                                                        'pending'   => 'badge-pending',
                                                        'rejected'  => 'badge-rejected',
                                                        default     => 'badge-pending'
                                                    };
                                                    $statusIcon = match ($sv->status ?? 'pending') {
                                                        'completed' => 'ri-checkbox-circle-line',
                                                        'pending'   => 'ri-time-line',
                                                        'rejected'  => 'ri-close-circle-line',
                                                        default     => 'ri-time-line'
                                                    };
                                                    $rowStatusClass = match ($sv->status ?? 'pending') {
                                                        'completed' => 'table-row-completed',
                                                        'pending'   => 'table-row-pending',
                                                        'rejected'  => 'table-row-rejected',
                                                        default     => ''
                                                    };
                                                @endphp
                                                <tr data-url="{{ route('mocksubjectvetting.destroy', $sv->svid) }}"
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
                                                    <td class="vetting_username"
                                                        data-vetting_userid="{{ $sv->vetting_userid }}"
                                                        data-vetting-name="{{ $sv->vetting_username ?? 'N/A' }}">
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
                                                                        style="width:38px;height:38px;object-fit:cover;cursor:pointer;"
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
                                                        <p class="text-muted">No assignments have been created yet.</p>
                                                        @can('Create mock-subject-vettings')
                                                            <button type="button" class="btn btn-primary add-btn mt-2"
                                                                data-bs-toggle="modal" data-bs-target="#addMockSubjectVettingModal">
                                                                <i class="ri-add-line me-1"></i> Create Your First Assignment
                                                            </button>
                                                        @endcan
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
                                            Showing <span id="showing-records">0</span> of <span id="total-records-footer">{{ $mocksubjectvettings->count() }}</span> Results
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
                <div class="row card-view-container" id="mockCardViewContainer">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header bg-transparent pt-4 pb-0">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div>
                                        <h5 class="card-title mb-1 fw-bold">Mock Subject Vetting Assignments</h5>
                                        <p class="text-muted mb-0">Card view of all mock subject vetting assignments</p>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @can('Create mock-subject-vettings')
                                            <button type="button" class="btn btn-primary add-btn"
                                                data-bs-toggle="modal" data-bs-target="#addMockSubjectVettingModal">
                                                <i class="ri-add-line me-1"></i> Create Assignment
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-4">
                                <div id="mockCardsContainer" class="row g-3"></div>
                                <div class="row mt-4 align-items-center" id="mock-card-pagination-element">
                                    <div class="col-sm">
                                        <div class="text-muted text-center text-sm-start">
                                            Showing <span id="mock-card-showing-records">0</span> of <span id="mock-card-total-records">{{ $mocksubjectvettings->count() }}</span> Results
                                        </div>
                                    </div>
                                    <div class="col-sm-auto mt-3 mt-sm-0">
                                        <div class="pagination-wrap">
                                            <nav aria-label="Page navigation">
                                                <ul class="pagination mock-card-pagination mb-0"></ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Modal -->
                <div id="addMockSubjectVettingModal" class="modal fade" tabindex="-1"
                    aria-labelledby="addMockModalLabel" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered modal-xl">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 id="addMockModalLabel" class="modal-title">
                                    <i class="ri-add-circle-line me-2"></i>Add Mock Subject Vetting Assignment
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form class="tablelist-form" autocomplete="off" id="add-mocksubjectvetting-form">
                                <div class="modal-body">
                                    <input type="hidden" id="mock-add-id-field" name="id">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="mock-userid" class="form-label fw-semibold">Vetting Staff <span class="text-danger">*</span></label>
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
                                                <label for="mock-sessionid" class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
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
                                        <div class="checkbox-group p-3 bg-light rounded" style="max-height:100px;overflow-y:auto;">
                                            @foreach ($terms as $term)
                                                <div class="form-check form-check-inline me-3">
                                                    <input class="form-check-input modal-checkbox" type="checkbox"
                                                        name="termid[]" id="mock-add-term-{{ $term->id }}" value="{{ $term->id }}">
                                                    <label class="form-check-label" for="mock-add-term-{{ $term->id }}">
                                                        {{ $term->term }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Subject-Class Assignments <span class="text-danger">*</span></label>
                                        <div class="subject-class-search-box">
                                            <input type="text" class="form-control" id="mockSubjectClassSearch"
                                                placeholder="Search by subject, class, teacher, or session...">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                        <div class="no-results-message" id="mockNoResultsMessage">
                                            <i class="ri-search-eye-line me-2"></i>No matching subject-class assignments found.
                                        </div>
                                        <div class="subject-class-checkbox-group" id="mockSubjectClassList">
                                            @foreach ($subjectclasses as $sc)
                                                @php
                                                    $checkboxId = "mock-add-subjectclass-{$sc->scid}";
                                                    $termColor  = '';
                                                    $termId     = $sc->termid ?? 0;
                                                    if ($termId == 1)      $termColor = 'term-first';
                                                    elseif ($termId == 2)  $termColor = 'term-second';
                                                    elseif ($termId == 3)  $termColor = 'term-third';

                                                    $displayText = ($sc->subjectname ?? 'N/A') .
                                                                   ($sc->subjectcode ? ' (' . $sc->subjectcode . ')' : '') .
                                                                   ' - ' . ($sc->sclass ?? 'N/A') .
                                                                   ($sc->schoolarm ? ' (' . $sc->schoolarm . ')' : '') .
                                                                   ' - ' . ($sc->teachername ?? 'N/A') .
                                                                   ' -- ' . ($sc->sessionname ?? 'N/A') .
                                                                   ' -- ' . ($sc->termname ?? 'N/A');
                                                    $searchableText = strtolower(
                                                        ($sc->subjectname ?? '') . ' ' .
                                                        ($sc->subjectcode ?? '') . ' ' .
                                                        ($sc->sclass ?? '') . ' ' .
                                                        ($sc->schoolarm ?? '') . ' ' .
                                                        ($sc->teachername ?? '') . ' ' .
                                                        ($sc->sessionname ?? '') . ' ' .
                                                        ($sc->termname ?? '')
                                                    );
                                                @endphp
                                                <div class="form-check subject-class-item current-session-item"
                                                    data-search="{{ $searchableText }}">
                                                    <input class="form-check-input modal-checkbox"
                                                        type="checkbox"
                                                        name="subjectclassid[]"
                                                        id="{{ $checkboxId }}"
                                                        value="{{ $sc->scid }}"
                                                        data-termid="{{ $sc->termid }}">
                                                    <label class="form-check-label {{ $termColor }}" for="{{ $checkboxId }}">
                                                        {{ $displayText }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="alert alert-light mt-3 p-2 border" id="mockSubjectClassSelectionSummary">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>
                                                    <i class="ri-checkbox-line me-1"></i>
                                                    Selected: <strong id="mockSelectedCount">0</strong> of
                                                    <span id="mockTotalCount">{{ count($subjectclasses) }}</span> items
                                                </span>
                                                <button type="button" class="btn btn-sm btn-link p-0" id="mockClearSelectionBtn">Clear All</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alert alert-danger d-none" id="mock-alert-error-msg"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                        <i class="ri-close-line me-1"></i>Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary" id="mock-add-btn">
                                        <i class="ri-save-line me-1"></i>Add Assignment
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Edit Modal -->
                <div id="editMockModal" class="modal fade" tabindex="-1"
                    aria-labelledby="editMockModalLabel" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-warning text-dark">
                                <h5 id="editMockModalLabel" class="modal-title">
                                    <i class="ri-edit-line me-2"></i>Edit Mock Subject Vetting Assignment
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form class="tablelist-form" autocomplete="off" id="edit-mocksubjectvetting-form">
                                <div class="modal-body">
                                    <input type="hidden" id="mock-edit-id-field" name="id">
                                    <div class="mb-3">
                                        <label for="mock-edit-userid" class="form-label fw-semibold">Vetting Staff <span class="text-danger">*</span></label>
                                        <select name="userid" id="mock-edit-userid" class="form-select select2" required>
                                            <option value="">Select Staff</option>
                                            @foreach ($staff as $staff_member)
                                                <option value="{{ $staff_member->id }}">{{ $staff_member->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="mock-edit-termid" class="form-label fw-semibold">Term <span class="text-danger">*</span></label>
                                        <select name="termid" id="mock-edit-termid" class="form-select" required>
                                            <option value="">Select Term</option>
                                            @foreach ($terms as $term)
                                                <option value="{{ $term->id }}">{{ $term->term }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="mock-edit-sessionid" class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
                                        <select name="sessionid" id="mock-edit-sessionid" class="form-select" required>
                                            <option value="">Select Session</option>
                                            @foreach ($sessions as $session)
                                                <option value="{{ $session->id }}">{{ $session->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="mock-edit-subjectclassid" class="form-label fw-semibold">Subject-Class <span class="text-danger">*</span></label>
                                        <select name="subjectclassid" id="mock-edit-subjectclassid" class="form-select" required>
                                            <option value="">Select Subject-Class</option>
                                            @foreach ($subjectclasses as $sc)
                                                @php
                                                    $termColor = '';
                                                    $termId    = $sc->termid ?? 0;
                                                    if ($termId == 1)     $termColor = 'term-first';
                                                    elseif ($termId == 2) $termColor = 'term-second';
                                                    elseif ($termId == 3) $termColor = 'term-third';
                                                    $displayText = ($sc->subjectname ?? 'N/A') .
                                                                   ($sc->subjectcode ? ' (' . $sc->subjectcode . ')' : '') .
                                                                   ' - ' . ($sc->sclass ?? 'N/A') .
                                                                   ($sc->schoolarm ? ' (' . $sc->schoolarm . ')' : '') .
                                                                   ' - ' . ($sc->teachername ?? 'N/A') .
                                                                   ' -- ' . ($sc->sessionname ?? 'N/A') .
                                                                   ' -- ' . ($sc->termname ?? 'N/A');
                                                @endphp
                                                <option value="{{ $sc->scid }}" class="{{ $termColor }}">{{ $displayText }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="mock-edit-status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                        <select name="status" id="mock-edit-status" class="form-select" required>
                                            <option value="pending">Pending</option>
                                            <option value="completed">Completed</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                    </div>
                                    <div class="alert alert-danger d-none" id="mock-edit-alert-error-msg"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                        <i class="ri-close-line me-1"></i>Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary" id="mock-update-btn">
                                        <i class="ri-save-line me-1"></i>Update
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Delete Confirmation Modal -->
                <div id="deleteRecordModal" class="modal fade" tabindex="-1" aria-hidden="true">
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
                <div id="imageViewModal" class="modal fade" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Staff Image Preview</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img id="preview-image" src="" alt="Staff Image"
                                    class="img-fluid rounded-circle"
                                    style="width:150px;height:150px;object-fit:cover;" />
                                <p id="preview-teachername" class="mt-3 fw-bold mb-0"></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- end #mockSubjectVettingList --}}
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    window.mockVettingStatusCounts = @json($statusCounts);
    window.mockSubjectVettingsData = @json($mocksubjectvettings);
    console.log('mockVettingStatusCounts:', window.mockVettingStatusCounts);


    /**
 * mocksubjectvetting.init.js
 * Mock Subject Vetting Management — Complete Script
 * Mirrors subjectvetting.init.js implementation exactly.
 * Fix: Stats + chart read from List.js matchingItems (all pages),
 *      filter uses List.js .filter() instead of DOM row hiding,
 *      pagination works correctly across all pages.
 */

'use strict';

// ─── State ────────────────────────────────────────────────────────────────────
let mockCurrentView          = 'table';
let mockCurrentCardPage      = 1;
const mockCardsPerPage       = 9;
let mockCurrentTermFilter    = '';
let mockCurrentSessionFilter = '';
let mockSubjectVettingList   = null;
let mockVettingStatusChart   = null;
let mockDeleteId             = null;

// ─── Stats (reads ALL matchingItems, not just visible page) ──────────────────
function mockUpdateStatsFromList() {
    if (!mockSubjectVettingList) return;

    const items = mockSubjectVettingList.matchingItems;
    let total = items.length, pending = 0, completed = 0, rejected = 0;

    items.forEach(item => {
        const status = (item.elm.getAttribute('data-status') || 'pending').toLowerCase().trim();
        if (status === 'pending')        pending++;
        else if (status === 'completed') completed++;
        else if (status === 'rejected')  rejected++;
    });

    document.getElementById('mock-stat-total').textContent     = total;
    document.getElementById('mock-stat-pending').textContent   = pending;
    document.getElementById('mock-stat-completed').textContent = completed;
    document.getElementById('mock-stat-rejected').textContent  = rejected;

    const showingEl = document.getElementById('showing-records');
    const totalEl   = document.getElementById('total-records-footer');
    if (showingEl) showingEl.textContent = Math.min(total, 10);
    if (totalEl)   totalEl.textContent   = total;

    mockUpdateChart(pending, completed, rejected);
    if (mockCurrentView === 'card') mockRenderCardView();
}

// ─── Chart ────────────────────────────────────────────────────────────────────
function mockUpdateChart(pending, completed, rejected) {
    if (!mockVettingStatusChart) return;
    mockVettingStatusChart.data.datasets[0].data = [pending, completed, rejected];
    mockVettingStatusChart.update('none');
}

function mockInitializeChart() {
    const ctx = document.getElementById('mockVettingStatusChart')?.getContext('2d');
    if (!ctx) return;
    if (mockVettingStatusChart) mockVettingStatusChart.destroy();

    mockVettingStatusChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Pending', 'Completed', 'Rejected'],
            datasets: [{
                label: 'Mock Vetting Assignments',
                data: [0, 0, 0],
                backgroundColor: ['#dc3545', '#28a745', '#ffc107'],
                borderWidth: 1,
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
            plugins: { legend: { display: false } }
        }
    });
}

// ─── List.js Filter (term + session) ─────────────────────────────────────────
function mockApplyListFilter() {
    if (!mockSubjectVettingList) return;

    if (!mockCurrentTermFilter && !mockCurrentSessionFilter) {
        mockSubjectVettingList.filter(); // clear — show all
    } else {
        mockSubjectVettingList.filter(item => {
            const el         = item.elm;
            const rowTerm    = el.getAttribute('data-term')    || '';
            const rowSession = el.getAttribute('data-session') || '';
            const termOk    = !mockCurrentTermFilter    || rowTerm    === mockCurrentTermFilter;
            const sessionOk = !mockCurrentSessionFilter || rowSession === mockCurrentSessionFilter;
            return termOk && sessionOk;
        });
    }

    mockCurrentCardPage = 1;
}

// ─── List.js Init ─────────────────────────────────────────────────────────────
function mockInitializeListJS() {
    try {
        mockSubjectVettingList = new List('mockSubjectVettingList', {
            valueNames: [
                'sn', 'vetting_username', 'subjectname', 'sclass',
                'schoolarm', 'teachername', 'termname', 'sessionname',
                'status', 'datereg'
            ],
            page: 10,
            pagination: { paginationClass: 'listjs-pagination' }
        });

        mockSubjectVettingList.on('updated', mockUpdateStatsFromList);
        console.log('✅ Mock List.js initialized');
    } catch (e) {
        console.error('Mock List.js init failed:', e);
    }
}

// ─── Card View ────────────────────────────────────────────────────────────────
function mockRenderCardView() {
    const container = document.getElementById('mockCardsContainer');
    if (!container || !mockSubjectVettingList) return;

    const items = mockSubjectVettingList.matchingItems;
    container.innerHTML = '';

    if (!items.length) {
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="ri-inbox-line fs-48 text-muted"></i>
                <h5 class="mt-3">No Mock Subject Vetting Assignments Found</h5>
            </div>`;
        document.getElementById('mock-card-showing-records').textContent = 0;
        document.getElementById('mock-card-total-records').textContent   = 0;
        mockRenderCardPagination(0, 0);
        return;
    }

    const totalPages = Math.ceil(items.length / mockCardsPerPage);
    if (mockCurrentCardPage > totalPages) mockCurrentCardPage = totalPages || 1;

    const start     = (mockCurrentCardPage - 1) * mockCardsPerPage;
    const pageItems = items.slice(start, start + mockCardsPerPage);

    pageItems.forEach(item => {
        const row        = item.elm;
        const vettingName = row.querySelector('.vetting_username h6')?.textContent.trim() || 'N/A';
        const subject    = row.querySelector('.subjectname .fw-medium')?.textContent.trim() || 'N/A';
        const subjectCode = row.querySelector('.subjectname small')?.textContent.trim()    || '';
        const sclass     = row.querySelector('.sclass')?.textContent.trim()     || 'N/A';
        const arm        = row.querySelector('.schoolarm')?.textContent.trim()  || '';
        const teacher    = row.querySelector('.teachername')?.textContent.trim()|| 'N/A';
        const term       = row.querySelector('.termname')?.textContent.trim()   || 'N/A';
        const session    = row.querySelector('.sessionname')?.textContent.trim()|| 'N/A';
        const statusText = row.querySelector('.status span')?.textContent.trim()|| 'Pending';
        const updated    = row.querySelector('.datereg small')?.textContent.trim() || 'N/A';

        const statusLower = statusText.toLowerCase();
        const statusClass = statusLower.includes('completed') ? 'completed'
                          : statusLower.includes('pending')   ? 'pending' : 'rejected';
        const icon = statusLower.includes('completed') ? 'ri-checkbox-circle-line'
                   : statusLower.includes('pending')   ? 'ri-time-line' : 'ri-close-circle-line';
        const badgeClass = statusClass === 'completed' ? 'badge-completed'
                         : statusClass === 'pending'   ? 'badge-pending' : 'badge-rejected';

        const svid           = row.getAttribute('data-id')    || '';
        const deleteUrl      = row.getAttribute('data-url')   || '';
        const vettingUserId  = row.querySelector('.vetting_username')?.getAttribute('data-vetting_userid') || '';
        const subjectclassid = row.querySelector('.subjectname')?.getAttribute('data-subjectclassid')      || '';
        const termid         = row.querySelector('.termname')?.getAttribute('data-termid')                 || '';
        const sessionid      = row.querySelector('.sessionname')?.getAttribute('data-sessionid')           || '';

        container.insertAdjacentHTML('beforeend', `
            <div class="col-md-6 col-xl-4">
                <div class="vetting-card ${statusClass}-card"
                     data-id="${svid}" data-url="${deleteUrl}"
                     data-status="${statusClass}" data-term="${termid}" data-session="${sessionid}">
                    <div class="card-header-info">
                        <div class="staff-info-card">
                            <div class="staff-avatar-card">${vettingName.charAt(0).toUpperCase()}</div>
                            <div>
                                <h6 class="mb-0">${vettingName}</h6>
                                <small class="text-muted">Vetting Staff</small>
                            </div>
                        </div>
                        <span class="badge-status ${badgeClass}">
                            <i class="${icon} me-1"></i>${statusText}
                        </span>
                    </div>
                    <div class="card-details">
                        <div class="detail-item">
                            <i class="ri-book-open-line"></i>
                            <span><strong>Subject:</strong> ${subject}${subjectCode ? ` <small class="text-muted">(${subjectCode})</small>` : ''}</span>
                        </div>
                        <div class="detail-item">
                            <i class="ri-group-line"></i>
                            <span><strong>Class:</strong> ${sclass}${arm ? ` (${arm})` : ''}</span>
                        </div>
                        <div class="detail-item">
                            <i class="ri-user-line"></i>
                            <span><strong>Teacher:</strong> ${teacher}</span>
                        </div>
                        <div class="detail-item">
                            <i class="ri-calendar-line"></i>
                            <span><strong>Term:</strong> ${term}</span>
                        </div>
                        <div class="detail-item">
                            <i class="ri-calendar-event-line"></i>
                            <span><strong>Session:</strong> ${session}</span>
                        </div>
                        <div class="detail-item">
                            <i class="ri-time-line"></i>
                            <span><strong>Updated:</strong> ${updated}</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-light btn-sm action-btn mock-card-edit-btn"
                                data-id="${svid}"
                                data-vetting_userid="${vettingUserId}"
                                data-subjectclassid="${subjectclassid}"
                                data-termid="${termid}"
                                data-sessionid="${sessionid}"
                                data-status="${statusClass}"
                                title="Edit">
                            <i class="ri-pencil-line"></i>
                        </button>
                        <button class="btn btn-light btn-sm action-btn text-danger mock-card-delete-btn"
                                data-id="${svid}"
                                data-url="${deleteUrl}"
                                title="Delete">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </div>
            </div>`);
    });

    document.getElementById('mock-card-showing-records').textContent = pageItems.length;
    document.getElementById('mock-card-total-records').textContent   = items.length;
    mockRenderCardPagination(items.length, totalPages);
}

function mockRenderCardPagination(total, totalPages) {
    const ul = document.querySelector('.mock-card-pagination');
    if (!ul) return;
    ul.innerHTML = '';
    if (totalPages <= 1) return;

    const prev = document.createElement('li');
    prev.className = `page-item${mockCurrentCardPage === 1 ? ' disabled' : ''}`;
    prev.innerHTML = `<a class="page-link" href="#">&laquo;</a>`;
    prev.addEventListener('click', e => {
        e.preventDefault();
        if (mockCurrentCardPage > 1) { mockCurrentCardPage--; mockRenderCardView(); }
    });
    ul.appendChild(prev);

    for (let i = 1; i <= totalPages; i++) {
        const li = document.createElement('li');
        li.className = `page-item${i === mockCurrentCardPage ? ' active' : ''}`;
        li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
        li.addEventListener('click', e => {
            e.preventDefault();
            mockCurrentCardPage = i;
            mockRenderCardView();
        });
        ul.appendChild(li);
    }

    const next = document.createElement('li');
    next.className = `page-item${mockCurrentCardPage === totalPages ? ' disabled' : ''}`;
    next.innerHTML = `<a class="page-link" href="#">&raquo;</a>`;
    next.addEventListener('click', e => {
        e.preventDefault();
        if (mockCurrentCardPage < totalPages) { mockCurrentCardPage++; mockRenderCardView(); }
    });
    ul.appendChild(next);
}

// ─── Term & Session Filters ───────────────────────────────────────────────────
function mockInitializeTermAndSessionFilters() {
    const termFilter    = document.getElementById('mock-term-filter-stats');
    const sessionFilter = document.getElementById('mock-session-filter-stats');
    const resetBtn      = document.getElementById('mock-reset-stats-btn');

    if (termFilter) {
        termFilter.addEventListener('change', () => {
            mockCurrentTermFilter = termFilter.value;
            mockApplyListFilter();
        });
    }
    if (sessionFilter) {
        sessionFilter.addEventListener('change', () => {
            mockCurrentSessionFilter = sessionFilter.value;
            mockApplyListFilter();
        });
    }
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            mockCurrentTermFilter = mockCurrentSessionFilter = '';
            if (termFilter)    termFilter.value    = '';
            if (sessionFilter) sessionFilter.value = '';
            mockApplyListFilter();
        });
    }
}

// ─── Stat Card Click Filter ───────────────────────────────────────────────────
function mockInitializeStatCardFilters() {
    document.querySelectorAll('#mockStatsCardsRow .stat-card-clickable').forEach(card => {
        card.addEventListener('click', () => {
            const status = card.getAttribute('data-status');
            document.querySelectorAll('#mockStatsCardsRow .stat-card-clickable')
                .forEach(c => c.classList.remove('active-stat'));
            card.classList.add('active-stat');

            if (!mockSubjectVettingList) return;

            if (status === 'all') {
                mockSubjectVettingList.filter(item => mockApplyTermSessionFilter(item));
            } else {
                mockSubjectVettingList.filter(item => {
                    const itemStatus = (item.elm.getAttribute('data-status') || 'pending').toLowerCase();
                    return itemStatus === status && mockApplyTermSessionFilter(item);
                });
            }
            mockCurrentCardPage = 1;
        });
    });
}

function mockApplyTermSessionFilter(item) {
    const el         = item.elm;
    const rowTerm    = el.getAttribute('data-term')    || '';
    const rowSession = el.getAttribute('data-session') || '';
    const termOk    = !mockCurrentTermFilter    || rowTerm    === mockCurrentTermFilter;
    const sessionOk = !mockCurrentSessionFilter || rowSession === mockCurrentSessionFilter;
    return termOk && sessionOk;
}

// ─── View Toggle ──────────────────────────────────────────────────────────────
function mockInitializeViewToggle() {
    const tableBtn       = document.getElementById('mockTableViewBtn');
    const cardBtn        = document.getElementById('mockCardViewBtn');
    const tableContainer = document.querySelector('.table-view-container');
    const cardContainer  = document.getElementById('mockCardViewContainer');
    if (!tableBtn || !cardBtn) return;

    tableBtn.addEventListener('click', () => {
        mockCurrentView = 'table';
        tableBtn.classList.add('active');    cardBtn.classList.remove('active');
        tableContainer?.classList.remove('hide'); cardContainer?.classList.remove('active');
    });

    cardBtn.addEventListener('click', () => {
        mockCurrentView = 'card';
        cardBtn.classList.add('active');     tableBtn.classList.remove('active');
        tableContainer?.classList.add('hide');   cardContainer?.classList.add('active');
        mockCurrentCardPage = 1;
        mockRenderCardView();
    });
}

// ─── Add Assignment ───────────────────────────────────────────────────────────
function mockInitializeAddForm() {
    const form = document.getElementById('add-mocksubjectvetting-form');
    if (!form) return;

    // Subject-class search
    const searchInput = document.getElementById('mockSubjectClassSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const items = document.querySelectorAll('#mockSubjectClassList .subject-class-item');
            let hasResults = false;

            items.forEach(item => {
                const text  = (item.getAttribute('data-search') || '').toLowerCase();
                const match = !query || text.includes(query);
                item.style.display = match ? '' : 'none';
                if (match) hasResults = true;
            });

            const noResults = document.getElementById('mockNoResultsMessage');
            if (noResults) noResults.style.display = hasResults ? 'none' : 'block';
        });
    }

    // Selection counter
    const checkboxes = document.querySelectorAll('#mockSubjectClassList .form-check-input[name="subjectclassid[]"]');
    checkboxes.forEach(cb => cb.addEventListener('change', mockUpdateSelectionCount));

    const clearBtn = document.getElementById('mockClearSelectionBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            checkboxes.forEach(cb => cb.checked = false);
            mockUpdateSelectionCount();
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        mockSubmitAddForm();
    });

    // Reset form on modal close
    document.getElementById('addMockSubjectVettingModal')?.addEventListener('hidden.bs.modal', () => {
        form.reset();
        mockUpdateSelectionCount();
        const noResults = document.getElementById('mockNoResultsMessage');
        if (noResults) noResults.style.display = 'none';
        const searchEl = document.getElementById('mockSubjectClassSearch');
        if (searchEl) searchEl.value = '';
        document.querySelectorAll('#mockSubjectClassList .subject-class-item')
            .forEach(item => item.style.display = '');
        const errorEl = document.getElementById('mock-alert-error-msg');
        if (errorEl) errorEl.classList.add('d-none');
    });
}

function mockUpdateSelectionCount() {
    const checked = document.querySelectorAll('#mockSubjectClassList .form-check-input[name="subjectclassid[]"]:checked').length;
    const total   = document.querySelectorAll('#mockSubjectClassList .form-check-input[name="subjectclassid[]"]').length;
    const countEl = document.getElementById('mockSelectedCount');
    const totalEl = document.getElementById('mockTotalCount');
    if (countEl) countEl.textContent = checked;
    if (totalEl) totalEl.textContent = total;
}

function mockSubmitAddForm() {
    const form    = document.getElementById('add-mocksubjectvetting-form');
    const errorEl = document.getElementById('mock-alert-error-msg');
    const addBtn  = document.getElementById('mock-add-btn');

    const formData = new FormData(form);

    if (!formData.get('userid')) {
        mockShowFormError(errorEl, 'Please select a vetting staff member.');
        return;
    }
    if (!formData.get('sessionid')) {
        mockShowFormError(errorEl, 'Please select a session.');
        return;
    }
    if (!formData.getAll('termid[]').length) {
        mockShowFormError(errorEl, 'Please select at least one term.');
        return;
    }
    if (!formData.getAll('subjectclassid[]').length) {
        mockShowFormError(errorEl, 'Please select at least one subject-class assignment.');
        return;
    }

    if (errorEl) errorEl.classList.add('d-none');
    if (addBtn)  addBtn.disabled = true;

    const storeUrl = window.mockSubjectVettingStoreUrl
        || document.querySelector('meta[name="mock-vetting-store-url"]')?.content
        || '/mock-subject-vettings';

    fetch(storeUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success || data.status === 'success') {
            const modal = bootstrap.Modal.getInstance(document.getElementById('addMockSubjectVettingModal'));
            if (modal) modal.hide();
            mockShowToast('Mock assignment created successfully!', 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            // Handle validation errors object
            if (data.errors) {
                const messages = Object.values(data.errors).flat().join('<br>');
                mockShowFormError(errorEl, messages);
            } else {
                mockShowFormError(errorEl, data.message || 'An error occurred. Please try again.');
            }
        }
    })
    .catch(() => mockShowFormError(errorEl, 'Network error. Please try again.'))
    .finally(() => { if (addBtn) addBtn.disabled = false; });
}

// ─── Edit Assignment ──────────────────────────────────────────────────────────
function mockInitializeEditForm() {
    const form = document.getElementById('edit-mocksubjectvetting-form');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        mockSubmitEditForm();
    });

    // Table edit buttons
    document.querySelector('#kt_mock_subject_vetting_table tbody')?.addEventListener('click', function (e) {
        const btn = e.target.closest('.edit-item-btn');
        if (!btn) return;
        const row = btn.closest('tr');
        if (row) mockPopulateEditModalFromRow(row);
    });

    // Card edit buttons
    document.getElementById('mockCardsContainer')?.addEventListener('click', function (e) {
        const btn = e.target.closest('.mock-card-edit-btn');
        if (!btn) return;
        mockPopulateEditModalFromCard(btn);
    });

    // Reset on close
    document.getElementById('editMockModal')?.addEventListener('hidden.bs.modal', () => {
        const errorEl = document.getElementById('mock-edit-alert-error-msg');
        if (errorEl) errorEl.classList.add('d-none');
    });

    // Re-init Select2 in edit modal
    document.getElementById('editMockModal')?.addEventListener('shown.bs.modal', function () {
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('#mock-edit-userid').select2({
                dropdownParent: $('#editMockModal'),
                placeholder: 'Select Staff',
                allowClear: true,
                width: '100%'
            });
        }
    });
}

function mockPopulateEditModalFromRow(row) {
    const svid          = row.getAttribute('data-id')     || '';
    const vettingUserId = row.querySelector('.vetting_username')?.getAttribute('data-vetting_userid') || '';
    const termid        = row.querySelector('.termname')?.getAttribute('data-termid')                 || '';
    const sessionid     = row.querySelector('.sessionname')?.getAttribute('data-sessionid')           || '';
    const subjectclassid= row.querySelector('.subjectname')?.getAttribute('data-subjectclassid')      || '';
    const status        = row.getAttribute('data-status') || 'pending';

    document.getElementById('mock-edit-id-field').value        = svid;
    document.getElementById('mock-edit-userid').value          = vettingUserId;
    document.getElementById('mock-edit-termid').value          = termid;
    document.getElementById('mock-edit-sessionid').value       = sessionid;
    document.getElementById('mock-edit-subjectclassid').value  = subjectclassid;
    document.getElementById('mock-edit-status').value          = status;

    // Trigger Select2 update if available
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#mock-edit-userid').trigger('change');
    }

    const modal = new bootstrap.Modal(document.getElementById('editMockModal'));
    modal.show();
}

function mockPopulateEditModalFromCard(btn) {
    document.getElementById('mock-edit-id-field').value        = btn.getAttribute('data-id')             || '';
    document.getElementById('mock-edit-userid').value          = btn.getAttribute('data-vetting_userid')  || '';
    document.getElementById('mock-edit-termid').value          = btn.getAttribute('data-termid')          || '';
    document.getElementById('mock-edit-sessionid').value       = btn.getAttribute('data-sessionid')       || '';
    document.getElementById('mock-edit-subjectclassid').value  = btn.getAttribute('data-subjectclassid')  || '';
    document.getElementById('mock-edit-status').value          = btn.getAttribute('data-status')          || 'pending';

    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#mock-edit-userid').trigger('change');
    }

    const modal = new bootstrap.Modal(document.getElementById('editMockModal'));
    modal.show();
}

function mockSubmitEditForm() {
    const errorEl   = document.getElementById('mock-edit-alert-error-msg');
    const updateBtn = document.getElementById('mock-update-btn');
    const id        = document.getElementById('mock-edit-id-field').value;

    if (!id) { mockShowFormError(errorEl, 'Invalid record ID.'); return; }

    const form     = document.getElementById('edit-mocksubjectvetting-form');
    const formData = new FormData(form);
    formData.append('_method', 'PUT');

    if (updateBtn) updateBtn.disabled = true;

    fetch(`/mock-subject-vettings/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success || data.status === 'success') {
            const modal = bootstrap.Modal.getInstance(document.getElementById('editMockModal'));
            if (modal) modal.hide();
            mockShowToast('Mock assignment updated successfully!', 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            if (data.errors) {
                const messages = Object.values(data.errors).flat().join('<br>');
                mockShowFormError(errorEl, messages);
            } else {
                mockShowFormError(errorEl, data.message || 'An error occurred.');
            }
        }
    })
    .catch(() => mockShowFormError(errorEl, 'Network error. Please try again.'))
    .finally(() => { if (updateBtn) updateBtn.disabled = false; });
}

// ─── Delete ───────────────────────────────────────────────────────────────────
function mockInitializeDelete() {
    // Table delete buttons
    document.querySelector('#kt_mock_subject_vetting_table tbody')?.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-item-btn');
        if (!btn) return;
        const row = btn.closest('tr');
        if (row) {
            mockDeleteId       = row.getAttribute('data-id');
            window._mockDeleteUrl = row.getAttribute('data-url');
            const modal = new bootstrap.Modal(document.getElementById('deleteRecordModal'));
            modal.show();
        }
    });

    // Card delete buttons
    document.getElementById('mockCardsContainer')?.addEventListener('click', function (e) {
        const btn = e.target.closest('.mock-card-delete-btn');
        if (!btn) return;
        mockDeleteId          = btn.getAttribute('data-id');
        window._mockDeleteUrl = btn.getAttribute('data-url');
        const modal = new bootstrap.Modal(document.getElementById('deleteRecordModal'));
        modal.show();
    });

    // Confirm delete
    document.getElementById('delete-record')?.addEventListener('click', function () {
        if (!mockDeleteId || !window._mockDeleteUrl) return;
        this.disabled = true;

        fetch(window._mockDeleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteRecordModal'));
            if (modal) modal.hide();
            if (data.success || data.status === 'success') {
                mockShowToast('Mock record deleted successfully!', 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                mockShowToast(data.message || 'Could not delete record.', 'danger');
            }
        })
        .catch(() => mockShowToast('Network error. Please try again.', 'danger'))
        .finally(() => { this.disabled = false; });
    });
}

// ─── Bulk Delete ──────────────────────────────────────────────────────────────
function mockInitializeBulkDelete() {
    const checkAll  = document.getElementById('checkAll');
    const removeBtn = document.getElementById('remove-actions');

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            document.querySelectorAll('input[name="chk_child"]').forEach(cb => {
                cb.checked = this.checked;
            });
            mockToggleRemoveBtn();
        });
    }

    document.querySelector('#kt_mock_subject_vetting_table tbody')?.addEventListener('change', function (e) {
        if (e.target.name === 'chk_child') mockToggleRemoveBtn();
    });

    function mockToggleRemoveBtn() {
        const anyChecked = document.querySelectorAll('input[name="chk_child"]:checked').length > 0;
        if (removeBtn) removeBtn.classList.toggle('d-none', !anyChecked);
    }
}

window.deleteMultiple = function () {
    const ids = Array.from(document.querySelectorAll('input[name="chk_child"]:checked')).map(cb => cb.value);
    if (!ids.length) return;
    if (!confirm(`Are you sure you want to delete ${ids.length} record(s)?`)) return;

    fetch('/mock-subject-vettings/bulk-delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ ids })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success || data.status === 'success') {
            mockShowToast(`${ids.length} record(s) deleted.`, 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            mockShowToast(data.message || 'Could not delete records.', 'danger');
        }
    })
    .catch(() => mockShowToast('Network error.', 'danger'));
};

// ─── Image Preview ────────────────────────────────────────────────────────────
function mockInitializeImagePreview() {
    document.querySelectorAll('.staff-image').forEach(img => {
        img.addEventListener('click', function () {
            const src         = this.getAttribute('data-image')      || this.src;
            const teacherName = this.getAttribute('data-teachername') || '';
            const previewImg  = document.getElementById('preview-image');
            const previewName = document.getElementById('preview-teachername');
            if (previewImg)  previewImg.src         = src;
            if (previewName) previewName.textContent = teacherName;
        });
    });
}

// ─── Search ───────────────────────────────────────────────────────────────────
function mockInitializeSearch() {
    const searchInput = document.querySelector('.search-box input.search');
    if (!searchInput) return;

    searchInput.addEventListener('input', () => {
        if (mockSubjectVettingList) {
            mockSubjectVettingList.search(searchInput.value);
            // 'updated' event fires → mockUpdateStatsFromList() called automatically
        }
    });
}

// ─── Select2 Init ─────────────────────────────────────────────────────────────
function mockInitializeSelect2() {
    if (typeof $ === 'undefined' || !$.fn.select2) return;

    $('#mock-userid').select2({
        placeholder: 'Select Staff',
        allowClear: true,
        width: '100%'
    });

    $('#addMockSubjectVettingModal').on('shown.bs.modal', function () {
        $('#mock-userid').select2({
            dropdownParent: $('#addMockSubjectVettingModal'),
            placeholder: 'Select Staff',
            allowClear: true,
            width: '100%'
        });
    });
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function mockShowFormError(el, message) {
    if (!el) return;
    el.innerHTML = message;
    el.classList.remove('d-none');
}

function mockShowToast(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alertDiv.style.zIndex = 9999;
    alertDiv.style.minWidth = '280px';
    alertDiv.innerHTML = `
        <i class="ri-${type === 'success' ? 'checkbox-circle' : 'error-warning'}-line me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.body.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 3500);
}

// ─── Boot ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    mockInitializeListJS();
    mockInitializeChart();
    mockInitializeTermAndSessionFilters();
    mockInitializeStatCardFilters();
    mockInitializeViewToggle();
    mockInitializeAddForm();
    mockInitializeEditForm();
    mockInitializeDelete();
    mockInitializeBulkDelete();
    mockInitializeImagePreview();
    mockInitializeSearch();
    mockInitializeSelect2();

    // Initial stats load after List.js has settled
    setTimeout(mockUpdateStatsFromList, 250);

    console.log('✅ Mock Subject Vetting fully initialized');
});
</script>
@endsection
