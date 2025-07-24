// myclass-list.init.js

// Ensure Axios is available
function ensureAxios() {
    if (typeof axios === 'undefined') {
        console.error("Axios is not defined. Please include Axios library.");
        Swal.fire({
            position: "center",
            icon: "error",
            title: "Configuration error",
            text: "Axios library is missing",
            showConfirmButton: true
        });
        return false;
    }
    return true;
}

// Filter Data Function
function filterData() {
    console.log("filterData called");
    if (!ensureAxios()) return;

    const classSelect = document.getElementById("idclass");
    const sessionSelect = document.getElementById("idsession");
    const searchInput = document.getElementById("searchInput");

    // Debug: Log elements to verify existence
    console.log("classSelect:", classSelect);
    console.log("sessionSelect:", sessionSelect);
    console.log("searchInput:", searchInput);

    if (!classSelect || !sessionSelect) {
        console.error("Class or session select elements not found");
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Required filter elements not found.",
            showConfirmButton: true
        });
        return;
    }

    // Handle Choices.js if present
    let classValue, sessionValue;
    if (typeof Choices !== 'undefined' && classSelect.choices && sessionSelect.choices) {
        classValue = classSelect.choices.getValue(true);
        sessionValue = sessionSelect.choices.getValue(true);
    } else {
        classValue = classSelect.value;
        sessionValue = sessionSelect.value;
    }
    const searchValue = searchInput ? searchInput.value.trim() : '';

    if (classValue === 'ALL' || sessionValue === 'ALL') {
        document.getElementById('classTableBody').innerHTML = '<tr><td colspan="6" class="text-center">Select class and session to view classes.</td></tr>';
        document.getElementById('pagination-container').innerHTML = '';
        document.getElementById('classcount').innerText = '0';
        Swal.fire({
            icon: "warning",
            title: "Missing Selection",
            text: "Please select a valid class and session.",
            showConfirmButton: true
        });
        return;
    }

    console.log("Sending AJAX request with:", { search: searchValue, schoolclassid: classValue, sessionid: sessionValue });

    const tableBody = document.getElementById('classTableBody');
    tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Loading...</td></tr>';

    axios.get('/myclass', {
        params: {
            search: searchValue,
            schoolclassid: classValue,
            sessionid: sessionValue
        },
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(function (response) {
        console.log("AJAX response received:", response.data);
        document.getElementById('classTableBody').innerHTML = response.data.tableBody || '<tr><td colspan="6" class="text-center">No classes found.</td></tr>';
        document.getElementById('pagination-container').innerHTML = response.data.pagination || '';
        document.getElementById('classcount').innerText = response.data.classCount || '0';
        setupPaginationLinks();
        if (response.data.tableBody.includes('No classes found') || response.data.tableBody.includes('Select class and session')) {
            Swal.fire({
                icon: "info",
                title: "No Results",
                text: "No classes found for the selected class and session.",
                showConfirmButton: true
            });
        }
    }).catch(function (error) {
        console.error("AJAX error:", error);
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
        Swal.fire({
            icon: "error",
            title: "Error",
            text: error.response?.data?.message || "Failed to fetch class data.",
            showConfirmButton: true
        });
    });
}

// Pagination Handler
function setupPaginationLinks() {
    const paginationLinks = document.querySelectorAll('#pagination-container a');
    paginationLinks.forEach(link => {
        link.removeEventListener('click', handlePaginationClick); // Prevent duplicate listeners
        link.addEventListener('click', handlePaginationClick);
    });
}

function handlePaginationClick(e) {
    e.preventDefault();
    const url = this.href;
    if (url && !this.classList.contains('disabled')) {
        loadPage(url);
    }
}

function loadPage(url) {
    console.log("Loading page:", url);
    const tableBody = document.getElementById('classTableBody');
    tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Loading...</td></tr>';

    axios.get(url, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(function (response) {
        console.log("Page load response:", response.data);
        document.getElementById('classTableBody').innerHTML = response.data.tableBody || '<tr><td colspan="6" class="text-center">No classes found.</td></tr>';
        document.getElementById('pagination-container').innerHTML = response.data.pagination || '';
        document.getElementById('classcount').innerText = response.data.classCount || '0';
        setupPaginationLinks();
    }).catch(function (error) {
        console.error("Page load error:", error);
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
        Swal.fire({
            icon: "error",
            title: "Error",
            text: error.response?.data?.message || "Failed to fetch class data.",
            showConfirmButton: true
        });
    });
}

// Checkbox Handling
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
    document.getElementById("remove-actions").classList.toggle("d-none", checkedCount === 0);
    const allCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]');
    document.getElementById("checkAll").checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCount;
}

