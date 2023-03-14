@extends('backend.layout')

@section('styles')
    <link rel="stylesheet"
          href="{{asset('backend/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css')}}">
    <link rel="stylesheet" href="{{asset('backend/bower_components/select2/dist/css/select2.min.css')}}">
    <link rel="stylesheet"
          href="{{asset('backend/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css')}}">
@endsection

@section('scripts')
    <script src="{{asset('backend/bower_components/datatables.net/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('backend/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
    <script
        src="{{asset('backend/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
    <script src="{{asset('backend/bower_components/select2/dist/js/select2.full.min.js')}}"></script>
    <script src="{{asset('backend/dist/js/jquery.validate.min.js')}}"></script>
    <script src="{{asset('backend/dist/js/pages/report.js')}}"></script>
    <script src="{{asset('backend/dist/js/pages/custom.js')}}"></script>
    <script>
        @if(!empty($report['device']))
        $(function () {
            $('#btnSubmit').trigger('click');
        });
        @endif
    </script>
@endsection

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Report</h1>
            <ol class="breadcrumb">
                <li><a href="{{route('admin.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Report</li>
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
                            <h3 class="box-title">Sensor History</h3>
                        </div>
                        <div class="box-body">
                            {{ Form::model(null, ['route' => ['admin.report.post'], 'files' => true, 'role' => 'form', 'id'=>'frmReport', 'method'=>'post']) }}
                            <div class="row">
                                @if($report['is_company'] == false)
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            {{ Form::label('company_id','Company') }}
                                            <select name="company_id" id="company_id" onchange="getDevice(this.value)"
                                                    class="form-control select2" required>
                                                <option value="">Select Company</option>
                                                @foreach($report['company_list'] as $value)
                                                    <option
                                                        value="{{$value['id']}}">{{$value['first_name']." (". $value['email'] .")"}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('device_id','Sensor') }}
                                        <select name="device_id" id="device_id" class="form-control select2" required>
                                            <option value="">Select Sensor</option>
                                            @foreach($report['device_list'] as $value)
                                                @php
                                                    $selected = '';
                                                    if (!empty($report['device']) && $value['uuid'] == $report['device']) {
                                                        $selected = 'selected';
                                                    }
                                                @endphp
                                                <option
                                                    value="{{$value['id']}}" {{$selected}}>{{$value['device_name']}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('report_type','Report Type') }}
                                        <select name="report_type" id="report_type" class="form-control select2">
                                            @foreach($report['report_type'] as $value)
                                                <option
                                                    value="{{$value}}">{{strtoupper($value)}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('time_period','Time Period') }}
                                        <select name="time_period" id="time_period"
                                                onchange="changeTimePeriod(this.value)" class="form-control select2">
                                            @foreach($report['time_period'] as $value)
                                                <option
                                                    value="{{$value}}">{{ucwords(str_replace('_',' ',$value))}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 display-none custom-period">
                                    <div class="form-group">
                                        {{ Form::label('start_date','Start Date') }}
                                        <div class="input-group">
                                            {{ Form::text('start_date',old('start_date'), ["autocomplete"=>"off","class"=>"form-control datepicker", "placeholder"=>"Select effective start date", "id"=>"start_date","required"]) }}
                                            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3 display-none custom-period">
                                    <div class="form-group">
                                        {{ Form::label('end_date','End Date') }}
                                        <div class="input-group">
                                            {{ Form::text('end_date',old('end_date'), ["autocomplete"=>"off","class"=>"form-control datepicker", "placeholder"=>"Select end date", "id"=>"end_date","required"]) }}
                                            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group m-t-25">
                                        <button type="submit" id="btnSubmit"
                                                data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> loading"
                                                class="btn btn-gradient">Submit
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert-danger"></div>
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>

                <div id="device_report" class="col-xs-12 display-none">
                    <div class="box box-warning">

                        <div class="box-body">
                            <div class="row">
                                <div class="col-xs-6 text-right">
                                    {{ Form::model(null, ['route' => ['admin.report.download.csv'], 'target'=>"_blank", 'files' => true, 'role' => 'form', 'id'=>'frmReportCSV', 'method'=>'post']) }}
                                    <input type="hidden" class="hidden_device_id" name="device_id">
                                    <input type="hidden" class="hidden_report_type" name="report_type">
                                    <input type="hidden" class="hidden_time_period" name="time_period">
                                    <input type="hidden" class="hidden_start_date" name="start_date">
                                    <input type="hidden" class="hidden_end_date" name="end_date">
                                    <button type="submit" id="btnDownloadCSV"
                                            data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> loading"
                                            class="btn btn-gradient">
                                        <i class="fa fa-download"></i> Download CSV
                                    </button>
                                    {{ Form::close() }}
                                </div>
                                <div class="col-xs-6 text-left">
                                    {{ Form::model(null, ['route' => ['admin.report.download.pdf'], 'target'=>"_blank", 'files' => true, 'role' => 'form', 'id'=>'frmReportPDF', 'method'=>'post']) }}
                                    <input type="hidden" class="hidden_device_id" name="device_id">
                                    <input type="hidden" class="hidden_report_type" name="report_type">
                                    <input type="hidden" class="hidden_time_period" name="time_period">
                                    <input type="hidden" class="hidden_start_date" name="start_date">
                                    <input type="hidden" class="hidden_end_date" name="end_date">
                                    <button type="submit" id="btnDownloadPDF"
                                            data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> loading"
                                            class="btn btn-gradient">
                                        <i class="fa fa-download"></i> Download Pdf
                                    </button>
                                    {{ Form::close() }}
                                </div>
                                <div class="col-md-12">
                                    <hr class="gray">
                                    <h3>
                                        <i class="fa fa-mobile"></i> <span id="device_name"></span>
                                        <span id="current_datetime" class="pull-right"></span>
                                    </h3>
                                    <hr class="gray">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <h3>Sensor Information</h3>
                                    <div class="report-box">
                                        <p>
                                            <span class="strong">Sensor Name : </span>
                                            <span id="report_device_name"></span>
                                        </p>
                                        <p>
                                            <span class="strong">IMEI/SN/DevEui : </span>
                                            <span id="report_device_imei"></span>
                                        </p>
                                        <p>
                                            <span class="strong">Type of facility : </span>
                                            <span id="report_device_facility"></span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <h3>Sensor Configuration</h3>
                                    <div class="report-box">
                                        <p>
                                            <span class="strong">Sensor Data Interval : </span>
                                            <span id="report_device_interval"></span>
                                        </p>
                                        <p>
                                            <span class="strong">Associated Alarm Scheme : </span>
                                            <span id="report_device_alarm_count"></span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <h3>Statistics</h3>
                                    <div class="report-box">
                                        <p>
                                            <span class="strong">Start Date : </span>
                                            <span id="report_start_date"></span>
                                        </p>
                                        <p>
                                            <span class="strong">End Date : </span>
                                            <span id="report_end_date"></span>
                                        </p>
                                        <p>
                                            <span class="strong">Total Device Log : </span>
                                            <span id="report_device_log_count"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <h3>Alarm information</h3>
                                    <table id="device-alarm-list" class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Alarm Name</th>
                                            <th>Alarm Type</th>
                                            <th>Time</th>
                                            <th>Date</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <h3>Sensor Log List</h3>
                                    <table id="report-device-log" class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Temperature</th>
                                            <th>Humidity</th>
                                            <th>Date Time</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
