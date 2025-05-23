console.log("schoolclass.init.js is loaded and executing!");

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
const addSchoolClassField = document.getElementById("schoolclass");
const addArmField = document.getElementById("arm_id");
const addClassCategoryIdField = document.getElementById("classcategoryid");
const addSubmitButton = document.getElementById("add-btn");
const editIdField = document.getElementById("edit-id-field");
const editSchoolClassField = document.getElementById("edit-schoolclass");
const editArmField = document.getElementById("edit-arm_id");
const editClassCategoryIdField = document.getElementById("edit-classcategoryid");
const editSubmitButton = document.getElementById("update-btn");

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
            if (schoolClassList) {
                schoolClassList.reIndex();
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

// Delete single school class
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
            console.log("Deleting school class:", itemId);
            axios.delete(deleteUrl)
                .then(function (response) {
                    console.log("Delete success:", response.data);
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: response.data.message || "School class deleted successfully!",
                        showConfirmButton: false,
                        timer: 2000,
                        showCloseButton: true
                    });
                    if (schoolClassList) {
                        schoolClassList.remove("schoolclassid", document.querySelector(`tr[data-id="${itemId}"] .schoolclassid`).innerText);
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
                            '<tr><td colspan="7" class="noresult" style="display: block;">No results found</td></tr>';
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
                        title: "Error deleting school class",
                        text: error.response?.data?.message || "An error occurred",
                        showConfirmButton: true
                    });
                    modal.hide();
                });
        };
    }
}

// Edit school class
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
    console.log("Edit Item ID:", itemId);
    if (editSchoolClassField) editSchoolClassField.value = tr.querySelector(".schoolclass")?.innerText || "";
    if (editArmField) {
        const armName = tr.querySelector(".arm")?.innerText || "";
        const option = Array.from(editArmField.options).find(opt => opt.text === armName);
        editArmField.value = option ? option.value : "";
    }
    if (editClassCategoryIdField) {
        const categoryName = tr.querySelector(".classcategory")?.innerText || "";
        const option = Array.from(editClassCategoryIdField.options).find(opt => opt.text === categoryName);
        editClassCategoryIdField.value = option ? option.value : "";
    }
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
    if (addSchoolClassField) addSchoolClassField.value = "";
    if (addArmField) addArmField.value = "";
    if (addClassCategoryIdField) addClassCategoryIdField.value = "";
    const errorMsg = document.getElementById("alert-error-msg");
    if (errorMsg) errorMsg.classList.add("d-none");
}

function clearEditFields() {
    if (editIdField) editIdField.value = "";
    if (editSchoolClassField) editSchoolClassField.value = "";
    if (editArmField) editArmField.value = "";
    if (editClassCategoryIdField) editClassCategoryIdField.value = "";
    const errorMsg = document.getElementById("edit-alert-error-msg");
    if (errorMsg) errorMsg.classList.add("d-none");
}

