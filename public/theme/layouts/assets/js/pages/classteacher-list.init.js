console.log("classteacher.init.js is loaded and executing!");

// Verify dependencies
try {
    if (typeof axios === 'undefined') throw new Error("Axios is not loaded");
    if (typeof Swal === 'undefined') throw new Error("SweetAlert2 is not loaded");
    if (typeof bootstrap === 'undefined') throw new Error("Bootstrap is not loaded");
    if (typeof List === 'undefined') throw new Error("List.js is not loaded");
} catch (error) {
    console.error("Dependency check failed:", error);
}

// Set Axios CSRF token globally
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;

// Debounce function for search input
function debounce(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// Check all checkbox
var checkAll = document.getElementById("checkAll");
if (checkAll) {
    checkAll.onclick = function () {
        console.log("checkAll clicked");
        var checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
        checkboxes.forEach((checkbox) => {
            checkbox.checked = this.checked;
            const row = checkbox.closest("tr");
            if (checkbox.checked) {
                row.classList.add("table-active");
            } else {
                row.classList.remove("table-active");
            }
        });
        const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
        var removeActions = document.getElementById("remove-actions");
        if (removeActions) {
            removeActions.classList.toggle("d-none", checkedCount === 0);
        }
    };
}

// Form fields
var addIdField = document.getElementById("add-id-field");
var addStaffIdField = document.getElementById("staffid");
var addSchoolClassIdField = document.getElementById("schoolclassid");
var addTermIdField = document.getElementById("termid");
var addSessionIdField = document.getElementById("sessionid");
var editIdField = document.getElementById("edit-id-field");
var editStaffIdField = document.getElementById("edit-staffid");
var editSchoolClassIdField = document.getElementById("edit-schoolclassid");
var editTermIdField = document.getElementById("edit-termid");
var editSessionIdField = document.getElementById("edit-sessionid");

// Checkbox handling
function ischeckboxcheck() {
    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
    checkboxes.forEach((checkbox) => {
        checkbox.removeEventListener("change", handleCheckboxChange);
        checkbox.addEventListener("change", handleCheckboxChange);
    });
}

function handleCheckboxChange(e) {
    const row = e.target.closest("tr");
    if (e.target.checked) {
        row.classList.add("table-active");
    } else {
        row.classList.remove("table-active");
    }
    const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
    var removeActions = document.getElementById("remove-actions");
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
    if (e.target.closest('.edit-item-btn')) {
        handleEditClick(e);
    } else if (e.target.closest('.remove-item-btn')) {
        handleRemoveClick(e);
    }
});

// Delete single class teacher
function handleRemoveClick(e) {
    e.preventDefault();
    var itemId = e.target.closest("tr").querySelector(".id").getAttribute("data-id");
    var deleteButton = document.getElementById("delete-record");
    if (deleteButton) {
        deleteButton.addEventListener("click", function () {
            axios.delete(`/classteacher/${itemId}`).then(function () {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Class Teacher deleted successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
                window.location.reload();
            }).catch(function (error) {
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error deleting class teacher",
                    text: error.response?.data?.message || "An error occurred",
                    showConfirmButton: true
                });
            });
        }, { once: true });
    }
    try {
        var modal = new bootstrap.Modal(document.getElementById("deleteRecordModal"));
        modal.show();
    } catch (error) {
        console.error("Error opening delete modal:", error);
    }
}

