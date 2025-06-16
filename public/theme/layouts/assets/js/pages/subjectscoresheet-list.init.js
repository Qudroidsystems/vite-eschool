console.log("subjectscoresheet.init.js loaded at", new Date().toISOString());

// Dependency checks
try {
    if (typeof axios === 'undefined') throw new Error("Axios is not loaded");
    if (typeof Swal === 'undefined') throw new Error("SweetAlert2 is not loaded");
    if (typeof bootstrap === 'undefined') throw new Error("Bootstrap is not loaded");
    if (typeof List === 'undefined') throw new Error("List.js is not loaded");
    console.log("All dependencies loaded successfully");
} catch (error) {
    console.error("Dependency check failed:", error.message);
    Swal.fire({
        icon: "error",
        title: "Dependency Error",
        text: "Required libraries are missing. Check console for details.",
        showConfirmButton: true
    });
}

// Set CSRF token for Axios
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
if (!csrfToken) {
    console.warn("CSRF token not found. AJAX requests may fail.");
}

// Ensure window.broadsheets is an array
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
                window.broadsheets = [window.broadsheets ? window.broadsheets : []];
                console.log("Wrapped single broadsheet object in array");
            }
        } else {
            window.broadsheets = [];
            console.warn("window.broadsheets was not an array or object, initialized as empty array");
        }
    }
    console.log("window.broadsheets is now an array with", window.broadsheets.length, "items");
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// Initialize List.js
let scoresheetList = null;
function initializeListJs() {
    const options = {
        valueNames: [
            'student_name',
            'admission_no',
            'ca1',
            'ca2',
            'ca3',
            'exam',
            'total',
            'bf',
            'cum',
            'grade',
            'position'
        ],
        listClass: 'scoresheet-list'
    };

    try {
        scoresheetList = new List('scoresheetTable', options);
        console.log("List.js initialized successfully");
    } catch (error) {
        console.error("List.js initialization failed:", error);
    }
}

// Custom search function
function customSearch(searchString) {
    if (!scoresheetList) return;
    if (!searchString) {
        scoresheetList.search();
        return;
    }
    scoresheetList.search(searchString, ['student_name', 'admission_no']);
}

// Trigger search with debounce
const triggerSearch = debounce(() => {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        customSearch(searchInput.value.trim());
    }
}, 300);

// Initialize search input
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    const clearSearch = document.getElementById('clearSearch');
    
    if (searchInput) {
        searchInput.addEventListener('input', triggerSearch);
    }
    
    if (clearSearch) {
        clearSearch.addEventListener('click', () => {
            if (searchInput) {
                searchInput.value = '';
                triggerSearch();
            }
        });
    }
}

// Initialize "Check All" checkbox
function initializeCheckAll() {
    const checkAll = document.getElementById('checkAll');
    if (!checkAll) return;

    checkAll.addEventListener('change', function () {
        const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
}

// Initialize individual checkboxes
function initializeCheckboxes() {
    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', handleCheckboxChange);
    });
}

// Handle checkbox change
function handleCheckboxChange(e) {
    const checkAll = document.getElementById('checkAll');
    if (!checkAll) return;

    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
    const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
    checkAll.checked = allChecked;
}

// Initialize score input fields
function initializeScoreInputs() {
    const scoreInputs = document.querySelectorAll('.score-input');
    console.log("Found", scoreInputs.length, "score inputs");
    
    scoreInputs.forEach(input => {
        input.addEventListener('input', (e) => {
            const row = input.closest('tr');
            if (row) {
                updateRowTotal(row);
            }
        });
        
        input.addEventListener('blur', (e) => {
            const value = parseFloat(e.target.value);
            if (e.target.value && (isNaN(value) || value < 0 || value > 100)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Score',
                    text: 'Please enter a score between 0 and 100',
                    timer: 2000,
                    showConfirmButton: false
                });
                e.target.focus();
            }
        });
    });
}


function calculateGrade(score) {
    if (score >= 70) return 'A';
    else if (score >= 60) return 'B';
    else if (score >= 50) return 'C';
    else if (score >= 40) return 'D';
    return 'F';
}

