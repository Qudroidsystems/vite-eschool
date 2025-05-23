console.log("classteacher.init.js is loaded and executing!");

// Verify dependencies
try {
    if (typeof axios === 'undefined') throw new Error("Axios is not loaded");
    if (typeof Swal === 'undefined') throw new Error("SweetAlert2 is not loaded");
    if (typeof bootstrap === 'undefined') throw new Error("Bootstrap is not loaded");
    if (typeof List === 'undefined') throw new Error("List.js is not loaded");
    console.log("All dependencies loaded successfully");
} catch (error) {
    console.error("Dependency check failed:", error);
}

// Set Axios CSRF token globally
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
if (!csrfToken) console.warn("CSRF token not found");

// Debounce function for search input
function debounce(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// Check all checkbox
const checkAll = document.getElementById("checkAll");
if (checkAll) {
    checkAll.addEventListener("click", function () {
        console.log("CheckAll clicked");
        const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
        checkboxes.forEach((checkbox) => {
            checkbox.checked = this.checked;
            const row = checkbox.closest("tr");
            row.classList.toggle("table-active", this.checked);
        });
        const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
        const removeActions = document.getElementById("remove-actions");
        if (removeActions) {
            removeActions.classList.toggle("d-none", checkedCount === 0);
        }
    });
}

// Form fields
const addIdField = document.getElementById("add-id-field");
const addStaffIdField = document.getElementById("staffid");
const addSchoolClassIdField = document.getElementById("schoolclassid");
const addTermIdField = document.getElementById("termid");
const addSessionIdField = document.getElementById("sessionid");
const editIdField = document.getElementById("edit-id-field");
const editStaffIdField = document.getElementById("edit-staffid");
const editSchoolClassIdField = document.getElementById("edit-schoolclassid");
const editTermIdField = document.getElementById("edit-termid");
const editSessionIdField = document.getElementById("edit-sessionid");

// Checkbox handling
function initializeCheckboxes() {
    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
    checkboxes.forEach((checkbox) => {
        checkbox.removeEventListener("change", handleCheckboxChange);
        checkbox.addEventListener("change", handleCheckboxChange);
    });
}

function handleCheckboxChange(e) {
    console.log("Checkbox changed:", e.target.checked);
    const row = e.target.closest("tr");
    row.classList.toggle("table-active", e.target.checked);
    const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
    const removeActions = document.getElementById("remove-actions");
    if (removeActions) {
        removeActions.classList.toggle("d-none", checkedCount === 0);
    }
    const allCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]');
    if (checkAll) {
        checkAll.checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCount;
    }
}

// Event delegation for edit, remove, and pagination buttons
document.addEventListener('click', function (e) {
    const editBtn = e.target.closest('.edit-item-btn');
    const removeBtn = e.target.closest('.remove-item-btn');
    const paginationLink = e.target.closest('.pagination-prev, .pagination-next, .pagination .page-link');
    if (editBtn) {
        handleEditClick(e, editBtn);
    } else if (removeBtn) {
        handleRemoveClick(e, removeBtn);
    } else if (paginationLink) {
        e.preventDefault();
        const url = paginationLink.getAttribute('data-url');
        if (url) fetchPage(url);
    }
});

