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
                <?php if ($id != '') { ?>
                    <li class="breadcrumb-item"><a href="{{route('restaurants.coupons',$id)}}">{{trans('lang.coupon_plural')}}</a>
                    </li>
                <?php } else { ?>
                    <li class="breadcrumb-item"><a href="{!! route('coupons') !!}">{{trans('lang.coupon_plural')}}</a>
                    </li>
                <?php } ?>
                <li class="breadcrumb-item active">{{trans('lang.coupon_create')}}</li>
            </ol>
        </div>
        <div>
            <div class="card-body">
                <div class="error_top" style="display:none"></div>
                <div class="row restaurant_payout_create">
                    <div class="restaurant_payout_create-inner">
                        <fieldset>
                            <legend>{{trans('lang.coupon_create')}}</legend>
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
                                    <input type="number" class="form-control coupon_discount">
                                    <div class="form-text text-muted">{{ trans("lang.coupon_discount_help") }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">Item Value</label>
                                <div class="col-7">
                                    <input type="number" class="form-control item_value" min="0">
                                    <div class="form-text text-muted">Minimum order value required to use this coupon (e.g., 299 for FLAT100, 30 for SAVE30)</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">Usage Limit</label>
                                <div class="col-7">
                                    <select class="form-control usage_limit">
                                        <option value="0" selected>0</option>
                                        <option value="1">1</option>
                                    </select>
                                    <div class="form-text text-muted">Set usage limit to 0 or 1.</div>
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
                            <?php if ($id == '') { ?>
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
                            <?php } ?>
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
                                    <label class="col-3 control-label" for="coupon_enabled">{{trans('lang.coupon_enabled')}}</label>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>
            <div class="form-group col-12 text-center btm-btn">
                <button type="button" class="btn btn-primary save-form-btn"><i class="fa fa-save"></i> {{
                    trans('lang.save')}}
                </button>
                <?php if ($id != '') { ?>
                    <a href="{{route('restaurants.coupons',$id)}}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
                <?php } else { ?>
                    <a href="{!! route('coupons') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet">
<script>
    var zoneIdToName = {};
    var zonesLoaded = false;
    var loadZonesPromise;
    var allZoneIds = [];
    var vendorsData = @json($vendors ?? []);

    function renderAllVendorsByType(type, selectedVendor) {
        $('#vendor_restaurant_select').empty();
        $('#vendor_restaurant_select').append($('<option></option>').attr('value', '').text('{{ trans('lang.select_restaurant') }}'));
        if (type) {
            $('#vendor_restaurant_select').append($('<option></option>').attr('value', 'ALL').text('All ' + type + 's'));
        }

        vendorsData
            .filter(v => !type || (v.vType === type))
            .forEach(v => {
                var opt = $('<option></option>').attr('value', v.id).text(v.title);
                if (selectedVendor && v.id == selectedVendor) opt.attr('selected', 'selected');
                $('#vendor_restaurant_select').append(opt);
            });

        $('#vendor_restaurant_select').prop('disabled', false);
    }

        function loadZones() {
            $.ajax({
                url: '{{ route("zone.data") }}',
                method: 'GET',
                success: function (response) {

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

                            var checkboxHtml = '<div class="form-check mb-2">' +
                                '<input class="form-check-input zone-checkbox" type="checkbox" name="zone_ids[]" value="' + zone.id + '" id="zone_' + zone.id + '">' +
                                '<label class="form-check-label" for="zone_' + zone.id + '">' + zone.name + '</label>' +
                                '</div>';

                            $('#zone-checkbox-container').append(checkboxHtml);
                        });

                    } else {
                        $('#zone-checkbox-container').html('<p>No zones available</p>');
                    }
                }
            });
        }

    function updateZoneCount() {
        let count = $('.zone-checkbox:checked').length;
        if ($('#zone_all').is(':checked')) {
            $('#zone-count').text('All zones selected');
        } else {
            $('#zone-count').text(count + ' zones selected');
        }
    }

    $(document).on('change', '.zone-checkbox', function () {
        if (!$(this).is(':checked')) {
            $('#zone_all').prop('checked', false);
        } else if ($('.zone-checkbox').length > 0 && $('.zone-checkbox:checked').length === $('.zone-checkbox').length) {
            $('#zone_all').prop('checked', true);
        }
        updateZoneCount();
    });

    $(document).on('change', '#zone_all', function () {
        var isChecked = $(this).is(':checked');
        $('.zone-checkbox').prop('checked', isChecked);
        updateZoneCount();
        if (!isChecked) {
            $('#vendor_restaurant_select')
                .html('<option value="">Select zone first</option>')
                .prop('disabled', true);
            return;
        }

        if (allZoneIds.length > 0) {
            renderAllVendorsByType($('#coupon_type').val(), $('#vendor_restaurant_select').val());
        }
    });

    $(document).ready(function(){
        jQuery("#data-table_processing").hide();
        $('#datetimepicker1 .date_picker').datepicker({ dateFormat: 'mm/dd/yyyy', startDate: new Date() });

        var vendors = @json($vendors ?? []);
        function renderVendors(type){
            $('#vendor_restaurant_select').empty();
            $('#vendor_restaurant_select').append($('<option></option>').attr('value','').text('{{trans('lang.select_restaurant')}}'));
            if(type){ $('#vendor_restaurant_select').append($('<option></option>').attr('value','ALL').text('All ' + type + 's')); }
            vendors.filter(v=>!type || (v.vType===type)).forEach(v=>{
                $('#vendor_restaurant_select').append($('<option></option>').attr('value', v.id).text(v.title));
            });
        }
        // $('#vendor_restaurant_select').html('<option value="">Select zone first</option>');
        // $('#vendor_restaurant_select').prop('disabled', true);
        loadZones(); // ✅ load on page load

        // renderVendors('');
        $('#coupon_type').on('change', function(){ renderVendors($(this).val()); });

// ✅ Select All
        $('#select-all-zones').click(function () {
            $('#zone_all').prop('checked', true);
            $('.zone-checkbox').prop('checked', true);
            updateZoneCount();
            if (allZoneIds.length > 0) {
                renderAllVendorsByType($('#coupon_type').val(), $('#vendor_restaurant_select').val());
            }
        });

// ✅ Deselect All
        $('#deselect-all-zones').click(function () {
            $('#zone_all').prop('checked', false);
            $('.zone-checkbox').prop('checked', false);
            updateZoneCount();
            loadRestaurantsByZones([]);
        });

        $(".save-form-btn").click(function(){
            $(".error_top").hide().html('');

            var code = $(".coupon_code").val();
            var discount = $(".coupon_discount").val();
            var description = $(".coupon_description").val();
            var item_value = parseInt($(".item_value").val()||'0',10);
            var usage_limit = parseInt($(".usage_limit").val()||'0',10);
            var couponType = $("#coupon_type").val();
            var selectedVendor = $('#vendor_restaurant_select').val();
            var dateValue = $(".date_picker").val();

            let selectedZones = [];

            $('.zone-checkbox:checked').each(function () {
                selectedZones.push($(this).val());
            });
            var isAllZonesSelected = $('#zone_all').is(':checked') ||
                (allZoneIds.length > 0 && selectedZones.length === allZoneIds.length);


                console.log('💾 Creating coupon - Form values:', {
                code: code,
                discount: discount,
                couponType: couponType,
                selectedVendor: selectedVendor,
                dateValue: dateValue
            });

            // Detailed validation
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
                url: '{{ route('coupons.store') }}',
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .done(function(response){
                console.log('✅ Coupon created successfully:', response);

                // Log activity
                if (typeof logActivity === 'function') {
                    logActivity('coupons', 'created', 'Created coupon: ' + code);
                }

                window.location.href = '{{ $id ? route('restaurants.coupons',$id) : route('coupons') }}';
            })
            .fail(function(xhr){
                console.error('❌ Create failed:', xhr);
                jQuery("#data-table_processing").hide();
                $(".error_top").show().html('<p>Failed ('+xhr.status+'): '+(xhr.responseJSON?.message || xhr.statusText)+'</p>');
                window.scrollTo(0,0);
            });
        });
    });
    let couponType = $('#coupon_type').val(); // 👈 ADD

    function loadRestaurantsByZones(selectedZones) {
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
                type: $('#coupon_type').val() // 👈 IMPORTANT
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function (response) {
                var currentType = $('#coupon_type').val();
                var allLabel = currentType ? ('All ' + currentType + 's') : 'All';

                $('#vendor_restaurant_select')
                    .empty()
                    .append('<option value="">Select Restaurant</option>')
                    .append('<option value="ALL">' + allLabel + '</option>')
                    .prop('disabled', false);

                if (response.data && response.data.length > 0) {
                    response.data.forEach(function (vendor) {
                        $('#vendor_restaurant_select').append(
                            `<option value="${vendor.id}">${vendor.title}</option>`
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

    $(document).on('change', '.zone-checkbox', function () {

        let selectedZones = [];

        if ($('#zone_all').is(':checked') && allZoneIds.length > 0) {
            selectedZones = allZoneIds.slice();
        } else {
        $('.zone-checkbox:checked').each(function () {
            selectedZones.push($(this).val());
        });
        }

        console.log('📍 Selected Zones:', selectedZones);

        if ($('#zone_all').is(':checked') && allZoneIds.length > 0) {
            renderAllVendorsByType($('#coupon_type').val(), $('#vendor_restaurant_select').val());
            return;
        }

        loadRestaurantsByZones(selectedZones);
    });
</script>
@endsection
