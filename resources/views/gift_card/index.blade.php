@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.gift_card_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.gift_card_table')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/gift_card.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.gift_card_plural')}}</h3>
                        <span class="counter ml-3 gift_count"></span>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.gift_card_table')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.gift_card_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                     <div class="card-header-btn mr-3">
                        <a class="btn-primary btn rounded-full" href="{!! route('gift-card.save') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.create_gift_card')}}</a>
                     </div>
                   </div>
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="giftCardTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <?php if (in_array('gift-card.delete', json_decode(@session('user_permissions'),true))) { ?>
                                            <th class="delete-all">
                                                <input type="checkbox" id="is_active">
                                                <label class="col-3 control-label" for="is_active">
                                                    <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a>
                                                </label>
                                            </th>
                                        <?php } ?>
                                        <th>{{trans('lang.title')}}</th>
                                        <th>{{trans('lang.expires_in')}}</th>
                                        <th>{{trans('lang.status')}}</th>
                                        <th>{{trans('lang.actions')}}</th>
                                    </tr>
                                </thead>
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
@section('style')
<style>
/* Toggle switch */
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
@endsection
@section('scripts')
<script type="text/javascript">
    var user_permissions = '<?php echo @session("user_permissions")?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = ($.inArray('gift-card.delete', user_permissions) >= 0);

    $(document).ready(function(){
        console.log('📡 Initializing Gift Cards DataTable...');

        const table = $('#giftCardTable').DataTable({
            pageLength: 30,
            lengthMenu: [[10, 25, 30, 50, 100, -1], [10, 25, 30, 50, 100, "All"]],
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: function (data, callback) {
                const params = { start: data.start, length: data.length, draw: data.draw,
                    search: { value: data.search.value }
                };
                console.log('📡 Fetching gift cards:', params);

                $.get('{{ route('gift-card.data') }}', params, function(json){
                    console.log('📥 Gift cards response:', json);

                    // Update count display
                    if (json.stats && json.stats.total) {
                        $('.gift_count').text(json.stats.total);
                        console.log('📊 Total gift cards:', json.stats.total);
                    } else {
                        $('.gift_count').text(json.recordsTotal || 0);
                    }

                    callback(json);
                })
                .fail(function(xhr){
                    console.error('❌ Error loading gift cards:', xhr);
                });
            },
            order: (checkDeletePermission) ? [1, 'asc'] : [0,'asc'],
            columnDefs: [ { orderable: false, targets: (checkDeletePermission) ? [0, 3, 4] : [2, 3] } ],
            language: { zeroRecords: "{{trans('lang.no_record_found')}}", emptyTable: "{{trans('lang.no_record_found')}}", processing: "" }
        });

        // Toggle enable/disable
        $('#giftCardTable').on('change', '.toggle-enable', function(){
            var id = $(this).data('id');
            var isEnable = $(this).is(':checked');
            var $cb = $(this);
            var giftName = $cb.closest('tr').find('a').text().trim() || 'Unknown';
            var action = isEnable ? 'enabled' : 'disabled';

            console.log('🔄 Toggle gift card status:', { id: id, name: giftName, status: isEnable });

            $cb.prop('disabled', true);
            $.post({ url: '{{ url('gift-card') }}' + '/' + id + '/toggle', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, data: { isEnable: isEnable } })
                .done(function(resp){
                    console.log('✅ Gift card status toggled:', resp);
                    $cb.prop('checked', !!resp.isEnable);

                    // Log activity
                    if (typeof logActivity === 'function') {
                        logActivity('gift_cards', action, action.charAt(0).toUpperCase() + action.slice(1) + ' gift card: ' + giftName);
                    }
                })
                .fail(function(xhr){
                    console.error('❌ Toggle failed:', xhr);
                    $cb.prop('checked', !isEnable);
                    alert('Failed to update ('+xhr.status+'): '+xhr.statusText);
                })
                .always(function(){ $cb.prop('disabled', false); });
        });

        // Single delete
        $('#giftCardTable').on('click', '.delete-gift', function(){
            var id = $(this).data('id');
            var giftName = $(this).closest('tr').find('a').text().trim() || 'Unknown';

            console.log('🗑️ Delete gift card clicked:', { id: id, name: giftName });

            if(!confirm("{{trans('lang.selected_delete_alert')}}")) return;

            jQuery("#data-table_processing").show();

            $.post({ url: '{{ url('gift-card') }}' + '/' + id + '/delete', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                .done(function(response){
                    console.log('✅ Gift card deleted successfully:', response);

                    // Log activity
                    if (typeof logActivity === 'function') {
                        logActivity('gift_cards', 'deleted', 'Deleted gift card: ' + giftName);
                    }

                    $('#giftCardTable').DataTable().ajax.reload(null,false);

                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message || 'Gift card deleted successfully');
                    }
                })
                .fail(function(xhr){
                    console.error('❌ Delete failed:', xhr);
                    alert('Failed to delete ('+xhr.status+'): '+xhr.statusText);
                })
                .always(function(){ jQuery("#data-table_processing").hide(); });
        });

        // Select all
        $(document).on('click','#is_active', function(){
            $("#giftCardTable .is_open").prop('checked', $(this).prop('checked'));
        });

        // Bulk delete
        $(document).on('click','#deleteAll', function(){
            var ids = [];
            $('#giftCardTable .is_open:checked').each(function(){ ids.push($(this).attr('dataId')); });

            if(ids.length===0){ alert("{{trans('lang.select_delete_alert')}}"); return; }

            var selectedCount = ids.length;
            console.log('🗑️ Bulk delete gift cards requested:', { count: selectedCount });

            if(!confirm("{{trans('lang.selected_delete_alert')}}")) return;

            jQuery("#data-table_processing").show();

            $.post({ url: '{{ route('gift-card.bulkDelete') }}', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, data: { ids: ids } })
                .done(function(response){
                    console.log('✅ Bulk delete completed:', response);

                    // Log activity
                    if (typeof logActivity === 'function') {
                        logActivity('gift_cards', 'bulk_deleted', 'Bulk deleted ' + (response.deleted || selectedCount) + ' gift cards');
                    }

                    $('#giftCardTable').DataTable().ajax.reload(null,false);

                    if (typeof toastr !== 'undefined') {
                        toastr.success('Deleted ' + (response.deleted || selectedCount) + ' gift cards');
                    }
                })
                .fail(function(xhr){
                    console.error('❌ Bulk delete failed:', xhr);
                    alert('Failed to delete ('+xhr.status+'): '+xhr.statusText);
                })
                .always(function(){ jQuery("#data-table_processing").hide(); });
        });
    });
</script>
@endsection
