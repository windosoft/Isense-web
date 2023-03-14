@extends('backend.before-layout')

@section('script')
    <script src="{{asset('backend/dist/js/pages/login.js')}}"></script>
@endsection

@section('content')
    <p class="login-box-msg">Sign in to start your session</p>
    {{ Form::model(null, ['route' => ['admin.login.post'], 'files' => true, 'role' => 'form', 'id'=>'frmLogin', 'method'=>'post', 'autocomplete'=>'off']) }}
    <div class="form-group has-feedback">
        {{ Form::email('email', old('email'), ["autofocus","required", "class"=>"form-control", "placeholder"=>"Email", "id"=>"email"]) }}
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
    </div>
    <div class="form-group has-feedback">
        {{ Form::password('password', ["required", "class"=>"form-control", "placeholder"=>"Password", "id"=>"password"]) }}
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-success"></div>
            <div class="alert alert-danger"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-8">
            <div class="checkbox icheck">
                <label>
                    <input type="checkbox" name="remember"> Remember Me
                </label>
            </div>
        </div>
        <div class="col-xs-4">
            <button type="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> loading"
                    id="btnSubmit" class="btn btn-gradient btn-block">Sign In
            </button>
        </div>
    </div>
    {{ Form::close() }}
    <a href="{{route('admin.forgot-password')}}">I forgot my password</a>
@endsection
