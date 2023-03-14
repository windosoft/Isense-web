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
    <script src="{{asset('backend/dist/js/pages/branch.js')}}"></script>
    <script>
        $(function () {
            $('#branch-list').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                iDisplayLength: 10,
                "ajax": {
                    url: '{{route('admin.branches.paginate')}}',
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
                        data: "name", sortable: false
                    },
                    {data: "email", sortable: false},
                    {data: "phone", sortable: false},
                    {data: "key_person", sortable: false},
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
                            @if($permission::checkActionPermission('edit_branch'))
                                btn += '<a href="' + adminUrl + '/branches/' + uuid + '/edit" class="btn-round"><i class="fa fa-pencil-square-o"></i></a>';
                            @endif

                                @if($permission::checkActionPermission('delete_branch'))
                                btn += ' <a href="javascript:void(0);" onclick="deleteBranch(this,\'' + uuid + '\')" class="btn-round"><i class="fa fa-trash"></i></a>';
                            @endif

                                return [btn].join('');
                        },
                        "targets": $('#branch-list th#action').index(),
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
            <h1>Branches</h1>
            <ol class="breadcrumb">
                <li><a href="{{route('admin.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Branches</li>
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
                            <h3 class="box-title">List of Branches</h3>
                            @if($permission::checkActionPermission('add_branch'))
                                <div class="pull-right">
                                    <a href="{{route('admin.branches.create')}}" class="btn btn-gradient">Add</a>
                                </div>
                            @endif
                        </div>
                        <div class="box-body">
                            <table id="branch-list" class="table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    @if($isCompany == false)
                                        <th>Company Name</th>
                                    @endif
                                    <th>Branch Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Key Person</th>
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
