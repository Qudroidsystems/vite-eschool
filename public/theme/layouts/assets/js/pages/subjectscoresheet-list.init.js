console.log("subjectscoresheet.init.js loaded at", new Date().toISOString());

// Dependency checks
try {
    if (typeof axios === 'undefined') throw new Error("Axios is not loaded");
    if (typeof Swal === 'undefined') throw new Error("SweetAlert2 is not loaded");
    if (typeof bootstrap === 'undefined') throw new Error("Bootstrap is not loaded");
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

// Utility: Normalize picture path
function normalizePicturePath(picture) {
    if (!picture || picture === 'none' || picture === '') {
        console.log("normalizePicturePath: Empty or 'none' picture, returning 'unnamed.jpg'");
        return 'unnamed.jpg';
    }
    const normalized = picture.replace(/^studentavatar\/|^\//, '');
    console.log(`normalizePicturePath: Original: ${picture}, Normalized: ${normalized}`);
    return normalized;
}

// Utility: Ensure broadsheets is a flat array
function ensureBroadsheetsArray() {
    if (typeof window.broadsheets === 'undefined') {
        window.broadsheets = [];
    } else if (!Array.isArray(window.broadsheets)) {
        window.broadsheets = [window.broadsheets];
    } else if (
        window.broadsheets.length === 1 &&
        typeof window.broadsheets[0] === 'object' &&
        !Array.isArray(window.broadsheets[0])
    ) {
        const nestedObject = window.broadsheets[0];
        window.broadsheets = Object.values(nestedObject).filter(
            item => item && typeof item === 'object' && item.id
        );
    }
    console.log('Broadsheets pictures:', window.broadsheets.map(b => ({
        admissionno: b.admissionno,
        picture: b.picture || 'none'
    })));
}

// Grade calculation
function calculateGrade(score) {
    if (isNaN(score)) return '-';
    if (score >= 70) return 'A';
    if (score >= 60) return 'B';
    if (score >= 50) return 'C';
    if (score >= 40) return 'D';
    return 'F';
}

// Ordinal for position
function getOrdinalSuffix(position) {
    if (position % 100 >= 11 && position % 100 <= 13) return position + 'th';
    switch (position % 10) {
        case 1: return position + 'st';
        case 2: return position + 'nd';
        case 3: return position + 'rd';
        default: return position + 'th';
    }
}

// Update a table row and trigger full position update
function updateRowTotal(row) {
    const scoreInputs = row.querySelectorAll('.score-input');
    const id = row.querySelector('.score-input')?.dataset.id;
    if (!id) return;
    if (!window.broadsheets || !Array.isArray(window.broadsheets) || window.broadsheets.length === 0) return;

    let ca1 = 0, ca2 = 0, ca3 = 0, exam = 0;
    scoreInputs.forEach(input => {
        const value = parseFloat(input.value) || 0;
        switch (input.dataset.field) {
            case 'ca1': ca1 = value; break;
            case 'ca2': ca2 = value; break;
            case 'ca3': ca3 = value; break;
            case 'exam': exam = value; break;
        }
    });

    const caAverage = (ca1 + ca2 + ca3) / 3;
    const total = (caAverage + exam) / 2;
    const broadsheet = window.broadsheets.find(b => String(b.id) === String(id));
    const bf = broadsheet ? parseFloat(broadsheet.bf) || 0 : 0;
    const cum = window.term_id === 1 ? total : (bf + total) / 2;
    const grade = calculateGrade(cum);

    // Update DOM
    const totalDisplay = row.querySelector('.total-display span');
    if (totalDisplay) {
        totalDisplay.textContent = total.toFixed(1);
        totalDisplay.classList.add('bg-warning');
        setTimeout(() => totalDisplay.classList.remove('bg-warning'), 500);
    }
    const bfDisplay = row.querySelector('.bf-display span');
    if (bfDisplay) bfDisplay.textContent = bf.toFixed(2);
    const cumDisplay = row.querySelector('.cum-display span');
    if (cumDisplay) {
        cumDisplay.textContent = cum.toFixed(2);
        cumDisplay.classList.add('bg-warning');
        setTimeout(() => cumDisplay.classList.remove('bg-warning'), 500);
    }
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
            ca1, ca2, ca3, exam, total, cum, grade
        };
    }

    forceUpdatePositions();
}

