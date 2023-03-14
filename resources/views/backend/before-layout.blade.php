<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{env('APP_NAME')}}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{asset('backend/bower_components/bootstrap/dist/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('backend/bower_components/font-awesome/css/font-awesome.min.css')}}">
    <link rel="stylesheet" href="{{asset('backend/bower_components/Ionicons/css/ionicons.min.css')}}">
    <link rel="stylesheet" href="{{asset('backend/dist/css/AdminLTE.min.css')}}">
    <link rel="stylesheet" href="{{asset('backend/dist/css/custom.css')}}">
    <link rel="stylesheet" href="{{asset('backend/plugins/iCheck/square/blue.css')}}">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script>
        var adminUrl = '{{url('/'.config('constants.admin'))}}';
    </script>
    <link rel="icon" type="image/png" href="{{asset('backend/images/favicon.png')}}">

    <!-- Google Font -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>
<body class="hold-transition login-page new-theme">
<div class="login-content">
    <h1>From Now Onward,</h1>
    <h2>Temperature &</h2>
    <h2>Humidity</h2>
    <h2>Under Control !</h2>
    <a href="{{config('constants.app_store_url')}}">
        <img src="{{asset('public/backend/images/ios.png')}}" alt="ios">
    </a>
    <a href="{{config('constants.play_store_url')}}">
        <img src="{{asset('public/backend/images/android.png')}}" alt="android">
    </a>
</div>

<div class="login-box">
    <div class="login-box-body">
        <div class="login-logo">
            <img alt="{{env('APP_NAME')}}" src="{{ asset('backend/images/logo.png') }}" id="project_logo_img"
                 width="250px"/>
        </div>

        @yield('content')

    </div>
</div>

<script src="{{asset('backend/bower_components/jquery/dist/jquery.min.js')}}"></script>
<script src="{{asset('backend/bower_components/bootstrap/dist/js/bootstrap.min.js')}}"></script>
<script src="{{asset('backend/dist/js/jquery.validate.min.js')}}"></script>
@yield('script')
<script src="{{asset('backend/plugins/iCheck/icheck.min.js')}}"></script>
<script>
    $(function () {
        $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' /* optional */
        });
    });
</script>
</body>
</html>
