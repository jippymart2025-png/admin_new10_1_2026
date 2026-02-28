@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Promotions</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">Dashboard</a></li>
                <li class="breadcrumb-item active">Promotions</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><img src="{{ asset('images/category.png') }}"></span>
                            <h3 class="mb-0">Promotions List</h3>
                            <span class="counter ml-3 promotion_count"></span>
                        </div>
                        <div class="d-flex top-title-right align-self-center">
                            <div class="select-box pl-3">
                            </div>
                        </div>
                        <div class=" d-flex justify-content-between align-items-center border-0 flex-nowrap">
                            <!-- RIGHT ACTIONS -->
                            <div class="d-flex align-items-center flex-nowrap">
                                <div class="select-box pl-3">
                                    <select id="vtype_filter" class="form-control">
                                        <option value="">Type</option>
                                        <option value="restaurant">Restaurant</option>
                                        <option value="mart">Mart</option>
                                    </select>
                                </div>

                                <div class="select-box pl-3">
                                    <select id="zone_filter" class="form-control">
                                        <option value="">All Zones</option>
                                    </select>
                                </div>

{{--                                <div class="select-box pl-3">--}}
{{--                                    <button type="button"--}}
{{--                                            class="btn btn-sm btn-secondary"--}}
{{--                                            id="clear_filters_btn"--}}
{{--                                            style="display:none; white-space: nowrap;">--}}
{{--                                        <i class="mdi mdi-close-circle mr-1"></i>Clear Filters--}}
{{--                                    </button>--}}
{{--                                </div>--}}

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

