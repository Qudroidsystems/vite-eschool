console.log("subjectvetting.init.js is loaded and executing!");

let chartInitCount = 0;

// Verify dependencies
try {
    if (typeof axios === 'undefined') throw new Error("Axios is not loaded");
    if (typeof Swal === 'undefined') throw new Error("SweetAlert2 is not loaded");
    if (typeof bootstrap === 'undefined') throw new Error("Bootstrap is not loaded");
    if (typeof List === 'undefined') throw new Error("List.js is not loaded");
    if (typeof Chart === 'undefined') throw new Error("Chart.js is not loaded");
    console.log("All dependencies loaded successfully");
} catch (error) {
    console.error("Dependency check failed:", error);
}

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
if (!csrfToken) console.warn("CSRF token not found");

function debounce(func, wait) {
    let timeout;
    return function (...args) {
        const context = this;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}

function initializeSessionFilter() {
    const sessionFilter = document.getElementById('session-filter');
    if (!sessionFilter) return;

    sessionFilter.addEventListener('change', function() {
        const sessionId = this.value;
        if (sessionId) {
            window.location.href = '/subjectvetting?session=' + sessionId;
        } else {
            window.location.href = '/subjectvetting';
        }
    });
}

function initializeSubjectClassSearch() {
    const searchInput = document.getElementById('subjectClassSearch');
    const subjectClassItems = document.querySelectorAll('.subject-class-item');
    const clearSelectionBtn = document.getElementById('clearSelectionBtn');
    const selectedCountSpan = document.getElementById('selectedCount');
    const noResultsMessage = document.getElementById('noResultsMessage');

    if (!searchInput) return;

    const filterSubjectClasses = () => {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        subjectClassItems.forEach(item => {
            const searchText = item.getAttribute('data-search');
            const matches = searchTerm === '' || searchText.includes(searchTerm);

            if (matches) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        if (noResultsMessage) {
            noResultsMessage.style.display = visibleCount === 0 && searchTerm !== '' ? 'block' : 'none';
        }
    };

    const clearAllSelections = () => {
        subjectClassItems.forEach(item => {
            const checkbox = item.querySelector('input[type="checkbox"]');
            if (checkbox && !checkbox.disabled) {
                checkbox.checked = false;
            }
        });
        updateSelectionCount();
    };

    const updateSelectionCount = () => {
        const checkedBoxes = document.querySelectorAll('.subject-class-item input[type="checkbox"]:checked:not(:disabled)');
        if (selectedCountSpan) selectedCountSpan.textContent = checkedBoxes.length;
    };

    searchInput.addEventListener('input', filterSubjectClasses);
    searchInput.addEventListener('keyup', filterSubjectClasses);

    if (clearSelectionBtn) {
        clearSelectionBtn.addEventListener('click', clearAllSelections);
    }

    document.addEventListener('change', function(e) {
        if (e.target.matches('.subject-class-item input[type="checkbox"]')) {
            updateSelectionCount();
        }
    });

    filterSubjectClasses();
    updateSelectionCount();

    searchInput.removeAttribute('readonly');
    searchInput.removeAttribute('disabled');
    searchInput.style.pointerEvents = 'auto';
}

const checkAll = document.getElementById("checkAll");
if (checkAll) {
    checkAll.addEventListener("click", function () {
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

const addIdField = document.getElementById("add-id-field");
const addUserIdField = document.getElementById("userid");
const addSessionIdField = document.getElementById("sessionid");
const editIdField = document.getElementById("edit-id-field");
const editUserIdField = document.getElementById("edit-userid");
const editTermIdField = document.getElementById("edit-termid");
const editSessionIdField = document.getElementById("edit-sessionid");
const editSubjectClassIdField = document.getElementById("edit-subjectclassid");
const editStatusField = document.getElementById("edit-status");

let vettingStatusChart;

function initializeVettingStatusChart() {
    chartInitCount++;
    console.log(`Attempting to initialize chart (attempt #${chartInitCount}) with data:`, window.vettingStatusCounts);

    const ctx = document.getElementById('vettingStatusChart')?.getContext('2d');
    if (!ctx) {
        console.error("Chart canvas not found");
        return;
    }

    if (!window.vettingStatusCounts) {
        window.vettingStatusCounts = { pending: 0, completed: 0, rejected: 0 };
    }

    if (vettingStatusChart) {
        vettingStatusChart.destroy();
    }

    try {
        vettingStatusChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Pending', 'Completed', 'Rejected'],
                datasets: [{
                    label: 'Vetting Assignments',
                    data: [
                        window.vettingStatusCounts.pending || 0,
                        window.vettingStatusCounts.completed || 0,
                        window.vettingStatusCounts.rejected || 0
                    ],
                    backgroundColor: ['#dc3545', '#28a745', '#ffc107'],
                    borderColor: ['#c82333', '#218838', '#e0a800'],
                    borderWidth: 1,
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 },
                        title: { display: true, text: 'Number of Assignments', font: { weight: 'bold' } },
                        grid: { borderDash: [5, 5] }
                    },
                    x: {
                        title: { display: true, text: 'Status', font: { weight: 'bold' } },
                        grid: { display: false }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.raw}`;
                            }
                        }
                    }
                }
            }
        });
        console.log("Vetting status chart initialized successfully");
    } catch (error) {
        console.error("Failed to initialize chart:", error);
    }
}

function initializeCheckboxes() {
    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
    checkboxes.forEach((checkbox) => {
        checkbox.removeEventListener("change", handleCheckboxChange);
        checkbox.addEventListener("change", handleCheckboxChange);
    });
}

function handleCheckboxChange(e) {
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

const createButton = document.querySelector('.add-btn');
if (createButton) {
    createButton.addEventListener('click', function (e) {
        e.preventDefault();
        try {
            const modal = new bootstrap.Modal(document.getElementById("addSubjectVettingModal"));
            modal.show();
        } catch (error) {
            console.error("Error opening add modal:", error);
            Swal.fire({
                icon: "error",
                title: "Error opening modal",
                text: "Please ensure Bootstrap is loaded and try again.",
                showConfirmButton: true
            });
        }
    });
}

document.addEventListener('click', function (e) {
    const editBtn = e.target.closest('.edit-item-btn');
    const removeBtn = e.target.closest('.remove-item-btn');
    const image = e.target.closest('.staff-image');

    if (editBtn) {
        handleEditClick(e, editBtn);
    } else if (removeBtn) {
        handleRemoveClick(e, removeBtn);
    } else if (image) {
        handleImageClick(e, image);
    }
});

function handleImageClick(e, image) {
    e.preventDefault();
    const imageUrl = image.getAttribute('data-image');
    const teacherName = image.getAttribute('data-teachername');
    const fileExists = image.getAttribute('data-file-exists') === 'true';
    const defaultExists = image.getAttribute('data-default-exists') === 'true';

    const previewImage = document.getElementById('preview-image');
    const previewTeacherName = document.getElementById('preview-teachername');

    if (previewImage && previewTeacherName) {
        if (fileExists || (image.getAttribute('data-picture') === 'none' && defaultExists)) {
            previewImage.src = imageUrl;
            previewTeacherName.textContent = teacherName || 'Unknown Staff';
        } else {
            previewImage.src = '/storage/staff_avatars/unnamed.jpg';
            previewTeacherName.textContent = teacherName || 'Unknown Staff';
        }

        previewImage.onerror = function() {
            this.src = '/storage/staff_avatars/unnamed.jpg';
        };

        try {
            const modal = new bootstrap.Modal(document.getElementById('imageViewModal'));
            modal.show();
        } catch (error) {
            console.error("Error opening image preview modal:", error);
        }
    }
}

function handleRemoveClick(e, button) {
    e.preventDefault();
    const itemId = button.closest("tr").querySelector(".id")?.getAttribute("data-id");
    const deleteUrl = button.closest("tr").getAttribute("data-url");

    if (!itemId || !deleteUrl) return;

    const modal = new bootstrap.Modal(document.getElementById("deleteRecordModal"));
    modal.show();

    const deleteButton = document.getElementById("delete-record");
    if (deleteButton) {
        deleteButton.onclick = null;
        deleteButton.onclick = function () {
            axios.delete(deleteUrl)
                .then(function (response) {
                    Swal.fire({
                        icon: "success",
                        title: "Deleted!",
                        text: response.data.message || "Subject Vetting assignment deleted successfully!",
                        timer: 2000,
                        showConfirmButton: false
                    });
                    modal.hide();
                    setTimeout(() => refreshTable(), 500);
                })
                .catch(function (error) {
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: error.response?.data?.message || "An error occurred",
                        showConfirmButton: true
                    });
                    modal.hide();
                });
        };
    }
}

function handleEditClick(e, button) {
    e.preventDefault();
    const itemId = button.closest("tr").querySelector(".id")?.getAttribute("data-id");
    const tr = button.closest("tr");

    if (!itemId) return;

    const vettingUserId = tr.querySelector(".vetting_username")?.getAttribute("data-vetting_userid") || "";
    const subjectClassId = tr.querySelector(".subjectname")?.getAttribute("data-subjectclassid") || "";
    const termId = tr.querySelector(".termname")?.getAttribute("data-termid") || "";
    const sessionId = tr.querySelector(".sessionname")?.getAttribute("data-sessionid") || "";
    const status = tr.querySelector(".status span")?.textContent.trim().toLowerCase() || "pending";

    if (editIdField) editIdField.value = itemId;
    if (editUserIdField) editUserIdField.value = vettingUserId;
    if (editSubjectClassIdField) editSubjectClassIdField.value = subjectClassId;
    if (editTermIdField) editTermIdField.value = termId;
    if (editSessionIdField) editSessionIdField.value = sessionId;
    if (editStatusField) editStatusField.value = status;

    try {
        const modal = new bootstrap.Modal(document.getElementById("editModal"));
        modal.show();
    } catch (error) {
        console.error("Error opening edit modal:", error);
        Swal.fire({
            icon: "error",
            title: "Error opening edit modal",
            text: "Please try again or contact support.",
            showConfirmButton: true
        });
    }
}

function clearAddFields() {
    if (addIdField) addIdField.value = "";
    if (addUserIdField) addUserIdField.value = "";
    if (addSessionIdField) addSessionIdField.value = "";
    document.querySelectorAll('#addSubjectVettingModal input[name="termid[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.querySelectorAll('#addSubjectVettingModal input[name="subjectclassid[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
}

function clearEditFields() {
    if (editIdField) editIdField.value = "";
    if (editUserIdField) editUserIdField.value = "";
    if (editTermIdField) editTermIdField.value = "";
    if (editSessionIdField) editSessionIdField.value = "";
    if (editSubjectClassIdField) editSubjectClassIdField.value = "";
    if (editStatusField) editStatusField.value = "pending";
}

function deleteMultiple() {
    const ids_array = [];
    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]:checked');

    checkboxes.forEach((checkbox) => {
        const id = checkbox.closest("tr").querySelector(".id")?.getAttribute("data-id");
        if (id) ids_array.push(id);
    });

    if (ids_array.length === 0) {
        Swal.fire({
            title: "Please select at least one checkbox",
            icon: "warning",
            showConfirmButton: true
        });
        return;
    }

    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            Promise.all(ids_array.map((id) => axios.delete(`/subjectvetting/${id}`)))
                .then(() => {
                    Swal.fire({
                        title: "Deleted!",
                        text: "Your subject vetting assignments have been deleted.",
                        icon: "success",
                        timer: 2000,
                        showConfirmButton: false
                    });
                    setTimeout(() => refreshTable(), 1000);
                })
                .catch((error) => {
                    console.error("Bulk delete error:", error);
                    Swal.fire({
                        title: "Error!",
                        text: error.response?.data?.message || "Failed to delete subject vetting assignments",
                        icon: "error",
                        showConfirmButton: true
                    });
                });
        }
    });
}

let subjectVettingList;

function initializeListJS() {
    const subjectVettingListContainer = document.getElementById('kt_subject_vetting_table');
    const hasRows = document.querySelectorAll('#kt_subject_vetting_table tbody tr:not(.noresult)').length > 0;

    if (subjectVettingListContainer && hasRows) {
        try {
            if (subjectVettingList) {
                subjectVettingList.clear();
            }

            subjectVettingList = new List('subjectVettingList', {
                valueNames: ['sn', 'vetting_username', 'subjectname', 'sclass', 'schoolarm', 'teachername', 'termname', 'sessionname', 'status', 'datereg'],
                page: 10,
                pagination: {
                    innerWindow: 2,
                    outerWindow: 1,
                    left: 0,
                    right: 0,
                    paginationClass: "listjs-pagination"
                },
                listClass: 'list'
            });

            subjectVettingList.on('updated', function () {
                const totalRecords = subjectVettingList.items.length;
                const visibleRecords = subjectVettingList.visibleItems.length;
                const showingRecords = Math.min(visibleRecords, subjectVettingList.page);
                const currentPage = Math.ceil((subjectVettingList.i - 1) / subjectVettingList.page) + 1;

                document.getElementById('showing-records').textContent = showingRecords;
                document.getElementById('total-records-footer').textContent = totalRecords;

                const pagination = document.querySelector('.listjs-pagination');
                if (pagination) {
                    pagination.querySelectorAll('.page').forEach(page => {
                        page.classList.remove('active');
                        if (parseInt(page.textContent) === currentPage) {
                            page.classList.add('active');
                        }
                    });
                }

                const noResultRow = document.querySelector('.noresult');
                if (noResultRow) {
                    noResultRow.style.display = visibleRecords === 0 ? 'block' : 'none';
                }

                initializeCheckboxes();
            });

            subjectVettingList.update();

        } catch (error) {
            console.error("List.js initialization failed:", error);
        }
    } else {
        document.getElementById('showing-records').textContent = 0;
        document.getElementById('total-records-footer').textContent = 0;
    }
}

function filterData() {
    const searchInput = document.querySelector(".search-box input.search");
    const searchValue = searchInput?.value || "";

    if (subjectVettingList) {
        subjectVettingList.search(searchValue, ['sn', 'vetting_username', 'subjectname', 'sclass', 'schoolarm', 'teachername', 'termname', 'sessionname', 'status']);
        subjectVettingList.update();
    }
}

let isRefreshing = false;

function refreshTable() {
    if (isRefreshing) return;
    isRefreshing = true;

    axios.get('/subjectvetting', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => {
        if (!response.data || !response.data.subjectvettings) {
            throw new Error('Invalid response data structure');
        }

        if (subjectVettingList) {
            subjectVettingList.clear();

            response.data.subjectvettings.forEach((item, index) => {
                subjectVettingList.add({
                    sn: index + 1,
                    vetting_username: item.vetting_username,
                    subjectname: `${item.subjectname} ${item.subjectcode ? `(${item.subjectcode})` : ''}`,
                    sclass: item.sclass,
                    schoolarm: item.schoolarm || '',
                    teachername: item.teachername || '',
                    termname: item.termname,
                    sessionname: item.sessionname,
                    status: `<span class="badge-status ${item.status === 'completed' ? 'badge-completed' : (item.status === 'pending' ? 'badge-pending' : 'badge-rejected')}">
                                <i class="${item.status === 'completed' ? 'ri-checkbox-circle-line' : (item.status === 'pending' ? 'ri-time-line' : 'ri-close-circle-line')} me-1 fs-10"></i>
                                ${item.status.charAt(0).toUpperCase() + item.status.slice(1)}
                            </span>`,
                    datereg: item.updated_at.split(' ')[0]
                });
            });

            subjectVettingList.update();

            window.vettingStatusCounts = response.data.statusCounts || { pending: 0, completed: 0, rejected: 0 };
            initializeVettingStatusChart();
            initializeCheckboxes();

            document.getElementById('stat-total').textContent = response.data.subjectvettings.length;
            document.getElementById('stat-pending').textContent = window.vettingStatusCounts.pending || 0;
            document.getElementById('stat-completed').textContent = window.vettingStatusCounts.completed || 0;
            document.getElementById('stat-rejected').textContent = window.vettingStatusCounts.rejected || 0;
        }
    })
    .catch(error => {
        console.error("Error refreshing table and chart:", error);
        Swal.fire({
            icon: "error",
            title: "Error refreshing data",
            text: error.response?.data?.message || "An error occurred",
            showConfirmButton: true
        });
    })
    .finally(() => {
        isRefreshing = false;
    });
}

const addSubjectVettingForm = document.getElementById("add-subjectvetting-form");
if (addSubjectVettingForm) {
    addSubjectVettingForm.addEventListener("submit", function (e) {
        e.preventDefault();

        const errorMsg = document.getElementById("alert-error-msg");
        if (errorMsg) errorMsg.classList.add("d-none");

        const userId = document.getElementById("userid")?.value;
        const termIds = Array.from(document.querySelectorAll('#addSubjectVettingModal input[name="termid[]"]:checked')).map(cb => cb.value);
        const sessionId = document.getElementById("sessionid")?.value;
        const subjectClassCheckboxes = document.querySelectorAll('#addSubjectVettingModal input[name="subjectclassid[]"]:checked:not(:disabled)');
        const subjectClassIds = [...new Set(Array.from(subjectClassCheckboxes).map(cb => cb.value))];

        if (!userId) {
            if (errorMsg) { errorMsg.innerHTML = "Please select a staff member"; errorMsg.classList.remove("d-none"); }
            return;
        }
        if (termIds.length === 0) {
            if (errorMsg) { errorMsg.innerHTML = "Please select at least one term"; errorMsg.classList.remove("d-none"); }
            return;
        }
        if (!sessionId) {
            if (errorMsg) { errorMsg.innerHTML = "Please select a session"; errorMsg.classList.remove("d-none"); }
            return;
        }
        if (subjectClassIds.length === 0) {
            if (errorMsg) { errorMsg.innerHTML = "Please select at least one subject-class from current session"; errorMsg.classList.remove("d-none"); }
            return;
        }

        const submitBtn = document.getElementById("add-btn");
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Adding...';
        }

        axios.post('/subjectvetting', {
            userid: userId,
            termid: termIds,
            sessionid: sessionId,
            subjectclassid: subjectClassIds
        })
        .then(function (response) {
            const modal = bootstrap.Modal.getInstance(document.getElementById("addSubjectVettingModal"));
            if (modal) modal.hide();

            Swal.fire({
                icon: "success",
                title: "Success!",
                text: response.data.message || "Subject Vetting assignment(s) added successfully!",
                timer: 2000,
                showConfirmButton: false
            });

            setTimeout(() => refreshTable(), 500);
        })
        .catch(function (error) {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="ri-save-line me-1"></i>Add Assignment';
            }
            if (errorMsg) {
                errorMsg.innerHTML = error.response?.data?.message || "Error adding subject vetting assignment";
                errorMsg.classList.remove("d-none");
            }
        });
    });
}

const editSubjectVettingForm = document.getElementById("edit-subjectvetting-form");
if (editSubjectVettingForm) {
    editSubjectVettingForm.addEventListener("submit", function (e) {
        e.preventDefault();

        const errorMsg = document.getElementById("edit-alert-error-msg");
        if (errorMsg) errorMsg.classList.add("d-none");

        const id = editIdField?.value;
        const userId = editUserIdField?.value;
        const termId = editTermIdField?.value;
        const sessionId = editSessionIdField?.value;
        const subjectClassId = editSubjectClassIdField?.value;
        const status = editStatusField?.value;

        if (!id || !userId || !termId || !sessionId || !subjectClassId || !status) {
            if (errorMsg) {
                errorMsg.innerHTML = "Please fill all required fields";
                errorMsg.classList.remove("d-none");
            }
            return;
        }

        const submitBtn = document.getElementById("update-btn");
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Updating...';
        }

        axios.put(`/subjectvetting/${id}`, {
            userid: userId,
            termid: termId,
            sessionid: sessionId,
            subjectclassid: subjectClassId,
            status
        })
        .then(function (response) {
            const modal = bootstrap.Modal.getInstance(document.getElementById("editModal"));
            if (modal) modal.hide();

            Swal.fire({
                icon: "success",
                title: "Updated!",
                text: response.data.message || "Subject Vetting assignment updated successfully!",
                timer: 2000,
                showConfirmButton: false
            });

            setTimeout(() => refreshTable(), 500);
        })
        .catch(function (error) {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="ri-save-line me-1"></i>Update';
            }
            if (errorMsg) {
                errorMsg.innerHTML = error.response?.data?.message || "Error updating subject vetting assignment";
                errorMsg.classList.remove("d-none");
            }
        });
    });
}

const addModal = document.getElementById("addSubjectVettingModal");
if (addModal) {
    addModal.addEventListener("show.bs.modal", function () {
        const addBtn = document.getElementById("add-btn");
        if (addBtn) {
            addBtn.disabled = true;
            addBtn.innerHTML = '<i class="ri-save-line me-1"></i>Add Assignment';
        }

        setTimeout(() => {
            initializeSubjectClassSearch();
            const searchInput = document.getElementById('subjectClassSearch');
            if (searchInput) searchInput.focus();
        }, 50);

        const updateSubmitButton = () => {
            const userId = document.getElementById("userid")?.value;
            const sessionId = document.getElementById("sessionid")?.value;
            const checkedTerms = document.querySelectorAll('#addSubjectVettingModal input[name="termid[]"]:checked').length;
            const checkedClasses = document.querySelectorAll('#addSubjectVettingModal input[name="subjectclassid[]"]:checked:not(:disabled)').length;
            if (addBtn) addBtn.disabled = !userId || !sessionId || checkedTerms === 0 || checkedClasses === 0;
        };

        const updateSelectionCount = () => {
            const checkedBoxes = document.querySelectorAll('#addSubjectVettingModal .subject-class-item input[type="checkbox"]:checked:not(:disabled)');
            const selectedCountSpan = document.getElementById('selectedCount');
            if (selectedCountSpan) selectedCountSpan.textContent = checkedBoxes.length;
        };

        const userIdSelect = document.getElementById("userid");
        const sessionIdSelect = document.getElementById("sessionid");
        const termCheckboxes = document.querySelectorAll('#addSubjectVettingModal input[name="termid[]"]');
        const subjectClassCheckboxes = document.querySelectorAll('#addSubjectVettingModal input[name="subjectclassid[]"]');

        if (userIdSelect) userIdSelect.addEventListener("change", updateSubmitButton);
        if (sessionIdSelect) sessionIdSelect.addEventListener("change", updateSubmitButton);
        termCheckboxes.forEach(cb => cb.addEventListener("change", updateSubmitButton));
        subjectClassCheckboxes.forEach(cb => {
            if (!cb.disabled) cb.addEventListener("change", () => { updateSubmitButton(); updateSelectionCount(); });
        });

        updateSubmitButton();
        updateSelectionCount();
    });

    addModal.addEventListener("hidden.bs.modal", function () {
        clearAddFields();
        const errorMsg = document.getElementById("alert-error-msg");
        if (errorMsg) errorMsg.classList.add("d-none");

        const addBtn = document.getElementById("add-btn");
        if (addBtn) {
            addBtn.disabled = true;
            addBtn.innerHTML = '<i class="ri-save-line me-1"></i>Add Assignment';
        }

        const searchInput = document.getElementById('subjectClassSearch');
        if (searchInput) {
            searchInput.value = '';
            const event = new Event('input', { bubbles: true });
            searchInput.dispatchEvent(event);
        }

        const noResultsMessage = document.getElementById('noResultsMessage');
        if (noResultsMessage) noResultsMessage.style.display = 'none';

        setTimeout(() => {
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop && backdrop.parentNode) backdrop.parentNode.removeChild(backdrop);
        }, 300);
    });
}

const editModalElement = document.getElementById("editModal");
if (editModalElement) {
    editModalElement.addEventListener("show.bs.modal", function () {
        const updateBtn = document.getElementById("update-btn");
        if (updateBtn) {
            updateBtn.disabled = false;
            updateBtn.innerHTML = '<i class="ri-save-line me-1"></i>Update';
        }
    });

    editModalElement.addEventListener("hidden.bs.modal", function () {
        clearEditFields();
        const errorMsg = document.getElementById("edit-alert-error-msg");
        if (errorMsg) errorMsg.classList.add("d-none");

        const updateBtn = document.getElementById("update-btn");
        if (updateBtn) {
            updateBtn.disabled = false;
            updateBtn.innerHTML = '<i class="ri-save-line me-1"></i>Update';
        }

        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) backdrop.remove();

        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });
}

document.addEventListener('DOMContentLoaded', function () {
    initializeListJS();
    initializeVettingStatusChart();
    initializeCheckboxes();
    initializeSessionFilter();

    const searchInput = document.querySelector(".search-box input.search");
    if (searchInput) {
        searchInput.addEventListener("input", debounce(filterData, 300));
    }
});
