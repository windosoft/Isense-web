@if(isset($gatewayData))
    {{ Form::model($gatewayData, ['route' => ['admin.gateway.update', $gatewayData->uuid], 'files' => true, 'role' => 'form', 'id'=>'frmGateway', 'method'=>'put']) }}
@else
    {{ Form::model(null, ['route' => ['admin.gateway.store'], 'files' => true, 'role' => 'form', 'id'=>'frmGateway', 'method'=>'post']) }}
@endif
<div class="box-body">
    <div class="row">
        @if (isset($gatewayData) && $isCompany == false)
            <div class="col-md-4">
                <div class="form-group required">
                    {{ Form::label('company_id','Company') }}
                    <input type="text" class="form-control" value="{{$gatewayData->company_name}}" readonly>
                </div>
            </div>
        @else
            @if ($isCompany == false)
                <div class="col-md-4">
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

        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('branch_id','Branch') }}
                <select name="branch_id" id="branch_id" class="form-control select2" required>
                    <option value="">Select Branch</option>
                    @foreach($branchList as $value)
                        @php
                            $selected = '';
                            if (isset($gatewayData)) {
                                if ($gatewayData->branch_id == $value['id']) {
                                    $selected = 'selected';
                                }
                            }
                        @endphp
                        <option value="{{$value['id']}}" {{$selected}}>{{$value['name']}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('name','Gateway Name') }}
                {{ Form::text('name',old('name'), ["class"=>"form-control", "placeholder"=>"Enter gateway name", "id"=>"name","required"]) }}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('imei','IMEI') }}
                {{ Form::text('imei',old('imei'), ["class"=>"form-control", "placeholder"=>"Enter imei", "id"=>"imei","required"]) }}
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('receiver_type','Receiver Type') }}
                <select name="receiver_type" id="receiver_type" class="form-control" required>
                    <option value="">Select Receiver Type</option>
                    @foreach($terminalType as $value)
                        @php
                            $selected = '';
                            if (isset($gatewayData)) {
                                if ($gatewayData->receiver_type == $value) {
                                    $selected = 'selected';
                                }
                            }
                        @endphp
                        <option value="{{$value}}" {{$selected}}>{{$value}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group required">
                {{ Form::label('password','Password') }}
                {{ Form::text('password',old('password'), ["class"=>"form-control", "placeholder"=>"Enter password", "id"=>"password","required"]) }}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('remarks','Remarks') }}
                {{ Form::text('remarks',old('remarks'), ["class"=>"form-control", "placeholder"=>"Enter remarks", "id"=>"remarks"]) }}
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