// Delete and Edit Handlers
function refreshCallbacks() {
    console.log("refreshCallbacks executed at", new Date().toISOString());
    const removeButtons = document.getElementsByClassName("remove-item-btn");
    const editButtons = document.getElementsByClassName("edit-item-btn");
    console.log("Attaching event listeners to", removeButtons.length, "remove buttons and", editButtons.length, "edit buttons");

    Array.from(removeButtons).forEach(function (btn) {
        btn.removeEventListener("click", handleRemoveClick);
        btn.addEventListener("click", handleRemoveClick);
    });

    Array.from(editButtons).forEach(function (btn) {
        btn.removeEventListener("click", handleEditClick);
        btn.addEventListener("click", handleEditClick);
    });
}

function handleRemoveClick(e) {
    e.preventDefault();
    try {
        const itemId = e.target.closest("tr").querySelector(".id").getAttribute("data-id");
        console.log("Remove button clicked for ID:", itemId);
        document.getElementById("delete-record").addEventListener("click", function () {
            if (!ensureAxios()) return;
            axios.delete(`/myclass/${itemId}`, {
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            }).then(function () {
                console.log("Deleted class setting ID:", itemId);
                window.location.reload();
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Class setting deleted successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
            }).catch(function (error) {
                console.error("Error deleting class setting:", error);
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error",
                    text: error.response?.data?.message || "An error occurred",
                    showConfirmButton: true
                });
            });
        }, { once: true });
        const modal = new bootstrap.Modal(document.getElementById("deleteRecordModal"));
        modal.show();
    } catch (error) {
        console.error("Error in remove-item-btn click:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Failed to initiate delete",
            showConfirmButton: true
        });
    }
}

function handleEditClick(e) {
    e.preventDefault();
    try {
        const itemId = e.target.closest("tr").querySelector(".id").getAttribute("data-id");
        console.log("Edit button clicked for ID:", itemId);
        axios.get(`/myclass/${itemId}/edit`).then(function (response) {
            const setting = response.data.setting;
            editlist = true;
            editIdField.value = setting.id;
            editSchoolClassIdField.value = setting.vschoolclassid;
            editTermIdField.value = setting.termid;
            editSessionIdField.value = setting.sessionid;
            editNoSchoolOpenedField.value = setting.noschoolopened || '';
            editTermEndsField.value = setting.termends || '';
            editNextTermBeginsField.value = setting.nexttermbegins || '';
            const modal = new bootstrap.Modal(document.getElementById("editModal"));
            modal.show();
        }).catch(function (error) {
            console.error("Error fetching class setting:", error);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Failed to load class setting",
                showConfirmButton: true
            });
        });
    } catch (error) {
        console.error("Error in edit-item-btn click:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Failed to populate edit modal",
            showConfirmButton: true
        });
    }
}

