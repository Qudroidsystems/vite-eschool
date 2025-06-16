
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
       <p style="color: green"> Student Payments infomation and Payment Status</p>
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
        <div id="kt_app_content_container" class="app-container  ">

            <?php

                use App\Models\SchoolBillTermSession;
                use App\Models\Schoolclass;
                use App\Models\Schoolsession;
                use App\Models\Schoolterm;
                use App\Models\Student;
                use App\Models\StudentBillInvoice;
                use App\Models\StudentBillPayment;
                use App\Models\StudentBillPaymentBook;
                use App\Models\StudentBillPaymentRecord;
                use Carbon\Carbon;
                use Illuminate\Http\Request;
                use Illuminate\Support\Facades\Auth;
                use Illuminate\Support\Facades\DB;

                $subsegstatus="";
                foreach($studentdata as $s) {
                $fn =   $s->firstname;
                $ln =  $s->lastname;
                $ad = $s->admissionNo;

                }


         ?>

             <!--begin::Navbar-->
 <div class="card mb-5 mb-xl-10">
    <div class="card-body pt-9 pb-0">
        <!--begin::Details-->
        <div class="d-flex flex-wrap flex-sm-nowrap">
            <!--begin: Pic-->
            <div class="me-7 mb-4">

                <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                    <?php $image = "";?>
                    <?php
                    if ($s->avatar == NULL || $s->avatar =="" || !isset($s->avatar) ){
                           $image =  'unnamed.png';
                    }else {
                       $image =  $s->avatar;
                    }
                    ?>
                    <img src="{{Storage::url('images/studentavatar/'.$image)}}" alt="image" />
                    <div class="position-absolute translate-middle bottom-0 start-100 mb-6 bg-success rounded-circle border border-4 border-body h-20px w-20px"></div>
                </div>

            </div>
            <!--end::Pic-->

            <!--begin::Info-->
            <div class="flex-grow-1">
                <!--begin::Title-->
                <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                    <!--begin::User-->
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
                                {{ $s->schoolclass }}  {{ $s->arm }}
                            </a>

                        </div>
                        <!--end::Info-->
                    </div>
                    <!--end::User-->


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
                                    <div class="fs-2 fw-bold" data-kt-countup="true" data-kt-countup-value="{{ $student_bill_info_count }}" data-kt-countup-prefix="">0</div>
                                </div>
                                <!--end::Number-->

                                <!--begin::Label-->
                                <div class="fw-semibold fs-6 text-gray-400">All School Bills</div>
                                <!--end::Label-->
                            </div>
                            <!--end::Stat-->

                            <!--begin::Stat-->
                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                <!--begin::Number-->
                                <div class="d-flex align-items-center">
                                    <i class="ki-duotone ki-arrow-up fs-3 text-success me-2"><span class="path1"></span><span class="path2"></span></i>
                                    <div class="fs-2 fw-bold" data-kt-countup="true" data-kt-countup-value="">0</div>
                                </div>
                                <!--end::Number-->

                                <!--begin::Label-->
                                <div class="fw-semibold fs-6 text-gray-400"> Complete Payments</div>
                                <!--end::Label-->
                            </div>
                            <!--end::Stat-->

                              <!--begin::Stat-->
                              <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                <!--begin::Number-->
                                <div class="d-flex align-items-center">
                                    <i class="ki-duotone ki-arrow-up fs-3 text-success me-2"><span class="path1"></span><span class="path2"></span></i>
                                    <div class="fs-2 fw-bold" data-kt-countup="true" data-kt-countup-value="">0</div>
                                </div>
                                <!--end::Number-->

                                <!--begin::Label-->
                                <div class="fw-semibold fs-6 text-gray-400"> Uncomplete Payments</div>
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
    <a href="{{ route('schoolpayment.index') }}" class="btn btn-primary">
                << Back
    </a>
    <!--end::Button-->

</div>

<!--begin::Basic info-->
<div class="card mb-5 mb-xl-10">
    <!--begin::Card header-->
    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
        <!--begin::Card title-->
        <div class="card-title m-0">
            <h3 class="fw-bold m-0"><p style="color: rgb(109, 109, 212)"> School Bills FOR &nbsp;&nbsp;{{  ucwords(strtolower($fn)) }} {{ucwords(strtolower($ln)) }} ({{ $ad }}) for {{ $schoolterm }} | {{ $schoolsession }} Academic Session</p></h3>
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

        <!--begin::Payment methods-->
