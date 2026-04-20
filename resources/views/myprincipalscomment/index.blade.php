@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ $pagetitle }}</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Principal Comments</a></li>
                                <li class="breadcrumb-item active">My Assignments</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
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

            <!-- Search Box + Counters -->
            <div class="row mb-4">
                <div class="col-lg-5">
                    <div class="search-box">
                        <input type="text" class="form-control search" id="classSearchInput" placeholder="Search by class name or arm...">
                        <i class="ri-search-line search-icon"></i>
                    </div>
                </div>
                <div class="col-lg-7 d-flex align-items-center justify-content-end gap-3">
                    <span class="text-muted">Total Classes: <strong id="totalClasses">{{ $assignments->count() }}</strong></span>
                    <span class="text-muted">Showing: <strong id="visibleClasses">{{ $assignments->count() }}</strong></span>
                </div>
            </div>

            <!-- Assignments Card -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0">Classes Assigned for Principal's Comment</h5>
                            <span class="badge bg-primary fs-6">{{ $assignments->count() }} Assigned</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-centered align-middle table-nowrap mb-0" id="assignmentsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Class</th>
                                            <th>Last Updated</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    {{-- <tbody id="assignmentsBody">
                                        @forelse($assignments as $index => $assignment)
                                            <tr class="assignment-row"
                                                data-search-text="{{ strtolower($assignment->sclass . ' ' . ($assignment->schoolarm ?? '')) }}">
                                                <td class="sn">{{ $loop->iteration }}</td>
                                                <td>
                                                    <strong>{{ $assignment->sclass }} {{ $assignment->schoolarm ?? '' }}</strong>
                                                </td>
                                                <td>
                                                    {{ $assignment->updated_at ? $assignment->updated_at->format('d M Y, h:i A') : 'Never' }}
                                                </td>
                                                <td>
                                                    <a href="{{ route('myprincipalscomment.classbroadsheet', [
                                                        $assignment->schoolclassid,
                                                        $currentSession->id ?? 1,
                                                        $currentTerm->id ?? 1
                                                    ]) }}"
                                                       class="btn btn-soft-success btn-sm">
                                                        <i class="ph-eye me-1"></i> View Broadsheet & Enter Comments
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr id="emptyStateRow">
                                                <td colspan="4" class="text-center py-5">
                                                    <lord-icon src="https://cdn.lordicon.com/msoeawqm.json"
                                                               trigger="loop"
                                                               colors="primary:#121331,secondary:#08a88a"
                                                               style="width:120px;height:120px">
                                                    </lord-icon>
                                                    <h5 class="mt-4 text-muted">No Classes Assigned</h5>
                                                    <p class="text-muted mb-0">You have not been assigned any class for entering Principal's comments yet.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody> --}}

                                     <tbody id="assignmentsBody">
                                        @forelse($assignments as $index => $assignment)
                                            <tr class="assignment-row"
                                                data-search-text="{{ strtolower($assignment->sclass . ' ' . ($assignment->schoolarm ?? '')) }}">
                                                <td class="sn">{{ $loop->iteration }}</td>
                                                <td>
                                                    <strong>{{ $assignment->sclass }} {{ $assignment->schoolarm ?? '' }}</strong>
                                                </td>
                                                <td>
                                                    {{ $assignment->updated_at ? $assignment->updated_at->format('d M Y, h:i A') : 'Never' }}
                                                </td>
                                                <td>
                                                    <a href="{{ route('myprincipalscomment.classbroadsheet', [
                                                        $assignment->schoolclassid,
                                                        $currentSession->id ?? 1,
                                                        2
                                                    ]) }}"
                                                       class="btn btn-soft-success btn-sm">
                                                        <i class="ph-eye me-1"></i> View Broadsheet & Enter Comments
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr id="emptyStateRow">
                                                <td colspan="4" class="text-center py-5">
                                                    <lord-icon src="https://cdn.lordicon.com/msoeawqm.json"
                                                               trigger="loop"
                                                               colors="primary:#121331,secondary:#08a88a"
                                                               style="width:120px;height:120px">
                                                    </lord-icon>
                                                    <h5 class="mt-4 text-muted">No Classes Assigned</h5>
                                                    <p class="text-muted mb-0">You have not been assigned any class for entering Principal's comments yet.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                <!-- No Search Results -->
                                <div id="noSearchResults" class="text-center py-5" style="display: none;">
                                    <lord-icon src="https://cdn.lordicon.com/msoeawqm.json"
                                               trigger="loop"
                                               colors="primary:#121331,secondary:#08a88a"
                                               style="width:120px;height:120px">
                                    </lord-icon>
                                    <h5 class="mt-4 text-muted">No Classes Found</h5>
                                    <p class="text-muted mb-0">Try adjusting your search term.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
window.studentGradesData = @json($studentGrades);
let activeTooltip = null;

