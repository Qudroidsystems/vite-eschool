console.log("subjectscoresheet.init.js loaded at", new Date().toISOString());

// Dependency checks to ensure required libraries are loaded
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

// Set CSRF token for Axios to prevent 403 errors
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
if (!csrfToken) {
    console.warn("CSRF token not found. AJAX requests may fail.");
}

// FIX: Ensure window.broadsheets is always an array
function ensureBroadsheetsArray() {
    if (typeof window.broadsheets === 'undefined') {
        window.broadsheets = [];
        console.warn("window.broadsheets was undefined, initialized as empty array");
    } else if (!Array.isArray(window.broadsheets)) {
        // Convert to array if it's not already an array
        if (window.broadsheets && typeof window.broadsheets === 'object') {
            // If it's an object with numeric keys, convert to array
            const keys = Object.keys(window.broadsheets);
            if (keys.length > 0 && keys.every(key => !isNaN(key))) {
                window.broadsheets = Object.values(window.broadsheets);
                console.log("Converted window.broadsheets object to array");
            } else {
                // If it's a single object, wrap it in an array
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

// Debounce function to limit rapid search triggers
function debounce(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// Initialize List.js for table sorting and searching
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

// Custom search function for List.js
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

// Handle checkbox change to sync "Check All"
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
        
        // Add blur event for validation
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

// Calculate grade based on total
function calculateGrade(total) {
    if (total >= 70) return 'A';
    if (total >= 60) return 'B';
    if (total >= 40) return 'C';
    if (total >= 30) return 'D';
    return 'F';
}

// Update single row total - FIXED CALCULATION
function updateRowTotal(row) {
    const scoreInputs = row.querySelectorAll('.score-input');
    let ca1 = 0, ca2 = 0, ca3 = 0, examValue = 0;
    
    scoreInputs.forEach(input => {
        const value = parseFloat(input.value) || 0;
        const field = input.dataset.field;
        
        switch(field) {
            case 'ca1':
                ca1 = value;
                break;
            case 'ca2':
                ca2 = value;
                break;
            case 'ca3':
                ca3 = value;
                break;
            case 'exam':
                examValue = value;
                break;
        }
    });
    
    // Calculate CA average (30% of total)
    const caAverage = (ca1 + ca2 + ca3) / 3;
    const caScore = (caAverage * 30) / 100;
    
    // Calculate exam score (70% of total)
    const examScore = (examValue * 70) / 100;
    
    // Total score
    const total = caScore + examScore;
    
    // Update display
    const totalDisplay = row.querySelector('.total-display span');
    if (totalDisplay) {
        totalDisplay.textContent = total.toFixed(1);
    }

    const grade = calculateGrade(total);
    const gradeDisplay = row.querySelector('.grade-display span');
    if (gradeDisplay) {
        gradeDisplay.textContent = grade;
    }
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
    const row = document.querySelector(`input[data-id="${id}"]`)?.closest('tr');
    if (!row) {
        console.warn("Row not found for ID:", id);
        return;
    }

    const totalDisplay = row.querySelector('.total-display span');
    if (totalDisplay && data.total !== undefined) {
        totalDisplay.textContent = parseFloat(data.total).toFixed(1);
    }

    const gradeDisplay = row.querySelector('.grade-display span');
    if (gradeDisplay && data.grade !== undefined) {
        gradeDisplay.textContent = data.grade || '-';
    }

    const positionDisplay = row.querySelector('.position-display span');
    if (positionDisplay && data.position !== undefined) {
        positionDisplay.textContent = data.position || '-';
    }
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

    // Keyboard shortcut for save
    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            console.log("Ctrl+S pressed - triggering bulk save");
            bulkSaveAllScores();
        }
    });
}

// FIXED: Bulk save all scores with proper array handling
function bulkSaveAllScores() {
    console.log("Starting bulkSaveAllScores");
    
    // Ensure broadsheets is an array
    ensureBroadsheetsArray();
    
    const scoreInputs = document.querySelectorAll('.score-input');
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = progressContainer?.querySelector('.progress-bar');

    if (scoreInputs.length === 0) {
        console.warn("No score inputs found");
        Swal.fire({
            icon: 'info',
            title: 'No Scores Found',
            text: 'No score inputs found to save.',
            showConfirmButton: true
        });
        return;
    }

    console.log("Found", scoreInputs.length, "score inputs");

    const scores = [];
    const scoreData = {};
    let hasInvalidData = false;

    scoreInputs.forEach(input => {
        const id = input.dataset.id;
        const field = input.dataset.field;
        const value = input.value ? parseFloat(input.value) : null;

        if (!id || !field) {
            console.error("Invalid input attributes:", { id, field, value });
            hasInvalidData = true;
            return;
        }

        if (value !== null && (isNaN(value) || value < 0 || value > 100)) {
            console.error("Invalid score value:", { id, field, value });
            hasInvalidData = true;
            return;
        }

        if (!scoreData[id]) {
            scoreData[id] = { id: parseInt(id) };
        }
        scoreData[id][field] = value;
    });

    if (hasInvalidData) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Data',
            text: 'Please correct invalid score values (0-100) before saving.',
            showConfirmButton: true
        });
        return;
    }

    // Convert scoreData object to array
    Object.keys(scoreData).forEach(id => {
        scores.push(scoreData[id]);
    });

    if (scores.length === 0) {
        console.warn("No valid scores to update");
        Swal.fire({
            icon: 'info',
            title: 'No Changes',
            text: 'No valid scores to update.',
            showConfirmButton: true
        });
        return;
    }

    console.log("Prepared scores for submission:", scores);

    // Show progress
    if (progressContainer) {
        progressContainer.style.display = 'block';
        if (progressBar) progressBar.style.width = '0%';
    }

    const bulkUpdateBtn = document.getElementById('bulkUpdateScores');
    if (bulkUpdateBtn) {
        bulkUpdateBtn.disabled = true;
        bulkUpdateBtn.innerHTML = '<i class="ri-loader-4-line spin me-1"></i> Saving...';
    }

    // Make the API call
    axios.post('/subjectscoresheet/bulk-update', { scores })
        .then(response => {
            console.log("Bulk update successful:", response.data);
            if (progressBar) progressBar.style.width = '100%';
            
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: response.data.message || `Successfully updated ${response.data.updated_count || scores.length} scores.`,
                timer: 2000,
                showConfirmButton: false
            });

            // FIXED: Update global broadsheets data with proper array handling
            if (Array.isArray(window.broadsheets) && response.data.broadsheets) {
                response.data.broadsheets.forEach(updatedBroadsheet => {
                    const index = window.broadsheets.findIndex(b => b.id == updatedBroadsheet.id);
                    if (index !== -1) {
                        window.broadsheets[index] = updatedBroadsheet;
                    }
                });
            }

            // Update row displays
            scores.forEach(score => {
                if (response.data.broadsheets) {
                    const updatedScore = response.data.broadsheets.find(b => b.id == score.id);
                    if (updatedScore) {
                        updateRowDisplay(score.id, updatedScore);
                    }
                }
            });
            
            // Recalculate all totals
            updateAllRowTotals();
        })
        .catch(error => {
            console.error("Bulk update failed:", {
                status: error.response?.status,
                data: error.response?.data,
                message: error.message
            });
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.response?.data?.message || 'Failed to update scores. Please try again.',
                showConfirmButton: true
            });
        })
        .finally(() => {
            if (progressContainer) {
                setTimeout(() => {
                    progressContainer.style.display = 'none';
                }, 1000);
            }
            if (bulkUpdateBtn) {
                bulkUpdateBtn.disabled = false;
                bulkUpdateBtn.innerHTML = '<i class="ri-save-line me-1"></i> Save All Scores';
            }
        });
}

