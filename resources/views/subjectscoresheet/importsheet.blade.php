@extends('layouts.master')
@section('content')

     <!-- Start Page title and tab -->
     <div class="section-body">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center ">
                <div class="header-action">
                    <h1 class="page-title">School</h1>
                    <ol class="breadcrumb page-breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Result Room</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Bullk import </li>
                    </ol>
                </div>
                <ul class="nav nav-tabs page-header-tab">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#edit-class">Scoresheet bulk import</a></li>

                </ul>
            </div>
        </div>
        @if (\Session::has('status'))
           <div class="alert alert-warning fade in">
            <a href="#" class="close" data-dismiss="alert">&times;</a>
                <p>{{ \Session::get('status') }}</p>
            </div>
        @endif
            @if (\Session::has('success'))
           <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"></button>
                <p>{{ \Session::get('success') }}</p>
            </div>
        @endif

    </div>
    <div class="section-body mt-4">
        <div class="container-fluid">
            <div class="tab-content">
                <div class="tab-pane active" id="edit-arm">
                    <div class="card">
                        <div class="card-header">
                            <li class="nav-item">
                                @if (\Session::has('schoolclassid')||\Session::has('subjectclassid') || \Session::has('stafid') || \Session::has('termid') || \Session::has('sessionid'))
                                <a href='/subjectscoresheet/{{ \Session::get('schoolclassid') }}/{{ \Session::get('subjectclassid') }}/{{ \Session::get('staffid') }}/{{ \Session::get('termid')}}/{{ \Session::get('sessionid') }}'
                                class="btn btn-outline-success" data-toggle="tooltip" > < Back</a> </li>
                                @else
                                <?php
                                 redirect('myresultroom.index');
                                ?>
                                @endif
                            @if (count($errors) > 0)
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert"></button>
                                <strong>Opps!</strong> Something went wrong, please check below errors.<br><br>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                            <div class="card-options ">
                                <a href="#" class="card-options-collapse" data-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a>
                                <a href="#" class="card-options-remove" data-toggle="card-remove"><i class="fe fe-x"></i></a>
                            </div>
                        </div>



                        <div class="tab-content">


                            <div class="card">
                                <div class="card-body">

                                    <form action="{{ route('import.post') }}" method="POST" enctype="multipart/form-data">
                                     @csrf
                                        <input type="hidden" name="schoolclassid" value="{{ $schoolclassid }}">
                                        <input type="hidden" name="subjectclassid" value="{{ $subjectclassid }}">
                                        <input type="hidden" name="staffid" value="{{ $staffid }}">
                                        <input type="hidden" name="termid" value="{{ $termid }}">
                                        <input type="hidden" name="sessionsid" value="{{ $sessionid }}">

                                        <div class="form-group row">
                                            <label class="col-md-3 col-form-label">Import Batch File</label>
                                            <div class="col-md-9">
                                                <input  type="file" name="file" class="dropify">
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-md-3 col-form-label"></label>
                                            <div class="col-md-7">
                                                <button type="submit" class="btn btn-primary">Upload Excel File</button>

                                            </div>
                                         </div>
                                    </form>






                                </div>
                            </div>


                        </div>






                    </div>
                </div>


            </div>
        </div>
    </div>
    @endsection
