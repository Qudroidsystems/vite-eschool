var perPage = 10;
var studentListOptions = {
    valueNames: [
        "id",
        "admissionNo",
        { data: ["id"] },
        { data: ["class"] },
        { data: ["status"] },
        { data: ["gender"] },
        { name: "class", attr: "data-class" },
        { name: "status", attr: "data-status" },
        { name: "gender", attr: "data-gender" }
    ],
    page: perPage,
    pagination: true,
    searchColumns: ["admissionNo", "id"]
};

var studentList = new List("student_list", studentListOptions);
var classFilterVal, statusFilterVal, genderFilterVal;

// Load states and LGAs
let statesLgas = [];
axios.get('/states_lgas.json').then(response => {
    statesLgas = response.data;
    populateStates('addState');
    populateStates('editState');
}).catch(error => {
    console.error("Error loading states/LGAs:", error);
});

function populateStates(selectId) {
    const select = document.getElementById(selectId);
    statesLgas.forEach(state => {
        const option = document.createElement('option');
        option.value = state.state;
        option.textContent = state.state;
        select.appendChild(option);
    });
}

function populateLGAs(state, selectId) {
    const select = document.getElementById(selectId);
    select.innerHTML = '<option value="">Select Local Government</option>';
    const stateData = statesLgas.find(s => s.state === state);
    if (stateData) {
        stateData.lgas.forEach(lga => {
            const option = document.createElement('option');
            option.value = lga;
            option.textContent = lga;
            select.appendChild(option);
        });
    }
}

document.getElementById('addState')?.addEventListener('change', function() {
    populateLGAs(this.value, 'addLocal');
});

document.getElementById('editState')?.addEventListener('change', function() {
    populateLGAs(this.value, 'editLocal');
});

function ensureAxios() {
    if (typeof axios === "undefined") {
        console.error("Axios is not loaded.");
        Swal.fire({
            title: "Error!",
            text: "Required library (Axios) is missing.",
            icon: "error",
            confirmButtonClass: "btn btn-info",
            buttonsStyling: false
        });
        return false;
    }
    return true;
}

function filterData() {
    var searchInput = document.querySelector(".search-box input.search").value.toLowerCase();
    var classSelect = document.getElementById("idClass");
    var statusSelect = document.getElementById("idStatus");
    var genderSelect = document.getElementById("idGender");
    var selectedClass = typeof Choices !== 'undefined' && classFilterVal ? classFilterVal.getValue(true) : classSelect.value;
    var selectedStatus = typeof Choices !== 'undefined' && statusFilterVal ? statusFilterVal.getValue(true) : statusSelect.value;
    var selectedGender = typeof Choices !== 'undefined' && genderFilterVal ? genderFilterVal.getValue(true) : genderSelect.value;

    console.log("Filtering with:", { search: searchInput, class: selectedClass, status: selectedStatus, gender: selectedGender });

    studentList.filter(function (item) {
        var matchesSearch = !searchInput || item.values().admissionNo.toLowerCase().includes(searchInput) || item.values().id.toLowerCase().includes(searchInput);
        var matchesClass = selectedClass === "all" || item.values().class === selectedClass;
        var matchesStatus = selectedStatus === "all" || item.values().status === selectedStatus;
        var matchesGender = selectedGender === "all" || item.values().gender === selectedGender;
        return matchesSearch && matchesClass && matchesStatus && matchesGender;
    });
}

