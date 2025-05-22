var perPage = 5,
    editlist = false,
    checkAll = document.getElementById("checkAll"),
    options = {
        valueNames: ["id", "session", "status", "datereg"],
    },
    sessionList = new List("sessionList", options);

console.log("Initial sessionList items:", sessionList.items.length);

sessionList.on("updated", function (e) {
    console.log("List.js updated, matching items:", e.matchingItems.length, "total items:", sessionList.items.length);
    document.getElementsByClassName("noresult")[0].style.display = e.matchingItems.length === 0 ? "block" : "none";
    setTimeout(() => {
        refreshCallbacks();
        ischeckboxcheck();
    }, 100);
});

document.addEventListener("DOMContentLoaded", function () {
    console.log("DOM loaded, initializing List.js...");
    console.log("Initial sessionList items:", sessionList.items.length);
    refreshCallbacks();
    ischeckboxcheck();
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

var addIdField = document.querySelector("#addSessionModal #add-id-field"),
    addSessionField = document.querySelector("#addSessionModal #session"),
    addStatusField = document.querySelector("#addSessionModal #sessionstatus"),
    editIdField = document.querySelector("#editModal #edit-id-field"),
    editSessionField = document.querySelector("#editModal #edit-session"),
    editStatusField = document.querySelector("#editModal #sessionstatus");

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
            if (typeof axios === 'undefined') {
                console.error("Axios is not defined. Please include Axios library.");
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Configuration error",
                    text: "Axios library is missing",
                    showConfirmButton: true
                });
                return;
            }
            axios.post('/session/deletesession', {
                sessionid: itemId,
                _token: document.querySelector('meta[name="csrf-token"]').content
            }).then(function (response) {
                console.log("Deleted session ID:", itemId);
                sessionList.remove("id", itemId);
                document.querySelector(`td[data-id="${itemId}"]`).closest("tr").remove();
                updateTotalCount();
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Session deleted successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
                var deleteModal = bootstrap.Modal.getInstance(document.getElementById("deleteRecordModal"));
                deleteModal.hide();
            }).catch(function (error) {
                console.error("Error deleting session:", error);
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error deleting session",
                    text: error.response?.data?.message || "An error occurred",
                    showConfirmButton: true
                });
            });
        }, { once: true });
        console.log("Opening deleteRecordModal...");
        var modal = new bootstrap.Modal(document.getElementById("deleteRecordModal"));
        modal.show();
    } catch (error) {
        console.error("Error in remove-item-btn click:", error);
    }
}

function handleEditClick(e) {
    e.preventDefault();
    try {
        var itemId = e.target.closest("tr").querySelector(".id").getAttribute("data-id");
        console.log("Edit button clicked for ID:", itemId);
        var tr = e.target.closest("tr");
        editlist = true;
        editIdField.value = itemId;
        editSessionField.value = tr.querySelector(".session").getAttribute("data-session");
        editStatusField.value = tr.querySelector(".status").getAttribute("data-status");
        console.log("Opening editModal...");
        var modal = new bootstrap.Modal(document.getElementById("editModal"));
        modal.show();
    } catch (error) {
        console.error("Error in edit-item-btn click:", error);
    }
}

function clearAddFields() {
    addIdField.value = "";
    addSessionField.value = "";
    addStatusField.value = "";
}

function clearEditFields() {
    editIdField.value = "";
    editSessionField.value = "";
    editStatusField.value = "";
}

