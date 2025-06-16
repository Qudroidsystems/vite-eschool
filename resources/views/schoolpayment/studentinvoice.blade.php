<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Invoice - Trinity School</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        body {
            background-color: #f5f8fa;
            color: #212529;
            line-height: 1.5;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        /* Typography */
        h1, h2, h3, h4 {
            color: #181C32;
            font-weight: 700;
        }
        
        .text-primary {
            color: #009ef7 !important;
        }
        
        .text-success {
            color: #50cd89 !important;
        }
        
        .text-danger {
            color: #f1416c !important;
        }
        
        .text-muted {
            color: #7E8299 !important;
        }
        
        .fw-bold {
            font-weight: 600 !important;
        }
        
        .fs-1 {
            font-size: 2rem !important;
        }
        
        .fs-2 {
            font-size: 1.75rem !important;
        }
        
        .fs-3 {
            font-size: 1.5rem !important;
        }
        
        .fs-4 {
            font-size: 1.25rem !important;
        }
        
        .fs-5 {
            font-size: 1.15rem !important;
        }
        
        .fs-6 {
            font-size: 1rem !important;
        }
        
        .fs-7 {
            font-size: 0.875rem !important;
        }
        
        /* Layout Components */
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #eff2f5;
            position: relative;
        }
        
        .ribbon {
            position: absolute;
            top: 0;
            left: 20px;
        }
        
        .ribbon-label {
            width: 40px;
            height: 40px;
            background-color: #009ef7;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom-left-radius: 5px;
            border-bottom-right-radius: 5px;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .card-footer {
            padding: 1.5rem 2rem;
            border-top: 1px solid #eff2f5;
            background-color: #f9f9f9;
            text-align: center;
        }
        
        .d-flex {
            display: flex;
        }
        
        .flex-column {
            flex-direction: column;
        }
        
        .justify-content-between {
            justify-content: space-between;
        }
        
        .justify-content-center {
            justify-content: center;
        }
        
        .justify-content-end {
            justify-content: flex-end;
        }
        
        .align-items-center {
            align-items: center;
        }
        
        .gap-2 {
            gap: 0.5rem;
        }
        
        .gap-3 {
            gap: 1rem;
        }
        
        .gap-4 {
            gap: 1.5rem;
        }
        
        .gap-5 {
            gap: 2rem;
        }
        
        .mb-0 {
            margin-bottom: 0 !important;
        }
        
        .mb-1 {
            margin-bottom: 0.25rem !important;
        }
        
        .mb-2 {
            margin-bottom: 0.5rem !important;
        }
        
        .mb-3 {
            margin-bottom: 1rem !important;
        }
        
        .mb-4 {
            margin-bottom: 1.5rem !important;
        }
        
        .mb-5 {
            margin-bottom: 2rem !important;
        }
        
        .mb-8 {
            margin-bottom: 3.5rem !important;
        }
        
        .mb-10 {
            margin-bottom: 4rem !important;
        }
        
        .mt-1 {
            margin-top: 0.25rem !important;
        }
        
        .mt-2 {
            margin-top: 0.5rem !important;
        }
        
        .mt-5 {
            margin-top: 2rem !important;
        }
        
        .mt-8 {
            margin-top: 3.5rem !important;
        }
        
        .me-2 {
            margin-right: 0.5rem !important;
        }
        
        .me-3 {
            margin-right: 1rem !important;
        }
        
        .ms-2 {
            margin-left: 0.5rem !important;
        }
        
        .ms-3 {
            margin-left: 1rem !important;
        }
        
        .p-4 {
            padding: 1.5rem !important;
        }
        
        .p-6 {
            padding: 2.5rem !important;
        }
        
        .p-7 {
            padding: 3rem !important;
        }
        
        .pt-8 {
            padding-top: 3.5rem !important;
        }
        
        .pb-5 {
            padding-bottom: 2rem !important;
        }
        
        .py-5 {
            padding-top: 2rem !important;
            padding-bottom: 2rem !important;
        }
        
        .rounded {
            border-radius: 0.475rem !important;
        }
        
        .rounded-3 {
            border-radius: 0.75rem !important;
        }
        
        .border-bottom {
            border-bottom: 1px solid #eff2f5 !important;
        }
        
        .border-top {
            border-top: 1px solid #eff2f5 !important;
        }
        
        .text-center {
            text-align: center !important;
        }
        
        .text-end {
            text-align: right !important;
        }
        
        .w-100 {
            width: 100% !important;
        }

        .h-50px {
            height: 50px !important;
        }
        
        /* Components */
        .bg-light {
            background-color: #f5f8fa !important;
        }
        
        .bg-light-primary {
            background-color: rgba(0, 158, 247, 0.1) !important;
        }
        
        .bg-light-success {
            background-color: rgba(80, 205, 137, 0.1) !important;
        }
        
        .bg-light-danger {
            background-color: rgba(241, 65, 108, 0.1) !important;
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 0.475rem;
            line-height: 1;
        }
        
        .badge-light-primary {
            background-color: rgba(0, 158, 247, 0.1);
            color: #009ef7;
        }
        
        .badge-light-success {
            background-color: rgba(80, 205, 137, 0.1);
            color: #50cd89;
        }
        
        .badge-light-info {
            background-color: rgba(116, 129, 255, 0.1);
            color: #7481ff;
        }
        
        .btn {
            display: inline-block;
            font-weight: 500;
            text-align: center;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.625rem 1rem;
            font-size: 1rem;
            border-radius: 0.475rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 0.425rem;
        }
        
        .btn-lg {
            padding: 0.825rem 1.75rem;
            font-size: 1.15rem;
            border-radius: 0.625rem;
        }
        
        .btn-primary {
            background-color: #009ef7;
            border-color: #009ef7;
            color: #fff;
        }
        
        .btn-primary:hover {
            background-color: #0095e8;
        }
        
        .btn-light-primary {
            background-color: rgba(0, 158, 247, 0.1);
            border-color: transparent;
            color: #009ef7;
        }
        
        .btn-light-primary:hover {
            background-color: rgba(0, 158, 247, 0.2);
        }
        
        .btn-success {
            background-color: #50cd89;
            border-color: #50cd89;
            color: #fff;
        }
        
        .btn-success:hover {
            background-color: #47be7d;
        }
        
        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        table {
            width: 100%;
            margin-bottom: 1rem;
            color: #181C32;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 1rem;
            vertical-align: top;
            text-align: left;
        }
        
        thead {
            background-color: #f5f8fa;
        }
        
        tbody tr:nth-child(even) {
            background-color: #fafbfc;
        }
        
        .table-striped tbody tr:nth-child(odd) {
            background-color: rgba(245, 248, 250, 0.5);
        }
        
        .table-rounded th:first-child {
            border-top-left-radius: 0.475rem;
        }
        
        .table-rounded th:last-child {
            border-top-right-radius: 0.475rem;
        }
        
        .border-gray-200 {
            border-color: #eff2f5 !important;
        }
        
        /* Student Profile */
        .symbol-circle {
            border-radius: 50%;
        }
        
        .symbol-100px {
            width: 100px;
            height: 100px;
        }
        
        .border {
            border: 1px solid #eff2f5;
        }
        
        .border-4 {
            border-width: 4px !important;
        }
        
        .border-white {
            border-color: #ffffff !important;
        }
        
        .shadow {
            box-shadow: 0 0.5rem 1.5rem 0.5rem rgba(0, 0, 0, 0.075) !important;
        }
        
        .overflow-hidden {
            overflow: hidden !important;
        }
        
        /* Grid System */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -12.5px;
            margin-left: -12.5px;
        }
        
        .col {
            flex: 1 0 0%;
            padding-right: 12.5px;
            padding-left: 12.5px;
        }
        
        .col-12 {
            flex: 0 0 auto;
            width: 100%;
            padding-right: 12.5px;
            padding-left: 12.5px;
        }
        
        .col-md-auto {
            flex: 0 0 auto;
            padding-right: 12.5px;
            padding-left: 12.5px;
        }
        
        .col-md-4 {
            flex: 0 0 auto;
            width: 33.33333333%;
            padding-right: 12.5px;
            padding-left: 12.5px;
        }
        
        .col-md-6 {
            flex: 0 0 auto;
            width: 50%;
            padding-right: 12.5px;
            padding-left: 12.5px;
        }
        
        /* Responsive */
        @media (max-width: 767.98px) {
            .col-md-4, .col-md-6 {
                width: 100%;
            }
            
            .flex-md-row {
                flex-direction: column !important;
            }
            
            .text-sm-end {
                text-align: center !important;
            }
            
            .mt-sm-0 {
                margin-top: 1.5rem !important;
            }
            
            .card-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .payment-summary-box {
                width: 100% !important;
                margin-bottom: 1rem;
            }
        }
        
        @media (min-width: 768px) {
            .flex-md-row {
                flex-direction: row !important;
            }
            
            .justify-content-md-end {
                justify-content: flex-end !important;
            }
            
            .mt-md-0 {
                margin-top: 0 !important;
            }
        }
        
        /* Print Styles */
        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }
            
            .container {
                box-shadow: none;
                max-width: 100%;
            }
            
            .btn {
                display: none;
            }
            
            .card-footer {
                display: none;
            }
            
            @page {
                margin: 0.5cm;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- begin::Header-->
        <div class="card-header">
            <div class="ribbon">
                <div class="ribbon-label">
                    <i class="fas fa-file-invoice" style="color: white; font-size: 20px;"></i>
                </div>
            </div>
            <div class="d-flex justify-content-between w-100 align-items-center">
                <h2 class="fw-bold fs-2 mb-0">Student Invoice</h2>
                <div>
                    <?php foreach($studentdata as $s) { ?>
                    <a href="<?php echo route('schoolpayment.termsession', $s->id); ?>" class="btn btn-sm btn-light-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                    <?php } ?>
                    <button type="button" class="btn btn-sm btn-primary ms-2" onclick="window.print();">
                        <i class="fas fa-print me-2"></i>Print
                    </button>
                    <a href="<?php echo url()->current(); ?>?download_pdf=1" class="btn btn-sm btn-success ms-2">
                        <i class="fas fa-download me-2"></i>Download PDF
                    </a>
                </div>
            </div>
        </div>
        <!-- end::Header-->

        <!-- begin::Body-->
        <div class="card-body">
            <!-- begin::Wrapper-->
            <div class="w-100">
                <!-- begin::School & Invoice Header-->
                <div class="d-flex flex-column flex-md-row justify-content-between mb-10 pb-5 border-bottom">
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex align-items-center gap-4">
                            <div>
                                <img alt="School Logo" src="../../../assets/media/svg/brand-logos/lloyds-of-london-logo.svg" class="h-50px" />
                            </div>
                            <div>
                                <h1 class="fw-bold text-primary fs-1 mb-0">Trinity Comprehensive</h1>
                                <h2 class="text-primary fs-4 mb-0">International School</h2>
                                <div class="text-muted">Ondo City, Ondo state</div>
                            </div>
                        </div>
                    </div>
                    <div class="text-sm-end mt-5 mt-md-0">
                        <div class="bg-light-primary rounded p-4 text-center text-sm-end">
                            <div class="text-uppercase text-muted fs-7 mb-1">Invoice Number</div>
                            <div class="fs-3 fw-bold text-primary">#<?php echo $invoiceNumber; ?></div>
                            <div class="text-muted fs-7 mt-1"><?php echo \Carbon\Carbon::now()->format('d F, Y'); ?></div>
                        </div>
                    </div>
                </div>
                <!--end::School & Invoice Header-->

                <!--begin::Student Information-->
                <div class="bg-light rounded-3 p-7 mb-10">
                    <div class="row">
                        <div class="col-12 col-md-auto">
                            <?php
                                foreach($studentdata as $s) {
                                    $fn = $s->firstname;
                                    $ln = $s->lastname;
                                    $ad = $s->admissionNo;
                                }
                                $image = $s->avatar ?? 'unnamed.png';
                            ?>
                            <div class="d-flex justify-content-center">
                                <div class="symbol-100px symbol-circle overflow-hidden border border-4 border-white shadow">
                                    <img src="<?php echo Storage::url('images/studentavatar/' . $image); ?>" alt="<?php echo $fn; ?> <?php echo $ln; ?>"/>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row g-5">
                                <div class="col-12 col-md-6">
                                    <div class="fs-6 text-muted mb-1">Student Name</div>
                                    <div class="fs-4 fw-bold text-dark"><?php echo $fn; ?> <?php echo $ln; ?></div>
                                    <div class="badge badge-light-primary mt-2">ID: <?php echo $ad; ?></div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="fs-6 text-muted mb-1">Home Address</div>
                                    <div class="fs-6"><?php echo nl2br(wordwrap($s->homeadd, 20, "\n", true)); ?></div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="fs-6 text-muted mb-1">Class</div>
                                    <div class="fs-5 fw-bold"><?php echo $s->schoolclass; ?> <?php echo $s->arm; ?></div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="fs-6 text-muted mb-1">Term</div>
                                    <div class="fs-5 fw-bold"><?php echo $schoolterm; ?></div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="fs-6 text-muted mb-1">Session</div>
                                    <div class="fs-5 fw-bold"><?php echo $schoolsession; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Student Information-->

                <!--begin::Payment Summary-->
                <div class="mb-10">
                    <h3 class="fw-bold fs-4 text-dark mb-5">Payment Summary</h3>
                    <div class="table-responsive">
                        <table class="table table-rounded table-striped">
                            <thead>
                                <tr class="fw-bold fs-6 text-dark border-bottom border-gray-200 bg-light">
                                    <th>Bill Description</th>
                                    <th class="text-end">Bill Amount</th>
                                    <th class="text-end">Last Paid</th>
                                    <th class="text-end">Payment Method</th>
                                    <th class="text-end">Outstanding</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($studentpaymentbill as $sp) { ?>
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold fs-6"><?php echo $sp->title; ?></span>
                                            <span class="text-muted fs-7"><?php echo $sp->description; ?></span>
                                        </div>
                                    </td>
                                    <td class="text-end fw-bold">₦ <?php echo number_format($sp->amount); ?></td>
                                    <td class="text-end">₦ <?php echo number_format($sp->lastPayment); ?></td>
                                    <td class="text-end">
                                        <?php if($sp->paymentMethod == 'Bank Transfer') { ?>
                                            <span class="badge badge-light-primary"><?php echo $sp->paymentMethod; ?></span>
                                        <?php } elseif($sp->paymentMethod == 'Cash') { ?>
                                            <span class="badge badge-light-success"><?php echo $sp->paymentMethod; ?></span>
                                        <?php } else { ?>
                                            <span class="badge badge-light-info"><?php echo $sp->paymentMethod; ?></span>
                                        <?php } ?>
                                    </td>
                                    <td class="text-end fw-bold">₦ <?php echo number_format($sp->balance); ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!--end::Payment Summary-->

                <!--begin::Payment Totals-->
                <div class="d-flex flex-column flex-md-row justify-content-md-end gap-3 mb-8">
                    <div class="bg-light-primary rounded-3 p-6 text-center" style="min-width: 200px;">
                        <div class="text-muted fs-7 mb-1">Total Bill Amount</div>
                        <div class="fs-2 fw-bold text-primary">₦ <?php echo number_format($totalBillAmount); ?></div>
                    </div>
                    <div class="bg-light-success rounded-3 p-6 text-center" style="min-width: 200px;">
                        <div class="text-muted fs-7 mb-1">Total Amount Paid</div>
                        <div class="fs-2 fw-bold text-success">₦ <?php echo number_format($totalPaid); ?></div>
                    </div>
                    <div class="bg-light-danger rounded-3 p-6 text-center" style="min-width: 200px;">
                        <div class="text-muted fs-7 mb-1">Total Outstanding</div>
                        <div class="fs-2 fw-bold text-danger">₦ <?php echo number_format($totalOutstanding); ?></div>
                    </div>
                </div>
                <!--end::Payment Totals-->

                <!--begin::Actions-->
                <div class="text-center border-top pt-8 mt-8">
                    <p class="text-muted mb-4">Thank you for your continued partnership with Trinity Comprehensive International School.</p>
                    <button type="button" class="btn btn-lg btn-primary" onclick="window.print();">
                        <i class="fas fa-print me-2"></i>Print Invoice
                    </button>
                    <a href="<?php echo url()->current(); ?>?download_pdf=1" class="btn btn-lg btn-success ms-3">
                        <i class="fas fa-download me-2"></i>Download PDF
                    </a>
                </div>
                <!--end::Actions-->
            </div>
            <!-- end::Wrapper-->
        </div>
        <!-- end::Body-->
        
        <!-- begin::Footer-->
        <div class="card-footer py-5 text-center text-muted bg-light">
            <div class="mb-2">For any inquiries regarding this invoice, please contact the school administration.</div>
            <div class="fs-7">© <?php echo date('Y'); ?> Trinity Comprehensive International School. All Rights Reserved.</div>
        </div>
        <!-- end::Footer-->
    </div>

    <script>
        // Optional script for interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Print button already has onclick handler in the HTML
        });
    </script>
</body>
</html>