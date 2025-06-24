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
                window.broadsheets = [window.broadsheets ? window.broadsheets : {}];
                console.log("Wrapped single broadsheet object in array");
            }
        } else {
            window.broadsheets = [];
            console.warn("window.broadsheets was not an array or object, initialized as empty array");
        }
    }
    console.log("window.broadsheets is now an array with", window.broadsheets.length, "items");
    // Validate broadsheets data
    window.broadsheets.forEach((b, i) => {
        if (!b.admissionno && (!b.fname || !b.lname)) {
            console.warn(`Broadsheet at index ${i} is missing admissionno or name:`, b);
        } else if (!b.fname && !b.lname) {
            console.warn(`Broadsheet at index ${i} is missing fname and lname:`, b);
        }
    });
}

// Populate missing name-text elements
function populateNameTextElements() {
    const rows = document.querySelectorAll('#scoresheetTableBody tr:not(#noDataRow)');
    rows.forEach((row, index) => {
        const admissionno = row.querySelector('.admissionno')?.textContent?.trim() || '';
        const nameElement = row.querySelector('.name-text');
        if (!nameElement || !nameElement.textContent.trim()) {
            const broadsheet = window.broadsheets.find(b => b.admissionno == admissionno);
            const fullName = broadsheet ? `${broadsheet.fname || ''} ${broadsheet.lname || ''}`.trim() : 'Unknown';
            if (fullName && nameElement) {
                nameElement.textContent = fullName;
                console.log(`Populated name-text for row ${index} (admissionno: ${admissionno}) with: ${fullName}`);
            } else {
                console.warn(`Could not populate name-text for row ${index} (admissionno: ${admissionno}). No name data found.`);
                if (nameElement) {
                    nameElement.textContent = 'Unknown';
                }
            }
        }
    });
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
        valueNames: ['name-text', 'admissionno', 'ca1', 'ca2', 'ca3', 'exam', 'total', 'bf', 'cum', 'grade', 'position']
    };

    try {
        // Populate name-text before initializing List.js
        populateNameTextElements();
        scoresheetList = new List('scoresheetTable', options);
        console.log("List.js initialized successfully for regular exams");
        console.log("Initial List.js items:", scoresheetList.items.length);
        // Debug DOM elements
        const nameElements = document.querySelectorAll('#scoresheetTable .name-text');
        const admissionElements = document.querySelectorAll('#scoresheetTable .admissionno');
        console.log(`Found ${nameElements.length} name-text elements, ${admissionElements.length} admissionno elements`);
        nameElements.forEach((el, i) => {
            console.log(`Name element ${i}: ${el.textContent || 'EMPTY'}`);
            if (!el.textContent.trim()) {
                console.warn(`Name element ${i} is empty`);
            }
        });
        admissionElements.forEach((el, i) => {
            console.log(`Admission element ${i}: ${el.textContent || 'EMPTY'}`);
        });
        if (nameElements.length === 0 || admissionElements.length === 0) {
            console.warn("Missing name-text or admissionno elements in table. Search may not work.");
            Swal.fire({
                icon: 'warning',
                title: 'Table Setup Issue',
                text: 'Missing name or admission number elements. Search may not work correctly.',
                showConfirmButton: true
            });
        }
    } catch (error) {
        console.error("List.js initialization failed:", error);
        Swal.fire({
            icon: 'error',
            title: 'Initialization Error',
            text: 'List.js failed to initialize. Check console for details.',
            showConfirmButton: true
        });
    }
}

