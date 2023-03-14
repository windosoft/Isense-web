<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel15"><i class="fa fa-plus"></i> Add Role</h4>
        </div>
        {{ Form::model(null, ['route' => ['admin.roles.store'], 'files' => true, 'role' => 'form', 'id'=>'frmRole', 'method'=>'post']) }}
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {{ Form::label('role_name','Role Name') }}
                        {{ Form::text('role_name', old('role_name'), ["required", "class"=>"form-control", "placeholder"=>"Enter your role name", "id"=>"role_name"]) }}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-success"></div>
                    <div class="alert alert-danger"></div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-gradient" id="btnSubmit"
                    data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> loading">Submit
            </button>
        </div>
        {{ Form::close() }}
    </div>
</div>
<script src="{{asset('backend/dist/js/jquery.validate.min.js')}}"></script>
<script src="{{asset('backend/dist/js/pages/roles.js')}}"></script>
