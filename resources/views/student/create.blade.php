
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
   Student Management
        </h1>
<!--end::Title-->


    <!--begin::Breadcrumb-->
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <!--begin::Item-->
                                <li class="breadcrumb-item text-muted">
                                                <a href="../index.html" class="text-muted text-hover-primary">
                          Student Managemt                           </a>
                                        </li>
                            <!--end::Item-->
                                <!--begin::Item-->
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <!--end::Item-->

                        <!--begin::Item-->
                                <li class="breadcrumb-item text-muted">
                                               Add Student Record                                            </li>
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

<!--begin::Content-->
<div id="kt_app_content" class="app-content  flex-column-fluid " >


    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container ">


                                <!--begin::Actions-->
                                <div class="d-flex align-items-center gap-2 gap-lg-3">


                                    <!--begin::Secondary button-->


                                    <!--begin::Primary button-->
                                        <a href="{{ route('student.index') }}" class="btn btn-sm fw-bold btn-primary" >
                                        << Back        </a>
                                    <!--end::Primary button-->
                                    </div>
                                    <!--end::Actions-->

<!--begin::Basic info-->
<div class="card mb-5 mb-xl-10">
<!--begin::Card header-->
<div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
    <!--begin::Card title-->
    <div class="card-title m-0">
        <h3 class="fw-bold m-0">Student Details</h3>
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
<div id="kt_account_settings_profile_details" class="collapse show">
    <!--begin::Form-->
    <form id="kt_account_profile_details_form" class="form" method="POST"
    enctype="multipart/form-data" action="{{ route('student.store') }}" >
      @csrf
        <!--begin::Card body-->
        <div class="card-body border-top p-9">
            <input type="hidden" name="registeredBy" value="{{ Auth::user()->id}}">
            <!--begin::Input group-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-semibold fs-6">Avatar</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8">

                    <!--begin::Image input-->
                    <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url('{{asset('assets/assets/media/svg/avatars/blank.svg')  }})">
                        <!--begin::Preview existing avatar-->

                        <div class="image-input-wrapper w-125px h-125px"></div>
                        <!--end::Preview existing avatar-->

                        <!--begin::Label-->
                        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change avatar">
                            <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                            <!--begin::Inputs-->
                            <input type="file" name="avatar" id="avatar" accept=".png, .jpg, .jpeg" required/>
                            <input type="hidden" name="avatar_remove"/>
                            <!--end::Inputs-->
                        </label>
                        <!--end::Label-->

                        <!--begin::Cancel-->
                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel avatar">
                            <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>                            </span>
                        <!--end::Cancel-->

                        <!--begin::Remove-->
                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove avatar">
                            <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>                            </span>
                        <!--end::Remove-->
                    </div>
                    <!--end::Image input-->

                    <!--begin::Hint-->
                    <div class="form-text">Allowed file types:  png, jpg, jpeg.</div>
                    <!--end::Hint-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->

            <!--begin::Input group-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Admission No</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8">
                    <!--begin::Row-->
                    <div class="row">
                        <!--begin::Col-->
                        <div class="col-lg-6 fv-row">
                            <input type="text" name="admissionNo" id="admissionNo" class="form-control form-control-lg form-control-solid mb-3 mb-lg-0" placeholder="Admission Number"  />
                        </div>
                        <!--end::Col-->
                    </div>
                    <!--end::Row-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->

            <!--begin::Input group-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Title</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">

                    <select  class="form-control form-control-lg form-control-solid" name="title" id="title">
                        <option value="" selected>Select Title </option>
                        <option value="Mr">Mr</option>
                        <option value="Mrs">Mrs</option>
                        <option value="Miss">Miss</option>
                    </select>

                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->

              <!--begin::Input group-->
              <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Full Name</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8">
                    <!--begin::Row-->
                    <div class="row">
                        <!--begin::Col-->
                        <div class="col-lg-6 fv-row">
                            <input type="text" name="firstname" id="firstname" class="form-control form-control-lg form-control-solid mb-3 mb-lg-0" placeholder="First name"  />
                        </div>
                        <!--end::Col-->

                        <!--begin::Col-->
                        <div class="col-lg-6 fv-row">
                            <input type="text" name="lastname" id="lastname" class="form-control form-control-lg form-control-solid" placeholder="Last name" />
                        </div>
                        <!--end::Col-->



                    </div>
                    <!--end::Row-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->



            <!--begin::Input group-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label  fw-semibold fs-6"></label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-6 fv-row">
                    <input type="text" name="othername" id="othername" class="form-control form-control-lg form-control-solid" placeholder="Other names"  />
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->


              <!--begin::Input group-->
              <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">
                    Gender
                </label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <!--begin::Options-->
                    <div class="d-flex align-items-center mt-3">
                        <!--begin::Option-->
                        <label class="form-check form-check-custom form-check-inline form-check-solid me-5">
                            <input class="form-check-input" name="gender"  type="radio" value="Male" required />
                            <span class="fw-semibold ps-2 fs-6">
                                Male
                            </span>
                        </label>
                        <!--end::Option-->

                        <!--begin::Option-->
                        <label class="form-check form-check-custom form-check-inline form-check-solid">
                            <input class="form-check-input" name="gender"  type="radio" value="Female" required />
                            <span class="fw-semibold ps-2 fs-6">
                                Female
                            </span>
                        </label>
                        <!--end::Option-->
                    </div>
                    <!--end::Options-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->



            <!--begin::Input group-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Home Address 1</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <input type="text" name="home_address" id="home_address" class="form-control form-control-lg form-control-solid" placeholder="Address"  />
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->

               <!--begin::Input group-->
               <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Home Address 2</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <input type="text" name="home_address2" id="home_address2" class="form-control form-control-lg form-control-solid" placeholder="Address"  />
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->


               <!--begin::Input group-->
               <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Date of Birth</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <input type="date" name="dateofbirth" id="dateofbirth" onkeyup="showage()" class="form-control form-control-lg form-control-solid" placeholder="Date of Birth"  />
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->



               <!--begin::Input group-->
               <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Age</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <input type="text" name="age1" id="age1"  onkeyup="showage()" class="form-control form-control-lg form-control-solid" placeholder="Age"  />
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->

             <!--begin::Input group-->
             <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Place of Birth</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <input type="text" name="placeofbirth" id="placeofbirth" class="form-control form-control-lg form-control-solid" placeholder="Birth Place"  />
                </div>
                <!--end::Col-->
            </div>
         
              <!--begin::Input group-->
              <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Nationality</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <input type="text" name="nationality" id="nationality" class="form-control form-control-lg form-control-solid" placeholder="Nationality"  />
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->

            <!--begin::Input group-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">State of Origin</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <select  class="form-control form-control-lg form-control-solid" id="state" data-rel="chosen" name="state">
                        <option value="" selected>Select State </option>
                    </select>

                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->


              <!--begin::Input group-->
              <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Local Goverment</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <select  class="form-control form-control-lg form-control-solid" id="local" data-rel="chosen" name="local">
                        <option value="" selected>Select State </option>
                    </select>
                    </select>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->

            <script language="javascript">
                populateCountries("state", "local");
            </script>

                <!--begin::Input group-->
                <div class="row mb-6">
                    <!--begin::Label-->
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Religion</label>
                    <!--end::Label-->

                    <!--begin::Col-->
                    <div class="col-lg-8 fv-row">

                        <select  class="form-control form-control-lg form-control-solid" id="religion" data-rel="chosen" name="religion">
                            <option value="" selected>Select Religion </option>
                            <option value="Christianity">Christianity</option>
                            <option value="Islam">Islam</option>
                            <option value="others">Others</option>
                        </select>

                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Input group-->


              <!--begin::Input group-->
              <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Last School Attended</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <input type="text" name="last_school" id="last_school" class="form-control form-control-lg form-control-solid" placeholder="Last School Attended"  />
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->


              <!--begin::Input group-->
              <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Last Class</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <input type="text"  name="last_class" id="last_class" class="form-control form-control-lg form-control-solid" placeholder="Last Class"  />
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->




        </div>
        <!--end::Card body-->

        {{-- <!--begin::Actions-->
        <div class="card-footer d-flex justify-content-end py-6 px-9">
            <button type="reset" class="btn btn-light btn-active-light-primary me-2">Discard</button>
            <button type="submit" class="btn btn-primary" id="kt_account_profile_details_submit">Save Changes</button>
        </div>
        <!--end::Actions-->
    </form>
    <!--end::Form--> --}}
</div>
<!--end::Content-->
</div>
<!--end::Basic info-->
<!--begin::Sign-in Method-->
<div class="card  mb-5 mb-xl-10"   >
<!--begin::Card header-->
<div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_signin_method">
    <div class="card-title m-0">
        <h3 class="fw-bold m-0">Class Information</h3>
    </div>
</div>
<!--end::Card header-->

<!--begin::Content-->
<div id="kt_account_settings_signin_method" class="collapse show">
    <!--begin::Card body-->
    <div class="card-body border-top p-9">
        <!--begin::Email Address-->
        <div class="d-flex flex-wrap align-items-center">
            <!--begin::Label-->
            <div id="kt_signin_email">
                <div class="fs-6 fw-bold mb-1">Provide Class Information</div>
                {{-- <div class="fw-semibold text-gray-600">{{ $user->email }}</div> --}}
            </div>
            <!--end::Label-->

            <!--begin::Edit-->
            <div id="kt_signin_email_edit" class="flex-row-fluid d-none">
                <!--begin::Form-->

                    <!--begin::Input group-->
                <div class="row mb-6">
                    <!--begin::Label-->
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Select Class</label>
                    <!--end::Label-->

                    <!--begin::Col-->
                    <div class="col-lg-8 fv-row">

                        <select  class="form-control form-control-lg form-control-solid" name ="schoolclassid" id="schoolclassid" required>
                            <option value="">Select Class</option>
                            @foreach ($schoolclass as $class => $name )
                            <option value="{{$name->id}}">{{ $name->schoolclass }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $name->arm}} </option>
                            @endforeach
                        </select>

                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Input group-->


                 <!--begin::Input group-->
                 <div class="row mb-6">
                    <!--begin::Label-->
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Select Term</label>
                    <!--end::Label-->

                    <!--begin::Col-->
                    <div class="col-lg-8 fv-row">

                        <select  class="form-control form-control-lg form-control-solid" name ="termid" id="termid" required>
                            <option value="">Select Term </option>
                            @foreach ($schoolterm as $term => $name )
                                <option value="{{$name->id}}">{{ $name->term}}</option>
                            @endforeach
                        </select>

                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Input group-->



                 <!--begin::Input group-->
                 <div class="row mb-6">
                    <!--begin::Label-->
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Select Current Session</label>
                    <!--end::Label-->

                    <!--begin::Col-->
                    <div class="col-lg-8 fv-row">

                        <select  class="form-control form-control-lg form-control-solid" name ="sessionid" id="sessionid" required>
                            <option value="">Select Session </option>
                            @foreach ($schoolsession as $schoolsession => $name )
                                <option value="{{$name->id}}">{{ $name->session}}</option>
                            @endforeach
                        </select>

                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Input group-->
                 <!--begin::Input group-->
             <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">
                   Student Status
                </label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <!--begin::Options-->
                    <div class="d-flex align-items-center mt-3">
                        <!--begin::Option-->
                        <label class="form-check form-check-custom form-check-inline form-check-solid me-5">
                            <input class="form-check-input" name="statusId"  type="radio" value="1" required />
                            <span class="fw-semibold ps-2 fs-6">
                               Old student
                            </span>
                        </label>
                        <!--end::Option-->

                        <!--begin::Option-->
                        <label class="form-check form-check-custom form-check-inline form-check-solid">
                            <input class="form-check-input" name="statusId"  type="radio" value="2" required />
                            <span class="fw-semibold ps-2 fs-6">
                               New Student
                            </span>
                        </label>
                        <!--end::Option-->
                    </div>
                    <!--end::Options-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->


                    <div class="d-flex">
                        <button type="reset" class="btn btn-light btn-active-light-primary me-2">Discard</button>
                        <button type="submit" class="btn btn-primary" id="kt_account_profile_details_submit">Save Record</button>
                    </div>
                </form>
                <!--end::Form-->
            </div>
            <!--end::Edit-->

            <!--begin::Action-->
            <div id="kt_signin_email_button" class="ms-auto">
                <button class="btn btn-light btn-active-light-primary">Click Here</button>
            </div>
            <!--end::Action-->
        </div>
        <!--end::Email Address-->

        <!--begin::Separator-->
        <div class="separator separator-dashed my-6"></div>
        <!--end::Separator-->
{{--
        <!--begin::Password-->
        <div class="d-flex flex-wrap align-items-center mb-10">
            <!--begin::Label-->
            <div id="kt_signin_password">
                <div class="fs-6 fw-bold mb-1">Password</div>
                <div class="fw-semibold text-gray-600">************</div>
            </div>
            <!--end::Label-->

            <!--begin::Edit-->
            <div id="kt_signin_password_edit" class="flex-row-fluid d-none">
                <!--begin::Form-->
                <form id="kt_signin_change_password" class="form" novalidate="novalidate" method="POST">
                    @csrf
                    <input type="hidden" name="pid" id="pid" value="{{ Auth::user()->id}}">
                    <div class="row mb-1">
                        <div class="col-lg-4">
                            <div class="fv-row mb-0">
                                <label for="currentpassword" class="form-label fs-6 fw-bold mb-3">Current Password</label>
                                <input type="password" class="form-control form-control-lg form-control-solid "
                                name="currentpassword" id="currentpassword"  />
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="fv-row mb-0">
                                <label for="newpassword" class="form-label fs-6 fw-bold mb-3">New Password</label>
                                <input type="password" class="form-control form-control-lg form-control-solid "
                                 name="newpassword" id="newpassword" />
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="fv-row mb-0">
                                <label for="confirmpassword" class="form-label fs-6 fw-bold mb-3">Confirm New Password</label>
                                <input type="password" class="form-control form-control-lg form-control-solid "
                                 name="confirmpassword" id="confirmpassword" />
                            </div>
                        </div>
                    </div>

                    <div class="form-text mb-5">Password must be at least 8 character and contain symbols</div>

                    <div class="d-flex">
                        <button id="kt_password_submit" type="button" class="btn btn-primary me-2 px-6">Update Password</button>
                        <button id="kt_password_cancel" type="button" class="btn btn-color-gray-400 btn-active-light-primary px-6">Cancel</button>
                    </div>
                </form>
                <!--end::Form-->
            </div>
            <!--end::Edit-->

            <!--begin::Action-->
            <div id="kt_signin_password_button" class="ms-auto">
                <button class="btn btn-light btn-active-light-primary">Reset Password</button>
            </div>
            <!--end::Action-->
        </div>
        <!--end::Password--> --}}


