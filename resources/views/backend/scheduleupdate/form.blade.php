@php
    $class = 'col-md-4';
    if ($isCompany == false) {
        $class = 'col-md-3';
    }
@endphp
@if(isset($scheduleData))
    {{ Form::model($scheduleData, ['route' => ['admin.schedule.update', $scheduleData->su_uuid], 'files' => true, 'role' => 'form', 'id'=>'frmSchedule', 'method'=>'put']) }}
@else
    {{ Form::model(null, ['route' => ['admin.schedule.store'], 'files' => true, 'role' => 'form', 'id'=>'frmSchedule', 'method'=>'post']) }}
@endif
<div class="box-body">
    <div class="row">
        @if (isset($scheduleData) && $isCompany == false)
            <div class="{{$class}}">
                <div class="form-group required">
                    {{ Form::label('company_id','Company') }}
                    <input type="text" class="form-control" value="{{$scheduleData->company_name}}" readonly>
                </div>
            </div>
        @else
            @if ($isCompany == false)
                <div class="{{$class}}">
                    <div class="form-group required">
                        {{ Form::label('company_id','Company') }}
                        <select name="company_id" id="company_id" onchange="getSensor(this.value)"
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
        <div class="{{$class}}">
            <div class="form-group required">
                {{ Form::label('sensors','Sensor') }}
                <select name="sensor_id" id="sensor_id" class="form-control select2" data-placeholder="Select Sensor" placeholder="Select Sensor" required>
                    <option value="">Select Sensor</option>
                    @foreach($sensorList as $value)
                        @php $isSelected = ''; @endphp
                        @if(isset($scheduleData))
                            <?php
                            $allDevices = explode(',', $scheduleData->su_devices);
                            if (in_array($value['id'], $allDevices)) {
                                $isSelected = 'selected';
                            }
                            ?>
                        @endif
                        <option value="{{$value['id']}}" {{$isSelected}}>{{$value['device_name']}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="{{$class}}">
            <div class="form-group required">
                {{ Form::label('su_type','Type') }}
                <select name="su_type" id="su_type" class="form-control select2" onchange="changeSuType(this.value)" required>
                    <option value="">Select Type</option>
                    <option value="1" @if(isset($scheduleData)) @if($scheduleData->su_type == 1) selected @endif @endif>
                        Daily
                    </option>
                    <option value="2" @if(isset($scheduleData)) @if($scheduleData->su_type == 2) selected @endif @endif>
                        Weekly
                    </option>
                    <option value="3" @if(isset($scheduleData)) @if($scheduleData->su_type == 3) selected @endif @endif>
                        Monthly
                    </option>
                </select>
            </div>
        </div>
        <div class="{{$class}}">
            <div class="form-group required" style="display: none" id="su_week_day_box">
                {{ Form::label('su_week_day','Week Day') }}
                <select name="su_week_day" id="su_week_day" class="form-control select2" required>
                    <option value="">Select Week</option>
                    <option value="monday"
                            @if(isset($scheduleData)) @if($scheduleData->su_week_day == 'monday') selected @endif @endif>
                        Monday
                    </option>
                    <option value="tuesday"
                            @if(isset($scheduleData)) @if($scheduleData->su_week_day == 'tuesday') selected @endif @endif>
                        Tuesday
                    </option>
                    <option value="wednesday"
                            @if(isset($scheduleData)) @if($scheduleData->su_week_day == 'wednesday') selected @endif @endif>
                        Wednesday
                    </option>
                    <option value="thursday"
                            @if(isset($scheduleData)) @if($scheduleData->su_week_day == 'thursday') selected @endif @endif>
                        Thursday
                    </option>
                    <option value="friday"
                            @if(isset($scheduleData)) @if($scheduleData->su_week_day == 'friday') selected @endif @endif>
                        Friday
                    </option>
                    <option value="saturday"
                            @if(isset($scheduleData)) @if($scheduleData->su_week_day == 'saturday') selected @endif @endif>
                        Saturday
                    </option>
                    <option value="sunday"
                            @if(isset($scheduleData)) @if($scheduleData->su_week_day == 'sunday') selected @endif @endif>
                        Sunday
                    </option>
                </select>
            </div>
            {{--<div class="form-group required" style="display: none" id="su_month_date_box">
                {{ Form::label('su_month_date','Select Date') }}
                <select name="su_month_date" id="su_month_date" class="form-control select2" required>
                    <option value="">Select Day</option>
                    @for($i=1;$i<32;$i++)
                        <option value="{{sprintf("%02d", $i)}}"
                                @if(isset($scheduleData)) @if($scheduleData->su_month_date == $i) selected @endif @endif>
                            {{sprintf("%02d", $i)}}
                        </option>
                    @endfor
                </select>
            </div>--}}
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('su_time','Time') }}
                {{ Form::text('su_time',old('su_time'), ["class"=>"form-control timepicker", "placeholder"=>"Select Time", "id"=>"su_time","required"]) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('su_email','Email') }}
                {{ Form::email('su_email',old('su_email'), ["class"=>"form-control", "placeholder"=>"Enter Email", "id"=>"su_email","required"]) }}
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
