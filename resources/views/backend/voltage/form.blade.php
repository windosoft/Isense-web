@php
    $required = 'required';
@endphp
@if(isset($voltageData))
    @php
        $required = '';
    @endphp
    {{ Form::model($voltageData, ['route' => ['admin.voltage.update', $voltageData->uuid], 'files' => true, 'role' => 'form', 'id'=>'frmVoltage', 'method'=>'put']) }}
@else
    {{ Form::model(null, ['route' => ['admin.voltage.store'], 'files' => true, 'role' => 'form', 'id'=>'frmVoltage', 'method'=>'post']) }}
@endif
<div class="box-body">
    <div class="row">
        @if (isset($voltageData) && $isCompany == false)
            <div class="col-md-4">
                <div class="form-group required">
                    {{ Form::label('company_id','Company') }}
                    <input type="text" class="form-control" value="{{$voltageData->company_name}}" readonly>
                </div>
            </div>
        @else
            @if ($isCompany == false)
                <div class="col-md-4">
                    <div class="form-group required">
                        {{ Form::label('company_id','Company') }}
                        <select name="company_id" id="company_id" onchange="getDevice(this.value)"
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
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('device_id','Sensor') }}
                <select name="device_id" id="device_id" class="form-control select2" required>
                    <option value="">Select Sensor</option>
                    @foreach($deviceList as $value)
                        @php
                            $selected = '';
                            if (isset($voltageData)) {
                                if ($voltageData->device_id == $value['id']) {
                                    $selected = 'selected';
                                }
                            }
                        @endphp
                        <option
                            value="{{$value['id']}}" {{$selected}}>{{$value['device_name']}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('name','Voltage Name') }}
                {{ Form::text('name',old('name'), ["class"=>"form-control", "placeholder"=>"Enter name", "id"=>"name","required"]) }}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('low_voltage_value','Low Voltage Value') }}
                <div class="input-group">
                    {{ Form::number('low_voltage_value',old('low_voltage_value'), ["class"=>"form-control", "placeholder"=>"Enter low voltage", "id"=>"low_voltage_value","required"]) }}
                    <div class="input-group-addon"><strong>V</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('effective_start_date','Effective Start Date') }}
                <div class="input-group">
                    {{ Form::text('effective_start_date',old('effective_start_date'), ["autocomplete"=>"off","class"=>"form-control datepicker", "placeholder"=>"Select effective start date", "id"=>"effective_start_date","required"]) }}
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('effective_end_date','Effective End Date') }}
                <div class="input-group">
                    {{ Form::text('effective_end_date',old('effective_end_date'), ["autocomplete"=>"off","class"=>"form-control datepicker", "placeholder"=>"Select effective end date", "id"=>"effective_end_date","required"]) }}
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group m-t-25">
                <label>
                    @php
                        $checkDate = '';
                        if (isset($voltageData)) {
                            if ($voltageData->effective_date_enable == 1) {
                                $checkDate = 'checked';
                            }
                        }
                    @endphp
                    <input type="checkbox" name="effective_date_enable" {{$checkDate}} class="flat-red"/>
                    Is Effective Date Enable?
                </label>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group m-t-25">
                <label for="repeat">Repeat :</label>
                @foreach($dayList as $value)
                    <label>
                        @php
                            $checkDay = '';
                            if (isset($voltageData)) {
                                if (in_array($value,$voltageData->repeat_day)) {
                                    $checkDay = 'checked';
                                }
                            }
                        @endphp
                        <input type="checkbox" name="repeat[]" {{$checkDay}} value="{{$value}}" class="flat-red">
                        {{ucfirst($value)}} &nbsp;&nbsp;&nbsp;
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="bootstrap-timepicker">
                <div class="form-group required">
                    {{ Form::label('effective_start_time','Effective Start Time') }}
                    <div class="input-group">
                        {{ Form::text('effective_start_time',old('effective_start_time'), ["readonly","autocomplete"=>"off","class"=>"form-control timepicker", "placeholder"=>"Select effective start time", "id"=>"effective_start_time","required"]) }}
                        <div class="input-group-addon"><i class="fa fa-clock-o"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bootstrap-timepicker">
                <div class="form-group required">
                    {{ Form::label('effective_end_time','Effective End Time') }}
                    <div class="input-group">
                        {{ Form::text('effective_end_time',old('effective_end_time'), ["readonly","autocomplete"=>"off","class"=>"form-control timepicker", "placeholder"=>"Select effective end time", "id"=>"effective_end_time","required"]) }}
                        <div class="input-group-addon"><i class="fa fa-clock-o"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group m-t-25">
                <label>
                    @php
                        $checkTime = '';
                        if (isset($voltageData)) {
                            if ($voltageData->effective_time_enable == 1) {
                                $checkTime = 'checked';
                            }
                        }
                    @endphp
                    <input type="checkbox" name="effective_time_enable" {{$checkTime}} class="flat-red">
                    Is Effective Time Enable?
                </label>
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
