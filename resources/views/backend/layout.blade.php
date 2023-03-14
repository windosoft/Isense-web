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

    @yield('styles')

    <link rel="stylesheet" href="{{asset('backend/dist/css/AdminLTE.min.css')}}">
    <link rel="stylesheet" href="{{asset('backend/dist/css/custom.css')}}">
    <?php
    $cookieName = Cookie::get('theme-selected');
    ?>
    @if($cookieName == 'lightMode' || $cookieName == '')

    @else
        <link rel="stylesheet" href="{{asset('backend/dist/css/custom-dark.css')}}">
    @endif

    <link rel="stylesheet" href="{{asset('backend/dist/css/skins/_all-skins.min.css')}}">
    <link rel="stylesheet" href="{{asset('backend/plugins/toastr/toastr.min.css')}}">

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
<body class="hold-transition sidebar-mini skin-yellow new-theme">
<div class="wrapper">

    @include('backend.partials._header')

    @include('backend.partials._sidebar')

    @yield('content')

    @include('backend.partials._footer')

</div>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog"></div>
<script src="{{asset('backend/bower_components/jquery/dist/jquery.min.js')}}"></script>
<script src="{{asset('backend/bower_components/jquery-ui/jquery-ui.min.js')}}"></script>
<script>
    $.widget.bridge('uibutton', $.ui.button);
</script>
<script src="{{asset('backend/plugins/toastr/toastr.min.js')}}"></script>
<script src="{{asset('backend/bower_components/bootstrap/dist/js/bootstrap.min.js')}}"></script>
<script src="{{asset('backend/dist/js/adminlte.min.js')}}"></script>
<script src="{{asset('backend/dist/js/demo.js')}}"></script>
@include('backend.partials._toastr')
<script>
    var message = localStorage.getItem('message');
    localStorage.removeItem('message');
    if (message) {
        $('div#display_message').find('.alert').show().html(message);
        setTimeout(function () {
            $('div#display_message').find('.alert').hide();
        }, 5000);
    }
</script>

@yield('scripts')
</body>
</html>