// Initialize import form
function initializeImportForm() {
    const importForm = document.getElementById('importForm');
    if (!importForm) return;

    importForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const importBtn = this.querySelector('button[type="submit"]');
        
        if (importBtn) {
            importBtn.disabled = true;
            importBtn.innerHTML = '<i class="ri-loader-4-line spin me-1"></i> Importing...';
        }

        axios.post(this.action, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            }
        })
        .then(response => {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: response.data.message,
                timer: 2000,
                showConfirmButton: false
            });
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        })
        .catch(error => {
            console.error("Import error:", error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.response?.data?.message || 'Failed to import scores',
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

// FIXED: Populate results modal with better error handling
function populateResultsModal() {
    const modalBody = document.querySelector('#scoresModal .modal-body');
    if (!modalBody) return;

    modalBody.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    // Use current broadsheets data if available instead of making another API call
    if (Array.isArray(window.broadsheets) && window.broadsheets.length > 0) {
        generateResultsTable(modalBody, window.broadsheets);
        return;
    }

    // Fallback to API call
    axios.get('/subjectscoresheet/results')
        .then(response => {
            if (response.data.html) {
                modalBody.innerHTML = response.data.html;
            } else if (response.data.scores) {
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

// Helper function to generate results table
function generateResultsTable(container, scores) {
    let html = `
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                     <tr>
                                <th>#</th>
                                <th>Admission No.</th>
                                <th>Name</th>
                                <th>CA1</th>
                                <th>CA2</th>
                                <th>CA3</th>
                                <th>
                                    <div class="fraction">
                                        <div class="numerator">a + b + c</div>
                                        <div class="denominator">3</div>
                                    </div>
                                </th>
                                <th>Exam</th>
                                <th>
                                    <div class="fraction">
                                        <div class="numerator">Exam + Total CA</div>
                                        <div class="denominator">2</div>
                                    </div>
                                </th>
                                <th><span class="d-block">Cum</span> (f/g)/2</th>
                                <th>Cum</th>
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

            // Calculate CA average
            const caAverage = (ca1 + ca2 + ca3) / 3;

            // Calculate weighted total (30% CA average + 70% exam)
            const caWeighted = (caAverage * 30) / 100;
            const examWeighted = (exam * 70) / 100;
            const total = caWeighted + examWeighted;

            // Calculate CA AVG / EXAM (ratio)
            const caAvgExam = exam !== 0 ? (caAverage / exam).toFixed(2) : '-';

            // B/F and Cum (assumed to be 0 and total if not provided)
            const bf = score.bf || 0;
            const cum = score.cum || total.toFixed(1);

            // Determine CSS class for scores below 40
            const ca1Class = ca1 < 40 && ca1 !== 0 ? 'text-danger' : '';
            const ca2Class = ca2 < 40 && ca2 !== 0 ? 'text-danger' : '';
            const ca3Class = ca3 < 40 && ca3 !== 0 ? 'text-danger' : '';
            const examClass = exam < 40 && exam !== 0 ? 'text-danger' : '';
            const totalClass = total < 40 && total !== 0 ? 'text-danger' : '';

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
                    <td>${caAvgExam}</td>
                    <td>${bf}</td>
                    <td class="${totalClass}">${cum}</td>
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
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
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
    
    // FIRST: Ensure broadsheets is properly initialized
    ensureBroadsheetsArray();
    
    // Initialize all components
    initializeCheckAll();
    initializeCheckboxes();
    initializeListJs();
    initializeSearch();
    initializeScoreInputs();
    initializeImportForm();
    initializeBulkActions();

    // Initialize modals
    const scoresModal = document.getElementById('scoresModal');
    if (scoresModal) {
        scoresModal.addEventListener('show.bs.modal', populateResultsModal);
    }

    // Initial calculation of all row totals
    updateAllRowTotals();
    
    console.log("All initialization complete");
});

// Expose functions globally for external access
window.deleteSelectedScores = deleteSelectedScores;
window.triggerSearch = triggerSearch;
window.bulkSaveAllScores = bulkSaveAllScores;
window.updateAllRowTotals = updateAllRowTotals;
window.updateRowTotal = updateRowTotal;
window.ensureBroadsheetsArray = ensureBroadsheetsArray;