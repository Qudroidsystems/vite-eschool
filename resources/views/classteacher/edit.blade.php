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
                                Class Teacher
                                        </h1>
                                <!--end::Title-->


                                    <!--begin::Breadcrumb-->
                                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                                                        <!--begin::Item-->
                                                        <li class="breadcrumb-item text-muted">
                                                            <a href="{{ route('classteacher.index') }}" class="text-muted text-hover-primary">Class Teacher </a>
                                                                        </li>
                                                            <!--end::Item-->
                                                                <!--begin::Item-->
                                                <li class="breadcrumb-item">
                                                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                                                </li>
                                                <!--end::Item-->

                                                        <!--begin::Item-->
                                                                <li class="breadcrumb-item text-muted">Class Teacher</li>
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

                                <!--begin::Actions-->
<div class="d-flex align-items-center gap-2 gap-lg-3">


<!--begin::Secondary button-->


<!--begin::Primary button-->
    <a href="{{ route('classteacher.index') }}" class="btn btn-sm fw-bold btn-primary" >
    << Back        </a>
<!--end::Primary button-->
</div>
<!--end::Actions-->
                            </div>
                            <!--end::Toolbar container-->
                        </div>
                    <!--end::Toolbar-->


        <div id="kt_app_content" class="app-content  flex-column-fluid " >
            <!--begin::Content container-->

            <?php
            foreach($classteachers as $cteachers) {
                $cteachers->ctid;
                $cteachers->staffname;
                $cteachers->schoolclass;
                $cteachers->schoolarm;
                $cteachers->schoolterm;
                $cteachers->schoolsession;
            }

            ?>
        <!--begin::Content container-->
        <div id="kt_app_content_container" class="app-container  container-xxl ">
            {{-- <form id="kt_ecommerce_add_category_form" class="form d-flex flex-column flex-lg-row" action="{{ route('classteacher.update',$cteachers->ctid) }}" method="PATCH"> --}}
        {!! Form::model($classteachers, ['route' => ['classteacher.update', $cteachers->ctid], 'method'=>'PATCH','class'=>'form d-flex flex-column flex-lg-row']) !!}
                @csrf
                
    <!--begin::Main column-->
    <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
        <!--begin::General options-->
