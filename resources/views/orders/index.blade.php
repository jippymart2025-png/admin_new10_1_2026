@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{trans('lang.order_plural')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item active">{{trans('lang.order_table')}}</li>
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
                                <span class="icon mr-3"><img src="{{ asset('images/order.png') }}"></span>
                                <h3 class="mb-0">{{trans('lang.order_plural')}}</h3>
                                <span class="counter ml-3 order_count"></span>
                            </div>
                            <div class="d-flex top-title-right align-self-center">
                                <div class="d-flex top-title-right align-self-center">
                                    <div class="select-box pl-3">
                                        <select class="form-control zone_selector filteredRecords">
                                            <option value="" selected>{{trans("lang.select_zone")}}</option>
                                        </select>
                                    </div>
                                    <div class="select-box pl-3">
                                        <select class="form-control status_selector filteredRecords">
                                            <option value="" selected>{{trans("lang.status")}}</option>
                                            <option value="All">{{trans("lang.all_status")}}</option>
                                            <option value="Order Placed">{{trans("lang.order_placed")}}</option>
                                            <option value="Order Accepted">{{trans("lang.order_accepted")}}</option>
                                            <option value="Order Rejected">{{trans("lang.order_rejected")}}</option>
                                            <option value="Driver Pending">{{trans("lang.driver_pending")}}</option>
                                            <option value="Driver Rejected">{{trans("lang.driver_rejected")}}</option>
                                            <option value="Order Shipped">{{trans("lang.order_shipped")}}</option>
                                            <option value="In Transit">{{trans("lang.in_transit")}}</option>
                                            <option value="Order Completed">{{trans("lang.order_completed")}}</option>
                                        </select>
                                    </div>
                                    <div class="select-box pl-3">
                                        <select class="form-control order_type_selector filteredRecords">
                                            <option value="" selected>{{trans("lang.order_type")}}</option>
                                            <option value="takeaway">{{trans("lang.order_takeaway")}}</option>
                                            <option value="delivery">{{trans("lang.delivery")}}</option>
                                        </select>
                                    </div>
                                    <div class="select-box pl-3">
                                        <select class="form-control date_range_selector filteredRecords"
                                                id="date_range_selector">
                                            <option value="" selected>{{trans("lang.select_range")}}</option>
                                            <option value="last_24_hours">⏰ Last 24 Hours</option>
                                            <option value="last_week">📅 Last Week</option>
                                            <option value="last_month">📆 Last Month</option>
                                            <option value="custom">🗓️ Custom Range</option>
                                            <option value="all_orders">📋 All Orders</option>
                                        </select>
                                    </div>
                                    <div class="select-box pl-3" id="custom_daterange_container" style="display:none;">
                                        <div id="daterange"><i class="fa fa-calendar"></i>&nbsp;
                                            <span></span>&nbsp; <i class="fa fa-caret-down"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-list">
                <div class="row">
                    <div class="col-12">
                        <div class="menu-tab d-none vendorMenuTab">
                            <ul>
                                <li>
                                    <a href="{{route('restaurants.view', $id)}}">{{trans('lang.tab_basic')}}</a>
                                </li>
                                <li>
                                    <a href="{{route('restaurants.foods', $id)}}">{{trans('lang.tab_foods')}}</a>
                                </li>
                                <li class="active">
                                    <a href="{{route('restaurants.orders', $id)}}">{{trans('lang.tab_orders')}}</a>
                                </li>
                                <li>
                                    <a href="{{route('restaurants.coupons', $id)}}">{{trans('lang.tab_promos')}}</a>
                                </li>
                                <li>
                                    <a href="{{route('restaurants.payout', $id)}}">{{trans('lang.tab_payouts')}}</a>
                                </li>
                                <li>
                                    <a
                                        href="{{route('payoutRequests.restaurants.view', $id)}}">{{trans('lang.tab_payout_request')}}</a>
                                </li>
                                <li>
                                    <a href="{{route('restaurants.booktable', $id)}}">{{trans('lang.dine_in_future')}}</a>
                                </li>
                                <li id="restaurant_wallet"></li>
                                <li id="subscription_plan"></li>
                            </ul>
                        </div>
                        @if(request()->has('driverId'))
                            <div class="menu-tab d-none driverMenuTab">
                                <ul>
                                    <li>
                                        <a
                                            href="{{route('drivers.view', request()->query('driverId'))}}">{{trans('lang.tab_basic')}}</a>
                                    </li>
                                    <li class="active">
                                        <a
                                            href="{{route('orders')}}?driverId={{request()->query('driverId')}}">{{trans('lang.tab_orders')}}</a>
                                    </li>
                                    <li>
                                        <a
                                            href="{{route('driver.payout', request()->query('driverId'))}}">{{trans('lang.tab_payouts')}}</a>
                                    </li>
                                    <li>
                                        <a
                                            href="{{route('payoutRequests.drivers.view', request()->query('driverId'))}}">{{trans('lang.tab_payout_request')}}</a>
                                    </li>
                                    <li>
                                        <a
                                            href="{{route('users.walletstransaction', request()->query('driverId'))}}">{{trans('lang.wallet_transaction')}}</a>
                                    </li>
                                </ul>
                            </div>
                        @endif
                        @if(request()->has('userId'))
                            <div class="menu-tab d-none userMenuTab">
                                <ul>
                                    <li>
                                        <a
                                            href="{{ route('users.view', request()->query('userId')) }}">{{ trans('lang.tab_basic') }}</a>
                                    </li>
                                    <li class="active">
                                        <a
                                            href="{{route('orders', 'userId='.request()->query('userId'))}}">{{trans('lang.tab_orders')}}</a>
                                    </li>
                                    <li>
                                        <a
                                            href="{{route('users.walletstransaction', request()->query('userId'))}}">{{trans('lang.wallet_transaction')}}</a>
                                    </li>
                                </ul>
                            </div>
                        @endif
                        <div class="card border">
                            <div class="card-header d-flex justify-content-between align-items-center border-0">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.order_table')}}</h3>
                                    <p class="mb-0 text-dark-2">{{trans('lang.order_table_text')}}</p>
                                </div>
                                <div class="card-header-right d-flex align-items-center">
                                    <div class="card-header-btn mr-3">
                                        <!-- <a class="btn-primary btn rounded-full" href="{!! route('users.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.user_create')}}</a> -->
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive m-t-10">
                                    <table id="orderTable"
                                           class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                           cellspacing="0" width="100%">
                                        <thead>
                                        <tr>
                                            <?php if (in_array('orders.delete', json_decode(@session('user_permissions'), true))) { ?>
                                            <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                    class="col-3 control-label" for="is_active">
                                                    <a id="deleteAll" class="do_not_delete" href="javascript:void(0)">
                                                        <i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label>
                                            </th>
                                            <?php } ?>
                                            <th>{{trans('lang.order_id')}}</th>
                                            {{--                                            @if ($id == '')--}}
                                            {{--                                                <th>{{trans('lang.restaurant')}}</th>--}}
                                            {{--                                            @endif--}}
                                            @if ($id == '')
                                                <th style="white-space:normal; max-width:150px;">{{trans('lang.restaurant')}}</th>
                                            @endif
                                            @if (isset($_GET['userId']))
                                                <th class="driverClass">{{trans('lang.driver_plural')}}</th>
                                            @elseif (isset($_GET['driverId']))
                                                <th>{{trans('lang.order_user_id')}}</th>
                                            @else
                                                <th class="driverClass">{{trans('lang.driver_plural')}}</th>
                                                <th>{{trans('lang.order_user_id')}}</th>
                                            @endif
                                            <th>{{trans('lang.date')}}</th>
                                            <th>{{trans('lang.restaurants_payout_amount')}}</th>
                                            {{--                                            <th>{{trans('lang.order_order_status_id')}}</th>--}}
                                            <th style="white-space:normal; max-width:120px;text-align:center">{{trans('lang.order_order_status_id')}}</th>
                                            <th>{{trans('lang.actions')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody id="append_list1">
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

    <!-- Quick Driver Assignment Modal -->
    <div class="modal fade" id="quickDriverAssignmentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('lang.assign_driver') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label for="quick_driver_selector">{{ trans('lang.select_driver') }}</label>
                            <select id="quick_driver_selector" class="form-control">
                                <option value="">{{ trans('lang.select_driver') }}</option>
                            </select>
                            <div class="form-text text-muted">
                                {{ trans('lang.manual_driver_assignment_help') }}
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ trans('lang.cancel') }}</button>
                    <button type="button" class="btn btn-success" id="quick_assign_driver_btn">
                        <i class="fa fa-user-plus"></i> {{ trans('lang.assign_driver') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('style')
    <style>
        /* Date range preset selector styling */
        #date_range_selector {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: 2px solid #5a67d8;
            border-radius: 12px;
            padding: 8px 12px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-width: 180px;
        }

        #date_range_selector:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        #date_range_selector option {
            background: white;
            color: #2d3748;
        }

        /* Custom date range container animation */
        #custom_daterange_container {
            animation: slideIn 0.3s ease-in-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
@endsection
@section('scripts')
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript">
        // MySQL-based DataTables - Firebase removed

        var vendor_id = '<?php echo $id; ?>';
        var append_list = '';
        // Currency settings (loaded from config/session if needed, or use defaults)
        var currentCurrency = '<?php echo config("app.currency_symbol", "₹"); ?>';
        var currencyAtRight =<?php echo config("app.currency_symbol_at_right", false) ? "true" : "false"; ?>;
        var decimal_degits =<?php echo config("app.currency_decimal_digits", 2); ?>;
        var user_permissions = '<?php echo @session("user_permissions") ?>';
        user_permissions = Object.values(JSON.parse(user_permissions));
        var checkDeletePermission = false;
        if ($.inArray('orders.delete', user_permissions) >= 0) {
            checkDeletePermission = true;
        }
        // Remove Firebase refs - DataTables will handle server-side
        var getId = '<?php echo $id; ?>';
        var userID = '<?php echo request()->query('userId', ''); ?>';
        var driverID = '<?php echo request()->query('driverId', ''); ?>';
        var orderStatus = '<?php echo request()->query('status', ''); ?>';

        // Format date time helper function (same as users module)
        function formatDateTime(dateString) {
            if (!dateString) return '-';
            try {
                // Decode HTML entities first (e.g., &quot; to ")
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = dateString;
                var decodedDate = tempDiv.textContent || tempDiv.innerText || dateString;

                // Strip quotes if present
                var cleanDate = decodedDate.toString().replace(/^["']|["']$/g, '').trim();

                console.log('🔧 Date transformation:', {
                    original: dateString,
                    decoded: decodedDate,
                    cleaned: cleanDate
                });

                const date = new Date(cleanDate);

                if (isNaN(date.getTime())) {
                    console.warn('⚠️ Invalid date, returning cleaned version:', cleanDate);
                    return cleanDate;
                }

                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const month = months[date.getMonth()];
                const day = date.getDate();
                const year = date.getFullYear();
                let hours = date.getHours();
                const minutes = String(date.getMinutes()).padStart(2, '0');
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12 || 12;

                var formatted = `${month} ${day}, ${year} ${hours}:${minutes} ${ampm}`;
                console.log('✅ Date formatted successfully:', {input: cleanDate, output: formatted});
                return formatted;
            } catch (e) {
                console.error('❌ Error formatting date:', e, 'Input:', dateString);
                return dateString;
            }
        }

        // Test formatDateTime function
        console.log('🧪 Testing formatDateTime function:');
        var testDates = [
            '"2025-11-05T05:33:06.677795Z"',
            '2025-11-05T05:33:06.677795Z',
            '&quot;2025-11-05T05:33:06.677795Z&quot;'
        ];
        testDates.forEach(function (testDate) {
            var result = formatDateTime(testDate);
            console.log('🧪 Test input:', testDate, '→ Output:', result);
        });

        // Show menu tabs based on context
        if (userID) {
            $('.userMenuTab').removeClass('d-none');
        } else if (driverID) {
            $('.driverMenuTab').removeClass('d-none');
        } else if (getId != '') {
            $('.vendorMenuTab').removeClass('d-none');
        }

        // Load zones from PHP (passed from controller)
        @if(isset($zones))
        @foreach($zones as $zone)
        $('.zone_selector').append($("<option></option>")
            .attr("value", '{{ $zone->id }}')
            .text('{{ $zone->name }}'));
        @endforeach
        $('.zone_selector').prop('disabled', false);
        @endif
        $('.status_selector').select2({
            placeholder: '{{trans("lang.status")}}',
            minimumResultsForSearch: Infinity,
            allowClear: true
        });
        $('.zone_selector').select2({
            placeholder: '{{trans("lang.select_zone")}}',
            minimumResultsForSearch: Infinity,
            allowClear: true
        });
        $('.order_type_selector').select2({
            placeholder: '{{trans("lang.order_type")}}',
            minimumResultsForSearch: Infinity,
            allowClear: true
        });
        $('select').on("select2:unselecting", function (e) {
            var self = $(this);
            setTimeout(function () {
                self.select2('close');
            }, 0);
        });
        // Initialize Select2 for date range selector FIRST
        $('#date_range_selector').select2({
            placeholder: '{{trans("lang.select_range")}}',
            minimumResultsForSearch: Infinity,
            allowClear: true
        });

        // Initialize date range picker (for custom range only)
        function initCustomDateRange() {
            console.log('📅 Initializing custom date range picker...');
            $('#daterange span').html('{{trans("lang.select_range")}}');
            $('#daterange').daterangepicker({
                autoUpdateInput: false,
                opens: 'left',
                locale: {
                    format: 'MMMM D, YYYY'
                }
            }, function (start, end) {
                console.log('📅 Custom range selected:', start.format('YYYY-MM-DD'), 'to', end.format('YYYY-MM-DD'));
                $('#daterange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                // Only reload if DataTable is initialized
                if ($.fn.DataTable.isDataTable('#orderTable')) {
                    $('#orderTable').DataTable().ajax.reload();
                }
            });
            $('#daterange').on('apply.daterangepicker', function (ev, picker) {
                console.log('📅 Custom range applied:', picker.startDate.format('YYYY-MM-DD'), 'to', picker.endDate.format('YYYY-MM-DD'));
                $('#daterange span').html(picker.startDate.format('MMMM D, YYYY') + ' - ' + picker.endDate.format('MMMM D, YYYY'));
                if ($.fn.DataTable.isDataTable('#orderTable')) {
                    $('#orderTable').DataTable().ajax.reload();
                }
            });
            $('#daterange').on('cancel.daterangepicker', function (ev, picker) {
                console.log('📅 Custom range cancelled');
                $('#daterange span').html('{{trans("lang.select_range")}}');
                if ($.fn.DataTable.isDataTable('#orderTable')) {
                    $('#orderTable').DataTable().ajax.reload();
                }
            });
            console.log('✅ Custom date range picker initialized');
        }

        // Initialize custom date range picker
        initCustomDateRange();

        // Handle date range preset selector - MUST be after daterangepicker init
        $('#date_range_selector').on('change', function () {
            var selectedRange = $(this).val();
            console.log('📅 Date range preset changed to:', selectedRange);

            if (selectedRange === 'custom') {
                // Show custom date range picker
                console.log('📅 Showing custom date picker');
                $('#custom_daterange_container').slideDown(300);
            } else {
                // Hide custom date range picker
                $('#custom_daterange_container').slideUp(300);
                $('#daterange span').html('{{trans("lang.select_range")}}');

                if (selectedRange === '') {
                    // Clear date filter
                    console.log('📅 Date filter cleared');
                    // Clear daterangepicker values
                    var picker = $('#daterange').data('daterangepicker');
                    if (picker) {
                        picker.setStartDate(moment());
                        picker.setEndDate(moment());
                    }
                    // Reload table
                    if ($.fn.DataTable.isDataTable('#orderTable')) {
                        $('#orderTable').DataTable().ajax.reload();
                    }
                    return;
                }

                if (selectedRange === 'all_orders') {
                    // Show all orders - clear date filter
                    console.log('📅 Showing all orders (no date filter)');
                    // Clear daterangepicker values
                    var picker = $('#daterange').data('daterangepicker');
                    if (picker) {
                        picker.setStartDate(moment());
                        picker.setEndDate(moment());
                    }
                    // Reload table without date filter
                    if ($.fn.DataTable.isDataTable('#orderTable')) {
                        $('#orderTable').DataTable().ajax.reload();
                    }
                    return;
                }

                // Set predefined ranges
                var startDate, endDate;
                var now = moment();

                if (selectedRange === 'last_24_hours') {
                    startDate = moment().subtract(24, 'hours');
                    endDate = now;
                    console.log('📅 Setting Last 24 hours:', startDate.format('YYYY-MM-DD HH:mm'), 'to', endDate.format('YYYY-MM-DD HH:mm'));
                } else if (selectedRange === 'last_week') {
                    startDate = moment().subtract(7, 'days').startOf('day');
                    endDate = now;
                    console.log('📅 Setting Last week:', startDate.format('YYYY-MM-DD'), 'to', endDate.format('YYYY-MM-DD'));
                } else if (selectedRange === 'last_month') {
                    startDate = moment().subtract(30, 'days').startOf('day');
                    endDate = now;
                    console.log('📅 Setting Last month:', startDate.format('YYYY-MM-DD'), 'to', endDate.format('YYYY-MM-DD'));
                }

                // Set the date range picker values (for the hidden custom picker)
                if (startDate && endDate) {
                    var picker = $('#daterange').data('daterangepicker');
                    if (picker) {
                        picker.setStartDate(startDate);
                        picker.setEndDate(endDate);
                        $('#daterange span').html(startDate.format('MMMM D, YYYY') + ' - ' + endDate.format('MMMM D, YYYY'));
                        console.log('✅ Date range set in picker:', startDate.format('YYYY-MM-DD'), 'to', endDate.format('YYYY-MM-DD'));
                    } else {
                        console.error('❌ Daterangepicker not initialized!');
                    }

                    // Reload table with new date range
                    if ($.fn.DataTable.isDataTable('#orderTable')) {
                        console.log('📅 Reloading table with date filter...');
                        $('#orderTable').DataTable().ajax.reload();
                    } else {
                        console.warn('⚠️ DataTable not initialized yet');
                    }
                }
            }
        });

        // Filter change handler - reload DataTable with new filters
        $('.filteredRecords').change(function () {
            console.log('🔍 Filter changed:', $(this).attr('class'));
            if ($.fn.DataTable.isDataTable('#orderTable')) {
                $('#orderTable').DataTable().ajax.reload();
            }
        });
        $(document).ready(function () {
            console.log('📡 Initializing Orders page...');
            console.log('🔐 Vendor ID:', vendor_id);
            console.log('🔐 User ID:', userID);
            console.log('🔐 Driver ID:', driverID);
            console.log('🔐 Can delete:', checkDeletePermission);

            jQuery('#search').hide();
            $(document.body).on('click', '.redirecttopage', function () {
                var url = $(this).attr('href') || $(this).attr('data-url');
                if (url) window.location.href = url;
            });
            jQuery("#data-table_processing").show();
            $(document).on('click', '.dt-button-collection .dt-button', function () {
                $('.dt-button-collection').hide();
                $('.dt-button-background').hide();
            });
            $(document).on('click', function (event) {
                if (!$(event.target).closest('.dt-button-collection, .dt-buttons').length) {
                    $('.dt-button-collection').hide();
                    $('.dt-button-background').hide();
                }
            });
            var fieldConfig = {
                columns: [
                    {key: 'id', header: "{{trans('lang.order_id')}}"},
                    {key: 'driverName', header: "{{trans('lang.driver_plural')}}"},
                    {key: 'client', header: "{{trans('lang.order_user_id')}}"},
                    {key: 'status', header: "{{trans('lang.order_order_status_id')}}"},
                        {{--{ key: 'orderType', header: "{{trans('lang.order_type')}}" },--}}
                    {
                        key: 'amount', header: "{{trans('lang.amount')}}"
                    },
                    {key: 'createdAt', header: "{{trans('lang.created_at')}}"},
                ],
                fileName: "{{trans('lang.order_table')}}",
            };

            console.log('📡 Initializing Orders DataTable...');

            const table = $('#orderTable').DataTable({
                pageLength: 30,
                lengthMenu: [[10, 25, 30, 50, 100], [10, 25, 30, 50, 100]],
                processing: true,
                serverSide: true,
                responsive: true,
                columns: null, // Let DataTables auto-detect from table headers
                ajax: {
                    url: '{{ route("orders") }}',
                    type: 'GET',
                    data: function (d) {
                        // Add filter parameters
                        d.vendor_id = getId;
                        d.user_id = userID;
                        d.driver_id = driverID;
                        d.status = $('.status_selector').val();
                        d.zone_id = $('.zone_selector').val();
                        d.order_type = $('.order_type_selector').val();
                        d._cache_bust = new Date().getTime(); // Cache busting

                        // Date range - check both preset and custom
                        var selectedRange = $('#date_range_selector').val();
                        var daterangepicker = $('#daterange').data('daterangepicker');

                        console.log('📅 AJAX data - Selected range:', selectedRange);
                        console.log('📅 AJAX data - Daterangepicker exists:', !!daterangepicker);

                        // Send date_range parameter for preset handling
                        if (selectedRange) {
                            d.date_range = selectedRange;
                        }

                        // Handle "all_orders" - don't send date filters
                        if (selectedRange === 'all_orders') {
                            console.log('📅 AJAX data - All orders selected, skipping date filter');
                            // Don't set date_from/date_to - this will show all orders
                        } else if (daterangepicker && $('#daterange span').html() != '{{trans("lang.select_range")}}') {
                            // Always try to get date from daterangepicker if it has valid dates
                            try {
                                // Send full timestamp so last_24_hours / last_week works correctly
                                d.date_from = daterangepicker.startDate.format('YYYY-MM-DD HH:mm:ss');
                                d.date_to   = daterangepicker.endDate.format('YYYY-MM-DD HH:mm:ss');
                                console.log('📅 AJAX data - Sending dates:', d.date_from, 'to', d.date_to);
                            } catch (e) {
                                console.error('❌ Error getting daterangepicker values:', e);
                            }
                        } else {
                            console.log('📅 AJAX data - No date range set');
                        }

                        console.log('📡 Final AJAX params:', d);
                    },
                    dataSrc: function (json) {
                        console.log('📥 Orders response:', json);
                        console.log('📥 First row sample:', json.data && json.data[0]);

                        // IMPORTANT: Re-format dates client-side for consistent display
                        if (json.data && Array.isArray(json.data)) {
                            var processedCount = 0;
                            json.data.forEach(function (row, rowIndex) {
                                // Find the date column (varies by context)
                                // In most views, date is at index 4 or 5
                                for (var i = 0; i < row.length; i++) {
                                    var cell = row[i];
                                    if (typeof cell === 'string' && cell.includes('dt-time')) {
                                        // Extract date from HTML
                                        var match = cell.match(/<span class="dt-time">(.*?)<\/span>/);
                                        if (match && match[1]) {
                                            var rawDate = match[1];

                                            // Only log first 3 rows to avoid console spam
                                            if (rowIndex < 3) {
                                                console.log('📅 Raw date from server:', rawDate);
                                            }

                                            var formattedDate = formatDateTime(rawDate);

                                            if (rowIndex < 3) {
                                                console.log('📅 Formatted date:', formattedDate);
                                            }

                                            row[i] = '<span class="dt-time">' + formattedDate + '</span>';
                                            processedCount++;
                                        }
                                    }
                                }
                            });
                            console.log('✅ Processed ' + processedCount + ' date cells');
                        }

                        $('.order_count').text(json.recordsFiltered || 0);
                        console.log('📊 Total orders:', json.recordsFiltered);
                        $('#data-table_processing').hide();
                        if (!json.data || !Array.isArray(json.data)) {
                            return [];
                        }
                        return json.data;
                    },
                    error: function (xhr, error, thrown) {
                        console.error('❌ DataTables error:', error, thrown);
                        $('#data-table_processing').hide();
                    }
                },
                order: (getId != '' || driverID || userID && checkDeletePermission) ? [[4, 'desc']] : (getId != '' || driverID || userID) ? ((checkDeletePermission) ? [[4, 'desc']] : [[3, 'desc']]) : ((checkDeletePermission) ? [[5, 'desc']] : [[4, 'desc']]),
                columnDefs: [
                    {
                        targets: (getId != '' || driverID || userID && checkDeletePermission) ? 4 : (getId != '' || driverID || userID) ? ((checkDeletePermission) ? 4 : 3) : ((checkDeletePermission) ? 5 : 4),
                        type: 'date',
                        render: function (data) {
                            return data;
                        }
                    },
                    {
                        orderable: false,
                        targets: (getId != '' || driverID || userID && checkDeletePermission) ? [0, 7] : (getId != '' || driverID || userID) ? ((checkDeletePermission) ? [0, 7] : [6]) : (checkDeletePermission) ? [0, 8] : [7]
                    },
                    {
                        // ✅ Restaurant Wrap Column
                        targets: (checkDeletePermission ? 2 : 1),
                        render: function (data) {
                            return `<div style="white-space:normal; max-width:150px; word-break:break-word;">${data}</div>`;
                        }
                    },
                    {
                        // ✅ Wrap Order Status Column
                        targets: (checkDeletePermission ? 7 : 6),
                        render: function (data) {
                            return `<div style="white-space:normal; max-width:120px; word-break:break-word; text-align:center;font-size:10px;">${data}</div>`;
                        }
                    }

                ],
                "language": {
                    "zeroRecords": "{{trans("lang.no_record_found")}}",
                    "emptyTable": "{{trans("lang.no_record_found")}}",
                    "processing": ""
                },
                dom: 'lfrtipB',
                buttons: [
                    {
                        extend: 'collection',
                        text: '<i class="mdi mdi-cloud-download"></i> Export as',
                        className: 'btn btn-info',
                        buttons: [
                            {
                                extend: 'excelHtml5',
                                text: 'Export Excel',
                                exportOptions: {
                                    columns: ':visible:not(:first-child):not(:last-child)'
                                },
                                title: 'orders',
                            },
                            {
                                extend: 'csvHtml5',
                                text: 'Export CSV',
                                exportOptions: {
                                    columns: ':visible:not(:first-child):not(:last-child)'
                                },
                                title: 'orders',
                            },
                            {
                                extend: 'pdfHtml5',
                                text: 'Export PDF',
                                exportOptions: {
                                    columns: ':visible:not(:first-child):not(:last-child)'
                                },
                                title: 'orders',
                                orientation: 'landscape',
                                pageSize: 'A4'
                            }
                        ]
                    }
                ],
                initComplete: function () {
                    $(".dataTables_filter").append($(".dt-buttons").detach());
                    $('.dataTables_filter input').attr('placeholder', 'Search here...').attr('autocomplete', 'new-password').val('');
                    $('.dataTables_filter label').contents().filter(function () {
                        return this.nodeType === 3;
                    }).remove();
                }
            });

            function debounce(func, wait) {
                let timeout;
                return function (...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            $('#search-input').on('input', debounce(function () {
                const searchValue = $(this).val();
                if (searchValue.length >= 1) {
                    $('#data-table_processing').show();
                    table.search(searchValue).draw();
                } else if (searchValue.length === 0) {
                    $('#data-table_processing').show();
                    table.search('').draw();
                }
            }, 300));
        });

        // Delete handlers - MySQL-based
        $("#is_active").click(function () {
            $("#orderTable .is_open").prop('checked', $(this).prop('checked'));
        });

        {{--// Bulk delete orders--}}
        {{--$("#deleteAll").click(function() {--}}
        {{--    if($('#orderTable .is_open:checked').length) {--}}
        {{--        if(confirm("{{trans('lang.selected_delete_alert')}}")) {--}}
        {{--            var selectedIds = [];--}}
        {{--            $('#orderTable .is_open:checked').each(function() {--}}
        {{--                selectedIds.push($(this).attr('dataId'));--}}
        {{--            });--}}
        {{--            --}}
        {{--            // Send AJAX request to delete orders--}}
        {{--            $.ajax({--}}
        {{--                url: '{{ route("orders.bulk.delete") }}',--}}
        {{--                type: 'POST',--}}
        {{--                data: {--}}
        {{--                    _token: '{{ csrf_token() }}',--}}
        {{--                    ids: selectedIds--}}
        {{--                },--}}
        {{--                success: function(response) {--}}
        {{--                    if(response.success) {--}}
        {{--                        $('#orderTable').DataTable().ajax.reload();--}}
        {{--                    } else {--}}
        {{--                        alert('Error: ' + (response.message || 'Failed to delete orders'));--}}
        {{--                    }--}}
        {{--                },--}}
        {{--                error: function() {--}}
        {{--                    alert('{{trans("lang.error_occurred")}}');--}}
        {{--                }--}}
        {{--            });--}}
        {{--        }--}}
        {{--    } else {--}}
        {{--        alert("{{trans('lang.select_delete_alert')}}");--}}
        {{--    }--}}
        {{--});--}}

        {{--// Single order delete--}}
        {{--$(document).on("click","a[name='order-delete']", function(e) {--}}
        {{--    e.preventDefault();--}}
        {{--    var id = $(this).attr('id');--}}
        {{--    if(confirm("{{trans('lang.confirm_delete')}}")) {--}}
        {{--        $.ajax({--}}
        {{--            url: '{{ route("orders.delete", ":id") }}'.replace(':id', id),--}}
        {{--            type: 'DELETE',--}}
        {{--            data: {--}}
        {{--                _token: '{{ csrf_token() }}'--}}
        {{--            },--}}
        {{--            success: function(response) {--}}
        {{--                if(response.success) {--}}
        {{--                    $('#orderTable').DataTable().ajax.reload();--}}
        {{--                } else {--}}
        {{--                    alert('Error: ' + (response.message || 'Failed to delete order'));--}}
        {{--                }--}}
        {{--            },--}}
        {{--            error: function() {--}}
        {{--                alert('{{trans("lang.error_occurred")}}');--}}
        {{--            }--}}
        {{--        });--}}
        {{--    }--}}
        {{--});--}}
    </script>
@endsection
