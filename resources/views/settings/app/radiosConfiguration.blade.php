@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.radios_configuration')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.radios_configuration')}}</li>
                </ol>
            </div>
        </div>
        <div class="card-body">
            <div class="error_top" style="display:none"></div>
            <div class="row restaurant_payout_create">
                <div class="restaurant_payout_create-inner">
                    <fieldset>
                        <legend>{{trans('lang.radios_configuration')}}</legend>
                        <div class="form-group row width-50">
                            <label class="col-4 control-label">{{ trans('lang.distance_type') }}</label>
                            <div class="col-7">
                                <select name="distanceType" id="distanceType" class="form-control distanceType">
                                    <option value="km">{{ trans('lang.km') }}</option>
                                    <option value="miles">{{ trans('lang.miles') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-4 control-label">{{ trans('lang.restaurantnearby_radios')}}</label>
                            <div class="col-7">
                                <div class="control-inner">
                                    <input type="number" class="form-control restaurant_near_by" required>
                                    <span></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-4 control-label">{{ trans('lang.driver_nearby_radios')}}</label>
                            <div class="col-7">
                                <div class="control-inner">
                                    <input type="number" class="form-control driver_nearby_radios" required>
                                    <span></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-4 control-label">{{ trans('lang.driverOrderAcceptRejectDuration')}}</label>
                            <div class="col-7">
                                <div class="control-inner">
                                    <input type="number" class="form-control driverOrderAcceptRejectDuration" required>
                                    <span>{{ trans('lang.second')}}</span>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary edit-setting-btn"><i
                        class="fa fa-save"></i> {{trans('lang.save')}}</button>
            <a href="{{url('/dashboard')}}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}
            </a>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        const radiusGetUrl = "{{ route('api.radius.settings') }}";
        const radiusPostUrl = "{{ route('api.radius.update') }}";
        $(document).ready(function () {
            jQuery("#data-table_processing").show();
            $('#distanceType').on('change', function () {
                const unit = $(this).val(); 
                $('.restaurant_near_by').next('span').text(unit); 
                $('.driver_nearby_radios').next('span').text(unit);
            }).trigger('change');
            $.get(radiusGetUrl, function(resp){
                try{
                    if(resp.distanceType){
                        $('.restaurant_near_by').next('span').text(resp.distanceType);
                        $('.driver_nearby_radios').next('span').text(resp.distanceType);
                        $('#distanceType').val(resp.distanceType).trigger('change');
                    }
                    $(".restaurant_near_by").val(resp.restaurantNearBy || '');
                    $(".driver_nearby_radios").val(resp.driverNearBy || '');
                    $(".driverOrderAcceptRejectDuration").val(resp.driverOrderAcceptRejectDuration || 0);
                }catch(e){}
                jQuery("#data-table_processing").hide();
            });
        });
        $(".edit-setting-btn").click(function () {
            var restaurantNearBy = $(".restaurant_near_by").val();
            var driverOrderAcceptRejectDuration = $(".driverOrderAcceptRejectDuration").val();
            var driverNearBy = $(".driver_nearby_radios").val();
            var distanceType = $("#distanceType").val();
            if (restaurantNearBy == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.enter_restaurant_nearby_error')}}</p>");
            } else if (driverNearBy == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.enter_driver_nearby_radios_error')}}</p>");
            } else if (driverOrderAcceptRejectDuration == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.driverOrderAcceptRejectDuration_error')}}</p>");
            } else {
                jQuery("#data-table_processing").show();
                $.post({
                    url: radiusPostUrl,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: {
                        restaurantNearBy: restaurantNearBy,
                        distanceType: distanceType,
                        driverNearBy: driverNearBy,
                        driverOrderAcceptRejectDuration: parseInt(driverOrderAcceptRejectDuration)
                    }
                }).done(function(){
                    window.location.href = '{{ url()->current() }}';
                }).fail(function(){
                    jQuery("#data-table_processing").hide();
                    alert('Failed to update settings');
                });
            }
        })
    </script>
@endsection
