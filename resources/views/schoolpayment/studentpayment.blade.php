@extends('layouts.master')

@section('content')


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

            <!-- Student Information Cards -->
            @if ($studentdata->isNotEmpty())
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="d-flex flex-wrap flex-stack mb-4">
                                        <div class="d-flex flex-column flex-grow-1 pe-8">
                                            <div class="d-flex flex-wrap">
                                                <!-- Student Name Card -->
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-person fs-3 text-primary me-2"></i>
                                                        <div class="fs-2 fw-bold text-success">{{ $studentdata->first()->firstname }} {{ $studentdata->first()->lastname }}</div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Student Name</div>
                                                </div>
                                                <!-- Admission No Card -->
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-card-text fs-3 text-success me-2"></i>
                                                        <div class="fs-2 fw-bold text-success">{{ $studentdata->first()->admissionNo }}</div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Admission No</div>
                                                </div>
                                                <!-- Class Card -->
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-building fs-3 text-success me-2"></i>
                                                        <div class="fs-2 fw-bold text-success">{{ $studentdata->first()->schoolclass }} {{ $studentdata->first()->arm }}</div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Class</div>
                                                </div>
                                                <!-- Term | Session Card -->
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-calendar fs-3 text-success me-2"></i>
                                                        <div class="fs-2 fw-bold text-success">{{ $schoolterm }} | {{ $schoolsession }}</div>
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
                                    @if ($studentpaymentbill->isNotEmpty())
                                        <a href="{{ route('schoolpayment.invoice', [$studentId, 'schoolclassid' => $schoolclassId, 'termid' => $schooltermId, 'sessionid' => $schoolsessionId]) }}" class="btn btn-primary me-2">
                                            <i class="ri-download-line me-1"></i> Generate Invoice
                                        </a>
                                    @else
                                        <button class="btn btn-primary me-2" disabled>
                                            <i class="ri-download-line me-1"></i> Generate Invoice
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- Tab Navigation -->
                            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold mb-5" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link active" id="payment-records-tab" data-bs-toggle="tab" href="#payment-records" role="tab" aria-controls="payment-records" aria-selected="true">
                                        Payment Records
                                        @if ($studentpaymentbill->isNotEmpty())
                                            <span class="badge bg-info-subtle text-info ms-2" id="paymentCount">{{ $studentpaymentbill->count() }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" id="school-bills-tab" data-bs-toggle="tab" href="#school-bills" role="tab" aria-controls="school-bills" aria-selected="false">
                                        School Bills
                                        @if ($student_bill_info->isNotEmpty())
                                            <span class="badge bg-info-subtle text-info ms-2">{{ $student_bill_info->count() }}</span>
                                        @endif
                                    </a>
                                </li>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content" id="paymentTabContent">
                                <!-- Payment Records Tab -->
                                <div class="tab-pane fade show active" id="payment-records" role="tabpanel" aria-labelledby="payment-records-tab">
                                    <!-- No Data Alert -->
                                    <div class="alert alert-info text-center" id="noDataAlert" style="display: {{ $studentpaymentbill->isEmpty() ? 'block' : 'none' }};">
                                        <i class="ri-information-line me-2"></i>
                                        No payment records available for the selected student. Please check your filters or add payments.
                                    </div>

                                    <!-- Payments Table -->
                                    <div class="table-responsive">
                                        <table class="table table-centered align-middle table-nowrap mb-0" id="paymentsTable">
                                            <thead class="table-active">
                                                <tr>
                                                    <th style="width: 50px;">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="checkAll">
                                                            <label class="form-check-label" for="checkAll"></label>
                                                        </div>
                                                    </th>
                                                    <th style="width: 50px;" class="sort cursor-pointer" data-sort="sn">SN</th>
                                                    <th class="sort cursor-pointer" data-sort="title">School Bill</th>
                                                    <th class="sort cursor-pointer" data-sort="description">Description</th>
                                                    <th>Bill Amount</th>
                                                    <th>Amount Paid</th>
                                                    <th>Outstanding</th>
                                                    <th>Received By</th>
                                                    <th>Date - Time</th>
                                                    <th>Payment Method</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="paymentsTableBody" class="list form-check-all">
                                                @php $i = 0; @endphp
                                                @forelse ($studentpaymentbill as $sp)
                                                    <tr>
                                                        <td>
                                                            <div class="form-check">
                                                                <input class="form-check-input payment-checkbox" type="checkbox" name="chk_child" data-id="{{ $sp->paymentid }}">
                                                                <label class="form-check-label"></label>
                                                            </div>
                                                        </td>
                                                        <td class="sn">{{ ++$i }}</td>
                                                        <td class="title" data-title="{{ $sp->title }}">{{ $sp->title }}</td>
                                                        <td class="description" data-description="{{ $sp->description }}">{{ $sp->description }}</td>
                                                        <td>₦ {{ number_format($sp->billAmount) }}</td>
                                                        <td>₦ {{ number_format(intval($sp->lastPayment) ?? 0) }}</td>
                                                        <td>₦ {{ number_format($sp->balance) }}</td>
                                                        <td>{{ $sp->recievedBy }}</td>
                                                        <td>{{ $sp->recievedDate }}</td>
                                                        <td>{{ $sp->paymentMethod }}</td>
                                                        <td>
                                                            <span class="badge {{ $sp->paymentStatus === 'Completed' ? 'bg-success' : 'bg-danger' }}">{{ $sp->paymentStatus }}</span>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0)" class="btn btn-sm btn-danger delete-payment" data-url="{{ route('schoolpayment.deletestudentpayment', ['paymentid' => $sp->paymentid]) }}">Delete</a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr id="noDataRow">
                                                        <td colspan="12" class="text-center">No payment records available.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- School Bills Tab -->
                                <div class="tab-pane fade" id="school-bills" role="tabpanel" aria-labelledby="school-bills-tab">
                               
                                   @if ($student_bill_info->isNotEmpty())
                                            <div class="row g-3">
                                                @foreach ($student_bill_info as $sc)
                                                    @php
                                                        $paymentFound = false;
                                                        $amountPaid = 0;
                                                        $balance = $sc->amount;
                                                        foreach ($studentpaymentbillbook as $paymentBook) {
                                                            if ((int)$paymentBook->school_bill_id === (int)$sc->schoolbillid) {
                                                                $paymentFound = true;
                                                                $amountPaid = $paymentBook->amount_paid;
                                                                $balance = $paymentBook->amount_owed;
                                                                break;
                                                            }
                                                        }
                                                        $totalLastPayment = \App\Models\StudentBillPayment::where('student_id', $studentId)
                                                            ->where('student_bill_payment.class_id', $schoolclassId)
                                                            ->where('student_bill_payment.termid_id', $schooltermId)
                                                            ->where('student_bill_payment.session_id', $schoolsessionId)
                                                            ->where('school_bill_id', $sc->schoolbillid)
                                                            ->leftJoin('student_bill_payment_record', 'student_bill_payment_record.student_bill_payment_id', '=', 'student_bill_payment.id')
                                                            ->sum(DB::raw('CAST(student_bill_payment_record.amount_paid AS DECIMAL(10, 2))'));
                                                        if ($totalLastPayment > 0) {
                                                            $amountPaid = $totalLastPayment;
                                                            $balance = $sc->amount - $amountPaid;
                                                        }
                                                        
                                                        // Calculate payment progress percentage
                                                        $progressPercentage = $sc->amount > 0 ? ($amountPaid / $sc->amount) * 100 : 0;
                                                        $isPaidInFull = (float)$sc->amount === (float)$amountPaid;
                                                    @endphp
                                                    
                                                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                                        <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden" 
                                                            style="border-radius: 12px; transition: all 0.3s ease;">
                                                            
                                                            <!-- Status indicator stripe -->
                                                            <div class="position-absolute top-0 start-0 w-100" 
                                                                style="height: 3px; background: {{ $isPaidInFull ? 'linear-gradient(90deg, #10b981, #059669)' : 'linear-gradient(90deg, #f59e0b, #d97706)' }};"></div>
                                                            
                                                            <div class="card-body p-4">
                                                                <!-- Header Section -->
                                                                <div class="d-flex align-items-start justify-content-between mb-3">
                                                                    <div class="flex-grow-1">
                                                                        <h6 class="card-title mb-1 fw-bold text-gray-900" style="font-size: 1rem; line-height: 1.3;">
                                                                            {{ $sc->title }}
                                                                        </h6>
                                                                        <span class="badge {{ $isPaidInFull ? 'bg-success' : 'bg-warning' }} bg-opacity-10 
                                                                                {{ $isPaidInFull ? 'text-success' : 'text-warning' }} px-2 py-1 rounded-pill fw-medium"
                                                                            style="font-size: 0.65rem;">
                                                                            <i class="fas {{ $isPaidInFull ? 'fa-check-circle' : 'fa-clock' }} me-1"></i>
                                                                            {{ $isPaidInFull ? 'Paid' : $sc->description }}
                                                                        </span>
                                                                    </div>
                                                                    
                                                                    <!-- Payment status icon -->
                                                                    <div class="ms-2">
                                                                        <div class="d-flex align-items-center justify-content-center rounded-circle 
                                                                                {{ $isPaidInFull ? 'bg-success' : 'bg-warning' }} bg-opacity-10" 
                                                                            style="width: 32px; height: 32px;">
                                                                            <i class="fas {{ $isPaidInFull ? 'fa-check' : 'fa-credit-card' }} 
                                                                                    {{ $isPaidInFull ? 'text-success' : 'text-warning' }}" 
                                                                            style="font-size: 0.9rem;"></i>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Amount Information -->
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

                                                                <!-- Progress Bar -->
                                                                <div class="mb-3">
                                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                                        <span class="fs-8 text-muted">Progress</span>
                                                                        <span class="fs-8 fw-bold {{ $isPaidInFull ? 'text-success' : 'text-primary' }}">
                                                                            {{ number_format($progressPercentage, 0) }}%
                                                                        </span>
                                                                    </div>
                                                                    <div class="progress rounded-pill" style="height: 6px;">
                                                                        <div class="progress-bar {{ $isPaidInFull ? 'bg-success' : 'bg-primary' }} rounded-pill" 
                                                                            role="progressbar" 
                                                                            style="width: {{ $progressPercentage }}%; transition: width 0.6s ease;"
                                                                            aria-valuenow="{{ $progressPercentage }}" 
                                                                            aria-valuemin="0" 
                                                                            aria-valuemax="100">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Action Button -->
                                                                <div class="d-grid">
                                                                    @if ($isPaidInFull)
                                                                        <button class="btn btn-success btn-sm rounded-pill py-2 fw-medium" disabled>
                                                                            <i class="fas fa-check-circle me-1"></i>
                                                                            Complete
                                                                        </button>
                                                                    @else
                                                                        <button class="btn btn-primary btn-sm rounded-pill py-2 fw-medium make-payment"
                                                                                data-student_id="{{ $studentId }}"
                                                                                data-amount="{{ number_format($sc->amount) }}"
                                                                                data-amount_actual="{{ $sc->amount }}"
                                                                                data-amount_paid="{{ number_format($amountPaid) }}"
                                                                                data-balance="{{ number_format($balance) }}"
                                                                                data-school_bill_id="{{ $sc->schoolbillid }}"
                                                                                data-class_id="{{ $schoolclassId }}"
                                                                                data-term_id="{{ $schooltermId }}"
                                                                                data-session_id="{{ $schoolsessionId }}"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#paymentModal"
                                                                                style="background: #3b82f6; 
                                                                                    border: none; 
                                                                                    color: white;
                                                                                    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
                                                                                    transition: all 0.3s ease;">
                                                                            <i class="fas fa-credit-card me-1"></i>
                                                                            Make Payment
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <!-- Custom CSS for enhanced interactions -->
                                            <style>
                                                .card:hover {
                                                    transform: translateY(-4px);
                                                    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
                                                }
                                                
                                                .make-payment:hover {
                                                    transform: translateY(-1px);
                                                    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4) !important;
                                                    background: #1d4ed8 !important;
                                                }
                                                
                                                .progress-bar {
                                                    background: linear-gradient(90deg, #3b82f6, #1d4ed8) !important;
                                                }
                                                
                                                .bg-success.progress-bar {
                                                    background: linear-gradient(90deg, #10b981, #059669) !important;
                                                }
                                                
                                                .card-title {
                                                    color: #1f2937;
                                                    line-height: 1.4;
                                                }
                                                
                                                .badge {
                                                    font-size: 0.75rem;
                                                    font-weight: 600;
                                                    letter-spacing: 0.025em;
                                                }
                                                
                                                @media (max-width: 768px) {
                                                    .col-6 {
                                                        margin-bottom: 0.25rem;
                                                    }
                                                    
                                                    .card-body {
                                                        padding: 1rem !important;
                                                    }
                                                    
                                                    .fs-5 {
                                                        font-size: 1.1rem !important;
                                                    }
                                                }
                                                
                                                .fs-8 {
                                                    font-size: 0.7rem;
                                                }
                                            </style>

                                        @else
                                            <div class="text-center py-5">
                                                <div class="card border-0 shadow-sm mx-auto" style="max-width: 400px; border-radius: 16px;">
                                                    <div class="card-body p-5">
                                                        <div class="mb-4">
                                                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-info bg-opacity-10 mx-auto mb-3" 
                                                                style="width: 80px; height: 80px;">
                                                                <i class="fas fa-info-circle text-info" style="font-size: 2rem;"></i>
                                                            </div>
                                                        </div>
                                                        <h5 class="card-title mb-3 text-gray-900">No Bills Available</h5>
                                                        <p class="text-muted mb-0">No school bills are currently available for the selected student.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                                               
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Modal -->
        <!-- Payment Modal -->
            <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="fw-bold">Make Payment</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body scroll-y mx-5 mx-xl-10 my-7">
                            <form id="paymentForm" action="{{ route('schoolpayment.store') }}" method="POST">
                                @csrf
                                <input type="hidden" id="student_id" name="student_id">
                                <input type="hidden" id="class_id" name="class_id">
                                <input type="hidden" id="term_id" name="term_id">
                                <input type="hidden" id="session_id" name="session_id">
                                <input type="hidden" id="school_bill_id" name="school_bill_id">
                                <input type="hidden" id="actual_amount" name="actualAmount">
                                <input type="hidden" id="balance2" name="balance2">
                                <input type="hidden" id="last_amount_paid" name="last_amount_paid">

                                <!-- Bill Information (Read-only) -->
                                <div class="form-group mb-6">
                                    <label class="fw-semibold fs-6 mb-2">Bill Amount</label>
                                    <input type="text" id="amount_d" class="form-control form-control-sm" readonly>
                                </div>
                                
                                <div class="form-group mb-6">
                                    <label class="fw-semibold fs-6 mb-2">Amount Paid</label>
                                    <input type="text" id="amount_paid_d" class="form-control form-control-sm" readonly>
                                </div>
                                
                                <div class="form-group mb-6">
                                    <label class="fw-semibold fs-6 mb-2">Outstanding Balance</label>
                                    <input type="text" id="balance_d" class="form-control form-control-sm bg-warning bg-opacity-10" readonly>
                                    <div class="form-text text-muted">
                                        <small><i class="fas fa-info-circle me-1"></i>Payment amount cannot exceed this balance</small>
                                    </div>
                                </div>

                                <!-- Payment Input -->
                                <div class="form-group mb-6">
                                    <label class="required fw-semibold fs-6 mb-2">Enter Payment Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₦</span>
                                        <input type="text" 
                                            id="payment_amount" 
                                            name="payment_amount" 
                                            class="form-control form-control-sm @error('payment_amount') is-invalid @enderror" 
                                            placeholder="0.00" 
                                            required>
                                    </div>
                                    <input type="hidden" id="payment_amount2" name="payment_amount2">
                                    
                                    @error('payment_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Payment Method -->
                                <div class="form-group mb-6">
                                    <label class="required fw-semibold fs-6 mb-2">Payment Method</label>
                                    <select name="payment_method2" class="form-select form-select-sm @error('payment_method2') is-invalid @enderror" required>
                                        <option value="">Select Payment Method</option>
                                        <option value="Bank Deposit" {{ old('payment_method2') == 'Bank Deposit' ? 'selected' : '' }}>Bank Deposit / Bank Teller</option>
                                        <option value="School POS" {{ old('payment_method2') == 'School POS' ? 'selected' : '' }}>School POS/Cash</option>
                                        <option value="Bank Transfer" {{ old('payment_method2') == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                        <option value="Cheque" {{ old('payment_method2') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                                    </select>
                                    
                                    @error('payment_method2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Action Buttons -->
                                <div class="text-center pt-10">
                                    <button type="reset" class="btn btn-outline-secondary me-3" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary" id="submitPaymentBtn">
                                        <i class="fas fa-credit-card me-1"></i>
                                        Make Payment
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Modal -->
            <div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="messageModalTitle">Notification</h5>
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
                            <button type="button" class="btn btn-danger" id="confirmDeleteButton">Delete</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Optional CSS for confirmation modal -->
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
                    color: #1f2937;
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
                    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
                }
            </style>

            <!-- Optional CSS for better modal styling -->
            <style>
                #messageModal .modal-content {
                    border-radius: 12px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                }

                #messageModal .modal-header {
                    background: linear-gradient(90deg, #3b82f6, #1d4ed8);
                    color: white;
                    border-bottom: none;
                }

                #messageModal .modal-title {
                    font-weight: 600;
                }

                #messageModal .modal-body {
                    font-size: 1rem;
                    color: #1f2937;
                    text-align: center;
                    padding: 1.5rem;
                }

                #messageModal .modal-footer {
                    border-top: none;
                    justify-content: center;
                }

                #messageModal .btn-primary {
                    background: #3b82f6;
                    border: none;
                    border-radius: 8px;
                    padding: 0.5rem 1.5rem;
                    transition: all 0.3s ease;
                }

                #messageModal .btn-primary:hover {
                    background: #1d4ed8;
                    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
                }
            </style>



            <!-- Additional CSS for better validation styling -->
            <style>
                    .form-control.is-invalid {
                        border-color: #dc3545;
                        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
                    }

                    .invalid-feedback {
                        display: block !important;
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                    }

                    .form-control:focus {
                        border-color: #86b7fe;
                        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
                    }

                    .form-control.is-invalid:focus {
                        border-color: #dc3545;
                        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
                    }

                    #paymentModal .modal-body {
                        max-height: 70vh;
                        overflow-y: auto;
                    }

                    .input-group-text {
                        background-color: #f8f9fa;
                        border-color: #dee2e6;
                        font-weight: 600;
                    }

                    .bg-warning.bg-opacity-10 {
                        background-color: rgba(255, 193, 7, 0.1) !important;
                        border-color: rgba(255, 193, 7, 0.3) !important;
                    }
            </style>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize Bootstrap modals
    const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'), {
        backdrop: 'static', // Prevent closing by clicking outside
        keyboard: false // Prevent closing with ESC key
    });
    const paymentModal = document.getElementById('paymentModal');

    // Function to show message modal
    function showMessageModal(title, message, isSuccess = true) {
        document.getElementById('messageModalTitle').textContent = isSuccess ? 'Success' : 'Error';
        document.getElementById('messageModalBody').textContent = message;
        document.getElementById('messageModalTitle').parentElement.style.background = isSuccess 
            ? 'linear-gradient(90deg, #10b981, #059669)' 
            : 'linear-gradient(90deg, #ef4444, #b91c1c)';
        messageModal.show();
    }

    // Tab change event to toggle search input
    const searchInput = document.getElementById('searchInput');
    const paymentRecordsTab = document.getElementById('payment-records-tab');
    const schoolBillsTab = document.getElementById('school-bills-tab');

    function toggleSearchInput() {
        if (paymentRecordsTab.classList.contains('active')) {
            searchInput.disabled = {{ $studentpaymentbill->isEmpty() ? 'true' : 'false' }};
            searchInput.value = ''; // Clear search when switching to Payment Records
            searchInput.dispatchEvent(new Event('input')); // Trigger search to reset table
        } else {
            searchInput.disabled = true;
        }
    }

    paymentRecordsTab.addEventListener('shown.bs.tab', toggleSearchInput);
    schoolBillsTab.addEventListener('shown.bs.tab', toggleSearchInput);

    // Payment modal data population
    document.querySelectorAll('.make-payment').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('student_id').value = this.dataset.student_id;
            document.getElementById('class_id').value = this.dataset.class_id;
            document.getElementById('term_id').value = this.dataset.term_id;
            document.getElementById('session_id').value = this.dataset.session_id;
            document.getElementById('school_bill_id').value = this.dataset.school_bill_id;
            document.getElementById('actual_amount').value = this.dataset.amount_actual;
            document.getElementById('balance2').value = this.dataset.balance;
            document.getElementById('last_amount_paid').value = this.dataset.amount_paid.replace(/,/g, '');
            document.getElementById('amount_d').value = '₦ ' + this.dataset.amount;
            document.getElementById('amount_paid_d').value = '₦ ' + this.dataset.amount_paid;
            document.getElementById('balance_d').value = '₦ ' + this.dataset.balance;
            
            // Clear any previous payment amount and error messages
            document.getElementById('payment_amount').value = '';
            document.getElementById('payment_amount2').value = '';
            
            // Remove any existing error styling
            const paymentInput = document.getElementById('payment_amount');
            paymentInput.classList.remove('is-invalid');
            
            // Remove any existing error message
            const existingError = paymentInput.parentNode.querySelector('.invalid-feedback');
            if (existingError) {
                existingError.remove();
            }
        });
    });

    // Payment amount formatting and validation
    const paymentInput = document.getElementById('payment_amount');
    const paymentHidden = document.getElementById('payment_amount2');
    const paymentForm = document.getElementById('paymentForm');

    paymentInput.addEventListener('input', function () {
        let value = this.value.replace(/[^0-9.]/g, '');
        const parts = value.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        this.value = parts.join('.');
        
        const numericValue = parseFloat(value.replace(/,/g, '')) || 0;
        paymentHidden.value = numericValue;
        
        // Get the outstanding balance
        const balanceText = document.getElementById('balance2').value;
        const balanceValue = parseFloat(balanceText.replace(/[^0-9.,]/g, '').replace(/,/g, '')) || 0;
        
        // Remove existing error styling and message
        this.classList.remove('is-invalid');
        const existingError = this.parentNode.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }
        
        // Validate amount
        if (numericValue > 0 && numericValue > balanceValue) {
            this.classList.add('is-invalid');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = `Payment amount cannot exceed outstanding balance of ₦${balanceText}`;
            this.parentNode.appendChild(errorDiv);
        }
    });

    // Form submission validation with modal confirmation
    paymentForm.addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent default form submission
        const paymentAmount = parseFloat(paymentHidden.value) || 0;
        const balanceText = document.getElementById('balance2').value;
        const balanceValue = parseFloat(balanceText.replace(/[^0-9.,]/g, '').replace(/,/g, '')) || 0;
        
        // Validate payment amount
        if (paymentAmount <= 0) {
            paymentInput.classList.add('is-invalid');
            const existingError = paymentInput.parentNode.querySelector('.invalid-feedback');
            if (existingError) {
                existingError.remove();
            }
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = 'Please enter a valid payment amount';
            paymentInput.parentNode.appendChild(errorDiv);
            paymentInput.focus();
            return;
        }
        
        if (paymentAmount > balanceValue) {
            paymentInput.classList.add('is-invalid');
            const existingError = paymentInput.parentNode.querySelector('.invalid-feedback');
            if (existingError) {
                existingError.remove();
            }
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = `Payment amount cannot exceed outstanding balance of ₦${balanceText}`;
            paymentInput.parentNode.appendChild(errorDiv);
            paymentInput.focus();
            return;
        }
        
        // Show confirmation modal for payment
        document.getElementById('messageModalBody').textContent = `Are you sure you want to make a payment of ₦${paymentInput.value}?`;
        document.getElementById('messageModalTitle').textContent = 'Confirm Payment';
        document.getElementById('messageModalTitle').parentElement.style.background = 'linear-gradient(90deg, #3b82f6, #1d4ed8)';
        const footer = document.querySelector('#messageModal .modal-footer');
        footer.innerHTML = `
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="confirmPaymentButton">Confirm</button>
        `;
        messageModal.show();

        // Handle confirm payment
        document.getElementById('confirmPaymentButton').onclick = () => {
            paymentForm.submit(); // Submit the form
        };
    });

    // Clear validation when payment modal is closed
    paymentModal.addEventListener('hidden.bs.modal', function () {
        paymentInput.classList.remove('is-invalid');
        const existingError = paymentInput.parentNode.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }
    });

    // Handle delete payment
    document.querySelectorAll('.delete-payment').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.dataset.url;
            const row = this.closest('tr');

            // Show confirmation modal
            confirmModal.show();

            // Handle confirm button click
            const confirmButton = document.getElementById('confirmDeleteButton');
            confirmButton.onclick = () => {
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    confirmModal.hide();
                    if (data.success) {
                        showMessageModal('Success', data.message);
                        // Remove the row from the table
                        row.remove();
                        // Update payment count
                        const paymentCount = document.getElementById('paymentCount');
                        if (paymentCount) {
                            const currentCount = parseInt(paymentCount.textContent) - 1;
                            paymentCount.textContent = currentCount;
                            if (currentCount === 0) {
                                document.getElementById('noDataAlert').style.display = 'block';
                                document.getElementById('paymentsTableBody').innerHTML = `
                                    <tr id="noDataRow">
                                        <td colspan="12" class="text-center">No payment records available.</td>
                                    </tr>
                                `;
                            }
                        }
                    } else {
                        showMessageModal('Error', data.message || 'Failed to delete record.', false);
                    }
                })
                .catch(error => {
                    confirmModal.hide();
                    console.error('Error:', error);
                    showMessageModal('Error', 'An error occurred while deleting the record.', false);
                });
            };
        });
    });

    // Handle cancel button in confirmation modal
    document.getElementById('cancelDeleteButton').addEventListener('click', () => {
        confirmModal.hide();
    });
});
</script>
@endsection
