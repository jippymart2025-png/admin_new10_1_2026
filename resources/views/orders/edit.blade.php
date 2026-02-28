@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.order_plural') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <?php if (isset($_GET['eid']) && $_GET['eid'] != '') { ?>
                    <li class="breadcrumb-item"><a
                            href="{{ route('restaurants.orders', $_GET['eid']) }}">{{ trans('lang.order_plural') }}</a>
                    </li>
                    <?php } else { ?>
                    <li class="breadcrumb-item"><a href="{!! route('orders') !!}">{{ trans('lang.order_plural') }}</a>
                    </li>
                    <?php } ?>
                    <li class="breadcrumb-item">{{ trans('lang.order_edit') }}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card-body pb-5 p-0">
                <div class="text-right print-btn pb-3">
                    <a href="{{ route('vendors.orderprint', $id) }}">
                        <button type="button" class="fa fa-print"></button>
                    </a>
                </div>
                <div class="order_detail" id="order_detail">
                    <div class="order_detail-top">
                        <div class="row">
                            <div class="order_edit-genrl col-lg-7 col-md-12">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h3>{{ trans('lang.general_details') }}</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="order_detail-top-box">
                                            <div class="form-group row widt-100 gendetail-col">
                                                <label
                                                    class="col-12 control-label"><strong>{{ trans('lang.date_created') }}
                                                        : </strong><span id="createdAt"></span></label>
                                            </div>
                                            <div class="form-group row widt-100 gendetail-col payment_method">
                                                <label
                                                    class="col-12 control-label"><strong>{{ trans('lang.payment_methods') }}
                                                        : </strong><span id="payment_method"></span></label>
                                            </div>
                                            <div class="form-group row widt-100 gendetail-col">
                                                <label
                                                    class="col-12 control-label"><strong>{{ trans('lang.order_type') }}
                                                        :</strong>
                                                    <span id="order_type"></span></label>
                                            </div>
                                            <div class="form-group row widt-100 gendetail-col schedule_date">
                                            </div>
                                            <div class="form-group row widt-100 gendetail-col prepare_time">
                                            </div>
                                            <div class="form-group row width-100 ">
                                                <label class="col-3 control-label">{{ trans('lang.status') }}:</label>
                                                <div class="col-7">
                                                    <select id="order_status" class="form-control">
                                                        <option value="Order Placed" id="order_placed">
                                                            {{ trans('lang.order_placed') }}
                                                        </option>
                                                        <option value="Order Accepted" id="order_accepted">
                                                            {{ trans('lang.order_accepted') }}
                                                        </option>
                                                        <option value="Order Rejected" id="order_rejected">
                                                            {{ trans('lang.order_rejected') }}
                                                        </option>
                                                        <option value="Driver Pending" id="driver_pending">
                                                            {{ trans('lang.driver_pending') }}
                                                        </option>
                                                        <option value="Driver Accepted" id="driver_accepted">
                                                            {{ trans('lang.driver_accepted') }}
                                                        </option>
                                                        <option value="Driver Rejected" id="driver_rejected">
                                                            {{ trans('lang.driver_rejected') }}
                                                        </option>
                                                        <option value="Order Shipped" id="order_shipped">
                                                            {{ trans('lang.order_shipped') }}
                                                        </option>
                                                        <option value="In Transit" id="in_transit">
                                                            {{ trans('lang.in_transit') }}
                                                        </option>
                                                        <option value="Order Completed" id="order_completed">
                                                            {{ trans('lang.order_completed') }}
                                                        </option>
                                                        <!-- 🔁 Refund statuses -->
                                                        <option value="Refund Initiated" id="refund_initiated">
                                                            Refund Initiated
                                                        </option>

                                                        <option value="Refund Completed" id="refund_completed">
                                                            Refund Completed
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row width-100">
                                                <label class="col-3 control-label"></label>
                                                <div class="col-7 text-right">
                                                    <button type="button" class="btn btn-primary edit-form-btn"><i
                                                            class="fa fa-save"></i> {{ trans('lang.update') }}
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Manual Driver Assignment Section -->
                                            <div class="form-group row width-100" id="manual_driver_assignment_section">
                                                <label class="col-3 control-label">{{ trans('lang.assign_driver') }}
                                                    :</label>
                                                <div class="col-7">
                                                    <select id="driver_selector" class="form-control">
                                                        <option value="">{{ trans('lang.select_driver') }}</option>
                                                    </select>
                                                    <div class="form-text text-muted">
                                                        {{ trans('lang.manual_driver_assignment_help') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row width-100" id="assign_driver_button_section">
                                                <label class="col-3 control-label"></label>
                                                <div class="col-7 text-right">
                                                    <button type="button" class="btn btn-success assign-driver-btn"
                                                            id="assign_driver_btn">
                                                        <i class="fa fa-user-plus"></i> {{ trans('lang.assign_driver') }}
                                                    </button>
                                                    <button type="button" class="btn btn-warning remove-driver-btn"
                                                            id="remove_driver_btn" style="display: none;">
                                                        <i class="fa fa-user-times"></i> {{ trans('lang.remove_driver') }}
                                                    </button>
                                                </div>
                                            </div>
                                            <!-- Refund Transaction -->
                                            <div class="form-group row width-100 mt-3">
                                                <label class="col-3 control-label">
                                                    Refund Transaction ID
                                                </label>

                                                <div class="col-7 d-flex">
                                                    <input type="text"
                                                           id="refund_transaction_id"
                                                           name="refund_transaction_id"
                                                           class="form-control mr-2"
                                                           placeholder="Enter refund transaction ID">

                                                    <button type="button"
                                                            id="save_transaction_id"
                                                            class="btn btn-primary">
                                                        Save
                                                    </button>
                                                    @if(!empty($order->refund_transaction_id['refunded_at']))
                                                        <div class="form-group row width-100">
                                                            <span class="form-control-plaintext">
                                                                 <strong>Refunded At:</strong>
                                                                    {{ \Carbon\Carbon::parse($order->refund_transaction_id['refunded_at'])
                                                                             ->setTimezone('Asia/Kolkata')
                                                                           ->format('d M Y, h:i A') }}
                                                                          </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="order-items-list mt-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <table cellpadding="0" cellspacing="0"
                                                   class="table table-striped table-valign-middle">
                                                <thead>
                                                <tr>
                                                    <th>{{ trans('lang.item') }}</th>
                                                    <th class="text-center">{{ trans('lang.price') }}</th>
                                                    <th>{{ trans('lang.qty') }}</th>
                                                    <th>{{ trans('lang.extras') }}</th>
                                                    <th>{{ trans('lang.total') }}</th>
                                                </tr>
                                                </thead>
                                                <tbody id="order_products">
                                                </tbody>
                                            </table>
                                            <div class="order-data-row order-totals-items">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="table-responsive bk-summary-table">
                                                            <table class="order-totals">
                                                                <tbody id="order_products_total">
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
                            @php
                                $permissions = json_decode(session('user_permissions', '[]'), true);
                            @endphp
                            <div class="order_addre-edit col-lg-5 col-md-12">
                                @if(in_array('view-billing-details', $permissions))
                                    <div class="card">
                                        <div class="card-header bg-white">
                                            <h3>{{ trans('lang.billing_details') }}</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="address order_detail-top-box">
                                                <p>
                                                    <strong>{{ trans('lang.name') }}: </strong><span
                                                        id="billing_name"></span>
                                                </p>
                                                <p>
                                                    <strong>{{ trans('lang.address') }}: </strong>
                                                    <span id="billing_line1"></span>
                                                    <span id="billing_line2"></span>
                                                    <span id="billing_country"></span>
                                                </p>
                                                <p><strong>{{ trans('lang.email_address') }}:</strong>
                                                    <span id="billing_email"></span>
                                                </p>
                                                <p><strong>{{ trans('lang.phone') }}:</strong>
                                                    <span id="billing_phone"></span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="order_addre-edit driver_details_hide">
                                    <div class="card mt-4">
                                        <div class="card-header bg-white">
                                            <h3>{{ trans('lang.driver_detail') }}</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="address order_detail-top-box">
                                                <p>
                                                    <strong>{{ trans('lang.name') }}: </strong><span
                                                        id="driver_firstName"></span> <span id="driver_lastName"></span><br>
                                                </p>
                                                <p><strong>{{ trans('lang.email_address') }}:</strong>
                                                    <span id="driver_email"></span>
                                                </p>
                                                <p><strong>{{ trans('lang.phone') }}:</strong>
                                                    <span id="driver_phone"></span>
                                                </p>
{{--                                                <p><strong>{{ trans('lang.car_name') }}:</strong>--}}
{{--                                                    <span id="driver_carName"></span>--}}
{{--                                                </p>--}}
{{--                                                <p><strong>{{ trans('lang.car_number') }}:</strong>--}}
{{--                                                    <span id="driver_carNumber"></span>--}}
{{--                                                </p>--}}
                                                <p><strong>{{ trans('lang.zone') }}:</strong>
                                                    <span id="zone_name"></span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="resturant-detail mt-4">
                                    <div class="card">
                                        <div class="card-header bg-white">
                                            <h4 class="card-header-title">{{ trans('lang.restaurant') }}</h4>
                                        </div>
                                        <div class="card-body">
                                            <a href="#" class="row redirecttopage align-items-center"
                                               id="resturant-view">
                                                <div class="col-md-3">
                                                    <img src="" class="resturant-img rounded-circle" alt="vendor"
                                                         width="70px" height="70px">
                                                </div>
                                                <div class="col-md-9">
                                                    <h4 class="vendor-title"></h4>
                                                </div>
                                            </a>
                                            <h5 class="contact-info">{{ trans('lang.contact_info') }}:</h5>
                                            <p><strong>{{ trans('lang.phone') }}:</strong>
                                                <span id="vendor_phone"></span>
                                            </p>
                                            <p><strong>{{ trans('lang.address') }}:</strong>
                                                <span id="vendor_address"></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="order_detail-review mt-4">
                                    <div class="rental-review">
                                        <div class="card">
                                            <div class="card-header bg-white box-header">
                                                <h3>{{ trans('lang.customer_reviews') }}</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="review-inner">
                                                    <div id="customers_rating_and_review">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group col-12 text-center btm-btn">
                <button type="button" class="btn btn-primary edit-form-btn"><i class="fa fa-save"></i>
                    {{ trans('lang.save') }}
                </button>
                <?php if (isset($_GET['eid']) && $_GET['eid'] != '') { ?>
                <a href="{{ route('restaurants.orders', $_GET['eid']) }}" class="btn btn-default"><i
                        class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                <?php } else { ?>
                <a href="{!! route('orders') !!}" class="btn btn-default"><i
                        class="fa fa-undo"></i>{{ trans('lang.cancel') }}
                </a>
                <?php } ?>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>
    <div class="modal fade" id="addPreparationTimeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered location_modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title locationModalTitle">{{ trans('lang.add_preparation_time') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="">
                        <div class="form-row">
                            <div class="form-group row">
                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{ trans('lang.time') }}</label>
                                    <div class="col-12">
                                        <input type="text" name="prepare_time" class="form-control time-picker"
                                               id="prepare_time">
                                        <div id="add_prepare_time_error"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="add-prepare-time-btn">{{ trans('submit') }}
                        </button>
                        <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                            {{ trans('close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('style')
    <style>
        #manual_driver_assignment_section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-top: 15px;
        }

        #manual_driver_assignment_section label {
            font-weight: bold;
            color: #495057;
        }

        .assign-driver-btn {
            margin-right: 10px;
        }

        .remove-driver-btn {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }

        .remove-driver-btn:hover {
            background-color: #e0a800;
            border-color: #d39e00;
            color: #212529;
        }

        /* Promotional Price Styles */
        .promotional-price {
            color: #28a745 !important;
            font-weight: bold;
        }

        .original-price {
            text-decoration: line-through;
            color: #6c757d;
        }

        .promotional_savings {
            color: #28a745 !important;
            font-weight: bold;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }

        /* Promotional Item Badge Styles */
        .promotional-badge {
            background: linear-gradient(45deg, #ff6b6b, #ff8e8e) !important;
            color: white !important;
            padding: 4px 10px !important;
            border-radius: 15px !important;
            font-size: 9px !important;
            font-weight: bold !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            box-shadow: 0 2px 6px rgba(255, 107, 107, 0.4) !important;
            display: inline-block !important;
            margin-top: 4px !important;
            animation: pulse 2s infinite !important;
            text-align: center !important;
            width: fit-content !important;
            border: none !important;
            outline: none !important;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Promotional item row styling */
        .promotional-item-row {
            background: linear-gradient(90deg, rgba(255, 107, 107, 0.05), rgba(255, 142, 142, 0.05));
            border-left: 3px solid #ff6b6b;
        }

        .promotional-item-row td {
            position: relative;
        }
    </style>
@endsection
@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/printThis/1.15.0/printThis.js"></script>
    <script>
        // MySQL-based - Order data passed from controller
        var id = "<?php echo $id; ?>";

        // Order data from PHP
        var orderData = @json($order);
        var currency = @json($currency ?? (object)[]);
        var availableDrivers = @json($availableDrivers ?? []);
        var zoneDrivers = @json($zoneDrivers ?? []);

        // Currency settings
        var currentCurrency = currency?.symbol || '₹';
        var currencyAtRight = currency?.symbolAtRight || false;
        var decimal_degits = currency?.decimal_degits || 2;
        var vendorType = (orderData.vendor_type || '').toLowerCase();

        // Order variables
        var driverId = orderData.driverID || '';
        var old_order_status = orderData.status || '';
        var orderPreviousStatus = orderData.status || '';
        var orderTakeAwayOption = (orderData.takeAway === '1' || orderData.takeAway === 'true' || orderData.takeAway === true);
        var deliveryChargeVal = parseFloat(orderData.deliveryCharge || 0);
        var deliveryCharge = parseFloat(orderData.deliveryCharge || 0);
        var tip_amount = parseFloat(orderData.tip_amount || 0);
        var orderCustomerId = orderData.authorID || '';
        var orderPaytableAmount = parseFloat(orderData.toPayAmount || 0);
        var payment_shared = orderData.payment_shared || false;

        // Driver and vendor info
        var currentDriverId = driverId;
        var customername = (orderData.user_first_name || '') + ' ' + (orderData.user_last_name || '');
        var vendorname = orderData.vendor_title || '';

        // Helper variables
        var append_procucts_list = '';
        var append_procucts_total = '';
        var total_price = 0;
        var place_image = '{{ asset("images/placeholder.png") }}';

        // Provide no-op stubs when Firebase is not available (MySQL build)
        if (typeof window.database === 'undefined') {
            window.database = {
                collection: function() {
                    return {
                        doc: function() {
                            return {
                                id: 'tmp',
                                set: async function() {},
                                update: async function() {},
                                get: async function() { return { docs: [], exists: false, data: function(){ return {}; } }; }
                            };
                        },
                        where: function() { return { get: async function(){ return { docs: [] }; } }; },
                        get: async function(){ return { docs: [] }; }
                    };
                }
            };
        }
        if (typeof window.firebase === 'undefined') {
            window.firebase = { firestore: { FieldValue: { serverTimestamp: function(){ return new Date(); } }, Timestamp: { now: function(){ return { toDate: function(){ return new Date(); } }; } } } };
        }

        {{--// Load available drivers for manual assignment (from PHP)--}}
        {{--function loadAvailableDrivers() {--}}
        {{--    $('#driver_selector').empty();--}}
        {{--    $('#driver_selector').append('<option value="">{{ trans("lang.select_driver") }}</option>');--}}

        {{--    if (availableDrivers && availableDrivers.length > 0) {--}}
        {{--        availableDrivers.forEach(function(driverData) {--}}
        {{--            var driverName = (driverData.firstName || '') + ' ' + (driverData.lastName || '');--}}
        {{--            var driverPhone = driverData.phoneNumber || '';--}}
        {{--            var displayText = driverName + ' (' + driverPhone + ')';--}}

        {{--            $('#driver_selector').append($("<option></option>")--}}
        {{--                .attr("value", driverData.id)--}}
        {{--                .text(displayText));--}}
        {{--        });--}}
        {{--    }--}}
        {{--}--}}

        function loadAvailableDrivers() {
            $('#driver_selector').empty();
            $('#driver_selector').append('<option value="">{{ trans("lang.select_driver") }}</option>');

            if (zoneDrivers && zoneDrivers.length > 0) {
                zoneDrivers.forEach(function(driverData) {
                    var driverName = (driverData.firstName || '') + ' ' + (driverData.lastName || '');
                    var driverPhone = driverData.phoneNumber || '';
                    var displayText = driverName + ' (' + driverPhone + ')';

                    $('#driver_selector').append(
                        $('<option></option>')
                            .val(driverData.id)
                            .text(displayText)
                    );
                });
            }
        }


        // Initialize driver assignment functionality
        function initializeDriverAssignment() {
            loadAvailableDrivers();

            // Handle driver assignment
            $('#assign_driver_btn').click(function () {
                var selectedDriverId = $('#driver_selector').val();
                if (!selectedDriverId) {
                    alert('{{ trans("lang.please_select_driver") }}');
                    return;
                }

                if (confirm('{{ trans("lang.confirm_assign_driver") }}')) {
                    assignDriverToOrder(selectedDriverId);
                }
            });

            // Handle driver removal
            $('#remove_driver_btn').click(function () {
                if (confirm('{{ trans("lang.confirm_remove_driver") }}')) {
                    removeDriverFromOrder();
                }
            });
        }

        // Assign driver to order using Laravel route
        function assignDriverToOrder(driverId) {
            // Disable button and show loading state
            var $btn = $('#assign_driver_btn');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Assigning...');

            console.log('🚗 Assigning driver to order:', { orderId: id, driverId: driverId });

            $.ajax({
                url: '{{ route("orders.assign.driver", ":id") }}'.replace(':id', id),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    driver_id: driverId
                },
                success: function(response) {
                    if (response.success) {
                        console.log('✅ Order request sent to driver:', response);

                        // Log activity - note: this is a request, not an assignment
                        if (typeof logActivity === 'function') {
                            logActivity('orders', 'driver_request_sent', 'Order request sent to driver ' + (response.driver_name || driverId) + ' for order #' + id + ' (pending acceptance)');
                        }

                        // Show success message from server (indicates it's a request, not direct assignment)
                        var successMsg = response.message || 'Order request sent to driver. Driver will receive a notification to accept or reject.';
                        alert(successMsg);

                        // Reload to show updated state (order will still show no driver until driver accepts)
                        window.location.reload();
                    } else {
                        // Reset button on failure
                        $btn.prop('disabled', false).html('<i class="fa fa-user-plus"></i> {{ trans("lang.assign_driver") }}');
                        alert('Error: ' + (response.message || 'Failed to send order request to driver'));
                    }
                },
                error: function(xhr) {
                    console.error('❌ Error assigning driver:', xhr);
                    // Reset button on error
                    $btn.prop('disabled', false).html('<i class="fa fa-user-plus"></i> {{ trans("lang.assign_driver") }}');

                    var errorMsg = '{{ trans("lang.error_assigning_driver") }}';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                }
            });
        }

        // Remove driver from order using Laravel route
        function removeDriverFromOrder() {
            // Disable button and show loading state
            var $btn = $('#remove_driver_btn');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Removing...');

            console.log('🚗 Removing driver from order:', { orderId: id });

            $.ajax({
                url: '{{ route("orders.remove.driver", ":id") }}'.replace(':id', id),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        console.log('✅ Driver removed successfully:', response);

                        // Log activity
                        if (typeof logActivity === 'function') {
                            logActivity('orders', 'driver_removed', 'Removed driver ' + (response.old_driver_name || response.old_driver_id) + ' from order #' + id);
                        }

                        // Show success message and reload
                        alert('{{ trans("lang.driver_removed_successfully") }}');
                        window.location.reload();
                    } else {
                        // Reset button on failure
                        $btn.prop('disabled', false).html('<i class="fa fa-user-times"></i> {{ trans("lang.remove_driver") }}');
                        alert('Error: ' + (response.message || 'Failed to remove driver'));
                    }
                },
                error: function(xhr) {
                    console.error('❌ Error removing driver:', xhr);
                    // Reset button on error
                    $btn.prop('disabled', false).html('<i class="fa fa-user-times"></i> {{ trans("lang.remove_driver") }}');

                    var errorMsg = '{{ trans("lang.error_removing_driver") }}';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                }
            });
        }

        let _deliverySettingsCache = null;

        async function getBaseDeliveryCharge() {
            if (_deliverySettingsCache) {
                return parseFloat(_deliverySettingsCache.base_delivery_charge || 0);
            }

            try {
                const res = await fetch('{{ route("api.deliveryCharge.settings") }}', {
                    headers: { 'Accept': 'application/json' }
                });

                if (!res.ok) throw new Error('Failed to fetch delivery settings');

                _deliverySettingsCache = await res.json();
                console.log('🚚 Delivery settings loaded:', _deliverySettingsCache);

                return parseFloat(_deliverySettingsCache.base_delivery_charge || 0);
            } catch (e) {
                console.error('❌ Delivery settings fetch failed, fallback used', e);
                return 23; // absolute fallback safety
            }
        }


        $(document).ready(async function () {
            // Initialize driver assignment
            initializeDriverAssignment();

            $('.time-picker').timepicker({
                timeFormat: "HH:mm",
                showMeridian: false,
                format24: true
            });
            $('.time-picker').timepicker().on('changeTime.timepicker', function (e) {
                var hours = e.time.hours,
                    min = e.time.minutes;
                if (hours < 10) {
                    $(e.currentTarget).val('0' + hours + ':' + min);
                }
            });
            $(document.body).on('click', '.redirecttopage', function () {
                var url = $(this).attr('data-url');
                window.location.href = url;
            });

            // MySQL-based: Populate order data from PHP
            var order = orderData || {};
            var vendorOrder = order; // For compatibility
            var vendorIDSafe = order.vendorID || order.vendor_db_id || order.vendor_id || '';
            var productsSafe = Array.isArray(order.products) ? order.products : [];

            // Populate order details
            append_procucts_list = document.getElementById('order_products');
            append_procucts_list.innerHTML = '';
            append_procucts_total = document.getElementById('order_products_total');
            append_procucts_total.innerHTML = '';

            // Billing name
            if (order.address && order.address.name) {
                $("#billing_name").text(order.address.name);
            } else {
                var billingName = (order.user_first_name || '') + ' ' + (order.user_last_name || '');
                if (!billingName.trim() && order.author && order.author.firstName) {
                    billingName = (order.author.firstName || '') + ' ' + (order.author.lastName || '');
                }
                $("#billing_name").text(billingName.trim() || 'N/A');
            }

            $("#trackng_number").text(id);

            // Billing address
            var billingAddressstring = '';
            if (order.address && order.address.address) {
                $("#billing_line1").text(order.address.address);
            }
            if (order.address && order.address.locality) {
                billingAddressstring = order.address.locality;
            }
            if (order.address && order.address.landmark) {
                billingAddressstring = billingAddressstring + " " + order.address.landmark;
            }
            $("#billing_line2").text(billingAddressstring);

            // Billing phone and email
            var userPhone = order.user_phone || (order.author && order.author.phoneNumber) || '';
            $("#billing_phone").text(userPhone ? shortEditNumber(userPhone) : "");

            var userEmail = order.user_email || (order.author && order.author.email) || '';
            if (userEmail) {
                $("#billing_email").html('<a href="mailto:' + userEmail + '">' + shortEmail(userEmail) + '</a>');
            } else {
                $("#billing_email").html("");
            }

            // Created date - robust parsing for MySQL (YYYY-MM-DD HH:mm:ss), ISO, or timestamp
            // Format date like "Oct 1, 2025 11:27 PM"
            function formatDateTime(value){
                try{
                    if(!value){ return ''; }

                    // Parse the date
                    var date = new Date(value);

                    // Check if valid
                    if(isNaN(date.getTime())) {
                        return value; // Return raw value if can't parse
                    }

                    // Format: Oct 1, 2025 11:27 PM
                    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    var month = months[date.getMonth()];
                    var day = date.getDate();
                    var year = date.getFullYear();
                    var hours = date.getHours();
                    var minutes = date.getMinutes();
                    var ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12;
                    hours = hours ? hours : 12; // 0 should be 12
                    minutes = minutes < 10 ? '0' + minutes : minutes;

                    return month + ' ' + day + ', ' + year + ' ' + hours + ':' + minutes + ' ' + ampm;
                }catch(err){
                    console.error('Error formatting date:', err);
                    return value;
                }
            }

            // 🔁 Populate refund transaction if exists
            if (order.refund_transaction_id && order.refund_transaction_id.refund_transaction_id) {

                $('#refund_transaction_id')
                    .val(order.refund_transaction_id.refund_transaction_id)
                    .prop('disabled', true);

                $('#save_transaction_id').hide();

                console.log('💸 Refund transaction loaded:', order.refund_transaction_id);
            }

            if(order.createdAt){
                $('#createdAt').text(formatDateTime(order.createdAt));
                console.log('📅 Formatted order date:', formatDateTime(order.createdAt));
            }
            var payment_method = '';
            if (order.payment_method) {
                if (order.payment_method == "stripe") {
                    image = '{{ asset('images/stripe.png') }}';
                    payment_method = '<img alt="image" src="' + image +
                        '"  width="30%" height="30%">';
                } else if (order.payment_method == "cod") {
                    image = '{{ asset('images/cashondelivery.png') }}';
                    payment_method = '<img alt="image" src="' + image +
                        '"  width="30%" height="30%">';
                } else if (order.payment_method == "razorpay") {
                    image = '{{ asset('images/razorepay.png') }}';
                    payment_method = '<img alt="image" src="' + image +
                        '"  width="30%" height="30%">';
                } else if (order.payment_method == "paypal") {
                    image = '{{ asset('images/paypal.png') }}';
                    payment_method = '<img alt="image" src="' + image +
                        '"  width="30%" height="30%">';
                } else if (order.payment_method == "payfast") {
                    image = '{{ asset('images/payfast.png') }}';
                    payment_method = '<img alt="image" src="' + image +
                        '" width="30%" height="30%">';
                } else if (order.payment_method == "paystack") {
                    image = '{{ asset('images/paystack.png') }}';
                    payment_method = '<img alt="image" src="' + image +
                        '"  width="30%" height="30%">';
                } else if (order.payment_method == "flutterwave") {
                    image = '{{ asset('images/flutter_wave.png') }}';
                    payment_method = '<img alt="image" src="' + image +
                        '"  width="30%" height="30%">';
                } else if (order.payment_method == "mercado pago") {
                    image = '{{ asset('images/marcado_pago.png') }}';
                    payment_method = '<img alt="image" src="' + image +
                        '"  width="30%" height="30%">';
                } else if (order.payment_method == "wallet") {
                    image = '{{ asset('images/foodie_wallet.png') }}';
                    payment_method = '<img alt="image" src="' + image +
                        '"  width="30%" height="30%" >';
                } else if (order.payment_method == "paytm") {
                    image = '{{ asset('images/paytm.png') }}';
                    payment_method = '<img alt="image" src="' + image +
                        '"  width="30%" height="30%">';
                } else if (order.payment_method == "cancelled order payment") {
                    image = '{{ asset('images/cancel_order.png') }}';
                    payment_method = '<img alt="image" src="' + image +
                        '"  width="30%" height="30%">';
                } else if (order.payment_method == "refund amount") {
                    image = '{{ asset('images/refund_amount.png') }}';
                    payment_method = '<img alt="image" src="' + image +
                        '"  width="30%" height="30%">';
                } else if (order.payment_method == "referral amount") {
                    image = '{{ asset('images/reffral_amount.png') }}';
                    payment_method = '<img alt="image" src="' + image +
                        '"  width="30%" height="30%">';
                } else if (order.payment_method == "midtrans") {
                    image = '{{ asset('images/midtrans.png') }}';
                    payment_method = '<img alt="image" src="' + image +
                        '"  width="30%" height="30%">';
                } else if (order.payment_method == "xendit") {
                    image = '{{ asset('images/xendit.png') }}';
                    payment_method = '<img alt="image" src="' + image +
                        '"  width="30%" height="30%">';
                } else if (order.payment_method == "orangepay") {
                    image = '{{ asset('images/orangeMoney.png') }}';
                    payment_method = '<img alt="image" src="' + image +
                        '"  width="30%" height="30%">';
                } else {
                    payment_method = order.payment_method;
                }
            }
            $('#payment_method').html(payment_method);
            if (order.hasOwnProperty('takeAway') && order.takeAway) {
                // $('#driver_pending').hide();
                // $('#driver_rejected').hide();
                // Keep Order Shipped and In Transit visible for all orders
                // $('#order_shipped').hide();
                // $('#in_transit').hide();
                $('#order_type').text('{{ trans('lang.order_takeaway') }}');
                orderTakeAwayOption = true;
            } else {
                $('#order_type').text('{{ trans('lang.order_delivery') }}');
            }
            // Ensure Order Shipped and In Transit are always visible
            $('#order_shipped').show();
            $('#in_transit').show();
            $('#driver_pending').show();
            $('#driver_accepted').show();
            $('#driver_rejected').show();

            if ((order.driver != '' && order.driver != undefined) && (order.takeAway == false)) {
                $('#driver_email').html('<a href="mailto:' + order.driver.email + '">' + shortEmail(order.driver.email) + '</a>');
                $('#driver_firstName').text(order.driver.firstName);
                $('#driver_lastName').text(order.driver.lastName);
                $('#driver_phone').text(shortEditNumber(order.driver.phoneNumber));
                var zoneName = order.zone_name || (order.driver && order.driver.zone) || '';
                $("#zone_name").text(zoneName);

                // Hide manual assignment section when driver is already assigned
                $('#manual_driver_assignment_section').hide();
                $('#assign_driver_button_section').hide();
            } else {
                // MySQL-based: populate vendor and product sections even when no driver is assigned
                $('.order_edit-genrl').removeClass('col-md-7').addClass('col-md-7');
                $('.order_addre-edit').removeClass('col-md-5').addClass('col-md-5');
                $('.driver_details_hide').empty();

                // Show manual assignment section when no driver is assigned
                $('#manual_driver_assignment_section').show();
                $('#assign_driver_button_section').show();
                $('#assign_driver_btn').show();
                $('#remove_driver_btn').hide();
            }

            // --- MySQL-based: Vendor details fill ---
            try {
                var vendorIdVal = order.vendorID || order.vendor_db_id || order.vendor_id;
                var route_view = '';
                if ((order.vendor_type || '').toLowerCase() === 'mart') {
                    route_view = '{{ route('marts.view', ':id') }}'.replace(':id', vendorIdVal || '');
                } else {
                    route_view = '{{ route('restaurants.view', ':id') }}'.replace(':id', vendorIdVal || '');
                }
                if (vendorIdVal) {
                    $('#resturant-view').attr('data-url', route_view);
                }
                if (order.vendor_photo) {
                    $('.resturant-img').attr('src', order.vendor_photo);
                } else {
                    $('.resturant-img').attr('src', place_image);
                }
                if (order.vendor_title) {
                    $('.vendor-title').html(order.vendor_title);
                }
                if (order.vendor_phone) {
                    $('#vendor_phone').text(shortEditNumber(order.vendor_phone));
                } else {
                    $('#vendor_phone').text("");
                }
                if (order.vendor_location) {
                    $('#vendor_address').text(order.vendor_location);
                }
            } catch (e) {}

            // --- MySQL-based: Build products list and totals (fallback if promotional builder not used) ---
            try {
                var products = productsSafe;
                var html = '';
                var total_price_local = 0;
                products.forEach(function (product) {
                    var name = product.name || '';
                    var qty = parseInt(product.quantity || 1);
                    var unit = vendorType === 'mart'
                        ? parseFloat(product.discountPrice || product.price || 0)
                        : parseFloat(product.price || 0);
                    var extras_price = parseFloat(product.extras_price || 0) * qty;
                    var row_total = (unit * qty) + (isNaN(extras_price) ? 0 : extras_price);
                    total_price_local += row_total;

                    var price_val = currencyAtRight ? (unit.toFixed(decimal_degits) + currentCurrency) : (currentCurrency + unit.toFixed(decimal_degits));
                    var extras_val = currencyAtRight ? ((isNaN(extras_price)?0:extras_price).toFixed(decimal_degits) + currentCurrency) : (currentCurrency + (isNaN(extras_price)?0:extras_price).toFixed(decimal_degits));
                    var row_total_val = currencyAtRight ? (row_total.toFixed(decimal_degits) + currentCurrency) : (currentCurrency + row_total.toFixed(decimal_degits));

                    html += '<tr>';
                    html += '<td class="order-product"><div class="order-product-box"><div class="orders-tracking"><h6>' + name + '</h6></div></div></td>';
                    html += '<td class="text-green text-center"><span class="item-price">' + price_val + '</span></td>';
                    html += '<td> × ' + qty + '</td>';
                    html += '<td class="text-green"> + ' + extras_val + '</td>';
                    html += '<td class="text-green"> ' + row_total_val + '</td>';
                    html += '</tr>';
                });
                if (html) {
                    document.getElementById('order_products').innerHTML = html;
                }
            } catch (e) {}

            if (order.driverID != '' && order.driverID != undefined) {
                driverId = order.driverID;
            }
            if (order.vendor && order.vendor.author != '' && order.vendor.author != undefined) {
                vendorAuthor = order.vendor.author;
            }
            var scheduleTime = '';
            if (order.hasOwnProperty('scheduleTime') && order.scheduleTime != null && order.scheduleTime != '') {
                scheduleTime = order.scheduleTime;
                var scheduleDateTime = formatDateTime(scheduleTime);
                $('.schedule_date').append(
                    '<label class="col-12 control-label"><strong>{{ trans('lang.schedule_date_time') }}:</strong><span id=""> ' + scheduleDateTime + '</span></label>');
                console.log('📅 Formatted schedule time:', scheduleDateTime);
            }
            if (order.hasOwnProperty('estimatedTimeToPrepare') && order.estimatedTimeToPrepare !=
                null && order.estimatedTimeToPrepare != '') {
                prepareTime = order.estimatedTimeToPrepare;
                var [h, m] = prepareTime.split(":");
                var hour = h;
                if (h.charAt(0) == "0") {
                    hour = h.charAt(1);
                }
                time = (h == "00") ? m + " minutes" : hour + " hours" + m + " minutes";
                $('.prepare_time').append(
                    '<label class="col-12 control-label "><strong>{{ trans('lang.prepare_time') }}:</strong><span id=""> ' +
                    time + '</span></label>')
            }
            fcmToken = order.author.fcmToken;
            vendorname = order.vendor.title;
            fcmTokenVendor = order.vendor.fcmToken;
            customername = order.author.firstName;
            vendorId = order.vendor.id;
            old_order_status = order.status;
            if (order.payment_shared != undefined) {
                payment_shared = order.payment_shared;
            }

            let promotionalTotals = null;
            try {
                promotionalTotals = await calculatePromotionalTotals(productsSafe, vendorIDSafe);
                console.log('💰 Promotional totals calculated:', promotionalTotals);
            } catch (error) {
                console.error('❌ Error calculating promotional totals:', error);
                console.log('🔄 Continuing without promotional totals...');
                promotionalTotals = null;
            }

            // Store promotional totals globally for use in buildHTMLProductstotal
            window.promotionalTotals = promotionalTotals;

            console.log('🎯 ===== PROMOTIONAL PRICING SYSTEM STATUS =====');
            console.log('🎯 Functions available:', {
                testPromotionalPricing: typeof testPromotionalPricing,
                getPromotionalPrice: typeof getPromotionalPrice,
                buildHTMLProductsListWithPromotions: typeof buildHTMLProductsListWithPromotions,
                calculatePromotionalTotals: typeof calculatePromotionalTotals
            });

            // Test promotional pricing with specific data first
            console.log('🧪 ===== RUNNING PROMOTIONAL PRICING TEST =====');
            try {
                await testPromotionalPricing();
            } catch (error) {
                console.error('❌ Test failed:', error);
            }

            if (productsSafe && productsSafe.length > 0) {
                const testProduct = productsSafe[0];
                console.log('🎯 TEST PRODUCT DETAILS:', {
                    id: testProduct.id,
                    name: testProduct.name,
                    price: testProduct.price,
                    discountPrice: testProduct.discountPrice,
                    vendorID: order.vendorID
                });

                try {
                    const testPriceInfo = await getPromotionalPrice(testProduct, order.vendorID);
                } catch (error) {
                    console.error('❌ Test price info failed:', error);
                }
            }

            console.log('🎯 About to call buildHTMLProductsListWithPromotions...');
            var productsListHTML = '';
            try {
                productsListHTML = await buildHTMLProductsListWithPromotions(productsSafe, vendorIDSafe);
                console.log('🎯 buildHTMLProductsListWithPromotions completed successfully');
            } catch (error) {
                console.error('❌ buildHTMLProductsListWithPromotions failed:', error);
                console.log('🔄 Falling back to original buildHTMLProductsList');
                productsListHTML = buildHTMLProductsList(order.products || []);
            }

            // Build totals with hard fallback
            var productstotalHTML = '';
            try {
                productstotalHTML = await buildHTMLProductstotal(order);
            } catch (e) {
                console.error('❌ buildHTMLProductstotal failed, using fallback:', e);
                productstotalHTML = simpleBuildTotals(order);
            }

            append_procucts_list.innerHTML = productsListHTML || '';
            append_procucts_total.innerHTML = (productstotalHTML && productstotalHTML.trim().length>0) ? productstotalHTML : simpleBuildTotals(order);
            console.log('✅ Totals rendered');
            orderPreviousStatus = order.status;
            if (order.hasOwnProperty('payment_method')) {
                orderPaymentMethod = order.payment_method;
            }

            // Set selected status with case-insensitive matching and status normalization
            var currentStatus = order.status || '';
            var statusNormalized = currentStatus.toLowerCase().trim();

            // Map database status values to dropdown values
            var statusMap = {
                'order shipped': 'Order Shipped',
                // 'restaurantorders shipped': 'Order Shipped',
                // 'orders shipped': 'Order Shipped',
                'in transit': 'In Transit',
                // 'order in transit': 'In Transit',
                'order placed': 'Order Placed',
                // 'restaurantorders placed': 'Order Placed',
                // 'orders placed': 'Order Placed',
                'order accepted': 'Order Accepted',
                // 'restaurantorders accepted': 'Order Accepted',
                'orders accepted': 'Order Accepted',
                'order rejected': 'Order Rejected',
                // 'restaurantorders rejected': 'Order Rejected',
                // 'orders rejected': 'Order Rejected',
                'driver pending': 'Driver Pending',
                'driver accepted': 'Driver Accepted',
                'driver rejected': 'Driver Rejected',
                'order completed': 'Order Completed',
                // 'restaurantorders completed': 'Order Completed',
                'orders completed': 'Order Completed',
                // 🔁 Refund statuses
                'refund initiated': 'Refund Initiated',
                'refund completed': 'Refund Completed'
            };

            // Try exact match first
            var matchedValue = currentStatus;
            if (statusMap[statusNormalized]) {
                matchedValue = statusMap[statusNormalized];
            }

            // Set selected option
            $("#order_status option[value='" + matchedValue + "']").attr("selected", "selected");

            // If no match found, try to find by partial match
            if (!$("#order_status option[value='" + matchedValue + "']").length) {
                $("#order_status option").each(function() {
                    var optionValue = $(this).val().toLowerCase();
                    if (optionValue === statusNormalized || optionValue.indexOf(statusNormalized) !== -1 || statusNormalized.indexOf(optionValue) !== -1) {
                        $(this).attr("selected", "selected");
                        return false; // break loop
                    }
                });
            }

            if (order.status == "restaurantorders Rejected" || order.status == "Driver Rejected") {
                $("#order_status").prop("disabled", true);
            }
            var price = 0;
            if (order.authorID) {
                orderCustomerId = order.authorID;
            }
            // Firebase vendor fetch removed; vendor details already filled from MySQL above
            tip_amount = order.tip_amount;
            jQuery("#data-table_processing").hide();

            // Load reviews for this order (MySQL-based)
            initializeReviews();
        })

        function getTwentyFourFormat(h, timeslot) {
            if (h < 10 && timeslot == "PM") {
                h = parseInt(h) + 12;
            } else if (h < 10 && timeslot == "AM") {
                h = '0' + h;
            }
            return h;
        }

        $('#add-prepare-time-btn').click(function () {
            var preparationTime = $('#prepare_time').val();
            if (preparationTime == '') {
                $('#add_prepare_time_error').text('{{ trans('lang.add_prepare_time_error') }}');
                return false;
            }
            alert('Preparation time update is not available in this build.');
            $('#addPreparationTimeModal').modal('hide');
        });

        async function callAjax() {
            await $.ajax({
                type: 'POST',
                url: "<?php echo route('order-status-notification'); ?>",
                data: {
                    _token: '<?php echo csrf_token(); ?>',
                    'fcm': manfcmTokenVendor,
                    'vendorname': manname,
                    'orderStatus': "restaurantorders Accepted",
                    'subject': orderAcceptedSubject,
                    'message': orderAcceptedMsg
                },
                success: function (data) {
                    window.location.href = '{{ route('orders') }}';
                }
            });
        }

        $(".edit-form-btn").click(function () {
            var clientName = $(".client_name").val();
            var orderStatus = $("#order_status").val();
            const refundTxnId = $('#refund_transaction_id').val().trim();

            if (old_order_status != orderStatus) {
                // Update order status via Laravel route
                console.log('🔄 Updating order status:', { orderId: id, oldStatus: old_order_status, newStatus: orderStatus });
                // 🔒 refund validation only for refund completed
                if (orderStatus === 'Refund Completed') {
                    if (!refundTxnId) {
                        alert('Please enter refund transaction ID');
                        return;
                    }
                }
                $.ajax({
                    type: 'POST',
                    url: '{{ route("orders.update.status", ":id") }}'.replace(':id', id),
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: orderStatus
                    },
                    success: function(response) {
                        if (response.success) {
                            console.log('✅ Order status updated:', response);

                            // Log activity
                            if (typeof logActivity === 'function') {
                                logActivity('orders', 'status_updated', 'Updated order #' + id + ' status from "' + old_order_status + '" to "' + orderStatus + '"');
                            }

                            alert('Order status updated successfully');
                            <?php if (isset($_GET['eid']) && $_GET['eid'] != '') { ?>
                                window.location.href = "{{ route('restaurants.orders', $_GET['eid']) }}";
                            <?php } else { ?>
                            {{--window.location.href = '{{ route('orders') }}';--}}
                            // window.location.reload();
                            <?php } ?>
                        } else {
                            alert('Error: ' + (response.message || 'Failed to update status'));
                        }
                    },
                    error: function(xhr) {
                        console.error('❌ Error updating order status:', xhr);
                        alert('Error updating order status');
                    }
                });
            } else {
                // No status change, just close
                <?php if (isset($_GET['eid']) && $_GET['eid'] != '') { ?>
                    window.location.href = "{{ route('restaurants.orders', $_GET['eid']) }}";
                <?php } else { ?>
                    window.location.href = '{{ route('orders') }}';
                <?php } ?>
            }
            // Close click handler cleanly
        });
        // Initialize promotional pricing interceptor to catch any order loading
        function initializePromotionalPricingInterceptor() {
            console.log('🔄 ===== INITIALIZING PROMOTIONAL PRICING INTERCEPTOR =====');

            // Monitor DOM changes to detect when order data is loaded
            let orderProcessingTimeout;
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        // Check if order products table was updated
                        const orderProductsTable = document.getElementById('order_products');
                        const orderTotalTable = document.getElementById('order_products_total');

                        if (orderProductsTable && orderProductsTable.children.length > 0) {
                            console.log('🔄 restaurantorders data detected, scheduling promotional pricing check...');

                            // Clear any existing timeout
                            if (orderProcessingTimeout) {
                                clearTimeout(orderProcessingTimeout);
                            }

                            // Schedule promotional pricing processing
                            orderProcessingTimeout = setTimeout(async () => {
                                console.log('🔄 Executing promotional pricing check...');
                                await checkAndApplyPromotionalPricing();
                            }, 1000); // Wait 1 second for data to stabilize
                        }
                    }
                });
            });

            // Start observing
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });

            console.log('✅ Promotional pricing interceptor initialized');
        }

        // Check and apply promotional pricing to current order data
        async function checkAndApplyPromotionalPricing() {
            console.log('🔄 ===== CHECKING FOR PROMOTIONAL PRICING OPPORTUNITIES =====');

            // Prevent infinite loops by checking if promotional pricing has already been applied
            if (window.promotionalPricingApplied) {
                console.log('🔄 Promotional pricing already applied, skipping...');
                return;
            }

            try {
                // Try to get order data from global variables or DOM
                let orderData = null;

                // Check if we have order data in global variables
                if (window.vendorOrder) {
                    orderData = window.vendorOrder;
                    console.log('🔄 Found order data in vendorOrder global variable');
                } else if (window.currentOrder) {
                    orderData = window.currentOrder;
                    console.log('🔄 Found order data in currentOrder global variable');
                } else {
                    console.log('🔄 No global order data found, attempting to extract from DOM...');
                    // Try to extract order data from DOM or other sources
                    return;
                }

                // Check if order has the required data for promotional pricing
                if (orderData && orderData.vendorID && orderData.products) {
                    console.log('🔄 restaurantorders data suitable for promotional pricing, processing...');
                    await processOrderWithPromotionalPricing(orderData);
                } else {
                    console.log('🔄 restaurantorders data not suitable for promotional pricing');
                    console.log('🔄 Vendor ID:', orderData ? orderData.vendorID : 'not found');
                    console.log('🔄 Products:', orderData && orderData.products ? orderData.products.length : 'not found');
                }

            } catch (error) {
                console.error('❌ Error checking promotional pricing:', error);
            }
        }

        // Universal order processing function that works with any order loading system
        async function processOrderWithPromotionalPricing(order) {
            try {
                // Get DOM elements
                const append_procucts_list = document.getElementById('order_products');
                const append_procucts_total = document.getElementById('order_products_total');

                if (!append_procucts_list || !append_procucts_total) {
                    console.log('❌ DOM elements not found, skipping promotional processing');
                    return;
                }

                // Build product list with promotional pricing
                console.log('🎯 Building product list...');
                let productsListHTML = '';
                if (order.vendorID && order.products) {
                    productsListHTML = await buildHTMLProductsList(order.products, order.vendorID);
                } else {
                    console.log('ℹ️ Missing vendor ID or products, using fallback');
                    productsListHTML = await buildHTMLProductsListOriginal(order.products || []);
                }

                // Build product totals with promotional pricing
                console.log('💰 Building product totals...');
                const productstotalHTML = await buildHTMLProductstotal(order);

                // Update DOM
                if (productsListHTML) {
                    append_procucts_list.innerHTML = productsListHTML;
                    console.log('✅ Product list updated with promotional pricing');

                    // Ensure promotional badges are properly styled after DOM update
                    setTimeout(function() {
                        var promotionalBadges = document.querySelectorAll('.promotional-badge');
                        promotionalBadges.forEach(function(badge) {
                            if (!badge.style.background) {
                                badge.style.background = 'linear-gradient(45deg, #ff6b6b, #ff8e8e)';
                                badge.style.color = 'white';
                                badge.style.padding = '4px 10px';
                                badge.style.borderRadius = '15px';
                                badge.style.fontSize = '9px';
                                badge.style.fontWeight = 'bold';
                                badge.style.textTransform = 'uppercase';
                                badge.style.letterSpacing = '0.5px';
                                badge.style.boxShadow = '0 2px 6px rgba(255, 107, 107, 0.4)';
                                badge.style.display = 'inline-block';
                                badge.style.marginTop = '4px';
                                badge.style.textAlign = 'center';
                                badge.style.width = 'fit-content';
                                badge.style.border = 'none';
                                badge.style.outline = 'none';
                                console.log('🎯 Applied inline styles to promotional badge');
                            }
                        });
                    }, 100);
                }

                if (productstotalHTML) {
                    append_procucts_total.innerHTML = productstotalHTML;
                    console.log('✅ Product totals updated with promotional pricing');
                }

                // Set flag to prevent infinite loops
                window.promotionalPricingApplied = true;

                console.log('🚀 ===== UNIVERSAL ORDER PROCESSING COMPLETE =====');

            } catch (error) {
                console.error('❌ Error in universal order processing:', error);
                console.log('🔄 Falling back to original processing...');
            }
        }

        // Test function to verify promotional pricing with specific data
        async function testPromotionalPricing() {
            console.log('🧪 ===== TESTING PROMOTIONAL PRICING =====');
            try {
                // Test with your specific promotional data
                const testProduct = {
                    id: "bRqazcK1Cxo9b5nnXiuM",
                    name: "Veg Manchurian",
                    price: "148",
                    discountPrice: "148"
                };
                const testVendorID = "WYIy8UYfyRi1gNDhd3Gm";

                console.log('🧪 Testing with specific data:', {
                    product: testProduct,
                    vendorID: testVendorID
                });

                const result = await getPromotionalPrice(testProduct, testVendorID);
                console.log('🧪 TEST RESULT:', result);

                return result;
            } catch (error) {
                console.error('🧪 Test failed:', error);
                return null;
            }
        }

        // Clean and robust promotional price checking function with proper hierarchy
        async function getPromotionalPrice(product, vendorID) {
            // MySQL version: no Firebase calls. Use discountPrice (>0) else price
            var price = (product.discountPrice && parseFloat(product.discountPrice) > 0)
                // ? parseFloat(product.discountPrice)
                // : parseFloat(product.price);
                ? parseFloat(product.price)
                : parseFloat(product.price);
            return {
                price: price,
                isPromotional: false,
                promotionId: null,
                originalPrice: price
            };
        }

        // Function to enhance product list with promotional pricing
        async function enhanceProductListWithPromotions(products, vendorID) {
            console.log('🎯 ===== PRODUCT ENHANCEMENT START =====');
            console.log('🎯 Starting promotional price enhancement for', products.length, 'products');
            console.log('🎯 Vendor ID:', vendorID);
            console.log('🎯 Products:', products.map(p => ({ id: p.id, name: p.name, price: p.price, discountPrice: p.discountPrice, quantity: p.quantity })));

            let promotionalItemsCount = 0;
            let regularItemsCount = 0;
            let totalPromotionalSavings = 0;

            for (const product of products) {
                try {
                    const priceInfo = await getPromotionalPrice(product, vendorID);
                    console.log('🎯 Price Info Result:', priceInfo);

                    if (priceInfo.isPromotional) {
                        promotionalItemsCount++;
                        const savings = (priceInfo.originalPrice - priceInfo.price) * (parseInt(product.quantity) || 1);
                        totalPromotionalSavings += savings;

                        // Update the product price in the DOM
                        const productRow = document.querySelector(`[data-product-id="${product.id}"]`);
                        console.log('🎯 Product row found:', productRow);
                        if (productRow) {
                            // Update price display
                            const priceElement = productRow.querySelector('.item-price');
                            console.log('🎯 Price element found:', priceElement);
                            if (priceElement) {
                                const originalPrice = priceInfo.originalPrice;
                                const promotionalPrice = priceInfo.price;

                                // Format prices
                                let originalPriceFormatted = '';
                                let promotionalPriceFormatted = '';

                                if (currencyAtRight) {
                                    originalPriceFormatted = originalPrice.toFixed(decimal_degits) + currentCurrency;
                                    promotionalPriceFormatted = promotionalPrice.toFixed(decimal_degits) + currentCurrency;
                                } else {
                                    originalPriceFormatted = currentCurrency + originalPrice.toFixed(decimal_degits);
                                    promotionalPriceFormatted = currentCurrency + promotionalPrice.toFixed(decimal_degits);
                                }

                                console.log('🎯 Formatted prices:', {
                                    original: originalPriceFormatted,
                                    promotional: promotionalPriceFormatted
                                });

                                // Update price display with promotional styling
                                priceElement.innerHTML = `
                                    <span class="promotional-price" style="color: #28a745; font-weight: bold;">${promotionalPriceFormatted}</span>
                                    <br><span class="original-price" style="text-decoration: line-through; font-size: 12px; color: #6c757d;">Original: ${originalPriceFormatted}</span>
                                `;

                                // Add promotional badge
                                const productNameElement = productRow.querySelector('h6');
                                if (productNameElement && !productNameElement.querySelector('.badge-success')) {
                                    const badge = document.createElement('span');
                                    badge.className = 'badge badge-success';
                                    badge.style.cssText = 'background-color: #28a745; color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px; margin-left: 5px;';
                                    badge.textContent = '🎯 Promotional Price';
                                    productNameElement.appendChild(badge);
                                }

                                console.log('🎯 DOM updated successfully for promotional item');
                            } else {
                                console.log('❌ Price element not found for promotional item');
                            }
                        } else {
                            console.log('❌ Product row not found for promotional item');
                        }
                    } else {
                        regularItemsCount++;
                    }
                } catch (error) {
                    console.error('❌ Error enhancing product with promotional pricing:', error);
                    console.error('❌ Product:', product.name, 'ID:', product.id);
                }
            }
        }

        // Enhanced function to build product list with promotional pricing
        async function buildHTMLProductsListWithPromotions(snapshotsProducts, vendorID) {
            try {
                snapshotsProducts = Array.isArray(snapshotsProducts) ? snapshotsProducts : [];
                console.log('🎯 Products:', snapshotsProducts.length);
                console.log('🎯 Vendor ID:', vendorID);

                var html = '';
                var alldata = [];
                var number = [];
                var totalProductPrice = 0;

                for (const product of snapshotsProducts) {
                    try {
                        // Get promotional price for this product
                        const priceInfo = await getPromotionalPrice(product, vendorID);
                        console.log('🎯 Price Info Result:', priceInfo);

                        getProductInfo(product);
                        var val = product;
                        var product_id = (val.variant_info && val.variant_info.variant_id) ? val.variant_info.variant_id : val.id;
                        html = html + '<tr data-product-id="' + val.id + '">';
                        var extra_html = '';
                        if (product.extras != undefined && product.extras != '' && product.extras.length > 0) {
                            extra_html = extra_html + '<span>';
                            var extra_count = 1;
                            try {
                                product.extras.forEach((extra) => {
                                    if (extra_count > 1) {
                                        extra_html = extra_html + ',' + extra;
                                    } else {
                                        extra_html = extra_html + extra;
                                    }
                                    extra_count++;
                                })
                            } catch (error) {
                            }
                            extra_html = extra_html + '</span>';
                        }
                        html = html + '<td class="order-product"><div class="order-product-box">';
                        if (val.photo != '' && val.photo != null) {
                            html = html + '<img  onerror="this.onerror=null;this.src=\'' + place_image +
                                '\'" class="img-circle img-size-32 mr-2" style="width:60px;height:60px;" src="' + val
                                    .photo + '" alt="image">';
                        } else {
                            html = html + '<img class="img-circle img-size-32 mr-2" style="width:60px;height:60px;" src="' +
                                place_image + '" alt="image">';
                        }
                        html = html + '</div><div class="orders-tracking"><h6>' + val.name +
                            '</h6><div class="orders-tracking-item-details">';
                        if (val.variant_info) {
                            html = html + '<div class="variant-info">';
                            html = html + '<ul>';
                            $.each(val.variant_info.variant_options, function (label, value) {

                                // 🚫 Hide merchant price
                                if (label.toLowerCase().includes('merchant')) {
                                    return; // continue
                                }

                                html = html + '<li class="variant"><span class="label">' + label +
                                    '</span><span class="value">' + value + '</span></li>';
                            });
                            html = html + '</ul>';
                            html = html + '</div>';
                        }
                        if (extra_count > 1 || product.size) {
                            html = html + '<strong>{{ trans('lang.extras') }} :</strong>';
                        }
                        if (extra_count > 1) {
                            html = html +
                                '<div class="extra"><span>{{ trans('lang.extras') }} :</span><span class="ext-item">' +
                                extra_html + '</span></div>';
                        }
                        if (product.size) {
                            html = html +
                                '<div class="type"><span>{{ trans('lang.type') }} :</span><span class="ext-size">' +
                                product.size + '</span></div>';
                        }

                        // Use promotional price if available, otherwise use original price
                        var final_price = priceInfo.price;
                        console.log('🎯 Using final price:', final_price, 'for product:', product.name);
                        console.log('🎯 Is promotional:', priceInfo.isPromotional);

                        price_item = final_price.toFixed(decimal_degits);
                        totalProductPrice = parseFloat(price_item) * parseInt(val.quantity);
                        var extras_price = 0;
                        if (product.extras != undefined && product.extras != '' && product.extras.length > 0) {
                            extras_price_item = (parseFloat(val.extras_price) * parseInt(val.quantity)).toFixed(
                                decimal_degits);
                            if (parseFloat(extras_price_item) != NaN && val.extras_price != undefined) {
                                extras_price = extras_price_item;
                            }
                            totalProductPrice = parseFloat(extras_price) + parseFloat(totalProductPrice);
                        }
                        totalProductPrice = parseFloat(totalProductPrice).toFixed(decimal_degits);
                        if (currencyAtRight) {
                            price_val = parseFloat(price_item).toFixed(decimal_degits) + "" + currentCurrency;
                            extras_price_val = parseFloat(extras_price).toFixed(decimal_degits) + "" + currentCurrency;
                            totalProductPrice_val = parseFloat(totalProductPrice).toFixed(decimal_degits) + "" +
                                currentCurrency;
                        } else {
                            price_val = currentCurrency + "" + parseFloat(price_item).toFixed(decimal_degits);
                            extras_price_val = currentCurrency + "" + parseFloat(extras_price).toFixed(decimal_degits);
                            totalProductPrice_val = currentCurrency + "" + parseFloat(totalProductPrice).toFixed(
                                decimal_degits);
                        }

                        // Add promotional badge and styling if order has promotion (orderData.promotion == 1)
        //                 var promotionalBadge = '';
        //                 var rowClass = '';
        //                 var shouldShowBadge = false;
        //
        //                 // Show badge when order-level promotion is active
        //                 if (orderData && orderData.promotion == 1) {
        //                     promotionalBadge = `
        // <span class="badge badge-warning" style="font-size:16px; padding:4px 8px; margin-left:8px; background-color:#ff9800; color:#fff;">
        //    🎯 PROMO
        // </span>`;
        //
        //                     rowClass = ' promotional-item-row';
        //                     shouldShowBadge = true;
        //
        //                     console.log('🎯 PROMO badge applied for order-level promotion:', product.name, {
        //                         orderPromo: orderData.promotion
        //                     });
        //                 }
                        var promotionalBadge = '';
                        var rowClass = '';
                        var shouldShowBadge = false;

                       // ✅ promo_id comes from JSON
                        if (val.promo_id && Number(val.promo_id) === 1) {

                            promotionalBadge = `
        <span class="badge badge-warning"
              style="font-size:14px; padding:4px 8px; margin-left:8px;">
           🎯 PROMO
        </span>`;

                            rowClass = 'promotional-item-row';
                            shouldShowBadge = true;
                        }


                        html = html + '<td class="text-green text-center"><span class="item-price">' + price_val +
                            '</span><br><span class="base-price-' + product_id + ' text-muted"></span></td><td> × ' + val
                                .quantity + '</td><td class="text-green"> + ' + extras_price_val +
                            '</td><td class="text-green">  ' + totalProductPrice_val + '</td>';
                        html = html + '</tr>';

                        // Update the product name with promotional badge when order has promotion
                        if (shouldShowBadge) {
                            html = html.replace(
                                '<h6>' + val.name + '</h6>',
                                '<h6>' + val.name + promotionalBadge + '</h6>'
                            );
                            // Add promotional row class to the tr element
                            html = html.replace(
                                '<tr data-product-id="' + val.id + '">',
                                '<tr data-product-id="' + val.id + '" class="' + rowClass + '">'
                            );
                        }

                        total_price += parseFloat(totalProductPrice);
                    } catch (error) {
                        console.error('❌ Error processing product:', product.name, error);
                        // Fallback to original pricing if promotional pricing fails
                        console.log('🔄 Falling back to original pricing for:', product.name);
                        // Continue with original logic as fallback
                    }
                }
                totalProductPrice = 0;
                console.log('🎯 ===== PRODUCT LIST BUILD COMPLETE =====');
                return html;
            } catch (error) {
                console.error('❌ Error in buildHTMLProductsListWithPromotions:', error);
                // Fallback to original function if promotional function fails
                console.log('🔄 Falling back to original buildHTMLProductsList function');
                return buildHTMLProductsList(snapshotsProducts);
            }
        }

        // Enhanced function to build product list with automatic promotional pricing detection
        async function buildHTMLProductsList(snapshotsProducts, vendorID) {
            console.log('🎯 ===== BUILDING PRODUCT LIST (ENHANCED) =====');
            console.log('🎯 Products:', snapshotsProducts.length);
            console.log('🎯 Vendor ID:', vendorID);
            console.log('🎯 Will attempt promotional pricing if vendor ID is available');

            // If vendor ID is available, use promotional pricing
            if (vendorID && typeof getPromotionalPrice === 'function') {
                console.log('🎯 Using promotional pricing system');
                return await buildHTMLProductsListWithPromotions(snapshotsProducts, vendorID);
            } else {
                console.log('🎯 Using original pricing system (no vendor ID or promotional functions)');
                return buildHTMLProductsListOriginal(snapshotsProducts);
            }
        }

        // Original product list function (renamed for clarity)
        function buildHTMLProductsListOriginal(snapshotsProducts) {
            console.log('🎯 ===== BUILDING PRODUCT LIST (ORIGINAL) =====');
            var html = '';
            var alldata = [];
            var number = [];
            var totalProductPrice = 0;
            snapshotsProducts.forEach((product) => {
                getProductInfo(product);
                var val = product;
                var product_id = (val.variant_info && val.variant_info.variant_id) ? val.variant_info.variant_id :
                    val.id;
                html = html + '<tr data-product-id="' + val.id + '">';
                var extra_html = '';
                if (product.extras != undefined && product.extras != '' && product.extras.length > 0) {
                    extra_html = extra_html + '<span>';
                    var extra_count = 1;
                    try {
                        product.extras.forEach((extra) => {
                            if (extra_count > 1) {
                                extra_html = extra_html + ',' + extra;
                            } else {
                                extra_html = extra_html + extra;
                            }
                            extra_count++;
                        })
                    } catch (error) {
                    }
                    extra_html = extra_html + '</span>';
                }
                html = html + '<td class="order-product"><div class="order-product-box">';
                if (val.photo != '' && val.photo != null) {
                    html = html + '<img  onerror="this.onerror=null;this.src=\'' + place_image +
                        '\'" class="img-circle img-size-32 mr-2" style="width:60px;height:60px;" src="' + val
                            .photo + '" alt="image">';
                } else {
                    html = html + '<img class="img-circle img-size-32 mr-2" style="width:60px;height:60px;" src="' +
                        place_image + '" alt="image">';
                }
                html = html + '</div><div class="orders-tracking"><h6>' + val.name +
                    '</h6><div class="orders-tracking-item-details">';

                if (val.variant_info) {
                    html = html + '<div class="variant-info">';
                    html = html + '<ul>';
                    $.each(val.variant_info.variant_options, function (label, value) {

                        // 🚫 Hide merchant price
                        if (label.toLowerCase().includes('merchant')) {
                            return; // continue
                        }
                        html = html + '<li class="variant"><span class="label">' + label +
                            '</span><span class="value">' + value + '</span></li>';
                    });
                    html = html + '</ul>';
                    html = html + '</div>';
                }
                if (extra_count > 1 || product.size) {
                    html = html + '<strong>{{ trans('lang.extras') }} :</strong>';
                }
                if (extra_count > 1) {
                    html = html +
                        '<div class="extra"><span>{{ trans('lang.extras') }} :</span><span class="ext-item">' +
                        extra_html + '</span></div>';
                }
                if (product.size) {
                    html = html +
                        '<div class="type"><span>{{ trans('lang.type') }} :</span><span class="ext-size">' +
                        product.size + '</span></div>';
                }
                // HIERARCHY: 1. Promo price (handled by getPromotionalPrice), 2. discountPrice (>0), 3. price
                // var final_price = '';
                // if (val.discountPrice != 0 && val.discountPrice != "" && val.discountPrice != null && !isNaN(val
                //     .discountPrice) && parseFloat(val.discountPrice) > 0) {
                //     final_price = parseFloat(val.discountPrice);
                //     console.log('🎯 Using discountPrice (Hierarchy 2):', final_price);
                // } else {
                //     final_price = parseFloat(val.price);
                //     console.log('🎯 Using regular price (Hierarchy 3):', final_price);

                var final_price = vendorType === 'mart'
                    ? parseFloat(val.discountPrice || val.price || 0)
                    : parseFloat(val.price || 0);

                price_item = final_price.toFixed(decimal_degits);
                totalProductPrice = parseFloat(price_item) * parseInt(val.quantity);
                var extras_price = 0;
                if (product.extras != undefined && product.extras != '' && product.extras.length > 0) {
                    extras_price_item = (parseFloat(val.extras_price) * parseInt(val.quantity)).toFixed(
                        decimal_degits);
                    if (parseFloat(extras_price_item) != NaN && val.extras_price != undefined) {
                        extras_price = extras_price_item;
                    }
                    totalProductPrice = parseFloat(extras_price) + parseFloat(totalProductPrice);
                }
                totalProductPrice = parseFloat(totalProductPrice).toFixed(decimal_degits);
                if (currencyAtRight) {
                    price_val = parseFloat(price_item).toFixed(decimal_degits) + "" + currentCurrency;
                    extras_price_val = parseFloat(extras_price).toFixed(decimal_degits) + "" + currentCurrency;
                    totalProductPrice_val = parseFloat(totalProductPrice).toFixed(decimal_degits) + "" +
                        currentCurrency;
                } else {
                    price_val = currentCurrency + "" + parseFloat(price_item).toFixed(decimal_degits);
                    extras_price_val = currentCurrency + "" + parseFloat(extras_price).toFixed(decimal_degits);
                    totalProductPrice_val = currentCurrency + "" + parseFloat(totalProductPrice).toFixed(
                        decimal_degits);
                }
                html = html + '<td class="text-green text-center"><span class="item-price">' + price_val +
                    '</span><br><span class="base-price-' + product_id + ' text-muted"></span></td><td> × ' + val
                        .quantity + '</td><td class="text-green"> + ' + extras_price_val +
                    '</td><td class="text-green">  ' + totalProductPrice_val + '</td>';
                html = html + '</tr>';
                total_price += parseFloat(totalProductPrice);
            });
            totalProductPrice = 0;
            return html;
        }

        function getProductInfo(product) {
            // MySQL version: use product's own price info
            var base_price = 0;
            var product_id = (product.variant_info && product.variant_info.variant_id) ? product.variant_info.variant_id : product.id;
            // if (product.discountPrice && parseFloat(product.discountPrice) > 0) {
            //     base_price = parseFloat(product.discountPrice);
            // } else if (product.price) {
            //     base_price = parseFloat(product.price);
            // }
            if (vendorType === 'mart') {
                base_price = parseFloat(product.discountPrice || product.price || 0);
            } else {
                base_price = parseFloat(product.price || 0);
            }

            var base_price_format = currencyAtRight
                ? (parseFloat(base_price).toFixed(decimal_degits) + currentCurrency)
                : (currentCurrency + parseFloat(base_price).toFixed(decimal_degits));
            $(".base-price-" + product_id).text('(Base Price: ' + base_price_format + ')');
        }

        // Function to enhance total calculation with promotional savings
        async function enhanceTotalWithPromotionalSavings(products, vendorID) {
            try {
                let totalPromotionalSavings = 0;
                let promotionalItems = [];
                let regularItems = [];

                for (const product of products) {
                    const priceInfo = await getPromotionalPrice(product, vendorID);
                    console.log('💰 Price Info Result:', priceInfo);

                    if (priceInfo.isPromotional) {
                        const savings = (priceInfo.originalPrice - priceInfo.price) * (parseInt(product.quantity) || 1);
                        totalPromotionalSavings += savings;

                        const promotionalItem = {
                            name: product.name,
                            originalPrice: priceInfo.originalPrice,
                            promotionalPrice: priceInfo.price,
                            quantity: parseInt(product.quantity) || 1,
                            savings: savings
                        };
                        promotionalItems.push(promotionalItem);

                    } else {
                        const regularItem = {
                            name: product.name,
                            price: priceInfo.price,
                            quantity: parseInt(product.quantity) || 1,
                            total: priceInfo.price * (parseInt(product.quantity) || 1)
                        };
                        regularItems.push(regularItem);
                    }
                }

                if (totalPromotionalSavings > 0) {
                    console.log('💰 ===== ADDING PROMOTIONAL SAVINGS TO TOTAL =====');
                    console.log('💰 Total promotional savings to display:', totalPromotionalSavings);

                    // Find the total amount row and add promotional savings before it
                    const totalRow = document.querySelector('.grand-total');
                    console.log('💰 Total row found:', totalRow);

                    if (totalRow) {
                        const promotionalSavingsRow = document.createElement('tr');
                        promotionalSavingsRow.className = 'promotional-savings-row';

                        let promotionalSavingsFormatted = '';
                        if (currencyAtRight) {
                            promotionalSavingsFormatted = totalPromotionalSavings.toFixed(decimal_degits) + currentCurrency;
                        } else {
                            promotionalSavingsFormatted = currentCurrency + totalPromotionalSavings.toFixed(decimal_degits);
                        }

                        console.log('💰 Formatted promotional savings:', promotionalSavingsFormatted);

                        promotionalSavingsRow.innerHTML = `
                            <td class="seprater" colspan="2"><hr><span>🎯 Promotional Savings</span></td>
                        `;

                        const savingsRow = document.createElement('tr');
                        savingsRow.innerHTML = `
                            <td class="label">🎯 Promotional Savings</td>
                            <td class="promotional_savings text-success" style="color: #28a745; font-weight: bold;">(-${promotionalSavingsFormatted})</td>
                        `;

                        console.log('💰 Inserting promotional savings rows before total');
                        totalRow.parentNode.insertBefore(promotionalSavingsRow, totalRow);
                        totalRow.parentNode.insertBefore(savingsRow, totalRow);

                        console.log('💰 Promotional savings rows inserted successfully');
                    } else {
                        console.log('❌ Total row not found - cannot add promotional savings');
                    }
                } else {
                    console.log('ℹ️ No promotional savings to add');
                }

                console.log('💰 ===== TOTAL ENHANCEMENT COMPLETE =====');
            } catch (error) {
                console.error('❌ Error enhancing total with promotional savings:', error);
                console.error('❌ Error details:', error);
            }
        }

        // Enhanced function to calculate totals with promotional pricing
        async function calculatePromotionalTotals(products, vendorID) {
            products = Array.isArray(products) ? products : [];

            let promotionalSubtotal = 0;
            let originalSubtotal = 0;
            let promotionalSavings = 0;
            let promotionalItems = [];
            let regularItems = [];

            for (const product of products) {
                const priceInfo = await getPromotionalPrice(product, vendorID);
                console.log('💰 Price Info Result:', priceInfo);

                const quantity = parseInt(product.quantity) || 1;
                // Use discountPrice only if it exists and is greater than 0, otherwise use price
                // const originalPrice = (product.discountPrice && parseFloat(product.discountPrice) > 0)
                //     ? parseFloat(product.price)
                //     : parseFloat(product.price);
                const originalPrice = vendorType === 'mart'
                    ? parseFloat(product.discountPrice || product.price || 0)
                    : parseFloat(product.price || 0);

                const promotionalPrice = priceInfo.price;

                if (priceInfo.isPromotional) {
                    const itemTotal = promotionalPrice * quantity;
                    const originalTotal = originalPrice * quantity;
                    const savings = originalTotal - itemTotal;

                    promotionalSubtotal += itemTotal;
                    originalSubtotal += originalTotal;
                    promotionalSavings += savings;

                    promotionalItems.push({
                        name: product.name,
                        originalPrice: originalPrice,
                        promotionalPrice: promotionalPrice,
                        quantity: quantity,
                        originalTotal: originalTotal,
                        promotionalTotal: itemTotal,
                        savings: savings
                    });

                } else {
                    const itemTotal = originalPrice * quantity;
                    promotionalSubtotal += itemTotal;
                    originalSubtotal += itemTotal;

                    regularItems.push({
                        name: product.name,
                        price: originalPrice,
                        quantity: quantity,
                        total: itemTotal
                    });
                }
            }

            return {
                promotionalSubtotal: promotionalSubtotal,
                originalSubtotal: originalSubtotal,
                promotionalSavings: promotionalSavings,
                promotionalItems: promotionalItems,
                regularItems: regularItems
            };
        }

        // Enhanced function to build product totals with promotional pricing support
        async function buildHTMLProductstotal(snapshotsProducts) {
            console.log('💰 ===== BUILDING PRODUCT TOTALS =====');
            console.log('💰 restaurantorders data:', snapshotsProducts);
            console.log('💰 Products:', snapshotsProducts.products ? snapshotsProducts.products.length : 0);

            var html = '';
            var alldata = [];
            var number = [];
            adminCommissionValue = snapshotsProducts.adminCommission;
            var adminCommissionType = snapshotsProducts.adminCommissionType;
            var discount = snapshotsProducts.discount;
            var couponCode = snapshotsProducts.couponCode;
            var extras = snapshotsProducts.extras;
            var extras_price = snapshotsProducts.extras_price;
            var rejectedByDrivers = snapshotsProducts.rejectedByDrivers;
            var takeAway = snapshotsProducts.takeAway;
            var tip_amount = snapshotsProducts.tip_amount;
            var notes = snapshotsProducts.notes;
            var tax_amount = snapshotsProducts.vendor.tax_amount;
            var status = snapshotsProducts.status;
            var products = snapshotsProducts.products;
            var deliveryCharge = snapshotsProducts.deliveryCharge;
            var specialDiscount = snapshotsProducts.specialDiscount;
            var intRegex = /^\d+$/;
            var floatRegex = /^((\d+(\.\d+)?)|((\d+\.)?\d+))$/;
            var perKmChargeAboveFreeDistance = 8;
            var freeDeliveryDistanceKm = 7;
            var itemTotalThreshold = 299;
            var gstRate = 18;
            var sgstRate = 5;
            var subtotal = 0;

            // Calculate subtotal with promotional pricing support
            if (products) {
                console.log('💰 ===== CALCULATING SUBTOTAL WITH PROMOTIONAL SUPPORT =====');
                console.log('💰 Vendor ID for promotional check:', snapshotsProducts.vendorID);

                // Try to use promotional pricing if vendor ID is available
                if (snapshotsProducts.vendorID) {
                    try {
                        console.log('💰 Attempting promotional pricing calculation...');
                        const promotionalTotals = await calculatePromotionalTotals(products, snapshotsProducts.vendorID);
                        console.log('💰 Promotional totals calculated:', promotionalTotals);

                        if (promotionalTotals && promotionalTotals.promotionalSubtotal > 0) {
                            subtotal = promotionalTotals.promotionalSubtotal;
                            window.promotionalTotals = promotionalTotals;
                            console.log('💰 Using promotional subtotal:', subtotal);
                            console.log('💰 Promotional savings:', promotionalTotals.promotionalSavings);
                        } else {
                            console.log('💰 No promotional pricing found, using original calculation');
                            products.forEach((product) => {
                                var price = (product.discountPrice && parseFloat(product.discountPrice) > 0)
                                    ? parseFloat(product.discountPrice)
                                    : parseFloat(product.price);
                                subtotal += price * (parseInt(product.quantity) || 1);
                            });
                        }
                    } catch (error) {
                        console.error('❌ Error calculating promotional pricing:', error);
                        console.log('🔄 Falling back to original subtotal calculation');
                        products.forEach((product) => {
                            // Use discountPrice only if it exists and is greater than 0, otherwise use price
                            var price = (product.discountPrice && parseFloat(product.discountPrice) > 0)
                                ? parseFloat(product.discountPrice)
                                : parseFloat(product.price);
                            subtotal += price * (parseInt(product.quantity) || 1);
                        });
                    }
                } else {
                    console.log('💰 No vendor ID available, using original calculation');
                    products.forEach((product) => {
                        // Use discountPrice only if it exists and is greater than 0, otherwise use price
                        var price = (product.discountPrice && parseFloat(product.discountPrice) > 0)
                            ? parseFloat(product.discountPrice)
                            : parseFloat(product.price);
                        subtotal += price * (parseInt(product.quantity) || 1);
                    });
                }
            }

            // Use promotional subtotal if available
            if (window.promotionalTotals && window.promotionalTotals.promotionalSubtotal && window.promotionalTotals.promotionalSubtotal > 0) {
                console.log('💰 ===== USING PROMOTIONAL SUBTOTAL =====');
                console.log('💰 Original subtotal:', subtotal);
                console.log('💰 Promotional subtotal:', window.promotionalTotals.promotionalSubtotal);
                console.log('💰 Promotional savings:', window.promotionalTotals.promotionalSavings);
                subtotal = window.promotionalTotals.promotionalSubtotal;
            } else {
                console.log('💰 ===== USING ORIGINAL SUBTOTAL =====');
                console.log('💰 No promotional totals available, using original subtotal:', subtotal);
            }

            // Use delivery charge from order data (exact same logic as print.blade.php)
            var deliveryCharge = snapshotsProducts.deliveryCharge;


            // Initialize total_price with subtotal
            var total_price = subtotal;
            // console.log('Initial total_price (subtotal):', total_price);

            // Add extras to total_price if available
            if (intRegex.test(extras_price) || floatRegex.test(extras_price)) {
                total_price += parseFloat(extras_price);
                // console.log('Added extras to total_price:', parseFloat(extras_price));
                // console.log('Total_price after extras:', total_price);
            }

            var sgst = subtotal * (sgstRate / 100); // 5% of subtotal only
            var gst = 0;
            // if (parseFloat(deliveryCharge) > 0) {
            //     gst = parseFloat(deliveryCharge) * (gstRate / 100); // GST only on payable delivery charges
            // }
            let charge = parseFloat(deliveryCharge);

            if (charge === 0) {
                charge = await getBaseDeliveryCharge();
            }

            gst = charge * (gstRate / 100);

            // Log tax calculations
            console.log('💰 ===== TAX CALCULATIONS =====');
            console.log('💰 Subtotal used for taxes:', subtotal);
            console.log('💰 SGST (5%):', sgst);
            console.log('💰 GST (18%):', gst);
            console.log('💰 Delivery Charge:', deliveryCharge);
            if (currencyAtRight) {
                var sub_total = parseFloat(subtotal).toFixed(decimal_degits) + "" + currentCurrency;
            } else {
                var sub_total = currentCurrency + "" + parseFloat(subtotal).toFixed(decimal_degits);
            }
            html = html + '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.sub_total') }}</span></td></tr>';
            html = html +
                '<tr class="final-rate"><td class="label">Subtotal</td><td class="sub_total" style="color:green">(' +
                sub_total + ')</td></tr>';
            var priceWithCommision = total_price;
            if (intRegex.test(discount) || floatRegex.test(discount)) {
                html = html +
                    '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.discount') }}</span></td></tr>';
                discount = parseFloat(discount).toFixed(decimal_degits);
                total_price -= parseFloat(discount);
                if (currencyAtRight) {
                    discount_val = discount + "" + currentCurrency;
                } else {
                    discount_val = currentCurrency + "" + discount;
                }
                couponCode_html = '';
                if (couponCode) {
                    couponCode_html = '</br><small>{{ trans('lang.coupon_codes') }} :' + couponCode + '</small>';
                }
                html = html + '<tr><td class="label">{{ trans('lang.discount') }}' + couponCode_html +
                    '</td><td class="discount text-danger">(-' + discount_val + ')</td></tr>';
            }
            if (specialDiscount != undefined) {
                special_discount = parseFloat(specialDiscount.special_discount).toFixed(decimal_degits);
                total_price -= parseFloat(special_discount);
                if (currencyAtRight) {
                    special_discount_val = special_discount + "" + currentCurrency;
                } else {
                    special_discount_val = currentCurrency + "" + special_discount;
                }
                special_html = '';
                if (specialDiscount.specialType == "percentage") {
                    special_html = '</br><small>(' + specialDiscount.special_discount_label + '%)</small>';
                }
                html = html + '<tr><td class="label">{{ trans('lang.special_offer') }} {{ trans('lang.discount') }}' +
                    special_html + '</td><td class="special_discount text-danger">(-' + special_discount_val +
                    ')</td></tr>';
            }
            html = html +
                '<tr><td class="seprater" colspan="2"><hr><span>Tax Calculation</span></td></tr>';
            html += '<tr><td class="label">SGST (' + sgstRate + '%)</td><td class="tax_amount" id="greenColor">+' + sgst.toFixed(decimal_degits) + currentCurrency + '</td></tr>';
            html += '<tr><td class="label">GST (' + gstRate + '%)</td><td class="tax_amount" id="greenColor">+' + gst.toFixed(decimal_degits) + currentCurrency + '</td></tr>';
            var total_tax_amount = sgst + gst;
            total_price = parseFloat(total_price) + parseFloat(total_tax_amount);
            var totalAmount = total_price;

            // Always show delivery charge for delivery orders (not takeaway)
            // Temporarily force show to debug
            if (true) { // Force show for debugging
                html = html +
                    '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.delivery_charge') }}</span></td></tr>';

                // Check if delivery charge is a valid number
                var deliveryChargeNum = parseFloat(deliveryCharge);
                console.log('Delivery charge before display check:', deliveryCharge);
                console.log('Delivery charge number:', deliveryChargeNum);
                console.log('Is valid number?', !isNaN(deliveryChargeNum) && deliveryChargeNum >= 0);

                // Use same logic as print.blade.php for delivery charge
                if (intRegex.test(deliveryCharge) || floatRegex.test(deliveryCharge)) {
                    deliveryCharge = parseFloat(deliveryCharge).toFixed(decimal_degits);
                    totalAmount += parseFloat(deliveryCharge);
                    if (currencyAtRight) {
                        deliveryCharge_val = deliveryCharge + "" + currentCurrency;
                    } else {
                        deliveryCharge_val = currentCurrency + "" + deliveryCharge;
                    }
                    deliveryChargeVal = deliveryCharge;
                    html = html +
                        '<tr><td class="label">{{ trans('lang.deliveryCharge') }}</td><td class="deliveryCharge " id="greenColor">+' +
                        deliveryCharge_val + '</td></tr>';
                    console.log('Added delivery charge to total:', parseFloat(deliveryCharge));
                    console.log('New total amount:', totalAmount);
                } else {
                    // Show 0 delivery charge if not valid
                    if (currencyAtRight) {
                        deliveryCharge_val = "0.00" + currentCurrency;
                    } else {
                        deliveryCharge_val = currentCurrency + "0.00";
                    }
                    html = html +
                        '<tr><td class="label">{{ trans('lang.deliveryCharge') }}</td><td class="deliveryCharge " id="greenColor">+' +
                        deliveryCharge_val + '</td></tr>';
                    console.log('Showing 0 delivery charge - invalid format');
                }
            }
            // Always show tip section for delivery orders (not takeaway)
            // Temporarily force show to debug
            console.log('Forcing tip amount display regardless of takeAway value');
            if (true) { // Force show for debugging
                html = html + '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.tip') }}</span></td></tr>';

                // Check if tip amount is a valid number
                var tipAmountNum = parseFloat(tip_amount);
                console.log('Tip amount before display check:', tip_amount);
                console.log('Tip amount number:', tipAmountNum);
                console.log('Is tip valid number?', !isNaN(tipAmountNum) && tipAmountNum >= 0);

                // Use same logic as print.blade.php for tip amount
                if (intRegex.test(tip_amount) || floatRegex.test(tip_amount)) {
                    tip_amount = parseFloat(tip_amount).toFixed(decimal_degits);
                    totalAmount += parseFloat(tip_amount);
                    if (currencyAtRight) {
                        tip_amount_val = tip_amount + "" + currentCurrency;
                    } else {
                        tip_amount_val = currentCurrency + "" + tip_amount;
                    }
                    html = html +
                        '<tr><td class="label">{{ trans('lang.tip_amount') }}</td><td class="tip_amount_val " id="greenColor">+' +
                        tip_amount_val + '</td></tr>';
                    console.log('Added tip amount to total:', parseFloat(tip_amount));
                    console.log('New total amount after tip:', totalAmount);
                } else {
                    // Show 0 tip amount if not valid
                    if (currencyAtRight) {
                        tip_amount_val = "0.00" + currentCurrency;
                    } else {
                        tip_amount_val = currentCurrency + "0.00";
                    }
                    html = html +
                        '<tr><td class="label">{{ trans('lang.tip_amount') }}</td><td class="tip_amount_val " id="greenColor">+' +
                        tip_amount_val + '</td></tr>';
                    console.log('Showing 0 tip amount - invalid format');
                }
            }
            html += '<tr><td class="seprater" colspan="2"><hr></td></tr>';
            orderPaytableAmount = totalAmount;

            // Comprehensive final calculation logging
            console.log('💰 ===== FINAL TOTAL CALCULATION =====');
            console.log('💰 Subtotal (promotional):', subtotal);
            console.log('💰 SGST (5% of subtotal):', sgst);
            console.log('💰 GST (18% of delivery):', gst);
            console.log('💰 Total tax amount:', total_tax_amount);
            console.log('💰 Delivery charge:', deliveryCharge);
            console.log('💰 Tip amount:', tip_amount);
            console.log('💰 Final total amount:', totalAmount);

            if (window.promotionalTotals && window.promotionalTotals.promotionalSavings && window.promotionalTotals.promotionalSavings > 0) {
                console.log('💰 ===== PROMOTIONAL SAVINGS BREAKDOWN =====');
                console.log('💰 Original subtotal would have been:', window.promotionalTotals.originalSubtotal);
                console.log('💰 Promotional subtotal used:', window.promotionalTotals.promotionalSubtotal);
                console.log('💰 Total promotional savings:', window.promotionalTotals.promotionalSavings);
                console.log('💰 Effective discount on final total:', window.promotionalTotals.promotionalSavings);
            }

            console.log('Final totalAmount before formatting:', totalAmount);
            if (currencyAtRight) {
                total_price_val = parseFloat(totalAmount).toFixed(decimal_degits) + "" + currentCurrency;
            } else {
                total_price_val = currentCurrency + "" + parseFloat(totalAmount).toFixed(decimal_degits);
            }
            console.log('Formatted total_price_val:', total_price_val);
            html = html +
                '<tr class="grand-total"><td class="label">{{ trans('lang.total_amount') }}</td><td class="total_price_val " id="greenColor">' +
                total_price_val + '</td></tr>';

            // 💰 Total Collected (ToPay - Wallet Used)
            var walletUsed = parseFloat(orderData.wallet_used_amount || 0);
            var toPayAmount = parseFloat(orderData.toPayAmount || 0);
            var totalCollected = toPayAmount - walletUsed;

            if (totalCollected < 0) totalCollected = 0; // safety

            var totalCollectedVal = currencyAtRight
                ? totalCollected.toFixed(decimal_degits) + currentCurrency
                : currentCurrency + totalCollected.toFixed(decimal_degits);

             // Separator (same pattern as admin commission)
            html += '<tr><td class="seprater" colspan="2"><hr></td></tr>';

             // Display like admin commission
            html += '<tr>' +
                '<td class="label"><big>Total Collect</big></td>' +
                '<td style="color:green"><big>' + totalCollectedVal + '</big></td>' +
                '</tr>';

            html += '<tr>' +
                '<td class="label"><small></small></td>' +
                '<td style="color:blue"><small>' + (toPayAmount) + - +  (walletUsed) + '</small></td>' +
                '</tr>';

            var adminCommHtml = "";
            if (adminCommissionType == "Percent") {
                basePrice = (priceWithCommision / (1 + (parseFloat(adminCommissionValue) / 100)));
                adminCommission = parseFloat(priceWithCommision - basePrice);
                adminCommHtml = "(" + adminCommissionValue + "%)";
            } else {
                basePrice = priceWithCommision - adminCommissionValue;
                adminCommission = parseFloat(priceWithCommision - basePrice);
            }
            if (currencyAtRight) {
                adminCommission_val = parseFloat(adminCommission).toFixed(decimal_degits) + "" + currentCurrency;
            } else {
                adminCommission_val = currentCurrency + "" + parseFloat(adminCommission).toFixed(decimal_degits);
            }
            html = html + '<tr><td class="label"><small>{{ trans('lang.admin_commission') }} ' + adminCommHtml +
                '</small> </td><td style="color:red"><small>( ' + adminCommission_val + ' )</small></td></tr>';
            if (notes) {
                html = html + '<tr><td class="label">{{ trans('lang.notes') }}</td><td class="adminCommission_val">' +
                    notes + '</td></tr>';
            }
            return html;
        }

        function PrintElem(elem) {
            jQuery('#' + elem).printThis({
                debug: false,
                importStyle: true,
                loadCSS: [
                    '<?php echo asset('assets/plugins/bootstrap/css/bootstrap.min.css'); ?>',
                    '<?php echo asset('css/style.css'); ?>',
                    '<?php echo asset('css/colors/blue.css'); ?>',
                    '<?php echo asset('css/icons/font-awesome/css/font-awesome.css'); ?>',
                    '<?php echo asset('assets/plugins/toast-master/css/jquery.toast.css'); ?>',
                ],
            });
        }

        // Simple totals fallback (MySQL only)
        function simpleBuildTotals(order){
            try{
                var curr = currentCurrency;
                var atRight = !!currencyAtRight;
                var digits = decimal_degits || 2;

                function fmt(v){
                    v = parseFloat(v || 0).toFixed(digits);
                    return atRight ? (v + curr) : (curr + v);
                }

                var products = Array.isArray(order.products)? order.products: [];
                var subtotal = 0;
                products.forEach(function(p){
                    var unit = vendorType === 'mart'
                        ? parseFloat(p.discountPrice || p.price || 0)
                        : parseFloat(p.price || 0);
                    var qty = parseInt(p.quantity||1);
                    var extras = parseFloat(p.extras_price||0) * qty;
                    subtotal += (unit*qty) + (isNaN(extras)?0:extras);
                });
                var discount = parseFloat(order.discount||0);
                var special = (order.specialDiscount && order.specialDiscount.special_discount)? parseFloat(order.specialDiscount.special_discount):0;
                var delivery = parseFloat(order.deliveryCharge||0);
                var tip = parseFloat(order.tip_amount||0);
                var sgstRate = 5, gstRate = 18;
                var sgst = subtotal * (sgstRate/100);
                // GST should only be charged on payable delivery amounts
                var gst = 0;
                if (delivery > 0) {
                    gst = delivery * (gstRate / 100);
                }
                var total = subtotal - discount - special + sgst + gst + (delivery>0?delivery:0) + (tip>0?tip:0);

                var html='';
                html += '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.sub_total') }}</span></td></tr>';
                html += '<tr class="final-rate"><td class="label">Subtotal</td><td class="sub_total" style="color:green">(' + fmt(subtotal) + ')</td></tr>';
                if(discount>0){
                    var coupon = order.couponCode? '</br><small>{{ trans('lang.coupon_codes') }} :' + order.couponCode + '</small>' : '';
                    html += '<tr><td class="label">{{ trans('lang.discount') }}' + coupon + '</td><td class="discount text-danger">(-' + fmt(discount) + ')</td></tr>';
                }
                if(special>0){
                    html += '<tr><td class="label">{{ trans('lang.special_offer') }} {{ trans('lang.discount') }}</td><td class="special_discount text-danger">(-' + fmt(special) + ')</td></tr>';
                }
                html += '<tr><td class="seprater" colspan="2"><hr><span>Tax Calculation</span></td></tr>';
                html += '<tr><td class="label">SGST ('+sgstRate+'%)</td><td class="tax_amount" id="greenColor">+' + fmt(sgst) + '</td></tr>';
                html += '<tr><td class="label">GST ('+gstRate+'%)</td><td class="tax_amount" id="greenColor">+' + fmt(gst) + '</td></tr>';
                html += '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.delivery_charge') }}</span></td></tr>';
                html += '<tr><td class="label">{{ trans('lang.deliveryCharge') }}</td><td class="deliveryCharge" id="greenColor">+' + fmt(delivery) + '</td></tr>';
                html += '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.tip') }}</span></td></tr>';
                html += '<tr><td class="label">{{ trans('lang.tip_amount') }}</td><td class="tip_amount_val" id="greenColor">+' + fmt(tip) + '</td></tr>';
                html += '<tr class="grand-total"><td class="label">{{ trans('lang.total_amount') }}</td><td class="total_price_val" id="greenColor">' + fmt(total) + '</td></tr>';
                return html;
            }catch(err){
                console.error('simpleBuildTotals error:', err);
                return '';
            }
        }

        // MySQL-based Reviews System
        var reviewAttributes = {}; // Will store review attributes from database

        // Load review attributes from MySQL
        function loadReviewAttributes() {
            $.ajax({
                url: '/review-attributes',
                method: 'GET',
                success: function(response) {
                    if (Array.isArray(response)) {
                        response.forEach(function(attr) {
                            reviewAttributes[attr.id] = attr.title;
                        });
                        console.log('✅ Review attributes loaded:', reviewAttributes);
                    }
                },
                error: function(error) {
                    console.error('❌ Error loading review attributes:', error);
                }
            });
        }

        // Load and display order reviews from MySQL
        function loadOrderReviews(orderId, orderProducts) {
            $.ajax({
                url: '/order/' + orderId + '/reviews',
                method: 'GET',
                success: function(response) {
                    console.log('📥 Reviews response:', response);
                    var reviews = response.data || [];

                    if (reviews.length > 0) {
                        var reviewHTML = buildRatingsAndReviewsHTML(reviews, orderProducts);
                        jQuery("#customers_rating_and_review").html(reviewHTML);
                    } else {
                        jQuery("#customers_rating_and_review").html('<h4>No Reviews Found</h4>');
                    }
                },
                error: function(error) {
                    console.error('❌ Error loading reviews:', error);
                    jQuery("#customers_rating_and_review").html('<h4>No Reviews Found</h4>');
                }
            });
        }

        // Initialize reviews when order data is loaded
        function initializeReviews() {
            if (orderData && orderData.id) {
                loadReviewAttributes();
                // Wait a bit for review attributes to load, then load reviews
                setTimeout(function() {
                    var orderProducts = orderData.products || [];
                    loadOrderReviews(orderData.id, orderProducts);
                }, 500);
            }
        }

        // MySQL-based: Build reviews HTML from reviews array and products
        function buildRatingsAndReviewsHTML(reviews, orderProducts) {
            var reviewhtml = '<div class="user-ratings">';

            if (!reviews || reviews.length === 0) {
                reviewhtml += '<h4>No Reviews Found</h4>';
                reviewhtml += '</div>';
                return reviewhtml;
            }

            // Create a map of products for quick lookup
            var productsMap = {};
            if (orderProducts && Array.isArray(orderProducts)) {
                orderProducts.forEach(function(product) {
                    productsMap[product.id] = product;
                });
            }

            // Build review HTML for each review
            reviews.forEach(function(review) {
                var product = productsMap[review.productId] || null;

                // Skip if product not found in order
                if (!product) {
                    console.warn('Product not found for review:', review.productId);
                    return;
                }

                var rating = review.rating || 0;
                var productName = product.name || 'Unknown Product';
                var productPhoto = product.photo || '';
                var comment = review.comment || '';
                var photos = review.photos || [];
                var reviewAttributesData = review.reviewAttributes || {};
                // Use formatted date: "Nov 29, 2025" (matching screenshot format)
                var reviewDate = review.createdAtFormatted || '';

                reviewhtml += '<div class="reviews-members py-3 border mb-3">';
                reviewhtml += '<div class="media">';

                // Product image
                if (productPhoto) {
                    reviewhtml += '<a href="javascript:void(0);"><img onerror="this.onerror=null;this.src=\'' + place_image + '\'" alt="#" src="' + productPhoto + '" class="img-circle img-size-32 mr-2" style="width:60px;height:60px"></a>';
                } else {
                    reviewhtml += '<a href="javascript:void(0);"><img alt="#" src="' + place_image + '" class="img-circle img-size-32 mr-2" style="width:60px;height:60px"></a>';
                }

                // Product name and rating
                reviewhtml += '<div class="media-body d-flex">';
                reviewhtml += '<div class="reviews-members-header">';
                reviewhtml += '<h6 class="mb-0"><a class="text-dark" href="javascript:void(0);">' + productName + '</a></h6>';
                reviewhtml += '<div class="star-rating"><div class="d-inline-block" style="font-size: 14px;">';
                reviewhtml += '<ul class="rating" data-rating="' + rating + '">';
                for (var i = 0; i < 5; i++) {
                    reviewhtml += '<li class="rating__item"></li>';
                }
                reviewhtml += '</ul>';
                reviewhtml += '</div></div>';
                reviewhtml += '</div>';
                reviewhtml += '</div>';

                // Review date
                reviewhtml += '<div class="review-date ml-auto">';
                if (reviewDate) {
                    reviewhtml += '<span>' + reviewDate + '</span>';
                }
                reviewhtml += '</div>';
                reviewhtml += '</div>';

                // Review body (comment and photos)
                reviewhtml += '<div class="reviews-members-body w-100">';
                if (comment) {
                    reviewhtml += '<p class="mb-2">' + comment + '</p>';
                }

                // Photos
                if (photos && Array.isArray(photos) && photos.length > 0) {
                    reviewhtml += '<div class="photos"><ul>';
                    photos.forEach(function(img) {
                        if (img) {
                            reviewhtml += '<li><img src="' + img + '" width="100"></li>';
                        }
                    });
                    reviewhtml += '</ul></div>';
                }
                reviewhtml += '</div>';

                // Review attributes (feature ratings)
                if (reviewAttributesData && Object.keys(reviewAttributesData).length > 0) {
                    reviewhtml += '<div class="attribute-ratings feature-rating mb-2">';
                    var label_feature = "{{ trans('lang.byfeature') }}";
                    reviewhtml += '<h3 class="mb-2">' + label_feature + '</h3>';
                    reviewhtml += '<div class="media-body">';

                    $.each(reviewAttributesData, function(attributeId, attributeValue) {
                        var attributeTitle = reviewAttributes[attributeId] || attributeId;
                        reviewhtml += '<div class="feature-reviews-members-header d-flex mb-3">';
                        reviewhtml += '<h6 class="mb-0">' + attributeTitle + '</h6>';
                        reviewhtml += '<div class="rating-info ml-auto d-flex">';
                        reviewhtml += '<div class="star-rating">';
                        reviewhtml += '<ul class="rating" data-rating="' + attributeValue + '">';
                        for (var i = 0; i < 5; i++) {
                            reviewhtml += '<li class="rating__item"></li>';
                        }
                        reviewhtml += '</ul>';
                        reviewhtml += '</div>';
                        reviewhtml += '<div class="count-rating ml-2">';
                        reviewhtml += '<span class="count">' + attributeValue + '</span>';
                        reviewhtml += '</div>';
                        reviewhtml += '</div></div>';
                    });

                    reviewhtml += '</div></div>';
                }

                reviewhtml += '</div>';
            });

            reviewhtml += '</div>';
            return reviewhtml;
        }
        $('#save_transaction_id').on('click', function () {

            const refundTxnId = $('#refund_transaction_id').val().trim();
            const $btn1 = $('#save_transaction_id'); // ✅ FIX: define button

            if (!refundTxnId) {
                alert('Please enter refund transaction ID');
                return;
            }

            if (!confirm('Are you sure you want to save this refund transaction?')) {
                return;
            }

            $btn1.prop('disabled', true).text('Saving...');

            $.ajax({
                url: '{{ route("orders.refund.transaction", ":id") }}'.replace(':id', id),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    refund_transaction_id: refundTxnId
                },
                success: function (res) {
                    if (res.success) {
                        alert(res.message);

                        // 🔒 Lock input
                        $('#refund_transaction_id')
                            .prop('disabled', true)
                            .addClass('bg-light');

                        // ✅ Hide save button
                        $btn1.hide();

                    //     // 🕒 Show refunded at (same line)
                    //     if (res.refunded_at) {
                    //         const refundedHtml = `
                    //     <span class="form-control-plaintext ml-3 text-muted">
                    //         <strong>Refunded At:</strong> ${res.refunded_at}
                    //     </span>
                    // `;
                    //         $('#refund_transaction_id').after(refundedHtml);
                    //     }

                    } else {
                        alert(res.message || 'Something went wrong');
                        $btn1.prop('disabled', false).text('Save');
                    }
                },
                error: function (xhr) {
                    console.error(xhr);
                    alert('Failed to save refund transaction');
                    $btn1.prop('disabled', false).text('Save');
                }
            });
        });
    </script>
@endsection
