@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="admin-top-section pt-4">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex top-title-section pb-4 justify-content-between">
                            <div class="d-flex top-title-left align-self-center">
                                <span class="icon mr-3"><img src="{{ asset('images/building-four.png') }}"></span>
                                <div class="top-title-breadcrumb">
                                    <h3 class="mb-0 restaurantTitle">{{trans('lang.restaurant_plural')}}</h3>
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a
                                                href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                                        <li class="breadcrumb-item"><a
                                                href="{!! route('restaurants') !!}">{{trans('lang.restaurant_plural')}}</a>
                                        </li>
                                        <li class="breadcrumb-item active">{{trans('lang.restaurant_details')}}</li>
                                    </ol>
                                </div>
                            </div>
                            <div class="d-flex top-title-right align-self-center">
                                <div class="card-header-right">
                                    <a href="javascript:void(0)" data-toggle="modal" data-target="#addWalletModal"
                                       class="btn-primary btn rounded-full add-wallate"><i
                                            class="mdi mdi-plus mr-2"></i>{{trans('lang.add_wallet_amount')}}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="resttab-sec mb-4">
                <div class="menu-tab">
                    <ul>
                        <li class="active">
                            <a href="{{route('restaurants.view', $id)}}">{{trans('lang.tab_basic')}}</a>
                        </li>
                        <li>
                            <a href="{{route('restaurants.foods', $id)}}">{{trans('lang.tab_foods')}}</a>
                        </li>
                        <li>
                            <a href="{{route('restaurants.orders', $id)}}">{{trans('lang.tab_orders')}}</a>
                        </li>
                        <li>
                            <a href="{{route('restaurants.coupons', $id)}}">{{trans('lang.tab_promos')}}</a>
                        </li>
                        <li>
                            <a href="{{route('restaurants.payout', $id)}}">{{trans('lang.tab_payouts')}}</a>
                        </li>
                        <li>
                            <a
                                href="{{route('payoutRequests.restaurants.view', $id)}}">{{trans('lang.tab_payout_request')}}</a>
                        </li>
                        <li>
                            <a href="{{route('restaurants.booktable', $id)}}">{{trans('lang.dine_in_future')}}</a>
                        </li>
                        <li id="restaurant_wallet"></li>
                        <li id="subscription_plan"></li>
                    </ul>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--1">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 rest_count" id="total_orders">06</h4>
                                    <p class="mb-0 small text-dark-2">{{trans('lang.dashboard_total_orders')}}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/active_restaurant.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--2">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 rest_active_count" id="total_earnings">$0.00</h4>
                                    <p class="mb-0 small text-dark-2">{{trans('lang.dashboard_total_earnings')}}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/total_earning.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--3">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 total_transaction" id="total_payment">$0.00</h4>
                                    <p class="mb-0 small text-dark-2">{{trans('lang.total_payments')}}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/total_payment.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--5">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 commission_earned" id="remaining_amount">$0.00</h4>
                                    <p class="mb-0 small text-dark-2">{{trans('lang.remaining_payments')}}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/remaining_payment.png') }}"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="text-dark-2 mb-0 h4">{{trans('lang.subscription_details')}}</h3>
                            <a href="javascript:void(0)" data-toggle="modal" data-target="#changeSubscriptionModal"
                               class="btn-primary btn rounded-full change-plan"><i
                                    class="mdi mdi-plus mr-2"></i>{{trans('lang.change_subscription_plan')}}</a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--9">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 plan_name"></h4>
                                    <p class="mb-0 small text-dark-2">{{trans('lang.plan_name')}}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/basic.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--5">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 number_of_days"></h4>
                                    <p class="mb-0 small text-dark-2">{{trans('lang.number_of_days')}}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/countdown.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--14">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 plan_expire_date"></h4>
                                    <p class="mb-0 small text-dark-2">{{trans('lang.expiry_date')}}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/calendar.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--6">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 plan_price"></h4>
                                    <p class="mb-0 small text-dark-2">{{trans('lang.total_price')}}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/price.png') }}"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="restaurant_info-section">
                <div class="card border">
                    <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                        <div class="card-header-title">
                            <h3 class="text-dark-2 mb-0 h4">{{trans('lang.restaurant_details')}}</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="restaurant_info_left">
                                    <div class="d-flex mb-1">
                                        <div class="sis-img restaurant_image" id="restaurant_image">
                                        </div>
                                        <div class="sis-content pl-4">
                                            <div class="sis-content-title d-flex align-items-center mb-1">
                                                <h5 class="text-dark-2 mb-0 font-18 font-semibold"><span
                                                        class="restaurant_name"></span></h5><span class="sis-review"
                                                                                                  id="restaurant_reviewcount"></span>
                                            </div>
                                            <ul class="p-0 info-list mb-0">
                                                <li class="d-flex align-items-center mb-2">
                                                    <label
                                                        class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.restaurant_phone')}}</label>
                                                    <span class="restaurant_phone"></span>
                                                </li>
                                                <li class="d-flex align-items-center mb-2">
                                                    <label
                                                        class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.restaurant_address')}}</label>
                                                    <span class="restaurant_address"></span>
                                                </li>
                                                <li class="d-flex align-items-center mb-2">
                                                    <label
                                                        class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.restaurant_cuisines')}}</label>
                                                    <span class="restaurant_cuisines"></span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label
                                                class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.restaurant_description')}}</label>
                                            <p><span class="restaurant_description"></span></p>
                                        </div>
                                        <div class="col-md-3">
                                            <label
                                                class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.wallet_Balance')}}</label>
                                            <p><span class="wallet"></span></p>
                                        </div>
                                        <div class="col-md-3">
                                            <label
                                                class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.zone')}}</label>
                                            <p><span id="zone_name"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="map-box">
                                    <div class="mapouter">
                                        <div class="gmap_canvas">
                                            <iframe class="gmap_iframe" width="100%" frameborder="0"
                                                    scrolling="no" marginheight="0" marginwidth="0"
                                                    src="https://maps.google.com/maps?width=600&amp;height=225&amp;hl=en&amp;q=University of Oxford&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"></iframe>
                                            <a
                                                href="https://sprunkiplay.com/">Sprunki Game</a></div>
                                        <style>
                                            .mapouter {
                                                position: relative;
                                                text-align: right;
                                                width: 100%;
                                                height: 225px;
                                            }

                                            .gmap_canvas {
                                                overflow: hidden;
                                                background: none !important;
                                                width: 100%;
                                                height: 225px;
                                            }

                                            .gmap_iframe {
                                                height: 225px !important;
                                            }
                                        </style>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="restaurant_info-section">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border">
                            <div
                                class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-0 h4">{{trans('lang.vendor_details')}}</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{trans('lang.name')}}</label>
                                        <p><span class="vendor_name"></span></p>
                                    </div>
                                    <div class="col-md-3">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{trans('lang.restaurant_phone')}}</label>
                                        <p><span class="vendor_phoneNumber"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{trans('lang.email')}}</label>
                                        <p><span class="vendor_email"></span></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{trans('lang.dine_in_future')}}</label>
                                        <p><span class="dine_in_future"></span></p>
                                    </div>
                                    <div class="col-md-9">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{trans('lang.restaurant_status')}}</label>
                                        <p><span class="vendor_avtive"></span></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-9">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{trans('lang.open_closed')}}</label>
                                        <p><span class="restaurant_is_open"></span></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-9">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{trans('lang.admin_commission')}}</label>
                                        <p><span class="admin_commission"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border">
                            <div
                                class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-0 h4">{{trans('lang.gallery')}}</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="restaurant_gallery">
                                    <div id="photos"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="restaurant_info-section">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border">
                            <div
                                class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-0 h4">{{trans('lang.working_hours')}}</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 Monday_working_hours">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{trans('lang.monday')}}</label>
                                    </div>
                                    <div class="col-md-3 Tuesday_working_hours">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{trans('lang.tuesday')}}</label>
                                    </div>
                                    <div class="col-md-3 Wednesday_working_hours">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{trans('lang.wednesday')}}</label>
                                    </div>
                                    <div class="col-md-3 Thursday_working_hours">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{trans('lang.thursday')}}</label>
                                    </div>
                                    <div class="col-md-3 Friday_working_hours">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{trans('lang.friday')}}</label>
                                    </div>
                                    <div class="col-md-3 Satuarday_working_hours">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{trans('lang.Saturday')}}</label>
                                    </div>
                                    <div class="col-md-3 Sunday_working_hours">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{trans('lang.sunday')}}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border">
                            <div
                                class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-0 h4">{{trans('lang.services')}}</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="restaurant_service">
                                    <ul class="p-0" id="filtershtml">
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border">
                            <div
                                class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-0 h4">{{trans('lang.active_subscription_plan')}}</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">

                                    <div class="col-md-3">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{trans('lang.plan_name')}}</label>
                                        <p><span class="plan_name"></span></p>
                                    </div>
                                    <div class="col-md-3">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{trans('lang.plan_type')}}</label>
                                        <p><span class="plan_type"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{trans('lang.plan_expires_at')}}</label>
                                        <p><span class="plan_expire_at"></span></p>
                                    </div>
                                    <div class="col-md-3">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{trans('lang.order_limit')}}</label>
                                        <p><span class="order_limit"></span></p>
                                    </div>
                                    <div class="col-md-3">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{trans('lang.item_limit')}}</label>
                                        <p><span class="item_limit"></span></p>
                                    </div>
                                    <div class="col-md-3 update-limit-div" style="display:none">
                                        <a href="javascript:void(0)" data-toggle="modal" data-target="#updateLimitModal"
                                           class="btn-primary btn rounded-full update-limit">{{trans('lang.update_plan_limit')}}</a>
                                    </div>
                                    <div class="col-md-6">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{trans('lang.available_features')}}</label>
                                        <p><span class="plan_features"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="addWalletModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered location_modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title locationModalTitle">{{trans('lang.add_wallet_amount')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="">
                        <div class="form-row">
                            <div class="form-group row">
                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{trans('lang.amount')}}</label>
                                    <div class="col-12">
                                        <input type="number" name="amount" class="form-control" id="amount">
                                        <div id="wallet_error" style="color:red"></div>
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{trans('lang.note')}}</label>
                                    <div class="col-12">
                                        <input type="text" name="note" class="form-control" id="note">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <div id="user_account_not_found_error" class="align-items-center" style="color:red">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary save-form-btn">{{trans('submit')}}</a></button>
                        <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                            {{trans('close')}}</a>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="changeSubscriptionModal" tabindex="-1" role="dialog" aria-hidden="true"
         style="width: 100%">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="text-dark-2 h5 mb-0">{{ trans('lang.business_plans') }}</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-lg-12 ml-lg-auto mr-lg-auto">
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex top-title-section pb-4 mb-2 justify-content-between">
                                        <div class="d-flex top-title-left align-start-center">
                                            <div class="top-title">
                                                <h3 class="mb-0">{{ trans('lang.choose_your_business_plan') }}</h3>
                                                <p class="mb-0 text-dark-2">
                                                    {{ trans('lang.choose_your_business_plan_description') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="row" id="default-plan"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <div class="w-100 d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-danger rounded-full" onclick="removeSubscriptionPlan()"
                                id="remove-plan-btn" style="display: none;">
                            <i class="mdi mdi-delete mr-2"></i>Remove Plan
                        </button>
                        <button type="button" class="btn btn-secondary rounded-full ml-auto" data-dismiss="modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="checkoutSubscriptionModal" tabindex="-1" role="dialog" aria-hidden="true"
         style="width: 100%">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="text-dark-2 h5 mb-0">{{ trans('lang.shift_to_plan') }}</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <form class="">
                        <div class="subscription-section">
                            <div class="subscription-section-inner">
                                <div class="card-body">
                                    <div class="row" id="plan-details"></div>
                                    <div class="pay-method-section pt-4">
                                        <h6 class="text-dark-2 h6 mb-3 pb-3">{{ trans('lang.pay_via_online') }}</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="pay-method-box d-flex align-items-center">
                                                    <div class="pay-method-icon">
                                                        <img src="{{ asset('images/wallet_icon_ic.png') }}">
                                                    </div>
                                                    <div class="form-check">
                                                        <h6 class="text-dark-2 h6 mb-0">{{ trans('lang.manual_pay') }}</h6>
                                                        <input type="radio" id="manual_pay" name="payment_method"
                                                               value="manual_pay" checked="">
                                                        <label class="control-label mb-0" for="manual_pay"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer border-top">
                                    <div class="align-items-center justify-content-between">
                                        <div class="edit-form-group btm-btn text-right">
                                            <div class="card-block-active-plan">
                                                <a href="" class="btn btn-default rounded-full mr-2"
                                                   data-dismiss="modal">{{ trans('lang.cancel_plan') }}</a>
                                                <input type="hidden" id="plan_id" name="plan_id" value="">
                                                <button type="button" class="btn-primary btn rounded-full mr-2"
                                                        onclick="assignSubscriptionPlan()">Assign Subscription Plan
                                                </button>
                                                <button type="button" class="btn-primary btn rounded-full"
                                                        onclick="finalCheckout()">{{ trans('lang.change_plan') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="updateLimitModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered location_modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title locationModalTitle">{{trans('lang.update_plan_limit')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="">
                        <div class="form-row">
                            <div class="form-group row">
                                <div class="form-group row width-100">
                                    <label class="control-label">{{ trans('lang.maximum_order_limit') }}</label>
                                    <div class="form-check width-100">
                                        <input type="radio" id="unlimited_order" name="set_order_limit"
                                               value="unlimited"
                                               checked>
                                        <label class="control-label"
                                               for="unlimited_order">{{ trans('lang.unlimited') }}</label>
                                    </div>
                                    <div class="d-flex">
                                        <div class="form-check width-50 limited_order_div">
                                            <input type="radio" id="limited_order" name="set_order_limit"
                                                   value="limited">
                                            <label class="control-label"
                                                   for="limited_order">{{ trans('lang.limited') }}</label>
                                        </div>
                                        <div class="form-check width-50 d-none order-limit-div">
                                            <input type="number" id="order_limit" class="form-control"
                                                   placeholder="{{ trans('lang.ex_1000') }}">
                                        </div>
                                    </div>
                                    <span class="order_limit_err"></span>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="control-label">{{ trans('lang.maximum_item_limit') }}</label>
                                    <div class="form-check width-100">
                                        <input type="radio" id="unlimited_item" name="set_item_limit" value="unlimited"
                                               checked>
                                        <label class="control-label"
                                               for="unlimited_item">{{ trans('lang.unlimited') }}</label>
                                    </div>
                                    <div class="d-flex ">
                                        <div class="form-check width-50 limited_item_div  ">
                                            <input type="radio" id="limited_item" name="set_item_limit" value="limited">
                                            <label class="control-label"
                                                   for="limited_item">{{ trans('lang.limited') }}</label>
                                        </div>
                                        <div class="form-check width-50 d-none item-limit-div">
                                            <input type="number" id="item_limit" class="form-control"
                                                   placeholder="{{ trans('lang.ex_1000') }}">
                                        </div>
                                    </div>
                                    <span class="item_limit_err"></span>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary update-plan-limit">{{trans('submit')}}</a></button>
                        <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                            {{trans('close')}}</a>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        var id = "<?php echo $id; ?>";
        var photo = "";
        var vendorAuthor = '';
        var restaurantOwnerId = "";
        var restaurantOwnerOnline = false;
        var workingHours = [];
        var timeslotworkSunday = [];
        var timeslotworkMonday = [];
        var timeslotworkTuesday = [];
        var timeslotworkWednesday = [];
        var timeslotworkFriday = [];
        var timeslotworkSatuarday = [];
        var timeslotworkThursday = [];
        var placeholderImage = '';
        var currentCurrency = '';
        var currencyAtRight = false;
        var decimal_degits = 0;
        var commisionModel = false;
        var AdminCommission = '';
        var subscriptionModel = false;
        var subscriptionPlans = [];
        var currentRestaurant = null;

        function parseIsoDate(value) {
            if (!value) {
                return null;
            }
            if (typeof value === 'string') {
                var cleaned = value.replace(/"/g, '');
                var parsed = new Date(cleaned);
                if (!isNaN(parsed.getTime())) {
                    return parsed;
                }
            } else if (typeof value === 'object' && value !== null) {
                if (value.seconds !== undefined) {
                    return new Date(value.seconds * 1000);
                }
            }
            return null;
        }

        function normalizePlan(plan) {
            var clone = Object.assign({}, plan);
            if (typeof clone.plan_points === 'string') {
                try {
                    clone.plan_points = JSON.parse(clone.plan_points);
                } catch (e) {
                    clone.plan_points = [];
                }
            } else if (!Array.isArray(clone.plan_points)) {
                clone.plan_points = Array.isArray(clone.planPoints) ? clone.planPoints : (clone.planPoints ? [clone.planPoints] : []);
            }

            if (typeof clone.features === 'string') {
                try {
                    clone.features = JSON.parse(clone.features);
                } catch (e) {
                    clone.features = {};
                }
            } else if (!clone.features || typeof clone.features !== 'object') {
                clone.features = {};
            }

            return clone;
        }

        // Load placeholder image from SQL
        placeholderImage = '{{ asset('images/placeholder.png') }}';

        // Load currency settings from SQL
        $.ajax({
            url: '{{url("/payments/currency")}}',
            type: 'GET',
            success: function (response) {
                if (response.success && response.data) {
                    currentCurrency = response.data.symbol;
                    currencyAtRight = response.data.symbolAtRight;
                    decimal_degits = response.data.decimal_degits || 0;
                }
            }
        });

        // Load admin commission settings from SQL
        $.ajax({
            url: '{{url("/api/settings/AdminCommission")}}',
            type: 'GET',
            success: function (response) {
                if (response.success && response.data) {
                    var commissionSetting = response.data;
                    if (commissionSetting.isEnabled == true) {
                        commisionModel = true;
                    }
                    if (commissionSetting.commissionType == "Percent") {
                        AdminCommission = commissionSetting.fix_commission + '%';
                    } else {
                        if (currencyAtRight) {
                            AdminCommission = parseFloat(commissionSetting.fix_commission).toFixed(decimal_degits) + currentCurrency;
                        } else {
                            AdminCommission = currentCurrency + parseFloat(commissionSetting.fix_commission).toFixed(decimal_degits);
                        }
                    }
                }
            }
        });

        // Load business model settings from SQL
        $.ajax({
            url: '{{url("/api/settings/restaurant")}}',
            type: 'GET',
            success: function (response) {
                if (response.success && response.data) {
                    var businessModelSettings = response.data;
                    if (businessModelSettings.subscription_model == true) {
                        subscriptionModel = true;
                    }
                }
            }
        });
        var emailTemplatesData = null;

        // Load email template from SQL
        $.ajax({
            url: '{{url("/api/email-templates/wallet_topup")}}',
            type: 'GET',
            success: function (response) {
                if (response.success && response.template) {
                    emailTemplatesData = response.template;
                }
            }
        });

        $(".save-form-btn").click(function () {
            var amount = $('#amount').val();
            if (amount == '') {
                $('#wallet_error').text('{{trans("lang.add_wallet_amount_error")}}')
                return false;
            }
            var note = $('#note').val();

            // Add wallet amount via AJAX
            $.ajax({
                url: '{{url("/api/users/wallet/add")}}',
                type: 'POST',
                data: {
                    user_id: vendorAuthor,
                    amount: amount,
                    note: note,
                    _token: '{{csrf_token()}}'
                },
                success: function (response) {
                    if (response.success) {
                        var amountFormatted, newWalletFormatted;
                        if (currencyAtRight) {
                            amountFormatted = parseInt(amount).toFixed(decimal_degits) + currentCurrency;
                            newWalletFormatted = parseFloat(response.newWalletAmount).toFixed(decimal_degits) + currentCurrency;
                        } else {
                            amountFormatted = currentCurrency + parseInt(amount).toFixed(decimal_degits);
                            newWalletFormatted = currentCurrency + parseFloat(response.newWalletAmount).toFixed(decimal_degits);
                        }

                        var formattedDate = new Date();
                        var month = formattedDate.getMonth() + 1;
                        var day = formattedDate.getDate();
                        var year = formattedDate.getFullYear();
                        month = month < 10 ? '0' + month : month;
                        day = day < 10 ? '0' + day : day;
                        formattedDate = day + '-' + month + '-' + year;

                        if (emailTemplatesData) {
                            var message = emailTemplatesData.message;
                            message = message.replace(/{username}/g, response.user.firstName + ' ' + response.user.lastName);
                            message = message.replace(/{date}/g, formattedDate);
                            message = message.replace(/{amount}/g, amountFormatted);
                            message = message.replace(/{paymentmethod}/g, 'Wallet');
                            message = message.replace(/{transactionid}/g, response.transaction_id);
                            message = message.replace(/{newwalletbalance}/g, newWalletFormatted);

                            var url = "{{url('send-email')}}";
                            sendEmail(url, emailTemplatesData.subject, message, [response.user.email]).then(function (sendEmailStatus) {
                                if (sendEmailStatus) {
                                    window.location.reload();
                                }
                            });
                        } else {
                            window.location.reload();
                        }
                    } else {
                        $('#user_account_not_found_error').text(response.message || '{{trans("lang.user_detail_not_found")}}');
                    }
                },
                error: function () {
                    $('#user_account_not_found_error').text('{{trans("lang.error_adding_wallet_amount")}}');
                }
            });
        });

        async function getWalletBalance(vendorId) {
            return $.ajax({
                url: '/api/users/' + vendorId + '/wallet-balance',
                type: 'GET',
                success: function (response) {
                    if (response.success) {
                        var wallet_balance = response.wallet_balance || 0;
                        if (currencyAtRight) {
                            wallet_balance = parseFloat(wallet_balance).toFixed(decimal_degits) + currentCurrency;
                        } else {
                            wallet_balance = currentCurrency + parseFloat(wallet_balance).toFixed(decimal_degits);
                        }
                        $('.wallet').html(wallet_balance);
                    }
                }
            });
        }

        $(document).ready(async function () {
            jQuery("#data-table_processing").show();

            // Load stats from SQL
            $.ajax({
                url: '/restaurants/' + id + '/stats',
                type: 'GET',
                success: function (response) {
                    if (response.success) {
                        $("#total_orders").text(response.totalOrders);

                        var totalEarnings_formatted;
                        if (currencyAtRight) {
                            totalEarnings_formatted = parseFloat(response.totalEarnings).toFixed(decimal_degits) + currentCurrency;
                        } else {
                            totalEarnings_formatted = currentCurrency + parseFloat(response.totalEarnings).toFixed(decimal_degits);
                        }
                        $("#total_earnings").text(totalEarnings_formatted);

                        var totalPayments_formatted;
                        if (currencyAtRight) {
                            totalPayments_formatted = parseFloat(response.totalPayments).toFixed(decimal_degits) + currentCurrency;
                        } else {
                            totalPayments_formatted = currentCurrency + parseFloat(response.totalPayments).toFixed(decimal_degits);
                        }
                        $("#total_payment").text(totalPayments_formatted);

                        var remaining_formatted;
                        if (currencyAtRight) {
                            remaining_formatted = parseFloat(response.remainingBalance).toFixed(decimal_degits) + currentCurrency;
                        } else {
                            remaining_formatted = currentCurrency + parseFloat(response.remainingBalance).toFixed(decimal_degits);
                        }
                        $("#remaining_amount").text(remaining_formatted);
                    }
                }
            });

            // Load restaurant data from SQL
            $.ajax({
                url: '/restaurants/' + id + '/data',
                type: 'GET',
                success: function (response) {
                    jQuery("#data-table_processing").hide();
                    if (response.success && response.data) {
                        var restaurant = response.data;
                        currentRestaurant = restaurant;
                        vendorAuthor = restaurant.author;
                        $(".restaurant_name").text(restaurant.title);
                        var rating = 0;
                        if (restaurant.hasOwnProperty('reviewsCount') && restaurant.reviewsCount != 0) {
                            rating = Math.round(parseFloat(restaurant.reviewsSum) / parseInt(restaurant.reviewsCount));
                        } else {
                            rating = 0;
                        }
                        walletRoute = "{{route('users.walletstransaction', ':id')}}";
                        walletRoute = walletRoute.replace(":id", restaurant.author);
                        $('#restaurant_wallet').append('<a href="' + walletRoute + '">{{trans("lang.wallet_transaction")}}</a>');
                        $('#subscription_plan').append('<a href="' + "{{route('vendor.subscriptionPlanHistory', ':id')}}".replace(':id', restaurant.author) + '">' + '{{trans('lang.subscription_history')}}' + '</a>');
                        const walletBalance = getWalletBalance(restaurant.author);
                        const getStoreName = getStoreNameFunction('<?php echo $id; ?>');
                        var review = '<ul class="rating" data-rating="' + rating + '">';
                        review = review + '<li class="rating__item"></li>';
                        review = review + '<li class="rating__item"></li>';
                        review = review + '<li class="rating__item"></li>';
                        review = review + '<li class="rating__item"></li>';
                        review = review + '<li class="rating__item"></li>';
                        review = review + '</ul>';
                        if (restaurant.reviewsCount == null || restaurant.reviewsCount == undefined || restaurant.reviewsCount == '') {
                            restaurant.reviewsCount = 0;
                        }
                        var restaurant_reviewcount = restaurant.reviewsCount + '<i class="mdi mdi-star"></i>';
                        $("#restaurant_reviewcount").append(restaurant_reviewcount);
                        var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        var currentdate = new Date();
                        var currentDay = days[currentdate.getDay()];
                        hour = currentdate.getHours();
                        minute = currentdate.getMinutes();
                        if (hour < 10) {
                            hour = '0' + hour
                        }
                        if (minute < 10) {
                            minute = '0' + minute
                        }
                        var currentHours = hour + ':' + minute;
                        $(".vendor_avtive").text("Closed").removeClass("green").addClass("red");

                        // Check if restaurant is manually set to closed
                        if (restaurant.hasOwnProperty('isOpen') && restaurant.isOpen === false) {
                            $(".vendor_avtive").text("Closed").removeClass("green").addClass("red");
                        } else {
                            // Check working hours only if restaurant is not manually closed
                            var isOpenBasedOnHours = false;
                            if (restaurant.hasOwnProperty('workingHours')) {
                                for (i = 0; i < restaurant.workingHours.length; i++) {
                                    var day = restaurant.workingHours[i]['day'];
                                    if (restaurant.workingHours[i]['day'] == currentDay) {
                                        if (restaurant.workingHours[i]['timeslot'].length != 0) {
                                            for (j = 0; j < restaurant.workingHours[i]['timeslot'].length; j++) {
                                                var timeslot = restaurant.workingHours[i]['timeslot'][j];
                                                var from = timeslot[`from`];
                                                var to = timeslot[`to`];
                                                if (currentHours >= from && currentHours <= to) {
                                                    isOpenBasedOnHours = true;
                                                }
                                            }
                                        }
                                    }
                                }
                            } else {
                                // If no working hours are set, use default times (9:30 AM to 10:00 PM)
                                var defaultFrom = '09:30';
                                var defaultTo = '22:00';
                                if (currentHours >= defaultFrom && currentHours <= defaultTo) {
                                    isOpenBasedOnHours = true;
                                }
                            }

                            if (isOpenBasedOnHours) {
                                $(".vendor_avtive").text("Open").removeClass("red").addClass("green");
                            }
                        }
                        if (restaurant.subscriptionPlanId) {

                            let activePlan = null;

                            // 1. Try JSON first
                            if (restaurant.subscription_plan) {
                                if (typeof restaurant.subscription_plan === 'string') {
                                    try {
                                        activePlan = JSON.parse(restaurant.subscription_plan);
                                    } catch (e) {
                                        activePlan = null;
                                    }
                                } else if (typeof restaurant.subscription_plan === 'object') {
                                    activePlan = restaurant.subscription_plan;
                                }
                            }

                            // 2. If JSON missing, fallback from subscriptionPlans list
                            if (!activePlan && Array.isArray(subscriptionPlans)) {
                                activePlan = subscriptionPlans.find(
                                    p => p.id === restaurant.subscriptionPlanId
                                ) || null;
                            }

                            // 3. If still not found → No plan
                            if (!activePlan) {
                                showNoPlanUI();
                                return;
                            }

                            // -------------------------
                            // DISPLAY ACTIVE PLAN
                            // -------------------------
                            $(".update-limit-div").show();
                            $(".plan_name").html(activePlan.name || 'N/A');
                            $(".plan_type").html(activePlan.type || 'N/A');

                            const expiryDateObject = parseIsoDate(restaurant.subscriptionExpiryDate);
                            if (expiryDateObject) {
                                const formattedDate = expiryDateObject.toDateString();
                                const formattedTime = expiryDateObject.toLocaleTimeString('en-US');
                                $(".plan_expire_at").html(formattedDate + ' ' + formattedTime);
                                $(".plan_expire_date").html(formattedDate);
                            } else {
                                $(".plan_expire_at").html("{{ trans('lang.unlimited') }}");
                                $(".plan_expire_date").html("{{ trans('lang.unlimited') }}");
                            }

                            const number_of_days =
                                activePlan.expiryDay == -1 || activePlan.expiryDay == "-1"
                                    ? 'Unlimited'
                                    : activePlan.expiryDay + ' Days';

                            $(".number_of_days").html(number_of_days);

                            const price = parseFloat(activePlan.price || 0).toFixed(decimal_degits);
                            $(".plan_price").html(
                                currencyAtRight ? price + currentCurrency : currentCurrency + price
                            );

                            $(".order_limit").html(
                                activePlan.orderLimit == -1 ? "{{ trans('lang.unlimited') }}" : activePlan.orderLimit
                            );

                            $(".item_limit").html(
                                activePlan.itemLimit == -1 ? "{{ trans('lang.unlimited') }}" : activePlan.itemLimit
                            );

                            // FEATURES
                            if (activePlan.features) {
                                let features = activePlan.features;
                                if (typeof features === 'string') {
                                    try {
                                        features = JSON.parse(features);
                                    } catch (e) {
                                        features = {};
                                    }
                                }

                                const html = `<ul class="pricing-card-list text-dark-2">
            ${features.chat ? `<li>{{ trans('lang.chat') }}</li>` : ''}
            ${features.dineIn ? `<li>{{ trans('lang.dine_in') }}</li>` : ''}
            ${features.qrCodeGenerate ? `<li>{{ trans('lang.generate_qr_code') }}</li>` : ''}
            ${features.restaurantMobileApp ? `<li>{{ trans('lang.mobile_app') }}</li>` : ''}
        </ul>`;

                                $(".plan_features").html(html);
                            }

                        } else {
                            showNoPlanUI();
                        }

                        function showNoPlanUI() {
                            $(".plan_name").html('Commission Plan');
                            $(".plan_type").html('Commission Based');
                            $(".plan_expire_at").html('{{ trans("lang.unlimited") }}');
                            $(".plan_expire_date").html('N/A');
                            $(".number_of_days").html('N/A');
                            $(".plan_price").html('{{ trans("lang.commission_based") }}');
                            $(".order_limit").html('N/A');
                            $(".item_limit").html('N/A');
                            $(".plan_features").html('N/A');
                            $(".update-limit-div").hide();

                            const html = `
        <ul class="pricing-card-list text-dark-2">
            <li>{{ trans('lang.pay_commission_per_order') }}</li>
            <li>{{ trans('lang.unlimited_orders') }}</li>
            <li>{{ trans('lang.unlimited_products') }}</li>
        </ul>
    `;
                        }

                        if (restaurant.hasOwnProperty('workingHours')) {
                            for (i = 0; i < restaurant.workingHours.length; i++) {
                                var day = restaurant.workingHours[i]['day'];
                                var timeslotHtml = '';
                                if (restaurant.workingHours[i]['timeslot'].length != 0) {
                                    for (j = 0; j < restaurant.workingHours[i]['timeslot'].length; j++) {
                                        var timeslot = restaurant.workingHours[i]['timeslot'][j];
                                        var from = timeslot['from'], to = timeslot['to'];
                                        var fromTime = (parseInt(from.split(":")[0]) % 12 || 12) + ':' + from.split(":")[1] + (parseInt(from.split(":")[0]) >= 12 ? ' PM' : ' AM');
                                        var toTime = (parseInt(to.split(":")[0]) % 12 || 12) + ':' + to.split(":")[1] + (parseInt(to.split(":")[0]) >= 12 ? ' PM' : ' AM');
                                        timeslotHtml += `<p class="mb-2">${fromTime} - ${toTime}</p>`;
                                    }
                                } else {
                                    // Show default times when no working hours are set
                                    timeslotHtml = '<p class="mb-2">9:30 AM - 10:00 PM</p>';
                                }
                                $("." + day + "_working_hours").append(timeslotHtml);
                            }
                        } else {
                            // If no workingHours property exists, show default times for all days
                            var defaultTimeslotHtml = '<p class="mb-2">9:30 AM - 10:00 PM</p>';
                            $(".Monday_working_hours").append(defaultTimeslotHtml);
                            $(".Tuesday_working_hours").append(defaultTimeslotHtml);
                            $(".Wednesday_working_hours").append(defaultTimeslotHtml);
                            $(".Thursday_working_hours").append(defaultTimeslotHtml);
                            $(".Friday_working_hours").append(defaultTimeslotHtml);
                            $(".Satuarday_working_hours").append(defaultTimeslotHtml);
                            $(".Sunday_working_hours").append(defaultTimeslotHtml);
                        }
                        if (restaurant.hasOwnProperty('adminCommission')) {
                            if (restaurant.adminCommission.commissionType == 'Percent') {
                                $('.admin_commission').html(restaurant.adminCommission.fix_commission + "%");
                            } else {
                                if (currencyAtRight) {
                                    $('.admin_commission').html(restaurant.adminCommission.fix_commission + currentCurrency);
                                } else {
                                    $('.admin_commission').html(currentCurrency + restaurant.adminCommission.fix_commission);
                                }
                            }
                        }
                        var photos = '<ul class="p-0">';
                        if (restaurant.photos && Array.isArray(restaurant.photos) && restaurant.photos.length > 0) {
                            restaurant.photos.forEach((photo) => {
                                photos = photos + '<li><img width="100px" id="" height="auto" src="' + photo + '"></span></li>';
                            })
                            photos = photos + '</ul>'
                            $("#photos").html(photos);
                        } else {
                            $("#photos").html('<p>photos not available.</p>');
                        }
                        var image = "";
                        if (restaurant.photo != "" && restaurant.photo != null) {
                            image = '<img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" width="200px" id="" height="auto" src="' + restaurant.photo + '">';
                        } else {
                            image = '<img width="200px" id="" height="auto" src="' + placeholderImage + '">';
                        }
                        $("#restaurant_image").html(image);
                        $(".reviewhtml").html(review);
                        filtershtml = '';
                        if (restaurant.filters && typeof restaurant.filters === 'object') {
                            for (var key in restaurant.filters) {
                                if (restaurant.filters[key] == "Yes") {
                                    filtershtml = filtershtml + '<li><span class="mdi mdi-check green mr-2"></span>' + key + '</li>';
                                } else {
                                    filtershtml = filtershtml + '<li><span class="mdi mdi-close red mr-2"></span>' + key + '</li>';
                                }
                            }
                        }
                        $("#filtershtml").html(filtershtml);
                        // Handle multiple categories display
                        if (restaurant.categoryTitle && Array.isArray(restaurant.categoryTitle)) {
                            // Multiple categories
                            $(".restaurant_cuisines").text(restaurant.categoryTitle.join(", "));
                        } else if (restaurant.categoryTitle && typeof restaurant.categoryTitle === 'string') {
                            try {
                                var categoryTitles = JSON.parse(restaurant.categoryTitle);
                                if (Array.isArray(categoryTitles)) {
                                    $(".restaurant_cuisines").text(categoryTitles.join(", "));
                                } else {
                                    $(".restaurant_cuisines").text(restaurant.categoryTitle);
                                }
                            } catch (e) {
                                $(".restaurant_cuisines").text(restaurant.categoryTitle);
                            }
                        } else if (restaurant.categoryID) {
                            // Category ID - need to look up from database
                            $.ajax({
                                url: '{{route("restaurants.categories")}}',
                                type: 'GET',
                                success: function (catResponse) {
                                    if (catResponse.success && catResponse.data) {
                                        var categoryId = Array.isArray(restaurant.categoryID) ? restaurant.categoryID[0] : restaurant.categoryID;
                                        if (typeof categoryId === 'string') {
                                            try {
                                                var parsed = JSON.parse(categoryId);
                                                categoryId = Array.isArray(parsed) ? parsed[0] : parsed;
                                            } catch (e) {
                                            }
                                        }
                                        var category = catResponse.data.find(cat => cat.id == categoryId);
                                        if (category) {
                                            $(".restaurant_cuisines").text(category.title);
                                        }
                                    }
                                }
                            });
                        }
                        $(".opentime").text(restaurant.opentime);
                        $(".closetime").text(restaurant.closetime);
                        $(".restaurant_address").text(restaurant.location);
                        $(".restaurant_latitude").text(restaurant.latitude);
                        $(".restaurant_longitude").text(restaurant.longitude);
                        $(".restaurant_description").text(restaurant.description);
                        if (restaurant.hasOwnProperty('enabledDiveInFuture') && restaurant.enabledDiveInFuture == true) {
                            $(".dine_in_future").html("ON").removeClass("red").addClass("green");
                        } else {
                            $(".dine_in_future").html("OFF").removeClass("green").addClass("red");
                        }

                        // Display isOpen status
                        if (restaurant.hasOwnProperty('isOpen') && restaurant.isOpen === false) {
                            $(".restaurant_is_open").html("Closed").removeClass("green").addClass("red");
                        } else {
                            $(".restaurant_is_open").html("Open").removeClass("red").addClass("green");
                        }
                        restaurantOwnerOnline = restaurant.isActive;
                        photo = restaurant.photo;
                        restaurantOwnerId = restaurant.author;

                        // Load user data from SQL
                        $.ajax({
                            url: '/api/users/' + restaurant.author,
                            type: 'GET',
                            success: function (userResponse) {
                                if (userResponse.success && userResponse.user) {
                                    var user = userResponse.user;
                                    $(".vendor_name").html(user.firstName + " " + user.lastName);
                                    if (user.email != "" && user.email != null) {
                                        $(".vendor_email").html(shortEmail(user.email));
                                    } else {
                                        $(".vendor_email").html("");
                                    }
                                    if (user.phoneNumber != "" && user.phoneNumber != null) {
                                        $(".vendor_phoneNumber").html(shortEditNumber(user.phoneNumber));
                                    } else {
                                        $(".vendor_phoneNumber").html("");
                                    }
                                }
                            }
                        });

                        // Load categories from SQL
                        $.ajax({
                            url: '{{route("restaurants.categories")}}',
                            type: 'GET',
                            success: function (catResponse) {
                                if (catResponse.success && catResponse.data) {
                                    catResponse.data.forEach(function (data) {
                                        $('#restaurant_cuisines').append($("<option></option>")
                                            .attr("value", data.id)
                                            .text(data.title));
                                    });

                                    // Handle multiple category selection for existing restaurant
                                    if (restaurant.categoryID) {
                                        var categoryIds = restaurant.categoryID;
                                        if (typeof categoryIds === 'string') {
                                            try {
                                                categoryIds = JSON.parse(categoryIds);
                                            } catch (e) {
                                                categoryIds = [categoryIds];
                                            }
                                        }
                                        if (Array.isArray(categoryIds)) {
                                            $('#restaurant_cuisines').val(categoryIds);
                                        } else {
                                            $('#restaurant_cuisines').val([categoryIds]);
                                        }
                                    }
                                }
                            }
                        });
                        if (restaurant.hasOwnProperty('phonenumber')) {
                            $(".restaurant_phone").text(shortEditNumber(restaurant.phonenumber));
                        } else {
                            $(".restaurant_phone").text();
                        }
                        if (restaurant.hasOwnProperty('coordinates') && restaurant.coordinates) {
                            var lat = restaurant.coordinates.latitude;
                            var lng = restaurant.coordinates.longitude;
                            var mapSrc = `https://maps.google.com/maps?width=600&height=225&hl=en&q=${lat},${lng}&t=&z=14&ie=UTF8&iwloc=B&output=embed`;
                            $(".gmap_iframe").attr("src", mapSrc);
                        } else {
                            $(".mapouter").html("<p>No map available</p>");
                        }
                        if (restaurant.hasOwnProperty('zoneId') && restaurant.zoneId != '') {
                            $.ajax({
                                url: '/api/zone/' + restaurant.zoneId,
                                type: 'GET',
                                success: function (zoneResponse) {
                                    if (zoneResponse.success && zoneResponse.zone) {
                                        $("#zone_name").text(zoneResponse.zone.name);
                                    }
                                }
                            });
                        }
                        jQuery("#data-table_processing").hide();
                    } else {
                        jQuery("#data-table_processing").hide();
                        console.error('Failed to load restaurant data:', response);
                        alert('Error loading restaurant data: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function (xhr, status, error) {
                    jQuery("#data-table_processing").hide();
                    console.error('AJAX error loading restaurant data:', error, xhr.responseText);
                    alert('Error loading restaurant data. Please check console for details.');
                }
            });

            $(".save_restaurant_btn").click(function () {
                var restaurantname = $(".restaurant_name").val();
                // Handle multiple category selection
                var categoryIDs = $("#restaurant_cuisines").val() || [];
                var categoryTitles = [];
                $("#restaurant_cuisines option:selected").each(function () {
                    if ($(this).val() !== "") {
                        categoryTitles.push($(this).text());
                    }
                });
                var address = $(".restaurant_address").val();
                var latitude = parseFloat($(".restaurant_latitude").val());
                var longitude = parseFloat($(".restaurant_longitude").val());
                var description = $(".restaurant_description").val();
                var phonenumber = $(".restaurant_phone").val();

                // Update restaurant via AJAX
                $.ajax({
                    url: '/restaurants/' + id,
                    type: 'PUT',
                    data: {
                        title: restaurantname,
                        description: description,
                        latitude: latitude,
                        longitude: longitude,
                        location: address,
                        photo: photo,
                        categoryID: categoryIDs,
                        categoryTitle: categoryTitles,
                        phonenumber: phonenumber,
                        _token: '{{csrf_token()}}'
                    },
                    success: function (response) {
                        if (response.success) {
                            window.location.href = '{{ route("restaurants")}}';
                        } else {
                            alert('Error updating restaurant: ' + (response.message || 'Unknown error'));
                        }
                    },
                    error: function () {
                        alert('Error updating restaurant');
                    }
                });
            })
        })

        async function getStoreNameFunction(vendorId) {
            var vendorName = '';
            return $.ajax({
                url: '/restaurants/' + vendorId + '/data',
                type: 'GET',
                success: function (response) {
                    if (response.success && response.data) {
                        vendorName = response.data.title;
                        $('.restaurantTitle').html('{{trans("lang.restaurant_plural")}} - ' + vendorName);
                        if (response.data.dine_in_active == true) {
                            $(".dine_in_future").show();
                        }
                    }
                }
            });
        }

        // These functions are now handled by the main stats AJAX call in document.ready
        async function getTotalOrders() {
            // Stats already loaded via /restaurants/{id}/stats endpoint
        }

        async function getTotalEarnings() {
            // Stats already loaded via /restaurants/{id}/stats endpoint
        }

        async function getTotalpayment() {
            // Stats already loaded via /restaurants/{id}/stats endpoint
        }

        $("#changeSubscriptionModal").on('shown.bs.modal', function () {
            getSubscriptionPlan();
        });
        $("#changeSubscriptionModal").on('hide.bs.modal', function () {
            $("#default-plan").html('');
        });
        $("#checkoutSubscriptionModal").on('hide.bs.modal', function () {
            $("#plan-details").html('');
        });

        function renderSubscriptionPlans(plans, activeSubscriptionId) {
            var html = '';
            console.log('Rendering plans:', plans.length, 'subscriptionModel:', subscriptionModel, 'commisionModel:', commisionModel);

            plans.forEach(function (plan) {
                var normalizedPlan = normalizePlan(plan);
                var activeBadge = (normalizedPlan.id == activeSubscriptionId) ? '<span class="badge badge-success">{{trans("lang.active")}}</span>' : '';

                // Commission-based plan (specific ID check) - Always show if it exists in plans
                if (normalizedPlan.id === "d5883a92-dfd3-11f0-827a-10ffe083f1e8") {
                    var planPoints = Array.isArray(normalizedPlan.plan_points) ? normalizedPlan.plan_points : [];
                    // Use commission from plan's 'place' field if available, otherwise use AdminCommission setting
                    var commissionDisplay = normalizedPlan.place ? (normalizedPlan.place + '%') : (AdminCommission || '0%');

                    html += `<div class="col-md-12 mt-3 pricing-card pricing-card-commission">
                                <div class="pricing-card-inner">
                                    <div class="pricing-card-top">
                                        <div class="d-flex align-items-center pb-4">
                                            <span class="pricing-card-icon mr-4"><img src="${normalizedPlan.image || ''}"></span>
                                        </div>
                                        <div class="pricing-card-price">
                                            <h3 class="text-dark-2">${normalizedPlan.name || 'Commission Plan'} ${activeBadge}</h3>
                                            <span class="price-day">${commissionDisplay} {{ trans('lang.commision_per_order') }}</span>
                                        </div>
                                    </div>
                                    <div class="pricing-card-content pt-3 mt-3 border-top">
                                        <ul class="pricing-card-list text-dark-2">
                                            <li><span class="mdi mdi-check"></span>{{trans('lang.pay_commission_of')}} ${commissionDisplay} {{trans('lang.on_each_order')}} </li>
                                            ${planPoints.map(point => `<li><span class="mdi mdi-check"></span>${point}</li>`).join('')}
                                            <li><span class="mdi mdi-check"></span>{{ trans('lang.unlimited') }} {{ trans('lang.orders') }}</li>
                                            <li><span class="mdi mdi-check"></span>{{ trans('lang.unlimited') }} {{ trans('lang.products') }}</li>
                                        </ul>
                                    </div>
                                    <div class="pricing-card-btm">
                                        <a href="javascript:void(0)" onClick="chooseSubscriptionPlan('${normalizedPlan.id}')" class="btn rounded-full active-btn btn-primary">${activeBadge ? "{{ trans('lang.renew_plan') }}" : "{{ trans('lang.select_plan') }}"}</a>
                                    </div>
                                </div>
                            </div>`;
                }
                // Regular subscription plans - show ALL plans regardless of subscriptionModel flag
                else {
                    var features = normalizedPlan.features || {};
                    var translations = {
                        chatingOption: "{{ trans('lang.chating_option') }}",
                        dineInOption: "{{ trans('lang.dinein_option') }}",
                        generateQrCode: "{{ trans('lang.generate_qr_code') }}",
                        mobileAppAccess: "{{ trans('lang.mobile_app_access') }}"
                    };
                    var priceText = currencyAtRight ? parseFloat(normalizedPlan.price || 0).toFixed(decimal_degits) + currentCurrency : currentCurrency + parseFloat(normalizedPlan.price || 0).toFixed(decimal_degits);
                    var expiryLabel = (normalizedPlan.expiryDay == -1 || normalizedPlan.expiryDay == "-1") ? "{{ trans('lang.unlimited') }}" : normalizedPlan.expiryDay + " Days";
                    html += `<div class="col-md-12 mt-3 pricing-card pricing-card-subscription ${normalizedPlan.name || ''}">
                            <div class="pricing-card-inner">
                                <div class="pricing-card-top">
                                    <div class="d-flex align-items-center pb-4">
                                        <span class="pricing-card-icon mr-4"><img src="${normalizedPlan.image || ''}"></span>
                                        <h2 class="text-dark-2">${normalizedPlan.name || ''} ${activeBadge}</h2>
                                    </div>
                                    <p class="text-muted">${normalizedPlan.description || ''}</p>
                                    <div class="pricing-card-price">
                                        <h3 class="text-dark-2">${priceText}</h3>
                                        <span class="price-day">${expiryLabel}</span>
                                    </div>
                                </div>
                                <div class="pricing-card-content pt-3 mt-3 border-top">
                                    <ul class="pricing-card-list text-dark-2">
                                        ${(features.chat ? `<li><span class="mdi mdi-check"></span>${translations.chatingOption}</li>` : `<li><span class="mdi mdi-close"></span>${translations.chatingOption}</li>`)}
                                        ${(features.dineIn ? `<li><span class="mdi mdi-check"></span>${translations.dineInOption}</li>` : `<li><span class="mdi mdi-close"></span>${translations.dineInOption}</li>`)}
                                        ${(features.qrCodeGenerate ? `<li><span class="mdi mdi-check"></span>${translations.generateQrCode}</li>` : `<li><span class="mdi mdi-close"></span>${translations.generateQrCode}</li>`)}
                                        ${(features.restaurantMobileApp ? `<li><span class="mdi mdi-check"></span>${translations.mobileAppAccess}</li>` : `<li><span class="mdi mdi-close"></span>${translations.mobileAppAccess}</li>`)}
                                        <li><span class="mdi mdi-check"></span>${(normalizedPlan.orderLimit == -1 || normalizedPlan.orderLimit == '-1') ? "{{ trans('lang.unlimited') }}" : normalizedPlan.orderLimit} {{ trans('lang.orders') }}</li>
                                        <li><span class="mdi mdi-check"></span>${(normalizedPlan.itemLimit == -1 || normalizedPlan.itemLimit == '-1') ? "{{ trans('lang.unlimited') }}" : normalizedPlan.itemLimit} {{ trans('lang.products') }}</li>
                                    </ul>
                                </div>
                                <div class="pricing-card-btm">
                                    <a href="javascript:void(0)" onClick="chooseSubscriptionPlan('${normalizedPlan.id}')" class="btn rounded-full">${activeBadge ? "{{ trans('lang.renew_plan') }}" : "{{ trans('lang.select_plan') }}"}</a>
                                </div>
                            </div>
                        </div>`;
                }
            });
            if (html === '') {
                html = '<div class="col-12"><p class="mb-0">{{ trans('lang.no_record_found') }}</p></div>';
            }
            $('#default-plan').html(html);
        }

        function getSubscriptionPlan() {
            var activeSubscriptionId = '';
            if (currentRestaurant && currentRestaurant.subscriptionPlanId) {
                activeSubscriptionId = currentRestaurant.subscriptionPlanId;
            }

            // Show/hide remove plan button based on whether there's an active plan
            if (activeSubscriptionId) {
                $('#remove-plan-btn').show();
            } else {
                $('#remove-plan-btn').hide();
            }

            $('#default-plan').html('<div class="col-12"><p class="mb-0 font-weight-bold text-center">loading...</p></div>');

            $.ajax({
                url: '{{ route("subscription-plans.fetch") }}',
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    console.log('Subscription plans API response:', response);

                    if (response.success && Array.isArray(response.data)) {
                        subscriptionPlans = response.data;
                        console.log('Loaded ' + subscriptionPlans.length + ' subscription plans');
                        console.log('Commission plan included:', subscriptionPlans.some(p => p.id === 'd5883a92-dfd3-11f0-827a-10ffe083f1e8'));

                        if (subscriptionPlans.length === 0) {
                            $('#default-plan').html('<div class="col-12"><p class="mb-0 text-danger">No subscription plans found. Please add plans in the admin panel.</p></div>');
                        } else {
                            renderSubscriptionPlans(subscriptionPlans, activeSubscriptionId);
                        }
                    } else {
                        console.error('Invalid response format:', response);
                        $('#default-plan').html('<div class="col-12"><p class="mb-0 text-danger">Error: Invalid response format. ' + (response.message || 'Unknown error') + '</p></div>');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error loading subscription plans:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText,
                        statusCode: xhr.status
                    });

                    var errorMsg = 'Error loading subscription plans';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg += ': ' + xhr.responseJSON.message;
                    } else if (xhr.status === 404) {
                        errorMsg += ': API endpoint not found';
                    } else if (xhr.status === 500) {
                        errorMsg += ': Server error';
                    }

                    $('#default-plan').html('<div class="col-12"><p class="mb-0 text-danger">' + errorMsg + '</p></div>');
                }
            });
        }

        function getFormattedSubscriptionExpiry() {
            if (!restaurant || !restaurant.subscriptionExpiryDate) {
                return "{{ trans('lang.unlimited') }}";
            }

            const dateObj = parseIsoDate(restaurant.subscriptionExpiryDate);
            if (!dateObj) {
                return "{{ trans('lang.unlimited') }}";
            }

            return dateObj.toDateString();
        }


        function showPlanDetail(planId) {
            $("#plan_id").val(planId);
            var selectedPlan = subscriptionPlans.find(function (plan) {
                return plan.id === planId;
            });
            if (!selectedPlan) {
                $("#plan-details").html('<div class="col-12"><p class="mb-0">{{ trans('lang.no_record_found') }}</p></div>');
                return;
            }
            selectedPlan = normalizePlan(selectedPlan);

            var activePlan = currentRestaurant && currentRestaurant.subscription_plan ? currentRestaurant.subscription_plan : null;
            if (typeof activePlan === 'string') {
                try {
                    activePlan = JSON.parse(activePlan);
                } catch (e) {
                    activePlan = null;
                }
            }
            if (activePlan) {
                activePlan = normalizePlan(activePlan);
            }

            var selectedPrice = currencyAtRight ? parseFloat(selectedPlan.price || 0).toFixed(decimal_degits) + currentCurrency : currentCurrency + parseFloat(selectedPlan.price || 0).toFixed(decimal_degits);
            {{--var selectedExpiry = (selectedPlan.expiryDay == -1 || selectedPlan.expiryDay == '-1') ? "{{ trans('lang.unlimited') }}" : selectedPlan.expiryDay + " {{ trans('lang.days') }}";--}}
            var selectedExpiry = "{{ trans('lang.unlimited') }}";

            if (selectedPlan.type) {
                selectedExpiry =
                    selectedPlan.type.charAt(0).toUpperCase() +
                    selectedPlan.type.slice(1);
            }

            if (selectedPlan.expiryDay != -1 && selectedPlan.expiryDay != '-1') {
                const future = new Date();
                future.setDate(future.getDate() + parseInt(selectedPlan.expiryDay));
                selectedExpiry += " (Expires on " + future.toDateString() + ")";
            }
// --------------------
// COMMISSION (%)
// --------------------
            var commissionText = "0%";

            if (selectedPlan.place !== null && selectedPlan.place !== undefined) {
                commissionText = parseFloat(selectedPlan.place) + "%";
            }


            var html = '';
            if (activePlan) {
                var activePrice = currencyAtRight ? parseFloat(activePlan.price || 0).toFixed(decimal_degits) + currentCurrency : currentCurrency + parseFloat(activePlan.price || 0).toFixed(decimal_degits);
                var activeExpiry = (activePlan.expiryDay == -1 || activePlan.expiryDay == '-1') ? "{{ trans('lang.unlimited') }}" : activePlan.expiryDay + " {{ trans('lang.days') }}";
                html += `
            <div class="col-md-8">
                <div class="subscription-card-left">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <div class="subscription-card text-center">
                                <div class="d-flex align-items-center pb-3 justify-content-center">
                                    <span class="pricing-card-icon mr-4"><img src="${activePlan.image || ''}"></span>
                                    <h2 class="text-dark-2 mb-0 font-weight-semibold">${activePlan.id == "J0RwvxCWhZzQQD7Kc2Ll" ? "{{ trans('lang.commission') }}" : (activePlan.name || '')}</h2>
                                </div>
                                <h3 class="text-dark-2">${activePlan.id == "J0RwvxCWhZzQQD7Kc2Ll" ? AdminCommission + " {{ trans('lang.base_plan') }}" : activePrice}</h3>
                                <p class="text-center">${activePlan.id == "J0RwvxCWhZzQQD7Kc2Ll" ? "Free" : activeExpiry}</p>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <img src="{{asset('images/left-right-arrow.png')}}">
                        </div>
                        <div class="col-md-5">
                            <div class="subscription-card text-center">
                                <div class="d-flex align-items-center pb-3 justify-content-center">
                                    <span class="pricing-card-icon mr-4"><img src="${selectedPlan.image || ''}"></span>
                                    <h2 class="text-dark-2 mb-0 font-weight-semibold">${selectedPlan.name || ''}
                                    </h2>
                                </div>
                                <h3 class="text-dark-2">${selectedPrice}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="subscription-card-right">
                    <div
                        class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                        <span class="font-weight-bold">{{ trans('lang.validity') }}</span>
                        <span class="font-weight-semibold">${selectedExpiry}</span>
                    </div>
                    <div
                        class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                        <span class="font-weight-bold">{{ trans('lang.price') }}</span>
                        <span class="font-weight-semibold">${selectedPrice}</span>
                    </div>
                      <div class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
    <span class="font-weight-bold">{{ trans('lang.bill_status') }}</span>

    <div class="d-flex align-items-center">

        <div class="custom-control custom-radio mr-3">
            <input type="radio"
                   id="bill_paid_1"
                   name="bill_status"
                   class="custom-control-input"
                   value="paid">
            <label class="custom-control-label" for="bill_paid_1">Paid</label>
        </div>

        <div class="custom-control custom-radio">
            <input type="radio"
                   id="bill_unpaid_1"
                   name="bill_status"
                   class="custom-control-input"
                   value="unpaid">
            <label class="custom-control-label" for="bill_unpaid_1">Unpaid</label>
        </div>

    </div>
</div>
                    <div
                      class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                        <span class="font-weight-bold">Commission</span>
                        <span class="font-weight-semibold">${commissionText}</span>
                    </div>
                </div>
            </div>`;
            } else {
                html += `
            <div class="col-md-6">
                <div class="subscription-card-left">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="subscription-card text-center">
                                <div class="d-flex align-items-center pb-3 justify-content-center">
                                    <span class="pricing-card-icon mr-4"><img src="${selectedPlan.image || ''}"></span>
                                    <h2 class="text-dark-2 mb-0 font-weight-semibold">${selectedPlan.name || ''}
                                    </h2>
                                </div>
                                <h3 class="text-dark-2">${selectedPrice}</h3>
                                <p class="text-center">${selectedPlan.id == "J0RwvxCWhZzQQD7Kc2Ll" ? "Free" : selectedExpiry}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="subscription-card-right">
                    <div
                        class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                        <span class="font-weight-bold">{{ trans('lang.validity') }}</span>
                        <span class="font-weight-semibold">${selectedPlan.id == "J0RwvxCWhZzQQD7Kc2Ll" ? "Unlimited" : selectedExpiry}</span>
                    </div>
                    <div
                        class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                        <span class="font-weight-bold">{{ trans('lang.price') }}</span>
                        <span class="font-weight-semibold">${selectedPrice}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
    <span class="font-weight-bold">{{ trans('lang.bill_status') }}</span>

    <div class="d-flex align-items-center">

        <div class="custom-control custom-radio mr-3">
            <input type="radio"
                   id="bill_paid_2"
                   name="bill_status"
                   class="custom-control-input"
                   value="paid">
            <label class="custom-control-label" for="bill_paid_2">Paid</label>
        </div>

        <div class="custom-control custom-radio">
            <input type="radio"
                   id="bill_unpaid_2"
                   name="bill_status"
                   class="custom-control-input"
                   value="unpaid">
            <label class="custom-control-label" for="bill_unpaid_2">Unpaid</label>
        </div>
    </div>
</div>
                     <div
                      class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                        <span class="font-weight-bold">Commission</span>
                        <span class="font-weight-semibold">${commissionText}</span>
                    </div>
                </div>
            </div>`;
            }
            $("#plan-details").html(html);

            // Set the checked state for bill_status radio button
            // If bill_status exists in database, use that value
            // Otherwise, if plan is already assigned, default to "paid"
            if (currentRestaurant) {
                var billStatus = null;
                if (currentRestaurant.bill_status) {
                    billStatus = currentRestaurant.bill_status.toLowerCase();
                } else if (currentRestaurant.subscriptionPlanId) {
                    // Plan is assigned but no bill_status saved, default to "paid"
                    billStatus = 'paid';
                }

                if (billStatus === 'paid') {
                    $('input[name="bill_status"][value="paid"]').prop('checked', true);
                } else if (billStatus === 'unpaid') {
                    $('input[name="bill_status"][value="unpaid"]').prop('checked', true);
                }
            }
        }

        /**
         * This function is called when a plan is selected from the changeSubscriptionModal.
         * It closes the plan selection modal and opens the checkoutSubscriptionModal
         * (the "Shift to New Subscription Plan" popup) to show plan details and payment options.
         */
        function chooseSubscriptionPlan(planId) {
            $("#changeSubscriptionModal").modal('hide');
            $("#checkoutSubscriptionModal").modal('show');
            showPlanDetail(planId);
        }

        /**
         * Assign subscription plan directly (for new assignments)
         */
        function assignSubscriptionPlan() {
            let planId = $("#plan_id").val();

            // ✅ READ RADIO VALUE
            const billStatus = $('input[name="bill_status"]:checked').val();

            if (!planId) {
                alert('Please select a plan first');
                return;
            }

            if (!billStatus) {
                alert('Please select Bill Status (Paid or Unpaid)');
                return;
            }

            $.ajax({
                url: "/restaurants/" + id + "/subscription",
                method: "POST",
                data: {
                    plan_id: planId,
                    bill_status: billStatus,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.success) {
                        alert('Subscription plan assigned successfully!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to assign plan'));
                    }
                },
                error: function (xhr) {
                    var errorMsg = 'Error assigning subscription plan';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg += ': ' + xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                }
            });
        }

        /**
         * Remove subscription plan - unassigns the current plan from the restaurant
         */
        function removeSubscriptionPlan() {
            if (!confirm('Are you sure you want to remove the current subscription plan? This action cannot be undone.')) {
                return;
            }

            $.ajax({
                url: "/restaurants/" + id + "/subscription/remove",
                method: "POST",
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.success) {
                        alert('Subscription plan removed successfully!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to remove plan'));
                    }
                },
                error: function (xhr) {
                    var errorMsg = 'Error removing subscription plan';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg += ': ' + xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                }
            });
        }

        /**
         * Change Plan button - closes checkout modal and shows all plans again
         */
        function finalCheckout() {
            // Close the checkout modal
            $("#checkoutSubscriptionModal").modal('hide');

            // Clear the selected plan
            $("#plan_id").val('');


            // Open the plan selection modal to show all plans again
            setTimeout(function () {
                $("#changeSubscriptionModal").modal('show');
            }, 300); // Small delay to ensure modal closes properly
        }

        $('input[name="set_item_limit"]').on('change', function () {

            if ($('#limited_item').is(':checked')) {
                $('.item-limit-div').removeClass('d-none');
            } else {
                $('.item-limit-div').addClass('d-none');
            }
        });
        $('input[name="set_order_limit"]').on('change', function () {
            if ($('#limited_order').is(':checked')) {
                $('.order-limit-div').removeClass('d-none');
            } else {
                $('.order-limit-div').addClass('d-none');
            }
        });
        $("#updateLimitModal").on('shown.bs.modal', function () {
            $(".item_limit_err").html("");
            $(".order_limit_err").html("");
            if (currentRestaurant && currentRestaurant.subscription_plan) {
                var planLimits = currentRestaurant.subscription_plan;
                if (typeof planLimits === 'string') {
                    try {
                        planLimits = JSON.parse(planLimits);
                    } catch (e) {
                        planLimits = {};
                    }
                }
                var itemLimit = (planLimits && planLimits.itemLimit !== undefined) ? planLimits.itemLimit : '-1';
                var orderLimit = (planLimits && planLimits.orderLimit !== undefined) ? planLimits.orderLimit : '-1';

                if (itemLimit != '-1') {
                    $("#limited_item").prop('checked', true);
                    $('.item-limit-div').removeClass('d-none');
                    $('#item_limit').val(itemLimit);
                } else {
                    $("#unlimited_item").prop('checked', true);
                    $('.item-limit-div').addClass('d-none');
                    $('#item_limit').val('');
                }

                if (orderLimit != '-1') {
                    $("#limited_order").prop('checked', true);
                    $('.order-limit-div').removeClass('d-none');
                    $('#order_limit').val(orderLimit);
                } else {
                    $("#unlimited_order").prop('checked', true);
                    $('.order-limit-div').addClass('d-none');
                    $('#order_limit').val('');
                }
            } else {
                $("#unlimited_item").prop('checked', true);
                $("#unlimited_order").prop('checked', true);
                $('.item-limit-div').addClass('d-none');
                $('.order-limit-div').addClass('d-none');
                $('#item_limit').val('');
                $('#order_limit').val('');
            }
        })
        $('.update-plan-limit').click(function () {

            var set_item_limit = $('input[name="set_item_limit"]:checked').val();
            var item_limit = (set_item_limit == 'limited') ? $('#item_limit').val() : '-1';
            var set_order_limit = $('input[name="set_order_limit"]:checked').val();
            var order_limit = (set_order_limit == 'limited') ? $('#order_limit').val() : '-1';


            $(".item_limit_err").html("");
            $(".order_limit_err").html("");

            if (set_item_limit == 'limited' && $('#item_limit').val() == '') {
                $(".item_limit_err").html("<p>{{ trans('lang.enter_item_limit') }}</p>");
                return false;
            } else if (set_order_limit == 'limited' && $('#order_limit').val() == '') {
                $(".order_limit_err").html("<p>{{ trans('lang.enter_order_limit') }}</p>");
                return false;
            } else {
                $.ajax({
                    url: "{{ url('restaurants') }}/" + id + "/subscription-limits",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        order_limit: order_limit,
                        item_limit: item_limit
                    },
                    success: function (response) {
                        if (response.success) {
                            window.location.reload();
                        } else {
                            $(".order_limit_err").html(`<p>${response.message || 'Unable to update limits.'}</p>`);
                        }
                    },
                    error: function (xhr) {
                        var message = 'Unable to update limits.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        $(".order_limit_err").html(`<p>${message}</p>`);
                    }
                });
            }
        })
    </script>
@endsection
