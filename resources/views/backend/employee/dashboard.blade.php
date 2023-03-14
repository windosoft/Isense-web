@php
    $permission = new \App\Models\Permissions();
@endphp
@extends('backend.layout')

@section('styles')
@endsection

@section('scripts')
    <script src="{{asset('backend/dist/js/Chart.js')}}"></script>
    <script>
        var pieChartCanvas = $('#sensorSummaryChart').get(0).getContext('2d');
        var pieChart = new Chart(pieChartCanvas)
        var PieData = [
                @foreach($dashboard['color_list'] as $value)
            {
                value: parseInt('{{$value['count']}}'),
                color: '{{$value['color']}}',
                highlight: '{{$value['color']}}',
                label: '{{$value['name']}}'
            },
            @endforeach
        ];
        var pieOptions = {
            //Boolean - Whether we should show a stroke on each segment
            segmentShowStroke: true,
            //String - The colour of each segment stroke
            segmentStrokeColor: '#fff',
            //Number - The width of each segment stroke
            segmentStrokeWidth: 2,
            //Number - The percentage of the chart that we cut out of the middle
            percentageInnerCutout: 50, // This is 0 for Pie charts
            //Number - Amount of animation steps
            animationSteps: 100,
            //String - Animation easing effect
            animationEasing: 'easeOutBounce',
            //Boolean - Whether we animate the rotation of the Doughnut
            animateRotate: true,
            //Boolean - Whether we animate scaling the Doughnut from the centre
            animateScale: false,
            //Boolean - whether to make the chart responsive to window resizing
            responsive: true,
            // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
            maintainAspectRatio: true,
            //String - A legend template
            legendTemplate: '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<segments.length; i++){%><li><span style="background-color:<%=segments[i].fillColor%>"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>'
        };
        //Create pie or douhnut chart
        // You can switch between pie and douhnut using the method below.
        pieChart.Doughnut(PieData, pieOptions);

        var areaChartCanvas = $('#sensorHistory').get(0).getContext('2d');
        var areaChart = new Chart(areaChartCanvas);
        var areaChartData = {
            labels: ['12am', '2am', '4am', '6am', '8am', '10am', '12pm', '2pm', '4pm', '6pm', '8pm', '10pm'],
            datasets: [
                {
                    label: 'Temperatures',
                    fillColor: '#8bc55f',
                    strokeColor: '#4caf50',
                    pointColor: '#4caf50',
                    pointStrokeColor: 'rgba(60,141,188,1)',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: 'rgba(60,141,188,1)',
                    data: []
                }
            ]
        }

        var areaChartOptions = {
            //Boolean - If we should show the scale at all
            showScale: true,
            //Boolean - Whether grid lines are shown across the chart
            scaleShowGridLines: false,
            //String - Colour of the grid lines
            scaleGridLineColor: 'rgba(0,0,0,.05)',
            //Number - Width of the grid lines
            scaleGridLineWidth: 1,
            //Boolean - Whether to show horizontal lines (except X axis)
            scaleShowHorizontalLines: true,
            //Boolean - Whether to show vertical lines (except Y axis)
            scaleShowVerticalLines: true,
            //Boolean - Whether the line is curved between points
            bezierCurve: true,
            //Number - Tension of the bezier curve between points
            bezierCurveTension: 0.3,
            //Boolean - Whether to show a dot for each point
            pointDot: true,
            //Number - Radius of each point dot in pixels
            pointDotRadius: 4,
            //Number - Pixel width of point dot stroke
            pointDotStrokeWidth: 1,
            //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
            pointHitDetectionRadius: 20,
            //Boolean - Whether to show a stroke for datasets
            datasetStroke: true,
            //Number - Pixel width of dataset stroke
            datasetStrokeWidth: 2,
            //Boolean - Whether to fill the dataset with a color
            datasetFill: true,
            //String - A legend template
            legendTemplate: '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].lineColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
            //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
            maintainAspectRatio: true,
            //Boolean - whether to make the chart responsive to window resizing
            responsive: true
        }
        areaChart.Line(areaChartData, areaChartOptions);

        updateSensorHistory();
        function updateSensorHistory() {
            var sensor = $('#history_sensor').val();
            var day = $('#history_day').val();
            var isTemp = true;
            var labelName = 'Temperature';
            if ($('#history_switch').prop('checked') == true) {
                isTemp = false;
                labelName = 'Humidity';
            }
            areaChartData.datasets[0].label = labelName;
            $('#sensorHistoryOverlay').show();
            $.post(adminUrl + '/dashboard/sensor-history', {
                _token: '{{csrf_token()}}',
                sensor: sensor,
                day: day,
                is_temp: isTemp
            }, function (response) {
                areaChartData.datasets[0].data = response.data;
                areaChart.Line(areaChartData, areaChartOptions).update();
                $('#sensorHistoryOverlay').hide();
            }).fail(function (error) {
                areaChartData.datasets[0].data = [];
                areaChart.Line(areaChartData, areaChartOptions).update();
                $('#sensorHistoryOverlay').hide();
            });
        }
    </script>
