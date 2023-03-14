@extends('backend.layout')

@section('styles')
    <link rel="stylesheet" href="{{asset('backend/bower_components/select2/dist/css/select2.min.css')}}">
@endsection

@section('scripts')
    <script src="{{asset('backend/bower_components/select2/dist/js/select2.full.min.js')}}"></script>
    <script src="{{asset('backend/dist/js/jquery.validate.min.js')}}"></script>
    <script src="{{asset('backend/dist/js/pages/employee.js')}}"></script>
    <script src="{{asset('backend/dist/js/pages/custom.js')}}"></script>
    <script>
        $(function(){
            $('.select2').select2();
        });
    </script>
@endsection

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Employee</h1>
            <ol class="breadcrumb">
                <li><a href="{{route('admin.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{route('admin.employee.index')}}">Employee</a></li>
                <li class="active">Create</li>
            </ol>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title">Create Employee</h3>
                        </div>
                        @include('backend.employee.form')
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
