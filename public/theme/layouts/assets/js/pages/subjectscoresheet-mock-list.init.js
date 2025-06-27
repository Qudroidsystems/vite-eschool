(function () {
    // Log script loading
    console.log("subjectscoresheet-mock.init.js loaded at", new Date().toISOString());

    // Dependency checks
    function checkDependencies() {
        try {
            if (typeof axios === 'undefined') throw new Error("Axios is not loaded");
            if (typeof Swal === 'undefined') throw new Error("SweetAlert2 is not loaded");
            if (typeof bootstrap === 'undefined') throw new Error("Bootstrap is not loaded");
            console.log("All dependencies loaded successfully");
            return true;
        } catch (error) {
            console.error("Dependency check failed:", error.message);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: "error",
                    title: "Dependency Error",
                    text: "Required libraries are missing. Check console for details.",
                    showConfirmButton: true
                });
            } else {
                alert("Dependency Error: " + error.message);
            }
            return false;
        }
    }

    // Set CSRF token for Axios
    function setupAxios() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        if (typeof axios !== 'undefined') {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
        }
        if (!csrfToken) {
            console.warn("CSRF token not found. AJAX requests may fail.");
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: "warning",
                    title: "CSRF Token Missing",
                    text: "CSRF token not found. AJAX requests may fail.",
                    timer: 3000
                });
            }
        }
    }

    // Ensure window.broadsheets is a flat array
    function ensureBroadsheetsArray() {
        if (typeof window.broadsheets === 'undefined') {
            window.broadsheets = [];
        } else if (!Array.isArray(window.broadsheets)) {
            window.broadsheets = [window.broadsheets];
        } else if (window.broadsheets.length === 1 && typeof window.broadsheets[0] === 'object' && !Array.isArray(window.broadsheets[0])) {
            const nestedObject = window.broadsheets[0];
            window.broadsheets = Object.values(nestedObject).filter(item => item && typeof item === 'object' && item.id);
        }
    }

    // Calculate grade (same as controller)
    function calculateGrade(score) {
        if (isNaN(score) || score === null || score === undefined) return '-';
        const numScore = parseFloat(score);
        if (numScore >= 70) return 'A';
        else if (numScore >= 60) return 'B';
        else if (numScore >= 50) return 'C';
        else if (numScore >= 40) return 'D';
        return 'F';
    }

    // Get ordinal suffix for position
    function getOrdinalSuffix(position) {
        const num = parseInt(position);
        if (isNaN(num)) return position;
        if (num % 100 >= 11 && num % 100 <= 13) {
            return num + 'th';
        }
        switch (num % 10) {
            case 1: return num + 'st';
            case 2: return num + 'nd';
            case 3: return num + 'rd';
            default: return num + 'th';
        }
    }

    // Update row totals and positions
    function updateRowTotal(row) {
        const scoreInput = row.querySelector('.score-input[data-field="exam"]');
        const id = scoreInput?.dataset.id;
        if (!id) return;
        if (!window.broadsheets || !Array.isArray(window.broadsheets) || window.broadsheets.length === 0) return;

        const exam = parseFloat(scoreInput.value) || 0;
        const total = exam;
        const grade = calculateGrade(total);

        // Update total display
        const totalDisplay = row.querySelector('.total-display span');
        if (totalDisplay) {
            totalDisplay.textContent = total.toFixed(1);
            totalDisplay.classList.toggle('text-danger', total < 40 && total !== 0);
            totalDisplay.classList.add('bg-warning');
            setTimeout(() => totalDisplay.classList.remove('bg-warning'), 500);
        }

        // Update grade display
        const gradeDisplay = row.querySelector('.grade-display span');
        if (gradeDisplay) {
            gradeDisplay.textContent = grade;
            gradeDisplay.classList.add('bg-warning');
            setTimeout(() => gradeDisplay.classList.remove('bg-warning'), 500);
        }

        // Update broadsheets array
        const broadsheetIndex = window.broadsheets.findIndex(b => String(b.id) === String(id));
        if (broadsheetIndex !== -1) {
            window.broadsheets[broadsheetIndex] = {
                ...window.broadsheets[broadsheetIndex],
                exam,
                total,
                grade
            };
        }

        // Position logic (see forceUpdatePositions)
        forceUpdatePositions();
    }

    // Initialize score input fields
    function initializeScoreInputs() {
        const scoreInputs = document.querySelectorAll('.score-input');
        if (scoreInputs.length === 0) return;
        scoreInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                input.dataset.dirty = 'true';
                const row = e.target.closest('tr');
                if (row) updateRowTotal(row);
            });
            input.addEventListener('blur', (e) => {
                const value = e.target.value.trim();
                if (value === '') {
                    e.target.classList.remove('is-invalid', 'is-valid');
                    return;
                }
                const numValue = parseFloat(value);
                if (isNaN(numValue) || numValue < 0 || numValue > 100) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Invalid Score',
                            text: `Please enter a valid score between 0 and 100 for ${e.target.dataset.field.toUpperCase()}`,
                            timer: 2000
                        });
                    }
                    e.target.classList.add('is-invalid');
                    e.target.classList.remove('is-valid');
                    e.target.focus();
                } else {
                    e.target.classList.remove('is-invalid');
                    e.target.classList.add('is-valid');
                }
            });
        });
    }

    // Bulk save all scores (includes zeroes)
    function bulkSaveAllScores() {
        ensureBroadsheetsArray();
        const scoreInputs = document.querySelectorAll('.score-input');
        const progressContainer = document.getElementById('progressContainer');
        const progressBar = progressContainer?.querySelector('.progress-bar');
        const bulkUpdateBtn = document.getElementById('bulkUpdateScores');
        const originalBtnContent = bulkUpdateBtn?.innerHTML;

        if (!scoreInputs.length) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'No Scores',
                    text: 'No score inputs found.',
                    timer: 2000
                });
            }
            return;
        }

        // Validate session variables
        const sessionVars = {
            term_id: window.term_id,
            session_id: window.session_id,
            subjectclass_id: window.subjectclass_id,
            schoolclass_id: window.schoolclass_id,
            staff_id: window.staff_id
        };
        for (const [key, value] of Object.entries(sessionVars)) {
            if (!value) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: `Please select a ${key.replace('_id', '')} before saving.`,
                        showConfirmButton: true
                    });
                }
                return;
            }
        }

        const scores = [];
        const invalidInputs = [];
        scoreInputs.forEach(input => {
            const id = input.dataset.id;
            const field = input.dataset.field;
            let value = input.value.trim();

            if (input.disabled) return;

            input.classList.remove('is-invalid', 'is-valid');

            if (!id || !field) {
                input.classList.add('is-invalid');
                invalidInputs.push({ input, error: 'Missing required attributes' });
                return;
            }

            // Treat empty as zero (force save zero)
            let numValue = value === '' ? 0 : parseFloat(value);
            if (isNaN(numValue) || numValue < 0 || numValue > 100) {
                input.classList.add('is-invalid');
                invalidInputs.push({ input, error: `Score must be between 0-100 for ${field.toUpperCase()}` });
                return;
            }
            input.classList.add('is-valid');

            let found = scores.find(obj => obj.id == id);
            if (!found) {
                found = { id: parseInt(id), exam: 0 };
                scores.push(found);
            }
            found[field] = numValue;
            found.total = numValue; // Only exam field in this context
            found.grade = calculateGrade(numValue);
        });

        if (invalidInputs.length > 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Failed',
                    html: `Some scores are invalid:<ul>${invalidInputs.map(e => `<li>${e.error}</li>`).join('')}</ul>`,
                    showConfirmButton: true
                });
            }
            return;
        }

        if (!scores.length) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'No Scores',
                    text: 'No valid scores to save.',
                    timer: 2000
                });
            }
            return;
        }

        // Show progress
        if (progressContainer) progressContainer.style.display = 'block';
        if (progressBar) progressBar.style.width = '20%';
        if (bulkUpdateBtn) {
            bulkUpdateBtn.disabled = true;
            bulkUpdateBtn.innerHTML = '<i class="ri-loader-4-line sync-icon"></i> Saving...';
        }

        if (typeof axios !== 'undefined') {
            axios.post(window.routes?.bulkUpdate || '/subjectscoresheet-mock/bulk-update', {
                scores,
                ...sessionVars
            }, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                },
                timeout: 30000
            })
            .then(response => {
                if (progressBar) progressBar.style.width = '100%';
                if (response.data.success && response.data.data?.broadsheets) {
                    window.broadsheets = response.data.data.broadsheets;
                    ensureBroadsheetsArray();

                    // Update DOM with server response
                    window.broadsheets.forEach(broadsheet => {
                        const row = document.querySelector(`input[data-id="${broadsheet.id}"]`)?.closest('tr');
                        if (row) {
                            const examInput = row.querySelector('input[data-field="exam"]');
                            if (examInput) {
                                examInput.value = broadsheet.exam !== null ? broadsheet.exam : '';
                                examInput.dataset.dirty = 'false';
                            }
                            const totalDisplay = row.querySelector('.total-display span');
                            if (totalDisplay) {
                                totalDisplay.textContent = parseFloat(broadsheet.total || 0).toFixed(1);
                                totalDisplay.classList.toggle('text-danger', broadsheet.total < 40 && broadsheet.total !== 0);
                            }
                            const gradeDisplay = row.querySelector('.grade-display span');
                            if (gradeDisplay) {
                                gradeDisplay.textContent = broadsheet.grade || '-';
                            }
                            const remarkDisplay = row.querySelector('.remark-display span');
                            if (remarkDisplay) {
                                remarkDisplay.textContent = broadsheet.remark || '-';
                            }
                        }
                    });

                    // Recompute and update positions immediately after save!
                    forceUpdatePositions();

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved!',
                            text: `Successfully updated ${scores.length} score${scores.length !== 1 ? 's' : ''} with positions.`,
                            timer: 2000
                        });
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Save Failed',
                            text: 'Server did not return updated scores.',
                            showConfirmButton: true
                        });
                    }
                }
            })
            .catch(error => {
                let errorMessage = 'Failed to save scores. Check console for details.';
                if (error.response) {
                    errorMessage = error.response.data.message || errorMessage;
                    if (error.response.status === 422) {
                        const errors = error.response.data.errors || {};
                        errorMessage += '<ul>' + Object.values(errors).flat().map(err => `<li>${err}</li>`).join('') + '</ul>';
                    } else if (error.response.status === 419) {
                        errorMessage = 'Session expired. Please refresh the page.';
                    }
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Save Failed',
                        html: errorMessage,
                        showConfirmButton: true
                    });
                }
            })
            .finally(() => {
                if (progressContainer) {
                    setTimeout(() => {
                        progressContainer.style.display = 'none';
                        if (progressBar) progressBar.style.width = '0%';
                    }, 1000);
                }
                if (bulkUpdateBtn) {
                    bulkUpdateBtn.disabled = false;
                    bulkUpdateBtn.innerHTML = originalBtnContent || '<i class="ri-save-line me-1"></i> Save All Scores';
                }
            });
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Axios library is not loaded.',
                    showConfirmButton: true
                });
            }
        }
    }

    // Delete selected scores
    window.deleteSelectedScores = function() {
        const selectedCheckboxes = document.querySelectorAll('.score-checkbox:checked');
        if (!selectedCheckboxes.length) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'No Selection',
                    text: 'Please select at least one score to delete.',
                    timer: 2000
                });
            }
            return;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Delete Selected Scores?',
                text: 'This will clear the selected mock scores. Are you sure?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Delete!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const selectedIds = Array.from(selectedCheckboxes).map(checkbox => checkbox.dataset.id);
                    if (typeof axios !== 'undefined') {
                        axios.post(window.routes?.destroy || '/subjectscoresheet-mock/destroy', {
                            ids: selectedIds
                        }, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => {
                            if (response.data.success) {
                                selectedIds.forEach(id => {
                                    const row = document.querySelector(`tr:has(.score-checkbox[data-id="${id}"])`);
                                    if (row) row.remove();
                                    window.broadsheets = window.broadsheets.filter(b => String(b.id) !== String(id));
                                });
                                const scoreCount = document.getElementById('scoreCount');
                                if (scoreCount) scoreCount.textContent = window.broadsheets.length;
                                const noDataAlert = document.getElementById('noDataAlert');
                                if (noDataAlert) noDataAlert.style.display = window.broadsheets.length === 0 ? 'block' : 'none';
                                const noDataRow = document.getElementById('noDataRow');
                                if (noDataRow) noDataRow.style.display = window.broadsheets.length === 0 ? '' : 'none';
                                const checkAll = document.getElementById('checkAll');
                                if (checkAll) checkAll.checked = false;
                                forceUpdatePositions();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'Selected scores have been cleared.',
                                    timer: 2000
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Delete Failed',
                                    text: response.data.message || 'Failed to delete scores.',
                                    showConfirmButton: true
                                });
                            }
                        })
                        .catch(error => {
                            let message = 'Failed to delete scores: ';
                            if (error.response) {
                                message += error.response.data.message || 'Server error';
                                if (error.response.status === 422) {
                                    const errors = error.response.data.errors || {};
                                    message += '<ul>' + Object.values(errors).flat().map(err => `<li>${err}</li>`).join('') + '</ul>';
                                } else if (error.response.status === 419) {
                                    message = 'Session expired. Please refresh the page.';
                                }
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Delete Failed',
                                html: message,
                                showConfirmButton: true
                            });
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Axios library is not loaded.',
                            showConfirmButton: true
                        });
                    }
                }
            });
        }
    };

    // Import scores from Excel
    function initializeImportForm() {
        const importForm = document.getElementById('importForm');
        const importSubmit = document.getElementById('importSubmit');
        if (importForm && importSubmit) {
            importForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const formData = new FormData(importForm);
                const indicatorLabel = importSubmit.querySelector('.indicator-label');
                const indicatorProgress = importSubmit.querySelector('.indicator-progress');
                if (indicatorLabel) indicatorLabel.style.display = 'none';
                if (indicatorProgress) indicatorProgress.style.display = 'inline';
                importSubmit.disabled = true;
                if (typeof axios !== 'undefined') {
                    axios.post(window.routes?.import || '/subjectscoresheet-mock/import', formData, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (response.data.success) {
                            window.broadsheets = response.data.data?.broadsheets || window.broadsheets;
                            ensureBroadsheetsArray();
                            forceUpdatePositions();
                            const scoreCount = document.getElementById('scoreCount');
                            if (scoreCount) scoreCount.textContent = window.broadsheets.length;
                            const noDataAlert = document.getElementById('noDataAlert');
                            if (noDataAlert) noDataAlert.style.display = window.broadsheets.length === 0 ? 'block' : 'none';
                            const noDataRow = document.getElementById('noDataRow');
                            if (noDataRow) noDataRow.style.display = window.broadsheets.length === 0 ? '' : 'none';
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Imported!',
                                    text: 'Scores imported successfully.',
                                    timer: 2000
                                });
                            }
                            const importModal = document.getElementById('importModal');
                            if (importModal && typeof bootstrap !== 'undefined') {
                                const modalInstance = bootstrap.Modal.getInstance(importModal);
                                if (modalInstance) modalInstance.hide();
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Import Failed',
                                    text: response.data.message || 'Failed to import scores.',
                                    showConfirmButton: true
                                });
                            }
                        }
                    })
                    .catch(error => {
                        let message = 'Failed to import scores: ';
                        if (error.response) {
                            message += error.response.data.message || 'Server error';
                            if (error.response.status === 422) {
                                const errors = error.response.data.errors || {};
                                message += '<ul>' + Object.values(errors).flat().map(err => `<li>${err}</li>`).join('') + '</ul>';
                            }
                        }
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Import Failed',
                                html: message,
                                showConfirmButton: true
                            });
                        }
                    })
                    .finally(() => {
                        if (indicatorLabel) indicatorLabel.style.display = 'inline';
                        if (indicatorProgress) indicatorProgress.style.display = 'none';
                        importSubmit.disabled = false;
                    });
                }
            });
        }
    }

    // Force update positions after bulk operations (with ties)
 function forceUpdatePositions() {
    if (!window.broadsheets || !Array.isArray(window.broadsheets)) return;

    // Get all totals
    const totals = window.broadsheets.map(b => parseFloat(b.total) || 0);
    const allZero = totals.length > 0 && totals.every(total => total === 0);

    if (allZero) {
        // Set all positions to "0th"
        window.broadsheets.forEach(broadsheet => {
            broadsheet.subjectpositionclass = "0th"; // Update array!
            const row = document.querySelector(`tr:has(input[data-id="${broadsheet.id}"])`);
            if (row) {
                const positionDisplay = row.querySelector('.position-display span');
                if (positionDisplay) {
                    positionDisplay.textContent = "0th";
                    positionDisplay.classList.remove('bg-warning');
                    positionDisplay.classList.add('bg-info');
                }
            }
        });
    } else {
        // Normal position calculation with ties
        const sortedBroadsheets = window.broadsheets
            .map(b => ({
                ...b,
                total: parseFloat(b.total) || 0
            }))
            .sort((a, b) => {
                if (b.total !== a.total) {
                    return b.total - a.total;
                }
                return a.id - b.id;
            });

        let lastTotal = null;
        let lastPosition = 1;
        sortedBroadsheets.forEach((broadsheet, index) => {
            let position;
            if (broadsheet.total === lastTotal) {
                position = lastPosition;
            } else {
                position = index + 1;
                lastPosition = position;
                lastTotal = broadsheet.total;
            }

            // Update array
            const orig = window.broadsheets.find(b => String(b.id) === String(broadsheet.id));
            if (orig) orig.subjectpositionclass = getOrdinalSuffix(position);

            // Update table
            const row = document.querySelector(`tr:has(input[data-id="${broadsheet.id}"])`);
            if (row) {
                const positionDisplay = row.querySelector('.position-display span');
                if (positionDisplay) {
                    positionDisplay.textContent = getOrdinalSuffix(position);
                    positionDisplay.classList.remove('bg-warning');
                    positionDisplay.classList.add('bg-info');
                }
            }
        });
    }
}
    // Initialize check all functionality
    function initializeCheckAll() {
        const checkAll = document.getElementById('checkAll');
        if (checkAll) {
            checkAll.addEventListener('change', (e) => {
                const checkboxes = document.querySelectorAll('.score-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = e.target.checked;
                });
            });
        }
    }

    // Search functionality (search by student name or admission number)
    function initializeSearch() {
        const searchInput = document.getElementById('searchInput');
        if (!searchInput) return;
        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase().trim();
            const rows = document.querySelectorAll('tr:has(.score-input)');
            rows.forEach(row => {
                const nameCell = row.querySelector('.student-name');
                const admissionNoCell = row.querySelector('.admission-no');
                const name = (nameCell?.textContent || '').toLowerCase();
                const admissionNo = (admissionNoCell?.textContent || '').toLowerCase();
                const matches = name.includes(searchTerm) || admissionNo.includes(searchTerm);
                row.style.display = matches ? '' : 'none';
            });
        });
    }

    // Initialize the module
    function init() {
        if (!checkDependencies()) return;
        setupAxios();
        ensureBroadsheetsArray();
        initializeScoreInputs();
        initializeImportForm();
        initializeCheckAll();
        initializeSearch();

        // Attach bulk update handler
        const bulkUpdateBtn = document.getElementById('bulkUpdateScores');
        if (bulkUpdateBtn) {
            bulkUpdateBtn.addEventListener('click', bulkSaveAllScores);
        }

        // Add Select All button functionality
        const selectAllBtn = document.getElementById('selectAllScores');
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', () => {
                const checkboxes = document.querySelectorAll('.score-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });
                const checkAll = document.getElementById('checkAll');
                if (checkAll) checkAll.checked = true;
            });
        }

        // Add Clear All button functionality
        const clearAllBtn = document.getElementById('clearAllScores');
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', () => {
                // Clear all score inputs
                const inputs = document.querySelectorAll('.score-input');
                inputs.forEach(input => {
                    input.value = '';
                    input.classList.remove('is-valid', 'is-invalid');
                    const row = input.closest('tr');
                    if (row) {
                        updateRowTotal(row);
                    }
                });
                // Clear checkboxes as well
                const checkboxes = document.querySelectorAll('.score-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                // Uncheck "check all" checkbox
                const checkAll = document.getElementById('checkAll');
                if (checkAll) checkAll.checked = false;
            });
        }

        // Update initial row totals and positions
        document.querySelectorAll('tr:has(.score-input)').forEach(row => {
            updateRowTotal(row);
        });
    }

    // Export module functionality
    window.SubjectScoresheetMock = {
        init,
        bulkSaveAllScores,
        deleteSelectedScores,
        updateRowTotal,
        forceUpdatePositions
    };

    // Auto-initialize if DOM is ready
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        init();
    } else {
        document.addEventListener('DOMContentLoaded', init);
    }
})();

document.addEventListener('DOMContentLoaded', function() {
    // Populate the Scores Modal when opened
    const scoresModal = document.getElementById('scoresModal');
    if (scoresModal) {
        scoresModal.addEventListener('show.bs.modal', function () {
            const tbody = document.getElementById('scoresBody');
            tbody.innerHTML = ''; // Clear any old rows

            if (window.broadsheets && Array.isArray(window.broadsheets) && window.broadsheets.length > 0) {
                window.broadsheets.forEach(function(bs, i) {
                    tbody.innerHTML += `
                        <tr>
                            <td>${i+1}</td>
                            <td>${bs.admissionno ?? '-'}</td>
                            <td>${(bs.fname ?? '') + ' ' + (bs.lname ?? '')}</td>
                            <td>${bs.exam ?? ''}</td>
                            <td>${bs.total !== undefined && bs.total !== null ? Number(bs.total).toFixed(1) : '0.0'}</td>
                            <td>${bs.grade ?? '-'}</td>
                            <td>${bs.subjectpositionclass ?? '-'}</td>
                            <td>${bs.remark ?? '-'}</td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center">No scores found.</td></tr>`;
            }
        });
    }
});