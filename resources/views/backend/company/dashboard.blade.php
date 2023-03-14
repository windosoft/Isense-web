@php
    $fullName = auth()->user()->fullname;
@endphp
@extends('backend.layout')

@section('styles')
    <link rel="stylesheet" href="{{asset('backend/bower_components/select2/dist/css/select2.min.css')}}">
@endsection

@section('scripts')
    <script src="{{asset('backend/bower_components/select2/dist/js/select2.full.min.js')}}"></script>
    <script src="{{asset('backend/dist/js/jquery.sparkline.min.js')}}"></script>
    <script>
        function playRing() {
            //$('#myAudio').get(0).play();
        }

        loadRealTimeData('');

        function loadRealTimeData(device = '') {
            $('#loader').show();
            $('#real_time_data').html('');
            $.get('{{route('admin.dashboard.real-time-data')}}', {device: device}, function (response) {
                if (response.status == 200) {

                    var isPlay = localStorage.getItem('i-sense-notification');
                    if (response.is_music_play == 1 && isPlay == 1) {
                        playRing();
                    }
                }
                $('#loader').hide();
                $('#real_time_data').html(response.html);
                $(".sparkline").each(function () {
                    var $this = $(this);
                    $this.sparkline('html', $this.data());
                });
            }).fail(function (error) {
                $('#real_time_data').html('<div class="text-center col-md-12"><h3 class="text-danger">Ooops...Something went wrong. Please contact to support team.</h3></div>');
            });
        }

        $(function () {
            $('.select2').select2();
            setInterval(function () {
                loadRealTimeData('');
            }, 120000);

            var notificationCheck = localStorage.getItem('i-sense-notification');
            if (notificationCheck == null) {
                localStorage.setItem('i-sense-notification', 1);
            }

            if (notificationCheck == 0) {
                $('#notification_sound').attr('checked', false);
            }
        });

        function changeNotifySound() {
            if ($('#notification_sound').prop('checked') == true) {
                localStorage.setItem('i-sense-notification', 1);
            } else {
                localStorage.setItem('i-sense-notification', 0);
                $('#myAudio').get(0).pause();
            }
        }

    </script>
@endsection

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            {{--<h1>Welcome {{$fullName}}!</h1>--}}
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Dashboard</li>
            </ol>
        </section>

        <section class="content company-dashboard">
            <div id="real_time_data" class="row">
                {{--<div class="col-md-12">
                    <div class="box box-warning">
                        <div class="box-header">
                            <div class="col-md-3 col-md-offset-4">
                                <div class="form-group">
                                    <select id="device_id" onchange="loadRealTimeData(this.value)" name="device_id"
                                            class="form-control select2">
                                        <option value="all">All</option>
                                        @foreach($dashboard['device_list'] as $value)
                                            <option value="{{$value['id']}}">{{$value['device_name']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div id="notification-ring">
                                    <div class="pull-right">
                                        <div class="notify-sound">
                                            <span class="text">Notification Sound</span>
                                            <label class="switch">
                                                <input type="checkbox" onchange="changeNotifySound()"
                                                       id="notification_sound" checked>
                                                <span class="slider round"></span>
                                            </label>
                                        </div>
                                        <a href="{{route('admin.notification.index')}}"
                                           class="btn btn-default notify-bell">
                                            <img src="{{asset('backend/images/bell.png')}}" alt="bell">
                                            <span class="notify-count">{{$dashboard['notification_count']}}</span>
                                        </a>
                                        <audio controls id="myAudio" class="display-none">
                                            <source src="{{asset('backend/images/notification.wav')}}" type="audio/wav">
                                        </audio>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div id="loader" class="col-md-12 text-center display-none1">
                                    <img src="{{asset('backend/images/loader.gif')}}" alt="loader">
                                </div>
                                <div id="real_time_data" class="real-data"></div>
                            </div>
                        </div>
                    </div>
                </div>--}}
            </div>
        </section>
    </div>
@endsection
