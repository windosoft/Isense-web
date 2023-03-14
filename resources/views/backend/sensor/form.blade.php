@if(isset($sensorData))
    {{ Form::model($sensorData, ['route' => ['admin.sensor.update', $sensorData->uuid], 'files' => true, 'role' => 'form', 'id'=>'frmSensor', 'method'=>'put']) }}
@else
    {{ Form::model(null, ['route' => ['admin.sensor.store'], 'files' => true, 'role' => 'form', 'id'=>'frmSensor', 'method'=>'post']) }}
@endif
<div class="box-body">
    <div class="row">
        <div class="col-md-3">
            <div class="form-group required">
                {{ Form::label('device_name','Sensor Name') }}
                {{ Form::text('device_name',old('device_name'), ["class"=>"form-control", "placeholder"=>"Enter sensor name", "id"=>"device_name","required"]) }}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group required">
                {{ Form::label('device_sn','IMEI/SN/DevEui') }}
                {{ Form::text('device_sn',old('device_sn'), ["class"=>"form-control", "placeholder"=>"Enter sensor serial no.", "id"=>"device_sn","required"]) }}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group required">
                {{ Form::label('device_password','Password') }}
                {{ Form::text('device_password',old('device_password'), ["class"=>"form-control", "placeholder"=>"Enter device password", "id"=>"device_password","required"]) }}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group required">
                {{ Form::label('data_interval','Data Interval') }}
                {{ Form::number('data_interval',old('data_interval'), ["class"=>"form-control", "placeholder"=>"Enter data interval", "id"=>"data_interval","required"]) }}
            </div>
        </div>
    </div>

    <div class="row">
        @if (isset($sensorData) && $isCompany == false)
            <div class="col-md-3">
                <div class="form-group required">
                    {{ Form::label('company_id','Company') }}
                    <input type="text" class="form-control" value="{{$sensorData->company_name}}" readonly>
                </div>
            </div>
        @else
            @if ($isCompany == false)
                <div class="col-md-3">
                    <div class="form-group required">
                        {{ Form::label('company_id','Company') }}
                        <select name="company_id" id="company_id" onchange="getBranch(this.value)"
                                class="form-control select2" required>
                            <option value="">Select Company</option>
                            @foreach($companyList as $value)
                                <option
                                    value="{{$value['id']}}">{{$value['first_name']." (". $value['email'] .")"}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif
        @endif

        <div class="col-md-3">
            <div class="form-group required">
                {{ Form::label('branch_id','Branch') }}
                @if(isset($sensorData))
                    <input type="text" class="form-control" value="{{$sensorData->branch_name}}" readonly/>
                @else
                    <select name="branch_id" id="branch_id" onchange="getGateway(this.value)"
                            class="form-control select2" required>
                        <option value="">Select Branch</option>
                        @foreach($branchList as $value)
                            @php
                                $selected = '';
                                if (isset($sensorData)) {
                                    if ($sensorData->branch_id == $value['id']) {
                                        $selected = 'selected';
                                    }
                                }
                            @endphp
                            <option value="{{$value['id']}}" {{$selected}}>{{$value['name']}}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group required">
                {{ Form::label('terminal_id','Gateway') }}
                @if(isset($sensorData))
                    <input type="text" class="form-control" value="{{$sensorData->gateway_name}}" readonly/>
                @else
                    <select name="terminal_id" id="terminal_id" class="form-control select2" required>
                        <option value="">Select Gateway</option>
                    </select>
                @endif
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group required">
                {{ Form::label('type_of_facility','Type Of Facility') }}
                <select name="type_of_facility" id="type_of_facility" class="form-control" required>
                    <option value="">Select Type of facility</option>
                    @foreach($typeOfFacility as $value)
                        @php
                            $selected = '';
                            if (isset($sensorData)) {
                                if ($sensorData->type_of_facility == $value) {
                                    $selected = 'selected';
                                }
                            }
                        @endphp
                        <option value="{{$value}}" {{$selected}}>{{$value}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group required">
                {{ Form::label('temp_adjustment','Temperature Adjustment') }}
                {{ Form::number('temp_adjustment',old('temp_adjustment'), ["class"=>"form-control", "placeholder"=>"Enter Adjustment Temperature", "id"=>"data_interval"]) }}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group required">
                {{ Form::label('humidity_adjustment','Humidity Adjustment') }}
               {{ Form::number('humidity_adjustment',old('humidity_adjustment'), ["class"=>"form-control", "placeholder"=>"Enter Humidity Temperature", "id"=>"data_interval"]) }}
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="alert alert-success"></div>
        <div class="alert alert-danger"></div>
    </div>
</div>
<div class="box-footer">
    <button type="submit" id="btnSubmit"
            data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> loading"
            class="btn btn-gradient pull-right">Submit
    </button>
</div>
{{ Form::close() }}
