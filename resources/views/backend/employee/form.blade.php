@php
    $required = 'required';
@endphp
@if(isset($employeeData))
    @php
        $required = '';
    @endphp
    {{ Form::model($employeeData, ['route' => ['admin.employee.update', $employeeData->uuid], 'files' => true, 'role' => 'form', 'id'=>'frmEmployee', 'method'=>'put']) }}
@else
    {{ Form::model(null, ['route' => ['admin.employee.store'], 'files' => true, 'role' => 'form', 'id'=>'frmEmployee', 'method'=>'post']) }}
@endif
<div class="box-body">
    <div class="row">
        @if (isset($employeeData) && $isCompany == false)
            <div class="col-md-4">
                <div class="form-group required">
                    {{ Form::label('company_id','Company') }}
                    <input type="text" class="form-control" value="{{$employeeData->company_name}}" readonly>
                </div>
            </div>
        @else
            @if ($isCompany == false)
                <div class="col-md-4">
                    <div class="form-group required">
                        {{ Form::label('company_id','Company') }}
                        <select name="company_id" id="company_id" onchange="getGroup(this.value)"
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
                {{ Form::label('group_id','Group') }}
                <select name="group_id" id="group_id" class="form-control select2" required>
                    <option value="">Select Group</option>
                    @foreach($groupList as $value)
                        @php
                            $selected = '';
                            if (isset($employeeData)) {
                                if ($employeeData->group_id == $value['id']) {
                                    $selected = 'selected';
                                }
                            }
                        @endphp
                        <option value="{{$value['id']}}" {{$selected}}>{{$value['name']}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('first_name','First Name') }}
                {{ Form::text('first_name',old('first_name'), ["class"=>"form-control", "placeholder"=>"Enter first name", "id"=>"first_name","required"]) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('last_name','Last Name') }}
                {{ Form::text('last_name',old('last_name'), ["class"=>"form-control", "placeholder"=>"Enter last name", "id"=>"last_name","required"]) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('phone','Phone') }}
                {{ Form::number('phone',old('phone'), ["class"=>"form-control", "placeholder"=>"Enter phone number", "id"=>"phone","required"]) }}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('email','Email') }}
                {{ Form::email('email',old('email'), ["class"=>"form-control", "placeholder"=>"Enter email address", "id"=>"email","required"]) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('password','Password') }}
                {{ Form::password('password',["minlength"=>6,"class"=>"form-control", "placeholder"=>"Enter password", "id"=>"password",$required]) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('confirm_password','Confirm Password') }}
                {{ Form::password('confirm_password', ["minlength"=>6,"class"=>"form-control", "placeholder"=>"Enter confirm password", "id"=>"confirm_password",$required]) }}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('profile','Profile') }}
                <input type="file" onchange="previewImage(this)" class="form-control"
                       id="profile" name="profile" accept="image/*">
                <div class="preview-image">
                    @if(isset($employeeData))
                        <img src="{{asset($employeeData->profile)}}" alt="">
                    @endif
                </div>
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