// Standard competition ranking (tied ranks)
function forceUpdatePositions() {
    ensureBroadsheetsArray();
    const cums = window.broadsheets.map(b => parseFloat(b.cum) || 0);
    const allZero = cums.length > 0 && cums.every(cum => cum === 0);

    if (allZero) {
        document.querySelectorAll('#scoresheetTableBody tr:not(#noDataRow)').forEach(row => {
            const positionDisplay = row.querySelector('.position-display span');
            if (positionDisplay) {
                positionDisplay.textContent = "0th";
                positionDisplay.classList.remove('bg-warning');
                positionDisplay.classList.add('bg-info');
            }
        });
    } else {
        const sorted = window.broadsheets
            .map(b => ({...b, cum: parseFloat(b.cum) || 0}))
            .sort((a, b) => b.cum - a.cum || a.id - b.id);

        let lastCum = null, lastPosition = 0, rank = 0;
        const idToPos = {};
        sorted.forEach((broadsheet, idx) => {
            rank++;
            if (lastCum !== null && broadsheet.cum === lastCum) {
                // use lastPosition
            } else {
                lastPosition = rank;
                lastCum = broadsheet.cum;
            }
            idToPos[broadsheet.id] = getOrdinalSuffix(lastPosition);
        });

        window.broadsheets.forEach(broadsheet => {
            const row = document.querySelector(`tr:has(input[data-id="${broadsheet.id}"])`);
            if (row) {
                const positionDisplay = row.querySelector('.position-display span');
                if (positionDisplay) {
                    positionDisplay.textContent = idToPos[broadsheet.id] || "-";
                    positionDisplay.classList.remove('bg-warning');
                    positionDisplay.classList.add('bg-info');
                }
            }
        });
    }
}