function updateTotalCount() {
    const total = sessionList.items.length;
    document.querySelector(".badge.bg-dark-subtle").textContent = total;
    document.querySelector(".fw-semibold:last-child").textContent = total;
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
                if (typeof axios === 'undefined') {
                    console.error("Axios is not defined. Please include Axios library.");
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Configuration error",
                        text: "Axios library is missing",
                        showConfirmButton: true
                    });
                    return;
                }
                Promise.all(ids_array.map((id) => {
                    return axios.post('/session/deletesession', {
                        sessionid: id,
                        _token: document.querySelector('meta[name="csrf-token"]').content
                    });
                })).then(() => {
                    ids_array.forEach((id) => {
                        sessionList.remove("id", id);
                        document.querySelector(`td[data-id="${id}"]`).closest("tr").remove();
                    });
                    updateTotalCount();
                    Swal.fire({
                        title: "Deleted!",
                        text: "Your sessions have been deleted.",
                        icon: "success",
                        confirmButtonClass: "btn btn-info w-xs mt-2",
                        buttonsStyling: false
                    });
                    document.getElementById("remove-actions").classList.add("d-none");
                    document.getElementById("checkAll").checked = false;
                }).catch((error) => {
                    console.error("Error deleting sessions:", error);
                    Swal.fire({
                        title: "Error!",
                        text: error.response?.data?.message || "Failed to delete sessions",
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
    console.log("Filtering with:", { search: searchInput });

    sessionList.filter(function (item) {
        var sessionMatch = item.values().session.toLowerCase().includes(searchInput);
        return sessionMatch;
    });
}

document.getElementById("add-user-form").addEventListener("submit", function (e) {
    e.preventDefault();
    var errorMsg = document.getElementById("alert-error-msg");
    errorMsg.classList.remove("d-none");
    setTimeout(() => errorMsg.classList.add("d-none"), 2000);

    if (addSessionField.value === "") {
        errorMsg.innerHTML = "Please enter a session name";
        return false;
    }
    if (addStatusField.value === "") {
        errorMsg.innerHTML = "Please select a status";
        return false;
    }

    if (typeof axios === 'undefined') {
        console.error("Axios is not defined. Please include Axios library.");
        errorMsg.innerHTML = "Configuration error: Axios library is missing";
        return false;
    }

    axios.post('/session', {
        session: addSessionField.value,
        sessionstatus: addStatusField.value,
        _token: document.querySelector('meta[name="csrf-token"]').content
    }).then(function (response) {
        const newSession = response.data.session;
        sessionList.add({
            id: newSession.id,
            session: newSession.session,
            status: newSession.sessionstatus,
            datereg: new Date(newSession.updated_at).toISOString().split('T')[0]
        });
        updateTotalCount();
        Swal.fire({
            position: "center",
            icon: "success",
            title: "Session added successfully!",
            showConfirmButton: false,
            timer: 2000,
            showCloseButton: true
        });
        var addModal = bootstrap.Modal.getInstance(document.getElementById("addSessionModal"));
        addModal.hide();
        clearAddFields();
        refreshCallbacks();
    }).catch(function (error) {
        console.error("Error adding session:", error);
        errorMsg.innerHTML = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat().join(", ") || "Error adding session";
    });
});

document.getElementById("edit-user-form").addEventListener("submit", function (e) {
    e.preventDefault();
    var errorMsg = document.getElementById("alert-error-msg");
    errorMsg.classList.remove("d-none");
    setTimeout(() => errorMsg.classList.add("d-none"), 2000);

    if (editSessionField.value === "") {
        errorMsg.innerHTML = "Please enter a session name";
        return false;
    }
    if (editStatusField.value === "") {
        errorMsg.innerHTML = "Please select a status";
        return false;
    }

    if (typeof axios === 'undefined') {
        console.error("Axios is not defined. Please include Axios library.");
        errorMsg.innerHTML = "Configuration error: Axios library is missing";
        return false;
    }

    axios.post('/session/updatesession', {
        id: editIdField.value,
        session: editSessionField.value,
        sessionstatus: editStatusField.value,
        _token: document.querySelector('meta[name="csrf-token"]').content
    }).then(function (response) {
        const updatedSession = response.data.session;
        sessionList.update({
            id: updatedSession.id,
            session: updatedSession.session,
            status: updatedSession.sessionstatus,
            datereg: new Date(updatedSession.updated_at).toISOString().split('T')[0]
        });
        const row = document.querySelector(`td[data-id="${updatedSession.id}"]`).closest("tr");
        row.querySelector(".session").setAttribute("data-session", updatedSession.session);
        row.querySelector(".session").textContent = updatedSession.session;
        row.querySelector(".status").setAttribute("data-status", updatedSession.sessionstatus);
        row.querySelector(".status").textContent = updatedSession.sessionstatus;
        row.querySelector(".datereg").textContent = new Date(updatedSession.updated_at).toISOString().split('T')[0];
        updateTotalCount();
        Swal.fire({
            position: "center",
            icon: "success",
            title: "Session updated successfully!",
            showConfirmButton: false,
            timer: 2000,
            showCloseButton: true
        });
        var editModal = bootstrap.Modal.getInstance(document.getElementById("editModal"));
        editModal.hide();
        clearEditFields();
        refreshCallbacks();
    }).catch(function (error) {
        console.error("Error updating session:", error);
        errorMsg.innerHTML = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat().join(", ") || "Error updating session";
    });
});

document.getElementById("addSessionModal").addEventListener("show.bs.modal", function (e) {
    console.log("Opening addSessionModal...");
    document.getElementById("exampleModalLabel").innerHTML = "Add Session";
    document.getElementById("add-btn").innerHTML = "Add Session";
});

document.getElementById("editModal").addEventListener("show.bs.modal", function (e) {
    console.log("Opening editModal...");
    document.getElementById("exampleModalLabel").innerHTML = "Edit Session";
    document.getElementById("add-btn").innerHTML = "Update Session";
});

document.getElementById("addSessionModal").addEventListener("hidden.bs.modal", function () {
    console.log("addSessionModal closed, clearing fields...");
    clearAddFields();
});

document.getElementById("editModal").addEventListener("hidden.bs.modal", function () {
    console.log("editModal closed, clearing fields...");
    clearEditFields();
});