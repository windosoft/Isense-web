@extends('backend.layout')

@section('styles')
    <link rel="stylesheet" href="{{asset('backend/bower_components/select2/dist/css/select2.min.css')}}">
@endsection

@section('scripts')
    <script src="{{asset('backend/bower_components/select2/dist/js/select2.full.min.js')}}"></script>
    <script src="{{asset('backend/dist/js/jquery.validate.min.js')}}"></script>
    <script src="{{asset('backend/dist/js/pages/company.js')}}"></script>
    <script>
        $(function(){
            $('.select2').select2();
        });
    </script>
@endsection

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Company</h1>
            <ol class="breadcrumb">
                <li><a href="{{route('admin.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{route('admin.company.index')}}">Company</a></li>
                <li class="active">Edit</li>
            </ol>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-md-8">
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title">Edit Company</h3>
                        </div>
                        @include('backend.company.form')
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
