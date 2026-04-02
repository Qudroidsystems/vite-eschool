console.log("subjectvetting.init.js is loaded and executing!");

let chartInitCount = 0;
let currentView = 'table';
let currentCardPage = 1;
const cardsPerPage = 9;

let currentTermFilter = '';
let currentSessionFilter = '';
let currentStatusFilter = 'all';

let subjectVettingList = null;
let vettingStatusChart = null;

// Dependencies check
try {
    if (typeof axios === 'undefined') throw new Error("Axios is not loaded");
    if (typeof Swal === 'undefined') throw new Error("SweetAlert2 is not loaded");
    if (typeof bootstrap === 'undefined') throw new Error("Bootstrap is not loaded");
    if (typeof List === 'undefined') throw new Error("List.js is not loaded");
    if (typeof Chart === 'undefined') throw new Error("Chart.js is not loaded");
    console.log("All dependencies loaded successfully");
} catch (error) {
    console.error("Dependency check failed:", error);
}

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

// ====================== HELPER FUNCTIONS ======================

function debounce(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// ====================== STATS & CHART UPDATE (FIXED) ======================

function updateStatsFromTable() {
    const visibleRows = document.querySelectorAll('#kt_subject_vetting_table tbody tr:not(.noresult):not([style*="display: none"])');

    let total = 0, pending = 0, completed = 0, rejected = 0;

    visibleRows.forEach(row => {
        total++;
        const status = (row.getAttribute('data-status') || 'pending').toLowerCase().trim();
        if (status === 'pending') pending++;
        else if (status === 'completed') completed++;
        else if (status === 'rejected') rejected++;
    });

    // Update Stats Cards
    document.getElementById('stat-total').textContent = total;
    document.getElementById('stat-pending').textContent = pending;
    document.getElementById('stat-completed').textContent = completed;
    document.getElementById('stat-rejected').textContent = rejected;

    // Update footer
    const totalFooter = document.getElementById('total-records-footer');
    const showingEl = document.getElementById('showing-records');

    if (totalFooter) totalFooter.textContent = total;
    if (showingEl && subjectVettingList) {
        showingEl.textContent = Math.min(visibleRows.length, subjectVettingList.page || 10);
    }
}

function updateChartFromTable() {
    if (!vettingStatusChart) return;

    const visibleRows = document.querySelectorAll('#kt_subject_vetting_table tbody tr:not(.noresult):not([style*="display: none"])');

    let pending = 0, completed = 0, rejected = 0;

    visibleRows.forEach(row => {
        const status = (row.getAttribute('data-status') || 'pending').toLowerCase().trim();
        if (status === 'pending') pending++;
        else if (status === 'completed') completed++;
        else if (status === 'rejected') rejected++;
    });

    vettingStatusChart.data.datasets[0].data = [pending, completed, rejected];
    vettingStatusChart.update('none'); // smooth update without animation
}

// ====================== FILTER FUNCTIONS ======================

function filterTableByTermAndSession() {
    const rows = document.querySelectorAll('#kt_subject_vetting_table tbody tr:not(.noresult)');

    rows.forEach(row => {
        const rowTermId = row.getAttribute('data-term');
        const rowSessionId = row.getAttribute('data-session');

        let showRow = true;

        if (currentTermFilter && rowTermId !== currentTermFilter) showRow = false;
        if (currentSessionFilter && rowSessionId !== currentSessionFilter && showRow) showRow = false;

        row.style.display = showRow ? '' : 'none';
    });

    if (subjectVettingList) subjectVettingList.update();

    updateStatsFromTable();
    updateChartFromTable();
    updatePaginationVisibility();
}

function updatePaginationVisibility() {
    const visibleRows = document.querySelectorAll('#kt_subject_vetting_table tbody tr:not(.noresult):not([style*="display: none"])').length;
    const paginationElement = document.getElementById('pagination-element');
    if (paginationElement) {
        paginationElement.style.display = visibleRows > 10 ? '' : 'none';
    }
}

// ====================== INITIALIZE CHART ======================

function initializeVettingStatusChart() {
    chartInitCount++;
    const ctx = document.getElementById('vettingStatusChart')?.getContext('2d');
    if (!ctx) return;

    if (vettingStatusChart) vettingStatusChart.destroy();

    vettingStatusChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Pending', 'Completed', 'Rejected'],
            datasets: [{
                label: 'Vetting Assignments',
                data: [0, 0, 0],
                backgroundColor: ['#dc3545', '#28a745', '#ffc107'],
                borderColor: ['#c82333', '#218838', '#e0a800'],
                borderWidth: 1,
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } },
                x: { grid: { display: false } }
            },
            plugins: { legend: { display: false } }
        }
    });
}

// ====================== STATS CARD CLICK ======================

