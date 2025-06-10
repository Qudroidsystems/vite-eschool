$(document).ready(function() {
    // Set CSRF token for AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Initialize Toastr options
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 5000
    };

    // Handle exam score input changes
    $(document).on('change blur', '.score-input', function() {
        var $input = $(this);
        var id = $input.data('id');
        var field = $input.data('field');
        var value = $input.val();

        // Validate input
        if (value === '' || isNaN(value) || value < 0 || value > 100) {
            toastr.error('Please enter a valid score between 0 and 100.');
            $input.val($input.data('original-value') || '');
            return;
        }

        // Store original value for rollback on error
        $input.data('original-value', value);

        $.ajax({
            url: '{{ route("scoresheet.update-score-mock") }}',
            type: 'POST',
            data: {
                id: id,
                field: field,
                value: value
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    var $row = $input.closest('tr');
                    // Update broadsheets data
                    var broadsheet = window.broadsheets.find(b => b.id == id);
                    if (broadsheet) {
                        broadsheet.exam = response.broadsheet.exam;
                        broadsheet.total = response.broadsheet.total;
                        broadsheet.grade = response.broadsheet.grade;
                        broadsheet.subjectpositionclass = response.broadsheet.subjectpositionclass;
                        broadsheet.remark = response.broadsheet.remark;
                    }
                } else {
                    toastr.error(response.message);
                    $input.val($input.data('original-value') || '');
                }
            },
            error: function(xhr) {
                toastr.error('Failed to update score: ' + (xhr.responseJSON?.message || 'Server error'));
                $input.val($input.data('original-value') || '');
                console.error('Error:', xhr.responseText);
            }
        });
    });

    // Search functionality
    $('#searchInput').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('#scoresheetTableBody tr').each(function() {
            var $row = $(this);
            var admissionNo = $row.find('.admissionno').data('admissionno')?.toLowerCase() || '';
            var name = $row.find('.name').data('name')?.toLowerCase() || '';
            if (admissionNo.includes(searchTerm) || name.includes(searchTerm)) {
                $row.show();
            } else {
                $row.hide();
            }
        });
        updateNoDataAlert();
    });

    // Clear search
    $('#clearSearch').on('click', function() {
        $('#searchInput').val('').trigger('keyup');
    });

    // Check all checkboxes
    $('#checkAll').on('change', function() {
        $('.score-checkbox').prop('checked', $(this).is(':checked'));
    });

    // Update checkAll state when individual checkboxes change
    $(document).on('change', '.score-checkbox', function() {
        if ($('.score-checkbox:checked').length === $('.score-checkbox').length) {
            $('#checkAll').prop('checked', true);
        } else {
            $('#checkAll').prop('checked', false);
        }
    });

    // Populate scores modal
    $('#scoresModal').on('show.bs.modal', function() {
        var $tbody = $('#scoresBody');
        $tbody.empty();
        window.broadsheets.forEach(function(broadsheet, index) {
            $tbody.append(`
                <tr>
                    <td>${index + 1}</td>
                    <td>${broadsheet.admissionno || '-'}</td>
                    <td>${(broadsheet.fname || '') + ' ' + (broadsheet.lname || '')}</td>
                    <td>${broadsheet.exam || ''}</td>
                    <td>${broadsheet.total || ''}</td>
                    <td>${broadsheet.grade || ''}</td>
                    <td>${broadsheet.subjectpositionclass || ''}</td>
                    <td>${broadsheet.remark || ''}</td>
                </tr>
            `);
        });
    });

    // Delete selected scores
    window.deleteSelectedScores = function() {
        var selectedIds = $('.score-checkbox:checked').map(function() {
            return $(this).data('id');
        }).get();

        if (selectedIds.length === 0) {
            toastr.warning('Please select at least one score to delete.');
            return;
        }

        if (!confirm('Are you sure you want to delete the selected scores?')) {
            return;
        }

        $.ajax({
            url: '{{ route("scoresheet.destroy-mock") }}', // Note: This route needs to handle multiple IDs
            type: 'POST', // Use POST with _method: DELETE
            data: {
                _method: 'DELETE',
                ids: selectedIds
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    selectedIds.forEach(function(id) {
                        $(`tr:has(.score-checkbox[data-id="${id}"])`).remove();
                        window.broadsheets = window.broadsheets.filter(b => b.id != id);
                    });
                    $('#scoreCount').text(window.broadsheets.length);
                    updateNoDataAlert();
                    $('#checkAll').prop('checked', false);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('Failed to delete scores: ' + (xhr.responseJSON?.message || 'Server error'));
                console.error('Error:', xhr.responseText);
            }
        });
    };

    // Update no data alert visibility
    function updateNoDataAlert() {
        var visibleRows = $('#scoresheetTableBody tr:visible').length;
        if (visibleRows === 0) {
            $('#noDataAlert').show();
            $('#noDataRow').show();
        } else {
            $('#noDataAlert').hide();
            $('#noDataRow').hide();
        }
    }

    // Initialize original values for inputs
    $('.score-input').each(function() {
        $(this).data('original-value', $(this).val());
    });
});


// Ensure broadsheets is an array
function ensureBroadsheetsArray() {
    if (typeof window.broadsheets === 'undefined') {
        window.broadsheets = [];
        console.warn("window.broadsheets was undefined, initialized as empty array");
    } else if (!Array.isArray(window.broadsheets)) {
        if (window.broadsheets && typeof window.broadsheets === 'object') {
            const keys = Object.keys(window.broadsheets);
            if (keys.length > 0 && keys.every(key => !isNaN(key))) {
                window.broadsheets = Object.values(window.broadsheets);
                console.log("Converted window.broadsheets object to array");
            } else {
                window.broadsheets = [window.broadsheets];
                console.log("Wrapped single broadsheet object in array");
            }
        } else {
            window.broadsheets = [];
            console.warn("window.broadsheets was not an array or object, initialized as empty array");
        }
    }
    console.log("window.broadsheets is now an array with", window.broadsheets.length, "items");
}

// Populate scores modal
$('#scoresModal').on('show.bs.modal', function() {
    ensureBroadsheetsArray();
    var $tbody = $('#scoresBody');
    $tbody.empty();

    if (!window.broadsheets || window.broadsheets.length === 0) {
        $tbody.append(`
            <tr>
                <td colspan="8" class="text-center">No mock scores available.</td>
            </tr>
        `);
        return;
    }

    window.broadsheets.forEach(function(broadsheet, index) {
        const name = broadsheet.name || `${broadsheet.fname || ''} ${broadsheet.lname || ''}`.trim();
        const exam = parseFloat(broadsheet.exam) || 0;
        const total = parseFloat(broadsheet.total) || exam; // Total is same as exam if not provided
        const examClass = exam < 40 && exam !== 0 ? 'text-danger' : '';
        const totalClass = total < 40 && total !== 0 ? 'text-danger' : '';

        $tbody.append(`
            <tr>
                <td>${index + 1}</td>
                <td>${broadsheet.admissionno || '-'}</td>
                <td>${name || '-'}</td>
                <td class="${examClass}">${broadsheet.exam !== null && broadsheet.exam !== undefined ? broadsheet.exam : '-'}</td>
                <td class="${totalClass}">${broadsheet.total !== null && broadsheet.total !== undefined ? parseFloat(broadsheet.total).toFixed(1) : '-'}</td>
                <td>${broadsheet.grade || '-'}</td>
                <td>${broadsheet.subjectpositionclass || '-'}</td>
                <td>${broadsheet.remark || '-'}</td>
            </tr>
        `);
    });
});