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

// Check if page is for mock exams
const isMock = window.isMock || false;
console.log("Page context:", isMock ? "Mock exams" : "Regular exams");

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
        valueNames: isMock
            ? ['student_name', 'admission_no', 'exam', 'total', 'grade', 'position']
            : ['student_name', 'admission_no', 'ca1', 'ca2', 'ca3', 'exam', 'total', 'bf', 'cum', 'grade', 'position'],
        listClass: 'scoresheet-list'
    };

    try {
        scoresheetList = new List('scoresheetTable', options);
        console.log("List.js initialized successfully for", isMock ? "mock" : "regular", "exams");
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
    console.log("Found", scoreInputs.length, "score inputs for", isMock ? "mock" : "regular", "exams");
    
    scoreInputs.forEach(input => {
        const field = input.dataset.field;
        // Disable CA inputs for mock exams
        if (isMock && ['ca1', 'ca2', 'ca3'].includes(field)) {
            input.disabled = true;
            input.classList.add('disabled');
            return;
        }

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
    
    if (isMock) {
        // Mock exams: only exam score matters
        let examValue = 0;
        scoreInputs.forEach(input => {
            if (input.dataset.field === 'exam') {
                examValue = parseFloat(input.value) || 0;
            }
        });

        const total = examValue;
        const grade = calculateGrade(total);
        const remark = { A: 'Excellent', B: 'Very Good', C: 'Good', D: 'Pass', F: 'Fail' }[grade] || 'Unknown';

        const totalDisplay = row.querySelector('.total-display span');
        if (totalDisplay) totalDisplay.textContent = total.toFixed(1);

        const gradeDisplay = row.querySelector('.grade-display span');
        if (gradeDisplay) gradeDisplay.textContent = grade;

        const remarkDisplay = row.querySelector('.remark-display span');
        if (remarkDisplay) remarkDisplay.textContent = remark;

        // Hide or skip bf and cum for mock exams
        const bfDisplay = row.querySelector('.bf-display span');
        if (bfDisplay) bfDisplay.textContent = '-';

        const cumDisplay = row.querySelector('.cum-display span');
        if (cumDisplay) cumDisplay.textContent = '-';
    } else {
        // Regular exams: calculate based on CA and exam
        let ca1 = 0, ca2 = 0, ca3 = 0, examValue = 0;
        scoreInputs.forEach(input => {
            const value = parseFloat(input.value) || 0;
            const field = input.dataset.field;
            switch (field) {
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
}

function updateAllRowTotals() {
    const rows = document.querySelectorAll('#scoresheetTableBody tr');
    rows.forEach(row => {
        if (!row.matches('#noDataRow')) {
            updateRowTotal(row);
        }
    });
}

function updateRowDisplay(id, data) {
    console.log(`=== DEBUG: updateRowDisplay called for ID ${id} (${isMock ? 'mock' : 'regular'} exams) ===`);
    console.log("Data received:", data);
    
    const row = document.querySelector(`input[data-id="${id}"]`)?.closest('tr');
    if (!row) {
        console.warn("Row not found for ID:", id);
        return;
    }

    if (isMock) {
        // Mock exams: update exam, total, grade, position
        const examInput = row.querySelector('input[data-field="exam"]');
        if (examInput && data.exam !== undefined) {
            examInput.value = data.exam !== null ? data.exam : '';
            console.log(`Updated exam input to:`, examInput.value);
        }

        const totalDisplay = row.querySelector('.total-display span');
        if (totalDisplay && data.total !== undefined) {
            const totalValue = parseFloat(data.total).toFixed(1);
            totalDisplay.textContent = totalValue;
            console.log(`Updated total display to:`, totalValue);
        }

        const gradeDisplay = row.querySelector('.grade-display span');
        if (gradeDisplay && data.grade !== undefined) {
            gradeDisplay.textContent = data.grade || '-';
            console.log(`Updated grade display to:`, data.grade || '-');
        }

        const positionDisplay = row.querySelector('.position-display span');
        if (positionDisplay && data.subjectpositionclass !== undefined) {
            positionDisplay.textContent = data.subjectpositionclass || '-';
            console.log(`Updated position display to:`, data.subjectpositionclass || '- (based on total)');
        }

        // Clear bf and cum displays
        const bfDisplay = row.querySelector('.bf-display span');
        if (bfDisplay) bfDisplay.textContent = '-';

        const cumDisplay = row.querySelector('.cum-display span');
        if (cumDisplay) cumDisplay.textContent = '-';
    } else {
        // Regular exams: update all fields
        ['ca1', 'ca2', 'ca3', 'exam'].forEach(field => {
            const input = row.querySelector(`input[data-field="${field}"]`);
            if (input && data[field] !== undefined) {
                input.value = data[field] !== null ? data[field] : '';
                console.log(`Updated ${field} input to:`, input.value);
            }
        });

        const totalDisplay = row.querySelector('.total-display span');
        if (totalDisplay && data.total !== undefined) {
            const totalValue = parseFloat(data.total).toFixed(1);
            totalDisplay.textContent = totalValue;
            console.log(`Updated total display to:`, totalValue);
        }

        const bfDisplay = row.querySelector('.bf-display span');
        if (bfDisplay) {
            const bfValue = data.bf !== null && data.bf !== undefined ? parseFloat(data.bf) : 0;
            bfDisplay.textContent = bfValue.toFixed(2);
            console.log(`Updated bf display to:`, bfValue.toFixed(2));
        }

        const cumDisplay = row.querySelector('.cum-display span');
        if (cumDisplay) {
            const cumValue = data.cum !== null && data.cum !== undefined ? parseFloat(data.cum) : 0;
            cumDisplay.textContent = cumValue.toFixed(2);
            console.log(`Updated cum display to:`, cumValue.toFixed(2));
        }

        const gradeDisplay = row.querySelector('.grade-display span');
        if (gradeDisplay && data.grade !== undefined) {
            gradeDisplay.textContent = data.grade || '-';
            console.log(`Updated grade display to:`, data.grade || '-');
        }

        const positionDisplay = row.querySelector('.position-display span');
        if (positionDisplay && data.subject_position_class !== undefined) {
            positionDisplay.textContent = data.subject_position_class || '-';
            console.log(`Updated position display to:`, data.subject_position_class || '- (based on cum)');
        }
    }

    if (scoresheetList) scoresheetList.reIndex();
}

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
                        if (!isMock || input.dataset.field === 'exam') {
                            input.value = '';
                        }
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

function bulkSaveAllScores() {
    console.log(`=== DEBUG: Starting bulkSaveAllScores (${isMock ? 'mock' : 'regular'} exams) ===`);
    console.log("window.term_id:", window.term_id);
    console.log("window.broadsheets:", window.broadsheets);
    
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

    console.log("Session values:", {
        term_id: window.term_id,
        session_id: window.session_id,
        subjectclass_id: window.subjectclass_id,
        schoolclass_id: window.schoolclass_id,
        staff_id: window.staff_id
    });

    const scores = [];
    const scoreData = {};
    const invalidInputs = [];

    console.log("=== DEBUG: Processing score inputs ===");
    scoreInputs.forEach(input => {
        const id = input.dataset.id;
        const field = input.dataset.field;
        const value = input.value.trim();

        console.log(`Input - ID: ${id}, Field: ${field}, Value: ${value}`);

        // Skip disabled inputs (e.g., CA fields for mock exams)
        if (input.disabled) return;

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

        if (value !== '') {
            input.classList.add('is-valid');
        }

        if (!scoreData[id]) scoreData[id] = { id: parseInt(id) };
        scoreData[id][field] = numValue;
    });

    if (invalidInputs.length > 0) {
        console.error("Validation failed:", invalidInputs);
        return;
    }

    console.log("=== DEBUG: Raw score data collected ===");
    console.log("scoreData:", scoreData);

    if (isMock) {
        // Mock exams: only include id and exam
        Object.values(scoreData).forEach(scoreEntry => {
            console.log(`\n=== DEBUG: Processing mock score ID ${scoreEntry.id} ===`);
            const exam = parseFloat(scoreEntry.exam) || 0;
            console.log(`Exam score: ${exam}`);
            scores.push({
                id: scoreEntry.id,
                exam: exam
            });
        });
    } else {
        // Regular exams: include all fields and calculate cum
        Object.values(scoreData).forEach(scoreEntry => {
            console.log(`\n=== DEBUG: Processing score ID ${scoreEntry.id} ===`);
            
            const ca1 = parseFloat(scoreEntry.ca1) || 0;
            const ca2 = parseFloat(scoreEntry.ca2) || 0;
            const ca3 = parseFloat(scoreEntry.ca3) || 0;
            const exam = parseFloat(scoreEntry.exam) || 0;
            
            console.log(`Raw scores - CA1: ${ca1}, CA2: ${ca2}, CA3: ${ca3}, Exam: ${exam}`);
            
            const caAverage = (ca1 + ca2 + ca3) / 3;
            const total = (caAverage + exam) / 2;
            
            console.log(`Calculated - CA Average: ${caAverage.toFixed(2)}, Total: ${total.toFixed(2)}`);
            
            const broadsheet = window.broadsheets.find(b => b.id == scoreEntry.id);
            const bf = broadsheet ? parseFloat(broadsheet.bf) || 0 : 0;
            
            console.log(`BF from broadsheet: ${bf.toFixed(2)}`);
            console.log(`Broadsheet data:`, broadsheet);
            
            const cum = window.term_id === 1 ? total : (bf + total) / 2;
            
            console.log(`Cum calculation: term_id=${window.term_id}, formula=${window.term_id === 1 ? 'total' : '(bf + total) / 2'}`);
            console.log(`Final cum: ${cum.toFixed(2)}`);
            
            scoreEntry.ca1 = ca1;
            scoreEntry.ca2 = ca2;
            scoreEntry.ca3 = ca3;
            scoreEntry.exam = exam;
            scoreEntry.total = parseFloat(total.toFixed(2));
            scoreEntry.bf = bf;
            scoreEntry.cum = parseFloat(cum.toFixed(2));
            
            console.log(`Final scoreEntry:`, scoreEntry);
            scores.push(scoreEntry);
        });
    }

    if (!scores.length) {
        console.warn("No valid scores to save");
        return;
    }

    console.log("=== DEBUG: Final scores to be sent ===");
    console.log(JSON.stringify(scores, null, 2));

    if (progressContainer) progressContainer.style.display = 'block';
    if (progressBar) progressBar.style.width = '20%';

    const bulkUpdateBtn = document.getElementById('bulkUpdateScores');
    const originalBtnContent = bulkUpdateBtn?.innerHTML;
    
    if (bulkUpdateBtn) {
        bulkUpdateBtn.disabled = true;
        bulkUpdateBtn.innerHTML = '<i class="ri-loader-4-line sync-icon"></i> Saving...';
    }

    const endpoint = isMock ? '/subjectscoresheet/bulk-update-mock' : '/subjectscoresheet/bulk-update';
    axios.post(endpoint, {
        scores,
        term_id: window.term_id,
        session_id: window.session_id,
        subjectclass_id: window.subjectclass_id,
        schoolclass_id: window.schoolclass_id,
        staff_id: window.staff_id
    }, {
        headers: { 
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        timeout: 30000
    })
    .then(response => {
        console.log("=== DEBUG: Server response ===");
        console.log("Response data:", response.data);
        
        if (progressBar) progressBar.style.width = '100%';

        scoreInputs.forEach(input => {
            input.classList.remove('is-invalid', 'is-valid');
        });

        const updatedCount = response.data.data?.broadsheets?.length || scores.length;
        
        if (response.data.data?.broadsheets) {
            console.log("=== DEBUG: Updating local broadsheets ===");
            response.data.data.broadsheets.forEach(broadsheet => {
                console.log(`Server returned broadsheet ${broadsheet.id}:`, broadsheet);
                
                const index = window.broadsheets.findIndex(b => b.id == broadsheet.id);
                if (index !== -1) {
                    console.log(`Updating existing broadsheet at index ${index}`);
                    console.log(`Before update:`, window.broadsheets[index]);
                    window.broadsheets[index] = { ...window.broadsheets[index], ...broadsheet };
                    console.log(`After update:`, window.broadsheets[index]);
                } else {
                    console.log(`Adding new broadsheet`);
                    window.broadsheets.push(broadsheet);
                }
                
                updateRowDisplay(broadsheet.id, broadsheet);
            });
        }

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

        updateAllRowTotals();
        populateResultsModal();
        if (scoresheetList) scoresheetList.reIndex();

        showTemporaryMessage('Changes saved successfully!', 'success');

        console.log("=== DEBUG: Final window.broadsheets state ===");
        console.log(window.broadsheets);
        
        console.log("=== DEBUG: Current table display values ===");
        scores.forEach(score => {
            const row = document.querySelector(`input[data-id="${score.id}"]`)?.closest('tr');
            if (row) {
                const displayField = isMock ? '.total-display span' : '.cum-display span';
                const display = row.querySelector(displayField);
                console.log(`Row ${score.id} ${isMock ? 'total' : 'cum'} display:`, display?.textContent);
            }
        });
    })
    .catch(error => {
        console.error("=== DEBUG: Server error ===");
        console.error("Error details:", {
            status: error.response?.status,
            data: error.response?.data,
            message: error.message
        });

        Swal.fire({
            icon: 'error',
            title: 'Save Failed',
            text: 'Check console for detailed error information',
            showConfirmButton: true
        });
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
}

function debugCurrentState() {
    console.log(`=== DEBUG: Current State Check (${isMock ? 'mock' : 'regular'} exams) ===`);
    console.log("window.broadsheets:", window.broadsheets);
    console.log("window.term_id:", window.term_id);
    
    const scoreInputs = document.querySelectorAll('.score-input');
    console.log("Score inputs found:", scoreInputs.length);
    
    scoreInputs.forEach(input => {
        console.log(`Input - ID: ${input.dataset.id}, Field: ${input.dataset.field}, Value: ${input.value}, Disabled: ${input.disabled}`);
    });
    
    const displayField = isMock ? '.total-display span' : '.cum-display span';
    const displays = document.querySelectorAll(displayField);
    console.log(`${isMock ? 'Total' : 'Cum'} displays found:`, displays.length);
    
    displays.forEach((display, index) => {
        const row = display.closest('tr');
        const idInput = row?.querySelector('.score-input');
        console.log(`${isMock ? 'Total' : 'Cum'} display ${index} - ID: ${idInput?.dataset.id}, Value: ${display.textContent}`);
    });
}

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

    setTimeout(() => {
        if (messageEl.parentNode) {
            messageEl.parentNode.removeChild(messageEl);
        }
    }, 5000);
}

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

        const endpoint = isMock ? '/subjectscoresheet/import-mock' : '/subjectscoresheet/import';
        axios.post(endpoint, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        .then(response => {
            console.log(`Import success (${isMock ? 'mock' : 'regular'} exams):`, response.data);
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
            console.error(`Import error (${isMock ? 'mock' : 'regular'} exams):`, {
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

function populateResultsModal() {
    const modalBody = document.querySelector('#scoresModal .modal-body');
    if (!modalBody) return;

    modalBody.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    if (Array.isArray(window.broadsheets) && window.broadsheets.length > 0) {
        generateResultsTable(modalBody, window.broadsheets);
        return;
    }

    const endpoint = isMock ? '/subjectscoresheet/results-mock' : '/subjectscoresheet/results';
    axios.get(endpoint)
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
            console.error(`Error loading results (${isMock ? 'mock' : 'regular'} exams):`, error);
            modalBody.innerHTML = `
                <div class="alert alert-warning text-center">
                    <i class="ri-alert-line me-2"></i>
                    Unable to load results from server. 
                    ${error.response?.status === 500 ? 'Please check the server logs for errors.' : ''}
                </div>
            `;
        });
}

function generateResultsTable(container, scores) {
    console.log(`=== DEBUG: Generating results table (${isMock ? 'mock' : 'regular'} exams) ===`);
    let html = `
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Admission No</th>
                        <th>Name</th>
                        ${isMock ? `
                            <th>Exam</th>
                            <th>Total</th>
                            <th>Grade</th>
                            <th>Position</th>
                        ` : `
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
                        `}
                    </tr>
                </thead>
                <tbody>
    `;

    if (!scores || !Array.isArray(scores) || scores.length === 0) {
        html += `
            <tr>
                <td colspan="${isMock ? 7 : 13}" class="text-center">No scores available.</td>
            </tr>
        `;
    } else {
        scores.forEach((score, index) => {
            const name = score.name || `${score.fname || ''} ${score.lname || ''}`.trim();
            
            if (isMock) {
                const exam = parseFloat(score.exam) || 0;
                const total = parseFloat(score.total) || exam;
                const examClass = exam < 40 && exam !== 0 ? 'text-danger' : '';
                const totalClass = total < 40 && total !== 0 ? 'text-danger' : '';

                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${score.admissionno || '-'}</td>
                        <td>${name || '-'}</td>
                        <td class="${examClass}">${score.exam !== null && score.exam !== undefined ? score.exam : '-'}</td>
                        <td class="${totalClass}">${total.toFixed(1)}</td>
                        <td>${score.grade || '-'}</td>
                        <td>${score.subjectpositionclass || '-'}</td>
                    </tr>
                `;
            } else {
                const ca1 = parseFloat(score.ca1) || 0;
                const ca2 = parseFloat(score.ca2) || 0;
                const ca3 = parseFloat(score.ca3) || 0;
                const exam = parseFloat(score.exam) || 0;
                const caAverage = (ca1 + ca2 + ca3) / 3;
                const total = (caAverage + exam) / 2;
                const bf = parseFloat(score.bf) || 0;
                const cum = parseFloat(score.cum) || (window.term_id === 1 ? total : (bf + total) / 2);

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
                        <td>${score.subject_position_class || '-'}</td>
                    </tr>
                `;
            }
        });
    }

    html += '</tbody></table></div>';
    container.innerHTML = html;
}

function deleteSelectedScores() {
    console.log(`Delete selected scores triggered (${isMock ? 'mock' : 'regular'} exams)`);
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
            const endpoint = isMock ? '/subjectscoresheet/destroy-mock' : '/subjectscoresheet/destroy';
            Promise.all(scores.map(id => axios.post(endpoint, { id })))
                .then(() => {
                    Swal.fire({
                        title: "Deleted!",
                        text: "Scores have been deleted.",
                        icon: "success",
                        timer: 2000
                    });
                    const resultsEndpoint = isMock ? '/subjectscoresheet/results-mock' : '/subjectscoresheet/results';
                    axios.get(resultsEndpoint)
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
                    console.error(`Bulk delete error (${isMock ? 'mock' : 'regular'} exams):`, error);
                    Swal.fire({
                        title: "Error!",
                        text: error.response?.data?.message || "Failed to delete scores",
                        icon: "error"
                    });
                });
        }
    });
}

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
    
    console.log("All initialization complete for", isMock ? "mock" : "regular", "exams");
});

window.deleteSelectedScores = deleteSelectedScores;
window.triggerSearch = triggerSearch;
window.bulkSaveAllScores = bulkSaveAllScores;
window.updateAllRowTotals = updateAllRowTotals;
window.updateRowTotal = updateRowTotal;
window.ensureBroadsheetsArray = ensureBroadsheetsArray;
window.debugCurrentState = debugCurrentState;