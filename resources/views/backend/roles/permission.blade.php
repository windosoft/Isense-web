@extends('backend.layout')

@section('styles')
    <link rel="stylesheet" href="{{asset('backend/plugins/iCheck/all.css')}}">
@endsection

@section('scripts')
    <script src="{{asset('backend/plugins/iCheck/icheck.min.js')}}"></script>
    <script src="{{asset('backend/dist/js/jquery.validate.min.js')}}"></script>
    <script src="{{asset('backend/dist/js/pages/permission.js')}}"></script>
    <script>
        $(function () {
            $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
                checkboxClass: 'icheckbox_flat-green',
                radioClass: 'iradio_flat-green'
            });
        });
    </script>
@endsection

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Roles</h1>
            <ol class="breadcrumb">
                <li><a href="{{route('admin.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{route('admin.roles.index')}}">Roles</a></li>
                <li class="active">Permission</li>
            </ol>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-warning">
                        <div class="box-header">
                            <h3 class="box-title">Role - {{ucfirst($roleData->name)}}</h3>
                        </div>
                        <div class="box-body">
                            {{ Form::model($roleData, ['route' => ['admin.roles.permissions.update',$roleData->uuid], 'files' => true, 'role' => 'form', 'id'=>'frmPermission', 'method'=>'PUT']) }}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered">
                                            <thead>
                                            <tr>
                                                <th>#</th>
                                                <?php
                                                foreach ($roleData->actionList as $action) {
                                                    $name = $action;
                                                    if ($action == 'browse') {
                                                        $name = 'List';
                                                    } elseif ($action == 'read') {
                                                        $name = 'View';
                                                    }
                                                    echo "<th class='text-center'>" . ucfirst($name) . "</th>";
                                                }
                                                ?>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($roleData->moduleList as $module)
                                                @if($module != 'roles')
                                                    <tr>
                                                        <td>
                                                            <strong>{{ucwords(str_replace('_',' ',$module))}}</strong>
                                                        </td>
                                                        @foreach($roleData->actionList as $action)
                                                            <td class="text-center">
                                                                <?php
                                                                $permissionName = $action . "_" . $module;
                                                                if (in_array($permissionName, $roleData->permissionList)) {
                                                                    $checked = '';
                                                                    if (in_array($permissionName, $roleData->allowPermission)) {
                                                                        $checked = 'checked';
                                                                    }
                                                                    echo "<input type='checkbox' name='permissions[]' class='flat-red' $checked value='$permissionName'/>";
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endif
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-gradient pull-right" id="btnSubmit"
                                            data-loading-text="<i class='fa fa-spinner fa-spin'></i> loading"> Update
                                    </button>
                                </div>
                                <div class="col-md-12 m-t-20">
                                    <div class="alert alert-success"></div>
                                    <div class="alert alert-danger"></div>
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