<div class="card card-flush py-4">
    <!--begin::Card header-->
    <div class="card-header">
        <div class="card-title">
            <h2>Edit Class Teacher</h2>
        </div>
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">

 <!--begin::Input group-->
 <div class="mb-7">
    <!--begin::Label-->
    <label class="required fw-semibold fs-6 mb-5">Select Class Teacher</label>
    <!--end::Label-->
            <!--begin::Input row-->
            <div class="fv-row mb-7">

                    <!--begin::Input-->
                    <select name ="staffid" id="staffid"  class="form-control form-control-solid mb-3 mb-lg-0">
                        @if ($cteachers->userid == "")
                        <option value="">Select Staff </option>
                        @else
                            <option value="{{ $cteachers->userid }}">Select  </option>
                        @endif

                        @foreach ($teachers as $staffname => $name )
                            <option value="{{$name->userid}}">{{ $name->name }} </option>
                        @endforeach
                        </select>[ {{ $cteachers->staffname }}]
                    <!--end::Input-->

            </div>
            <!--end::Input row-->
        </div>
        <!--end::Input group-->


        <!--begin::Input group-->
 <div class="mb-7">
    <!--begin::Label-->
    <label class="required fw-semibold fs-6 mb-5">Select Class </label>
    <!--end::Label-->
            <!--begin::Input row-->
            <div class="fv-row mb-7">

                    <!--begin::Input-->
                    <select name ="schoolclassid" id="schoolclassid"  class="form-control form-control-solid mb-3 mb-lg-0">
                        @if ($cteachers->classid == "")
                        <option value="">Select Class </option>
                   @else
                       <option value="{{ $cteachers->classid }}">Select  Class </option>
                   @endif

                   @foreach ($schoolclasses as $schoolclass => $name )
                    <option value="{{$name->id}}">{{ $name->schoolclass }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $name->arm}} </option>
                   @endforeach
               </select>[ {{ $cteachers->schoolclass }} &nbsp;&nbsp;&nbsp;&nbsp; {{ $cteachers->schoolarm }}]
                    <!--end::Input-->

            </div>
            <!--end::Input row-->
        </div>
        <!--end::Input group-->


        <!--begin::Input group-->
 <div class="mb-7">
    <!--begin::Label-->
    <label class="required fw-semibold fs-6 mb-5">Select Class Teacher</label>
    <!--end::Label-->
            <!--begin::Input row-->
            <div class="fv-row mb-7">

                    <!--begin::Input-->
                    <select name ="staffid" id="staffid"  class="form-control form-control-solid mb-3 mb-lg-0">
                        @if ($cteachers->userid == "")
                        <option value="">Select Staff </option>
                        @else
                            <option value="{{ $cteachers->userid }}">Select  </option>
                        @endif

                        @foreach ($teachers as $staffname => $name )
                            <option value="{{$name->userid}}">{{ $name->name }} </option>
                        @endforeach
                        </select>[ {{ $cteachers->staffname }}]
                    <!--end::Input-->

            </div>
            <!--end::Input row-->
        </div>
        <!--end::Input group-->


                            <!--begin::Input group-->
                    <div class="mb-7">
                        <!--begin::Label-->
                        <label class="required fw-semibold fs-6 mb-5">Select Term</label>
                        <!--end::Label-->
                                <!--begin::Input row-->
                                <div class="fv-row mb-7">

                                        <!--begin::Input-->
                                        <select name ="termid" id="termid"  class="form-control form-control-solid mb-3 mb-lg-0">
                                            @if ($cteachers->termid == "")
                                            <option value="{{ $cteachers->termid }}">Select Term </option>
                                            @else
                                                <option value="{{ $cteachers->termid }}" selected>Select  Term </option>
                                            @endif



                                            @foreach ($schoolterms as $schoolterm => $name )
                                            <option value="{{$name->id}}">{{ $name->term}}</option>
                                            @endforeach
                                        </select>[ {{ $cteachers->term }}]
                                        <!--end::Input-->

                                </div>
                                <!--end::Input row-->
                            </div>
                            <!--end::Input group-->



                              <!--begin::Input group-->
 <div class="mb-7">
    <!--begin::Label-->
    <label class="required fw-semibold fs-6 mb-5">Select Session</label>
    <!--end::Label-->
            <!--begin::Input row-->
            <div class="fv-row mb-7">

                    <!--begin::Input-->
                    <select name ="sessionid" id="sessiionid"  class="form-control form-control-solid mb-3 mb-lg-0">
                        @if ($cteachers->sessionid == "")
                        <option value="">Select Session </option>
                        @else
                            <option value="{{ $cteachers->sessionid }}">Select  Session </option>
                        @endif

                        @foreach ($schoolsessions as $schoolsession => $name )
                         <option value="{{$name->id}}">{{ $name->session}}</option>
                        @endforeach
                    </select>[ {{ $cteachers->session }}]
                    <!--end::Input-->

            </div>
            <!--end::Input row-->
        </div>
        <!--end::Input group-->



    </div>
    <!--end::Card header-->

    <div class="d-flex justify-content-end">
        <!--begin::Button-->
        <a href="products.html" id="kt_ecommerce_add_product_cancel" class="btn btn-light me-5">
           Cancel
       </a>
       <!--end::Button-->

       <!--begin::Button-->
       <button type="submit" id="kt_ecommerce_add_category_submit" class="btn btn-primary">
           <span class="indicator-label">
               Save Changes
           </span>
           <span class="indicator-progress">
               Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
           </span>
       </button>
       <!--end::Button-->
   </div>
</div>
<!--end::General options-->

    </div>
    <!--end::Main column-->
    {!! Form::close() !!}
 </div>

            <!--end::Content container-->
        </div>
                <!--end::Content-->


@endsection
