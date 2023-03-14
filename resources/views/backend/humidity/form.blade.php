@php
    $required = 'required';
@endphp
@if(isset($humidityData))
    @php
        $required = '';
    @endphp
    {{ Form::model($humidityData, ['route' => ['admin.humidity.update', $humidityData->uuid], 'files' => true, 'role' => 'form', 'id'=>'frmHumidity', 'method'=>'put']) }}
@else
    {{ Form::model(null, ['route' => ['admin.humidity.store'], 'files' => true, 'role' => 'form', 'id'=>'frmHumidity', 'method'=>'post']) }}
@endif
<div class="box-body">
    <div class="row">
        @if (isset($humidityData) && $isCompany == false)
            <div class="col-md-4">
                <div class="form-group required">
                    {{ Form::label('company_id','Company') }}
                    <input type="text" class="form-control" value="{{$humidityData->company_name}}" readonly>
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
                            if (isset($humidityData)) {
                                if ($humidityData->device_id == $value['id']) {
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
                {{ Form::label('name','Humidity Name') }}
                {{ Form::text('name',old('name'), ["class"=>"form-control", "placeholder"=>"Enter name", "id"=>"name","required"]) }}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('warning_low_humidity_threshold','Low Humidity Warning') }}
                <div class="input-group">
                    {{ Form::number('warning_low_humidity_threshold',old('warning_low_humidity_threshold'), ["class"=>"form-control", "placeholder"=>"Enter low humidity warning", "id"=>"warning_low_humidity_threshold","required"]) }}
                    <div class="input-group-addon"><strong>%</strong></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('warning_high_humidity_threshold','High Humidity Warning') }}
                <div class="input-group">
                    {{ Form::number('warning_high_humidity_threshold',old('warning_high_humidity_threshold'), ["class"=>"form-control", "placeholder"=>"Enter high humidity warning", "id"=>"warning_high_humidity_threshold","required"]) }}
                    <div class="input-group-addon"><strong>%</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('low_humidity_threshold','Low Humidity Threshold') }}
                <div class="input-group">
                    {{ Form::number('low_humidity_threshold',old('low_humidity_threshold'), ["class"=>"form-control", "placeholder"=>"Enter low humidity threshold", "id"=>"low_humidity_threshold","required"]) }}
                    <div class="input-group-addon"><strong>%</strong></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('high_humidity_threshold','High Humidity Threshold') }}
                <div class="input-group">
                    {{ Form::number('high_humidity_threshold',old('high_humidity_threshold'), ["class"=>"form-control", "placeholder"=>"Enter high humidity threshold", "id"=>"high_humidity_threshold","required"]) }}
                    <div class="input-group-addon"><strong>%</strong></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('dramatic_changes_value','Dramatic Changes Value') }}
                <div class="input-group">
                    {{ Form::number('dramatic_changes_value',old('dramatic_changes_value'), ["class"=>"form-control", "placeholder"=>"Enter dramatic changes value", "id"=>"dramatic_changes_value","required"]) }}
                    <div class="input-group-addon"><strong>%</strong></div>
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
                        if (isset($humidityData)) {
                            if ($humidityData->effective_date_enable == 1) {
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
                            if (isset($humidityData)) {
                                if (in_array($value,$humidityData->repeat_day)) {
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
                        if (isset($humidityData)) {
                            if ($humidityData->effective_time_enable == 1) {
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
