@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">Create Promotion</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('promotions') !!}">Promotions</a></li>
                    <li class="breadcrumb-item active">Create Promotion</li>
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
                                        <legend>Create Promotion</legend>
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
                                            <label class="col-3 control-label">Products</label>
                                            <div class="col-7">
                                                <div class="mb-2" id="product_search_wrapper" style="display: none;">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                                                        </div>
                                                        <input type="text" class="form-control form-control-sm" id="product_search_input" placeholder="Search products by name...">
                                                        <div class="input-group-append">
                                                            <button class="btn btn-sm btn-outline-secondary" type="button" id="clear_search_btn" title="Clear search">
                                                                <i class="fa fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div id="product_search_count" class="text-muted small mt-1" style="display: none;"></div>
                                                </div>
                                                <div id="product_checkbox_container" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                                    <div class="text-muted">Select a restaurant first to load products</div>
                                                </div>
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-sm btn-secondary" id="select_all_products">Select All</button>
                                                    <button type="button" class="btn btn-sm btn-secondary" id="deselect_all_products">Deselect All</button>
                                                    <span id="selected_count" class="ml-2 text-info"></span>
                                                </div>
                                                <div class="form-text text-muted">
                                                    Select one or more products for this promotion. Set individual special price for each selected product.
                                                    <span id="actual_price_display" class="text-warning" style="display: none;"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">Special Price</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control" id="promotion_special_price" min="0" step="0.01" placeholder="Enter default price (optional)">
                                                <div class="form-text text-muted">Default price for quick fill. Individual prices can be set for each product below.</div>
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
                                                    <input type="checkbox" class="form-check-input" id="promotion_is_available" checked>
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
                            Save
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

function populateRestaurants(selectedVType, selectedZoneId) {
    restaurantSelect.empty();
    restaurantSelect.append('<option value="">Select Restaurant / Mart</option>');
    $.ajax({
        url: '{{ route('promotions.vendors') }}',
        method: 'GET',
        data: {
            vType: selectedVType,
            zoneId: selectedZoneId
        },
        success: function(response) {
            if (response.success) {
                restaurantList = response.data;
                response.data.forEach(function(vendor) {
                    restaurantSelect.append('<option value="' + vendor.id + '" data-vtype="' + (vendor.vType || '') + '" data-zoneid="' + (vendor.zoneId || '') + '">' + vendor.title + '</option>');
                });
            }
        }
    });
}