// Fetch paginated data
function fetchPage(url) {
    if (!url) return;
    console.log("Fetching page:", url);
    axios.get(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(function (response) {
        console.log("Fetch page success:", response.data);
        // Extract table body and pagination from response HTML
        const parser = new DOMParser();
        const doc = parser.parseFromString(response.data.html, 'text/html');
        const newTbody = doc.querySelector('#kt_roles_view_table tbody');
        const newPagination = doc.querySelector('#pagination-element');
        const newBadge = doc.querySelector('.badge.bg-dark-subtle');
        if (newTbody && newPagination && newBadge) {
            document.querySelector('#kt_roles_view_table tbody').innerHTML = newTbody.innerHTML;
            document.querySelector('#pagination-element').outerHTML = newPagination.outerHTML;
            document.querySelector('.badge.bg-dark-subtle').outerHTML = newBadge.outerHTML;
            if (classTeacherList) {
                classTeacherList.reIndex();
            }
            initializeCheckboxes();
            document.querySelector("#pagination-element .text-muted").innerHTML =
                `Showing <span class="fw-semibold">${response.data.count}</span> of <span class="fw-semibold">${response.data.total}</span> Results`;
            // Update noresult display
            const noResult = document.querySelector(".noresult");
            const rowCount = document.querySelectorAll("#kt_roles_view_table tbody tr").length;
            if (noResult) {
                noResult.style.display = rowCount === 0 ? "block" : "none";
            }
        } else {
            console.error("Required elements not found in response");
        }
    }).catch(function (error) {
        console.error("Error fetching page:", error);
        Swal.fire({
            position: "center",
            icon: "error",
            title: "Error loading page",
            text: error.response?.data?.message || "An error occurred",
            showConfirmButton: true
        });
    });
}

// Delete single class teacher
function handleRemoveClick(e, button) {
    e.preventDefault();
    console.log("Remove button clicked");
    const itemId = button.closest("tr").querySelector(".id")?.getAttribute("data-id");
    const deleteUrl = button.closest("tr").getAttribute("data-url");
    if (!itemId || !deleteUrl) {
        console.error("Item ID or delete URL not found");
        return;
    }
    const modal = new bootstrap.Modal(document.getElementById("deleteRecordModal"));
    modal.show();
    console.log("Delete modal opened");

    const deleteButton = document.getElementById("delete-record");
    if (deleteButton) {
        deleteButton.onclick = function () {
            console.log("Deleting class teacher:", itemId);
            axios.delete(deleteUrl)
                .then(function (response) {
                    console.log("Delete success:", response.data);
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: response.data.message || "Class Teacher deleted successfully!",
                        showConfirmButton: false,
                        timer: 2000,
                        showCloseButton: true
                    });
                    if (classTeacherList) {
                        classTeacherList.remove("id", itemId);
                    }
                    const row = document.querySelector(`tr[data-id="${itemId}"]`);
                    if (row) row.remove();
                    modal.hide();
                    // Update badge
                    const badge = document.querySelector('.badge.bg-dark-subtle');
                    if (badge) {
                        const currentTotal = parseInt(badge.textContent);
                        badge.textContent = currentTotal - 1;
                    }
                    // Update noresult display
                    const noResult = document.querySelector(".noresult");
                    const rowCount = document.querySelectorAll("#kt_roles_view_table tbody tr").length;
                    if (noResult) {
                        noResult.style.display = rowCount === 0 ? "block" : "none";
                    } else if (rowCount === 0) {
                        document.querySelector("#kt_roles_view_table tbody").innerHTML =
                            '<tr><td colspan="9" class="noresult" style="display: block;">No results found</td></tr>';
                    }
                    // Fetch previous page if table is empty and pagination exists
                    if (rowCount === 0 && document.querySelector("#pagination-element .pagination-prev")) {
                        const prevUrl = document.querySelector("#pagination-element .pagination-prev").getAttribute("data-url");
                        console.log("Fetching previous page:", prevUrl);
                        fetchPage(prevUrl);
                    }
                })
                .catch(function (error) {
                    console.error("Delete error:", error.response?.data || error);
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Error deleting class teacher",
                        text: error.response?.data?.message || "An error occurred",
                        showConfirmButton: true
                    });
                    modal.hide();
                });
        };
    }
}

// Edit class teacher
function handleEditClick(e, button) {
    e.preventDefault();
    console.log("Edit button clicked");
    const itemId = button.closest("tr").querySelector(".id")?.getAttribute("data-id");
    const tr = button.closest("tr");
    if (!itemId) {
        console.error("Item ID not found");
        return;
    }
    if (editIdField) editIdField.value = itemId;
    if (editStaffIdField) editStaffIdField.value = tr.querySelector(".staffname")?.getAttribute("data-staffid") || "";
    if (editSchoolClassIdField) editSchoolClassIdField.value = tr.querySelector(".schoolclass")?.getAttribute("data-classid") || "";
    if (editTermIdField) editTermIdField.value = tr.querySelector(".term")?.getAttribute("data-termid") || "";
    if (editSessionIdField) editSessionIdField.value = tr.querySelector(".session")?.getAttribute("data-sessionid") || "";
    try {
        const modal = new bootstrap.Modal(document.getElementById("editModal"));
        modal.show();
        console.log("Edit modal opened");
    } catch (error) {
        console.error("Error opening edit modal:", error);
        Swal.fire({
            position: "center",
            icon: "error",
            title: "Error opening edit modal",
            text: "Please try again or contact support.",
            showConfirmButton: true
        });
    }
}

