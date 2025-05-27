@extends('layouts.app')
@section('title', 'Student Management')

@section('css')
<link rel="stylesheet" href="{{ asset('theme/layouts/assets/css/choices.min.css') }}">
<style>
    .list tbody tr { display: none; }
    .list tbody tr.is-visible { display: table-row; }
</style>
@endsection

@section('content')
<!--begin::Content wrapper-->
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Container-->
    <div class="container-xxl" id="kt_content_container">
        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <!--begin::Search-->
                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <input type="text" data-kt-customer-table-filter="search" class="form-control form-control-solid w-250px ps-13 search" placeholder="Search Students" />
                    </div>
                    <!--end::Search-->
                </div>
                <div class="card-toolbar">
                    <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base">
                        <!--begin::Filter-->
                        <button type="button" class="btn btn-light-primary me-3" data-bs-toggle="modal" data-bs-target="#kt_modal_filter">
                            <i class="ki-duotone ki-filter fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i> Filter
                        </button>
                        <!--end::Filter-->
                        @can('student-create')
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_student">
                            <i class="ki-duotone ki-plus fs-2"></i> Add Student
                        </button>
                        @endcan
                    </div>
                    <div class="d-flex justify-content-end align-items-center d-none" data-kt-customer-table-toolbar="selected">
                        <div class="fw-bold me-5">
                            <span class="me-2" data-kt-customer-table-select="selected_count"></span> Selected
                        </div>
                        @can('student-delete')
                        <button type="button" class="btn btn-danger" onclick="deleteMultiple()">Delete Selected</button>
                        @endcan
                    </div>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Chart-->
                <div class="card mb-5">
                    <div class="card-body">
                        <canvas id="studentsByClassChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
                <!--end::Chart-->
                <!--begin::Table-->
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="student_list">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th class="w-10px pe-2">
                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                        <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#student_list .form-check-input" value="1" />
                                    </div>
                                </th>
                                <th class="min-w-125px">Name</th>
                                <th class="min-w-125px">Admission No</th>
                                <th class="min-w-125px">Class</th>
                                <th class="min-w-125px">Gender</th>
                                <th class="min-w-125px">Registered</th>
                                <th class="min-w-70px">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                            @foreach ($data as $student)
                            <tr>
                                <td>
                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" name="chk_child" value="1" />
                                    </div>
                                </td>
                                <td class="name" data-name="{{ $student->firstname }} {{ $student->lastname }}">
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                            <a href="{{ route('student.show', $student->id) }}">
                                                @if ($student->picture)
                                                <div class="symbol-label">
                                                    <img src="{{ asset('storage/images/studentavatar/' . $student->picture) }}" alt="{{ $student->firstname }}" class="w-100" />
                                                </div>
                                                @else
                                                <div class="symbol-label fs-3 bg-light-danger text-danger">{{ Str::upper(Str::substr($student->firstname, 0, 1)) }}</div>
                                                @endif
                                            </a>
                                        </div>
                                        <div class="ms-5">
                                            <a href="{{ route('student.show', $student->id) }}" class="text-gray-800 text-hover-primary fs-5 fw-bold">{{ $student->firstname }} {{ $student->lastname }}</a>
                                        </div>
                                    </div>
                                </td>
                                <td class="admissionNo" data-admissionNo="{{ $student->admissionNo }}">{{ $student->admissionNo }}</td>
                                <td class="class" data-class="{{ optional($student->schoolclass)->schoolclass ?? 'N/A' }}">{{ optional($student->schoolclass)->schoolclass ?? 'N/A' }}</td>
                                <td class="gender" data-gender="{{ $student->gender }}">{{ $student->gender }}</td>
                                <td data-order="{{ $student->created_at }}">{{ $student->created_at->format('d M Y, H:i') }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        @can('student-show')
                                        <a href="{{ route('student.show', $student->id) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
                                            <i class="ki-duotone ki-eye fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                        </a>
                                        @endcan
                                        @can('student-edit')
                                        <a href="{{ route('student.edit', $student->id) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
                                            <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                                        </a>
                                        @endcan
                                        @can('student-delete')
                                        <a href="javascript:;" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" onclick="deleteRow({{ $student->id }})">
                                            <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $data->links() }}
                </div>
                <!--end::Table-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
        <!--begin::Modals-->
        <!--begin::Add student modal-->
        <div class="modal fade" id="kt_modal_add_student" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mw-650px">
                <div class="modal-content">
                    <div class="modal-header" id="kt_modal_add_customer_header">
                        <h2 class="fw-bold">Add a Student</h2>
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                    </div>
                    <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                        <form id="kt_modal_add_student_form" class="form" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="registeredBy" value="{{ Auth::user()->id }}">
                            <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_add_student_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_student_header" data-kt-scroll-wrappers="#kt_modal_add_student_scroll" data-kt-scroll-offset="300px">
                                <!-- Avatar -->
                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 mb-2">Avatar</label>
                                    <input type="file" name="avatar" class="form-control form-control-solid mb-3 mb-lg-0" accept=".png,.jpg,.jpeg" required />
                                    <div class="form-text">Allowed file types: png, jpg, jpeg.</div>
                                </div>
                                <!-- Admission No -->
                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 mb-2">Admission No</label>
                                    <input type="text" name="admissionNo" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Admission Number" required />
                                </div>
                                <!-- Title -->
                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 mb-2">Title</label>
                                    <select name="title" class="form-control form-control-solid" required>
                                        <option value="">Select Title</option>
                                        <option value="Mr">Mr</option>
                                        <option value="Mrs">Mrs</option>
                                        <option value="Miss">Miss</option>
                                    </select>
                                </div>
                                <!-- Full Name -->
                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 mb-2">Full Name</label>
                                    <div class="row">
                                        <div class="col-6">
                                            <input type="text" name="firstname" class="form-control form-control-solid" placeholder="First name" required />
                                        </div>
                                        <div class="col-6">
                                            <input type="text" name="lastname" class="form-control form-control-solid" placeholder="Last name" required />
                                        </div>
                                    </div>
                                </div>
                                <!-- Other Name -->
                                <div class="fv-row mb-7">
                                    <label class="fw-semibold fs-6 mb-2">Other Name</label>
                                    <input type="text" name="othername" class="form-control form-control-solid" placeholder="Other names" />
                                </div>
                                <!-- Gender -->
                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 mb-2">Gender</label>
                                    <div class="d-flex">
                                        <label class="form-check form-check-custom form-check-inline form-check-solid me-5">
                                            <input class="form-check-input" name="gender" type="radio" value="Male" required />
                                            <span class="fw-semibold ps-2 fs-6">Male</span>
                                        </label>
                                        <label class="form-check form-check-custom form-check-inline form-check-solid">
                                            <input class="form-check-input" name="gender" type="radio" value="Female" required />
                                            <span class="fw-semibold ps-2 fs-6">Female</span>
                                        </label>
                                    </div>
                                </div>
                                <!-- Home Address 1 -->
                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 mb-2">Home Address 1</label>
                                    <input type="text" name="home_address" class="form-control form-control-solid" placeholder="Address" required />
                                </div>
                                <!-- Home Address 2 -->
                                <div class="fv-row mb-7">
                                    <label class="fw-semibold fs-6 mb-2">Home Address 2</label>
                                    <input type="text" name="home_address2" class="form-control form-control-solid" placeholder="Address" />
                                </div>
                                <!-- Date of Birth -->
                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 mb-2">Date of Birth</label>
                                    <input type="date" name="dateofbirth" id="dateofbirth" oninput="showage()" class="form-control form-control-solid" required />
                                </div>
                                <!-- Age -->
                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 mb-2">Age</label>
                                    <input type="text" name="age1" id="age1" class="form-control form-control-solid" placeholder="Age" readonly />
                                </div>
                                <!-- Place of Birth -->
                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 mb-2">Place of Birth</label>
                                    <input type="text" name="placeofbirth" class="form-control form-control-solid" placeholder="Place of Birth" required />
                                </div>
                                <!-- Nationality -->
                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 mb-2">Nationality</label>
                                    <input type="text" name="nationality" class="form-control form-control-solid" placeholder="Nationality" required />
                                </div>
                                <!-- State of Origin -->
                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 mb-2">State of Origin</label>
                                    <select name="state" id="state" class="form-control form-control-solid" required>
                                        <option value="">Select State</option>
                                    </select>
                                </div>
                                <!-- Local Government -->
                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 mb-2">Local Government</label>
                                    <select name="local" id="local" class="form-control form-control-solid" required>
                                        <option value="">Select Local Government</option>
                                    </select>
                                </div>
                                <!-- Religion -->
                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 mb-2">Religion</label>
                                    <select name="religion" class="form-control form-control-solid" required>
                                        <option value="">Select Religion</option>
                                        <option value="Christianity">Christianity</option>
                                        <option value="Islam">Islam</option>
                                        <option value="Others">Others</option>
                                    </select>
                                </div>
                                <!-- Last School Attended -->
                                <div class="fv-row mb-7">
                                    <label class="fw-semibold fs-6 mb-2">Last School Attended</label>
                                    <input type="text" name="last_school" class="form-control form-control-solid" placeholder="Last School Attended" />
                                </div>
                                <!-- Last Class -->
                                <div class="fv-row mb-7">
                                    <label class="fw-semibold fs-6 mb-2">Last Class</label>
                                    <input type="text" name="last_class" class="form-control form-control-solid" placeholder="Last Class" />
                                </div>
                                <!-- Class -->
                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 mb-2">Class</label>
                                    <select name="schoolclassid" class="form-control form-control-solid" required>
                                        <option value="">Select Class</option>
                                        @foreach ($schoolclass as $class)
                                        <option value="{{ $class->id }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Term -->
                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 mb-2">Term</label>
                                    <select name="termid" class="form-control form-control-solid" required>
                                        <option value="">Select Term</option>
                                        @foreach ($schoolterm as $term)
                                        <option value="{{ $term->id }}">{{ $term->term }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Session -->
                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 mb-2">Session</label>
                                    <select name="sessionid" class="form-control form-control-solid" required>
                                        <option value="">Select Session</option>
                                        @foreach ($schoolsession as $session)
                                        <option value="{{ $session->id }}">{{ $session->session }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Student Status -->
                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 mb-2">Student Status</label>
                                    <div class="d-flex">
                                        <label class="form-check form-check-custom form-check-inline form-check-solid me-5">
                                            <input class="form-check-input" name="statusId" type="radio" value="1" required />
                                            <span class="fw-semibold ps-2 fs-6">Old Student</span>
                                        </label>
                                        <label class="form-check form-check-custom form-check-inline form-check-solid">
                                            <input class="form-check-input" name="statusId" type="radio" value="2" required />
                                            <span class="fw-semibold ps-2 fs-6">New Student</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center pt-15">
                                <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                                <button type="submit" class="btn btn-primary">
                                    <span class="indicator-label">Submit</span>
                                    <span class="indicator-progress">Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Add student modal-->
        <!--begin::Filter modal-->
        <div class="modal fade" id="kt_modal_filter" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mw-650px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="fw-bold">Filter Students</h2>
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                    </div>
                    <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                        <form id="kt_modal_filter_form" class="form">
                            <div class="fv-row mb-7">
                                <label class="fw-semibold fs-6 mb-2">Class</label>
                                <select id="idClass" class="form-control form-control-solid" data-choices="true">
                                    <option value="all">All Classes</option>
                                    @foreach ($schoolclass as $class)
                                    <option value="{{ $class->schoolclass }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="fv-row mb-7">
                                <label class="fw-semibold fs-6 mb-2">Admission No</label>
                                <select id="idAdmissionNo" class="form-control form-control-solid" data-choices="true">
                                    <option value="all">All Admission Nos</option>
                                    @foreach ($data as $student)
                                    <option value="{{ $student->admissionNo }}">{{ $student->admissionNo }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="text-center pt-15">
                                <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                                <button type="button" class="btn btn-primary" onclick="filterData()">
                                    <span class="indicator-label">Apply Filters</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Filter modal-->
    </div>
    <!--end::Container-->
</div>
<!--end::Content wrapper-->
@endsection

@section('scripts')
{{-- <script src="{{ asset('theme/layouts/assets/js/choices.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/js/list.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/js/Chart.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/js/sweetalert2.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/js/axios.min.js') }}"></script>
<script src="{{ asset('js/student-list.init.js') }}"></script> --}}
<script>
document.addEventListener("DOMContentLoaded", function () {
    var ctx = document.getElementById("studentsByClassChart").getContext("2d");
    new Chart(ctx, {
        type: "bar",
        data: {
            labels: @json(array_keys($class_counts)),
            datasets: [{
                label: "Students by Class",
                data: @json(array_values($class_counts)),
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                borderColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true, title: { display: true, text: "Number of Students" } },
                x: { title: { display: true, text: "Classes" } }
            },
            plugins: { legend: { display: true, position: "top" } }
        }
    });
});
</script>
@endsection