// Custom search function
function customSearch(searchString) {
    if (!scoresheetList) {
        console.warn("scoresheetList is not initialized");
        Swal.fire({
            icon: 'warning',
            title: 'Search Unavailable',
            text: 'Table search is not initialized. Please refresh the page.',
            timer: 2000,
            showConfirmButton: false
        });
        return;
    }

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.classList.add('search-loading');
    }

    console.log("Searching for:", searchString);
    if (!searchString) {
        console.log("Clearing search filter");
        scoresheetList.filter();
        if (scoresheetList.items.length === 0) {
            console.warn("No items in List.js after clearing filter. Re-indexing...");
            scoresheetList.reIndex();
            console.log("After re-indexing, items:", scoresheetList.items.length);
        }
        if (searchInput) {
            searchInput.classList.remove('search-loading');
            searchInput.focus();
        }
        return;
    }

    const lowerSearch = searchString.toLowerCase().trim();
    let matchedItems = 0;
    scoresheetList.filter(item => {
        const admissionno = item.values()['admissionno']?.toLowerCase() || '';
        let name = item.values()['name-text']?.toLowerCase() || '';
        if (!name) {
            const row = item.elm;
            const nameElement = row.querySelector('.name-text');
            name = nameElement?.textContent?.toLowerCase() || '';
            console.log(`DOM name-text for admissionno ${admissionno}: ${name}`);
            if (!name) {
                const dataName = row.querySelector('.name')?.dataset.name?.toLowerCase() || '';
                name = dataName;
                console.log(`Fallback: Using data-name for admissionno ${admissionno}: ${name}`);
            }
            if (!name) {
                const broadsheet = window.broadsheets.find(b => b.admissionno == admissionno);
                name = broadsheet ? `${broadsheet.fname || ''} ${broadsheet.lname || ''}`.trim().toLowerCase() : '';
                console.log(`Fallback: Constructed name from broadsheets for admissionno ${admissionno}: ${name}`);
            }
        }
        const matches = name.includes(lowerSearch) || admissionno.includes(lowerSearch);
        if (matches) {
            matchedItems++;
            console.log(`Match found - Name: ${name}, Admission: ${admissionno}`);
        } else {
            console.log(`No match - Name: ${name}, Admission: ${admissionno}`);
        }
        return matches;
    });
    console.log(`Total matched items: ${matchedItems}`);
    if (matchedItems === 0) {
        console.warn("No matches found. Checking DOM and data...");
        const nameElements = document.querySelectorAll('#scoresheetTable .name-text');
        nameElements.forEach((el, i) => {
            console.log(`Name element ${i}: ${el.textContent || 'EMPTY'}`);
        });
        console.log("window.broadsheets names:", window.broadsheets.map(b => `${b.fname || ''} ${b.lname || ''}`.trim()));
    }

    if (searchInput) {
        searchInput.classList.remove('search-loading');
    }
}

// Trigger search with debounce
const triggerSearch = debounce(() => {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        console.log("Triggering search with value:", searchInput.value.trim());
        customSearch(searchInput.value.trim());
    } else {
        console.warn("Search input not found during triggerSearch");
    }
}, 100);

// Initialize search input
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    const clearSearch = document.getElementById('clearSearch');
    
    if (searchInput) {
        console.log("Search input found, attaching event listener");
        searchInput.addEventListener('input', triggerSearch);
        // Enable input if data exists
        if (window.broadsheets.length > 0 && searchInput.disabled) {
            searchInput.disabled = false;
            console.log("Enabled search input as data is present");
        }
        // Add keyup event to handle manual clearing
        searchInput.addEventListener('keyup', (e) => {
            if (e.target.value === '') {
                console.log("Search input manually cleared via keyup");
                triggerSearch();
            }
        });
    } else {
        console.error("Search input not found. Search functionality will not work.");
        Swal.fire({
            icon: 'error',
            title: 'Search Input Missing',
            text: 'The search input element was not found. Please check the page structure.',
            showConfirmButton: true
        });
    }
    
    if (clearSearch) {
        clearSearch.addEventListener('click', () => {
            if (searchInput) {
                console.log("Clearing search input via clear button");
                searchInput.value = '';
                triggerSearch();
            }
        });
    } else {
        console.warn("Clear search button not found");
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
    console.log("Found", scoreInputs.length, "score inputs for regular exams");
    
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
    const grade = calculateGrade(cum);

    const totalDisplay = row.querySelector('.total-display span');
    if (totalDisplay) totalDisplay.textContent = total.toFixed(1);

    const bfDisplay = row.querySelector('.bf-display span');
    if (bfDisplay) bfDisplay.textContent = bf.toFixed(2);

    const cumDisplay = row.querySelector('.cum-display span');
    if (cumDisplay) cumDisplay.textContent = cum.toFixed(2);

    const gradeDisplay = row.querySelector('.grade-display span');
    if (gradeDisplay) gradeDisplay.textContent = grade;
}