// Form Field References
let editlist = false;
const addIdField = document.getElementById("add-id-field");
const addSchoolClassIdField = document.getElementById("vschoolclassid");
const addTermIdField = document.getElementById("termid");
const addSessionIdField = document.getElementById("sessionid");
const addNoSchoolOpenedField = document.getElementById("noschoolopened");
const addTermEndsField = document.getElementById("termends");
const addNextTermBeginsField = document.getElementById("nexttermbegins");
const editIdField = document.getElementById("edit-id-field");
const editSchoolClassIdField = document.getElementById("edit-vschoolclassid");
const editTermIdField = document.getElementById("edit-termid");
const editSessionIdField = document.getElementById("edit-sessionid");
const editNoSchoolOpenedField = document.getElementById("edit-noschoolopened");
const editTermEndsField = document.getElementById("edit-termends");
const editNextTermBeginsField = document.getElementById("edit-nexttermbegins");

// Clear Form Fields
function clearAddFields() {
    if (addIdField) addIdField.value = "";
    if (addSchoolClassIdField) addSchoolClassIdField.value = "";
    if (addTermIdField) addTermIdField.value = "";
    if (addSessionIdField) addSessionIdField.value = "";
    if (addNoSchoolOpenedField) addNoSchoolOpenedField.value = "";
    if (addTermEndsField) addTermEndsField.value = "";
    if (addNextTermBeginsField) addNextTermBeginsField.value = "";
}

function clearEditFields() {
    if (editIdField) editIdField.value = "";
    if (editSchoolClassIdField) editSchoolClassIdField.value = "";
    if (editTermIdField) editTermIdField.value = "";
    if (editSessionIdField) editSessionIdField.value = "";
    if (editNoSchoolOpenedField) editNoSchoolOpenedField.value = "";
    if (editTermEndsField) editTermEndsField.value = "";
    if (editNextTermBeginsField) editNextTermBeginsField.value = "";
}

