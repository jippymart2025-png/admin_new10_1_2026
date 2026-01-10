@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            @if ($id == '')
                <h3 class="text-themecolor">{{ trans('lang.create_subscription_plan') }}</h3>
            @else
                <h3 class="text-themecolor">{{ trans('lang.edit_subscription_plan') }}</h3>
            @endif
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a
                        href="{{ url('subscription-plans') }}">{{ trans('lang.subscription_plans') }}</a>
                </li>
                @if ($id == '')
                    <li class="breadcrumb-item active">{{ trans('lang.create_subscription_plan') }}</li>
                @else
                    <li class="breadcrumb-item active">{{ trans('lang.edit_subscription_plan') }}</li>
                @endif
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card-body">
            <div class="error_top" style="display:none"></div>
            <div class="success_top" style="display:none"></div>
            <div class="row restaurant_payout_create">
                <div class="restaurant_payout_create-inner">
                    <fieldset>
                        <legend>{{ trans('lang.plan_details') }}</legend>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ trans('lang.plan_name') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="plan_name"
                                    placeholder="{{ trans('lang.enter_plan_name') }}">
                            </div>
                        </div>
                        <div class="form-group row width-100 plan_price_div">
                            <label class="col-3 control-label">{{ trans('lang.plan_price') }}</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="plan_price"
                                    placeholder="{{ trans('lang.enter_plan_price') }}" min="0">
                            </div>
                        </div>
                        {{--<div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.plan_validity_days') }}</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="plan_validity"
                                    placeholder="{{ trans('lang.ex_365') }}">
                            </div>
                        </div>--}}
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">
                                {{ trans('lang.plan_validity_days') }}
                            </label>
                            <div class="form-check width-100">
                                <input type="radio" id="monthly_days" name="expiry_type" value="30">
                                <label class="control-label" for="monthly_days">Monthly</label>
                            </div>

                            <div class="form-check width-100">
                                <input type="radio" id="yearly_days" name="expiry_type" value="365">
                                <label class="control-label" for="yearly_days">Yearly</label>
                            </div>

                            <div class="form-check width-100">
                                <input type="radio" id="none_days" name="expiry_type" value="0" checked>
                                <label class="control-label" for="none_days">None</label>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">Plan Type</label>
                                <div class="col-7">
                                    <div class="form-check">
                                        <input type="radio" name="plan_type" id="plan_subscription" value="subscription" checked>
                                        <label for="plan_subscription">Subscription Plan</label>
                                    </div>

                                    <div class="form-check">
                                        <input type="radio" name="plan_type" id="plan_commission" value="commission">
                                        <label for="plan_commission">Commission Plan</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">Commission (%)</label>
                                <div class="col-7">
                                    <input type="number"
                                           class="form-control"
                                           id="place"
                                           name="place"
                                           min="0"
                                           max="100"
                                           placeholder="Enter commission percentage">
                                </div>
                            </div>

                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.description') }}</label>
                            <div class="col-7">
                                <textarea class="form-control" id="description" rows="5"></textarea>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.order') }}</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="order"
                                    placeholder="{{ trans('lang.enter_display_order') }}">
                            </div>
                        </div>

                        <div class="form-group row width-100 status-div">
                            <div class="form-check width-100">
                                <input type="checkbox" id="status">
                                <label class="control-label" for="status">{{ trans('lang.status') }}</label>
                            </div>
                        </div>