function updateRowTotal(row) {
    const scoreInputs = row.querySelectorAll('.score-input');
    let ca1 = 0, ca2 = 0, ca3 = 0, examValue = 0;
    
    scoreInputs.forEach(input => {
        const value = parseFloat(input.value) || 0;
        const field = input.dataset.field;
        
        switch(field) {
            case 'ca1': ca1 = value; break;
            case 'ca2': ca2 = value; break;
            case 'ca3': ca3 = value; break;
            case 'exam': examValue = value; break;
        }
    });
    
    const caAverage = (ca1 + ca2 + ca3) / 3;
    const total = (caAverage + examValue) / 2;
    
    const id = row.querySelector('.score-input').dataset.id;
    const broadsheet = window.broadsheets.find(b => b.id == id);
    const bf = broadsheet ? parseFloat(broadsheet.bf) || 0 : 0;
    const cum = window.term_id === 1 ? total : (bf + total) / 2;

    const totalDisplay = row.querySelector('.total-display span');
    if (totalDisplay) totalDisplay.textContent = total.toFixed(1);

    const bfDisplay = row.querySelector('.bf-display span');
    if (bfDisplay) bfDisplay.textContent = bf.toFixed(2);

    const cumDisplay = row.querySelector('.cum-display span');
    if (cumDisplay) cumDisplay.textContent = cum.toFixed(2);

    const grade = calculateGrade(cum); // Grade based on cum
    const gradeDisplay = row.querySelector('.grade-display span');
    if (gradeDisplay) gradeDisplay.textContent = grade;

    const remark = { A: 'Excellent', B: 'Very Good', C: 'Good', D: 'Pass', F: 'Fail' }[grade] || 'Unknown';
    const remarkDisplay = row.querySelector('.remark-display span');
    if (remarkDisplay) remarkDisplay.textContent = remark;
}

// Update all row totals
function updateAllRowTotals() {
    const rows = document.querySelectorAll('#scoresheetTableBody tr');
    rows.forEach(row => {
        if (!row.matches('#noDataRow')) {
            updateRowTotal(row);
        }
    });
}

// Update row display with server data
function updateRowDisplay(id, data) {
    console.log("Updating row for ID:", id, "with data:", data);
    const row = document.querySelector(`input[data-id="${id}"]`)?.closest('tr');
    if (!row) {
        console.warn("Row not found for ID:", id);
        return;
    }

    // Update input fields
    ['ca1', 'ca2', 'ca3', 'exam'].forEach(field => {
        const input = row.querySelector(`input[data-field="${field}"]`);
        if (input && data[field] !== undefined) {
            input.value = data[field] !== null ? data[field] : '';
        }
    });

    const totalDisplay = row.querySelector('.total-display span');
    if (totalDisplay && data.total !== undefined) {
        totalDisplay.textContent = parseFloat(data.total).toFixed(1);
    }

    const bfDisplay = row.querySelector('.bf-display span');
    if (bfDisplay) {
        const bfValue = data.bf !== null && data.bf !== undefined ? parseFloat(data.bf) : 0;
        console.log("Setting bf for ID:", id, "to:", bfValue);
        bfDisplay.textContent = bfValue.toFixed(2);
    }

    const cumDisplay = row.querySelector('.cum-display span');
    if (cumDisplay) {
        const cumValue = data.cum !== null && data.cum !== undefined ? parseFloat(data.cum) : 0;
        cumDisplay.textContent = cumValue.toFixed(2);
    }

    const gradeDisplay = row.querySelector('.grade-display span');
    if (gradeDisplay && data.grade !== undefined) {
        gradeDisplay.textContent = data.grade || '-';
    }

    const positionDisplay = row.querySelector('.position-display span');
    if (positionDisplay && data.subject_position_class !== undefined) {
        positionDisplay.textContent = data.subject_position_class || '-';
    }

    if (scoresheetList) scoresheetList.reIndex();
}

