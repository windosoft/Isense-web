<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel15"><i class="fa fa-trash"></i> Delete Notification</h4>
        </div>
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
            @foreach($ids as $value)
                <span class="notification-uuid" data-uuid="{{$value}}"></span>
            @endforeach
            <button type="button" class="btn" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-gradient" id="btnSubmit"
                    onclick="destroyMultiNotification(this)"
                    data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> loading">Delete
            </button>
        </div>
    </div>
</div>
