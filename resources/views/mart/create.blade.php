@extends('layouts.app')
{{--
    MART CREATE FORM - FIXED VERSION

    Issues Fixed:
    1. Added proper vendor validation (vendor selection is now required)
    2. Improved error handling with try-catch blocks for image storage
    3. Added loading states and timeout handling
    4. Added required field indicators
    5. Enhanced validation messages
    6. Fixed image storage error handling

    Key Features:
    - Vendor selection is mandatory
    - Proper error handling for all async operations
    - Loading states with timeout protection
    - Comprehensive validation
--}}

@section('content')
<?php
$countries = file_get_contents(public_path('countriesdata.json'));
$countries = json_decode($countries);
$countries = (array) $countries;
$newcountries = array();
$newcountriesjs = array();
foreach ($countries as $keycountry => $valuecountry) {
    $newcountries[$valuecountry->phoneCode] = $valuecountry;
    $newcountriesjs[$valuecountry->phoneCode] = $valuecountry->code;
}
?>
<div class="page-wrapper">
    <div class="row page-titles">

        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.mart_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a
                        href="{!! route('marts') !!}">{{trans('lang.mart_plural')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.create_mart')}}</li>
            </ol>
        </div>
        <div>

            <div class="card-body">
                <div id="data-table_processing" class="dataTables_processing panel panel-default"
                    style="display: none;">{{trans('lang.processing')}}
                </div>
                <div class="error_top"></div>
                <div class="row restaurant_payout_create">
                    <div class="restaurant_payout_create-inner">
                        <fieldset>
                            <legend>{{trans('lang.mart_details')}}</legend>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.mart_name')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control mart_name" required>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.mart_name_help") }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.vendor')}} <span class="required-field">*</span></label>
                                <div class="col-7">
                                    <select id='restaurant_vendors' class="form-control" required>
                                        <option value="">{{ trans("lang.select_vendor") }}</option>
                                    </select>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.vendor_help") }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.category')}}</label>
                                <div class="col-7">
                                <div id="selected_categories" class="mb-2"></div>
                                <input type="text" id="category_search" class="form-control mb-2" placeholder="Search categories...">
                                <select id='restaurant_cuisines' class="form-control" multiple required>
                                    <option value="">Select Cuisines</option>
                                    <!-- options populated dynamically -->
                                </select>
                                <div class="form-text text-muted">
                                    {{ trans("lang.mart_cuisines_help") }} (Hold Ctrl/Cmd to select multiple)
                                </div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                            <label class="col-3 control-label">Admin Commission</label>
                             <div class="col-7">
                             <input type="number" class="form-control admin_commission" name="admin_commission" min="0" step="1" pattern="[0-9]*">
                                  <div class="form-text text-muted">
                               Enter the admin commission percentage or amount.
                              </div>
                             </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.mart_phone')}}</label>
                                <div class="col-md-12">
                                    <div class="phone-box position-relative">
                                        <select name="country" id="country_selector1">
                                            <?php foreach ($newcountries as $keycy => $valuecy) { ?>
                                            <?php    $selected = ""; ?>
                                            <option <?php    echo $selected; ?> code="<?php    echo $valuecy->code; ?>"
                                                value="<?php    echo $keycy; ?>">
                                                +<?php    echo $valuecy->phoneCode; ?> {{$valuecy->countryName}}
                                            </option>
                                            <?php } ?>
                                        </select>


                                        <input type="text" class="form-control restaurant_phone"
                                            onkeypress="return chkAlphabets2(event,'error2')">
                                        <div id="error2" class="err"></div>
                                    </div>
                                </div>
                                <div class="form-text text-muted">
                                    {{ trans("lang.mart_phone_help") }}
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.mart_address')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control restaurant_address" required>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.mart_address_help") }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.zone')}}<span
                                        class="required-field"></span></label>
                                <div class="col-7">
                                    <select id='zone' class="form-control">
                                        <option value="">{{ trans("lang.select_zone") }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row width-50" style="display:none">
                                <label class="col-4 control-label">{{ trans('lang.commission_type')}}</label>
                                <div class="col-7">
                                    <select class="form-control commission_type" id="commission_type">
                                        <option value="Percent">{{trans('lang.coupon_percent')}}</option>
                                        <option value="Fixed">{{trans('lang.coupon_fixed')}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row width-50" style="display:none">
                                <label class="col-4 control-label">{{ trans('lang.admin_commission')}}</label>
                                <div class="col-7">
                                    <input type="number" value="0" class="form-control commission_fix">
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.mart_latitude')}}</label>
                                <div class="col-7">
                                    <input class="form-control restaurant_latitude" type="number" min="-90" max="90" step="any">
                                    <div class="form-text text-muted">
                                        {{ trans("lang.mart_latitude_help") }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.mart_longitude')}}</label>
                                <div class="col-7">
                                    <input class="form-control restaurant_longitude" type="number" min="-180" max="180" step="any">
                                    <div class="form-text text-muted">
                                        {{ trans("lang.mart_longitude_help") }}
                                    </div>
                                </div>
                                <div class="form-text text-muted ml-3">
                                    Don't Know your cordinates ? use <a target="_blank"
                                        href="https://www.latlong.net/">Latitude and Longitude Finder</a>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-3 control-label ">{{trans('lang.mart_description')}}</label>
                                <div class="col-7">
                                    <textarea rows="7" class="restaurant_description form-control"
                                        id="restaurant_description"></textarea>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>{{trans('lang.gallery')}}</legend>

                            <div class="form-group row width-50 restaurant_image">
                                <div class="">
                                    <div id="photos"></div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div>
                                    <input type="file" id="galleryImage" onChange="handleFileSelect(event,'photos')">
                                    <div id="uploding_image_photos"></div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset style="display:none;">
                            <legend>{{trans('lang.services')}}</legend>

                            <div class="form-group row">

                                <div class="form-check width-100">
                                    <input type="checkbox" id="Free_Wi_Fi">
                                    <label class="col-3 control-label"
                                        for="Free_Wi_Fi">{{trans('lang.free_wi_fi')}}</label>
                                </div>
                                <div class="form-check width-100">
                                    <input type="checkbox" id="Good_for_Breakfast">
                                    <label class="col-3 control-label"
                                        for="Good_for_Breakfast">{{trans('lang.good_for_breakfast')}}</label>
                                </div>
                                <div class="form-check width-100">
                                    <input type="checkbox" id="Good_for_Dinner">
                                    <label class="col-3 control-label"
                                        for="Good_for_Dinner">{{trans('lang.good_for_dinner')}}</label>
                                </div>
                                <div class="form-check width-100">
                                    <input type="checkbox" id="Good_for_Lunch">
                                    <label class="col-3 control-label"
                                        for="Good_for_Lunch">{{trans('lang.good_for_lunch')}}</label>
                                </div>

                                <div class="form-check width-100">
                                    <input type="checkbox" id="Live_Music">
                                    <label class="col-3 control-label"
                                        for="Live_Music">{{trans('lang.live_music')}}</label>
                                </div>

                                <div class="form-check width-100">
                                    <input type="checkbox" id="Outdoor_Seating">
                                    <label class="col-3 control-label"
                                        for="Outdoor_Seating">{{trans('lang.outdoor_seating')}}</label>
                                </div>

                                <div class="form-check width-100">
                                    <input type="checkbox" id="Takes_Reservations">
                                    <label class="col-3 control-label"
                                        for="Takes_Reservations">{{trans('lang.takes_reservations')}}</label>
                                </div>

                                <div class="form-check width-100">
                                    <input type="checkbox" id="Vegetarian_Friendly">
                                    <label class="col-3 control-label"
                                        for="Vegetarian_Friendly">{{trans('lang.vegetarian_friendly')}}</label>
                                </div>

                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>{{trans('lang.working_hours')}}</legend>

                            <div class="form-group row">

                                <div class="form-group row width-100">
                                    <div class="col-7">
                                        <button type="button" class="btn btn-primary  add_working_hours_restaurant_btn">
                                            <i></i>{{trans('lang.add_working_hours')}}
                                        </button>
                                    </div>
                                </div>
                                <div class="working_hours_div" style="display:none">


                                    <div class="form-group row mb-0">
                                        <label class="col-1 control-label">{{trans('lang.sunday')}}</label>
                                        <div class="col-12">
                                            <button type="button" class="btn btn-primary add_more_sunday"
                                                onclick="addMorehour('Sunday','sunday', '1')">
                                                {{trans('lang.add_more')}}
                                            </button>
                                        </div>
                                    </div>


                                    <div class="restaurant_discount_options_Sunday_div restaurant_discount mb-5"
                                        style="display:none">


                                        <table class="booking-table" id="working_hour_table_Sunday">
                                            <tr>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.from')}}</label>
                                                </th>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.to')}}</label>
                                                </th>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.actions')}}</label>
                                                </th>
                                            </tr>

                                        </table>

                                    </div>

                                    <div class="form-group row mb-0">
                                        <label class="col-1 control-label">{{trans('lang.monday')}}</label>
                                        <div class="col-12">
                                            <button type="button" class="btn btn-primary add_more_sunday"
                                                onclick="addMorehour('Monday','monday', '1')">
                                                {{trans('lang.add_more')}}
                                            </button>
                                        </div>
                                    </div>

                                    <div class="restaurant_discount_options_Monday_div restaurant_discount mb-5"
                                        style="display:none">

                                        <table class="booking-table" id="working_hour_table_Monday">
                                            <tr>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.from')}}</label>
                                                </th>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.to')}}</label>
                                                </th>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.actions')}}</label>
                                                </th>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="form-group row mb-0">
                                        <label class="col-1 control-label">{{trans('lang.tuesday')}}</label>
                                        <div class="col-12">
                                            <button type="button" class="btn btn-primary"
                                                onclick="addMorehour('Tuesday','tuesday', '1')">
                                                {{trans('lang.add_more')}}
                                            </button>
                                        </div>
                                    </div>

                                    <div class="restaurant_discount_options_Tuesday_div restaurant_discount mb-5"
                                        style="display:none">

                                        <table class="booking-table" id="working_hour_table_Tuesday">
                                            <tr>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.from')}}</label>
                                                </th>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.to')}}</label>
                                                </th>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.actions')}}</label>
                                                </th>
                                            </tr>

                                        </table>
                                    </div>
                                    <div class="form-group row mb-0">
                                        <label class="col-1 control-label">{{trans('lang.wednesday')}}</label>
                                        <div class="col-12">
                                            <button type="button" class="btn btn-primary"
                                                onclick="addMorehour('Wednesday','wednesday', '1')">
                                                {{trans('lang.add_more')}}
                                            </button>
                                        </div>
                                    </div>


                                    <div class="restaurant_discount_options_Wednesday_div restaurant_discount mb-5"
                                        style="display:none">
                                        <table class="booking-table" id="working_hour_table_Wednesday">
                                            <tr>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.from')}}</label>
                                                </th>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.to')}}</label>
                                                </th>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.actions')}}</label>
                                                </th>
                                            </tr>

                                        </table>
                                    </div>

                                    <div class="form-group row mb-0">
                                        <label class="col-1 control-label">{{trans('lang.thursday')}}</label>
                                        <div class="col-12">
                                            <button type="button" class="btn btn-primary"
                                                onclick="addMorehour('Thursday','thursday', '1')">
                                                {{trans('lang.add_more')}}
                                            </button>
                                        </div>
                                    </div>

                                    <div class="restaurant_discount_options_Thursday_div restaurant_discount mb-5"
                                        style="display:none">
                                        <table class="booking-table" id="working_hour_table_Thursday">
                                            <tr>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.from')}}</label>
                                                </th>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.to')}}</label>
                                                </th>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.actions')}}</label>
                                                </th>
                                            </tr>

                                        </table>
                                    </div>

                                    <div class="form-group row mb-0">
                                        <label class="col-1 control-label">{{trans('lang.friday')}}</label>
                                        <div class="col-12">
                                            <button type="button" class="btn btn-primary"
                                                onclick="addMorehour('Friday','friday', '1')">
                                                {{trans('lang.add_more')}}
                                            </button>
                                        </div>
                                    </div>
                                    <div class="restaurant_discount_options_Friday_div restaurant_discount mb-5"
                                        style="display:none">
                                        <table class="booking-table" id="working_hour_table_Friday">
                                            <tr>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.from')}}</label>
                                                </th>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.to')}}</label>
                                                </th>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.actions')}}</label>
                                                </th>
                                            </tr>

                                        </table>
                                    </div>


                                    <div class="form-group row mb-0">
                                        <label class="col-1 control-label">{{trans('lang.Saturday')}}</label>
                                        <div class="col-12">
                                            <button type="button" class="btn btn-primary"
                                                onclick="addMorehour('Saturday','Saturday','1')">
                                                {{trans('lang.add_more')}}
                                            </button>
                                        </div>
                                    </div>
                                    <div class="restaurant_discount_options_Saturday_div restaurant_discount mb-5"
                                        style="display:none">
                                        <table class="booking-table" id="working_hour_table_Saturday">
                                            <tr>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.from')}}</label>
                                                </th>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.to')}}</label>
                                                </th>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.actions')}}</label>
                                                </th>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </fieldset>


                        <fieldset>
                            <legend>{{trans('restaurant')}} {{trans('lang.active_deactive')}}</legend>
                            <div class="form-group row">
                                <div class="form-group row width-50">
                                    <div class="form-check width-100">
                                        <input type="checkbox" id="is_open" checked>
                                        <label class="col-3 control-label" for="is_open">{{ trans('lang.open_closed') }}</label>
                                    </div>
                                </div>
                            </div>
                        </fieldset>


                        <fieldset id="dine_in_div" style="display: none;">
                            <legend>{{trans('lang.dine_in_future_setting')}}</legend>

                            <div class="form-group row">

                                <div class="form-group row width-100">
                                    <div class="form-check width-100">
                                        <input type="checkbox" id="dine_in_feature" class="">
                                        <label class="col-3 control-label"
                                            for="dine_in_feature">{{trans('lang.enable_dine_in_feature')}}</label>
                                    </div>
                                </div>
                                <div class="divein_div" style="display:none">


                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label">{{trans('lang.Opening_Time')}}</label>
                                        <div class="col-7">
                                            <input type="time" class="form-control" id="openDineTime" required>
                                        </div>
                                    </div>

                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label">{{trans('lang.Closing_Time')}}</label>
                                        <div class="col-7">
                                            <input type="time" class="form-control" id="closeDineTime" required>
                                        </div>
                                    </div>

                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label">Cost</label>
                                        <div class="col-7">
                                            <input type="number" class="form-control restaurant_cost" min="0" step="0.01" required>
                                        </div>
                                    </div>
                                    <div class="form-group row width-100 restaurant_image">
                                        <label class="col-3 control-label">Menu Card Images</label>
                                        <div class="">
                                            <div id="photos_menu_card"></div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div>
                                            <input type="file" onChange="handleFileSelectMenuCard(event)">
                                            <div id="uploaded_image_menu"></div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </fieldset>

                        <fieldset style="display: none;">
                            <legend>{{trans('lang.deliveryCharge')}}</legend>

                            <div class="form-group row">

                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{ trans('lang.delivery_charges_per')}} <span
                                            class="global_distance_type"></span></label>
                                    <div class="col-7">
                                        <input type="number" class="form-control" id="delivery_charges_per_km">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{trans('lang.minimum_delivery_charges')}}</label>
                                    <div class="col-7">
                                        <input type="number" class="form-control" id="minimum_delivery_charges">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label
                                        class="col-4 control-label">{{ trans('lang.minimum_delivery_charges_within')}}
                                        <span class="global_distance_type"></span></label>
                                    <div class="col-7">
                                        <input type="number" class="form-control"
                                            id="minimum_delivery_charges_within_km">
                                    </div>
                                </div>

                            </div>
                        </fieldset>
                        <fieldset>
                            <legend>{{trans('lang.special_offer')}}</legend>

                            <div class="form-group row">

                                <div class="form-check width-100">
                                    <input type="checkbox" id="specialDiscountEnable">
                                    <label class="col-3 control-label"
                                        for="specialDiscountEnable">{{trans('lang.special_discount_enable')}}</label>
                                </div>
                                <div class="form-group row width-100">
                                    <div class="col-7">
                                        <button type="button" class="btn btn-primary  add_special_offer_restaurant_btn">
                                            <i></i>{{trans('lang.add_special_offer')}}
                                        </button>
                                    </div>
                                </div>
                                <div class="special_offer_div" style="display:none">


                                    <div class="form-group row">
                                        <label class="col-1 control-label">{{trans('lang.sunday')}}</label>
                                        <div class="col-12">
                                            <button type="button" class="btn btn-primary add_more_sunday"
                                                onclick="addMoreButton('Sunday','sunday', '1')">
                                                {{trans('lang.add_more')}}
                                            </button>
                                        </div>
                                    </div>


                                    <div class="restaurant_discount_options_Sunday_div restaurant_discount"
                                        style="display:none">


                                        <table class="booking-table" id="special_offer_table_Sunday">
                                            <tr>
                                                <th>
                                                    <label
                                                        class="col-3 control-label">{{trans('lang.Opening_Time')}}</label>
                                                </th>
                                                <th>
                                                    <label
                                                        class="col-3 control-label">{{trans('lang.Closing_Time')}}</label>
                                                </th>
                                                <th>
                                                    <label
                                                        class="col-3 control-label">{{trans('lang.coupon_discount')}}</label>
                                                </th>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.coupon_discount')}}
                                                        {{trans('lang.type')}}</label>
                                                </th>

                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.actions')}}</label>
                                                </th>
                                            </tr>

                                        </table>

                                    </div>

                                    <div class="form-group row">
                                        <label class="col-1 control-label">{{trans('lang.monday')}}</label>
                                        <div class="col-12">
                                            <button type="button" class="btn btn-primary add_more_sunday"
                                                onclick="addMoreButton('Monday','monday', '1')">
                                                {{trans('lang.add_more')}}
                                            </button>
                                        </div>
                                    </div>

                                    <div class="restaurant_discount_options_Monday_div restaurant_discount"
                                        style="display:none">

                                        <table class="booking-table" id="special_offer_table_Monday">
                                            <tr>
                                                <th>
                                                    <label
                                                        class="col-3 control-label">{{trans('lang.Opening_Time')}}</label>
                                                </th>
                                                <th>
                                                    <label
                                                        class="col-3 control-label">{{trans('lang.Closing_Time')}}</label>
                                                </th>
                                                <th>
                                                    <label
                                                        class="col-3 control-label">{{trans('lang.coupon_discount')}}</label>
                                                </th>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.coupon_discount')}}
                                                        {{trans('lang.type')}}</label>
                                                </th>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.actions')}}</label>
                                                </th>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-1 control-label">{{trans('lang.tuesday')}}</label>
                                        <div class="col-12">
                                            <button type="button" class="btn btn-primary"
                                                onclick="addMoreButton('Tuesday','tuesday', '1')">
                                                {{trans('lang.add_more')}}
                                            </button>
                                        </div>
                                    </div>

                                    <div class="restaurant_discount_options_Tuesday_div restaurant_discount"
                                        style="display:none">

                                        <table class="booking-table" id="special_offer_table_Tuesday">
                                            <tr>
                                                <th>
                                                    <label
                                                        class="col-3 control-label">{{trans('lang.Opening_Time')}}</label>
                                                </th>
                                                <th>
                                                    <label
                                                        class="col-3 control-label">{{trans('lang.Closing_Time')}}</label>
                                                </th>
                                                <th>
                                                    <label
                                                        class="col-3 control-label">{{trans('lang.coupon_discount')}}</label>
                                                </th>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.coupon_discount')}}
                                                        {{trans('lang.type')}}</label>
                                                </th>

                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.actions')}}</label>
                                                </th>
                                            </tr>

                                        </table>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-1 control-label">{{trans('lang.wednesday')}}</label>
                                        <div class="col-12">
                                            <button type="button" class="btn btn-primary"
                                                onclick="addMoreButton('Wednesday','wednesday', '1')">
                                                {{trans('lang.add_more')}}
                                            </button>
                                        </div>
                                    </div>


                                    <div class="restaurant_discount_options_Wednesday_div restaurant_discount"
                                        style="display:none">
                                        <table class="booking-table" id="special_offer_table_Wednesday">
                                            <tr>
                                                <th>
                                                    <label
                                                        class="col-3 control-label">{{trans('lang.Opening_Time')}}</label>
                                                </th>
                                                <th>
                                                    <label
                                                        class="col-3 control-label">{{trans('lang.Closing_Time')}}</label>
                                                </th>
                                                <th>
                                                    <label
                                                        class="col-3 control-label">{{trans('lang.coupon_discount')}}</label>
                                                </th>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.coupon_discount')}}
                                                        {{trans('lang.type')}}</label>
                                                </th>

                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.actions')}}</label>
                                                </th>
                                            </tr>

                                        </table>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-1 control-label">{{trans('lang.thursday')}}</label>
                                        <div class="col-12">
                                            <button type="button" class="btn btn-primary"
                                                onclick="addMoreButton('Thursday','thursday', '1')">
                                                {{trans('lang.add_more')}}
                                            </button>
                                        </div>
                                    </div>

                                    <div class="restaurant_discount_options_Thursday_div restaurant_discount"
                                        style="display:none">
                                        <table class="booking-table" id="special_offer_table_Thursday">
                                            <tr>
                                                <th>
                                                    <label
                                                        class="col-3 control-label">{{trans('lang.Opening_Time')}}</label>
                                                </th>
                                                <th>
                                                    <label
                                                        class="col-3 control-label">{{trans('lang.Closing_Time')}}</label>
                                                </th>
                                                <th>
                                                    <label
                                                        class="col-3 control-label">{{trans('lang.coupon_discount')}}</label>
                                                </th>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.coupon_discount')}}
                                                        {{trans('lang.type')}}</label>
                                                </th>

                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.actions')}}</label>
                                                </th>
                                            </tr>

                                        </table>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-1 control-label">{{trans('lang.friday')}}</label>
                                        <div class="col-12">
                                            <button type="button" class="btn btn-primary"
                                                onclick="addMoreButton('Friday','friday', '1')">
                                                {{trans('lang.add_more')}}
                                            </button>
                                        </div>
                                    </div>

                                    <div class="restaurant_discount_options_Friday_div restaurant_discount"
                                        style="display:none">
                                        <table class="booking-table" id="special_offer_table_Friday">
                                            <tr>
                                                <th>
                                                    <label
                                                        class="col-3 control-label">{{trans('lang.Opening_Time')}}</label>
                                                </th>
                                                <th>
                                                    <label
                                                        class="col-3 control-label">{{trans('lang.Closing_Time')}}</label>
                                                </th>
                                                <th>
                                                    <label
                                                        class="col-3 control-label">{{trans('lang.coupon_discount')}}</label>
                                                </th>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.coupon_discount')}}
                                                        {{trans('lang.type')}}</label>
                                                </th>

                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.actions')}}</label>
                                                </th>
                                            </tr>

                                        </table>
                                    </div>


                                    <div class="form-group row">
                                        <label class="col-1 control-label">{{trans('lang.Saturday')}}</label>
                                        <div class="col-12">
                                            <button type="button" class="btn btn-primary"
                                                onclick="addMoreButton('Saturday','Saturday','1')">
                                                {{trans('lang.add_more')}}
                                            </button>
                                        </div>
                                    </div>
                                    <div class="restaurant_discount_options_Saturday_div restaurant_discount"
                                        style="display:none">
                                        <table class="booking-table" id="special_offer_table_Saturday">
                                            <tr>
                                                <th>
                                                    <label
                                                        class="col-3 control-label">{{trans('lang.Opening_Time')}}</label>
                                                </th>
                                                <th>
                                                    <label
                                                        class="col-3 control-label">{{trans('lang.Closing_Time')}}</label>
                                                </th>
                                                <th>
                                                    <label
                                                        class="col-3 control-label">{{trans('lang.coupon_discount')}}</label>
                                                </th>
                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.coupon_discount')}}
                                                        {{trans('lang.type')}}</label>
                                                </th>

                                                <th>
                                                    <label class="col-3 control-label">{{trans('lang.actions')}}</label>
                                                </th>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                            </div>

                        </fieldset>
                        <fieldset id="story_upload_div">
                            <legend>Story</legend>

                            <div class="form-group row width-50 vendor_image">
                                <label class="col-3 control-label">Choose humbling GIF/Image</label>
                                <div class="">
                                    <div id="story_thumbnail"></div>
                                </div>
                            </div>


                            <div class="form-group row">
                                <div>
                                    <input type="file" id="file" onChange="handleStoryThumbnailFileSelect(event)">
                                    <div id="uploding_story_thumbnail"></div>
                                </div>
                            </div>


                            <div class="form-group row vendor_image">
                                <label class="col-3 control-label">Select Story Video</label>
                                <div class="">
                                    <div id="story_vedios" class="row"></div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div>
                                    <input type="file" id="video_file" onChange="handleStoryFileSelect(event)">
                                    <div id="uploding_story_video"></div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>
            <div class="form-group col-12 text-center">
                <button type="button" class="btn btn-primary  save-form-btn"><i class="fa fa-save"></i>
                    {{trans('lang.save')}}
                </button>
                <a href="{!! route('marts') !!}" class="btn btn-default"><i
                        class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
            </div>

        </div>
    </div>
</div>

@endsection

<style>
/* Fix number input precision issues */
input[type="number"] {
    -moz-appearance: textfield;
}
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
/* Ensure proper display of selected categories */
.selected-category-tag {
    display: inline-block;
    background: #007bff;
    color: white;
    padding: 2px 8px;
    margin: 2px;
    border-radius: 12px;
    font-size: 12px;
}
.remove-tag {
    cursor: pointer;
    margin-left: 5px;
    font-weight: bold;
}
.remove-tag:hover {
    color: #ff6b6b;
}
/* Improve form validation feedback */
.form-control:invalid {
    border-color: #dc3545;
}
.form-control:valid {
    border-color: #28a745;
}
/* Loading state for save button */
.save-form-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
/* Required field indicator */
.required-field {
    color: #dc3545;
    font-weight: bold;
}
</style>

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/compressorjs/1.1.1/compressor.min.js"
    integrity="sha512-VaRptAfSxXFAv+vx33XixtIVT9A/9unb1Q8fp63y1ljF+Sbka+eMJWoDAArdm7jOYuLQHVx5v60TQ+t3EA8weA=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
    // MySQL data injected from controller
    window.phpZones = @json(isset($zones) ? $zones : []);
    window.phpVendors = @json(isset($vendors) ? $vendors : []);
    window.phpCategories = @json(isset($categories) ? $categories : []);
    (function preloadFromMySQL(){
        try{
            if (Array.isArray(window.phpZones)){
                window.phpZones.forEach(function(z){
                    $('#zone').append($('<option></option>').attr('value', z.id).text(z.name));
                });
            }
            if (Array.isArray(window.phpVendors)){
                window.phpVendors.forEach(function(v){
                    var name = [v.firstName||'', v.lastName||''].join(' ').trim() || 'Vendor';
                    $('#restaurant_vendors').append($('<option></option>').attr('value', v.id).text(name + (v.vendorID ? ' (Assigned)' : '')));
                });
            }
            if (Array.isArray(window.phpCategories)){
                window.phpCategories.forEach(function(c){
                    $('#restaurant_cuisines').append($('<option></option>').attr('value', c.id).text(c.title));
                });
                console.log('✅ Loaded ' + window.phpCategories.length + ' categories from PHP');
            }
        }catch(e){
            console.error('Error preloading data:', e);
        }
    })();
    // Generate UUID for restaurant_id (MySQL-based, no Firebase)
    function generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0;
            var v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
    var restaurant_id = generateUUID();

    // Firebase references removed - using MySQL now
    // var database=firebase.firestore(); // Not needed for MySQL
    // var storageRef=firebase.storage().ref('images'); // Using Laravel storage instead
    var photo="";
    var menuPhotoCount=0;
    var restaurantMenuPhotos="";
    var restaurant_menu_photos=[];
    var restaurant_menu_filename=[];
    var restaurantPhoto='';
    var resPhotoFileName='';
    var restaurantOwnerId="";
    var restaurantOwnerOnline=false;
    var photocount=0;
    var restaurnt_photos=[];
    var restaurant_photos_filename=[];
    var workingHours=[];
    var timeslotworkSunday=[];
    var timeslotworkMonday=[];
    var timeslotworkTuesday=[];
    var timeslotworkWednesday=[];
    var timeslotworkFriday=[];
    var timeslotworkSaturday=[];
    var timeslotworkThursday=[];

    var specialDiscount=[];
    var timeslotSunday=[];
    var timeslotMonday=[];
    var timeslotTuesday=[];
    var timeslotWednesday=[];
    var timeslotFriday=[];
    var timeslotSaturday=[];
    var timeslotThursday=[];
    var storevideoDuration=0;

    var story_vedios=[];
    var story_thumbnail='';
    var story_thumbnail_filename='';
    var storyCount=0;

    // Firebase settings removed - using MySQL now
    // Default values for admin commission (can be loaded from SQL if needed)
    $(".commission_fix").val(0);
    $("#commission_type").val("Percent");

    // Story feature is disabled during migration
    // $("#story_upload_div").hide(); // Already hidden by default
    var storevideoDuration = 30; // Default value

    // Zones are now preloaded from PHP (see window.phpZones above)
    // No need to fetch from Firebase

    // Email templates - not needed for mart creation
    var emailTemplatesData = null;
    var adminEmail = '';

    // Special discount - set default
    var specialDiscountOfferisEnable = false;

    // Currency - set defaults (can be loaded from SQL if needed)
    var currentCurrency = '$';
    var currencyAtRight = false;


    // Function to fix number input precision issues
    function fixNumberInputPrecision() {
        $('input[type="number"]').on('blur', function() {
            var value = parseFloat($(this).val());
            if (!isNaN(value)) {
                // Round to appropriate decimal places based on step attribute
                var step = $(this).attr('step');
                var className = $(this).attr('class');

                // Special handling for latitude and longitude - preserve decimal precision
                if (className && (className.includes('latitude') || className.includes('longitude'))) {
                    $(this).val(value.toFixed(6)); // Preserve 6 decimal places for coordinates
                } else if (step === '1') {
                    $(this).val(Math.round(value));
                } else if (step === '0.01') {
                    $(this).val(value.toFixed(2));
                } else if (step === 'any') {
                    $(this).val(value); // Don't round, just keep the original value
                } else {
                    $(this).val(value);
                }
            }
        });
    }





    $(document).ready(async function() {

        jQuery("#country_selector1").select2({
            templateResult: formatState,
            templateSelection: formatState2,
            placeholder: "Select Country",
            allowClear: true
        });

        jQuery("#data-table_processing").show();

        // Email templates and settings removed - not needed for mart creation
        // (Firebase references removed during migration to MySQL)

        // Categories are now preloaded from PHP (see window.phpCategories above)
        // Fallback AJAX load if needed
        if ($('#restaurant_cuisines option').length <= 1) {
            console.log('⚠️ Categories not preloaded, fetching via AJAX...');
            $.ajax({
                url: '{{ route("api.marts.categories") }}',
                method: 'GET',
                success: function(response) {
                    console.log('✅ Mart categories loaded from SQL:', response);
                    if (response.success && response.data) {
                        response.data.forEach(function(category) {
                            $('#restaurant_cuisines').append($("<option></option>")
                                .attr("value", category.id)
                                .text(category.title));
                        });
                        console.log('📦 Total categories loaded:', response.data.length);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error loading mart categories:', error);
                    alert('Failed to load categories. Please refresh the page.');
                }
            });
        }

        // Function to load vendors from SQL - Only show users with vType='mart' AND role='vendor'
        function loadVendors() {
            console.log('🔍 Loading mart vendors from SQL...');

            $.ajax({
                url: '{{ route("api.marts.vendors") }}',
                method: 'GET',
                success: function(response) {
                    console.log('✅ Mart vendors loaded from SQL:', response);

                    if (response.success && response.data) {
                        var vendors = response.data;
                        console.log('📦 Total vendors found:', vendors.length);

                        if (vendors.length === 0) {
                            $('#restaurant_vendors').append($("<option></option>")
                                .attr("value", "")
                                .text("No mart vendors available"));
                        } else {
                            vendors.forEach(function(vendor) {
                                var displayText = vendor.name || (vendor.firstName + ' ' + (vendor.lastName || '')).trim();

                                // Mark if already assigned to a mart
                                if (vendor.vendorID && vendor.vendorID.trim() !== '') {
                                    displayText += " (Assigned)";
                                }

                                $('#restaurant_vendors').append($("<option></option>")
                                    .attr("value", vendor.id)
                                    .text(displayText));

                                console.log('➕ Added vendor:', displayText, '(ID:', vendor.id + ')');
                            });
                        }
                    } else {
                        console.error('❌ Invalid response from server');
                        $('#restaurant_vendors').append($("<option></option>")
                            .attr("value", "")
                            .text("Error loading vendors"));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error loading mart vendors:', error);
                    $('#restaurant_vendors').append($("<option></option>")
                        .attr("value", "")
                        .text("Error loading vendors: " + error));
                    alert('Failed to load vendors. Please refresh the page.');
                }
            });
        }

        // Load vendors initially
        loadVendors();
        jQuery("#data-table_processing").hide();

        // Initialize number input precision fix
        fixNumberInputPrecision();



        // Delivery charges - set default values (Firebase removed)
        // Can be loaded from SQL settings if needed
        try {
            $("#delivery_charges_per_km").val(5);
            $("#minimum_delivery_charges").val(10);
            $("#minimum_delivery_charges_within_km").val(5);
        } catch(error) {
            console.error('Error setting delivery charges:', error);
        }

        // 1. Filter dropdown options based on search
        $('#category_search').on('keyup', function() {
            var search = $(this).val().toLowerCase();
            $('#restaurant_cuisines option').each(function() {
                if ($(this).val() === "") {
                    $(this).show();
                    return;
                }
                var text = $(this).text().toLowerCase();
                if (text.indexOf(search) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // 2. When selecting from dropdown, add tag (multi-select support)
        $('#restaurant_cuisines').on('change', function() {
            updateSelectedCategoryTags();
        });

        // 3. Remove tag and unselect in dropdown
        $('#selected_categories').on('click', '.remove-tag', function() {
            var value = $(this).parent().data('value');
            $('#restaurant_cuisines option[value="' + value + '"]').prop('selected', false);
            updateSelectedCategoryTags();
        });

        // 4. Update tags display
        function updateSelectedCategoryTags() {
            var selected = $('#restaurant_cuisines').val() || [];
            var html = '';
            $('#restaurant_cuisines option:selected').each(function() {
                if ($(this).val() !== "") {
                    html += '<span class="selected-category-tag" data-value="' + $(this).val() + '">' +
                        $(this).text() +
                        '<span class="remove-tag">&times;</span></span>';
                }
            });
            $('#selected_categories').html(html);
        }

        // 5. Preselect for edit (if you have preselected IDs)
        // Example: var preselected = ['1','3'];
        // $('#restaurant_cuisines').val(preselected).trigger('change');
    })

    function checkLocationInZone(area,address_lng,address_lat) {
        var vertices_x=[];
        var vertices_y=[];
        for(j=0;j<area.length;j++) {
            var geopoint=area[j];
            vertices_x.push(geopoint.longitude);
            vertices_y.push(geopoint.latitude);
        }
        var points_polygon=(vertices_x.length)-1;
        if(is_in_polygon(points_polygon,vertices_x,vertices_y,address_lng,address_lat)) {
            return true;
        } else {
            return false;
        }
    }

    function is_in_polygon($points_polygon,$vertices_x,$vertices_y,$longitude_x,$latitude_y) {
        $i=$j=$c=$point=0;
        for($i=0,$j=$points_polygon;$i<$points_polygon;$j=$i++) {
            $point=$i;
            if($point==$points_polygon)
                $point=0;
            if((($vertices_y[$point]>$latitude_y!=($vertices_y[$j]>$latitude_y))&&($longitude_x<($vertices_x[$j]-$vertices_x[$point])*($latitude_y-$vertices_y[$point])/($vertices_y[$j]-$vertices_y[$point])+$vertices_x[$point])))
                $c=!$c;
        }
        return $c;
    }

    $(".save-form-btn").click(async function() {

        $(".error_top").hide();

        var restaurantname=$(".mart_name").val();
        // Handle multiple category selection
        var categoryIDs = $("#restaurant_cuisines").val() || [];
        var categoryTitles = [];
        $("#restaurant_cuisines option:selected").each(function() {
            if ($(this).val() !== "") {
                categoryTitles.push($(this).text());
            }
        });
        var restaurantOwner=$("#restaurant_owners option:selected").val();
        var address=$(".restaurant_address").val();
        var latitude=parseFloat($(".restaurant_latitude").val()) || 0;
        var longitude=parseFloat($(".restaurant_longitude").val()) || 0;
        var description=$(".restaurant_description").val();
        var rescountry_code=$("#country_selector1").val();
        var phonenumber=$(".restaurant_phone").val();
        var commissionType=$("#commission_type").val();
        var fixCommission=$(".commission_fix").val();
        var adminCommissionValue=$(".admin_commission").val();
        var zoneId=$('#zone option:selected').val();
        var zoneArea=$('#zone option:selected').data('area');
        // When no polygon is available (MySQL build), allow saving
        var isInZone=true;
        try{
            if(zoneId && zoneArea){
                // Ensure array of {latitude,longitude}
                if (typeof zoneArea === 'string') { zoneArea = JSON.parse(zoneArea); }
                if (Array.isArray(zoneArea) && zoneArea.length>0){
                    isInZone = checkLocationInZone(zoneArea, longitude, latitude);
                }
            }
        }catch(e){ isInZone=true; }

        var enabledDiveInFuture=$("#dine_in_feature").is(':checked');
        var isOpen = $("#is_open").is(':checked');
        var specialDiscountEnable=false;
        var restaurantCost=parseFloat($(".restaurant_cost").val()) || 0;

        var selectedOwnerId = $("#restaurant_vendors option:selected").val();
        console.log('Selected vendor ID:', selectedOwnerId);
        console.log('All vendor options:', $('#restaurant_vendors option').map(function() { return {value: $(this).val(), text: $(this).text()}; }).get());
        console.log('Selected vendor text:', $("#restaurant_vendors option:selected").text());

        // Check if vendor is selected and valid
        if(selectedOwnerId && selectedOwnerId != '' && selectedOwnerId != null && selectedOwnerId != undefined && selectedOwnerId !== "No vendors available" && selectedOwnerId !== "Error loading vendors") {

            // Handle assigned vendors (marked with |assigned)
            var actualVendorId = selectedOwnerId;
            var isAssignedVendor = false;

            if (selectedOwnerId.includes('|assigned')) {
                actualVendorId = selectedOwnerId.split('|')[0];
                isAssignedVendor = true;
                console.log('Selected vendor is assigned to existing mart. Actual vendor ID:', actualVendorId);
            }
            var vendorData=await getOwnerDetails(selectedOwnerId);
            console.log('Vendor data:', vendorData);
            if(vendorData != undefined && vendorData != null && vendorData != ""){
                var user_name=vendorData.firstName +" "+vendorData.lastName;
                var subscriptionPlanId = vendorData.subscriptionPlanId ? vendorData.subscriptionPlanId : null;
                var subscription_plan = vendorData.subscription_plan ? vendorData.subscription_plan : null;
                var subscriptionOrderLimit=vendorData.subscription_plan ? vendorData.subscription_plan.orderLimit : null;
                var subscriptionExpiryDate = vendorData.subscriptionExpiryDate ? vendorData.subscriptionExpiryDate : null;
                var user_id = vendorData.id;
                var user_profilepic = vendorData.profilePictureURL ? vendorData.profilePictureURL : null;
            } else{
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.owner_detail_not_fount')}}</p>");
                window.scrollTo(0,0);
                return;
            }
        } else {
            // Require vendor selection for mart creation
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>Please select a vendor for this mart.</p>");
            window.scrollTo(0,0);
            return;
        }


        var openDineTime=$("#openDineTime").val();
        var openDineTime_val=$("#openDineTime").val();
        if(openDineTime) {
            openDineTime=new Date('1970-01-01T'+openDineTime+'Z')
                .toLocaleTimeString('en-US',{
                    timeZone: 'UTC',
                    hour12: true,
                    hour: 'numeric',
                    minute: 'numeric'
                });
        }

        var closeDineTime=$("#closeDineTime").val();
        var closeDineTime_val=$("#closeDineTime").val();
        if(closeDineTime) {
            closeDineTime=new Date('1970-01-01T'+closeDineTime+'Z')
                .toLocaleTimeString('en-US',{
                    timeZone: 'UTC',
                    hour12: true,
                    hour: 'numeric',
                    minute: 'numeric'
                });
        }

        if($("#specialDiscountEnable").is(':checked')) {
            specialDiscountEnable=true;
        }
        var specialDiscount=[];

        var sunday={
            'day': 'Sunday',
            'timeslot': timeslotSunday
        };
        var monday={
            'day': 'Monday',
            'timeslot': timeslotMonday
        };
        var tuesday={
            'day': 'Tuesday',
            'timeslot': timeslotTuesday
        };
        var wednesday={
            'day': 'Wednesday',
            'timeslot': timeslotWednesday
        };
        var thursday={
            'day': 'Thursday',
            'timeslot': timeslotThursday
        };
        var friday={
            'day': 'Friday',
            'timeslot': timeslotFriday
        };
        var Saturday={
            'day': 'Saturday',
            'timeslot': timeslotSaturday
        };

        specialDiscount.push(monday);
        specialDiscount.push(tuesday);
        specialDiscount.push(wednesday);
        specialDiscount.push(thursday);
        specialDiscount.push(friday);
        specialDiscount.push(Saturday);
        specialDiscount.push(sunday);

        var workingHours=[];

        var sunday={
            'day': 'Sunday',
            'timeslot': timeslotworkSunday
        };
        var monday={
            'day': 'Monday',
            'timeslot': timeslotworkMonday
        };
        var tuesday={
            'day': 'Tuesday',
            'timeslot': timeslotworkTuesday
        };
        var wednesday={
            'day': 'Wednesday',
            'timeslot': timeslotworkWednesday
        };
        var thursday={
            'day': 'Thursday',
            'timeslot': timeslotworkThursday
        };
        var friday={
            'day': 'Friday',
            'timeslot': timeslotworkFriday
        };
        var Saturday={
            'day': 'Saturday',
            'timeslot': timeslotworkSaturday
        };

        workingHours.push(monday);
        workingHours.push(tuesday);
        workingHours.push(wednesday);
        workingHours.push(thursday);
        workingHours.push(friday);
        workingHours.push(Saturday);
        workingHours.push(sunday);

        // Check if any working hours are set, if not set default times
        var hasWorkingHours = false;
        for (var i = 0; i < workingHours.length; i++) {
            if (workingHours[i].timeslot.length > 0) {
                hasWorkingHours = true;
                break;
            }
        }

        // If no working hours are set, add default times (9:30 AM to 10:00 PM) for all days
        if (!hasWorkingHours) {
            var defaultTimeslot = {
                'from': '09:30',
                'to': '22:00'
            };

            for (var i = 0; i < workingHours.length; i++) {
                workingHours[i].timeslot = [defaultTimeslot];
            }
        }

        var Free_Wi_Fi="No";
        if($("#Free_Wi_Fi").is(":checked")) {
            Free_Wi_Fi="Yes";
        }
        var Good_for_Breakfast="No";
        if($("#Good_for_Breakfast").is(':checked')) {
            Good_for_Breakfast="Yes";
        }
        var Good_for_Dinner="No";
        if($("#Good_for_Dinner").is(':checked')) {
            Good_for_Dinner="Yes";
        }
        var Good_for_Lunch="No";
        if($("#Good_for_Lunch").is(':checked')) {
            Good_for_Lunch="Yes";
        }
        var Live_Music="No";
        if($("#Live_Music").is(':checked')) {
            Live_Music="Yes";
        }
        var Outdoor_Seating="No";
        if($("#Outdoor_Seating").is(':checked')) {
            Outdoor_Seating="Yes";
        }
        var Takes_Reservations="No";
        if($("#Takes_Reservations").is(':checked')) {
            Takes_Reservations="Yes";
        }
        var Vegetarian_Friendly="No";
        if($("#Vegetarian_Friendly").is(':checked')) {
            Vegetarian_Friendly="Yes";
        }

        var filters_new={
            "Free Wi-Fi": Free_Wi_Fi,
            "Good for Breakfast": Good_for_Breakfast,
            "Good for Dinner": Good_for_Dinner,
            "Good for Lunch": Good_for_Lunch,
            "Live Music": Live_Music,
            "Outdoor Seating": Outdoor_Seating,
            "Takes Reservations": Takes_Reservations,
            "Vegetarian Friendly": Vegetarian_Friendly
        };

        if(!restaurantname || restaurantname.trim()=='') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.mart_name_error')}}</p>");
            window.scrollTo(0,0);
        } else if(!selectedOwnerId || selectedOwnerId == '' || selectedOwnerId == null || selectedOwnerId == undefined || selectedOwnerId === "No vendors available" || selectedOwnerId === "Error loading vendors") {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>Please select a vendor for this mart.</p>");
            window.scrollTo(0,0);
        } else if(categoryIDs.length === 0 || (categoryIDs.length === 1 && categoryIDs[0] === '')) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.mart_cuisine_error')}}</p>");
            window.scrollTo(0,0);
        } else if(!rescountry_code) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.select_rescountry_code')}}</p>");
            window.scrollTo(0,0);
        } else if(phonenumber=='') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.mart_phone_error')}}</p>");
            window.scrollTo(0,0);
        } else if(address=='') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.mart_address_error')}}</p>");
            window.scrollTo(0,0);
        } else if(zoneId=='') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.select_zone_help')}}</p>");
            window.scrollTo(0,0);
        } else if(isNaN(latitude)) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.mart_latitude_error')}}</p>");
            window.scrollTo(0,0);
        } else if(latitude<-90||latitude>90) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.mart_latitude_limit_error')}}</p>");
            window.scrollTo(0,0);
        } else if(isNaN(longitude)) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.mart_longitude_error')}}</p>");
            window.scrollTo(0,0);
        } else if(longitude<-180||longitude>180) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.mart_longitude_limit_error')}}</p>");
            window.scrollTo(0,0);
        } else if(isInZone==false) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.invalid_location_zone')}}</p>");
            window.scrollTo(0,0);
        } else if(!description || description.trim()=='') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.mart_description_error')}}</p>");
            window.scrollTo(0,0);

        } else {
            console.log('Starting mart creation process...');
            console.log('Mart name:', restaurantname);
            console.log('Category IDs:', categoryIDs);
            console.log('Category Titles:', categoryTitles);
            console.log('Working Hours:', workingHours);

            // Show loading state
            jQuery("#data-table_processing").show();
            $('.save-form-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Creating Mart...');

            // Add timeout to prevent infinite loading
            var saveTimeout = setTimeout(function() {
                jQuery("#data-table_processing").hide();
                $('.save-form-btn').prop('disabled', false).html('<i class="fa fa-save"></i> {{trans("lang.save")}}');
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>Request timed out. Please try again.</p>");
                window.scrollTo(0,0);
            }, 60000); // 60 seconds timeout

            // Story feature disabled during Firebase to SQL migration
            // if(story_vedios.length>0||story_thumbnail!='') { ... }

            var delivery_charges_per_km=parseInt($("#delivery_charges_per_km").val());
            var minimum_delivery_charges=parseInt($("#minimum_delivery_charges").val());
            var minimum_delivery_charges_within_km=parseInt($("#minimum_delivery_charges_within_km").val());
            var DeliveryCharge={
                'delivery_charges_per_km': delivery_charges_per_km,
                'minimum_delivery_charges': minimum_delivery_charges,
                'minimum_delivery_charges_within_km': minimum_delivery_charges_within_km
            };
            // Handle admin commission - use the new field if available, otherwise fall back to the old one
            var commissionValue = adminCommissionValue || fixCommission || 0;
            // Ensure commission value is a valid number
            if (isNaN(commissionValue) || commissionValue < 0) {
                commissionValue = 0;
            }
            const adminCommission = {
                "commissionType": commissionType || "Percent",
                "fix_commission": parseInt(commissionValue) || 0,
                "isEnabled": true
            };
            try {
                // Store all images
                console.log('Storing images...');

                let IMG, GalleryIMG, MenuIMG;
                try {
                    IMG = await storeImageData();
                    console.log('Story image stored successfully');
                } catch (error) {
                    console.error('Error storing story image:', error);
                    IMG = { storyThumbnailImage: '' };
                }

                try {
                    GalleryIMG = await storeGalleryImageData();
                    console.log('Gallery images stored successfully:', GalleryIMG);
                } catch (error) {
                    console.error('Error storing gallery images:', error);
                    GalleryIMG = [];
                }

                try {
                    MenuIMG = await storeMenuImageData();
                    console.log('Menu images stored successfully:', MenuIMG);
                } catch (error) {
                    console.error('Error storing menu images:', error);
                    MenuIMG = [];
                }

                // Create mart data with proper validation
                // No Firebase GeoPoint needed - MySQL uses separate latitude/longitude columns

                // Debug logging to check values
                console.log('Debug - restaurantname:', restaurantname);
                console.log('Debug - description:', description);
                console.log('Debug - latitude:', latitude);
                console.log('Debug - longitude:', longitude);
                console.log('Debug - address:', address);
                console.log('Debug - categoryIDs:', categoryIDs);
                console.log('Debug - categoryTitles:', categoryTitles);
                console.log('Debug - rescountry_code:', rescountry_code);
                console.log('Debug - phonenumber:', phonenumber);
                console.log('Debug - user_id:', user_id);
                console.log('Debug - user_name:', user_name);
                console.log('Debug - adminCommission:', adminCommission);

                // Final validation before creating mart
                if (!restaurantname || !description || !address || !zoneId) {
                    throw new Error('Required fields are missing. Please check all required fields.');
                }

                if (!categoryIDs || categoryIDs.length === 0 || (categoryIDs.length === 1 && categoryIDs[0] === '')) {
                    throw new Error('Please select at least one category.');
                }

                const martData = {
                    'title': restaurantname || '',
                    'description': description || '',
                    'latitude': latitude || 0,
                    'longitude': longitude || 0,
                    'location': address || '',
                    'photo': (Array.isArray(GalleryIMG) && GalleryIMG.length > 0) ? GalleryIMG[0] : null,
                    'categoryID': categoryIDs || [],
                    'categoryTitle': categoryTitles || [],
                    'countryCode': rescountry_code || '',
                    'phonenumber': phonenumber || '',
                    // No Firebase GeoPoint needed - MySQL uses separate lat/lng columns
                    'id': restaurant_id,
                    'vType': 'mart', // This is crucial for mart filtering
                    'filters': filters_new || {},
                    'photos': GalleryIMG || [],
                    'author': user_id || '',
                    'authorName': user_name || '',
                    'authorProfilePic': user_profilepic || null,
                    'isOpen': isOpen || false,
                    'hidephotos': false,
                    // createdAt will be set by MySQL (or leave empty for controller to handle)
                    'createdAt': '',
                    'enabledDelivery': enabledDiveInFuture || false,
                    'restaurantMenuPhotos': MenuIMG || [],
                    'restaurantCost': restaurantCost || 0,
                    'openDineTime': openDineTime || null,
                    'closeDineTime': closeDineTime || null,
                    'workingHours': workingHours || [],
                    'specialDiscount': specialDiscount || [],
                    'specialDiscountEnable': specialDiscountEnable || false,
                    'zoneId': zoneId || '',
                    'adminCommission': adminCommission || {commissionType: "Percent", fix_commission: 0, isEnabled: true},
                    'subscription_plan': subscription_plan || null,
                    'subscriptionPlanId': subscriptionPlanId || null,
                    'subscriptionExpiryDate': subscriptionExpiryDate || null,
                    'subscriptionTotalOrders': subscriptionOrderLimit || null
                };

                // Update user vendorID if not admin created
                // SQL controller will link user->vendorID when author is provided

                // Create mart via MySQL endpoint
                try {
                    await $.ajax({
                        url: '{{ route("marts.store") }}',
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: Object.assign({}, martData, {
                            createdAt: '',
                            // Coerce complex fields to JSON strings for the controller to normalize
                            categoryID: JSON.stringify(martData.categoryID || []),
                            categoryTitle: JSON.stringify(martData.categoryTitle || []),
                            photos: JSON.stringify(martData.photos || []),
                            restaurantMenuPhotos: JSON.stringify(martData.restaurantMenuPhotos || []),
                            filters: JSON.stringify(martData.filters || {}),
                            workingHours: JSON.stringify(martData.workingHours || []),
                            specialDiscount: JSON.stringify(martData.specialDiscount || []),
                            adminCommission: JSON.stringify(martData.adminCommission || {})
                        })
                    });
                    console.log('Mart created successfully (MySQL)');
                } catch (error) {
                    try {
                        console.error('Error creating mart (MySQL):', error);
                        var msg = (error && error.responseJSON && error.responseJSON.error) ? error.responseJSON.error : (error.responseText || 'Failed to save mart data');
                        alert('Create failed: ' + msg);
                    } catch(e) {}
                    throw new Error('Failed to save mart data');
                }

                console.log('✅ Mart saved successfully, now logging activity...');
                try {
                    if (typeof logActivity === 'function') {
                        console.log('🔍 Calling logActivity for mart creation...');
                        await logActivity('marts', 'created', 'Created new mart: ' + restaurantname);
                        console.log('✅ Activity logging completed successfully');
                    } else {
                        console.error('❌ logActivity function is not available');
                    }
                } catch (error) {
                    console.error('❌ Error calling logActivity:', error);
                }

                clearTimeout(saveTimeout);
                jQuery("#data-table_processing").hide();
                $('.save-form-btn').prop('disabled', false).html('<i class="fa fa-save"></i> {{trans("lang.save")}}');
                window.location.href = '{{ route("marts")}}';

            } catch (error) {
                clearTimeout(saveTimeout);
                console.error('Error in mart creation process:', error);
                jQuery("#data-table_processing").hide();
                $('.save-form-btn').prop('disabled', false).html('<i class="fa fa-save"></i> {{trans("lang.save")}}');
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>Error creating mart: " + error.message + "</p>");
                window.scrollTo(0, 0);
            }

        }

    })


    $(document).on("click",".remove-btn",function() {
        var id=$(this).attr('data-id');
        var photo_remove=$(this).attr('data-img');

        $("#photo_"+id).remove();
        index=restaurnt_photos.indexOf(photo_remove);
        if(index>-1) {
            restaurnt_photos.splice(index,1); // 2nd parameter means remove one item only
        }

    });
    $(document).on("click",".remove-menu-btn",function() {
        var id=$(this).attr('data-id');
        var photo_remove=$(this).attr('data-img');

        $("#photo_menu"+id).remove();
        index=restaurant_menu_photos.indexOf(photo_remove);
        if(index>-1) {
            restaurant_menu_photos.splice(index,1); // 2nd parameter means remove one item only
        }

    });

    async function storeImageData() {
        var newPhoto=[];
        newPhoto['storyThumbnailImage']='';
        try {
            if(story_thumbnail!='') {
                // Story feature disabled during migration - return empty
                console.log('Story feature is disabled during migration');
                // If you want to enable it later, use Laravel storage API:
                // const response = await $.ajax({
                //     url: '{{ route("api.upload.image") }}',
                //     method: 'POST',
                //     headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                //     data: {
                //         image: story_thumbnail,
                //         folder: 'marts/story',
                //         filename: story_thumbnail_filename || ('story_' + Date.now() + '.jpg')
                //     }
                // });
                // if (response.success && response.url) {
                //     newPhoto['storyThumbnailImage'] = response.url;
                // }
            }
        } catch(error) {
            console.log("ERR ===",error);
        }
        return newPhoto;
    }

    function handleFileSelect(evt,type) {
        var f=evt.target.files[0];
        var reader=new FileReader();

        new Compressor(f,{
            quality: <?php echo env('IMAGE_COMPRESSOR_QUALITY', 0.8); ?>,
            success(result) {
                f=result;

                reader.onload=(function(theFile) {
                    return function(e) {

                        var filePayload=e.target.result;
                        var val=f.name;
                        var ext=val.split('.')[1];
                        var docName=val.split('fakepath')[1];
                        var filename=(f.name).replace(/C:\\fakepath\\/i,'')

                        var timestamp=Number(new Date());
                        var filename=filename.split('.')[0]+"_"+timestamp+'.'+ext;
                        photo=filePayload;
                        // if (type == "photo") {
                        //     restaurantPhoto = filePayload;
                        //     resPhotoFileName = filename;
                        // }
                        if(photo) {
                            // if (type == 'photo') {
                            //     $("#uploaded_image").attr('src', photo);
                            //     $(".uploaded_image").show();
                            // } else
                            if(type=='photos') {

                                photocount++;
                                photos_html='<span class="image-item" id="photo_'+photocount+'"><span class="remove-btn" data-id="'+photocount+'" data-img="'+photo+'"><i class="fa fa-remove"></i></span><img width="100px" id="" height="auto" src="'+photo+'"></span>';
                                $("#photos").append(photos_html);
                                restaurnt_photos.push(photo);
                                restaurant_photos_filename.push(filename);
                            }
                        }
                    };
                })(f);
                reader.readAsDataURL(f);
            },
            error(err) {
                console.log(err.message);
            },
        });
    }

    function chkAlphabets2(event,msg) {
        if(!(event.which>=48&&event.which<=57)
        ) {
            document.getElementById(msg).innerHTML="Accept only Number";
            return false;
        }
        else {
            document.getElementById(msg).innerHTML="";
            return true;
        }
    }

    function formatState(state) {
        if(!state.id) {
            return state.text;
        }
        var baseUrl="<?php echo URL::to('/');?>/scss/icons/flag-icon-css/flags";
        var $state=$(
            '<span><img src="'+baseUrl+'/'+newcountriesjs[state.element.value].toLowerCase()+'.svg" class="img-flag" /> '+state.text+'</span>'
        );
        return $state;
    }

    function formatState2(state) {
        if(!state.id) {
            return state.text;
        }

        var baseUrl="<?php echo URL::to('/');?>/scss/icons/flag-icon-css/flags"
        var $state=$(
            '<span><img class="img-flag" /> <span></span></span>'
        );
        $state.find("span").text(state.text);
        $state.find("img").attr("src",baseUrl+"/"+newcountriesjs[state.element.value].toLowerCase()+".svg");

        return $state;
    }
    var newcountriesjs='<?php echo json_encode($newcountriesjs); ?>';
    var newcountriesjs=JSON.parse(newcountriesjs);

    async function storeGalleryImageData() {
        var newPhoto=[];
        try {
            if(restaurnt_photos.length>0) {
                const photoPromises=restaurnt_photos.map(async (resPhoto,index) => {
                    // Use Laravel storage API instead of Firebase
                    const base64Data = resPhoto; // Keep full data URL for API
                    const filename = restaurant_photos_filename[index] || ('gallery_' + Date.now() + '_' + index + '.jpg');

                    try {
                        const response = await $.ajax({
                            url: '{{ route("api.upload.image") }}',
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: {
                                image: base64Data,
                                folder: 'marts/gallery',
                                filename: filename
                            }
                        });

                        if (response.success && response.url) {
                            return {index, downloadURL: response.url};
                        } else {
                            throw new Error('Upload failed: ' + (response.message || 'Unknown error'));
                        }
                    } catch(uploadError) {
                        console.error('Error uploading image ' + index + ':', uploadError);
                        throw uploadError;
                    }
                });
                const photoResults=await Promise.all(photoPromises);
                photoResults.sort((a,b) => a.index-b.index);
                newPhoto=photoResults.map(photo => photo.downloadURL);
            }
            return newPhoto;
        } catch(error) {
            console.error('Error storing gallery images:', error);
            throw error;
        }
    }

    function handleFileSelectGallary(evt,type) {
        var f=evt.target.files[0];
        var reader=new FileReader();

        new Compressor(f,{
            quality: <?php echo env('IMAGE_COMPRESSOR_QUALITY', 0.8); ?>,
            success(result) {
                f=result;

                reader.onload=(function(theFile) {
                    return function(e) {

                        var filePayload=e.target.result;
                        var val=f.name;
                        var ext=val.split('.')[1];
                        var docName=val.split('fakepath')[1];
                        var filename=(f.name).replace(/C:\\fakepath\\/i,'')

                        var timestamp=Number(new Date());
                        var filename=filename.split('.')[0]+"_"+timestamp+'.'+ext;
                        photo=filePayload;
                        if(photo) {
                            photocount++;
                            photos_html='<span class="image-item" id="photo_'+photocount+'"><span class="remove-btn" data-id="'+photocount+'" data-img="'+photo+'"><i class="fa fa-remove"></i></span><img width="100px" id="" height="auto" src="'+photo+'"></span>';
                            $("#photos").append(photos_html);
                            restaurnt_photos.push(photo);
                            restaurant_photos_filename.push(filename);
                        }
                    };
                })(f);
                reader.readAsDataURL(f);
            },
            error(err) {
                console.log(err.message);
            },
        });
    }


    function handleStoryFileSelect(evt) {
        var f=evt.target.files[0];
        var reader=new FileReader();
        var story_video_duration=$("#story_video_duration").val();
        var isVideo=document.getElementById('video_file');
        var videoValue=isVideo.value;
        var allowedExtensions=/(\.mp4)$/i;;

        if(!allowedExtensions.exec(videoValue)) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>Error: Invalid video type</p>");
            window.scrollTo(0,0);
            isVideo.value='';
            return false;
        }

        var video=document.createElement('video');


        video.preload='metadata';

        video.onloadedmetadata=function() {

            window.URL.revokeObjectURL(video.src);


            if(video.duration>storevideoDuration) {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>Error: Story video duration maximum allow to "+storevideoDuration+" seconds</p>");
                window.scrollTo(0,0);
                evt.target.value='';
                return false;
            }

            reader.onload=(function(theFile) {
                return function(e) {

                    var filePayload=e.target.result;
                    var val=f.name;
                    var ext=val.split('.')[1];
                    var docName=val.split('fakepath')[1];
                    var filename=(f.name).replace(/C:\\fakepath\\/i,'')

                    var timestamp=Number(new Date());
                    var filename=filename.split('.')[0]+"_"+timestamp+'.'+ext;

                    var uploadTask=storyRef.child(filename).put(theFile);
                    uploadTask.on('state_changed',function(snapshot) {

                        var progress=(snapshot.bytesTransferred/snapshot.totalBytes)*100;
                        console.log('Upload is '+progress+'% done');
                        jQuery("#uploding_story_video").text("video is uploading...");
                    },function(error) {},function() {
                        uploadTask.snapshot.ref.getDownloadURL().then(function(downloadURL) {
                            jQuery("#uploding_story_video").text("Upload is completed");
                            setTimeout(function() {
                                jQuery("#uploding_story_video").empty();
                            },3000);

                            var nextCount=$("#story_vedios").children().length;
                            html='<div class="col-md-3" id="story_div_'+nextCount+'">\n'+
                                '<div class="video-inner"><video width="320px" height="240px"\n'+
                                '                                   controls="controls">\n'+
                                '                            <source src="'+downloadURL+'"\n'+
                                '            type="video/mp4"></video><span class="remove-story-video" data-id="'+nextCount+'" data-img="'+downloadURL+'"><i class="fa fa-remove"></i></span></div></div>';

                            jQuery("#story_vedios").append(html);
                            story_vedios.push(downloadURL);
                            $("#video_file").val('');
                        });
                    });

                };
            })(f);
            reader.readAsDataURL(f);
        }
        video.src=URL.createObjectURL(f);
    }


    $(document).on("click",".remove-story-video",function() {
        var id=$(this).attr('data-id');
        var photo_remove=$(this).attr('data-img');

        $("#story_div_"+id).remove();
        index=story_vedios.indexOf(photo_remove);
        $("#video_file").val('');
        if(index>-1) {
            story_vedios.splice(index,1); // 2nd parameter means remove one item only
        }

        var newhtml='';
        if(story_vedios.length>0) {
            for(var i=0;i<story_vedios.length;i++) {
                newhtml+='<div class="col-md-3" id="story_div_'+i+'">\n'+
                    '<div class="video-inner"><video width="320px" height="240px"\n'+
                    'controls="controls">\n'+
                    '<source src="'+story_vedios[i]+'"\n'+
                    'type="video/mp4"></video><span class="remove-story-video" data-id="'+i+'" data-img="'+story_vedios[i]+'"><i class="fa fa-remove"></i></span></div></div>';
            }
        }
        jQuery("#story_vedios").html(newhtml);
        deleteStoryfromCollection();
    });

    $(document).on("click",".remove-story-thumbnail",function() {
        var photo_remove=$(this).attr('data-img');

        $("#story_thumbnail").empty();
        $('#file').val('');
        story_thumbnail='';
        deleteStoryfromCollection();
    });

    function deleteStoryfromCollection() {
        // Story feature disabled during migration
        // Firebase story collection removed
        console.log('Story feature is disabled');
    }
    async function storeStoryImageData() {
        var newPhoto=[];
        newPhoto['storyThumbnailImage']='';
        try {
            if(story_thumbnail!='') {
                story_thumbnail=story_thumbnail.replace(/^data:image\/[a-z]+;base64,/,"")
                var uploadTask=await storageRef.child(story_thumbnail_filename).putString(story_thumbnail,'base64',{
                    contentType: 'image/jpg'
                });
                var downloadURL=await uploadTask.ref.getDownloadURL();
                newPhoto['storyThumbnailImage']=downloadURL;
            }
        } catch(error) {
            console.log("ERR ===",error);
        }
        return newPhoto;
    }

    function handleStoryThumbnailFileSelect(evt) {

        var f=evt.target.files[0];
        var reader=new FileReader();

        var fileInput=
            document.getElementById('file');

        var filePath=fileInput.value;

        // Allowing file type
        var allowedExtensions=/(\.jpg|\.jpeg|\.png|\.gif)$/i;;

        if(!allowedExtensions.exec(filePath)) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>Error: Invalid File type</p>");
            window.scrollTo(0,0);
            fileInput.value='';
            return false;
        }
        reader.onload=(function(theFile) {
            return function(e) {

                var filePayload=e.target.result;
                var val=f.name;
                var ext=val.split('.')[1];
                var docName=val.split('fakepath')[1];
                var filename=(f.name).replace(/C:\\fakepath\\/i,'')

                var timestamp=Number(new Date());
                var filename=filename.split('.')[0]+"_"+timestamp+'.'+ext;
                story_thumbnail=filePayload;
                story_thumbnail_filename=filename;
                var html='<div class="col-md-3"><div class="thumbnail-inner"><span class="remove-story-thumbnail" data-img="'+story_thumbnail+'"><i class="fa fa-remove"></i></span><img id="story_thumbnail_image" src="'+story_thumbnail+'" width="150px" height="150px;"></div></div>';
                jQuery("#story_thumbnail").html(html);


            };
        })(f);
        reader.readAsDataURL(f);
    }

    async function storeMenuImageData() {
        var newPhoto=[];
        try {
            if(restaurant_menu_photos.length>0) {
                await Promise.all(restaurant_menu_photos.map(async (menuPhoto,index) => {
                    // Use Laravel storage API instead of Firebase
                    const base64Data = menuPhoto; // Keep full data URL for API
                    const filename = restaurant_menu_filename[index] || ('menu_' + Date.now() + '_' + index + '.jpg');

                    try {
                        const response = await $.ajax({
                            url: '{{ route("api.upload.image") }}',
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: {
                                image: base64Data,
                                folder: 'marts/menu',
                                filename: filename
                            }
                        });

                        if (response.success && response.url) {
                            newPhoto.push(response.url);
                        } else {
                            console.error('Upload failed for menu image ' + index + ':', response.message || 'Unknown error');
                        }
                    } catch(uploadError) {
                        console.error('Error uploading menu image ' + index + ':', uploadError);
                    }
                }));
            }
            return newPhoto;
        } catch(error) {
            console.error('Error storing menu images:', error);
            throw error;
        }
    }

    function handleFileSelectMenuCard(evt) {
        var f=evt.target.files[0];
        var reader=new FileReader();

        new Compressor(f,{
            quality: <?php echo env('IMAGE_COMPRESSOR_QUALITY', 0.8); ?>,
            success(result) {
                f=result;

                reader.onload=(function(theFile) {
                    return function(e) {

                        var filePayload=e.target.result;
                        var val=f.name;
                        var ext=val.split('.')[1];
                        var docName=val.split('fakepath')[1];
                        var filename=(f.name).replace(/C:\\fakepath\\/i,'')

                        var timestamp=Number(new Date());
                        var filename=filename.split('.')[0]+"_"+timestamp+'.'+ext;
                        photo=filePayload;

                        if(photo) {

                            menuPhotoCount++;
                            photos_html='<span class="image-item" id="photo_menu'+menuPhotoCount+'"><span class="remove-menu-btn" data-id="'+menuPhotoCount+'" data-img="'+photo+'"><i class="fa fa-remove"></i></span><img width="100px" id="" height="auto" src="'+photo+'"></span>';
                            $("#photos_menu_card").append(photos_html);
                            restaurant_menu_photos.push(photo);
                            restaurant_menu_filename.push(filename);
                        }
                    };
                })(f);
                reader.readAsDataURL(f);
            },
            error(err) {
                console.log(err.message);
            },
        });
    }

    $("#dine_in_feature").change(function() {
        if(this.checked) {
            $(".divein_div").show();
        } else {
            $(".divein_div").hide();
        }
    });


    $(".add_special_offer_restaurant_btn").click(function() {
        if(specialDiscountOfferisEnable) {
            $(".special_offer_div").show();
        } else {
            alert("{{trans('lang.special_offer_disabled')}}");
            return false;
        }
    })

    var countAddButton=1;

    function addMoreButton(day,day2,count) {
        count=countAddButton;
        $(".restaurant_discount_options_"+day+"_div").show();

        $('#special_offer_table_'+day+' tr:last').after('<tr>'+
            '<td class="" style="width:10%;"><input type="time" class="form-control" id="openTime'+day+count+'"></td>'+
            '<td class="" style="width:10%;"><input type="time" class="form-control" id="closeTime'+day+count+'"></td>'+
            '<td class="" style="width:30%;">'+
            '<input type="number" class="form-control" id="discount'+day+count+'" style="width:60%;">'+
            '<select id="discount_type'+day+count+'" class="form-control" style="width:40%;"><option value="percentage"/>%</option><option value="amount"/>'+currentCurrency+'</option></select>'+
            '</td>'+
            '<td style="width:30%;"><select id="type'+day+count+'" class="form-control"><option value="delivery"/>Delivery Discount</option><option value="dinein"/>Dine-In Discount</option></select></td>'+
            '<td class="action-btn" style="width:20%;">'+
            '<button type="button" class="btn btn-primary save_option_day_button'+day+count+'" onclick="addMoreFunctionButton(`'+day2+'`,`'+day+'`,'+countAddButton+')" style="width:62%;"><i class="fa fa-save"></i> Save</button>'+
            '</td></tr>');
        countAddButton++;

    }

    function addMoreFunctionButton(day1,day2,count) {
        var discount=$("#discount"+day2+count).val();
        var discount_type=$('#discount_type'+day2+count).val();
        var type=$('#type'+day2+count).val();
        var closeTime=$("#closeTime"+day2+count).val();
        var openTime=$("#openTime"+day2+count).val();

        $(".error_top").hide();
        $(".error_top").html("");
        if(openTime=='') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>Please enter special offer start time</p>");
            window.scrollTo(0,0);
        }
        else if(closeTime=='') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>Please enter special offer close time</p>");
            window.scrollTo(0,0);
        }

        else if(openTime>closeTime) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>Close time can not be less than Open time</p>");
            window.scrollTo(0,0);
        }
        else if(discount=="") {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>Please Enter discount</p>");
            window.scrollTo(0,0);
        } else if(discount>100||discount==0) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>Please Enter valid discount</p>");
            window.scrollTo(0,0);
        } else {

            if(typeof timeslotSunday==='undefined') timeslotSunday=[];
            if(typeof timeslotMonday==='undefined') timeslotMonday=[];
            if(typeof timeslotTuesday==='undefined') timeslotTuesday=[];
            if(typeof timeslotWednesday==='undefined') timeslotWednesday=[];
            if(typeof timeslotThursday==='undefined') timeslotThursday=[];
            if(typeof timeslotFriday==='undefined') timeslotFriday=[];
            if(typeof timeslotSaturday==='undefined') timeslotSaturday=[];

            var isDuplicate=false;
            var existingTimeslots=[];


            if(day1=='sunday') {
                existingTimeslots=timeslotSunday;
            } else if(day1=='monday') {
                existingTimeslots=timeslotMonday;
            } else if(day1=='tuesday') {
                existingTimeslots=timeslotTuesday;
            } else if(day1=='wednesday') {
                existingTimeslots=timeslotWednesday;
            } else if(day1=='thursday') {
                existingTimeslots=timeslotThursday;
            } else if(day1=='friday') {
                existingTimeslots=timeslotFriday;
            } else if(day1=='Saturday') {
                existingTimeslots=timeslotSaturday;
            }

            function timeToDate(time) {
                var [hours,minutes]=time.split(':');
                return new Date(0,0,0,hours,minutes); // Using "0" date and month for comparison
            }

            var newOpenTime=timeToDate(openTime);
            var newCloseTime=timeToDate(closeTime);

            existingTimeslots.forEach(function(slot) {
                var existingStart=timeToDate(slot.from);
                var existingEnd=timeToDate(slot.to);

                // Check if the new slot is inside the existing slot
                if((newOpenTime<existingEnd&&newCloseTime>existingStart)) {
                    if(slot.discount_type!==type) {
                        isDuplicate=false;  // Allow the new slot with a different type
                    } else {
                        isDuplicate=true;   // Same time range and type -> duplicate
                    }
                }
            });


            if(isDuplicate) {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>This time slot for "+type+" already exists. Please select a different time slot.</p>");
                window.scrollTo(0,0);
            }
            else {
                var timeslotVar={
                    'discount': discount,
                    'from': openTime,
                    'to': closeTime,
                    'type': discount_type,
                    'discount_type': type
                };


                if(day1=='sunday') {
                    timeslotSunday.push(timeslotVar);
                } else if(day1=='monday') {
                    timeslotMonday.push(timeslotVar);
                } else if(day1=='tuesday') {
                    timeslotTuesday.push(timeslotVar);
                } else if(day1=='wednesday') {
                    timeslotWednesday.push(timeslotVar);
                } else if(day1=='thursday') {
                    timeslotThursday.push(timeslotVar);
                } else if(day1=='friday') {
                    timeslotFriday.push(timeslotVar);
                } else if(day1=='Saturday') {
                    timeslotSaturday.push(timeslotVar);
                }


                $(".save_option_day_button"+day2+count).hide();
                $("#discount"+day2+count).attr('disabled',"true");
                $("#discount_type"+day2+count).attr('disabled',"true");
                $("#type"+day2+count).attr('disabled',"true");
                $("#closeTime"+day2+count).attr('disabled',"true");
                $("#openTime"+day2+count).attr('disabled',"true");
            }
        }

    }


    $(".add_working_hours_restaurant_btn").click(function() {
        $(".working_hours_div").show();
    })
    var countAddhours=1;

    function addMorehour(day,day2,count) {
        count=countAddhours;
        $(".restaurant_discount_options_"+day+"_div").show();
        $('#working_hour_table_'+day+' tr:last').after('<tr>'+
            '<td class="" style="width:50%;"><input type="time" class="form-control" id="from'+day+count+'"></td>'+
            '<td class="" style="width:50%;"><input type="time" class="form-control" id="to'+day+count+'"></td>'+
            '<td><button type="button" class="btn btn-primary save_option_day_button'+day+count+'" onclick="addMoreFunctionhour(`'+day2+'`,`'+day+'`,'+countAddhours+')" style="width:62%;"><i class="fa fa-save" title="Save""></i></button>'+
            '</td></tr>');
        countAddhours++;

    }

    function chkNumbers(event,msg) {
        var charCode=event.which? event.which:event.keyCode;
        if(charCode<48||charCode>57) {
            document.getElementById(msg).innerHTML="Accept only numbers";
            return false;
        } else {
            document.getElementById(msg).innerHTML="";
            return true;
        }
    }

    function addMoreFunctionhour(day1,day2,count) {
        var to=$("#to"+day2+count).val();
        var from=$("#from"+day2+count).val();
        if(from=='') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>Please enter restaurant open time</p>");
            window.scrollTo(0,0);
        }
        else if(to=='') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>Please enter restaurant close time</p>");
            window.scrollTo(0,0);

        }
        else if(from>to) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>To time can not be less than From time</p>");
            window.scrollTo(0,0);
        } else {

            var timeslotworkVar={
                'from': from,
                'to': to,
            };


            if(day1=='sunday') {
                timeslotworkSunday.push(timeslotworkVar);
            } else if(day1=='monday') {
                timeslotworkMonday.push(timeslotworkVar);
            } else if(day1=='tuesday') {
                timeslotworkTuesday.push(timeslotworkVar);
            } else if(day1=='wednesday') {
                timeslotworkWednesday.push(timeslotworkVar);
            } else if(day1=='thursday') {
                timeslotworkThursday.push(timeslotworkVar);
            } else if(day1=='friday') {
                timeslotworkFriday.push(timeslotworkVar);
            } else if(day1=='Saturday') {
                timeslotworkSaturday.push(timeslotworkVar);
            }

            $(".save_option_day_button"+day2+count).hide();
            $("#to"+day2+count).attr('disabled',"true");
            $("#from"+day2+count).attr('disabled',"true");
        }

    }

    async function getOwnerDetails(selectedOwnerId) {
        try {
            console.log('Fetching owner details for ID:', selectedOwnerId);

            // Fetch from SQL instead of Firebase
            const response = await $.ajax({
                url: '/api/users/' + selectedOwnerId,
                method: 'GET',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });

            if (response && response.success && response.user) {
                console.log('Owner details fetched:', response.user);
                // Return user object in format expected by the code
                return {
                    id: response.user.id,
                    firstName: response.user.firstName || response.user.first_name,
                    lastName: response.user.lastName || response.user.last_name,
                    email: response.user.email,
                    profilePictureURL: response.user.profilePictureURL || response.user.photo,
                    subscriptionPlanId: response.user.subscriptionPlanId,
                    subscription_plan: response.user.subscription_plan ? JSON.parse(response.user.subscription_plan) : null,
                    subscriptionExpiryDate: response.user.subscriptionExpiryDate
                };
            } else {
                console.error('Failed to fetch owner details');
                return null;
            }
        } catch(error) {
            console.error('Error getting owner details:', error);
            return null;
        }
    }

    $('#subscription_plan').on('change',function() {
        var id=$(this).val();
        // Subscription plans - can be loaded from SQL if needed
        // For now, always show dine-in div
        $('#dine_in_div').removeClass('d-none');
    })

</script>
@endsection
