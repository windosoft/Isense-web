@php
    $companyRole = \App\Models\Roles::$company;
@endphp
@extends('backend.layout')

@section('styles')
    <link rel="stylesheet" href="{{asset('backend/bower_components/select2/dist/css/select2.min.css')}}">
@endsection

@section('scripts')
    <script src="{{asset('backend/bower_components/select2/dist/js/select2.full.min.js')}}"></script>
    <script src="{{asset('backend/dist/js/jquery.validate.min.js')}}"></script>
    <script src="{{asset('backend/dist/js/pages/profile.js')}}"></script>
    <script>
        $(function(){
            $('.select2').select2();
        });
    </script>
@endsection

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Profile</h1>
            <ol class="breadcrumb">
                <li><a href="{{route('admin.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Profile</li>
            </ol>
        </section>

        <section class="content">
            <div class="row">
                <div id="display_message" class="col-xs-12">
                    <div class="alert alert-success"></div>
                </div>
                <div class="col-md-6">
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title">Update Profile</h3>
                        </div>
                        {{ Form::model($userData, ['route' => ['admin.profile.update'], 'files' => true, 'role' => 'form', 'id'=>'frmProfile', 'method'=>'PUT']) }}
                        <div class="box-body">
                            <div class="row">
                                @if($userData->role_id == $companyRole)
                                    <div class="col-sm-12">
                                        <div class="form-group required">
                                            {{ Form::label('first_name','Company Name') }}
                                            {{ Form::text('first_name', old('first_name'), ["required", "class"=>"form-control", "placeholder"=>"Enter your first name", "id"=>"first_name"]) }}
                                        </div>
                                    </div>
                                @else
                                    <div class="col-sm-6">
                                        <div class="form-group required">
                                            {{ Form::label('first_name','First Name') }}
                                            {{ Form::text('first_name', old('first_name'), ["required", "class"=>"form-control", "placeholder"=>"Enter your first name", "id"=>"first_name"]) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group required">
                                            {{ Form::label('last_name','Last Name') }}
                                            {{ Form::text('last_name', old('last_name'), ["required", "class"=>"form-control", "placeholder"=>"Enter your last name", "id"=>"last_name"]) }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group required">
                                        {{ Form::label('email','Email Address') }}
                                        {{ Form::email('email', old('email'), ["required", "class"=>"form-control", "placeholder"=>"Enter your email address", "id"=>"email"]) }}
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group required">
                                        {{ Form::label('phone','Phone') }}
                                        {{ Form::number('phone', old('phone'), ["required", "class"=>"form-control", "placeholder"=>"Enter your phone", "id"=>"phone"]) }}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        {{ Form::label('password','Password') }}
                                        {{ Form::password('password', ["minlength"=>"6","class"=>"form-control", "placeholder"=>"Enter your password", "id"=>"password"]) }}
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        {{ Form::label('confirm_password','Confirm Password') }}
                                        {{ Form::password('confirm_password', ["minlength"=>"6","class"=>"form-control", "placeholder"=>"Enter confirm password", "id"=>"confirm_password"]) }}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        {{ Form::label('profile','Profile') }}
                                        <input type="file" onchange="previewImage(this)" class="form-control"
                                               id="profile" name="profile" accept="image/*">
                                        <div class="preview-image">
                                            <img src="{{asset($userData->profile)}}" alt="">
                                        </div>
                                    </div>
                                </div>
                                @if($userData->role_id == $companyRole)
                                    <div class="col-sm-6">
                                        <div class="form-group required">
                                            {{ Form::label('time_zone','Time Zone') }}
                                            <select name="time_zone" id="time_zone" class="form-control select2" required>
                                                <option value="">Select Time Zone</option>
                                                @foreach($timeZoneList as $value)
                                                    <option
                                                        value="{{$value}}" {{($value == $userData->time_zone) ? 'selected' : '' }} >{{$value}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="form-group">
                                <div class="alert alert-success"></div>
                                <div class="alert alert-danger"></div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" id="btnSubmit"
                                    data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> loading"
                                    class="btn btn-gradient pull-right">Update
                            </button>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
