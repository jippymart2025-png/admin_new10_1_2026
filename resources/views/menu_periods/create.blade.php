@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Create Menu Period</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{{route('menu-periods')}}">Menu Periods</a></li>
                <li class="breadcrumb-item active">Create Menu Period</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="cat-edite-page max-width-box">
            <div class="card pb-4">
                <div class="card-header">
                    <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                        <li role="presentation" class="nav-item">
                            <a href="#menu_period_information" aria-controls="description" role="tab" data-toggle="tab"
                               class="nav-link active">Menu Period Information</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="error_top" style="display:none"></div>
                    <div class="success_top" style="display:none"></div>
                    <div class="row restaurant_payout_create" role="tabpanel">
                        <div class="restaurant_payout_create-inner tab-content">
                            <div role="tabpanel" class="tab-pane active" id="menu_period_information">
                                <fieldset>
                                    <legend>Create Menu Period</legend>
                                    <div class="form-group row width-100">
                                        <label class="col-3 control-label">Label</label>
                                        <div class="col-7">
                                            <input type="text" class="form-control menu-period-label" placeholder="e.g., Breakfast, Lunch, Dinner">
                                            <div class="form-text text-muted">Enter a descriptive label for this meal time period</div>
                                        </div>
                                    </div>
                                    <div class="form-group row width-100">
                                        <label class="col-3 control-label">From Time</label>
                                        <div class="col-7">
                                            <input type="time" class="form-control menu-period-from" required>
                                            <div class="form-text text-muted">Start time for this meal period</div>
                                        </div>
                                    </div>
                                    <div class="form-group row width-100">
                                        <label class="col-3 control-label">To Time</label>
                                        <div class="col-7">
                                            <input type="time" class="form-control menu-period-to" required>
                                            <div class="form-text text-muted">End time for this meal period</div>
                                        </div>
                                    </div>
                                    <div class="form-check row width-100">
                                        <input type="checkbox" class="menu_period_publish" id="menu_period_publish">
                                        <label class="col-3 control-label" for="menu_period_publish">Publish</label>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group col-12 text-center btm-btn">
                    <button type="button" class="btn btn-primary save-setting-btn"><i class="fa fa-save"></i>
                        {{trans('lang.save')}}
                    </button>
                    <a href="{{ route('menu-periods') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(document).ready(function () {
        $(".save-setting-btn").click(async function () {
            var label = $(".menu-period-label").val();
            var from = $(".menu-period-from").val();
            var to = $(".menu-period-to").val();
            var publish = $("#menu_period_publish").is(":checked");

            if (label == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>Please enter a label for the menu period</p>");
                window.scrollTo(0, 0);
                return false;
            }

            if (from == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>Please enter a start time</p>");
                window.scrollTo(0, 0);
                return false;
            }

            if (to == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>Please enter an end time</p>");
                window.scrollTo(0, 0);
                return false;
            }

            if (from >= to) {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>End time must be after start time</p>");
                window.scrollTo(0, 0);
                return false;
            }

            var publish = $("#menu_period_publish").is(":checked");

            console.log('💾 Creating menu period:', { label: label, from: from, to: to, publish: publish });

            jQuery("#data-table_processing").show();

            $.post({
                url: '{{ route('menu-periods.store') }}',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                data: { label: label, from: from, to: to, publish: publish ? 1 : 0 }
            })
            .done(function(response){
                console.log('✅ Menu period created successfully:', response);

                // Log activity
                if (typeof logActivity === 'function') {
                    logActivity('menu_periods', 'created', 'Created menu period: ' + label);
                }

                window.location.href = '{{ route("menu-periods")}}';
            })
            .fail(function(xhr){
                console.error('❌ Create failed:', xhr);
                jQuery("#data-table_processing").hide();
                alert('Failed to save ('+xhr.status+'): '+xhr.statusText);
            });
        });
    });
</script>
@endsection