// Bulk save all scores
function bulkSaveAllScores() {
    ensureBroadsheetsArray();
    const scoreInputs = document.querySelectorAll('.score-input');
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = progressContainer?.querySelector('.progress-bar');
    const bulkUpdateScores = document.getElementById('bulkUpdateScores');
    const originalBtnContent = bulkUpdateScores?.innerHTML;

    if (!scoreInputs.length) {
        Swal.fire({
            icon: 'info',
            title: 'No Scores',
            text: 'No scores to save.',
            timer: 2000
        });
        return;
    }

    const sessionVars = {
        term_id: window.term_id,
        session_id: window.session_id,
        subjectclass_id: window.subjectclass_id,
        schoolclass_id: window.schoolclass_id,
        staff_id: window.staff_id
    };
    for (const [key, value] of Object.entries(sessionVars)) {
        if (!value) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: `Please select a ${key.replace('_id', '')} before saving.`,
                showConfirmButton: true
            });
            return;
        }
    }

    const scores = [];
    const scoreData = {};
    const invalidInputs = [];

    scoreInputs.forEach(input => {
        const id = input.dataset.id;
        const field = input.dataset.field;
        const value = input.value.trim();
        if (input.disabled) return;
        input.classList.remove('is-invalid', 'is-valid');
        if (!id || !field) {
            input.classList.add('is-invalid');
            invalidInputs.push({ input, error: 'Missing required attributes' });
            return;
        }
        const numValue = value === '' ? 0 : parseFloat(value);
        if (isNaN(numValue) || numValue < 0 || numValue > 100) {
            input.classList.add('is-invalid');
            invalidInputs.push({ input, error: `Score must be between 0-100 for ${field.toUpperCase()}` });
            return;
        }
        input.classList.add('is-valid');
        if (!scoreData[id]) scoreData[id] = { id: parseInt(id) };
        scoreData[id][field] = numValue;
    });

    if (invalidInputs.length > 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Failed',
            html: `Some scores are invalid:<ul>${invalidInputs.map(e => `<li>${e.error}</li>`).join('')}</ul>`,
            showConfirmButton: true
        });
        return;
    }

    Object.values(scoreData).forEach(scoreEntry => {
        const ca1 = parseFloat(scoreEntry.ca1) || 0;
        const ca2 = parseFloat(scoreEntry.ca2) || 0;
        const ca3 = parseFloat(scoreEntry.ca3) || 0;
        const exam = parseFloat(scoreEntry.exam) || 0;
        const caAverage = (ca1 + ca2 + ca3) / 3;
        const total = (caAverage + exam) / 2;
        const broadsheet = window.broadsheets.find(b => String(b.id) === String(scoreEntry.id));
        const bf = broadsheet ? parseFloat(broadsheet.bf) || 0 : 0;
        const cum = window.term_id === 1 ? total : (bf + total) / 2;
        const grade = calculateGrade(cum);

        scoreEntry.ca1 = ca1;
        scoreEntry.ca2 = ca2;
        scoreEntry.ca3 = ca3;
        scoreEntry.exam = exam;
        scoreEntry.total = total;
        scoreEntry.bf = bf;
        scoreEntry.cum = cum;
        scoreEntry.grade = grade;
        scores.push(scoreEntry);
    });

    if (!scores.length) {
        Swal.fire({
            icon: 'info',
            title: 'No Scores',
            text: 'No valid scores to save.',
            timer: 2000
        });
        return;
    }

    if (progressContainer) progressContainer.style.display = 'block';
    if (progressBar) progressBar.style.width = '20%';
    if (bulkUpdateScores) {
        bulkUpdateScores.disabled = true;
        bulkUpdateScores.innerHTML = '<i class="ri-loader-4-line sync-icon"></i> Saving...';
    }

    axios.post(window.routes?.bulkUpdate || '/subjectscoresheet/bulk-update', {
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
        if (response.data.data?.broadsheets) {
            window.broadsheets = response.data.data.broadsheets;
            ensureBroadsheetsArray();
            window.broadsheets.forEach(broadsheet => {
                const row = document.querySelector(`input[data-id="${broadsheet.id}"]`)?.closest('tr');
                if (row) {
                    ['ca1', 'ca2', 'ca3', 'exam'].forEach(field => {
                        const input = row.querySelector(`input[data-field="${field}"]`);
                        if (input) input.value = broadsheet[field] !== null ? broadsheet[field] : '';
                    });
                    const totalDisplay = row.querySelector('.total-display span');
                    if (totalDisplay) totalDisplay.textContent = parseFloat(broadsheet.total || 0).toFixed(1);
                    const bfDisplay = row.querySelector('.bf-display span');
                    if (bfDisplay) bfDisplay.textContent = parseFloat(broadsheet.bf || 0).toFixed(2);
                    const cumDisplay = row.querySelector('.cum-display span');
                    if (cumDisplay) cumDisplay.textContent = parseFloat(broadsheet.cum || 0).toFixed(2);
                    const gradeDisplay = row.querySelector('.grade-display span');
                    if (gradeDisplay) gradeDisplay.textContent = broadsheet.grade || '-';
                    const image = row.querySelector('.student-image');
                    if (image && broadsheet.picture) {
                        const existingPicture = image.dataset.picture || 'none';
                        const newPicture = broadsheet.picture || existingPicture;
                        const picture = normalizePicturePath(newPicture);
                        const imageUrl = `/storage/student_avatars/${picture}`;
                        image.src = imageUrl;
                        image.dataset.image = imageUrl;
                        image.dataset.picture = newPicture;
                        image.onerror = () => {
                            image.src = '/storage/student_avatars/unnamed.jpg';
                            image.dataset.image = '/storage/student_avatars/unnamed.jpg';
                            image.dataset.picture = 'none';
                            console.log(`Image failed to load for admissionno: ${broadsheet.admissionno || 'unknown'}, picture: ${newPicture}, attempted URL: ${imageUrl}`);
                        };
                    }
                }
            });
            forceUpdatePositions();

            Swal.fire({
                icon: 'success',
                title: 'Saved!',
                text: `Successfully updated ${scores.length} score${scores.length !== 1 ? 's' : ''} with positions.`,
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Save Failed',
                text: 'Server did not return updated scores.',
                showConfirmButton: true
            });
        }
    })
    .catch(error => {
        let errorMessage = 'Failed to save scores. Check console for details.';
        if (error.response) errorMessage = error.response.data.message || errorMessage;
        Swal.fire({
            icon: 'error',
            title: 'Save Failed',
            text: errorMessage,
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
        if (bulkUpdateScores) {
            bulkUpdateScores.disabled = false;
            bulkUpdateScores.innerHTML = originalBtnContent || '<i class="ri-save-line me-1"></i> Save All Scores';
        }
    });
}

// Delete selected scores
function deleteSelectedScores() {
    const selectedCheckboxes = document.querySelectorAll('.score-checkbox:checked');
    if (!selectedCheckboxes.length) {
        Swal.fire({
            icon: 'info',
            title: 'No Selection',
            text: 'Please select at least one score to delete.',
            timer: 2000
        });
        return;
    }
    Swal.fire({
        title: 'Delete Selected Scores?',
        text: 'This will clear the selected scores. Are you sure?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, Delete!'
    }).then((result) => {
        if (result.isConfirmed) {
            selectedCheckboxes.forEach(checkbox => {
                const row = checkbox.closest('tr');
                if (row) {
                    ['ca1', 'ca2', 'ca3', 'exam'].forEach(field => {
                        const input = row.querySelector(`input[data-field="${field}"]`);
                        if (input) input.value = '';
                    });
                    updateRowTotal(row);
                }
            });
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: 'Selected scores have been cleared.',
                timer: 2000
            });
        }
    });
}

