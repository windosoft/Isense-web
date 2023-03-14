@extends('backend.layout')

@section('styles')
    <link rel="stylesheet"
          href="{{asset('backend/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css')}}">
    <link rel="stylesheet" href="{{asset('backend/plugins/timepicker/bootstrap-timepicker.min.css')}}">
    <link rel="stylesheet" href="{{asset('backend/bower_components/select2/dist/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('backend/plugins/iCheck/all.css')}}">
@endsection

@section('scripts')
    <script
        src="{{asset('backend/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
    <script src="{{asset('backend/plugins/timepicker/bootstrap-timepicker.min.js')}}"></script>
    <script src="{{asset('backend/bower_components/select2/dist/js/select2.full.min.js')}}"></script>
    <script src="{{asset('backend/plugins/iCheck/icheck.min.js')}}"></script>
    <script src="{{asset('backend/dist/js/jquery.validate.min.js')}}"></script>
    <script src="{{asset('backend/dist/js/pages/offline.js')}}"></script>
    <script src="{{asset('backend/dist/js/pages/custom.js')}}"></script>
    <script>
        $(function () {
            $('.select2').select2();
            $('.datepicker').datepicker({
                autoclose: true,
                format: 'yyyy-mm-dd'
            });
            $('input[type="checkbox"]').iCheck({
                checkboxClass: 'icheckbox_flat-green',
                radioClass: 'iradio_flat-green'
            });
            $('.timepicker').timepicker({
                showMeridian: false
            });
        });
    </script>
@endsection

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Offline</h1>
            <ol class="breadcrumb">
                <li><a href="{{route('admin.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{route('admin.offline.index')}}">Offline</a></li>
                <li class="active">Edit</li>
            </ol>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title">Edit Offline</h3>
                        </div>
                        @include('backend.offline.form')
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
