@php
    $class = 'col-md-9';
    if ($isCompany) {
        $class = 'col-md-12';
    }
@endphp
@if(isset($groupData))
    {{ Form::model($groupData, ['route' => ['admin.group.update', $groupData->uuid], 'files' => true, 'role' => 'form', 'id'=>'frmGroup', 'method'=>'put']) }}
@else
    {{ Form::model(null, ['route' => ['admin.group.store'], 'files' => true, 'role' => 'form', 'id'=>'frmGroup', 'method'=>'post']) }}
@endif
<div class="box-body">
    <div class="row">
        @if (isset($groupData) && $isCompany == false)
            <div class="col-md-3">
                <div class="form-group required">
                    {{ Form::label('company_id','Company') }}
                    <input type="text" class="form-control" value="{{$groupData->company_name}}" readonly>
                </div>
            </div>
        @else
            @if ($isCompany == false)
                <div class="col-md-3">
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

        <div class="{{$class}}">
            <div class="form-group required">
                {{ Form::label('device_id','Sensor') }}
                <select name="device_id[]" id="device_id" multiple class="form-control select2" required>
                    @foreach($deviceList as $device)
                        @php
                            $selected = '';
                            if (isset($groupData)) {
                                if (in_array($device['id'],$groupData->device_ids)) {
                                    $selected = 'selected';
                                }
                            }
                        @endphp
                        <option value="{{$device['id']}}" {{$selected}}>{{$device['device_name']}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group required">
                {{ Form::label('name','Group Name') }}
                {{ Form::text('name',old('name'), ["class"=>"form-control", "placeholder"=>"Enter group name", "id"=>"name","required"]) }}
            </div>
        </div>
        <div class="col-md-9">
            <div class="form-group">
                {{ Form::label('description','Group Description') }}
                {{ Form::text('description',old('description'), ["class"=>"form-control", "placeholder"=>"Enter group description", "id"=>"description"]) }}
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
