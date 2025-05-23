console.log("subjectclass.init.js is loaded and executing!");

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
const addSchoolClassIdField = document.getElementById("schoolclassid");
const addSubjectTeacherIdField = document.getElementById("subjectteacherid");
const editIdField = document.getElementById("edit-id-field");
const editSchoolClassIdField = document.getElementById("edit-schoolclassid");
const editSubjectTeacherIdField = document.getElementById("edit-subjectteacherid");

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

// Explicit event listener for Create Subject Class button
const createButton = document.querySelector('.add-btn');
if (createButton) {
    createButton.addEventListener('click', function (e) {
        e.preventDefault();
        console.log("Create Subject Class button clicked");
        try {
            const modal = new bootstrap.Modal(document.getElementById("addSubjectClassModal"));
            modal.show();
            console.log("Add modal opened");
        } catch (error) {
            console.error("Error opening add modal:", error);
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error opening modal",
                text: "Please ensure Bootstrap is loaded and try again.",
                showConfirmButton: true
            });
        }
    });
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

// Delete single subject class
function handleRemoveClick(e, button) {
    e.preventDefault();
    console.log("Remove button clicked");
    const itemId = button.closest("tr").querySelector(".id")?.getAttribute("data-id");
    if (!itemId) {
        console.error("Item ID not found");
        return;
    }
    const deleteButton = document.getElementById("delete-record");
    if (deleteButton) {
        deleteButton.onclick = function () {
            console.log("Deleting subject class:", itemId);
            axios.delete(`/subjectclass/${itemId}`)
                .then(function () {
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: "Subject Class deleted successfully!",
                        showConfirmButton: false,
                        timer: 2000,
                        showCloseButton: true
                    });
                    window.location.reload();
                })
                .catch(function (error) {
                    console.error("Delete error:", error);
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Error deleting subject class",
                        text: error.response?.data?.message || "An error occurred",
                        showConfirmButton: true
                    });
                });
        };
    }
    try {
        const modal = new bootstrap.Modal(document.getElementById("deleteRecordModal"));
        modal.show();
        console.log("Delete modal opened");
    } catch (error) {
        console.error("Error opening delete modal:", error);
    }
}

// Edit subject class
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
    if (editSchoolClassIdField) editSchoolClassIdField.value = tr.querySelector(".sclass")?.getAttribute("data-schoolclassid") || "";
    if (editSubjectTeacherIdField) editSubjectTeacherIdField.value = tr.querySelector(".subjectteacher")?.getAttribute("data-subteacherid") || "";
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
    if (addSchoolClassIdField) addSchoolClassIdField.value = "";
    if (addSubjectTeacherIdField) addSubjectTeacherIdField.value = "";
}

function clearEditFields() {
    if (editIdField) editIdField.value = "";
    if (editSchoolClassIdField) editSchoolClassIdField.value = "";
    if (editSubjectTeacherIdField) editSubjectTeacherIdField.value = "";
}

// Delete multiple subject classes
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
            Promise.all(ids_array.map((id) => axios.delete(`/subjectclass/${id}`)))
                .then(() => {
                    Swal.fire({
                        title: "Deleted!",
                        text: "Your subject classes have been deleted.",
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
                        text: error.response?.data?.message || "Failed to delete subject classes",
                        icon: "error",
                        confirmButtonClass: "btn btn-info w-xs mt-2",
                        buttonsStyling: false
                    });
                });
        }
    });
}

// Initialize List.js for client-side filtering
let subjectClassList;
const subjectClassListContainer = document.getElementById('subjectClassList');
if (subjectClassListContainer && document.querySelectorAll('#subjectClassList tbody tr').length > 0) {
    try {
        subjectClassList = new List('subjectClassList', {
            valueNames: ['sn', 'subjectteacher', 'subject', 'sclass', 'schoolarm', 'term', 'session', 'datereg'],
            page: 1000,
            pagination: false,
            listClass: 'list'
        });
        console.log("List.js initialized");
    } catch (error) {
        console.error("List.js initialization failed:", error);
    }
} else {
    console.warn("No subject classes available for List.js initialization");
}

