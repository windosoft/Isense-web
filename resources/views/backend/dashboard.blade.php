@php
    $permission = new \App\Models\Permissions();
@endphp

@extends('backend.layout')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Dashboard</h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Dashboard</li>
            </ol>
        </section>

        <section class="content">
            <div class="row">
                @if($permission::checkActionPermission('view_company'))
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-aqua">
                            <div class="inner">
                                <h3>{{number_format($dashboard['company'])}}</h3>
                                <p>Company</p>
                            </div>
                            <div class="icon"><i class="fa fa-building-o"></i></div>
                            <a href="{{route('admin.company.index')}}" class="small-box-footer">
                                More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                @endif

                @if($permission::checkActionPermission('view_branch'))
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3>{{number_format($dashboard['branch'])}}</h3>
                                <p>Branches</p>
                            </div>
                            <div class="icon"><i class="fa fa-code-fork"></i></div>
                            <a href="{{route('admin.branches.index')}}" class="small-box-footer">
                                More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                @endif

                @if($permission::checkActionPermission('view_gateway'))
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-yellow">
                            <div class="inner">
                                <h3>{{number_format($dashboard['gateway'])}}</h3>
                                <p>Gateway</p>
                            </div>
                            <div class="icon"><i class="fa fa-random"></i></div>
                            <a href="{{route('admin.gateway.index')}}" class="small-box-footer">
                                More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                @endif

                @if($permission::checkActionPermission('view_sensor'))
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-red">
                            <div class="inner">
                                <h3>{{number_format($dashboard['sensor'])}}</h3>
                                <p>Sensor</p>
                            </div>
                            <div class="icon"><i class="fa fa-mobile"></i></div>
                            <a href="{{route('admin.sensor.index')}}" class="small-box-footer">
                                More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                @endif

                @if($permission::checkActionPermission('view_group'))
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-aqua">
                            <div class="inner">
                                <h3>{{number_format($dashboard['group'])}}</h3>
                                <p>Group</p>
                            </div>
                            <div class="icon"><i class="fa fa-object-group"></i></div>
                            <a href="{{route('admin.group.index')}}" class="small-box-footer">
                                More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                @endif

                @if($permission::checkActionPermission('view_employee'))
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3>{{number_format($dashboard['employee'])}}</h3>
                                <p>Employee</p>
                            </div>
                            <div class="icon"><i class="fa fa-user"></i></div>
                            <a href="{{route('admin.employee.index')}}" class="small-box-footer">
                                More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                @endif

                @if($permission::checkActionPermission('view_temperatures'))
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-yellow">
                            <div class="inner">
                                <h3>{{number_format($dashboard['temperatures'])}}</h3>
                                <p>Temperatures</p>
                            </div>
                            <div class="icon"><i class="fa fa-thermometer"></i></div>
                            <a href="{{route('admin.temperatures.index')}}" class="small-box-footer">
                                More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                @endif

                @if($permission::checkActionPermission('view_humidity'))
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-red">
                            <div class="inner">
                                <h3>{{number_format($dashboard['humidity'])}}</h3>
                                <p>Humidity</p>
                            </div>
                            <div class="icon"><i class="fa fa-cloud"></i></div>
                            <a href="{{route('admin.humidity.index')}}" class="small-box-footer">
                                More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                @endif

                @if($permission::checkActionPermission('view_voltage'))
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-aqua">
                            <div class="inner">
                                <h3>{{number_format($dashboard['voltage'])}}</h3>
                                <p>Voltage</p>
                            </div>
                            <div class="icon"><i class="fa fa-plug"></i></div>
                            <a href="{{route('admin.voltage.index')}}" class="small-box-footer">
                                More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                @endif

                @if($permission::checkActionPermission('view_offline'))
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3>{{number_format($dashboard['offline'])}}</h3>
                                <p>Offline</p>
                            </div>
                            <div class="icon"><i class="fa fa-circle-o"></i></div>
                            <a href="{{route('admin.offline.index')}}" class="small-box-footer">
                                More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                @endif
            </div>

            <div class="row">
                @if($permission::checkActionPermission('view_company'))
                    <div class="col-md-6">
                        <div class="box box-warning">
                            <div class="box-header">
                                <h3 class="box-title">Last 5 Company List</h3>
                                <div class="pull-right">
                                    <a href="{{route('admin.company.index')}}" class="btn btn-gradient">View All</a>
                                </div>
                            </div>
                            <div class="box-body">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Company Name</th>
                                        <th>Email</th>
                                        <th>Branch</th>
                                        <th>Terminal</th>
                                        <th>Sensor</th>
                                    </tr>
                                    </thead>
                                    @if(count($dashboard['company_list']) > 0)
                                        <tbody>
                                        @foreach($dashboard['company_list'] as $key => $value)
                                            <tr>
                                                <td>{{++$key}}</td>
                                                <td>{{ucfirst($value['first_name'])}}</td>
                                                <td>{{$value['email']}}</td>
                                                <td>{{$value['branches']}}</td>
                                                <td>{{$value['terminals']}}</td>
                                                <td>{{$value['devices']}}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    @else
                                        <tfoot>
                                        <tr>
                                            <td colspan="6" class="text-center">No record Available.</td>
                                        </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                @if($permission::checkActionPermission('view_sensor'))
                    <div class="col-md-6">
                        <div class="box box-warning">
                            <div class="box-header">
                                <h3 class="box-title">Last 5 Sensor List</h3>
                                <div class="pull-right">
                                    <a href="{{route('admin.sensor.index')}}" class="btn btn-gradient">View All</a>
                                </div>
                            </div>
                            <div class="box-body">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Sensor Name</th>
                                        <th>Company Name</th>
                                        <th>Created At</th>
                                    </tr>
                                    </thead>
                                    @if(count($dashboard['sensor_list']) > 0)
                                        <tbody>
                                        @foreach($dashboard['sensor_list'] as $key => $value)
                                            <tr>
                                                <td>{{++$key}}</td>
                                                <td>{{ucfirst($value['device_name'])}}</td>
                                                <td>{{ucfirst($value['company_name'])}}</td>
                                                <td>{{$value['created_at']}}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    @else
                                        <tfoot>
                                        <tr>
                                            <td colspan="6" class="text-center">No record Available.</td>
                                        </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection
