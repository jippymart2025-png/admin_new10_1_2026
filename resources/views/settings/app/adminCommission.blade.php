@extends('layouts.app')
@section('content')
<div class="page-wrapper">
  <div class="row page-titles">
    <div class="col-md-5 align-self-center">
      <h3 class="text-themecolor">{{ trans('lang.business_model_settings')}}</h3>
    </div>
    <div class="col-md-7 align-self-center">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
        <li class="breadcrumb-item active">{{ trans('lang.business_model_settings')}}</li>
      </ol>
    </div>
  </div>
  <div class="card-body">
    <div class="row restaurant_payout_create">
      <div class="restaurant_payout_create-inner">
        <fieldset>
          <legend><i class="mr-3 mdi mdi-shopping"></i>{{ trans('lang.subscription_based_model_settings') }}</legend>
          <div class="form-group row mt-1 ">
            <div class="form-group row mt-1 ">
              <div class="col-12 switch-box">
                <div class="switch-box-inner">
                  <label class=" control-label">{{ trans('lang.subscription_based_model') }}</label>
                  <label class="switch"> <input type="checkbox" name="subscription_model" id="subscription_model"><span
                      class="slider round"></span></label>
                  <i class="text-dark fs-12 fa-solid fa fa-info" data-toggle="tooltip"
                    title="{{ trans('lang.subscription_tooltip') }}" aria-describedby="tippy-3"></i>
                </div>
              </div>
            </div>
          </div>
        </fieldset>
        <fieldset>
          <legend><i class="mr-3 mdi mdi-shopping"></i>{{trans('lang.commission_based_model_settings')}}</legend>
          <div class="form-group row width-100 switch-box">
            <div class="switch-box-inner">
              <label class=" control-label">{{ trans('lang.commission_based_model') }}</label>
              <label class="switch"> <input type="checkbox" name="enable_commission" onclick="ShowHideDiv()"
                  id="enable_commission"><span class="slider round"></span></label>
              <i class="text-dark fs-12 fa-solid fa fa-info" data-toggle="tooltip"
                title="{{ trans('lang.commission_tooltip') }}" aria-describedby="tippy-3"></i>
            </div>
          </div>
          <div class="form-group row width-50 admin_commision_detail" style="display:none">
            <label class="col-4 control-label">{{ trans('lang.commission_type')}}</label>
            <div class="col-7">
              <select class="form-control commission_type" id="commission_type">
                <option value="Percent">{{trans('lang.coupon_percent')}}</option>
                <option value="Fixed">{{trans('lang.coupon_fixed')}}</option>
              </select>
            </div>
          </div>
          <div class="form-group row width-50 admin_commision_detail" style="display:none">
            <label class="col-4 control-label">{{ trans('lang.admin_commission')}}</label>
            <div class="col-7">
              <input type="number" class="form-control commission_fix">
            </div>
          </div>
          <div class="form-group col-12 text-center">
            <button type="button" class="btn btn-primary edit-setting-btn"><i class="fa fa-save"></i>
              {{trans('lang.save')}}</button>
            <a href="{{url('/dashboard')}}" class="btn btn-default"><i
                class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
          </div>
        </fieldset>
        <fieldset>
          <legend><i class="mr-3 mdi mdi-shopping"></i>{{ trans('lang.bulk_update')}}</legend>
          <div class="form-group row width-100">
            <label class="col-3 control-label">{{ trans('lang.food_restaurant_id') }} <i
                class="text-dark fs-12 fa-solid fa fa-info" data-toggle="tooltip"
                title="{{ trans('lang.bulk_update_commission_tooltip') }}" aria-describedby="tippy-3"></i>
            </label>
            <div class="col-12">
              <select id="food_restaurant_type" class="form-control" required>
                <option value="all">{{ trans('lang.all_restaurant')}}</option>
                <option value="custom">{{ trans('lang.custom_restaurant')}}</option>
              </select>
              <select id="food_restaurant" style="display:none" multiple class="form-control mt-3" required> </select>
              <div class="form-text text-muted">
                {{ trans("lang.food_restaurant_id_help") }}
              </div>
            </div>
          </div>
          <div class="form-group row width-50">
            <label class="col-4 control-label">{{ trans('lang.commission_type')}}</label>
            <div class="col-7">
              <select class="form-control bulk_commission_type" id="bulk_commission_type">
                <option value="Percent">{{trans('lang.coupon_percent')}}</option>
                <option value="Fixed">{{trans('lang.coupon_fixed')}}</option>
              </select>
            </div>
          </div>
          <div class="form-group row width-50">
            <label class="col-4 control-label">{{ trans('lang.admin_commission')}}</label>
            <div class="col-7">
              <input type="number" value="0" class="form-control bulk_commission_fix">
            </div>
          </div>
          <div class="form-group col-12 text-center">
            <div class="col-12">
              <button type="button" id="bulk_update_btn" class="btn btn-primary edit-setting-btn"><i
                  class="fa fa-save"></i> {{ trans('lang.bulk_update')}}</button>
            </div>
          </div>
        </fieldset>
      </div>
    </div>
  </div>
  <style>
    .select2.select2-container {
      width: 100% !important;
      position: static;
      margin-top: 1rem;
    }
  </style>
  @endsection
  @section('scripts')
  <script>
    const settingsFetchUrl = "{{ route('api.admin-commission.settings') }}";
    const settingsUpdateUrl = "{{ route('api.admin-commission.update') }}";
    const subscriptionToggleUrl = "{{ route('api.admin-commission.subscription') }}";
    const vendorsAjaxUrl = "{{ route('api.admin-commission.vendors') }}";
    const bulkUpdateUrl = "{{ route('api.admin-commission.bulk-update') }}";
    var photo="";
    $(document).ready(function() {
      $('#food_restaurant_type').on('change',function() {
        if($('#food_restaurant_type').val()==='custom') {
          $('#food_restaurant').show();
          $('#food_restaurant').select2({
            placeholder: "{{trans('lang.select_restaurant')}}",
            allowClear: true,
            width: '100%',
            dropdownAutoWidth: true,
            ajax: {
              url: vendorsAjaxUrl,
              dataType: 'json',
              delay: 250,
              data: function (params) { return { q: params.term || '' }; },
              processResults: function (data) { return data; },
              cache: true
            },
            minimumInputLength: 0
          });
        } else {
          $('#food_restaurant').hide();
          $('#food_restaurant').select2('destroy');
        }
      });
      jQuery("#data-table_processing").show();
      $.get(settingsFetchUrl, function(resp){
        try {
          if(resp && resp.adminCommission){
            if(resp.adminCommission.isEnabled){
              $("#enable_commission").prop('checked',true);
              $(".admin_commision_detail").show();
            }
            $(".commission_fix").val(resp.adminCommission.fix_commission || '');
            $("#commission_type").val(resp.adminCommission.commissionType || 'Percent');
          }
          if(resp && resp.restaurant && resp.restaurant.subscription_model){
            $("#subscription_model").prop('checked', true);
          }
        }catch(e){}
        jQuery("#data-table_processing").hide();
      });

      $(document).on("click","input[name='subscription_model']",function(e) {

        var subscription_model=$("#subscription_model").is(":checked");
        var userConfirmed=confirm(subscription_model? "{{ trans('lang.enable_subscription_plan_confirm_alert')}}":"{{ trans('lang.disable_subscription_plan_confirm_alert')}}");
        if(!userConfirmed) {
          $(this).prop("checked",!subscription_model);
          return;
        }
        $.post({
          url: subscriptionToggleUrl,
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
          data: { subscription_model: subscription_model ? 1 : 0 }
        }).done(function(){
          Swal.fire('Update Complete!', subscription_model ? `Subscription model enabled.` : `Subscription model disabled.`, 'success');
        }).fail(function(){
          Swal.fire('Error','Failed to update subscription model.','error');
        });
      });
      $(".edit-setting-btn").click(function() {
        var checkboxValue=$("#enable_commission").is(":checked");
        var commission_type=$("#commission_type").val();
        var howmuch=($(".commission_fix").val() || '').toString();
        $.post({
          url: settingsUpdateUrl,
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
          data: { isEnabled: checkboxValue ? 1 : 0, commissionType: commission_type, fix_commission: howmuch }
        }).done(function(){
          Swal.fire('Update Complete!',`Successfully updated.`,'success');
        }).fail(function(){
          Swal.fire('Error','Failed to update settings.','error');
        });
      })
      $('#bulk_update_btn').on('click',async function() {
        const commissionType=$("#bulk_commission_type").val();
        const fixCommission=$(".bulk_commission_fix").val().toString();

        const foodRestaurantType=$('#food_restaurant_type').val();
        const selectedIds=$('#food_restaurant').val()||[];

        try {
          Swal.fire({title: 'Processing...',text: 'Starting',allowOutsideClick: false, didOpen: () => Swal.showLoading()});
          const scope = (foodRestaurantType === 'custom') ? 'custom' : 'all';
          $.post({
            url: bulkUpdateUrl,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: { scope: scope, vendor_ids: selectedIds, commissionType: commissionType, fix_commission: fixCommission }
          }).done(function(resp){
            Swal.close();
            Swal.fire('Update Complete!', (resp && resp.updated ? resp.updated : 0) + ' vendors updated.', 'success');
          }).fail(function(xhr){
            Swal.close();
            Swal.fire('Error', xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to update vendors.', 'error');
          });
        } catch(error) {
          Swal.fire('Error','An error occurred during the update process.','error');
          console.error('Error:',error);
        }
      });


    })

    function ShowHideDiv() {
      var checkboxValue=$("#enable_commission").is(":checked");
      if(checkboxValue) {
        $(".admin_commision_detail").show();
      } else {
        $(".admin_commision_detail").hide();
      }
    }
  </script>
  @endsection