// Initialize bulk actions
function initializeBulkActions() {
    const selectAllScores = document.getElementById('selectAllScores');
    const clearAllScores = document.getElementById('clearAllScores');
    const bulkUpdateScores = document.getElementById('bulkUpdateScores');

    if (bulkUpdateScores) {
        console.log("Bulk update scores button found");
        bulkUpdateScores.addEventListener('click', (e) => {
            e.preventDefault();
            console.log("Bulk update scores button clicked");
            bulkSaveAllScores();
        });
    } else {
        console.error("Bulk update scores button not found");
    }

    if (selectAllScores) {
        selectAllScores.addEventListener('click', () => {
            document.querySelectorAll('.score-checkbox').forEach(checkbox => {
                checkbox.checked = true;
            });
            const checkAll = document.getElementById('checkAll');
            if (checkAll) checkAll.checked = true;
        });
    }

    if (clearAllScores) {
        clearAllScores.addEventListener('click', () => {
            Swal.fire({
                title: 'Clear All Scores?',
                text: 'This will clear all score inputs. Are you sure?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Clear All!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.querySelectorAll('.score-input').forEach(input => {
                        input.value = '';
                    });
                    updateAllRowTotals();
                }
            });
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            console.log("Ctrl+S pressed - triggering bulk save");
            bulkSaveAllScores();
        }
    });
}

// Bulk save all scores
// Improved bulk save with better error handling and progress tracking
function bulkSaveAllScores() {
    console.log("Starting bulkSaveAllScores at", new Date().toISOString());
    
    ensureBroadsheetsArray();
    
    const scoreInputs = document.querySelectorAll('.score-input');
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = progressContainer?.querySelector('.progress-bar');

    if (!scoreInputs.length) {
        console.warn("No score inputs found");
        Swal.fire({
            icon: 'info',
            title: 'No Scores',
            text: 'No scores to save.',
            timer: 2000
        });
        return;
    }

    // Improved validation with visual feedback
    const scores = [];
    const scoreData = {};
    const invalidInputs = [];

    scoreInputs.forEach(input => {
        const id = input.dataset.id;
        const field = input.dataset.field;
        const value = input.value.trim();

        // Clear previous validation state
        input.classList.remove('is-invalid', 'is-valid');

        if (!id || !field) {
            console.error("Missing input attributes", { id, field, value });
            input.classList.add('is-invalid');
            invalidInputs.push({ input, error: 'Missing required attributes' });
            return;
        }

        const numValue = value === '' ? null : parseFloat(value);
        if (numValue !== null && (isNaN(numValue) || numValue < 0 || numValue > 100)) {
            console.error("Invalid score", { id, field, value: numValue });
            input.classList.add('is-invalid');
            invalidInputs.push({ input, error: 'Score must be between 0-100' });
            return;
        }

        // Mark valid inputs
        if (value !== '') {
            input.classList.add('is-valid');
        }

        if (!scoreData[id]) scoreData[id] = { id: parseInt(id) };
        scoreData[id][field] = numValue;
    });

    if (invalidInputs.length > 0) {
        // Show detailed validation errors
        const errorMessages = invalidInputs.map(item => 
            `Row with ID ${item.input.dataset.id}, Field ${item.input.dataset.field}: ${item.error}`
        ).join('\n');

        Swal.fire({
            icon: 'error',
            title: 'Validation Errors',
            html: `<div style="text-align: left; max-height: 200px; overflow-y: auto;">
                     <strong>Please fix the following errors:</strong><br><br>
                     ${errorMessages.replace(/\n/g, '<br>')}
                   </div>`,
            showConfirmButton: true
        });

        // Focus on first invalid input
        invalidInputs[0].input.focus();
        return;
    }

    scores.push(...Object.values(scoreData));

    if (!scores.length) {
        console.warn("No valid scores to save");
        Swal.fire({
            icon: 'info',
            title: 'No Changes',
            text: 'No scores to update.',
            timer: 2000
        });
        return;
    }

    console.log("Submitting scores:", scores);

    // Show progress
    if (progressContainer) progressContainer.style.display = 'block';
    if (progressBar) progressBar.style.width = '20%';

    const bulkUpdateBtn = document.getElementById('bulkUpdateScores');
    const originalBtnContent = bulkUpdateBtn?.innerHTML;
    
    if (bulkUpdateBtn) {
        bulkUpdateBtn.disabled = true;
        bulkUpdateBtn.innerHTML = '<i class="ri-loader-4-line spin me-1"></i> Saving...';
    }

    // Add timeout and retry logic
    const saveRequest = axios.post('/subjectscoresheet/bulk-update', { scores }, {
        headers: { 
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        timeout: 30000 // 30 second timeout
    });

    Promise.race([
        saveRequest,
        new Promise((_, reject) => 
            setTimeout(() => reject(new Error('Request timeout')), 35000)
        )
    ])
    .then(response => {
        console.log("Bulk update success:", response.data);
        if (progressBar) progressBar.style.width = '100%';

        // Clear validation classes on success
        scoreInputs.forEach(input => {
            input.classList.remove('is-invalid', 'is-valid');
        });

        const updatedCount = response.data.updated_count || scores.length;
        Swal.fire({
            icon: 'success',
            title: 'Saved!',
            html: `
                <div class="text-center">
                    <i class="ri-check-circle-fill text-success" style="font-size: 2rem;"></i>
                    <p class="mt-2">Successfully updated <strong>${updatedCount}</strong> score${updatedCount !== 1 ? 's' : ''}.</p>
                    <small class="text-muted">Last saved: ${new Date().toLocaleString()}</small>
                </div>
            `,
            timer: 3000,
            showConfirmButton: false
        });

        // Update local data and UI
        if (response.data.broadsheets) {
            response.data.broadsheets.forEach(broadsheet => {
                const index = window.broadsheets.findIndex(b => b.id == broadsheet.id);
                if (index !== -1) {
                    window.broadsheets[index] = { ...window.broadsheets[index], ...broadsheet };
                } else {
                    window.broadsheets.push(broadsheet);
                }
                updateRowDisplay(broadsheet.id, broadsheet);
            });
        }

        updateAllRowTotals();
        populateResultsModal();
        if (scoresheetList) scoresheetList.reIndex();

        // Auto-save success feedback
        showTemporaryMessage('Changes saved successfully!', 'success');
    })
    .catch(error => {
        console.error("Bulk update error:", {
            status: error.response?.status,
            data: error.response?.data,
            message: error.message
        });

        let errorMessage = 'Failed to save scores.';
        let errorDetails = '';

        if (error.message === 'Request timeout') {
            errorMessage = 'Request timed out. Please try again.';
            errorDetails = 'The server took too long to respond. Your scores may have been saved partially.';
        } else if (error.response?.status === 422) {
            errorMessage = 'Validation failed on server.';
            errorDetails = error.response.data.message || 'Please check your input data.';
        } else if (error.response?.status >= 500) {
            errorMessage = 'Server error occurred.';
            errorDetails = 'Please try again later or contact support if the problem persists.';
        } else if (!navigator.onLine) {
            errorMessage = 'No internet connection.';
            errorDetails = 'Please check your connection and try again.';
        }

        Swal.fire({
            icon: 'error',
            title: 'Save Failed',
            html: `
                <div class="text-left">
                    <p><strong>${errorMessage}</strong></p>
                    ${errorDetails ? `<p class="text-muted small">${errorDetails}</p>` : ''}
                    <details class="mt-2">
                        <summary class="text-muted small" style="cursor: pointer;">Technical Details</summary>
                        <pre class="text-muted small mt-1" style="font-size: 0.8rem;">${JSON.stringify(error.response?.data || error.message, null, 2)}</pre>
                    </details>
                </div>
            `,
            showConfirmButton: true,
            confirmButtonText: 'Retry',
            showCancelButton: true,
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Retry the save operation
                setTimeout(() => bulkSaveAllScores(), 1000);
            }
        });
    })
    .finally(() => {
        // Cleanup UI state
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
}

// Helper function to show temporary messages
function showTemporaryMessage(message, type = 'info') {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    };

    const messageEl = document.createElement('div');
    messageEl.className = `alert ${alertClass[type]} alert-dismissible fade show position-fixed`;
    messageEl.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    messageEl.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(messageEl);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (messageEl.parentNode) {
            messageEl.parentNode.removeChild(messageEl);
        }
    }, 5000);
}

