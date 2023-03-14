<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel15"><i class="fa fa-trash"></i> Delete Branch</h4>
        </div>
        {{ Form::model(null, ['route' => ['admin.branches.destroy',$uuid], 'files' => true, 'role' => 'form', 'id'=>'frmBranch', 'method'=>'delete']) }}
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <h4>Are you sure you want delete this record?</h4>
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
                    data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> loading">Delete
            </button>
        </div>
        {{ Form::close() }}
    </div>
</div>
<script src="{{asset('backend/dist/js/jquery.validate.min.js')}}"></script>
<script src="{{asset('backend/dist/js/pages/branch.js')}}"></script>
