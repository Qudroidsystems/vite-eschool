var perPage = 5,
    editlist = false,
    checkAll = document.getElementById("checkAll"),
    options = {
        valueNames: ["id", "schoolclass", "schoolarm", "term", "session"],
    },
    classList = new List("classList", options);

console.log("Initial classList items:", classList.items.length);

classList.on("updated", function (e) {
    console.log("List.js updated, matching items:", e.matchingItems.length, "total items:", classList.items.length);
    document.getElementsByClassName("noresult")[0].style.display = e.matchingItems.length === 0 ? "block" : "none";
    setTimeout(() => {
        refreshCallbacks();
        ischeckboxcheck();
    }, 100);
});

document.addEventListener("DOMContentLoaded", function () {
    console.log("DOM loaded, initializing List.js...");
    console.log("Initial classList items:", classList.items.length);
    refreshCallbacks();
    ischeckboxcheck();

    // Initialize Choices.js
    if (typeof Choices !== 'undefined') {
        var termFilterVal = new Choices(document.getElementById("idTerm"), { searchEnabled: true });
        var sessionFilterVal = new Choices(document.getElementById("idSession"), { searchEnabled: true });
    } else {
        console.warn("Choices.js not available, falling back to native select");
    }
});

if (checkAll) {
    checkAll.onclick = function () {
        console.log("checkAll clicked");
        var checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
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

var addIdField = document.getElementById("add-id-field"),
    addSchoolClassIdField = document.getElementById("vschoolclassid"),
    addTermIdField = document.getElementById("termid"),
    addSessionIdField = document.getElementById("sessionid"),
    addNoSchoolOpenedField = document.getElementById("noschoolopened"),
    addTermEndsField = document.getElementById("termends"),
    addNextTermBeginsField = document.getElementById("nexttermbegins"),
    editIdField = document.getElementById("edit-id-field"),
    editSchoolClassIdField = document.getElementById("edit-vschoolclassid"),
    editTermIdField = document.getElementById("edit-termid"),
    editSessionIdField = document.getElementById("edit-sessionid"),
    editNoSchoolOpenedField = document.getElementById("edit-noschoolopened"),
    editTermEndsField = document.getElementById("edit-termends"),
    editNextTermBeginsField = document.getElementById("edit-nexttermbegins");

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

function refreshCallbacks() {
    console.log("refreshCallbacks executed at", new Date().toISOString());
    var removeButtons = document.getElementsByClassName("remove-item-btn");
    var editButtons = document.getElementsByClassName("edit-item-btn");
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
        var itemId = e.target.closest("tr").querySelector(".id").getAttribute("data-id");
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
                    title: "Error deleting class setting",
                    text: error.response?.data?.message || "An error occurred",
                    showConfirmButton: true
                });
            });
        }, { once: true });
        var modal = new bootstrap.Modal(document.getElementById("deleteRecordModal"));
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
        var itemId = e.target.closest("tr").querySelector(".id").getAttribute("data-id");
        console.log("Edit button clicked for ID:", itemId);
        axios.get(`/myclass/${itemId}/edit`).then(function (response) {
            var setting = response.data.setting;
            editlist = true;
            editIdField.value = setting.id;
            editSchoolClassIdField.value = setting.vschoolclassid;
            editTermIdField.value = setting.termid;
            editSessionIdField.value = setting.sessionid;
            editNoSchoolOpenedField.value = setting.noschoolopened || '';
            editTermEndsField.value = setting.termends || '';
            editNextTermBeginsField.value = setting.nexttermbegins || '';
            var modal = new bootstrap.Modal(document.getElementById("editModal"));
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

function clearAddFields() {
    addIdField.value = "";
    addSchoolClassIdField.value = "";
    addTermIdField.value = "";
    addSessionIdField.value = "";
    addNoSchoolOpenedField.value = "";
    addTermEndsField.value = "";
    addNextTermBeginsField.value = "";
}

function clearEditFields() {
    editIdField.value = "";
    editSchoolClassIdField.value = "";
    editTermIdField.value = "";
    editSessionIdField.value = "";
    editNoSchoolOpenedField.value = "";
    editTermEndsField.value = "";
    editNextTermBeginsField.value = "";
}

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

function filterData() {
    var searchInput = document.querySelector(".search-box input.search").value.toLowerCase();
    var termSelect = document.getElementById("idTerm");
    var sessionSelect = document.getElementById("idSession");
    var selectedTerm = typeof Choices !== 'undefined' && termFilterVal ? termFilterVal.getValue(true) : termSelect.value;
    var selectedSession = typeof Choices !== 'undefined' && sessionFilterVal ? sessionFilterVal.getValue(true) : sessionSelect.value;

    console.log("Filtering with:", { search: searchInput, term: selectedTerm, session: selectedSession });

    classList.filter(function (item) {
        var classMatch = item.values().schoolclass.toLowerCase().includes(searchInput);
        var armMatch = item.values().schoolarm.toLowerCase().includes(searchInput);
        var termMatch = selectedTerm === "all" || item.values().term === selectedTerm;
        var sessionMatch = selectedSession === "all" || item.values().session === selectedSession;

        return (classMatch || armMatch) && termMatch && sessionMatch;
    });
}

document.getElementById("add-class-form").addEventListener("submit", function (e) {
    e.preventDefault();
    var errorMsg = document.getElementById("alert-error-msg");
    errorMsg.classList.remove("d-none");
    setTimeout(() => errorMsg.classList.add("d-none"), 5000);

    if (addSchoolClassIdField.value === "") {
        errorMsg.innerHTML = "Please select a class";
        return false;
    }
    if (addTermIdField.value === "") {
        errorMsg.innerHTML = "Please select a term";
        return false;
    }
    if (addSessionIdField.value === "") {
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
        var message = error.response?.data?.message || "Error adding class setting";
        if (error.response?.status === 422) {
            message = Object.values(error.response.data.errors || {}).flat().join(", ");
        }
        errorMsg.innerHTML = message;
    });
});

document.getElementById("edit-class-form").addEventListener("submit", function (e) {
    e.preventDefault();
    var errorMsg = document.getElementById("alert-error-msg");
    errorMsg.classList.remove("d-none");
    setTimeout(() => errorMsg.classList.add("d-none"), 5000);

    if (editSchoolClassIdField.value === "") {
        errorMsg.innerHTML = "Please select a class";
        return false;
    }
    if (editTermIdField.value === "") {
        errorMsg.innerHTML = "Please select a term";
        return false;
    }
    if (editSessionIdField.value === "") {
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
        var message = error.response?.data?.message || "Error updating class setting";
        if (error.response?.status === 422) {
            message = Object.values(error.response.data.errors || {}).flat().join(", ");
        }
        errorMsg.innerHTML = message;
    });
});

document.getElementById("showModal").addEventListener("show.bs.modal", function (e) {
    if (e.relatedTarget.classList.contains("add-btn")) {
        console.log("Opening showModal for adding class setting...");
        document.getElementById("addModalLabel").innerHTML = "Add Class Setting";
        document.getElementById("add-btn").innerHTML = "Add Class Setting";
    }
});

document.getElementById("editModal").addEventListener("show.bs.modal", function () {
    console.log("Opening editModal...");
    document.getElementById("editModalLabel").innerHTML = "Edit Class Setting";
    document.getElementById("update-btn").innerHTML = "Update";
});

document.getElementById("showModal").addEventListener("hidden.bs.modal", function () {
    console.log("showModal closed, clearing fields...");
    clearAddFields();
});

document.getElementById("editModal").addEventListener("hidden.bs.modal", function () {
    console.log("editModal closed, clearing fields...");
    clearEditFields();
});