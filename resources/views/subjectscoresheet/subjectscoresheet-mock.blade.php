@extends('layouts.master')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Main content container -->
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Display validation errors -->
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

            <!-- Display success/status messages -->
            @if (session('status') || session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') ?: session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Subject Information Cards -->
            @if ($broadsheets->isNotEmpty())
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="d-flex flex-wrap flex-stack mb-4">
                                        <div class="d-flex flex-column flex-grow-1 pe-8">
                                            <div class="d-flex flex-wrap">
                                                <!-- Subject Card -->
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-book fs-3 text-primary me-2"></i>
                                                        <div class="fs-2 fw-bold text-success">{{ $broadsheets->first()->subject ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Subject</div>
                                                </div>
                                                <!-- Subject Code Card -->
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-code fs-3 text-success me-2"></i>
                                                        <div class="fs-2 fw-bold text-success">{{ $broadsheets->first()->subject_code ?? '-' }}</div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Subject Code</div>
                                                   
                                                </div>
                                                <!-- Class Card -->
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-building fs-3 text-success me-2"></i>
                                                        <div class="fs-2 fw-bold text-success">{{ $broadsheets->first()->schoolclass ?? 'N/A' }} {{ $broadsheets->first()->arm ?? '' }}</div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Class</div>
                                                </div>
                                                <!-- Term | Session Card -->
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
                </div>
            @endif

            <!-- Mock Scoresheet Table -->
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
                                    <a href="{{ route('subjectscoresheet-mock.export') }}" class="btn btn-info me-2">
                                        <i class="ri-download-line me-1"></i> Download Excel
                                    </a>
                                    <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#importModal" {{ !session('schoolclass_id') || !session('subjectclass_id') || !session('staff_id') || !session('term_id') || !session('session_id') ? 'disabled title="Please select a class, subject, term, and session first"' : '' }}>
                                        <i class="ri-upload-line me-1"></i> Bulk Excel Upload
                                    </button>
                                    @if ($broadsheets->isNotEmpty())
                                        <button class="btn btn-secondary me-2" data-bs-toggle="modal" data-bs-target="#scoresModal">
                                            <i class="bi bi-table me-1"></i> View Scores
                                        </button>
                                        <button class="btn btn-danger me-2" onclick="deleteSelectedScores()">
                                            <i class="ri-delete-bin-line me-1"></i> Delete Selected
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- No Data Alert -->
                            <div class="alert alert-info text-center" id="noDataAlert" style="display: {{ $broadsheets->isEmpty() ? 'block' : 'none' }};">
                                <i class="ri-information-line me-2"></i>
                                No mock scores available for the selected subject. Please check your filters or import scores.
                            </div>

                            <!-- Mock Scoresheet Table -->
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
                                            <th>Exam</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="scoresheetTableBody" class="list form-check-all">
                                        @php $i = 0; @endphp
                                        @forelse ($broadsheets as $broadsheet)
                                            <tr>
                                                <td>
                                                    <div class="form-check">
                                                        <input class="form-check score-checkbox" type="checkbox" name="chk_child" data-id="{{ $broadsheet->id }}">
                                                        <label class="form-check-label"></label>
                                                    </div>
                                                </td>
                                                <td class="sn">{{ ++$i }}</td>
                                                <td class="admissionno" data-admissionno="{{ $broadsheet->admissionno ?? '-' }}">{{ $broadsheet->admissionno ?? '-' }}</td>
                                                <td class="name" data-name="{{ ($broadsheet->fname ?? '') . ' ' . ($broadsheet->lname ?? '') }}">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm me-2">
                                                            <img src="{{ $broadsheet->picture ? Storage::url('images/studentavatar/' . $broadsheet->picture) : Storage::url('images/studentavatar/avatar.jpg') }}" alt="{{ ($broadsheet->fname ?? '') . ' ' . ($broadsheet->lname ?? '') }}" class="rounded-circle w-100">
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            {{ ($broadsheet->fname ?? '') . ' ' . ($broadsheet->lname ?? '') }}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="exam">
                                                    <input type="number" class="form-control form-control-sm score-input" data-field="exam" data-id="{{ $broadsheet->id }}" value="{{ $broadsheet->exam ?? '' }}" min="0" step="0.1">
                                                </td>
                                         
                                                <td>
                                                    <a href="{{ route('subjectscoresheet-mock.edit', $broadsheet->id) }}" class="btn btn-primary btn-sm" title="Edit Score">
                                                        <i class="ri-edit-line me-1"></i> Edit
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr id="noDataRow">
                                                <td colspan="9" class="text-center">No mock scores available.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import Modal -->
            <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="fw-bold">Bulk Upload Mock Scores</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body scroll-y mx-5 mx-xl-10 my-7">
                            <form action="{{ route('scoresheet-mock.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                                @csrf
                                <input type="hidden" name="schoolclass_id" value="{{ session('schoolclass_id') }}">
                                <input type="hidden" name="subjectclass_id" value="{{ session('subjectclass_id') }}">
                                <input type="hidden" name="staff_id" value="{{ session('staff_id') }}">
                                <input type="hidden" name="term_id" value="{{ session('term_id') }}">
                                <input type="hidden" name="session_id" value="{{ session('session_id') }}">
                                <div class="form-group mb-6">
                                    <label class="required fw-semibold fs-6 mb-2">Excel File</label>
                                    <input type="file" name="file" class="form-control form-control-sm mb-3" accept=".xlsx" required>
                                </div>
                                <div class="text-center pt-10">
                                    <button type="reset" class="btn btn-outline-secondary me-3" data-bs-dismiss="modal">Discard</button>
                                    <button type="submit" class="btn btn-primary" id="importSubmit" {{ !session('schoolclass_id') || !session('subjectclass_id') || !session('staff_id') || !session('term_id') || !session('session_id') ? 'disabled' : '' }}>
                                        <span class="indicator-label">Submit</span>
                                        <span class="indicator-progress">Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scores Modal -->
           <!-- Add CSS for smaller table headers and highlighting low scores -->
<style>
    .narrow-th {
        width: 80px; /* Adjust width as needed */
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .text-danger {
        color: red !important;
    }
</style>

<!-- Scores Modal -->
<div class="modal fade" id="scoresModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title fw-bold">Mock Scoresheet</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th class="text-nowrap">Admission No.</th>
                                <th>Name</th>
                                <th class="narrow-th">Exam</th>
                                <th class="narrow-th">Total</th>
                                <th class="narrow-th">Grade</th>
                                <th class="narrow-th">Position</th>
                                <th class="narrow-th">Remark</th>
                            </tr>
                        </thead>
                        <tbody id="scoresBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

        </div>
    </div>
</div>

<!-- Pass broadsheets data to JavaScript -->
<script>
    window.broadsheets = @json($broadsheets);
    console.log('Broadsheet data:', window.broadsheets);
</script>
@endsection

