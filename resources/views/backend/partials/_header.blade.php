@php
    $userData = auth()->user();
    $permission = new \App\Models\Permissions();
    $todayNotification = \App\Models\Helpers::todayNotification();
@endphp
<header class="main-header">
    <a href="javascript:void(0);" class="logo">
        <span class="logo-mini text-center">
            <img src="{{asset('backend/images/mini-logo-menu.png')}}" alt="{{env('APP_NAME')}}">
        </span>
        <span class="logo-lg">
            <img src="{{asset('backend/images/logo-menu.png')}}" alt="{{env('APP_NAME')}}">
        </span>
    </a>
    <nav class="navbar navbar-static-top">
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav header-menu">

                <?php
                $cookieName = Cookie::get('theme-selected');
                ?>
                @if($cookieName == 'lightMode' || $cookieName == '')
                    <li class="notifications-menu">
                        <a href="{{route('admin.changeTheme','darkMode')}}" class="NightOption">
                            <i class="fa fa-moon-o" aria-hidden="true"></i>
                        </a>
                    </li>
                @else
                    <li class="notifications-menu darkBtnBox">
                        <a href="{{route('admin.changeTheme','lightMode')}}" class="dayOption">
                            <i class="fa fa-sun-o" aria-hidden="true"></i>
                        </a>
                    </li>
                @endif

                @if($permission::checkActionPermission('view_message_center'))
                    <li class="notifications-menu">

                        <?php
                        $cookieName = Cookie::get('theme-selected');
                        ?>
                        @if($cookieName == 'lightMode' || $cookieName == '')
                            <a href="{{route('admin.notification.index')}}">
                                @if($todayNotification > 0)
                                    <img src="{{asset('backend/images/notification-on.png')}}" alt="notification"/>
                                @else
                                    <img src="{{asset('backend/images/notification-off.png')}}" alt="notification"/>
                                @endif
                            </a>
                        @else
                            <a href="{{route('admin.notification.index')}}">
                                @if($todayNotification > 0)
                                    <img src="{{asset('backend/images/dark-notification-on.png')}}" alt="notification"/>
                                @else
                                    <img src="{{asset('backend/images/dark-notification-off.png')}}"
                                         alt="notification"/>
                                @endif
                            </a>
                        @endif
                    </li>
                @endif
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="{{asset($userData->profile)}}" class="user-image" alt="{{$userData->fullname}}">
                        <span class="hidden-xs">{{$userData->fullname}}</span>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header">
                            <img src="{{asset($userData->profile)}}" class="img-circle" alt="User Image">
                            <p>{{$userData->fullname}}</p>
                        </li>
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="{{route('admin.profile')}}" class="btn btn-white btn-flat">Profile</a>
                            </div>
                            <div class="pull-right">
                                <a href="{{route('admin.logout')}}" class="btn btn-white btn-flat">Sign out</a>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>
