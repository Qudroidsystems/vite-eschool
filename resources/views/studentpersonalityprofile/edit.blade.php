```blade
@extends('layouts.master')

@section('content')
<style>
    :root {
        --tb-primary: #009ef7;
        --tb-secondary: #3b82f6;
        --tb-success: #50cd89;
        --tb-light: #f5f8fa;
        --tb-success-subtle: rgba(80, 205, 137, 0.1);
    }

    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        max-width: 100%;
    }

    .card-body {
        padding: 1.5rem;
    }

    .card-logo {
        height: 28px;
        margin-bottom: 1rem;
    }

    .fs-md {
        font-size: 1.125rem !important;
    }

    .table-borderless th, .table-borderless td {
        border: none;
        padding: 0.5rem 0.75rem;
        vertical-align: middle;
    }

    .table-nowrap th, .table-nowrap td {
        white-space: nowrap;
    }

    .table-light {
        background-color: var(--tb-light);
    }

    .alert-danger, .alert-warning {
        background-color: rgba(241, 65, 108, 0.1);
        border-color: #f1416c;
        color: #f1416c;
        padding: 0.75rem;
    }

    .alert-success {
        background-color: var(--tb-success-subtle);
        border-color: var(--tb-success);
        color: var(--tb-success);
        padding: 0.75rem;
    }

    .hstack {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 0.5rem;
    }

    .d-print-none {
        display: flex !important;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    table {
        width: 100%;
        table-layout: auto;
    }

    .symbol img {
        width: 100px;
        height: 100px;
        object-fit: cover;
    }

    .address-wrap {
        overflow-wrap: break-word;
        word-break: break-word;
        hyphens: auto;
        max-width: 180px;
        display: inline-block;
    }

    .school-details {
        margin-bottom: 1rem;
    }

    .school-details h6 {
        margin-bottom: 0.5rem;
    }

    @media print {
        html, body {
            background-color: #fff;
            margin: 0;
            padding: 0;
            width: 210mm;
            height: 297mm;
        }

        .app-main, .app-content, .app-container {
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }

        .card {
            box-shadow: none;
            max-width: 100%;
            width: 100%;
            border-radius: 0;
            margin: 0;
            padding: 0;
        }

        .card-body {
            padding: 0.5cm;
        }

        .d-print-none, .alert {
            display: none !important;
        }

        .card::before {
            content: "{{ $schoolInfo->school_name ?? 'TOPCLASS COLLEGE' }} Personality Profile";
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #212529;
        }

        .card::after {
            content: "© {{ date('Y') }} {{ $schoolInfo->school_name ?? 'TOPCLASS COLLEGE' }}";
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #7E8299;
        }

        .table-responsive {
            overflow: visible;
        }

        table {
            table-layout: fixed;
            font-size: 0.75rem;
        }

        .fs-md {
            font-size: 0.9rem !important;
        }

        .table-borderless th, .table-borderless td {
            padding: 0.3rem 0.5rem;
        }

        h6, p {
            margin-bottom: 0.2rem;
            font-size: 0.8rem;
        }

        .symbol img {
            width: 30px;
            height: 30px;
        }

        .address-wrap {
            max-width: 120px;
        }

        .card-logo {
            height: 20px;
            margin-bottom: 0.5cm;
        }

        .school-details {
            margin-bottom: 0.5cm;
        }

        .school-details h6 {
            margin-bottom: 0.3rem;
        }

        @page {
            size: A4;
            margin: 0.5cm;
        }
    }

    @media (max-width: 767.98px) {
        .card-body {
            padding: 1rem;
        }

        .hstack {
            flex-direction: column;
            gap: 1rem;
        }

        .symbol img {
            width: 80px;
            height: 80px;
        }

        .address-wrap {
            max-width: 100%;
        }

        .card-logo {
            margin-bottom: 0.75rem;
        }

        .school-details {
            margin-bottom: 0.75rem;
        }
    }
</style>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        <p style="color: green">Student's Personality Profile</p>
                    </h1>
                </div>
                <div class="hstack gap-2 d-print-none">
                    <a href="{{ route('myclass.index') }}" class="btn btn-light-primary"><i class="fas fa-arrow-left me-1"></i> Back</a>
                    <a href="javascript:window.print()" class="btn btn-success"><i class="ri-printer-line align-bottom me-1"></i> Print</a>
                </div>
            </div>
        </div>

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Whoops!</strong> There were some problems with your input.<br>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status') || session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') ?: session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card mb-5 mb-xl-10">
                    <div class="card-body pt-9 pb-0">
                        <div class="school-header d-none d-print-block">
                            <img src="{{ $schoolInfo->logo_url }}" class="card-logo" alt="{{ $schoolInfo->school_name ?? 'TOPCLASS COLLEGE' }}" height="28">
                            <div class="school-details">
                                <h6><span class="text-muted fw-normal">Email:</span> <span id="email">{{ $schoolInfo->school_email ?? 'info@topclassschool.edu' }}</span></h6>
                                <h6><span class="text-muted fw-normal">Website:</span> <span id="website">{{ $schoolInfo->school_website ? '<a href="' . $schoolInfo->school_website . '" target="_blank">' . $schoolInfo->school_website . '</a>' : 'N/A' }}</span></h6>
                                <h6><span class="text-muted fw-normal">Address:</span> <span id="address" class="address-wrap">{!! Str::replace(',', ',<br>', $schoolInfo->school_address ?? 'Your School Address Here') !!}</span></h6>
                                <h6 class="mb-0"><span class="text-muted fw-normal">Contact No:</span> <span id="contact-no">{{ $schoolInfo->school_phone ?? 'Your Phone Number' }}</span></h6>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap flex-sm-nowrap">
                            <div class="me-7 mb-4">
                                @foreach ($students as $st)
                                    <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                                        @php
                                            $image = $st->avatar ?? 'unnamed.png';
                                        @endphp
                                        <img src="{{ Storage::url('images/studentavatar/' . $image) }}" alt="{{ $st->fname }} {{ $st->lastname }}" />
                                        <div class="position-absolute translate-middle bottom-0 start-100 mb-6 bg-success rounded-circle border border-4 border-body h-20px w-20px"></div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center mb-2">
                                            <a href="#" class="text-gray-900 text-hover-primary fs-2 fw-bold me-1">{{ $st->fname }} {{ $st->lastname }}</a>
                                            <a href="#"><i class="ki-duotone ki-verify fs-1 text-primary"><span class="path1"></span><span class="path2"></span></i></a>
                                        </div>
                                        <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                                            <a href="#" class="d-flex align-items-center me-5 mb-2">
                                                <i class="ki-duotone ki-profile-circle fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                {{ $st->gender }}
                                            </a>
                                            <a href="#" class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2">
                                                <i class="ki-duotone ki-geolocation fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                                <span class="address-wrap">{!! Str::replace(',', ',<br>', $st->homeaddress ?? 'N/A') !!}</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-5 mb-xl-10">
                    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
                        <div class="card-title m-0">
                            <h3 class="fw-bold m-0"><p style="color: rgb(109, 109, 212)">Personality Profile Details for {{ $st->fname }} {{ $st->lastname }}</p></h3>
                        </div>
                    </div>
                    <div id="kt_account_settings_profile_details" class="collapse show">
                        <div class="card-body py-4">
                            <form role="form" id="inline-validation" class="form-horizontal form-stripe" action="/save" method="POST">
                                @csrf
                                <input type="hidden" name="studentid" value="{{ $studentid }}">
                                <input type="hidden" name="schoolclassid" value="{{ $schoolclassid }}">
                                <input type="hidden" name="staffid" value="{{ $staffid }}">
                                <input type="hidden" name="termid" value="{{ $termid }}">
                                <input type="hidden" name="sessionid" value="{{ $sessionid }}">

                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0" id="kt_roles_view_table">
                                        <thead>
                                            <tr class="table-light">
                                                <th scope="col" style="width: 50px;">#</th>
                                                <th scope="col">Trait</th>
                                                <th scope="col">Remark</th>
                                                <th scope="col">Current Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($studentpp as $s)
                                                <tr>
                                                    <td>1</td>
                                                    <td>Punctuality</td>
                                                    <td>
                                                        <select class="form-control col-md-8" name="punctuality" required>
                                                            <option value="" {{ $s->punctuality == '' ? 'selected' : '' }}>Select Remark</option>
                                                            <option value="Excellent" {{ $s->punctuality == 'Excellent' ? 'selected' : '' }}>Excellent</option>
                                                            <option value="Very Good" {{ $s->punctuality == 'Very Good' ? 'selected' : '' }}>Very Good</option>
                                                            <option value="Good" {{ $s->punctuality == 'Good' ? 'selected' : '' }}>Good</option>
                                                            <option value="Fairly Good" {{ $s->punctuality == 'Fairly Good' ? 'selected' : '' }}>Fairly Good</option>
                                                            <option value="Poor" {{ $s->punctuality == 'Poor' ? 'selected' : '' }}>Poor</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" value="{{ $s->punctuality }}" readonly required>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>2</td>
                                                    <td>Neatness</td>
                                                    <td>
                                                        <select class="form-control col-md-8" name="neatness" required>
                                                            <option value="" {{ $s->neatness == '' ? 'selected' : '' }}>Select Remark</option>
                                                            <option value="Excellent" {{ $s->neatness == 'Excellent' ? 'selected' : '' }}>Excellent</option>
                                                            <option value="Very Good" {{ $s->neatness == 'Very Good' ? 'selected' : '' }}>Very Good</option>
                                                            <option value="Good" {{ $s->neatness == 'Good' ? 'selected' : '' }}>Good</option>
                                                            <option value="Fairly Good" {{ $s->neatness == 'Fairly Good' ? 'selected' : '' }}>Fairly Good</option>
                                                            <option value="Poor" {{ $s->neatness == 'Poor' ? 'selected' : '' }}>Poor</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" value="{{ $s->neatness }}" readonly required>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>3</td>
                                                    <td>Leadership</td>
                                                    <td>
                                                        <select class="form-control col-md-8" name="leadership" required>
                                                            <option value="" {{ $s->leadership == '' ? 'selected' : '' }}>Select Remark</option>
                                                            <option value="Excellent" {{ $s->leadership == 'Excellent' ? 'selected' : '' }}>Excellent</option>
                                                            <option value="Very Good" {{ $s->leadership == 'Very Good' ? 'selected' : '' }}>Very Good</option>
                                                            <option value="Good" {{ $s->leadership == 'Good' ? 'selected' : '' }}>Good</option>
                                                            <option value="Fairly Good" {{ $s->leadership == 'Fairly Good' ? 'selected' : '' }}>Fairly Good</option>
                                                            <option value="Poor" {{ $s->leadership == 'Poor' ? 'selected' : '' }}>Poor</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" value="{{ $s->leadership }}" readonly required>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>4</td>
                                                    <td>Attitude</td>
                                                    <td>
                                                        <select class="form-control col-md-8" name="attitude" required>
                                                            <option value="" {{ $s->attitude == '' ? 'selected' : '' }}>Select Remark</option>
                                                            <option value="Excellent" {{ $s->attitude == 'Excellent' ? 'selected' : '' }}>Excellent</option>
                                                            <option value="Very Good" {{ $s->attitude == 'Very Good' ? 'selected' : '' }}>Very Good</option>
                                                            <option value="Good" {{ $s->attitude == 'Good' ? 'selected' : '' }}>Good</option>
                                                            <option value="Fairly Good" {{ $s->attitude == 'Fairly Good' ? 'selected' : '' }}>Fairly Good</option>
                                                            <option value="Poor" {{ $s->attitude == 'Poor' ? 'selected' : '' }}>Poor</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" value="{{ $s->attitude }}" readonly required>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>5</td>
                                                    <td>Reading</td>
                                                    <td>
                                                        <select class="form-control col-md-8" name="reading" required>
                                                            <option value="" {{ $s->reading == '' ? 'selected' : '' }}>Select Remark</option>
                                                            <option value="Excellent" {{ $s->reading == 'Excellent' ? 'selected' : '' }}>Excellent</option>
                                                            <option value="Very Good" {{ $s->reading == 'Very Good' ? 'selected' : '' }}>Very Good</option>
                                                            <option value="Good" {{ $s->reading == 'Good' ? 'selected' : '' }}>Good</option>
                                                            <option value="Fairly Good" {{ $s->reading == 'Fairly Good' ? 'selected' : '' }}>Fairly Good</option>
                                                            <option value="Poor" {{ $s->reading == 'Poor' ? 'selected' : '' }}>Poor</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" value="{{ $s->reading }}" readonly required>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>6</td>
                                                    <td>Honesty</td>
                                                    <td>
                                                        <select class="form-control col-md-8" name="honesty" required>
                                                            <option value="" {{ $s->honesty == '' ? 'selected' : '' }}>Select Remark</option>
                                                            <option value="Excellent" {{ $s->honesty == 'Excellent' ? 'selected' : '' }}>Excellent</option>
                                                            <option value="Very Good" {{ $s->honesty == 'Very Good' ? 'selected' : '' }}>Very Good</option>
                                                            <option value="Good" {{ $s->honesty == 'Good' ? 'selected' : '' }}>Good</option>
                                                            <option value="Fairly Good" {{ $s->honesty == 'Fairly Good' ? 'selected' : '' }}>Fairly Good</option>
                                                            <option value="Poor" {{ $s->honesty == 'Poor' ? 'selected' : '' }}>Poor</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" value="{{ $s->honesty }}" readonly required>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>7</td>
                                                    <td>Cooperation</td>
                                                    <td>
                                                        <select class="form-control col-md-8" name="cooperation" required>
                                                            <option value="" {{ $s->cooperation == '' ? 'selected' : '' }}>Select Remark</option>
                                                            <option value="Excellent" {{ $s->cooperation == 'Excellent' ? 'selected' : '' }}>Excellent</option>
                                                            <option value="Very Good" {{ $s->cooperation == 'Very Good' ? 'selected' : '' }}>Very Good</option>
                                                            <option value="Good" {{ $s->cooperation == 'Good' ? 'selected' : '' }}>Good</option>
                                                            <option value="Fairly Good" {{ $s->cooperation == 'Fairly Good' ? 'selected' : '' }}>Fairly Good</option>
                                                            <option value="Poor" {{ $s->cooperation == 'Poor' ? 'selected' : '' }}>Poor</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" value="{{ $s->cooperation }}" readonly required>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>8</td>
                                                    <td>Self-control</td>
                                                    <td>
                                                        <select class="form-control col-md-8" name="selfcontrol" required>
                                                            <option value="" {{ $s->selfcontrol == '' ? 'selected' : '' }}>Select Remark</option>
                                                            <option value="Excellent" {{ $s->selfcontrol == 'Excellent' ? 'selected' : '' }}>Excellent</option>
                                                            <option value="Very Good" {{ $s->selfcontrol == 'Very Good' ? 'selected' : '' }}>Very Good</option>
                                                            <option value="Good" {{ $s->selfcontrol == 'Good' ? 'selected' : '' }}>Good</option>
                                                            <option value="Fairly Good" {{ $s->selfcontrol == 'Fairly Good' ? 'selected' : '' }}>Fairly Good</option>
                                                            <option value="Poor" {{ $s->selfcontrol == 'Poor' ? 'selected' : '' }}>Poor</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" value="{{ $s->selfcontrol }}" readonly required>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>9</td>
                                                    <td>Physical Health</td>
                                                    <td>
                                                        <select class="form-control col-md-8" name="physicalhealth" required>
                                                            <option value="" {{ $s->physicalhealth == '' ? 'selected' : '' }}>Select Remark</option>
                                                            <option value="Excellent" {{ $s->physicalhealth == 'Excellent' ? 'selected' : '' }}>Excellent</option>
                                                            <option value="Very Good" {{ $s->physicalhealth == 'Very Good' ? 'selected' : '' }}>Very Good</option>
                                                            <option value="Good" {{ $s->physicalhealth == 'Good' ? 'selected' : '' }}>Good</option>
                                                            <option value="Fairly Good" {{ $s->physicalhealth == 'Fairly Good' ? 'selected' : '' }}>Fairly Good</option>
                                                            <option value="Poor" {{ $s->physicalhealth == 'Poor' ? 'selected' : '' }}>Poor</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" value="{{ $s->physicalhealth }}" readonly required>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>10</td>
                                                    <td>Politeness</td>
                                                    <td>
                                                        <select class="form-control col-md-8" name="politeness" required>
                                                            <option value="" {{ $s->politeness == '' ? 'selected' : '' }}>Select Remark</option>
                                                            <option value="Excellent" {{ $s->politeness == 'Excellent' ? 'selected' : '' }}>Excellent</option>
                                                            <option value="Very Good" {{ $s->politeness == 'Very Good' ? 'selected' : '' }}>Very Good</option>
                                                            <option value="Good" {{ $s->politeness == 'Good' ? 'selected' : '' }}>Good</option>
                                                            <option value="Fairly Good" {{ $s->politeness == 'Fairly Good' ? 'selected' : '' }}>Fairly Good</option>
                                                            <option value="Poor" {{ $s->politeness == 'Poor' ? 'selected' : '' }}>Poor</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" value="{{ $s->politeness }}" readonly required>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>11</td>
                                                    <td>Stability</td>
                                                    <td>
                                                        <select class="form-control col-md-8" name="stability" required>
                                                            <option value="" {{ $s->stability == '' ? 'selected' : '' }}>Select Remark</option>
                                                            <option value="Excellent" {{ $s->stability == 'Excellent' ? 'selected' : '' }}>Excellent</option>
                                                            <option value="Very Good" {{ $s->stability == 'Very Good' ? 'selected' : '' }}>Very Good</option>
                                                            <option value="Good" {{ $s->stability == 'Good' ? 'selected' : '' }}>Good</option>
                                                            <option value="Fairly Good" {{ $s->stability == 'Fairly Good' ? 'selected' : '' }}>Fairly Good</option>
                                                            <option value="Poor" {{ $s->stability == 'Poor' ? 'selected' : '' }}>Poor</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" value="{{ $s->stability }}" readonly required>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>12</td>
                                                    <td>Games and Sports</td>
                                                    <td>
                                                        <select class="form-control col-md-8" name="gamesandsports" required>
                                                            <option value="" {{ $s->gamesandsports == '' ? 'selected' : '' }}>Select Remark</option>
                                                            <option value="Excellent" {{ $s->gamesandsports == 'Excellent' ? 'selected' : '' }}>Excellent</option>
                                                            <option value="Very Good" {{ $s->gamesandsports == 'Very Good' ? 'selected' : '' }}>Very Good</option>
                                                            <option value="Good" {{ $s->gamesandsports == 'Good' ? 'selected' : '' }}>Good</option>
                                                            <option value="Fairly Good" {{ $s->gamesandsports == 'Fairly Good' ? 'selected' : '' }}>Fairly Good</option>
                                                            <option value="Poor" {{ $s->gamesandsports == 'Poor' ? 'selected' : '' }}>Poor</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" value="{{ $s->gamesandsports }}" readonly required>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>13</td>
                                                    <td>School Attendance</td>
                                                    <td>
                                                        <input type="number" name="attendance" value="{{ $s->attendance }}" class="form-control" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td>14</td>
                                                    <td>Teacher's Comment</td>
                                                    <td>
                                                        <input type="text" name="classteachercomment" value="{{ $s->classteachercomment }}" class="form-control">
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td>15</td>
                                                    <td>Principal's Comment</td>
                                                    <td>
                                                        <input type="text" name="principalscomment" value="{{ $s->principalscomment }}" class="form-control">
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td>
                                                        <button type="submit" class="btn btn-primary" id="update-profile-btn">Update Profile</button>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/js/personalityprofile/init.js') }}"></script>
@endsection
```

