@extends('layouts.app')
@section('content')
	<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.dine_in_future_setting')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.dine_in_future_setting')}}</li>
            </ol>
        </div>
    </div>
        <div class="card-body">
      	  <div class="row restaurant_payout_create">
            <div class="restaurant_payout_create-inner">
              <fieldset>
                <legend>{{trans('lang.dine_in_future_setting')}}</legend>
                    <div class="form-check width-100">
                      <input type="checkbox" class="form-check-inline" onclick="ShowHideDiv()" id="enable_dine_in_for_restaurant">
                        <label class="col-5 control-label" for="enable_dine_in_for_restaurant">{{ trans('lang.enable_dine_in_future')}}</label>
                    </div>
                    <div class="form-check width-100">
                      <input type="checkbox" class="form-check-inline" onclick="ShowHideDiv()" id="dine_in_customers">
                        <label class="col-5 control-label" for="dine_in_customers">{{ trans('lang.dine_in_customers')}}</label>
                    </div>
              </fieldset>
            </div>
          </div>
          <div class="form-group col-12 text-center">
            <button type="button" class="btn btn-primary edit-setting-btn" ><i class="fa fa-save"></i> {{trans('lang.save')}}</button>
            <a href="{{url('/dashboard')}}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
          </div>
        </div>
 @endsection
@section('scripts')
<script>
    const dineGetUrl = "{{ route('api.dinein.settings') }}";
    const dinePostUrl = "{{ route('api.dinein.update') }}";
    $(document).ready(function(){
        jQuery("#data-table_processing").show();
        $.get(dineGetUrl, function(resp){
          try{
            if(resp.isEnabled){ $("#enable_dine_in_for_restaurant").prop('checked',true); }
            if(resp.isEnabledForCustomer){ $("#dine_in_customers").prop('checked',true); }
          }catch(e){}
          jQuery("#data-table_processing").hide();
        });
        $(".edit-setting-btn").click(function(){
          var checkboxValue = $("#enable_dine_in_for_restaurant").is(":checked");
          var isEnabledForCustomer = $('#dine_in_customers').is(":checked");
          $.post({
            url: dinePostUrl,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: { isEnabled: checkboxValue ? 1 : 0, isEnabledForCustomer: isEnabledForCustomer ? 1 : 0 }
          }).done(function(){
            window.location.href = '{{ url("settings/app/bookTable")}}';
          }).fail(function(){
            alert('Failed to update settings');
          });
        })
    })
</script>
@endsection
