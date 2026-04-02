console.log("subjectvetting.init.js - FIXED VERSION LOADED");

let currentView = 'table';
let currentCardPage = 1;
const cardsPerPage = 9;

let currentTermFilter = '';
let currentSessionFilter = '';
let subjectVettingList = null;
let vettingStatusChart = null;

// ====================== CORE UPDATE FUNCTIONS ======================

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

    document.getElementById('stat-total').textContent = total;
    document.getElementById('stat-pending').textContent = pending;
    document.getElementById('stat-completed').textContent = completed;
    document.getElementById('stat-rejected').textContent = rejected;

    // Update footer
    if (document.getElementById('total-records-footer')) {
        document.getElementById('total-records-footer').textContent = total;
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
    vettingStatusChart.update('none');
}

// ====================== FILTER FUNCTIONS ======================

function filterTableByTermAndSession() {
    const rows = document.querySelectorAll('#kt_subject_vetting_table tbody tr:not(.noresult)');

    rows.forEach(row => {
        const rowTerm = row.getAttribute('data-term');
        const rowSession = row.getAttribute('data-session');

        let show = true;

        if (currentTermFilter && rowTerm !== currentTermFilter) show = false;
        if (currentSessionFilter && rowSession !== currentSessionFilter && show) show = false;

        row.style.display = show ? '' : 'none';
    });

    if (subjectVettingList) {
        subjectVettingList.update();
    }

    // CRITICAL: Update everything after filter
    updateStatsFromTable();
    updateChartFromTable();

    if (currentView === 'card') {
        setTimeout(() => {
            renderCardView(subjectVettingList ? subjectVettingList.items : []);
        }, 50);
    }

    updatePaginationVisibility();
}

function updatePaginationVisibility() {
    const visibleCount = document.querySelectorAll('#kt_subject_vetting_table tbody tr:not(.noresult):not([style*="display: none"])').length;
    const paginationEl = document.getElementById('pagination-element');
    if (paginationEl) {
        paginationEl.style.display = visibleCount > 10 ? '' : 'none';
    }
}

// ====================== CHART INITIALIZATION ======================

function initializeVettingStatusChart() {
    const ctx = document.getElementById('vettingStatusChart')?.getContext('2d');
    if (!ctx) return;

    if (vettingStatusChart) vettingStatusChart.destroy();

    vettingStatusChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Pending', 'Completed', 'Rejected'],
            datasets: [{
                label: 'Assignments',
                data: [0, 0, 0],
                backgroundColor: ['#dc3545', '#28a745', '#ffc107'],
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            },
            plugins: { legend: { display: false } }
        }
    });
}

// ====================== LIST.JS ======================

function initializeListJS() {
    if (subjectVettingList) subjectVettingList.clear();

    subjectVettingList = new List('subjectVettingList', {
        valueNames: ['sn', 'vetting_username', 'subjectname', 'sclass', 'schoolarm', 'teachername', 'termname', 'sessionname', 'status', 'datereg'],
        page: 10,
        pagination: { paginationClass: "listjs-pagination" }
    });

    subjectVettingList.on('updated', function () {
        updateStatsFromTable();
        updateChartFromTable();

        if (currentView === 'card') {
            renderCardView(subjectVettingList.items);
        }
    });
}

// ====================== CARD VIEW ======================