// Delete Multiple
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
                if (!ensureAxios()) return;
                Promise.all(ids_array.map((id) => {
                    return axios.delete(`/myclass/${id}`, {
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    });
                })).then(() => {
                    window.location.reload();
                    Swal.fire({
                        title: "Deleted!",
                        text: "Your data has been deleted.",
                        icon: "success",
                        confirmButtonClass: "btn btn-info w-xs mt-2",
                        buttonsStyling: false
                    });
                }).catch((error) => {
                    console.error("Error deleting class settings:", error);
                    Swal.fire({
                        title: "Error!",
                        text: error.response?.data?.message || "Failed to delete class settings",
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

// Initialize List.js
document.addEventListener("DOMContentLoaded", function () {
    const perPage = 5;
    const options = {
        valueNames: ["id", "schoolclass", "schoolarm", "term", "session"],
        page: perPage,
        pagination: true
    };
    const classList = new List("classList", options);
    console.log("Initial classList items:", classList.items.length);

    classList.on("updated", function (e) {
        console.log("List.js updated, matching items:", e.matchingItems.length, "total items:", classList.items.length);
        const noResult = document.getElementsByClassName("noresult")[0];
        if (noResult) {
            noResult.style.display = e.matchingItems.length === 0 ? "block" : "none";
        }
        setTimeout(() => {
            refreshCallbacks();
            ischeckboxcheck();
        }, 100);
    });

    // Initialize Choices.js
    if (typeof Choices !== 'undefined') {
        const classFilterVal = new Choices(document.getElementById("idclass"), { searchEnabled: true });
        const sessionFilterVal = new Choices(document.getElementById("idsession"), { searchEnabled: true });
    } else {
        console.warn("Choices.js not available, falling back to native select");
    }

    // Check All Checkbox
    const checkAll = document.getElementById("checkAll");
    if (checkAll) {
        checkAll.onclick = function () {
            console.log("checkAll clicked");
            const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
            console.log("checkAll clicked, checkboxes found:", checkboxes.length);
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
            document.getElementById("remove-actions").classList.toggle("d-none", checkedCount === 0);
        };
    }

    // Form Submission Handlers
    const addForm = document.getElementById("add-class-form");
    if (addForm) {
        addForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const errorMsg = document.getElementById("alert-error-msg");
            errorMsg.classList.remove("d-none");
            setTimeout(() => errorMsg.classList.add("d-none"), 5000);

            if (!addSchoolClassIdField.value) {
                errorMsg.innerHTML = "Please select a class";
                return false;
            }
            if (!addTermIdField.value) {
                errorMsg.innerHTML = "Please select a term";
                return false;
            }
            if (!addSessionIdField.value) {
                errorMsg.innerHTML = "Please select a session";
                return false;
            }

            if (!ensureAxios()) return;

            axios.post('/myclass', {
                staffid: document.getElementById("staffid").value,
                vschoolclassid: addSchoolClassIdField.value,
                termid: addTermIdField.value,
                sessionid: addSessionIdField.value,
                noschoolopened: addNoSchoolOpenedField.value,
                termends: addTermEndsField.value,
                nexttermbegins: addNextTermBeginsField.value,
                _token: document.querySelector('meta[name="csrf-token"]').content
            }).then(function (response) {
                window.location.reload();
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Class setting added successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
            }).catch(function (error) {
                console.error("Error adding class setting:", error);
                let message = error.response?.data?.message || "Error adding class setting";
                if (error.response?.status === 422) {
                    message = Object.values(error.response.data.errors || {}).flat().join(", ");
                }
                errorMsg.innerHTML = message;
            });
        });
    }

    const editForm = document.getElementById("edit-class-form");
    if (editForm) {
        editForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const errorMsg = document.getElementById("alert-error-msg");
            errorMsg.classList.remove("d-none");
            setTimeout(() => errorMsg.classList.add("d-none"), 5000);

            if (!editSchoolClassIdField.value) {
                errorMsg.innerHTML = "Please select a class";
                return false;
            }
            if (!editTermIdField.value) {
                errorMsg.innerHTML = "Please select a term";
                return false;
            }
            if (!editSessionIdField.value) {
                errorMsg.innerHTML = "Please select a session";
                return false;
            }

            if (!ensureAxios()) return;

            axios.put(`/myclass/${editIdField.value}`, {
                staffid: document.getElementById("edit-staffid").value,
                vschoolclassid: editSchoolClassIdField.value,
                termid: editTermIdField.value,
                sessionid: editSessionIdField.value,
                noschoolopened: editNoSchoolOpenedField.value,
                termends: editTermEndsField.value,
                nexttermbegins: editNextTermBeginsField.value,
                _token: document.querySelector('meta[name="csrf-token"]').content
            }).then(function (response) {
                window.location.reload();
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Class setting updated successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
            }).catch(function (error) {
                console.error("Error updating class setting:", error);
                let message = error.response?.data?.message || "Error updating class setting";
                if (error.response?.status === 422) {
                    message = Object.values(error.response.data.errors || {}).flat().join(", ");
                }
                errorMsg.innerHTML = message;
            });
        });
    }

    // Modal Event Listeners
    const showModal = document.getElementById("showModal");
    if (showModal) {
        showModal.addEventListener("show.bs.modal", function (e) {
            if (e.relatedTarget.classList.contains("add-btn")) {
                console.log("Opening showModal for adding class setting...");
                document.getElementById("addModalLabel").innerHTML = "Add Class Setting";
                document.getElementById("add-btn").innerHTML = "Add Class Setting";
            }
        });
        showModal.addEventListener("hidden.bs.modal", function () {
            console.log("showModal closed, clearing fields...");
            clearAddFields();
        });
    }

    const editModal = document.getElementById("editModal");
    if (editModal) {
        editModal.addEventListener("show.bs.modal", function () {
            console.log("Opening editModal...");
            document.getElementById("editModalLabel").innerHTML = "Edit Class Setting";
            document.getElementById("update-btn").innerHTML = "Update";
        });
        editModal.addEventListener("hidden.bs.modal", function () {
            console.log("editModal closed, clearing fields...");
            clearEditFields();
        });
    }

    // Initialize callbacks
    refreshCallbacks();
    ischeckboxcheck();
});

// Ensure script runs after DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
    const searchButton = document.querySelector('button[onclick="filterData()"]');
    if (searchButton) {
        searchButton.onclick = filterData;
    }
});