// Populate scores modal
function populateScoresModal() {
    const modalBody = document.querySelector('#scoresModal .modal-body');
    if (!modalBody) {
        console.error("Scores modal body not found");
        return;
    }
    ensureBroadsheetsArray();
    if (!window.broadsheets || !Array.isArray(window.broadsheets) || window.broadsheets.length === 0) {
        console.log("No broadsheets data available for modal");
        modalBody.innerHTML = `<div class="alert alert-info text-center">
            <i class="ri-information-line me-2"></i>
            No scores available to display.
        </div>`;
        return;
    }
    console.log("Populating scores modal with", window.broadsheets.length, "records");

    const cums = window.broadsheets.map(b => parseFloat(b.cum) || 0);
    const allZero = cums.length > 0 && cums.every(cum => cum === 0);

    let idToPos = {};
    if (allZero) {
        window.broadsheets.forEach(b => { idToPos[b.id] = "0th"; });
    } else {
        const sorted = window.broadsheets
            .map(b => ({id: b.id, cum: parseFloat(b.cum) || 0}))
            .sort((a, b) => b.cum - a.cum || a.id - b.id);

        let lastCum = null, lastPosition = 0, rank = 0;
        sorted.forEach((item, idx) => {
            rank++;
            if (lastCum !== null && item.cum === lastCum) {
                // tied
            } else {
                lastPosition = rank;
                lastCum = item.cum;
            }
            idToPos[item.id] = getOrdinalSuffix(lastPosition);
        });
    }

    let html = `
        <div class="table-responsive">
            <table class="table table-centered align-middle table-nowrap mb-0">
                <thead class="table-active">
                    <tr>
                        <th>SN</th><th>Admission No</th><th>Name</th>
                        <th>CA1</th><th>CA2</th><th>CA3</th><th>Exam</th>
                        <th>Total</th><th>BF</th><th>Cum</th><th>Grade</th><th>Position</th>
                    </tr>
                </thead>
                <tbody>`;
    window.broadsheets.forEach((broadsheet, idx) => {
        const ca1 = parseFloat(broadsheet.ca1) || 0;
        const ca2 = parseFloat(broadsheet.ca2) || 0;
        const ca3 = parseFloat(broadsheet.ca3) || 0;
        const exam = parseFloat(broadsheet.exam) || 0;
        const caAverage = (ca1 + ca2 + ca3) / 3;
        const total = (caAverage + exam) / 2;
        const bf = parseFloat(broadsheet.bf) || 0;
        const cum = window.term_id === 1 ? total : (bf + total) / 2;
        const grade = calculateGrade(cum);
        const name = `${broadsheet.fname || ''} ${broadsheet.lname || ''}`.trim() || 'Unknown';
        const admissionno = broadsheet.admissionno || '-';
        const picture = normalizePicturePath(broadsheet.picture);
        const imageUrl = `/storage/student_avatars/${picture}`;
        let position = idToPos[broadsheet.id] || "-";
        console.log(`Modal image for ${admissionno}: Original picture=${broadsheet.picture || 'none'}, Normalized=${picture}, URL=${imageUrl}`);

        html += `<tr>
            <td>${idx + 1}</td>
            <td class="admissionno">${admissionno}</td>
            <td class="name">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-2">
                        <img src="${imageUrl}" alt="${name}" class="rounded-circle w-100 student-image" data-bs-toggle="modal" data-bs-target="#imageViewModal" data-image="${imageUrl}" data-picture="${broadsheet.picture || 'none'}" onerror="this.src='/storage/student_avatars/unnamed.jpg'; console.log('Modal image failed to load for admissionno: ${admissionno}, picture: ${broadsheet.picture || 'none'}, attempted URL: ${imageUrl}');">
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fw-bold">${broadsheet.lname || ''}</span> ${broadsheet.fname || ''} ${broadsheet.mname || ''}
                    </div>
                </div>
            </td>
            <td>${ca1.toFixed(1)}</td>
            <td>${ca2.toFixed(1)}</td>
            <td>${ca3.toFixed(1)}</td>
            <td>${exam.toFixed(1)}</td>
            <td>${total.toFixed(1)}</td>
            <td>${bf.toFixed(2)}</td>
            <td>${cum.toFixed(2)}</td>
            <td>${grade}</td>
            <td>${position}</td>
        </tr>`;
    });
    html += `</tbody></table></div>`;
    modalBody.innerHTML = html;

    const images = modalBody.querySelectorAll('.student-image');
    images.forEach(img => {
        const src = img.src;
        img.src = '';
        img.src = src;
        console.log(`Forcing image load: ${src}`);
    });
}

