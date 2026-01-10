@extends('layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h2 class="text-dark mr-2">Driver Settlement</h2>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">Driver Settlement</li>
                </ol>
            </div>
        </div>

        <div class="container-fluid">
            {{-- FILTER BAR --}}
            <div class="card mb-3">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                    <form class="d-flex align-items-center flex-wrap" id="filter-form" method="GET"
                          action="{{ url('/driverSettlements') }}">
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
                            <a href="{{ url('/driverSettlements') }}" class="btn btn-secondary ml-2">
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
                        <button class="btn btn-light text-dark font-bold mr-2" id="import-payment-btn">
                            <i class="mdi mdi-upload"></i> Import Payment Sheet
                        </button>
                        <button class="btn btn-light text-dark font-bold mr-2" id="export-excel-btn">
                            <i class="mdi mdi-file-excel"></i> Export to Excel
                        </button>
                        <button class="btn btn-light text-dark font-bold" id="bulk-payment-btn">
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
                                <h4 class="mb-0" id="summary-drivers">0</h4>
                                <small class="text-muted">Drivers</small>
                            </div>
                            <div class="px-4">
                                <h4 class="mb-0" id="summary-orders">0</h4>
                                <small class="text-muted">Orders</small>
                            </div>
                            <div class="px-4 text-success">
                                <h4 class="mb-0">₹<span id="summary-settlement">0.00</span></h4>
                                <small>Total Settlement</small>
                            </div>
                            <div class="px-4 text-info">
                                <h4 class="mb-0">₹<span id="summary-delivery-earnings">0.00</span></h4>
                                <small>Delivery Earnings</small>
                            </div>
                            <div class="px-4 text-warning">
                                <h4 class="mb-0">₹<span id="summary-tips">0.00</span></h4>
                                <small>Total Tips</small>
                            </div>
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

            {{-- DRIVERS LIST --}}
            <div id="drivers-container">
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
        $(document).ready(function () {
            let currentWeekId = null;
            let allWeeks = @json($weeks);

            // Load zones from SQL
            var loadZonesPromise = new Promise(function(resolve){
                console.log('🔄 Loading zones from SQL for driver settlement...');
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

            // Filter submit
            $('#filter-form').on('submit', function (e) {
                e.preventDefault();

                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();

                if (!startDate || !endDate) {
                    alert('Please select start date and end date');
                    return;
                }

                // Update URL
                const params = new URLSearchParams(window.location.search);
                params.set('start_date', startDate);
                params.set('end_date', endDate);
                window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);

                // Show loading
                $('#drivers-container').html('<div class="text-center py-5"><i class="fa fa-spinner fa-spin fa-3x"></i><p class="mt-3">Loading data...</p></div>');
                $('#week-summary').hide();

                // Get selected zone and status
                const zoneId = $('.zone_selector').val();
                const status = $('input[name="status"]:checked').val();

                // Fetch summary
                $.get('/settlements/driver-summary-by-date', {
                    start_date: startDate,
                    end_date: endDate,
                    zone_id: zoneId || '',
                    status: status || ''
                }, function (summaryData) {
                    currentWeekId = null;

                    function getNextFriday(dateStr) {
                        const date = new Date(dateStr);
                        const day = date.getDay();
                        let daysToAdd = (5 - day + 7) % 7;
                        if (daysToAdd === 0) daysToAdd = 7;
                        date.setDate(date.getDate() + daysToAdd);
                        return date.toISOString().split('T')[0];
                    }

                    $('#summary-week-range').text(startDate + ' - ' + endDate);
                    const settlementDate = getNextFriday(endDate);
                    $('#summary-settlement-date').text(settlementDate);
                    $('#summary-drivers').text(summaryData.drivers || 0);
                    $('#summary-orders').text(summaryData.orders || 0);
                    $('#summary-settlement').text(parseFloat(summaryData.total_settlement || 0).toLocaleString('en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                    $('#summary-delivery-earnings').text(parseFloat(summaryData.total_delivery_earnings || 0).toLocaleString('en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                    $('#summary-tips').text(parseFloat(summaryData.total_tips || 0).toLocaleString('en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));

                    updateWeekStatusUI(summaryData.week_status || 'open');
                    $('#week-summary').slideDown();

                    // Fetch drivers
                    $.get('/settlements/drivers-by-date', {
                        start_date: startDate,
                        end_date: endDate,
                        zone_id: zoneId || '',
                        status: status || ''
                    }, function (drivers) {
                        if (!drivers || drivers.length === 0) {
                            $('#drivers-container').html('<div class="alert alert-info text-center">No drivers found for the selected date range</div>');
                            return;
                        }
                        displayDrivers(drivers, startDate, endDate);
                    }).fail(function (xhr) {
                        console.error('Error loading drivers:', xhr.responseText);
                        $('#drivers-container').html('<div class="alert alert-danger">Error loading drivers</div>');
                    });
                }).fail(function (xhr) {
                    console.error('Error loading summary:', xhr.responseText);
                    alert('Error loading summary data');
                });
            });

            function updateWeekStatusUI(status) {
                $('#week-status').val(status);
                if (status === 'settled') {
                    $('#week-status-controls').hide();
                    $('#week-settled-badge').show();
                } else {
                    $('#week-status-controls').show();
                    $('#week-settled-badge').hide();
                }
            }

            // Display drivers
            function displayDrivers(drivers, startDate = null, endDate = null, weekId = null) {
                let html = '';
                drivers.forEach((d, index) => {
                    const driverName = d.driver_name || 'NA';
                    const initials = driverName.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();

                    const savedStatus = (d.saved_settlement && d.saved_settlement.payment_status) ? d.saved_settlement.payment_status : 'Pending';
                    const statusClass = savedStatus === 'Settled' ? 'badge-success' : 'badge-warning';
                    const statusBadge = `<span class="badge ${statusClass}">${savedStatus}</span>`;
                    const savedTxnId = (d.saved_settlement && d.saved_settlement.transaction_id) ? d.saved_settlement.transaction_id : '';
                    const savedComments = (d.saved_settlement && d.saved_settlement.payment_comments) ? d.saved_settlement.payment_comments : '';
                    const incentives = (d.saved_settlement && d.saved_settlement.incentives) ? parseFloat(d.saved_settlement.incentives) : 0;
                    const deductions = (d.saved_settlement && d.saved_settlement.deductions) ? parseFloat(d.saved_settlement.deductions) : 0;

                    html += `
                <div class="card mb-3 driver-card" data-driver="${d.driver_id}" data-week="${weekId || ''}" data-start-date="${startDate || ''}" data-end-date="${endDate || ''}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                     style="width: 50px; height: 50px; font-weight: bold; margin-right: 15px;">
                                    ${initials}
                                </div>
                                <div class="d-flex flex-column">
                                    <div class="d-flex align-items-center flex-wrap">
                                        <h4 class="mb-0 mr-2">${driverName}</h4>
                                        <h3 class="font-weight-bold mb-0 mr-3" style="font-size: 14px;">(${d.phone || '-'})</h3>
                                        <span class="badge badge-info mr-3" style="font-size:10px; padding:4px 10px;">${d.zone || 'N/A'}</span>
                                        <span class="badge badge-light mr-3" style="font-size:15px; padding:4px 10px;">
                                            ${d.orders_count} Deliveries
                                        </span>

<!--                                    <div class="d-flex align-items-center mt-2">-->
                                        <div class="mr-3 text-primary">
                                            <small class="d-block">DELIVERY EARNINGS</small>
                                            <h5 class="mb-0">₹${parseFloat(d.delivery_earning || 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}</h5>
                                        </div>
                                        <div class="mr-3 text-warning">
                                            <small class="d-block">TIPS RECEIVED</small>
                                            <h5 class="mb-0">₹${parseFloat(d.tip_earning || 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}</h5>
                                        </div>
                                        <div class="text-success">
                                            <small class="d-block">SETTLEMENT AMOUNT</small>
                                            <h5 class="mb-0">₹${parseFloat(d.settlement_amount || d.total_earning || 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}</h5>
<!--                                        </div>-->
</div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                ${statusBadge}
                                <button class="btn btn-sm btn-light toggle-orders mt-2" title="View Orders">
                                    <i class="mdi mdi-chevron-down"></i>
                                </button>
                            </div>
                        </div>

                        <div class="orders-container mt-3" style="display: none;"></div>
                        <div class="orders-pagination text-center mt-2"></div>

                        <div class="settlement-form mt-3" style="display: none;">
                            <hr>
                            <div class="row align-items-end">
                                <div class="col-md-2">
                                    <label class="form-label">Transaction ID</label>
                                    <input type="text" class="form-control txn-id" placeholder="TXN ID" value="${savedTxnId}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Status</label>
                                    <select class="form-control settlement-status">
                                        <option value="Pending" ${savedStatus === 'Pending' ? 'selected' : ''}>Pending</option>
                                        <option value="Processing" ${savedStatus === 'Processing' ? 'selected' : ''}>Processing</option>
                                        <option value="Settled" ${savedStatus === 'Settled' ? 'selected' : ''}>Settled</option>
                                        <option value="Failed" ${savedStatus === 'Failed' ? 'selected' : ''}>Failed</option>
                                        <option value="On Hold" ${savedStatus === 'On Hold' ? 'selected' : ''}>On Hold</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Incentives (₹)</label>
                                    <input type="number" step="0.01" class="form-control incentives" placeholder="0.00" value="${incentives}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Deductions (₹)</label>
                                    <input type="number" step="0.01" class="form-control deductions" placeholder="0.00" value="${deductions}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Comments</label>
                                    <input type="text" class="form-control comments" placeholder="Comments" value="${savedComments}">
                                </div>
                                <div class="col-md-1">
                                    <button class="btn btn-success save-settlement" data-driver="${d.driver_id}">
                                        <i class="mdi mdi-content-save"></i> Save
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
                });

                $('#drivers-container').html(html);
            }

            // Toggle orders
            $(document).on('click', '.toggle-orders', function () {
                const driverCard = $(this).closest('.driver-card');
                const driverId = driverCard.data('driver');
                const weekId = driverCard.data('week');
                const startDate = driverCard.data('start-date');
                const endDate = driverCard.data('end-date');
                const ordersBox = driverCard.find('.orders-container');
                const formBox = driverCard.find('.settlement-form');
                const btn = $(this);

                if (ordersBox.hasClass('loaded')) {
                    ordersBox.slideToggle();
                    formBox.slideToggle();
                    const isVisible = ordersBox.is(':visible');
                    btn.html(isVisible ? '<i class="mdi mdi-chevron-up"></i>' : '<i class="mdi mdi-chevron-down"></i>');
                    return;
                }

                let ordersUrl = `/settlements/driver/${driverId}/orders`;
                if (weekId && currentWeekId !== null) {
                    ordersUrl += `?week_id=${weekId}`;
                } else if (startDate && endDate) {
                    ordersUrl += `?start_date=${startDate}&end_date=${endDate}`;
                }

                ordersBox.html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading orders...</div>').show();

                $.get(ordersUrl, function (orders) {
                    if (orders.length === 0) {
                        ordersBox.html('<div class="alert alert-info">No orders found</div>').addClass('loaded');
                        formBox.slideDown();
                        return;
                    }

                    function formatOrderDate(createdAt) {
                        if (!createdAt) return '-';
                        if (!isNaN(createdAt)) {
                            let ts = createdAt.toString().length > 10 ? parseInt(createdAt) / 1000 : parseInt(createdAt);
                            return new Date(ts * 1000).toLocaleDateString('en-IN');
                        }
                        let d = new Date(createdAt);
                        if (!isNaN(d.getTime())) {
                            return d.toLocaleDateString('en-IN');
                        }
                        return createdAt;
                    }

                    let html = `
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>DATE</th>
                            <th>ORDER ID</th>
                            <th>DELIVERY CHARGE</th>
                            <th>TIP AMOUNT</th>
                            <th>TOTAL EARNING</th>
                        </tr>
                    </thead>
                    <tbody>`;

                    orders.forEach(o => {
                        const deliveryCharge = parseFloat(o.deliveryCharge || 0);
                        const tipAmount = parseFloat(o.tip_amount || 0);
                        const total = deliveryCharge + tipAmount;

                        html += `
                    <tr>
                        <td>${formatOrderDate(o.createdAt)}</td>
                        <td>${o.id}</td>
                        <td>₹${deliveryCharge.toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                        <td>₹${tipAmount.toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                        <td class="text-success">₹${total.toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                    </tr>`;
                    });

                    html += '</tbody></table></div>';
                    ordersBox.html(html).addClass('loaded').slideDown();
                    formBox.slideDown();
                    btn.html('<i class="mdi mdi-chevron-up"></i>');
                    // ✅ PAGINATION STARTS HERE
                    const rowsPerPage = 10;
                    let currentPage = 1;
                    const rows = ordersBox.find('tbody tr');
                    const paginationBox = driverCard.find('.orders-pagination');

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
                    ordersBox.html('<div class="alert alert-danger">Error loading orders</div>');
                });
            });

            // Save settlement
            $(document).on('click', '.save-settlement', function () {
                const driverId = $(this).data('driver');
                const driverCard = $(this).closest('.driver-card');
                const txnId = driverCard.find('.txn-id').val();
                const status = driverCard.find('.settlement-status').val();
                const comments = driverCard.find('.comments').val();
                const incentives = parseFloat(driverCard.find('.incentives').val() || 0);
                const deductions = parseFloat(driverCard.find('.deductions').val() || 0);

                const startDate = driverCard.data('start-date') || $('#start_date').val();
                const endDate = driverCard.data('end-date') || $('#end_date').val();

                const driverName = driverCard.find('h4').text().trim();
                const deliveryEarningText = driverCard.find('.text-primary h5').text().replace(/[₹,]/g, '');
                const tipEarningText = driverCard.find('.text-warning h5').text().replace(/[₹,]/g, '');
                const ordersCountText = driverCard.find('.badge-light').text();
                const ordersMatch = ordersCountText.match(/(\d+)/);
                const totalDeliveries = ordersMatch ? parseInt(ordersMatch[1]) : 0;

                const deliveryEarnings = parseFloat(deliveryEarningText) || 0;
                const tipsReceived = parseFloat(tipEarningText) || 0;
                const settlementAmount = deliveryEarnings + tipsReceived + incentives - deductions;

                $.post(`/settlements/driver/${driverId}/save`, {
                    txn_id: txnId,
                    status: status,
                    comments: comments,
                    start_date: startDate,
                    end_date: endDate,
                    driver_name: driverName,
                    total_deliveries: totalDeliveries,
                    delivery_earnings: deliveryEarnings,
                    tips_received: tipsReceived,
                    settlement_amount: settlementAmount,
                    incentives: incentives,
                    deductions: deductions,
                    _token: '{{ csrf_token() }}'
                }, function (response) {
                    if (response.success) {
                        alert('Driver settlement saved successfully!');
                        const badge = driverCard.find('.badge').first();
                        if (badge.length) {
                            badge.removeClass('badge-warning badge-success');
                            badge.addClass(status === 'Settled' ? 'badge-success' : 'badge-warning');
                            badge.text(status);
                        }
                    }
                }).fail(function (xhr) {
                    console.error('Error saving settlement:', xhr.responseText);
                    alert('Error saving settlement');
                });
            });

            // Save week status
            $('#save-week-status').on('click', function () {
                const weekStart = $('#start_date').val();
                const weekEnd = $('#end_date').val();

                if (!weekStart || !weekEnd) {
                    alert('Please select date range first');
                    return;
                }

                const payload = {
                    week_start: weekStart,
                    week_end: weekEnd,
                    status: $('#week-status').val(),
                    drivers: parseInt($('#summary-drivers').text()) || 0,
                    orders: parseInt($('#summary-orders').text()) || 0,
                    to_settle: parseFloat($('#summary-settlement').text().replace(/[₹,]/g, '')) || 0,
                    delivery_earnings: parseFloat($('#summary-delivery-earnings').text().replace(/[₹,]/g, '')) || 0,
                    tips: parseFloat($('#summary-tips').text().replace(/[₹,]/g, '')) || 0,
                    _token: '{{ csrf_token() }}'
                };

                $.post('/settlements/driver-week/save', payload)
                    .done(function (res) {
                        const savedStatus = $('#week-status').val();
                        updateWeekStatusUI(savedStatus);

                        // If status is "settled", refresh driver data to show updated statuses
                        if (savedStatus === 'settled') {
                            const startDate = $('#start_date').val();
                            const endDate = $('#end_date').val();

                            if (startDate && endDate) {
                                // Get selected zone and status
                                const zoneId = $('.zone_selector').val();
                                const status = $('input[name="status"]:checked').val();
                                
                                // Reload drivers to show updated statuses
                                $.get('/settlements/drivers-by-date', {
                                    start_date: startDate,
                                    end_date: endDate,
                                    zone_id: zoneId || '',
                                    status: status || ''
                                }, function (drivers) {
                                    if (drivers && drivers.length > 0) {
                                        displayDrivers(drivers, startDate, endDate);
                                    }
                                }).fail(function (xhr) {
                                    console.error('Error reloading drivers:', xhr.responseText);
                                });
                            }
                        }

                        alert('Week status saved successfully!');
                    })
                    .fail(function (xhr) {
                        console.error('Error saving week status:', xhr.responseText);
                        alert('Error saving week status');
                    });
            });

            // Export to Excel
            $('#export-excel-btn').on('click', function () {
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();

                if (!startDate || !endDate) {
                    alert('Please select start date and end date');
                    return;
                }

                // Get selected zone and status
                const zoneId = $('.zone_selector').val();
                const status = $('input[name="status"]:checked').val();

                let url = `/settlements/export-driver?start_date=${startDate}&end_date=${endDate}`;
                if (zoneId) {
                    url += `&zone_id=${zoneId}`;
                }
                if (status) {
                    url += `&status=${status}`;
                }

                console.log('Exporting to:', url);
                window.location.href = url;
            });

            // Auto-load on page load
            @if(request('start_date') && request('end_date'))
            const startDate = '{{ request('start_date') }}';
            const endDate = '{{ request('end_date') }}';
            $('#start_date').val(startDate);
            $('#end_date').val(endDate);
            // Wait for zones to load before submitting
            loadZonesPromise.then(function() {
                $('#filter-form').submit();
            });
            @endif

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
        });
        $(document).on('click', '.toggle-week', function () {
            const btn = $(this);
            const container = $('#drivers-container');
            const icon = btn.find('i');

            if (container.is(':visible')) {
                container.slideUp();
                icon.removeClass('mdi-chevron-up').addClass('mdi-chevron-down');
            } else {
                container.slideDown();
                icon.removeClass('mdi-chevron-down').addClass('mdi-chevron-up');
            }
        });
    </script>
@endsection
