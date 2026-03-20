@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.coupon_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <?php if (isset($_GET['eid']) && $_GET['eid'] != '') { ?>
                    <li class="breadcrumb-item"><a href="{{route('restaurants.coupons',$_GET['eid'])}}">{{trans('lang.coupon_plural')}}</a>
                    </li>
                <?php } else { ?>
                    <li class="breadcrumb-item"><a href="{!! route('coupons') !!}">{{trans('lang.coupon_plural')}}</a>
                    </li>
                <?php } ?>
                <li class="breadcrumb-item active">{{trans('lang.coupon_edit')}}</li>
            </ol>
        </div>
    </div>
    <div>
        <div class="card-body">
            <div class="error_top" style="display:none"></div>
            <div class="row restaurant_payout_create">
                <div class="restaurant_payout_create-inner">
                    <fieldset>
                        <legend>{{trans('lang.coupon_edit')}}</legend>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{trans('lang.coupon_code')}}</label>
                            <div class="col-7">
                                <input type="text" type="text" class="form-control coupon_code">
                                <div class="form-text text-muted">{{ trans("lang.coupon_code_help") }}</div>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{trans('lang.coupon_discount_type')}}</label>
                            <div class="col-7">
                                    <select id="coupon_discount_type" class="form-control">
                                        <option value="Fix Price" selected>{{trans('lang.coupon_fixed')}}</option>
                                        <option value="Percentage">{{trans('lang.coupon_percent')}}</option>
                                    </select>
                                <div class="form-text text-muted">{{ trans("lang.coupon_discount_type_help") }}</div>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{trans('lang.coupon_discount')}}</label>
                            <div class="col-7">
                                <input type="number" type="text" class="form-control coupon_discount">
                                <div class="form-text text-muted">{{ trans("lang.coupon_discount_help") }}</div>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">Item Value</label>
                            <div class="col-7">
                                <input type="number" class="form-control item_value" min="0">
                                <div class="form-text text-muted">Minimum order value required to use this coupon (e.g., 299 for FLAT100, 30 for SAVE30)</div>
                            </div>
                        </div>
                        <div class="form-group row width-50" style="display: none;">
                            <label class="col-3 control-label">Usage Limit</label>
                            <div class="col-7">
                                <input type="number" class="form-control usage_limit" min="0" placeholder="0 for unlimited" value="0">
                                <div class="form-text text-muted">Maximum number of users who can use this coupon (e.g., 100 for "First-100"). Leave empty or 0 for unlimited usage.</div>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{trans('lang.coupon_expires_at')}}</label>
                            <div class="col-7">
                                <div class='input-group date' id='datetimepicker1'>
                                    <input type='text' class="form-control date_picker input-group-addon"/>
                                    <span class=""></span>
                                </div>
                                <div class="form-text text-muted">
                                    {{ trans("lang.coupon_expires_at_help") }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{trans('lang.coupon_type')}}</label>
                            <div class="col-7">
                                    <select class="form-control" id="coupon_type">
                                    <option value="" selected>select coupon type</option>
                                        <option value="restaurant">🍽️ {{trans('lang.restaurant')}}</option>
                                        <option value="mart">🛒 {{trans('lang.mart')}}</option>
                                    </select>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ trans('lang.zone') }}</label>
                            <div class="col-7">
                                <div class="zone-selection-container" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                    <div class="mb-2">
                                        <button type="button" class="btn btn-sm btn-primary" id="select-all-zones">Select All</button>
                                        <button type="button" class="btn btn-sm btn-secondary" id="deselect-all-zones">Deselect All</button>
                                        <span class="ml-2 text-muted" id="zone-count">0 zones selected</span>
                                    </div>
                                    <div id="zone-checkbox-container">
                                        <p class="text-muted">Loading zones...</p>
                                    </div>
                                </div>
                                <div class="form-text text-muted">Select one or more zones for this subscription plan.</div>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{trans('lang.coupon_restaurant_id')}}</label>
                            <div class="col-7">
                                <select id="vendor_restaurant_select" class="form-control">
                                    <option value="">{{trans('lang.select_restaurant')}}</option>
                                </select>
                                <div class="form-text text-muted">
                                    {{ trans("lang.coupon_restaurant_id_help") }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                                <div class="form-check">
                                 <input type="checkbox" class="coupon_public" id="coupon_public">
                                <label class="col-3 control-label" for="coupon_public">{{trans('lang.coupon_public')}}</label>
                                </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.coupon_description')}}</label>
                            <div class="col-7">
                                <textarea rows="12" class="form-control coupon_description"
                                          id="coupon_description"></textarea>
                                <div class="form-text text-muted">{{ trans("lang.coupon_description_help") }}</div>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.category_image')}}</label>
                            <div class="col-7">
                                <input type="file" onChange="handleFileSelect(event)">
                                <div class="placeholder_img_thumb coupon_image"></div>
                                <div id="uploding_image"></div>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <div class="form-check">
                                <input type="checkbox" class="coupon_enabled" id="coupon_enabled">
                                <label class="col-3 control-label"
                                       for="coupon_enabled">{{trans('lang.coupon_enabled')}}</label>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary edit-form-btn"><i class="fa fa-save"></i> {{
                trans('lang.save')}}
            </button>
            <?php if (isset($_GET['eid']) && $_GET['eid'] != '') { ?>
                <a href="{{route('restaurants.coupons',$_GET['eid'])}}" class="btn btn-default"><i
                            class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
            <?php } else { ?>
                <a href="{!! route('coupons') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
            <?php } ?>
        </div>
    </div>
