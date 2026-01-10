@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.notifications')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.notificaions_table')}}</li>
            </ol>
        </div>
        <div>
        </div>
    </div>
    <div class="container-fluid">
       <div class="admin-top-section">
        <div class="row">
            <div class="col-12">
                <div class="d-flex top-title-section pb-4 justify-content-between">
                    <div class="d-flex top-title-left align-self-center">
                        <span class="icon mr-3"><img src="{{ asset('images/notification.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.notifications')}}</h3>
                        <span class="counter ml-3 notification_count"></span>
                    </div>
                    <div class="d-flex top-title-right align-self-center">
                        <div class="select-box pl-3">

                        </div>
                    </div>
                </div>
            </div>
        </div>
       </div>
       <div class="table-list">
       <div class="row">
           <div class="col-12">
               <div class="card border">
                 <div class="card-header d-flex justify-content-between align-items-center border-0">
                   <div class="card-header-title">
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.notificaions_table')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.notifications_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3">
                        <a class="btn-primary btn rounded-full" href="{!! url('notification/send') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.create_notificaion')}}</a>
                     </div>
                   </div>
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="notificationTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <?php if (in_array('notification.delete', json_decode(@session('user_permissions'), true))) { ?>
                                            <th class="delete-all"><input type="checkbox" id="is_active">
                                                <label class="col-3 control-label" for="is_active">
                                                    <a id="deleteAll" class="do_not_delete" href="javascript:void(0)">
                                                        <i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a>
                                                    </label>
                                            </th>
                                        <?php } ?>
                                        <th>{{trans('lang.subject')}}</th>
                                        <th>{{trans('lang.message')}}</th>
                                        <th>{{trans('lang.date_created')}}</th>
                                        <?php if (in_array('notification.delete', json_decode(@session('user_permissions'), true))) { ?>
                                            <th>{{trans('lang.actions')}}</th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody id="append_restaurants">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
    // SQL mode
    var append_list = '';
    var user_permissions = '<?php echo @session("user_permissions") ?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;
    if ($.inArray('notification.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }
    $(document).ready(function () {
        jQuery("#data-table_processing").show();
        const table = $('#notificationTable').DataTable({
            pageLength: 10, // Number of rows per page
            processing: false, // Show processing indicator
            serverSide: true, // Enable server-side processing
            responsive: true,
            ajax: async function (data, callback, settings) {
                const start = data.start;
                const length = data.length;
                const searchValue = data.search.value.toLowerCase();
                const orderColumnIndex = data.order[0].column;
                const orderDirection = data.order[0].dir;
                const orderableColumns = (checkDeletePermission) ? ['', 'subject', 'message', 'createdAt', ''] : ['subject', 'message', 'createdAt', '']; // Ensure this matches the actual column names
                const orderByField = orderableColumns[orderColumnIndex]; // Adjust the index to match your table
                if (searchValue.length >= 3 || searchValue.length === 0) {
                    $('#data-table_processing').show();
                }
                $.ajax({
                    url: '{{ route("notification.data") }}',
                    method: 'GET',
                    data: {
                        draw: data.draw,
                        start: start,
                        length: length,
                        'search[value]': searchValue,
                        'order[0][column]': orderColumnIndex,
                        'order[0][dir]': orderDirection
                    }
                }).done(function(resp){
                    $('.notification_count').text(resp.recordsTotal || 0);
                    $('#data-table_processing').hide();
                    callback({
                        draw: resp.draw,
                        recordsTotal: resp.recordsTotal,
                        recordsFiltered: resp.recordsFiltered,
                        data: resp.data
                    });
                }).fail(function(){
                    $('#data-table_processing').hide();
                    callback({ draw: data.draw, recordsTotal:0, recordsFiltered:0, data: [] });
                });
            },
            order: (checkDeletePermission) ? [[3, 'desc']] : [[2, 'desc']],
            columnDefs: [
                {
                    targets: (checkDeletePermission) ? 3 : 2,
                    type: 'date',
                    render: function (data) {
                        // Ensure human-readable output regardless of backend format
                        try { return window.formatDateTime(data); } catch(e) { return data || '-'; }
                    }
                },
                { orderable: false, targets: (checkDeletePermission) ? [0, 4] : [3] },
            ],
            "language": {
                "zeroRecords": "{{trans("lang.no_record_found")}}",
                "emptyTable": "{{trans("lang.no_record_found")}}",
                "processing": "" // Remove default loader
            },
        });
        function debounce(func, wait) {
            let timeout;
            const context = this;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }
        $('#search-input').on('input', debounce(function () {
            const searchValue = $(this).val();
            if (searchValue.length >= 3) {
                $('#data-table_processing').show();
                table.search(searchValue).draw();
            } else if (searchValue.length === 0) {
                $('#data-table_processing').show();
                table.search('').draw();
            }
        }, 300));
    })
    $("#is_active").click(function () {
        $("#notificationTable .is_open").prop('checked', $(this).prop('checked'));
    });
    $("#deleteAll").click(function () {
        if ($('#notificationTable .is_open:checked').length) {
            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#data-table_processing").show();
                var deletePromises = [];
                $('#notificationTable .is_open:checked').each(function () {
                    var dataId = $(this).attr('dataId');
                    deletePromises.push(
                        $.ajax({
                            url: '{{ url('/notification') }}/' + dataId,
                            method: 'DELETE',
                            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
                        })
                    );
                });
                Promise.all(deletePromises).then(function() {
                    window.location.reload();
                }).catch(function(error) {
                    jQuery("#data-table_processing").hide();
                    alert('Error deleting notifications');
                    console.error(error);
                });
            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });
    function buildHTML(val) {
        var html = [];
        newdate = '';
        var id = val.id;
        if (checkDeletePermission) {
            html.push('<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' +
                'for="is_open_' + id + '" ></label></td>');
        }
        html.push(val.subject)
        html.push(val.message);
        html.push(val[ (checkDeletePermission)? 3 : 2 ] || '');
        if (checkDeletePermission) {
           html.push('<span class="action-btn"><a id="' + val.id + '" name="notifications-delete" class="delete-btn" href="javascript:void(0)"><i class="mdi mdi-delete"></i></a></span>');
        }
        return html;
    }
    $(document).on("click", "a[name='notifications-delete']", function (e) {
        var id = this.id;
        if(!confirm("{{trans('lang.delete_alert')}}")) return;
        $('#data-table_processing').show();
        $.ajax({
            url: '{{ url('/notification') }}/'+id,
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        }).done(function(){ window.location.reload(); })
          .fail(function(){ $('#data-table_processing').hide(); alert('Delete failed'); });
    });
</script>
@endsection
