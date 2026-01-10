@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.app_settings')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.app_settings')}}</li>
                </ol>
            </div>
        </div>
        <div class="card-body">
            <div class="error_top"></div>
            <div class="row restaurant_payout_create">
                <div class="restaurant_payout_create-inner">
                    <fieldset>
                        <legend>{{trans('lang.app_settings')}}</legend>
                        
                        <!-- Force Update Checkbox -->
                        <div class="form-check width-100">
                            <input type="checkbox" class="form-check-inline" id="force_update">
                            <label class="col-5 control-label" for="force_update">Force Update</label>
                        </div>

                        <!-- Latest Version -->
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Latest Version</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="latest_version" placeholder="2.3.4">
                            </div>
                        </div>

                        <!-- Min Required Version -->
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Min Required Version</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="min_required_version" placeholder="1.0.0">
                            </div>
                        </div>

                        <!-- Update Message -->
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Update Message</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="update_message" placeholder="Please Update">
                            </div>
                        </div>

                        <!-- Update URL -->
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Update URL</label>
                            <div class="col-7">
                                <input type="url" class="form-control" id="update_url" placeholder="https://play.google.com/store/apps/details?id=com.jippymart.customer">
                            </div>
                        </div>

                        <!-- Android Version -->
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Android Version</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="android_version" placeholder="2.3.4">
                            </div>
                        </div>

                        <!-- Android Build -->
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Android Build</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="android_build" placeholder="Android build number">
                            </div>
                        </div>

                        <!-- Android Update URL -->
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Android Update URL</label>
                            <div class="col-7">
                                <input type="url" class="form-control" id="android_update_url" placeholder="update_url">
                            </div>
                        </div>

                        <!-- iOS Version -->
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">iOS Version</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="ios_version" placeholder="latest_version">
                            </div>
                        </div>

                        <!-- iOS Build -->
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">iOS Build</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="ios_build" placeholder="iOS build number">
                            </div>
                        </div>

                        <!-- iOS Update URL -->
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">iOS Update URL</label>
                            <div class="col-7">
                                <input type="url" class="form-control" id="ios_update_url" placeholder="update_url">
                            </div>
                        </div>

                        <!-- Last Updated Display -->
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Last Updated</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="last_updated_display" readonly placeholder="Will be updated automatically">
                            </div>
                        </div>

                        <input type="hidden" id="last_updated">
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
                const appGetUrl = "{{ route('api.app.settings') }}";
                const appPostUrl = "{{ route('api.app.update') }}";

                $(document).ready(function() {
                    jQuery("#data-table_processing").show();

                    $.get(appGetUrl, function(appSettings){
                        jQuery("#data-table_processing").hide();
                        try {
                            if (appSettings.force_update) { $("#force_update").prop('checked', true); }
                            $("#latest_version").val(appSettings.latest_version || '');
                            $("#min_required_version").val(appSettings.min_required_version || '');
                            $("#update_message").val(appSettings.update_message || '');
                            $("#update_url").val(appSettings.update_url || '');
                            $("#android_version").val(appSettings.android_version || '');
                            $("#android_build").val(appSettings.android_build || '');
                            $("#android_update_url").val(appSettings.android_update_url || '');
                            $("#ios_version").val(appSettings.ios_version || '');
                            $("#ios_build").val(appSettings.ios_build || '');
                            $("#ios_update_url").val(appSettings.ios_update_url || '');
                            if (appSettings.last_updated) {
                                $("#last_updated_display").val(appSettings.last_updated);
                                $("#last_updated").val(appSettings.last_updated);
                            }
                        } catch(error) { console.error('Error loading app settings:', error); }
                    });

                    $(".edit-setting-btn").click(function() {
                        var forceUpdate = $("#force_update").is(":checked");
                        var latestVersion = $("#latest_version").val();
                        var minRequiredVersion = $("#min_required_version").val();
                        var updateMessage = $("#update_message").val();
                        var updateUrl = $("#update_url").val();
                        var androidVersion = $("#android_version").val();
                        var androidBuild = $("#android_build").val();
                        var androidUpdateUrl = $("#android_update_url").val();
                        var iosVersion = $("#ios_version").val();
                        var iosBuild = $("#ios_build").val();
                        var iosUpdateUrl = $("#ios_update_url").val();

                        // Validation
                        if (!latestVersion || latestVersion === '') {
                            $(".error_top").show();
                            $(".error_top").html("<p>Please enter latest version.</p>");
                            window.scrollTo(0, 0);
                            return;
                        }

                        if (!minRequiredVersion || minRequiredVersion === '') {
                            $(".error_top").show();
                            $(".error_top").html("<p>Please enter minimum required version.</p>");
                            window.scrollTo(0, 0);
                            return;
                        }

                        if (!updateMessage || updateMessage === '') {
                            $(".error_top").show();
                            $(".error_top").html("<p>Please enter update message.</p>");
                            window.scrollTo(0, 0);
                            return;
                        }

                        if (!updateUrl || updateUrl === '') {
                            $(".error_top").show();
                            $(".error_top").html("<p>Please enter update URL.</p>");
                            window.scrollTo(0, 0);
                            return;
                        }

                        if (!androidVersion || androidVersion === '') {
                            $(".error_top").show();
                            $(".error_top").html("<p>Please enter Android version.</p>");
                            window.scrollTo(0, 0);
                            return;
                        }

                        if (!androidBuild || androidBuild === '') {
                            $(".error_top").show();
                            $(".error_top").html("<p>Please enter Android build.</p>");
                            window.scrollTo(0, 0);
                            return;
                        }

                        if (!androidUpdateUrl || androidUpdateUrl === '') {
                            $(".error_top").show();
                            $(".error_top").html("<p>Please enter Android update URL.</p>");
                            window.scrollTo(0, 0);
                            return;
                        }

                        if (!iosVersion || iosVersion === '') {
                            $(".error_top").show();
                            $(".error_top").html("<p>Please enter iOS version.</p>");
                            window.scrollTo(0, 0);
                            return;
                        }

                        if (!iosBuild || iosBuild === '') {
                            $(".error_top").show();
                            $(".error_top").html("<p>Please enter iOS build.</p>");
                            window.scrollTo(0, 0);
                            return;
                        }

                        if (!iosUpdateUrl || iosUpdateUrl === '') {
                            $(".error_top").show();
                            $(".error_top").html("<p>Please enter iOS update URL.</p>");
                            window.scrollTo(0, 0);
                            return;
                        }

                        $.post({
                            url: appPostUrl,
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: {
                                force_update: forceUpdate ? 1 : 0,
                                latest_version: latestVersion,
                                min_required_version: minRequiredVersion,
                                update_message: updateMessage,
                                update_url: updateUrl,
                                android_version: androidVersion,
                                android_build: androidBuild,
                                android_update_url: androidUpdateUrl,
                                ios_version: iosVersion,
                                ios_build: iosBuild,
                                ios_update_url: iosUpdateUrl
                            }
                        }).done(function(){
                            $(".error_top").hide();
                            alert('App settings updated successfully!');
                            window.location.href = '{{ url("settings/app/appSettings")}}';
                        }).fail(function(){
                            $(".error_top").show();
                            $(".error_top").html("<p>Error updating settings. Please try again.</p>");
                            window.scrollTo(0, 0);
                        });
                    });
                });
            </script>
@endsection