function deleteMultiple() {
    const ids_array = [];
    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]:checked');
    checkboxes.forEach((checkbox) => {
        const id = checkbox.closest("tr").querySelector(".id").getAttribute("data-id");
        ids_array.push(id);
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
                axios.post('/students/destroy-multiple', {
                    ids: ids_array,
                    _token: document.querySelector('meta[name="csrf-token"]').content
                }).then(() => {
                    window.location.reload();
                    Swal.fire({
                        title: "Deleted!",
                        text: "Selected students have been deleted.",
                        icon: "success",
                        confirmButtonClass: "btn btn-info w-xs mt-2",
                        buttonsStyling: false
                    });
                }).catch((error) => {
                    console.error("Error deleting students:", error);
                    Swal.fire({
                        title: "Error!",
                        text: error.response?.data?.message || "Failed to delete students",
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

document.addEventListener("DOMContentLoaded", function () {
    const classSelect = document.getElementById("idClass");
    const statusSelect = document.getElementById("idStatus");
    const genderSelect = document.getElementById("idGender");

    if (typeof Choices !== 'undefined') {
        classFilterVal = new Choices(classSelect, { searchEnabled: true, itemSelectText: '' });
        statusFilterVal = new Choices(statusSelect, { searchEnabled: true, itemSelectText: '' });
        genderFilterVal = new Choices(genderSelect, { searchEnabled: true, itemSelectText: '' });
    }

    const searchInput = document.querySelector(".search-box input.search");
    searchInput.addEventListener("input", filterData);
    classSelect.addEventListener("change", filterData);
    statusSelect.addEventListener("change", filterData);
    genderSelect.addEventListener("change", filterData);

    document.getElementById("selectAll").addEventListener("change", function () {
        document.querySelectorAll('tbody input[name="chk_child"]').forEach((checkbox) => {
            checkbox.checked = this.checked;
        });
    });

    document.getElementById("perPageSelect").addEventListener("change", function () {
        perPage = parseInt(this.value);
        studentList.page = perPage;
        studentList.update();
    });

    document.getElementById("addStudentForm").addEventListener("submit", function (e) {
        e.preventDefault();
        if (!ensureAxios()) return;

        const formData = new FormData(this);
        axios.post('/students', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        }).then(() => {
            window.location.reload();
            Swal.fire({
                title: "Success!",
                text: "Student added successfully",
                icon: "success",
                confirmButtonClass: "btn btn-info",
                buttonsStyling: false
            });
        }).catch((error) => {
            console.error("Error adding student:", error);
            Swal.fire({
                title: "Error!",
                text: error.response?.data?.message || "Failed to add student",
                icon: "error",
                confirmButtonClass: "btn btn-info",
                buttonsStyling: false
            });
        });
    });

    document.querySelectorAll(".editStudent").forEach((button) => {
        button.addEventListener("click", function () {
            const id = this.getAttribute("data-id");
            if (!ensureAxios()) return;

            axios.get(`/students/${id}/edit`).then((response) => {
                const student = response.data.student;
                document.getElementById("editStudentId").value = student.id;
                document.getElementById("editAdmissionNo").value = student.admissionNo;
                document.getElementById("editTitle").value = student.title;
                document.getElementById("editFirstname").value = student.firstname;
                document.getElementById("editLastname").value = student.lastname;
                document.getElementById("editGender").value = student.gender;
                document.getElementById("editNationality").value = student.nationality;
                document.getElementById("editState").value = student.state;
                populateLGAs(student.state, 'editLocal');
                document.getElementById("editLocal").value = student.local;
                document.getElementById("editReligion").value = student.religion;
                document.getElementById("editDOB").value = student.dateofbirth;
                document.getElementById("editBloodgroup").value = student.bloodgroup || '';
                document.getElementById("editGenotype").value = student.genotype || '';
                document.getElementById("editSchoolclassid").value = student.schoolclassid;
                document.getElementById("editTermid").value = student.termid;
                document.getElementById("editSessionid").value = student.sessionid;
                document.getElementById("editStatusId").value = student.statusId;
                document.getElementById("editStudentAvatar").src = student.picture ? `/storage/${student.picture}` : '/theme/layouts/assets/media/avatars/blank.png';
                showage(student.dateofbirth, 'editAge');
            }).catch((error) => {
                console.error("Error fetching student:", error);
                Swal.fire({
                    title: "Error!",
                    text: "Failed to load student data",
                    icon: "error",
                    confirmButtonClass: "btn btn-info",
                    buttonsStyling: false
                });
            });
        });
    });

    document.getElementById("editStudentForm").addEventListener("submit", function (e) {
        e.preventDefault();
        if (!ensureAxios()) return;

        const id = document.getElementById("editStudentId").value;
        const formData = new FormData(this);
        axios.post(`/students/${id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
            params: { _method: 'PUT' }
        }).then(() => {
            window.location.reload();
            Swal.fire({
                title: "Success!",
                text: "Student updated successfully",
                icon: "success",
                confirmButtonClass: "btn btn-info",
                buttonsStyling: false
            });
        }).catch((error) => {
            console.error("Error updating student:", error);
            Swal.fire({
                title: "Error!",
                text: error.response?.data?.message || "Failed to update student",
                icon: "error",
                confirmButtonClass: "btn btn-info",
                buttonsStyling: false
            });
        });
    });

    document.querySelectorAll(".deleteStudent").forEach((button) => {
        button.addEventListener("click", function () {
            const id = this.getAttribute("data-id");
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
                    axios.delete(`/students/${id}`, {
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    }).then(() => {
                        window.location.reload();
                        Swal.fire({
                            title: "Deleted!",
                            text: "Student has been deleted.",
                            icon: "success",
                            confirmButtonClass: "btn btn-info w-xs mt-2",
                            buttonsStyling: false
                        });
                    }).catch((error) => {
                        console.error("Error deleting student:", error);
                        Swal.fire({
                            title: "Error!",
                            text: error.response?.data?.message || "Failed to delete student",
                            icon: "error",
                            confirmButtonClass: "btn btn-info w-xs mt-2",
                            buttonsStyling: false
                        });
                    });
                }
            });
        });
    });

    document.getElementById("deleteMultiple").addEventListener("click", deleteMultiple);
});