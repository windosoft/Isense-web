@extends('backend.before-layout')

@section('script')
    <script src="{{asset('backend/dist/js/pages/forgot-password.js')}}"></script>
@endsection

@section('content')
    <p class="login-box-msg">Forgot your password?</p>
    {{ Form::model(null, ['route' => ['admin.forgot-password.post'], 'files' => true, 'role' => 'form', 'id'=>'frmForgotPassword', 'method'=>'post', 'autocomplete'=>'off']) }}
    <div class="form-group has-feedback">
        {{ Form::email('email', old('email'), ["autofocus","required", "class"=>"form-control", "placeholder"=>"Email", "id"=>"email"]) }}
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-success"></div>
            <div class="alert alert-danger"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 text-center">
            <button type="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> loading"
                    id="btnSubmit" class="btn mb-20 btn-gradient btn-block">Reset My Password
            </button>
            <a href="{{route('admin.login')}}">Return to login page</a>
        </div>
    </div>
    {{ Form::close() }}
@endsection
