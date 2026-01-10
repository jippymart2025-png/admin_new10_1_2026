@extends('layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h2 class="text-dark mr-2">Merchant Settlement</h2>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">Merchant Settlement</li>
                </ol>
            </div>
        </div>

        <div class="container-fluid">
            {{-- FILTER BAR --}}
            <div class="card mb-3">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                    <form class="d-flex align-items-center flex-wrap" id="filter-form" method="GET"
                          action="{{ url('/settlements') }}">
                        <div class="d-flex align-items-center mr-3 mb-2 mb-md-0">
                            <input type="date" name="start_date" id="start_date" class="form-control mr-2"
                                   value="{{ request('start_date', '') }}" style="max-width: 180px;"
                                   placeholder="Start Date">
                            <span class="mr-2">to</span>
                            <input type="date" name="end_date" id="end_date" class="form-control mr-2"
                                   value="{{ request('end_date', '') }}" style="max-width: 180px;"
                                   placeholder="End Date">
                            <button type="submit" class="btn btn-primary" id="filter-btn">
                                <i class="mdi mdi-filter"></i> Filter
                            </button>
                            <a href="{{ url('/settlements') }}" class="btn btn-secondary ml-2">
                                <i class="mdi mdi-refresh"></i> Reset
                            </a>
                            <div class="select-box pl-3">
                                <select class="form-control zone_selector">
                                    <option value="" selected>{{trans('lang.select_zone')}}</option>
                                </select>
                            </div>
                            <div class="ml-2 px-2">
                                <input type="radio" id="pending" name="status" value="pending">
                                <label for="pending">Pending</label>

                                <input type="radio" id="settled" name="status" value="settled" style="margin-left: 15px;">
                                <label for="settled">Settled</label>
                            </div>
                        </div>
                    </form>

                    <div class="d-flex align-items-center mt-2 mt-md-0">
                        <button class="btn btn-info mr-2" id="import-payment-btn">
                            <i class="mdi mdi-upload"></i> Import Payment Sheet
                        </button>
                        <button class="btn btn-success mr-2" id="export-excel-btn">
                            <i class="mdi mdi-file-excel"></i> Export to Excel
                        </button>
                        <button class="btn btn-warning" id="bulk-payment-btn">
                            <i class="mdi mdi-cash-multiple"></i> Process Bulk Payment
                        </button>
                    </div>
                </div>
            </div>

            {{-- WEEK SUMMARY --}}
            <div id="week-summary" class="card mb-4" style="display: none;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h4 class="mb-1">
                                Settlement Week: <span id="summary-week-range"></span>
                            </h4>
                            <small class="text-muted">
                                Settlement Date: <span id="summary-settlement-date"></span>
                            </small>
                        </div>
                        <div class="d-flex text-center mt-3 mt-md-0">
                            <div class="px-4">
                                <h4 class="mb-0" id="summary-vendors">0</h4>
                                <small class="text-muted">Restaurants</small>
                            </div>
                            <div class="px-4">
                                <h4 class="mb-0" id="summary-orders">0</h4>
                                <small class="text-muted">Orders</small>
                            </div>
                            <div class="px-4 text-success">
                                <h4 class="mb-0">₹<span id="summary-settlement">0.00</span></h4>
                                <small>To Settle</small>
                            </div>
{{--                            <div class="px-4 text-info">--}}
{{--                                <h4 class="mb-0">₹<span id="summary-profit">0.00</span></h4>--}}
{{--                                <small>Jippy Profit</small>--}}
{{--                            </div>--}}
                            <div class="px-4" id="week-status-container">
                                <div class="d-flex align-items-center gap-2" id="week-status-controls">
                                    <select id="week-status" class="form-control form-control-sm" style="width:160px;">
                                        <option value="open">Open</option>
                                        <option value="under_review">Under Review</option>
                                        <option value="approved">Approved</option>
                                        <option value="processing">Processing</option>
                                        <option value="settled">Settled</option>
                                        <option value="failed">Failed</option>
                                        <option value="on_hold">On Hold</option>
                                    </select>

                                    <button class="btn btn-sm btn-success" id="save-week-status">
                                        <i class="mdi mdi-content-save"></i>
                                    </button>
                                </div>
                                <div id="week-settled-badge" style="display: none;">
                                    <button class="btn btn-sm btn-warning" disabled style="width:160px;">
                                        Settled
                                    </button>
                                </div>
                                <small class="text-primary">Settlement Status</small>
                            </div>
                            <button class="btn btn-sm btn-light toggle-week" title="Expand / Collapse Week">
                                <i class="mdi mdi-chevron-down"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RESTAURANTS LIST --}}
            <div id="vendors-container">
                <div class="text-center py-5">
                    <i class="mdi mdi-information-outline" style="font-size: 48px; color: #ccc;"></i>
                    <p class="text-muted mt-3">Select a date range and click Filter to view settlements</p>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        console.log('Settlement script loaded');

        // Make sure jQuery is loaded
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded!');
        }

        $(document).ready(function () {
            console.log('Document ready');
            console.log('jQuery version:', $.fn.jquery);

            let currentWeekId = null;
            let allWeeks = @json($weeks);

            // Load zones from SQL
            var loadZonesPromise = new Promise(function(resolve){
                console.log('🔄 Loading zones from SQL for merchant settlement...');
                $.ajax({
                    url: '{{ route("zone.data") }}',
                    method: 'GET',
                    success: function(response) {
                        console.log('📊 Zones API response:', response);
                        if (response.data && response.data.length > 0) {
                            response.data.forEach(function(zone) {
                                console.log('Zone found:', zone.name, 'ID:', zone.id);
                                // Add zone to selector
                                $('.zone_selector').append($("<option></option>")
                                    .attr("value", zone.id)
                                    .text(zone.name));
                            });
                            console.log('✅ Zones loaded from SQL (' + response.data.length + ' zones)');
                        } else {
                            console.warn('⚠️ No zones found in database');
                        }
                        // Enable the zone selector after zones are loaded
                        $('.zone_selector').prop('disabled', false);
                        resolve();
                    },
                    error: function(error) {
                        console.error('❌ Error loading zones from SQL:', error);
                        $('.zone_selector').prop('disabled', false);
                        resolve();
                    }
                });
            });

            console.log('All weeks:', allWeeks);

            // Test if date inputs are found
            console.log('Start date input found:', $('#start_date').length > 0);
            console.log('End date input found:', $('#end_date').length > 0);
            console.log('Filter button found:', $('#filter-btn').length > 0);

            // Filter submit - Fetch from database (progressive enhancement: without JS it will still submit GET and update URL)
            $('#filter-form').on('submit', function (e) {
                e.preventDefault();
                console.log('=== FILTER BUTTON CLICKED ===');

                const startDateInput = $('#start_date');
                const endDateInput = $('#end_date');

                console.log('Start date input element:', startDateInput);
                console.log('End date input element:', endDateInput);

                const startDate = startDateInput.val();
                const endDate = endDateInput.val();

                console.log('Start Date value:', startDate);
                console.log('End Date value:', endDate);
                console.log('Start Date type:', typeof startDate);
                console.log('End Date type:', typeof endDate);
                console.log('Start Date length:', startDate ? startDate.length : 0);
                console.log('End Date length:', endDate ? endDate.length : 0);

                // More detailed validation
                if (!startDate || startDate.trim() === '') {
                    console.error('Start date is empty!');
                    alert('Please select a start date');
                    startDateInput.focus();
                    return;
                }

                if (!endDate || endDate.trim() === '') {
                    console.error('End date is empty!');
                    alert('Please select an end date');
                    endDateInput.focus();
                    return;
                }

                console.log('Dates validated successfully');
                console.log('Proceeding with filter - Start:', startDate, 'End:', endDate);

                // Update URL immediately (even if API returns 0 vendors / errors)
                try {
                    const params = new URLSearchParams(window.location.search);
                    params.set('start_date', startDate);
                    params.set('end_date', endDate);
                    const newUrl = `${window.location.pathname}?${params.toString()}`;
                    window.history.replaceState({}, '', newUrl);
                    console.log('✓ URL updated to:', newUrl);
                } catch (err) {
                    console.warn('Could not update URL params:', err);
                }

                // Show loading indicator
                $('#vendors-container').html('<div class="text-center py-5"><i class="fa fa-spinner fa-spin fa-3x"></i><p class="mt-3">Loading data...</p></div>');
                $('#week-summary').hide();

                // Get selected zone and status
                const zoneId = $('.zone_selector').val();
                const status = $('input[name="status"]:checked').val();

                // Fetch summary by date range from server
                const summaryUrl = '/settlements/summary-by-date';
                console.log('Fetching summary from:', summaryUrl);
                console.log('With data:', {start_date: startDate, end_date: endDate, zone_id: zoneId, status: status});

                $.ajax({
                    url: summaryUrl,
                    method: 'GET',
                    data: {
                        start_date: startDate,
                        end_date: endDate,
                        zone_id: zoneId || '',
                        status: status || ''
                    },
                    beforeSend: function () {
                        console.log('Sending summary request...');
                    },
                    success: function (summaryData) {
                        console.log('✓ Summary data received:', summaryData);
                        currentWeekId = null;

                        function getNextFriday(dateStr) {
                            const date = new Date(dateStr);

                            // JS: Sunday = 0, Monday = 1, ..., Friday = 5
                            const day = date.getDay();

                            // Days to add to reach next Friday
                            let daysToAdd = (5 - day + 7) % 7;

                            // If already Friday or earlier in week, move to NEXT Friday
                            if (daysToAdd === 0) {
                                daysToAdd = 7;
                            }

                            date.setDate(date.getDate() + daysToAdd);

                            return date.toISOString().split('T')[0];
                        }

                        // Update summary
                        $('#summary-week-range').text(startDate + ' - ' + endDate);
                        const settlementDate = getNextFriday(endDate);
                        $('#summary-settlement-date').text(settlementDate);
                        $('#summary-vendors').text(summaryData.vendors || 0);
                        $('#summary-orders').text(summaryData.orders || 0);
                        // Initialize "To Settle" to 0 - it will be updated as orders are displayed
                        // The total will be the sum of all individual order settlements (calculated with correct promo logic)
                        $('#summary-settlement').text('0.00');
                        $('#summary-profit').text(parseFloat(summaryData.total_profit || 0).toLocaleString('en-IN', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));

                        // Update week status UI
                        updateWeekStatusUI(summaryData.week_status || 'open');

                        $('#week-summary').slideDown();

                        // Fetch vendors by date range from server
                        const vendorsUrl = '/settlements/vendors-by-date';
                        console.log('Fetching vendors from:', vendorsUrl);

                        $.ajax({
                            url: vendorsUrl,
                            method: 'GET',
                            data: {
                                start_date: startDate,
                                end_date: endDate,
                                zone_id: zoneId || '',
                                status: status || ''
                            },
                            beforeSend: function () {
                                console.log('Sending vendors request...');
                            },
                            success: function (vendors) {
                                console.log('✓ Vendors data received');
                                console.log('Number of vendors:', vendors.length);
                                console.log('Vendors:', vendors);
                                currentWeekId = null;

                                if (!vendors || vendors.length === 0) {
                                    $('#vendors-container').html('<div class="alert alert-info text-center">No restaurants found for the selected date range</div>');
                                    return;
                                }

                                displayVendors(vendors, startDate, endDate);

                                console.log('✓ Filter completed successfully');
                            },
                            error: function (xhr, status, error) {
                                console.error('✗ Error loading vendors');
                                console.error('Status:', status);
                                console.error('Error:', error);
                                console.error('Response:', xhr.responseText);
                                console.error('Status code:', xhr.status);

                                let errorMsg = 'Error loading restaurants: ' + error;
                                try {
                                    const errorData = JSON.parse(xhr.responseText);
                                    if (errorData.error) {
                                        errorMsg = errorData.error;
                                    }
                                } catch (e) {
                                    console.error('Could not parse error response');
                                }

                                $('#vendors-container').html('<div class="alert alert-danger">' + errorMsg + '<br>Status: ' + xhr.status + '<br>Please check console for details</div>');
                            }
                        });
                    },
                    error: function (xhr, status, error) {
                        console.error('✗ Error loading summary');
                        console.error('Status:', status);
                        console.error('Error:', error);
                        console.error('Response:', xhr.responseText);
                        console.error('Status code:', xhr.status);

                        let errorMsg = 'Error loading summary data: ' + error;
                        try {
                            const errorData = JSON.parse(xhr.responseText);
                            if (errorData.error) {
                                errorMsg = errorData.error;
                            }
                        } catch (e) {
                            console.error('Could not parse error response');
                        }

                        alert(errorMsg + '\nStatus: ' + xhr.status + '\nPlease check console for details');
                        $('#vendors-container').html('<div class="alert alert-danger">' + errorMsg + '</div>');
                    }
                });
            });

            // Load week data
            function loadWeekData(week) {
                console.log('Loading week data:', week);
                currentWeekId = week.id;
                const weekStart = week.week_start_date;
                const weekEnd = week.week_end_date;
                const settlementDate = week.settlement_date;

                // Load summary
                loadWeekSummary(week.id, weekStart, weekEnd, settlementDate);

                // Load vendors
                loadVendors(week.id);
            }

            // Auto-load first week (most recent or filtered) on page load
            @if(count($weeks) > 0)
            @if(request('start_date') && request('end_date'))
            // Filter by date range from URL - fetch from server
            const startDate = '{{ request('start_date') }}';
            const endDate = '{{ request('end_date') }}';

            console.log('Loading from URL params - Start:', startDate, 'End:', endDate);

            $('#start_date').val(startDate);
            $('#end_date').val(endDate);

            // Show loading
            $('#vendors-container').html('<div class="text-center py-5"><i class="fa fa-spinner fa-spin fa-3x"></i><p class="mt-3">Loading data...</p></div>');

            // Get selected zone and status
            const zoneId = $('.zone_selector').val();
            const status = $('input[name="status"]:checked').val();

            // Fetch summary by date range from server
            $.get('/settlements/summary-by-date', {
                start_date: startDate,
                end_date: endDate,
                zone_id: zoneId || '',
                status: status || ''
            }, function (summaryData) {
                console.log('Initial summary loaded:', summaryData);

                $('#summary-week-range').text(startDate + ' - ' + endDate);
                $('#summary-settlement-date').text('Custom Date Range');
                $('#summary-vendors').text(summaryData.vendors || 0);
                $('#summary-orders').text(summaryData.orders || 0);
                // Initialize "To Settle" to 0 - it will be updated as orders are displayed
                // The total will be the sum of all individual order settlements (calculated with correct promo logic)
                $('#summary-settlement').text('0.00');
                $('#summary-profit').text(parseFloat(summaryData.total_profit || 0).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

                // Update week status UI
                updateWeekStatusUI(summaryData.week_status || 'open');

                $('#week-summary').show();

                // Fetch vendors by date range from server
                $.get('/settlements/vendors-by-date', {
                    start_date: startDate,
                    end_date: endDate,
                    zone_id: zoneId || '',
                    status: status || ''
                }, function (vendors) {
                    console.log('Initial vendors loaded:', vendors.length, 'vendors');

                    if (vendors.length === 0) {
                        $('#vendors-container').html('<div class="alert alert-info text-center">No restaurants found for the selected date range</div>');
                        return;
                    }

                    displayVendors(vendors, startDate, endDate);
                }).fail(function (xhr) {
                    console.error('Error loading initial vendors:', xhr.responseText);
                    $('#vendors-container').html('<div class="alert alert-danger">Error loading restaurants</div>');
                });
            }).fail(function (xhr) {
                console.error('Error loading initial summary:', xhr.responseText);
                // Fallback to first week if date filter fails
                const firstWeek = @json($weeks->first());
                console.log('Falling back to first week:', firstWeek);
                loadWeekData(firstWeek);
            });
            @else
            const firstWeek = @json($weeks->first());
            console.log('Loading first week:', firstWeek);
            if (firstWeek) {
                loadWeekData(firstWeek);
            } else {
                console.log('No weeks available to load');
            }
            @endif
            @else
            console.log('No weeks available in database');
            @endif

            function loadWeekSummary(weekId, weekStart, weekEnd, settlementDate) {
                console.log('Loading week summary:', weekId);

                $.get(`/settlements/week/${weekId}/summary`, function (data) {
                    console.log('Week summary loaded:', data);

                    $('#summary-week-range').text(weekStart + ' - ' + weekEnd);
                    $('#summary-settlement-date').text(settlementDate);
                    $('#summary-vendors').text(data.vendors || 0);
                    $('#summary-orders').text(data.orders || 0);
                    // Initialize "To Settle" to 0 - it will be updated as orders are displayed
                    // The total will be the sum of all individual order settlements (calculated with correct promo logic)
                    $('#summary-settlement').text('0.00');
                    $('#summary-profit').text(parseFloat(data.total_profit || 0).toLocaleString('en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));

                    // Update week status UI (from week data if available)
                    if (data.week && data.week.status) {
                        updateWeekStatusUI(data.week.status);
                    } else {
                        updateWeekStatusUI('open');
                    }

                    $('#week-summary').slideDown();
                }).fail(function (xhr) {
                    console.error('Error loading week summary:', xhr.responseText);
                    alert('Error loading week summary');
                });
            }

            // Display vendors in the UI
            function displayVendors(vendors, startDate = null, endDate = null, weekId = null) {
                console.log('Displaying', vendors.length, 'vendors');
                console.log('Parameters - startDate:', startDate, 'endDate:', endDate, 'weekId:', weekId);

                // Track total settlement across all vendors (sum of all individual order settlements)
                let totalSettlementAllVendors = 0;

                let html = '';
                vendors.forEach((v, index) => {
                    const vendorName = v.vendor_name || 'NA';
                    const initials = vendorName
                        .split(' ')
                        .map(n => n[0])
                        .join('')
                        .substring(0, 2)
                        .toUpperCase();

                    // Get saved settlement status or default to Pending
                    const savedStatus = (v.saved_settlement && v.saved_settlement.payment_status) ? v.saved_settlement.payment_status : 'Pending';
                    const statusClass = savedStatus === 'Settled' ? 'badge-primary' : 'badge-warning';
                    const statusBadge = `<span class="badge ${statusClass}">${savedStatus}</span>`;
                    const savedTxnId = (v.saved_settlement && v.saved_settlement.transaction_id) ? v.saved_settlement.transaction_id : '';
                    const savedComments = (v.saved_settlement && v.saved_settlement.payment_comments) ? v.saved_settlement.payment_comments : '';

                    html += `
                <div class="card mb-3 vendor-card" data-vendor="${v.vendor_id}" data-week="${weekId || ''}" data-start-date="${startDate || ''}" data-end-date="${endDate || ''}" data-gst="${v.gst || 0}" data-customer-paid="${v.customer_paid || 0}" data-plan-name="${v.plan_name || 'Commission Plan'}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                     style="width: 50px; height: 50px; font-weight: bold; margin-right: 15px;">
                                    ${initials}
                                </div>
                          <div class="d-flex flex-column">
    <div class="d-flex align-items-center flex-wrap">
        <h4 class="mb-0 mr-2">${vendorName}</h4>
        <h3 class="font-weight-bold mb-0 mr-3" style="font-size: 14px;">(${v.phone || '-'})</h3>
        <span class="badge badge-light commission-badge mr-3" style="font-size:15px; padding:4px 10px;">
            ${v.orders_count} Orders · ${v.commission}%
        </span>
        <span class="badge badge-info mr-3" style="font-size:15px; padding:4px 10px;">${v.zone || 'N/A'}</span>
        <span class="badge ${v.gst == 1 ? 'badge-success' : 'badge-danger'} mr-3" style="font-size:10px; padding:4px 10px;">
            GST - ${v.gst == 1 ? 'Accepted' : 'Unaccepted'}
        </span>
        <div class="mr-3">
            <small class="text-primary d-block">MERCHANT PRICE</small>
            <h5 class="mb-0">₹${parseFloat(v.merchant_price || 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}</h5>
        </div>
        <div class="mr-3 text-info">
            <small class="d-block">PROMOTION PRICE</small>
            <h5 class="mb-0">₹${parseFloat(v.promotion_price || 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}</h5>
        </div>
           <div class="mr-3 text-info">
            <small class="d-block">TOTAL PRICE</small>
            <h5 class="mb-0">₹${parseFloat(v.total_price || 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}</h5>
        </div>
        <div class="mr-3 text-success">
            <small class="d-block">SETTLEMENT</small>
            <h5 class="mb-0">₹${parseFloat(v.settlement_amount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}</h5>
        </div>
    </div>
</div>
                            </div>
                           <div class="text-right">
                            ${statusBadge}
                              <button class="btn btn-sm btn-light toggle-orders mt-2"
                                    title="View Orders">
                                   <i class="mdi mdi-chevron-down"></i>
                                    </button>
                                </div>
                            </div>

                        <div class="orders-container mt-3" style="display: none;"></div>
                         <div class="orders-pagination text-center mt-2"></div>

                        <div class="settlement-form mt-3" style="display: none;">
                            <hr>
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Transaction ID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control txn-id" placeholder="Enter Transaction ID" value="${savedTxnId}" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-control settlement-status" required>
                                        <option value="">Select Status</option>
                                        <option value="Pending" ${savedStatus === 'Pending' ? 'selected' : ''}>Pending</option>
                                        <option value="Settled" ${savedStatus === 'Settled' ? 'selected' : ''}>Settled</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Comments (if pending)</label>
                                    <input type="text" class="form-control comments" placeholder="Enter comments" value="${savedComments}">
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-success save-settlement" data-vendor="${v.vendor_id}">
                                        <i class="mdi mdi-content-save"></i> Save
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
                });

                // <div class="col-md-3">
                //     <small class="text-muted d-block">CUSTOMER PAID</small>
                //     <h5 class="mb-0">₹${parseFloat(v.customer_paid || 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}</h5>
                // </div>
                // <div class="text-info">
                //     <small class="d-block">JIPPY PROFIT</small>
                //     <h5 class="mb-0">₹${parseFloat(v.jippy_profit || 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}</h5>
                // </div>

                $('#vendors-container').html(html);
                console.log('✓ Vendors displayed successfully');
                
                // Auto-expand all vendor cards after displaying
                setTimeout(function() {
                    $('.vendor-card').each(function(index) {
                        const vendorCard = $(this);
                        const toggleBtn = vendorCard.find('.toggle-orders');
                        const ordersBox = vendorCard.find('.orders-container');
                        const formBox = vendorCard.find('.settlement-form');
                        
                        // Stagger the expansion slightly to avoid overwhelming the server
                        setTimeout(function() {
                            // Only expand if not already loaded
                            if (!ordersBox.hasClass('loaded')) {
                                // Trigger click to load orders and show form
                                toggleBtn.trigger('click');
                            } else {
                                // If already loaded, just show them
                                ordersBox.slideDown();
                                formBox.slideDown();
                                toggleBtn.html('<i class="mdi mdi-chevron-up"></i>');
                            }
                        }, index * 50); // 50ms delay between each vendor expansion
                    });
                }, 100); // Small delay to ensure DOM is ready
            }

            function loadVendors(weekId) {
                console.log('Loading vendors for week:', weekId);

                $.get(`/settlements/week/${weekId}/vendors`, function (vendors) {
                    console.log('Vendors loaded:', vendors.length, 'vendors');

                    if (vendors.length === 0) {
                        $('#vendors-container').html('<div class="alert alert-info text-center">No restaurants found for this week</div>');
                        return;
                    }

                    displayVendors(vendors, null, null, weekId);
                }).fail(function (xhr) {
                    console.error('Error loading vendors:', xhr.responseText);
                    $('#vendors-container').html('<div class="alert alert-danger">Error loading restaurants</div>');
                });
            }

            // Toggle orders
            $(document).on('click', '.toggle-orders', function () {
                console.log('Toggle orders clicked');

                const vendorCard = $(this).closest('.vendor-card');
                const vendorId = vendorCard.data('vendor');
                const weekId = vendorCard.data('week');
                const startDate = vendorCard.data('start-date');
                const endDate = vendorCard.data('end-date');
                const ordersBox = vendorCard.find('.orders-container');
                const formBox = vendorCard.find('.settlement-form');
                const btn = $(this);

                console.log('Vendor:', vendorId, 'Week:', weekId, 'Dates:', startDate, endDate);

                if (ordersBox.hasClass('loaded')) {
                    ordersBox.slideToggle();
                    formBox.slideToggle();
                    const isVisible = ordersBox.is(':visible');
                    btn.html(isVisible ? '<i class="mdi mdi-chevron-up"></i>' : '<i class="mdi mdi-chevron-down"></i>');
                    return;
                }

                // Load orders - use week_id if available, otherwise use date range
                let ordersUrl = `/settlements/vendor/${vendorId}/orders`;
                if (weekId && currentWeekId !== null) {
                    ordersUrl += `?week_id=${weekId}`;
                } else if (startDate && endDate) {
                    ordersUrl += `?start_date=${startDate}&end_date=${endDate}`;
                }


                console.log('Loading orders from:', ordersUrl);

                ordersBox.html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading orders...</div>').show();
                // Show form immediately when toggle is clicked
                formBox.slideDown();

                $.get(ordersUrl, function (orders) {
                    console.log('Orders loaded:', orders.length, 'orders');

                    if (orders.length === 0) {
                        ordersBox.html('<div class="alert alert-info">No orders found</div>').addClass('loaded');
                        formBox.slideDown();
                        return;
                    }

                    function formatOrderItems(productsJson) {
                        if (!productsJson) return 'N/A';

                        let items;
                        try {
                            items = typeof productsJson === 'string'
                                ? JSON.parse(productsJson)
                                : productsJson;
                        } catch (e) {
                            console.error('Invalid products JSON', productsJson);
                            return 'N/A';
                        }

                        if (!Array.isArray(items)) return 'N/A';

                        return items.map(item => {
                            const name = item.name || 'Item';
                            const qty = item.quantity || 1;
                            // const price = parseFloat(item || 0);

                            return `${name} x${qty} `;
                        }).join(' + ');
                    }
                    // (₹${price.toLocaleString('en-IN')})
                    function calculateItemTotal(productsJson) {
                        if (!productsJson) return 0;

                        let items;
                        try {
                            items = typeof productsJson === 'string'
                                ? JSON.parse(productsJson)
                                : productsJson;
                        } catch (e) {
                            console.error('Invalid products JSON', productsJson);
                            return 0;
                        }

                        if (!Array.isArray(items)) return 0;

                        return items.reduce((sum, item) => {
                            // Use merchant_price from vendor_products (enriched by backend)
                            // Fallback to price if merchant_price not available
                            const price = parseFloat(item.merchant_price ?? item.price ?? 0);
                            const qty = parseInt(item.quantity || 1);
                            return sum + (price * qty);
                        }, 0);
                    }

                    function calculatePromotionPriceTotal(productsJson) {
                        if (!productsJson) return null;

                        let items;
                        try {
                            items = typeof productsJson === 'string'
                                ? JSON.parse(productsJson)
                                : productsJson;
                        } catch (e) {
                            console.error('Invalid products JSON', productsJson);
                            return null;
                        }

                        if (!Array.isArray(items)) return null;

                        let hasPromotion = false;
                        let promotionTotal = 0;

                        items.forEach(item => {
                            // SETTLEMENT RULE:
                            // If promotions.promo = 1 (restaurant accepts promotion) → has_promotion = true, promotion_price = special_price
                            // If promotions.promo = 0 or no promotion → has_promotion = false, promotion_price = null
                            //
                            // Backend enriches each item with:
                            // - has_promotion: true if promo = 1 and promotion is active, else false
                            // - promotion_price: special_price value if promo = 1, else null
                            if (item.has_promotion === true && item.promotion_price !== null && item.promotion_price !== undefined && item.promotion_price > 0) {
                                hasPromotion = true;
                                // promotion_price is the special_price from promotions table
                                const promotionPrice = parseFloat(item.promotion_price ?? 0);
                                const qty = parseInt(item.quantity || 1);
                                promotionTotal += (promotionPrice * qty);
                            }
                        });

                        // Return promotion total (sum of special_price * quantity) if restaurant accepts promotion (promo = 1)
                        // Otherwise return null to display '-' in PROMOTION PRICE column
                        return hasPromotion ? promotionTotal : null;
                    }
                    function formatOrderDate(createdAt) {
                        if (!createdAt) return '-';

                        // If numeric timestamp
                        if (!isNaN(createdAt)) {
                            let ts = createdAt.toString().length > 10
                                ? parseInt(createdAt) / 1000
                                : parseInt(createdAt);
                            return new Date(ts * 1000).toLocaleDateString('en-IN');
                        }

                        // ISO / normal date string
                        let d = new Date(createdAt);
                        if (!isNaN(d.getTime())) {
                            return d.toLocaleDateString('en-IN');
                        }

                        return createdAt; // fallback
                    }

                    // function customRound(value) {
                    //     const decimal = value % 1;
                    //
                    //     if (decimal === 0.5) {
                    //         return Math.floor(value);
                    //     }
                    //
                    //     return Math.round(value);
                    // }


                    let html = `
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                             <th>DATE</th>
                            <th>ORDER ID</th>
                            <th>ITEMS</th>
                            <th>MERCHANT PRICE</th>
<!--                            <th>CUSTOMER PAID</th>-->
                            <th>PROMOTION PRICE</th>
                            <th>JIPPY %</th>
                            <th>GST %</th>
                            <th>SETTLEMENT AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>`;

                    const vendorCard = $(`.vendor-card[data-vendor="${vendorId}"]`);
                    const commissionText = vendorCard.find('.commission-badge').text();
                    const commissionMatch = commissionText.match(/(\d+(?:\.\d+)?)%/);
                    const commissionPercent = commissionMatch ? commissionMatch[1] : '0';

                    // Get GST status from vendor card data attribute
                    const isGstAccepted = parseInt(vendorCard.data('gst') || 0) === 1;
                    
                    // Get plan name from vendor card data attribute
                    // COMMISSION PLAN RULE: If plan_name = "Commission Plan" (case-insensitive) → DO NOT deduct commission
                    // Handles all case variations: "Commission Plan", "COMMISSION PLAN", "commission plan", etc.
                    const planName = vendorCard.data('plan-name') || 'Commission Plan';
                    const isCommissionPlan = planName && planName.toString().toLowerCase().trim() === 'commission plan';

                    // Track totals for all orders (to update header)
                    let totalPromotionPriceAllOrders = 0;
                    let totalMerchantPriceAllOrders = 0;
                    let totalPriceAllOrders = 0; // merchant + promotion
                    let totalSettlementAllOrders = 0; // Sum of all individual order settlements

                    orders.forEach(o => {

                        // ✅ Calculate merchant price and promotion price separately
                        // GOLDEN RULE: restaurant_orders.promotion = 1 is the source of truth
                        // If order.promotion = 1 → treat ALL items as promotional
                        const orderHasPromotion = parseInt(o.promotion || 0) === 1;
                        
                        // Separate items into promotional (has_promotion = true) and non-promotional
                        let merchantPriceTotal = 0;  // Non-promotional items ONLY
                        let promotionPriceTotal = 0; // Promotional items ONLY
                        let hasAnyPromotion = false;
                        let hasAcceptedPromo = false; // Track if any item has promotions.promo = 1

                        let items;
                        try {
                            items = typeof o.products === 'string' ? JSON.parse(o.products) : o.products;
                        } catch (e) {
                            items = [];
                        }

                        if (Array.isArray(items)) {
                            items.forEach(item => {
                                const qty = parseInt(item.quantity || 1);
                                // MERCHANT PRICE column should ALWAYS show base merchant_price from vendor_products
                                // This is set by backend in item.merchant_price (from vendor_products table)
                                // Do NOT fallback to item.price (which might be promotional price)
                                const merchantPrice = parseFloat(item.merchant_price ?? 0);

                                // FINAL RULE: Use promo price ONLY when BOTH are true:
                                // promotions.promo = 1 AND restaurant_orders.promotion = 1
                                // Use the price from order JSON (frozen at order time)
                                // Otherwise, always use merchant_price
                                
                                let isPromotional = false;
                                let usePromoPrice = false;
                                
                                if (orderHasPromotion && item.has_promotion === true && item.promo_accepted === true) {
                                    // BOTH conditions true: promotions.promo = 1 AND order.promotion = 1
                                    // Use the price from order JSON (item.promotion_price which comes from item.price in JSON)
                                    isPromotional = true;
                                    usePromoPrice = true;
                                    hasAcceptedPromo = true; // Track that restaurant accepted promo
                                } else {
                                    // In all other cases, use merchant_price
                                    isPromotional = false;
                                    usePromoPrice = false;
                                }
                                
                                // MERCHANT PRICE column: ALWAYS add base merchant_price (for display)
                                // This shows the base price from vendor_products table, regardless of whether promotion was used
                                merchantPriceTotal += (merchantPrice * qty);
                                
                                if (isPromotional && usePromoPrice) {
                                    // Promotional item - use price from order JSON (frozen at order time)
                                    // This goes to PROMOTION PRICE column
                                    hasAnyPromotion = true;
                                    const promotionPrice = parseFloat(item.promotion_price ?? item.price ?? 0);
                                    promotionPriceTotal += (promotionPrice * qty);
                                }
                            });
                        }

                        // For display: MERCHANT PRICE column should ALWAYS show only merchant_price from vendor_products
                        // This is the base price, regardless of whether promotion was used or not
                        const merchantPriceDisplay = merchantPriceTotal; // Show only merchant_price (NOT merchant + promotion)
                        
                        // Accumulate totals for header display
                        totalMerchantPriceAllOrders += merchantPriceTotal;

                        // ✅ Settlement calculation with GOLDEN RULE: Commission NEVER applies to promotional items
                        // STEP 1: Separate bases for settlement calculation
                        // merchantBase: items that will use merchant_price for settlement (non-promotional OR promo not accepted)
                        // promotionBase: items that will use promo price for settlement (promotional AND promo accepted)
                        // Note: merchantPriceTotal includes ALL items' base merchant_price (for MERCHANT PRICE column display)
                        //       But for settlement, we need to separate based on promotion rules
                        let merchantBase = 0;  // For settlement: non-promotional items
                        let promotionBase = 0;  // For settlement: promotional items (promo = 1 AND order.promotion = 1)
                        
                        // Recalculate bases for settlement (separate from display totals)
                        items.forEach(item => {
                            const qty = parseInt(item.quantity || 1);
                            const merchantPrice = parseFloat(item.merchant_price ?? item.price ?? 0);
                            
                            if (orderHasPromotion && item.has_promotion === true && item.promo_accepted === true) {
                                // Promotional item - use promo price for settlement
                                const promotionPrice = parseFloat(item.promotion_price ?? item.price ?? 0);
                                promotionBase += (promotionPrice * qty);
                            } else {
                                // Non-promotional item - use merchant_price for settlement
                                merchantBase += (merchantPrice * qty);
                            }
                        });
                        
                        const totalBase = merchantBase + promotionBase;

                        // ✅ GST % display
                        const gstPercent = isGstAccepted ? 5 : 0;

                        // ✅ Settlement calculation
                        // FINAL RULE: Commission logic based on promotions.promo when order.promotion = 1
                        // If order.promotion = 1 AND promotions.promo = 1 → NO commission (use special_price, promotionBase > 0)
                        // If order.promotion = 1 AND promotions.promo = 0 → APPLY commission (use merchant_price, promotionBase = 0)
                        // If order.promotion = 0 → APPLY commission (use merchant_price)
                        let settlementAmount = totalBase;

                        // Check if plan exists: if commissionPercent > 0, then plan exists
                        const comm = parseFloat(commissionPercent) || 0;
                        const hasPlan = comm > 0;

                        // Determine if commission should be applied
                        // If orderHasPromotion and promotionBase > 0 → promotions.promo = 1 (NO commission)
                        // If orderHasPromotion and promotionBase = 0 → promotions.promo = 0 (APPLY commission)
                        // If !orderHasPromotion → APPLY commission
                        const shouldApplyCommission = !orderHasPromotion || promotionBase === 0;

                        // Apply commission if needed (skip if Commission Plan)
                        // COMMISSION PLAN RULE: If plan_name = "Commission Plan" → DO NOT deduct commission
                        if (hasPlan && comm > 0 && shouldApplyCommission && merchantBase > 0 && !isCommissionPlan) {
                            // Apply commission to merchantBase (non-promotional items)
                            settlementAmount -= merchantBase * (comm / 100);
                        }

                        // STEP 2: GST check (SECOND) - deduct 5% if gst = 1, else 0%
                        // GST applies to everything (both promotional and non-promotional items)
                        if (isGstAccepted) {
                            settlementAmount -= totalBase * 0.05;
                        }

                        // STEP 3: Round final value
                        settlementAmount = Math.round(settlementAmount);
                        
                        // Accumulate settlement for total
                        totalSettlementAllOrders += settlementAmount;

                        // Display promotion price (special_price) or merchant_price with light red background
                        // If order.promotion = 1 AND promotions.promo = 1 → show special_price with green background
                        // If order.promotion = 1 AND promotions.promo = 0 → show merchant_price with light red background (but skip commission)
                        // If order.promotion = 0 AND promotions.promo = 1 → show special_price with green background
                        // If order.promotion = 0 AND promotions.promo = 0 → show merchant_price with light red background
                        let promotionPriceDisplay;
                        let promotionPriceCellClass = '';
                        if (hasAnyPromotion && promotionPriceTotal > 0) {
                            // Has promotion with special_price (order.promotion = 1 AND promo = 1, OR order.promotion = 0 AND promo = 1)
                            // Show special_price total with green background
                            promotionPriceDisplay = `₹${promotionPriceTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;
                            promotionPriceCellClass = 'style="background-color: #99ffbb;"'; // Light green
                            // Add to total for header display
                            totalPromotionPriceAllOrders += promotionPriceTotal;
                        } else if (orderHasPromotion && promotionPriceTotal === 0) {
                            // order.promotion = 1 BUT promotions.promo = 0 → show merchant_price with light red background (but skip commission)
                            promotionPriceDisplay = `₹${merchantPriceDisplay.toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;
                            promotionPriceCellClass = 'style="background-color: #ffebee;"'; // Light red background
                        } else {
                            // No promotion or promo = 0 → show merchant_price total with light red background
                            promotionPriceDisplay = `₹${merchantPriceDisplay.toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;
                            promotionPriceCellClass = 'style="background-color: #ffebee;"'; // Light red background
                        }
                        
                        // TOTAL PRICE = Sum of what's displayed in PROMOTION PRICE column
                        // This matches what's shown in the PROMOTION PRICE column for each order
                        // If promotional (hasAnyPromotion && promotionPriceTotal > 0): use promotionPriceTotal
                        // Else: use merchantPriceDisplay (merchant_price)
                        const promotionPriceColumnValue = (hasAnyPromotion && promotionPriceTotal > 0) 
                            ? promotionPriceTotal 
                            : merchantPriceDisplay;
                        totalPriceAllOrders += promotionPriceColumnValue;

                        html += `
        <tr>
            <td>${formatOrderDate(o.createdAt)}</td>
            <td>${o.id}</td>
            <td>${formatOrderItems(o.products)}</td>

            <!-- Merchant price from DB (total of all items) -->
            <td>₹${merchantPriceDisplay.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</td>

            <!-- Promotion price: special_price if promo=1, merchant_price (light red) if promo=0 or no promotion -->
            <td ${promotionPriceCellClass}>${promotionPriceDisplay}</td>

            <!-- JIPPY %: Show 0% if:
                 - order.promotion = 1 AND promotions.promo = 1 (promotionBase > 0), OR
                 - plan_name = "Commission Plan" (commission handled elsewhere, not per-order deduction)
                 Otherwise show commission % -->
            <td>${(orderHasPromotion && promotionBase > 0) || isCommissionPlan ? '0%' : commissionPercent + '%'}</td>

            <!-- GST % -->
            <td>${gstPercent}%</td>

            <!-- Settlement after commission and GST deduction -->
            <td class="text-success">
                ₹${settlementAmount.toLocaleString('en-IN', { minimumFractionDigits: 2 })}
            </td>
        </tr>`;
                    });

                    // <td>₹${customerPaid.toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>

                    html += '</tbody></table></div>';

                    // Update header with total promotion price from all orders (sum of all promotion prices in the column)
                    const promotionPriceHeader = vendorCard.find('.text-info:has(small:contains("PROMOTION PRICE")) h5');
                    if (promotionPriceHeader.length) {
                        promotionPriceHeader.text(`₹${totalPromotionPriceAllOrders.toLocaleString('en-IN', {minimumFractionDigits: 2})}`);
                    }
                    
                    // Update header with total price (merchant + promotion) from all orders
                    const totalPriceHeader = vendorCard.find('.text-info:has(small:contains("TOTAL PRICE")) h5');
                    if (totalPriceHeader.length) {
                        totalPriceHeader.text(`₹${totalPriceAllOrders.toLocaleString('en-IN', {minimumFractionDigits: 2})}`);
                    }
                    
                    // Update header with total settlement (sum of all individual order settlements)
                    // This ensures the total matches the sum of individual rows
                    const settlementHeader = vendorCard.find('.text-success:has(small:contains("SETTLEMENT")) h5');
                    if (settlementHeader.length) {
                        settlementHeader.text(`₹${totalSettlementAllOrders.toLocaleString('en-IN', {minimumFractionDigits: 2})}`);
                    }
                    
                    // Update global "To Settle" header (sum of all vendors' settlements)
                    // Get current total from header, subtract old vendor total, add new vendor total
                    const currentToSettleText = $('#summary-settlement').text().replace(/[₹,]/g, '');
                    const currentToSettle = parseFloat(currentToSettleText) || 0;
                    
                    // Get old vendor settlement from vendor card (if exists)
                    const oldVendorSettlementText = vendorCard.data('vendor-settlement-total') || '0';
                    const oldVendorSettlement = parseFloat(oldVendorSettlementText) || 0;
                    
                    // Calculate new total: current - old + new
                    const newToSettle = currentToSettle - oldVendorSettlement + totalSettlementAllOrders;
                    
                    // Update "To Settle" header
                    $('#summary-settlement').text(newToSettle.toLocaleString('en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                    
                    // Store vendor settlement total for future updates
                    vendorCard.data('vendor-settlement-total', totalSettlementAllOrders);
                    
                    ordersBox.html(html).addClass('loaded').slideDown();
                    formBox.slideDown();
                    btn.html('<i class="mdi mdi-chevron-up"></i>');
                    // ✅ PAGINATION STARTS HERE
                    const rowsPerPage = 10;
                    let currentPage = 1;
                    const rows = ordersBox.find('tbody tr');
                    const paginationBox = vendorCard.find('.orders-pagination');

                    function renderPage(page) {
                        const start = (page - 1) * rowsPerPage;
                        const end = start + rowsPerPage;

                        rows.hide();
                        rows.slice(start, end).show();

                        renderPagination(page);
                    }

                    function renderPagination(page) {
                        const totalPages = Math.ceil(rows.length / rowsPerPage);
                        if (totalPages <= 1) {
                            paginationBox.html('');
                            return;
                        }

                        let html = '';

                        if (page > 1) {
                            html += `<button class="btn btn-sm btn-dark mr-2 prev-page">‹</button>`;
                        }

                        html += `<span>Page ${page} of ${totalPages}</span>`;

                        if (page < totalPages) {
                            html += `<button class="btn btn-sm btn-dark ml-2 next-page">›</button>`;
                        }

                        paginationBox.html(html);
                    }

// Initial render
                    renderPage(1);

// Events
                    paginationBox.off('click').on('click', '.prev-page', function () {
                        currentPage--;
                        renderPage(currentPage);
                    });

                    paginationBox.on('click', '.next-page', function () {
                        currentPage++;
                        renderPage(currentPage);
                    });
                }).fail(function (xhr) {
                    console.error('Error loading orders:', xhr.responseText);
                    ordersBox.html('<div class="alert alert-danger">Error loading orders</div>').addClass('loaded');
                    // Ensure form is still visible even on error
                    formBox.slideDown();
                });
            });

            // Save settlement
            $(document).on('click', '.save-settlement', function () {
                console.log('Save settlement clicked');

                const vendorId = $(this).data('vendor');
                const vendorCard = $(this).closest('.vendor-card');
                const txnId = vendorCard.find('.txn-id').val().trim();
                const status = vendorCard.find('.settlement-status').val();
                const comments = vendorCard.find('.comments').val();

                // Validate Transaction ID is required
                if (!txnId || txnId === '') {
                    alert('Transaction ID is required. Please enter a Transaction ID before saving.');
                    vendorCard.find('.txn-id').focus();
                    return;
                }

                // Validate Status is required
                if (!status || status === '') {
                    alert('Status is required. Please select a status before saving.');
                    vendorCard.find('.settlement-status').focus();
                    return;
                }

                // Prevent saving if status is Pending - only Settled status can be saved
                if (status === 'Pending') {
                    alert('Cannot save with Pending status. Please select "Settled" status to save the settlement.');
                    vendorCard.find('.settlement-status').focus();
                    return;
                }

                // Get date range from vendor card or filter inputs
                const startDate = vendorCard.data('start-date') || $('#start_date').val();
                const endDate = vendorCard.data('end-date') || $('#end_date').val();

                // Get vendor data from the card
                const vendorName = vendorCard.find('h4').text().trim();
                const commissionText = vendorCard.find('.commission-badge').text();
                const commissionMatch = commissionText.match(/(\d+(?:\.\d+)?)%/);
                const commission = commissionMatch ? commissionMatch[1] : '30';
                const ordersCount = commissionText.match(/(\d+)\s+Orders/);
                const totalOrders = ordersCount ? parseInt(ordersCount[1]) : 0;

                // Get amounts from the card
                const merchantPriceText = vendorCard.find('.mr-3:has(small:contains("MERCHANT PRICE")) h5').text().replace(/[₹,]/g, '');
                
                // Get settlement amount - prefer the calculated sum from orders if available
                // Check if orders have been loaded and settlement has been recalculated
                let settlementText = vendorCard.find('.text-success:has(small:contains("SETTLEMENT")) h5').text().replace(/[₹,]/g, '');
                
                // If orders are loaded, recalculate settlement from individual order rows
                const ordersTable = vendorCard.find('.orders-container table tbody');
                if (ordersTable.length && ordersTable.find('tr').length > 0) {
                    // Recalculate settlement from individual order settlement amounts
                    let recalculatedSettlement = 0;
                    ordersTable.find('tr').each(function() {
                        const settlementCell = $(this).find('td:last-child'); // Last column is SETTLEMENT AMOUNT
                        const settlementValue = settlementCell.text().replace(/[₹,]/g, '');
                        const settlement = parseFloat(settlementValue) || 0;
                        recalculatedSettlement += settlement;
                    });
                    settlementText = recalculatedSettlement.toFixed(2);
                }
                
                const profitText = vendorCard.find('.text-info:has(small:contains("JIPPY PROFIT")) h5').text().replace(/[₹,]/g, '');

                const totalMerchantPrice = parseFloat(merchantPriceText) || 0;
                const settlementAmount = parseFloat(settlementText) || 0;
                const totalJippyCommission = parseFloat(profitText) || 0;
                const totalCustomerPaid = parseFloat(vendorCard.data('customer-paid') || 0);

                console.log('Saving settlement for vendor:', vendorId, 'Status:', status);
                console.log('Settlement amount being saved:', settlementAmount, '(from text:', settlementText, ')');

                $.post(`/settlements/vendor/${vendorId}/save`, {
                    txn_id: txnId,
                    status: status,
                    comments: comments,
                    start_date: startDate,
                    end_date: endDate,
                    vendor_name: vendorName,
                    jippy_percentage: commission,
                    total_orders: totalOrders,
                    total_merchant_price: totalMerchantPrice,
                    total_customer_paid: totalCustomerPaid,
                    settlement_amount: settlementAmount,
                    total_jippy_commission: totalJippyCommission,
                    _token: '{{ csrf_token() }}'
                }, function (response) {
                    console.log('Settlement saved:', response);

                    if (response.success) {
                        alert('Settlement saved successfully!');
                        // Update status badge
                        const badge = vendorCard.find('.badge.badge-warning, .badge.badge-success').first();
                        if (badge.length) {
                            badge.removeClass('badge-warning badge-success');
                            badge.addClass(status === 'Settled' ? 'badge-success' : 'badge-warning');
                            badge.text(status);
                        }
                    }
                }).fail(function (xhr) {
                    console.error('Error saving settlement:', xhr.responseText);
                    let errorMessage = 'Error saving settlement';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        } else if (response.errors && response.errors.txn_id) {
                            errorMessage = response.errors.txn_id[0];
                        }
                    } catch (e) {
                        // Use default message
                    }
                    alert(errorMessage);
                });
            });

            // Import Payment Sheet button
            $('#import-payment-btn').on('click', function () {
                console.log('Import payment clicked');
                alert('Import Payment Sheet functionality will be implemented soon');
            });

            // Export to Excel button
            $('#export-excel-btn').on('click', function () {
                console.log('Export to Excel clicked');

                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();

                if (!startDate || !endDate) {
                    alert('Please select a date range first');
                    return;
                }

                let url = `/settlements/export?start_date=${startDate}&end_date=${endDate}`;

                console.log('Exporting to:', url);
                window.location.href = url;
            });

            // Process Bulk Payment button
            $('#bulk-payment-btn').on('click', function () {
                console.log('Bulk payment clicked');

                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();

                if (!startDate || !endDate) {
                    alert('Please select a date range first');
                    return;
                }

                if (confirm('Are you sure you want to process bulk payment for all pending settlements in this date range?')) {
                    alert('Bulk payment processing functionality will be implemented soon');
                }
            });

            console.log('✓ All event handlers attached successfully');
        });

        $('#save-week-status').on('click', function () {

            const weekStart = $('#start_date').val();
            const weekEnd   = $('#end_date').val();
            const weekStatus = $('#week-status').val();

            if (!weekStart || !weekEnd) {
                alert('Please select date range first');
                return;
            }

            // Check if trying to settle week with pending restaurants
            if (weekStatus === 'settled') {
                // Check if any vendor has pending status
                let hasPendingVendors = false;
                $('.vendor-card').each(function() {
                    const vendorCard = $(this);
                    const statusBadge = vendorCard.find('.badge-warning, .badge-success').first();
                    const statusText = statusBadge.text().trim();
                    
                    if (statusText === 'Pending') {
                        hasPendingVendors = true;
                        return false; // Break loop
                    }
                });

                if (hasPendingVendors) {
                    alert('Cannot settle week. Some restaurants have pending status. Please ensure all restaurants are settled before marking the week as settled.');
                    return;
                }
            }

            const payload = {
                week_start: weekStart,
                week_end: weekEnd,
                status: weekStatus,

                restaurants: parseInt($('#summary-vendors').text()) || 0,
                orders: parseInt($('#summary-orders').text()) || 0,
                to_settle: parseFloat($('#summary-settlement').text().replace(/,/g,'')) || 0,
                profit: parseFloat($('#summary-profit').text().replace(/,/g,'')) || 0,

                _token: '{{ csrf_token() }}'
            };

            $.post('/settlements/week/save', payload)
                .done(function (res) {
                    const savedStatus = $('#week-status').val();
                    updateWeekStatusUI(savedStatus);

                    // If status is "settled", refresh vendor data to show updated statuses
                    if (savedStatus === 'settled') {
                        const startDate = $('#start_date').val();
                        const endDate = $('#end_date').val();

                        if (startDate && endDate) {
                            // Get selected zone and status
                            const zoneId = $('.zone_selector').val();
                            const status = $('input[name="status"]:checked').val();

                            // Reload vendors to show updated statuses
                            $.get('/settlements/vendors-by-date', {
                                start_date: startDate,
                                end_date: endDate,
                                zone_id: zoneId || '',
                                status: status || ''
                            }, function (vendors) {
                                if (vendors && vendors.length > 0) {
                                    displayVendors(vendors, startDate, endDate);
                                }
                            }).fail(function (xhr) {
                                console.error('Error reloading vendors:', xhr.responseText);
                            });
                        }
                    }

                    alert(
                        `Settlement saved!\nWeek: ${res.week_code}\nSettlement Date: ${res.settlement_date}`
                    );
                })
                .fail(function (xhr) {
                    console.error(xhr.responseText);
                    let errorMessage = 'Failed to save settlement';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        // Use default message
                    }
                    alert(errorMessage);
                });
        });

        // Function to update week status UI based on status
        function updateWeekStatusUI(status) {
            const weekStatus = status.toLowerCase();
            const weekStatusSelect = $('#week-status');
            const weekStatusControls = $('#week-status-controls');
            const weekSettledBadge = $('#week-settled-badge');

            // Set the dropdown value
            weekStatusSelect.val(weekStatus);

            if (weekStatus === 'settled') {
                // Hide dropdown and save button, show "Settled" badge
                weekStatusControls.hide();
                weekSettledBadge.show();
                weekStatusSelect.prop('disabled', true);
                $('#save-week-status').prop('disabled', true);
            } else {
                // Show dropdown and save button, hide "Settled" badge
                weekStatusControls.show();
                weekSettledBadge.hide();
                weekStatusSelect.prop('disabled', false);
                $('#save-week-status').prop('disabled', false);
            }
        }
        $(document).on('click', '.toggle-week', function () {
            const btn = $(this);
            const container = $('#vendors-container');
            const icon = btn.find('i');

            if (container.is(':visible')) {
                container.slideUp();
                icon.removeClass('mdi-chevron-up').addClass('mdi-chevron-down');
            } else {
                container.slideDown();
                icon.removeClass('mdi-chevron-down').addClass('mdi-chevron-up');
            }
        });

        // Zone filter change event - reload data with zone filter
        $(document).on('change', '.zone_selector', function () {
            const zoneId = $(this).val();
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();

            console.log('Zone filter changed:', zoneId);

            // Only filter if dates are selected
            if (!startDate || !endDate) {
                console.log('Dates not selected, zone filter will apply on next filter');
                return;
            }

            // Trigger filter form submit to reload data with zone filter
            $('#filter-form').submit();
        });

        // Status filter change event - reload data with status filter
        $(document).on('change', 'input[name="status"]', function () {
            const status = $(this).val();
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();

            console.log('Status filter changed:', status);

            // Only filter if dates are selected
            if (!startDate || !endDate) {
                console.log('Dates not selected, status filter will apply on next filter');
                return;
            }

            // Trigger filter form submit to reload data with status filter
            $('#filter-form').submit();
        });
    </script>
@endsection
