
@extends('layouts.master')
@section('content')


           <!--begin::Main-->
        <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
            <!--begin::Content wrapper-->
            <div class="d-flex flex-column flex-column-fluid">

<!--begin::Toolbar-->
<div id="kt_app_toolbar" class="app-toolbar  py-3 py-lg-6 " >

        <!--begin::Toolbar container-->
    <div id="kt_app_toolbar_container" class="app-container  container-xxl d-flex flex-stack ">



<!--begin::Page title-->
<div  class="page-title d-flex flex-column justify-content-center flex-wrap me-3 ">
<!--begin::Title-->
<h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
   <p style="color: green"> My Class Students</p>
        </h1>
<!--end::Title-->


    <!--begin::Breadcrumb-->
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">


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

<!--begin::Content-->
<div id="kt_app_content" class="app-content  flex-column-fluid " >


    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container  container-xxl ">


         <!--begin::Navbar-->
<div class="card mb-5 mb-xl-10">
<div class="card-body pt-9 pb-0">
    <!--begin::Details-->
    <div class="d-flex flex-wrap flex-sm-nowrap">

        <!--begin::Info-->
        <div class="flex-grow-1">
            <!--begin::Title-->
            <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                {{-- <!--begin::User-->
                <div class="d-flex flex-column">
                    <!--begin::Name-->
                    <div class="d-flex align-items-center mb-2">
                        <a href="#" class="text-gray-900 text-hover-primary fs-2 fw-bold me-1">{{ $fn }} {{ $ln }}</a>
                        <a href="#"><i class="ki-duotone ki-verify fs-1 text-primary"><span class="path1"></span><span class="path2"></span></i></a>
                    </div>
                    <!--end::Name-->

                    <!--begin::Info-->
                    <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">

                        <a href="#" class="d-flex align-items-center  me-5 mb-2 vv">
                            <i class="ki-duotone ki-profile-circle fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            {{ $fn }} {{ $ln }}
                        </a>

                        <a href="#" class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2">
                            <i class="ki-duotone ki-geolocation fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                            {{ $cname }}  {{ $aname }}
                        </a>

                    </div>
                    <!--end::Info-->
                </div>
                <!--end::User--> --}}


            </div>
            <!--end::Title-->

            <!--begin::Stats-->
            <div class="d-flex flex-wrap flex-stack">
                <!--begin::Wrapper-->
                <div class="d-flex flex-column flex-grow-1 pe-8">
                    <!--begin::Stats-->
                    <div class="d-flex flex-wrap">
                        <!--begin::Stat-->
                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                            <!--begin::Number-->
                            <div class="d-flex align-items-center">
                                <i class="ki-duotone ki-arrow-up fs-3 text-primary me-2"><span class="path1"></span><span class="path2"></span></i>
                                <div class="fs-2 fw-bold" data-kt-countup="true" data-kt-countup-value="{{ $studentcount }}" data-kt-countup-prefix="">0</div>
                            </div>
                            <!--end::Number-->

                            <!--begin::Label-->
                            <div class="fw-semibold fs-6 text-gray-400">Total Students in Class</div>
                            <!--end::Label-->
                        </div>
                        <!--end::Stat-->

                        <!--begin::Stat-->
                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                            <!--begin::Number-->
                            <div class="d-flex align-items-center">
                                <i class="ki-duotone ki-arrow-up fs-3 text-success me-2"><span class="path1"></span><span class="path2"></span></i>
                                <div class="fs-2 fw-bold" data-kt-countup="true" data-kt-countup-value="{{ $male }}">0</div>
                            </div>
                            <!--end::Number-->

                            <!--begin::Label-->
                            <div class="fw-semibold fs-6 text-gray-400"> Male</div>
                            <!--end::Label-->
                        </div>
                        <!--end::Stat-->

                          <!--begin::Stat-->
                          <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                            <!--begin::Number-->
                            <div class="d-flex align-items-center">
                                <i class="ki-duotone ki-arrow-up fs-3 text-success me-2"><span class="path1"></span><span class="path2"></span></i>
                                <div class="fs-2 fw-bold" data-kt-countup="true" data-kt-countup-value="{{ $female }}">0</div>
                            </div>
                            <!--end::Number-->

                            <!--begin::Label-->
                            <div class="fw-semibold fs-6 text-gray-400"> Female</div>
                            <!--end::Label-->
                        </div>
                        <!--end::Stat-->


                    </div>
                    <!--end::Stats-->
                </div>
                <!--end::Wrapper-->


            </div>
            <!--end::Stats-->
        </div>
        <!--end::Info-->
    </div>
    <!--end::Details-->

    <!--begin::Navs-->
    <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">

    </ul>
    <!--begin::Navs-->
