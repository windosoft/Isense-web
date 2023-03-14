@php
    $fullName = auth()->user()->fullname;
@endphp
@extends('backend.layout')

@section('styles')
    <link rel="stylesheet"
          href="{{asset('backend/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('backend/bower_components/select2/dist/css/select2.min.css')}}">
    <link rel="stylesheet"
          href="<?php echo e(asset('backend/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css')); ?>">
    <style>
        .table-condensed {
            width: 260px;
        }

    </style>
@endsection

@section('scripts')
    <script src="{{asset('backend/bower_components/select2/dist/js/select2.full.min.js')}}"></script>
    <script src="{{asset('backend/dist/js/jquery.sparkline.min.js')}}"></script>
    <script src="{{asset('backend/dist/js/Chart.min.js')}}"></script>
    <script src="{{asset('backend/bower_components/datatables.net/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('backend/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
    <script
        src="{{asset('backend/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
    {{--<script src="{{asset('backend/dist/js/canvasjs.min.js')}}"></script>--}}
    <script src="{{asset('backend/dist/js/custom.js')}}"></script>
    <script>
        $(document).ready(function () {
            renderDoughnotChart();
            renderLineChart();
            renderLiveTempData();
            renderNotifications();
        });
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

        <section class="content">
            <div id="dashboard2" class="row">
                <div class="col-md-4">
                    <div class="box">
                        <div class="box-header" style="padding-bottom: 0px;">
                            <h2>Total Sensors <span id="totalSensor">{{$totalSensor}}</span></h2>
                        </div>
                        <div class="box-body" style="padding-top: 0px;">
                            <div id="canvas-holder" style="width:100%;text-align: center;margin: 0 auto">
                                <canvas id="chart-area" width="100%" height="58%"></canvas>
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-md-12 infoDots">
                                <div class="normalClass"><span class="normalDot"></span> Normal</div>
                                <div class="dangerClass"><span class="dangerDot"></span> Danger</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="box-Blocker" style="display: none;"><img src="{{asset('backend/images/ringload.gif')}}"
                                                                         alt=""></div>
                    <div class="box">
                        <div class="box-header">
                            <div id="historyInfoHeader">
                                <h4><b>History</b></h4>
                                <span class="currentSensorNote">Currently running <span id="currentRunningSensor">{{$totalSensor}} Sensors</span></span>
                            </div>
                            <div id="historyTempInfoHeader">
                                <?php
                                $cookieName = Cookie::get('theme-selected');
                                ?>
                                @if($cookieName == 'lightMode' || $cookieName == '')
                                    <div class="historyTempInfoBlock">
                                        <img src="{{asset('backend/images/temperatures.png')}}"
                                             class="img-responsive historyTempInfoBlockImg" alt="">
                                        <span class="normalDot" style="margin-left: 8px;"></span>
                                    </div>
                                    <div class="historyTempInfoBlock">
                                        <img src="{{asset('backend/images/humidity.png')}}"
                                             class="img-responsive humidityHistoryIcon" alt="">
                                        <span class="humidityDot" style="margin-left: 8px;"></span>
                                    </div>
                                @else
                                    <div class="historyTempInfoBlock">
                                        <img src="{{asset('backend/images/temperatures-white.png')}}"
                                             class="img-responsive historyTempInfoBlockImg" alt="">
                                        <span class="normalDot" style="margin-left: 8px;"></span>
                                    </div>
                                    <div class="historyTempInfoBlock">
                                        <img src="{{asset('backend/images/humidity-white.png')}}"
                                             class="img-responsive humidityHistoryIcon" alt="">
                                        <span class="humidityDot" style="margin-left: 8px;"></span>
                                    </div>
                                @endif
                            </div>
                            <div id="historyFilters">
                                <div id="historyFilterMachineBox">
                                    @if($deviceDashboardList)
                                        @if(count($deviceDashboardList) > 0)
                                            <div class="dropdown historyFilterMachine">
                                                <button class="btn btn-primary dropdown-toggle" type="button"
                                                        data-toggle="dropdown"><span
                                                        id="selectedMachine">{{$deviceDashboardList[0]['device_name']}}</span>
                                                    <span class="caret"></span></button>
                                                <ul class="dropdown-menu">
                                                    @for($i=0;$i<count($deviceDashboardList);$i++)
                                                        <?php
                                                        $classNameActive = '';
                                                        if ($i == '0') {
                                                            $classNameActive = 'activeMachines';
                                                        }
                                                        ?>
                                                        <li><a href="#" class="{{$classNameActive}} machineFilterList"
                                                               data-id="{{$deviceDashboardList[$i]['device_id']}}">{{$deviceDashboardList[$i]['device_name']}}</a>
                                                        </li>
                                                    @endfor
                                                </ul>
                                            </div>

                                            <div class="dropdown historyFilterDates">
                                                <button class="btn btn-primary dropdown-toggle" type="button"
                                                        data-toggle="dropdown"><span id="selectedDate"><i
                                                            class="fa fa-calendar"></i>&nbsp;&nbsp; Today</span>
                                                    <span class="caret"></span></button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="#" class="activeDate dateFilterList" data-id="today">Today</a>
                                                    </li>
                                                    <li><a href="#" class="dateFilterList" data-id="week">Week</a></li>
                                                    <li><a href="#" class="dateFilterList" data-id="month">Month</a>
                                                    </li>
                                                    <li><a href="#" class="dateFilterList" data-id="year">Year</a></li>
                                                </ul>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="box-body">
                            <div id="drawLineChart">
                                <div id="canvas-holder">
                                    <canvas id="line-area" height="90px"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="clearfix"></div>

            <div id="dashboard2" class="row">
                <div class="col-md-8">
                    <div class="box">
                        <div class="box-header">
                            <div id="LiveInfoHeader">
                                <h4><b>Live</b></h4>
                            </div>
                            <div id="liveTempInfoHeader">
                                <div class="historyTempInfoBlock">
                                    <span class="normalDot" style="margin-left: 8px;"></span>
                                    Normal
                                </div>
                                <div class="historyTempInfoBlock">
                                    <span class="humidityDot" style="margin-left: 8px;"></span>
                                    Caution
                                </div>
                                <div class="historyTempInfoBlock">
                                    <span class="dangerDot" style="margin-left: 8px;"></span>
                                    Danger
                                </div>
                                <div class="historyTempInfoBlock">
                                    <span class="inactiveDot" style="margin-left: 8px;"></span>
                                    Inactive
                                </div>
                            </div>
                            <div id="liveFilterHeader">
                                <div class="dropdown liveFilterDates">
                                    <button class="btn btn-primary dropdown-toggle" onclick="openLiveFilter()"
                                            type="button"
                                            data-toggle="dropdown">Filter
                                        <i class="fa fa-search" aria-hidden="true"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive" id="putLiveTable">

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="box">
                        <div class="box-header">
                            <div id="LiveInfoHeader">
                                <h4 style="margin-left: 15px;"><b>Alerts</b></h4>
                            </div>
                            <span class="fa fa-bell-o pull-right bellIcons"></span>
                        </div>
                        <div class="box-body">
                            <div id="putAlertsHere">

                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="modal fade" id="liveFilterModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel15"><i class="fa fa-search"></i> Filter</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="" class="form-label">Date</label>
                                <div class="input-group">
                                    <input autocomplete="off" class="form-control form-control-sm"
                                           placeholder="Select Date" id="liveFilterDate" required=""
                                           name="liveFilterDate" type="text">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-gradient" id="btnSubmit" onclick="renderLiveTempData()"><i
                            class="fa fa-search"></i> Filter
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
