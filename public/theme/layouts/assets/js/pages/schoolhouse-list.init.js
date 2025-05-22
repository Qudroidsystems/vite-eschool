console.log("schoolhouse.init.js is loaded and executing!");

try {
    // Verify dependencies
    if (typeof axios === 'undefined') throw new Error("Axios is not loaded");
    if (typeof Swal === 'undefined') throw new Error("SweetAlert2 is not loaded");
    if (typeof bootstrap === 'undefined') throw new Error("Bootstrap is not loaded");
    if (typeof List === 'undefined') throw new Error("List.js is not loaded");

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
    var addHouseField = document.getElementById("house");
    var addHouseColourField = document.getElementById("housecolour");
    var addHousemasterIdField = document.getElementById("housemasterid");
    var addTermIdField = document.getElementById("termid");
    var addSessionIdField = document.getElementById("sessionid");
    var editIdField = document.getElementById("edit-id-field");
    var editHouseField = document.getElementById("edit-house");
    var editHouseColourField = document.getElementById("edit-housecolour");
    var editHousemasterIdField = document.getElementById("edit-housemasterid");
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

    // Refresh edit/delete button callbacks
    function refreshCallbacks() {
        console.log("refreshCallbacks executed at", new Date().toISOString());
        var removeButtons = document.getElementsByClassName("remove-item-btn");
        var editButtons = document.getElementsByClassName("edit-item-btn");

        Array.from(removeButtons).forEach(function (btn) {
            btn.removeEventListener("click", handleRemoveClick);
            btn.addEventListener("click", handleRemoveClick);
        });

        Array.from(editButtons).forEach(function (btn) {
            btn.removeEventListener("click", handleEditClick);
            btn.addEventListener("click", handleEditClick);
        });
    }

    // Delete single house
    function handleRemoveClick(e) {
        e.preventDefault();
        var itemId = e.target.closest("tr").querySelector(".id").getAttribute("data-id");
        var deleteUrl = e.target.closest("tr").getAttribute("data-url");
        var deleteButton = document.getElementById("delete-record");
        if (deleteButton) {
            deleteButton.addEventListener("click", function () {
                axios.post(deleteUrl, {
                    houseid: itemId,
                    _token: document.querySelector('meta[name="csrf-token"]').content
                }).then(function (response) {
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: response.data.message || "School house deleted successfully!",
                        showConfirmButton: false,
                        timer: 2000,
                        showCloseButton: true
                    });
                    window.location.reload();
                }).catch(function (error) {
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Error deleting school house",
                        text: error.response?.data?.message || "An error occurred",
                        showConfirmButton: true
                    });
                });
            }, { once: true });
        }
        var modal = new bootstrap.Modal(document.getElementById("deleteRecordModal"));
        modal.show();
    }

    // Edit house
    function handleEditClick(e) {
        e.preventDefault();
        var itemId = e.target.closest("tr").querySelector(".id").getAttribute("data-id");
        var tr = e.target.closest("tr");
        if (editIdField) editIdField.value = itemId;
        if (editHouseField) editHouseField.value = tr.querySelector(".house").innerText;
        if (editHouseColourField) editHouseColourField.value = tr.querySelector(".housecolour").innerText;
        if (editHousemasterIdField) {
            var housemaster = tr.querySelector(".housemaster").innerText;
            Array.from(editHousemasterIdField.options).forEach(option => {
                option.selected = option.text === housemaster;
            });
        }
        if (editTermIdField) {
            var term = tr.querySelector(".term").innerText;
            Array.from(editTermIdField.options).forEach(option => {
                option.selected = option.text === term;
            });
        }
        if (editSessionIdField) {
            var session = tr.querySelector(".session").innerText;
            Array.from(editSessionIdField.options).forEach(option => {
                option.selected = option.text === session;
            });
        }
        var modal = new bootstrap.Modal(document.getElementById("editModal"));
        modal.show();
    }

    // Clear form fields
    function clearAddFields() {
        if (addIdField) addIdField.value = "";
        if (addHouseField) addHouseField.value = "";
        if (addHouseColourField) addHouseColourField.value = "";
        if (addHousemasterIdField) addHousemasterIdField.value = "";
        if (addTermIdField) addTermIdField.value = "";
        if (addSessionIdField) addSessionIdField.value = "";
    }

    function clearEditFields() {
        if (editIdField) editIdField.value = "";
        if (editHouseField) editHouseField.value = "";
        if (editHouseColourField) editHouseColourField.value = "";
        if (editHousemasterIdField) editHousemasterIdField.value = "";
        if (editTermIdField) editTermIdField.value = "";
        if (editSessionIdField) editSessionIdField.value = "";
    }

    // Delete multiple houses
    function deleteMultiple() {
        const ids_array = [];
        const urls_array = [];
        const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
        checkboxes.forEach((checkbox) => {
            if (checkbox.checked) {
                const id = checkbox.closest("tr").querySelector(".id").getAttribute("data-id");
                const url = checkbox.closest("tr").getAttribute("data-url");
                ids_array.push(id);
                urls_array.push(url);
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
                    Promise.all(urls_array.map((url, index) => {
                        return axios.post(url, {
                            houseid: ids_array[index],
                            _token: document.querySelector('meta[name="csrf-token"]').content
                        });
                    })).then(() => {
                        Swal.fire({
                            title: "Deleted!",
                            text: "Your school houses have been deleted.",
                            icon: "success",
                            confirmButtonClass: "btn btn-info w-xs mt-2",
                            buttonsStyling: false
                        });
                        window.location.reload();
                    }).catch((error) => {
                        Swal.fire({
                            title: "Error!",
                            text: error.response?.data?.message || "Failed to delete school houses",
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
    var houseList = new List('houseList', {
        valueNames: ['house', 'housecolour', 'housemaster', 'term', 'session'],
        page: 1000,
        pagination: false
    });

    // Update no results message
    houseList.on('searchComplete', function () {
        var noResultRow = document.querySelector('.noresult');
        if (houseList.visibleItems.length === 0) {
            noResultRow.style.display = 'block';
        } else {
            noResultRow.style.display = 'none';
        }
    });

    // Filter data (client-side)
    function filterData() {
        var searchInput = document.querySelector(".search-box input.search");
        var searchValue = searchInput ? searchInput.value : "";
        console.log("Filtering with search:", searchValue);
        houseList.search(searchValue, ['house', 'housecolour']);
    }

    // Add house
    var addHouseForm = document.getElementById("add-house-form");
    if (addHouseForm) {
        addHouseForm.addEventListener("submit", function (e) {
            e.preventDefault();
            var errorMsg = document.getElementById("alert-error-msg");
            if (errorMsg) {
                errorMsg.classList.remove("d-none");
                setTimeout(() => errorMsg.classList.add("d-none"), 2000);
            }

            if (!addHouseField || !addHouseField.value ||
                !addHouseColourField || !addHouseColourField.value ||
                !addHousemasterIdField || !addHousemasterIdField.value ||
                !addTermIdField || !addTermIdField.value ||
                !addSessionIdField || !addSessionIdField.value) {
                if (errorMsg) errorMsg.innerHTML = "Please fill all required fields";
                return false;
            }

            axios.post('/schoolhouse', {
                house: addHouseField.value,
                housecolour: addHouseColourField.value,
                housemasterid: addHousemasterIdField.value,
                termid: addTermIdField.value,
                sessionid: addSessionIdField.value,
                _token: document.querySelector('meta[name="csrf-token"]').content
            }).then(function (response) {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: response.data.message || "School house added successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
                window.location.reload();
            }).catch(function (error) {
                if (errorMsg) {
                    errorMsg.innerHTML = error.response?.data?.message || "Error adding school house";
                }
            });
        });
    }

    // Edit house
    var editHouseForm = document.getElementById("edit-house-form");
    if (editHouseForm) {
        editHouseForm.addEventListener("submit", function (e) {
            e.preventDefault();
            var errorMsg = document.getElementById("edit-alert-error-msg");
            if (errorMsg) {
                errorMsg.classList.remove("d-none");
                setTimeout(() => errorMsg.classList.add("d-none"), 2000);
            }

            if (!editHouseField || !editHouseField.value ||
                !editHouseColourField || !editHouseColourField.value ||
                !editHousemasterIdField || !editHousemasterIdField.value ||
                !editTermIdField || !editTermIdField.value ||
                !editSessionIdField || !editSessionIdField.value) {
                if (errorMsg) errorMsg.innerHTML = "Please fill all required fields";
                return false;
            }

            axios.post('/schoolhouse/updatehouse', {
                id: editIdField.value,
                house: editHouseField.value,
                housecolour: editHouseColourField.value,
                housemasterid: editHousemasterIdField.value,
                termid: editTermIdField.value,
                sessionid: editSessionIdField.value,
                _token: document.querySelector('meta[name="csrf-token"]').content
            }).then(function (response) {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: response.data.message || "School house updated successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
                window.location.reload();
            }).catch(function (error) {
                if (errorMsg) {
                    errorMsg.innerHTML = error.response?.data?.message || "Error updating school house";
                }
            });
        });
    }

    // Modal events
    var addModal = document.getElementById("addHouseModal");
    if (addModal) {
        addModal.addEventListener("show.bs.modal", function (e) {
            if (e.relatedTarget.classList.contains("add-btn")) {
                var modalLabel = document.getElementById("exampleModalLabel");
                var addBtn = document.getElementById("add-btn");
                if (modalLabel) modalLabel.innerHTML = "Add School House";
                if (addBtn) addBtn.innerHTML = "Add House";
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
            if (modalLabel) modalLabel.innerHTML = "Edit School House";
            if (updateBtn) updateBtn.innerHTML = "Update";
        });
        editModal.addEventListener("hidden.bs.modal", function () {
            clearEditFields();
        });
    }

    // Initialize listeners
    document.addEventListener("DOMContentLoaded", function () {
        var searchInput = document.querySelector(".search-box input.search");
        if (searchInput) {
            searchInput.addEventListener("input", debounce(function () {
                console.log("Search input changed:", searchInput.value);
                filterData();
            }, 300));
        } else {
            console.error("Search input not found!");
        }

        refreshCallbacks();
        ischeckboxcheck();
    });

    // Expose functions to global scope
    window.deleteMultiple = deleteMultiple;

} catch (error) {
    console.error("Error in schoolhouse.init.js:", error);
}