// Edit class teacher
function handleEditClick(e) {
    e.preventDefault();
    var itemId = e.target.closest("tr").querySelector(".id").getAttribute("data-id");
    var tr = e.target.closest("tr");
    if (editIdField) editIdField.value = itemId;
    if (editStaffIdField) editStaffIdField.value = tr.querySelector(".staffname")?.getAttribute("data-staffid") || "";
    if (editSchoolClassIdField) editSchoolClassIdField.value = tr.querySelector(".schoolclass")?.getAttribute("data-classid") || "";
    if (editTermIdField) editTermIdField.value = tr.querySelector(".term")?.getAttribute("data-termid") || "";
    if (editSessionIdField) editSessionIdField.value = tr.querySelector(".session")?.getAttribute("data-sessionid") || "";
    try {
        var modal = new bootstrap.Modal(document.getElementById("editModal"));
        modal.show();
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
    const ids_array = [];
    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
    checkboxes.forEach((checkbox) => {
        if (checkbox.checked) {
            const id = checkbox.closest("tr").querySelector(".id").getAttribute("data-id");
            ids_array.push(id);
        }
    });
    if (ids_array.length > 0) {
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
            if (result.value) {
                Promise.all(ids_array.map((id) => {
                    return axios.delete(`/classteacher/${id}`);
                })).then(() => {
                    Swal.fire({
                        title: "Deleted!",
                        text: "Your class teachers have been deleted.",
                        icon: "success",
                        confirmButtonClass: "btn btn-info w-xs mt-2",
                        buttonsStyling: false
                    });
                    window.location.reload();
                }).catch((error) => {
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
    } else {
        Swal.fire({
            title: "Please select at least one checkbox",
            confirmButtonClass: "btn btn-info",
            buttonsStyling: false,
            showCloseButton: true
        });
    }
}

// Initialize List.js for client-side filtering
var classTeacherList;
var classTeacherListContainer = document.getElementById('classTeacherList');
if (classTeacherListContainer && document.querySelectorAll('#classTeacherList tbody tr').length > 0) {
    try {
        classTeacherList = new List('classTeacherList', {
            valueNames: ['sn', 'staffname', 'schoolclass', 'schoolarm', 'term', 'session', 'datereg'],
            page: 1000,
            pagination: false,
            listClass: 'list'
        });
    } catch (error) {
        console.error("List.js initialization failed:", error);
    }
} else {
    console.warn("No class teachers available for List.js initialization");
}

// Update no results message
if (classTeacherList) {
    classTeacherList.on('searchComplete', function () {
        var noResultRow = document.querySelector('.noresult');
        if (classTeacherList.visibleItems.length === 0) {
            noResultRow.style.display = 'block';
        } else {
            noResultRow.style.display = 'none';
        }
    });
}

// Filter data (client-side)
function filterData() {
    var searchInput = document.querySelector(".search-box input.search");
    var searchValue = searchInput ? searchInput.value : "";
    console.log("Filtering with search:", searchValue);
    if (classTeacherList) {
        classTeacherList.search(searchValue, ['sn', 'staffname', 'schoolclass', 'schoolarm', 'term', 'session']);
    }
}

// Add class teacher
var addClassTeacherForm = document.getElementById("add-classteacher-form");
if (addClassTeacherForm) {
    addClassTeacherForm.addEventListener("submit", function (e) {
        e.preventDefault();
        var errorMsg = document.getElementById("alert-error-msg");
        if (errorMsg) errorMsg.classList.add("d-none");
        var formData = new FormData(addClassTeacherForm);
        var staffid = formData.get('staffid');
        var schoolclassid = formData.get('schoolclassid');
        var termid = formData.get('termid');
        var sessionid = formData.get('sessionid');
        if (!staffid || !schoolclassid || !termid || !sessionid) {
            if (errorMsg) {
                errorMsg.innerHTML = "Please fill all required fields";
                errorMsg.classList.remove("d-none");
            }
            return;
        }
        console.log("Submitting Add Class Teacher:", { staffid, schoolclassid, termid, sessionid });
        axios.post('/classteacher', {
            staffid: staffid,
            schoolclassid: schoolclassid,
            termid: termid,
            sessionid: sessionid
        }, {
            headers: { 'Content-Type': 'application/json' }
        }).then(function (response) {
            console.log("Add Class Teacher Success:", response.data);
            Swal.fire({
                position: "center",
                icon: "success",
                title: "Class Teacher added successfully!",
                showConfirmButton: false,
                timer: 2000,
                showCloseButton: true
            });
            window.location.reload();
        }).catch(function (error) {
            console.error("Add Class Teacher Error:", error.response);
            if (errorMsg) {
                errorMsg.innerHTML = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat().join(", ") || "Error adding class teacher";
                errorMsg.classList.remove("d-none");
            }
        });
    });
}

// Edit class teacher
var editClassTeacherForm = document.getElementById("edit-classteacher-form");
if (editClassTeacherForm) {
    editClassTeacherForm.addEventListener("submit", function (e) {
        e.preventDefault();
        var errorMsg = document.getElementById("edit-alert-error-msg");
        if (errorMsg) errorMsg.classList.add("d-none");
        var formData = new FormData(editClassTeacherForm);
        var staffid = formData.get('staffid');
        var schoolclassid = formData.get('schoolclassid');
        var termid = formData.get('termid');
        var sessionid = formData.get('sessionid');
        var id = editIdField.value;
        if (!staffid || !schoolclassid || !termid || !sessionid) {
            if (errorMsg) {
                errorMsg.innerHTML = "Please fill all required fields";
                errorMsg.classList.remove("d-none");
            }
            return;
        }
        console.log("Submitting Edit Class Teacher:", { id, staffid, schoolclassid, termid, sessionid });
        axios.put(`/classteacher/${id}`, {
            staffid: staffid,
            schoolclassid: schoolclassid,
            termid: termid,
            sessionid: sessionid
        }, {
            headers: { 'Content-Type': 'application/json' }
        }).then(function (response) {
            console.log("Edit Class Teacher Success:", response.data);
            Swal.fire({
                position: "center",
                icon: "success",
                title: "Class Teacher updated successfully!",
                showConfirmButton: false,
                timer: 2000,
                showCloseButton: true
            });
            window.location.reload();
        }).catch(function (error) {
            console.error("Edit Class Teacher Error:", error.response);
            if (errorMsg) {
                errorMsg.innerHTML = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat().join(", ") || "Error updating class teacher";
                errorMsg.classList.remove("d-none");
            }
        });
    });
}

// Modal events
var addModal = document.getElementById("addClassTeacherModal");
if (addModal) {
    addModal.addEventListener("show.bs.modal", function (e) {
        if (e.relatedTarget.classList.contains("add-btn")) {
            var modalLabel = document.getElementById("exampleModalLabel");
            var addBtn = document.getElementById("add-btn");
            if (modalLabel) modalLabel.innerHTML = "Add Class Teacher";
            if (addBtn) addBtn.innerHTML = "Add Class Teacher";
        }
    });
    addModal.addEventListener("hidden.bs.modal", function () {
        clearAddFields();
    });
}

var editModal = document.getElementById("editModal");
if (editModal) {
    editModal.addEventListener("show.bs.modal", function () {
        var modalLabel = document.getElementById("editModalLabel");
        var updateBtn = document.getElementById("update-btn");
        if (modalLabel) modalLabel.innerHTML = "Edit Class Teacher";
        if (updateBtn) updateBtn.innerHTML = "Update";
    });
    editModal.addEventListener("hidden.bs.modal", function () {
        clearEditFields();
    });
}

// Initialize listeners
document.addEventListener("DOMContentLoaded", function () {
    console.log("DOMContentLoaded fired");
    var searchInput = document.querySelector(".search-box input.search");
    if (searchInput) {
        searchInput.addEventListener("input", debounce(function () {
            console.log("Search input changed:", searchInput.value);
            filterData();
        }, 300));
    } else {
        console.error("Search input not found!");
    }

    ischeckboxcheck();
});

// Expose functions to global scope
window.deleteMultiple = deleteMultiple;