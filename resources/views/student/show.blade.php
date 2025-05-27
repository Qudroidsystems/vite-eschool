@extends('layouts.master')
@section('content')
<div class="content">
            <div class="content-header">
          
                <div class="leftside-content-header">
                    <ul class="breadcrumbs">
                        <li><i class="fa fa-columns" aria-hidden="true"></i><a href="#">User Management</a></li>
                        <li><a>Show User</a></li>
                    </ul>
                </div>
            </div>
            <div class="row animated fadeInUp">
                   @if (\Session::has('success'))
             <div class="alert alert-warning fade in">
            <a href="#" class="close" data-dismiss="alert">&times;</a>
                <p>{{ \Session::get('success') }}</p>
            </div>
        @endif
                <div class="col-sm-12 col-md-8 col-md-offset-2">
                   <span class="float-right">
                    <a class="btn btn-primary" href="{{ route('users.index') }}"> Back to Users</a>
                </span>
                    <div class="panel">
                        <div class="panel-content">
                            <div class="row">
                                <div class="col-md-12">
                                  
                                    <div class="lead">
                    <strong>Name:</strong>
                    {{ $user->name }}
                </div>
                <div class="lead">
                    <strong>Email:</strong>
                    {{ $user->email }}
                </div>
                <div class="lead">
                    <strong>Password:</strong>
                    ********
                </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
               
            </div>
        </div>
        @endsection
