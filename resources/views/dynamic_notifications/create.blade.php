@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
             @if($id=='')
                <h3 class="text-themecolor">{{trans('lang.create_notification')}}</h3>
                @else
                <h3 class="text-themecolor">{{trans('lang.edit_notification')}}</h3>
                 @endif
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{{ url('dynamic-notification') }}">{{trans('lang.dynamic_notification')}}</a></li>
                @if($id=='')
                <li class="breadcrumb-item active">{{trans('lang.create_notification')}}</li>
                @else
                <li class="breadcrumb-item active">{{trans('lang.edit_notification')}}</li>
                @endif
            </ol>
        </div>
    </div>
    <div>
        <div class="card-body">
            <div class="error_top" style="display:none"></div>
            <div class="success_top" style="display:none"></div>
            <div class="row restaurant_payout_create">
                <div class="restaurant_payout_create-inner">
                    <fieldset>
                        <legend>{{trans('lang.notification')}}</legend>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.type')}}</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="type" readonly>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.subject')}}</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="subject">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.message')}}</label>
                            <div class="col-7">
                                <textarea class="form-control" id="message"></textarea>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary edit-setting-btn send_message"><i class="fa fa-save"></i> {{
                trans('lang.save')}}
            </button>
            <a href="{{url('/dynamic-notification')}}" class="btn btn-default"><i class="fa fa-undo"></i>{{
                trans('lang.cancel')}}</a>
        </div>
    </div>
    @endsection
    @section('scripts')
    <script>
        var requestId = "<?php echo $id; ?>";
        var id = requestId || '';
        var pagesize = 20;
        var start = '';
        var originalType = ''; // Store original type code for saving
        $(document).ready(function () {
            if (requestId != '') {
                jQuery("#data-table_processing").show();
                $.get('{{ url('api/dynamic-notification') }}/'+id, function(np){
                        $("#message").val(np.message || '');
                        $("#subject").val(np.subject || '');
                        originalType = np.type || ''; // Store original type code
                        
            var typeDisplay = '';
            if(np.type=="restaurant_rejected"){
                typeDisplay="{{trans('lang.order_rejected_by_restaurant')}}";
            }
            else if(np.type=="restaurant_accepted"){
                typeDisplay="{{trans('lang.order_accepted_by_restaurant')}}";
            }
            else if(np.type=="takeaway_completed"){
                typeDisplay="{{trans('lang.takeaway_order_completed')}}";
            }
            else if(np.type=="driver_completed"){
                typeDisplay="{{trans('lang.driver_completed_order')}}";
            }
            else if(np.type=="driver_accepted"){
                typeDisplay="{{trans('lang.driver_accepted_order')}}";
            }
            else if(np.type=="dinein_canceled"){
                typeDisplay="{{trans('lang.dine_order_book_canceled')}}";
            }
            else if(np.type=="dinein_accepted"){
                typeDisplay="{{trans('lang.dine_order_book_accepted')}}";
            }
            else if(np.type=="order_placed"){
                typeDisplay="{{trans('lang.new_order_place')}}";
            }
            else if(np.type=="dinein_placed"){
                typeDisplay="{{trans('lang.new_dine_booking')}}";
            }else if(np.type=="schedule_order"){
                typeDisplay="{{trans('lang.shedule_order')}}";
            }
            else if(np.type=="payment_received"){
                typeDisplay="{{trans('lang.pament_received')}}";
            }
            else if(np.type=="driver_reached_doorstep"){
                typeDisplay="Driver reached your Doorstep";
            } else {
                typeDisplay = np.type || '';
            }
            $('#type').val(typeDisplay);

                jQuery("#data-table_processing").hide();
                }).fail(function(){ jQuery("#data-table_processing").hide(); });
            }
        });
        $(".send_message").click(async function () {
            $(".success_top").hide();
            $(".error_top").hide();
            var message = $("#message").val();
            var subject = $("#subject").val();
            // Use stored original type code, or empty if creating new
            var type = originalType || '';
            if (subject == "") {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.please_enter_subject')}}</p>");
                window.scrollTo(0, 0);
                return false;
            } else if (message == "") {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.please_enter_message')}}</p>");
                window.scrollTo(0, 0);
                return false;
            } else {
                jQuery("#data-table_processing").show();
                $.ajax({
                    url: '{{ route('dynamic-notification.upsert') }}',
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { id: id, subject: subject, message: message, type: type }
                }).done(function(resp){
                    jQuery("#data-table_processing").hide();
                    $(".success_top").show().html("<p>{{trans('lang.notification_created_success')}}</p>");
                    window.scrollTo(0, 0);
                    window.location.href = '{{ route("dynamic-notification.index")}}';
                }).fail(function(xhr){
                    jQuery("#data-table_processing").hide();
                    $(".error_top").show().html("<p>" + (xhr.responseJSON?.message || 'Error') + "</p>");
                });
            }
        });
    </script>
    @endsection
