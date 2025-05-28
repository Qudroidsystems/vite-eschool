// Ensure Axios is available
function ensureAxios() {
    if (typeof axios === 'undefined') {
        console.error('Error: Axios is not defined');
        Swal.fire({
            title: "Error!",
            text: "Axios library is missing",
            icon: "error",
            confirmButtonClass: "btn btn-primary",
            buttonsStyling: true
        });
        return false;
    }
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) {
        console.error('Error: CSRF token not found');
        Swal.fire({
            title: "Error!",
            text: "CSRF token is missing",
            icon: "error",
            confirmButtonClass: "btn btn-primary",
            buttonsStyling: true
        });
        return false;
    }
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    return true;
}

// Populate States
function populateStates(stateSelectId, lgaSelectId) {
    const stateSelect = document.getElementById(stateSelectId);
    stateSelect.innerHTML = '<option value="">Select State</option>';

    axios.get('/states_lgas.json').then((response) => {
        console.log("States data received:", response.data); // Debug
        let states = response.data;

        // Handle root array format
        if (Array.isArray(states)) {
            states = states.map(item => ({
                name: item.state,
                lgas: item.lgas
            }));
        } else if (states && Array.isArray(states.states)) {
            states = states.states;
        } else {
            throw new Error("Invalid or empty states data format");
        }

        if (!states.length) {
            throw new Error("No states data available");
        }

        states.forEach(state => {
            if (!state.name) {
                console.warn("Skipping state with missing name:", state);
                return;
            }
            const option = document.createElement('option');
            option.value = state.name;
            option.textContent = state.name;
            stateSelect.appendChild(option);
        });

        // Event listener for state change to populate LGAs
        stateSelect.addEventListener('change', function() {
            populateLGAs(this.value, lgaSelectId);
        });
    }).catch((error) => {
        console.error('Error loading states:', error.message);
        Swal.fire({
            title: "Error!",
            text: error.response?.status === 404 ? "States data file not found at /states_lgas.json" : error.message || "Failed to load states",
            icon: "error",
            confirmButtonClass: "btn btn-primary",
            buttonsStyling: true
        });
        // Fallback: Add a manual input option
        const option = document.createElement('option');
        option.value = "Other";
        option.textContent = "Other (manual)";
        stateSelect.appendChild(option);
    });
}

// Populate LGAs based on selected state
function populateLGAs(state, lgaSelectId) {
    const lgaSelect = document.getElementById(lgaSelectId);
    lgaSelect.innerHTML = '<option value="">Select Local Government</option>';

    if (!state || state === 'Other') {
        const option = document.createElement('option');
        option.value = "Other";
        option.textContent = "Other (manual)";
        lgaSelect.appendChild(option);
        return;
    }

    axios.get('/states_lgas.json').then((response) => {
        console.log("LGAs data for state:", state, response.data);
        let states = response.data;

        if (Array.isArray(states)) {
            states = states.map(item => ({
                name: item.state,
                lgas: item.lgas
            }));
        } else if (states && Array.isArray(states.states)) {
            states = states.states;
        } else {
            throw new Error("Invalid states data format");
        }

        const selectedState = states.find(s => s.name === state);
        if (selectedState && Array.isArray(selectedState.lgas)) {
            selectedState.lgas.forEach(lga => {
                const option = document.createElement('option');
                option.value = lga;
                option.textContent = lga;
                lgaSelect.appendChild(option);
            });
        } else {
            console.warn("No LGAs found for state:", state);
            const option = document.createElement('option');
            option.value = "Other";
            option.textContent = "Other (manual)";
            lgaSelect.appendChild(option);
        }
    }).catch((error) => {
        console.error('Error loading LGAs:', error.message);
        Swal.fire({
            title: "Error!",
            text: error.response?.status === 404 ? "Failed to load LGAs from /states_lgas.json" : error.message || "Failed to load LGAs",
            icon: "error",
            confirmButtonClass: "btn btn-primary",
            buttonsStyling: true
        });
        const option = document.createElement('option');
        option.value = "Other";
        option.textContent = "Other (manual)";
        lgaSelect.appendChild(option);
    });
}