// Update no results message
if (subjectClassList) {
    subjectClassList.on('searchComplete', function () {
        const noResultRow = document.querySelector('.noresult');
        if (noResultRow) {
            noResultRow.style.display = subjectClassList.visibleItems.length === 0 ? 'block' : 'none';
        }
    });
}

// Filter data (client-side)
function filterData() {
    const searchInput = document.querySelector(".search-box input.search");
    const searchValue = searchInput?.value || "";
    console.log("Filtering with search:", searchValue);
    if (subjectClassList) {
        subjectClassList.search(searchValue, ['sn', 'subjectteacher', 'subject', 'sclass', 'schoolarm', 'term', 'session']);
    }
}

// Add subject class
const addSubjectClassForm = document.getElementById("add-subjectclass-form");
if (addSubjectClassForm) {
    addSubjectClassForm.addEventListener("submit", function (e) {
        e.preventDefault();
        console.log("Add form submitted");
        const errorMsg = document.getElementById("alert-error-msg");
        if (errorMsg) errorMsg.classList.add("d-none");
        const formData = new FormData(addSubjectClassForm);
        const schoolclassid = formData.get('schoolclassid');
        const subjectteacherid = formData.get('subjectteacherid');
        if (!schoolclassid || !subjectteacherid) {
            if (errorMsg) {
                errorMsg.innerHTML = "Please fill all required fields";
                errorMsg.classList.remove("d-none");
            }
            return;
        }
        console.log("Sending add request:", { schoolclassid, subjectteacherid });
        axios.post('/subjectclass', { schoolclassid, subjectteacherid })
            .then(function (response) {
                console.log("Add success:", response.data);
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Subject Class added successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
                window.location.reload();
            })
            .catch(function (error) {
                console.error("Add error:", error.response?.data || error);
                if (errorMsg) {
                    errorMsg.innerHTML = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat().join(", ") || "Error adding subject class";
                    errorMsg.classList.remove("d-none");
                }
            });
    });
}

// Edit subject class
const editSubjectClassForm = document.getElementById("edit-subjectclass-form");
if (editSubjectClassForm) {
    editSubjectClassForm.addEventListener("submit", function (e) {
        e.preventDefault();
        console.log("Edit form submitted");
        const errorMsg = document.getElementById("edit-alert-error-msg");
        if (errorMsg) errorMsg.classList.add("d-none");
        const formData = new FormData(editSubjectClassForm);
        const schoolclassid = formData.get('schoolclassid');
        const subjectteacherid = formData.get('subjectteacherid');
        const id = editIdField?.value;
        if (!id || !schoolclassid || !subjectteacherid) {
            if (errorMsg) {
                errorMsg.innerHTML = "Please fill all required fields";
                errorMsg.classList.remove("d-none");
            }
            return;
        }
        console.log("Sending edit request:", { id, schoolclassid, subjectteacherid });
        axios.put(`/subjectclass/${id}`, { schoolclassid, subjectteacherid })
            .then(function (response) {
                console.log("Edit success:", response.data);
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Subject Class updated successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
                window.location.reload();
            })
            .catch(function (error) {
                console.error("Edit error:", error.response?.data || error);
                if (errorMsg) {
                    errorMsg.innerHTML = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat().join(", ") || "Error updating subject class";
                    errorMsg.classList.remove("d-none");
                }
            });
    });
}

// Modal events
const addModal = document.getElementById("addSubjectClassModal");
if (addModal) {
    addModal.addEventListener("show.bs.modal", function (e) {
        console.log("Add modal show event");
        const modalLabel = document.getElementById("exampleModalLabel");
        const addBtn = document.getElementById("add-btn");
        if (modalLabel) modalLabel.innerHTML = "Add Subject Class";
        if (addBtn) addBtn.innerHTML = "Add Subject Class";
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
        if (modalLabel) modalLabel.innerHTML = "Edit Subject Class";
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