// Initialize import form
function initializeImportForm() {
    const importForm = document.getElementById('importForm');
    if (!importForm) {
        console.warn("Import form not found");
        return;
    }

    importForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const importBtn = this.querySelector('button[type="submit"]');

        if (importBtn) {
            importBtn.disabled = true;
            importBtn.innerHTML = '<i class="ri-loader-4-line spin me-1"></i> Importing...';
        }

        axios.post(this.action, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        .then(response => {
            console.log("Import success:", response.data);
            let message = response.data.message;
            let html = '';

            if (response.data.errors && response.data.errors.length > 0) {
                message = 'Some rows failed to import:';
                html = '<ul style="text-align: left; max-height: 200px; overflow-y: auto;">';
                response.data.errors.forEach(err => {
                    html += `<li>Row ${err.row}: ${err.attribute === '1' ? 'Admission No.' : err.attribute} - ${err.errors.join(', ')}</li>`;
                });
                html += '</ul>';
            }

            Swal.fire({
                icon: response.data.errors && response.data.errors.length > 0 ? 'warning' : 'success',
                title: response.data.errors && response.data.errors.length > 0 ? 'Partial Import' : 'Imported!',
                html: `${message}${html}`,
                timer: response.data.errors ? undefined : 2000,
                showConfirmButton: !!response.data.errors
            });

            if (response.data.broadsheets && response.data.broadsheets.length > 0) {
                response.data.broadsheets.forEach(score => {
                    const index = window.broadsheets.findIndex(b => b.id == score.id);
                    if (index !== -1) {
                        window.broadsheets[index] = score;
                    } else {
                        window.broadsheets.push(score);
                    }
                    updateRowDisplay(score.id, score);
                });
                updateAllRowTotals();
                populateResultsModal();
                if (scoresheetList) scoresheetList.reIndex();
            }
        })
        .catch(error => {
            console.error("Import error:", {
                status: error.response?.status,
                data: error.response?.data
            });
            let message = error.response?.data?.message || 'Import failed.';
            let html = '';

            if (error.response?.status === 422 && error.response?.data?.errors) {
                message = 'Validation errors:';
                html = '<ul style="text-align: left; max-height: 200px; overflow-y: auto;">';
                error.response.data.errors.forEach(err => {
                    html += `<li>Row ${err.row}: ${err.attribute === '1' ? 'Admission No.' : err.attribute} - ${err.errors.join(', ')}</li>`;
                });
                html += '</ul>';
            }

            Swal.fire({
                icon: 'error',
                title: 'Import Failed',
                html: `${message}${html}`,
                showConfirmButton: true
            });
        })
        .finally(() => {
            if (importBtn) {
                importBtn.disabled = false;
                importBtn.innerHTML = '<i class="ri-upload-line me-1"></i> Import';
            }
        });
    });
}

