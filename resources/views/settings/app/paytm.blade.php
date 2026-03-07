@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="card">
        <div class="payment-top-tab mt-3 mb-3">
            <ul class="nav nav-tabs card-header-tabs align-items-end">
                <li class="nav-item">
                    <a class="nav-link  stripe_active_label" href="{!! url('settings/payment/stripe') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_stripe')}}<span
                            class="badge ml-2"></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link cod_active_label" href="{!! url('settings/payment/cod') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_cod_short')}}<span
                            class="badge ml-2"></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link razorpay_active_label" href="{!! url('settings/payment/razorpay') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_razorpay')}}<span
                            class="badge ml-2"></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link paypal_active_label" href="{!! url('settings/payment/paypal') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_paypal')}}<span
                            class="badge ml-2"></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active paytm_active_label" href="{!! url('settings/payment/paytm') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_paytm')}}<span
                            class="badge ml-2"></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link wallet_active_label" href="{!! url('settings/payment/wallet') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_wallet')}}<span
                            class="badge ml-2"></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link payfast_active_label" href="{!! url('settings/payment/payfast') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.payfast')}}<span class="badge ml-2"></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link paystack_active_label" href="{!! url('settings/payment/paystack') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_paystack_lable')}}<span
                            class="badge ml-2"></span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link flutterWave_active_label" href="{!! url('settings/payment/flutterwave') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.flutterWave')}}<span
                            class="badge ml-2"></span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link mercadopago_active_label" href="{!! url('settings/payment/mercadopago') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.mercadopago')}}<span
                            class="badge ml-2"></span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link xendit_active_label"
                       href="{!! url('settings/payment/xendit') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_xendit')}}<span
                            class="badge ml-2"></span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link orangepay_active_label"
                       href="{!! url('settings/payment/orangepay') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_orangepay')}}<span
                            class="badge ml-2"></span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link midtrans_active_label"
                       href="{!! url('settings/payment/midtrans') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_midtrans')}}<span
                            class="badge ml-2"></span></a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="row restaurant_payout_create">
                <div class="restaurant_payout_create-inner">
                    <fieldset>
                        <legend>{{trans('lang.app_setting_paytm')}}</legend>
                        <div class="form-check width-100">
                            <input type="checkbox" class=" enable_paytm" id="enable_paytm">
                            <label class="col-3 control-label"
                                for="enable_paytm">{{trans('lang.app_setting_enable_paytm')}}</label>
                        </div>
                        <div class="form-check width-100">
                            <input type="checkbox" class=" enable_paytm_sendbox" id="enable_paytm_sendbox">
                            <label class="col-3 control-label"
                                for="enable_paytm_sendbox">{{trans('lang.app_setting_enable_sandbox_mode_paytm')}}</label>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.paytm_merchant_key')}}</label>
                            <div class="col-7">
                                <input type="password" class="form-control paytm_merchant_key">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.paytm_id')}}</label>
                            <div class="col-7">
                                <input type="password" class="form-control paytm_id">
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>{{trans('lang.withdraw_setting')}}</legend>
                        <div class="form-check width-100">
                            <div class="form-text text-muted">
                                {!! trans('lang.withdraw_setting_not_available_help') !!}
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary edit-setting-btn"><i class="fa fa-save"></i>
                {{trans('lang.save')}}</button>
            <a href="{{url('/dashboard')}}" class="btn btn-default"><i
                    class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
        $(document).ready(function () {

        // Load Paytm settings from MySQL
        $.get("{{ url('api/paytm/settings') }}", function (data) {

            if (data.isEnabled) {
                $(".enable_paytm").prop('checked', true);
                $(".paytm_active_label span")
                    .addClass('badge-success')
                    .text('Active');
            }

            if (data.isSandboxEnabled) {
                $(".enable_paytm_sendbox").prop('checked', true);
            }

            $(".paytm_merchant_key").val(data.PAYTM_MERCHANT_KEY);
            $(".paytm_id").val(data.PaytmMID);

        });

        // Save settings
        $(".edit-setting-btn").click(function () {

        $.ajax({
        url: "{{ url('api/paytm/settings') }}",
        type: "POST",
        data: {
        _token: "{{ csrf_token() }}",
        isEnabled: $(".enable_paytm").is(":checked") ? 1 : 0,
        isSandboxEnabled: $(".enable_paytm_sendbox").is(":checked") ? 1 : 0,
        PAYTM_MERCHANT_KEY: $(".paytm_merchant_key").val(),
        PaytmMID: $(".paytm_id").val()
    },
        success: function (res) {
        if (res.success) {
        alert("Paytm settings saved successfully");
        location.reload();
    }
    }
    });

    });

    });
</script>
@endsection