<div class="card mb-5 mb-xl-10">
    <!--begin::Card header-->
    <div class="card-header card-header-stretch pb-0">
        <!--begin::Title-->
        <div class="card-title">
            <h3 class="m-0">School BIlls</h3>
        </div>
        <!--end::Title-->

        <!--begin::Toolbar-->
        <div class="card-toolbar m-0">
            <!--begin::Tab nav-->
            <ul class="nav nav-stretch nav-line-tabs border-transparent" role="tablist">
                <!--begin::Tab item-->
                <li class="nav-item" role="presentation">
                    <a id="kt_billing_creditcard_tab" class="nav-link fs-5 fw-bold me-5 active" data-bs-toggle="tab" role="tab" href="#kt_billing_creditcard">
                        School Bills / Payment
                    </a>
                </li>
                <!--end::Tab item-->

                <!--begin::Tab item-->
                <li class="nav-item" role="presentation">
                    <a id="kt_billing_paypal_tab" class="nav-link fs-5 fw-bold" data-bs-toggle="tab" role="tab" href="#kt_billing_paypal">
                      Payments Orders
                    </a>
                </li>
                <!--end::Tab item-->
            </ul>
            <!--end::Tab nav-->
        </div>
        <!--end::Toolbar-->
    </div>
    <!--end::Card header-->

    <!--begin::Tab content-->
    <div id="kt_billing_payment_tab_content" class="card-body tab-content">
        <!--begin::Tab panel-->
        <div id="kt_billing_creditcard" class="tab-pane fade show active" role="tabpanel"">
            {{-- <!--begin::Title-->
            <h3 class="mb-5">My Cards</h3>
            <!--end::Title--> --}}

            <!--begin::Row-->
            <div class="row gx-9 gy-6">
                @php
                    $paymentFound = false;
                    $amountPaid = 0;
                    $balance = 0;
               @endphp
                @foreach ($student_bill_info as $sc)

                    <!--begin::Col-->
                    <div class="col-xl-6" data-kt-billing-element="card">
                        <!--begin::Card-->
                        <div class="card card-dashed h-xl-100 flex-row flex-stack flex-wrap p-6">
                            <!--begin::Info-->
                            @php
                                $actualAmount1 =    $sc->amount;
                                $paymentFound1 = 0;
                                $amountPaid1 = 0;
                                $balance1 = 0;
                            @endphp

                        @foreach ($studentpaymentbillbook as $paymentBook)

                            @if ((int)$paymentBook->school_bill_id === (int)$sc->schoolbillid)
                                @php

                                    $paymentFound1 = true;
                                    $amountPaid1 = $paymentBook->amount_paid;
                                    $balance1 = $paymentBook->amount_owed;
                                    //  echo  (float)$amountPaid1;
                                    //  echo " ";
                                    //  echo (float)$actualAmount1;
                                @endphp
                            @endif
                        @endforeach

                        @if ((float)$actualAmount1 === (float)$amountPaid1 )

                               <h5 class="badge badge-success ">Completed</h5>

                        @else

                            <h5 class="badge badge-danger ">Not Completed</h5>

                        @endif

                            <div class="d-flex flex-column py-2">

                                <!--begin::Owner-->
                                <div class="d-flex align-items-center fs-4 fw-bold mb-5">
                                    {{ $sc->title }}
                                    <span class="badge badge-light-info fs-7 ms-2">{{ $sc->description }}</span>

                                </div>
                                <!--end::Owner-->

                                <!--begin::Wrapper-->
                                <div class="d-flex align-items-center">


                                    <!--begin::Details-->
                                    <div>
                                        <div class="fs-4 fw-bold"><h2>₦ {{ number_format($sc->amount) }}</h2></div>
                                    @php
                                        $paymentFound = false;
                                        $amountPaid = 0;
                                        $balance = 0;
                                        $balance2 = null;
                                    @endphp

                                    @foreach ($studentpaymentbillbook as $paymentBook)
                                        @if ((int)$paymentBook->school_bill_id === (int)$sc->schoolbillid)

                                            @php
                                                $totalLastPayment = StudentBillPayment::where('student_bill_payment.student_id', $studentId)
                                                            ->where('student_bill_payment.class_id', $schoolclassId)
                                                            ->where('student_bill_payment.termid_id', $schooltermId)
                                                            ->where('student_bill_payment.session_id', $schoolsessionId)
                                                            ->where('student_bill_payment.school_bill_id', $sc->schoolbillid)
                                                            //->where('student_bill_payment.delete_status', 0)
                                                            ->leftjoin('student_bill_payment_record', 'student_bill_payment_record.student_bill_payment_id', '=', 'student_bill_payment.id')
                                                            ->leftjoin('school_bill', 'school_bill.id', '=', 'student_bill_payment.school_bill_id')
                                                            ->leftjoin('users', 'users.id', '=', 'student_bill_payment.generated_by')
                                                            // ->whereDate('student_bill_payment.created_at', Carbon::today()) // Filter by today's date
                                                        // ->sum('student_bill_payment_record.last_payment');
                                                            ->sum(DB::raw('CAST(student_bill_payment_record.amount_paid AS DECIMAL(10, 2))'));


                                                $paymentFound = true;
                                                $amountPaid = $totalLastPayment;


                                                if((float)$amountPaid == 0){
                                                  // echo  $balance = $sc->amount;
                                                }
                                                else{
                                                  $balance = $paymentBook->amount_owed;
                                                }

                                            @endphp
                                        @endif
                                    @endforeach

                                    @if ($paymentFound)
                                        <div class="fs-6 fw-semibold " style="color: rgb(23, 100, 67)">Amount Paid: ₦ {{ number_format($amountPaid)  }} </div>
                                        <div class="fs-6 fw-semibold " style="color: rgb(190, 32, 11)"> Outstanding: ₦ {{  number_format($balance) }}</div>
                                    @else
                                    @php
                                         if((float)$amountPaid == 0){
                                              echo   $balance = $sc->amount;
                                            }
                                            else{
                                               echo  $balance = $paymentBook->amount_owed;
                                            }

                                    @endphp

                                        <div class="fs-6 fw-semibold ">Amount Paid: ₦ {{ number_format($amountPaid)  }} </div>
                                        <div class="fs-6 fw-semibold " style="color: rgb(190, 32, 11)">Outstanding: ₦ {{  number_format($balance) }}</div>
                                        <div class="fs-6 fw-semibold ">No payment records found </div>

                                    @endif

                                    </div>
                                    <!--end::Details-->
                                </div>
                                <!--end::Wrapper-->
                            </div>
                            <!--end::Info-->



                        @if ((float)$actualAmount1 === (float)$amountPaid1 )

                          <!--begin::Actions-->
                          <div class="d-flex align-items-center py-2">
                            {{-- <button class="btn btn-sm btn-light btn-primary" disabled
                                 data-student_id="{{ $s->id }}"
                                 data-amount="{{ number_format($sc->amount) }}"
                                 data-amount_actual="{{ $sc->amount }}"
                                 data-amount_paid="{{ number_format($amountPaid) }}"
                                 data-balance="{{ number_format($balance) }}"
                                 data-school_bill_id="{{ $sc->schoolbillid }}"
                                 data-class_id="{{ $s->schoolclassid }}"
                                 data-term_id="{{ $s->termid }}"
                                 data-session_id="{{ $s->sessionid }}"
                                 data-bs-toggle="modal"
                                 data-bs-target="#kt_modal_new_card" >Make Payment</button> --}}
                        </div>
                        <!--end::Actions-->
                        @else
                            <!--begin::Actions-->
                            <div class="d-flex align-items-center py-2">
                                <button class="btn btn-sm btn-light btn-primary"
                                    data-student_id="{{ $s->id }}"
                                    data-amount="{{ number_format($sc->amount) }}"
                                    data-amount_actual="{{ $sc->amount }}"
                                    data-amount_paid="{{ number_format($amountPaid) }}"
                                    data-balance="{{ number_format($balance) }}"
                                    data-school_bill_id="{{ $sc->schoolbillid }}"
                                    data-class_id="{{ $s->schoolclassid }}"
                                    data-term_id="{{ $schooltermId }}"
                                    data-session_id="{{ $schoolsessionId }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#kt_modal_new_card" >Make Payment</button>
                            </div>
                            <!--end::Actions-->
                        @endif

                        </div>
                        <!--end::Card-->
                    </div>
                    <!--end::Col-->
                @endforeach



            </div>
            <!--end::Row-->

        </div>
        <!--end::Tab panel-->

        <!--begin::Tab panel-->
        <div id="kt_billing_paypal" class="tab-pane fade" role="tabpanel" aria-labelledby="kt_billing_paypal_tab">

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
                                        <th class="min-w-125px" style="color: green">School Bill</th>
                                        <th class="min-w-125px" style="color: green">Description</th>
                                        <th class="min-w-125px" style="color: green">Bill Amount</th>
                                        {{-- <th class="min-w-80px" style="color: green">Total Amount Paid </th> --}}
                                        <th class="min-w-80px" style="color: green">Amount Paid Today</th>
                                        <th class="min-w-125px" style="color: green">Outstanding</th>
                                        <th class="min-w-125px" style="color: green">Recieved By</th>
                                        <th class="min-w-125px" style="color: green">Date - Time</th>
                                        <th class="min-w-125px" style="color: green">Payment Method</th>
                                        <th class="min-w-125px" style="color: green">Payment Status</th>
                                        <th class="min-w-125px" style="color: green">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    @php
                                    $i = 0
                                    @endphp
                                    @foreach ($studentpaymentbill as $sp)
                                        <tr>
                                            <td>
                                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                    <input class="form-check-input" type="checkbox" value="1" />
                                                </div>
                                            </td>
                                            <td>  <input type="hidden" id="tid"  value="{{ $sp->paymentid }}" />{{ ++$i }}</td>
                                            <td class="d-flex align-items-center">{{ $sp->title }}</td>
                                            <td > {{ $sp->description }}</td>
                                            <td > ₦ {{ number_format($sp->billAmount) }} </td>
                                            {{-- <td > ₦ {{ number_format($sp->totalAmountPaid) }} </td> --}}
                                            <td style="color: rgb(53, 12, 119)"> ₦ {{ number_format(intval($sp->lastPayment) ?? 0) }} </td>
                                            <td style="color: rgb(190, 32, 11)"> ₦ {{ number_format($sp->balance) }}</td>
                                            <td > {{ $sp->recievedBy }}</td>
                                            <td > {{ $sp->recievedDate }}</td>
                                            <td > {{ $sp->paymentMethod }}</td>
                                            <td > {{ $sp->paymentStatus }}</td>
                                            <td >
                                                {{-- @can('school_arm-delete') --}}
                                                <div class="menu-item px-3" >
                                                    {{-- <form method="post" class="menu-link px-3" data-kt-roles-table-filter="delete_row" data-route="">
                                                      @csrf
                                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                    </form> --}}
                                                    <a
                                                    href="javascript:void(0)"
                                                    id="show-user"
                                                    data-kt-roles-table-filter="delete_row"
                                                    data-url="{{ route('schoolpayment.deletestudentpayment', ['paymentid'=> $sp->paymentid]) }}"

                                                    class="btn btn-danger btn-sm">Delete</a>
                                                </div>
                                                <!--end::Menu item-->
                                                {{-- @endcan --}}
                                            </td>
                                        </tr>

                                    @endforeach
                                </tbody>
                            </table>
                    <!--end::Table-->
                </div>

                <!--end::Card body-->
                 <!--begin::Card title-->


         @if($studentpaymentbill->isNotEmpty())
          <a href="#"
            class="btn btn-primary"
            style="float: right;"
            data-toggle="tooltip"
            onclick="confirmAndProceed('{{ route('schoolpayment.invoice', [ $s->id, 'schoolclassid' => $s->schoolclassid, 'termid' => $schooltermId, 'sessionid' => $schoolsessionId]) }}')">
            <i class="fe fe-download mr-2"></i>Generate Invoice
         </a>
         @else

         <a href="#"
         class="btn btn-primary"
         style="float: right; pointer-events: none; color: gray; text-decoration: none;"
         data-toggle="tooltip"
         {{-- onclick="confirmAndProceed('{{ route('schoolpayment.invoice', [ $s->id, 'schoolclassid' => $s->schoolclassid, 'termid' => $s->termid, 'sessionid' => $s->sessionid]) }}')"> --}}
         <i class="fe fe-download mr-2"></i>Generate Invoice
      </a>
         @endif


        <!--end::Card title-->
                    </div>
                    <!--end::Tab panel-->
                </div>
                <!--end::Tab content-->
            </div>
            <!--end::Payment methods-->


    </div>
    <!--end::Content-->