</div>
</div>
<!--end::Navbar-->
<!--begin::Card toolbar-->
<div class="card-toolbar">
<!--begin::Button-->
<a href="{{ route('myclass.index') }}" class="btn btn-light-primary" >
               << Back
</a>
<!--end::Button-->
</div>

<!--begin::Basic info-->
<div class="card mb-5 mb-xl-10">
<!--begin::Card header-->
<div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
    <!--begin::Card title-->
     <?php
                // $session = "";
                // $term = "";
            foreach($session as $s){
                $session =  $s->session;
            }

            foreach($term as $s){
                $term =  $s->term;
            }

            foreach($schoolclass as $s){
                $schoolclass =  $s->schoolclass;
                $schoolarm = $s->arm;
            }
    ?>
    <div class="card-title m-0">
        <h3 class="fw-bold m-0"><p style="color: rgb(109, 109, 212)"> Class Record for <b style="color: green">{{ $schoolclass }}  {{ $schoolarm }}</b>  <b>{{ $term }} {{ $session }}</p><span class="badge badge-success"><i class="fa fa-eye"></i> {{$studentcount}}</span></h3>
    </div>
    <!--end::Card title-->
</div>


<!--begin::Card header-->
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
<!--begin::Content-->

<?php use App\Models\subjectRegistrationStatus;?>
<div id="kt_account_settings_profile_details" class="collapse show">

<!--begin::Card body-->
<div class="card-body py-4">
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
<!--end::Card toolbar-->
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
                    <th class="min-w-125px" style="color: green">Admission No</th>
                    <th class="min-w-125px" style="color: green">Student Name</th>
                    <th class="min-w-125px" style="color: green">Gender</th>
                    <th class="min-w-100px" style="color: green">Action</th>
                </tr>
            </thead>
            <tbody class="fw-semibold text-gray-600">
                @php
                  $i = 0
                @endphp
                @foreach ($allstudents as $sc)
                    <tr>
                        <td>
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" value="1" />
                            </div>
                        </td>
                        <td> {{ ++$i }}</td>
                        <td >{{ $sc->admissionno }}</td>
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
                                        <a href="#" class="text-gray-800 text-hover-primary mb-1">{{ $sc->firstname }} {{ $sc->lastname }}  {{ $sc->othername }}</a>

                                    </div>
                                    <!--begin::User details-->
                        </td>
                        <td >{{ $sc->gender }}</td>

                        <td >
                            <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                Actions
                                <i class="ki-duotone ki-down fs-5 ms-1"></i>                    </a>
                            <!--begin::Menu-->
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-10" data-kt-menu="true">

                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">

                                            <a href="/studentpersonalityprofile/{{ $sc->stid }}/{{ $schoolclassid }}/{{ $termid }}/{{ $sessionid }}" data-toggle="tooltip"
                                                title=" Student's  Personality Profile" class=" btn btn-light btn-active-primary" >Persionality Profile</a>
                                        </div>
                                        <!--end::Menu item-->

                                </div>
                                    <!--end::Menu-->
                        </td>

            </tr>

            @endforeach
                            </tbody>
        </table>
<!--end::Table-->
</div>
<!--end::Card body-->

</div>
<!--end::Content-->
</div>
<!--end::Basic info-->



</div>
    <!--end::Content container-->
</div>
<!--end::Content-->
            </div>
            <!--end::Content wrapper-->

@endsection