function showToast(message, type) {
    type = type || 'info';
    var existing = document.querySelector('.auto-save-toast');
    if (existing) existing.remove();

    var toast = document.createElement('div');
    toast.className = 'auto-save-toast alert alert-' + type + ' alert-dismissible fade show';
    toast.innerHTML =
        '<div class="d-flex align-items-center">' +
            '<i class="ri-' + (type === 'success' ? 'checkbox-circle' : 'information') + '-fill me-2 fs-5"></i>' +
            '<span>' + escapeHtml(message) + '</span>' +
            '<button type="button" class="btn-close ms-3" data-bs-dismiss="alert"></button>' +
        '</div>';
    document.body.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 3000);
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function closeAllTooltips() {
    document.querySelectorAll('.grades-tooltip.show').forEach(function(t) {
        t.classList.remove('show');
    });
    activeTooltip = null;
}

function showTooltip(tooltipId, studentId, studentName) {
    var tooltip = document.getElementById(tooltipId);
    if (!tooltip) return;
    closeAllTooltips();

    var titleEl = document.getElementById('tooltip-title-' + studentId);
    if (titleEl) titleEl.textContent = studentName + "'s Performance";

    var grades = window.studentGradesData[studentId] || [];
    var tbody = document.getElementById('grades-body-' + studentId);

    if (tbody) {
        tbody.innerHTML = '';
        if (grades.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No grades available</td></tr>';
        } else {
            grades.forEach(function(g) {
                var gradeClass = 'grade-f';
                if (g.grade_letter === 'A') gradeClass = 'grade-a';
                else if (g.grade_letter === 'B') gradeClass = 'grade-b';
                else if (g.grade_letter === 'C') gradeClass = 'grade-c';
                else if (g.grade_letter === 'D') gradeClass = 'grade-d';
                else if (g.grade_letter === 'E') gradeClass = 'grade-e';

                var termScoreClass = (g.term_score < 50) ? 'text-danger' : 'text-success';
                var cumScoreClass  = (g.score < 50) ? 'text-danger' : 'text-success';

                var row = document.createElement('tr');
                row.innerHTML =
                    '<td><strong>' + escapeHtml(g.subject) + '</strong></td>' +
                    '<td class="text-center fw-bold ' + termScoreClass + '">' + (g.term_score || '-') + '</td>' +
                    '<td class="text-center fw-bold ' + cumScoreClass + '">' + (g.score || '-') + '</td>' +
                    '<td class="text-center"><span class="grade-badge ' + gradeClass + '">' + escapeHtml(g.grade) + '</span></td>';
                tbody.appendChild(row);
            });
        }
    }

    tooltip.classList.add('show');
    activeTooltip = tooltipId;
}

// Update the saved comment preview in the UI without reloading
function updateCommentUI(studentId, comment) {
    // Find all rows/cards for this student (covers both desktop and mobile)
    document.querySelectorAll('[data-student-id="' + studentId + '"]').forEach(function(container) {

        // Update the "Comment saved" indicator in the student name cell (desktop)
        var nameCell = container.querySelector('td:nth-child(3) div > div');
        if (nameCell) {
            var savedBadge = nameCell.querySelector('.text-success');
            if (comment) {
                if (!savedBadge) {
                    var small = document.createElement('small');
                    small.className = 'd-block text-success mt-1';
                    small.innerHTML = '<i class="ri-check-double-line"></i> Comment saved';
                    nameCell.appendChild(small);
                }
            } else {
                if (savedBadge) savedBadge.remove();
            }
        }

        // Update saved comment preview box (desktop)
        var commentCell = container.querySelector('.comment-cell');
        if (commentCell) {
            var existingPreviewWrapper = commentCell.querySelector('.mb-2:not(.intelligent-comment-section)');

            if (comment) {
                var previewText = comment.length > 100 ? comment.substring(0, 100) + '...' : comment;
                if (existingPreviewWrapper) {
                    // Update existing preview
                    var previewBox = existingPreviewWrapper.querySelector('.saved-comment-preview small');
                    if (previewBox) previewBox.textContent = previewText;
                } else {
                    // Create new preview block
                    var newWrapper = document.createElement('div');
                    newWrapper.className = 'mb-2';
                    newWrapper.innerHTML =
                        '<small class="text-success d-block mb-1">' +
                            '<i class="ri-chat-check-line"></i> <strong>Saved Comment</strong>' +
                        '</small>' +
                        '<div class="saved-comment-preview">' +
                            '<small class="text-secondary">' + escapeHtml(previewText) + '</small>' +
                        '</div>';

                    // Insert before the select dropdown
                    var selectEl = commentCell.querySelector('select');
                    if (selectEl) {
                        commentCell.insertBefore(newWrapper, selectEl);
                    }
                }
            } else {
                if (existingPreviewWrapper) existingPreviewWrapper.remove();
            }
        }

        // Update the mobile card checkmark badge
        var mobileHeader = container.querySelector('.student-details h6');
        if (mobileHeader) {
            var mobileBadge = mobileHeader.querySelector('.badge.bg-success');
            if (comment && !mobileBadge) {
                var badge = document.createElement('span');
                badge.className = 'badge bg-success ms-2';
                badge.textContent = '✓';
                mobileHeader.appendChild(badge);
            } else if (!comment && mobileBadge) {
                mobileBadge.remove();
            }
        }
    });
}

