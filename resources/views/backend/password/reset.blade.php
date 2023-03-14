@extends('backend.before-layout')

@section('script')
    <script src="{{asset('backend/dist/js/pages/reset-password.js')}}"></script>
@endsection

@section('content')
    <p class="login-box-msg">Reset Password</p>
    {{ Form::model(null, ['route' => ['admin.reset-password.update',$token], 'files' => true, 'role' => 'form', 'id'=>'frmResetPassword', 'method'=>'PUT', 'autocomplete'=>'off']) }}
    <div class="form-group has-feedback">
        {{ Form::password('password', ["minlength"=>6,"autofocus","required", "class"=>"form-control", "placeholder"=>"New Password", "id"=>"password"]) }}
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
    </div>
    <div class="form-group has-feedback">
        {{ Form::password('confirm_password', ["minlength"=>6,"autofocus","required", "class"=>"form-control", "placeholder"=>"Confirm Password", "id"=>"confirm_password"]) }}
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
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