{{-- <!--begin::Notice-->
<div class="notice d-flex bg-light-primary rounded border-primary border border-dashed  p-6">
        <!--begin::Icon-->
    <i class="ki-duotone ki-shield-tick fs-2tx text-primary me-4"><span class="path1"></span><span class="path2"></span></i>        <!--end::Icon-->

<!--begin::Wrapper-->
<div class="d-flex flex-stack flex-grow-1 flex-wrap flex-md-nowrap">
                <!--begin::Content-->
        <div class="mb-3 mb-md-0 fw-semibold">
                                <h4 class="text-gray-900 fw-bold">Secure Your Account</h4>

                                <div class="fs-6 text-gray-700 pe-7">Two-factor authentication adds an extra layer of security to your account. To log in, in addition you'll need to provide a 6 digit code</div>
                        </div>
        <!--end::Content-->

                <!--begin::Action-->
        <a href="#" class="btn btn-primary px-6 align-self-center text-nowrap"  data-bs-toggle="modal" data-bs-target="#kt_modal_two_factor_authentication" >
            Enable            </a>
        <!--end::Action-->
        </div>
<!--end::Wrapper-->
</div>
<!--end::Notice--> --}}
    </div>
    <!--end::Card body-->
</div>
<!--end::Content-->
</div>
<!--end::Sign-in Method-->



