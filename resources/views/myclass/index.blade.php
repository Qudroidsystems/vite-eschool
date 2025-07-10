@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">My Classes</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('myclass.index') }}">Class Management</a></li>
                                <li class="breadcrumb-item active">My Classes</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            <!-- Classes by Term Chart -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Classes by Term (Current Session)</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="classesByTermChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div id="classList">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-3">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" placeholder="Search classes">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <div>
                                            <select class="form-control" id="idTerm" data-choices data-choices-search-false data-choices-removeItem>
                                                <option value="all">Select Term</option>
                                                @foreach ($terms as $term)
                                                    <option value="{{ $term->term }}">{{ $term->term }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <div>
                                            <select class="form-control" id="idSession" data-choices data-choices-search-false data-choices-removeItem>
                                                <option value="all">Select Session</option>
                                                @foreach ($schoolsessions as $session)
                                                    <option value="{{ $session->session }}">{{ $session->session }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xxl-1 col-sm-6">
                                        <button type="button" class="btn btn-secondary w-100" onclick="filterData();"><i class="bi bi-funnel align-baseline me-1"></i> Filters</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">Classes <span class="badge bg-dark-subtle text-dark ms-1">{{ $myclass->count() }}</span></h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="d-flex flex-wrap align-items-start gap-2">
                                        <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button>
                                        @can('Create class')
                                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#showModal"><i class="bi bi-plus-circle align-baseline me-1"></i> Add Class Setting</button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-centered align-middle table-nowrap mb-0" id="classListTable">
                                        <thead class="table-active">
                                            <tr>
                                                <th><div class="form-check"><input class="form-check-input" type="checkbox" value="option" id="checkAll"><label class="form-check-label" for="checkAll"></label></div></th>
                                                <th class="sort cursor-pointer" data-sort="schoolclass">Class</th>
                                                <th class="sort cursor-pointer" data-sort="schoolarm">Arm</th>
                                                <th class="sort cursor-pointer" data-sort="term">Term</th>
                                                <th class="sort cursor-pointer" data-sort="session">Session</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list form-check-all">
                                            @forelse ($myclass as $sc)
                                                <tr>
                                                    <td class="id" data-id="{{ $sc->id }}">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="chk_child">
                                                            <label class="form-check-label"></label>
                                                        </div>
                                                    </td>
                                                    <td class="schoolclass" data-schoolclass="{{ $sc->schoolclass }}">{{ $sc->schoolclass }}</td>
                                                    <td class="schoolarm" data-schoolarm="{{ $sc->schoolarm }}">{{ $sc->schoolarm }}</td>
                                                    <td class="term" data-term="{{ $sc->term }}">{{ $sc->term }}</td>
                                                    <td class="session" data-session="{{ $sc->session }}">{{ $sc->session }}</td>
                                                    <td>
                                                        <ul class="d-flex gap-2 list-unstyled mb-0">
                                                            @can('View my-class')
                                                                <li>
                                                                    <a href="viewstudent/{{ $sc->schoolclassID }}/{{ $sc->termid }}/{{ $sc->sessionid }}"   title="View Students in {{$sc->schoolclass}}  {{ $sc->schoolarm}}"  class="btn btn-subtle-primary btn-icon "><i class="ph-eye"></i></a>
                                                                </li>
                                                                 <li>
                                                                    <a href="{{ route('classbroadsheet', [$sc->schoolclassID, $sc->termid, $sc->sessionid]) }}" title="Broadsheet for students in  {{$sc->schoolclass}}  {{ $sc->schoolarm}}" class="btn btn-subtle-success btn-icon"><i class="ph-eye"></i></a>
                                                                </li>
                                                            @endcan
                                                            @can('Update my-class')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn"><i class="ph-pencil"></i></a>
                                                                </li>
                                                            @endcan
                                                            @can('Delete my-class')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn"><i class="ph-trash"></i></a>
                                                                </li>
                                                            @endcan
                                                        </ul>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="noresult" style="display: block;">No results found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Class Setting Modal -->
            <div id="showModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="addModalLabel" class="modal-title">Add Class Setting</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="tablelist-form" autocomplete="off" id="add-class-form">
                            <div class="modal-body">
                                <input type="hidden" id="add-id-field" name="id">
                                <input type="hidden" id="staffid" name="staffid" value="{{ auth()->user()->id }}">
                                <div class="mb-3">
                                    <label for="vschoolclassid" class="form-label">Class</label>
                                    <select id="vschoolclassid" name="vschoolclassid" class="form-control" required>
                                        @foreach ($schoolclasses as $class)
                                            <option value="{{ $class->id }}">{{ $class->schoolclass }} ({{ $class->arm }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="termid" class="form-label">Term</label>
                                    <select id="termid" name="termid" class="form-control" required>
                                        @foreach ($terms as $term)
                                            <option value="{{ $term->id }}">{{ $term->term }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="sessionid" class="form-label">Session</label>
                                    <select id="sessionid" name="sessionid" class="form-control" required>
                                        @foreach ($schoolsessions as $session)
                                            <option value="{{ $session->id }}">{{ $session->session }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="noschoolopened" class="form-label">Number of School Days Opened</label>
                                    <input type="number" id="noschoolopened" name="noschoolopened" class="form-control" placeholder="Enter number of school days">
                                </div>
                                <div class="mb-3">
                                    <label for="termends" class="form-label">Term Ends</label>
                                    <input type="date" id="termends" name="termends" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="nexttermbegins" class="form-label">Next Term Begins</label>
                                    <input type="date" id="nexttermbegins" name="nexttermbegins" class="form-control">
                                </div>
                                <div class="alert alert-danger d-none" id="alert-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="add-btn">Add Class Setting</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Class Setting Modal -->
            <div id="editModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="editModalLabel" class="modal-title">Edit Class Setting</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="tablelist-form" autocomplete="off" id="edit-class-form">
                            <div class="modal-body">
                                <input type="hidden" id="edit-id-field" name="id">
                                <input type="hidden" id="edit-staffid" name="staffid" value="{{ auth()->user()->id }}">
                                <div class="mb-3">
                                    <label for="edit-vschoolclassid" class="form-label">Class</label>
                                    <select id="edit-vschoolclassid" name="vschoolclassid" class="form-control" required>
                                        @foreach ($schoolclasses as $class)
                                            <option value="{{ $class->id }}">{{ $class->schoolclass }} ({{ $class->arm }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-termid" class="form-label">Term</label>
                                    <select id="edit-termid" name="termid" class="form-control" required>
                                        @foreach ($terms as $term)
                                            <option value="{{ $term->id }}">{{ $term->term }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-sessionid" class="form-label">Session</label>
                                    <select id="edit-sessionid" name="sessionid" class="form-control" required>
                                        @foreach ($schoolsessions as $session)
                                            <option value="{{ $session->id }}">{{ $session->session }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-noschoolopened" class="form-label">Number of School Days Opened</label>
                                    <input type="number" id="edit-noschoolopened" name="noschoolopened" class="form-control" placeholder="Enter number of school days">
                                </div>
                                <div class="mb-3">
                                    <label for="edit-termends" class="form-label">Term Ends</label>
                                    <input type="date" id="edit-termends" name="termends" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="edit-nexttermbegins" class="form-label">Next Term Begins</label>
                                    <input type="date" id="edit-nexttermbegins" name="nexttermbegins" class="form-control">
                                </div>
                                <div class="alert alert-danger d-none" id="alert-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="update-btn">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Class Setting Modal -->
            <div id="deleteRecordModal" class="modal fade zoomIn" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="btn-close" id="deleteRecord-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-md-5">
                            <div class="text-center">
                                <div class="text-danger">
                                    <i class="bi bi-trash display-4"></i>
                                </div>
                                <div class="mt-4">
                                    <h3 class="mb-2">Are you sure?</h3>
                                    <p class="text-muted fs-lg mx-3 mb-0">Are you sure you want to remove this class setting?</p>
                                </div>
                            </div>
                            <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                                <button type="button" class="btn w-sm btn-light btn-hover" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn w-sm btn-danger btn-hover" id="delete-record">Yes, Delete It!</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page-content -->

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="{{ asset('js/class-list.init.js') }}"></script>
        <!-- Chart Initialization -->
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                var ctx = document.getElementById("classesByTermChart").getContext("2d");
                new Chart(ctx, {
                    type: "bar",
                    data: {
                        labels: @json(array_keys($term_counts)),
                        datasets: [{
                            label: "Classes by Term",
                            data: @json(array_values($term_counts)),
                            backgroundColor: ["#4e73df", "#1cc88a", "#36b9cc", "#f6c23e", "#e74a3b"],
                            borderColor: ["#4e73df", "#1cc88a", "#36b9cc", "#f6c23e", "#e74a3b"],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: "Number of Classes"
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: "Terms"
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: "top"
                            }
                        }
                    }
                });
            });
        </script>
    </div>
</div>
@endsection