@extends('backend.layout')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>403</h1>
            <ol class="breadcrumb">
                <li><a href="{{route('admin.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">403</li>
            </ol>
        </section>

        <section class="content">
            <div class="error-page">
                <div class="error-content">
                    <h3><i class="fa fa-warning text-yellow"></i> Access Forbidden</h3>
                </div>
            </div>
        </section>
    </div>
@endsection
