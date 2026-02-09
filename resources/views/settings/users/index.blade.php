@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{trans('lang.user_plural')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item active">{{trans('lang.user_table')}}</li>
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
                                <span class="icon mr-3"><img src="{{ asset('images/users.png') }}"></span>
                                <h3 class="mb-0">{{trans('lang.user_plural')}}</h3>
                                <span class="counter ml-3 total_count"></span>
                            </div>
                            <div class="d-flex top-title-right align-self-center">
                                <div class="select-box pl-3">
                                    <select class="form-control status_selector">
                                        <option value="">{{trans("lang.status")}}</option>
                                        <option value="1">{{trans("lang.active")}}</option>
                                        <option value="0">{{trans("lang.in_active")}}</option>
                                    </select>
                                </div>
                                <div class="select-box pl-3">
                                    <select class="form-control zone_selector">
                                        <option value="" disabled selected>{{trans('lang.select_zone')}}</option>
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
                                        <option value="all_orders">📋 All Users</option>
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
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            {{--            @if($errors->any())--}}
            {{--                <div class="alert alert-danger">--}}
            {{--                    <ul class="mb-0">--}}
            {{--                        @foreach($errors->all() as $error)--}}
            {{--                            <li>{{ $error }}</li>--}}
            {{--                        @endforeach--}}
            {{--                    </ul>--}}
            {{--                </div>--}}
            {{--            @endif--}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">Bulk Import Users</h3>
                                <p class="mb-0 text-dark-2">Upload Excel file to import multiple users at once</p>
                            </div>
                            <div class="card-header-right d-flex align-items-center">
                                <div class="card-header-btn mr-3">
                                    <a href="{{ route('users.download-template') }}"
                                       class="btn btn-outline-primary rounded-full">
                                        <i class="mdi mdi-download mr-2"></i>Download Template
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="importFile" class="control-label">Select Excel File
                                                (.xls/.xlsx)</label>
                                            <input type="file" name="file" id="importFile" accept=".xls,.xlsx"
                                                   class="form-control" required>
                                            <div class="form-text text-muted">
                                                <i class="mdi mdi-information-outline mr-1"></i>
                                                File should contain: firstName, lastName, email, password, active, role,
                                                profilePictureURL, createdAt
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary rounded-full">
                                            <i class="mdi mdi-upload mr-2"></i>Import Users
                                        </button>
                                    </div>
                                </div>
                            </form>
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
                                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.user_table')}}</h3>
                                    <p class="mb-0 text-dark-2">{{trans('lang.users_table_text')}}</p>
                                </div>
                                <div class="card-header-right d-flex align-items-center">
                                    <div class="card-header-btn mr-3">
                                        <a class="btn-primary btn rounded-full" href="{!! route('users.create') !!}"><i
                                                class="mdi mdi-plus mr-2"></i>{{trans('lang.user_create')}}</a>
                                    </div>
                                    {{--                                    <div class="dropdown ml-2">--}}
                                    {{--                                        <button class="btn btn-info dropdown-toggle" type="button"--}}
                                    {{--                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">--}}
                                    {{--                                            <i class="mdi mdi-cloud-download"></i> Export as--}}
                                    {{--                                        </button>--}}

                                    {{--                                        <div class="dropdown-menu dropdown-menu-right">--}}
                                    {{--                                            <a class="dropdown-item" href="javascript:void(0)" onclick="exportUsers('excel')">--}}
                                    {{--                                                Export Excel--}}
                                    {{--                                            </a>--}}
                                    {{--                                            <a class="dropdown-item" href="javascript:void(0)" onclick="exportUsers('pdf')">--}}
                                    {{--                                                Export PDF--}}
                                    {{--                                            </a>--}}
                                    {{--                                            <a class="dropdown-item" href="javascript:void(0)" onclick="exportUsers('csv')">--}}
                                    {{--                                                Export CSV--}}
                                    {{--                                            </a>--}}
                                    {{--                                        </div>--}}
                                    {{--                                    </div>--}}
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive m-t-10">
                                    <table id="userTable"
                                           class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                           cellspacing="0" width="100%">
                                        <thead>
                                        <tr>
                                            <?php if (in_array('user.delete', json_decode(@session('user_permissions'),true))) { ?>
                                            <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                    class="col-3 control-label" for="is_active"><a id="deleteAll"
                                                                                                   class="do_not_delete"
                                                                                                   href="javascript:void(0)"><i
                                                            class="mdi mdi-delete"></i> {{trans('lang.all')}}
                                                    </a></label></th>
                                            <?php } ?>
                                            <th>{{trans('lang.user_info')}}</th>
                                            <th>{{trans('lang.email')}}</th>
                                            <th>{{trans('lang.phone_number')}}</th>
                                            <th>{{trans('lang.zone')}}</th>
                                            <th>{{trans('lang.date')}}</th>
                                            <th>{{trans('lang.active')}}</th>
                                            {{-- <th>{{trans('lang.wallet_transaction')}}</th> --}}
                                            {{-- <th >{{trans('lang.role')}}</th> --}}
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
        // SQL mode: fetch via API instead of Firebase
        var apiBase = '{{ url('/api') }}';
        var placeholderImage = '{{ asset('images/placeholder.png') }}';
        var user_permissions = '<?php echo @session("user_permissions") ?>';
        user_permissions = Object.values(JSON.parse(user_permissions));
        var checkDeletePermission = false;
        if ($.inArray('user.delete', user_permissions) >= 0) {
            checkDeletePermission = true;
        }
        var zoneIdToName = {};
        var zonesLoaded = false;

        // Format date time helper function
        function formatDateTime(dateString) {
            if (!dateString) return '-';
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return '-';

                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const month = months[date.getMonth()];
                const day = String(date.getDate()).padStart(2, '0');
                const year = date.getFullYear();
                let hours = date.getHours();
                const minutes = String(date.getMinutes()).padStart(2, '0');
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12 || 12;

                return `${month} ${day}, ${year} ${String(hours).padStart(2, '0')}:${minutes} ${ampm}`;
            } catch (e) {
                console.error('Error formatting date:', e);
                return '-';
            }
        }
        // Load zones from SQL
        var loadZonesPromise = new Promise(function(resolve){
            console.log('🔄 Loading zones from SQL...');
            $.ajax({
                url: '{{ route("zone.data") }}',
                method: 'GET',
                success: function(response) {
                    console.log('📊 Zones API response:', response);
                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function(zone) {
                            zoneIdToName[zone.id] = zone.name;
                            // Add zone to selector
                            $('.zone_selector').append(
                                $('<option></option>').val(zone.id).text(zone.name)
                            );
                        });
                        console.log('✅ Zones loaded from SQL (' + response.data.length + ' zones):', zoneIdToName);
                    } else {
                        console.warn('⚠️ No zones found in database');
                    }
                    zonesLoaded = true;
                    resolve(zoneIdToName);
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error loading zones:', error);
                    console.error('Response:', xhr.responseText);
                    zonesLoaded = true;
                    resolve(zoneIdToName);
                }
            });
        });

        // Initialize select2 for all selectors
        $('.status_selector').select2({
            placeholder: '{{trans("lang.status")}}',
            minimumResultsForSearch: Infinity,
            allowClear: true
        });
        $('.zone_selector').select2({
            placeholder: "{{trans('lang.select_zone')}}",
            minimumResultsForSearch: Infinity,
            allowClear: true
        });
        // Initialize Select2 for date range selector
        $('#date_range_selector').select2({
            placeholder: '{{trans("lang.select_range")}}',
            minimumResultsForSearch: Infinity,
            allowClear: true
        });

        // Handle select2 unselecting
        $('select').on("select2:unselecting", function (e) {
            var self = $(this);
            setTimeout(function () {
                self.select2('close');
            }, 0);
        });

        // Main filter handler - triggers when ANY select changes
        $('select').change(async function() {
            var zoneValue = $('.zone_selector').val();
            var daterangepicker = $('#daterange').data('daterangepicker');

            let statusValue = $('.status_selector').val();
            console.log('- Status Value:', statusValue);
            console.log('Filter change triggered:');
            console.log('- Zone Value:', zoneValue);

            // No-op; filters are sent to server via DataTables ajax

            // Note: Zone filter is NOT applied in Firestore query
            // It will be applied client-side because zoneId is nested in shippingAddress

            // Apply date filter
            if ($('#daterange span').html() != '{{trans("lang.select_range")}}' && daterangepicker) {
            }

            $('#userTable').DataTable().ajax.reload();
        });

        // Initialize custom date range picker
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
                if ($.fn.DataTable.isDataTable('#userTable')) {
                    $('#userTable').DataTable().ajax.reload();
                }
            });
            $('#daterange').on('apply.daterangepicker', function (ev, picker) {
                console.log('📅 Custom range applied:', picker.startDate.format('YYYY-MM-DD'), 'to', picker.endDate.format('YYYY-MM-DD'));
                $('#daterange span').html(picker.startDate.format('MMMM D, YYYY') + ' - ' + picker.endDate.format('MMMM D, YYYY'));
                if ($.fn.DataTable.isDataTable('#userTable')) {
                    $('#userTable').DataTable().ajax.reload();
                }
            });
            $('#daterange').on('cancel.daterangepicker', function (ev, picker) {
                console.log('📅 Custom range cancelled');
                $('#daterange span').html('{{trans("lang.select_range")}}');
                if ($.fn.DataTable.isDataTable('#userTable')) {
                    $('#userTable').DataTable().ajax.reload();
                }
            });
            console.log('✅ Custom date range picker initialized');
        }

        // Initialize custom date range picker
        initCustomDateRange();

        // Handle date range preset selector
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
                    if ($.fn.DataTable.isDataTable('#userTable')) {
                        $('#userTable').DataTable().ajax.reload();
                    }
                    return;
                }

                if (selectedRange === 'all_orders') {
                    // Show all users - clear date filter
                    console.log('📅 Showing all users (no date filter)');
                    // Clear daterangepicker values
                    var picker = $('#daterange').data('daterangepicker');
                    if (picker) {
                        picker.setStartDate(moment());
                        picker.setEndDate(moment());
                    }
                    // Reload table without date filter
                    if ($.fn.DataTable.isDataTable('#userTable')) {
                        $('#userTable').DataTable().ajax.reload();
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
                    if ($.fn.DataTable.isDataTable('#userTable')) {
                        console.log('📅 Reloading table with date filter...');
                        $('#userTable').DataTable().ajax.reload();
                    } else {
                        console.warn('⚠️ DataTable not initialized yet');
                    }
                }
            }
        });
        $(document).ready(function () {
            $(document.body).on('click', '.redirecttopage', function () {
                var url = $(this).attr('data-url');
                window.location.href = url;
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

            // Wait for zones to load before initializing DataTable
            loadZonesPromise.then(function() {
                console.log('🚀 Zones loaded, initializing DataTable with zone mapping:', zoneIdToName);

                var fieldConfig = {
                    columns: [
                        {key: 'fullName', header: "{{trans('lang.user_info')}}"},
                        {key: 'email', header: "{{trans('lang.email')}}"},
                        {key: 'phoneNumber', header: "{{trans('lang.phone_number')}}"},
                        {key: 'zone', header: "{{trans('lang.zone')}}"},
                        {key: 'active', header: "{{trans('lang.active')}}"},
                        {key: 'createdAt', header: "{{trans('lang.created_at')}}"},
                    ],
                    fileName: "{{trans('lang.user_table')}}",
                };
                const table = $('#userTable').DataTable({
                    pageLength: 30,
                    lengthMenu: [[10,30, 50, 100,500,1000], [10,30, 50, 100,500,1000]],
                    processing: false, // Show processing indicator
                    serverSide: true, // Enable server-side processing
                    responsive: true,
                    ajax: function (data, callback, settings) {
                        const start = data.start;
                        const length = data.length;
                        const searchValue = data.search.value.toLowerCase();
                        let statusValue = $('.status_selector').val();

                        let activeFilter = statusValue;  // sends '1' or '0' directly

                        const zoneValue = $('.zone_selector').val();
                        console.log('🌍 Zone filter value:', zoneValue);
                        const daterangepicker = $('#daterange').data('daterangepicker');
                        let from = '', to = '';

                        // Date range - check both preset and custom
                        var selectedRange = $('#date_range_selector').val();

                        // Send date_range parameter for preset handling
                        var dateRangeParam = '';
                        if (selectedRange) {
                            dateRangeParam = selectedRange;
                        }

                        // Handle "all_orders" - don't send date filters
                        if (selectedRange === 'all_orders') {
                            console.log('📅 AJAX data - All users selected, skipping date filter');
                            // Don't set from/to - this will show all users
                        } else if (daterangepicker && $('#daterange span').html() != '{{trans("lang.select_range")}}') {
                            // Always try to get date from daterangepicker if it has valid dates
                            try {
                                // Send full timestamp so last_24_hours / last_week works correctly
                                from = daterangepicker.startDate.format('YYYY-MM-DD HH:mm:ss');
                                to = daterangepicker.endDate.format('YYYY-MM-DD HH:mm:ss');
                                console.log('📅 AJAX data - Sending dates:', from, 'to', to);
                            } catch (e) {
                                console.error('❌ Error getting daterangepicker values:', e);
                            }
                        } else {
                            console.log('📅 AJAX data - No date range set');
                        }

                        $('#data-table_processing').show();
                        $.ajax({
                            url: apiBase + '/app-users',
                            method: 'GET',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: {
                                page: Math.floor(start / length) + 1,
                                limit: length,
                                search: searchValue,
                                active: activeFilter,
                                zoneId: zoneValue,
                                from: from,
                                to: to,
                                date_range: dateRangeParam,
                                role: 'customer'
                            }
                        }).done(function (resp) {
                            console.log('📊 Users API response:', resp);
                            const items = resp.data || [];
                            const total = (resp.meta && resp.meta.total) ? resp.meta.total : items.length;
                            $('.total_count').text(total);
                            console.log('👥 Loading ' + items.length + ' users');
                            let records = [];
                            items.forEach(function (childData) {
                                console.log('User data:', {
                                    id: childData.id,
                                    name: childData.fullName,
                                    zoneId: childData.zoneId,
                                    zoneIdType: typeof childData.zoneId
                                });
                                var id = childData.id;
                                var route1 = '{{route("users.edit",":id")}}'.replace(':id', id);
                                var user_view = '{{route("users.view",":id")}}'.replace(':id', id);
                                var vendorImage = childData.profilePictureURL == '' || childData.profilePictureURL == null ? '<img alt="" width="100%" style="width:70px;height:70px;" src="' + placeholderImage + '" alt="image">' : '<img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="" width="100%" style="width:70px;height:70px;" src="' + childData.profilePictureURL + '" alt="image">'

                                // Display zone name based on zoneId
                                var zoneName = '';
                                if (childData.zoneId && childData.zoneId !== '' && childData.zoneId !== null) {
                                    // Check if zone exists in mapping
                                    if (zoneIdToName[childData.zoneId]) {
                                        zoneName = '<span class="badge badge-info py-2 px-3">' + zoneIdToName[childData.zoneId] + '</span>';
                                        console.log('✅ Zone found for user ' + id + ':', zoneIdToName[childData.zoneId]);
                                    } else {
                                        // Zone ID exists but not found in zones table
                                        zoneName = '<span class="badge badge-warning py-2 px-3" style="color: #666;">Zone Not Found (ID: ' + childData.zoneId + ')</span>';
                                        console.warn('⚠️ Zone ID "' + childData.zoneId + '" not found in zones table for user ' + id);
                                    }
                                } else {
                                    // No zone ID assigned
                                    zoneName = '<span style="color: #999; font-style: italic;">null</span>';
                                    console.log('ℹ️ No zone assigned for user ' + id);
                                }
                                // Format date with the new format: Oct 06, 2025 07:24 AM
                                var createdAt = formatDateTime(childData.createdAt) || '-';
                                records.push([
                                    checkDeletePermission ? '<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label" for="is_open_' + id + '" ></label></td>' : '',
                                    vendorImage + '<a href="' + user_view + '" class="redirecttopage">' + (childData.fullName || '') + '</a>',
                                    childData.email ? childData.email : ' ',
                                    childData.phoneNumber ? childData.phoneNumber : ' ',
                                    zoneName,
                                    createdAt,
                                    childData.active ? '<label class="switch"><input type="checkbox" checked id="' + id + '" name="isActive"><span class="slider round"></span></label>' : '<label class="switch"><input type="checkbox" id="' + id + '" name="isActive"><span class="slider round"></span></label>',
                                    '<span class="action-btn"><a href="' + user_view + '"><i class="mdi mdi-eye"></i></a><a href="' + route1 + '"><i class="mdi mdi-lead-pencil" title="Edit"></i></a><?php if (in_array('user.delete', json_decode(@session('user_permissions'), true))){ ?> <a id="' + id + '" class="delete-btn" name="user-delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i></a></td><?php } ?></span>'
                                ]);
                            });
                            $('#data-table_processing').hide();
                            callback({
                                draw: data.draw,
                                recordsTotal: total,
                                recordsFiltered: total,
                                filteredData: items,
                                data: records
                            });
                        }).fail(function () {
                            $('#data-table_processing').hide();
                            callback({
                                draw: data.draw,
                                recordsTotal: 0,
                                recordsFiltered: 0,
                                filteredData: [],
                                data: []
                            });
                        });
                    },
                    order: [checkDeletePermission ? 5 : 4, 'desc'],
                    columnDefs: [
                        {
                            targets: (checkDeletePermission) ? 5 : 4,
                            type: 'date',
                            render: function (data) {
                                return data;
                            }
                        },
                        {orderable: false, targets: (checkDeletePermission) ? [0, 6, 7] : [5, 6]},
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
                                    text: 'Export CSV',
                                    action: function () {
                                        exportUsers('csv');
                                    }
                                },
                                {
                                    text: 'Export PDF',
                                    action: function () {
                                        exportUsers('pdf');
                                    }
                                },
                                {
                                    text: 'Export EXCEL',
                                    action: function () {
                                        exportUsers('excel');
                                    }
                                },
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
                table.columns.adjust().draw();

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
            }); // Close loadZonesPromise.then()
        });
        $("#is_active").click(function () {
            $("#userTable .is_open").prop('checked', $(this).prop('checked'));
        });
        $("#deleteAll").click(async function () {
            if ($('#userTable .is_open:checked').length) {
                if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                    jQuery("#data-table_processing").show();
                    var selectedUsers = [];
                    // Get selected user IDs for logging
                    $('#userTable .is_open:checked').each(function() {
                        var dataId = $(this).attr('dataId');
                        selectedUsers.push('User ID: ' + dataId);
                    });

                    $('#userTable .is_open:checked').each(async function () {
                        var dataId = $(this).attr('dataId');
                        await deleteDocumentWithImage('users', dataId, 'profilePictureURL');
                        const getStoreName = deleteUserData(dataId);
                        console.log('✅ Bulk user deletion completed, now logging activity...');
                        try {
                            if (typeof logActivity === 'function') {
                                console.log('🔍 Calling logActivity for bulk user deletion...');
                                await logActivity('users', 'bulk_deleted', 'Bulk deleted users: ' + selectedUsers.join(', '));
                                console.log('✅ Activity logging completed successfully');
                            } else {
                                console.error('❌ logActivity function is not available');
                            }
                        } catch (error) {
                            console.error('❌ Error calling logActivity:', error);
                        }
                        setTimeout(function () {
                            window.location.reload();
                        }, 7000);
                    });
                }
            } else {
                alert("{{trans('lang.select_delete_alert')}}");
            }
        });

        async function deleteUserData(userId) {
            // Delete user via SQL API
            try {
                const response = await DB.delete(`/users/${userId}`);
                console.log('✅ User deleted successfully');
                return true;
            } catch (error) {
                console.error('❌ Error deleting user:', error);
                return false;
            }

        }

        $(document).on("click", "a[name='user-delete']", async function (e) {
            var id = this.id;
            if (!confirm("{{trans('lang.delete_alert')}}")) return;
            $('#data-table_processing').show();
            $.ajax({
                url: apiBase + '/app-users/' + id,
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).done(function(){
                $('#data-table_processing').hide();
                window.location.href = '{{ url()->current() }}';
            }).fail(function(){
                $('#data-table_processing').hide();
                alert('Failed to delete user');
            });
        });
        $(document).on("click", "input[name='isActive']", async function (e) {
            var ischeck = $(this).is(':checked');
            var id = this.id;
            $.ajax({
                url: apiBase + '/app-users/' + id + '/active',
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                data: { active: ischeck ? 'true' : 'false' }
            }).fail(function(){
                alert('Failed to update status');
            });
        });
        function exportUsers(type) {
            let daterangepicker = $('#daterange').data('daterangepicker');
            const selectedZoneId = $('.zone_selector').val();
            console.log('🌍 Export - Zone filter value:', selectedZoneId);

            let params = {
                search: $('.dataTables_filter input').val(),
                active: $('.status_selector').val(),
                zoneId: selectedZoneId,
                date_range: $('#date_range_selector').val(),
                from: daterangepicker ? daterangepicker.startDate.format('YYYY-MM-DD HH:mm:ss') : '',
                to: daterangepicker ? daterangepicker.endDate.format('YYYY-MM-DD HH:mm:ss') : '',
                role: 'customer',
                type: type
            };

            window.location.href = apiBase + '/app-users/export?' + $.param(params);
        }
    </script>
@endsection
