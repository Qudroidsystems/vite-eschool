console.log("subjectteacher.init.js is loaded and executing at", new Date().toISOString());

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

// Loading indicator
function showLoading() {
    const tableBody = document.querySelector('#kt_roles_view_table tbody');
    if (tableBody) {
        tableBody.innerHTML = '<tr><td colspan="9" class="text-center">Loading...</td></tr>';
    }
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
const editIdField = document.getElementById("edit-id-field");
const editStaffIdField = document.getElementById("edit-staffid");

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

// Event delegation for edit and remove buttons
document.addEventListener('click', function (e) {
    const editBtn = e.target.closest('.edit-item-btn');
    const removeBtn = e.target.closest('.remove-item-btn');
    if (editBtn) {
        handleEditClick(e, editBtn);
    } else if (removeBtn) {
        handleRemoveClick(e, removeBtn);
    }
});

// Delete single subject teacher
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
            console.log("Deleting subject teacher:", itemId);
            axios.delete(deleteUrl)
                .then(function (response) {
                    console.log("Delete success:", response.data);
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: response.data.message || "Subject Teacher deleted successfully!",
                        showConfirmButton: false,
                        timer: 2000,
                        showCloseButton: true
                    });
                    if (subjectTeacherList) {
                        subjectTeacherList.remove("id", itemId);
                        updatePaginationCounts();
                    }
                    const row = document.querySelector(`tr[data-id="${itemId}"]`);
                    if (row) row.remove();
                    modal.hide();
                    const badge = document.querySelector('#total-count');
                    if (badge) {
                        const currentTotal = parseInt(badge.textContent);
                        badge.textContent = currentTotal - 1;
                    }
                    const rowCount = document.querySelectorAll("#kt_roles_view_table tbody tr").length;
                    const noResult = document.querySelector(".noresult");
                    if (noResult) {
                        noResult.style.display = rowCount === 0 ? "block" : "none";
                    } else if (rowCount === 0) {
                        document.querySelector("#kt_roles_view_table tbody").innerHTML =
                            '<tr><td colspan="9" class="noresult" style="display: block;">No results found</td></tr>';
                    }
                })
                .catch(function (error) {
                    console.error("Delete error:", error.response?.data || error);
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Error deleting subject teacher",
                        text: error.response?.data?.message || "An error occurred",
                        showConfirmButton: true
                    });
                    modal.hide();
                });
        };
    }
}

// Edit subject teacher
function handleEditClick(e, button) {
    e.preventDefault();
    console.log("Edit button clicked at", new Date().toISOString());

    const tr = button.closest("tr");
    const itemId = tr.querySelector(".id")?.getAttribute("data-id");
    const staffId = tr.querySelector(".subjectteacher")?.getAttribute("data-staffid");
    const sessionId = tr.querySelector(".session")?.getAttribute("data-sessionid");

    console.log("Table row data:", { itemId, staffId, sessionId });

    if (!itemId || !staffId || !sessionId) {
        console.error("Missing required data", { itemId, staffId, sessionId });
        Swal.fire({
            position: "center",
            icon: "error",
            title: "Error",
            text: "Unable to load subject teacher data",
            showConfirmButton: true
        });
        return;
    }

    clearEditFields();

    if (editIdField) editIdField.value = itemId;
    if (editStaffIdField) {
        const staffOption = editStaffIdField.querySelector(`option[value="${staffId}"]`);
        if (staffOption) {
            editStaffIdField.value = staffId;
            console.log("Staff ID set to:", staffId);
        } else {
            console.error("Staff ID option not found in select:", staffId);
        }
    }

    const sessionRadio = document.querySelector(`#edit-session-${sessionId}`);
    if (sessionRadio) {
        sessionRadio.checked = true;
        console.log("Session radio set to:", sessionId);
    } else {
        console.error("Session radio button not found:", sessionId);
    }

    axios.get(`/subjectteacher/${itemId}/subjects`, {
        headers: { 'X-CSRF-TOKEN': csrfToken }
    })
        .then(function (response) {
            console.log("Subjects and terms response:", response.data);
            if (!response.data.success) {
                console.error("AJAX response unsuccessful:", response.data.message);
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error",
                    text: response.data.message || "Failed to fetch subjects and terms",
                    showConfirmButton: true
                });
                return;
            }

            const subjectIds = Array.isArray(response.data.subjectIds) ? response.data.subjectIds.map(Number) : [];
            const termIds = Array.isArray(response.data.termIds) ? response.data.termIds.map(Number) : [];
            console.log("Pre-selecting subjects:", subjectIds);
            console.log("Pre-selecting terms:", termIds);

            const subjectCheckboxes = document.querySelectorAll('#editModal input[name="subjectid[]"]');
            const termCheckboxes = document.querySelectorAll('#editModal input[name="termid[]"]');
            console.log("Available subject checkboxes:", Array.from(subjectCheckboxes).map(cb => cb.value));
            console.log("Available term checkboxes:", Array.from(termCheckboxes).map(cb => cb.value));

            subjectCheckboxes.forEach(cb => {
                const value = Number(cb.value);
                const isChecked = subjectIds.includes(value);
                cb.checked = isChecked;
                console.log(`Subject checkbox ${value}: ${isChecked ? "checked" : "unchecked"}`);
            });

            termCheckboxes.forEach(cb => {
                const value = Number(cb.value);
                const isChecked = termIds.includes(value);
                cb.checked = isChecked;
                console.log(`Term checkbox ${value}: ${isChecked ? "checked" : "unchecked"}`);
            });

            const modal = new bootstrap.Modal(document.getElementById("editModal"));
            modal.show();
            console.log("Edit modal opened");
        })
        .catch(function (error) {
            console.error("Error fetching subjects and terms:", error.response?.status, error.response?.data || error.message);
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error loading data",
                text: error.response?.data?.message || "An error occurred while fetching subjects and terms",
                showConfirmButton: true
            });
            const modal = new bootstrap.Modal(document.getElementById("editModal"));
            modal.show();
        });
}

