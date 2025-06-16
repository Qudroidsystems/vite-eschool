@extends('layouts.master')
@section('content')

            <!--begin::Main-->
            <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                <!--begin::Content wrapper-->
                <div class="d-flex flex-column flex-column-fluid">

                <!--begin::Toolbar-->
                <div id="kt_app_toolbar" class="app-toolbar  py-3 py-lg-6 ">

                            <!--begin::Toolbar container-->
                        <div id="kt_app_toolbar_container" class="app-container  container-xxl d-flex flex-stack ">

                            <!--begin::Page title-->
                            <div  class="page-title d-flex flex-column justify-content-center flex-wrap me-3 ">
                                <!--begin::Title-->
                                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                              <p style="color: green" >School Bill Payments</p>
                                        </h1>
                                <!--end::Title-->


                                    <!--begin::Breadcrumb-->
                                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                                                        <!--begin::Item-->
                                                        <li class="breadcrumb-item text-muted">
                                                            <a href="{{ route('schoolpayment.index') }}" class="text-muted text-hover-primary">Student Management </a>
                                                                        </li>
                                                            <!--end::Item-->
                                                                <!--begin::Item-->
                                                <li class="breadcrumb-item">
                                                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                                                </li>
                                                <!--end::Item-->

                                                        <!--begin::Item-->
                                                                <li class="breadcrumb-item text-muted">School BIlls  for Students</li>
                                                            <!--end::Item-->

                                                </ul>
                                    <!--end::Breadcrumb-->
                                </div>
                            <!--end::Page title-->
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

                                @if (\Session::has('status'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ \Session::get('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                @endif
                                @if (\Session::has('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ \Session::get('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                @endif

                            </div>
                            <!--end::Toolbar container-->
                        </div>
                    <!--end::Toolbar-->


                    <div id="kt_app_content" class="app-content  flex-column-fluid " >
                        <!--begin::Content container-->
                        <div id="kt_app_content_container" class="app-container   ">

                   {{-- <!--begin::Toolbar-->
                        <div class="d-flex flex-wrap flex-stack my-5">
                            <!--begin::Heading-->
                            <h2 class="fs-2 fw-semibold my-2">
                               Subject Registration For Student
                                <span class="fs-6 text-gray-400 ms-1">Database</span>
                            </h2>
                            <!--end::Heading-->


                        </div>
                    <!--end::Toolbar--> --}}



        <!--begin::Card-->
    <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">

                  <!--begin::Card toolbar-->
                    <div class="card-toolbar">
                              <!--begin::Toolbar-->
                                    <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                                                <!--begin::Add user-->
                                                <a #" type="button" class="btn btn-primary">
                                                       School Bills for Students
                                                </a>
                                                <!--end::Add user-->
                                    </div>





                                    </div>
                                    <!--end::Card toolbar-->
                                            <!--begin::Card toolbar-->
                                            <div class="card-toolbar">
                                                <!--begin::Search-->
                                                <div class="d-flex align-items-center position-relative my-1"  data-kt-view-roles-table-toolbar="base">
                                                    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6"><span class="path1"></span><span class="path2"></span></i>                <input type="text" data-kt-roles-table-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Search ..." />
                                                </div>
                                                <!--end::Search-->

                                                <!--begin::Group actions-->
                                                <div class="d-flex justify-content-end align-items-center d-none" data-kt-view-roles-table-toolbar="selected">
                                                    <div class="fw-bold me-5">
                                                        <span class="me-2" data-kt-view-roles-table-select="selected_count"></span> Selected
                                                    </div>

                                                    <button type="button" class="btn btn-danger" data-kt-view-roles-table-select="delete_selected">
                                                        Delete Selected
                                                    </button>
                                                </div>
                                                <!--end::Group actions-->
                                            </div>
                                            <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->

            @if (count($errors) > 0)
            <div class="row animated fadeInUp">
                @if (count($errors) > 0)
            <div class="alert alert-warning fade in">
            <a href="#" class="close" data-dismiss="alert">&times;</a>
                <strong>Opps!</strong> Something went wrong, please check below errors.<br><br>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            </div>
            @endif
            <!--begin::Card body-->
            <div class="card-body py-4">

                <!--begin::Table-->
                        <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0" id="kt_roles_view_table">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th class="w-10px pe-2">
                                        <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                            <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_roles_view_table .form-check-input" value="1" />
                                        </div>
                                    </th>
                                    <th class="min-w-125px" style="color: green">SN</th>
                                    <th class="min-w-125px" style="color: green">Admmssion NO</th>
                                    <th class="min-w-125px" style="color: green">Name</th>
                                    <th class="min-w-125px" style="color: green">Gender</th>
                                    <th class="min-w-100px" style="color: green">Action</th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-600">
                                @php
                                $i = 0
                            @endphp
                                @foreach ($student as $sc)
                                @if ($sc->cstatus == "CURRENT")

                                    <tr>
                                        <td>
                                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                <input class="form-check-input" type="checkbox" value="1" />
                                            </div>
                                        </td>
                                        <td>  <input type="hidden" id="tid"  value="{{ $sc->id }}" />{{ ++$i }}</td>
                                        <td >{{ $sc->admissionNo }}</td>
                                        <td class="d-flex align-items-center">
                                            <!--begin:: Avatar -->
                                                    <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                                        <a href="#">
                                                                        <div class="symbol-label">
                                                        <?php $image = "";?>
                                                        <?php
                                                        if ($sc->picture  == NULL || !isset($sc->picture ) || $sc->picture =="" ){
                                                                $image =  'unnamed.png';
                                                        }else {
                                                            $image =   $sc->picture;
                                                        }
                                                        ?>
                                                                    <img src="{{ Storage::url('images/studentavatar/'.$image)}}" alt="{{ $sc->staffname }}" class="w-100" />
                                                                </div>
                                                                                    </a>
                                                    </div>
                                                    <!--end::Avatar-->
                                                    <!--begin::User details-->
                                                    <div class="d-flex flex-column">
                                                        <a href="#" class="text-gray-800 text-hover-primary mb-1">{{ $sc->firstname }} {{ $sc->lastname }}</a>

                                                    </div>
                                                    <!--begin::User details-->
                                        </td>
                                        <td >{{ $sc->gender }}</td>
                                        <td >
                                            <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                Actions
                                                <i class="ki-duotone ki-down fs-5 ms-1"></i>                    </a>
                                            <!--begin::Menu-->
                                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                                    @can('subject_class-edit')
                                                        <!--begin::Menu item-->
                                                        <div class="menu-item px-3">

                                                            <a  href="{{ route('schoolpayment.termsession',$sc->id) }}"  data-toggle="tooltip" title=" School Bill Payment for {{ $sc->firstname  }} {{ $sc->lastname }}"  class=" btn btn-light btn-active-primary" >Proceed to Payment</a>
                                                        </div>
                                                        <!--end::Menu item-->
                                                    @endcan
                                                </div>
                                                    <!--end::Menu-->
                                        </td>

                            </tr>
                            @endif
                            @endforeach
                                            </tbody>
                        </table>
                <!--end::Table-->
            </div>
            <!--end::Card body-->
      </div>
            <!--end::Card-->
    </div>

            <!--end::Content container-->
</div>
        <!--end::Content-->


@endsection
