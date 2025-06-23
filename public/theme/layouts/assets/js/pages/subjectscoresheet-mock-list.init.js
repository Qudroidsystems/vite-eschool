// Debug: Log script loading
console.log("subjectscoresheet-mock.init.js loading at", new Date().toISOString());

try {
    // Check for jQuery
    if (typeof jQuery === 'undefined') {
        throw new Error("jQuery is not loaded.");
    }
    console.log("jQuery version:", jQuery.fn.jquery);

    // Check for Toastr
    if (typeof toastr === 'undefined') {
        throw new Error("Toastr is not loaded.");
    }
    console.log("Toastr detected.");

    // Check for Bootstrap
    if (typeof bootstrap === 'undefined') {
        console.warn("Bootstrap is not loaded. Modals may not work.");
    } else {
        console.log("Bootstrap detected.");
    }

    $(document).ready(function() {
        console.log("Document ready at", new Date().toISOString());

        // Verify CSRF token
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (!csrfToken) {
            console.error("CSRF token not found. AJAX requests will fail.");
            toastr.error("CSRF token missing. Please refresh the page.");
        } else {
            console.log("CSRF token found:", csrfToken);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });
        }

        // Initialize Toastr options
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 5000
        };

        // Ensure window.broadsheets is an array
        function ensureBroadsheetsArray() {
            console.log("Ensuring window.broadsheets is an array...");
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

        // Initialize original values for inputs
        function initializeScoreInputs() {
            console.log("Initializing score inputs...");
            const $inputs = $('.score-input');
            if (!$inputs.length) {
                console.warn("No .score-input elements found in DOM.");
            }
            $inputs.each(function() {
                const $this = $(this);
                $this.data('original-value', $this.val());
                $this.data('dirty', false);
                console.log(`Initialized input ID: ${$this.data('id')}, Value: ${$this.val()}`);
            });
            console.log("Initialized", $inputs.length, "score inputs for mock exams");
        }

        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        // Update row display after AJAX response
        function updateRowDisplay(id, data) {
            console.log(`=== DEBUG: updateRowDisplay called for ID ${id} (mock exams) ===`);
            console.log("Data received:", data);

            const $row = $(`input[data-id="${id}"]`).closest('tr');
            if (!$row.length) {
                console.warn("Row not found for ID:", id);
                return;
            }

            const $examInput = $row.find('input[data-field="exam"]');
            if ($examInput.length && data.exam !== undefined) {
                $examInput.val(data.exam !== null ? data.exam : '');
                $examInput.data('original-value', $examInput.val());
                $examInput.data('dirty', false);
                console.log(`Updated exam input to:`, $examInput.val());
            }

            const $totalDisplay = $row.find('.total-display span');
            if ($totalDisplay.length && data.total !== undefined) {
                const totalValue = parseFloat(data.total).toFixed(1);
                $totalDisplay.text(totalValue);
                $totalDisplay.toggleClass('text-danger', data.total < 40 && data.total !== 0);
                console.log(`Updated total display to:`, totalValue);
            }

            const $gradeDisplay = $row.find('.grade-display span');
            if ($gradeDisplay.length && data.grade !== undefined) {
                $gradeDisplay.text(data.grade || '-');
                console.log(`Updated grade display to:`, data.grade || '-');
            }

            const $remarkDisplay = $row.find('.remark-display span');
            if ($remarkDisplay.length && data.remark !== undefined) {
                $remarkDisplay.text(data.remark || '-');
                console.log(`Updated remark display to:`, data.remark || '-');
            }

            const $positionDisplay = $row.find('.position-display span');
            if ($positionDisplay.length && data.subjectpositionclass !== undefined) {
                $positionDisplay.text(data.subjectpositionclass || '-');
                console.log(`Updated position display to:`, data.subjectpositionclass || '-');
            }
        }

        // Search functionality
        const triggerSearch = debounce(function() {
            console.log("Triggering search...");
            const searchTerm = $('#searchInput').val().toLowerCase();
            let visibleRows = 0;
            $('#scoresheetTableBody tr').each(function() {
                const $row = $(this);
                if ($row.is('#noDataRow')) return;
                const admissionNo = $row.find('.admissionno').data('admissionno')?.toString().toLowerCase() || '';
                const name = $row.find('.name').data('name')?.toLowerCase() || '';
                const isVisible = admissionNo.includes(searchTerm) || name.includes(searchTerm);
                $row.toggle(isVisible);
                if (isVisible) visibleRows++;
            });
            $('#noDataAlert').toggle(visibleRows === 0);
            console.log("Visible rows:", visibleRows, "Search term:", searchTerm);
        }, 300);

        $('#searchInput').on('keyup', triggerSearch);

        // Clear search
        $('#clearSearch').on('click', function() {
            console.log("Clearing search...");
            $('#searchInput').val('').trigger('keyup');
        });

        // Check all checkboxes
        $('#checkAll').on('change', function() {
            console.log("Check all toggled:", $(this).is(':checked'));
            $('.score-checkbox').prop('checked', $(this).is(':checked'));
        });

        // Update checkAll state when individual checkboxes change
        $(document).on('change', '.score-checkbox', function() {
            console.log("Score checkbox changed");
            $('#checkAll').prop('checked', $('.score-checkbox:checked').length === $('.score-checkbox').length);
        });

        // Bulk actions initialization
        function initializeBulkActions() {
            console.log("Initializing bulk actions...");
            if (!$('#selectAllScores').length) {
                console.warn("Select All button not found.");
            }
            $('#selectAllScores').on('click', function() {
                console.log("Select All clicked");
                $('.score-checkbox').prop('checked', true);
                $('#checkAll').prop('checked', true);
            });

            if (!$('#clearAllScores').length) {
                console.warn("Clear All button not found.");
            }
            $('#clearAllScores').on('click', function() {
                console.log("Clear All clicked");
                if (confirm('Are you sure you want to clear all scores? This will reset all exam inputs to empty.')) {
                    $('.score-input').each(function() {
                        const $input = $(this);
                        $input.val('');
                        $input.data('dirty', true);
                        updateRowDisplay($input.data('id'), { exam: null, total: 0, grade: '-', remark: '-', subjectpositionclass: '-' });
                    });
                    toastr.success('All scores cleared.');
                    console.log("Cleared all scores");
                }
            });

            if (!$('#bulkUpdateScores').length) {
                console.warn("Bulk Update button not found.");
            }
            $('#bulkUpdateScores').on('click', function(e) {
                e.preventDefault();
                console.log("Bulk Update clicked");
                bulkSaveAllScores();
            });

            // Ctrl+S shortcut
            $(document).on('keydown', function(e) {
                if (e.ctrlKey && e.key === 's') {
                    e.preventDefault();
                    console.log("Ctrl+S pressed - triggering bulk save");
                    bulkSaveAllScores();
                }
            });
        }

        // Bulk save all scores
        function bulkSaveAllScores() {
            console.log("=== DEBUG: Starting bulkSaveAllScores (mock exams) ===");
            ensureBroadsheetsArray();

            const $scoreInputs = $('.score-input[data-dirty="true"]');
            const $progressContainer = $('#progressContainer');
            const $progressBar = $progressContainer.find('.progress-bar');
            const $bulkUpdateBtn = $('#bulkUpdateScores');
            const originalBtnContent = $bulkUpdateBtn.html();

            if (!$scoreInputs.length) {
                toastr.info('No scores have been modified.');
                console.warn("No modified score inputs found");
                return;
            }

            // Verify session variables
            const sessionVars = {
                term_id: window.term_id,
                session_id: window.session_id,
                subjectclass_id: window.subjectclass_id,
                schoolclass_id: window.schoolclass_id,
                staff_id: window.staff_id
            };
            console.log("Session values:", sessionVars);
            for (const [key, value] of Object.entries(sessionVars)) {
                if (!value) {
                    console.error(`Missing session variable: ${key}`);
                    toastr.error(`Please select a ${key.replace('_id', '')} before saving.`);
                    return;
                }
            }

            const scores = [];
            const scoreData = {};
            const invalidInputs = [];

            $scoreInputs.each(function() {
                const $input = $(this);
                const id = $input.data('id');
                const field = $input.data('field');
                const value = $input.val().trim();

                console.log(`Input - ID: ${id}, Field: ${field}, Value: ${value}`);

                $input.removeClass('is-invalid is-valid');

                if (!id || !field) {
                    console.error("Missing input attributes", { id, field, value });
                    $input.addClass('is-invalid');
                    invalidInputs.push({ input: $input, error: 'Missing required attributes' });
                    return;
                }

                const numValue = value === '' ? null : parseFloat(value);
                if (numValue !== null && (isNaN(numValue) || numValue < 0 || numValue > 100)) {
                    console.error("Invalid score", { id, field, value: numValue });
                    $input.addClass('is-invalid');
                    invalidInputs.push({ input: $input, error: 'Score must be between 0-100' });
                    return;
                }

                if (value !== '') {
                    $input.addClass('is-valid');
                }

                if (!scoreData[id]) scoreData[id] = { id: parseInt(id) };
                scoreData[id][field] = numValue;
            });

            if (invalidInputs.length > 0) {
                console.error("Validation failed:", invalidInputs);
                toastr.error('Please correct invalid scores (must be between 0 and 100).');
                return;
            }

            Object.values(scoreData).forEach(scoreEntry => {
                console.log(`Processing score ID ${scoreEntry.id}:`, scoreEntry);
                scores.push({
                    id: scoreEntry.id,
                    exam: scoreEntry.exam
                });
            });

            if (!scores.length) {
                console.warn("No valid scores to save");
                toastr.info('No valid scores to save.');
                return;
            }

            console.log("=== DEBUG: Final scores to be sent ===");
            console.log(JSON.stringify(scores, null, 2));

            $progressContainer.show();
            $progressBar.css('width', '20%');
            $bulkUpdateBtn.prop('disabled', true).html('<i class="ri-loader-4-line sync-icon"></i> Saving...');

            $.ajax({
                url: '{{ route("subjectscoresheet-mock.bulk-update") }}',
                type: 'POST',
                data: {
                    scores: scores,
                    term_id: window.term_id,
                    session_id: window.session_id,
                    subjectclass_id: window.subjectclass_id,
                    schoolclass_id: window.schoolclass_id,
                    staff_id: window.staff_id
                },
                success: function(response) {
                    console.log("=== DEBUG: Server response ===");
                    console.log("Response data:", response);

                    $progressBar.css('width', '100%');

                    $scoreInputs.removeClass('is-invalid is-valid');

                    if (response.success && response.data && response.data.broadsheets) {
                        const updatedCount = response.data.broadsheets.length;
                        response.data.broadsheets.forEach(broadsheet => {
                            console.log(`Server returned broadsheet ${broadsheet.id}:`, broadsheet);
                            const index = window.broadsheets.findIndex(b => b.id == broadsheet.id);
                            if (index !== -1) {
                                window.broadsheets[index] = { ...window.broadsheets[index], ...broadsheet };
                                console.log(`Updated broadsheet at index ${index}`);
                            } else {
                                window.broadsheets.push(broadsheet);
                                console.log(`Added new broadsheet`);
                            }
                            updateRowDisplay(broadsheet.id, broadsheet);
                        });

                        toastr.success(`Successfully updated ${updatedCount} score${updatedCount !== 1 ? 's' : ''}.`);
                        populateResultsModal();
                        $('#scoreCount').text(window.broadsheets.length);
                        console.log("=== DEBUG: Final window.broadsheets state ===");
                        console.log(window.broadsheets);
                    } else {
                        toastr.error(response.message || 'Failed to update scores.');
                    }
                },
                error: function(xhr) {
                    console.error("=== DEBUG: Server error ===");
                    console.error("Error details:", {
                        status: xhr.status,
                        data: xhr.responseJSON,
                        message: xhr.statusText
                    });
                    toastr.error('Failed to save scores: ' + (xhr.responseJSON?.message || 'Server error'));
                },
                complete: function() {
                    setTimeout(() => {
                        $progressContainer.hide();
                        $progressBar.css('width', '0%');
                        $bulkUpdateBtn.prop('disabled', false).html(originalBtnContent);
                    }, 1000);
                }
            });
        }

        // Populate scores modal
        function populateResultsModal() {
            console.log("=== DEBUG: Populating scores modal (mock exams) ===");
            ensureBroadsheetsArray();
            const $tbody = $('#scoresBody');
            $tbody.empty().html('<tr><td colspan="8" class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');

            if (!window.broadsheets || window.broadsheets.length === 0) {
                $tbody.html('<tr><td colspan="8" class="text-center">No mock scores available.</td></tr>');
                console.log("No broadsheets available for modal");
                return;
            }

            window.broadsheets.forEach(function(broadsheet, index) {
                const name = broadsheet.name || `${broadsheet.fname || ''} ${broadsheet.lname || ''}`.trim();
                const exam = parseFloat(broadsheet.exam) || 0;
                const total = parseFloat(broadsheet.total) || exam;
                const examClass = exam < 40 && exam !== 0 ? 'text-danger' : '';
                const totalClass = total < 40 && total !== 0 ? 'text-danger' : '';

                console.log(`Row ${index + 1} - ID: ${broadsheet.id}, Total: ${total.toFixed(1)}, Position: ${broadsheet.subjectpositionclass || '-'}`);

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
        }

        // Delete selected scores
        window.deleteSelectedScores = function() {
            console.log("=== DEBUG: deleteSelectedScores triggered (mock exams) ===");
            const selectedIds = $('.score-checkbox:checked').map(function() {
                return $(this).data('id');
            }).get();

            if (selectedIds.length === 0) {
                toastr.warning('Please select at least one score to delete.');
                console.log("No scores selected for deletion");
                return;
            }

            if (!confirm('Are you sure you want to delete the selected scores?')) {
                console.log("Deletion cancelled by user");
                return;
            }

            $.ajax({
                url: '{{ route("subjectscoresheet-mock.destroy") }}',
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    ids: selectedIds
                },
                success: function(response) {
                    console.log("Delete response:", response);
                    if (response.success) {
                        toastr.success(response.message);
                        selectedIds.forEach(function(id) {
                            $(`tr:has(.score-checkbox[data-id="${id}"])`).remove();
                            window.broadsheets = window.broadsheets.filter(b => b.id != id);
                            console.log(`Removed score ID ${id} from table and window.broadsheets`);
                        });
                        $('#scoreCount').text(window.broadsheets.length);
                        $('#noDataAlert').toggle(window.broadsheets.length === 0);
                        $('#noDataRow').toggle(window.broadsheets.length === 0);
                        $('#checkAll').prop('checked', false);
                        populateResultsModal();
                    } else {
                        toastr.error(response.message || 'Failed to delete scores.');
                    }
                },
                error: function(xhr) {
                    console.error("Delete error:", xhr.responseText);
                    toastr.error('Failed to delete scores: ' + (xhr.responseJSON?.message || 'Server error'));
                }
            });
        };

        // Initialize import form
        function initializeImportForm() {
            console.log("Initializing import form...");
            const $form = $('#importForm');
            if (!$form.length) {
                console.warn("Import form not found.");
                return;
            }
            $form.on('submit', function(e) {
                e.preventDefault();
                console.log("Import form submitted");
                const $submitBtn = $form.find('#importSubmit');
                const originalBtnContent = $submitBtn.find('.indicator-label').text();
                $submitBtn.find('.indicator-label').hide();
                $submitBtn.find('.indicator-progress').show();
                $submitBtn.prop('disabled', true);

                const formData = new FormData(this);
                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log("Import response:", response);
                        if (response.success && response.broadsheets) {
                            toastr.success(response.message);
                            response.broadsheets.forEach(score => {
                                const index = window.broadsheets.findIndex(b => b.id == score.id);
                                if (index !== -1) {
                                    window.broadsheets[index] = score;
                                } else {
                                    window.broadsheets.push(score);
                                }
                                updateRowDisplay(score.id, score);
                            });
                            $('#scoreCount').text(window.broadsheets.length);
                            $('#noDataAlert').toggle(window.broadsheets.length === 0);
                            $('#noDataRow').toggle(window.broadsheets.length === 0);
                            populateResultsModal();
                        } else {
                            let message = response.message || 'Import failed.';
                            if (response.errors && response.errors.length) {
                                message += '<ul style="text-align: left; max-height: 200px; overflow-y: auto;">';
                                response.errors.forEach(err => {
                                    message += `<li>Row ${err.row}: ${err.attribute === '1' ? 'Admission No.' : err.attribute} - ${err.errors.join(', ')}</li>`;
                                });
                                message += '</ul>';
                            }
                            toastr.error(message, 'Import Failed', { timeOut: 0 });
                        }
                    },
                    error: function(xhr) {
                        console.error("Import error:", xhr.responseText);
                        let message = xhr.responseJSON?.message || 'Import failed.';
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            message += '<ul style="text-align: left; max-height: 200px; overflow-y: auto;">';
                            xhr.responseJSON.errors.forEach(err => {
                                message += `<li>Row ${err.row}: ${err.attribute === '1' ? 'Admission No.' : err.attribute} - ${err.errors.join(', ')}</li>`;
                            });
                            message += '</ul>';
                        }
                        toastr.error(message, 'Import Failed', { timeOut: 0 });
                    },
                    complete: function() {
                        $submitBtn.find('.indicator-label').show().text(originalBtnContent);
                        $submitBtn.find('.indicator-progress').hide();
                        $submitBtn.prop('disabled', false);
                    }
                });
            });
        }

        // Debug current state
        window.debugCurrentState = function() {
            console.log("=== DEBUG: Current State Check (mock exams) ===");
            console.log("window.broadsheets:", window.broadsheets);
            console.log("Score inputs found:", $('.score-input').length);
            
            $('.score-input').each(function() {
                console.log(`Input - ID: ${$(this).data('id')}, Field: ${$(this).data('field')}, Value: ${$(this).val()}, Dirty: ${$(this).data('dirty')}`);
            });
            
            $('.total-display span').each(function(index) {
                const $row = $(this).closest('tr');
                const id = $row.find('.score-input').data('id');
                const position = $row.find('.position-display span').text();
                console.log(`Row ${index + 1} - ID: ${id}, Total: ${$(this).text()}, Position: ${position}`);
            });
        };

        // Initialize
        console.log("Starting initialization...");
        try {
            ensureBroadsheetsArray();
            initializeScoreInputs();
            initializeBulkActions();
            initializeImportForm();
            $('#scoresModal').on('show.bs.modal', populateResultsModal);

            // Verify DOM elements
            console.log("DOM checks:");
            console.log("scoresheetTableBody:", $('#scoresheetTableBody').length ? "Found" : "Not found");
            console.log("bulkUpdateScores:", $('#bulkUpdateScores').length ? "Found" : "Not found");
            console.log("progressContainer:", $('#progressContainer').length ? "Found" : "Not found");
            console.log("scoresModal:", $('#scoresModal').length ? "Found" : "Not found");

            console.log("Initialization complete for mock exams");
        } catch (err) {
            console.error("Initialization error:", err);
            toastr.error("Script initialization failed. Check console for details.");
        }

        // Track input changes for dirty state
        $(document).on('input', '.score-input', function() {
            console.log(`Input changed - ID: ${$(this).data('id')}, Value: ${$(this).val()}`);
            $(this).data('dirty', true);
        });
    });
} catch (err) {
    console.error("Critical error loading script:", err);
    alert("Failed to load scoresheet script. Check console for details.");
}
