@php
    $permission = new \App\Models\Permissions();
@endphp
@extends('backend.layout')

@section('styles')
    <link rel="stylesheet"
          href="{{asset('backend/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css')}}">
@endsection

@section('scripts')
    <script src="{{asset('backend/bower_components/datatables.net/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('backend/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
    <script src="{{asset('backend/dist/js/jquery.validate.min.js')}}"></script>
    <script src="{{asset('backend/dist/js/pages/voltage.js')}}"></script>
    <script>
        $(function () {
            $('#voltage-list').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                iDisplayLength: 10,
                "ajax": {
                    url: '{{route('admin.voltage.paginate')}}',
                    type: 'POST',
                    data: function (d) {
                        d._token = '{{csrf_token()}}';
                    }
                },
                bPaginate: true,
                columns: [
                    {data: "index", sortable: false},
                        @if($isCompany == false)
                    {
                        data: "company_name", sortable: false
                    },
                        @endif
                    {
                        data: "device_name", sortable: false
                    },
                    {data: "name", sortable: false},
                    {data: "low_voltage", sortable: false},
                    {data: "effective_date", sortable: false},
                    {data: "effective_time", sortable: false},
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
                            @if($permission::checkActionPermission('edit_voltage'))
                                btn += '<a href="' + adminUrl + '/voltage/' + uuid + '/edit" class="btn-round"><i class="fa fa-pencil-square-o"></i></a>';
                            @endif

                                @if($permission::checkActionPermission('delete_voltage'))
                                btn += ' <a href="javascript:void(0);" onclick="deleteVoltage(this,\'' + uuid + '\')" class="btn-round"><i class="fa fa-trash"></i></a>';
                            @endif

                                return [btn].join('');
                        },
                        "targets": $('#voltage-list th#action').index(),
                        "orderable": false,
                        "searchable": false
                    }
                ]
            });
        });
    </script>
@endsection

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Voltage</h1>
            <ol class="breadcrumb">
                <li><a href="{{route('admin.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Voltage</li>
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
                            <h3 class="box-title">List of Voltage</h3>
                            @if($permission::checkActionPermission('add_voltage'))
                                <div class="pull-right">
                                    <a href="{{route('admin.voltage.create')}}" class="btn btn-gradient">Add</a>
                                </div>
                            @endif
                        </div>
                        <div class="box-body">
                            <table id="voltage-list" class="table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    @if($isCompany == false)
                                        <th>Company Name</th>
                                    @endif
                                    <th>Device Name</th>
                                    <th>Voltage Name</th>
                                    <th>Low Voltage</th>
                                    <th>Effective Date</th>
                                    <th>Effective Time</th>
                                    <th id="action">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
