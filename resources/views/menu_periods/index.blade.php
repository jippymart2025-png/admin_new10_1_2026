@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Menu Periods</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item">Menu Periods</li>
                <li class="breadcrumb-item active">Menu Periods Table</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/restaurant.png') }}"></span>
                        <h3 class="mb-0">Menu Periods</h3>
                        <span class="counter ml-3 menu_period_count"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card border">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card card-box-with-icon bg--1">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                       <div class="card-box-with-content">
                                        <h4 class="text-dark-2 mb-1 h4 menu_period_count">00</h4>
                                        <p class="mb-0 small text-dark-2">Total Menu Periods</p>
                                       </div>
                                        <span class="box-icon ab"><img src="{{ asset('images/restaurant_icon.png') }}"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
       </div>

    @if(session('success'))
        <div class="alert alert-success">{!! session('success') !!}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{!! session('error') !!}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{!! $error !!}</li>
                @endforeach
            </ul>
        </div>
    @endif
       <div class="table-list">
        <div class="row">
            <div class="col-12">
                <div class="card border">
                    <div class="card-header d-flex justify-content-between align-items-center border-0">
                    <div class="card-header-title">
                        <h3 class="text-dark-2 mb-2 h4">Menu Periods Table</h3>
                        <p class="mb-0 text-dark-2">Manage meal time periods for restaurants</p>
                    </div>
                    <div class="card-header-right d-flex align-items-center">
                        <div class="card-header-btn mr-3">
                        <a href="{!! route('menu-periods.create') !!}" class="btn-primary btn rounded-full"><i class="mdi mdi-plus mr-2"></i>Create Menu Period</a>
                        </div>
                    </div>
                    </div>
                    <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="menuPeriodsTable"
                                    class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> All</a></label></th>
                                            <th class="text-center">Menu Period Info</th>
                                            <th class="text-center">From Time</th>
                                            <th class="text-center">To Time</th>
                                            <th class="text-center">Publish</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="append_menu_periods"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="data-table_processing" class="data-table-processing" style="display: none">Processing...</div>
@endsection
@section('scripts')
<style>
.table-responsive {
    overflow-x: auto;
}
#menuPeriodsTable {
    width: 100% !important;
}
#menuPeriodsTable td {
    white-space: nowrap;
    vertical-align: middle;
}
#menuPeriodsTable .delete-all {
    width: 80px;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}
