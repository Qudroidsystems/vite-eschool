@extends('layouts.master')

@section('content')
<style>
    .highlight-red { color: red !important; }
    .avatar-sm { width: 32px; height: 32px; object-fit: cover; }
    .table-centered th, .table-centered td { text-align: center; vertical-align: middle; }
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
    .comment-cell {
        position: relative;
    }
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
    .comment-info-icon:hover,
    .comment-info-icon:focus {
        color: #0056b3;
    }

    /* Toast Notification */
    .auto-save-toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 250px;
    }

    /* Floating Tooltip */
    .grades-tooltip {
        position: fixed;
        left: 50%;
        transform: translateX(-50%);
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 20px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        width: 360px;
        max-height: 480px;
        overflow: hidden;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        pointer-events: none;
    }

    .grades-tooltip.show {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    .grades-tooltip.position-top {
        top: 15%;
    }
    .grades-tooltip.position-bottom {
        bottom: 15%;
    }

    .grades-tooltip .tooltip-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 18px 24px;
        font-weight: 600;
        font-size: 1.1rem;
        text-align: center;
        border-radius: 20px 20px 0 0;
        margin: -20px -20px 20px -20px;
        box-shadow: 0 6px 15px rgba(0,0,0,0.1);
    }

    .grades-tooltip .tooltip-body {
        padding: 0 24px 24px 24px;
        max-height: 360px;
        overflow-y: auto;
    }

    .grades-tooltip .tooltip-body::-webkit-scrollbar {
        width: 8px;
    }
    .grades-tooltip .tooltip-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    .grades-tooltip .tooltip-body::-webkit-scrollbar-thumb {
        background: #c0c0c0;
        border-radius: 10px;
    }
    .grades-tooltip .tooltip-body::-webkit-scrollbar-thumb:hover {
        background: #a0a0a0;
    }

    .grades-tooltip table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 10px;
    }
    .grades-tooltip th {
        color: #6c757d;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 10px 8px;
    }
    .grades-tooltip td {
        padding: 14px 8px;
        background: #f8f9fa;
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    .grades-tooltip tr:hover td {
        background: #e3f2fd;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    .grades-tooltip .grade-badge {
        font-weight: 700;
        padding: 8px 16px;
        border-radius: 30px;
        font-size: 0.9rem;
    }

    /* Mobile Card Styles */
    .student-card {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .student-header {
        background: #f8f9fa;
        padding: 15px;
        border-bottom: 1px solid #dee2e6;
    }
    .student-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .student-details h6 {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
    }
    .student-meta {
        font-size: 0.875rem;
        color: #6c757d;
    }
    .student-body {
        padding: 15px;
    }
    .subjects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }
    .subject-item {
        text-align: center;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    .subject-name {
        font-size: 0.8rem;
        font-weight: 600;
        color: #495057;
    }
    .subject-score {
        font-size: 1.2rem;
        font-weight: bold;
        margin-top: 4px;
    }
    .comment-section-mobile {
        margin-top: 15px;
    }
    .comment-label-mobile {
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    /* Responsive */
    @media (max-width: 991px) {
        .desktop-table { display: none; }
        .mobile-cards { display: block; }
        .grades-tooltip {
            width: 92%;
            max-width: 380px;
        }
    }
    @media (min-width: 992px) {
        .mobile-cards { display: none; }
        .desktop-table { display: block; }
    }
</style>

<div class="main-content class-broadsheet">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Title -->
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

            <!-- Flash Messages -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
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

            <!-- Debug Info (remove in production) -->
            <div class="alert alert-info d-none" id="debugInfo">
                Route URL: {{ route('myprincipalscomment.updateComments', [$schoolclassid, $sessionid, $termid]) }}
            </div>

            @if ($students->isNotEmpty())
                <form id="commentsForm" action="{{ route('myprincipalscomment.updateComments', [$schoolclassid, $sessionid, $termid]) }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        Broadsheet: {{ $schoolclass->schoolclass }} {{ $schoolclass->arm_name }} 
                                        - {{ $schoolterm }} {{ $schoolsession }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <!-- Search Box -->
                                    <div class="search-box mb-4">
                                        <input type="text" class="form-control" placeholder="Search students by name or admission no..." id="searchInput">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>

                                    <!-- Desktop Table -->
                                    <div class="desktop-table">
                                        <div class="table-responsive">
                                            <table class="table table-centered align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>SN</th>
                                                        <th>Admission No</th>
                                                        <th>Student</th>
                                                        <th>Gender</th>
                                                        @foreach ($subjects as $subject)
                                                            <th>{{ $subject }}</th>
                                                        @endforeach
                                                        <th>Principal's Comment</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($students as $index => $student)
                                                        @php
                                                            $picture = $student->picture ? basename($student->picture) : 'unnamed.jpg';
                                                            $imagePath = asset('storage/student_avatars/' . $picture);
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>{{ $student->admissionNo }}</td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <img src="{{ $imagePath }}" class="rounded-circle avatar-sm me-3" alt="">
                                                                    {{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}
                                                                </div>
                                                            </td>
                                                            <td>{{ $student->gender ?? 'N/A' }}</td>
                                                            @foreach ($subjects as $subject)
                                                                @php
                                                                    $score = $scores->where('student_id', $student->id)
                                                                                   ->where('subject_name', $subject)
                                                                                   ->first();
                                                                @endphp
                                                                <td class="{{ ($score && $score->total < 50) ? 'highlight-red' : '' }}">
                                                                    {{ $score?->total ?? '-' }}
                                                                </td>
                                                            @endforeach
                                                            <td class="comment-cell">
                                                                <select class="form-select teacher-comment-dropdown auto-save-comment"
                                                                        name="teacher_comments[{{ $student->id }}]"
                                                                        data-student-id="{{ $student->id }}"
                                                                        data-original-value="{{ $profiles[$student->id] ?? '' }}">
                                                                    <option value="">-- Select Comment --</option>
                                                                    <option value="Excellent result, keep it up!" {{ ($profiles[$student->id] ?? '') == 'Excellent result, keep it up!' ? 'selected' : '' }}>
                                                                        Excellent result, keep it up!
                                                                    </option>
                                                                    <option value="A very good result, keep it up!" {{ ($profiles[$student->id] ?? '') == 'A very good result, keep it up!' ? 'selected' : '' }}>
                                                                        A very good result, keep it up!
                                                                    </option>
                                                                    <option value="Good result, keep it up!" {{ ($profiles[$student->id] ?? '') == 'Good result, keep it up!' ? 'selected' : '' }}>
                                                                        Good result, keep it up!
                                                                    </option>
                                                                    <option value="Average result, there's still room for improvement next term." {{ ($profiles[$student->id] ?? '') == "Average result, there's still room for improvement next term." ? 'selected' : '' }}>
                                                                        Average result, there's still room for improvement next term.
                                                                    </option>
                                                                    <option value="You can do better next term." {{ ($profiles[$student->id] ?? '') == 'You can do better next term.' ? 'selected' : '' }}>
                                                                        You can do better next term.
                                                                    </option>
                                                                    <option value="You need to sit up and be serious." {{ ($profiles[$student->id] ?? '') == 'You need to sit up and be serious.' ? 'selected' : '' }}>
                                                                        You need to sit up and be serious.
                                                                    </option>
                                                                    <option value="Wake up and be serious." {{ ($profiles[$student->id] ?? '') == 'Wake up and be serious.' ? 'selected' : '' }}>
                                                                        Wake up and be serious.
                                                                    </option>
                                                                </select>

                                                                <button type="button"
                                                                        class="comment-info-icon grades-trigger btn btn-link p-0"
                                                                        data-student-id="{{ $student->id }}"
                                                                        data-student-name="{{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}">
                                                                    <i class="ri-eye-line" aria-hidden="true"></i>
                                                                </button>

                                                                <!-- Floating Tooltip -->
                                                                <div class="grades-tooltip position-bottom" id="tooltip-{{ $student->id }}">
                                                                    <div class="tooltip-header" id="header-{{ $student->id }}">
                                                                        Grades
                                                                    </div>

                                                                    <div class="tooltip-body">
                                                                        <table>
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Subject</th>
                                                                                    <th>Score</th>
                                                                                    <th>Grade</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody id="grades-body-{{ $student->id }}"></tbody>
                                                                        </table>
                                                                        <div class="text-center py-4 text-muted d-none" id="no-grades-{{ $student->id }}">
                                                                            No grades available
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Mobile Card View -->
                                    <div class="mobile-cards">
                                        @foreach ($students as $index => $student)
                                            @php
                                                $picture = $student->picture ? basename($student->picture) : 'unnamed.jpg';
                                                $imagePath = asset('storage/student_avatars/' . $picture);
                                            @endphp
                                            <div class="student-card">
                                                <div class="student-header">
                                                    <div class="student-info">
                                                        <img src="{{ $imagePath }}" class="rounded-circle avatar-sm" alt="">
                                                        <div class="student-details">
                                                            <h6>{{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}</h6>
                                                            <div class="student-meta">
                                                                <strong>SN:</strong> {{ $index + 1 }} | 
                                                                <strong>Admission:</strong> {{ $student->admissionNo }} | 
                                                                <strong>Gender:</strong> {{ $student->gender ?? 'N/A' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="student-body">
                                                    <div class="subjects-grid">
                                                        @foreach ($subjects as $subject)
                                                            @php
                                                                $score = $scores->where('student_id', $student->id)
                                                                               ->where('subject_name', $subject)
                                                                               ->first();
                                                            @endphp
                                                            <div class="subject-item">
                                                                <div class="subject-name">{{ $subject }}</div>
                                                                <div class="subject-score {{ ($score && $score->total < 50) ? 'highlight-red' : '' }}">
                                                                    {{ $score?->total ?? '-' }}
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    <div class="comment-section-mobile">
                                                        <div class="comment-label-mobile">Principal's Comment</div>
                                                        <div class="position-relative">
                                                            <select class="form-select teacher-comment-dropdown auto-save-comment"
                                                                    name="teacher_comments[{{ $student->id }}]"
                                                                    data-student-id="{{ $student->id }}"
                                                                    data-original-value="{{ $profiles[$student->id] ?? '' }}">
                                                                <option value="">-- Select Comment --</option>
                                                                <option value="Excellent result, keep it up!" {{ ($profiles[$student->id] ?? '') == 'Excellent result, keep it up!' ? 'selected' : '' }}>
                                                                    Excellent result, keep it up!
                                                                </option>
                                                                <option value="A very good result, keep it up!" {{ ($profiles[$student->id] ?? '') == 'A very good result, keep it up!' ? 'selected' : '' }}>
                                                                    A very good result, keep it up!
                                                                </option>
                                                                <option value="Good result, keep it up!" {{ ($profiles[$student->id] ?? '') == 'Good result, keep it up!' ? 'selected' : '' }}>
                                                                    Good result, keep it up!
                                                                </option>
                                                                <option value="Average result, there's still room for improvement next term." {{ ($profiles[$student->id] ?? '') == "Average result, there's still room for improvement next term." ? 'selected' : '' }}>
                                                                    Average result, there's still room for improvement next term.
                                                                </option>
                                                                <option value="You can do better next term." {{ ($profiles[$student->id] ?? '') == 'You can do better next term.' ? 'selected' : '' }}>
                                                                    You can do better next term.
                                                                </option>
                                                                <option value="You need to sit up and be serious." {{ ($profiles[$student->id] ?? '') == 'You need to sit up and be serious.' ? 'selected' : '' }}>
                                                                    You need to sit up and be serious.
                                                                </option>
                                                                <option value="Wake up and be serious." {{ ($profiles[$student->id] ?? '') == 'Wake up and be serious.' ? 'selected' : '' }}>
                                                                    Wake up and be serious.
                                                                </option>
                                                            </select>

                                                            <button type="button"
                                                                    class="comment-info-icon grades-trigger btn btn-link p-0"
                                                                    data-student-id="{{ $student->id }}"
                                                                    data-student-name="{{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}">
                                                                <i class="ri-eye-line" aria-hidden="true"></i>
                                                            </button>
                                                        </div>

                                                        <!-- Mobile Tooltip -->
                                                        <div class="grades-tooltip position-bottom mt-3" id="tooltip-{{ $student->id }}">
                                                            <div class="tooltip-header" id="header-{{ $student->id }}">
                                                                Grades
                                                            </div>

                                                            <div class="tooltip-body">
                                                                <table>
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Subject</th>
                                                                            <th>Score</th>
                                                                            <th>Grade</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="grades-body-{{ $student->id }}"></tbody>
                                                                </table>
                                                                <div class="text-center py-4 text-muted d-none" id="no-grades-{{ $student->id }}">
                                                                    No grades available
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="text-end mt-4">
                                        <button type="submit" class="btn btn-success btn-lg">Save All Comments</button>
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

<script>
// Grades data
window.studentGrades = @json($studentGrades);

// Toast notification function
function showToast(message, type = 'info') {
    // Remove any existing toast
    const existingToast = document.querySelector('.auto-save-toast');
    if (existingToast) existingToast.remove();
    
    const toast = document.createElement('div');
    toast.className = `auto-save-toast alert alert-${type} alert-dismissible fade show`;
    toast.style.zIndex = '99999';
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="ri-${type === 'success' ? 'checkbox-circle' : 'error-warning'}-fill me-2 fs-5"></i>
            <span class="flex-grow-1">${message}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 3000);
}

// Auto-save on dropdown change
document.querySelectorAll('.auto-save-comment').forEach(select => {
    select.addEventListener('change', function () {
        const studentId = this.getAttribute('data-student-id');
        const comment = this.value;
        const originalValue = this.getAttribute('data-original-value');
        
        // Don't save if value didn't change
        if (comment === originalValue) {
            return;
        }

        // Show saving indicator
        const originalBorderColor = this.style.borderColor;
        const originalBackgroundColor = this.style.backgroundColor;
        this.style.borderColor = '#ffc107';
        this.style.backgroundColor = '#fff3cd';
        this.disabled = true;
        
        // Store the current option text
        const selectedOption = this.options[this.selectedIndex];
        const originalText = selectedOption ? selectedOption.text : '';
        
        // Change dropdown text to show saving
        if (selectedOption) {
            selectedOption.text = 'Saving...';
        }

        // Create form data
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('teacher_comments[' + studentId + ']', comment);

        // Build URL
        const saveUrl = '{{ route("myprincipalscomment.updateComments", [$schoolclassid, $sessionid, $termid]) }}';
        
        fetch(saveUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            // First check if response is OK
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            // Check if response is JSON
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.includes("application/json")) {
                return response.json();
            } else {
                // If not JSON, try to parse as text
                return response.text().then(text => {
                    // If it's HTML, it might be a redirect or error page
                    if (text.includes('<!DOCTYPE html>') || text.includes('<html')) {
                        throw new Error('Server returned HTML instead of JSON');
                    }
                    return { success: true, message: 'Comment saved' };
                });
            }
        })
        .then(data => {
            if (data.success) {
                // Update original value attribute
                this.setAttribute('data-original-value', comment);
                
                // Show success
                this.style.borderColor = '#28a745';
                this.style.backgroundColor = '#d1e7dd';
                showToast(data.message || 'Comment saved successfully!', 'success');
                
                // Restore original option text
                if (selectedOption) {
                    selectedOption.text = originalText;
                }
                
                // Reset styling after delay
                setTimeout(() => {
                    this.style.borderColor = originalBorderColor;
                    this.style.backgroundColor = originalBackgroundColor;
                    this.disabled = false;
                }, 1500);
            } else {
                throw new Error(data.message || 'Failed to save comment');
            }
        })
        .catch(err => {
            console.error('Auto-save error:', err);
            
            // Revert to original value on error
            this.value = originalValue;
            this.style.borderColor = '#dc3545';
            this.style.backgroundColor = '#f8d7da';
            this.disabled = false;
            
            // Restore original option text
            if (selectedOption) {
                selectedOption.text = originalText;
            }
            
            // Show error message
            let errorMsg = 'Error saving comment';
            if (err.message.includes('403')) {
                errorMsg = 'Unauthorized: You are not assigned to this class';
            } else if (err.message.includes('419')) {
                errorMsg = 'Session expired. Please refresh the page.';
            } else if (err.message.includes('500')) {
                errorMsg = 'Server error. Please try again.';
            } else {
                errorMsg = err.message;
            }
            
            showToast(errorMsg, 'error');
            
            // Reset styling after delay
            setTimeout(() => {
                this.style.borderColor = originalBorderColor;
                this.style.backgroundColor = originalBackgroundColor;
            }, 3000);
        });
    });
});

// Tooltip functionality
document.querySelectorAll('.grades-trigger').forEach(trigger => {
    const tooltipId = `tooltip-${trigger.getAttribute('data-student-id')}`;
    const tooltip = document.getElementById(tooltipId);
    if (!tooltip) return;

    const header = tooltip.querySelector('.tooltip-header');
    const body = tooltip.querySelector('.tooltip-body');
    const noGrades = tooltip.querySelector('.text-center.py-4');

    let hideTimeout;

    const positionTooltip = () => {
        const rect = trigger.getBoundingClientRect();
        const tooltipHeight = 480;
        const viewportHeight = window.innerHeight;
        const spaceBelow = viewportHeight - rect.bottom;
        const spaceAbove = rect.top;

        tooltip.classList.remove('position-top', 'position-bottom');

        if (spaceBelow < tooltipHeight && spaceAbove > spaceBelow) {
            tooltip.classList.add('position-top');
        } else {
            tooltip.classList.add('position-bottom');
        }
    };

    const showTooltip = () => {
        clearTimeout(hideTimeout);
        document.querySelectorAll('.grades-tooltip.show').forEach(t => t.classList.remove('show'));
        
        positionTooltip();
        tooltip.classList.add('show');

        const studentId = trigger.getAttribute('data-student-id');
        const studentName = trigger.getAttribute('data-student-name') || 'Student';
        header.textContent = studentName + "'s Grades";

        const grades = window.studentGrades[studentId] || [];
        const tbody = document.getElementById(`grades-body-${studentId}`);
        tbody.innerHTML = '';

        if (grades.length === 0) {
            noGrades.classList.remove('d-none');
        } else {
            noGrades.classList.add('d-none');
            grades.forEach(g => {
                const row = document.createElement('tr');
                const gradeColor = g.grade[0] === 'A' ? 'success' :
                                   g.grade[0] === 'B' || g.grade === 'C' ? 'info' :
                                   g.grade[0] === 'D' || g.grade[0] === 'E' ? 'warning' : 'danger';

                row.innerHTML = `
                    <td><strong>${g.subject}</strong></td>
                    <td class="text-center fw-bold ${g.score < 50 ? 'text-danger' : 'text-success'}">${g.score}</td>
                    <td class="text-center">
                        <span class="badge bg-${gradeColor} grade-badge">${g.grade}</span>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
    };

    const hideTooltip = () => {
        hideTimeout = setTimeout(() => tooltip.classList.remove('show'), 300);
    };

    // Desktop: hover
    if (window.innerWidth > 991) {
        trigger.addEventListener('mouseenter', showTooltip);
        trigger.addEventListener('focus', showTooltip);
        trigger.addEventListener('mouseleave', hideTooltip);
        trigger.addEventListener('blur', hideTooltip);
    } else {
        // Mobile: click
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            if (tooltip.classList.contains('show')) {
                tooltip.classList.remove('show');
            } else {
                showTooltip();
            }
        });
        document.addEventListener('click', (e) => {
            if (!tooltip.contains(e.target) && !trigger.contains(e.target)) {
                tooltip.classList.remove('show');
            }
        });
    }
});

// Search functionality
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('searchInput');
    if (input) {
        input.addEventListener('input', function () {
            const term = this.value.toLowerCase().trim();

            // Desktop
            document.querySelectorAll('.desktop-table tbody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = term === '' || text.includes(term) ? '' : 'none';
            });

            // Mobile
            document.querySelectorAll('.mobile-cards .student-card').forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = term === '' || text.includes(term) ? '' : 'none';
            });
        });
    }
});

// Form submission handler
document.getElementById('commentsForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Show loading on submit button
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.includes("application/json")) {
            return response.json();
        } else {
            return response.text().then(text => {
                if (text.includes('<!DOCTYPE html>') || text.includes('<html')) {
                    return { success: true, message: 'All comments saved successfully!' };
                }
                return { success: true, message: 'Saved' };
            });
        }
    })
    .then(data => {
        if (data.success) {
            showToast(data.message || 'All comments saved successfully!', 'success');
            
            // Update all original values
            document.querySelectorAll('.auto-save-comment').forEach(select => {
                select.setAttribute('data-original-value', select.value);
                select.style.borderColor = '#28a745';
                select.style.backgroundColor = '#d1e7dd';
                setTimeout(() => {
                    select.style.borderColor = '';
                    select.style.backgroundColor = '';
                }, 2000);
            });
            
            // Reload page after successful save
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            throw new Error(data.message || 'Failed to save comments');
        }
    })
    .catch(err => {
        console.error('Form save error:', err);
        
        let errorMsg = 'Error saving comments';
        if (err.message.includes('403')) {
            errorMsg = 'Unauthorized: You are not assigned to this class';
        } else if (err.message.includes('419')) {
            errorMsg = 'Session expired. Please refresh the page and try again.';
        } else if (err.message.includes('500')) {
            errorMsg = 'Server error. Please try again.';
        } else {
            errorMsg = err.message;
        }
        
        showToast(errorMsg, 'error');
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalBtnText;
        submitBtn.disabled = false;
    });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+S or Cmd+S to save
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        document.getElementById('commentsForm')?.dispatchEvent(new Event('submit'));
    }
    
    // Escape to close tooltips
    if (e.key === 'Escape') {
        document.querySelectorAll('.grades-tooltip.show').forEach(tooltip => {
            tooltip.classList.remove('show');
        });
    }
});

// Initialize original values on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.auto-save-comment').forEach(select => {
        select.setAttribute('data-original-value', select.value);
    });
    
    // Add debug info
    console.log('Route URL:', '{{ route("myprincipalscomment.updateComments", [$schoolclassid, $sessionid, $termid]) }}');
    console.log('Student grades loaded:', Object.keys(window.studentGrades || {}).length, 'students');
});
</script>
@endsection