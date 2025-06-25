@extends('layouts.master')
@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">School Analytics</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboards</a></li>
                                <li class="breadcrumb-item active">School Analytics</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            <div class="row">
                <div class="col-xxl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <div class="d-flex flex-column h-100">
                                        <p class="fs-md text-muted mb-4">Total Population</p>
                                        <h3 class="mb-0 mt-auto">
                                            <span class="counter-value" data-target="{{ $total_population }}">0</span>
                                            <small class="text-success fs-xs mb-0 ms-1"><i class="bi bi-arrow-up me-1"></i> 06.19%</small>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--end col-->
                <div class="col-xxl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <div class="d-flex flex-column h-100">
                                        <p class="fs-md text-muted mb-4">Staff</p>
                                        <h3 class="mb-0 mt-auto">
                                            <span class="counter-value" data-target="{{ $staff_count }}">0</span>
                                            <small class="text-success fs-xs mb-0 ms-1"><i class="bi bi-arrow-up me-1"></i> 02.33%</small>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--end col-->
                <div class="col-xxl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <div class="d-flex flex-column h-100">
                                        <p class="fs-md text-muted mb-4">Male Students</p>
                                        <h3 class="mb-0 mt-auto">
                                            <span class="counter-value" data-target="{{ $gender_counts['Male'] }}">0</span>
                                            <small class="text-success fs-xs mb-0 ms-1"><i class="bi bi-arrow-up me-1"></i> 12.33%</small>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--end col-->
                <div class="col-xxl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <div class="d-flex flex-column h-100">
                                        <p class="fs-md text-muted mb-4">Female Students</p>
                                        <h3 class="mb-0 mt-auto">
                                            <span class="counter-value" data-target="{{ $gender_counts['Female'] }}">0</span>
                                            <small class="text-danger fs-xs mb-0 ms-1"><i class="bi bi-arrow-down me-1"></i> 09.57%</small>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--end col-->
            </div><!--end row-->

            <!-- Commented-out sections remain unchanged -->
        
            <div class="row">
                <div class="col-xxl-4 order-last order-xxl-first">
                    <div class="card">
                        <div class="card-header d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">School Population Census</h4>
                            <div class="dropdown card-header-dropdown float-end">
                                <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#">Today</a>
                                    <a class="dropdown-item" href="#">Last Week</a>
                                    <a class="dropdown-item" href="#">Last Month</a>
                                    <a class="dropdown-item" href="#">Current Year</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="property_type" data-colors='["--tb-primary", "--tb-secondary", "--tb-light","--tb-danger", "--tb-success"]' class="e-charts shadow-none" style="height: 336px;"></div>
                        </div>
                    </div>
                </div>
                <!--end col-->
                <div class="col-xxl-8">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h5 class="card-title flex-grow-1 mb-0">Revenue Overview</h5>
                            <div class="flex-shrink-0">
                                <input type="text" class="form-control form-control-sm" id="exampleInputPassword1" data-provider="flatpickr" data-range-date="true" data-date-format="d M, Y" data-default-date="01 Feb 2023 to 28 Feb 2023">
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-lg-3">
                                    <div class="nav flex-column nav-light nav-pills gap-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                        <a class="nav-link d-flex p-2 gap-3 active" id="revenue-tab" data-bs-toggle="pill" href="#revenue" role="tab" aria-controls="revenue" aria-selected="true">
                                            <div class="avatar-sm flex-shrink-0">
                                                <div class="avatar-title rounded bg-warning-subtle text-warning fs-2xl">
                                                    <i class="bi bi-coin"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="text-reset">$<span class="counter-value" data-target="2478">0</span>M</h5>
                                                <p class="mb-0">Total Revenue</p>
                                            </div>
                                        </a>
                                        <a class="nav-link d-flex p-2 gap-3" id="income-tab" data-bs-toggle="pill" href="#income" role="tab" aria-controls="income" aria-selected="false">
                                            <div class="avatar-sm flex-shrink-0">
                                                <div class="avatar-title rounded bg-success-subtle text-success fs-2xl">
                                                    <i class="bi bi-coin"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="text-reset">$<span class="counter-value" data-target="14587.37">0</span></h5>
                                                <p class="mb-0">Prev. Session</p>
                                            </div>
                                        </a>
                                        <a class="nav-link d-flex p-2 gap-3" id="property-sale-tab" data-bs-toggle="pill" href="#property-sale" role="tab" aria-controls="property-sale" aria-selected="false">
                                            <div class="avatar-sm flex-shrink-0">
                                                <div class="avatar-title rounded bg-danger-subtle text-danger fs-2xl">
                                                    <i class="bi bi-coin"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="text-reset"><span class="counter-value" data-target="2365">0</span></h5>
                                                <p class="mb-0">Current Session</p>
                                            </div>
                                        </a>
                                        <a class="nav-link d-flex p-2 gap-3" id="_-tab" data-bs-toggle="pill" href="#propetry-rent" role="tab" aria-controls="propetry-rent" aria-selected="false">
                                            <div class="avatar-sm flex-shrink-0">
                                                <div class="avatar-title rounded bg-primary-subtle text-primary fs-2xl">
                                                    <i class="bi bi-coin"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="text-reset"><span class="counter-value" data-target="3456">0</span></h5>
                                                <p class="mb-0">Total Expenditure</p>
                                            </div>
                                        </a>
                                    </div>
                                </div><!--end col-->
                                <div class="col-lg-9">
                                    <div class="tab-content text-muted">
                                        <div class="tab-pane active" id="revenue" role="tabpanel">
                                            <div id="total_revenue" data-colors='["--tb-primary"]' class="apex-charts effect-chart" dir="ltr"></div>
                                        </div><!--end tab-->
                                        <div class="tab-pane" id="income" role="tabpanel">
                                            <div id="total_income" data-colors='["--tb-success"]' class="apex-charts" dir="ltr"></div>
                                        </div>
                                        <div class="tab-pane" id="property-sale" role="tabpanel">
                                            <div id="property_sale_chart" data-colors='["--tb-danger"]' class="apex-charts" dir="ltr"></div>
                                        </div>
                                        <div class="tab-pane" id="propetry-rent" role="tabpanel">
                                            <div id="propetry_rent" data-colors='["--tb-info"]' class="apex-charts" dir="ltr"></div>
                                        </div>
                                    </div>
                                </div><!--end col-->
                            </div><!--end row-->
                        </div>
                    </div>
                </div><!--end col-->
            </div>
            <!--end row-->

            <div class="row">
                <div class="col-xxl-9">
                    <div class="card" id="propertyList">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Recently Added Property</h4>
                            <div class="flex-shrink-0">
                                <div class="dropdown card-header-dropdown sortble-dropdown">
                                    <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="fw-semibold text-uppercase fs-12">Sort by:
                                        </span><span class="text-muted dropdown-title">Property Name</span> <i class="mdi mdi-chevron-down ms-1"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <button class="dropdown-item sort" data-sort="propert_name">Property Name</button>
                                        <button class="dropdown-item sort" data-sort="price">Price</button>
                                        <button class="dropdown-item sort" data-sort="agent_name">Agent Name</button>
                                        <button class="dropdown-item sort" data-sort="status">Status</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-borderless table-centered align-middle table-nowrap mb-0">
                                    <thead class="text-muted table-light">
                                        <tr>
                                            <th scope="col" class="sort cursor-pointer" data-sort="propert_id">#</th>
                                            <th scope="col" class="sort cursor-pointer" data-sort="propert_type">Property Type</th>
                                            <th scope="col" class="sort cursor-pointer" data-sort="propert_name">Property Name</th>
                                            <th scope="col" class="sort cursor-pointer" data-sort="address">Address</th>
                                            <th scope="col" class="sort cursor-pointer" data-sort="agent_name">Agent Name</th>
                                            <th scope="col" class="sort cursor-pointer" data-sort="price">Price</th>
                                            <th scope="col" class="sort cursor-pointer" data-sort="status">Status</th>
                                            <th scope="col" class="sort cursor-pointer">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        <tr>
                                            <td class="propert_id">
                                                <a href="apps-ecommerce-order-details.html" class="fw-medium link-primary">#TBS01</a>
                                            </td>
                                            <td class="propert_type">
                                                Villa
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2 position-relative">
                                                    <img src="assets/images/real-estate/img-01.jpg" alt="" height="35" class="rounded">
                                                    <a href="apps-real-estate-property-overview.html" class="propert_name text-reset stretched-link">The Country House</a>
                                                </div>
                                            </td>
                                            <td class="address">
                                                United Kingdom
                                            </td>
                                            <td class="agent_name">Josefa Weissnat</td>
                                            <td class="price">
                                                <span class="fw-medium">$2451.39</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger-subtle text-danger status">Sale</span>
                                            </td>
                                            <td>
                                                <ul class="d-flex gap-2 list-unstyled mb-0">
                                                    <li>
                                                        <a href="apps-real-estate-property-overview.html" class="btn btn-subtle-primary btn-icon btn-sm "><i class="ph-eye"></i></a>
                                                    </li>
                                                    <li>
                                                        <a href="#!" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn"><i class="ph-pencil"></i></a>
                                                    </li>
                                                    <li>
                                                        <a href="#!" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn"><i class="ph-trash"></i></a>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                        <!-- Additional table rows remain unchanged -->
                                    </tbody>
                                </table>
                                <div class="noresult" style="display: none">
                                    <div class="text-center">
                                        <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop" colors="primary:#405189,secondary:#0ab39c" style="width:75px;height:75px"></lord-icon>
                                        <h5 class="mt-2">Sorry! No Result Found</h5>
                                        <p class="text-muted mb-0">We've searched more than 150+ transactions We did not find any transactions for you search.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-lg-6">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4 class="card-title mb-0 flex-grow-1">Customer Feedback</h4>
                            <div class="flex-shrink-0">
                                <a href="#!" class="text-muted">View All <i class="bi bi-chevron-right align-baseline"></i></a>
                            </div>
                        </div>
                        <div class="card-body px-0">
                            <div data-simplebar style="max-height: 400px;">
                                <!-- Feedback items remain unchanged -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-lg-6">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4 class="card-title mb-0 flex-grow-1">Popular Property</h4>
                            <div class="flex-shrink-0">
                                <div class="nav nav-pills gap-1" id="popularProperty" role="tablist" aria-orientation="vertical">
                                    <button class="btn btn-ghost-danger btn-sm active" id="saleProperty" data-bs-toggle="pill" data-bs-target="#salePropertyTabs" type="button" role="tab" aria-controls="salePropertyTabs" aria-selected="true">Sale</button>
                                    <button class="btn btn-ghost-info btn-sm" id="rentProperty" data-bs-toggle="pill" data-bs-target="#rentPropertyTabs" type="button" role="tab" aria-controls="rentPropertyTabs" aria-selected="false" tabindex="-1">Rent</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="tab-content mb-2" id="popularPropertyContent">
                                <!-- Popular property items remain unchanged -->
                            </div>
                            <div class="text-center">
                                <a href="apps-real-estate-list.html" class="icon-link">View All <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-8">
                    <div class="d-flex flex-column h-100">
                        <div class="row h-100 justify-content-between">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row g-4">
                                            <div class="col-xxl-3 col-md-6 border-end-md border-dashed">
                                                <div class="text-center">
                                                    <p class="text-muted">Project On Hold</p>
                                                    <div class="mx-3 mb-3 pb-1">
                                                        <div id="mini-chart-6" data-colors='["--tb-secondary"]' class="apex-charts" dir="ltr"></div>
                                                    </div>
                                                    <h5 class="mb-0">2451 <small class="badge fs-2xs bg-danger-subtle text-danger ms-1"><i class="ph-arrow-down align-baseline"></i> 1.02%</small></h5>
                                                </div>
                                            </div>
                                            <div class="col-xxl-3 col-md-6 border-end-xxl border-dashed">
                                                <div class="text-center">
                                                    <p class="text-muted">Ongoing Properties</p>
                                                    <div class="mx-3 mb-3 pb-1">
                                                        <div id="mini-chart-7" data-colors='["--tb-primary"]' class="apex-charts" dir="ltr"></div>
                                                    </div>
                                                    <h5 class="mb-0">$750.36M <small class="badge fs-2xs bg-success-subtle text-success ms-1"><i class="ph-arrow-up align-baseline"></i> 2.17%</small></h5>
                                                </div>
                                            </div>
                                            <div class="col-xxl-3 col-md-6 border-end-md border-dashed">
                                                <div class="text-center">
                                                    <p class="text-muted">Pending Properties</p>
                                                    <div class="mx-3 mb-3 pb-1">
                                                        <div id="mini-chart-8" data-colors='["--tb-warning"]' class="apex-charts" dir="ltr"></div>
                                                    </div>
                                                    <h5 class="mb-0">$750.36M <small class="badge fs-2xs bg-success-subtle text-success ms-1"><i class="ph-arrow-up align-baseline"></i> 07.26%</small></h5>
                                                </div>
                                            </div>
                                            <div class="col-xxl-3 col-md-6">
                                                <div class="text-center">
                                                    <p class="text-muted">Completed Project</p>
                                                    <div class="mx-3 mb-3 pb-1">
                                                        <div id="mini-chart-9" data-colors='["--tb-success"]' class="apex-charts" dir="ltr"></div>
                                                    </div>
                                                    <h5 class="mb-0">4689 <small class="badge fs-2xs bg-success-subtle text-success ms-1"><i class="ph-arrow-up align-baseline"></i> 3.62%</small></h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header d-flex align-items-center">
                                        <h4 class="card-title mb-0 flex-grow-1">Recent Activity</h4>
                                        <div class="flex-shrink-0">
                                            <a href="pages-timeline.html" class="text-muted">View All <i class="bi bi-chevron-right align-baseline"></i></a>
                                        </div>
                                    </div>
                                    <div class="card-body px-0">
                                        <div class="px-3" data-simplebar style="height: 255px">
                                            <div class="acitivity-timeline acitivity-main">
                                                <!-- Activity items remain unchanged -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header d-flex align-items-center">
                                        <h4 class="card-title mb-0 flex-grow-1">Agent List</h4>
                                        <div class="flex-shrink-0">
                                            <a href="apps-real-estate-agent-list.html" class="text-muted">View All <i class="bi bi-chevron-right align-baseline"></i></a>
                                        </div>
                                    </div>
                                    <div class="card-body pt-4">
                                        <div class="table-responsive table-card">
                                            <table class="table table-borderless table-centered align-middle table-nowrap mb-0">
                                                <tbody>
                                                    <!-- Agent list items remain unchanged -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end row-->
        </div>
        <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

    <!-- JAVASCRIPT -->
    {{-- <script src="{{ asset('theme/layouts/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('theme/layouts/assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('theme/layouts/assets/js/plugins.js') }}"></script>
    <script src="{{ asset('theme/layouts/assets/libs/list.js/list.min.js') }}"></script>
    <script src="{{ asset('theme/layouts/assets/libs/echarts/echarts.min.js') }}"></script>
    <script src="{{ asset('theme/layouts/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('theme/layouts/assets/js/pages/dashboard-real-estate.init.js') }}"></script>
    <script src="{{ asset('theme/layouts/assets/js/app.js') }}"></script> --}}

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Counter animation for the cards
            const counters = document.querySelectorAll('.counter-value');
            counters.forEach(counter => {
                const updateCount = () => {
                    const target = +counter.getAttribute('data-target');
                    const count = +counter.innerText;
                    const increment = target / 100;
                    if (count < target) {
                        counter.innerText = Math.ceil(count + increment);
                        setTimeout(updateCount, 20);
                    } else {
                        counter.innerText = target;
                    }
                };
                updateCount();
            });
        });
    </script>
</div>
@endsection