// Clear form fields
function clearAddFields() {
    if (addIdField) addIdField.value = "";
    if (addStaffIdField) addStaffIdField.value = "";
    if (addSchoolClassIdField) addSchoolClassIdField.value = "";
    if (addTermIdField) addTermIdField.value = "";
    if (addSessionIdField) addSessionIdField.value = "";
}

function clearEditFields() {
    if (editIdField) editIdField.value = "";
    if (editStaffIdField) editStaffIdField.value = "";
    if (editSchoolClassIdField) editSchoolClassIdField.value = "";
    if (editTermIdField) editTermIdField.value = "";
    if (editSessionIdField) editSessionIdField.value = "";
}

// Delete multiple class teachers
function deleteMultiple() {
    console.log("Delete multiple triggered");
    const ids_array = [];
    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]:checked');
    checkboxes.forEach((checkbox) => {
        const id = checkbox.closest("tr").querySelector(".id")?.getAttribute("data-id");
        if (id) ids_array.push(id);
    });
    if (ids_array.length === 0) {
        Swal.fire({
            title: "Please select at least one checkbox",
            confirmButtonClass: "btn btn-info",
            buttonsStyling: false,
            showCloseButton: true
        });
        return;
    }
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn btn-primary w-xs me-2 mt-2",
        cancelButtonClass: "btn btn-danger w-xs mt-2",
        confirmButtonText: "Yes, delete it!",
        buttonsStyling: false,
        showCloseButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            Promise.all(ids_array.map((id) => axios.delete(`/classteacher/${id}`)))
                .then(() => {
                    Swal.fire({
                        title: "Deleted!",
                        text: "Your class teachers have been deleted.",
                        icon: "success",
                        confirmButtonClass: "btn btn-info w-xs mt-2",
                        buttonsStyling: false
                    });
                    window.location.reload();
                })
                .catch((error) => {
                    console.error("Bulk delete error:", error);
                    Swal.fire({
                        title: "Error!",
                        text: error.response?.data?.message || "Failed to delete class teachers",
                        icon: "error",
                        confirmButtonClass: "btn btn-info w-xs mt-2",
                        buttonsStyling: false
                    });
                });
        }
    });
}

// Initialize List.js for client-side filtering
let classTeacherList;
const classTeacherListContainer = document.getElementById('classTeacherList');
if (classTeacherListContainer && document.querySelectorAll('#classTeacherList tbody tr').length > 0) {
    try {
        classTeacherList = new List('classTeacherList', {
            valueNames: ['sn', 'staffname', 'schoolclass', 'schoolarm', 'term', 'session', 'datereg'],
            page: 1000,
            pagination: false,
            listClass: 'list'
        });
        console.log("List.js initialized");
    } catch (error) {
        console.error("List.js initialization failed:", error);
    }
} else {
    console.warn("No class teachers available for List.js initialization");
}

// Update no results message
if (classTeacherList) {
    classTeacherList.on('searchComplete', function () {
        const noResultRow = document.querySelector('.noresult');
        if (noResultRow) {
            noResultRow.style.display = classTeacherList.visibleItems.length === 0 ? 'block' : 'none';
        }
    });
}

// Filter data (client-side)
function filterData() {
    const searchInput = document.querySelector(".search-box input.search");
    const searchValue = searchInput?.value || "";
    console.log("Filtering with search:", searchValue);
    if (classTeacherList) {
        classTeacherList.search(searchValue, ['sn', 'staffname', 'schoolclass', 'schoolarm', 'term', 'session']);
    }
}

