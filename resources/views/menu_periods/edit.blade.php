@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Edit Menu Period</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{{route('menu-periods')}}">Menu Periods</a></li>
                <li class="breadcrumb-item active">Edit Menu Period</li>
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
                                    <legend>Edit Menu Period</legend>
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
    var id_menu_period = "{{ $id ?? '' }}";
    $(document).ready(function () {
        if (id_menu_period) {
            console.log('🔄 Loading menu period data for ID:', id_menu_period);

            $.get('{{ url('/menu-periods/json') }}/' + id_menu_period, function(resp){
                console.log('✅ Menu period data loaded:', resp);

                $(".menu-period-label").val(resp.label || '');
                $(".menu-period-from").val(resp.from || '');
                $(".menu-period-to").val(resp.to || '');

                if (resp.publish || resp.publish == 1 || resp.publish == '1') {
                    $("#menu_period_publish").prop('checked', true);
                }

                console.log('📝 Form populated with menu period data');
            }).fail(function(xhr){
                console.error('❌ Failed to load menu period data:', xhr);
                $(".error_top").show().html('<p>Error loading menu period data</p>');
            });
        }

        $(".save-setting-btn").click(async function () {
            var label = $(".menu-period-label").val();
            var from = $(".menu-period-from").val();
            var to = $(".menu-period-to").val();

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

            console.log('💾 Updating menu period:', { id: id_menu_period, label: label, from: from, to: to, publish: publish });

            jQuery("#data-table_processing").show();

            $.post({
                url: '{{ url('/menu-periods') }}' + '/' + id_menu_period,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                data: { label: label, from: from, to: to, publish: publish ? 1 : 0 }
            })
            .done(function(response){
                console.log('✅ Menu period updated successfully:', response);

                // Log activity
                if (typeof logActivity === 'function') {
                    logActivity('menu_periods', 'updated', 'Updated menu period: ' + label);
                }

                window.location.href = '{{ route("menu-periods")}}';
            })
            .fail(function(xhr){
                console.error('❌ Update failed:', xhr);
                jQuery("#data-table_processing").hide();
                alert('Failed to save ('+xhr.status+'): '+xhr.statusText);
            });
        });
    });
</script>
@endsection
