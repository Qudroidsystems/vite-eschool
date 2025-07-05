@extends('layouts.master')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
@media (max-width: 768px) {
    .score-input {
        height: 48px;
        font-size: 1.1rem;
        padding: 8px;
        width: 80px;
        min-width: 80px;
        box-sizing: border-box;
        touch-action: manipulation;
        text-align: right;
    }
    .table-responsive {
        overflow-x: auto;
    }
    .avatar-sm {
        width: 40px !important;
        height: 40px !important;
    }
    td.ca1, td.ca2, td.ca3, td.exam {
        padding: 4px !important;
    }
    .chart-container {
        max-width: 100%;
        height: 300px;
    }
}
.chart-container {
    position: relative;
    width: 100%;
    max-width: 400px;
    height: 350px;
    margin: 20px auto;
}
.chart-fallback {
    display: none;
    text-align: center;
    color: #6c757d;
    font-style: italic;
}
.alert-debug {
    display: none;
    margin-bottom: 20px;
}
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Debug Alert -->
            <div class="alert alert-warning alert-debug" id="dataDebugAlert">
                <strong>Debug Info:</strong> No scores data available. Check controller or database.
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Error!</strong> There were some problems with your input.<br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('status') || session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') ?: session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($broadsheets->isNotEmpty())
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-wrap flex-stack mb-4">
                                    <div class="d-flex flex-column flex-grow-1 pe-8">
                                        <div class="d-flex flex-wrap">
                                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-book fs-3 text-primary me-2"></i>
                                                    <div class="fs-2 fw-bold text-success">{{ $broadsheets->first()->subject ?? 'N/A' }}</div>
                                                </div>
                                                <div class="fw-semibold fs-6 text-gray-400">Subject</div>
                                            </div>
                                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-code fs-3 text-success me-2"></i>
                                                    <div class="fs-2 fw-bold text-success">{{ $broadsheets->first()->subject_code ?? 'N/A' }}</div>
                                                </div>
                                                <div class="fw-semibold fs-6 text-gray-400">Subject Code</div>
                                            </div>
                                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-building fs-3 text-success me-2"></i>
                                                    <div class="fs-2 fw-bold text-success">{{ $broadsheets->first()->schoolclass ?? 'N/A' }} {{ $broadsheets->first()->arm ?? '' }}</div>
                                                </div>
                                                <div class="fw-semibold fs-6 text-gray-400">Class</div>
                                            </div>
                                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-calendar fs-3 text-success me-2"></i>
                                                    <div class="fs-2 fw-bold text-success">{{ $broadsheets->first()->term ?? 'N/A' }} | {{ $broadsheets->first()->session ?? 'N/A' }}</div>
                                                </div>
                                                <div class="fw-semibold fs-6 text-gray-400">Term | Session</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-0">
                                    {{ $pagetitle }}
                                    @if ($broadsheets->isNotEmpty())
                                        <span class="badge bg-info-subtle text-info ms-2" id="scoreCount">{{ $broadsheets->count() }}</span>
                                    @endif
                                </h5>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchInput" placeholder="Search by admission no or name..." style="min-width: 200px;" {{ $broadsheets->isEmpty() ? 'disabled' : '' }}>
                                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                        <i class="ri-close-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <a href="{{ route('myresultroom.index') }}" class="btn btn-primary">
                                    <i class="ri-arrow-left-line"></i> Back
                                </a>
                                <div>
                                    @if(session('subjectclass_id'))
                                        <a href="{{ route('scoresheet.download-marks-sheet') }}" class="btn btn-warning">
                                            <i class="fas fa-file-pdf"></i> Download Marks Sheet
                                        </a>
                                    @endif
                                    <a href="{{ route('subjectscoresheet.export') }}" class="btn btn-info me-2">
                                        <i class="ri-download-line me-1"></i> Download Excel
                                    </a>
                                    <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#importModal" {{ !session('schoolclass_id') || !session('subjectclass_id') || !session('staff_id') || !session('term_id') || !session('session_id') ? 'disabled title="Please select a class, subject, term, and session first"' : '' }}>
                                        <i class="ri-upload-line me-1"></i> Bulk Excel Upload
                                    </button>
                                    @if ($broadsheets->isNotEmpty())
                                        <button class="btn btn-secondary me-2" data-bs-toggle="modal" data-bs-target="#scoresModal">
                                            <i class="bi bi-table me-1"></i> View Scores
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <div class="alert alert-info text-center" id="noDataAlert" style="display: {{ $broadsheets->isEmpty() ? 'block' : 'none' }};">
                                <i class="ri-information-line me-2"></i>
                                No scores available for the selected subject. Please check your filters or import scores.
                            </div>

                            <div class="table-responsive">
                                <table class="table table-centered align-middle table-nowrap mb-0" id="scoresheetTable">
                                    <thead class="table-active">
                                        <tr>
                                            <th style="width: 50px;">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="checkAll">
                                                    <label class="form-check-label" for="checkAll"></label>
                                                </div>
                                            </th>
                                            <th style="width: 50px;" class="sort cursor-pointer" data-sort="sn">SN</th>
                                            <th class="sort cursor-pointer" data-sort="admissionno">Admission No</th>
                                            <th class="sort cursor-pointer" data-sort="name">Name</th>
                                            <th>CA1</th>
                                            <th>CA2</th>
                                            <th>CA3</th>
                                            <th>Exam</th>
                                            <th>Total</th>
                                            <th>BF</th>
                                            <th>Cum</th>
                                            <th>Grade</th>
                                            <th>Position</th>
                                        </tr>
                                    </thead>
                                    <tbody id="scoresheetTableBody" class="list form-check-all">
                                        @php $i = 0; @endphp
                                        @forelse ($broadsheets as $broadsheet)
                                            <tr>
                                                <td>
                                                    <div class="form-check">
                                                        <input class="form-check-input score-checkbox" type="checkbox" name="chk_child" data-id="{{ $broadsheet->id }}">
                                                        <label class="form-check-label"></label>
                                                    </div>
                                                </td>
                                                <td class="sn">{{ ++$i }}</td>
                                                <td class="admissionno" data-admissionno="{{ $broadsheet->admissionno }}">{{ $broadsheet->admissionno ?? '-' }}</td>
                                                <td class="name" data-name="{{ ($broadsheet->lname ?? '') . ' ' . ($broadsheet->fname ?? '') . ' ' . ($broadsheet->mname ?? '') }}">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm me-2">
                                                            <img src="{{ $broadsheet->picture && file_exists(storage_path('app/public/' . $broadsheet->picture)) ? asset('storage/' . $broadsheet->picture) : asset('storage/student_avatars/unnamed.jpg') }}"
                                                                 alt="{{ ($broadsheet->lname ?? '') . ' ' . ($broadsheet->fname ?? '') . ' ' . ($broadsheet->mname ?? '') }}"
                                                                 class="rounded-circle w-100 student-image"
                                                                 data-bs-toggle="modal"
                                                                 data-bs-target="#imageViewModal"
                                                                 data-image="{{ $broadsheet->picture && file_exists(storage_path('app/public/' . $broadsheet->picture)) ? asset('storage/' . $broadsheet->picture) : asset('storage/student_avatars/unnamed.jpg') }}"
                                                                 data-picture="{{ $broadsheet->picture ?? 'none' }}"
                                                                 onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'; console.error('Image failed to load for admissionno: {{ $broadsheet->admissionno ?? 'unknown' }}, path: {{ $broadsheet->picture ?? 'none' }}');">
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <span class="fw-bold">{{ $broadsheet->lname ?? '' }}</span> {{ $broadsheet->fname ?? '' }} {{ $broadsheet->mname ?? '' }}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="ca1">
                                                    <input type="number" class="form-control score-input" data-field="ca1" data-id="{{ $broadsheet->id }}" value="{{ $broadsheet->ca1 ?? '' }}" min="0" max="100" step="0.1" placeholder="">
                                                </td>
                                                <td class="ca2">
                                                    <input type="number" class="form-control score-input" data-field="ca2" data-id="{{ $broadsheet->id }}" value="{{ $broadsheet->ca2 ?? '' }}" min="0" max="100" step="0.1" placeholder="">
                                                </td>
                                                <td class="ca3">
                                                    <input type="number" class="form-control score-input" data-field="ca3" data-id="{{ $broadsheet->id }}" value="{{ $broadsheet->ca3 ?? '' }}" min="0" max="100" step="0.1" placeholder="">
                                                </td>
                                                <td class="exam">
                                                    <input type="number" class="form-control score-input" data-field="exam" data-id="{{ $broadsheet->id }}" value="{{ $broadsheet->exam ?? '' }}" min="0" max="100" step="0.1" placeholder="">
                                                </td>
                                                <td class="total-display text-center">
                                                    <span class="badge bg-primary">{{ $broadsheet->total ? number_format($broadsheet->total, 1) : '0.0' }}</span>
                                                </td>
                                                <td class="bf-display text-center">
                                                    <span class="badge bg-secondary">{{ $broadsheet->bf ? number_format($broadsheet->bf, 2) : '0.00' }}</span>
                                                </td>
                                                <td class="cum-display text-center">
                                                    <span class="badge bg-info">{{ $broadsheet->cum ? number_format($broadsheet->cum, 2) : '0.00' }}</span>
                                                </td>
                                                <td class="grade-display text-center">
                                                    <span class="badge bg-secondary">{{ $broadsheet->grade ?? '-' }}</span>
                                                </td>
                                                <td class="position-display text-center">
                                                    <span class="badge bg-info">{{ $broadsheet->position ?? '-' }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr id="noDataRow">
                                                <td colspan="14" class="text-center">No scores available.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($broadsheets->isNotEmpty())
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <h6 class="card-title mb-0 me-3">Bulk Actions:</h6>
                                                        <div class="btn-group me-2" role="group">
                                                            <button type="button" class="btn btn-outline-primary btn-sm" id="selectAllScores">
                                                                <i class="ri-check-double-line me-1"></i> Select All
                                                            </button>
                                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="clearAllScores">
                                                                <i class="ri-close-line me-1"></i> Clear All
                                                            </button>
                                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteSelectedScores()">
                                                                <i class="ri-delete-bin-line me-1"></i> Delete Selected
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <small class="text-muted me-3">
                                                            <i class="ri-information-line"></i> Press Ctrl+S to save quickly
                                                        </small>
                                                        <button class="btn btn-success" id="bulkUpdateScores">
                                                            <i class="ri-save-line me-1"></i> Save All Scores
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-2" id="progressContainer" style="display: none;">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">Updating Scores...</h6>
                                                        <div class="progress" style="height: 6px;">
                                                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="fw-bold">Bulk Upload Scores</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body scroll-y mx-5 mx-xl-10 my-7">
                            <form action="{{ route('subjectscoresheet.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                                @csrf
                                <input type="hidden" name="schoolclass_id" value="{{ session('schoolclass_id') }}">
                                <input type="hidden" name="subjectclass_id" value="{{ session('subjectclass_id') }}">
                                <input type="hidden" name="staff_id" value="{{ session('staff_id') }}">
                                <input type="hidden" name="term_id" value="{{ session('term_id') }}">
                                <input type="hidden" name="session_id" value="{{ session('session_id') }}">
                                <div class="form-group mb-6">
                                    <label class="required fw-semibold fs-6 mb-2">Excel File</label>
                                    <input type="file" name="file" class="form-control form-control-sm mb-3" accept=".xlsx,.xls" required>
                                </div>
                                <div class="form-group mb-6" id="importLoader" style="display: none;">
                                    <div class="d-flex align-items-center">
                                        <div class="spinner-border spinner-border-sm text-primary me-3" role="status">
                                            <span class="visually-hidden">Uploading...</span>
                                        </div>
                                        <span>Uploading file...</span>
                                    </div>
                                </div>
                                <div class="text-center pt-10">
                                    <button type="reset" class="btn btn-outline-secondary me-3" data-bs-dismiss="modal">Discard</button>
                                    <button type="submit" class="btn btn-primary" id="importSubmit">Upload</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="scoresModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="fw-bold">Scores Overview</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="chart-container">
                                        <h6 class="text-center mb-3">Grade Distribution</h6>
                                        <canvas id="gradeDistributionChart"></canvas>
                                        <div class="chart-fallback" id="gradeChartFallback">
                                            No grades available to display.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="chart-container">
                                        <h6 class="text-center mb-3">Total Score Distribution</h6>
                                        <canvas id="scoreDistributionChart"></canvas>
                                        <div class="chart-fallback" id="scoreChartFallback">
                                            No scores available to display.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive mt-4">
                                <table class="table table-centered align-middle table-nowrap mb-0">
                                    <thead class="table-active">
                                        <tr>
                                            <th>SN</th>
                                            <th>Admission No</th>
                                            <th>Name</th>
                                            <th>CA1</th>
                                            <th>CA2</th>
                                            <th>CA3</th>
                                            <th>Exam</th>
                                            <th>Total</th>
                                            <th>BF</th>
                                            <th>Cum</th>
                                            <th>Grade</th>
                                            <th>Position</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $i = 0; @endphp
                                        @forelse ($broadsheets as $broadsheet)
                                            <tr>
                                                <td>{{ ++$i }}</td>
                                                <td class="admissionno">{{ $broadsheet->admissionno ?? '-' }}</td>
                                                <td class="name">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm me-2">
                                                            <img src="{{ $broadsheet->picture && file_exists(storage_path('app/public/' . $broadsheet->picture)) ? asset('storage/' . $broadsheet->picture) : asset('storage/student_avatars/unnamed.jpg') }}"
                                                                 alt="{{ ($broadsheet->lname ?? '') . ' ' . ($broadsheet->fname ?? '') . ' ' . ($broadsheet->mname ?? '') }}"
                                                                 class="rounded-circle w-100 student-image"
                                                                 data-bs-toggle="modal"
                                                                 data-bs-target="#imageViewModal"
                                                                 data-image="{{ $broadsheet->picture && file_exists(storage_path('app/public/' . $broadsheet->picture)) ? asset('storage/' . $broadsheet->picture) : asset('storage/student_avatars/unnamed.jpg') }}"
                                                                 data-picture="{{ $broadsheet->picture ?? 'none' }}"
                                                                 onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'; console.error('Image failed to load for admissionno: {{ $broadsheet->admissionno ?? 'unknown' }}, path: {{ $broadsheet->picture ?? 'none' }}');">
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <span class="fw-bold">{{ $broadsheet->lname ?? '' }}</span> {{ $broadsheet->fname ?? '' }} {{ $broadsheet->mname ?? '' }}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $broadsheet->ca1 ? number_format($broadsheet->ca1, 1) : '0.0' }}</td>
                                                <td>{{ $broadsheet->ca2 ? number_format($broadsheet->ca2, 1) : '0.0' }}</td>
                                                <td>{{ $broadsheet->ca3 ? number_format($broadsheet->ca3, 1) : '0.0' }}</td>
                                                <td>{{ $broadsheet->exam ? number_format($broadsheet->exam, 1) : '0.0' }}</td>
                                                <td>{{ $broadsheet->total ? number_format($broadsheet->total, 1) : '0.0' }}</td>
                                                <td>{{ $broadsheet->bf ? number_format($broadsheet->bf, 2) : '0.00' }}</td>
                                                <td>{{ $broadsheet->cum ? number_format($broadsheet->cum, 2) : '0.00' }}</td>
                                                <td>{{ $broadsheet->grade ?? '-' }}</td>
                                                <td>{{ $broadsheet->position ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="12" class="text-center">No scores available.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                            <img id="enlargedImage" src="" alt="Student Image" class="img-fluid" onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'; console.error('Enlarged image failed to load');">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Debug logs
    console.log('Chart.js loaded:', typeof Chart !== 'undefined' ? 'Yes' : 'No');
    console.log('jQuery loaded:', typeof $ !== 'undefined' ? 'Yes' : 'No');
    console.log('Bootstrap loaded:', typeof bootstrap !== 'undefined' ? 'Yes' : 'No');
    console.log('Raw broadsheets JSON:', @json($broadsheets));

    // Initialize broadsheets with fallback
    window.broadsheets = @json($broadsheets) || [
        { id: 1, admissionno: 'ST001', lname: 'Doe', fname: 'John', cum: 75.5, total: 80.0, picture: 'student_avatars/doe.jpg' },
        { id: 2, admissionno: 'ST002', lname: 'Smith', fname: 'Jane', cum: 65.0, total: 70.0, picture: 'student_avatars/smith.jpg' },
        { id: 3, admissionno: 'ST003', lname: 'Brown', fname: 'Bob', cum: 55.0, total: 60.0, picture: 'student_avatars/brown.jpg' }
    ];
    console.log('Broadsheets:', window.broadsheets);
    console.log('Broadsheets length:', window.broadsheets.length);

    // Session variables
    window.term_id = {{ session('term_id') ?? 'null' }};
    window.session_id = {{ session('session_id') ?? 'null' }};
    window.subjectclass_id = {{ session('subjectclass_id') ?? 'null' }};
    window.schoolclass_id = {{ session('schoolclass_id') ?? 'null' }};
    window.staff_id = {{ session('staff_id') ?? 'null' }};
    console.log('Session vars:', { term_id: window.term_id, session_id: window.session_id, subjectclass_id: window.subjectclass_id, schoolclass_id: window.schoolclass_id, staff_id: window.staff_id });

    // Simplified chart rendering
    function renderCharts() {
        console.log('renderCharts called');
        if (typeof Chart === 'undefined') {
            console.error('Chart.js not loaded');
            document.getElementById('gradeChartFallback').textContent = 'Error: Chart library not loaded.';
            document.getElementById('scoreChartFallback').textContent = 'Error: Chart library not loaded.';
            document.getElementById('gradeChartFallback').style.display = 'block';
            document.getElementById('scoreChartFallback').style.display = 'block';
            return;
        }

        const gradeCanvas = document.getElementById('gradeDistributionChart');
        const scoreCanvas = document.getElementById('scoreDistributionChart');
        if (!gradeCanvas || !scoreCanvas) {
            console.error('Canvas elements missing:', { gradeCanvas: !!gradeCanvas, scoreCanvas: !!scoreCanvas });
            document.getElementById('gradeChartFallback').textContent = 'Error: Chart containers not found.';
            document.getElementById('scoreChartFallback').textContent = 'Error: Chart containers not found.';
            document.getElementById('gradeChartFallback').style.display = 'block';
            document.getElementById('scoreChartFallback').style.display = 'block';
            return;
        }

        // Destroy existing charts
        if (window.gradeChart) window.gradeChart.destroy();
        if (window.scoreChart) window.scoreChart.destroy();

        // Process data
        const gradeCounts = { A: 0, B: 0, C: 0, D: 0, F: 0 };
        const scoreRanges = { '0-20': 0, '20-40': 0, '40-60': 0, '60-80': 0, '80-100': 0 };
        let hasData = false;

        if (Array.isArray(window.broadsheets) && window.broadsheets.length > 0) {
            console.log('Processing broadsheets:', window.broadsheets);
            window.broadsheets.forEach((broadsheet, index) => {
                const cum = parseFloat(broadsheet.cum) || 0;
                const total = parseFloat(broadsheet.total) || 0;
                if (cum > 0 || total > 0) hasData = true;

                // Grade calculation
                if (cum >= 70) gradeCounts.A++;
                else if (cum >= 60) gradeCounts.B++;
                else if (cum >= 50) gradeCounts.C++;
                else if (cum >= 40) gradeCounts.D++;
                else gradeCounts.F++;

                // Score ranges
                if (total >= 0 && total <= 20) scoreRanges['0-20']++;
                else if (total <= 40) scoreRanges['20-40']++;
                else if (total <= 60) scoreRanges['40-60']++;
                else if (total <= 80) scoreRanges['60-80']++;
                else if (total <= 100) scoreRanges['80-100']++;

                if (isNaN(cum) || isNaN(total)) {
                    console.warn(`Invalid data at index ${index}:`, { cum, total });
                }
            });
        } else {
            console.error('No valid broadsheets data');
            document.getElementById('dataDebugAlert').style.display = 'block';
            document.getElementById('gradeChartFallback').textContent = 'No scores data available.';
            document.getElementById('scoreChartFallback').textContent = 'No scores data available.';
            document.getElementById('gradeChartFallback').style.display = 'block';
            document.getElementById('scoreChartFallback').style.display = 'block';
            return;
        }

        // Render grade chart
        ```chartjs
        {
            "type": "pie",
            "data": {
                "labels": ["A", "B", "C", "D", "F"],
                "datasets": [{
                    "label": "Grade Distribution",
                    "data": [
                        gradeCounts.A,
                        gradeCounts.B,
                        gradeCounts.C,
                        gradeCounts.D,
                        gradeCounts.F
                    ],
                    "backgroundColor": ["#4CAF50", "#2196F3", "#FF9800", "#F44336", "#9E9E9E"],
                    "borderColor": ["#388E3C", "#1976D2", "#F57C00", "#D32F2F", "#616161"],
                    "borderWidth": 1
                }]
            },
            "options": {
                "responsive": true,
                "maintainAspectRatio": false,
                "plugins": {
                    "legend": {
                        "display": true,
                        "position": "top",
                        "labels": {
                            "font": { "size": 14 },
                            "color": "#333"
                        }
                    },
                    "tooltip": {
                        "callbacks": {
                            "label": function(context) {
                                return `${context.label}: ${context.raw} student${context.raw !== 1 ? 's' : ''}`;
                            }
                        }
                    }
                }
            }
        }
        ```
        window.gradeChart = new Chart(gradeCanvas, chartConfig);
        console.log('Grade chart rendered:', gradeCounts);

        // Render score chart
        ```chartjs
        {
            "type": "pie",
            "data": {
                "labels": ["0-20", "20-40", "40-60", "60-80", "80-100"],
                "datasets": [{
                    "label": "Total Score Distribution",
                    "data": [
                        scoreRanges['0-20'],
                        scoreRanges['20-40'],
                        scoreRanges['40-60'],
                        scoreRanges['60-80'],
                        scoreRanges['80-100']
                    ],
                    "backgroundColor": ["#FF5733", "#FFC107", "#4CAF50", "#2196F3", "#9C27B0"],
                    "borderColor": ["#C4302B", "#FFA000", "#388E3C", "#1976D2", "#7B1FA2"],
                    "borderWidth": 1
                }]
            },
            "options": {
                "responsive": true,
                "maintainAspectRatio": false,
                "plugins": {
                    "legend": {
                        "display": true,
                        "position": "top",
                        "labels": {
                            "font": { "size": 14 },
                            "color": "#333"
                        }
                    },
                    "tooltip": {
                        "callbacks": {
                            "label": function(context) {
                                return `${context.label}: ${context.raw} student${context.raw !== 1 ? 's' : ''}`;
                            }
                        }
                    }
                }
            }
        }
        ```
        window.scoreChart = new Chart(scoreCanvas, chartConfig);
        console.log('Score chart rendered:', scoreRanges);
    }

    document.addEventListener('DOMContentLoaded', function () {
        console.log('DOM loaded');
        const scoresModal = document.getElementById('scoresModal');
        if (scoresModal) {
            scoresModal.addEventListener('shown.bs.modal', function () {
                console.log('Scores modal shown');
                renderCharts();
            });
        } else {
            console.error('Scores modal not found');
        }

        // Image modal handler
        document.querySelectorAll('.student-image').forEach(img => {
            img.addEventListener('click', function() {
                const enlargedImage = document.getElementById('enlargedImage');
                const imagePath = this.dataset.image;
                enlargedImage.src = imagePath;
                console.log('Loading enlarged image:', imagePath);
            });
        });
    });
</script>
@endsection