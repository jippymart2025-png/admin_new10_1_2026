@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.mart_settings')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.mart_settings')}}</li>
                </ol>
            </div>
        </div>
        <div class="card-body">
            <div class="error_top"></div>
            <div class="row restaurant_payout_create">
                <div class="restaurant_payout_create-inner">
                    <fieldset>
                        <legend>{{trans('lang.mart_settings')}}</legend>
                        <div class="form-check width-100">
                            <input type="checkbox" class="form-check-inline" id="is_active">
                            <label class="col-5 control-label" for="is_active">Active</label>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Free Delivery Distance (km)</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="free_delivery_distance_km" placeholder="3">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Free Delivery Threshold (₹)</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="free_delivery_threshold" placeholder="199">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Per KM Charge Above Free Distance (₹)</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="per_km_charge_above_free_distance" placeholder="7">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Minimum Order Value (₹)</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="min_order_value" placeholder="99">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Minimum Order Message</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="min_order_message" placeholder="Min Item value is ₹99">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Delivery Promotion Text</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="delivery_promotion_text" placeholder="Daily">
                            </div>
                        </div>

                        <input type="hidden" id="distanceType">
                    </fieldset>
                </div>
            </div>
            <div class="form-group col-12 text-center">
                <button type="button" class="btn btn-primary edit-setting-btn"><i class="fa fa-save"></i>
                    {{trans('lang.save')}}</button>
                <a href="{{url('/dashboard')}}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
            </div>
        </div>
        @endsection
        @section('scripts')
            <script>
                const martGetUrl = "{{ route('api.mart.settings') }}";
                const martPostUrl = "{{ route('api.mart.update') }}";

                $(document).ready(function() {
                    jQuery("#data-table_processing").show();

                    $.get(martGetUrl, function(deliverySettings){
                        jQuery("#data-table_processing").hide();
                        try {
                            if (deliverySettings.is_active) { $("#is_active").prop('checked', true); }
                            $("#free_delivery_distance_km").val(deliverySettings.free_delivery_distance_km || '');
                            $("#free_delivery_threshold").val(deliverySettings.free_delivery_threshold || '');
                            $("#per_km_charge_above_free_distance").val(deliverySettings.per_km_charge_above_free_distance || '');
                            $("#min_order_value").val(deliverySettings.min_order_value || '');
                            $("#min_order_message").val(deliverySettings.min_order_message || '');
                            $("#delivery_promotion_text").val(deliverySettings.delivery_promotion_text || '');
                        } catch(error) { console.error('Error loading delivery settings:', error); }
                    });

                    $(".edit-setting-btn").click(function() {
                        var isActive = $("#is_active").is(":checked");
                        var freeDeliveryDistanceKm = $("#free_delivery_distance_km").val();
                        var freeDeliveryThreshold = $("#free_delivery_threshold").val();
                        var perKmChargeAboveFreeDistance = $("#per_km_charge_above_free_distance").val();
                        var minOrderValue = $("#min_order_value").val();
                        var minOrderMessage = $("#min_order_message").val();
                        var deliveryPromotionText = $("#delivery_promotion_text").val();

                        // Validation
                        if (!freeDeliveryDistanceKm || freeDeliveryDistanceKm === '') {
                            $(".error_top").show();
                            $(".error_top").html("<p>Please enter free delivery distance.</p>");
                            window.scrollTo(0, 0);
                            return;
                        }

                        if (!freeDeliveryThreshold || freeDeliveryThreshold === '') {
                            $(".error_top").show();
                            $(".error_top").html("<p>Please enter free delivery threshold.</p>");
                            window.scrollTo(0, 0);
                            return;
                        }

                        if (!perKmChargeAboveFreeDistance || perKmChargeAboveFreeDistance === '') {
                            $(".error_top").show();
                            $(".error_top").html("<p>Please enter per KM charge above free distance.</p>");
                            window.scrollTo(0, 0);
                            return;
                        }

                        if (!minOrderValue || minOrderValue === '') {
                            $(".error_top").show();
                            $(".error_top").html("<p>Please enter minimum order value.</p>");
                            window.scrollTo(0, 0);
                            return;
                        }

                        if (!minOrderMessage || minOrderMessage === '') {
                            $(".error_top").show();
                            $(".error_top").html("<p>Please enter minimum order message.</p>");
                            window.scrollTo(0, 0);
                            return;
                        }

                        if (!deliveryPromotionText || deliveryPromotionText === '') {
                            $(".error_top").show();
                            $(".error_top").html("<p>Please enter delivery promotion text.</p>");
                            window.scrollTo(0, 0);
                            return;
                        }

                        $.post({
                            url: martPostUrl,
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: {
                                is_active: isActive ? 1 : 0,
                                free_delivery_distance_km: parseInt(freeDeliveryDistanceKm),
                                free_delivery_threshold: parseInt(freeDeliveryThreshold),
                                per_km_charge_above_free_distance: parseInt(perKmChargeAboveFreeDistance),
                                min_order_value: parseInt(minOrderValue),
                                min_order_message: minOrderMessage,
                                delivery_promotion_text: deliveryPromotionText
                            }
                        }).done(function(){
                            $(".error_top").hide();
                            alert('Delivery settings updated successfully!');
                            window.location.href = '{{ url("settings/app/martSettings")}}';
                        }).fail(function(){
                            $(".error_top").show();
                            $(".error_top").html("<p>Error updating settings. Please try again.</p>");
                            window.scrollTo(0, 0);
                        });
                    });
                });
            </script>
@endsection