function initializeStatsCardClick() {
    document.querySelectorAll('.stat-card-clickable').forEach(card => {
        card.addEventListener('click', function () {
            const status = this.getAttribute('data-status');

            // Remove active from all cards
            document.querySelectorAll('.stat-card-clickable').forEach(c => c.classList.remove('active-stat'));
            this.classList.add('active-stat');

            const rows = document.querySelectorAll('#kt_subject_vetting_table tbody tr:not(.noresult)');

            if (status === 'all') {
                rows.forEach(row => {
                    if (row.style.display !== 'none') row.style.display = '';
                });
            } else {
                rows.forEach(row => {
                    if (row.style.display !== 'none') {
                        row.style.display = (row.getAttribute('data-status') === status) ? '' : 'none';
                    }
                });
            }

            if (subjectVettingList) subjectVettingList.update();

            updateStatsFromTable();
            updateChartFromTable();
            updatePaginationVisibility();

            if (currentView === 'card') {
                setTimeout(() => renderCardView(subjectVettingList?.items || []), 100);
            }
        });
    });
}

// ====================== LIST.JS INITIALIZATION ======================

function initializeListJS() {
    if (subjectVettingList) subjectVettingList.clear();

    const hasRows = document.querySelectorAll('#kt_subject_vetting_table tbody tr:not(.noresult)').length > 0;

    if (!hasRows) {
        document.getElementById('showing-records').textContent = 0;
        document.getElementById('total-records-footer').textContent = 0;
        return;
    }

    subjectVettingList = new List('subjectVettingList', {
        valueNames: ['sn', 'vetting_username', 'subjectname', 'sclass', 'schoolarm', 'teachername', 'termname', 'sessionname', 'status', 'datereg'],
        page: 10,
        pagination: {
            innerWindow: 2,
            outerWindow: 1,
            paginationClass: "listjs-pagination"
        },
        listClass: 'list'
    });

    subjectVettingList.on('updated', function () {
        updateStatsFromTable();
        updateChartFromTable();

        const noResultRow = document.querySelector('.noresult');
        if (noResultRow) {
            noResultRow.style.display = subjectVettingList.visibleItems.length === 0 ? 'table-row' : 'none';
        }

        initializeCheckboxes();
    });

    subjectVettingList.update();
}

// ====================== SEARCH ======================

function filterData() {
    if (!subjectVettingList) return;

    const searchValue = document.querySelector(".search-box input.search")?.value.trim() || "";
    subjectVettingList.search(searchValue);

    setTimeout(() => {
        updateStatsFromTable();
        updateChartFromTable();
    }, 10);
}

// ====================== TERM & SESSION FILTER ======================

function initializeTermAndSessionFilters() {
    const termFilter = document.getElementById('term-filter-stats');
    const sessionFilter = document.getElementById('session-filter-stats');
    const resetBtn = document.getElementById('reset-stats-btn');

    if (termFilter) {
        termFilter.addEventListener('change', () => {
            currentTermFilter = termFilter.value;
            filterTableByTermAndSession();
        });
    }

    if (sessionFilter) {
        sessionFilter.addEventListener('change', () => {
            currentSessionFilter = sessionFilter.value;
            filterTableByTermAndSession();
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            if (termFilter) termFilter.value = '';
            if (sessionFilter) sessionFilter.value = '';
            currentTermFilter = '';
            currentSessionFilter = '';
            filterTableByTermAndSession();
        });
    }
}

// ====================== CHECKBOXES ======================

function initializeCheckboxes() {
    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
    checkboxes.forEach(cb => {
        cb.removeEventListener('change', handleCheckboxChange);
        cb.addEventListener('change', handleCheckboxChange);
    });
}

function handleCheckboxChange(e) {
    const row = e.target.closest("tr");
    row.classList.toggle("table-active", e.target.checked);

    const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
    const removeActions = document.getElementById("remove-actions");
    if (removeActions) removeActions.classList.toggle("d-none", checkedCount === 0);

    const checkAll = document.getElementById("checkAll");
    if (checkAll) {
        checkAll.checked = document.querySelectorAll('tbody input[name="chk_child"]').length === checkedCount;
    }
}

// ====================== VIEW TOGGLE (Table / Card) ======================

function initializeViewToggle() {
    const tableViewBtn = document.getElementById('tableViewBtn');
    const cardViewBtn = document.getElementById('cardViewBtn');
    const tableView = document.querySelector('.table-view-container');
    const cardView = document.getElementById('cardViewContainer');

    if (!tableViewBtn || !cardViewBtn) return;

    tableViewBtn.addEventListener('click', () => {
        currentView = 'table';
        tableViewBtn.classList.add('active');
        cardViewBtn.classList.remove('active');
        tableView.classList.remove('hide');
        cardView.classList.remove('active');
    });

    cardViewBtn.addEventListener('click', () => {
        currentView = 'card';
        cardViewBtn.classList.add('active');
        tableViewBtn.classList.remove('active');
        tableView.classList.add('hide');
        cardView.classList.add('active');

        if (subjectVettingList) {
            renderCardView(subjectVettingList.items);
        }
    });
}

