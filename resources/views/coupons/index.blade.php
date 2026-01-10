@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.coupon_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.coupon_table')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/coupon.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.coupon_plural')}}</h3>
                        <span class="counter ml-3 coupon_count"></span>
                    </div>
                    <div class="d-flex top-title-right align-self-center">
                        <div class="select-box pl-3">
                            <select class="form-control coupon_type_selector filteredRecords extraordinary-select">
                                <option value="" selected>🎯 {{trans("lang.coupon_type")}}</option>
                                <option value="restaurant">🍽️ {{trans("lang.restaurant")}}</option>
                                <option value="mart">🛒 {{trans("lang.mart")}}</option>
                            </select>
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
           <?php if ($id != '') { ?>
                    <div class="menu-tab">
                        <ul>
                            <li>
                                <a href="{{route('restaurants.view', $id)}}">{{trans('lang.tab_basic')}}</a>
                            </li>
                            <li>
                                <a href="{{route('restaurants.foods', $id)}}">{{trans('lang.tab_foods')}}</a>
                            </li>
                            <li>
                                <a href="{{route('restaurants.orders', $id)}}">{{trans('lang.tab_orders')}}</a>
                            </li>
                            <li class="active">
                                <a href="{{route('restaurants.coupons', $id)}}">{{trans('lang.tab_promos')}}</a>
                            <li>
                                <a href="{{route('restaurants.payout', $id)}}">{{trans('lang.tab_payouts')}}</a>
                            </li>
                            <li>
                                <a href="{{route('payoutRequests.restaurants.view', $id)}}">{{trans('lang.tab_payout_request')}}</a>
                            </li>
                            <li>
                                <a href="{{route('restaurants.booktable', $id)}}">{{trans('lang.dine_in_future')}}</a>
                            </li>
                            <li id="restaurant_wallet"></li>
                            <li id="subscription_plan"></li>
                        </ul>
                    </div>
                <?php } ?>
               <div class="card border">
                 <div class="card-header d-flex justify-content-between align-items-center border-0">
                   <div class="card-header-title">
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.coupon_table')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.coupons_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                        <div class="card-header-btn mr-3">
                            <?php if ($id != '') { ?>
                                <a class="btn-primary btn rounded-full" href="{!! route('coupons.create') !!}/{{$id}}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.coupon_create')}}</a>
                            <?php } else { ?>
                                <a class="btn-primary btn rounded-full" href="{!! route('coupons.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.coupon_create')}}</a>
                            <?php } ?>
                        </div>
                   </div>
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                         <table id="couponTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <?php if (in_array('coupons.delete', json_decode(@session('user_permissions'), true))) { ?>
                                    <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label>
                                    <?php } ?>
                                    </th>
                                    <th>{{trans('lang.coupon_code')}}</th>
                                    <th>{{trans('lang.coupon_discount')}}</th>
                                    <th>Item Value</th>
                                    <th style="display: none;">Usage Limit</th>
                                    <th>{{trans('lang.coupon_privacy')}}</th>
                                    <th>{{trans('lang.coupon_type')}}</th>
                                    <th>{{trans('lang.coupon_restaurant_id')}}</th>
                                    <th>{{trans('lang.coupon_expires_at')}}</th>
                                    <th>{{trans('lang.coupon_enabled')}}</th>
                                    <th>{{trans('lang.coupon_description')}}</th>
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
<style>
/* 🎨 EXTRAORDINARY COUPON TYPE CARDS */
.coupon-type-container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 2px;
}

.coupon-type-card {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    min-width: 100px;
    backdrop-filter: blur(10px);
}

.coupon-type-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.coupon-type-card:hover::before {
    left: 100%;
}

.type-icon-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    margin-right: 8px;
    position: relative;
    z-index: 2;
}

.type-icon {
    font-size: 14px;
    transition: all 0.3s ease;
}

.type-content {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    position: relative;
    z-index: 2;
}

.type-label {
    font-size: 11px;
    font-weight: 700;
    line-height: 1;
}

.type-indicator {
    width: 100%;
    height: 2px;
    border-radius: 1px;
    margin-top: 2px;
    transition: all 0.3s ease;
}

/* 🍽️ RESTAURANT CARD - Extraordinary Design */
.restaurant-card {
    background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
    color: white;
    border: 2px solid #ff5252;
    box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
}

.restaurant-card .type-icon-wrapper {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(5px);
}

.restaurant-card .type-icon {
    color: #fff;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.restaurant-card .type-indicator {
    background: linear-gradient(90deg, #fff, #ffebee);
    box-shadow: 0 1px 3px rgba(255, 255, 255, 0.3);
}

.restaurant-card:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4);
    border-color: #ff1744;
}

