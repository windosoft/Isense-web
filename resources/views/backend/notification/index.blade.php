@php
    $permission = new \App\Models\Permissions();
    $unRead = \App\Models\Helpers::UNREAD;
@endphp
@extends('backend.layout')

@section('styles')
    <link rel="stylesheet"
          href="{{asset('backend/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('backend/plugins/iCheck/square/blue.css')}}">
@endsection

@section('scripts')
    <script src="{{asset('backend/bower_components/datatables.net/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('backend/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
    <script src="{{asset('backend/dist/js/jquery.validate.min.js')}}"></script>
    <script src="{{asset('backend/dist/js/pages/notification.js')}}"></script>
    <script src="{{asset('backend/plugins/iCheck/icheck.min.js')}}"></script>
    <script>
        $(function () {
            $('input[type="checkbox"]').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%'
            });
            $('#notification-list').DataTable({
                searching: false
            });

            /*$('#notification-list').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                iDisplayLength: 10,
                "ajax": {
                    url: '{{route('admin.notification.paginate')}}',
                    type: 'POST',
                    data: function (d) {
                        d._token = '{{csrf_token()}}';
                    }
                },
                bPaginate: true,
                columns: [
                    {data: "index", sortable: false},
                    {data: "index", sortable: false},
                        @if($isCompany == false)
            {
                data: "company_name", sortable: false
            },
@endif
            {
                data: "notification", sortable: false
            },
            {data: "notification_for", sortable: false},
            {data: "created_date", sortable: false},
            {data: "action", sortable: false},
        ],
        lengthMenu: [
            [10, 25, 50, 100],
            [10, 25, 50, 100]
        ],
        "columnDefs": [
            {
                "render": function (data, type, row) {
                    var btn = '';
                    var uuid = row.uuid;

@if($permission::checkActionPermission('view_message_center'))
            btn += ' <button type="button" onclick="viewNotification(this,\'' + uuid + '\')" class="btn btn-success btn-flat btn-sm">View</button>';
        btn += ' <button type="button" onclick="deleteNotification(this,\'' + uuid + '\')" class="btn btn-danger btn-flat btn-sm">Delete</button>';
@endif

            return [btn].join('');
    },
    "targets": $('#notification-list th#action').index(),
    "orderable": false,
    "searchable": false
}, {
    "render": function (data, type, row) {
        var uuid = row.uuid;
        var input = '';
        input += '<div class="checkbox icheck">';
        input += '<label>';
        input += '<input type="checkbox" name="notification" class="notification_input" id="notify_' + uuid + '" value="' + uuid + '" />';
        input += '</label>';
        input += '</div>';

        $('input[type="checkbox"]').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%'
        });

        return [input].join('');
    },
    "targets": $('#notification-list th#delete_input').index(),
    "orderable": false,
    "searchable": false
}
],
"createdRow": function (row, data, index) {
if (data.read_status == '{{$unRead}}') {
                        var className = 'row_' + data.uuid;
                        $(row).addClass('info ' + className);
                    }
                }
            });*/
        });
    </script>
@endsection

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Notification</h1>
            <ol class="breadcrumb">
                <li><a href="{{route('admin.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Notification</li>
            </ol>
        </section>

        <section class="content">
            <div class="row">

                <div id="display_message" class="col-xs-12">
                    <div class="alert alert-success"></div>
                </div>

                <div class="col-xs-12">
                    <div class="box box-warning">
                        <div class="box-header">
                            <h3 class="box-title">List of Notification</h3>
                            <button type="button" style="float: right;" onclick="deleteMultipleNotification()"
                                    class="btn btn-gradient">Delete Multiple
                            </button>
                        </div>
                        <div class="box-body">
                            <table id="notification-list" class="table">
                                <thead>
                                <tr>
                                    <th id="delete_input"></th>
                                    <th>#</th>
                                    @if($isCompany == false)
                                        <th>Company Name</th>
                                    @endif
                                    <th>Notification</th>
                                    <th>Notification For</th>
                                    <th>Date Time</th>
                                    <th id="action">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($notificationList as $key => $value)
                                    <tr class="{{($value['read_status'] == 'unread') ? 'info row_'.$value['uuid'] : ''}}">
                                        <td>
                                            <div class="checkbox icheck">
                                                <label>
                                                    <input type="checkbox" name="notification"
                                                           class="notification_input" id="notify_{{$value['uuid']}}"
                                                           value="{{$value['uuid']}}"/>
                                                </label>
                                            </div>
                                        </td>
                                        <td>{{++$key}}</td>
                                        @if($isCompany == false)
                                            <td>{{$value['company_name']}}</td>
                                        @endif
                                        <td>{{$value['notification']}}</td>
                                        <td>{{$value['notification_for']}}</td>
                                        <td>{{$value['created_date']}}</td>
                                        <td id="action">
                                            @if($permission::checkActionPermission('view_message_center'))
                                                <a href="javascript:void(0);"
                                                   onclick="viewNotification(this,'{{$value['uuid']}}')"
                                                   class="btn-round"><i class="fa fa-eye"></i></a>
                                                <a href="javascript:void(0);"
                                                   onclick="deleteNotification(this,'{{$value['uuid']}}')"
                                                   class="btn-round"><i class="fa fa-trash"></i></a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