// Clear form fields
function clearAddFields() {
    if (addIdField) addIdField.value = "";
    if (addStaffIdField) addStaffIdField.value = "";
    document.querySelectorAll('#addSubjectTeacherModal input[name="subjectid[]"]').forEach(cb => cb.checked = false);
    document.querySelectorAll('#addSubjectTeacherModal input[name="termid[]"]').forEach(cb => cb.checked = false);
    document.querySelectorAll('#addSubjectTeacherModal input[name="sessionid"]').forEach(cb => cb.checked = false);
}
function clearEditFields() {
    if (editIdField) editIdField.value = "";
    if (editStaffIdField) editStaffIdField.value = "";
    document.querySelectorAll('#editModal input[name="subjectid[]"]').forEach(cb => cb.checked = false);
    document.querySelectorAll('#editModal input[name="termid[]"]').forEach(cb => cb.checked = false);
    document.querySelectorAll('#editModal input[name="sessionid"]').forEach(cb => cb.checked = false);
}

// Delete multiple subject teachers
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
            Promise.all(ids_array.map((id) => axios.delete(`/subjectteacher/${id}`)))
                .then((responses) => {
                    Swal.fire({
                        title: "Deleted!",
                        text: "Your subject teachers have been deleted.",
                        icon: "success",
                        confirmButtonClass: "btn btn-info w-xs mt-2",
                        buttonsStyling: false
                    });
                    responses.forEach((response) => {
                        if (response.data.success && subjectTeacherList) {
                            subjectTeacherList.remove("id", response.data.data.id);
                        }
                    });
                    updatePaginationCounts();
                    const badge = document.querySelector('#total-count');
                    if (badge) {
                        badge.textContent = subjectTeacherList.items.length;
                    }
                    const noResult = document.querySelector(".noresult");
                    if (noResult) {
                        noResult.style.display = subjectTeacherList.visibleItems.length === 0 ? "block" : "none";
                    }
                })
                .catch((error) => {
                    console.error("Bulk delete error:", error);
                    Swal.fire({
                        title: "Error!",
                        text: error.response?.data?.message || "Failed to delete subject teachers",
                        icon: "error",
                        confirmButtonClass: "btn btn-info w-xs mt-2",
                        buttonsStyling: false
                    });
                });
        }
    });
}