// Delete multiple school classes
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
            Promise.all(ids_array.map((id) => axios.delete(`/schoolclass/${id}`)))
                .then(() => {
                    Swal.fire({
                        title: "Deleted!",
                        text: "Your school classes have been deleted.",
                        icon: "success",
                        confirmButtonClass: "btn btn-info w-xs mt-2",
                        buttonsStyling: false
                    });
                    ids_array.forEach(id => {
                        const row = document.querySelector(`tr[data-id="${id}"]`);
                        if (row) row.remove();
                    });
                    if (schoolClassList) schoolClassList.reIndex();
                    // Update badge
                    const badge = document.querySelector('.badge.bg-dark-subtle');
                    if (badge) {
                        const currentTotal = parseInt(badge.textContent);
                        badge.textContent = currentTotal - ids_array.length;
                    }
                    // Update noresult display
                    const noResult = document.querySelector(".noresult");
                    const rowCount = document.querySelectorAll("#kt_roles_view_table tbody tr").length;
                    if (noResult) {
                        noResult.style.display = rowCount === 0 ? "block" : "none";
                    } else if (rowCount === 0) {
                        document.querySelector("#kt_roles_view_table tbody").innerHTML =
                            '<tr><td colspan="7" class="noresult" style="display: block;">No results found</td></tr>';
                    }
                    // Fetch previous page if table is empty
                    if (rowCount === 0 && document.querySelector("#pagination-element .pagination-prev")) {
                        const prevUrl = document.querySelector("#pagination-element .pagination-prev").getAttribute("data-url");
                        console.log("Fetching previous page:", prevUrl);
                        fetchPage(prevUrl);
                    }
                })
                .catch((error) => {
                    console.error("Bulk delete error:", error);
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
}

// Initialize List.js for client-side filtering
let schoolClassList;
const schoolClassListContainer = document.getElementById('schoolClassList');
if (schoolClassListContainer && document.querySelectorAll('#schoolClassList tbody tr').length > 0) {
    try {
        schoolClassList = new List('schoolClassList', {
            valueNames: ['schoolclassid', 'schoolclass', 'arm', 'classcategory', 'datereg'],
            page: 1000,
            pagination: false,
            listClass: 'list'
        });
        console.log("List.js initialized");
    } catch (error) {
        console.error("List.js initialization failed:", error);
    }
} else {
    console.warn("No school classes available for List.js initialization");
}

// Update no results message
if (schoolClassList) {
    schoolClassList.on('searchComplete', function () {
        const noResultRow = document.querySelector('.noresult');
        if (noResultRow) {
            noResultRow.style.display = schoolClassList.visibleItems.length === 0 ? 'block' : 'none';
        }
    });
}

// Filter data (client-side and server-side)
function filterData(searchValue) {
    console.log("Filtering with search:", searchValue);
    // Client-side filtering
    if (schoolClassList) {
        schoolClassList.search(searchValue, ['schoolclass', 'arm', 'classcategory']);
    }
    // Server-side search
    const url = new URL(window.location.origin + '/schoolclass');
    if (searchValue) {
        url.searchParams.set('search', searchValue);
    }
    fetchPage(url.toString());
}

// Add school class
const addSchoolClassForm = document.getElementById("add-schoolclass-form");
if (addSchoolClassForm) {
    addSchoolClassForm.addEventListener("submit", function (e) {
        e.preventDefault();
        console.log("Add form submitted");
        const errorMsg = document.getElementById("alert-error-msg");
        if (errorMsg) errorMsg.classList.add("d-none");
        const formData = new FormData(addSchoolClassForm);
        const schoolclass = formData.get('schoolclass');
        const arm_id = formData.get('arm_id');
        const classcategoryid = formData.get('classcategoryid');
        if (!schoolclass || !arm_id || !classcategoryid) {
            if (errorMsg) {
                errorMsg.innerHTML = "Please fill in all required fields";
                errorMsg.classList.remove("d-none");
            }
            return;
        }
        console.log("Sending add request:", { schoolclass, arm_id, classcategoryid });
        axios.post('/schoolclass', { schoolclass, arm_id, classcategoryid })
            .then(function (response) {
                console.log("Add success:", response.data);
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
                const rowCount = document.querySelectorAll('#kt_roles_view_table tbody tr').length + 1;
                const newRow = document.createElement('tr');
                newRow.setAttribute('data-url', `/schoolclass/${response.data.schoolclass.id}`);
                newRow.setAttribute('data-id', response.data.schoolclass.id);
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
                }
                // Update badge
                const badge = document.querySelector('.badge.bg-dark-subtle');
                if (badge) {
                    const currentTotal = parseInt(badge.textContent);
                    badge.textContent = currentTotal + 1;
                }
                // Close modal
                const addModal = bootstrap.Modal.getInstance(document.getElementById("addSchoolClassModal"));
                if (addModal) addModal.hide();
                // Reset checkboxes
                initializeCheckboxes();
            })
            .catch(function (error) {
                console.error("Add error:", error.response?.data || error);
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
const editSchoolClassForm = document.getElementById("edit-schoolclass-form");
if (editSchoolClassForm) {
    editSchoolClassForm.addEventListener("submit", function (e) {
        e.preventDefault();
        console.log("Edit form submitted");
        const errorMsg = document.getElementById("edit-alert-error-msg");
        if (errorMsg) errorMsg.classList.add("d-none");
        const formData = new FormData(editSchoolClassForm);
        const schoolclass = formData.get('schoolclass');
        const arm_id = formData.get('arm_id');
        const classcategoryid = formData.get('classcategoryid');
        const id = editIdField?.value;
        if (!id || !schoolclass || !arm_id || !classcategoryid) {
            if (errorMsg) {
                errorMsg.innerHTML = "Please fill in all required fields";
                errorMsg.classList.remove("d-none");
            }
            return;
        }
        console.log("Sending edit request:", { id, schoolclass, arm_id, classcategoryid });
        axios.post(`/schoolclass/${id}`, {
            _method: 'PUT',
            schoolclass,
            arm_id,
            classcategoryid
        })
            .then(function (response) {
                console.log("Edit success:", response.data);
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "School class updated successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
                // Update the table row
                const row = document.querySelector(`tr[data-id="${id}"]`);
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
            })
            .catch(function (error) {
                console.error("Edit error:", error.response?.data || error);
                if (errorMsg) {
                    const errors = error.response?.data?.errors;
                    let errorMessage = "Error updating school class";
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

// Modal events
const addModal = document.getElementById("addSchoolClassModal");
if (addModal) {
    addModal.addEventListener("show.bs.modal", function (e) {
        console.log("Add modal show event");
        const modalLabel = document.getElementById("exampleModalLabel");
        const addBtn = document.getElementById("add-btn");
        if (modalLabel) modalLabel.innerHTML = "Add School Class";
        if (addBtn) addBtn.innerHTML = "Add Class";
    });
    addModal.addEventListener("hidden.bs.modal", function () {
        console.log("Add modal hidden");
        clearAddFields();
    });
}

const editModal = document.getElementById("editModal");
if (editModal) {
    editModal.addEventListener("show.bs.modal", function () {
        console.log("Edit modal show event");
        const modalLabel = document.getElementById("editModalLabel");
        const updateBtn = document.getElementById("update-btn");
        if (modalLabel) modalLabel.innerHTML = "Edit School Class";
        if (updateBtn) updateBtn.innerHTML = "Update";
    });
    editModal.addEventListener("hidden.bs.modal", function () {
        console.log("Edit modal hidden");
        clearEditFields();
    });
}

// Initialize listeners
document.addEventListener("DOMContentLoaded", function () {
    console.log("DOMContentLoaded fired");
    const searchInput = document.querySelector(".search-box input.search");
    if (searchInput) {
        searchInput.addEventListener("input", debounce(function () {
            console.log("Search input changed:", searchInput.value);
            filterData(searchInput.value);
        }, 300));
    } else {
        console.error("Search input not found");
    }
    initializeCheckboxes();
});

// Expose functions to global scope
window.deleteMultiple = deleteMultiple;