@endsection
@section('scripts')
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet">
<script>
    var couponId = "<?php echo $id;?>";

    var zoneIdToName = {};
    var zonesLoaded = false;
    var allZoneIds = [];


    var loadZonesPromise = new Promise(function (resolve) {
        console.log('🔄 Loading zones from SQL...');

        $.ajax({
            url: '{{ route("zone.data") }}',
            method: 'GET',
            success: function (response) {
                console.log('📊 Zones API response:', response);

                $('#zone-checkbox-container').empty();
                allZoneIds = [];

                if (response.data && response.data.length > 0) {
                    var allZonesHtml = '<div class="form-check mb-2 border-bottom pb-2">' +
                        '<input class="form-check-input" type="checkbox" id="zone_all">' +
                        '<label class="form-check-label font-weight-bold" for="zone_all">All Zones</label>' +
                        '</div>';
                    $('#zone-checkbox-container').append(allZonesHtml);

                    response.data.forEach(function (zone) {
                        allZoneIds.push(String(zone.id));
                        zoneIdToName[zone.id] = zone.name;

                        var checkboxHtml = '<div class="form-check mb-2">' +
                            '<input class="form-check-input zone-checkbox" type="checkbox" name="zone_ids[]" value="' + zone.id + '" id="zone_' + zone.id + '">' +
                            '<label class="form-check-label" for="zone_' + zone.id + '">' + zone.name + '</label>' +
                            '</div>';
                        $('#zone-checkbox-container').append(checkboxHtml);
                    });

                    updateZoneCount();
                    console.log('✅ Zones loaded:', response.data.length);
                } else {
                    $('#zone-checkbox-container').html('<p class="text-muted">No zones available</p>');
                    console.warn('⚠️ No zones found');
                }

                zonesLoaded = true;
                resolve(zoneIdToName);
            },
            error: function (xhr) {
                console.error('❌ Error loading zones:', xhr);
                $('#zone-checkbox-container').html('<p class="text-danger">Failed to load zones. Please refresh the page.</p>');
                alert('Failed to load zones. Please refresh the page.');
                zonesLoaded = true;
                resolve(zoneIdToName);
            }
        });
    });
    function updateZoneCount() {
        let count = $('.zone-checkbox:checked').length;
        if ($('#zone_all').is(':checked')) {
            $('#zone-count').text('All zones selected');
        } else {
            $('#zone-count').text(count + ' zones selected');
        }
    }


    $(document).ready(function(){
        $('#datetimepicker1 .date_picker').datepicker({ dateFormat: 'mm/dd/yyyy', startDate: new Date() });
        var vendors = @json($vendors ?? []);
        function renderVendors(type, selected){
            $('#vendor_restaurant_select').empty();
            $('#vendor_restaurant_select').append($('<option></option>').attr('value','').text('{{trans('lang.select_restaurant')}}'));
            if(type){ $('#vendor_restaurant_select').append($('<option></option>').attr('value','ALL').text('All ' + type + 's')); }
            vendors.filter(v=>!type || (v.vType===type)).forEach(v=>{
                var opt = $('<option></option>').attr('value', v.id).text(v.title);
                if (selected && v.id===selected) opt.attr('selected', 'selected');
                $('#vendor_restaurant_select').append(opt);
            });
        }

        console.log('🔄 Loading coupon data for ID:', couponId);

        $.get('{{ url('coupons/json') }}/' + couponId, function(c){
            console.log('✅ Coupon data loaded:', c);

            $(".coupon_code").val(c.code);
            $("#coupon_discount_type").val(c.discountType||'Fix Price');
            $(".coupon_discount").val(parseInt(c.discount||0));
            $(".coupon_description").val(c.description||'');
            $(".item_value").val(c.item_value||0);
            $(".usage_limit").val(c.usageLimit||0);

            // Set coupon type FIRST
            $("#coupon_type").val(c.cType||'restaurant');

            // Then render vendors with the selected restaurant
            console.log('🔄 Rendering vendors for type:', c.cType, 'selected:', c.resturant_id);
            renderVendors(c.cType||'', c.resturant_id);

            if (c.isPublic) $(".coupon_public").prop('checked', true);
            if (c.isEnabled) $(".coupon_enabled").prop('checked', true);

            // Parse and format date
            if (c.expiresAt) {
                try {
                    // Handle various date formats
                    var dateStr = c.expiresAt;
                    // Extract just the date part if it has time
                    if (dateStr.indexOf(' ') > 0) {
                        dateStr = dateStr.split(' ')[0];
                    }
                    $('.date_picker').val(dateStr);
                    console.log('📅 Date set to:', dateStr);
                } catch(e) {
                    console.error('❌ Error parsing date:', e);
                }
            }

            if (c.image) {
                var imgSrc = c.image;
                if (c.image.indexOf('http') !== 0 && c.image.indexOf('storage/') !== 0) {
                    imgSrc = '{{ asset('storage') }}/' + c.image;
                } else if (c.image.indexOf('storage/') === 0) {
                    imgSrc = '{{ asset('') }}' + c.image;
                }
                $(".coupon_image").append('<img class="rounded" style="width:50px" src="'+imgSrc+'" alt="image">');
            }

            let couponZones = [];

            if (c.zone) {
                try {
                    if (c.zone === 'all') {
                        couponZones = 'all';
                    } else {
                        couponZones = JSON.parse(c.zone);

                        if (typeof couponZones === "string") {
                            couponZones = JSON.parse(couponZones);
                        }
                    }

                    console.log('📦 Coupon Zones:', couponZones);
                } catch (e) {
                    console.error('❌ Zone parse error', e);
                }
            }

            loadZonesPromise.then(function () {
                if (couponZones === 'all') {
                    $('#zone_all').prop('checked', true);
                    $('.zone-checkbox').prop('checked', true);
                    updateZoneCount();
                    loadRestaurantsByZones(allZoneIds, c.resturant_id);
                    return;
                }

                $('.zone-checkbox').each(function () {
                    let zoneId = $(this).val();

                    if (couponZones.includes(zoneId)) {
                        $(this).prop('checked', true);
                    }
                });

                updateZoneCount();

                loadRestaurantsByZones(couponZones, c.resturant_id);
            });

            console.log('📝 Form populated with coupon data');
        })
        .fail(function(xhr){
            console.error('❌ Failed to load coupon data:', xhr);
            $(".error_top").show().html('<p>Error loading coupon data</p>');
        });

        $('#coupon_type').on('change', function(){
            var type = $(this).val();
            var sel = $('.zone-checkbox:checked').map(function(){ return $(this).val(); }).get();
            if (sel.length > 0) {
                loadRestaurantsByZones(sel, $('#vendor_restaurant_select').val());
            } else {
                renderVendors(type, $('#vendor_restaurant_select').val());
            }
        });

        $('#select-all-zones').click(function () {
            $('#zone_all').prop('checked', true);
            $('.zone-checkbox').prop('checked', true);
            updateZoneCount();
            var sel = $('.zone-checkbox:checked').map(function(){ return $(this).val(); }).get();
            if (sel.length > 0) {
                loadRestaurantsByZones(sel, $('#vendor_restaurant_select').val());
            }
        });

        $('#deselect-all-zones').click(function () {
            $('#zone_all').prop('checked', false);
            $('.zone-checkbox').prop('checked', false);
            updateZoneCount();
            loadRestaurantsByZones([], null);
        });

        $(document).on('change', '.zone-checkbox', function () {
            if (!$(this).is(':checked')) {
                $('#zone_all').prop('checked', false);
            } else if ($('.zone-checkbox').length > 0 && $('.zone-checkbox:checked').length === $('.zone-checkbox').length) {
                $('#zone_all').prop('checked', true);
            }
            var selectedZones = $('.zone-checkbox:checked').map(function(){ return $(this).val(); }).get();
            console.log('📍 Selected Zones:', selectedZones);
            if (selectedZones.length === 0) {
                $('#vendor_restaurant_select')
                    .html('<option value="">Select zone first</option>')
                    .prop('disabled', true);
                return;
            }
            loadRestaurantsByZones(selectedZones, $('#vendor_restaurant_select').val());
        });

        $(document).on('change', '#zone_all', function () {
            var isChecked = $(this).is(':checked');
            $('.zone-checkbox').prop('checked', isChecked);
            updateZoneCount();

            if (!isChecked) {
                loadRestaurantsByZones([], null);
                return;
            }
            loadRestaurantsByZones(allZoneIds, $('#vendor_restaurant_select').val());
        });

        $(".edit-form-btn").click(function(){
            $(".error_top").hide().html('');


            var code = $(".coupon_code").val();
            var discount = $(".coupon_discount").val();
            var description = $(".coupon_description").val();
            var item_value = parseInt($(".item_value").val()||'0',10);
            var usage_limit = parseInt($(".usage_limit").val()||'0',10);
            var couponType = $("#coupon_type").val();
            var selectedVendor = $('#vendor_restaurant_select').val();
            var dateValue = $(".date_picker").val();

            console.log('💾 Updating coupon - Form values:', {
                code: code,
                discount: discount,
                couponType: couponType,
                selectedVendor: selectedVendor,
                dateValue: dateValue
            });

            // Validation
            if(!code) {
                $(".error_top").show().html('<p>Coupon code is required</p>');
                window.scrollTo(0,0);
                return;
            }
            if(!discount) {
                $(".error_top").show().html('<p>Discount is required</p>');
                window.scrollTo(0,0);
                return;
            }
            if(!couponType) {
                $(".error_top").show().html('<p>Coupon type is required</p>');
                window.scrollTo(0,0);
                return;
            }
            if(!selectedVendor) {
                $(".error_top").show().html('<p>Please select a restaurant/mart</p>');
                window.scrollTo(0,0);
                return;
            }
            if(!dateValue) {
                $(".error_top").show().html('<p>Expiry date is required</p>');
                window.scrollTo(0,0);
                return;
            }

            var newdate = new Date(dateValue);
            if(newdate.toString()==='Invalid Date'){
                $(".error_top").show().html('<p>Invalid expiry date format</p>');
                window.scrollTo(0,0);
                return;
            }

            var expiresAt = (newdate.getMonth()+1).toString().padStart(2,'0') + '/' + newdate.getDate().toString().padStart(2,'0') + '/' + newdate.getFullYear() + ' 11:59:59 PM';

            var selectedZones = [];
            $('.zone-checkbox:checked').each(function () {
                selectedZones.push($(this).val());
            });
            var isAllZonesSelected = $('#zone_all').is(':checked') ||
                (allZoneIds.length > 0 && selectedZones.length === allZoneIds.length);

            if (selectedZones.length === 0) {
                $(".error_top").show().html('<p>Please select at least one zone</p>');
                window.scrollTo(0,0);
                return;
            }


            console.log('📅 Formatted expiry date:', expiresAt);

            jQuery("#data-table_processing").show();

            var fd = new FormData();
            fd.append('code', code);
            fd.append('discount', discount);
            fd.append('discountType', $("#coupon_discount_type").val()||'Fix Price');
            fd.append('description', description);
            fd.append('item_value', item_value);
            fd.append('usageLimit', usage_limit);
            fd.append('expiresAt', expiresAt);
            fd.append('cType', couponType);
            fd.append('resturant_id', selectedVendor);
            fd.append('zone', isAllZonesSelected ? 'ALL' : JSON.stringify(selectedZones));
            fd.append('isPublic', $(".coupon_public").is(":checked") ? 1 : 0);
            fd.append('isEnabled', $(".coupon_enabled").is(":checked") ? 1 : 0);
            var f = document.querySelector('input[type=file]')?.files?.[0];
            if(f){
                fd.append('image', f);
                console.log('📤 Including image file:', f.name);
            }

            $.ajax({
                url: '{{ url('coupons') }}' + '/' + couponId,
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .done(function(response){
                console.log('✅ Coupon updated successfully:', response);

                // Log activity
                if (typeof logActivity === 'function') {
                    logActivity('coupons', 'updated', 'Updated coupon: ' + code);
                }

                <?php if (isset($_GET['eid']) && $_GET['eid'] != '') { ?>
                    window.location.href = "{{ route('restaurants.coupons', $_GET['eid']) }}";
                <?php } else { ?>
                    window.location.href='{{ route('coupons') }}';
                <?php } ?>
            })
            .fail(function(xhr){
                console.error('❌ Update failed:', xhr);
                jQuery("#data-table_processing").hide();
                $(".error_top").show().html('<p>Failed ('+xhr.status+'): '+(xhr.responseJSON?.message || xhr.statusText)+'</p>');
                window.scrollTo(0,0);
            });
        });
    });
    function loadRestaurantsByZones(selectedZones, selectedVendor = null) {

        if (!selectedZones || selectedZones.length === 0) {
            $('#vendor_restaurant_select')
                .html('<option value="">Select zone first</option>')
                .prop('disabled', true);
            return;
        }

        $('#vendor_restaurant_select')
            .html('<option>Loading...</option>')
            .prop('disabled', true);

        $.ajax({
            url: '{{ url("restaurants/by-zones") }}',
            method: 'POST',
            data: {
                zones: selectedZones,
                type: $('#coupon_type').val()
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function (response) {

                $('#vendor_restaurant_select')
                    .empty()
                    .append('<option value="">Select Restaurant</option>')
                    .prop('disabled', false);

                if (response.data && response.data.length > 0) {
                    response.data.forEach(function (vendor) {

                        let selected = (selectedVendor && vendor.id == selectedVendor) ? 'selected' : '';

                        $('#vendor_restaurant_select').append(
                            `<option value="${vendor.id}" ${selected}>${vendor.title}</option>`
                        );
                    });
                } else {
                    $('#vendor_restaurant_select').append('<option>No restaurants found</option>');
                }
            },
            error: function (xhr) {
                console.error('❌ Error:', xhr.responseText);

                $('#vendor_restaurant_select')
                    .html('<option>Error loading</option>')
                    .prop('disabled', false);
            }
        });
    }
</script>
@endsection