// Populate results modal
function populateResultsModal() {
    const modalBody = document.querySelector('#scoresModal .modal-body');
    if (!modalBody) return;

    modalBody.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    if (Array.isArray(window.broadsheets) && window.broadsheets.length > 0) {
        generateResultsTable(modalBody, window.broadsheets);
        return;
    }

    axios.get('/subjectscoresheet/results')
        .then(response => {
            if (response.data.html) {
                modalBody.innerHTML = response.data.html;
            } else if (response.data.scores) {
                window.broadsheets = response.data.scores;
                generateResultsTable(modalBody, response.data.scores);
            } else {
                modalBody.innerHTML = '<p class="text-center">No results available.</p>';
            }
        })
        .catch(error => {
            console.error("Error loading results:", error);
            modalBody.innerHTML = `
                <div class="alert alert-warning text-center">
                    <i class="ri-alert-line me-2"></i>
                    Unable to load results from server. 
                    ${error.response?.status === 500 ? 'Please check the server logs for errors.' : ''}
                </div>
            `;
        });
}

// Generate results table
function generateResultsTable(container, scores) {
    let html = `
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Admission No</th>
                        <th>Name</th>
                        <th>CA1</th>
                        <th>CA2</th>
                        <th>CA3</th>
                        <th>
                            <div class="fraction">
                                <div class="numerator">CA1 + CA2 + CA3</div>
                                <div class="denominator">3</div>
                            </div>
                        </th>
                        <th>Exam</th>
                        <th>Total</th>
                        <th>BF</th>
                        <th>Cumulative</th>
                        <th>Grade</th>
                        <th>Position</th>
                    </tr>
                </thead>
                <tbody>
    `;

    if (!scores || !Array.isArray(scores) || scores.length === 0) {
        html += `
            <tr>
                <td colspan="13" class="text-center">No scores available.</td>
            </tr>
        `;
    } else {
        scores.forEach((score, index) => {
            const name = score.name || `${score.fname || ''} ${score.lname || ''}`.trim();
            const ca1 = parseFloat(score.ca1) || 0;
            const ca2 = parseFloat(score.ca2) || 0;
            const ca3 = parseFloat(score.ca3) || 0;
            const exam = parseFloat(score.exam) || 0;
            const caAverage = (ca1 + ca2 + ca3) / 3;
            const total = (caAverage + exam) / 2;
            const bf = parseFloat(score.bf) || 0;
            const cum = parseFloat(score.cum) || (bf + total) / 2;

            const ca1Class = ca1 < 40 && ca1 !== 0 ? 'text-danger' : '';
            const ca2Class = ca2 < 40 && ca2 !== 0 ? 'text-danger' : '';
            const ca3Class = ca3 < 40 && ca3 !== 0 ? 'text-danger' : '';
            const examClass = exam < 40 && exam !== 0 ? 'text-danger' : '';
            const totalClass = total < 40 && total !== 0 ? 'text-danger' : '';
            const cumClass = cum < 40 && cum !== 0 ? 'text-danger' : '';

            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${score.admissionno || '-'}</td>
                    <td>${name || '-'}</td>
                    <td class="${ca1Class}">${score.ca1 !== null && score.ca1 !== undefined ? score.ca1 : '-'}</td>
                    <td class="${ca2Class}">${score.ca2 !== null && score.ca2 !== undefined ? score.ca2 : '-'}</td>
                    <td class="${ca3Class}">${score.ca3 !== null && score.ca3 !== undefined ? score.ca3 : '-'}</td>
                    <td>${caAverage ? caAverage.toFixed(1) : '-'}</td>
                    <td class="${examClass}">${score.exam !== null && score.exam !== undefined ? score.exam : '-'}</td>
                    <td class="${totalClass}">${total.toFixed(1)}</td>
                    <td>${bf.toFixed(2)}</td>
                    <td class="${cumClass}">${cum.toFixed(2)}</td>
                    <td>${score.grade || '-'}</td>
                    <td>${score.position || '-'}</td>
                </tr>
            `;
        });
    }

    html += '</tbody></table></div>';
    container.innerHTML = html;
}

// Delete selected scores
function deleteSelectedScores() {
    console.log("Delete selected scores triggered");
    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]:checked');
    
    if (checkboxes.length === 0) {
        Swal.fire({
            title: "Please select at least one score",
            icon: "info",
            showCloseButton: true
        });
        return;
    }

    const scores = [];
    checkboxes.forEach((checkbox) => {
        const id = checkbox.dataset.id;
        if (id) scores.push(id);
    });

    if (!scores.length) {
        Swal.fire({
            title: "Missing required data",
            text: "Please ensure all selected rows have valid data.",
            icon: "error",
            showCloseButton: true
        });
        return;
    }

    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        showCloseButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            Promise.all(scores.map(id => axios.delete(`/subjectscoresheet/${id}`)))
                .then(() => {
                    Swal.fire({
                        title: "Deleted!",
                        text: "Scores have been deleted.",
                        icon: "success",
                        timer: 2000
                    });
                    // Refresh table data
                    axios.get('/subjectscoresheet/results')
                        .then(response => {
                            if (response.data.scores) {
                                window.broadsheets = response.data.scores;
                                response.data.scores.forEach(score => {
                                    updateRowDisplay(score.id, score);
                                });
                                updateAllRowTotals();
                                populateResultsModal();
                                if (scoresheetList) scoresheetList.reIndex();
                            }
                        });
                })
                .catch((error) => {
                    console.error("Bulk delete error:", error);
                    Swal.fire({
                        title: "Error!",
                        text: error.response?.data?.message || "Failed to delete scores",
                        icon: "error"
                    });
                });
        }
    });
}

// Initialize on DOMContentLoaded
document.addEventListener("DOMContentLoaded", function () {
    console.log("DOMContentLoaded event fired");
    
    ensureBroadsheetsArray();
    
    initializeCheckAll();
    initializeCheckboxes();
    initializeListJs();
    initializeSearch();
    initializeScoreInputs();
    initializeImportForm();
    initializeBulkActions();

    const scoresModal = document.getElementById('scoresModal');
    if (scoresModal) {
        scoresModal.addEventListener('show.bs.modal', populateResultsModal);
    }

    updateAllRowTotals();
    
    console.log("All initialization complete");
});

// Expose functions globally
window.deleteSelectedScores = deleteSelectedScores;
window.triggerSearch = triggerSearch;
window.bulkSaveAllScores = bulkSaveAllScores;
window.updateAllRowTotals = updateAllRowTotals;
window.updateRowTotal = updateRowTotal;
window.ensureBroadsheetsArray = ensureBroadsheetsArray;