{{--         <div class="row mb-4">--}}
{{--            <div class="col-12">--}}
{{--                <div class="card border">--}}
{{--                    <div class="card-header d-flex justify-content-between align-items-center border-0">--}}
{{--                        <div class="card-header-title">--}}
{{--                            <h3 class="text-dark-2 mb-2 h4">Bulk Import Promotions</h3>--}}
{{--                            <p class="mb-0 text-dark-2">Upload Excel file to import multiple promotions at once</p>--}}
{{--                        </div>--}}
{{--                        <div class="card-header-right d-flex align-items-center">--}}
{{--                            <div class="card-header-btn mr-3">--}}
{{--                                <a href="{{ route('promotions.download-template') }}" class="btn btn-outline-primary rounded-full">--}}
{{--                                    <i class="mdi mdi-download mr-2"></i>Download Template--}}
{{--                                </a>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="card-body">--}}
{{--                        <form action="{{ route('promotions.import') }}" method="POST" enctype="multipart/form-data">--}}
{{--                            @csrf--}}
{{--                            <div class="row">--}}
{{--                                <div class="col-md-8">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <label for="importFile" class="control-label">Select Excel File (.xls/.xlsx)</label>--}}
{{--                                        <input type="file" name="file" id="importFile" accept=".xls,.xlsx" class="form-control" required>--}}
{{--                                        <div class="form-text text-muted">--}}
{{--                                            <i class="mdi mdi-information-outline mr-1"></i>--}}
{{--                                            File should contain: restaurant_id, product_id, special_price, extra_km_charge, free_delivery_km, start_time, end_time, payment_mode--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-md-4 d-flex align-items-end">--}}
{{--                                    <button type="submit" class="btn btn-primary rounded-full">--}}
{{--                                        <i class="mdi mdi-upload mr-2"></i>Import Promotions--}}
{{--                                    </button>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </form>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div> --}}

        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">Promotions Table</h3>
                                <p class="mb-0 text-dark-2">Manage all promotions and their details</p>
                            </div>
                            <div class="card-header-right d-flex align-items-center">
                                <div class="card-header-btn mr-3">
                                    <a href="{{ route('promotions.create') }}" class="btn-primary btn rounded-full">
                                        <i class="mdi mdi-plus mr-2"></i>Add Promotion
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="promotionsTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> All</a></label></th>
                                            <th>Type</th>
                                            <th>Zone</th>
                                            <th>Restaurant/Mart</th>
                                            <th>Product</th>
                                            <th>Special Price</th>
                                            <th>Item Limit</th>
                                            <th>Extra KM Charge</th>
                                            <th>Free Delivery KM</th>
                                            <th>Start Time</th>
                                            <th>End Time</th>
                                            <th>Payment Mode</th>
                                            <th>Available</th>
                                            <th>Promo</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="promotion-table-body">
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
<div id="data-table_processing" class="data-table-processing" style="display: none">Processing...</div>
@endsection
@section('scripts')
<script>
var selectedVTypeFilter = '';
var selectedZoneFilter = '';

// Sync filter globals from current select values (jQuery .val() works with Select2)
function syncFilterValues() {
    var v = ($('#vtype_filter').val() || '').toString().trim().toLowerCase();
    var z = ($('#zone_filter').val() || '').toString().trim();
    selectedVTypeFilter = v;
    selectedZoneFilter = z;
}

// Function to show/hide clear filters button
function updateClearFiltersButton() {
    if (selectedVTypeFilter || selectedZoneFilter) {
        $('#clear_filters_btn').show();
    } else {
        $('#clear_filters_btn').hide();
    }
}

function formatDateTime(dateStr) {
    if (!dateStr) return '';
    try {
        var date = new Date(dateStr);
        return date.toLocaleString();
    } catch (e) {
        return dateStr;
    }
}

function isExpired(endTime) {
    if (!endTime) return false;
    try {
        var endDate = new Date(endTime);
        var currentDate = new Date();
        return endDate < currentDate;
    } catch (e) {
        return false;
    }
}

function renderTable(promotions) {
    console.log('📊 Rendering ' + promotions.length + ' promotions');

    var tbody = '';
    var visibleCount = 0;
    promotions.forEach(function(promo) {
        var isExpiredPromo = promo.isExpired || false;
        var expiredText = isExpiredPromo ? '<br><span class="badge badge-danger">EXPIRED</span>' : '';
        var rowClass = isExpiredPromo ? 'table-danger' : '';

        var typeText = promo.vType ? (promo.vType.charAt(0).toUpperCase() + promo.vType.slice(1)) : '-';
        var zoneText = promo.zone_name || '-';

        tbody += '<tr class="' + rowClass + '">' +
            '<td class="delete-all"><input type="checkbox" id="is_open_' + promo.id + '" class="is_open" dataId="' + promo.id + '"><label class="col-3 control-label" for="is_open_' + promo.id + '"></label></td>' +
            '<td>' + typeText + '</td>' +
            '<td>' + zoneText + '</td>' +
            '<td>' + promo.restaurant_title + '</td>' +
            '<td>' + promo.product_title + '</td>' +
            '<td>₹' + promo.special_price + '</td>' +
            '<td>' + promo.item_limit + '</td>' +
            '<td>' + promo.extra_km_charge + '</td>' +
            '<td>' + promo.free_delivery_km + '</td>' +
            '<td>' + formatDateTime(promo.start_time) + '</td>' +
            '<td>' + formatDateTime(promo.end_time) + expiredText + '</td>' +
            '<td>' + promo.payment_mode + '</td>' +
            '<td>' + (promo.isAvailable ? '<label class="switch"><input type="checkbox" checked id="'+promo.id+'" name="isAvailable"><span class="slider round"></span></label>' : '<label class="switch"><input type="checkbox" id="'+promo.id+'" name="isAvailable"><span class="slider round"></span></label>') + '</td>' +
            '<td>' +    '<label class="switch">' +
            '<input type="checkbox" class="promo-toggle" data-id="'+promo.id+'" ' + ((promo.promo == 1 || promo.promo === true || promo.promo === '1') ? 'checked' : '') + '>' + '<span class="slider round"></span>' + '</label>' + '</td>' +
            '<td>' +
                '<span class="action-btn">' +
                    '<a href="'+editUrl(promo.id)+'"><i class="mdi mdi-lead-pencil" title="Edit"></i></a> ' +
                    '<a id="'+promo.id+'" name="promotion-delete" class="delete-btn" href="javascript:void(0)"><i class="mdi mdi-delete" title="Delete"></i></a>' +
                '</span>' +
            '</td>' +
            '</tr>';
        visibleCount++;
    });
    $('#promotion-table-body').html(tbody);

    console.log('✅ Table rendered with ' + visibleCount + ' rows');
}

function editUrl(id) {
    return '{{ route('promotions.edit', ['id' => 'PROMOID']) }}'.replace('PROMOID', id);
}

function loadPromotions() {
    syncFilterValues(); // ensure globals match current select values before request
    loadPromotionsRequestId += 1;
    var thisRequestId = loadPromotionsRequestId;
    console.log('📡 Loading promotions...', {
        vtype: selectedVTypeFilter,
        zone: selectedZoneFilter,
        requestId: thisRequestId
    });
    jQuery('#data-table_processing').show();

    $.ajax({
        url: '{{ route('promotions.data') }}',
        method: 'GET',
        data: {
            vtype_filter: selectedVTypeFilter || '',
            zone_filter: selectedZoneFilter || ''
        },
        traditional: true,
        success: function(response) {
            // Only apply this response if it's still the latest request (avoid stale response overwriting table)
            if (thisRequestId !== loadPromotionsRequestId) {
                console.log('⏭️ Ignoring stale promotions response', thisRequestId, 'current', loadPromotionsRequestId);
                return;
            }
            console.log('📥 Promotions response:', response.data.length, 'rows, requestId:', thisRequestId);

            if (response.success) {
                renderTable(response.data);
                jQuery('#data-table_processing').hide();

                // Update count from same response so count and table always match
                if (response.stats && response.stats.filtered !== undefined) {
                    var displayCount = (selectedVTypeFilter || selectedZoneFilter) ? response.stats.filtered : response.stats.total;
                    $('.promotion_count').text(displayCount);
                    console.log('📊 Filtered:', response.stats.filtered, 'Total:', response.stats.total, 'Data rows:', response.data.length);
                } else if (response.stats && response.stats.total) {
                    $('.promotion_count').text(response.stats.total);
                } else {
                    $('.promotion_count').text(response.data.length);
                }

                // Destroy existing DataTable if it exists
                if (promotionsDataTable) {
                    promotionsDataTable.destroy();
                    promotionsDataTable = null;
                }

                // Initialize DataTable on the newly rendered rows
                promotionsDataTable = $('#promotionsTable').DataTable({
                    destroy: true,
                    pageLength: 30,
                    lengthMenu: [[10, 25, 30, 50, 100, -1], [10, 25, 30, 50, 100, "All"]],
                    responsive: true,
                    searching: true,
                    ordering: true,
                    order: [[9, 'desc']], // Order by Start Time descending
                    columnDefs: [
                        { orderable: false, targets: [0, 12, 13, 14] } // Checkbox, Available, Promo, Actions
                    ],
                    "language": {
                        "zeroRecords": "No promotions found",
                        "emptyTable": "No promotions available",
                        "processing": ""
                    },
                    initComplete: function() {
                        console.log('✅ DataTable initialized successfully');
                    }
                });
            } else {
                jQuery('#data-table_processing').hide();
                console.error('❌ Error loading promotions:', response.error);
                $('.promotion_count').text('0');
                $('#promotion-table-body').html('<tr><td colspan="15" class="text-center">Error loading promotions: ' + (response.error || 'Unknown error') + '</td></tr>');

                if (typeof toastr !== 'undefined') {
                    toastr.error('Error loading promotions: ' + (response.error || 'Unknown error'));
                } else {
                    alert('Error loading promotions: ' + (response.error || 'Unknown error'));
                }
            }
        },
        error: function(xhr, status, error) {
            if (thisRequestId !== loadPromotionsRequestId) return;
            jQuery('#data-table_processing').hide();
            console.error('❌ AJAX Error loading promotions:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error
            });

            $('.promotion_count').text('0');
            $('#promotion-table-body').html('<tr><td colspan="15" class="text-center text-danger">Error loading promotions. Please try again.</td></tr>');

            var errorMsg = 'Error loading promotions';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg += ': ' + xhr.responseJSON.error;
            } else if (xhr.statusText) {
                errorMsg += ': ' + xhr.statusText;
            }

            if (typeof toastr !== 'undefined') {
                toastr.error(errorMsg);
            } else {
                alert(errorMsg);
            }
        }
    });
}

