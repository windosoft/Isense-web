<html>
<head>
    <title>{{$device_detail['device_name'] ." | ". env('APP_NAME')}}</title>
    <style id="__web-inspector-hide-shortcut-style__" type="text/css">
        @media only screen and (max-width: 600px) {
            body {
                float: left;
            }

            .font18 {
                font-size: 18px;
            }

            #responsive table tr td {
                font-size: 18px !important;
            }
        }

        .hide {
            display: none;
        }
    </style>
</head>
<body>
<table width="100%">
    <tr>
        <td>
            <img src="{{ asset('backend/images/logo.png') }}" alt="{{env('APP_NAME')}}" height="70px" width="200px">
        </td>
        <td align="right">
            <small>Date: {{$current_datetime}}</small>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
    </tr>

    <tr>
        <td>
            <h3>{{$device_detail['device_name']}}</h3>
        </td>
    </tr>

</table>
<br>
<hr>
<table width="100%" style="max-width:800px;padding: 5px;margin:0px auto;font-family:Gotham;" border="0">
    <tr>
        <td><h5><strong>Device Information</strong></h5></td>
        <td><h5><strong>Device Configuration</strong></h5></td>
        <td><h5><strong>Statistics</strong></h5></td>
    </tr>
    <tr>
        <td>
            <small><b>Device Name :</b> {{$device_detail['device_name']}}</small>
            <br>
            <small><b>IMEI/SN/DevEui :</b> {{$device_detail['device_sn']}}</small>
            <br>
            <small><b>Type of facility :</b> {{$device_detail['type_of_facility']}}</small>
        </td>
        <td>
            <small><b>Device Data Interval :</b> {{$device_detail['data_interval']}}Minutes</small>
            <br>
            <small><b>Associated alarm scheme : </b> {{count($alarm_list)}}</small>
            <br>
        </td>
        <td>
            <small><b>Start Time : </b> {{$start_date}}</small>
            <br>
            <small><b>End Time : </b> {{$end_date}}</small>
            <br>
            <small><b>Data Count :</b> {{count($device_log_list)}}</small>
        </td>
    </tr>
</table>
<hr>
<h4>Alarm information</h4>
<hr>
<table width="100%" style="max-width:800px;padding: 5px;margin:0px auto;font-family:Gotham;" border="0">
    <tr>
        <td width='10%'><h5><strong>#&nbsp;&nbsp;</strong></h5></td>
        <td width='30%'><h5><strong>Program Name</strong></h5></td>
        <td width='30%'><h5><strong>Alarm Type</strong></h5></td>
        <td width='30%'><h5><strong>Time</strong></h5></td>
        <td width='30%'><h5><strong>Date</strong></h5></td>
    </tr>
    @if(count($alarm_list) > 0)
        @foreach($alarm_list as $key => $value)
            <tr>
                <td>{{++$key}}</td>
                <td>{{$value['name']}}</td>
                <td>{{$value['type']}}</td>
                <td>{{$value['time']}}</td>
                <td>{{$value['date']}}</td>
            </tr>
        @endforeach
    @else
        <tr>
            <td colspan="5" style="text-align: center;">No alarm available!</td>
        </tr>
    @endif
</table>
<hr>
<h4>Sensor Log List</h4>
<hr>
<table width="100%" border="1" cellspacing="0px" cellpadding="5px">
    <tr>
        <td width='10%'><h5><strong>#</strong></h5></td>
        <td width='30%'><h5><strong>Temperature</strong></h5></td>
        <td width='30%'><h5><strong>Humidity</strong></h5></td>
        <td width='30%'><h5><strong>Time</strong></h5></td>
    </tr>
    @if(count($device_log_list) > 0)
        @foreach($device_log_list as $key => $value)
            <tr>
                <td>{{++$key}}</td>
                <td style="color:{{($value['device_color']) ? $value['device_color'] : '#000000' }};">
                    {{$value['temperature']}}℃</td>
                <td style="color:{{($value['device_color']) ? $value['device_color'] : '#000000' }};">
                    {{$value['humidity']}}%</td>
                <td>{{$value['created_at']}}</td>
            </tr>
        @endforeach
    @else
        <tr>
            <td colspan="4" style="text-align: center;">No device log found</td>
        </tr>
    @endif
</table>

</body>
</html>