// Initialize List.js for client-side filtering and pagination
let subjectTeacherList;
const subjectTeacherListContainer = document.getElementById('subjectTeacherList');
function initializeListJs() {
    if (subjectTeacherListContainer && document.querySelectorAll('#subjectTeacherList tbody tr').length > 0) {
        try {
            subjectTeacherList = new List('subjectTeacherList', {
                valueNames: ['id', 'sn', 'subjectteacher', 'subject', 'subjectcode', 'term', 'session', 'datereg'],
                page: 10, // Number of items per page
                pagination: {
                    innerWindow: 2,
                    outerWindow: 1,
                    left: 0,
                    right: 0,
                    paginationClass: "listjs-pagination",
                    item: "<li><a class='page-link' href='javascript:void(0);'></a></li>"
                },
                listClass: 'list'
            });
            console.log("List.js initialized with pagination");
            updatePaginationCounts();
            subjectTeacherList.on('updated', updatePaginationCounts);
        } catch (error) {
            console.error("List.js initialization failed:", error);
        }
    } else {
        console.warn("No subject teachers available for List.js initialization");
        const tableBody = document.querySelector('#kt_roles_view_table tbody');
        if (tableBody) {
            tableBody.innerHTML = '<tr><td colspan="9" class="noresult" style="display: block;">No results found</td></tr>';
        }
    }
}

// Update pagination counts
function updatePaginationCounts() {
    if (subjectTeacherList) {
        const totalCount = subjectTeacherList.items.length;
        const showingCount = subjectTeacherList.visibleItems.length;
        const totalCountElement = document.querySelector('#total-count');
        const totalCountFooterElement = document.querySelector('#total-count-footer');
        const showingCountElement = document.querySelector('#showing-count');
        if (totalCountElement) totalCountElement.textContent = totalCount;
        if (totalCountFooterElement) totalCountFooterElement.textContent = totalCount;
        if (showingCountElement) showingCountElement.textContent = showingCount;
        const noResult = document.querySelector(".noresult");
        if (noResult) {
            noResult.style.display = showingCount === 0 ? "block" : "none";
        }
    }
}

// Filter data
function filterData() {
    const searchInput = document.querySelector(".search-box input.search");
    const searchValue = searchInput?.value || "";
    console.log("Filtering with search:", searchValue);
    if (subjectTeacherList) {
        subjectTeacherList.search(searchValue, ['sn', 'subjectteacher', 'subject', 'subjectcode', 'term', 'session']);
        updatePaginationCounts();
    }
}

// Add subject teacher
const addSubjectTeacherForm = document.getElementById("add-subjectteacher-form");
if (addSubjectTeacherForm) {
    addSubjectTeacherForm.addEventListener("submit", function (e) {
        e.preventDefault();
        console.log("Add form submitted at", new Date().toISOString());
        const errorMsg = document.getElementById("alert-error-msg");
        const addBtn = document.getElementById("add-btn");
        if (errorMsg) errorMsg.classList.add("d-none");
        if (addBtn) addBtn.disabled = true;

        const formData = new FormData(addSubjectTeacherForm);
        const staffid = formData.get('staffid');
        const subjectids = formData.getAll('subjectid[]').map(Number);
        const termids = formData.getAll('termid[]').map(Number);
        const sessionid = formData.get('sessionid');

        console.log("Form Data:", { staffid, subjectids, termids, sessionid });

        if (!staffid) {
            errorMsg.innerHTML = "Please select a teacher.";
            errorMsg.classList.remove("d-none");
            addBtn.disabled = false;
            return;
        }
        if (subjectids.length === 0) {
            errorMsg.innerHTML = "Please select at least one subject.";
            errorMsg.classList.remove("d-none");
            addBtn.disabled = false;
            return;
        }
        if (termids.length === 0) {
            errorMsg.innerHTML = "Please select at least one term.";
            errorMsg.classList.remove("d-none");
            addBtn.disabled = false;
            return;
        }
        if (!sessionid) {
            errorMsg.innerHTML = "Please select a session.";
            errorMsg.classList.remove("d-none");
            addBtn.disabled = false;
            return;
        }

        console.log("Sending add request:", { staffid, subjectids, termid: termids, sessionid });
        axios.post('/subjectteacher', { staffid, subjectids, termid: termids, sessionid })
            .then(function (response) {
                console.log("Add success:", response.data);
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: response.data.message || "Subject Teacher(s) added successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
                addBtn.disabled = false;
                // Refresh data
                fetchData();
            })
            .catch(function (error) {
                console.error("Add error:", error.response?.data || error);
                if (errorMsg) {
                    errorMsg.innerHTML = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat().join(", ") || "Error adding subject teacher";
                    errorMsg.classList.remove("d-none");
                }
                addBtn.disabled = false;
            });
    });
}