// Bulk actions and modal initialization
function initializeBulkActions() {
    const bulkUpdateScores = document.getElementById('bulkUpdateScores');
    const selectAllScores = document.getElementById('selectAllScores');
    const clearAllScores = document.getElementById('clearAllScores');
    const checkAll = document.getElementById('checkAll');
    const scoresModal = document.getElementById('scoresModal');
    const imageViewModal = document.getElementById('imageViewModal');

    console.log('Initializing bulk actions:', {
        bulkUpdateScores: !!bulkUpdateScores,
        selectAllScores: !!selectAllScores,
        clearAllScores: !!clearAllScores,
        checkAll: !!checkAll,
        scoresModal: !!scoresModal,
        imageViewModal: !!imageViewModal
    });

    if (bulkUpdateScores) {
        bulkUpdateScores.addEventListener('click', e => {
            e.preventDefault();
            console.log('Bulk Update Scores button clicked');
            bulkSaveAllScores();
        });
    } else {
        console.warn('bulkUpdateScores button not found in DOM');
    }

    if (selectAllScores) {
        selectAllScores.addEventListener('click', () => {
            console.log('Select All Scores button clicked');
            document.querySelectorAll('.score-checkbox').forEach(checkbox => { checkbox.checked = true; });
            if (checkAll) checkAll.checked = true;
        });
    } else {
        console.warn('selectAllScores button not found in DOM');
    }

    if (clearAllScores) {
        clearAllScores.addEventListener('click', () => {
            console.log('Clear All Scores button clicked');
            Swal.fire({
                title: 'Clear All Scores?',
                text: 'This will clear all score inputs. Are you sure?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Clear All!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.querySelectorAll('.score-input').forEach(input => { input.value = ''; });
                    document.querySelectorAll('#scoresheetTableBody tr:not(#noDataRow)').forEach(row => { updateRowTotal(row); });
                    Swal.fire({
                        icon: 'success',
                        title: 'Cleared!',
                        text: 'All scores have been cleared.',
                        timer: 2000
                    });
                }
            });
        });
    } else {
        console.warn('clearAllScores button not found in DOM');
    }

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            console.log('Check All checkbox changed:', this.checked);
            document.querySelectorAll('.score-checkbox').forEach(checkbox => { checkbox.checked = this.checked; });
        });
    } else {
        console.warn('checkAll checkbox not found in DOM');
    }

    if (scoresModal) {
        scoresModal.addEventListener('show.bs.modal', () => {
            console.log('Scores modal opening, calling populateScoresModal');
            populateScoresModal();
        });
    } else {
        console.warn('scoresModal not found in DOM');
    }

    if (imageViewModal) {
        imageViewModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const imageSrc = button.getAttribute('data-image') || '/storage/student_avatars/unnamed.jpg';
            const pictureName = button.getAttribute('data-picture') || 'none';
            const modalImage = this.querySelector('#enlargedImage');
            console.log(`ImageViewModal: Setting image src=${imageSrc}, picture=${pictureName}`);
            modalImage.src = imageSrc;
            modalImage.onerror = () => {
                modalImage.src = '/storage/student_avatars/unnamed.jpg';
                console.log(`Enlarged image failed to load, picture: ${pictureName}, attempted URL: ${imageSrc}`);
            };
        });
    } else {
        console.warn('imageViewModal not found in DOM');
    }

    const searchInput = document.getElementById('searchInput');
    const clearSearch = document.getElementById('clearSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            console.log('Search input changed:', this.value);
            const search = this.value.trim().toLowerCase();
            document.querySelectorAll('#scoresheetTableBody tr:not(#noDataRow)').forEach(row => {
                const adm = row.querySelector('.admissionno')?.textContent.toLowerCase() || '';
                const nme = row.querySelector('.name')?.textContent.toLowerCase() || '';
                if (search === '' || adm.includes(search) || nme.includes(search)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    } else {
        console.warn('searchInput not found in DOM');
    }

    if (clearSearch) {
        clearSearch.addEventListener('click', function () {
            console.log('Clear Search button clicked');
            if (searchInput) searchInput.value = '';
            document.querySelectorAll('#scoresheetTableBody tr').forEach(row => row.style.display = '');
        });
    } else {
        console.warn('clearSearch button not found in DOM');
    }
}

// Score input initialization with override default 0
function initializeScoreInputs() {
    const scoreInputs = document.querySelectorAll('.score-input');
    scoreInputs.forEach(input => {
        // Clear default 0 on focus
        input.addEventListener('focus', function () {
            if (this.value === '0') {
                this.value = '';
            }
        });

        input.addEventListener('input', function () {
            const row = input.closest('tr');
            if (row) updateRowTotal(row);
        });

        input.addEventListener('blur', function () {
            const value = parseFloat(this.value);
            if (this.value === '') {
                this.value = ''; // Keep empty if user clears it
                this.classList.remove('is-invalid', 'is-valid');
            } else if (isNaN(value) || value < 0 || value > 100) {
                this.classList.add('is-invalid');
                this.focus();
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });
}

// Init
document.addEventListener("DOMContentLoaded", function () {
    ensureBroadsheetsArray();
    initializeScoreInputs();
    initializeBulkActions();
    document.querySelectorAll('#scoresheetTableBody tr:not(#noDataRow)').forEach(row => {
        updateRowTotal(row);
    });
    forceUpdatePositions();
});

window.bulkSaveAllScores = bulkSaveAllScores;
window.deleteSelectedScores = deleteSelectedScores;
window.updateRowTotal = updateRowTotal;
window.forceUpdatePositions = forceUpdatePositions;
window.populateScoresModal = populateScoresModal;