@endsection

@section('content')
    <div class="content-wrapper emp-dashboard">
        <section class="content-header">
            <h1>Welcome {{$dashboard['full_name']}}!</h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Dashboard</li>
            </ol>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-md-5">
                    <div class="box sensor-summary">
                        <div class="box-header">
                            <h3 class="box-title">Sensors Summary</h3>
                            <div class="box-tools pull-right">
                                <h3 class="box-title">{{(strlen(count($dashboard['sensor_list'])) == 1) ? '0'.count($dashboard['sensor_list']) : count($dashboard['sensor_list'])}}</h3>
                                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="box-body">
                            <canvas id="sensorSummaryChart" style="height:250px;margin-bottom: 36px;"></canvas>
                            <ul>
                                @foreach($dashboard['color_list'] as $value)
                                    <li>
                                        <span class="sensor-status-color"
                                              style="background-color: {{$value['color']}};"></span>
                                        <span class="sensor-status-name">{{$value['name']}}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="box sensor-history">
                        <div class="box-header">
                            <h3 class="box-title">History</h3>
                            <div class="box-tools pull-right">
                                <div class="alarm-switch">
                                    <img src="{{asset('backend/images/temperatures.png')}}" alt="">
                                    <label class="switch">
                                        <input type="checkbox" id="history_switch" onchange="updateSensorHistory()">
                                        <span class="slider round"></span>
                                    </label>
                                    <img class="humidity" src="{{asset('backend/images/humidity.png')}}" alt="">
                                </div>
                                <select id="history_sensor" onchange="updateSensorHistory()" class="form-control">
                                    @foreach($dashboard['sensor_list'] as $value)
                                        <option value="{{$value['id']}}">{{$value['device_name']}}</option>
                                    @endforeach
                                </select>
                                <select id="history_day" onchange="updateSensorHistory()" class="form-control">
                                    <option value="today">Today</option>
                                    <option value="yesterday">Yesterday</option>
                                </select>
                                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>
                            <h4>Currently Running <span>{{(strlen(count($dashboard['sensor_list'])) == 1) ? '0'.count($dashboard['sensor_list']) : count($dashboard['sensor_list'])}} Sensors</span>
                            </h4>
                        </div>
                        <div class="box-body">
                            <canvas id="sensorHistory" style="height:250px"></canvas>
                        </div>
                        <div id="sensorHistoryOverlay" class="overlay display-none">
                            <i class="fa fa-refresh fa-spin"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-7">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Sensors Summary</h3>
                            <div class="box-tools pull-right">
                                @foreach($dashboard['color_list'] as $value)
                                    <span class="sensor-status-color"
                                          style="background-color: {{$value['color']}};"></span>
                                    <span class="sensor-status-name">{{$value['name']}}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="sensor-list box-body">
                            <div class="table-responsive">
                                <table class="table no-margin">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Room Name</th>
                                        <th>Date/Time</th>
                                        <th>Temperature</th>
                                        <th>Status</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($dashboard['sensor_list'] as $key => $value)
                                        <tr>
                                            <td>{{++$key}}</td>
                                            <td>{{$value['device_name']}}</td>
                                            <td>{{$value['created_at']}}</td>
                                            <td>
                                                @if(!empty($value['temperature']))
                                                    <img src="{{asset('backend/images/temperatures.png')}}">
                                                    <span>{{$value['temperature']}} ℃</span>
                                                @endif
                                                @if(!empty($value['humidity']))
                                                    <img class="humidity"
                                                         src="{{asset('backend/images/humidity.png')}}">
                                                    <span>{{$value['humidity']}} %</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="status"
                                                      style="background-color: {{$value['temperature_color']}};"></span>
                                                <span class="status_name">{{$value['status_name']}}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Updates</h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="box-body">
                            <ul class="products-list product-list-in-box">
                                @foreach($dashboard['notification_list'] as $value)
                                    <li class="item">{{$value['notification']}}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
