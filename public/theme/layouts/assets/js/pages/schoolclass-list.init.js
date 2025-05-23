console.log("schoolclass.init.js is loaded and executing!");

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
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
console.log("CSRF Token:", csrfToken);
axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

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
var addSchoolClassField = document.getElementById("schoolclass");
var addArmField = document.getElementById("arm_id");
var addClassCategoryIdField = document.getElementById("classcategoryid");
var addSubmitButton = document.getElementById("add-btn");
var editIdField = document.getElementById("edit-id-field");
var editSchoolClassField = document.getElementById("edit-schoolclass");
var editArmField = document.getElementById("edit-arm_id");
var editClassCategoryIdField = document.getElementById("edit-classcategoryid");
var editSubmitButton = document.getElementById("update-btn");

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

// Delete single school class
function handleRemoveClick(e) {
    e.preventDefault();
    var itemId = e.target.closest("tr").querySelector(".id").getAttribute("data-id");
    var deleteButton = document.getElementById("delete-record");
    if (deleteButton) {
        deleteButton.addEventListener("click", function () {
            axios.delete(`/schoolclass/${itemId}`).then(function () {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "School class deleted successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
                const row = document.querySelector(`tr[data-url*="${itemId}"]`);
                if (row) row.remove();
                if (schoolClassList) schoolClassList.reIndex();
            }).catch(function (error) {
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error deleting school class",
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

// Edit school class
function handleEditClick(e) {
    e.preventDefault();
    var itemId = e.target.closest("tr").querySelector(".id").getAttribute("data-id");
    var tr = e.target.closest("tr");
    if (editIdField) editIdField.value = itemId;
    console.log("Edit Item ID:", itemId);
    if (editSchoolClassField) editSchoolClassField.value = tr.querySelector(".schoolclass").innerText;
    if (editArmField) {
        var armName = tr.querySelector(".arm").innerText;
        var option = Array.from(editArmField.options).find(opt => opt.text === armName);
        editArmField.value = option ? option.value : "";
    }
    if (editClassCategoryIdField) {
        var categoryName = tr.querySelector(".classcategory").innerText;
        var option = Array.from(editClassCategoryIdField.options).find(opt => opt.text === categoryName);
        editClassCategoryIdField.value = option ? option.value : "";
    }
    try {
        var modal = new bootstrap.Modal(document.getElementById("editModal"));
        modal.show();
    } catch (error) {
        console.error("Error opening edit modal:", error);
    }
}

// Clear form fields
function clearAddFields() {
    if (addIdField) addIdField.value = "";
    if (addSchoolClassField) addSchoolClassField.value = "";
    if (addArmField) addArmField.value = "";
    if (addClassCategoryIdField) addClassCategoryIdField.value = "";
    var errorMsg = document.getElementById("alert-error-msg");
    if (errorMsg) errorMsg.classList.add("d-none");
}

function clearEditFields() {
    if (editIdField) editIdField.value = "";
    if (editSchoolClassField) editSchoolClassField.value = "";
    if (editArmField) editArmField.value = "";
    if (editClassCategoryIdField) editClassCategoryIdField.value = "";
    var errorMsg = document.getElementById("edit-alert-error-msg");
    if (errorMsg) errorMsg.classList.add("d-none");
}

// Delete multiple school classes
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
                    return axios.delete(`/schoolclass/${id}`);
                })).then(() => {
                    Swal.fire({
                        title: "Deleted!",
                        text: "Your school classes have been deleted.",
                        icon: "success",
                        confirmButtonClass: "btn btn-info w-xs mt-2",
                        buttonsStyling: false
                    });
                    ids_array.forEach(id => {
                        const row = document.querySelector(`tr[data-url*="${id}"]`);
                        if (row) row.remove();
                    });
                    if (schoolClassList) schoolClassList.reIndex();
                }).catch((error) => {
                    Swal.fire({
                        title: "Error!",
                        text: error.response?.data?.message || "Failed to delete school classes",
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
var schoolClassList;
var schoolClassListContainer = document.getElementById('schoolClassList');
if (schoolClassListContainer && document.querySelectorAll('#schoolClassList tbody tr').length > 0) {
    try {
        schoolClassList = new List('schoolClassList', {
            valueNames: ['schoolclassid', 'schoolclass', 'arm', 'classcategory', 'datereg'],
            page: 1000,
            pagination: false,
            listClass: 'list'
        });
    } catch (error) {
        console.error("List.js initialization failed:", error);
    }
} else {
    console.warn("No school classes available for List.js initialization");
}

// Update no results message
if (schoolClassList) {
    schoolClassList.on('searchComplete', function () {
        var noResultRow = document.querySelector('.noresult');
        if (schoolClassList.visibleItems.length === 0) {
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
    if (schoolClassList) {
        schoolClassList.search(searchValue, ['schoolclass', 'arm', 'classcategory']);
    }
}




    // Add school class
    var addSchoolClassForm = document.getElementById("add-schoolclass-form");
    if (addSchoolClassForm) {
        addSchoolClassForm.addEventListener("submit", function (e) {
            e.preventDefault();
            var errorMsg = document.getElementById("alert-error-msg");
            if (errorMsg) errorMsg.classList.add("d-none");
            var formData = new FormData(addSchoolClassForm);
            var schoolclass = formData.get('schoolclass');
            var arm_id = formData.get('arm_id');
            var classcategoryid = formData.get('classcategoryid');

            if (!schoolclass || !arm_id || !classcategoryid) {
                if (errorMsg) {
                    errorMsg.innerHTML = "Please fill in all required fields";
                    errorMsg.classList.remove("d-none");
                }
                return;
            }

            console.log("Submitting Add School Class:", { schoolclass, arm_id, classcategoryid });

            axios.post('/schoolclass', {
                schoolclass: schoolclass,
                arm_id: arm_id,
                classcategoryid: classcategoryid
            }, {
                headers: { 'Content-Type': 'application/json' }
            }).then(function (response) {
                console.log("Add School Class Success - Full Response:", {
                    status: response.status,
                    data: response.data,
                    headers: response.headers
                });

                if (!response.data?.schoolclass) {
                    console.error("Response missing schoolclass object:", response.data);
                    if (errorMsg) {
                        errorMsg.innerHTML = "Invalid server response: missing schoolclass data";
                        errorMsg.classList.remove("d-none");
                    }
                    return;
                }

                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "School class added successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });

                // Add new row to table
                const tbody = document.querySelector('#kt_roles_view_table tbody');
                const rowCount = tbody.querySelectorAll('tr').length + 1;
                const newRow = document.createElement('tr');
                newRow.setAttribute('data-url', `/schoolclass/${response.data.schoolclass.id}`);
                newRow.innerHTML = `
                    <td class="id" data-id="${response.data.schoolclass.id}">
                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="chk_child" />
                        </div>
                    </td>
                    <td class="schoolclassid">${rowCount}</td>
                    <td class="schoolclass" data-schoolclass="${response.data.schoolclass.schoolclass}">${response.data.schoolclass.schoolclass}</td>
                    <td class="arm" data-arm="${response.data.schoolclass.arm_name || 'Unknown'}">${response.data.schoolclass.arm_name || 'Unknown'}</td>
                    <td class="classcategory" data-classcategory="${response.data.schoolclass.classcategory || 'Unknown'}">${response.data.schoolclass.classcategory || 'Unknown'}</td>
                    <td class="datereg">${new Date(response.data.schoolclass.updated_at).toISOString().split('T')[0]}</td>
                    <td>
                        <ul class="d-flex gap-2 list-unstyled mb-0">
                            <li><a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn"><i class="ph-pencil"></i></a></li>
                            <li><a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn"><i class="ph-trash"></i></a></li>
                        </ul>
                    </td>
                `;
                tbody.prepend(newRow);

                // Update List.js
                if (schoolClassList) {
                    try {
                        schoolClassList.add({
                            schoolclassid: rowCount.toString(),
                            schoolclass: response.data.schoolclass.schoolclass,
                            arm: response.data.schoolclass.arm_name || 'Unknown',
                            classcategory: response.data.schoolclass.classcategory || 'Unknown',
                            datereg: new Date(response.data.schoolclass.updated_at).toISOString().split('T')[0]
                        });
                        schoolClassList.reIndex();
                        console.log("List.js updated successfully");
                    } catch (error) {
                        console.error("List.js update failed:", error);
                    }
                } else {
                    console.warn("List.js not initialized");
                }

                // Close modal
                const addModal = bootstrap.Modal.getInstance(document.getElementById("addSchoolClassModal"));
                if (addModal) addModal.hide();
            }).catch(function (error) {
                console.error("Add School Class Full Error:", {
                    message: error.message,
                    response: error.response,
                    status: error.response?.status,
                    data: error.response?.data,
                    headers: error.response?.headers
                });
                if (errorMsg) {
                    const errors = error.response?.data?.errors;
                    let errorMessage = "Error adding school class";
                    if (errors) {
                        errorMessage = Object.values(errors).flat().join(", ");
                    } else if (error.response?.data?.message) {
                        errorMessage = error.response.data.message;
                    } else if (error.message) {
                        errorMessage = error.message;
                    }
                    errorMsg.innerHTML = errorMessage;
                    errorMsg.classList.remove("d-none");
                }
            });
        });
    }



// Edit school class
var editSchoolClassForm = document.getElementById("edit-schoolclass-form");
if (editSchoolClassForm) {
    editSchoolClassForm.addEventListener("submit", function (e) {
        e.preventDefault();
        var errorMsg = document.getElementById("edit-alert-error-msg");
        if (errorMsg) errorMsg.classList.add("d-none");
        var formData = new FormData(editSchoolClassForm);
        var schoolclass = formData.get('schoolclass');
        var arm_id = formData.get('arm_id');
        var classcategoryid = formData.get('classcategoryid');
        var id = editIdField.value;

        if (!schoolclass || !arm_id || !classcategoryid) {
            if (errorMsg) {
                errorMsg.innerHTML = "Please fill in all required fields";
                errorMsg.classList.remove("d-none");
            }
            return;
        }

        console.log("Submitting Edit School Class:", { id, schoolclass, arm_id, classcategoryid });
        axios.post(`/schoolclass/${id}`, {
            _method: 'PUT',
            schoolclass: schoolclass,
            arm_id: arm_id,
            classcategoryid: classcategoryid
        }, {
            headers: { 'Content-Type': 'application/json' }
        }).then(function (response) {
            console.log("Edit School Class Success:", response.data);
            Swal.fire({
                position: "center",
                icon: "success",
                title: "School class updated successfully!",
                showConfirmButton: false,
                timer: 2000,
                showCloseButton: true
            });

            // Update the table row
            const row = document.querySelector(`tr[data-url*="${id}"]`);
            if (row) {
                const schoolClassCell = row.querySelector('.schoolclass');
                const armCell = row.querySelector('.arm');
                const categoryCell = row.querySelector('.classcategory');
                const dateCell = row.querySelector('.datereg');

                schoolClassCell.innerText = response.data.schoolclass.schoolclass;
                schoolClassCell.dataset.schoolclass = response.data.schoolclass.schoolclass;
                armCell.innerText = response.data.schoolclass.arm_name;
                armCell.dataset.arm = response.data.schoolclass.arm_name;
                categoryCell.innerText = response.data.schoolclass.classcategory;
                categoryCell.dataset.classcategory = response.data.schoolclass.classcategory;
                dateCell.innerText = new Date(response.data.schoolclass.updated_at).toISOString().split('T')[0];

                if (schoolClassList) {
                    const item = schoolClassList.get('schoolclassid', row.querySelector('.schoolclassid').innerText)[0];
                    if (item) {
                        item.values({
                            schoolclass: response.data.schoolclass.schoolclass,
                            arm: response.data.schoolclass.arm_name,
                            classcategory: response.data.schoolclass.classcategory,
                            datereg: new Date(response.data.schoolclass.updated_at).toISOString().split('T')[0]
                        });
                    }
                }

                row.classList.add('table-success');
                setTimeout(() => row.classList.remove('table-success'), 2000);
            }

            // Close modal
            const editModal = bootstrap.Modal.getInstance(document.getElementById("editModal"));
            if (editModal) editModal.hide();
        }).catch(function (error) {
            console.error("Edit School Class Error:", error.response);
            if (errorMsg) {
                errorMsg.innerHTML = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat().join(", ") || "Error updating school class";
                errorMsg.classList.remove("d-none");
            }
        });
    });
}

// Modal events
var addModal = document.getElementById("addSchoolClassModal");
if (addModal) {
    addModal.addEventListener("show.bs.modal", function (e) {
        if (e.relatedTarget.classList.contains("add-btn")) {
            var modalLabel = document.getElementById("exampleModalLabel");
            var addBtn = document.getElementById("add-btn");
            if (modalLabel) modalLabel.innerHTML = "Add School Class";
            if (addBtn) addBtn.innerHTML = "Add Class";
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
        if (modalLabel) modalLabel.innerHTML = "Edit School Class";
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