function populateProducts(restaurantId) {
    var container = $('#product_checkbox_container');
    container.html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading products...</div>');
    // Show & bold actual price
    $('#actual_price_display')
        .show()
        .css('font-weight', 'bold');    $('#selected_count').text('');
    if (!restaurantId) {
        container.html('<div class="text-muted">Select a restaurant first to load products</div>');
        return;
    }

    var selectedOption = restaurantSelect.find('option:selected');
    var vendorType = (selectedOption.data('vtype') || vtypeSelect.val() || '').toString().toLowerCase();

    console.log('📦 Loading products for:', { restaurantId: restaurantId, vType: vendorType });

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
                    $('#product_search_wrapper').hide();
                    $('#product_search_count').hide();
                    console.warn('⚠️ No products found for vendor:', restaurantId);
                } else {
                    // Show search input when products are loaded
                    $('#product_search_wrapper').show();
                    $('#product_search_input').val('');
                    $('#clear_search_btn').hide(); // Hide clear button initially
                    
                    // Render all products
                    renderProducts(response.data);
                    console.log('✅ Loaded ' + response.data.length + ' products');
                    updateSelectedCount();
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

function renderProducts(products) {
    var container = $('#product_checkbox_container');
    container.empty();
    
    products.forEach(function(product) {
        // Create main container for each product
        var productContainer = $('<div class="product-item mb-2" data-product-name="' + product.name.toLowerCase() + '" id="product_container_' + product.id + '"></div>');

        // Checkbox section (always visible)
        var checkboxHtml = '<div class="form-check mb-2">' +
            '<input class="form-check-input product-checkbox" type="checkbox" ' +
            'id="product_' + product.id + '" ' +
            'value="' + product.id + '" ' +
            'data-price="' + product.price + '" ' +
            'data-name="' + product.name + '">' +
            '<label class="form-check-label" for="product_' + product.id + '">' +
            '<strong>' + product.name + '</strong> <span class="text-muted">(Regular: ₹' + product.price + ')</span>' +
            '</label>' +
            '</div>';

        // Price input section (hidden by default, shown when checked)
        var priceInputSection = $('<div class="product-price-section ml-4 mb-2 p-2 border rounded" style="display: none;" id="price_section_' + product.id + '"></div>');
        var priceInputHtml = '<div class="form-group row mb-0">' +
            '<label class="col-4 control-label small">Special Price:</label>' +
            '<div class="col-8">' +
            '<input type="number" class="form-control form-control-sm product-special-price" ' +
            'id="special_price_' + product.id + '" ' +
            'data-product-id="' + product.id + '" ' +
            'min="0" step="0.01" placeholder="Enter special price" required>' +
            '<small class="form-text text-muted">Regular: ₹' + product.price + '</small>' +
            '</div>' +
            '</div>';

        priceInputSection.html(priceInputHtml);
        productContainer.html(checkboxHtml);
        productContainer.append(priceInputSection);
        container.append(productContainer);
    });
    
    updateSearchCount();
}

function filterProducts(searchTerm) {
    if (!searchTerm || searchTerm.trim() === '') {
        // Show all products
        $('.product-item').show();
        updateSearchCount();
        return;
    }

    var term = searchTerm.toLowerCase().trim();
    var visibleCount = 0;
    
    $('.product-item').each(function() {
        var productName = $(this).data('product-name') || '';
        if (productName.includes(term)) {
            $(this).show();
            visibleCount++;
        } else {
            $(this).hide();
        }
    });
    
    updateSearchCount(visibleCount);
}

function updateSearchCount(filteredCount) {
    var totalCount = $('.product-item').length;
    var searchTerm = $('#product_search_input').val().trim();
    
    if (searchTerm === '') {
        $('#product_search_count').hide();
    } else {
        var visibleCount = filteredCount !== undefined ? filteredCount : $('.product-item:visible').length;
        if (visibleCount === 0) {
            $('#product_search_count').show().html('<span class="text-warning">No products match your search.</span>');
        } else {
            $('#product_search_count').show().html('Showing ' + visibleCount + ' of ' + totalCount + ' products');
        }
    }
}

function updateSelectedCount() {
    var selected = $('.product-checkbox:checked').length;
    if (selected > 0) {
        $('#selected_count').text(selected + ' product(s) selected');
    } else {
        $('#selected_count').text('');
    }
}

$(document).ready(function () {
    populateZones('');
    populateRestaurants('', '');

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

    // Type and Zone filters
    vtypeSelect.on('change', function() {
        var vtype = ($(this).val() || '').toString().toLowerCase();
        var zoneId = zoneSelect.val() || '';
        populateRestaurants(vtype, zoneId);
        $('#product_checkbox_container').html('<div class="text-muted">Select a restaurant first to load products</div>');
        $('#actual_price_display').hide();
        $('#selected_count').text('');
        // Reset search when filters change
        $('#product_search_input').val('');
        $('#product_search_wrapper').hide();
        $('#product_search_count').hide();
        $('#clear_search_btn').hide();
    });
    zoneSelect.on('change', function() {
        var vtype = (vtypeSelect.val() || '').toString().toLowerCase();
        var zoneId = ($(this).val() || '').toString();
        populateRestaurants(vtype, zoneId);
        $('#product_checkbox_container').html('<div class="text-muted">Select a restaurant first to load products</div>');
        $('#actual_price_display').hide();
        $('#selected_count').text('');
        // Reset search when filters change
        $('#product_search_input').val('');
        $('#product_search_wrapper').hide();
        $('#product_search_count').hide();
        $('#clear_search_btn').hide();
    });

    restaurantSelect.on('change', function() {
        var restId = $(this).val();
        populateProducts(restId);
        // Reset search when restaurant changes
        $('#product_search_input').val('');
        $('#product_search_wrapper').hide();
        $('#product_search_count').hide();
        $('#clear_search_btn').hide();
    });

    // Product search functionality
    $('#product_search_input').on('input', function() {
        var searchTerm = $(this).val();
        filterProducts(searchTerm);
        // Show/hide clear button based on search term
        var term = searchTerm.trim();
        if (term === '') {
            $('#clear_search_btn').hide();
        } else {
            $('#clear_search_btn').show();
        }
    });

    // Clear search button
    $('#clear_search_btn').on('click', function() {
        $('#product_search_input').val('');
        filterProducts('');
        $(this).hide();
        $('#product_search_input').focus();
    });

    // Clear search on escape key
    $('#product_search_input').on('keydown', function(e) {
        if (e.key === 'Escape') {
            $(this).val('');
            filterProducts('');
            $('#clear_search_btn').hide();
        }
    });

    // Handle product checkbox changes - show/hide price input
    $(document).on('change', '.product-checkbox', function() {
        var productId = $(this).val();
        var priceSection = $('#price_section_' + productId);
        var priceInput = $('#special_price_' + productId);

        if ($(this).is(':checked')) {
            priceSection.slideDown();
            // Small delay to ensure slideDown completes before focus
            setTimeout(function() {
                priceInput.focus();
            }, 300);

            // If default special price is set, use it as placeholder or value
            var defaultPrice = $('#promotion_special_price').val();
            if (defaultPrice && !priceInput.val()) {
                priceInput.val(defaultPrice);
            }
        } else {
            priceSection.slideUp();
            priceInput.val(''); // Clear price when unchecked
            priceInput.removeClass('is-invalid');
        }

        updateSelectedCount();
        validateProductPrices();
    });

    // Fill all selected products with default price
    $('#promotion_special_price').on('blur', function() {
        var defaultPrice = $(this).val();
        if (defaultPrice) {
            $('.product-checkbox:checked').each(function() {
                var productId = $(this).val();
                var priceInput = $('#special_price_' + productId);
                if (!priceInput.val()) {
                    priceInput.val(defaultPrice);
                }
            });
        }
    });

    // Validate that all selected products have special prices
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

    // Validate price input on change
    $(document).on('input', '.product-special-price', function() {
        var price = parseFloat($(this).val());
        if (price && price > 0) {
            $(this).removeClass('is-invalid');
        } else {
            $(this).addClass('is-invalid');
        }
    });

    // Select All / Deselect All buttons (only select visible/filtered products)
    $('#select_all_products').on('click', function() {
        // Only check visible products (respects search filter)
        $('.product-item:visible .product-checkbox').prop('checked', true).trigger('change');
        // Fill all with default price if available
        var defaultPrice = $('#promotion_special_price').val();
        if (defaultPrice) {
            $('.product-item:visible .product-checkbox:checked').each(function() {
                var productId = $(this).val();
                var priceInput = $('#special_price_' + productId);
                if (!priceInput.val()) {
                    priceInput.val(defaultPrice);
                }
            });
        }
    });

    $('#deselect_all_products').on('click', function() {
        // Only uncheck visible products (respects search filter)
        $('.product-item:visible .product-checkbox').prop('checked', false).trigger('change');
    });
    $('.save-promotion-btn').click(function () {
        var restaurant_id = restaurantSelect.val();
        var selectedProducts = $('.product-checkbox:checked');
        var item_limit = parseInt($('#promotion_item_limit').val()) || 2;
        var extra_km_charge = parseFloat($('#promotion_extra_km_charge').val()) || 7;
        var free_delivery_km = parseFloat($('#promotion_free_delivery_km').val()) || 3;
        var start_time = $('#promotion_start_time').val();
        var end_time = $('#promotion_end_time').val();
        var payment_mode = 'prepaid';
        var vType = (vtypeSelect.val() || '').toString().toLowerCase();
        var zoneId = zoneSelect.val() || '';
        var isAvailable = $('#promotion_is_available').is(':checked') ? 1 : 0;
        var promo = $('#promotion_promo').is(':checked') ? 1 : 0;
        console.log('✅ Checkbox value:', isAvailable ? 'Checked (1)' : 'Unchecked (0)');

        // Validation
        if (!restaurant_id) {
            $('.error_top').show().html('<p>Please select a restaurant.</p>');
            window.scrollTo(0, 0);
            return;
        }

        if (selectedProducts.length === 0) {
            $('.error_top').show().html('<p>Please select at least one product.</p>');
            window.scrollTo(0, 0);
            return;
        }

        if (!start_time || !end_time) {
            $('.error_top').show().html('<p>Please fill start time and end time.</p>');
            window.scrollTo(0, 0);
            return;
        }

        // Validate that all selected products have special prices
        if (!validateProductPrices()) {
            $('.error_top').show().html('<p>Please enter special price for all selected products.</p>');
            window.scrollTo(0, 0);
            return;
        }

        // Get restaurant title
        var restaurant_title = restaurantSelect.find('option:selected').text();

        // Check if end time is expired
        var endDateTime = new Date(end_time);
        var currentDateTime = new Date();
        if (endDateTime < currentDateTime) {
            isAvailable = false; // Force isAvailable to false if expired
        }

        $('.error_top').hide();
        jQuery('#data-table_processing').show();

        // Prepare products array with individual special prices
        var products = [];
        selectedProducts.each(function() {
            var productId = $(this).val();
            var specialPrice = parseFloat($('#special_price_' + productId).val());

            if (!specialPrice || specialPrice <= 0) {
                $('.error_top').show().html('<p>Please enter a valid special price for ' + $(this).data('name') + '.</p>');
                window.scrollTo(0, 0);
                jQuery('#data-table_processing').hide();
                return false; // Break the loop
            }

            products.push({
                id: productId,
                name: $(this).data('name'),
                price: $(this).data('price'),
                special_price: specialPrice
            });
        });

        if (products.length === 0) {
            jQuery('#data-table_processing').hide();
            return;
        }

        console.log('💾 Creating promotions:', {
            restaurant: restaurant_title,
            product_count: products.length,
            vType: vType
        });

        // Call bulk create endpoint
        $.ajax({
            url: '{{ route('promotions.bulkStore') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                restaurant_id: restaurant_id,
                restaurant_title: restaurant_title,
                products: products,
                vType: vType,
                zoneId: zoneId,
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
                console.log('✅ Promotions created successfully:', response);

                jQuery('#data-table_processing').hide();

                if (response.success) {
                    // Log activity
                    if (typeof logActivity === 'function') {
                        logActivity('promotions', 'created', 'Created ' + response.created + ' promotion(s) for ' + restaurant_title);
                    }

                    window.location.href = '{!! route('promotions') !!}';
                } else {
                    $('.error_top').show().html('<p>' + (response.error || 'Error creating promotions') + '</p>');
                    window.scrollTo(0, 0);
                }
            },
            error: function(xhr) {
                console.error('❌ Create error:', xhr);
                jQuery('#data-table_processing').hide();
                var errorMsg = xhr.responseJSON?.error || xhr.responseJSON?.message || xhr.statusText;
                $('.error_top').show().html('<p>Error creating promotions: ' + errorMsg + '</p>');
                window.scrollTo(0, 0);
            }
        });
    });
});
</script>
@endsection
