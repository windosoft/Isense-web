<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel15"><i class="fa fa-eye"></i> View Notification</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-bordered table-striped table-hover">
                        <tr>
                            <td colspan="2">
                                <strong>Notification</strong>
                                <div>{{$notificationData->notification}}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Notification For</strong>
                                <div>{{$notificationData->notification_for}}</div>
                            </td>
                            <td>
                                <strong>Alert Type</strong>
                                <div>{{$notificationData->alerttype}}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Device Name</strong>
                                <div>{{$notificationData->device_name}}</div>
                            </td>
                            <td>
                                <strong>Terminal Name</strong>
                                <div>{{$notificationData->terminal_name}}</div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <strong>Date Time</strong>
                                <div>{{date('d F Y H:i A',strtotime($notificationData->created_at))}}</div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-gradient" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>
