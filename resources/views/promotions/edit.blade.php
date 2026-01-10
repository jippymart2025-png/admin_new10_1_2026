@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">Edit Promotion</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('promotions') !!}">Promotions</a></li>
                    <li class="breadcrumb-item active">Edit Promotion</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="cat-edite-page max-width-box">
                <div class="card pb-4">
                    <div class="card-header">
                        <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                            <li role="presentation" class="nav-item">
                                <a href="#promotion_information" aria-controls="description" role="tab" data-toggle="tab"
                                   class="nav-link active">Promotion Information</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="error_top" style="display:none"></div>
                        <div class="row restaurant_payout_create" role="tabpanel">
                            <div class="restaurant_payout_create-inner tab-content">
                                <div role="tabpanel" class="tab-pane active" id="promotion_information">
                                    <fieldset>
                                        <legend>Edit Promotion</legend>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">Type</label>
                                            <div class="col-7">
                                                <select id="promotion_vtype" class="form-control">
                                                    <option value="">Select Type</option>
                                                    <option value="restaurant">Restaurant</option>
                                                    <option value="mart">Mart</option>
                                                </select>
                                                <div class="form-text text-muted">Choose whether this promotion is for a Restaurant or Mart.</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">Zone</label>
                                            <div class="col-7">
                                                <select id="promotion_zone" class="form-control"></select>
                                                <div class="form-text text-muted">Filter vendors by zone.</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">Restaurant / Mart</label>
                                            <div class="col-7">
                                                <select id="promotion_restaurant" class="form-control"></select>
                                                <div class="form-text text-muted">Select the restaurant/mart for this promotion.</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">Product</label>
                                            <div class="col-7">
                                                <div id="product_checkbox_container" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                                    <div class="text-muted">Select a restaurant first to load products</div>
                                                </div>
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-sm btn-secondary" id="select_all_products">Select All</button>
                                                    <button type="button" class="btn btn-sm btn-secondary" id="deselect_all_products">Deselect All</button>
                                                    <span id="selected_count" class="ml-2 text-info"></span>
                                                </div>
                                                <div class="form-text text-muted">
                                                    Select one product for this promotion. The current promotion's product will be pre-selected.
                                                    <span id="actual_price_display" class="text-warning" style="display: none;"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">Item Limit</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control" id="promotion_item_limit" min="1" value="2">
                                                <div class="form-text text-muted">Maximum number of items that can be ordered with this promotion. Default: 2</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">Extra KM Charge</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control" id="promotion_extra_km_charge" min="0" value="7">
                                                <div class="form-text text-muted">Additional charge per kilometer beyond free delivery distance. Default: 7</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">Free Delivery KM</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control" id="promotion_free_delivery_km" min="0" value="3">
                                                <div class="form-text text-muted">Distance in kilometers for free delivery. Default: 3</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">Start Time</label>
                                            <div class="col-7">
                                                <input type="datetime-local" class="form-control" id="promotion_start_time">
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">End Time</label>
                                            <div class="col-7">
                                                <input type="datetime-local" class="form-control" id="promotion_end_time">
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">Payment Mode</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control" value="prepaid" id="promotion_payment_mode" readonly>
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <div class="col-7 offset-3">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="promotion_is_available">
                                                    <label class="form-check-label" for="promotion_is_available">
                                                        Available
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <div class="col-7 offset-3">
                                                <div class="form-check">
                                                    <input type="checkbox"
                                                           class="form-check-input"
                                                           id="promotion_promo"
                                                           checked>
                                                    <label class="form-check-label" for="promotion_promo">
                                                        Promotion Accepted (Promo)
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-12 text-center btm-btn">
                        <button type="button" class="btn btn-primary save-promotion-btn"><i class="fa fa-save"></i>
                            Update
                        </button>
                        <a href="{!! route('promotions') !!}" class="btn btn-default"><i class="fa fa-undo"></i>Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
<style>
    .product-row {
        background-color: #f8f9fa;
        transition: all 0.3s ease;
    }
    .product-row:hover {
        background-color: #e9ecef;
    }
    .product-special-price.is-invalid {
        border-color: #dc3545;
    }
    .product-special-price:valid {
        border-color: #28a745;
    }
