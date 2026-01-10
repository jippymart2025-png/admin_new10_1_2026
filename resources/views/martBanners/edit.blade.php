@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Edit Mart Banner Item</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{!! route('mart.banners') !!}">Mart Banner Items</a></li>
                <li class="breadcrumb-item active">Edit Banner</li>
            </ol>
        </div>
    </div>
    <div class="card-body">
        <div class="error_top"></div>
        <div class="row restaurant_payout_create">
            <div class="restaurant_payout_create-inner">
                <fieldset>
                    <legend>Mart Banner Item Details</legend>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">Title *</label>
                        <div class="col-7">
                            <input type="text" class="form-control title" placeholder="Enter banner title">
                        </div>
                    </div>
{{--                    <div class="form-group row width-100">--}}
{{--                        <label class="col-3 control-label">Description</label>--}}
{{--                        <div class="col-7">--}}
{{--                            <textarea class="form-control description" rows="3" placeholder="Enter banner description"></textarea>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="form-group row width-100">--}}
{{--                        <label class="col-3 control-label">Text</label>--}}
{{--                        <div class="col-7">--}}
{{--                            <textarea class="form-control text" rows="3" placeholder="Enter additional text content"></textarea>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">Set Order</label>
                        <div class="col-7">
                            <input type="number" class="form-control set_order" min="0" value="0">
                        </div>
                    </div>
                    <div class="form-group row width-100">
                        <div class="form-check width-100">
                            <input type="checkbox" id="is_publish">
                            <label class="col-3 control-label" for="is_publish">Publish</label>
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">Photo</label>
                        <input type="file" onChange="handleFileSelect(event)" class="col-7" accept="image/*">
                        <div id="uploding_image"></div>
                        <div class="placeholder_img_thumb user_image"></div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">Position</label>
                        <div class="col-7">
                            <select name="position" id="position" class="form-control">
                                <option value="top">Top</option>
                                <option value="middle">Middle</option>
                                <option value="bottom">Bottom</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row width-50" id="banner_screen">
                        <label class="col-3 control-label">Screen</label>
                        <div class="col-7">
                            <select name="screen" id="screen" class="form-control">
                                <option value="home">Home Screen</option>
                                <option value="product">Product Screen</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">Zone</label>
                        <div class="col-7">
                            <select id="zone_select" class="form-control">
                                <option value="">Select Zone (Optional)</option>
                                <!-- options populated dynamically -->
                            </select>
                            <div class="form-text text-muted">
                                Select the zone for this banner (optional)
                            </div>
                        </div>
                    </div>
                    <div class="form-group row width-100 radio-form-row d-flex" id="redirect_type_div">
                        <div class="radio-form col-md-2">
                            <input type="radio" class="redirect_type" value="store" name="redirect_type" id="store">
                            <label class="custom-control-label">Store</label>
                        </div>
                        <div class="radio-form col-md-2">
                            <input type="radio" class="redirect_type" value="product" name="redirect_type" id="product">
                            <label class="custom-control-label">Product</label>
                        </div>
                        <div class="radio-form col-md-2">
                            <input type="radio" class="redirect_type" value="mart_category" name="redirect_type" id="mart_category">
                            <label class="custom-control-label">Mart Category</label>
                        </div>
                        <div class="radio-form col-md-2">
                            <input type="radio" class="redirect_type" value="ads_link" name="redirect_type" id="ads_link">
                            <label class="custom-control-label">Ads Link</label>
                        </div>
                        <div class="radio-form col-md-2">
                            <input type="radio" class="redirect_type" value="external_link" name="redirect_type" id="external_links">
                            <label class="custom-control-label">External Link</label>
                        </div>
                    </div>
                    <div class="form-group row width-50" id="vendor_div" style="display: none;">
                        <label class="col-3 control-label">Store</label>
                        <div class="col-7">
                            <select name="storeId" id="storeId" class="form-control">
                                <option value="">Select Store</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row width-50" id="product_div" style="display: none;">
                        <label class="col-3 control-label">Product</label>
                        <div class="col-7">
                            <select name="productId" id="productId" class="form-control">
                                <option value="">Select Product</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row width-50" id="mart_category_div" style="display: none;">
                        <label class="col-3 control-label">Mart Category</label>
                        <div class="col-7">
                            <select name="martCategoryId" id="martCategoryId" class="form-control">
                                <option value="">Select Mart Category</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row width-50" id="ads_link_div" style="display: none;">
                        <label class="col-3 control-label">Ads Link</label>
                        <div class="col-7">
                            <input type="text" class="form-control" id="ads_link" placeholder="https://example.com/ads">
                        </div>
                    </div>
                    <div class="form-group row width-100" id="external_link_div" style="display: none;">
                        <label class="col-3 control-label">External Link</label>
                        <div class="col-7">
                            <input type="text" class="form-control extlink" id="external_link" placeholder="https://example.com">
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
    <div class="form-group col-12 text-center">
        <button type="button" class="btn btn-primary edit-mart-banner-btn"><i class="fa fa-save"></i> Save</button>
        <a href="{!! route('mart.banners') !!}" class="btn btn-default"><i class="fa fa-undo"></i>Cancel</a>
    </div>
</div>
@endsection
@section('scripts')
<script>
    var id = '{{ $id }}';
    function toggleRedirectUI(){
        var type = $('.redirect_type:checked').val();
        $('#vendor_div, #product_div, #mart_category_div, #ads_link_div, #external_link_div').hide();
        if (type === 'store') { $('#vendor_div').show(); }
        else if (type === 'product') { $('#product_div').show(); }
        else if (type === 'mart_category') { $('#mart_category_div').show(); }
        else if (type === 'ads_link') { $('#ads_link_div').show(); }
        else if (type === 'external_link') { $('#external_link_div').show(); }
    }

    // Load zones from SQL
    function loadZones() {
        console.log('🔄 Loading zones from SQL');
        $.ajax({
            url: '{{ route("mart.banners.zones") }}',
            method: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    console.log('✅ Zones loaded:', response.data.length);
                    $('#zone_select').html('<option value="">Select Zone (Optional)</option>');
                    response.data.forEach(function(zone) {
                        $('#zone_select').append('<option value="' + zone.id + '">' + zone.name + '</option>');
                    });

                    // After zones loaded, load banner data
                    loadBannerData();
                }
            },
            error: function(xhr) {
                console.error('❌ Error loading zones:', xhr);
                alert('Failed to load zones. Please refresh the page.');
            }
        });
    }

    // Load stores (mart vendors)
    function loadStores() {
        console.log('🔄 Loading mart stores from SQL');
        $.ajax({
            url: '{{ route("mart.banners.stores") }}',
            method: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    console.log('✅ Stores loaded:', response.data.length);
                    $('#storeId').html('<option value="">Select Store</option>');
                    response.data.forEach(function(store) {
                        $('#storeId').append('<option value="' + store.id + '">' + store.title + '</option>');
                    });
                }
            },
            error: function(xhr) {
                console.error('❌ Error loading stores:', xhr);
            }
        });
    }

    // Load products (mart products)
    function loadProducts() {
        console.log('🔄 Loading mart products from SQL');
        $.ajax({
            url: '{{ route("mart.banners.products") }}',
            method: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    console.log('✅ Products loaded:', response.data.length);
                    $('#productId').html('<option value="">Select Product</option>');
                    response.data.forEach(function(product) {
                        $('#productId').append('<option value="' + product.id + '">' + product.name + '</option>');
                    });
                }
            },
            error: function(xhr) {
                console.error('❌ Error loading products:', xhr);
            }
        });
    }

    // Load mart categories
    function loadCategories() {
        console.log('🔄 Loading mart categories from SQL');
        $.ajax({
            url: '{{ route("mart.banners.categories") }}',
            method: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    console.log('✅ Categories loaded:', response.data.length);
                    $('#martCategoryId').html('<option value="">Select Mart Category</option>');
                    response.data.forEach(function(category) {
                        $('#martCategoryId').append('<option value="' + category.id + '">' + category.title + '</option>');
                    });
                }
            },
            error: function(xhr) {
                console.error('❌ Error loading categories:', xhr);
            }
        });
    }

    // Load banner data
    function loadBannerData() {
        $.get('{{ route('mart.banners.json', ['id'=>':id']) }}'.replace(':id', id), function(data){
            console.log('✅ Mart banner data loaded:', data);
            $('.title').val(data.title || '');
            $('.set_order').val(data.set_order || 0);
            $('#is_publish').prop('checked', !!data.is_publish);
            $('#position').val(data.position || 'top');
            $('#screen').val(data.screen || 'home');
            if (data.zoneId) { $('#zone_select').val(data.zoneId); }
            if (data.redirect_type) { $(".redirect_type[value='"+data.redirect_type+"']").prop('checked', true); }
            $('#storeId').val(data.storeId || '');
            $('#productId').val(data.productId || '');
            $('#martCategoryId').val(data.martCategoryId || '');
            $('#ads_link').val(data.ads_link || '');
            $('#external_link').val(data.external_link || '');
            if (data.photo) { $(".user_image").html('<img src="'+data.photo+'" style="max-width:100px;max-height:100px;border-radius:4px;">'); }
            toggleRedirectUI();
        });
    }

    $(document).ready(function(){
        console.log('✅ Initializing Mart Banner Edit page');

        // Load all dropdowns
        loadZones(); // This will call loadBannerData() after zones are loaded
        loadStores();
        loadProducts();
        loadCategories();

        $('.redirect_type').on('change', toggleRedirectUI);

        $('.edit-mart-banner-btn').on('click', function(){
            $(".error_top").hide().html('');
            var title = $('.title').val();
            if(!title){ $(".error_top").show().html('<p>Please enter banner title</p>'); window.scrollTo(0,0); return; }
            var fd = new FormData();
            fd.append('_method','PUT');
            fd.append('title', $('.title').val() || '');
            fd.append('set_order', $('.set_order').val() || 0);
            fd.append('is_publish', $('#is_publish').is(':checked') ? 1 : 0);
            fd.append('position', $('#position').val() || 'top');
            fd.append('screen', $('#screen').val() || 'home');
            fd.append('zoneId', $('#zone_select').val() || '');
            fd.append('zoneTitle', $('#zone_select option:selected').text() || '');
            fd.append('redirect_type', $('.redirect_type:checked').val() || 'external_link');
            fd.append('storeId', $('#storeId').val() || '');
            fd.append('productId', $('#productId').val() || '');
            fd.append('martCategoryId', $('#martCategoryId').val() || '');
            fd.append('ads_link', $('#ads_link').val() || '');
            fd.append('external_link', $('#external_link').val() || '');
            var fileInput = $("input[type='file']")[0];
            if (fileInput && fileInput.files && fileInput.files[0]) { fd.append('photo', fileInput.files[0]); }

            jQuery("#data-table_processing").show();

            $.ajax({
                url: '{{ url('mart-banners') }}' + '/' + id,
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .done(function(response){
                console.log('✅ Mart banner updated:', response);

                // Log activity
                if (typeof logActivity === 'function') {
                    logActivity('mart_banners', 'updated', 'Updated mart banner: ' + title);
                }

                window.location.href = '{{ route('mart.banners') }}';
            })
            .fail(function(xhr){
                jQuery("#data-table_processing").hide();
                $(".error_top").show().html('<p>Failed ('+xhr.status+'): '+xhr.responseText+'</p>');
                window.scrollTo(0,0);
            });
        });
    });
</script>
@endsection