{{-- <!--begin::Deactivate Account-->
<div class="card  "   >

    <!--begin::Card header-->
    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_deactivate" aria-expanded="true" aria-controls="kt_account_deactivate">
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">Deactivate Account</h3>
        </div>
    </div>
    <!--end::Card header-->

    <!--begin::Content-->
    <div id="kt_account_settings_deactivate" class="collapse show">
        <!--begin::Form-->
        <form id="kt_account_deactivate_form" class="form">

            <!--begin::Card body-->
            <div class="card-body border-top p-9">

        <!--begin::Notice-->
        <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed mb-9 p-6">
                    <!--begin::Icon-->
                <i class="ki-duotone ki-information fs-2tx text-warning me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>        <!--end::Icon-->

            <!--begin::Wrapper-->
            <div class="d-flex flex-stack flex-grow-1 ">
                            <!--begin::Content-->
                    <div class=" fw-semibold">
                                            <h4 class="text-gray-900 fw-bold">You Are Deactivating Your Account</h4>

                                            <div class="fs-6 text-gray-700 ">For extra security, this requires you to confirm your email or phone number when you reset yousignr password. <br/><a class="fw-bold" href="#">Learn more</a></div>
                                    </div>
                    <!--end::Content-->

                    </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Notice-->

                    <!--begin::Form input row-->
                    <div class="form-check form-check-solid fv-row">
                        <input name="deactivate" class="form-check-input" type="checkbox" value="" id="deactivate" />
                        <label class="form-check-label fw-semibold ps-2 fs-6" for="deactivate">I confirm my account deactivation</label>
                    </div>
                    <!--end::Form input row-->
                </div>
                <!--end::Card body-->

                <!--begin::Card footer-->
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button id="kt_account_deactivate_account_submit" type="submit" class="btn btn-danger fw-semibold">Deactivate Account</button>
                </div>
                <!--end::Card footer-->

            </form>
            <!--end::Form-->
        </div>
        <!--end::Content-->
</div>
<!--end::Deactivate Account--> --}}
</div>
    <!--end::Content container-->
</div>
<!--end::Content-->
            </div>
            <!--end::Content wrapper-->

@endsection