function updateAllRowTotals() {
    const rows = document.querySelectorAll('#scoresheetTableBody tr');
    rows.forEach(row => {
        if (!row.matches('#noDataRow')) {
            updateRowTotal(row);
        }
    });
    if (scoresheetList) {
        populateNameTextElements();
        scoresheetList.reIndex();
        console.log("Re-indexed List.js after updating all row totals. Items:", scoresheetList.items.length);
    }
}

function updateRowDisplay(id, data) {
    console.log(`=== DEBUG: updateRowDisplay called for ID ${id} ===`);
    console.log("Data received:", data);
    
    const row = document.querySelector(`input[data-id="${id}"]`)?.closest('tr');
    if (!row) {
        console.warn("Row not found for ID:", id);
        return;
    }

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
    if (bfDisplay && data.bf !== undefined) {
        const bfValue = data.bf !== null ? parseFloat(data.bf).toFixed(2) : '0.00';
        bfDisplay.textContent = bfValue;
        console.log(`Updated bf display to:`, bfValue);
    }

    const cumDisplay = row.querySelector('.cum-display span');
    if (cumDisplay && data.cum !== undefined) {
        const cumValue = data.cum !== null ? parseFloat(data.cum).toFixed(2) : '0.00';
        cumDisplay.textContent = cumValue;
        console.log(`Updated cum display to:`, cumValue);
    }

    const gradeDisplay = row.querySelector('.grade-display span');
    if (gradeDisplay && data.grade !== undefined) {
        gradeDisplay.textContent = data.grade || '-';
        console.log(`Updated grade display to:`, data.grade || '-');
    }

    const positionDisplay = row.querySelector('.position-display span');
    if (positionDisplay && data.position !== undefined) {
        positionDisplay.textContent = data.position || '-';
        console.log(`Updated position display to:`, data.position || '-');
    }

    // Update name-text in DOM
    const nameElement = row.querySelector('.name-text');
    if (nameElement && data.fname && data.lname) {
        const fullName = `${data.fname} ${data.lname}`.trim();
        nameElement.textContent = fullName;
        console.log(`Updated name-text for ID ${id} to: ${fullName}`);
    } else if (nameElement) {
        console.warn(`Could not update name-text for ID ${id}. Missing fname or lname in data:`, data);
    }

    if (scoresheetList) {
        scoresheetList.reIndex();
        console.log("Re-indexed List.js after updating row. Items:", scoresheetList.items.length);
    }
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

function bulkSaveAllScores() {
    console.log(`=== DEBUG: Starting bulkSaveAllScores ===`);
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
        Swal.fire({
            icon: 'error',
            title: 'Validation Failed',
            text: 'Some scores are invalid. Please check inputs and try again.',
            showConfirmButton: true
        });
        return;
    }

    console.log("=== DEBUG: Raw score data collected ===");
    console.log("scoreData:", scoreData);

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

    if (!scores.length) {
        console.warn("No valid scores to save");
        Swal.fire({
            icon: 'info',
            title: 'No Scores',
            text: 'No valid scores to save.',
            timer: 2000
        });
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

    axios.post('/subjectscoresheet/bulk-update', {
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
        showTemporaryMessage('Changes saved successfully!', 'success');

        console.log("=== DEBUG: Final window.broadsheets state ===");
        console.log(window.broadsheets);
        
        console.log("=== DEBUG: Current table display values ===");
        scores.forEach(score => {
            const row = document.querySelector(`input[data-id="${score.id}"]`)?.closest('tr');
            if (row) {
                const display = row.querySelector('.cum-display span');
                console.log(`Row ${score.id} cum display:`, display?.textContent);
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
    console.log("=== DEBUG: Current State Check ===");
    console.log("window.broadsheets:", window.broadsheets);
    console.log("window.term_id:", window.term_id);
    
    const scoreInputs = document.querySelectorAll('.score-input');
    console.log("Score inputs found:", scoreInputs.length);
    
    scoreInputs.forEach(input => {
        console.log(`Input - ID: ${input.dataset.id}, Field: ${input.dataset.field}, Value: ${input.value}, Disabled: ${input.disabled}`);
    });
    
    const displays = document.querySelectorAll('.cum-display span');
    console.log("Cum displays found:", displays.length);
    
    displays.forEach((display, index) => {
        const row = display.closest('tr');
        const idInput = row?.querySelector('.score-input');
        console.log(`Cum display ${index} - ID: ${idInput?.dataset.id}, Value: ${display.textContent}`);
    });

    const nameElements = document.querySelectorAll('#scoresheetTable .name-text');
    const admissionElements = document.querySelectorAll('#scoresheetTable .admissionno');
    console.log(`Name elements: ${nameElements.length}, Admission elements: ${admissionElements.length}`);
    nameElements.forEach((el, i) => console.log(`Name element ${i}: ${el.textContent || 'EMPTY'}`));
    admissionElements.forEach((el, i) => console.log(`Admission element ${i}: ${el.textContent || 'EMPTY'}`));
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

        axios.post('/subjectscoresheet/import', formData, {
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
            }
            // Re-enable search input if data was added
            const searchInput = document.getElementById('searchInput');
            if (searchInput && window.broadsheets.length > 0) {
                searchInput.disabled = false;
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
                importBtn.innerHTML = 'Upload';
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

    axios.get('/subjectscoresheet/results')
        .then(response => {
            if (response.data.html) {
                modalBody.innerHTML = response.data.html;
            } else if (response.data.scores) {
                window.broadsheets = response.data.scores;
                generateResultsTable(modalBody, response.data.scores);
                // Re-enable search input if data was loaded
                const searchInput = document.getElementById('searchInput');
                if (searchInput && window.broadsheets.length > 0) {
                    searchInput.disabled = false;
                }
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

function generateResultsTable(container, scores) {
    console.log("=== DEBUG: Generating results table ===");
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
            const name = `${score.fname || ''} ${score.lname || ''}`.trim();
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
                    <td>${score.position || '-'}</td>
                </tr>
            `;
        });
    }

    html += '</tbody></table></div>';
    container.innerHTML = html;
}

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
            Promise.all(scores.map(id => axios.post('/subjectscoresheet/destroy', { id })))
                .then(() => {
                    Swal.fire({
                        title: "Deleted!",
                        text: "Scores have been deleted.",
                        icon: "success",
                        timer: 2000
                    });
                    axios.get('/subjectscoresheet/results')
                        .then(response => {
                            if (response.data.scores) {
                                window.broadsheets = response.data.scores;
                                response.data.scores.forEach(score => {
                                    updateRowDisplay(score.id, score);
                                });
                                updateAllRowTotals();
                                populateResultsModal();
                            }
                        })
                        .catch((error) => {
                            console.error("Error fetching results after delete:", error);
                            Swal.fire({
                                title: "Error!",
                                text: "Failed to refresh data after deletion",
                                icon: "error"
                            });
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

document.addEventListener("DOMContentLoaded", function () {
    console.log("DOMContentLoaded event fired at", new Date().toISOString());
    console.log("Initial broadsheets:", window.broadsheets);
    
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
    
    console.log("All initialization complete for regular exams");
    // Debug initial state
    debugCurrentState();
});

window.deleteSelectedScores = deleteSelectedScores;
window.triggerSearch = triggerSearch;
window.bulkSaveAllScores = bulkSaveAllScores;
window.updateAllRowTotals = updateAllRowTotals;
window.updateRowTotal = updateRowTotal;
window.ensureBroadsheetsArray = ensureBroadsheetsArray;
window.debugCurrentState = debugCurrentState;