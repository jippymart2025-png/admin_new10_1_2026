@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.restaurant_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item">{{trans('lang.restaurant_plural')}}</li>
                <li class="breadcrumb-item active">{{trans('lang.restaurant_table')}}</li>
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
                        <h3 class="mb-0">{{trans('lang.restaurant_plural')}}</h3>
                        <span class="counter ml-3 rest_count"></span>
                    </div>
                    <div class="d-flex top-title-right align-self-center">
                        <div class="select-box pl-3">
                            <select class="form-control restaurant_type_selector">
                                <option value="">{{trans('lang.restaurant_type')}}</option>
                                <option value="true">{{trans('lang.dine_in')}}</option>
                            </select>
                        </div>
                        <div class="select-box pl-3">
                            <select class="form-control business_model_selector">
                                <option value="" disabled selected>{{trans('lang.business_model')}}</option>
                            </select>
                        </div>
{{--                        <div class="select-box pl-3">--}}
{{--                            <select class="form-control cuisine_selector">--}}
{{--                                <option value="" disabled selected>{{trans('lang.select_cuisines')}}</option>--}}
{{--                            </select>--}}
{{--                        </div>--}}
                        <div class="select-box pl-3">
                            <select class="form-control zone_selector">
                            <option value="" selected>{{trans('lang.select_zone')}}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card border">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card card-box-with-icon bg--1">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                       <div class="card-box-with-content">
                                        <h4 class="text-dark-2 mb-1 h4 rest_count">00</h4>
                                        <p class="mb-0 small text-dark-2">{{trans('lang.dashboard_total_restaurants')}}</p>
                                       </div>
                                        <span class="box-icon ab"><img src="{{ asset('images/restaurant_icon.png') }}"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-box-with-icon bg--5">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                       <div class="card-box-with-content">
                                        <h4 class="text-dark-2 mb-1 h4 rest_active_count">00</h4>
                                        <p class="mb-0 small text-dark-2">{{trans('lang.active_restaurants')}}</p>
                                       </div>
                                        <span class="box-icon ab"><img src="{{ asset('images/active_restaurant.png') }}"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-box-with-icon bg--8">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                       <div class="card-box-with-content">
                                        <h4 class="text-dark-2 mb-1 h4 rest_inactive_count">00</h4>
                                        <p class="mb-0 small text-dark-2">{{trans('lang.inactive_restaurants')}}</p>
                                       </div>
                                        <span class="box-icon ab"><img src="{{ asset('images/inactive_restaurant.png') }}"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-box-with-icon bg--6">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                       <div class="card-box-with-content">
                                        <h4 class="text-dark-2 mb-1 h4 new_joined_rest">00</h4>
                                        <p class="mb-0 small text-dark-2">{{trans('lang.new_joined_restaurants')}}</p>
                                       </div>
                                        <span class="box-icon ab"><img src="{{ asset('images/new_restaurant.png') }}"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
       </div>
       <div class="row mb-4">
        <div class="col-12">
            <div class="card border">
                <div class="card-header d-flex justify-content-between align-items-center border-0">
                    <div class="card-header-title">
                        <h3 class="text-dark-2 mb-2 h4">Bulk Import/Update Restaurants</h3>
                        <p class="mb-0 text-dark-2">Upload Excel file to import or update multiple restaurants at once</p>
                    </div>
                    <div class="card-header-right d-flex align-items-center">
                        <div class="card-header-btn mr-3">
                            <a href="{{ route('restaurants.download-template') }}" class="btn btn-outline-primary rounded-full">
                                <i class="mdi mdi-download mr-2"></i>Download Template
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('restaurants.bulk-import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="bulkUpdateFile" class="control-label">Select Excel File (.xls/.xlsx)</label>
                                    <input type="file" name="file" id="bulkUpdateFile" accept=".xls,.xlsx" class="form-control" required>
                                    <div class="form-text text-muted">
                                        <i class="mdi mdi-information-outline mr-1"></i>
                                        File should contain: id, title, authorName, phonenumber, adminCommission, etc. (id is required)
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary rounded-full">
                                    <i class="mdi mdi-upload mr-2"></i>Bulk Update
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Restaurant Status Control - Below Bulk Import/Update -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <i class="mdi mdi-store-check text-primary" style="font-size: 18px;"></i>
                            </div>
                            <div>
                                <label class="control-label mb-0" style="font-size: 14px; color: #333;">
                                    <strong>Global Restaurant Status:</strong>
                                </label>
                                <small class="text-muted d-block" style="font-size: 12px;">
                                    Override all restaurants' open/closed status
                                </small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="mr-3 d-flex align-items-center">
                                <input type="checkbox" id="global_restaurant_status" checked>
                                <label class="control-label mb-0 ml-2" for="global_restaurant_status">
                                    <span class="status-text">All Open</span>
                                </label>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" id="apply_global_status">
                                <i class="mdi mdi-check mr-1"></i>Apply to All Restaurants
                            </button>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{!! session('success') !!}</div>
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
                        <h3 class="text-dark-2 mb-2 h4">{{trans('lang.restaurant_table')}}</h3>
                        <p class="mb-0 text-dark-2">{{trans('lang.restaurants_table_text')}}</p>
                    </div>
                    <div class="card-header-right d-flex align-items-center">
                        <div class="card-header-btn mr-3">
                        <a href="{!! route('restaurants.create') !!}" class="btn-primary btn rounded-full"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.create_restaurant')}}</a>
                        </div>
                    </div>
                    </div>
                    <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="storeTable"
                                    class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <?php if (in_array('restaurants.delete', json_decode(@session('user_permissions'), true))) { ?>
                                            <?php if (in_array('restaurants.delete', json_decode(@session('user_permissions'), true))) { ?>
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                        class="col-3 control-label" for="is_active"><a id="deleteAll"
                                                            class="do_not_delete" href="javascript:void(0)"><i
                                                                class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label>
                                                </th>
                                            <?php }}?>
                                            <th>{{trans('lang.restaurant_info')}}</th>
                                            <th>{{trans('lang.owner_info')}}</th>
                                            <th>Zone</th>
                                            <th>Admin Commission</th>
                                            <th>{{trans('lang.date')}}</th>
                                            <th>{{trans('lang.wallet_history')}}</th>
                                            <th>Plan Name</th>
                                            <th>Plan Commission</th>
                                            <th>Plan ExpiryDate</th>
                                            <th>Best</th>
                                            <th>GST</th>
                                            <th>Publish</th>
                                            <th>{{trans('lang.actions')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="append_restaurants"></tbody>
                                </table>
                            </div>
                            <!-- Popup -->
                            <div class="modal fade" id="create_vendor" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered notification-main" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLongTitle">{{trans('lang.copy_vendor')}}
                                                <span id="vendor_title_lable"></span>
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div id="data-table_processing"
                                                class="dataTables_processing panel panel-default" style="display: none;">
                                                {{trans('lang.processing')}}
                                            </div>
                                            <div class="error_top"></div>
                                            <!-- Form -->
                                            <div class="form-row">
                                                <div class="col-md-12 form-group">
                                                    <label class="form-label">{{trans('lang.first_name')}}</label>
                                                    <div class="input-group">
                                                        <input placeholder="Name" type="text" id="user_name"
                                                            class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-md-12 form-group">
                                                    <label class="form-label">{{trans('lang.last_name')}}</label>
                                                    <div class="input-group">
                                                        <input placeholder="Name" type="text" id="user_last_name"
                                                            class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-md-12 form-group">
                                                    <label class="form-label">{{trans('lang.vendor_title')}}</label>
                                                    <div class="input-group">
                                                        <input placeholder="Vendor Title" type="text" id="vendor_title"
                                                            class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-md-12 form-group"><label
                                                        class="form-label">{{trans('lang.email')}}</label><input
                                                        placeholder="Email" value="" id="user_email" type="text"
                                                        class="form-control"></div>
                                                <div class="col-md-12 form-group"><label
                                                        class="form-label">{{trans('lang.password')}}</label><input
                                                        placeholder="Password" id="user_password" type="password"
                                                        class="form-control">
                                                </div>
                                            </div>
                                            <!-- Form -->
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary save-form-btn"
                                                >{{trans('lang.create')}}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Popup -->
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
    // SQL-based implementation (no Firebase)
    var currentCurrency = '';
    var currencyAtRight = false;
    var decimal_degits = 0;

    // Filter variables for SQL queries
    window.selectedZone = '';
    window.selectedRestaurantType = '';
    window.selectedBusinessModel = '';
    window.selectedCuisine = '';

    // Load currency from SQL (if you have a currency endpoint, otherwise hardcode)
    $.ajax({
        url: '/payments/currency',
        method: 'GET',
        success: function(response) {
            if (response.success && response.data) {
                currentCurrency = response.data.symbol;
                currencyAtRight = response.data.symbolAtRight;
                decimal_degits = response.data.decimal_degits || 0;
            }
        },
        error: function() {
            // Fallback values
            currentCurrency = '$';
            currencyAtRight = false;
            decimal_degits = 2;
        }
    });
    $('select').change(async function() {
        var zoneValue = $('.zone_selector').val();
        var restaurantTypeValue = $('.restaurant_type_selector').val();
        var businessModelValue = $('.business_model_selector').val();
        var cuisineValue = $('.cuisine_selector').val();

        console.log('Filter change triggered (SQL):');
        console.log('- Zone Value:', zoneValue);
        console.log('- Restaurant Type:', restaurantTypeValue);
        console.log('- Business Model:', businessModelValue);
        console.log('- Cuisine:', cuisineValue);

        // Store filter values for SQL query
        window.selectedZone = zoneValue || '';
        window.selectedRestaurantType = restaurantTypeValue || '';
        window.selectedBusinessModel = businessModelValue || '';
        window.selectedCuisine = cuisineValue || '';

        // Reload the table with new filters (will be sent to backend)
        $('#storeTable').DataTable().ajax.reload();
    });

    // Clear all filters functionality
    $('#clearFilters').click(function() {
        $('.zone_selector').val('').trigger('change');
        $('.restaurant_type_selector').val('').trigger('change');
        $('.business_model_selector').val('').trigger('change');
        $('.cuisine_selector').val('').trigger('change');

        // Clear filter variables
        window.selectedZone = '';
        window.selectedRestaurantType = '';
        window.selectedBusinessModel = '';
        window.selectedCuisine = '';

        // Reload the table
        $('#storeTable').DataTable().ajax.reload();
    });

    // Test function to check zone data - SQL version
    window.testZoneData = function() {
        console.log('Testing zone data from SQL...');
        $.ajax({
            url: '{{ route("restaurants.data") }}',
            method: 'GET',
            data: { start: 0, length: 5 },
            success: function(response) {
                console.log('Sample restaurants from SQL:');
                response.data.forEach(restaurant => {
                    console.log(`Restaurant: ${restaurant.title}, ZoneId: ${restaurant.zoneId}`);
                });
            }
        });
    };

    // Get vendor IDs by subscription plan - SQL version (handled server-side now)
    async function subscriptionPlanVendorIds(businessModelValue){
        // This is now handled on the server side via the restaurants.data endpoint
        // The filter is sent and applied in the SQL query
        return [];
    }
    var append_list = '';
    var placeholderImage = '{{ asset('images/placeholder.png') }}';
    var user_permissions = '<?php echo @session("user_permissions") ?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;
    if ($.inArray('restaurants.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }
    var cloneSourceVendorId = null;
    var zoneIdToName = {}; // Map zone IDs to names
    var zonesLoaded = false;

    // Load zones from SQL and create mapping
    var loadZonesPromise = new Promise(function(resolve){
        console.log('🔄 Loading zones from SQL for restaurants...');
        $.ajax({
            url: '{{ route("zone.data") }}',
            method: 'GET',
            success: function(response) {
                console.log('📊 Zones API response:', response);
                if (response.data && response.data.length > 0) {
                    response.data.forEach(function(zone) {
                        console.log('Zone found:', zone.name, 'ID:', zone.id);
                        // Store zone ID to name mapping
                        zoneIdToName[zone.id] = zone.name;
                        // Add zone to selector
                        $('.zone_selector').append($("<option></option>")
                            .attr("value", zone.id)
                            .text(zone.name));
                    });
                    console.log('✅ Zones loaded from SQL (' + response.data.length + ' zones):', zoneIdToName);
                } else {
                    console.warn('⚠️ No zones found in database');
                }
                // Enable the zone selector after zones are loaded
                $('.zone_selector').prop('disabled', false);
                zonesLoaded = true;
                resolve(zoneIdToName);
            },
            error: function(error) {
                console.error('❌ Error loading zones from SQL:', error);
                $('.zone_selector').prop('disabled', false);
                zonesLoaded = true;
                resolve(zoneIdToName);
            }
        });
    });

    // Load vendor categories from SQL
    $.ajax({
        url: '/api/vendor-categories',
        method: 'GET',
        success: function(response) {
            console.log('Loading vendor categories from SQL');
            if (response.data && response.data.length > 0) {
                response.data.forEach(function(category) {
                    if (category.publish) {
                        $('.cuisine_selector').append($("<option></option>")
                            .attr("value", category.id)
                            .text(category.title));
                    }
                });
            }
        },
        error: function(error) {
            console.error('Error loading vendor categories from SQL:', error);
        }
    });

    // Load subscription plans from SQL
    $.ajax({
        url: '/subscription-plans/fetch',
        method: 'GET',
        success: function(response) {
            console.log('Loading subscription plans from SQL');
            if (response.data && response.data.length > 0) {
                response.data.forEach(function(plan) {
                    if (plan.isEnable) {
                        $('.business_model_selector').append($("<option>").attr("value", plan.id).text(plan.name));
                    }
                });
            }
        },
        error: function(error) {
            console.error('Error loading subscription plans from SQL:', error);
        }
    });
    $('.zone_selector').select2({
        placeholder: "{{trans('lang.select_zone')}}",
        minimumResultsForSearch: Infinity,
        allowClear: true
    });
    $('.restaurant_type_selector').select2({
        placeholder: "{{trans('lang.restaurant_type')}}",
        minimumResultsForSearch: Infinity,
        allowClear: true
    });
    $('.business_model_selector').select2({
        placeholder: "{{trans('lang.business_model')}}",
        minimumResultsForSearch: Infinity,
        allowClear: true
    });
    $('.cuisine_selector').select2({
        placeholder: "{{trans('lang.select_cuisines')}}",
        minimumResultsForSearch: Infinity,
        allowClear: true
    });
    $('select').on("select2:unselecting", function(e) {
        var self = $(this);
        setTimeout(function() {
            self.select2('close');
        }, 0);
    });
    // Load placeholder image from SQL
    var placeholderImage = '{{ asset('images/placeholder.png') }}';
    $.ajax({
        url: '{{ route("vendors.placeholder-image") }}',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                placeholderImage = response.image;
            }
        }
    });
    $(document).ready(function () {
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
            console.log('🚀 Zones loaded, initializing Restaurant DataTable with zone mapping:', zoneIdToName);
            initializeRestaurantTable();
        });
    });

    function initializeRestaurantTable() {
        var fieldConfig = {
            columns: [
                { key: 'id', header: "{{trans('lang.id')}}" },
                { key: 'title', header: "{{trans('lang.restaurant')}}" },
                { key: 'authorName', header: "{{trans('lang.owner_name')}}" },
                { key: 'zoneName', header: "{{trans('lang.zone')}}" },
                { key: 'phonenumber', header: "{{trans('lang.phone')}}" },
                { key: 'createdAt', header: "{{trans('lang.created_at')}}" },
                { key: 'location', header: "{{trans('lang.location')}}" },
            ],
            fileName: "{{trans('lang.restaurant_table')}}",
        };
        const table = $('#storeTable').DataTable({
            pageLength: 30,
            lengthMenu: [[10,30, 50, 100], [10,30, 50, 100,]],
            processing: false,
            serverSide: true,
            responsive: true,
            ajax: function(data, callback, settings) {
                const start = data.start;
                const length = data.length;
                const searchValue = data.search.value.toLowerCase();

                if (searchValue.length >= 3 || searchValue.length === 0) {
                    $('#data-table_processing').show();
                }

                console.log('Fetching restaurants from SQL...');

                // Build request data with filters
                var requestData = {
                    start: start,
                    length: length,
                    draw: data.draw,
                    search: { value: searchValue },
                    zone: window.selectedZone || '',
                    restaurant_type: window.selectedRestaurantType || '',
                    business_model: window.selectedBusinessModel || ''
                    // vType: window.selectedBusinessModel || ''
                };

                // Fetch from SQL backend
                $.ajax({
                    url: '{{ route("restaurants.data") }}',
                    method: 'GET',
                    data: requestData,
                    success: async function(response) {
                        console.log('Restaurants loaded from SQL:', response.data.length);

                        const rawRecords = Array.isArray(response.data) ? response.data : [];

                        if (rawRecords.length === 0) {
                            // $('.rest_count').text('00');
                            // $('.rest_active_count').text('00');
                            // $('.rest_inactive_count').text('00');
                            // $('.new_joined_rest').text('00');
                            // ⭐ ALWAYS update counts after filters
                            $('.rest_count').text(response.stats.total);
                            $('.rest_active_count').text(response.stats.active);
                            $('.rest_inactive_count').text(response.stats.inactive);
                            $('.new_joined_rest').text(response.stats.new_joined);

                            $('#data-table_processing').hide();
                            callback({
                                draw: data.draw,
                                recordsTotal: response.recordsTotal || 0,
                                recordsFiltered: response.recordsFiltered || 0,
                                data: [],
                                filteredData: []
                            });
                            return;
                        }

                        // // Update statistics from response
                        // $('.rest_count').text(response.stats.total);
                        // $('.rest_active_count').text(response.stats.active);
                        // $('.rest_inactive_count').text(response.stats.inactive);
                        // $('.new_joined_rest').text(response.stats.new_joined);

                        // ⭐ ALWAYS update counts here as well
                        $('.rest_count').text(response.stats.total);
                        $('.rest_active_count').text(response.stats.active);
                        $('.rest_inactive_count').text(response.stats.inactive);
                        $('.new_joined_rest').text(response.stats.new_joined);

                        // Build table rows
                        let records = [];
                        const exportableRecords = [];

                        for (let restaurant of rawRecords) {
                            var rowData = await buildHTML(restaurant);
                            records.push(rowData);

                            exportableRecords.push({
                                id: restaurant.id || '',
                                title: restaurant.title || '',
                                authorName: restaurant.authorName || '',
                                zoneName: restaurant.zone_name || restaurant.zoneName || (restaurant.zoneId ? (zoneIdToName[restaurant.zoneId] || '') : ''),
                                phonenumber: restaurant.phonenumber || '',
                                createdAt: restaurant.createdAtFormatted || restaurant.createdAt || '',
                                location: restaurant.location || ''
                            });
                        }

                        $('#data-table_processing').hide();
                        callback({
                            draw: data.draw,
                            recordsTotal: response.recordsTotal,
                            recordsFiltered: response.recordsFiltered,
                            data: records,
                            filteredData: exportableRecords
                        });
                    },
                    error: function(error) {
                        console.error("Error fetching restaurants from SQL:", error);
                        $('#data-table_processing').hide();
                        $('.rest_count').text('00');
                        $('.rest_active_count').text('00');
                        $('.rest_inactive_count').text('00');
                        $('.new_joined_rest').text('00');
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: [],
                            filteredData: []
                        });
                    }
                });
            },
            order: (checkDeletePermission) ? [[5, 'desc']] : [[4, 'desc']],
            columnDefs: [
                {
                    targets: (checkDeletePermission) ? 5 : 4,
                    type: 'date',
                    render: function(data) {
                        return data;
                    }
                },
                { orderable: false, targets: (checkDeletePermission) ? [0, 5, 6, 10, 11, 12] : [4, 5, 10, 11, 12] }, // Best (10), GST (11), and Publish (12) columns are not sortable
            ],
            "language": {
                "zeroRecords": "{{trans('lang.no_record_found')}}",
                "emptyTable": "{{trans('lang.no_record_found')}}",
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
                            title: 'restaurants',
                        },
                        {
                            extend: 'pdfHtml5',
                            text: 'Export PDF',
                            exportOptions: {
                                columns: ':visible:not(:first-child):not(:last-child)'
                            },
                            title: 'restaurants',
                        },
                        {
                            extend: 'csvHtml5',
                            text: 'Export CSV',
                            exportOptions: {
                                columns: ':visible:not(:first-child):not(:last-child)'
                            },
                            title: 'restaurants',
                        }
                    ]
                }
            ],
            initComplete: function() {
                $(".dataTables_filter").append($(".dt-buttons").detach());
                $('.dataTables_filter input').attr('placeholder', 'Search here...').attr('autocomplete','new-password').val('');
                $('.dataTables_filter label').contents().filter(function() {
                    return this.nodeType === 3;
                }).remove();
            }
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
            alert(searchValue);
            if (searchValue.length >= 3) {
                $('#data-table_processing').show();
                table.search(searchValue).draw();
            } else if (searchValue.length === 0) {
                $('#data-table_processing').show();
                table.search('').draw();
            }
        }, 300));
    } // Close initializeRestaurantTable()
    async function buildHTML(val) {
        var html = [];
        var id = val.id;
        var vendorUserId = val.author;
        var route1 = '{{route("restaurants.edit",":id")}}';
        route1 = route1.replace(':id', id);
        var route_view = '{{route("restaurants.view",":id")}}';
        route_view = route_view.replace(':id', id);
        if (checkDeletePermission) {
            html.push('<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '" author="' + val.author + '"><label class="col-3 control-label"\n' +
                'for="is_open_' + id + '" ></label></td>');
        }
        var restaurantInfo = '';
        if (val.photo != '' && val.photo != null) {
            restaurantInfo += '<img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="" width="100%" style="width:70px;height:70px;" src="' + val.photo + '" alt="image">';
        } else {
            restaurantInfo += '<img alt="" width="100%" style="width:70px;height:70px;" src="' + placeholderImage + '" alt="image">';
        }
        if(val.title != " " && val.title != "null" && val.title != null && val.title != ""){
            restaurantInfo += '<a href="' + route_view + '">' + val.title + '</a>';
        }else{
            restaurantInfo += 'UNKNOWN';
        }
        html.push(restaurantInfo);
        var ownerInfo = '';
        if (val.authorName) {
            ownerInfo +=  '<a href="' + route_view + '">' + val.authorName + '</a><br>';
        }
        if (val.hasOwnProperty('phonenumber') && val.phonenumber != null && val.phonenumber != "") {
            ownerInfo +=  '<a>' + val.phonenumber + '</a>';
        } else {
            ownerInfo += '';
        }
        html.push(ownerInfo);

        // Zone column - Display zone name based on zoneId from SQL
        var zoneInfo = '';
        if (val.hasOwnProperty('zoneId') && val.zoneId != null && val.zoneId != '') {
            // Check if zone exists in mapping (loaded from SQL)
            if (zoneIdToName[val.zoneId]) {
                zoneInfo = '<span class="badge badge-info py-2 px-3">' + zoneIdToName[val.zoneId] + '</span>';
                console.log('✅ Zone found for restaurant ' + val.title + ':', zoneIdToName[val.zoneId]);
            } else {
                // Zone ID exists but not found in zones table
                zoneInfo = '<span class="badge badge-warning py-2 px-3" style="color: #666;">Zone Not Found (ID: ' + val.zoneId + ')</span>';
                console.warn('⚠️ Zone ID "' + val.zoneId + '" not found in zones table for restaurant ' + val.title);
            }
        } else {
            // No zone ID assigned
            zoneInfo = '<span style="color: #999; font-style: italic;">null</span>';
            console.log('ℹ️ No zone assigned for restaurant ' + val.title);
        }
        html.push(zoneInfo);

        // Admin Commission column - Display fix_commission from vendors.adminCommission
        var adminCommissionDisplay = '';
        var commissionValue = null;

        // Parse adminCommission (might be JSON string or object)
        if (val.adminCommission) {
            var commission = val.adminCommission;

            // If it's a string, try to parse it as JSON
            if (typeof commission === 'string') {
                try {
                    commission = JSON.parse(commission);
                } catch (e) {
                    console.warn('Could not parse adminCommission for restaurant ' + val.title);
                }
            }

            // Extract fix_commission value
            if (commission && typeof commission === 'object' && commission.fix_commission !== undefined) {
                commissionValue = commission.fix_commission;
            }
        }

        // Display commission with styling like zones
        if (commissionValue !== null && commissionValue !== '' && !isNaN(commissionValue)) {
            var formattedCommission = parseFloat(commissionValue).toFixed(decimal_degits);
            if (currencyAtRight) {
                adminCommissionDisplay = '<span class="badge badge-success py-2 px-3">' + formattedCommission + ' ' + '</span>';
            } else {
                adminCommissionDisplay = '<span class="badge badge-success py-2 px-3">' + ' ' + formattedCommission + '</span>';
            }
        } else {
            // No commission set
            adminCommissionDisplay = '<span style="color: #999; font-style: italic;">N/A</span>';
        }
        html.push(adminCommissionDisplay);
        var date = '';
        var time = '';
        var createdAtText = '';
        if (val.hasOwnProperty('createdAtFormatted') && val.createdAtFormatted) {
            createdAtText = val.createdAtFormatted;
        } else if (val.hasOwnProperty('createdAt') && val.createdAt) {
            createdAtText = val.createdAt;
        } else if (val.hasOwnProperty('createdAtRaw') && val.createdAtRaw) {
            createdAtText = val.createdAtRaw;
            }
        html.push('<span class="dt-time">' + createdAtText + '</span>');
        var payoutRequests = '{{route("users.walletstransaction",":id")}}';
        payoutRequests = payoutRequests.replace(':id', val.author);
        html.push('<a href="' + payoutRequests + '">{{trans("lang.wallet_history")}}</a>');
        // ==============================
// Subscription Plan Columns - Fetch directly from subscription_plans table
// ==============================
        var planNameHtml =
            '<span style="color:#999;font-style:italic;">N/A</span>';
        var planCommissionHtml =
            '<span style="color:#999;font-style:italic;">N/A</span>';
        var planExpiryHtml =
            '<span style="color:#999;font-style:italic;">N/A</span>';

        // Plan Name - directly from subscription_plans table
        if (val.subscriptionPlanName && val.subscriptionPlanName.trim() !== '') {
            planNameHtml =
                '<span class="badge badge-primary py-2 px-3">' +
                val.subscriptionPlanName +
                '</span>';
        }

        // Plan Commission - directly from subscription_plans table (place column)
        if (val.subscriptionPlanCommission !== undefined && val.subscriptionPlanCommission !== null && val.subscriptionPlanCommission !== '') {
            var commissionValue = val.subscriptionPlanCommission;
            if (typeof commissionValue === 'string') {
                commissionValue = parseFloat(commissionValue);
            }
            if (!isNaN(commissionValue) && commissionValue > 0) {
                planCommissionHtml =
                    '<span class="py-2 px-3 font-weight-bold">' +
                    commissionValue + '%</span>';
            }
        }

        // Expiry Date (DB column: subscriptionExpiryDate)
        if (val.subscriptionExpiryDate) {
            try {
                // Handle different date formats
                var expiryDateStr = val.subscriptionExpiryDate;
                // Replace space with T for ISO format if needed
                if (expiryDateStr.indexOf('T') === -1 && expiryDateStr.indexOf(' ') !== -1) {
                    expiryDateStr = expiryDateStr.replace(' ', 'T');
                }
                const expiryDate = new Date(expiryDateStr);
                const today = new Date();

                if (!isNaN(expiryDate.getTime())) {
                    // Format date for display
                    const formattedDate = expiryDate.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });

                    planExpiryHtml =
                        expiryDate < today
                            ? '<span class="badge badge-danger py-2 px-3">Expired</span>'
                            : '<span class="badge badge-success py-2 px-3">' +
                            formattedDate +
                            '</span>';
                } else {
                    // If date parsing fails, show raw value
                    planExpiryHtml = '<span class="badge badge-info py-2 px-3">' + val.subscriptionExpiryDate + '</span>';
                }
            } catch (e) {
                // If error, show raw value
                planExpiryHtml = '<span class="badge badge-info py-2 px-3">' + val.subscriptionExpiryDate + '</span>';
            }
        }

        html.push(planNameHtml);
        html.push(planCommissionHtml);
        html.push(planExpiryHtml);

        // Best column - Toggle button
        var bestHtml = '';
        var isBest = val.best === true || val.best === 1 || val.best === '1';
        var bestIconClass = isBest ? 'mdi-star' : 'mdi-star-outline';
        var bestColorClass = isBest ? 'text-warning' : 'text-muted';
        var bestTitle = isBest ? 'Mark as not best' : 'Mark as best';
        
        bestHtml = `<a href="javascript:void(0)" class="toggle-best-btn" data-restaurant-id="${val.id}" data-restaurant-name="${val.title || 'Restaurant'}" data-current-best="${isBest ? 1 : 0}" title="${bestTitle}">
            <i class="mdi ${bestIconClass} ${bestColorClass}" style="font-size: 20px;"></i>
        </a>`;
        html.push(bestHtml);

        // GST column - Toggle switch (same as publish)
        var gstHtml = '';
        var isGst = val.gst === true || val.gst === 1 || val.gst === '1';
        var gstChecked = isGst ? 'checked' : '';
        var gstTitle = isGst ? 'Unaccept GST' : 'Accept GST';
        
        gstHtml = `<label class="switch">
            <input type="checkbox" class="toggle-gst-switch" data-restaurant-id="${val.id}" data-restaurant-name="${val.title || 'Restaurant'}" data-current-gst="${isGst ? 1 : 0}" ${gstChecked} title="${gstTitle}">
            <span class="slider"></span>
        </label>`;
        html.push(gstHtml);

        // Publish column - Toggle switch (like screenshot)
        var publishHtml = '';
        var isPublish = val.publish === true || val.publish === 1 || val.publish === '1';
        var publishChecked = isPublish ? 'checked' : '';
        var publishTitle = isPublish ? 'Unpublish restaurant' : 'Publish restaurant';
        
        publishHtml = `<label class="switch">
            <input type="checkbox" class="toggle-publish-switch" data-restaurant-id="${val.id}" data-restaurant-name="${val.title || 'Restaurant'}" data-current-publish="${isPublish ? 1 : 0}" ${publishChecked} title="${publishTitle}">
            <span class="slider"></span>
        </label>`;
        html.push(publishHtml);

        var active = val.isActive;
        var vendorId = val.id;
        var food_url = '{{route("restaurants.foods",":id")}}';
        food_url = food_url.replace(":id", vendorId);
        var vendor_url = '{{route("restaurants.orders",":id")}}';
        vendor_url = vendor_url.replace(":id", vendorId);
        var actionHtml = '';
        actionHtml += `<span class="action-btn">
            <a href="${food_url}"><i class="mdi mdi-food" title="Foods"></i></a>
            <a href="${vendor_url}"><i class="mdi mdi-view-list" title="Orders"></i></a>
            <a href="javascript:void(0)" vendor_id="${val.id}" author="${val.author}" name="vendor-clone" title="Copy"><i class="mdi mdi-content-copy"></i></a>
            <a href="${route_view}"><i class="mdi mdi-eye" title="View"></i></a>
            <a href="${route1}"><i class="mdi mdi-lead-pencil" title="Edit"></i></a>
            <a href="javascript:void(0)" class="impersonate-restaurant-btn" data-restaurant-id="${val.id}" data-restaurant-name="${val.title || 'Unknown Restaurant'}" title="Login as Restaurant"><i class="mdi mdi-account-switch text-primary"></i></a>`;
        if (checkDeletePermission) {
            actionHtml += `<a id="${val.id}" author="${val.author}" name="vendor-delete" class="delete-btn" href="javascript:void(0)" title="Delete"><i class="mdi mdi-delete"></i></a>`;
        }
        actionHtml += `</span>`;
        html.push(actionHtml);
        return html;
    }

    // Zone names are now loaded from SQL and displayed immediately via zoneIdToName mapping
    // No need for async zone name fetching


    $("#is_active").click(function () {
        $("#storeTable .is_open").prop('checked', $(this).prop('checked'));
    });
    $("#deleteAll").click(function () {
        var selectedIds = $('#storeTable .is_open:checked').map(function () {
            return $(this).attr('dataId');
        }).get();

        if (selectedIds.length === 0) {
            alert("{{trans('lang.select_delete_alert')}}");
            return;
        }

        if (!confirm("{{trans('lang.selected_delete_alert')}}")) {
            return;
        }

                jQuery("#data-table_processing").show();

        $.ajax({
            url: "{{ route('restaurants.bulk-delete') }}",
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                ids: selectedIds
            },
            success: function (response) {
                jQuery("#data-table_processing").hide();
                if (response.success) {
                    $('#storeTable').DataTable().ajax.reload(null, false);
        } else {
                    alert(response.message || 'Unable to delete restaurants.');
                }
            },
            error: function (xhr) {
                jQuery("#data-table_processing").hide();
                console.error('Error deleting restaurants:', xhr);
                alert('Error deleting restaurants.');
        }
        });
    });
    $(document.body).on('click', '.redirecttopage', function () {
        var url = $(this).attr('data-url');
        window.location.href = url;
    });
    $(document).on("click", "a[name='vendor-delete']", function (e) {
        e.preventDefault();
        var id = this.id;
        if (!confirm("{{trans('lang.selected_delete_alert')}}")) {
            return;
        }
        jQuery("#data-table_processing").show();

        $.ajax({
            url: "{{ url('restaurants') }}/" + id,
            method: "DELETE",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function (response) {
                jQuery("#data-table_processing").hide();
                if (response.success) {
                    $('#storeTable').DataTable().ajax.reload(null, false);
                } else {
                    alert(response.message || 'Unable to delete restaurant.');
                }
            },
            error: function (xhr) {
                jQuery("#data-table_processing").hide();
                console.error('Error deleting restaurant:', xhr);
                alert('Error deleting restaurant.');
            }
        });
    });
    $(document).on("click", "a[name='vendor-clone']", function (e) {
        e.preventDefault();
        cloneSourceVendorId = $(this).attr('vendor_id');
        $(".error_top").hide().html("");
        $("#user_name").val('');
        $("#user_last_name").val('');
        $("#user_email").val('');
        $("#user_password").val('');
        $("#vendor_title").val('');
        if (!cloneSourceVendorId) {
            alert('Unable to load restaurant details.');
            return;
        }
        jQuery("#data-table_processing").show();
        $.ajax({
            url: "{{ url('restaurants') }}/" + cloneSourceVendorId + "/clone-data",
            method: "GET",
            success: function (response) {
                jQuery("#data-table_processing").hide();
                if (response.success && response.data) {
                    var vendorInfo = response.data.vendor || {};
                    var ownerInfo = response.data.owner || {};
                    $("#vendor_title_lable").text(vendorInfo.title || '');
                    $("#vendor_title").val(vendorInfo.title || '');
                    $("#user_name").val(ownerInfo.firstName || '');
                    $("#user_last_name").val(ownerInfo.lastName || '');
                    $("#create_vendor").modal('show');
                } else {
                    alert(response.message || 'Unable to load restaurant details.');
                }
            },
            error: function (xhr) {
                jQuery("#data-table_processing").hide();
                console.error('Error loading clone data:', xhr);
                alert('Error loading restaurant details.');
            }
        });
    });

    $(document).on("click", ".save-form-btn", function () {
        if (!cloneSourceVendorId) {
            alert('Unable to determine restaurant to clone.');
            return;
        }
        $(".error_top").hide().html("");
        var vendor_title = $("#vendor_title").val();
        var userFirstName = $("#user_name").val();
        var userLastName = $("#user_last_name").val();
        var email = $("#user_email").val();
        var password = $("#user_password").val();

        if (userFirstName === '') {
            $(".error_top").show().html("<p>{{trans('lang.user_name_required')}}</p>");
            window.scrollTo(0, 0);
            return;
        }
        if (userLastName === '') {
            $(".error_top").show().html("<p>{{trans('lang.user_last_name_required')}}</p>");
            window.scrollTo(0, 0);
            return;
        }
        if (vendor_title === '') {
            $(".error_top").show().html("<p>{{trans('lang.vendor_title_required')}}</p>");
            window.scrollTo(0, 0);
            return;
        }
        if (email === '') {
            $(".error_top").show().html("<p>{{trans('lang.user_email_required')}}</p>");
            window.scrollTo(0, 0);
            return;
        }
        if (password === '') {
            $(".error_top").show().html("<p>{{trans('lang.enter_owners_password_error')}}</p>");
            window.scrollTo(0, 0);
            return;
        }

        jQuery("#data-table_processing").show();
        $.ajax({
            url: "{{ route('restaurants.clone') }}",
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                source_vendor_id: cloneSourceVendorId,
                vendor_title: vendor_title,
                first_name: userFirstName,
                last_name: userLastName,
                email: email,
                password: password
            },
            success: function (response) {
                jQuery("#data-table_processing").hide();
                if (response.success) {
                    alert(response.message || 'Successfully created.');
                    $("#create_vendor").modal('hide');
                    $('#storeTable').DataTable().ajax.reload(null, false);
                } else {
                    $(".error_top").show().html(`<p>${response.message || 'Unable to clone restaurant.'}</p>`);
                    window.scrollTo(0, 0);
                }
            },
            error: function (xhr) {
                jQuery("#data-table_processing").hide();
                var message = 'Error cloning restaurant.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                $(".error_top").show().html(`<p>${message}</p>`);
                window.scrollTo(0, 0);
            }
        });
    });

    // Global Restaurant Status Toggle Functionality - MySQL version
     $(document).ready(function() {
         // Update status text when toggle changes
         $('#global_restaurant_status').change(function() {
             var isChecked = $(this).is(':checked');
             $('.status-text').text(isChecked ? 'All Open' : 'All Closed');
         });

        // Apply global status via backend
        $('#apply_global_status').click(function() {
             var isOpen = $('#global_restaurant_status').is(':checked');
             var statusText = isOpen ? 'open' : 'closed';
            var selectedZone = window.selectedZone || $('.zone_selector').val() || '';
            var scopeMessage = selectedZone ? 'the selected zone' : 'ALL zones';

            if (!confirm(`Are you sure you want to set all restaurants in ${scopeMessage} to ${statusText}?`)) {
                 return;
             }

             var $btn = $(this);
             var originalText = $btn.html();
             $btn.html('<i class="mdi mdi-loading mdi-spin mr-1"></i>Updating...').prop('disabled', true);

            $.ajax({
                url: '{{ route("restaurants.global-status") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    is_open: isOpen ? 1 : 0,
                    zone_id: selectedZone
                },
                success: function(response) {
                    if (response.success) {
                        var updated = response.updated || 0;
                        var successMessage = `${updated} restaurant${updated === 1 ? '' : 's'} updated successfully.`;
                        showNotification('success', successMessage);
                    } else {
                        showNotification('error', response.message || 'Unable to update restaurants.');
                    }
                },
                error: function(xhr) {
                    var message = 'Unable to update restaurants.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showNotification('error', message);
                },
                complete: function() {
                 $btn.html(originalText).prop('disabled', false);
                    if ($.fn.dataTable.isDataTable('#storeTable')) {
                        $('#storeTable').DataTable().ajax.reload(null, false);
                    }
             }
            });
         });

         // Simple styling for the status text
         $('<style>')
             .prop('type', 'text/css')
             .html(`
                 .status-text {
                     font-weight: 500;
                     color: #333;
                     font-size: 13px;
                     display: inline-block;
                     white-space: nowrap;
                 }
             `)
             .appendTo('head');
     });

     // Handle restaurant impersonation
     $(document).on('click', '.impersonate-restaurant-btn', function(e) {
         e.preventDefault();

         const restaurantId = $(this).data('restaurant-id');
         const restaurantName = $(this).data('restaurant-name');
         const $btn = $(this);

         // Show confirmation dialog
         if (!confirm(`Are you sure you want to login as "${restaurantName}"?\n\nThis will redirect you to the restaurant panel and log you in automatically.`)) {
             return;
         }

         // Show loading state
         const originalHtml = $btn.html();
         $btn.html('<i class="mdi mdi-loading mdi-spin text-primary"></i>').prop('disabled', true);

         // Generate impersonation token with retry logic
         let retryCount = 0;
         const maxRetries = 3;

         function attemptImpersonation() {
             $.ajax({
                 url: '{{ route("admin.impersonate.generate") }}',
                 method: 'POST',
                 timeout: 10000, // 10 second timeout
                 data: {
                     restaurant_id: restaurantId,
                     expiration_minutes: 5, // 5 minutes for security
                     _token: '{{ csrf_token() }}'
                 },
                                 success: function(response) {
                    console.log('🔍 Admin Panel Response:', response);

                    if (response.success) {
                        // Show success message
                        showNotification('success', `Redirecting to ${response.restaurant_name}...`);

                        console.log('🔍 Impersonation URL:', response.impersonation_url);

                        // Redirect to restaurant panel with impersonation token
                        setTimeout(() => {
                            // Try to focus existing tab first, then open new one
                            const newWindow = window.open(response.impersonation_url, '_blank');
                            if (newWindow) {
                                newWindow.focus();
                            }
                        }, 1000);
                     } else {
                         // Handle specific error cases
                         if (response.retry_after) {
                             showNotification('warning', `${response.error} Retrying in ${response.retry_after} seconds...`);
                             setTimeout(() => {
                                 if (retryCount < maxRetries) {
                                     retryCount++;
                                     attemptImpersonation();
                                 } else {
                                     showNotification('error', 'Maximum retry attempts reached. Please try again later.');
                                 }
                             }, response.retry_after * 1000);
                         } else {
                             showNotification('error', response.error || 'Failed to generate impersonation token');
                         }
                     }
                 },
                 error: function(xhr) {
                     let errorMsg = 'An error occurred while generating impersonation token';

                     if (xhr.status === 429) {
                         errorMsg = 'Too many attempts. Please wait before trying again.';
                     } else if (xhr.status === 403) {
                         errorMsg = 'Access denied. You do not have permission to impersonate.';
                     } else if (xhr.status === 500) {
                         errorMsg = 'Server error. Please try again later.';
                     } else if (xhr.responseJSON?.error) {
                         errorMsg = xhr.responseJSON.error;
                     }

                     showNotification('error', errorMsg);
                 },
                 complete: function() {
                     // Restore button state
                     $btn.html(originalHtml).prop('disabled', false);
                 }
             });
         }

         attemptImpersonation();
     });

     // Handle Publish toggle switch
     $(document).on('change', '.toggle-publish-switch', function(e) {
         e.preventDefault();
         
         const $switch = $(this);
         const restaurantId = $switch.data('restaurant-id');
         const restaurantName = $switch.data('restaurant-name');
         const currentPublish = $switch.is(':checked');
         
         // Disable switch during request
         $switch.prop('disabled', true);
         
         $.ajax({
             url: '{{ url("restaurants") }}/' + restaurantId + '/toggle-publish',
             method: 'POST',
             headers: {
                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
             },
             success: function(response) {
                 if (response.success) {
                     // Update UI
                     $switch.data('current-publish', response.publish ? 1 : 0);
                     $switch.attr('title', response.publish ? 'Unpublish restaurant' : 'Publish restaurant');
                     
                     showNotification('success', response.message || (response.publish ? 'Restaurant published' : 'Restaurant unpublished'));
                     
                     // Reload table to reflect changes
                     if ($.fn.dataTable.isDataTable('#storeTable')) {
                         $('#storeTable').DataTable().ajax.reload(null, false);
                     }
                 } else {
                     // Revert switch state on error
                     $switch.prop('checked', !currentPublish);
                     showNotification('error', response.message || 'Unable to update publish status');
                 }
             },
             error: function(xhr) {
                 // Revert switch state on error
                 $switch.prop('checked', !currentPublish);
                 let errorMsg = 'Unable to update publish status';
                 if (xhr.responseJSON && xhr.responseJSON.message) {
                     errorMsg = xhr.responseJSON.message;
                 }
                 showNotification('error', errorMsg);
             },
             complete: function() {
                 $switch.prop('disabled', false);
             }
         });
     });

     // Handle GST toggle switch
     $(document).on('change', '.toggle-gst-switch', function(e) {
         e.preventDefault();
         
         const $switch = $(this);
         const restaurantId = $switch.data('restaurant-id');
         const restaurantName = $switch.data('restaurant-name');
         const currentGst = $switch.is(':checked');
         
         // Disable switch during request
         $switch.prop('disabled', true);
         
         $.ajax({
             url: '{{ url("restaurants") }}/' + restaurantId + '/toggle-gst',
             method: 'POST',
             headers: {
                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
             },
             success: function(response) {
                 if (response.success) {
                     // Update UI
                     $switch.data('current-gst', response.gst ? 1 : 0);
                     $switch.attr('title', response.gst ? 'Unaccept GST' : 'Accept GST');
                     
                     showNotification('success', response.message || (response.gst ? 'GST accepted' : 'GST unaccepted'));
                     
                     // Reload table to reflect changes
                     if ($.fn.dataTable.isDataTable('#storeTable')) {
                         $('#storeTable').DataTable().ajax.reload(null, false);
                     }
                 } else {
                     // Revert switch state on error
                     $switch.prop('checked', !currentGst);
                     showNotification('error', response.message || 'Unable to update GST status');
                 }
             },
             error: function(xhr) {
                 // Revert switch state on error
                 $switch.prop('checked', !currentGst);
                 let errorMsg = 'Unable to update GST status';
                 if (xhr.responseJSON && xhr.responseJSON.message) {
                     errorMsg = xhr.responseJSON.message;
                 }
                 showNotification('error', errorMsg);
             },
             complete: function() {
                 $switch.prop('disabled', false);
             }
         });
     });

     // Handle best restaurant toggle
     $(document).on('click', '.toggle-best-btn', function(e) {
         e.preventDefault();
         
         const $btn = $(this);
         const restaurantId = $btn.data('restaurant-id');
         const restaurantName = $btn.data('restaurant-name');
         const currentBest = $btn.data('current-best') == 1;
         const $icon = $btn.find('i');
         
         // Show loading state
         const originalHtml = $icon.attr('class');
         $icon.attr('class', 'mdi mdi-loading mdi-spin text-primary');
         $btn.prop('disabled', true);
         
         $.ajax({
             url: '{{ url("restaurants") }}/' + restaurantId + '/toggle-best',
             method: 'POST',
             headers: {
                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
             },
             success: function(response) {
                 if (response.success) {
                     // Update UI
                     const isBest = response.best;
                     const newIconClass = isBest ? 'mdi-star' : 'mdi-star-outline';
                     const newColorClass = isBest ? 'text-warning' : 'text-muted';
                     const newTitle = isBest ? 'Mark as not best' : 'Mark as best';
                     
                     $icon.attr('class', 'mdi ' + newIconClass + ' ' + newColorClass);
                     $btn.attr('title', newTitle);
                     $btn.data('current-best', isBest ? 1 : 0);
                     
                     showNotification('success', response.message || (isBest ? 'Restaurant marked as best' : 'Restaurant unmarked as best'));
                     
                     // Reload table to reflect changes
                     if ($.fn.dataTable.isDataTable('#storeTable')) {
                         $('#storeTable').DataTable().ajax.reload(null, false);
                     }
                 } else {
                     showNotification('error', response.message || 'Unable to update best status');
                     // Restore original icon
                     $icon.attr('class', originalHtml);
                 }
             },
             error: function(xhr) {
                 let errorMsg = 'Unable to update best status';
                 if (xhr.responseJSON && xhr.responseJSON.message) {
                     errorMsg = xhr.responseJSON.message;
                 } else if (xhr.status === 422 && xhr.responseJSON) {
                     errorMsg = xhr.responseJSON.message || 'Validation error: Maximum 9 best restaurants allowed per zone';
                 }
                 showNotification('error', errorMsg);
                 // Restore original icon
                 $icon.attr('class', originalHtml);
             },
             complete: function() {
                 $btn.prop('disabled', false);
             }
         });
     });

     // Notification helper function
     function showNotification(type, message) {
         const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
         const notification = $(`
             <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                 ${message}
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                 </button>
             </div>
         `);
         $('body').append(notification);
         // Auto-dismiss after 5 seconds
         setTimeout(() => {
             notification.alert('close');
         }, 5000);
     }

     // CSS for Toggle Switch (Publish column)
     $('<style>')
         .prop('type', 'text/css')
         .html(`
             .switch {
                 position: relative;
                 display: inline-block;
                 width: 50px;
                 height: 24px;
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
                 transition: .4s;
                 border-radius: 24px;
             }
             .slider:before {
                 position: absolute;
                 content: "";
                 height: 18px;
                 width: 18px;
                 left: 3px;
                 bottom: 3px;
                 background-color: white;
                 transition: .4s;
                 border-radius: 50%;
             }
             input:checked + .slider {
                 background-color: #4CAF50;
             }
             input:checked + .slider:before {
                 transform: translateX(26px);
             }
             input:disabled + .slider {
                 opacity: 0.6;
                 cursor: not-allowed;
             }
         `)
         .appendTo('head');
</script>
@endsection