{{--                        <div class="form-group row width-100 zone_div">--}}
{{--                            <label class="col-3 control-label">{{ trans('lang.zone') }}</label>--}}
{{--                            <div class="col-7">--}}
{{--                                <input type="text"--}}
{{--                                       class="form-control"--}}
{{--                                       id="zone"--}}
{{--                                       name="zone"--}}
{{--                                       placeholder="Enter the zone">--}}
{{--                            </div>--}}
{{--                        </div>--}}
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

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.image') }}</label>
                            <div class="col-7">
                                <input type="file" class="form-control">
                                <div class="form-text text-muted">{{ trans('lang.image') }}</div>
                            </div>
                            <div class="placeholder_img_thumb plan_image"></div>
                            <div id="uploding_image"></div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>{{ trans('lang.available_features') }}</legend>
                        <div class="form-group row width-100 subscriptionPlan-features-div">
                            <div class="form-check">
                                <input type="checkbox" id="dine_in" name="features" value="dineIn">
                                <label class="control-label" for="dine_in">{{ trans('lang.dine_in') }}</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="mobile_app" name="features" value="restaurantMobileApp">
                                <label class="control-label" for="mobile_app">{{ trans('lang.mobile_app') }}</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="generate_qr_code" name="features" value="qrCodeGenerate">
                                <label class="control-label"
                                    for="generate_qr_code">{{ trans('lang.generate_qr_code') }}</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="chat" name="features" value="chat">
                                <label class="control-label" for="chat">{{ trans('lang.chat') }}</label>
                            </div>
                        </div>

                    </fieldset>
                    <fieldset id="commissionPlan-features-div" class="d-none">
                        <legend>{{ trans('lang.plan_points') }}</legend>
                        <div class="form-group row width-100 ">
                            <div id="options-container"></div>
                            <button id="add-plan-point" onclick="addPlanPoint()"
                                class="btn btn-primary">{{ trans('lang.add_more') }}</button>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>{{ trans('lang.maximum_item_limit') }}</legend>
                        <div class="form-group row width-100">
                            <div class="form-check width-100">
                                <input type="radio" id="unlimited_item" name="set_item_limit" value="unlimited" checked>
                                <label class="control-label" for="unlimited_item">{{ trans('lang.unlimited') }}</label>
                            </div>
                            <div class="d-flex ">
                                <div class="form-check width-50 limited_item_div  ">
                                    <input type="radio" id="limited_item" name="set_item_limit" value="limited">
                                    <label class="control-label" for="limited_item">{{ trans('lang.limited') }}</label>
                                </div>
                                <div class="form-check width-50 d-none item-limit-div">
                                    <input type="number" id="item_limit" class="form-control"
                                        placeholder="{{ trans('lang.ex_1000') }}">
                                </div>
                            </div>
                            <div class="form-check width-100">
                                <input type="radio" id="none_item" name="set_item_limit" value="none">
                                <label class="control-label" for="none_item">None</label>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>{{ trans('lang.maximum_order_limit') }}</legend>
                        <div class="form-group row width-100">
                            <div class="form-check width-100">
                                <input type="radio" id="unlimited_order" name="set_order_limit" value="unlimited"
                                    checked>
                                <label class="control-label" for="unlimited_order">{{ trans('lang.unlimited') }}</label>
                            </div>
                            <div class="d-flex  ">
                                <div class="form-check width-50 limited_order_div">
                                    <input type="radio" id="limited_order" name="set_order_limit" value="limited">
                                    <label class="control-label" for="limited_order">{{ trans('lang.limited') }}</label>
                                </div>
                                <div class="form-check width-50 d-none order-limit-div">
                                    <input type="number" id="order_limit" class="form-control"
                                        placeholder="{{ trans('lang.ex_1000') }}">
                                </div>
                            </div>
                            <div class="form-check width-100">
                                <input type="radio" id="none_order" name="set_order_limit" value="none">
                                <label class="control-label" for="none_order">None</label>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary edit-form-btn"><i class="fa fa-save"></i>
                {{ trans('lang.save') }}
            </button>
            <a href="{{ url('subscription-plans') }}" class="btn btn-default"><i
                    class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
        </div>
    </div>
