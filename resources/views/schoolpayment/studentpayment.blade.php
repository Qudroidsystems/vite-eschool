@extends('layouts.master')
@section('content')
<!-- Main content container -->
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <!-- Debug: Display payment records count -->
            <div>Debug: {{ $studentpaymentbill->count() }} payment records found</div>

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

            <!-- Student Information Cards -->
            @if ($studentdata)
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="d-flex flex-wrap flex-stack mb-4">
                                        <!-- Student Avatar -->
                                        <div class="me-6 mb-3">
                                            <img src="{{ $studentdata->avatar ? Storage::url('images/studentavatar/' . $studentdata->avatar) : asset('images/default-avatar.png') }}" 
                                                 alt="{{ $studentdata->firstname }} {{ $studentdata->lastname }}" 
                                                 class="rounded-circle" 
                                                 style="width: 80px; height: 80px; object-fit: cover; border: 2 solid #e5e7eb;">
                                        </div>

                                        <!-- Student Information -->
                                        <div class="d-flex flex-column flex-grow-1 pe-8">
                                            <div class="d-flex flex-wrap">
                                                <!-- Student Name -->
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-person fs-3 text-primary me-2"></i>
                                                        <div class="fs-2 fw-bold text-success">{{ $studentdata->firstname }} {{ $studentdata->lastname }}</div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Student Name</div>
                                                </div>

                                                <!-- Admission No -->
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-card-text fs-3 text-success me-2"></i>
                                                        <div class="fs-2 fw-bold text-success">{{ $studentdata->admissionNo }}</div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Admission No</div>
                                                </div>

                                                <!-- Student Status -->
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-card-text fs-3 {{ $studentdata->student_status !== 'Active' ? 'text-danger' : 'text-success' }} me-2"></i>
                                                        <div class="fs-2 fw-bold {{ $studentdata->student_status !== 'Active' ? 'text-danger' : 'text-success' }}">
                                                            {{ $studentdata->statusId == 1 ? 'Returning Student' : ($studentdata->statusId == 2 ? 'New Student' : $studentdata->statusId) }} | {{ $studentdata->student_status }}
                                                        </div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Student Status | Active Mode</div>
                                                </div>

                                                <!-- Class -->
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-building fs-3 text-success me-2"></i>
                                                        <div class="fs-2 fw-bold text-success">{{ $studentdata->schoolclass }} {{ $studentdata->arm }}</div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Class</div>
                                                </div>

                                                <!-- Term | Session -->
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-calendar fs-3 text-success me-2"></i>
                                                        <div class="fs-2 fw-bold text-success">{{ $schoolterm }} | {{ $schoolsession }}</div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Term | Session</div>
                                                </div>

                                                <!-- Total Bill -->
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-currency-dollar fs-3 text-success me-2"></i>
                                                        <div class="fs-2 fw-bold text-success">₦ {{ number_format($student_bill_info->sum('amount')) }}</div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Total Bill</div>
                                                </div>

                                                <!-- Total Paid (Accurate for this term/session only) -->
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-wallet fs-3 text-success me-2"></i>
                                                        <div class="fs-2 fw-bold text-success">
                                                            ₦ {{ number_format($studentpaymentbillbook->where('term_id', $termid)->where('session_id', $sessionid)->sum('amount_paid')) }}
                                                        </div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Total Paid</div>
                                                </div>

                                                <!-- Outstanding -->
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-exclamation-circle fs-3 text-danger me-2"></i>
                                                        @php
                                                            $totalBill = $student_bill_info->sum('amount');
                                                            $totalPaidThisTerm = $studentpaymentbillbook->where('term_id', $termid)->where('session_id', $sessionid)->sum('amount_paid');
                                                            $outstanding = max(0, $totalBill - $totalPaidThisTerm);
                                                        @endphp
                                                        <div class="fs-2 fw-bold text-danger">₦ {{ number_format($outstanding) }}</div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Outstanding</div>
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

            <!-- Payments Section -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-0">Student Payment Details</h5>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchInput" placeholder="Search by bill title or description..." style="min-width: 200px;" {{ $studentpaymentbill->isEmpty() ? 'disabled' : '' }}>
                                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                        <i class="ri-close-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <a href="{{ route('schoolpayment.index') }}" class="btn btn-primary">
                                    <i class="ri-arrow-left-line"></i> Back to Students
                                </a>
                                <div>
                                    @if ($paymentRecordsCount > 0)
                                        <a href="{{ route('schoolpayment.invoice', ['studentId' => $studentId, 'schoolclassid' => $schoolclassId, 'termid' => $termid, 'sessionid' => $sessionid]) }}" class="btn btn-primary me-2">
                                            <i class="ri-download-line me-1"></i> Generate Invoice
                                        </a>
                                    @else
                                        <button class="btn btn-primary me-2" disabled title="No payment records available">
                                            <i class="ri-download-line me-1"></i> Generate Invoice
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- Tab Navigation -->
                            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold mb-5" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#payment-records">
                                        Payment Records
                                        @if ($studentpaymentbill->isNotEmpty())
                                            <span class="badge bg-info-subtle text-info ms-2">{{ $studentpaymentbill->count() }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#school-bills">
                                        School Bills
                                        @if ($student_bill_info->isNotEmpty())
                                            <span class="badge bg-info-subtle text-info ms-2">{{ $student_bill_info->count() }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#payment-history">
                                        Payment History
                                        @if ($paymentHistory->isNotEmpty())
                                            <span class="badge bg-info-subtle text-info ms-2">{{ $paymentHistory->count() }}</span>
                                        @endif
                                    </a>
                                </li>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content">

                                <!-- Payment Records Tab -->
                                <div class="tab-pane fade show active" id="payment-records">
                                    <!-- Table content remains exactly the same -->
                                    <!-- ... (your existing table code is perfect, no change needed) ... -->
                                    <div class="table-responsive">
                                        <table class="table table-centered align-middle table-nowrap mb-0" id="paymentsTable">
                                            <!-- Your existing table header and body -->
                                            <!-- No changes needed here -->
                                        </table>
                                    </div>
                                </div>

                                <!-- School Bills Tab - FIXED VERSION -->
                                <!-- School Bills Tab - 100% FIXED VERSION -->
<div class="tab-pane fade" id="school-bills">
    @if ($student_bill_info->isNotEmpty())
        <div class="row g-3">
            @foreach ($student_bill_info as $sc)
                @php
                    // FORCE correct amount for THIS term + session only
                    $paidThisTerm = \App\Models\StudentBillPaymentRecord::join('student_bill_payment as sbp', 'student_bill_payment_record.student_bill_payment_id', '=', 'sbp.id')
                        ->where('sbp.student_id', $studentId)
                        ->where('sbp.school_bill_id', $sc->schoolbillid)
                        ->where('sbp.termid_id', $termid)
                        ->where('sbp.session_id', $sessionid)
                        ->sum('student_bill_payment_record.amount_paid');

                    $amountPaid = max(0, (float)$paidThisTerm);
                    $balance    = max(0, $sc->amount - $amountPaid);
                    $progressPercentage = $sc->amount > 0 ? ($amountPaid / $sc->amount) * 100 : 0;
                    $isPaidInFull = $amountPaid >= $sc->amount;

                    // Check if there is a pending payment (invoice not yet generated)
                    $pendingPayment = $studentpaymentbill->where('school_bill_id', $sc->schoolbillid)->first();
                    $invoicePending = $pendingPayment && $pendingPayment->delete_status == '1';
                @endphp

                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                    <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden" style="border-radius: 12px;">
                        <div class="position-absolute top-0 start-0 w-100" style="height: 3px; background: {{ $isPaidInFull ? 'linear-gradient(90deg, #10b981, #059669)' : 'linear-gradient(90deg, #f59e0b, #d97706)' }};"></div>
                        <div class="card-body p-4">

                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <div class="flex-grow-1">
                                    <h6 class="card-title mb-1 fw-bold">{{ $sc->title }}</h6>
                                    <span class="badge {{ $isPaidInFull ? 'bg-success' : 'bg-warning' }} bg-opacity-10 {{ $isPaidInFull ? 'text-success' : 'text-warning' }} px-2 py-1 rounded-pill fw-medium">
                                        {{ $isPaidInFull ? 'Paid' : $sc->description }}
                                    </span>
                                </div>
                                <div class="ms-2">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle {{ $isPaidInFull ? 'bg-success' : 'bg-warning' }} bg-opacity-10" style="width: 32px; height: 32px;">
                                        <i class="fas {{ $isPaidInFull ? 'fa-check' : 'fa-credit-card' }} {{ $isPaidInFull ? 'text-success' : 'text-warning' }}"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="text-center mb-3">
                                    <div class="fs-5 fw-bold text-primary">₦{{ number_format($sc->amount) }}</div>
                                    <div class="fs-7 text-muted">Total Amount</div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="text-center p-2 bg-success bg-opacity-10 rounded-2">
                                            <div class="fs-7 fw-bold text-success mb-0">₦{{ number_format($amountPaid) }}</div>
                                            <div class="fs-8 text-muted">Paid</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-2 {{ $balance > 0 ? 'bg-danger bg-opacity-10' : 'bg-success bg-opacity-10' }} rounded-2">
                                            <div class="fs-7 fw-bold {{ $balance > 0 ? 'text-danger' : 'text-success' }} mb-0">
                                                ₦{{ number_format($balance) }}
                                            </div>
                                            <div class="fs-8 text-muted">Balance</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="fs-8 text-muted">Progress</span>
                                    <span class="fs-8 fw-bold {{ $isPaidInFull ? 'text-success' : 'text-primary' }}">
                                        {{ number_format($progressPercentage, 0) }}%
                                    </span>
                                </div>
                                <div class="progress rounded-pill" style="height: 6px;">
                                    <div class="progress-bar {{ $isPaidInFull ? 'bg-success' : 'bg-primary' }} rounded-pill" 
                                         style="width: {{ $progressPercentage }}%"></div>
                                </div>
                            </div>

                            <div class="d-grid">
                                @if ($isPaidInFull)
                                    <button class="btn btn-success btn-sm rounded-pill py-2 fw-medium" disabled>
                                        <i class="fas fa-check-circle me-1"></i> Complete
                                    </button>
                                @else
                                    <button class="btn btn-primary btn-sm rounded-pill py-2 fw-medium make-payment"
                                            @if ($invoicePending) disabled title="Generate invoice first or delete pending payment" @endif
                                            data-student_id="{{ $studentId }}"
                                            data-amount="{{ number_format($sc->amount) }}"
                                            data-amount_actual="{{ $sc->amount }}"
                                            data-amount_paid="{{ number_format($amountPaid) }}"
                                            data-balance="{{ number_format($balance) }}"
                                            data-school_bill_id="{{ $sc->schoolbillid }}"
                                            data-class_id="{{ $schoolclassId }}"
                                            data-term_id="{{ $termid }}"
                                            data-session_id="{{ $sessionid }}"
                                            data-bs-toggle="modal" data-bs-target="#paymentModal">
                                        <i class="fas fa-credit-card me-1"></i> Make Payment
                                    </button>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5">
            <p>No school bills assigned for this class/term/session.</p>
        </div>
    @endif
</div>

                                <!-- Payment History Tab -->
                                <div class="tab-pane fade" id="payment-history">
                                    <!-- Your existing history table - no change needed -->
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Message Modal -->
                <div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="headerModalTitle">Notification</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" id="messageModalBody">
                                <!-- Message will be injected here -->
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Confirmation Modal -->
                <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Confirm Deletion</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to delete this payment record?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" id="cancelDeleteButton" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CSS for Payment Modal -->
                <style>
                    #paymentModal .modal-dialog {
                        max-width: 500px;
                        width: 90%;
                    }
                    #paymentModal .modal-content {
                        border-radius: 12px;
                        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                        max-height: 90vh;
                    }
                    #paymentModal .modal-header {
                        background: linear-gradient(90deg, #3b82f6, #1d4ed8);
                        color: white;
                        border-bottom: none;
                        border-radius: 12px 12px 0 0;
                    }
                    #paymentModal .modal-body {
                        padding: 1.5rem;
                        overflow-y: auto;
                        max-height: calc(90vh - 120px);
                    }
                    #paymentModal .form-group {
                        margin-bottom: 1rem;
                    }
                    #paymentModal .form-control-sm {
                        font-size: 0.875rem;
                        padding: 0.5rem 0.75rem;
                    }
                    #paymentModal .btn-primary {
                        background-color: #3b82f6;
                        border: none;
                        border-radius: 8px;
                        padding: 0.5rem 1.5rem;
                        transition: background-color 0.3s ease;
                    }
                    #paymentModal .btn-primary:hover {
                        background-color: #1d4ed8;
                        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.75);
                    }
                    @media (max-width: 576px) {
                        #paymentModal .modal-dialog {
                            margin: 0.5rem;
                        }
                        #paymentModal .modal-body {
                            padding: 1rem;
                        }
                    }
                </style>

                <!-- CSS for Confirmation Modal -->
                <style>
                    #confirmModal .modal-content {
                        border-radius: 12px;
                        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                    }
                    #confirmModal .modal-header {
                        background: linear-gradient(90deg, #ef4444, #b91c1c);
                        color: white;
                        border-bottom: none;
                    }
                    #confirmModal .modal-body {
                        font-size: 1rem;
                        color: inherit;
                        text-align: center;
                        padding: 1.5rem;
                    }
                    #confirmModal .modal-footer {
                        border-top: none;
                        justify-content: center;
                    }
                    #confirmModal .btn-danger {
                        background: #ef4444;
                        border: none;
                        border-radius: 8px;
                        padding: 0.5rem 1.5rem;
                        transition: all 0.3s ease;
                    }
                    #confirmModal .btn-danger:hover {
                        background: #b91c1c;
                        box-shadow: 0 4px 8px rgba(239, 64, 64, 0.3);
                    }
                </style>

                <!-- CSS for Message Modal -->
                <style>
                    #messageModal .modal-content {
                        border-radius: 12px;
                        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                    }
                    #messageModal .modal-header {
                        background: linear-gradient(90deg, #3b82f6, #1d4ed8);
                        color: white;
                        border-bottom: 1px solid #1f40af;
                        border-radius: 12px 12px 0 0;
                    }
                    #messageModal .modal-body {
                        font-size: 1rem;
                        color: inherit;
                        text-align: center;
                        padding: 1.5rem;
                    }
                    #messageModal .modal-footer {
                        border-top: none;
                        justify-content: center;
                    }
                    #messageModal .btn-primary {
                        background-color: #3b82f6;
                        border: none;
                        border-radius: 8px;
                        padding: 0.5rem 1.5rem;
                        transition: background-color 0.3s ease;
                    }
                    #messageModal .btn-primary:hover {
                        background-color: #1d4ed8;
                        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.75);
                    }
                </style>

                <!-- JavaScript -->
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        console.log('DOM fully loaded, initializing scripts');

                        // Search Functionality for Payment Records
                        const searchInput = document.getElementById('searchInput');
                        const clearSearch = document.getElementById('clearSearch');
                        const tableBody = document.getElementById('paymentsTableBody');
                        const noDataAlert = document.getElementById('noDataAlert');

                        if (searchInput && tableBody && noDataAlert) {
                            searchInput.addEventListener('input', function() {
                                const query = this.value.trim().toLowerCase();
                                const rows = tableBody.querySelectorAll('tr:not(#noDataRow)');
                                let hasVisibleRows = false;

                                rows.forEach(row => {
                                    const title = (row.querySelector('.title')?.getAttribute('data-title') || '').toLowerCase();
                                    const description = (row.querySelector('.description')?.getAttribute('data-description') || '').toLowerCase();
                                    const isMatch = title.includes(query) || description.includes(query);
                                    row.style.display = isMatch ? '' : 'none';
                                    if (isMatch) hasVisibleRows = true;
                                });

                                noDataAlert.style.display = hasVisibleRows ? 'none' : 'block';
                            });

                            clearSearch.addEventListener('click', function() {
                                searchInput.value = '';
                                const rows = tableBody.querySelectorAll('tr:not(#noDataRow)');
                                rows.forEach(row => row.style.display = '');
                                noDataAlert.style.display = rows.length > 0 ? 'none' : 'block';
                            });
                        }

                        // Search Functionality for Payment History
                        const historySearchInput = document.getElementById('historySearchInput');
                        const historyClearSearch = document.getElementById('historyClearSearch');
                        const historyTableBody = document.getElementById('paymentHistoryTableBody');
                        const historyNoDataAlert = document.getElementById('historyNoDataAlert');

                        if (historySearchInput && historyTableBody && historyNoDataAlert) {
                            historySearchInput.addEventListener('input', function() {
                                const query = this.value.trim().toLowerCase();
                                const rows = historyTableBody.querySelectorAll('tr:not(#historyNoDataRow)');
                                let hasVisibleRows = false;

                                rows.forEach(row => {
                                    const title = (row.querySelector('.title')?.getAttribute('data-title') || '').toLowerCase();
                                    const description = (row.querySelector('.description')?.getAttribute('data-description') || '').toLowerCase();
                                    const isMatch = title.includes(query) || description.includes(query);
                                    row.style.display = isMatch ? '' : 'none';
                                    if (isMatch) hasVisibleRows = true;
                                });

                                historyNoDataAlert.style.display = hasVisibleRows ? 'none' : 'block';
                            });

                            historyClearSearch.addEventListener('click', function() {
                                historySearchInput.value = '';
                                const rows = historyTableBody.querySelectorAll('tr:not(#historyNoDataRow)');
                                rows.forEach(row => row.style.display = '');
                                historyNoDataAlert.style.display = rows.length > 0 ? 'none' : 'block';
                            });
                        }

                        // Checkbox Select All for Payment Records
                        const checkAll = document.getElementById('checkAll');
                        const checkboxes = document.querySelectorAll('.payment-checkbox');

                        if (checkAll) {
                            checkAll.addEventListener('change', function() {
                                checkboxes.forEach(checkbox => {
                                    checkbox.checked = this.checked;
                                });
                            });

                            checkboxes.forEach(checkbox => {
                                checkbox.addEventListener('change', function() {
                                    checkAll.checked = Array.from(checkboxes).every(c => c.checked);
                                });
                            });
                        }

                        // Checkbox Select All for Payment History
                        const historyCheckAll = document.getElementById('historyCheckAll');
                        const historyCheckboxes = document.querySelectorAll('.history-checkbox');

                        if (historyCheckAll) {
                            historyCheckAll.addEventListener('change', function() {
                                historyCheckboxes.forEach(checkbox => {
                                    checkbox.checked = this.checked;
                                });
                            });

                            historyCheckboxes.forEach(checkbox => {
                                checkbox.addEventListener('change', function() {
                                    historyCheckAll.checked = Array.from(historyCheckboxes).every(c => c.checked);
                                });
                            });
                        }

                        // Populate Payment Modal
                        const paymentButtons = document.querySelectorAll('.make-payment');
                        console.log('Found', paymentButtons.length, 'make-payment buttons');

                        paymentButtons.forEach((button, index) => {
                            button.addEventListener('click', function(e) {
                                console.log(`Make Payment button ${index} clicked`);
                                try {
                                    const data = {
                                        student_id: button.getAttribute('data-student_id') || '',
                                        amount: button.getAttribute('data-amount') || '',
                                        amount_actual: button.getAttribute('data-amount_actual') || '',
                                        amount_paid: button.getAttribute('data-amount_paid') || '',
                                        balance: button.getAttribute('data-balance') || '',
                                        school_bill_id: button.getAttribute('data-school_bill_id') || '',
                                        class_id: button.getAttribute('data-class_id') || '',
                                        term_id: button.getAttribute('data-term_id') || '',
                                        session_id: button.getAttribute('data-session_id') || ''
                                    };
                                    console.log(`Button ${index} attributes:`, data);

                                    // Validate data
                                    if (!data.student_id || !data.class_id || !data.term_id || !data.session_id || !data.school_bill_id) {
                                        throw new Error('Missing required data attributes');
                                    }

                                    // Populate modal fields
                                    document.querySelector('#student_id').value = data.student_id;
                                    document.querySelector('#class_id').value = data.class_id;
                                    document.querySelector('#term_id').value = data.term_id;
                                    document.querySelector('#session_id').value = data.session_id;
                                    document.querySelector('#school_bill_id').value = data.school_bill_id;
                                    document.querySelector('#amount_d').value = data.amount ? '₦' + data.amount : '';
                                    document.querySelector('#amount_paid_d').value = data.amount_paid ? '₦' + data.amount_paid : '₦0';
                                    document.querySelector('#balance_d').value = data.balance ? '₦' + data.balance : '₦0';
                                    document.querySelector('#actual_amount').value = data.amount_actual ? parseFloat(data.amount_actual).toFixed(2) : '0.00';
                                    document.querySelector('#balance2').value = data.balance ? parseFloat(data.balance.replace(/[^0-9.]/g, '')).toFixed(2) : '0.00';
                                    document.querySelector('#last_amount_paid').value = data.amount_paid ? parseFloat(data.amount_paid.replace(/[^0-9.]/g, '')).toFixed(2) : '0.00';
                                    document.querySelector('#payment_amount').value = '';
                                    document.querySelector('#payment_amount2').value = '';
                                    document.querySelector('#payment_method2').value = '';

                                    console.log('Modal fields populated:', {
                                        student_id: document.querySelector('#student_id').value,
                                        amount_d: document.querySelector('#amount_d').value,
                                        amount_paid_d: document.querySelector('#amount_paid_d').value,
                                        balance_d: document.querySelector('#balance_d').value
                                    });

                                    // Open modal
                                    const modal = document.querySelector('#paymentModal');
                                    if (typeof bootstrap === 'undefined') {
                                        throw new Error('Bootstrap not loaded');
                                    }
                                    const paymentModal = new bootstrap.Modal(modal);
                                    paymentModal.show();
                                    console.log('Payment modal opened');
                                } catch (error) {
                                    console.error('Error populating modal:', error);
                                    const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                                    document.getElementById('messageModalBody').textContent = 'Error opening payment modal: ' + error.message;
                                    document.getElementById('headerModalTitle').textContent = 'Error';
                                    messageModal.show();
                                }
                            });
                        });

                        // Payment Amount Validation
                        const paymentForm = document.querySelector('#paymentForm');
                        const paymentModalElement = document.querySelector('#paymentModal');
                        const paymentAmountInput = document.querySelector('#payment_amount');
                        const paymentAmountHidden = document.querySelector('#payment_amount2');
                        const billAmountInput = document.querySelector('#amount_d');

                        if (paymentAmountInput && paymentAmountHidden) {
                            paymentAmountInput.addEventListener('input', function() {
                                let value = this.value.replace(/[^0-9.]/g, '');
                                const balance = parseFloat(document.querySelector('#balance2')?.value || 0);
                                const amount = parseFloat(value);

                                if (isNaN(amount) || amount <= 0) {
                                    this.classList.add('is-invalid');
                                    this.parentElement.querySelector('.invalid-feedback').textContent = 'Enter a valid amount greater than 0.';
                                } else if (amount > balance) {
                                    this.classList.add('is-invalid');
                                    this.parentElement.querySelector('.invalid-feedback').textContent = 'Amount cannot exceed outstanding balance.';
                                } else {
                                    this.classList.remove('is-invalid');
                                    this.parentElement.querySelector('.invalid-feedback').textContent = '';
                                }

                                paymentAmountHidden.value = isNaN(amount) ? '' : amount.toFixed(2);
                            });
                        }

                        // Form Submission
                        if (paymentForm) {
                            paymentForm.addEventListener('submit', function(e) {
                                e.preventDefault();
                                console.log('Payment form submitted');

                                // Validate Bill Amount
                                if (billAmountInput) {
                                    const billAmountValue = billAmountInput.value.trim();
                                    if (!billAmountValue || billAmountValue === '₦0' || isNaN(parseFloat(billAmountValue.replace(/[^0-9.]/g, '')))) {
                                        billAmountInput.classList.add('is-invalid');
                                        billAmountInput.parentElement.insertAdjacentHTML('afterend', '<div class="invalid-feedback">Bill Amount is required and must be greater than 0.</div>');
                                        return;
                                    } else {
                                        billAmountInput.classList.remove('is-invalid');
                                        const existingFeedback = billAmountInput.parentElement.querySelector('.invalid-feedback');
                                        if (existingFeedback) existingFeedback.remove();
                                    }
                                }

                                // Validate Payment Amount
                                let value = paymentAmountInput.value.replace(/[^0-9.]/g, '');
                                const balance = parseFloat(document.querySelector('#balance2')?.value || 0);
                                const amount = parseFloat(value);

                                if (isNaN(amount) || amount <= 0) {
                                    paymentAmountInput.classList.add('is-invalid');
                                    paymentAmountInput.parentElement.querySelector('.invalid-feedback').textContent = 'Enter a valid amount greater than 0.';
                                    return;
                                } else if (amount > balance) {
                                    paymentAmountInput.classList.add('is-invalid');
                                    paymentAmountInput.parentElement.querySelector('.invalid-feedback').textContent = 'Amount cannot exceed outstanding balance.';
                                    return;
                                }

                                // Validate Payment Method
                                const paymentMethodSelect = document.querySelector('#payment_method2');
                                if (!paymentMethodSelect?.value) {
                                    paymentMethodSelect.classList.add('is-invalid');
                                    paymentMethodSelect.parentElement.insertAdjacentHTML('afterend', '<div class="invalid-feedback">Select a payment method.</div>');
                                    return;
                                } else {
                                    paymentMethodSelect.classList.remove('is-invalid');
                                    const existingFeedback = paymentMethodSelect.parentElement.querySelector('.invalid-feedback');
                                    if (existingFeedback) existingFeedback.remove();
                                }

                                paymentAmountHidden.value = value;

                                const formData = new FormData(this);
                                console.log('Form Data:', Object.fromEntries(formData));

                                fetch(this.action, {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                                    }
                                })
                                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                                .then(({ status, body }) => {
                                    console.log('Response:', { status, body });
                                    const paymentModalInstance = bootstrap.Modal.getInstance(paymentModalElement);
                                    paymentModalInstance.hide();
                                    document.body.classList.remove('modal-open');
                                    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
                                    document.body.style.overflow = '';

                                    const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                                    if (status === 200 && body.success) {
                                        document.getElementById('messageModalBody').textContent = body.message || 'Payment processed successfully.';
                                        document.getElementById('headerModalTitle').textContent = 'Success';
                                        messageModal.show();
                                        if (body.redirect_url) {
                                            setTimeout(() => window.location.href = body.redirect_url, 1000);
                                        }
                                    } else {
                                        let errorMessage = body.message || 'Error processing payment.';
                                        if (status === 422 && body.errors) {
                                            errorMessage = Object.values(body.errors).flat().join('\n');
                                        }
                                        document.getElementById('messageModalBody').textContent = errorMessage;
                                        document.getElementById('headerModalTitle').textContent = 'Error';
                                        messageModal.show();
                                    }
                                })
                                .catch(error => {
                                    console.error('Fetch Error:', error);
                                    const paymentModalInstance = bootstrap.Modal.getInstance(paymentModalElement);
                                    paymentModalInstance.hide();
                                    document.body.classList.remove('modal-open');
                                    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
                                    document.body.style.overflow = '';
                                    const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                                    document.getElementById('messageModalBody').textContent = 'Unexpected error: ' + error.message;
                                    document.getElementById('headerModalTitle').textContent = 'Error';
                                    messageModal.show();
                                });
                            });
                        }

                        // Delete Payment
                        const deleteButtons = document.querySelectorAll('.delete-payment');
                        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
                        const confirmModal = document.getElementById('confirmModal');
                        let deleteUrl = '';

                        deleteButtons.forEach(button => {
                            button.addEventListener('click', function() {
                                deleteUrl = this.getAttribute('data-url');
                                console.log('Delete button clicked, URL:', deleteUrl);
                                if (deleteUrl) {
                                    new bootstrap.Modal(confirmModal).show();
                                }
                            });
                        });

                        if (confirmDeleteBtn) {
                            confirmDeleteBtn.addEventListener('click', function() {
                                console.log('Confirm delete clicked, URL:', deleteUrl);
                                const confirmModalInstance = bootstrap.Modal.getInstance(confirmModal);
                                confirmModalInstance.hide();
                                document.body.classList.remove('modal-open');
                                document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
                                document.body.style.overflow = '';

                                fetch(deleteUrl, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({})
                                })
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error(`HTTP error! Status: ${response.status}`);
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    console.log('Delete response:', data);
                                    const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                                    document.getElementById('messageModalBody').textContent = data.message || (data.success ? 'Payment deleted successfully.' : 'Failed to delete payment.');
                                    document.getElementById('headerModalTitle').textContent = data.success ? 'Success' : 'Error';
                                    messageModal.show();
                                    if (data.success) {
                                        setTimeout(() => window.location.reload(), 1000);
                                    }
                                })
                                .catch(error => {
                                    console.error('Delete Error:', error);
                                    const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                                    document.getElementById('messageModalBody').textContent = 'Error deleting payment: ' + error.message;
                                    document.getElementById('headerModalTitle').textContent = 'Error';
                                    messageModal.show();
                                });
                            });
                        }
                    });
                </script>
            </div>
        </div>
    </div>
</div>
@endsection