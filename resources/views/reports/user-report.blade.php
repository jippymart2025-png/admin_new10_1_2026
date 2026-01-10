@extends('layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ url('/reports/user/data') }}">User Order Report</a>
                    </li>
                    <li class="breadcrumb-item active">User Report</li>
                </ol>
            </div>
        </div>
        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center px-4">
                            <span class="icon mr-3"><img src="{{ asset('images/subscription.png') }}"></span>
                            <h3 class="text">User Order Report</h3>
                            <span class="counter ml-3 total_count"></span>
                        </div>
                        <div class="d-flex top-title-right align-self-center px-4">
                            <div class="input-group mr-3" style="width: 300px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                                </div>
                                <input type="text" class="form-control" id="user_search_input" placeholder="Search by name, email, phone, ID...">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="clear_search_btn" title="Clear search">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="select-box pl-3">
                                <select class="form-control zone_selector" id="zone_filter">
                                    <option value="" selected>{{trans('lang.select_zone')}}</option>
                                </select>
                            </div>
                            <div class="ml-2">
                                <button type="button" class="btn btn-sm btn-secondary" id="clear_filters_btn" style="display: none;">
                                    <i class="mdi mdi-close-circle mr-1"></i>Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">

                    <div class="table-responsive">
                        <table id="userReportTable" class="table table-bordered table-striped table-hover" width="100%">
                            <thead class="thead-light">
                            <tr>
                                <th>S.NO</th>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Zone</th>
                                <th>Last Order Date</th>
                            </tr>
                            </thead>
                            <tbody id="user-report-body">
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                    <div id="data-table_processing" class="data-table-processing" style="display: none;">Processing...</div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
    <script>
        var zoneIdToName = {};
        var selectedZoneFilter = '';
        var searchTerm = '';
        var userReportDataTable = null;

        // // Function to show/hide clear filters button
        // function updateClearFiltersButton() {
        //     if (selectedZoneFilter || searchTerm) {
        //         $('#clear_filters_btn').show();
        //     } else {
        //         $('#clear_filters_btn').hide();
        //     }
        // }

        // Load zones from SQL and create mapping
        var loadZonesPromise = new Promise(function(resolve){
            console.log('🔄 Loading zones for user report...');
            $.ajax({
                url: '{{ route("zone.data") }}',
                method: 'GET',
                success: function(response) {
                    console.log('📊 Zones response:', response);
                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function(zone) {
                            zoneIdToName[zone.id] = zone.name;
                            $('#zone_filter').append($("<option></option>")
                                .attr("value", zone.id)
                                .text(zone.name));
                        });
                        console.log('✅ Loaded ' + response.data.length + ' zones');
                    }
                    $('#zone_filter').prop('disabled', false);
                    zonesLoaded = true;
                    resolve(zoneIdToName);
                },
                error: function(error) {
                    console.error('❌ Error loading zones:', error);
                    $('#zone_filter').prop('disabled', false);
                    zonesLoaded = true;
                    resolve(zoneIdToName);
                }
            });
        });

        // Initialize Select2 after zones are loaded
        loadZonesPromise.then(function() {
            if (typeof $.fn.select2 !== 'undefined') {
                $('#zone_filter').select2({
                    placeholder: "{{trans('lang.select_zone')}}",
                    minimumResultsForSearch: Infinity,
                    allowClear: true,
                    width: '200px'
                });
            }

            // Load user data after zones are loaded
            loadUserReport();
        });

        function formatDate(dateStr) {
            if (!dateStr || dateStr === 'null' || dateStr === null) {
                return '<span class="badge badge-warning">Never Ordered</span>';
            }
            try {
                var date = new Date(dateStr);
                if (isNaN(date.getTime())) {
                    return '<span class="badge badge-warning">Invalid Date</span>';
                }
                var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                var day = String(date.getDate()).padStart(2, '0');
                var month = months[date.getMonth()];
                var year = date.getFullYear();
                var hours = String(date.getHours()).padStart(2, '0');
                var minutes = String(date.getMinutes()).padStart(2, '0');
                var ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12;
                return day + ' ' + month + ' ' + year + ', ' + hours + ':' + minutes + ' ' + ampm;
            } catch (e) {
                return '<span class="badge badge-warning">Invalid Date</span>';
            }
        }

        function renderTable(users) {
            var tbody = '';
            if (users && users.length > 0) {
                users.forEach(function(user, index) {
                    tbody += '<tr>' +
                        '<td>' + (index + 1) + '</td>' +
                        '<td>' + (user.user_id || '-') + '</td>' +
                        '<td>' + (user.name || '-') + '</td>' +
                        '<td>' + (user.email || '-') + '</td>' +
                        '<td>' + (user.phone || '-') + '</td>' +
                        '<td>' + (user.zone || '-') + '</td>' +
                        '<td>' + formatDate(user.last_order_date) + '</td>' +
                        '</tr>';
                });
            } else {
                tbody = '<tr><td colspan="7" class="text-center text-muted">No users found</td></tr>';
            }
            $('#user-report-body').html(tbody);
        }

        function loadUserReport() {
            console.log('📡 Loading user report...', { zone: selectedZoneFilter, search: searchTerm });
            $('#data-table_processing').show();

            $.ajax({
                url: '{{ route('reports.userdata') }}',
                method: 'GET',
                data: {
                    zone_id: selectedZoneFilter,
                    search: searchTerm
                },
                success: function(response) {
                    console.log('📥 User report response:', response);
                    $('#data-table_processing').hide();

                    if (response.success || (response.data && Array.isArray(response.data))) {
                        var users = response.data || response;
                        renderTable(users);

                        // Update count
                        var count = users.length;
                        var total = response.total || count;
                        if (selectedZoneFilter || searchTerm) {
                            $('.total_count').text(count + ' / ' + total);
                        } else {
                            $('.total_count').text(total);
                        }

                        // Initialize or reinitialize DataTable
                        if (userReportDataTable) {
                            userReportDataTable.destroy();
                            userReportDataTable = null;
                        }

                        userReportDataTable = $('#userReportTable').DataTable({
                            destroy: true,
                            pageLength: 30,
                            lengthMenu: [[10, 25, 30, 50, 100, -1], [10, 25, 30, 50, 100, "All"]],
                            responsive: true,
                            searching: false, // Disable DataTable search since we have custom search
                            ordering: true,
                            order: [[6, 'desc']], // Order by Last Order Date descending
                            columnDefs: [
                                { orderable: false, targets: [0] } // S.NO column not sortable
                            ],
                            "language": {
                                "zeroRecords": "No users found",
                                "emptyTable": "No users available",
                                "processing": ""
                            }
                        });
                    } else {
                        console.error('❌ Invalid response format:', response);
                        $('#user-report-body').html('<tr><td colspan="7" class="text-center text-danger">Error loading user report</td></tr>');
                        $('.total_count').text('0 users');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error loading user report:', xhr);
                    $('#data-table_processing').hide();
                    $('#user-report-body').html('<tr><td colspan="7" class="text-center text-danger">Error loading user report. Please try again.</td></tr>');
                    $('.total_count').text('0 users');
                }
            });
        }

        // Search functionality with debounce
        var searchTimeout = null;
        $('#user_search_input').on('input', function() {
            var term = $(this).val().trim();
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                searchTerm = term;
                updateClearFiltersButton();
                if (term === '') {
                    $('#clear_search_btn').hide();
                } else {
                    $('#clear_search_btn').show();
                }
                loadUserReport();
            }, 500);
        });

        // Clear search button
        $('#clear_search_btn').on('click', function() {
            $('#user_search_input').val('');
            searchTerm = '';
            $(this).hide();
            updateClearFiltersButton();
            loadUserReport();
        });

        // Zone filter change
        $(document).on('change', '#zone_filter', function() {
            var val = $(this).val();
            selectedZoneFilter = val ? val.toString() : '';
            console.log('🔍 Zone filter changed:', selectedZoneFilter);
            updateClearFiltersButton();
            loadUserReport();
        });

        // Clear filters button
        $('#clear_filters_btn').on('click', function(e) {
            e.preventDefault();
            selectedZoneFilter = '';
            searchTerm = '';
            $('#user_search_input').val('');
            $('#clear_search_btn').hide();

            if (typeof $.fn.select2 !== 'undefined') {
                $('#zone_filter').val(null).trigger('change');
            } else {
                $('#zone_filter').val('').trigger('change');
            }

            updateClearFiltersButton();
            loadUserReport();
        });

        // Clear search on Escape key
        $('#user_search_input').on('keydown', function(e) {
            if (e.key === 'Escape') {
                $(this).val('');
                searchTerm = '';
                $('#clear_search_btn').hide();
                updateClearFiltersButton();
                loadUserReport();
            }
        });
    </script>
@endsection