// Add class teacher
const addClassTeacherForm = document.getElementById("add-classteacher-form");
if (addClassTeacherForm) {
    addClassTeacherForm.addEventListener("submit", function (e) {
        e.preventDefault();
        console.log("Add form submitted");
        const errorMsg = document.getElementById("alert-error-msg");
        if (errorMsg) errorMsg.classList.add("d-none");
        const formData = new FormData(addClassTeacherForm);
        const staffid = formData.get('staffid');
        const schoolclassid = formData.get('schoolclassid');
        const termid = formData.get('termid');
        const sessionid = formData.get('sessionid');
        if (!staffid || !schoolclassid || !termid || !sessionid) {
            if (errorMsg) {
                errorMsg.innerHTML = "Please fill all required fields";
                errorMsg.classList.remove("d-none");
            }
            return;
        }
        console.log("Sending add request:", { staffid, schoolclassid, termid, sessionid });
        axios.post('/classteacher', { staffid, schoolclassid, termid, sessionid })
            .then(function (response) {
                console.log("Add success:", response.data);
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Class Teacher added successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
                window.location.reload();
            })
            .catch(function (error) {
                console.error("Add error:", error.response?.data || error);
                if (errorMsg) {
                    errorMsg.innerHTML = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat().join(", ") || "Error adding class teacher";
                    errorMsg.classList.remove("d-none");
                }
            });
    });
}

// Edit class teacher
const editClassTeacherForm = document.getElementById("edit-classteacher-form");
if (editClassTeacherForm) {
    editClassTeacherForm.addEventListener("submit", function (e) {
        e.preventDefault();
        console.log("Edit form submitted");
        const errorMsg = document.getElementById("edit-alert-error-msg");
        if (errorMsg) errorMsg.classList.add("d-none");
        const formData = new FormData(editClassTeacherForm);
        const staffid = formData.get('staffid');
        const schoolclassid = formData.get('schoolclassid');
        const termid = formData.get('termid');
        const sessionid = formData.get('sessionid');
        const id = editIdField?.value;
        if (!id || !staffid || !schoolclassid || !termid || !sessionid) {
            if (errorMsg) {
                errorMsg.innerHTML = "Please fill all required fields";
                errorMsg.classList.remove("d-none");
            }
            return;
        }
        console.log("Sending edit request:", { id, staffid, schoolclassid, termid, sessionid });
        axios.put(`/classteacher/${id}`, { staffid, schoolclassid, termid, sessionid })
            .then(function (response) {
                console.log("Edit success:", response.data);
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Class Teacher updated successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
                window.location.reload();
            })
            .catch(function (error) {
                console.error("Edit error:", error.response?.data || error);
                if (errorMsg) {
                    errorMsg.innerHTML = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat().join(", ") || "Error updating class teacher";
                    errorMsg.classList.remove("d-none");
                }
            });
    });
}

// Modal events
const addModal = document.getElementById("addClassTeacherModal");
if (addModal) {
    addModal.addEventListener("show.bs.modal", function (e) {
        console.log("Add modal show event");
        const modalLabel = document.getElementById("exampleModalLabel");
        const addBtn = document.getElementById("add-btn");
        if (modalLabel) modalLabel.innerHTML = "Add Class Teacher";
        if (addBtn) addBtn.innerHTML = "Add Class Teacher";
    });
    addModal.addEventListener("hidden.bs.modal", function () {
        console.log("Add modal hidden");
        clearAddFields();
        const errorMsg = document.getElementById("alert-error-msg");
        if (errorMsg) errorMsg.classList.add("d-none");
    });
}

const editModal = document.getElementById("editModal");
if (editModal) {
    editModal.addEventListener("show.bs.modal", function () {
        console.log("Edit modal show event");
        const modalLabel = document.getElementById("editModalLabel");
        const updateBtn = document.getElementById("update-btn");
        if (modalLabel) modalLabel.innerHTML = "Edit Class Teacher";
        if (updateBtn) updateBtn.innerHTML = "Update";
    });
    editModal.addEventListener("hidden.bs.modal", function () {
        console.log("Edit modal hidden");
        clearEditFields();
        const errorMsg = document.getElementById("edit-alert-error-msg");
        if (errorMsg) errorMsg.classList.add("d-none");
    });
}

// Initialize listeners
document.addEventListener("DOMContentLoaded", function () {
    console.log("DOMContentLoaded fired");
    const searchInput = document.querySelector(".search-box input.search");
    if (searchInput) {
        searchInput.addEventListener("input", debounce(function () {
            console.log("Search input changed:", searchInput.value);
            filterData();
        }, 300));
    } else {
        console.error("Search input not found");
    }
    initializeCheckboxes();
});

// Expose functions to global scope
window.deleteMultiple = deleteMultiple;