// ====================== CARD VIEW RENDERING ======================

function renderCardView(items) {
    const cardsContainer = document.getElementById('cardsContainer');
    if (!cardsContainer) return;

    const visibleItems = items.filter(item => {
        const row = item.elm;
        return row && row.style.display !== 'none';
    });

    const totalItems = visibleItems.length;
    const totalPages = Math.ceil(totalItems / cardsPerPage);
    if (currentCardPage > totalPages) currentCardPage = totalPages || 1;

    const startIndex = (currentCardPage - 1) * cardsPerPage;
    const currentItems = visibleItems.slice(startIndex, startIndex + cardsPerPage);

    cardsContainer.innerHTML = '';

    if (currentItems.length === 0) {
        cardsContainer.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="ri-inbox-line fs-48 text-muted"></i>
                <h5 class="mt-3">No Subject Vetting Assignments Found</h5>
            </div>`;
        return;
    }

    currentItems.forEach(item => {
        const statusText = (item._values.status || 'Pending').trim();
        const statusLower = statusText.toLowerCase();
        const statusClass = statusLower.includes('completed') ? 'completed' :
                           (statusLower.includes('pending') ? 'pending' : 'rejected');
        const statusIcon = statusClass === 'completed' ? 'ri-checkbox-circle-line' :
                          (statusClass === 'pending' ? 'ri-time-line' : 'ri-close-circle-line');

        const cardHTML = `
            <div class="col-md-6 col-xl-4">
                <div class="vetting-card ${statusClass}-card">
                    <div class="card-header-info">
                        <div class="staff-info-card">
                            <div class="staff-avatar-card">${(item._values.vetting_username || 'U').charAt(0).toUpperCase()}</div>
                            <div>
                                <h6 class="mb-0">${item._values.vetting_username || 'N/A'}</h6>
                                <small class="text-muted">Vetting Staff</small>
                            </div>
                        </div>
                        <span class="badge-status ${statusClass === 'pending' ? 'badge-pending' : statusClass === 'completed' ? 'badge-completed' : 'badge-rejected'}">
                            <i class="${statusIcon} me-1 fs-10"></i> ${statusText}
                        </span>
                    </div>
                    <div class="card-details">
                        <div class="detail-item"><i class="ri-book-open-line"></i> <strong>Subject:</strong> ${item._values.subjectname || 'N/A'}</div>
                        <div class="detail-item"><i class="ri-group-line"></i> <strong>Class:</strong> ${item._values.sclass || 'N/A'} ${item._values.schoolarm ? `(${item._values.schoolarm})` : ''}</div>
                        <div class="detail-item"><i class="ri-user-line"></i> <strong>Teacher:</strong> ${item._values.teachername || 'N/A'}</div>
                        <div class="detail-item"><i class="ri-calendar-line"></i> <strong>Term:</strong> ${item._values.termname || 'N/A'}</div>
                        <div class="detail-item"><i class="ri-calendar-event-line"></i> <strong>Session:</strong> ${item._values.sessionname || 'N/A'}</div>
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-sm btn-light edit-card-btn" data-sn="${item._values.sn}"><i class="ri-pencil-line"></i> Edit</button>
                        <button class="btn btn-sm btn-light text-danger delete-card-btn" data-sn="${item._values.sn}"><i class="ri-delete-bin-line"></i> Delete</button>
                    </div>
                </div>
            </div>`;

        cardsContainer.insertAdjacentHTML('beforeend', cardHTML);
    });

    document.getElementById('card-showing-records').textContent = currentItems.length;
    document.getElementById('card-total-records').textContent = totalItems;
}

// ====================== DOMContentLoaded ======================

document.addEventListener('DOMContentLoaded', function () {
    // Initialize core components
    initializeListJS();
    initializeVettingStatusChart();
    initializeCheckboxes();
    initializeTermAndSessionFilters();
    initializeStatsCardClick();
    initializeViewToggle();

    // Initial stats & chart update
    updateStatsFromTable();
    updateChartFromTable();

    // Search handler
    const searchInput = document.querySelector(".search-box input.search");
    if (searchInput) {
        searchInput.addEventListener("input", debounce(filterData, 300));
    }

    // Check All
    const checkAll = document.getElementById("checkAll");
    if (checkAll) {
        checkAll.addEventListener("click", function () {
            const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
                cb.closest("tr").classList.toggle("table-active", this.checked);
            });
            const removeActions = document.getElementById("remove-actions");
            if (removeActions) removeActions.classList.toggle("d-none", !this.checked);
        });
    }

    console.log("Subject Vetting Management initialized successfully");
});