function renderCardView(items = []) {
    const container = document.getElementById('cardsContainer');
    if (!container) return;

    // Only show visible items
    const visibleItems = items.filter(item => {
        const row = item.elm;
        return row && row.style.display !== 'none';
    });

    const totalItems = visibleItems.length;
    const totalPages = Math.ceil(totalItems / cardsPerPage);
    if (currentCardPage > totalPages) currentCardPage = Math.max(1, totalPages);

    const start = (currentCardPage - 1) * cardsPerPage;
    const currentItems = visibleItems.slice(start, start + cardsPerPage);

    container.innerHTML = '';

    if (currentItems.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="ri-inbox-line fs-48 text-muted"></i>
                <h5 class="mt-3">No assignments found</h5>
                <p class="text-muted">Try changing the filters</p>
            </div>`;
        return;
    }

    currentItems.forEach(item => {
        const status = (item._values.status || 'pending').toLowerCase();
        const statusClass = status.includes('completed') ? 'completed' :
                           (status.includes('pending') ? 'pending' : 'rejected');
        const icon = status.includes('completed') ? 'ri-checkbox-circle-line' :
                    (status.includes('pending') ? 'ri-time-line' : 'ri-close-circle-line');

        const cardHTML = `
            <div class="col-md-6 col-xl-4">
                <div class="vetting-card ${statusClass}-card">
                    <div class="card-header-info">
                        <div class="staff-info-card">
                            <div class="staff-avatar-card">${(item._values.vetting_username || 'U')[0].toUpperCase()}</div>
                            <div><h6 class="mb-0">${item._values.vetting_username || 'N/A'}</h6></div>
                        </div>
                        <span class="badge-status ${statusClass === 'pending' ? 'badge-pending' : statusClass === 'completed' ? 'badge-completed' : 'badge-rejected'}">
                            <i class="${icon} me-1"></i>${item._values.status || 'Pending'}
                        </span>
                    </div>
                    <div class="card-details">
                        <div class="detail-item"><i class="ri-book-open-line"></i> <strong>Subject:</strong> ${item._values.subjectname || 'N/A'}</div>
                        <div class="detail-item"><i class="ri-group-line"></i> <strong>Class:</strong> ${item._values.sclass || 'N/A'}</div>
                        <div class="detail-item"><i class="ri-user-line"></i> <strong>Teacher:</strong> ${item._values.teachername || 'N/A'}</div>
                        <div class="detail-item"><i class="ri-calendar-line"></i> <strong>Term:</strong> ${item._values.termname || 'N/A'}</div>
                        <div class="detail-item"><i class="ri-calendar-event-line"></i> <strong>Session:</strong> ${item._values.sessionname || 'N/A'}</div>
                    </div>
                </div>
            </div>`;

        container.insertAdjacentHTML('beforeend', cardHTML);
    });

    document.getElementById('card-showing-records').textContent = currentItems.length;
    document.getElementById('card-total-records').textContent = totalItems;
}

// ====================== TERM & SESSION FILTERS ======================

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
            currentTermFilter = '';
            currentSessionFilter = '';
            if (termFilter) termFilter.value = '';
            if (sessionFilter) sessionFilter.value = '';
            filterTableByTermAndSession();
        });
    }
}

// ====================== VIEW TOGGLE ======================

function initializeViewToggle() {
    const tableBtn = document.getElementById('tableViewBtn');
    const cardBtn = document.getElementById('cardViewBtn');
    const tableContainer = document.querySelector('.table-view-container');
    const cardContainer = document.getElementById('cardViewContainer');

    if (!tableBtn || !cardBtn) return;

    tableBtn.addEventListener('click', () => {
        currentView = 'table';
        tableBtn.classList.add('active');
        cardBtn.classList.remove('active');
        tableContainer.classList.remove('hide');
        cardContainer.classList.remove('active');
    });

    cardBtn.addEventListener('click', () => {
        currentView = 'card';
        cardBtn.classList.add('active');
        tableBtn.classList.remove('active');
        tableContainer.classList.add('hide');
        cardContainer.classList.add('active');

        if (subjectVettingList) {
            renderCardView(subjectVettingList.items);
        }
    });
}

// ====================== INITIALIZATION ======================

document.addEventListener('DOMContentLoaded', function () {
    initializeListJS();
    initializeVettingStatusChart();
    initializeTermAndSessionFilters();
    initializeViewToggle();

    // Initial update
    updateStatsFromTable();
    updateChartFromTable();

    // Search
    const searchInput = document.querySelector(".search-box input.search");
    if (searchInput) {
        searchInput.addEventListener("input", () => {
            if (subjectVettingList) {
                subjectVettingList.search(searchInput.value);
                setTimeout(() => {
                    updateStatsFromTable();
                    updateChartFromTable();
                    if (currentView === 'card') renderCardView(subjectVettingList.items);
                }, 10);
            }
        });
    }

    console.log("✅ Subject Vetting page initialized with fixed card & stats update");
});