</div>
<!--end::Basic info-->

                <!--end::Modal - Offer A Deal--><!--begin::Modal - New Card-->
                <div class="modal fade" id="kt_modal_new_card" tabindex="-1" aria-hidden="true">
                    <!--begin::Modal dialog-->
                    <div class="modal-dialog modal-dialog-centered mw-650px">
                        <!--begin::Modal content-->
                        <div class="modal-content">
                            <!--begin::Modal header-->
                            <div class="modal-header">
                                <!--begin::Modal title-->
                                <h2>Payment Screen</h2>
                                <!--end::Modal title-->

                                <!--begin::Close-->
                                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                            </div>
                                <!--end::Close-->
                            </div>
                            <!--end::Modal header-->

                                        <!--begin::Modal body-->
                                        <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">


                                                            <!--begin::Col-->
                                                    <div class="col-xl-12" data-kt-billing-element="card">
                                                        <!--begin::Card-->
                                                        <div class="card card-dashed h-xl-100 flex-row flex-stack flex-wrap p-6">
                                                            <!--begin::Info-->

                                                            <div class="d-flex flex-column py-2">

                                                                <!--begin::Owner-->
                                                                <div class="d-flex align-items-center fs-4 fw-bold mb-5">
                                                                    {{ $s->title }}
                                                                </div>
                                                                <span class="badge badge-light-info fs-7 ms-2">{{ $s->description }}</span>
                                                                <!--end::Owner-->

                                                                <!--begin::Wrapper-->
                                                                <div class="d-flex align-items-center">


                                                                    <!--begin::Details-->
                                                                    <div>
                                                                        <div class="fs-4 fw-bold" id="amount_d"><h2></h2></div>
                                                                        <div class="fs-6 fw-semibold" id="amount_paid_d">  </div>
                                                                        <div class="fs-6 fw-semibold" style="color: rgb(190, 32, 11)" id="balance_d"> </div>
                                                                    </div>
                                                                    <!--end::Details-->
                                                                </div>
                                                                <!--end::Wrapper-->
                                                            </div>
                                                            <!--end::Info-->


                                                            <!--begin::Actions-->
                                                            <div class="d-flex align-items-center py-2">
                                                                <!--begin: Pic-->
                                                            <div class="me-7 mb-4">

                                                                <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                                                                    <?php $image = "";?>
                                                                    <?php
                                                                    if ($s->avatar == NULL || $s->avatar =="" || !isset($s->avatar) ){
                                                                        $image =  'unnamed.png';
                                                                    }else {
                                                                    $image =  $s->avatar;
                                                                    }
                                                                    ?>
                                                                    <img src="{{Storage::url('images/studentavatar/'.$image)}}" alt="image" />
                                                                    <div class="position-absolute translate-middle bottom-0 start-100 mb-6 bg-success rounded-circle border border-4 border-body h-20px w-20px"></div>
                                                                </div>

                                                            </div>
                                                            <!--end::Pic-->
                                                            </div>
                                                            <!--end::Actions-->
                                                        </div>
                                                        <!--end::Card-->
                                                    </div>
                                                    <!--end::Col-->

                                                    <!--begin::Form-->
                                                    <form id="kt_modal_new_card_form" class="form" action="{{ route('schoolpayment.store') }}" method="POST">
                                                            @csrf

                                                                <input type="hidden"   id="actual_amount"    name="actualAmount">
                                                                <input type="hidden"   id="balance2"         name="balance2" >
                                                                <input type="hidden"   id="student_id"       name="student_id" >
                                                                <input type="hidden"   id="class_id"         name="class_id" >
                                                                <input type="hidden"   id="term_id"          name="term_id" >
                                                                <input type="hidden"   id="session_id"       name="session_id" >
                                                                <input type="hidden"   id="school_bill_id"   name="school_bill_id">
                                                                <input type="hidden"   id="last_amount_paid" name="last_amount_paid">
                                                                <input type="hidden"   id="outstanding"      name="outstanding">



                                                                <!--begin::Input group-->
                                                                <div class="d-flex flex-column mb-7 fv-row">
                                                                    <!--begin::Label-->
                                                                    <label class="required fs-6 fw-semibold form-label mb-2">Enter Amount </label>
                                                                    <!--end::Label-->

                                                                    <!--begin::Input wrapper-->
                                                                    <div class="position-relative">
                                                                        <!--begin::Input-->
                                                                        <input type="text" class="form-control form-control-solid" id="payment_amount" name="payment_amount" placeholder="Enter Amount "   />
                                                                        <input type="hidden" class="form-control form-control-solid" id="payment_amount2" name="payment_amount2" placeholder="Enter Amount "  />
                                                                        <!--end::Input-->

                                                                    </div>
                                                                    <!--end::Input wrapper-->
                                                                </div>
                                                                <!--end::Input group-->


                                                                <!--begin::Input group-->
                                                                <div class="d-flex flex-column mb-7 fv-row">

                                                                <!--begin::Input wrapper-->
                                                                <div class="position-relative">


                                                                    <select name="payment_method2" data-control="select2" data-placeholder="Select an option" data-hide-search="true" class="form-select form-select-solid form-select-lg fw-semibold fs-6 text-gray-700">
                                                                        <option value="">Select Payment Method</option>
                                                                        <option value="Bank Deposit"> Bank Deposit / Bank Teller </option>
                                                                        <option value="School POS"> School POS/Cash</option>
                                                                    </select>

                                                                </div>
                                                                <!--end::Input wrapper-->
                                                            </div>
                                                            <!--end::Input group-->

                                                            <!--begin::Actions-->
                                                                <div class="text-center pt-15">
                                                                    <button type="reset" id="kt_modal_new_card_cancel" class="btn btn-light me-3">
                                                                        Discard
                                                                    </button>

                                                                    <button type="submit" onclick="addItem()" id="kt_modal_new_card_submit" class="btn btn-primary">
                                                                        <span class="indicator-label">
                                                                        Make Payment
                                                                        </span>
                                                                        <span class="indicator-progress">
                                                                            Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                                                        </span>
                                                                    </button>
                                                                </div>
                                                                <!--end::Actions-->
                                                    </form>
                                                    <!--end::Form-->
                                    </div>
                                    <!--end::Modal body-->
                        </div>
                        <!--end::Modal content-->
                    </div>
                    <!--end::Modal dialog-->
                </div>

    </div>
            <!--end::Content container-->
        </div>
    <!--end::Content-->
                </div>
                <!--end::Content wrapper-->

                <script>

                    document.getElementById('payment_amount').addEventListener('input', function(e) {
                        let value = e.target.value;

                        // Remove any non-digit characters, except for a decimal point
                        value = value.replace(/[^0-9.]/g, '');

                        // Split the value on the decimal point, if it exists
                        const parts = value.split('.');

                        // Format the integer part with commas
                        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');

                        // Rejoin the integer and decimal parts
                        e.target.value =  parts.join('.');
                        });

                     function getFormattedNumber() {
                        const inputValue = document.getElementById('payment_amount2').value;
                        // Remove commas
                        const plainNumber = parseFloat(inputValue.replace(/,/g, ''));

                        return plainNumber;
                       }

                    // Example usage:
                    document.getElementById('kt_modal_new_card_submit').addEventListener('click', function () {
                        const amount = getFormattedNumber();
                        console.log('Amount as number:', amount);
                    });

                    document.getElementById('payment_amount').addEventListener('input', function() {
                     const value = this.value;
                        document.getElementById('payment_amount2').value = value;
                        console.log( document.getElementById('payment_amount2').value = parseFloat(value.replace(/,/g, '')));
                    });


                </script>

                    <script>
                        function confirmAndProceed(url) {
                            Swal.fire({
                                title: 'Are you sure you  want to generate an invoice for the payment orders.?',
                                text: "This action cannot be undone!",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Yes, generate it!',
                                cancelButtonText: 'Cancel'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Show loading indicator
                                    Swal.fire({
                                        title: 'Generating Invoice...',
                                        text: 'Please wait while we process your request.',
                                        allowOutsideClick: false,
                                        didOpen: () => {
                                            Swal.showLoading();
                                        }
                                    });

                                    // Redirect to the URL after a short delay
                                    setTimeout(() => {
                                        window.location.href = url;
                                    }, 1000); // Adjust delay if needed
                                }
                            });

                        }
                        </script>


    @endsection

