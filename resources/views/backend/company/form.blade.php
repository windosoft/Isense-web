@php
    $required = 'required';
@endphp
@if(isset($companyData))
    @php
        $required = '';
    @endphp
    {{ Form::model($companyData, ['route' => ['admin.company.update', $companyData->uuid], 'files' => true, 'role' => 'form', 'id'=>'frmCompany', 'method'=>'put']) }}
@else
    {{ Form::model(null, ['route' => ['admin.company.store'], 'files' => true, 'role' => 'form', 'id'=>'frmCompany', 'method'=>'post']) }}
@endif
<div class="box-body">
    <div class="row">
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('first_name','Company Name') }}
                {{ Form::text('first_name', old('first_name'), ["required", "class"=>"form-control", "placeholder"=>"Enter your company name", "id"=>"first_name"]) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('email','Email Address') }}
                {{ Form::email('email', old('email'), ["required", "class"=>"form-control", "placeholder"=>"Enter your email address", "id"=>"email"]) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('phone','Phone') }}
                {{ Form::number('phone', old('phone'), ["required", "class"=>"form-control", "placeholder"=>"Enter your phone", "id"=>"phone"]) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group {{$required}}">
                {{ Form::label('password','Password') }}
                {{ Form::password('password', ["minlength"=>"6","class"=>"form-control", "placeholder"=>"Enter your password", "id"=>"password", $required]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group {{$required}}">
                {{ Form::label('confirm_password','Confirm Password') }}
                {{ Form::password('confirm_password', ["minlength"=>"6","class"=>"form-control", "placeholder"=>"Enter confirm password", "id"=>"confirm_password", $required]) }}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="form-group">
                {{ Form::label('address','Address') }}
                {{ Form::text('address',old('address'), ["class"=>"form-control", "placeholder"=>"Enter your address", "id"=>"address"]) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('time_zone','Time Zone') }}
                <select name="time_zone" id="time_zone" class="form-control select2" required>
                    <option value="" hidden>Select time zone</option>
                    @foreach($timeZoneList as $value)
                        @php
                            $selected = '';
                            if(isset($companyData)){
                                if($value == $companyData->time_zone){
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
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('profile','Profile') }}
                <input type="file" onchange="previewImage(this)" class="form-control"
                       id="profile" name="profile" accept="image/*">
                <div class="preview-image">
                    @if(isset($companyData))
                        <img src="{{asset($companyData->profile)}}" alt="">
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
