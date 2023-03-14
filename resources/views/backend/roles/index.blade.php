@php
    $permission = new \App\Models\Permissions();
@endphp
@extends('backend.layout')

@section('styles')
    <link rel="stylesheet"
          href="{{asset('backend/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css')}}">
@endsection

@section('scripts')
    <script src="{{asset('backend/bower_components/datatables.net/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('backend/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
    <script src="{{asset('backend/dist/js/jquery.validate.min.js')}}"></script>
    <script src="{{asset('backend/dist/js/pages/roles.js')}}"></script>
    <script>
        $(function () {
            $('#role-list').DataTable();
        });
    </script>
@endsection

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Roles</h1>
            <ol class="breadcrumb">
                <li><a href="{{route('admin.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Roles</li>
            </ol>
        </section>

        <section class="content">
            <div class="row">

                <div id="display_message" class="col-xs-12">
                    <div class="alert alert-success"></div>
                </div>

                <div class="col-xs-12">
                    <div class="box box-warning">
                        <div class="box-header">
                            <h3 class="box-title">List of Roles</h3>
                            @if($permission::checkActionPermission('add_roles'))
                                <div class="pull-right">
                                    <button type="button" onclick="addRole(this)" class="btn btn-gradient">Add</button>
                                </div>
                            @endif
                        </div>
                        <div class="box-body">
                            <table id="role-list" class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($roleList as $key => $value)
                                    <tr>
                                        <td>{{++$key}}</td>
                                        <td>{{$value->name}}</td>
                                        <td>
                                            @if($permission::checkActionPermission('edit_roles'))
                                                <a href="javascript:void(0);"
                                                   onclick="editRole(this,'{{$value->uuid}}')"
                                                   class="btn-round"><i class="fa fa-pencil-square-o"></i></a>
                                                <a href="{{route('admin.roles.permissions',$value->uuid)}}"
                                                   class="btn-round"><i class="fa fa-lock"></i></a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