// Edit subject teacher
const editSubjectTeacherForm = document.getElementById("edit-subjectteacher-form");
if (editSubjectTeacherForm) {
    editSubjectTeacherForm.addEventListener("submit", function (e) {
        e.preventDefault();
        console.log("Edit form submitted at", new Date().toISOString());
        const errorMsg = document.getElementById("edit-alert-error-msg");
        const updateBtn = document.getElementById("update-btn");
        if (errorMsg) errorMsg.classList.add("d-none");
        if (updateBtn) updateBtn.disabled = true;

        const formData = new FormData(editSubjectTeacherForm);
        const staffid = formData.get('staffid');
        const subjectids = formData.getAll('subjectid[]').map(Number);
        const termids = formData.getAll('termid[]').map(Number);
        const sessionid = formData.get('sessionid');
        const id = editIdField?.value;

        console.log("Form Data:", { id, staffid, subjectids, termids, sessionid });

        if (!id) {
            errorMsg.innerHTML = "Invalid subject teacher ID.";
            errorMsg.classList.remove("d-none");
            updateBtn.disabled = false;
            return;
        }
        if (!staffid) {
            errorMsg.innerHTML = "Please select a teacher.";
            errorMsg.classList.remove("d-none");
            updateBtn.disabled = false;
            return;
        }
        if (subjectids.length === 0) {
            errorMsg.innerHTML = "Please select at least one subject.";
            errorMsg.classList.remove("d-none");
            updateBtn.disabled = false;
            return;
        }
        if (termids.length === 0) {
            errorMsg.innerHTML = "Please select at least one term.";
            errorMsg.classList.remove("d-none");
            updateBtn.disabled = false;
            return;
        }
        if (!sessionid) {
            errorMsg.innerHTML = "Please select a session.";
            errorMsg.classList.remove("d-none");
            updateBtn.disabled = false;
            return;
        }

        console.log("Sending edit request:", { id, staffid, subjectids, termid: termids, sessionid });
        axios.post(`/subjectteacher/${id}`, {
            _method: 'PUT',
            staffid,
            subjectids,
            termid: termids,
            sessionid,
            _token: csrfToken
        })
            .then(function (response) {
                console.log("Edit success:", response.data);
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: response.data.message || "Subject Teacher(s) updated successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
                updateBtn.disabled = false;
                // Refresh data
                fetchData();
            })
            .catch(function (error) {
                console.error("Edit error:", error.response?.status, error.response?.data || error.message);
                if (errorMsg) {
                    errorMsg.innerHTML = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat().join(", ") || "Error updating subject teacher";
                    errorMsg.classList.remove("d-none");
                }
                updateBtn.disabled = false;
            });
    });
}

// Fetch data from server
function fetchData() {
    showLoading();
    axios.get('/subjectteacher', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(function (response) {
            console.log("Fetch data success:", response.data);
            const parser = new DOMParser();
            const doc = parser.parseFromString(response.data.html, 'text/html');
            const newTbody = doc.querySelector('#kt_roles_view_table tbody');
            if (newTbody) {
                document.querySelector('#kt_roles_view_table tbody').innerHTML = newTbody.innerHTML;
                initializeListJs();
                initializeCheckboxes();
                filterData();
            } else {
                console.error("Table body not found in response");
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error",
                    text: "Incomplete server response",
                    showConfirmButton: true
                });
            }
        })
        .catch(function (error) {
            console.error("Error fetching data:", error);
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error loading data",
                text: error.response?.data?.message || "An error occurred",
                showConfirmButton: true
            });
            const tableBody = document.querySelector('#kt_roles_view_table tbody');
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="9" class="text-center">Failed to load data. Please try again.</td></tr>';
            }
        });
}

// Modal events
const addModal = document.getElementById("addSubjectTeacherModal");
if (addModal) {
    addModal.addEventListener("show.bs.modal", function (e) {
        console.log("Add modal show event");
        clearAddFields();
        const modalLabel = document.getElementById("exampleModalLabel");
        const addBtn = document.getElementById("add-btn");
        if (modalLabel) modalLabel.innerHTML = "Add Subject Teacher";
        if (addBtn) addBtn.innerHTML = "Add Subject Teacher";
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
        if (modalLabel) modalLabel.innerHTML = "Edit Subject Teacher";
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
    initializeListJs();
    fetchData();
});

// Expose functions to global scope
window.deleteMultiple = deleteMultiple;