.restaurant-card:hover .type-icon {
    transform: rotate(15deg) scale(1.1);
    color: #ffebee;
}

/* 🛒 MART CARD - Extraordinary Design */
.mart-card {
    background: linear-gradient(135deg, #4ecdc4, #44a08d);
    color: white;
    border: 2px solid #26a69a;
    box-shadow: 0 4px 15px rgba(78, 205, 196, 0.3);
}

.mart-card .type-icon-wrapper {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(5px);
}

.mart-card .type-icon {
    color: #fff;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.mart-card .type-indicator {
    background: linear-gradient(90deg, #fff, #e0f2f1);
    box-shadow: 0 1px 3px rgba(255, 255, 255, 0.3);
}

.mart-card:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(78, 205, 196, 0.4);
    border-color: #00bcd4;
}

.mart-card:hover .type-icon {
    transform: rotate(-15deg) scale(1.1);
    color: #e0f2f1;
}

/* ❓ DEFAULT CARD - Extraordinary Design */
.default-card {
    background: linear-gradient(135deg, #a8a8a8, #757575);
    color: white;
    border: 2px solid #616161;
    box-shadow: 0 4px 15px rgba(168, 168, 168, 0.3);
}

.default-card .type-icon-wrapper {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(5px);
}

.default-card .type-icon {
    color: #fff;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.default-card .type-indicator {
    background: linear-gradient(90deg, #fff, #f5f5f5);
    box-shadow: 0 1px 3px rgba(255, 255, 255, 0.3);
}

.default-card:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(168, 168, 168, 0.4);
    border-color: #424242;
}

.default-card:hover .type-icon {
    transform: rotate(360deg) scale(1.1);
    color: #f5f5f5;
}

/* 🌟 PULSE ANIMATION */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.coupon-type-card.pulse {
    animation: pulse 2s infinite;
}

/* 🎭 GLOW EFFECT */
@keyframes glow {
    0% { box-shadow: 0 0 5px currentColor; }
    50% { box-shadow: 0 0 20px currentColor, 0 0 30px currentColor; }
    100% { box-shadow: 0 0 5px currentColor; }
}

.coupon-type-card.glow {
    animation: glow 2s infinite;
}

/* 📱 RESPONSIVE DESIGN */
@media (max-width: 768px) {
    .coupon-type-card {
        min-width: 80px;
        padding: 6px 10px;
    }

    .type-icon-wrapper {
        width: 20px;
        height: 20px;
        margin-right: 6px;
    }

    .type-icon {
        font-size: 12px;
    }

    .type-label {
        font-size: 10px;
    }
}

/* 🎨 DARK MODE SUPPORT */
@media (prefers-color-scheme: dark) {
    .coupon-type-card {
        backdrop-filter: blur(15px);
    }
}

/* ⚡ PERFORMANCE OPTIMIZATIONS */
.coupon-type-card {
    will-change: transform, box-shadow;
    transform: translateZ(0);
    backface-visibility: hidden;
}

/* 🎨 EXTRAORDINARY SELECT DROPDOWN */
.extraordinary-select {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: 2px solid #5a67d8;
    border-radius: 12px;
    padding: 8px 12px;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
}

.extraordinary-select:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    border-color: #4c51bf;
}

.extraordinary-select:focus {
    outline: none;
    border-color: #3182ce;
    box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
}

.extraordinary-select option {
    background: white;
    color: #2d3748;
    padding: 8px;
    font-weight: 500;
}

.extraordinary-select option:hover {
    background: #f7fafc;
}

/* 🌟 FILTER ACTIVE STATE */
.extraordinary-select.filter-active {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    border-color: #e53e3e;
    animation: pulse 1.5s infinite;
}

/* 🎭 LOADING ANIMATION */
@keyframes shimmer {
    0% { background-position: -200px 0; }
    100% { background-position: calc(200px + 100%) 0; }
}

.coupon-type-card.loading {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200px 100%;
    animation: shimmer 1.5s infinite;
}
</style>
@endsection
@section('scripts')
<script type="text/javascript">
    var vendorId = '{{$id}}';
    var user_permissions = '<?php echo @session("user_permissions")?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = ($.inArray('coupons.delete', user_permissions) >= 0);

    $(document).ready(function(){
        console.log('📡 Initializing Coupons DataTable...');

        const table = $('#couponTable').DataTable({
            pageLength: 30,
            lengthMenu: [[10, 25, 30, 50, 100, -1], [10, 25, 30, 50, 100, "All"]],
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: function (data, callback) {
                const params = { start: data.start, length: data.length, draw: data.draw, search: { value: data.search.value}, vendorId: vendorId, couponType: $('.coupon_type_selector').val() };
                console.log('📡 Fetching coupons:', params);

                $.get('{{ route('coupons.data') }}', params, function(json){
                    console.log('📥 Coupons response:', json);

                    // Update count display
                    if (json.stats && json.stats.total) {
                        $('.coupon_count').text(json.stats.total);
                        console.log('📊 Total coupons:', json.stats.total);
                    } else {
                        $('.coupon_count').text(json.recordsTotal || 0);
                    }

                    callback(json);
                })
                .fail(function(xhr){
                    console.error('❌ Error loading coupons:', xhr);
                });
            },
            order: (checkDeletePermission) ? [6, 'desc'] : [5, 'desc'],
            columnDefs: [ { targets: (checkDeletePermission) ? [0, 4, 7, 9] : [4, 6, 8], orderable: false }, { targets: 4, visible: false } ],
            language: { zeroRecords: "{{trans('lang.no_record_found')}}", emptyTable: "{{trans('lang.no_record_found')}}" }
        });

        // Filter by coupon type
        $('.coupon_type_selector').on('change', function(){
            console.log('🔍 Coupon type filter changed:', $(this).val());
            table.ajax.reload();
        });

        // Toggle enable
        $('#couponTable').on('change', '.toggle-enable', function(){
            var id = $(this).data('id');
            var isEnabled = $(this).is(':checked');
            var $cb = $(this);
            var couponCode = $cb.closest('tr').find('a').text().trim() || 'Unknown';
            var action = isEnabled ? 'enabled' : 'disabled';

            console.log('🔄 Toggle coupon status:', { id: id, code: couponCode, status: isEnabled });

            $cb.prop('disabled', true);
            $.post({ url: '{{ url('coupons') }}' + '/' + id + '/toggle', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, data: { isEnabled: isEnabled } })
                .done(function(resp){
                    console.log('✅ Coupon status toggled:', resp);
                    $cb.prop('checked', !!resp.isEnabled);

                    // Log activity
                    if (typeof logActivity === 'function') {
                        logActivity('coupons', action, action.charAt(0).toUpperCase() + action.slice(1) + ' coupon: ' + couponCode);
                    }
                })
                .fail(function(xhr){
                    console.error('❌ Toggle failed:', xhr);
                    $cb.prop('checked', !isEnabled);
                    alert('Failed to update ('+xhr.status+'): '+xhr.statusText);
                })
                .always(function(){ $cb.prop('disabled', false); });
        });

        // Single delete
        $('#couponTable').on('click', '.delete-coupon', function(){
            var id = $(this).data('id');
            var couponCode = $(this).closest('tr').find('a').text().trim() || 'Unknown';

            console.log('🗑️ Delete coupon clicked:', { id: id, code: couponCode });

            if(!confirm("{{trans('lang.selected_delete_alert')}}")) return;

            jQuery("#data-table_processing").show();

            $.post({ url: '{{ url('coupons') }}' + '/' + id + '/delete', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                .done(function(response){
                    console.log('✅ Coupon deleted successfully:', response);

                    // Log activity
                    if (typeof logActivity === 'function') {
                        logActivity('coupons', 'deleted', 'Deleted coupon: ' + couponCode);
                    }

                    $('#couponTable').DataTable().ajax.reload(null,false);

                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message || 'Coupon deleted successfully');
                    }
                })
                .fail(function(xhr){
                    console.error('❌ Delete failed:', xhr);
                    alert('Failed to delete ('+xhr.status+'): '+xhr.statusText);
                })
                .always(function(){ jQuery("#data-table_processing").hide(); });
        });

        // Select all and bulk delete
        $(document).on('click','#is_active', function(){ $("#couponTable .is_open").prop('checked', $(this).prop('checked')); });
        $(document).on('click','#deleteAll', function(){
            var ids = [];
            $('#couponTable .is_open:checked').each(function(){ ids.push($(this).attr('dataId')); });

            if(ids.length===0){ alert("{{trans('lang.select_delete_alert')}}"); return; }

            var selectedCount = ids.length;
            console.log('🗑️ Bulk delete coupons requested:', { count: selectedCount });

            if(!confirm("{{trans('lang.selected_delete_alert')}}")) return;

            jQuery("#data-table_processing").show();

            $.post({ url: '{{ route('coupons.bulkDelete') }}', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, data: { ids: ids } })
                .done(function(response){
                    console.log('✅ Bulk delete completed:', response);

                    // Log activity
                    if (typeof logActivity === 'function') {
                        logActivity('coupons', 'bulk_deleted', 'Bulk deleted ' + (response.deleted || selectedCount) + ' coupons');
                    }

                    $('#couponTable').DataTable().ajax.reload(null,false);

                    if (typeof toastr !== 'undefined') {
                        toastr.success('Deleted ' + (response.deleted || selectedCount) + ' coupons');
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