#menuPeriodsTable .delete-all input[type="checkbox"] {
    margin: 0;
}
#menuPeriodsTable .delete-all .expand-row {
    margin: 0;
}
#menuPeriodsTable th:nth-child(2) {
    width: 200px;
}
#menuPeriodsTable th:nth-child(3) {
    width: 150px;
}
#menuPeriodsTable th:nth-child(4) {
    width: 150px;
}
#menuPeriodsTable th:nth-child(5) {
    width: 150px;
}
#menuPeriodsTable th:nth-child(6) {
    width: 100px;
}
.action-btn {
    white-space: nowrap;
}
/* Publish toggle switch */
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}
.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    -webkit-transition: .4s;
    transition: .4s;
}
.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    -webkit-transition: .4s;
    transition: .4s;
}
input:checked + .slider {
    background-color: #2196F3;
}
input:focus + .slider {
    box-shadow: 0 0 1px #2196F3;
}
input:checked + .slider:before {
    -webkit-transform: translateX(26px);
    -ms-transform: translateX(26px);
    transform: translateX(26px);
}
.slider.round {
    border-radius: 34px;
}
.slider.round:before {
    border-radius: 50%;
}
</style>
<script type="text/javascript">
    var selectedMenuPeriods = new Set();
    var user_permissions = '<?php echo @session("user_permissions") ?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = ($.inArray('menu-periods.delete', user_permissions) >= 0);

    $(document).ready(function () {
        const table = $('#menuPeriodsTable').DataTable({
            pageLength: 30,
            lengthMenu: [[10, 25, 30, 50, 100, -1], [10, 25, 30, 50, 100, "All"]],
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: function(data, callback){
                const params = { start: data.start, length: data.length, draw: data.draw, search: data.search.value };
                console.log('📡 Fetching menu periods:', params);

                $.get('{{ route('menu-periods.data') }}', params, function(json){
                    console.log('📥 Menu periods response:', json);

                    // Update count display
                    if (json.stats && json.stats.total) {
                        $('.menu_period_count').text(json.stats.total);
                        console.log('📊 Total menu periods:', json.stats.total);
                    } else {
                        $('.menu_period_count').text(json.recordsTotal || 0);
                    }

                    callback(json);
                })
                .fail(function(xhr){
                    console.error('❌ Failed to load menu periods:', xhr);
                    alert('Failed to load ('+xhr.status+'): '+xhr.statusText);
                });
            },
            order: [1, 'asc'],
            columnDefs: [ {orderable: false, targets: [0, 4, 5]} ],
            language: { zeroRecords: 'No record found', emptyTable: 'No record found', processing: '' }
        });
    });

    // Select all logic
    $("#is_active").click(function () {
        $("#menuPeriodsTable .is_open").prop('checked', $(this).prop('checked'));
    });

    // Row checkbox logic
    $('#menuPeriodsTable tbody').on('change', '.is_open', function () {
        var id = $(this).attr('dataId');
        if (this.checked) {
            selectedMenuPeriods.add(id);
        } else {
            selectedMenuPeriods.delete(id);
        }
        $('#is_active').prop('checked', $('.is_open:checked').length === $('.is_open').length);
    });

    // no expand rows in SQL version

    // Toggle publish
    $('#menuPeriodsTable').on('change', '.toggle-publish', function(){
        var id = $(this).data('id');
        var $cb = $(this);
        var intended = $cb.is(':checked');
        var periodName = $cb.closest('tr').find('a').text().trim();
        var action = intended ? 'published' : 'unpublished';

        $cb.prop('disabled', true);
        $.post({
            url: '{{ url('menu-periods') }}' + '/' + id + '/toggle',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: { publish: intended }
        })
        .done(function(resp){
            $cb.prop('checked', !!resp.publish);
            console.log('✅ Menu period publish toggled:', resp);

            // Log activity
            if (typeof logActivity === 'function') {
                logActivity('menu_periods', action, action.charAt(0).toUpperCase() + action.slice(1) + ' menu period: ' + periodName);
            }
        })
        .fail(function(xhr){
            $cb.prop('checked', !intended);
            alert('Failed to update ('+xhr.status+'): '+xhr.statusText);
        })
        .always(function(){ $cb.prop('disabled', false); });
    });

    // Single delete
    $('#menuPeriodsTable tbody').on('click', '.delete-btn, .delete-menu-period', async function () {
        var id = $(this).data('id') || $(this).attr('id');
        var periodName = $(this).closest('tr').find('a').text().trim() || 'Unknown';

        console.log('🗑️ Delete menu period clicked:', { id: id, name: periodName });

        if(!id){ alert('This item cannot be deleted because it has no ID.'); return; }

        if (confirm('Are you sure you want to delete this menu period?')) {
            jQuery("#data-table_processing").show();

            $.post({
                url: '{{ url('/menu-periods') }}' + '/' + id + '/delete',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .done(function(response){
                console.log('✅ Menu period deleted successfully:', response);

                // Log activity
                if (typeof logActivity === 'function') {
                    logActivity('menu_periods', 'deleted', 'Deleted menu period: ' + periodName);
                }

                selectedMenuPeriods.delete(id);
                $('#menuPeriodsTable').DataTable().ajax.reload(null,false);

                if (typeof toastr !== 'undefined') {
                    toastr.success(response.message || 'Menu period deleted successfully');
                }
            })
            .fail(function(xhr){
                console.error('❌ Delete failed:', xhr);
                alert('Failed to delete ('+xhr.status+'): '+xhr.statusText);
            })
            .always(function(){ jQuery("#data-table_processing").hide(); });
        }
    });

    // Bulk delete
    $("#deleteAll").click(async function () {
        if ($('#menuPeriodsTable .is_open:checked').length) {
            var selectedCount = $('#menuPeriodsTable .is_open:checked').length;

            console.log('🗑️ Bulk delete menu periods requested:', { count: selectedCount });

            if (confirm("Delete selected menu periods?")) {
                jQuery("#data-table_processing").show();

                // Log activity for bulk delete
                if (typeof logActivity === 'function') {
                    logActivity('menu_periods', 'bulk_deleted', 'Bulk deleted ' + selectedCount + ' menu periods');
                }

                $('#menuPeriodsTable .is_open:checked').each(function () {
                    var id = $(this).attr('dataId');
                    $.post({ url: '{{ url('/menu-periods') }}' + '/' + id + '/delete', async:false, headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                    selectedMenuPeriods.delete(id);
                });

                setTimeout(function(){
                    $('#menuPeriodsTable').DataTable().ajax.reload(null,false);
                    jQuery("#data-table_processing").hide();
                }, 300);
            }
        } else {
            alert("Select at least one menu period to delete.");
        }
    });
</script>
@endsection