// Initialize List.js for table sorting and searching
let studentList;
function initializeList() {
    const options = {
        valueNames: ['name', 'admissionNo', 'class', 'status', 'gender', 'datereg'],
        page: 10,
        pagination: true
    };
    studentList = new List('studentList', options);
    studentList.on('updated', function() {
        document.querySelector('.noresult').style.display = studentList.visibleItems.length === 0 ? 'block' : 'none';
        document.querySelector('.fw-semibold').textContent = studentList.visibleItems.length;
    });
}

// Filter Data
function filterData() {
    const search = document.querySelector('.search').value.toLowerCase();
    const classId = document.getElementById('idClass').value;
    const statusId = document.getElementById('idStatus').value;
    const gender = document.getElementById('idGender').value;

    studentList.filter(item => {
        const name = item.values().name.toLowerCase();
        const admissionNo = item.values().admissionNo.toLowerCase();
        const classValue = item.elm.querySelector('.class').dataset.class;
        const statusValue = item.elm.querySelector('.status').dataset.status;
        const genderValue = item.elm.querySelector('.gender').dataset.gender;

        const matchesSearch = name.includes(search) || admissionNo.includes(search);
        const matchesClass = classId === 'all' || classValue === classId;
        const matchesStatus = statusId === 'all' || statusValue === statusId;
        const matchesGender = gender === 'all' || genderValue === gender;

        return matchesSearch && matchesClass && matchesStatus && matchesGender;
    });
}

// Delete Multiple Students
function deleteMultiple() {
    const ids = Array.from(document.querySelectorAll('input[name="chk_child"]:checked'))
        .map(checkbox => checkbox.closest('tr').querySelector('.id').dataset.id);

    if (ids.length === 0) {
        Swal.fire({
            title: "Error!",
            text: "Please select at least one student",
            icon: "error",
            confirmButtonClass: "btn btn-primary",
            buttonsStyling: true
        });
        return;
    }

    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn btn-primary",
        cancelButtonClass: "btn btn-light",
        buttonsStyling: true
    }).then((result) => {
        if (result.isConfirmed && ensureAxios()) {
            axios.post('/students/destroy-multiple', { ids }).then(() => {
                ids.forEach(id => {
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    if (row) row.remove();
                });
                studentList.reIndex();
                Swal.fire({
                    title: "Success!",
                    text: "Students deleted",
                    icon: "success",
                    confirmButtonClass: "btn btn-primary",
                    buttonsStyling: true
                });
                document.getElementById('checkAll').checked = false;
                document.getElementById('remove-actions').classList.add('d-none');
            }).catch((error) => {
                console.error('Error deleting students:', error);
                Swal.fire({
                    title: "Error!",
                    text: error.response?.data?.message || "Failed to delete students",
                    icon: "error",
                    confirmButtonClass: "btn btn-primary",
                    buttonsStyling: true
                });
            });
        }
    });
}

