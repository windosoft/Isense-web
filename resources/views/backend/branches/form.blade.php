@php
    $class = 'col-md-4';
    if ($isCompany == false) {
        $class = 'col-md-3';
    }
@endphp
@if(isset($branchData))
    {{ Form::model($branchData, ['route' => ['admin.branches.update', $branchData->uuid], 'files' => true, 'role' => 'form', 'id'=>'frmBranch', 'method'=>'put']) }}
@else
    {{ Form::model(null, ['route' => ['admin.branches.store'], 'files' => true, 'role' => 'form', 'id'=>'frmBranch', 'method'=>'post']) }}
@endif
<div class="box-body">
    <div class="row">
        @if (isset($branchData) && $isCompany == false)
            <div class="{{$class}}">
                <div class="form-group required">
                    {{ Form::label('company_id','Company') }}
                    <input type="text" class="form-control" value="{{$branchData->company_name}}" readonly>
                </div>
            </div>
        @else
            @if ($isCompany == false)
                <div class="{{$class}}">
                    <div class="form-group required">
                        {{ Form::label('company_id','Company') }}
                        <select name="company_id" id="company_id" class="form-control select2" required>
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
                {{ Form::label('name','Branch Name') }}
                {{ Form::text('name',old('name'), ["class"=>"form-control", "placeholder"=>"Enter branch name", "id"=>"name","required"]) }}
            </div>
        </div>
        <div class="{{$class}}">
            <div class="form-group required">
                {{ Form::label('email','Email') }}
                {{ Form::email('email',old('email'), ["class"=>"form-control", "placeholder"=>"Enter branch email", "id"=>"email","required"]) }}
            </div>
        </div>
        <div class="{{$class}}">
            <div class="form-group required">
                {{ Form::label('phone','Phone') }}
                {{ Form::number('phone',old('phone'), ["class"=>"form-control", "placeholder"=>"Enter branch phone", "id"=>"phone","required"]) }}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('key_person','Key Person') }}
                {{ Form::text('key_person',old('key_person'), ["class"=>"form-control", "placeholder"=>"Enter branch key person", "id"=>"key_person","required"]) }}
            </div>
        </div>
        <div class="col-md-8">
            <div class="form-group">
                {{ Form::label('address','Address') }}
                {{ Form::text('address',old('address'), ["class"=>"form-control", "placeholder"=>"Enter branch address", "id"=>"address"]) }}
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