var promotionsDataTable = null;
var loadPromotionsRequestId = 0; // ignore stale AJAX responses

$(document).ready(function() {
    console.log('📡 Initializing Promotions page...');

    // Check for flash messages
    @if(session('success'))
        console.log('✅ Success message:', '{{ session('success') }}');
        if (typeof toastr !== 'undefined') {
            toastr.success('{{ session('success') }}');
        }
    @endif

    @if(session('error'))
        console.log('❌ Error message:', '{{ session('error') }}');
        if (typeof toastr !== 'undefined') {
            toastr.error('{{ session('error') }}');
        }
    @endif

    // Load zones for filter first
    $.ajax({
        url: '{{ route('promotions.zones') }}',
        method: 'GET',
        success: function(response) {
            if (response.success && response.data) {
                response.data.forEach(function(zone) {
                    $('#zone_filter').append('<option value="'+zone.id+'">'+zone.name+'</option>');
                });
                console.log('✅ Loaded ' + response.data.length + ' zones for filter');

                // Initialize Select2 after zones are loaded (if Select2 is available)
                if (typeof $.fn.select2 !== 'undefined') {
                    $('#vtype_filter').select2({
                        placeholder: "{{ trans('lang.restaurant_type') }}",
                        minimumResultsForSearch: Infinity,
                        allowClear: true,
                    });
                    $('#zone_filter').select2({
                        placeholder: "{{ trans('lang.select_zone') }}",
                        minimumResultsForSearch: Infinity,
                        allowClear: true,
                    });

                    // Select2: use event data so we have the correct value before DOM may update
                    $('#vtype_filter').on('select2:select', function(e) {
                        selectedVTypeFilter = (e.params.data.id || '').toString().trim().toLowerCase();
                        updateClearFiltersButton();
                        loadPromotions();
                    });
                    $('#vtype_filter').on('select2:clear', function() {
                        selectedVTypeFilter = '';
                        updateClearFiltersButton();
                        loadPromotions();
                    });
                    $('#zone_filter').on('select2:select', function(e) {
                        selectedZoneFilter = (e.params.data.id || '').toString().trim();
                        updateClearFiltersButton();
                        loadPromotions();
                    });
                    $('#zone_filter').on('select2:clear', function() {
                        selectedZoneFilter = '';
                        updateClearFiltersButton();
                        loadPromotions();
                    });

                    // Handle Select2 clear button / unselect
                    $('#vtype_filter, #zone_filter').on('select2:unselecting', function(e) {
                        var self = $(this);
                        setTimeout(function() {
                            self.select2('close');
                        }, 0);
                    });
                }
            }
        },
        error: function(xhr) {
            console.error('❌ Error loading zones:', xhr);
        },
        complete: function() {
            // Load promotions after zones are loaded
            loadPromotions();
        }
    });

    // Filter change handlers (for native select when Select2 is not used, and as fallback)
    $(document).on('change', '#vtype_filter', function() {
        syncFilterValues();
        console.log('🔍 Type filter changed:', selectedVTypeFilter);
        updateClearFiltersButton();
        loadPromotions();
    });

    $(document).on('change', '#zone_filter', function() {
        syncFilterValues();
        console.log('🔍 Zone filter changed:', selectedZoneFilter);
        updateClearFiltersButton();
        loadPromotions();
    });

    // Clear filters button
    $(document).on('click', '#clear_filters_btn', function(e) {
        e.preventDefault();
        selectedVTypeFilter = '';
        selectedZoneFilter = '';

        if (typeof $.fn.select2 !== 'undefined') {
            $('#vtype_filter').val(null).trigger('change');
            $('#zone_filter').val(null).trigger('change');
        } else {
            $('#vtype_filter').val('').trigger('change');
            $('#zone_filter').val('').trigger('change');
        }

        updateClearFiltersButton();
        loadPromotions();
    });


    // Select all checkboxes
    $("#is_active").click(function () {
        $("#promotionsTable .is_open").prop('checked', $(this).prop('checked'));
    });

    // Delete selected
    $("#deleteAll").click(function () {
        if ($('#promotionsTable .is_open:checked').length) {
            var selectedCount = $('#promotionsTable .is_open:checked').length;

            console.log('🗑️ Bulk delete promotions requested:', { count: selectedCount });

            if (confirm("Are you sure you want to delete selected promotions?")) {
                jQuery("#data-table_processing").show();

                var ids = [];
                $('#promotionsTable .is_open:checked').each(function () {
                    ids.push($(this).attr('dataId'));
                });

                $.ajax({
                    url: '{{ route('promotions.bulk-delete') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: ids
                    },
                    success: function(response) {
                        console.log('✅ Bulk delete completed:', response);

                        if (response.success) {
                            // Log activity
                            if (typeof logActivity === 'function') {
                                logActivity('promotions', 'bulk_deleted', 'Bulk deleted ' + (response.deleted || selectedCount) + ' promotions');
                            }

                            // Reload table without filters to show updated data
                            loadPromotions();

                            if (typeof toastr !== 'undefined') {
                                toastr.success('Deleted ' + (response.deleted || selectedCount) + ' promotions');
                            } else {
                                alert('Promotions deleted successfully');
                            }
                        } else {
                            alert('Error deleting promotions: ' + response.error);
                        }
                    },
                    error: function(xhr) {
                        console.error('❌ Bulk delete error:', xhr);
                        alert('Error deleting promotions: ' + (xhr.responseJSON?.error || xhr.statusText));
                    },
                    complete: function() {
                        jQuery("#data-table_processing").hide();
                    }
                });
            }
        } else {
            alert("Please select promotions to delete");
        }
    });

    // Single delete
    $(document).on("click", "a[name='promotion-delete'], .delete-btn", function() {
        var id = this.id || $(this).data('id');
        var promotionName = $(this).closest('tr').find('td').eq(3).text().trim() + ' - ' + $(this).closest('tr').find('td').eq(4).text().trim();

        console.log('🗑️ Delete promotion clicked:', { id: id, name: promotionName });

        if (confirm('Are you sure you want to delete this promotion?')) {
            jQuery('#data-table_processing').show();

            $.ajax({
                url: '{{ route('promotions.destroy', ['id' => 'PROMOTION_ID']) }}'.replace('PROMOTION_ID', id),
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log('✅ Promotion deleted successfully:', response);

                    if (response.success) {
                        // Log activity
                        if (typeof logActivity === 'function') {
                            logActivity('promotions', 'deleted', 'Deleted promotion: ' + promotionName);
                        }

                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message || 'Promotion deleted successfully');
                        } else {
                            alert('Promotion deleted successfully');
                        }

                        // ✅ Reload page after success
                        setTimeout(function() {
                            window.location.reload();
                        }, 800);
                    } else {
                        alert('Error deleting promotion: ' + response.error);
                    }
                },
                error: function(xhr) {
                    console.error('❌ Delete error:', xhr);
                    alert('Error deleting promotion: ' + (xhr.responseJSON?.error || xhr.statusText));
                },
                complete: function() {
                    jQuery('#data-table_processing').hide();
                }
            });
        }
    });

    // Toggle isAvailable
    $(document).on("click", "input[name='isAvailable']", function(e) {
        var checkbox = $(this);
        var ischeck = checkbox.is(':checked');
        var id = checkbox.attr('id');
        var promotionName = checkbox.closest('tr').find('td').eq(3).text().trim() + ' - ' + checkbox.closest('tr').find('td').eq(4).text().trim();

        console.log('🔄 Toggle promotion availability:', { id: id, checked: ischeck, name: promotionName });

        if (!id || id === '') {
            alert('Error: Promotion ID is missing');
            checkbox.prop('checked', !ischeck);
            return;
        }

        // Disable checkbox during update
        checkbox.prop('disabled', true);

        var url = '{{ url("/promotions/toggle") }}/' + encodeURIComponent(id);

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                isAvailable: ischeck ? 1 : 0
            },
            success: function(response) {
                console.log('✅ Promotion availability toggled:', response);

                if (response.success) {
                    // Update checkbox to match server state
                    checkbox.prop('checked', !!response.isAvailable);

                    // Log activity
                    var action = response.isAvailable ? 'activated' : 'deactivated';
                    if (typeof logActivity === 'function') {
                        logActivity('promotions', action, action.charAt(0).toUpperCase() + action.slice(1) + ' promotion: ' + promotionName);
                    }

                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message || 'Promotion updated successfully');
                    }
                } else {
                    console.error('❌ Toggle failed:', response);
                    alert('Error: ' + (response.error || 'Unknown error'));
                    checkbox.prop('checked', !ischeck);
                }
            },
            error: function(xhr) {
                console.error('❌ Toggle error:', xhr);
                alert('Error updating promotion: ' + (xhr.responseJSON?.error || xhr.statusText));
                checkbox.prop('checked', !ischeck);
            },
            complete: function() {
                checkbox.prop('disabled', false);
            }
        });
    });
    // Toggle promo
    $(document).on('change', '.promo-toggle', function () {
        const checkbox = $(this);
        const promoId = checkbox.data('id');
        const wasChecked = checkbox.is(':checked');

        // Prevent double trigger
        checkbox.prop('disabled', true);

        $.ajax({
            url: '/promotions/togglePromo/' + promoId,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function (res) {
                if (res.success) {
                    // Update checkbox state based on server response
                    checkbox.prop('checked', res.promo == 1 || res.promo === true);

                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message);
                    } else {
                        console.log('✅ Promo toggled:', res.message);
                    }
                } else {
                    // Revert checkbox on failure
                    checkbox.prop('checked', !wasChecked);
                    alert(res.message || 'Failed to update promo');
                }
            },
            error: function (xhr) {
                // Revert checkbox on error
                checkbox.prop('checked', !wasChecked);
                console.error('❌ Toggle promo error:', xhr);
                alert('Something went wrong: ' + (xhr.responseJSON?.message || xhr.statusText));
            },
            complete: function () {
                checkbox.prop('disabled', false);
            }
        });
    });
});
</script>
@endsection
