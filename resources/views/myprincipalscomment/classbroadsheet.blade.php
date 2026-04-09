@extends('layouts.master')

@section('content')
<style>
    .highlight-red { color: red !important; }
    .highlight-orange { color: #fd7e14 !important; }
    .avatar-sm { width: 32px; height: 32px; object-fit: cover; border-radius: 50%; }
    .table-centered th, .table-centered td { text-align: center; vertical-align: middle; }

    /* Subject Score Card Styles */
    .subject-score-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 6px 4px;
        text-align: center;
        transition: all 0.2s ease;
        min-width: 90px;
    }
    .subject-score-card:hover {
        background: #e9ecef;
        transform: translateY(-2px);
    }
    .term-score {
        font-size: 1.1rem;
        font-weight: 700;
        color: #495057;
    }
    .cumulative-score {
        font-size: 0.8rem;
        color: #6c757d;
        border-top: 1px dashed #dee2e6;
        margin-top: 4px;
        padding-top: 4px;
    }
    .term-label {
        font-size: 0.65rem;
        text-transform: uppercase;
        font-weight: 600;
        color: #adb5bd;
    }
    .cumulative-label {
        font-size: 0.6rem;
        text-transform: uppercase;
        font-weight: 600;
        color: #17a2b8;
    }

    /* Grade Badge Styles */
    .grade-badge-sm {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 700;
        margin-top: 4px;
    }
    .grade-a, .grade-a1 { background-color: #28a745; color: white; }
    .grade-b, .grade-b2, .grade-b3 { background-color: #17a2b8; color: white; }
    .grade-c, .grade-c4, .grade-c5, .grade-c6 { background-color: #6c757d; color: white; }
    .grade-d, .grade-d7 { background-color: #ffc107; color: black; }
    .grade-e, .grade-e8 { background-color: #fd7e14; color: white; }
    .grade-f, .grade-f9 { background-color: #dc3545; color: white; }

    /* Table Header Styles */
    .subject-header {
        font-size: 0.8rem;
        font-weight: 600;
        background: #e9ecef;
    }
    .score-header {
        font-size: 0.7rem;
        font-weight: 600;
        background: #f8f9fa;
    }

    .form-select.teacher-comment-dropdown {
        width: 100%;
        min-width: 150px;
        padding-right: 40px;
        cursor: pointer;
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        transition: all 0.2s ease;
    }
    .form-select.teacher-comment-dropdown:focus {
        background-color: #fff;
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    .comment-cell { position: relative; }
    .comment-info-icon {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.2rem;
        color: #0d6efd;
        z-index: 2;
        transition: color 0.2s ease;
    }
    .comment-info-icon:hover { color: #0056b3; }

    .auto-save-toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 99999;
        min-width: 250px;
    }

    .intelligent-comment-section {
        border-left: 4px solid #28a745;
        background-color: #f8fff8 !important;
        margin-bottom: 15px;
        border-radius: 8px;
        padding: 10px;
    }
    .intelligent-comment-preview {
        font-size: 0.9rem;
        line-height: 1.4;
        white-space: pre-wrap;
        background-color: white;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 10px;
        margin-top: 8px;
    }
    .intelligent-comment-text { color: #155724; font-weight: 500; }
    .intelligent-comment-badge { font-size: 0.75rem; padding: 2px 8px; margin-left: 8px; }
    .intelligent-option {
        background-color: #e8f5e8 !important;
        border-top: 2px solid #28a745 !important;
        font-weight: 600 !important;
        color: #155724 !important;
        margin-top: 5px;
    }

    .saved-comment-preview {
        background-color: #f8fff8 !important;
        border-left: 4px solid #28a745;
        border-radius: 6px;
        padding: 10px;
        font-size: 0.875rem;
        line-height: 1.5;
        white-space: pre-wrap;
        max-height: 120px;
        overflow-y: auto;
    }

    .grades-tooltip {
        position: fixed;
        background: white;
        border: 2px solid #667eea;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        width: 450px;
        max-height: 550px;
        overflow: hidden;
        z-index: 10050;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        pointer-events: none;
    }
    .grades-tooltip.show {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        animation: tooltipFadeIn 0.3s ease-out;
    }
    @keyframes tooltipFadeIn {
        from { opacity: 0; transform: translate(-50%, -48%) scale(0.95); }
        to { opacity: 1; transform: translate(-50%, -50%) scale(1); }
    }
    .grades-tooltip.position-bottom { bottom: 15%; left: 50%; transform: translateX(-50%); }
    .grades-tooltip .tooltip-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px 60px 20px 24px;
        font-weight: 700;
        font-size: 1.2rem;
        border-radius: 18px 18px 0 0;
        margin: -2px -2px 20px -2px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        position: relative;
        display: flex;
        align-items: center;
    }
    .grades-tooltip .tooltip-close {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        color: white;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .grades-tooltip .tooltip-body { padding: 0 20px 20px 20px; max-height: 420px; overflow-y: auto; }
    .grades-tooltip table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
    .grades-tooltip th { color: #6c757d; font-weight: 600; font-size: 0.85rem; padding: 12px 8px; border-bottom: 2px solid #e9ecef; }
    .grades-tooltip td { padding: 12px 8px; background: #f8f9fa; border-radius: 10px; font-size: 0.9rem; }
    .grade-badge { font-weight: 800; padding: 6px 14px; border-radius: 20px; font-size: 0.85rem; min-width: 50px; text-align: center; display: inline-block; }

    .student-card { background: #fff; border: 1px solid #dee2e6; border-radius: 12px; margin-bottom: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; }
    .student-header { background: #f8f9fa; padding: 15px; border-bottom: 1px solid #dee2e6; }
    .student-info { display: flex; align-items: center; gap: 12px; }
    .student-details h6 { margin: 0; font-size: 1rem; font-weight: 600; }
    .student-meta { font-size: 0.875rem; color: #6c757d; }
    .student-body { padding: 15px; }
    .subjects-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 10px; margin-bottom: 20px; }
    .performance-summary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 15px; margin-bottom: 15px; color: white; }
    .summary-title { font-weight: 600; font-size: 0.9rem; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
    .summary-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; text-align: center; }
    .summary-item { padding: 10px; background: rgba(255,255,255,0.15); border-radius: 10px; backdrop-filter: blur(5px); }
    .summary-label { font-size: 0.7rem; opacity: 0.9; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
    .summary-value { font-size: 1.3rem; font-weight: 700; }
    .comment-label-mobile { font-weight: 600; margin-bottom: 8px; font-size: 0.95rem; color: #495057; display: flex; align-items: center; gap: 8px; }
    .btn-save-all { padding: 10px 24px; font-weight: 600; }
    .saving-indicator { display: none; }

    .cumulative-badge {
        background-color: #17a2b8;
        color: white;
        font-size: 0.7rem;
        padding: 2px 8px;
        border-radius: 20px;
        margin-left: 8px;
    }
    .term-badge {
        background-color: #6c757d;
        color: white;
        font-size: 0.7rem;
        padding: 2px 8px;
        border-radius: 20px;
        margin-left: 5px;
    }

    .split-score {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 3px;
    }
    .term-score-box {
        background: white;
        padding: 4px 8px;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .cum-score-box {
        background: #e8f4f8;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
    }

    @media (min-width: 1200px) {
        .desktop-table { display: block !important; }
        .mobile-cards { display: none !important; }
        .comment-info-icon { display: block !important; }
        .subject-score-card { min-width: 100px; }
    }
    @media (max-width: 1199.98px) {
        .desktop-table { display: none !important; }
        .mobile-cards { display: block !important; }
        .comment-info-icon { display: none !important; }
    }
</style>

<div class="main-content class-broadsheet">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ $pagetitle }}</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('myprincipalscomment.index') }}">My Assignments</a></li>
                                <li class="breadcrumb-item active">Broadsheet</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($students->isNotEmpty())
                <form id="commentsForm" action="{{ route('myprincipalscomment.updateComments', [$schoolclassid, $sessionid, $termid]) }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                                        <h5 class="card-title mb-0">
                                            <i class="ri-bar-chart-2-line me-2"></i>
                                            Broadsheet: {{ $schoolclass->schoolclass }} {{ $schoolclass->arm_name }} - {{ $schoolterm }} {{ $schoolsession }}
                                            @if($isSenior)
                                                <span class="badge bg-warning ms-2">Senior Class</span>
                                            @else
                                                <span class="badge bg-info ms-2">Junior Class</span>
                                            @endif
                                        </h5>
                                        <div class="mt-2 mt-sm-0">
                                            <span class="badge bg-info cumulative-badge">
                                                <i class="ri-bar-chart-line"></i> Cumulative ({{ $schoolterm }})
                                            </span>
                                            <span class="badge bg-secondary term-badge">
                                                <i class="ri-calendar-line"></i> Term Total
                                            </span>
                                        </div>
                                    </div>
                                    <div class="alert alert-info mt-3 mb-0">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <i class="ri-bar-chart-line me-2"></i>
                                                <strong>Class Average (Cumulative):</strong> {{ $classAnalytics['average'] }} |
                                                <strong>Students:</strong> {{ $classAnalytics['total_students'] }}
                                            </div>
                                            <div class="col-md-6 text-md-end">
                                                @if($schoolterm == '2nd Term' || $schoolterm == 'Second Term')
                                                    <span class="text-success"><i class="ri-information-line"></i> Cumulative = Average of 1st & 2nd Terms</span>
                                                @elseif($schoolterm == '3rd Term' || $schoolterm == 'Third Term')
                                                    <span class="text-success"><i class="ri-information-line"></i> Cumulative = Average of 1st, 2nd & 3rd Terms</span>
                                                @else
                                                    <span class="text-success"><i class="ri-information-line"></i> Cumulative = Current Term Score</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="search-box mb-4">
                                        <input type="text" class="form-control" placeholder="Search students by name or admission number..." id="searchInput">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>

                                    <!-- Desktop Table -->
                                    <div class="desktop-table">
                                        <div class="table-responsive">
                                            <table class="table table-bordered align-middle">
                                                <thead>
                                                    <tr class="subject-header">
                                                        <th rowspan="2" style="vertical-align: middle; width: 40px;">SN</th>
                                                        <th rowspan="2" style="vertical-align: middle; width: 100px;">Admission No</th>
                                                        <th rowspan="2" style="vertical-align: middle; width: 200px;">Student</th>
                                                        <th rowspan="2" style="vertical-align: middle; width: 80px;">Gender</th>
                                                        <th colspan="{{ count($subjects) * 2 }}" class="text-center">Subjects Performance</th>
                                                        <th rowspan="2" style="vertical-align: middle; min-width: 280px;">Principal's Comment</th>
                                                    </tr>
                                                    <tr class="score-header">
                                                        @foreach ($subjects as $subject)
                                                            <th class="text-center" style="min-width: 110px;">
                                                                {{ $subject }}
                                                                <div class="small text-muted mt-1">
                                                                    <span class="text-secondary">Term</span> | <span class="text-info">Cum</span>
                                                                </div>
                                                            </th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($students as $index => $student)
                                                        @php
                                                            $picture = $student->picture ? basename($student->picture) : 'unnamed.jpg';
                                                            $imagePath = asset('storage/student_avatars/' . $picture);
                                                            $currentComment = $profiles[$student->id] ?? '';
                                                            $currentCommentPlain = strip_tags($currentComment);
                                                            $intelligentComment = $intelligentComments[$student->id] ?? '';
                                                            $hasWeakAdvice = !empty($studentGradeAnalysis[$student->id]['weak_subjects'] ?? []);
                                                            $analytics = $studentAnalytics[$student->id] ?? [];
                                                        @endphp
                                                        <tr data-student-id="{{ $student->id }}">
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>{{ $student->admissionNo }}</td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <img src="{{ $imagePath }}" class="avatar-sm me-2" alt="">
                                                                    <div>
                                                                        {{ $student->lastname }} {{ $student->firstname }}
                                                                        @if($currentComment)
                                                                            <small class="d-block text-success mt-1"><i class="ri-check-double-line"></i> Comment saved</small>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>{{ $student->gender ?? 'N/A' }}</td>

                                                            @foreach ($subjects as $subject)
                                                                @php
                                                                    $termScore = $termScores->where('student_id', $student->id)->where('subject_name', $subject)->first();
                                                                    $cumulativeScore = $scores->where('student_id', $student->id)->where('subject_name', $subject)->first();

                                                                    $termTotal = $termScore?->total ?? 0;
                                                                    $cumTotal = $cumulativeScore?->total ?? 0;

                                                                    // Get grade for cumulative score
                                                                    $cumGrade = '';
                                                                    if ($cumTotal > 0) {
                                                                        if ($isSenior) {
                                                                            if ($cumTotal >= 75) $cumGrade = 'A1';
                                                                            elseif ($cumTotal >= 70) $cumGrade = 'B2';
                                                                            elseif ($cumTotal >= 65) $cumGrade = 'B3';
                                                                            elseif ($cumTotal >= 60) $cumGrade = 'C4';
                                                                            elseif ($cumTotal >= 55) $cumGrade = 'C5';
                                                                            elseif ($cumTotal >= 50) $cumGrade = 'C6';
                                                                            elseif ($cumTotal >= 45) $cumGrade = 'D7';
                                                                            elseif ($cumTotal >= 40) $cumGrade = 'E8';
                                                                            else $cumGrade = 'F9';
                                                                        } else {
                                                                            if ($cumTotal >= 70) $cumGrade = 'A';
                                                                            elseif ($cumTotal >= 60) $cumGrade = 'B';
                                                                            elseif ($cumTotal >= 50) $cumGrade = 'C';
                                                                            elseif ($cumTotal >= 40) $cumGrade = 'D';
                                                                            else $cumGrade = 'F';
                                                                        }
                                                                    }

                                                                    $termGradeClass = $termTotal < 50 ? 'text-danger' : ($termTotal < 60 ? 'text-warning' : 'text-success');
                                                                    $cumGradeClass = $cumTotal < 50 ? 'text-danger' : ($cumTotal < 60 ? 'text-warning' : 'text-success');
                                                                @endphp
                                                                <td class="p-2">
                                                                    <div class="subject-score-card">
                                                                        <div class="split-score">
                                                                            <div class="term-score-box w-100">
                                                                                <span class="term-label">TERM</span>
                                                                                <div class="term-score {{ $termGradeClass }} fw-bold">
                                                                                    {{ $termTotal ?: '-' }}
                                                                                </div>
                                                                            </div>
                                                                            <div class="cum-score-box w-100">
                                                                                <span class="cumulative-label">CUM</span>
                                                                                <div class="cumulative-score {{ $cumGradeClass }} fw-bold">
                                                                                    {{ $cumTotal ?: '-' }}
                                                                                    @if($cumGrade)
                                                                                        <span class="grade-badge-sm grade-{{ strtolower($cumGrade) }} ms-1">
                                                                                            {{ $cumGrade }}
                                                                                        </span>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            @endforeach

                                                            <td class="comment-cell position-relative">
                                                                @if($intelligentComment)
                                                                <div class="intelligent-comment-section mb-2">
                                                                    <small class="text-muted d-block mb-1">
                                                                        <i class="ri-lightbulb-line"></i> AI Generated (Cumulative)
                                                                        @if($hasWeakAdvice)<span class="badge bg-warning intelligent-comment-badge">+ Advice</span>@endif
                                                                    </small>
                                                                    <div class="intelligent-comment-preview p-2">
                                                                        <div class="intelligent-comment-text small">{!! nl2br(e(Str::limit($intelligentComment, 150))) !!}</div>
                                                                    </div>
                                                                </div>
                                                                @endif

                                                                @if($currentComment)
                                                                <div class="mb-2">
                                                                    <small class="text-success d-block mb-1"><i class="ri-chat-check-line"></i> Saved Comment</small>
                                                                    <div class="saved-comment-preview p-2">
                                                                        <small class="text-secondary">{!! nl2br(e(Str::limit($currentCommentPlain, 100))) !!}</small>
                                                                    </div>
                                                                </div>
                                                                @endif

                                                                <select class="form-select form-select-sm teacher-comment-dropdown auto-save-comment"
                                                                        name="teacher_comments[{{ $student->id }}]"
                                                                        data-student-id="{{ $student->id }}"
                                                                        data-original-value="{{ $currentCommentPlain }}">
                                                                    <option value="">-- Select Comment --</option>

                                                                    @foreach ($standardPersonalizedComments[$student->id] ?? [] as $comment)
                                                                        @php
                                                                            $commentPlain = strip_tags($comment);
                                                                            $isSelected = ($currentCommentPlain == $commentPlain);
                                                                        @endphp
                                                                        <option value="{{ $commentPlain }}" {{ $isSelected ? 'selected' : '' }}>
                                                                            {{ Str::limit($commentPlain, 80) }}
                                                                            @if(str_contains($commentPlain, 'should work harder'))
                                                                                <span class="badge bg-warning ms-2">Advice</span>
                                                                            @endif
                                                                        </option>
                                                                    @endforeach

                                                                    @if(isset($intelligentComments[$student->id]) && !in_array(strip_tags($intelligentComments[$student->id]), array_map('strip_tags', $standardPersonalizedComments[$student->id] ?? [])))
                                                                        @php
                                                                            $intelligentPlain = strip_tags($intelligentComments[$student->id]);
                                                                            $isSelected = ($currentCommentPlain == $intelligentPlain);
                                                                        @endphp
                                                                        <option value="{{ $intelligentPlain }}" class="intelligent-option" {{ $isSelected ? 'selected' : '' }}>
                                                                            💡 Use AI Comment (Cumulative)
                                                                        </option>
                                                                    @endif
                                                                </select>

                                                                <button type="button" class="comment-info-icon grades-trigger btn btn-link p-0"
                                                                        data-student-id="{{ $student->id }}"
                                                                        data-student-name="{{ $student->lastname }} {{ $student->firstname }}">
                                                                    <i class="ri-eye-line"></i>
                                                                </button>

                                                                <!-- Tooltip for detailed grades -->
                                                                <div class="grades-tooltip position-bottom" id="tooltip-{{ $student->id }}">
                                                                    <div class="tooltip-header">
                                                                        <span id="tooltip-title-{{ $student->id }}">Performance Details</span>
                                                                        <button type="button" class="tooltip-close"><i class="ri-close-line"></i></button>
                                                                    </div>
                                                                    <div class="tooltip-body">
                                                                        <div class="row mb-3">
                                                                            <div class="col-6">
                                                                                <div class="bg-light rounded p-2 text-center">
                                                                                    <small class="text-muted">Term Total</small>
                                                                                    <h5 class="mb-0">{{ $analytics['term_total'] ?? 0 }}</h5>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-6">
                                                                                <div class="bg-info bg-opacity-10 rounded p-2 text-center">
                                                                                    <small class="text-info">Cumulative Total</small>
                                                                                    <h5 class="mb-0 text-info">{{ $analytics['total_score'] ?? 0 }}</h5>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="text-center mb-3 p-2 bg-light rounded">
                                                                            <div class="row">
                                                                                <div class="col-4"><strong>Avg:</strong> {{ $analytics['average'] ?? 0 }}</div>
                                                                                <div class="col-4"><strong>Position:</strong> {{ $analytics['position_text'] ?? '-' }}</div>
                                                                                <div class="col-4"><strong>Class Avg:</strong> {{ $classAnalytics['average'] }}</div>
                                                                            </div>
                                                                        </div>
                                                                        <table class="table table-sm">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Subject</th>
                                                                                    <th>Term</th>
                                                                                    <th>Cumulative</th>
                                                                                    <th>Grade</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody id="grades-body-{{ $student->id }}"></tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Mobile Cards View -->
                                    <div class="mobile-cards">
                                        @foreach ($students as $index => $student)
                                            @php
                                                $picture = $student->picture ? basename($student->picture) : 'unnamed.jpg';
                                                $imagePath = asset('storage/student_avatars/' . $picture);
                                                $currentComment = $profiles[$student->id] ?? '';
                                                $currentCommentPlain = strip_tags($currentComment);
                                                $intelligentComment = $intelligentComments[$student->id] ?? '';
                                                $hasWeakAdvice = !empty($studentGradeAnalysis[$student->id]['weak_subjects'] ?? []);
                                                $analytics = $studentAnalytics[$student->id] ?? [];
                                                $myAvg = $analytics['average'] ?? 0;
                                                $diff = $myAvg - $classAnalytics['average'];
                                            @endphp
                                            <div class="student-card" data-student-id="{{ $student->id }}">
                                                <div class="student-header">
                                                    <div class="student-info">
                                                        <img src="{{ $imagePath }}" class="avatar-sm" alt="">
                                                        <div class="student-details">
                                                            <h6>
                                                                {{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}
                                                                @if($currentComment)<span class="badge bg-success ms-2">✓</span>@endif
                                                            </h6>
                                                            <div class="student-meta">
                                                                <i class="ri-id-card-line"></i> {{ $student->admissionNo }} |
                                                                <i class="ri-user-line"></i> {{ $student->gender ?? 'N/A' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="student-body">
                                                    <div class="performance-summary">
                                                        <div class="summary-title">
                                                            <i class="ri-bar-chart-line"></i> Performance Summary
                                                        </div>
                                                        <div class="summary-grid">
                                                            <div class="summary-item">
                                                                <div class="summary-label">Term Average</div>
                                                                <div class="summary-value">{{ $analytics['term_average'] ?? 0 }}</div>
                                                            </div>
                                                            <div class="summary-item">
                                                                <div class="summary-label">Cumulative Avg</div>
                                                                <div class="summary-value">{{ $myAvg }}</div>
                                                            </div>
                                                            <div class="summary-item">
                                                                <div class="summary-label">Position</div>
                                                                <div class="summary-value">{{ $analytics['position_text'] ?? '-' }}</div>
                                                            </div>
                                                            <div class="summary-item">
                                                                <div class="summary-label">Total Score</div>
                                                                <div class="summary-value">{{ $analytics['total_score'] ?? 0 }}</div>
                                                            </div>
                                                        </div>
                                                        <div class="text-center mt-2 small">
                                                            @if($diff > 0.5)
                                                                <span class="text-success"><i class="ri-arrow-up-line"></i> +{{ round(abs($diff), 1) }} above average</span>
                                                            @elseif($diff < -0.5)
                                                                <span class="text-warning"><i class="ri-arrow-down-line"></i> {{ round(abs($diff), 1) }} below average</span>
                                                            @else
                                                                <span class="text-white-50">At class average</span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="subjects-grid">
                                                        @foreach ($subjects as $subject)
                                                            @php
                                                                $termScore = $termScores->where('student_id', $student->id)->where('subject_name', $subject)->first();
                                                                $cumulativeScore = $scores->where('student_id', $student->id)->where('subject_name', $subject)->first();
                                                                $termTotal = $termScore?->total ?? 0;
                                                                $cumTotal = $cumulativeScore?->total ?? 0;
                                                            @endphp
                                                            <div class="subject-item">
                                                                <div class="subject-name">{{ $subject }}</div>
                                                                <div class="small text-muted">Term: <strong class="{{ $termTotal < 50 ? 'text-danger' : 'text-success' }}">{{ $termTotal ?: '-' }}</strong></div>
                                                                <div class="small text-info">Cum: <strong>{{ $cumTotal ?: '-' }}</strong></div>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    @if($intelligentComment)
                                                    <div class="intelligent-comment-section mb-2">
                                                        <small><i class="ri-lightbulb-line"></i> AI Suggestion</small>
                                                        <div class="small mt-1">{{ Str::limit($intelligentComment, 120) }}</div>
                                                    </div>
                                                    @endif

                                                    <div class="comment-section-mobile">
                                                        <label class="comment-label-mobile"><i class="ri-chat-3-line"></i> Principal's Comment</label>
                                                        <select class="form-select form-select-sm auto-save-comment"
                                                                name="teacher_comments[{{ $student->id }}]"
                                                                data-student-id="{{ $student->id }}"
                                                                data-original-value="{{ $currentCommentPlain }}">
                                                            <option value="">-- Select Comment --</option>
                                                            @foreach ($standardPersonalizedComments[$student->id] ?? [] as $comment)
                                                                @php $commentPlain = strip_tags($comment); @endphp
                                                                <option value="{{ $commentPlain }}" {{ $currentCommentPlain == $commentPlain ? 'selected' : '' }}>
                                                                    {{ Str::limit($commentPlain, 60) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="row mt-4">
                                        <div class="col-12 text-end">
                                            <button type="submit" class="btn btn-primary btn-save-all">
                                                <i class="ri-save-line me-1"></i> Save All Comments
                                            </button>
                                            <span class="saving-indicator ms-2 text-muted" id="savingIndicator" style="display: none;">
                                                <i class="ri-loader-4-line spin-icon me-1"></i> Saving...
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            @else
                <div class="alert alert-info text-center">No students enrolled in this class for the selected session and term.</div>
            @endif
        </div>
    </div>
</div>

<style>
    .spin-icon { animation: spin 1s linear infinite; }
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

<script>
// First, we need to pass both term and cumulative scores to JavaScript
window.studentTermScores = @json($termScores ?? []);
window.studentCumScores = @json($scores);
window.studentGradesData = @json($studentGrades);

let activeTooltip = null;

function showToast(message, type = 'info') {
    const existing = document.querySelector('.auto-save-toast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.className = `auto-save-toast alert alert-${type} alert-dismissible fade show`;
    toast.innerHTML = `<div class="d-flex align-items-center"><i class="ri-${type === 'success' ? 'checkbox-circle' : 'information'}-fill me-2"></i><span>${escapeHtml(message)}</span><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function closeAllTooltips() {
    document.querySelectorAll('.grades-tooltip.show').forEach(t => t.classList.remove('show'));
    activeTooltip = null;
}

function showTooltip(tooltipId, studentId, studentName) {
    const tooltip = document.getElementById(tooltipId);
    if (!tooltip) return;
    closeAllTooltips();
    document.getElementById(`tooltip-title-${studentId}`).textContent = `${studentName}'s Performance`;

    const grades = window.studentGradesData[studentId] || [];
    const tbody = document.getElementById(`grades-body-${studentId}`);
    tbody.innerHTML = '';

    grades.forEach(g => {
        let gradeClass = 'grade-f';
        if (g.grade_letter === 'A') gradeClass = 'grade-a';
        else if (g.grade_letter === 'B') gradeClass = 'grade-b';
        else if (g.grade_letter === 'C') gradeClass = 'grade-c';
        else if (g.grade_letter === 'D') gradeClass = 'grade-d';
        else if (g.grade_letter === 'E') gradeClass = 'grade-e';

        const row = document.createElement('tr');
        row.innerHTML = `
            <td><strong>${escapeHtml(g.subject)}</strong></td>
            <td class="text-center">${g.term_score || '-'}</td>
            <td class="text-center fw-bold ${g.score < 50 ? 'text-danger' : 'text-success'}">${g.score}</td>
            <td class="text-center"><span class="grade-badge ${gradeClass}">${escapeHtml(g.grade)}</span></td>
        `;
        tbody.appendChild(row);
    });

    tooltip.classList.add('show');
    activeTooltip = tooltipId;
}

// Auto-save functionality
document.querySelectorAll('.auto-save-comment').forEach(select => {
    select.addEventListener('change', function() {
        const studentId = this.dataset.studentId;
        const comment = this.value.trim();
        const original = this.dataset.originalValue || '';
        if (comment === original) return;

        this.disabled = true;
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append(`teacher_comments[${studentId}]`, comment);

        fetch('{{ route("myprincipalscomment.updateComments", [$schoolclassid, $sessionid, $termid]) }}', {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.dataset.originalValue = comment;
                showToast('Comment saved!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else throw new Error(data.message);
        })
        .catch(error => showToast('Error: ' + error.message, 'danger'))
        .finally(() => this.disabled = false);
    });
});

// Form submission
document.getElementById('commentsForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const submitBtn = document.querySelector('.btn-save-all');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ri-loader-4-line spin-icon me-1"></i> Saving...';

    fetch(this.action, {
        method: 'POST',
        body: new FormData(this),
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 2000);
        } else throw new Error(data.message);
    })
    .catch(error => showToast('Error: ' + error.message, 'danger'))
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Tooltip handlers
if (window.innerWidth > 1199) {
    document.querySelectorAll('.grades-trigger').forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const tid = `tooltip-${this.dataset.studentId}`;
            showTooltip(tid, this.dataset.studentId, this.dataset.studentName);
        });
    });
    document.querySelectorAll('.tooltip-close').forEach(btn => btn.addEventListener('click', closeAllTooltips));
    document.addEventListener('click', e => {
        if (activeTooltip && !document.getElementById(activeTooltip)?.contains(e.target)) {
            closeAllTooltips();
        }
    });
}

// Search functionality
document.getElementById('searchInput')?.addEventListener('input', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('.desktop-table tbody tr, .mobile-cards .student-card').forEach(el => {
        el.style.display = term === '' || el.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
});
</script>
@endsection