### Generated init.js
The `init.js` file initializes the form with Bootstrap 5 validation, handles AJAX submission to `/save`, displays success/error alerts, and prevents multiple submissions.

<xaiArtifact artifact_id="4466e6e1-c601-4958-8736-e5f21929dcb9" artifact_version_id="8a4863bf-2325-4aec-9618-6eb4b309355c" title="init.js" contentType="application/javascript">
```javascript
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('inline-validation');
    const submitButton = document.getElementById('update-profile-btn');

    // Initialize Bootstrap validation
    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            form.classList.add('was-validated');
            return;
        }

        event.preventDefault();
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="ri-save-line align-bottom me-1"></i> Saving...';

        // Collect form data
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });

        // Perform AJAX submission
        fetch('/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(data),
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success alert
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show';
                    alert.role = 'alert';
                    alert.innerHTML = `
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    document.querySelector('#kt_app_content_container').prepend(alert);
                    form.classList.remove('was-validated');
                } else {
                    // Show error alert
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-danger alert-dismissible fade show';
                    alert.role = 'alert';
                    alert.innerHTML = `
                        ${data.message || 'Failed to update profile.'}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    document.querySelector('#kt_app_content_container').prepend(alert);
                }
            })
            .catch(error => {
                // Show error alert for network issues
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger alert-dismissible fade show';
                alert.role = 'alert';
                alert.innerHTML = `
                    Failed to update profile: ${error.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                document.querySelector('#kt_app_content_container').prepend(alert);
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="ri-save-line align-bottom me-1"></i> Update Profile';
            });
    });

    // Add Bootstrap validation classes on input change
    form.querySelectorAll('select, input').forEach(input => {
        input.addEventListener('change', function () {
            if (form.checkValidity()) {
                form.classList.add('was-validated');
            }
        });
    });
});
```