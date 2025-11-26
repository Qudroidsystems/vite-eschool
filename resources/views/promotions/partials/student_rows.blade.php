{{-- resources/views/promotions/partials/student_rows.blade.php --}}
@forelse ($allstudents as $student)
    <tr>
        <td class="fw-medium">{{ $student->admissionno }}</td>
        <td>
            @if ($student->picture)
                <img src="{{ asset('storage/student_avatars' . $student->picture) }}" alt="Student Picture" width="50" height="50" class="rounded-circle" onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}';">
            @else
                <span class="text-muted">No Picture</span>
            @endif
        </td>
        <td>{{ $student->lastname }}</td>
        <td>{{ $student->firstname }}</td>
        <td>{{ $student->othername ?? '-' }}</td>
        <td>
            @if($student->gender === 'Male')
                <span class="badge bg-primary-subtle text-primary">
                    <i class="ri-men-line me-1"></i>Male
                </span>
            @else
                <span class="badge bg-danger-subtle text-danger">
                    <i class="ri-women-line me-1"></i>Female
                </span>
            @endif
        </td>
        <td>{{ $student->schoolclass }}</td>
        <td>{{ $student->schoolarm ?? '-' }}</td>
        <td>{{ $student->session }}</td>
        <td>
            @php
                $status = strtolower($student->promotion_status ?? 'n/a');
            @endphp
            
            @if($status === 'promoted')
                <span class="badge bg-success-subtle text-success fs-6 px-3 py-2">
                    <i class="ri-arrow-up-circle-line me-1"></i>Promoted
                </span>
            @elseif($status === 'repeated')
                <span class="badge bg-warning-subtle text-warning fs-6 px-3 py-2">
                    <i class="ri-repeat-line me-1"></i>Repeated
                </span>
            @else
                <span class="badge bg-secondary-subtle text-secondary fs-6 px-3 py-2">
                    <i class="ri-question-line me-1"></i>N/A
                </span>
            @endif
        </td>
        <td>
            <div class="d-flex gap-2">
                <button type="button" 
                        class="btn btn-sm btn-primary d-inline-flex align-items-center"
                        onclick="openPromotionModal(
                            '{{ $student->stid }}',
                            '{{ $student->admissionno }}',
                            '{{ $student->firstname }}',
                            '{{ $student->lastname }}',
                            '{{ $student->othername }}',
                            '{{ $student->picture }}',
                            '{{ $student->schoolclass }}',
                            '{{ $student->schoolarm }}',
                            '{{ $student->session }}',
                            '{{ $student->termid }}',
                            '{{ $student->promotion_status }}'
                        )">
                    <i class="ri-edit-line me-1"></i>
                    Manage
                </button>
                
                <button type="button" 
                        class="btn btn-sm btn-danger d-inline-flex align-items-center"
                        onclick="removeStudent(
                            '{{ $student->stid }}',
                            {{ $student->schoolclassID }},
                            {{ $student->sessionid }},
                            {{ $student->termid }},
                            '{{ $student->admissionno }}',
                            '{{ $student->firstname }}',
                            '{{ $student->lastname }}'
                        )"
                        title="Remove from Class">
                    <i class="ri-delete-bin-line me-1"></i>
                    Remove
                </button>
                
                {{-- <a href="{{ route('students.show', $student->stid) }}" 
                   class="btn btn-sm btn-info d-inline-flex align-items-center"
                   title="View Details">
                    <i class="ri-eye-line me-1"></i>
                    View
                </a> --}}
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="11" class="text-center py-4">
            <div class="text-muted">
                <i class="ri-inbox-line fs-1 d-block mb-2"></i>
                <p class="mb-0">No students found</p>
            </div>
        </td>
    </tr>
@endforelse

<style>
    /* Badge Subtle Backgrounds */
    .bg-success-subtle {
        background-color: rgba(25, 135, 84, 0.1) !important;
    }
    
    .bg-warning-subtle {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }
    
    .bg-secondary-subtle {
        background-color: rgba(108, 117, 125, 0.1) !important;
    }
    
    .bg-primary-subtle {
        background-color: rgba(13, 110, 253, 0.1) !important;
    }
    
    .bg-danger-subtle {
        background-color: rgba(220, 53, 69, 0.1) !important;
    }
    
    /* Text Colors */
    .text-success {
        color: #198754 !important;
    }
    
    .text-warning {
        color: #ffc107 !important;
    }
    
    .text-secondary {
        color: #6c757d !important;
    }
    
    .text-primary {
        color: #0d6efd !important;
    }
    
    .text-danger {
        color: #dc3545 !important;
    }
    
    /* Button Improvements */
    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 0.25rem;
        font-weight: 500;
        transition: all 0.15s ease-in-out;
    }
    
    .btn-sm:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .btn-sm i {
        font-size: 1rem;
    }
    
    /* Badge Styling */
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
    }
    
    /* Table Row Hover Effect */
    #studentListTable tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
</style>