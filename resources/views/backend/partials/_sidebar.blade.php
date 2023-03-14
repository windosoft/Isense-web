@php
    $backend = config('constants.admin');
    $userData = auth()->user();
    $adminRole = \App\Models\Roles::$admin;

    $permission = new \App\Models\Permissions();

    $totalGroups = \App\Models\Helpers::totalGroups();
@endphp
<aside class="main-sidebar">
    <section class="sidebar">
        {{--<div class="user-panel">
            <div class="pull-left image">
                <img src="{{asset($userData->profile)}}" class="img-circle" alt="{{$userData->first_name}}">
            </div>
            <div class="pull-left info">
                <p>{{$userData->fullname}}</p>
            </div>
        </div>--}}

        <ul class="sidebar-menu" data-widget="tree">
            {{--<li class="header">MAIN NAVIGATION</li>--}}
            <li class="h-56">&nbsp;</li>
            <li class="{{(Request::is("/","profile", "404", "403", "500")) ? 'active' : ''}}">
                <a href="{{route('admin.masterdashboard')}}">
                    <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                </a>
            </li>
            @if($userData->role_id != 1)
            <li class="{{(Request::is("dashboard2")) ? 'active' : ''}}">
                <a href="{{route('admin.home')}}">
                    <i class="fa fa-dashboard"></i> <span>Real-time Dashboard</span>
                </a>
            </li>
            @endif
            @if($permission::checkActionPermission('view_company'))
                <li class="{{(Request::is("company", "company/*")) ? 'active' : ''}}">
                    <a href="{{route('admin.company.index')}}">
                        <i class="fa fa-building-o"></i> <span>Company</span>
                    </a>
                </li>
            @endif

            @if($permission::checkActionPermission('view_branch'))
                <li class="{{(Request::is("branches", "branches/*")) ? 'active' : ''}}">
                    <a href="{{route('admin.branches.index')}}">
                        <i class="fa fa-code-fork"></i> <span>Branches</span>
                    </a>
                </li>
            @endif

            @if($permission::checkActionPermission('view_gateway'))
                <li class="{{(Request::is("gateway", "gateway/*")) ? 'active' : ''}}">
                    <a href="{{route('admin.gateway.index')}}">
                        <i class="fa fa-random"></i> <span>Gateway</span>
                    </a>
                </li>
            @endif

            @if($permission::checkActionPermission('view_sensor'))
                <li class="{{(Request::is("sensor", "sensor/*")) ? 'active' : ''}}">
                    <a href="{{route('admin.sensor.index')}}">
                        <i class="fa fa-mobile"></i> <span>Sensor</span>
                    </a>
                </li>
            @endif

            @if($permission::checkActionPermission('view_group'))
                <li class="{{(Request::is("group", "group/*")) ? 'active' : ''}}">
                    <a href="{{route('admin.group.index')}}">
                        <i class="fa fa-object-group"></i> <span>Groups</span>
                        <span class="pull-right-container">
                            <span class="label pull-right">{{$totalGroups}}</span>
                        </span>
                    </a>
                </li>
            @endif

            @if($permission::checkActionPermission('view_employee'))
                <li class="{{(Request::is("employee", "employee/*")) ? 'active' : ''}}">
                    <a href="{{route('admin.employee.index')}}">
                        <i class="fa fa-user"></i> <span>Employee</span>
                    </a>
                </li>
            @endif

            @if($permission::checkActionPermission(['view_temperatures','view_humidity','view_voltage','view_offline']))
                <li class="header">Alarm Navigation</li>
                @if($permission::checkActionPermission('view_temperatures'))
                    <li class="{{(Request::is("temperatures", "temperatures/*")) ? 'active' : ''}}">
                        <a href="{{route('admin.temperatures.index')}}">
                            <i class="fa fa-thermometer"></i> <span>Temperatures</span>
                        </a>
                    </li>
                @endif

                @if($permission::checkActionPermission('view_humidity'))
                    <li class="{{(Request::is("humidity", "humidity/*")) ? 'active' : ''}}">
                        <a href="{{route('admin.humidity.index')}}">
                            <i class="fa fa-cloud"></i> <span>Humidity</span>
                        </a>
                    </li>
                @endif

                @if($permission::checkActionPermission('view_voltage'))
                    <li class="{{(Request::is("voltage", "voltage/*")) ? 'active' : ''}}">
                        <a href="{{route('admin.voltage.index')}}">
                            <i class="fa fa-plug"></i> <span>Voltage</span>
                        </a>
                    </li>
                @endif

                @if($permission::checkActionPermission('view_offline'))
                    <li class="{{(Request::is("offline", "offline/*")) ? 'active' : ''}}">
                        <a href="{{route('admin.offline.index')}}">
                            <i class="fa fa-circle-o"></i> <span>Offline</span>
                        </a>
                    </li>
                @endif
            @endif

            @if($permission::checkActionPermission(['view_message_center','view_report']))
                <li class="header">Report Navigation</li>
                @if($permission::checkActionPermission('view_message_center'))
                    <li class="{{(Request::is("notification", "notification/*")) ? 'active' : ''}}">
                        <a href="{{route('admin.notification.index')}}">
                            <i class="fa fa-bell"></i> <span>Notification</span>
                        </a>
                    </li>
                @endif

                @if($permission::checkActionPermission('view_report'))
                    <li class="{{(Request::is("report", "report/*")) ? 'active' : ''}}">
                        <a href="{{route('admin.report.index')}}">
                            <i class="fa fa-file-pdf-o"></i> <span>Report</span>
                        </a>
                    </li>
                @endif
            @endif

            @if($permission::checkActionPermission('view_schedule_update'))
                <li class="{{(Request::is("schedule", "schedule/*")) ? 'active' : ''}}">
                    <a href="{{route('admin.schedule.index')}}">
                        <i class="fa fa-calendar"></i> <span>Schedule Update</span>
                    </a>
                </li>
            @endif

            @if($userData->role_id == $adminRole)
                @if($permission::checkActionPermission(['view_roles']))
                    <li class="header">Admin Control</li>
                    @if($permission::checkActionPermission('view_roles'))
                        <li class="{{(Request::is("roles", "roles/*")) ? 'active' : ''}}">
                            <a href="{{route('admin.roles.index')}}">
                                <i class="fa fa-tasks"></i> <span>Role</span>
                            </a>
                        </li>
                    @endif
                @endif
            @endif
        </ul>
    </section>
</aside>