document.addEventListener("DOMContentLoaded", function() {
    // Initialize List.js
    initializeList();

    // Initialize Choices.js for select elements
    if (typeof Choices !== 'undefined') {
        document.querySelectorAll('[data-choices]').forEach(element => {
            new Choices(element, {
                searchEnabled: element.dataset.choicesSearchFalse !== undefined,
                removeItemButton: true
            });
        });
    }

    // Populate state and LGA dropdowns
    populateStates('addState', 'addLocal');
    populateStates('editState', 'editLocal');

    // Filter event listeners
    document.querySelector('.search').addEventListener('input', filterData);
    document.getElementById('idClass').addEventListener('change', filterData);
    document.getElementById('idStatus').addEventListener('change', filterData);
    document.getElementById('idGender').addEventListener('change', filterData);

    // Check all checkbox
    document.getElementById('checkAll').addEventListener('change', function() {
        document.querySelectorAll('input[name="chk_child"]').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        document.getElementById('remove-actions').classList.toggle('d-none', !this.checked);
    });

    // Individual checkboxes
    document.querySelectorAll('input[name="chk_child"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = document.querySelectorAll('input[name="chk_child"]').length ===
                document.querySelectorAll('input[name="chk_child"]:checked').length;
            document.getElementById('checkAll').checked = allChecked;
            document.getElementById('remove-actions').classList.toggle('d-none',
                document.querySelectorAll('input[name="chk_child"]:checked').length === 0);
        });
    });

    // Add Student Form Submission
    document.getElementById('addStudentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        if (!ensureAxios()) return;

        const formData = new FormData(this);
        axios.post(this.action, formData).then((response) => {
            Swal.fire({
                title: "Success!",
                text: "Student added successfully",
                icon: "success",
                confirmButtonClass: "btn btn-primary",
                buttonsStyling: true
            }).then(() => {
                window.location.reload(true);
            });
        }).catch((error) => {
            console.error('Error adding student:', error);
            Swal.fire({
                title: "Error!",
                text: error.response?.data?.message || "Failed to add student",
                icon: "error",
                confirmButtonClass: "btn btn-primary",
                buttonsStyling: true
            });
        });
    });

    // Edit Student Modal Population
    document.querySelectorAll(".edit-item-btn").forEach((button) => {
        button.addEventListener("click", function() {
            const id = this.getAttribute("data-id");
            console.log("Edit student ID:", id);
            if (!ensureAxios()) return;

            axios.get(`/student/${id}/edit`).then((response) => {
                console.log("Student data:", response.data);
                const student = response.data.student;
                if (!student) {
                    throw new Error("Student data empty");
                }

                // Log fields for debugging
                console.log("Populating fields:", {
                    id: student.id,
                    admissionNo: student.admissionNo,
                    tittle: student.tittle,
                    firstname: student.firstname,
                    lastname: student.lastname,
                    othername: student.othername,
                    gender: student.gender,
                    home_address: student.home_address,
                    home_address2: student.home_address2,
                    dateofbirth: student.dateofbirth,
                    age: student.age,
                    placeofbirth: student.placeofbirth,
                    nationlity: student.nationlity,
                    state: student.state,
                    local: student.local,
                    religion: student.religion,
                    last_school: student.last_school,
                    last_class: student.last_class,
                    schoolclassid: student.schoolclassid,
                    termid: student.termid,
                    sessionid: student.sessionid,
                    statusId: student.statusId,
                    picture: student.picture
                });

                // Populate form fields
                try {
                    document.getElementById("editStudentId").value = student.id || '';
                    document.getElementById("editAdmissionNo").value = student.admissionNo || '';
                    document.getElementById("editTittle").value = student.tittle || '';
                    document.getElementById("editFirstname").value = student.firstname || '';
                    document.getElementById("editLastname").value = student.lastname || '';
                    document.getElementById("editOthername").value = student.othername || '';
                    document.querySelector(`#editGender${student.gender || 'Male'}`).checked = true;
                    document.getElementById("editHomeAddress").value = student.home_address || '';
                    document.getElementById("editHomeAddress2").value = student.home_address2 || '';
                    document.getElementById("editDOB").value = student.dateofbirth || '';

                    // Handle age field
                    const ageElement = document.getElementById("editAge") ||
                                      document.getElementById("editAge1") ||
                                      document.getElementById("age");
                    if (!ageElement) {
                        console.error("Age element not found (tried editAge, editAge1, age)");
                        throw new Error("Age input field not found");
                    }
                    console.log("Setting age value:", student.age, "Type:", typeof student.age);
                    ageElement.value = student.age != null && student.age !== '' ? student.age : '';

                    document.getElementById("editPlaceofbirth").value = student.placeofbirth || '';
                    document.getElementById("editNationlity").value = student.nationlity || '';
                    document.getElementById("editState").value = student.state || '';
                    setTimeout(() => {
                        populateLGAs(student.state, 'editLocal');
                        document.getElementById("editLocal").value = student.local || '';
                    }, 100);
                    document.getElementById("editReligion").value = student.religion || '';
                    document.getElementById("editLastSchool").value = student.last_school || '';
                    document.getElementById("editLastClass").value = student.last_class || '';
                    document.getElementById("editSchoolclassid").value = student.schoolclassid || '';
                    document.getElementById("editTermid").value = student.termid || '';
                    document.getElementById("editSessionid").value = student.sessionid || '';
                    document.querySelector(`#editStatus${student.statusId == 1 ? 'Old' : 'New'}`).checked = true;
                    document.getElementById("editStudentAvatar").src = student.picture ? `/storage/${student.picture}` : '/theme/layouts/assets/media/avatars/blank.png';
                    document.getElementById("editStudentAvatar").setAttribute('data-original-src', student.picture ? `/storage/${student.picture}` : '/theme/layouts/assets/media/avatars/blank.png');

                    // Update form action
                    const form = document.getElementById('editStudentForm');
                    form.action = `/student/${id}`;

                    console.log("Form fields populated for student ID:", id);
                } catch (fieldError) {
                    console.error("Error populating form fields:", fieldError);
                    Swal.fire({
                        title: "Error!",
                        text: "Failed to populate some fields: " + fieldError.message,
                        icon: "error",
                        confirmButtonClass: "btn btn-primary",
                        buttonsStyling: true
                    });
                }
            }).catch((error) => {
                console.error("Error fetching student:", error.message, error.response?.status, error.response?.data);
                Swal.fire({
                    title: "Error!",
                    text: error.response?.data?.message || "Failed to load student data",
                    icon: "error",
                    confirmButtonClass: "btn btn-primary",
                    buttonsStyling: true
                });
            });
        });
    });

    // Edit Student Form Submission
    document.getElementById('editStudentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        if (!ensureAxios()) return;

        const id = document.getElementById('editStudentId').value;
        const formData = new FormData(this);
        axios.post(this.action, formData, {
            headers: { 'X-HTTP-Method-Override': 'PATCH' }
        }).then((response) => {
            Swal.fire({
                title: "Success!",
                text: "Student updated successfully",
                icon: "success",
                confirmButtonClass: "btn btn-primary",
                buttonsStyling: true
            }).then(() => {
                window.location.reload(true);
            });
        }).catch((error) => {
            console.error('Error updating student:', error);
            Swal.fire({
                title: "Error!",
                text: error.response?.data?.message || "Failed to update student",
                icon: "error",
                confirmButtonClass: "btn btn-primary",
                buttonsStyling: true
            });
        });
    });

    // Delete Single Student
    document.querySelectorAll('.remove-item-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn btn-primary",
                cancelButtonClass: "btn btn-light",
                buttonsStyling: true
            }).then((result) => {
                if (result.isConfirmed && ensureAxios()) {
                    axios.delete(`/student/${id}`).then(() => {
                        const row = this.closest('tr');
                        if (row) row.remove();
                        studentList.reIndex();
                        Swal.fire({
                            title: "Success!",
                            text: "Student deleted",
                            icon: "success",
                            confirmButtonClass: "btn btn-primary",
                            buttonsStyling: true
                        });
                    }).catch((error) => {
                        console.error('Error deleting student:', error);
                        Swal.fire({
                            title: "Error!",
                            text: error.response?.data?.message || "Failed to delete student",
                            icon: "error",
                            confirmButtonClass: "btn btn-primary",
                            buttonsStyling: true
                        });
                    });
                }
            });
        });
    });
});
