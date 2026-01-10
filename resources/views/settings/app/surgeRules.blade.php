  @extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.surge_rules')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.surge_rules')}}</li>
                </ol>
            </div>
        </div>
        <div class="card-body">
            <div class="error_top"></div>
            <div class="row restaurant_payout_create">
                <div class="restaurant_payout_create-inner">
                    <fieldset>
                        <legend>{{trans('lang.surge_rules')}}</legend>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Bad Weather</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="bad_weather" placeholder="15" min="0" max="1000" step="1">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Rain</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="rain" placeholder="20" min="0" max="1000" step="1">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Summer</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="summer" placeholder="10" min="0" max="1000" step="1">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Admin Surge Fee</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="admin_surge_fee" placeholder="0" min="0" max="1000" step="1">
                            </div>
                        </div>
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
            <style>
                .error_top {
                    margin: 15px 0;
                    padding: 10px 15px;
                    border-radius: 4px;
                    display: none;
                }
                .error_top.alert-success {
                    background-color: #d4edda;
                    color: #155724;
                    border: 1px solid #c3e6cb;
                }
                .error_top.alert-danger {
                    background-color: #f8d7da;
                    color: #721c24;
                    border: 1px solid #f5c6cb;
                }
                .error_top p {
                    margin: 0;
                    font-weight: 500;
                }
            </style>
            <script>
                const surgeGetUrl = "{{ route('api.surge.settings') }}";
                const surgePostUrl = "{{ route('api.surge.update') }}";

                $(document).ready(function() {
                    jQuery("#data-table_processing").show();

                    $.get(surgeGetUrl, function(surgeRules){
                        jQuery("#data-table_processing").hide();
                        try {
                            $("#bad_weather").val(surgeRules.bad_weather ?? '');
                            $("#rain").val(surgeRules.rain ?? '');
                            $("#summer").val(surgeRules.summer ?? '');
                            $("#admin_surge_fee").val(surgeRules.admin_surge_fee ?? '');
                        } catch (error) { console.error('Error loading surge rules:', error); }
                    });

                    $(".edit-setting-btn").click(function() {
                        var badWeather = $("#bad_weather").val().trim();
                        var rain = $("#rain").val().trim();
                        var summer = $("#summer").val().trim();
                        var adminSurgeFee = $("#admin_surge_fee").val().trim();

                        // Convert to numbers and validate
                        var badWeatherNum = parseInt(badWeather);
                        var rainNum = parseInt(rain);
                        var summerNum = parseInt(summer);
                        var adminSurgeFeeNum = parseInt(adminSurgeFee);

                        // Enhanced validation
                        if (!badWeather || isNaN(badWeatherNum) || badWeatherNum < 0) {
                            $(".error_top").show().html("<p>Please enter a valid bad weather value (0 or greater).</p>");
                            window.scrollTo(0, 0);
                            return;
                        }
                        if (!rain || isNaN(rainNum) || rainNum < 0) {
                            $(".error_top").show().html("<p>Please enter a valid rain charge value (0 or greater).</p>");
                            window.scrollTo(0, 0);
                            return;
                        }
                        if (!summer || isNaN(summerNum) || summerNum < 0) {
                            $(".error_top").show().html("<p>Please enter a valid summer charge value (0 or greater).</p>");
                            window.scrollTo(0, 0);
                            return;
                        }
                        if (!adminSurgeFee || isNaN(adminSurgeFeeNum) || adminSurgeFeeNum < 0) {
                            $(".error_top").show().html("<p>Please enter a valid admin surge fee value (0 or greater).</p>");
                            window.scrollTo(0, 0);
                            return;
                        }

                        $.post({
                            url: surgePostUrl,
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: {
                                bad_weather: badWeatherNum,
                                rain: rainNum,
                                summer: summerNum,
                                admin_surge_fee: adminSurgeFeeNum
                            }
                        }).done(function(){
                                $(".error_top").hide();
                                // Show success message instead of redirecting
                                $(".error_top").removeClass('alert-danger').addClass('alert-success').show().html("<p><i class='fa fa-check'></i> Surge rules updated successfully!</p>");
                                window.scrollTo(0, 0);
                                
                                // Auto-hide success message after 3 seconds
                                setTimeout(function() {
                                    $(".error_top").hide();
                                }, 3000);
                            }).fail(function() {
                                $(".error_top").removeClass('alert-success').addClass('alert-danger').show().html("<p><i class='fa fa-exclamation-triangle'></i> Error updating rules. Please try again.</p>");
                                window.scrollTo(0, 0);
                            });
                    });
                });
            </script>
@endsection
