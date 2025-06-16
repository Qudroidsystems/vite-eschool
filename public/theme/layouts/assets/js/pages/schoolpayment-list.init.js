console.log("schoolpayment.init.js loaded at", new Date().toISOString());

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

// Initialize List.js
let studentList = null;
function initializeListJs() {
    const table = document.getElementById('studentTable');
    if (!table) {
        console.error("List.js: #studentTable not found in DOM");
        return;
    }
    const tbody = table.querySelector('tbody');
    if (!tbody || tbody.children.length === 0) {
        console.warn("List.js: No rows found in #studentTable tbody");
        return;
    }
    try {
        studentList = new List('studentList', {
            valueNames: ['admission_no', 'name', 'gender'],
            listClass: 'list'
        });
        console.log("List.js initialized successfully");
    } catch (error) {
        console.error("List.js initialization failed:", error);
    }
}

// Custom search function with debounce
function debounce(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

function customSearch(searchString) {
    if (!studentList) return;
    studentList.search(searchString, ['admission_no', 'name']);
}

const triggerSearch = debounce(() => {
    const searchInput = document.querySelector('.search');
    if (searchInput) customSearch(searchInput.value.trim());
}, 300);

// Initialize search
function initializeSearch() {
    const searchInput = document.querySelector('.search');
    if (searchInput) {
        searchInput.addEventListener('input', triggerSearch);
    }
}

// Initialize gender filter
function initializeFilters() {
    const genderSelect = document.getElementById('idGender');
    if (genderSelect) {
        genderSelect.addEventListener('change', filterData);
    }
}

// Filter data based on selected criteria
function filterData() {
    if (!studentList) return;

    const genderValue = document.getElementById('idGender')?.value;
    
    studentList.filter(item => {
        const genderMatch = genderValue === 'all' || item.values().gender === genderValue;
        return genderMatch;
    });
}

// Initialize "Check All" functionality
function initializeCheckAll() {
    const checkAll = document.getElementById('checkAll');
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            document.querySelectorAll('tbody input[name="chk_child"]')
                .forEach(checkbox => checkbox.checked = this.checked);
        });
    }
}

// Initialize individual checkboxes
function initializeCheckboxes() {
    document.querySelectorAll('tbody input[name="chk_child"]').forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            const checkAll = document.getElementById('checkAll');
            if (checkAll) {
                checkAll.checked = Array.from(document.querySelectorAll('tbody input[name="chk_child"]'))
                    .every(cb => cb.checked);
            }
        });
    });
}

// Document ready handler
document.addEventListener('DOMContentLoaded', () => {
    initializeListJs();
    initializeSearch();
    initializeFilters();
    initializeCheckAll();
    initializeCheckboxes();
});

// Expose necessary functions globally
window.filterData = filterData;
window.triggerSearch = triggerSearch;