</style>
<script>
var restaurantSelect = $('#promotion_restaurant');
var vtypeSelect = $('#promotion_vtype');
var zoneSelect = $('#promotion_zone');
var restaurantList = [];
var productList = [];
var promotionId = '{{ $id ?? '' }}';
var currentProductId = null; // Store the current promotion's product ID
var currentSpecialPrice = null; // Store the current promotion's special price
console.log('Promotion ID from controller:', '{{ $id ?? "NOT_SET" }}');

function populateZones(selectedId) {
    zoneSelect.empty();
    zoneSelect.append('<option value="">All Zones</option>');
    $.ajax({
        url: '{{ route('promotions.zones') }}',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                response.data.forEach(function(zone) {
                    var selected = (selectedId && zone.id === selectedId) ? 'selected' : '';
                    zoneSelect.append('<option value="' + zone.id + '" ' + selected + '>' + zone.name + '</option>');
                });
            }
        }
    });
}

function populateRestaurants(selectedId, selectedVType, selectedZoneId) {
    console.log('Populating restaurants with selected ID:', selectedId);
    restaurantSelect.empty();
    restaurantSelect.append('<option value="">Select Restaurant</option>');
    $.ajax({
        url: '{{ route('promotions.vendors') }}',
        method: 'GET',
        data: {
            vType: selectedVType,
            zoneId: selectedZoneId
        },
        success: function(response) {
            if (response.success) {
                console.log('Found', response.data.length, 'restaurants');
                restaurantList = response.data;
                response.data.forEach(function(vendor) {
                    var selected = (selectedId && vendor.id === selectedId) ? 'selected' : '';
                    restaurantSelect.append('<option value="' + vendor.id + '" data-vtype="' + (vendor.vType || '') + '" data-zoneid="' + (vendor.zoneId || '') + '" ' + selected + '>' + vendor.title + '</option>');
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading restaurants:', error);
        }
    });
}

function populateProducts(restaurantId, selectedProductId) {
    console.log('📦 Loading products for restaurant:', restaurantId, 'with selected product:', selectedProductId);
    var container = $('#product_checkbox_container');
    container.html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading products...</div>');
    $('#selected_count').text('');
    $('#actual_price_display').hide();
    
    if (!restaurantId) {
        container.html('<div class="text-muted">Select a restaurant first to load products</div>');
        return;
    }

    var selectedOption = restaurantSelect.find('option:selected');
    var vendorType = (selectedOption.data('vtype') || vtypeSelect.val() || '').toString().toLowerCase();

    console.log('🔍 Fetching products with vType:', vendorType);

    $.ajax({
        url: '{{ route('promotions.products') }}',
        method: 'GET',
        data: {
            vendor_id: restaurantId,
            vType: vendorType
        },
        success: function(response) {
            console.log('📥 Products response:', response);

            if (response.success) {
                productList = response.data;
                container.empty();

                if (response.data.length === 0) {
                    container.html('<div class="text-muted">No products found for this restaurant</div>');
                    console.warn('⚠️ No products found for vendor:', restaurantId);
                } else {
                    response.data.forEach(function(product) {
                        // Create main container for each product
                        var productContainer = $('<div class="product-item mb-2" id="product_container_' + product.id + '"></div>');

                        // Checkbox section (always visible)
                        var isSelected = (selectedProductId && product.id === selectedProductId);
                        var checkboxHtml = '<div class="form-check mb-2">' +
                            '<input class="form-check-input product-checkbox" type="checkbox" ' +
                            'id="product_' + product.id + '" ' +
                            'value="' + product.id + '" ' +
                            'data-price="' + product.price + '" ' +
                            'data-name="' + product.name + '" ' +
                            (isSelected ? 'checked' : '') + '>' +
                            '<label class="form-check-label" for="product_' + product.id + '">' +
                            '<strong>' + product.name + '</strong> <span class="text-muted">(Regular: ₹' + product.price + ')</span>' +
                            '</label>' +
                            '</div>';

                        // Price input section (hidden by default, shown when checked)
                        var priceInputSection = $('<div class="product-price-section ml-4 mb-2 p-2 border rounded" id="price_section_' + product.id + '" style="display: ' + (isSelected ? 'block' : 'none') + ';"></div>');
                        var specialPriceValue = (isSelected && currentSpecialPrice !== null) ? currentSpecialPrice : '';
                        var priceInputHtml = '<div class="form-group row mb-0">' +
                            '<label class="col-4 control-label small">Special Price:</label>' +
                            '<div class="col-8">' +
                            '<input type="number" class="form-control form-control-sm product-special-price" ' +
                            'id="special_price_' + product.id + '" ' +
                            'data-product-id="' + product.id + '" ' +
                            'min="0" step="0.01" placeholder="Enter special price" value="' + specialPriceValue + '" required>' +
                            '<small class="form-text text-muted">Regular: ₹' + product.price + '</small>' +
                            '</div>' +
                            '</div>';

                        priceInputSection.html(priceInputHtml);
                        productContainer.html(checkboxHtml);
                        productContainer.append(priceInputSection);
                        container.append(productContainer);
                    });
                    
                    console.log('✅ Loaded ' + response.data.length + ' products');
                    updateSelectedCount();
                    
                    // If a product was pre-selected, ensure only one is selected
                    if (selectedProductId) {
                        // Uncheck all others except the selected one
                        $('.product-checkbox').not('#product_' + selectedProductId).prop('checked', false);
                        $('.product-price-section').not('#price_section_' + selectedProductId).slideUp();
                        
                        var selectedProduct = response.data.find(function(p) { return p.id === selectedProductId; });
                        if (selectedProduct && selectedProduct.price > 0) {
                            $('#actual_price_display').show().text('Actual price: ₹' + selectedProduct.price);
                        }
                    }
                }
            } else {
                console.error('❌ Error loading products:', response.error);
                container.html('<div class="text-danger">Error loading products: ' + response.error + '</div>');
            }
        },
        error: function(xhr) {
            console.error('❌ Products AJAX error:', xhr);
            container.html('<div class="text-danger">Error loading products: ' + (xhr.responseJSON?.error || xhr.statusText) + '</div>');
        }
    });
}

function updateSelectedCount() {
    var selected = $('.product-checkbox:checked').length;
    if (selected > 0) {
        $('#selected_count').text(selected + ' product(s) selected');
    } else {
        $('#selected_count').text('');
    }
}

function validateProductPrices() {
    var hasErrors = false;
    $('.product-checkbox:checked').each(function() {
        var productId = $(this).val();
        var priceInput = $('#special_price_' + productId);
        var price = parseFloat(priceInput.val());

        if (!price || price <= 0) {
            priceInput.addClass('is-invalid');
            hasErrors = true;
        } else {
            priceInput.removeClass('is-invalid');
        }
    });
    return !hasErrors;
}

function formatDateTimeForInput(timestamp) {
    if (!timestamp) return '';

    try {
        // Remove quotes if present
        timestamp = timestamp.toString().replace(/"/g, '');

        let date = new Date(timestamp);

        console.log('Original timestamp:', timestamp);
        console.log('Parsed date:', date);

        // Format for datetime-local input (YYYY-MM-DDTHH:MM)
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');

        const formatted = `${year}-${month}-${day}T${hours}:${minutes}`;
        console.log('Formatted for input:', formatted);

        return formatted;
    } catch (e) {
        console.error('Error formatting date:', e);
        return timestamp;
    }
}

function loadPromotionData() {
    if (!promotionId) {
        console.log('No promotion ID provided');
        return;
    }
    console.log('🔄 Loading promotion data for ID:', promotionId);

    $.ajax({
        url: '{{ route('promotions.show', ['id' => 'PROMOTION_ID']) }}'.replace('PROMOTION_ID', promotionId),
        method: 'GET',
        success: function(response) {
            console.log('📥 Promotion data response:', response);

            if (response.success) {
                var data = response.data;
                console.log('✅ Promotion data loaded:', data);

                // Pre-fill fields
                if (data.vType) {
                    vtypeSelect.val((data.vType || '').toString().toLowerCase());
                }
                if (data.zoneId) {
                    zoneSelect.val(data.zoneId);
                }

                // Store current product info for later use
                currentProductId = data.product_id;
                currentSpecialPrice = data.special_price || 0;

                populateRestaurants(data.restaurant_id, (data.vType || '').toString().toLowerCase(), data.zoneId || '');

                setTimeout(function() {
                    populateProducts(data.restaurant_id, data.product_id);
                }, 500); // Wait for restaurant dropdown to populate

                $('#promotion_item_limit').val(data.item_limit || 2);
                $('#promotion_extra_km_charge').val(data.extra_km_charge || 7);
                $('#promotion_free_delivery_km').val(data.free_delivery_km || 3);
                $('#promotion_is_available').prop('checked', data.isAvailable ? true : false);
                $('#promotion_promo').prop('checked', data.promo ? true : false);

                if (data.start_time) {
                    $('#promotion_start_time').val(formatDateTimeForInput(data.start_time));
                }
                if (data.end_time) {
                    $('#promotion_end_time').val(formatDateTimeForInput(data.end_time));
                }

                console.log('📝 Form populated with promotion data');
            } else {
                console.error('❌ Promotion not found:', response.error);
                $('.error_top').show().html('<p>Promotion not found</p>');
            }
        },
        error: function(xhr) {
            console.error('❌ Error loading promotion data:', xhr);
            $('.error_top').show().html('<p>Error loading promotion data: ' + (xhr.responseJSON?.error || xhr.statusText) + '</p>');
        }
    });
}

$(document).ready(function () {
    console.log('Document ready, promotionId:', promotionId);

    // Input validation for numeric fields
    $('#promotion_special_price, #promotion_item_limit, #promotion_extra_km_charge, #promotion_free_delivery_km').on('input', function() {
        var value = $(this).val();
        // Remove non-numeric characters except decimal point
        value = value.replace(/[^0-9.]/g, '');
        // Ensure only one decimal point
        var parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        $(this).val(value);
    });

    populateZones('');

    if (promotionId) {
        loadPromotionData();
    } else {
        console.log('No promotion ID, just populating restaurants');
        populateRestaurants(null, '', '');
    }
    // Filters
    vtypeSelect.on('change', function() {
        var selectedVType = ($(this).val() || '').toString().toLowerCase();
        var zoneId = (zoneSelect.val() || '').toString();
        populateRestaurants(null, selectedVType, zoneId);
        $('#product_checkbox_container').html('<div class="text-muted">Select a restaurant first to load products</div>');
        $('#actual_price_display').hide();
        $('#selected_count').text('');
        currentProductId = null;
        currentSpecialPrice = null;
    });
    zoneSelect.on('change', function() {
        var selectedVType = (vtypeSelect.val() || '').toString().toLowerCase();
        var zoneId = ($(this).val() || '').toString();
        populateRestaurants(null, selectedVType, zoneId);
        $('#product_checkbox_container').html('<div class="text-muted">Select a restaurant first to load products</div>');
        $('#actual_price_display').hide();
        $('#selected_count').text('');
        currentProductId = null;
        currentSpecialPrice = null;
    });
    restaurantSelect.on('change', function() {
        var restId = $(this).val();
        // Reset current product when restaurant changes
        currentProductId = null;
        currentSpecialPrice = null;
        populateProducts(restId);
    });

    // Handle product checkbox changes - show/hide price input
    $(document).on('change', '.product-checkbox', function() {
        var productId = $(this).val();
        var priceSection = $('#price_section_' + productId);
        var priceInput = $('#special_price_' + productId);
        var isChecked = $(this).is(':checked');

        // Ensure only one product can be selected at a time (for edit, single promotion)
        if (isChecked) {
            // Uncheck all other checkboxes
            $('.product-checkbox').not(this).prop('checked', false);
            $('.product-price-section').not(priceSection).slideUp();
            
            priceSection.slideDown();
            setTimeout(function() {
                priceInput.focus();
            }, 300);

            // Show actual price for selected product
            var productPrice = $(this).data('price');
            if (productPrice && productPrice > 0) {
                $('#actual_price_display').show().text('Actual price: ₹' + productPrice);
            }
        } else {
            priceSection.slideUp();
            priceInput.val('');
            priceInput.removeClass('is-invalid');
            $('#actual_price_display').hide();
        }

        updateSelectedCount();
        validateProductPrices();
    });

    // Validate price input on change
    $(document).on('input', '.product-special-price', function() {
        var price = parseFloat($(this).val());
        if (price && price > 0) {
            $(this).removeClass('is-invalid');
        } else {
            $(this).addClass('is-invalid');
        }
        validateProductPrices();
    });

    // Select All / Deselect All buttons (for convenience, but only one should be selected)
    $('#select_all_products').on('click', function() {
        // Note: For edit, we typically only want one product, but we'll allow select all for UI consistency
        $('.product-checkbox').prop('checked', true).trigger('change');
    });

    $('#deselect_all_products').on('click', function() {
        $('.product-checkbox').prop('checked', false).trigger('change');
    });
    $('.save-promotion-btn').click(function () {
        var restaurant_id = restaurantSelect.val();
        var selectedProduct = $('.product-checkbox:checked').first();
        
        if (!selectedProduct.length) {
            $('.error_top').show().html('<p>Please select a product for this promotion.</p>');
            window.scrollTo(0, 0);
            return;
        }

        var product_id = selectedProduct.val();
        var special_price = parseFloat($('#special_price_' + product_id).val()) || 0;
        var item_limit = parseInt($('#promotion_item_limit').val()) || 2;
        var extra_km_charge = parseFloat($('#promotion_extra_km_charge').val()) || 7;
        var free_delivery_km = parseFloat($('#promotion_free_delivery_km').val()) || 3;
        var start_time = $('#promotion_start_time').val();
        var end_time = $('#promotion_end_time').val();
        var payment_mode = 'prepaid';
        var isAvailable = $('#promotion_is_available').is(':checked') ? 1 : 0;
        var promo = $('#promotion_promo').is(':checked') ? 1 : 0;

        // Resolve vType and zone to save on document
        var selectedVendorOption = restaurantSelect.find('option:selected');
        var vType = (vtypeSelect.val() || selectedVendorOption.data('vtype') || '').toString().toLowerCase();
        var zoneId = (zoneSelect.val() || selectedVendorOption.data('zoneid') || '').toString();

        if (!restaurant_id || !product_id || !start_time || !end_time) {
            $('.error_top').show().html('<p>Please fill all required fields.</p>');
            window.scrollTo(0, 0);
            return;
        }

        // Validate special price
        if (!special_price || special_price <= 0) {
            $('.error_top').show().html('<p>Please enter a valid special price for the selected product.</p>');
            window.scrollTo(0, 0);
            return;
        }

        // Validate that all selected products have special prices
        if (!validateProductPrices()) {
            $('.error_top').show().html('<p>Please enter special price for the selected product.</p>');
            window.scrollTo(0, 0);
            return;
        }

        // Get restaurant and product titles
        var restaurant_title = restaurantSelect.find('option:selected').text();
        var product_title = selectedProduct.data('name');

        // Check if end time is expired
        var endDateTime = new Date(end_time);
        var currentDateTime = new Date();
        if (endDateTime < currentDateTime) {
            isAvailable = false; // Force isAvailable to false if expired
        }

        $('.error_top').hide();
        jQuery('#data-table_processing').show();

        console.log('💾 Updating promotion:', {
            id: promotionId,
            restaurant: restaurant_title,
            product: product_title,
            vType: vType
        });

        $.ajax({
            url: '{{ route('promotions.update', ['id' => 'PROMOTION_ID']) }}'.replace('PROMOTION_ID', promotionId),
            method: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                restaurant_id: restaurant_id,
                restaurant_title: restaurant_title,
                product_id: product_id,
                product_title: product_title,
                vType: vType,
                zoneId: zoneId,
                special_price: special_price,
                item_limit: item_limit,
                extra_km_charge: extra_km_charge,
                free_delivery_km: free_delivery_km,
                start_time: start_time,
                end_time: end_time,
                payment_mode: payment_mode,
                isAvailable: isAvailable,
                promo: promo

            },
            success: function(response) {
                console.log('✅ Promotion updated successfully:', response);

                jQuery('#data-table_processing').hide();

                if (response.success) {
                    // Log activity
                    if (typeof logActivity === 'function') {
                        logActivity('promotions', 'updated', 'Updated promotion: ' + restaurant_title + ' - ' + product_title);
                    }

                    window.location.href = '{!! route('promotions') !!}';
                } else {
                    $('.error_top').show().html('<p>' + response.error + '</p>');
                    window.scrollTo(0, 0);
                }
            },
            error: function(xhr) {
                console.error('❌ Update error:', xhr);
                jQuery('#data-table_processing').hide();
                $('.error_top').show().html('<p>Error updating promotion: ' + (xhr.responseJSON?.error || xhr.statusText) + '</p>');
                window.scrollTo(0, 0);
            }
        });
    });
});
</script>
@endsection