</div>
@endsection
@section('scripts')
    <script>
        var requestId = "{{ $id }}";

        /* ---------------- TOGGLES ---------------- */

        // $('input[name="planType"]').on('change', function () {
        //     if ($('input[name="planType"]:checked').val() === 'free') {
        //         $('.plan_price_div').addClass('d-none');
        //         $('#plan_price').val(0);
        //     } else {
        //         $('.plan_price_div').removeClass('d-none');
        //     }
        // });

        var zoneIdToName = {};
        var zonesLoaded = false;

        var loadZonesPromise = new Promise(function (resolve) {
            console.log('🔄 Loading zones from SQL...');

            $.ajax({
                url: '{{ route("zone.data") }}',
                method: 'GET',
                success: function (response) {
                    console.log('📊 Zones API response:', response);

                    $('#zone-checkbox-container').empty();

                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function (zone) {
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

        // Update zone count display
        function updateZoneCount() {
            var count = $('.zone-checkbox:checked').length;
            $('#zone-count').text(count + ' zone' + (count !== 1 ? 's' : '') + ' selected');
        }

        // Zone checkbox change event
        $(document).on('change', '.zone-checkbox', function() {
            updateZoneCount();
        });

        // Select all zones
        $('#select-all-zones').on('click', function() {
            $('.zone-checkbox').prop('checked', true);
            updateZoneCount();
        });

        // Deselect all zones
        $('#deselect-all-zones').on('click', function() {
            $('.zone-checkbox').prop('checked', false);
            updateZoneCount();
        });

        $('input[name="plan_type"]').on('change', function () {
            let type = $(this).val();

            if (type === 'commission') {
                // Commission plan rules
                $('#plan_price').val(0);
                $('.plan_price_div').addClass('d-none');

                // Unlimited expiry
                $('#none_days').prop('checked', true);

            } else {
                // Subscription plan rules
                $('.plan_price_div').removeClass('d-none');
            }
        });



        $('input[name="set_expiry_limit"]').on('change', function () {
            $('#limited_days').is(':checked')
                ? $('.expiry-limit-div').removeClass('d-none')
                : $('.expiry-limit-div').addClass('d-none');
        });

        $('input[name="set_item_limit"]').on('change', function () {
            $('#limited_item').is(':checked')
                ? $('.item-limit-div').removeClass('d-none')
                : $('.item-limit-div').addClass('d-none');
        });

        $('input[name="set_order_limit"]').on('change', function () {
            $('#limited_order').is(':checked')
                ? $('.order-limit-div').removeClass('d-none')
                : $('.order-limit-div').addClass('d-none');
        });

        /* ---------------- PLAN POINTS ---------------- */
        function addPlanPoint() {
            var index = $('#options-container input').length;
            var html = '<div class="form-group mb-2 d-flex align-items-center">' +
                '<input type="text" class="form-control mr-2" placeholder="Enter plan point" />' +
                '<button type="button" class="btn btn-danger btn-sm" onclick="removePlanPoint(this)">Remove</button>' +
                '</div>';
            $('#options-container').append(html);
        }

        function removePlanPoint(btn) {
            $(btn).closest('.form-group').remove();
        }

        function getZoneArray() {
            let selectedZones = [];
            $('.zone-checkbox:checked').each(function() {
                selectedZones.push($(this).val());
            });
            return JSON.stringify(selectedZones);
        }



        /* ---------------- LOAD DATA (EDIT) ---------------- */

        if (requestId !== '') {
            $.get("{{ url('subscription-plans') }}/" + requestId + "/json", function (data) {

                $('#plan_name').val(data.name);
                $('#plan_price').val(data.price ?? 0);
                $('#description').val(data.description);
                $('#order').val(data.place);
                $('#place').val(data.place || 0);
                $('#status').prop('checked', data.isEnable);
                $('#zone').val(
                    data.zone !== null && data.zone !== undefined && data.zone !== ''
                        ? data.zone
                        : ''
                );


                // Type
                // if (parseFloat(data.price) === 0) {
                //     $('#free_type').prop('checked', true).trigger('change');
                //     $('.plan_price_div').addClass('d-none');
                //     $('#plan_price').val(0);
                // } else {
                //     $('#paid_type').prop('checked', true).trigger('change');
                //     $('.plan_price_div').removeClass('d-none');
                //     $('#plan_price').val(data.price);
                // }

                loadZonesPromise.then(function () {
                    if (data.zone) {
                        try {
                            let zones = typeof data.zone === 'string'
                                ? JSON.parse(data.zone)
                                : data.zone;

                            if (Array.isArray(zones) && zones.length > 0) {
                                // Check all zones that are in the array
                                zones.forEach(function(zoneId) {
                                    $('#zone_' + zoneId).prop('checked', true);
                                });
                                updateZoneCount();
                            }
                        } catch (e) {
                            console.warn('Invalid zone JSON');
                        }
                    }
                });


                if (data.orderLimit === null) {
                    $('#none_order').prop('checked', true);
                    $('.order-limit-div').addClass('d-none');
                } else if (data.orderLimit !== '-1') {
                    $('#limited_order').prop('checked', true);
                    $('.order-limit-div').removeClass('d-none');
                    $('#order_limit').val(data.orderLimit);
                } else {
                    $('#unlimited_order').prop('checked', true);
                }


                // Item limit
                if (parseInt(data.itemLimit) === 0) {
                    // None
                    $('#none_item').prop('checked', true);
                    $('.item-limit-div').addClass('d-none');
                } else {
                    // Limited
                    $('#limited_item').prop('checked', true);
                    $('.item-limit-div').removeClass('d-none');
                    $('#item_limit').val(data.itemLimit);
                }


                // Expiry
                if (data.expiryDay == 30) {
                    $('#monthly_days').prop('checked', true);
                } else if (data.expiryDay == 365) {
                    $('#yearly_days').prop('checked', true);
                } else {
                    $('#none_days').prop('checked', true);
                }

                // Plan Type
                if (data.plan_type) {
                    if (data.plan_type === 'commission') {
                        $('#plan_commission').prop('checked', true).trigger('change');
                    } else {
                        $('#plan_subscription').prop('checked', true).trigger('change');
                    }
                } else {
                    // Default to subscription if not set
                    $('#plan_subscription').prop('checked', true).trigger('change');
                }

                // Features
                if (data.features) {
                    let features = typeof data.features === 'string'
                        ? JSON.parse(data.features)
                        : data.features;

                    Object.keys(features).forEach(key => {
                        $('input[name="features"][value="' + key + '"]').prop('checked', features[key]);
                    });
                }

                // Plan Points
                if (data.plan_points) {
                    let planPoints = typeof data.plan_points === 'string'
                        ? JSON.parse(data.plan_points)
                        : data.plan_points;

                    if (Array.isArray(planPoints) && planPoints.length > 0) {
                        $('#options-container').empty();
                        planPoints.forEach(function(point) {
                            var html = '<div class="form-group mb-2 d-flex align-items-center">' +
                                '<input type="text" class="form-control mr-2" value="' + point + '" placeholder="Enter plan point" />' +
                                '<button type="button" class="btn btn-danger btn-sm" onclick="removePlanPoint(this)">Remove</button>' +
                                '</div>';
                            $('#options-container').append(html);
                        });
                    }
                }

                // Image
                if (data.image) {
                    $('.plan_image').html(
                        '<img src="' + data.image + '" class="rounded" style="width:50px">'
                    );
                }
            });
        }

        function handlePlanTypeVisibility(data) {
            // Commission plan → only FREE
            if (data.place && parseInt(data.place) > 0) {
                $('#free_type').prop('checked', true);
                $('#paid_type').closest('.form-check').hide();
                $('#free_type').closest('.form-check').show();
                $('.plan_price_div').addClass('d-none');
                $('#plan_price').val(0);
            }
            // Other plans → only PAID
            else {
                $('#paid_type').prop('checked', true);
                $('#free_type').closest('.form-check').hide();
                $('#paid_type').closest('.form-check').show();
                $('.plan_price_div').removeClass('d-none');
            }
        }


        /* ---------------- SAVE ---------------- */

        $('.edit-form-btn').click(function () {
            // Basic validation
            if (!$('#plan_name').val() || $('#plan_name').val().trim() === '') {
                $('.error_top').show().html('<p class="text-danger">Please enter a plan name</p>');
                return;
            }

            let zoneValue = $('#zoneId').val() || '';

            // Validate zone (string, max 255 characters)
            if (zoneValue && zoneValue.length > 255) {
                $('.error_top').show().html('<p class="text-danger">Zone must not exceed 255 characters</p>');
                return;
            }

            // if (data.plan_type === 'commission') {
            //     $('#plan_commission').prop('checked', true).trigger('change');
            // } else {
            //     $('#plan_subscription').prop('checked', true).trigger('change');
            // }


            let fd = new FormData();

            fd.append('_token', '{{ csrf_token() }}');
            fd.append('id', requestId);
            fd.append('name', $('#plan_name').val().trim());
            fd.append('price', $('#plan_price').val() || 0);
            // fd.append('type', $('input[name="planType"]:checked').val());
            fd.append('description', $('#description').val() || '');
            // fd.append('place', $('#order').val() || 0);
            fd.append('isEnable', $('#status').is(':checked') ? 1 : 0);
            fd.append('zone', getZoneArray());
            fd.append('place', $('#place').val());
            fd.append('itemLimit', $('#item_limit').val() || 0);
            fd.append('orderLimit', $('#order_limit').val() || 0);
            fd.append('plan_type', $('input[name="plan_type"]:checked').val());


            // Expiry
            let expiryDay = parseInt(
                $('input[name="expiry_type"]:checked').val()
            ) || 0;

            fd.append('expiryDay', expiryDay);


            // Item limit
            let itemType = $('input[name="set_item_limit"]:checked').val();
            let itemLimit =
                itemType === 'limited' ? $('#item_limit').val() :
                    itemType === 'none' ? null :
                        '-1';

            fd.append('itemLimit', itemLimit);


            let orderType = $('input[name="set_order_limit"]:checked').val();
            let orderLimit =
                orderType === 'limited' ? $('#order_limit').val() :
                    orderType === 'none' ? null :
                        '-1';

            fd.append('orderLimit', orderLimit);


            // Features
            let features = {};
            $('input[name="features"]').each(function () {
                features[$(this).val()] = $(this).is(':checked');
            });
            fd.append('features', JSON.stringify(features));

            // Image
            let fileInput = document.querySelector('input[type="file"]');
            if (fileInput.files.length > 0) {
                fd.append('photo', fileInput.files[0]);
            }

            // Plan points (for commission plans)
            let planPoints = [];
            $('#options-container input[type="text"]').each(function() {
                let pointValue = $(this).val();
                if (pointValue && pointValue.trim() !== '') {
                    planPoints.push(pointValue.trim());
                }
            });
            fd.append('plan_points', JSON.stringify(planPoints));

            // Determine URL - use update route for existing plans, store for new
            let url;
            if (requestId) {
                // UPDATE
                url = "{{ url('subscription-plans/save') }}/" + requestId;
            } else {
                // CREATE - use the store route
                url = "{{ route('subscription-plans.store') }}";
            }

            console.log('Request URL:', url, 'Request ID:', requestId);

            // Show loading state
            $('.edit-form-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
            $('.error_top').hide();
            $('.success_top').hide();

            console.log('Saving plan:', {
                url: url,
                requestId: requestId,
                isUpdate: !!requestId
            });

            $.ajax({
                url: url,
                type: "POST",
                data: fd,
                processData: false,
                contentType: false,
                success: function (res) {
                    console.log('Save response:', res);

                    // Reset button state
                    $('.edit-form-btn').prop('disabled', false).html('<i class="fa fa-save"></i> {{ trans('lang.save') }}');

                    if (res && res.success) {
                        $('.success_top').show().html('<p class="text-success">Plan saved successfully! Redirecting...</p>');
                        // ✅ Redirect after successful save
                        setTimeout(function() {
                            window.location.replace("{{ route('subscription-plans.index') }}");
                        }, 500);
                    } else {
                        var errorMsg = res && res.message ? res.message : 'Save failed. Please try again.';
                        $('.error_top').show().html('<p class="text-danger">' + errorMsg + '</p>');
                    }
                },
                error: function (xhr) {
                    console.error('AJAX Error:', xhr);
                    console.error('Response:', xhr.responseText);

                    // Reset button state
                    $('.edit-form-btn').prop('disabled', false).html('<i class="fa fa-save"></i> {{ trans('lang.save') }}');

                    var errorMsg = 'Failed to save plan';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg += ': ' + xhr.responseJSON.message;
                    } else if (xhr.status === 422) {
                        errorMsg += ': Validation error. Please check all fields.';
                    } else if (xhr.status === 404) {
                        errorMsg += ': Route not found. Please check the URL.';
                    } else if (xhr.status === 500) {
                        errorMsg += ': Server error. Please try again later.';
                    }

                    $('.error_top').show().html('<p class="text-danger">' + errorMsg + '</p>');
                }
            });
        });
    </script>
@endsection