// AUTO-SAVE: Save a single student's comment on dropdown change
document.querySelectorAll('.auto-save-comment').forEach(function(select) {
    select.addEventListener('change', function() {
        var studentId = this.dataset.studentId;
        var comment   = this.value.trim();
        var original  = this.dataset.originalValue || '';
        var self      = this;

        if (comment === original) return;

        self.style.backgroundColor = '#fff3cd';
        self.disabled = true;

        var formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('teacher_comments[' + studentId + ']', comment);

        fetch('{{ route("myprincipalscomment.updateComments", [$schoolclassid, $sessionid, $termid]) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                // Update original value tracker
                self.dataset.originalValue = comment;
                // Also sync the sibling select (desktop/mobile mirror)
                document.querySelectorAll('.auto-save-comment[data-student-id="' + studentId + '"]').forEach(function(s) {
                    s.value = comment;
                    s.dataset.originalValue = comment;
                });
                // Update UI in place — no reload
                updateCommentUI(studentId, comment);
                self.style.backgroundColor = '#d1e7dd';
                self.disabled = false;
                showToast('Comment saved!', 'success');
                setTimeout(function() {
                    self.style.backgroundColor = '';
                }, 1500);
            } else {
                throw new Error(data.message || 'Save failed');
            }
        })
        .catch(function(error) {
            console.error('Auto-save error:', error);
            self.value = original;
            self.style.backgroundColor = '#f8d7da';
            self.disabled = false;
            showToast('Error: ' + error.message, 'danger');
            setTimeout(function() {
                self.style.backgroundColor = '';
            }, 2000);
        });
    });
});

// BULK SAVE: Save all visible comments
var commentsForm = document.getElementById('commentsForm');
if (commentsForm) {
    commentsForm.addEventListener('submit', function(e) {
        e.preventDefault();

        var submitBtn       = document.getElementById('saveAllBtn');
        var savingIndicator = document.getElementById('savingIndicator');
        var originalText    = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ri-loader-4-line spin-icon me-1"></i> Saving All Comments...';
        savingIndicator.style.display = 'inline-block';

        // Only collect from visible selects to avoid duplicate hidden/visible conflicts
        var formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');

        document.querySelectorAll('.auto-save-comment').forEach(function(select) {
            if (select.offsetParent === null) return; // skip hidden (CSS display:none)
            var val = select.value.trim();
            if (val !== '') {
                formData.append('teacher_comments[' + select.dataset.studentId + ']', val);
            }
        });

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                // Update UI for all students that had a value selected
                document.querySelectorAll('.auto-save-comment').forEach(function(select) {
                    if (select.offsetParent === null) return;
                    var val = select.value.trim();
                    if (val) {
                        select.dataset.originalValue = val;
                        updateCommentUI(select.dataset.studentId, val);
                    }
                });
                showToast(data.message || 'All comments saved successfully!', 'success');
            } else {
                throw new Error(data.message || 'Save failed');
            }
        })
        .catch(function(error) {
            console.error('Bulk save error:', error);
            showToast('Error saving comments: ' + error.message, 'danger');
        })
        .finally(function() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            savingIndicator.style.display = 'none';
        });
    });
}

// Tooltip triggers — desktop only
if (window.innerWidth > 1199) {
    document.querySelectorAll('.grades-trigger').forEach(function(trigger) {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var sid  = this.dataset.studentId;
            var name = this.dataset.studentName;
            var tid  = 'tooltip-' + sid;
            if (activeTooltip === tid) {
                closeAllTooltips();
            } else {
                showTooltip(tid, sid, name);
            }
        });
    });

    document.querySelectorAll('.tooltip-close').forEach(function(btn) {
        btn.addEventListener('click', closeAllTooltips);
    });

    document.addEventListener('click', function(e) {
        if (!activeTooltip) return;
        var activeEl  = document.getElementById(activeTooltip);
        var activeSid = activeTooltip.replace('tooltip-', '');
        var trigger   = document.querySelector('.grades-trigger[data-student-id="' + activeSid + '"]');
        if (activeEl && !activeEl.contains(e.target) && (!trigger || !trigger.contains(e.target))) {
            closeAllTooltips();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeAllTooltips();
    });
}

// Search
var searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        var term = this.value.toLowerCase().trim();
        document.querySelectorAll('.desktop-table tbody tr').forEach(function(row) {
            row.style.display = (term === '' || row.textContent.toLowerCase().includes(term)) ? '' : 'none';
        });
        document.querySelectorAll('.mobile-cards .student-card').forEach(function(card) {
            card.style.display = (term === '' || card.textContent.toLowerCase().includes(term)) ? '' : 'none';
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.auto-save-comment').forEach(function(select) {
        select.dataset.originalValue = select.value;
    });
});
</script>
@endsection
