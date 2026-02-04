@extends('layouts.master')
@section('content')
<?php
use Spatie\Permission\Models\Role;
?>
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Students</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Student Management</a></li>
                                <li class="breadcrumb-item active">Students</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->
            <style>
                .card {
                    border: none;
                    border-radius: 15px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    transition: transform 0.3s ease, box-shadow 0.3s ease;
                    margin-bottom: 20px;
                }

                .card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
                }

                .card-body {
                    padding: 25px;
                    text-align: center;
                }

                .card-icon {
                    font-size: 3rem;
                    margin-bottom: 15px;
                    display: block;
                }

                .card-title {
                    font-size: 0.95rem;
                    font-weight: 600;
                    color: #6c757d;
                    margin-bottom: 10px;
                }

                .card-text {
                    font-size: 2.5rem;
                    font-weight: bold;
                    margin: 0;
                }

                /* Color schemes for different card types */
                .population-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
                .staff-card { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
                .old-student-card { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
                .new-student-card { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; }
                .active-card { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; }
                .inactive-card { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333; }
                .male-card { background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); color: #333; }
                .female-card { background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); color: #333; }
                .christian-card { background: linear-gradient(135deg, #89f7fe 0%, #66a6ff 100%); color: white; }
                .muslim-card { background: linear-gradient(135deg, #fdbb2d 0%, #22c1c3 100%); color: white; }
                .other-religion-card { background: linear-gradient(135deg, #e3ffe7 0%, #d9e7ff 100%); color: #333; }

                /* Student Card Styling */
                .student-card {
                    border: 1px solid #e9ecef;
                    border-radius: 12px;
                    transition: all 0.3s ease;
                    margin-bottom: 20px;
                    background: white;
                    position: relative;
                    overflow: hidden;
                    height: 100%;
                }
                .student-card:hover {
                    border-color: #405189;
                    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
                    transform: translateY(-5px);
                }
                .student-card.selected {
                    border-color: #405189;
                    background-color: rgba(64, 81, 137, 0.02);
                }
                .student-card .card-body {
                    padding: 20px;
                }
                .student-card .avatar-container {
                    width: 80px;
                    height: 80px;
                    margin: 0 auto 15px auto;
                    position: relative;
                }
                .student-card .avatar {
                    width: 100%;
                    height: 100%;
                    border-radius: 50%;
                    object-fit: cover;
                    border: 3px solid #fff;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 28px;
                    font-weight: bold;
                    color: white;
                }
                .student-card .avatar-initials {
                    width: 100%;
                    height: 100%;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 28px;
                    font-weight: bold;
                    color: white;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }
                .student-card .student-name {
                    font-size: 16px;
                    font-weight: 600;
                    color: #495057;
                    margin-bottom: 5px;
                    text-align: center;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
                .student-card .student-admission {
                    font-size: 12px;
                    color: #6c757d;
                    margin-bottom: 8px;
                    text-align: center;
                }
                .student-card .student-details {
                    font-size: 12px;
                    color: #6c757d;
                    text-align: center;
                    line-height: 1.4;
                }
                .student-card .student-details div {
                    margin-bottom: 3px;
                }
                .student-card .action-buttons {
                    position: absolute;
                    top: 10px;
                    right: 10px;
                    display: flex;
                    gap: 5px;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }
                .student-card:hover .action-buttons {
                    opacity: 1;
                }
                .student-card .action-btn {
                    width: 30px;
                    height: 30px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 14px;
                    border: none;
                    cursor: pointer;
                    transition: all 0.2s ease;
                }
                .student-card .view-btn {
                    background-color: rgba(13, 110, 253, 0.1);
                    color: #0d6efd;
                }
                .student-card .edit-btn {
                    background-color: rgba(25, 135, 84, 0.1);
                    color: #198754;
                }
                .student-card .delete-btn {
                    background-color: rgba(220, 53, 69, 0.1);
                    color: #dc3545;
                }
                .student-card .action-btn:hover {
                    transform: scale(1.1);
                }
                .student-card .checkbox-container {
                    position: absolute;
                    top: 10px;
                    left: 10px;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }
                .student-card:hover .checkbox-container {
                    opacity: 1;
                }
                .student-card .form-check-input {
                    width: 18px;
                    height: 18px;
                    cursor: pointer;
                }
                .student-card .status-badge {
                    position: absolute;
                    top: 10px;
                    right: 10px;
                    font-size: 10px;
                    padding: 3px 8px;
                    border-radius: 12px;
                    z-index: 1;
                }
                .student-card .status-active {
                    background-color: rgba(25, 135, 84, 0.1);
                    color: #198754;
                    border: 1px solid rgba(25, 135, 84, 0.2);
                }
                .student-card .status-inactive {
                    background-color: rgba(255, 193, 7, 0.1);
                    color: #ffc107;
                    border: 1px solid rgba(255, 193, 7, 0.2);
                }
                /* Empty state */
                .empty-state {
                    text-align: center;
                    padding: 40px 20px;
                }
                .empty-state i {
                    font-size: 48px;
                    color: #6c757d;
                    margin-bottom: 20px;
                }
                .empty-state h5 {
                    color: #6c757d;
                    margin-bottom: 10px;
                }
                .empty-state p {
                    color: #6c757d;
                    margin-bottom: 0;
                }
                /* Loading state */
                .loading-state {
                    text-align: center;
                    padding: 40px 20px;
                }
                .loading-state .spinner-border {
                    width: 3rem;
                    height: 3rem;
                    margin-bottom: 20px;
                }

                /* View toggle buttons */
                .btn-group .btn-outline-secondary.active {
                    background-color: #405189;
                    color: white;
                    border-color: #405189;
                }

                /* View containers */
                .view-container {
                    transition: all 0.3s ease;
                }

                /* Progress Steps */
                .progress-steps {
                    display: flex;
                    justify-content: space-between;
                    position: relative;
                    margin-bottom: 30px;
                    counter-reset: step;
                }

                .progress-steps::before {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 0;
                    right: 0;
                    height: 2px;
                    background: #e9ecef;
                    transform: translateY(-50%);
                    z-index: 1;
                }

                .progress-steps .step {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    background: #e9ecef;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    color: #6c757d;
                    position: relative;
                    z-index: 2;
                    border: 2px solid #e9ecef;
                }

                .progress-steps .step.active {
                    background: #405189;
                    color: white;
                    border-color: #405189;
                }

                /* Modern Modal Styles */
                .modern-modal {
                    border-radius: 12px;
                    overflow: hidden;
                }

                .modern-header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 20px 30px;
                    border: none;
                }

                .modern-close {
                    background: rgba(255,255,255,0.1);
                    border-radius: 50%;
                    width: 30px;
                    height: 30px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    border: none;
                    opacity: 1;
                }

                .modern-close:hover {
                    background: rgba(255,255,255,0.2);
                }

                .modern-body {
                    padding: 0;
                }

                /* Student header with photo */
                .student-header {
                    background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);
                    padding: 30px;
                    text-align: center;
                    border-bottom: 1px solid #e9ecef;
                }

                .photo-container {
                    display: inline-block;
                    position: relative;
                }

                .photo-frame {
                    width: 150px;
                    height: 150px;
                    border-radius: 50%;
                    overflow: hidden;
                    border: 5px solid white;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                    position: relative;
                }

                .student-photo {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }

                /* Form sections */
                .form-section {
                    padding: 20px 30px;
                    border-bottom: 1px solid #e9ecef;
                }

                .section-header {
                    margin-bottom: 20px;
                    padding-bottom: 10px;
                    border-bottom: 2px solid #f0f0f0;
                }

                .section-header h5 {
                    color: #495057;
                    font-weight: 600;
                }

                /* Form grid for better layout */
                .form-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                    gap: 20px;
                }

                .form-group {
                    margin-bottom: 15px;
                }

                .form-label {
                    font-size: 12px;
                    color: #6c757d;
                    text-transform: uppercase;
                    font-weight: 600;
                    letter-spacing: 0.5px;
                    margin-bottom: 5px;
                    display: block;
                }

                .form-value {
                    padding: 8px 12px;
                    background: #f8f9fa;
                    border-radius: 6px;
                    font-weight: 500;
                    color: #495057;
                    min-height: 38px;
                    display: flex;
                    align-items: center;
                    border: 1px solid #e9ecef;
                }

                /* Special value styles */
                .highlight {
                    background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
                    border: 1px solid #667eea30;
                    color: #405189;
                    font-weight: 600;
                }

                .class-badge {
                    display: inline-block;
                    padding: 4px 12px;
                    background: #e7f4ff;
                    color: #0066cc;
                    border-radius: 20px;
                    font-size: 12px;
                    font-weight: 600;
                }

                /* Modern tabs */
                .modern-tabs {
                    background: #f8f9fa;
                    padding: 10px;
                    border-radius: 10px;
                    margin: 0 30px;
                    position: relative;
                    top: -15px;
                    border: 1px solid #e9ecef;
                }

                .modern-tabs .nav-link {
                    color: #6c757d;
                    padding: 12px 20px;
                    border-radius: 8px;
                    transition: all 0.3s ease;
                    border: none;
                    position: relative;
                }

                .modern-tabs .nav-link.active {
                    background: white;
                    color: #405189;
                    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
                }

                .modern-tabs .nav-link i {
                    margin-right: 8px;
                    font-size: 16px;
                }

                /* Modern footer */
                .modern-footer {
                    background: #f8f9fa;
                    border-top: 1px solid #e9ecef;
                    padding: 15px 30px;
                }

                /* Full-width form groups */
                .full-width {
                    grid-column: 1 / -1;
                }

                .address-field {
                    min-height: 60px;
                    white-space: pre-wrap;
                }

                /* Status badges */
                .gender-badge {
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                }

                .blood-group {
                    background: #fff5f5;
                    color: #e53e3e;
                    border: 1px solid #fed7d7;
                    padding: 4px 12px;
                    border-radius: 20px;
                    font-weight: 600;
                    font-size: 12px;
                }

                .occupation-badge {
                    background: #f0fff4;
                    color: #38a169;
                    border: 1px solid #c6f6d5;
                    padding: 4px 12px;
                    border-radius: 20px;
                    font-size: 12px;
                }

                /* Contact info styling */
                .contact {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }

                .school-name {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }

                .category-badges {
                    display: flex;
                    gap: 10px;
                    flex-wrap: wrap;
                }

                .category-badge {
                    padding: 6px 12px;
                    border-radius: 20px;
                    font-size: 12px;
                    font-weight: 600;
                    display: inline-flex;
                    align-items: center;
                    gap: 5px;
                    opacity: 0.5;
                    transition: all 0.3s ease;
                }

                .category-badge.active {
                    opacity: 1;
                }

                .category-badge.day {
                    background: #fff3cd;
                    color: #856404;
                    border: 1px solid #ffeaa7;
                }

                .category-badge.boarding {
                    background: #d4edda;
                    color: #155724;
                    border: 1px solid #c3e6cb;
                }

                .name-container {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                    gap: 15px;
                }
            </style>
            <div class="container">
                <h2 class="mb-4 text-center">School Dashboard Statistics</h2>

                <!-- Dashboard Statistics -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card population-card">
                            <div class="card-body">
                                <i class="fas fa-users card-icon"></i>
                                <h5 class="card-title">Total Population</h5>
                                <p class="card-text">{{ $total_population }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card staff-card">
                            <div class="card-body">
                                <i class="fas fa-chalkboard-teacher card-icon"></i>
                                <h5 class="card-title">Staff Count</h5>
                                <p class="card-text">{{ $staff_count }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card old-student-card">
                            <div class="card-body">
                                <i class="fas fa-user-graduate card-icon"></i>
                                <h5 class="card-title">Old Students</h5>
                                <p class="card-text">{{ $status_counts['Old Student'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card new-student-card">
                            <div class="card-body">
                                <i class="fas fa-user-plus card-icon"></i>
                                <h5 class="card-title">New Students</h5>
                                <p class="card-text">{{ $status_counts['New Student'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card active-card">
                            <div class="card-body">
                                <i class="fas fa-user-check card-icon"></i>
                                <h5 class="card-title">Active Students</h5>
                                <p class="card-text">{{ $student_status_counts['Active'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card inactive-card">
                            <div class="card-body">
                                <i class="fas fa-user-times card-icon"></i>
                                <h5 class="card-title">Inactive Students</h5>
                                <p class="card-text">{{ $student_status_counts['Inactive'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card male-card">
                            <div class="card-body">
                                <i class="fas fa-mars card-icon"></i>
                                <h5 class="card-title">Male Students</h5>
                                <p class="card-text">{{ $gender_counts['Male'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card female-card">
                            <div class="card-body">
                                <i class="fas fa-venus card-icon"></i>
                                <h5 class="card-title">Female Students</h5>
                                <p class="card-text">{{ $gender_counts['Female'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card christian-card">
                            <div class="card-body">
                                <i class="fas fa-cross card-icon"></i>
                                <h5 class="card-title">Christian Students</h5>
                                <p class="card-text">{{ $religion_counts['Christianity'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card muslim-card">
                            <div class="card-body">
                                <i class="fas fa-moon card-icon"></i>
                                <h5 class="card-title">Muslim Students</h5>
                                <p class="card-text">{{ $religion_counts['Islam'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card other-religion-card">
                            <div class="card-body">
                                <i class="fas fa-globe card-icon"></i>
                                <h5 class="card-title">Other Religions</h5>
                                <p class="card-text">{{ $religion_counts['Others'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Charts -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Students by Status</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="studentsByStatusChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Students by Active/Inactive Status</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="studentsByActiveStatusChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Display Success Message -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <!-- Display Error Message -->
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <!-- Display Validation Errors -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <!-- Unified Students View Container -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <div class="flex-grow-1 d-flex align-items-center gap-2">
                                <div class="form-check me-2">
                                    <input class="form-check-input" type="checkbox" value="option" id="checkAll">
                                    <label class="form-check-label" for="checkAll"></label>
                                </div>
                                <h5 class="card-title mb-0">Students <span class="badge bg-dark-subtle text-dark ms-1" id="totalStudents">0</span></h5>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="d-flex flex-wrap align-items-start gap-2">
                                    <!-- View Toggle Buttons -->
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-secondary active" id="tableViewBtn" onclick="toggleView('table')">
                                            <i class="fas fa-table"></i> Table
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" id="cardViewBtn" onclick="toggleView('card')">
                                            <i class="fas fa-th-large"></i> Cards
                                        </button>
                                    </div>

                                    @can('Delete student')
                                        <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()">
                                            <i class="ri-delete-bin-2-line"></i> Remove Selected
                                        </button>
                                    @endcan
                                    @can('Create student')
                                        <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                                            <i class="bi bi-plus-circle align-baseline me-1"></i> Add Student
                                        </button>
                                    @endcan

                                      <!-- Print/Export Report Button -->
                                    <button type="button" class="btn btn-soft-success" data-bs-toggle="modal" data-bs-target="#printStudentReportModal">
                                        <i class="ri-printer-line align-bottom me-1"></i> Print / Export Report
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Search and Filter Bar -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="search-box">
                                        <input type="text" class="form-control search" id="search-input" placeholder="Search by name or admission no">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control" id="schoolclass-filter" data-choices data-choices-search-false>
                                        <option value="all">All Classes</option>
                                        @foreach ($schoolclasses as $class)
                                            <option value="{{ $class->id }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-control" id="status-filter" data-choices data-choices-search-false>
                                        <option value="all">All Statuses</option>
                                        <option value="1">Old Student</option>
                                        <option value="2">New Student</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-control" id="gender-filter" data-choices data-choices-search-false>
                                        <option value="all">All Genders</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-secondary w-100" onclick="filterData();">
                                        <i class="bi bi-funnel align-baseline me-1"></i> Filter
                                    </button>
                                </div>
                            </div>

                            <!-- Table View (Default - Visible) -->
                            <div id="tableView" class="view-container">
                                <div class="table-responsive">
                                    <table class="table table-centered align-middle table-nowrap mb-0" id="studentTable">
                                        <thead class="table-active">
                                            <tr>
                                                <th>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="option" id="checkAllTable">
                                                        <label class="form-check-label" for="checkAllTable"></label>
                                                    </div>
                                                </th>
                                                <th class="sort cursor-pointer" data-sort="name">Student</th>
                                                <th class="sort cursor-pointer" data-sort="admissionNo">Admission No</th>
                                                <th class="sort cursor-pointer" data-sort="class">Class</th>
                                                <th class="sort cursor-pointer" data-sort="status">Status</th>
                                                <th class="sort cursor-pointer" data-sort="gender">Gender</th>
                                                <th class="sort cursor-pointer" data-sort="datereg">Registered</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list form-check-all" id="studentTableBody">
                                            <!-- JS renders rows here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Cards View (Hidden by default) -->
                            <div id="cardView" class="view-container d-none">
                                <div class="row" id="studentsCardsContainer">
                                    <!-- Students will be rendered here as cards -->
                                </div>
                            </div>

                            <!-- Pagination -->
                            <div class="row mt-3 align-items-center" id="pagination-element">
                                <div class="col-sm">
                                    <div class="text-muted text-center text-sm-start">
                                        Showing <span class="fw-semibold" id="showingCount">0</span> of <span class="fw-semibold" id="totalCount">0</span> Results
                                    </div>
                                </div>
                                <div class="col-sm-auto mt-3 mt-sm-0">
                                    <div class="pagination-wrap hstack gap-2 justify-content-center">
                                        <a class="page-item pagination-prev disabled" href="javascript:void(0);" id="prevPage">
                                            <i class="mdi mdi-chevron-left align-middle"></i>
                                        </a>
                                        <ul class="pagination listjs-pagination mb-0" id="paginationLinks"></ul>
                                        <a class="page-item pagination-next" href="javascript:void(0);" id="nextPage">
                                            <i class="mdi mdi-chevron-right align-middle"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    <!-- ================================================= -->
    <!--        PRINT / EXPORT REPORT MODAL               -->
    <!-- ================================================= -->
    {{-- <div class="modal fade" id="printStudentReportModal" tabindex="-1" aria-labelledby="printStudentReportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-soft-success">
                    <h5 class="modal-title" id="printStudentReportModalLabel">
                        <i class="ri-printer-line me-2"></i> Generate Student Report
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form id="printReportForm">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Class</label>
                                <select class="form-select" name="class_id">
                                    <option value="">— All Classes —</option>
                                    @foreach ($schoolclasses as $class)
                                        <option value="{{ $class->id }}">{{ $class->class_display }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="">— All —</option>
                                    <option value="1">Old Students</option>
                                    <option value="2">New Students</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Select Columns to Include</label>
                            <div class="row g-3">
                                @php
                                    $availableColumns = [
                                        'photo'          => 'Photo',
                                        'admissionNo'    => 'Admission No',
                                        'fullname'       => 'Full Name',
                                        'gender'         => 'Gender',
                                        'dateofbirth'    => 'Date of Birth',
                                        'age'            => 'Age',
                                        'class'          => 'Class / Arm',
                                        'status'         => 'Student Status',
                                        'admission_date' => 'Admission Date',
                                        'phone_number'   => 'Phone Number',
                                        'state'          => 'State of Origin',
                                        'local'          => 'LGA',
                                        'religion'       => 'Religion',
                                        'blood_group'    => 'Blood Group',
                                        'father_name'    => "Father's Name",
                                        'mother_name'    => "Mother's Name",
                                        'guardian_phone' => 'Guardian Phone',
                                    ];
                                @endphp
                                @foreach ($availableColumns as $key => $label)
                                    <div class="col-md-4 col-sm-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="columns[]" value="{{ $key }}" id="col_{{ $key }}"
                                                {{ in_array($key, ['fullname','admissionNo','class','gender']) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="col_{{ $key }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Export Format</label>
                            <div class="d-flex gap-3 flex-wrap">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" id="format_pdf" value="pdf" checked>
                                    <label class="form-check-label" for="format_pdf">
                                        <i class="ri-file-pdf-2-line text-danger me-1"></i> PDF
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" id="format_excel" value="excel">
                                    <label class="form-check-label" for="format_excel">
                                        <i class="ri-file-excel-2-line text-success me-1"></i> Excel
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info small mb-0">
                            <i class="ri-information-fill me-2"></i>
                            Only students matching the selected filters will be included.
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="generateReportBtn">
                        <i class="ri-printer-line me-1"></i> Generate & Download
                    </button>
                </div>
            </div>
        </div>
    </div> --}}

    <!-- Print/Export Report Modal -->
<div class="modal fade" id="printStudentReportModal" tabindex="-1" aria-labelledby="printStudentReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-soft-success">
                <h5 class="modal-title" id="printStudentReportModalLabel">
                    <i class="ri-printer-line me-2"></i> Generate Student Report
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="printReportForm">
                    <!-- Filters Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Class</label>
                            <select class="form-select" name="class_id">
                                <option value="">— All Classes —</option>
                                @foreach ($schoolclasses as $class)
                                    <option value="{{ $class->id }}">{{ $class->class_display }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="">— All —</option>
                                <option value="1">Old Students</option>
                                <option value="2">New Students</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Column Selection with Drag & Drop -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="ri-draggable me-1"></i> Select & Arrange Columns (Drag to reorder)
                        </label>
                        <div class="row g-3" id="columnsContainer">
                            <input type="hidden" name="columns_order" id="columnsOrderInput" value="">
                            @php
                                $availableColumns = [
                                    'photo'          => 'Photo',
                                    'admissionNo'    => 'Admission No',
                                    'fullname'       => 'Full Name',
                                    'gender'         => 'Gender',
                                    'dateofbirth'    => 'Date of Birth',
                                    'age'            => 'Age',
                                    'class'          => 'Class / Arm',
                                    'status'         => 'Student Status',
                                    'admission_date' => 'Admission Date',
                                    'phone_number'   => 'Phone Number',
                                    'state'          => 'State of Origin',
                                    'local'          => 'LGA',
                                    'religion'       => 'Religion',
                                    'blood_group'    => 'Blood Group',
                                    'father_name'    => "Father's Name",
                                    'mother_name'    => "Mother's Name",
                                    'guardian_phone' => 'Guardian Phone',
                                ];
                            @endphp
                            @foreach ($availableColumns as $key => $label)
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-check border rounded p-2 mb-2 bg-light cursor-move">
                                        <input class="form-check-input" type="checkbox" name="columns[]" value="{{ $key }}" id="col_{{ $key }}"
                                            {{ in_array($key, ['admissionNo','fullname','class','gender']) ? 'checked' : '' }}>
                                        <label class="form-check-label w-100 cursor-move" for="col_{{ $key }}">
                                            <i class="ri-draggable me-1"></i> {{ $label }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted">Drag columns to arrange their order in the report</small>
                    </div>

                    <!-- Report Header Options -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="ri-file-info-line me-2"></i> Report Header Options</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" role="switch" name="include_header" id="includeHeader" checked>
                                        <label class="form-check-label" for="includeHeader">
                                            <i class="ri-building-line me-1"></i> Include School Header
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" role="switch" name="include_logo" id="includeLogo" checked>
                                        <label class="form-check-label" for="includeLogo">
                                            <i class="ri-image-line me-1"></i> Include School Logo
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="orientation" class="form-label">Page Orientation</label>
                                        <select class="form-select" name="orientation" id="orientation">
                                            <option value="portrait">Portrait</option>
                                            <option value="landscape">Landscape</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Export Format -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Export Format</label>
                        <div class="d-flex gap-3 flex-wrap">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format" id="format_pdf" value="pdf" checked>
                                <label class="form-check-label" for="format_pdf">
                                    <i class="ri-file-pdf-2-line text-danger me-1"></i> PDF
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format" id="format_excel" value="excel">
                                <label class="form-check-label" for="format_excel">
                                    <i class="ri-file-excel-2-line text-success me-1"></i> Excel
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="alert alert-info small mb-0">
                        <div class="d-flex align-items-center">
                            <i class="ri-information-fill me-2"></i>
                            <div>
                                <strong>Preview:</strong>
                                <span id="columnOrderPreview">admissionNo, fullname, class, gender</span>
                                <br>
                                <small>Only students matching the selected filters will be included.</small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="generateReportBtn">
                    <i class="ri-printer-line me-1"></i> Generate & Download
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add SortableJS library -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>

<style>
    .cursor-move {
        cursor: move;
    }

    .sortable-ghost {
        opacity: 0.4;
        background-color: #f8f9fa;
    }

    .sortable-chosen {
        background-color: #405189 !important;
        color: white !important;
    }

    .sortable-chosen .form-check-label {
        color: white !important;
    }
</style>

        <!-- Add Student Modal -->
        <div id="addStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-user-plus me-2"></i>
                            Student Registration
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form class="tablelist-form" id="addStudentForm" enctype="multipart/form-data" autocomplete="off" method="POST" action="{{ route('student.store') }}">
                        @csrf
                        <div class="modal-body p-4">
                            <!-- Progress Steps -->
                            <div class="progress-steps mb-4">
                                <div class="step active">1</div>
                                <div class="step">2</div>
                                <div class="step">3</div>
                                <div class="step">4</div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <!-- Section A: Academic Details -->
                                    <div class="card">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Academic Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Admission Number Mode <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="admissionMode" id="admissionAuto" value="auto" required onchange="toggleAdmissionInput()">
                                                        <label class="form-check-label" for="admissionAuto">
                                                            <i class="fas fa-magic me-1"></i>Auto Generate
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="admissionMode" id="admissionManual" value="manual" required onchange="toggleAdmissionInput()">
                                                        <label class="form-check-label" for="admissionManual">
                                                            <i class="fas fa-edit me-1"></i>Manual Entry
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="admissionNo" class="form-label">Admission Number <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <select class="form-control" id="admissionYear" name="admissionYear" required onchange="updateAdmissionNumber()">
                                                        @for ($year = date('Y'); $year >= date('Y') - 5; $year--)
                                                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                                        @endfor
                                                    </select>
                                                    <input type="text" id="admissionNo" name="admissionNo" class="form-control" placeholder="TCC/YYYY/0001" required>
                                                    <small class="form-text text-muted w-100 mt-1">Format: TCC/YYYY/0001 (e.g., TCC/2024/0871)</small>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="admissionDate" class="form-label">Admission Date <span class="text-danger">*</span></label>
                                                <input type="date" id="admissionDate" name="admissionDate" class="form-control" required max="{{ date('Y-m-d') }}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="schoolclassid" class="form-label">Class <span class="text-danger">*</span></label>
                                                <select id="schoolclassid" name="schoolclassid" class="form-control" required>
                                                    <option value="">Select Class</option>
                                                    @foreach ($schoolclasses as $class)
                                                        <option value="{{ $class->id }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="termid" class="form-label">Term <span class="text-danger">*</span></label>
                                                        <select id="termid" name="termid" class="form-control" required>
                                                            <option value="">Select Term</option>
                                                            @foreach ($schoolterms as $term)
                                                                <option value="{{ $term->id }}">{{ $term->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="sessionid" class="form-label">Session <span class="text-danger">*</span></label>
                                                        <select id="sessionid" name="sessionid" class="form-control" required>
                                                            <option value="">Select Session</option>
                                                            @foreach ($schoolsessions as $session)
                                                                <option value="{{ $session->id }}">{{ $session->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Student Status <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="statusId" id="statusOld" value="1" required>
                                                        <label class="form-check-label" for="statusOld">
                                                            <i class="fas fa-user-clock me-1"></i>Old Student
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="statusId" id="statusNew" value="2" required>
                                                        <label class="form-check-label" for="statusNew">
                                                            <i class="fas fa-user-plus me-1"></i>New Student
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Student Activity Status <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="student_status" id="statusActive" value="Active" required>
                                                        <label class="form-check-label" for="statusActive">
                                                            <i class="fas fa-check-circle text-success me-1"></i>Active
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="student_status" id="statusInactive" value="Inactive" required>
                                                        <label class="form-check-label" for="statusInactive">
                                                            <i class="fas fa-pause-circle text-warning me-1"></i>Inactive
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="student_category" class="form-label">Student Category <span class="text-danger">*</span></label>
                                                <select id="student_category" name="student_category" class="form-control" required>
                                                    <option value="">Select Category</option>
                                                    <option value="Day">Day Student</option>
                                                    <option value="Boarding">Boarding Student</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Personal Details -->
                                <div class="col-md-6">
                                    <!-- Section B: Student's Personal Details -->
                                    <div class="card">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0"><i class="fas fa-user me-2"></i>Personal Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3 text-center">
                                                <div class="upload-area border border-2 border-dashed border-primary rounded p-3">
                                                    <img id="addStudentAvatar" src="https://via.placeholder.com/120x120/667eea/ffffff?text=Photo" alt="Avatar Preview" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #667eea; box-shadow: 0 4px 8px rgba(0,0,0,0.1);" />
                                                    <div>
                                                        <label for="avatar" class="btn btn-outline-primary btn-sm">
                                                            <i class="fas fa-camera me-1"></i>Choose Photo
                                                        </label>
                                                        <input type="file" id="avatar" name="avatar" class="d-none" accept=".png,.jpg,.jpeg" onchange="previewImage(this)">
                                                        <div class="form-text mt-2">Max 2MB (PNG, JPG, JPEG)</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="mb-3">
                                                    <label for="title" class="form-label">Title</label>
                                                    <select id="title" name="title" class="form-control">
                                                        <option value="">Select</option>
                                                        <option value="Master">Master</option>
                                                        <option value="Miss">Miss</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="lastname" class="form-label">Last Name <span class="text-danger">*</span></label>
                                                    <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Last name" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="firstname" class="form-label">First Name <span class="text-danger">*</span></label>
                                                    <input type="text" id="firstname" name="firstname" class="form-control" placeholder="First name" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="othername" class="form-label">Other Names<span class="text-danger">*</span></label>
                                                <input type="text" id="othername" name="othername" class="form-control" placeholder="Middle name(s)">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Gender <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="gender" id="genderMale" value="Male" required>
                                                        <label class="form-check-label" for="genderMale">
                                                            <i class="fas fa-male text-primary me-1"></i>Male
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="Female" required>
                                                        <label class="form-check-label" for="genderFemale">
                                                            <i class="fas fa-female text-danger me-1"></i>Female
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="dateofbirth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                                        <input type="date" id="addDOB" name="dateofbirth" class="form-control" required onchange="calculateAge(this.value, 'addAgeInput')">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="addAgeInput" class="form-label">Age <span class="text-danger">*</span></label>
                                                        <input type="number" id="addAgeInput" name="age" class="form-control" readonly required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="placeofbirth" class="form-label">Place of Birth <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-primary text-white">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                    </span>
                                                    <input type="text" id="placeofbirth" name="placeofbirth" class="form-control" placeholder="e.g., Lagos, Nigeria" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="phone_number" class="form-label">Phone Number</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-primary text-white">
                                                        <i class="fas fa-phone"></i>
                                                    </span>
                                                    <input type="text" id="phone_number" name="phone_number" class="form-control" placeholder="+234 xxx xxx xxxx">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-primary text-white">
                                                        <i class="fas fa-envelope"></i>
                                                    </span>
                                                    <input type="email" id="email" name="email" class="form-control" placeholder="student@example.com">
                                                </div>
                                            </div>
                                           <div class="mb-3">
                                                <label for="future_ambition" class="form-label">Future Ambition <span class="text-danger">*</span></label>
                                                <textarea id="future_ambition" name="future_ambition" class="form-control" rows="2" placeholder="Enter future ambition" required></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label for="permanent_address" class="form-label">Permanent Address <span class="text-danger">*</span></label>
                                                <textarea id="permanent_address" name="permanent_address" class="form-control" rows="2" placeholder="Enter permanent address" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Additional Information, Parent/Guardian Details, and Previous School Details -->
                            <div class="row">
                                <div class="col-md-6">
                                    <!-- Section C: Additional Details -->
                                    <div class="card">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Additional Information</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-10">
                                                    <div class="mb-3">
                                                        <label for="nationality" class="form-label">Nationality <span class="text-danger">*</span></label>
                                                        <input type="text" id="nationality" name="nationality" class="form-control" placeholder="e.g., Nigerian" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="addState" class="form-label">State of Origin <span class="text-danger">*</span></label>
                                                        <select id="addState" name="state" class="form-control" required>
                                                            <option value="">Select State</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="addLocal" class="form-label">Local Government <span class="text-danger">*</span></label>
                                                        <select id="addLocal" name="local" class="form-control" required>
                                                            <option value="">Select LGA</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="city" class="form-label">City</label>
                                                        <input type="text" id="city" name="city" class="form-control" placeholder="Enter city">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="religion" class="form-label">Religion <span class="text-danger">*</span></label>
                                                        <select id="religion" name="religion" class="form-control" required>
                                                            <option value="">Select Religion</option>
                                                            <option value="Christianity">Christianity</option>
                                                            <option value="Islam">Islam</option>
                                                            <option value="Others">Others</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="blood_group" class="form-label">Blood Group</label>
                                                        <select id="blood_group" name="blood_group" class="form-control">
                                                            <option value="">Select Blood Group</option>
                                                            <option value="A+">A+</option>
                                                            <option value="A-">A-</option>
                                                            <option value="B+">B+</option>
                                                            <option value="B-">B-</option>
                                                            <option value="AB+">AB+</option>
                                                            <option value="AB-">AB-</option>
                                                            <option value="O+">O+</option>
                                                            <option value="O-">O-</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="mother_tongue" class="form-label">Mother Tongue</label>
                                                        <input type="text" id="mother_tongue" name="mother_tongue" class="form-control" placeholder="Native language">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="nin_number" class="form-label">NIN Number</label>
                                                        <input type="text" id="nin_number" name="nin_number" class="form-control" placeholder="11-digit NIN" maxlength="11">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="school_house" class="form-label">School House <span class="text-danger">*</span></label>
                                                        <select id="school_house" name="schoolhouseid" class="form-control" required>
                                                            <option value="">Select School House</option>
                                                            @foreach ($schoolhouses as $schoolhouse)
                                                                <option value="{{ $schoolhouse->id }}">{{ $schoolhouse->house }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <!-- Section D: Parent/Guardian Details -->
                                    <div class="card">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0"><i class="fas fa-users me-2"></i>Parent/Guardian Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="father_name" class="form-label">Father's Name</label>
                                                <input type="text" id="father_name" name="father_name" class="form-control" placeholder="Father's full name">
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="father_phone" class="form-label">Father's Phone</label>
                                                        <input type="text" id="father_phone" name="father_phone" class="form-control" placeholder="+234 xxx xxx xxxx">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="father_occupation" class="form-label">Father's Occupation</label>
                                                        <input type="text" id="father_occupation" name="father_occupation" class="form-control" placeholder="Occupation">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="father_city" class="form-label">Father's City</label>
                                                <input type="text" id="father_city" name="father_city" class="form-control" placeholder="City of residence">
                                            </div>
                                            <div class="mb-3">
                                                <label for="mother_name" class="form-label">Mother's Name</label>
                                                <input type="text" id="mother_name" name="mother_name" class="form-control" placeholder="Mother's full name">
                                            </div>
                                            <div class="mb-3">
                                                <label for="mother_phone" class="form-label">Mother's Phone</label>
                                                <input type="text" id="mother_phone" name="mother_phone" class="form-control" placeholder="+234 xxx xxx xxxx">
                                            </div>
                                            <div class="mb-3">
                                                <label for="parent_email" class="form-label">Parent's Email</label>
                                                <input type="email" id="parent_email" name="parent_email" class="form-control" placeholder="parent@example.com">
                                            </div>
                                            <div class="mb-3">
                                                <label for="parent_address" class="form-label">Parent's Address</label>
                                                <textarea id="parent_address" name="parent_address" class="form-control" rows="2" placeholder="Parent's address"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Section E: Previous School Details -->
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white">
                                            <h6 class="mb-0"><i class="fas fa-school me-2"></i>Previous School Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="last_school" class="form-label">Last School Attended</label>
                                                <input type="text" id="last_school" name="last_school" class="form-control" placeholder="Previous school name">
                                            </div>
                                            <div class="mb-3">
                                                <label for="last_class" class="form-label">Last Class Attended</label>
                                                <input type="text" id="last_class" name="last_class" class="form-control" placeholder="e.g., JSS 2">
                                            </div>
                                            <div class="mb-3">
                                                <label for="reason_for_leaving" class="form-label">Reason for Leaving</label>
                                                <textarea id="reason_for_leaving" name="reason_for_leaving" class="form-control" rows="2" placeholder="Reason for leaving previous school"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-danger d-none" id="alert-error-msg"></div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" id="add-btn">
                                <i class="fas fa-save me-1"></i>Register Student
                            </button>
                            <button type="button" class="btn btn-success" onclick="printStudentDetails()">
                                <i class="fas fa-print me-1"></i>Print PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Edit Student Modal -->
        <div id="editStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-user-edit me-2"></i>Edit Student
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form class="tablelist-form" id="editStudentForm" enctype="multipart/form-data" autocomplete="off" method="POST" action="{{ route('student.update', ':id') }}">
                        @csrf
                        @method('PATCH')
                        <div class="modal-body p-4">
                            <input type="hidden" id="editStudentId" name="id">

                            <!-- Progress Steps - Fixed: No active steps by default -->
                            <div class="progress-steps mb-4">
                                <div class="step">1</div>
                                <div class="step">2</div>
                                <div class="step">3</div>
                                <div class="step">4</div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                   <!-- Academic Details section -->
                                    <div class="card">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Academic Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Admission Number Mode <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="admissionMode" id="editAdmissionAuto" value="auto" required onchange="toggleAdmissionInput('edit')">
                                                        <label class="form-check-label" for="editAdmissionAuto">
                                                            <i class="fas fa-magic me-1"></i>Auto Generate
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="admissionMode" id="editAdmissionManual" value="manual" required onchange="toggleAdmissionInput('edit')">
                                                        <label class="form-check-label" for="editAdmissionManual">
                                                            <i class="fas fa-edit me-1"></i>Manual Entry
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="editAdmissionNo" class="form-label">Admission Number <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <select class="form-control" id="editAdmissionYear" name="admissionYear" required onchange="updateAdmissionNumber('edit')">
                                                        @for ($year = date('Y'); $year >= date('Y') - 5; $year--)
                                                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                                        @endfor
                                                    </select>
                                                    <input type="text" id="editAdmissionNo" name="admissionNo" class="form-control" placeholder="TCC/YYYY/0001" required>
                                                    <small class="form-text text-muted w-100 mt-1">Format: TCC/YYYY/0001 (e.g., TCC/2024/0871)</small>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="editAdmissionDate" class="form-label">Admission Date <span class="text-danger">*</span></label>
                                                <input type="date" id="editAdmissionDate" name="admissionDate" class="form-control" required max="{{ date('Y-m-d') }}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="editSchoolclassid" class="form-label">Class <span class="text-danger">*</span></label>
                                                <select id="editSchoolclassid" name="schoolclassid" class="form-control" required>
                                                    <option value="">Select Class</option>
                                                    @foreach ($schoolclasses as $class)
                                                        <option value="{{ $class->id }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="editTermid" class="form-label">Term <span class="text-danger">*</span></label>
                                                        <select id="editTermid" name="termid" class="form-control" required>
                                                            <option value="">Select Term</option>
                                                            @foreach ($schoolterms as $term)
                                                                <option value="{{ $term->id }}">{{ $term->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="editSessionid" class="form-label">Session <span class="text-danger">*</span></label>
                                                        <select id="editSessionid" name="sessionid" class="form-control" required>
                                                            <option value="">Select Session</option>
                                                            @foreach ($schoolsessions as $session)
                                                                <option value="{{ $session->id }}">{{ $session->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Student Status <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="statusId" id="editStatusOld" value="1" required>
                                                        <label class="form-check-label" for="editStatusOld">
                                                            <i class="fas fa-user-clock me-1"></i>Old Student
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="statusId" id="editStatusNew" value="2" required>
                                                        <label class="form-check-label" for="editStatusNew">
                                                            <i class="fas fa-user-plus me-1"></i>New Student
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Student Activity Status <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="student_status" id="editStatusActive" value="Active" required>
                                                        <label class="form-check-label" for="editStatusActive">
                                                            <i class="fas fa-check-circle text-success me-1"></i>Active
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="student_status" id="editStatusInactive" value="Inactive" required>
                                                        <label class="form-check-label" for="editStatusInactive">
                                                            <i class="fas fa-pause-circle text-warning me-1"></i>Inactive
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="editStudentCategory" class="form-label">Student Category <span class="text-danger">*</span></label>
                                                <select id="editStudentCategory" name="student_category" class="form-control" required>
                                                    <option value="">Select Category</option>
                                                    <option value="Day">Day Student</option>
                                                    <option value="Boarding">Boarding Student</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Personal Details -->
                                <div class="col-md-6">
                                 <!-- Personal Details section -->
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="fas fa-user me-2"></i>Personal Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3 text-center">
                                            <div class="upload-area border border-2 border-dashed border-primary rounded p-3">
                                                <img id="editStudentAvatar" src="{{ asset('theme/layouts/assets/media/avatars/blank.png') }}" alt="Avatar Preview" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #667eea; box-shadow: 0 4px 8px rgba(0,0,0,0.1);" />
                                                <div>
                                                    <label for="editAvatar" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-camera me-1"></i>Choose Photo
                                                    </label>
                                                    <input type="file" id="editAvatar" name="avatar" class="d-none" accept=".png,.jpg,.jpeg" onchange="previewImage(this, 'editStudentAvatar')">
                                                    <div class="form-text mt-2">Max 2MB (PNG, JPG, JPEG)</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="editTitle" class="form-label">Title</label>
                                                    <select id="editTitle" name="title" class="form-control">
                                                        <option value="">Select</option>
                                                        <option value="Master">Master</option>
                                                        <option value="Miss">Miss</option>
                                                    </select>
                                                </div>
                                            </div>
                                             <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="editLastname" class="form-label">Last Name <span class="text-danger">*</span></label>
                                                    <input type="text" id="editLastname" name="lastname" class="form-control" placeholder="Last name" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="editFirstname" class="form-label">First Name <span class="text-danger">*</span></label>
                                                    <input type="text" id="editFirstname" name="firstname" class="form-control" placeholder="First name" required>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="mb-3">
                                            <label for="editOthername" class="form-label">Other Names</label>
                                            <input type="text" id="editOthername" name="othername" class="form-control" placeholder="Middle name(s)" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="gender" id="editGenderMale" value="Male" required>
                                                    <label class="form-check-label" for="editGenderMale">
                                                        <i class="fas fa-male text-primary me-1"></i>Male
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="gender" id="editGenderFemale" value="Female" required>
                                                    <label class="form-check-label" for="editGenderFemale">
                                                        <i class="fas fa-female text-danger me-1"></i>Female
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editDOB" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                                    <input type="date" id="editDOB" name="dateofbirth" class="form-control" required onchange="calculateAge(this.value, 'editAgeInput')">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editAgeInput" class="form-label">Age <span class="text-danger">*</span></label>
                                                    <input type="number" id="editAgeInput" name="age" class="form-control" readonly required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editPlaceofbirth" class="form-label">Place of Birth <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-primary text-white">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </span>
                                                <input type="text" id="editPlaceofbirth" name="placeofbirth" class="form-control" placeholder="e.g., Lagos, Nigeria" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editPhoneNumber" class="form-label">Phone Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-primary text-white">
                                                    <i class="fas fa-phone"></i>
                                                </span>
                                                <input type="text" id="editPhoneNumber" name="phone_number" class="form-control" placeholder="+234 xxx xxx xxxx">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editEmail" class="form-label">Email</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-primary text-white">
                                                    <i class="fas fa-envelope"></i>
                                                </span>
                                                <input type="email" id="editEmail" name="email" class="form-control" placeholder="student@example.com">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editFutureAmbition" class="form-label">Future Ambition <span class="text-danger">*</span></label>
                                            <textarea id="editFutureAmbition" name="future_ambition" class="form-control" rows="2" placeholder="Enter future ambition" required></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editPermanentAddress" class="form-label">Permanent Address <span class="text-danger">*</span></label>
                                            <textarea id="editPermanentAddress" name="permanent_address" class="form-control" rows="2" placeholder="Enter permanent address" required></textarea>
                                        </div>
                                    </div>
                                </div>
                                </div>
                                <div class="col-md-6">
                            <!-- Additional Information section -->
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Additional Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-10">
                                                <div class="mb-3">
                                                    <label for="editNationality" class="form-label">Nationality <span class="text-danger">*</span></label>
                                                    <input type="text" id="editNationality" name="nationality" class="form-control" placeholder="e.g., Nigerian" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editState" class="form-label">State of Origin <span class="text-danger">*</span></label>
                                                    <select id="editState" name="state" class="form-control" required>
                                                        <option value="">Select State</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editLocal" class="form-label">Local Government <span class="text-danger">*</span></label>
                                                    <select id="editLocal" name="local" class="form-control" required>
                                                        <option value="">Select LGA</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editCity" class="form-label">City</label>
                                                    <input type="text" id="editCity" name="city" class="form-control" placeholder="Enter city">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editReligion" class="form-label">Religion <span class="text-danger">*</span></label>
                                                    <select id="editReligion" name="religion" class="form-control" required>
                                                        <option value="">Select Religion</option>
                                                        <option value="Christianity">Christianity</option>
                                                        <option value="Islam">Islam</option>
                                                        <option value="Others">Others</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editBloodGroup" class="form-label">Blood Group</label>
                                                    <select id="editBloodGroup" name="blood_group" class="form-control">
                                                        <option value="">Select Blood Group</option>
                                                        <option value="A+">A+</option>
                                                        <option value="A-">A-</option>
                                                        <option value="B+">B+</option>
                                                        <option value="B-">B-</option>
                                                        <option value="AB+">AB+</option>
                                                        <option value="AB-">AB-</option>
                                                        <option value="O+">O+</option>
                                                        <option value="O-">O-</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editMotherTongue" class="form-label">Mother Tongue</label>
                                                    <input type="text" id="editMotherTongue" name="mother_tongue" class="form-control" placeholder="Native language">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editNinNumber" class="form-label">NIN Number</label>
                                                    <input type="text" id="editNinNumber" name="nin_number" class="form-control" placeholder="11-digit NIN" maxlength="11">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editSchoolHouse" class="form-label">School House <span class="text-danger">*</span></label>
                                                    <select id="editSchoolHouse" name="schoolhouseid" class="form-control" required>
                                                        <option value="">Select School House</option>
                                                        @foreach ($schoolhouses as $schoolhouse)
                                                            <option value="{{ $schoolhouse->id }}">{{ $schoolhouse->house }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                    <!-- Section D: Parent/Guardian Details -->
                                    <div class="card">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0"><i class="fas fa-users me-2"></i>Parent/Guardian Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="editFatherName" class="form-label">Father's Name</label>
                                                <input type="text" id="editFatherName" name="father_name" class="form-control" placeholder="Father's full name">
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="editFatherPhone" class="form-label">Father's Phone</label>
                                                        <input type="text" id="editFatherPhone" name="father_phone" class="form-control" placeholder="+234 xxx xxx xxxx">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="editFatherOccupation" class="form-label">Father's Occupation</label>
                                                        <input type="text" id="editFatherOccupation" name="father_occupation" class="form-control" placeholder="Occupation">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="editFatherCity" class="form-label">Father's City</label>
                                                <input type="text" id="editFatherCity" name="father_city" class="form-control" placeholder="City of residence">
                                            </div>
                                            <div class="mb-3">
                                                <label for="editMotherName" class="form-label">Mother's Name</label>
                                                <input type="text" id="editMotherName" name="mother_name" class="form-control" placeholder="Mother's full name">
                                            </div>
                                            <div class="mb-3">
                                                <label for="editMotherPhone" class="form-label">Mother's Phone</label>
                                                <input type="text" id="editMotherPhone" name="mother_phone" class="form-control" placeholder="+234 xxx xxx xxxx">
                                            </div>
                                            <div class="mb-3">
                                                <label for="editParentEmail" class="form-label">Parent's Email</label>
                                                <input type="email" id="editParentEmail" name="parent_email" class="form-control" placeholder="parent@example.com">
                                            </div>
                                            <div class="mb-3">
                                                <label for="editParentAddress" class="form-label">Parent's Address</label>
                                                <textarea id="editParentAddress" name="parent_address" class="form-control" rows="2" placeholder="Parent's address"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Section E: Previous School Details -->
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white">
                                            <h6 class="mb-0"><i class="fas fa-school me-2"></i>Previous School Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="editLastSchool" class="form-label">Last School Attended<span class="text-danger">*</span></label>
                                                <input type="text" id="editLastSchool" name="last_school" class="form-control" placeholder="Previous school name" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="editLastClass" class="form-label">Last Class Attended<span class="text-danger">*</span></label>
                                                <input type="text" id="editLastClass" name="last_class" class="form-control" placeholder="e.g., JSS 2" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="editReasonForLeaving" class="form-label">Reason for Leaving<span class="text-danger">*</span></label>
                                                <textarea id="editReasonForLeaving" name="reason_for_leaving" class="form-control" rows="2" placeholder="Reason for leaving previous school" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-danger d-none" id="edit-alert-error-msg"></div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" id="edit-btn">
                                <i class="fas fa-save me-1"></i>Update Student
                            </button>
                            <button type="button" class="btn btn-success" onclick="printStudentDetails('edit')">
                                <i class="fas fa-print me-1"></i>Print PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- View Student Modal -->
        <div id="viewStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-fullscreen-lg-down modal-xl">
                <div class="modal-content modern-modal">
                    <!-- Header with Gradient -->
                    <div class="modal-header modern-header">
                        <div class="header-content">
                            <div class="header-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="header-text">
                                <h4 class="modal-title mb-0">Student Details</h4>
                                <p class="header-subtitle mb-0">Comprehensive Student Information</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close modern-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body modern-body">
                        <div class="registration-form">
                            <!-- Student Photo Section -->
                            <div class="student-header">
                                <div class="photo-container">
                                    <div class="photo-frame">
                                        <img id="viewStudentPhoto" src="https://via.placeholder.com/150x150/6366f1/ffffff?text=PHOTO" alt="Student Photo" class="student-photo">
                                        <div class="photo-overlay">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Progressive Tabs Navigation -->
                            <div class="form-navigation">
                                <nav class="nav nav-pills nav-justified modern-tabs" id="pills-tab" role="tablist">
                                    <button class="nav-link active" id="academic-tab" data-bs-toggle="pill" data-bs-target="#academic" type="button" role="tab">
                                        <i class="fas fa-school"></i>
                                        <span>Academic</span>
                                        <div class="tab-progress"></div>
                                    </button>
                                    <button class="nav-link" id="personal-tab" data-bs-toggle="pill" data-bs-target="#personal" type="button" role="tab">
                                        <i class="fas fa-user"></i>
                                        <span>Personal</span>
                                        <div class="tab-progress"></div>
                                    </button>
                                    <button class="nav-link" id="guardian-tab" data-bs-toggle="pill" data-bs-target="#guardian" type="button" role="tab">
                                        <i class="fas fa-users"></i>
                                        <span>Guardian</span>
                                        <div class="tab-progress"></div>
                                    </button>
                                    <button class="nav-link" id="previous-tab" data-bs-toggle="pill" data-bs-target="#previous" type="button" role="tab">
                                        <i class="fas fa-history"></i>
                                        <span>Previous</span>
                                        <div class="tab-progress"></div>
                                    </button>
                                </nav>
                            </div>

                            <!-- Tab Content -->
                            <div class="tab-content modern-tabs-content" id="pills-tabContent">

                                <!-- Academic Details Tab -->
                                <div class="tab-pane fade show active" id="academic" role="tabpanel">
                                    <div class="form-section">
                                        <div class="section-header">
                                            <h5><i class="fas fa-school me-2"></i>Academic Information</h5>
                                        </div>
                                        <div class="form-grid">
                                            <div class="form-group">
                                                <label class="form-label">Academic Year</label>
                                                <div class="form-value" id="viewAcademicYear">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Registration No.</label>
                                                <div class="form-value highlight" id="viewRegistrationNo">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Admission Date</label>
                                                <div class="form-value" id="viewAdmissionDate">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Class</label>
                                                <div class="form-value class-badge" id="viewClass">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Term</label>
                                                <div class="form-value" id="viewTerm">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">State of Origin</label>
                                                <div class="form-value" id="viewState">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Local Government</label>
                                                <div class="form-value" id="viewLocal">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Category</label>
                                                <div class="category-badges">
                                                    <span class="category-badge day" id="dayBadge">
                                                        <i class="fas fa-sun"></i> Day Student
                                                    </span>
                                                    <span class="category-badge boarding" id="boardingBadge">
                                                        <i class="fas fa-home"></i> Boarding
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Personal Details Tab -->
                                <div class="tab-pane fade" id="personal" role="tabpanel">
                                    <div class="form-section">
                                        <div class="section-header">
                                            <h5><i class="fas fa-user me-2"></i>Personal Information</h5>
                                        </div>
                                        <div class="form-grid">
                                            <div class="form-group full-width">
                                                <div class="name-container">
                                                    <div class="name-part">
                                                        <label class="form-label">Surname</label>
                                                        <div class="form-value" id="viewSurname">-</div>
                                                    </div>
                                                    <div class="name-part">
                                                        <label class="form-label">First Name</label>
                                                        <div class="form-value" id="viewFirstName">-</div>
                                                    </div>
                                                    <div class="name-part">
                                                        <label class="form-label">Middle Name</label>
                                                        <div class="form-value" id="viewMiddleName">-</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Gender</label>
                                                <div class="form-value gender-badge" id="viewGender">
                                                    <i class="fas fa-user"></i> -
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Date of Birth</label>
                                                <div class="form-value" id="viewDateOfBirth">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Blood Group</label>
                                                <div class="form-value blood-group" id="viewBloodGroup">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Mother Tongue</label>
                                                <div class="form-value" id="viewMotherTongue">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Religion</label>
                                                <div class="form-value" id="viewReligion">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Sport House</label>
                                                <div class="form-value" id="viewSportHouse">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Mobile Number</label>
                                                <div class="form-value contact" id="viewMobileNumber">
                                                    <i class="fas fa-phone"></i> -
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Email</label>
                                                <div class="form-value contact" id="viewEmail">
                                                    <i class="fas fa-envelope"></i> -
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">NIN</label>
                                                <div class="form-value" id="viewNIN">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">City</label>
                                                <div class="form-value" id="viewCity">-</div>
                                            </div>
                                            <div class="form-group full-width">
                                                <label class="form-label">Permanent Address</label>
                                                <div class="form-value address-field" id="viewPermanentAddress">-</div>
                                            </div>
                                            <div class="form-group full-width">
                                                <label class="form-label">Future Ambition</label>
                                                <div class="form-value address-field" id="viewFutureAmbition">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Guardian Details Tab -->
                                <div class="tab-pane fade" id="guardian" role="tabpanel">
                                    <div class="form-section">
                                        <div class="section-header">
                                            <h5><i class="fas fa-users me-2"></i>Guardian Information</h5>
                                        </div>
                                        <div class="form-grid">
                                            <div class="form-group">
                                                <label class="form-label">Father's Name</label>
                                                <div class="form-value" id="viewFatherName">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Mother's Name</label>
                                                <div class="form-value" id="viewMotherName">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Occupation</label>
                                                <div class="form-value occupation-badge" id="viewOccupation">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">City</label>
                                                <div class="form-value" id="viewParentCity">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Mobile Number</label>
                                                <div class="form-value contact" id="viewParentMobile">
                                                    <i class="fas fa-phone"></i> -
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Email</label>
                                                <div class="form-value contact" id="viewParentEmail">
                                                    <i class="fas fa-envelope"></i> -
                                                </div>
                                            </div>
                                            <div class="form-group full-width">
                                                <label class="form-label">Address</label>
                                                <div class="form-value address-field" id="viewParentAddress">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Previous School Tab -->
                                <div class="tab-pane fade" id="previous" role="tabpanel">
                                    <div class="form-section">
                                        <div class="section-header">
                                            <h5><i class="fas fa-history me-2"></i>Previous School Information</h5>
                                        </div>
                                        <div class="form-grid">
                                            <div class="form-group full-width">
                                                <label class="form-label">School Name</label>
                                                <div class="form-value school-name" id="viewSchoolName">
                                                    <i class="fas fa-school"></i> -
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Previous Class</label>
                                                <div class="form-value class-badge" id="viewPreviousClass">-</div>
                                            </div>
                                            <div class="form-group full-width">
                                                <label class="form-label">Reason for Leaving</label>
                                                <div class="form-value reason-field" id="viewReasonLeaving">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modern Footer -->
                    <div class="modal-footer modern-footer">
                        <div class="footer-actions">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>




// ============================================================================
// REPORT GENERATION
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    const generateReportBtn = document.getElementById('generateReportBtn');
    const printReportForm = document.getElementById('printReportForm');

    if (generateReportBtn && printReportForm) {
        generateReportBtn.addEventListener('click', function() {
            generateReport();
        });
    }
});

function generateReport() {
    const form = document.getElementById('printReportForm');
    if (!form) {
        console.error('Report form not found');
        return;
    }

    // Get selected columns
    const selectedColumns = Array.from(form.querySelectorAll('input[name="columns[]"]:checked'))
        .map(cb => cb.value);

    if (selectedColumns.length === 0) {
        Swal.fire({
            title: 'Warning!',
            text: 'Please select at least one column to include in the report.',
            icon: 'warning',
            customClass: { confirmButton: 'btn btn-primary' },
            buttonsStyling: false
        });
        return;
    }

    // Get form values
    const classId = form.querySelector('[name="class_id"]').value;
    const status = form.querySelector('[name="status"]').value;
    const formatElement = form.querySelector('[name="format"]:checked');

    if (!formatElement) {
        Swal.fire({
            title: 'Error!',
            text: 'Please select an export format (PDF or Excel).',
            icon: 'error',
            customClass: { confirmButton: 'btn btn-primary' },
            buttonsStyling: false
        });
        return;
    }

    const format = formatElement.value;
    const orientation = form.querySelector('[name="orientation"]')?.value || 'portrait';

    // Show loading indicator
    Swal.fire({
        title: 'Generating Report...',
        text: 'This may take a moment. Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Build query parameters
    const params = new URLSearchParams({
        class_id: classId,
        status: status,
        columns: selectedColumns.join(','),
        format: format,
        orientation: orientation
    });

    // Make the request
    axios.get(`/students/report?${params.toString()}`, {
        responseType: 'blob',
        timeout: 120000 // 2 minutes timeout
    })
    .then(response => {
        Swal.close();

        // Create a blob from the response
        const blob = new Blob([response.data], {
            type: response.headers['content-type']
        });

        // Create download link
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;

        // Get filename from content-disposition header or generate one
        const contentDisposition = response.headers['content-disposition'];
        let filename = 'student-report.' + (format === 'pdf' ? 'pdf' : 'xlsx');

        if (contentDisposition) {
            const filenameMatch = contentDisposition.match(/filename="(.+)"/);
            if (filenameMatch && filenameMatch[1]) {
                filename = filenameMatch[1];
            }
        }

        a.download = filename;
        document.body.appendChild(a);
        a.click();

        // Cleanup
        setTimeout(() => {
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        }, 100);

        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('printStudentReportModal'));
        if (modal) {
            modal.hide();
        }

        // Show success message
        Swal.fire({
            title: 'Success!',
            text: `Report generated successfully and downloaded as ${format.toUpperCase()}`,
            icon: 'success',
            customClass: { confirmButton: 'btn btn-primary' },
            buttonsStyling: false,
            timer: 3000,
            timerProgressBar: true
        });
    })
    .catch(error => {
        Swal.close();

        console.error('Error generating report:', error);

        let errorMessage = 'Failed to generate report. Please try again.';

        if (error.response) {
            // Server responded with error status
            if (error.response.status === 404) {
                errorMessage = 'No students found matching the selected filters.';
            } else if (error.response.status === 422) {
                errorMessage = error.response.data.message || 'Validation error. Please check your selections.';
            } else if (error.response.status === 500) {
                if (error.response.data && error.response.data.message) {
                    errorMessage = error.response.data.message;
                    if (error.response.data.error && error.response.data.error.includes('armRelation')) {
                        errorMessage = 'Report generation error. Please contact administrator.';
                    }
                } else {
                    errorMessage = 'Server error. Please try again later.';
                }
            }

            // Try to parse error message from response
            if (error.response.data && typeof error.response.data === 'object') {
                if (error.response.data.message) {
                    errorMessage = error.response.data.message;
                }
            }
        } else if (error.code === 'ECONNABORTED') {
            errorMessage = 'Request timeout. The report generation is taking too long. Try with fewer students or different filters.';
        }

        Swal.fire({
            title: 'Error!',
            text: errorMessage,
            icon: 'error',
            customClass: { confirmButton: 'btn btn-primary' },
            buttonsStyling: false
        });
    });
}

// ============================================================================
// DRAG AND DROP COLUMN ORDERING
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    initializeColumnOrdering();
});

function initializeColumnOrdering() {
    const columnContainer = document.getElementById('columnsContainer');
    const hiddenOrderInput = document.getElementById('columnsOrderInput');

    if (!columnContainer || !hiddenOrderInput) return;

    // Initialize SortableJS
    new Sortable(columnContainer, {
        animation: 150,
        ghostClass: 'bg-light',
        chosenClass: 'bg-primary text-white',
        dragClass: 'bg-primary text-white',
        filter: '.form-check-input', // Ignore the checkbox
        onEnd: function() {
            updateColumnOrder();
        }
    });

    function updateColumnOrder() {
        const checkboxes = columnContainer.querySelectorAll('.form-check');
        const order = [];

        checkboxes.forEach(checkbox => {
            const input = checkbox.querySelector('input[type="checkbox"]');
            if (input && input.checked) {
                order.push(input.value);
            }
        });

        hiddenOrderInput.value = order.join(',');
        console.log('Column order updated:', order);
    }

    // Update order when checkboxes change
    columnContainer.querySelectorAll('.form-check-input').forEach(checkbox => {
        checkbox.addEventListener('change', updateColumnOrder);
    });

    // Initial update
    updateColumnOrder();
}
</script>
@endsection
