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
                               Analysis Book for Term and Session
                                        </h1>
                                <!--end::Title-->


                                    <!--begin::Breadcrumb-->
                                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                                                        <!--begin::Item-->
                                                        <li class="breadcrumb-item text-muted">
                                                            <a href="{{ route('analysis.index') }}" class="text-muted text-hover-primary">School BIlls for Term and Sessions</a>
                                                                        </li>
                                                            <!--end::Item-->
                                                                <!--begin::Item-->
                                                <li class="breadcrumb-item">
                                                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                                                </li>
                                                <!--end::Item-->

                                                        <!--begin::Item-->
                                                                <li class="breadcrumb-item text-muted">Analysis for Term and Session</li>
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



        <!--end::Card title-->
                    <div id="kt_app_content" class="app-content  flex-column-fluid " >
                        <!--begin::Content container-->
                        <div id="kt_app_content_container" class="app-container  container-xxl ">

                   <!--begin::Toolbar-->
                        <div class="d-flex flex-wrap flex-stack my-5">
                            <!--begin::Heading-->
                            <h2 class="fs-2 fw-semibold my-2" style="color: rgb(29, 37, 195)">
                              ANALYSIS BOOK FOR CLASS {{ $schoolclass[0]->schoolclass }} {{ $schoolclass[0]->schoolarm }}, {{ $schoolterm[0]->schoolterm }} {{ $schoolsession[0]->schoolsession }} ACADEMIC SESSION

                            </h2>
                            <!--end::Heading-->

                        </div>
                    <!--end::Toolbar-->



        <!--begin::Card-->
    <div class="card">


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




                          <!--begin::Card toolbar-->
                          <div class="card-toolbar">
                            <!--begin::Search-->
                            <div class="d-flex align-items-center position-relative my-1"  data-kt-view-roles-table-toolbar="base">
                                <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6"><span class="path1"></span><span class="path2"></span></i>
                                  <input type="text" data-kt-roles-table-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Search ..." />
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
    <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0 table " id="kt_roles_view_table">
              <thead>
                    <tr class="text-start  fw-bold fs-7 text-uppercase gs-0" >
                        <th class="w-10px pe-2">
                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_roles_view_table .form-check-input" value="1" />
                            </div>
                        </th>
                        <th class="min-w-125px">Student Name</th>
                        <th class="min-w-125px">Admission Number</th>
                        @foreach ($student_bill_info as $bill)
                        <th class="min-w-125px">{{ $bill->title }}</th>
                        @endforeach
                    </tr>

               </thead>
                <tbody class="fw-semibold text-gray-600">
                        @php
                        $i = 0
                        @endphp
                        @foreach ($student as $stu)
                            <tr>
                                <td>
                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" value="1" />
                                    </div>
                                </td>

                                {{-- Display student name --}}
                                <td class="d-flex align-items-center">
                                                <!--begin:: Avatar -->
                                                        <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                                            <a href="#">
                                                                            <div class="symbol-label">
                                                            <?php $image = "";?>
                                                            <?php
                                                            if ($stu->picture  == NULL || !isset($stu->picture ) || $stu->picture =="" ){
                                                                    $image =  'unnamed.png';
                                                            }else {
                                                                $image =   $stu->picture;
                                                            }
                                                            ?>
                                                                        <img src="{{ Storage::url('images/studentavatar/'.$image)}}" alt="{{ $stu->staffname }}" class="w-100" />
                                                                    </div>
                                                                                        </a>
                                                        </div>
                                                        <!--end::Avatar-->
                                                        <!--begin::User details-->
                                                        <div class="d-flex flex-column">
                                                            <a href="#" class="text-gray-800 text-hover-primary mb-1">{{ $stu->firstname }} {{ $stu->lastname }} </a>

                                                        </div>
                                                        <!--begin::User details-->
                                        </td>

                                        <td>{{ $stu->admissionno }}</td>

                                            {{-- Display each bill and payment details for the student --}}
                                             @foreach ($student_bill_info as $bill)
                                                        @php
                                                            // Initialize variables to handle payments
                                                            $paymentFound = false;
                                                            $amountPaid = 0;
                                                            $balance = 0;
                                                        @endphp

                                                    {{-- Check if the student made a payment for this specific bill --}}
                                                    @foreach ($studentpaymentbillbook as $paymentBook)
                                                        @if (
                                                            (int)$paymentBook->school_bill_id === (int)$bill->schoolbillid
                                                            &&
                                                            (int)$paymentBook->student_id === (int)$stu->stid
                                                            // &&
                                                            // (int)$paymentBook->class_id === (int)$bill->class_id
                                                            // &&
                                                            // (int)$paymentBook->term_id === (int)$bill->termid_id
                                                            //  &&
                                                            // (int)$paymentBook->sessionl_id ===(int)$bill->session_id
                                                            )
                                                                    @php


                                                                        $paymentFound = true;
                                                                        $amountPaid = (int)$paymentBook->amount_paid;
                                                                        $balance = $paymentBook->amount_owed;
                                                                    @endphp
                                                                @break
                                                        @endif
                                                    @endforeach

                                                        {{-- Display amount paid or "Not Paid" --}}
                                                        @if ($paymentFound)

                                                            <td style="color: green">
                                                                ₦ {{ number_format($amountPaid) }}
                                                                <br>
                                                                <small style="color: rgb(77, 22, 165)">Outstanding: ₦ {{ number_format($balance) }}</small>
                                                            </td>
                                                        @else
                                                            <td style="color: rgb(235, 61, 27)">Not Paid</td>
                                                        @endif

                                                        @php
                                                        // Accumulate totals for each bill column
                                                        if (!isset($totalBill[$bill->schoolbillid])) {
                                                            $totalBill[$bill->schoolbillid] = 0;
                                                            $totalBillBalance[$bill->schoolbillid] = 0;
                                                        }
                                                        $totalBill[$bill->schoolbillid] += $amountPaid;
                                                        $totalBillBalance[$bill->schoolbillid] += $balance;
                                                    @endphp
                                        @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="text-start  fw-bold fs-20 text-uppercase gs-0" >
                            <th class="min-w-125px"></th>
                            <th class="min-w-125px"></th>

                            <th class="min-w-125px"></th>
                            @foreach ($student_bill_info as $bill)
                                <th>₦ {{ number_format($totalBill[$bill->schoolbillid] ?? 0) }}
                                    <br>
                                    <small style="color: rgb(77, 22, 165)">Outstanding: ₦ {{ number_format($totalBillBalance[$bill->schoolbillid] ?? 0) }} </small>
                                </th>
                            @endforeach
                        </tr>
                    </tfoot>

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
