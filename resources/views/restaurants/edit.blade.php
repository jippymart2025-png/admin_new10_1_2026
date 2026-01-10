@extends('layouts.app')
@section('content')
    <?php
    $countries = file_get_contents(public_path('countriesdata.json'));
    $countries = json_decode($countries);
    $countries = (array)$countries;
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
                <h3 class="text-themecolor">{{trans('lang.restaurant_plural')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('restaurants') !!}">{{trans('lang.restaurant_plural')}}</a></li>
                    <li class="breadcrumb-item active">{{trans('lang.restaurant_edit')}}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="resttab-sec">
                        <div class="menu-tab">
                            <ul>
                                <li>
                                    <a  class="profileRoute">{{trans('lang.profile')}}</a>
                                </li>
                                <li class="active">
                                    <a href="{{route('restaurants.edit',$id)}}">{{trans('lang.restaurant')}}</a>
                                </li>
                            </ul>
                        </div>
                        <div class="error_top"></div>
                        <div class="row restaurant_payout_create">
                            <div class="restaurant_payout_create-inner">
                                <fieldset>
                                    <legend>{{trans('lang.restaurant_details')}}</legend>
                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label">{{trans('lang.restaurant_name')}}</label>
                                        <div class="col-7">
                                            <input type="text" class="form-control restaurant_name">
                                            <div class="form-text text-muted">
                                                {{ trans("lang.restaurant_name_help") }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label">Cuisine</label>
                                        <div class="col-7">
                                            <select id='restaurant_vendor_cuisines' class="form-control" required>
                                                <option value="">Select Cuisine</option>
                                            </select>
                                            <div class="form-text text-muted">
                                                Please select the vendor cuisine.
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
                                                {{ trans("lang.restaurant_cuisines_help") }} (Hold Ctrl/Cmd to select multiple)
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label">{{trans('lang.restaurant_phone')}}</label>
                                        <div class="col-md-12">
                                            <div class="phone-box position-relative" >
                                                <select name="country" id="country_selector1">
                                                    <?php foreach ($newcountries as $keycy => $valuecy) { ?>
                                                        <?php $selected = ""; ?>
                                                    <option <?php echo $selected; ?> code="<?php echo $valuecy->code; ?>"
                                                            value="<?php echo $keycy; ?>">
                                                        +<?php echo $valuecy->phoneCode; ?> {{$valuecy->countryName}}</option>
                                                    <?php } ?>
                                                </select>
                                                <input type="text" class="form-control restaurant_phone"  onkeypress="return chkAlphabets2(event,'error2')">                                            <div id="error2" class="err"></div>
                                            </div>
                                        </div>
                                        <div class="form-text text-muted">
                                            {{ trans("lang.restaurant_phone_help") }}
                                        </div>
                                    </div>
                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label">Vendor Type <span class="required-field"></span></label>
                                        <div class="col-7">
                                            <select id="vendor_type" class="form-control" required>
                                                <option value="">Select Vendor Type</option>
                                                <option value="restaurant">Restaurant</option>
                                                <option value="mart">Mart</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label">{{trans('lang.restaurant_address')}}</label>
                                        <div class="col-7">
                                            <input type="text" class="form-control restaurant_address">
                                            <div class="form-text text-muted">
                                                {{ trans("lang.restaurant_address_help") }}
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
                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label">{{trans('lang.restaurant_latitude')}}</label>
                                        <div class="col-7">
                                            <input type="text" class="form-control restaurant_latitude">
                                            <div class="form-text text-muted">
                                                {{ trans("lang.restaurant_latitude_help") }}
                                            </div>
                                        </div>
                                        <div class="form-text text-muted ml-3">
                                            Don't Know your cordinates ? use <a target="_blank" href="https://www.latlong.net/">Latitude and Longitude Finder</a>
                                        </div>
                                    </div>
                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label">{{trans('lang.restaurant_longitude')}}</label>
                                        <div class="col-7">
                                            <input type="text" class="form-control restaurant_longitude">
                                            <div class="form-text text-muted">
                                                {{ trans("lang.restaurant_longitude_help") }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row width-100">
                                        <label class="col-3 control-label ">{{trans('lang.restaurant_description')}}</label>
                                        <div class="col-7">
                                            <textarea rows="7" class="restaurant_description form-control" id="restaurant_description"></textarea>
                                        </div>
                                    </div>
                                    <!-- <div class="form-group row width-100">
                                <label class="col-3 control-label">{{trans('lang.restaurant_image')}}</label>
                                <div class="col-7">
                                    <input type="file" onChange="handleFileSelect(event,'photo')">
                                    <div id="uploding_image"></div>
                                    <div class="uploaded_image" style="display:none;"><img id="uploaded_image" src="" width="150px" height="150px;">
                                    </div>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.restaurant_image_help") }}
                                    </div>
                                </div>
                            </div> -->
                                </fieldset>
                                <fieldset>
                                    <legend>{{ trans('lang.restaurant_admin_commission_details') }}</legend>
                                    <div class="form-group row width-50">
                                        <label class="col-4 control-label">{{ trans('lang.commission_type') }}</label>
                                        <div class="col-7">
                                            <select class="form-control commission_type" id="commission_type">
                                                <option value="Percent">{{ trans('lang.coupon_percent') }}</option>
                                                <option value="Fixed">{{ trans('lang.coupon_fixed') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row width-50">
                                        <label class="col-4 control-label">{{ trans('lang.admin_commission') }}</label>
                                        <div class="col-7">
                                            <input type="number" value="0" class="form-control commission_fix">
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
                                <fieldset style="display: none;">
                                    <legend>{{trans('lang.services')}}</legend>
                                    <div class="form-group row">
                                        <div class="form-check width-100">
                                            <input type="checkbox" id="Free_Wi_Fi">
                                            <label class="col-3 control-label" for="Free_Wi_Fi">{{trans('lang.free_wi_fi')}}</label>
                                        </div>
                                        <div class="form-check width-100">
                                            <input type="checkbox" id="Good_for_Breakfast">
                                            <label class="col-3 control-label" for="Good_for_Breakfast">{{trans('lang.good_for_breakfast')}}</label>
                                        </div>
                                        <div class="form-check width-100">
                                            <input type="checkbox" id="Good_for_Dinner">
                                            <label class="col-3 control-label" for="Good_for_Dinner">{{trans('lang.good_for_dinner')}}</label>
                                        </div>
                                        <div class="form-check width-100">
                                            <input type="checkbox" id="Good_for_Lunch">
                                            <label class="col-3 control-label" for="Good_for_Lunch">{{trans('lang.good_for_lunch')}}</label>
                                        </div>
                                        <div class="form-check width-100">
                                            <input type="checkbox" id="Live_Music">
                                            <label class="col-3 control-label" for="Live_Music">{{trans('lang.live_music')}}</label>
                                        </div>
                                        <div class="form-check width-100">
                                            <input type="checkbox" id="Outdoor_Seating">
                                            <label class="col-3 control-label" for="Outdoor_Seating">{{trans('lang.outdoor_seating')}}</label>
                                        </div>
                                        <div class="form-check width-100">
                                            <input type="checkbox" id="Takes_Reservations">
                                            <label class="col-3 control-label" for="Takes_Reservations">{{trans('lang.takes_reservations')}}</label>
                                        </div>
                                        <div class="form-check width-100">
                                            <input type="checkbox" id="Vegetarian_Friendly">
                                            <label class="col-3 control-label" for="Vegetarian_Friendly">{{trans('lang.vegetarian_friendly')}}</label>
                                        </div>
                                    </div>
                                </fieldset>
                                <fieldset>
                                    <legend>{{trans('lang.working_hours')}}</legend>
                                    <div class="form-group row">
                                        <label class="col-12 control-label" style="color:red;font-size:15px;">{{trans('lang.working_hour_note')}}</label>
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
                                                    <button type="button" class="btn btn-primary add_more_sunday" onclick="addMorehour('Sunday','sunday', '1')">
                                                        {{trans('lang.add_more')}}
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="restaurant_working_hour_Sunday_div restaurant_discount mb-5" style="display:none">
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
                                                    <button type="button" class="btn btn-primary add_more_sunday" onclick="addMorehour('Monday','monday', '1')">
                                                        {{trans('lang.add_more')}}
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="restaurant_working_hour_Monday_div restaurant_discount mb-5" style="display:none">
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
                                                    <button type="button" class="btn btn-primary" onclick="addMorehour('Tuesday','tuesday', '1')">
                                                        {{trans('lang.add_more')}}
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="restaurant_working_hour_Tuesday_div restaurant_discount mb-5" style="display:none">
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
                                                    <button type="button" class="btn btn-primary" onclick="addMorehour('Wednesday','wednesday', '1')">
                                                        {{trans('lang.add_more')}}
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="restaurant_working_hour_Wednesday_div restaurant_discount mb-5" style="display:none">
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
                                                    <button type="button" class="btn btn-primary" onclick="addMorehour('Thursday','thursday', '1')">
                                                        {{trans('lang.add_more')}}
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="restaurant_working_hour_Thursday_div restaurant_discount mb-5" style="display:none">
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
                                                    <button type="button" class="btn btn-primary" onclick="addMorehour('Friday','friday', '1')">
                                                        {{trans('lang.add_more')}}
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="restaurant_working_hour_Friday_div restaurant_discount mb-5" style="display:none">
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
                                                    <button type="button" class="btn btn-primary" onclick="addMorehour('Saturday','Saturday','1')">
                                                        {{trans('lang.add_more')}}
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="restaurant_working_hour_Saturday_div restaurant_discount mb-5" style="display:none">
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
                                                <input type="checkbox" id="is_open">
                                                <label class="col-3 control-label" for="is_open">{{ trans('lang.open_closed') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                                <fieldset>
                                    <legend>{{trans('lang.dine_in_future_setting')}}</legend>
                                    <div class="form-group row">
                                        <div class="form-group row width-100">
                                            <div class="form-check width-100">
                                                <input type="checkbox" id="dine_in_feature" class="">
                                                <label class="col-3 control-label" for="dine_in_feature">{{trans('lang.enable_dine_in_feature')}}</label>
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
                                                    <input type="number" class="form-control restaurant_cost" required>
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
                                <fieldset>
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
                                            <label class="col-4 control-label">{{
                                            trans('lang.minimum_delivery_charges')}}</label>
                                            <div class="col-7">
                                                <input type="number" class="form-control" id="minimum_delivery_charges">
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-4 control-label">{{ trans('lang.minimum_delivery_charges_within')}} <span
                                                    class="global_distance_type"></span></label>
                                            <div class="col-7">
                                                <input type="number" class="form-control" id="minimum_delivery_charges_within_km">
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                                <fieldset>
                                    <legend>{{trans('lang.special_offer')}}</legend>
                                    <div class="form-check width-100">
                                        <input type="checkbox" id="specialDiscountEnable">
                                        <label class="col-3 control-label"
                                               for="specialDiscountEnable">{{trans('lang.special_discount_enable')}}</label>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-12 control-label" style="color:red;font-size:15px;">NOTE :
                                            Please Click on Edit Button After Making Changes in Special Discount,
                                            Otherwise Data may not Save!! </label>
                                    </div>
                                    <div class="form-group row">
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
                                                    <button type="button" class="btn btn-primary add_more_sunday" onclick="addMoreButton('Sunday','sunday', '1')">
                                                        {{trans('lang.add_more')}}
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="restaurant_discount_options_Sunday_div restaurant_discount" style="display:none">
                                                <table class="booking-table" id="special_offer_table_Sunday">
                                                    <tr>
                                                        <th>
                                                            <label class="col-3 control-label">{{trans('lang.Opening_Time')}}</label>
                                                        </th>
                                                        <th>
                                                            <label class="col-3 control-label">{{trans('lang.Closing_Time')}}</label>
                                                        </th>
                                                        <th>
                                                            <label class="col-3 control-label">{{trans('lang.coupon_discount')}}</label>
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
                                                    <button type="button" class="btn btn-primary add_more_sunday" onclick="addMoreButton('Monday','monday', '1')">
                                                        {{trans('lang.add_more')}}
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="restaurant_discount_options_Monday_div restaurant_discount" style="display:none">
                                                <table class="booking-table" id="special_offer_table_Monday">
                                                    <tr>
                                                        <th>
                                                            <label class="col-3 control-label">{{trans('lang.Opening_Time')}}</label>
                                                        </th>
                                                        <th>
                                                            <label class="col-3 control-label">{{trans('lang.Closing_Time')}}</label>
                                                        </th>
                                                        <th>
                                                            <label class="col-3 control-label">{{trans('lang.coupon_discount')}}</label>
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
                                                    <button type="button" class="btn btn-primary" onclick="addMoreButton('Tuesday','tuesday', '1')">
                                                        {{trans('lang.add_more')}}
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="restaurant_discount_options_Tuesday_div restaurant_discount" style="display:none">
                                                <table class="booking-table" id="special_offer_table_Tuesday">
                                                    <tr>
                                                        <th>
                                                            <label class="col-3 control-label">{{trans('lang.Opening_Time')}}</label>
                                                        </th>
                                                        <th>
                                                            <label class="col-3 control-label">{{trans('lang.Closing_Time')}}</label>
                                                        </th>
                                                        <th>
                                                            <label class="col-3 control-label">{{trans('lang.coupon_discount')}}</label>
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
                                                    <button type="button" class="btn btn-primary" onclick="addMoreButton('Wednesday','wednesday', '1')">
                                                        {{trans('lang.add_more')}}
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="restaurant_discount_options_Wednesday_div restaurant_discount" style="display:none">
                                                <table class="booking-table" id="special_offer_table_Wednesday">
                                                    <tr>
                                                        <th>
                                                            <label class="col-3 control-label">{{trans('lang.Opening_Time')}}</label>
                                                        </th>
                                                        <th>
                                                            <label class="col-3 control-label">{{trans('lang.Closing_Time')}}</label>
                                                        </th>
                                                        <th>
                                                            <label class="col-3 control-label">{{trans('lang.coupon_discount')}}</label>
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
                                                    <button type="button" class="btn btn-primary" onclick="addMoreButton('Thursday','thursday', '1')">
                                                        {{trans('lang.add_more')}}
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="restaurant_discount_options_Thursday_div restaurant_discount" style="display:none">
                                                <table class="booking-table" id="special_offer_table_Thursday">
                                                    <tr>
                                                        <th>
                                                            <label class="col-3 control-label">{{trans('lang.Opening_Time')}}</label>
                                                        </th>
                                                        <th>
                                                            <label class="col-3 control-label">{{trans('lang.Closing_Time')}}</label>
                                                        </th>
                                                        <th>
                                                            <label class="col-3 control-label">{{trans('lang.coupon_discount')}}</label>
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
                                                    <button type="button" class="btn btn-primary" onclick="addMoreButton('Friday','friday', '1')">
                                                        {{trans('lang.add_more')}}
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="restaurant_discount_options_Friday_div restaurant_discount" style="display:none">
                                                <table class="booking-table" id="special_offer_table_Friday">
                                                    <tr>
                                                        <th>
                                                            <label class="col-3 control-label">{{trans('lang.Opening_Time')}}</label>
                                                        </th>
                                                        <th>
                                                            <label class="col-3 control-label">{{trans('lang.Closing_Time')}}</label>
                                                        </th>
                                                        <th>
                                                            <label class="col-3 control-label">{{trans('lang.coupon_discount')}}</label>
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
                                                    <button type="button" class="btn btn-primary" onclick="addMoreButton('Saturday','Saturday','1')">
                                                        {{trans('lang.add_more')}}
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="restaurant_discount_options_Saturday_div restaurant_discount" style="display:none">
                                                <table class="booking-table" id="special_offer_table_Saturday">
                                                    <tr>
                                                        <th>
                                                            <label class="col-3 control-label">{{trans('lang.Opening_Time')}}</label>
                                                        </th>
                                                        <th>
                                                            <label class="col-3 control-label">{{trans('lang.Closing_Time')}}</label>
                                                        </th>
                                                        <th>
                                                            <label class="col-3 control-label">{{trans('lang.coupon_discount')}}</label>
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
                </div>
                <div class="form-group col-12 text-center btm-btn">
                    <button type="button" class="btn btn-primary  edit-form-btn"><i class="fa fa-save"></i> {{trans('lang.save')}}
                    </button>
                    <a href="{!! route('restaurants') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.26.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/compressorjs/1.1.1/compressor.min.js" integrity="sha512-VaRptAfSxXFAv+vx33XixtIVT9A/9unb1Q8fp63y1ljF+Sbka+eMJWoDAArdm7jOYuLQHVx5v60TQ+t3EA8weA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        const restaurantId = "{{ $id }}";
        const placeholderImage = '{{ asset('images/placeholder.png') }}';
        const routes = {
            getRestaurant: "{{ route('restaurants.getById', ['id' => $id]) }}",
            categories: "{{ route('restaurants.categories') }}",
            cuisines: "{{ route('restaurants.cuisines') }}",
            zones: "{{ route('vendors.zones') }}",
            deliverySettings: "{{ route('api.deliveryCharge.settings') }}",
            currency: "{{ route('api.currencies.active') }}",
            specialOfferSettings: "{{ route('api.specialoffer.settings') }}",
            uploadImage: "{{ route('api.upload.image') }}",
            update: "{{ route('restaurants.update', ['id' => $id]) }}",
        };

        let restaurnt_photos = [];
        let new_added_restaurant_photos = [];
        let new_added_restaurant_photos_filename = [];
        let restaurant_menu_photos = [];
        let new_added_restaurant_menu = [];
        let new_added_restaurant_menu_filename = [];
        let photocount = 0;
        let menuPhotoCount = 0;
        let specialDiscountOfferisEnable = false;
        let vendorCanModifyDeliveryCharge = false;
        let currentCurrency = '';
        let currencyAtRight = false;
        let deliverySettingsDefaults = {};
        let availableCategories = [];
        let availableCuisines = [];
        let availableZones = [];

        // Story variables
        let story_vedios = [];
        let story_thumbnail = '';
        let story_thumbnail_filename = '';
        let storevideoDuration = 0;

        let timeslotSunday = [];
        let timeslotMonday = [];
        let timeslotTuesday = [];
        let timeslotWednesday = [];
        let timeslotThursday = [];
        let timeslotFriday = [];
        let timeslotSaturday = [];

        let timeslotworkSunday = [];
        let timeslotworkMonday = [];
        let timeslotworkTuesday = [];
        let timeslotworkWednesday = [];
        let timeslotworkThursday = [];
        let timeslotworkFriday = [];
        let timeslotworkSaturday = [];

        const dayOrder = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const newcountriesjs = @json($newcountriesjs);

        $(document).ready(function () {
            window.csrfToken = $('meta[name="csrf-token"]').attr('content');
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': window.csrfToken } });

            initCountrySelect();
            initCategorySearch();
            bindGalleryEvents();
            bindMenuEvents();
            bindMiscEvents();
            loadInitialData();
        });

        function initCountrySelect() {
            $('#country_selector1').select2({
                templateResult: formatState,
                templateSelection: formatStateSelection,
                placeholder: "Select Country",
                allowClear: true
            });
        }

        function initCategorySearch() {
            $('#category_search').on('keyup', function () {
                const search = $(this).val().toLowerCase();
                $('#restaurant_cuisines option').each(function () {
                    if ($(this).val() === "") {
                        $(this).show();
                        return;
                    }
                    const text = $(this).text().toLowerCase();
                    $(this).toggle(text.indexOf(search) > -1);
                });
            });

            $('#restaurant_cuisines').on('change', updateSelectedCategoryTags);
            $('#selected_categories').on('click', '.remove-tag', function () {
                const value = $(this).parent().data('value');
                $('#restaurant_cuisines option[value="' + value + '"]').prop('selected', false);
                updateSelectedCategoryTags();
            });
        }

        function bindGalleryEvents() {
            $('#photos').on('click', '.remove-btn', function () {
                const status = $(this).data('status');
                const photoUrl = $(this).data('img');
                const elementId = $(this).data('id');

                if (status === 'old') {
                    restaurnt_photos = restaurnt_photos.filter(url => url !== photoUrl);
                } else {
                    const index = new_added_restaurant_photos.indexOf(photoUrl);
                    if (index > -1) {
                        new_added_restaurant_photos.splice(index, 1);
                        new_added_restaurant_photos_filename.splice(index, 1);
                    }
                }
                $('#photo_' + elementId).remove();
                if (!$('#photos .image-item').length) {
                    $('#photos').html('<p>Photos not available.</p>');
                }
            });
        }

        function bindMenuEvents() {
            $('#photos_menu_card').on('click', '.remove-menu-btn', function () {
                const status = $(this).data('status');
                const photoUrl = $(this).data('img');
                const elementId = $(this).data('id');

                if (status === 'old') {
                    restaurant_menu_photos = restaurant_menu_photos.filter(url => url !== photoUrl);
                } else {
                    const index = new_added_restaurant_menu.indexOf(photoUrl);
                    if (index > -1) {
                        new_added_restaurant_menu.splice(index, 1);
                        new_added_restaurant_menu_filename.splice(index, 1);
                    }
                }
                $('#photo_menu_' + elementId).remove();
                if (!$('#photos_menu_card .image-item').length) {
                    $('#photos_menu_card').html('<p>Menu card photos not available.</p>');
                }
            });
        }

        function bindMiscEvents() {
            $('#dine_in_feature').on('change', function () {
                $('.divein_div').toggle(this.checked);
            });

            $('.add_special_offer_restaurant_btn').on('click', function () {
                if (!specialDiscountOfferisEnable) {
                    alert("{{ trans('lang.special_offer_disabled') }}");
                    return;
                }
                $('.special_offer_div').show();
                if (!timeslotSunday.length && !timeslotMonday.length && !timeslotTuesday.length &&
                    !timeslotWednesday.length && !timeslotThursday.length && !timeslotFriday.length && !timeslotSaturday.length) {
                    addSpecialDiscountSlot('Sunday');
                }
            });

            $('.add_working_hours_restaurant_btn').on('click', function () {
                $('.working_hours_div').show();
                if (!timeslotworkSunday.length && !timeslotworkMonday.length && !timeslotworkTuesday.length &&
                    !timeslotworkWednesday.length && !timeslotworkThursday.length && !timeslotworkFriday.length && !timeslotworkSaturday.length) {
                    addWorkingHourSlot('Monday');
                }
            });

            $('.edit-form-btn').on('click', async function (event) {
                event.preventDefault();
                await saveRestaurant();
            });
        }

        async function loadInitialData() {
            jQuery('#data-table_processing').show();
            clearError();
            try {
                const restaurantRes = await fetchJson(routes.getRestaurant);
                if (!restaurantRes || !restaurantRes.success || !restaurantRes.data) {
                    throw new Error(restaurantRes && restaurantRes.message ? restaurantRes.message : 'Unable to load restaurant data.');
                }

                try {
                    const categoriesRes = await fetchJson(routes.categories);
                    availableCategories = categoriesRes && categoriesRes.success ? categoriesRes.data : [];
                    populateCategorySelect(availableCategories);
                } catch (e) {
                    console.warn('Unable to load categories', e);
                    populateCategorySelect([]);
                }

                try {
                    const cuisinesRes = await fetchJson(routes.cuisines);
                    availableCuisines = cuisinesRes && cuisinesRes.success ? cuisinesRes.data : [];
                    populateCuisineSelect(availableCuisines);
                } catch (e) {
                    console.warn('Unable to load cuisines', e);
                    populateCuisineSelect([]);
                }

                try {
                    const zonesRes = await fetchJson(routes.zones);
                    availableZones = zonesRes && zonesRes.success ? zonesRes.data : [];
                    populateZonesSelect(availableZones);
                } catch (e) {
                    console.warn('Unable to load zones', e);
                    populateZonesSelect([]);
                }

                try {
                    const deliveryRes = await fetchJson(routes.deliverySettings);
                    if (deliveryRes) {
                        vendorCanModifyDeliveryCharge = !!deliveryRes.vendor_can_modify;
                        deliverySettingsDefaults = deliveryRes;
                    }
                } catch (e) {
                    console.warn('Delivery charge settings not available', e);
                }

                try {
                    const currencyRes = await fetchJson(routes.currency);
                    if (currencyRes && currencyRes.success && currencyRes.data) {
                        currentCurrency = currencyRes.data.symbol || '';
                        currencyAtRight = !!currencyRes.data.symbolAtRight;
                    }
                } catch (e) {
                    console.warn('Active currency not available', e);
                }

                try {
                    const specialOfferRes = await fetchJson(routes.specialOfferSettings);
                    if (specialOfferRes && typeof specialOfferRes.isEnable !== 'undefined') {
                        specialDiscountOfferisEnable = !!specialOfferRes.isEnable;
                    }
                } catch (e) {
                    console.warn('Special discount offer setting not available', e);
                }

                populateForm(restaurantRes.data);
            } catch (error) {
                console.error(error);
                showError(error.message || 'Failed to load restaurant details.');
            } finally {
                jQuery('#data-table_processing').hide();
            }
        }

        function populateCategorySelect(categories) {
            const $select = $('#restaurant_cuisines');
            $select.empty().append('<option value="">Select Cuisines</option>');
            categories.forEach(category => {
                $select.append($('<option></option>').attr('value', category.id).text(category.title));
            });
        }

        function populateCuisineSelect(cuisines) {
            const $select = $('#restaurant_vendor_cuisines');
            $select.empty().append('<option value="">Select Cuisine</option>');
            cuisines.forEach(cuisine => {
                $select.append($('<option></option>').attr('value', cuisine.id).text(cuisine.title));
            });
        }

        function populateZonesSelect(zones) {
            const $select = $('#zone');
            $select.empty().append('<option value="">{{ trans("lang.select_zone") }}</option>');
            zones.forEach(zone => {
                const option = $('<option></option>')
                    .attr('value', zone.id)
                    .text(zone.name || '')
                    .data('area', zone.area || []);
                $select.append(option);
            });
        }

        // Store vType globally to preserve it during save
        let currentVType = 'restaurant';

        function populateForm(restaurant) {
            $('.restaurant_name').val(restaurant.title || '');

            // Store vType from loaded data
            currentVType = restaurant.vType || 'restaurant';

            if (restaurant.vendorCuisineID) {
                $('#restaurant_vendor_cuisines').val(restaurant.vendorCuisineID).trigger('change');
            }

            if (Array.isArray(restaurant.categoryID)) {
                $('#restaurant_cuisines').val(restaurant.categoryID).trigger('change');
            } else if (restaurant.categoryID) {
                $('#restaurant_cuisines').val([restaurant.categoryID]).trigger('change');
            }
            updateSelectedCategoryTags();

            if (restaurant.countryCode) {
                $('#country_selector1').val(restaurant.countryCode.replace('+', '')).trigger('change');
            }
            $('#vendor_type').val(restaurant.vType ?? 'restaurant').trigger('change');
            $('.restaurant_phone').val(shortEditNumber(restaurant.phonenumber || ''));
            $('.restaurant_address').val(restaurant.location || '');
            $('#zone').val(restaurant.zoneId || '').trigger('change');
            $('.restaurant_latitude').val(restaurant.latitude || '');
            $('.restaurant_longitude').val(restaurant.longitude || '');
            $('.restaurant_description').val(restaurant.description || '');

            if (restaurant.adminCommission) {
                $('#commission_type').val(restaurant.adminCommission.commissionType || 'Percent');
                $('.commission_fix').val(restaurant.adminCommission.fix_commission ?? 0);
            }



            $('#is_open').prop('checked', !!restaurant.isOpen);

            if (restaurant.enabledDiveInFuture) {
                $('#dine_in_feature').prop('checked', true);
                $('.divein_div').show();
            } else {
                $('.divein_div').hide();
            }

            $('#openDineTime').val(convertTo24Hour(restaurant.openDineTime) || '');
            $('#closeDineTime').val(convertTo24Hour(restaurant.closeDineTime) || '');
            $('.restaurant_cost').val(restaurant.restaurantCost || '');

            if (restaurant.DeliveryCharge) {
                $('#delivery_charges_per_km').val(restaurant.DeliveryCharge.delivery_charges_per_km || '');
                $('#minimum_delivery_charges').val(restaurant.DeliveryCharge.minimum_delivery_charges || '');
                $('#minimum_delivery_charges_within_km').val(restaurant.DeliveryCharge.minimum_delivery_charges_within_km || '');
            } else {
                $('#delivery_charges_per_km').val(deliverySettingsDefaults.delivery_charges_per_km || '');
                $('#minimum_delivery_charges').val(deliverySettingsDefaults.minimum_delivery_charges || '');
                $('#minimum_delivery_charges_within_km').val(deliverySettingsDefaults.minimum_delivery_charges_within_km || '');
            }

            if (!vendorCanModifyDeliveryCharge) {
                $('#delivery_charges_per_km').prop('disabled', true);
                $('#minimum_delivery_charges').prop('disabled', true);
                $('#minimum_delivery_charges_within_km').prop('disabled', true);
            }

            restaurnt_photos = Array.isArray(restaurant.photos) ? restaurant.photos.slice() : [];
            renderPhotoGallery();

            restaurant_menu_photos = Array.isArray(restaurant.restaurantMenuPhotos) ? restaurant.restaurantMenuPhotos.slice() : [];
            renderMenuGallery();

            // Load story data
            if (restaurant.story) {
                if (restaurant.story.videoUrl && Array.isArray(restaurant.story.videoUrl)) {
                    story_vedios = restaurant.story.videoUrl.slice();
                    renderStoryVideos();
                }
                if (restaurant.story.videoThumbnail) {
                    story_thumbnail = restaurant.story.videoThumbnail;
                    renderStoryThumbnail();
                }
            }



            populateFilters(restaurant.filters || {});

            $('#specialDiscountEnable').prop('checked', !!restaurant.specialDiscountEnable);
            populateSpecialDiscount(restaurant.specialDiscount || []);
            populateWorkingHours(restaurant.workingHours || []);


            // ⭐ Set Vendor Profile Route based on vendor_db_id
            // ⭐ Set Vendor Profile Route based on vendor_db_id
            if (restaurant.author) {
                let route1 = '{{ route("vendor.edit", ":id") }}';
                route1 = route1.replace(':id', restaurant.author);
                $('.profileRoute').attr('href', route1);
            } else {
                $('.profileRoute').removeAttr('href');
            }
        }

        function populateFilters(filters) {
            $('#Free_Wi_Fi').prop('checked', filters['Free Wi-Fi'] === 'Yes');
            $('#Good_for_Breakfast').prop('checked', filters['Good for Breakfast'] === 'Yes');
            $('#Good_for_Dinner').prop('checked', filters['Good for Dinner'] === 'Yes');
            $('#Good_for_Lunch').prop('checked', filters['Good for Lunch'] === 'Yes');
            $('#Live_Music').prop('checked', filters['Live Music'] === 'Yes');
            $('#Outdoor_Seating').prop('checked', filters['Outdoor Seating'] === 'Yes');
            $('#Takes_Reservations').prop('checked', filters['Takes Reservations'] === 'Yes');
            $('#Vegetarian_Friendly').prop('checked', filters['Vegetarian Friendly'] === 'Yes');
        }

        function populateSpecialDiscount(discountRows) {
            timeslotSunday = [];
            timeslotMonday = [];
            timeslotTuesday = [];
            timeslotWednesday = [];
            timeslotThursday = [];
            timeslotFriday = [];
            timeslotSaturday = [];

            discountRows.forEach(row => {
                if (!row || !row.day || !Array.isArray(row.timeslot)) {
                    return;
                }
                const target = getSpecialDiscountArray(row.day);
                if (target) {
                    row.timeslot.forEach(slot => {
                        target.push({
                            discount: slot.discount ?? 0,
                            from: slot.from || '',
                            to: slot.to || '',
                            type: slot.type || 'percentage',
                            discount_type: slot.discount_type || 'delivery'
                        });
                    });
                }
            });

            renderSpecialDiscountTables();
        }

        function populateWorkingHours(rows) {
            timeslotworkSunday = [];
            timeslotworkMonday = [];
            timeslotworkTuesday = [];
            timeslotworkWednesday = [];
            timeslotworkThursday = [];
            timeslotworkFriday = [];
            timeslotworkSaturday = [];

            rows.forEach(row => {
                if (!row || !row.day || !Array.isArray(row.timeslot)) {
                    return;
                }
                const target = getWorkingHoursArray(row.day);
                if (target) {
                    row.timeslot.forEach(slot => {
                        target.push({
                            from: slot.from || '',
                            to: slot.to || ''
                        });
                    });
                }
            });

            renderWorkingHoursTables();
        }

        function renderPhotoGallery() {
            photocount = 0;
            if (!restaurnt_photos.length) {
                $('#photos').html('<p>Photos not available.</p>');
                return;
            }
            const fragments = restaurnt_photos.map(url => {
                photocount += 1;
                return `<span class="image-item" id="photo_${photocount}">
                    <span class="remove-btn" data-id="${photocount}" data-img="${url}" data-status="old"><i class="fa fa-remove"></i></span>
                    <img width="100px" height="auto" src="${url}" onerror="this.src='${placeholderImage}'">
                </span>`;
            });
            $('#photos').html(fragments.join(''));
        }

        function renderMenuGallery() {
            menuPhotoCount = 0;
            if (!restaurant_menu_photos.length) {
                $('#photos_menu_card').html('<p>Menu card photos not available.</p>');
                return;
            }
            const fragments = restaurant_menu_photos.map(url => {
                menuPhotoCount += 1;
                return `<span class="image-item" id="photo_menu_${menuPhotoCount}">
                    <span class="remove-menu-btn" data-id="${menuPhotoCount}" data-img="${url}" data-status="old"><i class="fa fa-remove"></i></span>
                    <img width="100px" height="auto" src="${url}" onerror="this.src='${placeholderImage}'">
                </span>`;
            });
            $('#photos_menu_card').html(fragments.join(''));
        }

        function renderSpecialDiscountTables() {
            const config = [
                { day: 'Sunday', container: '.restaurant_discount_options_Sunday_div', table: '#special_offer_table_Sunday' },
                { day: 'Monday', container: '.restaurant_discount_options_Monday_div', table: '#special_offer_table_Monday' },
                { day: 'Tuesday', container: '.restaurant_discount_options_Tuesday_div', table: '#special_offer_table_Tuesday' },
                { day: 'Wednesday', container: '.restaurant_discount_options_Wednesday_div', table: '#special_offer_table_Wednesday' },
                { day: 'Thursday', container: '.restaurant_discount_options_Thursday_div', table: '#special_offer_table_Thursday' },
                { day: 'Friday', container: '.restaurant_discount_options_Friday_div', table: '#special_offer_table_Friday' },
                { day: 'Saturday', container: '.restaurant_discount_options_Saturday_div', table: '#special_offer_table_Saturday' },
            ];

            config.forEach(({ day, container, table }) => {
                const slots = getSpecialDiscountArray(day);
                const $container = $(container);
                const $table = $(table);
                $table.find('tr:gt(0)').remove();

                if (!slots || !slots.length) {
                    $container.hide();
                    return;
                }

                $container.show();
                slots.forEach((slot, index) => {
                    const row = $('<tr></tr>');
                    const fromInput = $('<input type="time" class="form-control">').val(slot.from || '');
                    const toInput = $('<input type="time" class="form-control">').val(slot.to || '');
                    const discountInput = $('<input type="number" class="form-control" min="0" max="100" step="1">').val(slot.discount ?? 0);
                    const discountTypeSelect = $('<select class="form-control"></select>')
                        .append('<option value="percentage">%</option>')
                        .append(`<option value="amount">${currentCurrency || '{{ trans("lang.coupon_fixed") }}'}</option>`)
                        .val(slot.type || 'percentage');
                    const slotTypeSelect = $('<select class="form-control"></select>')
                        .append('<option value="delivery">Delivery Discount</option>')
                        .append('<option value="dinein">Dine-In Discount</option>')
                        .val(slot.discount_type || 'delivery');
                    const deleteButton = $('<button type="button" class="btn btn-primary"><i class="mdi mdi-delete"></i></button>');

                    fromInput.on('change', function () {
                        updateSpecialDiscountSlot(day, index, 'from', this.value);
                    });
                    toInput.on('change', function () {
                        updateSpecialDiscountSlot(day, index, 'to', this.value);
                    });
                    discountInput.on('change', function () {
                        updateSpecialDiscountSlot(day, index, 'discount', parseFloat(this.value || 0));
                    });
                    discountTypeSelect.on('change', function () {
                        updateSpecialDiscountSlot(day, index, 'type', this.value);
                    });
                    slotTypeSelect.on('change', function () {
                        updateSpecialDiscountSlot(day, index, 'discount_type', this.value);
                    });
                    deleteButton.on('click', function () {
                        removeSpecialDiscountSlot(day, index);
                    });

                    row.append($('<td style="width:10%;"></td>').append(fromInput));
                    row.append($('<td style="width:10%;"></td>').append(toInput));
                    const discountCell = $('<td style="width:30%; display:flex; gap:8px;"></td>');
                    discountCell.append($('<div style="width:60%;"></div>').append(discountInput));
                    discountCell.append($('<div style="width:40%;"></div>').append(discountTypeSelect));
                    row.append(discountCell);
                    row.append($('<td style="width:30%;"></td>').append(slotTypeSelect));
                    row.append($('<td class="action-btn" style="width:20%;"></td>').append(deleteButton));
                    $table.append(row);
                });
            });
        }

        function renderWorkingHoursTables() {
            const config = [
                { day: 'Sunday', container: '.restaurant_working_hour_Sunday_div', table: '#working_hour_table_Sunday' },
                { day: 'Monday', container: '.restaurant_working_hour_Monday_div', table: '#working_hour_table_Monday' },
                { day: 'Tuesday', container: '.restaurant_working_hour_Tuesday_div', table: '#working_hour_table_Tuesday' },
                { day: 'Wednesday', container: '.restaurant_working_hour_Wednesday_div', table: '#working_hour_table_Wednesday' },
                { day: 'Thursday', container: '.restaurant_working_hour_Thursday_div', table: '#working_hour_table_Thursday' },
                { day: 'Friday', container: '.restaurant_working_hour_Friday_div', table: '#working_hour_table_Friday' },
                { day: 'Saturday', container: '.restaurant_working_hour_Saturday_div', table: '#working_hour_table_Saturday' },
            ];

            config.forEach(({ day, container, table }) => {
                const slots = getWorkingHoursArray(day);
                const $container = $(container);
                const $table = $(table);
                $table.find('tr:gt(0)').remove();

                if (!slots || !slots.length) {
                    $container.hide();
                    return;
                }

                $container.show();
                slots.forEach((slot, index) => {
                    const row = $('<tr></tr>');
                    const fromInput = $('<input type="time" class="form-control">').val(slot.from || '');
                    const toInput = $('<input type="time" class="form-control">').val(slot.to || '');
                    const deleteButton = $('<button type="button" class="btn btn-primary"><i class="mdi mdi-delete"></i></button>');

                    fromInput.on('change', function () {
                        updateWorkingHourSlot(day, index, 'from', this.value);
                    });
                    toInput.on('change', function () {
                        updateWorkingHourSlot(day, index, 'to', this.value);
                    });
                    deleteButton.on('click', function () {
                        removeWorkingHourSlot(day, index);
                    });

                    row.append($('<td style="width:50%;"></td>').append(fromInput));
                    row.append($('<td style="width:50%;"></td>').append(toInput));
                    row.append($('<td class="action-btn" style="width:20%;"></td>').append(deleteButton));
                    $table.append(row);
                });
            });
        }

        async function saveRestaurant() {
            clearError();
            const errors = [];

            const restaurantname = $('.restaurant_name').val().trim();
            const vendorCuisine = $('#restaurant_vendor_cuisines').val();
            const categoryIDs = $('#restaurant_cuisines').val() || [];
            const categoryTitles = $('#restaurant_cuisines option:selected').map(function () {
                return $(this).val() ? $(this).text() : null;
            }).get().filter(Boolean);
            const address = $('.restaurant_address').val().trim();
            const vendorType = $('#vendor_type').val();
            const latitude = parseFloat($('.restaurant_latitude').val());
            const longitude = parseFloat($('.restaurant_longitude').val());
            const description = $('.restaurant_description').val().trim();
            const countryCode = $('#country_selector1').val();
            const phonenumber = $('.restaurant_phone').val().trim();
            const zoneId = $('#zone').val();
            const zoneArea = $('#zone option:selected').data('area') || [];
            const isOpen = $('#is_open').is(':checked');
            const enabledDiveInFuture = $('#dine_in_feature').is(':checked');
            const openDineTimeVal = $('#openDineTime').val();
            const closeDineTimeVal = $('#closeDineTime').val();
            const restaurantCost = $('.restaurant_cost').val();
            const deliveryChargesPerKm = $('#delivery_charges_per_km').val();
            const minimumDeliveryCharges = $('#minimum_delivery_charges').val();
            const minimumDeliveryChargesWithinKm = $('#minimum_delivery_charges_within_km').val();
            const specialDiscountEnable = $('#specialDiscountEnable').is(':checked');

            if (!restaurantname) {
                errors.push("{{ trans('lang.restaurant_name_error') }}");
            }
            if (!vendorCuisine) {
                errors.push('Please select the vendor cuisine.');
            }
            if (!categoryIDs.length || (categoryIDs.length === 1 && categoryIDs[0] === '')) {
                errors.push("{{ trans('lang.restaurant_cuisine_error') }}");
            }
            if (!phonenumber) {
                errors.push("{{ trans('lang.restaurant_phone_error') }}");
            }
            if (!address) {
                errors.push("{{ trans('lang.restaurant_address_error') }}");
            }
            if (!zoneId) {
                errors.push("{{ trans('lang.select_zone_help') }}");
            }
            if (!Number.isFinite(latitude)) {
                errors.push("{{ trans('lang.restaurant_lattitude_error') }}");
            } else if (latitude < -90 || latitude > 90) {
                errors.push("{{ trans('lang.restaurant_lattitude_limit_error') }}");
            }
            if (!Number.isFinite(longitude)) {
                errors.push("{{ trans('lang.restaurant_longitude_error') }}");
            } else if (longitude < -180 || longitude > 180) {
                errors.push("{{ trans('lang.restaurant_longitude_limit_error') }}");
            }
            if (zoneArea.length && !checkLocationInZone(zoneArea, longitude, latitude)) {
                errors.push("{{ trans('lang.invalid_location_zone') }}");
            }
            if (!description) {
                errors.push("{{ trans('lang.restaurant_description_error') }}");
            }
            if (!vendorType) {
                errors.push("Please select vendor type.");
            }

            if (errors.length) {
                showError(errors);
                return;
            }

            jQuery('#data-table_processing').show();

            try {
                const filters = {
                    'Free Wi-Fi': $('#Free_Wi_Fi').is(':checked') ? 'Yes' : 'No',
                    'Good for Breakfast': $('#Good_for_Breakfast').is(':checked') ? 'Yes' : 'No',
                    'Good for Dinner': $('#Good_for_Dinner').is(':checked') ? 'Yes' : 'No',
                    'Good for Lunch': $('#Good_for_Lunch').is(':checked') ? 'Yes' : 'No',
                    'Live Music': $('#Live_Music').is(':checked') ? 'Yes' : 'No',
                    'Outdoor Seating': $('#Outdoor_Seating').is(':checked') ? 'Yes' : 'No',
                    'Takes Reservations': $('#Takes_Reservations').is(':checked') ? 'Yes' : 'No',
                    'Vegetarian Friendly': $('#Vegetarian_Friendly').is(':checked') ? 'Yes' : 'No'
                };

                const adminCommission = {
                    commissionType: $('#commission_type').val(),
                    fix_commission: parseInt($('.commission_fix').val() || 0, 10),
                    isEnabled: true
                };

                const deliveryChargePayload = {
                    delivery_charges_per_km: deliveryChargesPerKm,
                    minimum_delivery_charges: minimumDeliveryCharges,
                    minimum_delivery_charges_within_km: minimumDeliveryChargesWithinKm
                };

                const galleryUrls = await storeGalleryImageData();
                const menuUrls = await storeMenuImageData();

                // Store story data
                const storyData = await storeStoryImageData();

                const payload = {
                    title: restaurantname,
                    description,
                    latitude,
                    longitude,
                    location: address,
                    vendorCuisineID: vendorCuisine,
                    categoryID: categoryIDs.filter(Boolean),
                    categoryTitle: categoryTitles,
                    phonenumber,
                    countryCode: countryCode ? '+' + countryCode : '',
                    zoneId,
                    filters,
                    reststatus: true,
                    isOpen,
                    enabledDiveInFuture,
                    openDineTime: openDineTimeVal || null,
                    closeDineTime: closeDineTimeVal || null,
                    restaurantCost,
                    DeliveryCharge: deliveryChargePayload,
                    specialDiscount: buildSpecialDiscountPayload(),
                    specialDiscountEnable,
                    workingHours: buildWorkingHoursPayload(),
                    adminCommission,
                    photo: galleryUrls.length ? galleryUrls[0] : null,
                    photos: galleryUrls,
                    restaurantMenuPhotos: menuUrls,
                    vType: vendorType,
                    storyData: {
                        thumbnail: storyData.storyThumbnailImage || '',
                        videos: story_vedios || []
                    }
                };

                await $.ajax({
                    url: routes.update,
                    method: 'PUT',
                    contentType: 'application/json; charset=utf-8',
                    data: JSON.stringify(payload)
                });

                window.location.href = "{{ route('restaurants') }}";
            } catch (error) {
                console.error('Failed to update restaurant', error);
                const message = error?.responseJSON?.message || error.message || 'Failed to update restaurant.';
                showError(message);
            } finally {
                jQuery('#data-table_processing').hide();
            }
        }

        function buildSpecialDiscountPayload() {
            return dayOrder.map(day => {
                const list = (getSpecialDiscountArray(day) || []).filter(slot => slot.from && slot.to);
                return {
                    day,
                    timeslot: list.map(slot => ({
                        from: slot.from,
                        to: slot.to,
                        discount: slot.discount ?? 0,
                        type: slot.type || 'percentage',
                        discount_type: slot.discount_type || 'delivery'
                    }))
                };
            });
        }

        function buildWorkingHoursPayload() {
            return dayOrder.map(day => {
                const list = (getWorkingHoursArray(day) || []).filter(slot => slot.from && slot.to);
                return {
                    day,
                    timeslot: list.map(slot => ({
                        from: slot.from,
                        to: slot.to
                    }))
                };
            });
        }

        async function storeGalleryImageData() {
            let finalPhotos = restaurnt_photos.slice();
            if (new_added_restaurant_photos.length) {
                for (let i = 0; i < new_added_restaurant_photos.length; i++) {
                    const base64 = new_added_restaurant_photos[i];
                    const filename = new_added_restaurant_photos_filename[i] || `restaurant_${Date.now()}_${i}.jpg`;
                    const uploadedUrl = await uploadBase64Image(base64, 'restaurant_gallery', filename);
                    finalPhotos.push(uploadedUrl);
                }
            }
            restaurnt_photos = finalPhotos.slice();
            new_added_restaurant_photos = [];
            new_added_restaurant_photos_filename = [];
            return finalPhotos;
        }

        async function storeMenuImageData() {
            let finalPhotos = restaurant_menu_photos.slice();
            if (new_added_restaurant_menu.length) {
                for (let i = 0; i < new_added_restaurant_menu.length; i++) {
                    const base64 = new_added_restaurant_menu[i];
                    const filename = new_added_restaurant_menu_filename[i] || `restaurant_menu_${Date.now()}_${i}.jpg`;
                    const uploadedUrl = await uploadBase64Image(base64, 'restaurant_menu', filename);
                    finalPhotos.push(uploadedUrl);
                }
            }
            restaurant_menu_photos = finalPhotos.slice();
            new_added_restaurant_menu = [];
            new_added_restaurant_menu_filename = [];
            return finalPhotos;
        }

        function uploadBase64Image(base64Data, folder, filename) {
            return $.ajax({
                url: routes.uploadImage,
                method: 'POST',
                dataType: 'json',
                data: {
                    image: base64Data,
                    folder: folder || 'uploads',
                    filename: filename || `image_${Date.now()}.jpg`
                }
            }).then(response => {
                if (!response || !response.success || !response.url) {
                    throw new Error(response && response.message ? response.message : 'Image upload failed');
                }
                return response.url;
            });
        }

        function handleFileSelect(evt, type) {
            const file = evt.target.files[0];
            if (!file) {
                return;
            }

            new Compressor(file, {
                quality: {{ env('IMAGE_COMPRESSOR_QUALITY', 0.8) }},
                success(result) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const filePayload = e.target.result;
                        const originalName = result.name || 'image.jpg';
                        const extension = originalName.split('.').pop();
                        const filename = `${originalName.replace(/\.[^/.]+$/, '')}_${Date.now()}.${extension}`;

                        if (type === 'photos') {
                            photocount += 1;
                            new_added_restaurant_photos.push(filePayload);
                            new_added_restaurant_photos_filename.push(filename);
                            const html = `<span class="image-item" id="photo_${photocount}">
                                    <span class="remove-btn" data-id="${photocount}" data-img="${filePayload}" data-status="new"><i class="fa fa-remove"></i></span>
                                    <img width="100px" height="auto" src="${filePayload}" onerror="this.src='${placeholderImage}'">
                                  </span>`;
                            if ($('#photos p').length) {
                                $('#photos').html(html);
                            } else {
                                $('#photos').append(html);
                            }
                        } else if (type === 'menu') {
                            menuPhotoCount += 1;
                            new_added_restaurant_menu.push(filePayload);
                            new_added_restaurant_menu_filename.push(filename);
                            const html = `<span class="image-item" id="photo_menu_${menuPhotoCount}">
                                    <span class="remove-menu-btn" data-id="${menuPhotoCount}" data-img="${filePayload}" data-status="new"><i class="fa fa-remove"></i></span>
                                    <img width="100px" height="auto" src="${filePayload}" onerror="this.src='${placeholderImage}'">
                                  </span>`;
                            if ($('#photos_menu_card p').length) {
                                $('#photos_menu_card').html(html);
                            } else {
                                $('#photos_menu_card').append(html);
                            }
                        }
                    };
                    reader.readAsDataURL(result);
                },
                error(err) {
                    console.error('Image compression error', err);
                    showError('Unable to process image: ' + err.message);
                }
            });

            if (evt && evt.target) {
                evt.target.value = '';
            }
        }

        function shortEditNumber(number) {
            if (!number) {
                return '';
            }
            return number.replace(/[^0-9+]/g, '');
        }

        function convertTo24Hour(timeString) {
            if (!timeString) {
                return '';
            }
            if (/^\d{2}:\d{2}$/.test(timeString)) {
                return timeString;
            }
            return moment(timeString, ['h:mm A', 'hh:mm A']).format('HH:mm');
        }

        function updateSelectedCategoryTags() {
            const selected = $('#restaurant_cuisines option:selected').map(function () {
                return $(this).val() ? {
                    value: $(this).val(),
                    text: $(this).text()
                } : null;
            }).get().filter(Boolean);

            if (!selected.length) {
                $('#selected_categories').html('');
                return;
            }

            const html = selected.map(item => `<span class="selected-category-tag" data-value="${item.value}">
                                            ${item.text}
                                            <span class="remove-tag">&times;</span>
                                        </span>`).join('');
            $('#selected_categories').html(html);
        }

        function formatState(state) {
            if (!state.id) {
                return state.text;
            }
            const code = newcountriesjs[state.id];
            if (!code) {
                return state.text;
            }
            const baseUrl = "{{ URL::to('/') }}/scss/icons/flag-icon-css/flags";
            const $state = $(`<span><img src="${baseUrl}/${code.toLowerCase()}.svg" class="img-flag" /> ${state.text}</span>`);
            return $state;
        }

        function formatStateSelection(state) {
            if (!state.id) {
                return state.text;
            }
            const code = newcountriesjs[state.id];
            if (!code) {
                return state.text;
            }
            const baseUrl = "{{ URL::to('/') }}/scss/icons/flag-icon-css/flags";
            const $state = $(`<span><img class="img-flag" src="${baseUrl}/${code.toLowerCase()}.svg" /> ${state.text}</span>`);
            return $state;
        }

        function getSpecialDiscountArray(day) {
            switch (day) {
                case 'Sunday': return timeslotSunday;
                case 'Monday': return timeslotMonday;
                case 'Tuesday': return timeslotTuesday;
                case 'Wednesday': return timeslotWednesday;
                case 'Thursday': return timeslotThursday;
                case 'Friday': return timeslotFriday;
                case 'Saturday': return timeslotSaturday;
                default: return null;
            }
        }

        function getWorkingHoursArray(day) {
            switch (day) {
                case 'Sunday': return timeslotworkSunday;
                case 'Monday': return timeslotworkMonday;
                case 'Tuesday': return timeslotworkTuesday;
                case 'Wednesday': return timeslotworkWednesday;
                case 'Thursday': return timeslotworkThursday;
                case 'Friday': return timeslotworkFriday;
                case 'Saturday': return timeslotworkSaturday;
                default: return null;
            }
        }

        function addSpecialDiscountSlot(day) {
            const target = getSpecialDiscountArray(day);
            if (!target) {
                return;
            }
            target.push({
                from: '09:30',
                to: '22:00',
                discount: 10,
                type: 'percentage',
                discount_type: 'delivery'
            });
            renderSpecialDiscountTables();
        }

        function removeSpecialDiscountSlot(day, index) {
            const target = getSpecialDiscountArray(day);
            if (target && target[index]) {
                target.splice(index, 1);
                renderSpecialDiscountTables();
            }
        }

        function updateSpecialDiscountSlot(day, index, field, value) {
            const target = getSpecialDiscountArray(day);
            if (!target || !target[index]) {
                return;
            }
            target[index][field] = value;
        }

        function addWorkingHourSlot(day) {
            const target = getWorkingHoursArray(day);
            if (!target) {
                return;
            }
            target.push({
                from: '09:30',
                to: '22:00'
            });
            renderWorkingHoursTables();
        }

        function removeWorkingHourSlot(day, index) {
            const target = getWorkingHoursArray(day);
            if (target && target[index]) {
                target.splice(index, 1);
                renderWorkingHoursTables();
            }
        }

        function updateWorkingHourSlot(day, index, field, value) {
            const target = getWorkingHoursArray(day);
            if (!target || !target[index]) {
                return;
            }
            target[index][field] = value;
        }

        function checkLocationInZone(area, lng, lat) {
            if (!Array.isArray(area) || !area.length) {
                return true;
            }
            let inside = false;
            for (let i = 0, j = area.length - 1; i < area.length; j = i++) {
                const xi = parseFloat(area[i].longitude);
                const yi = parseFloat(area[i].latitude);
                const xj = parseFloat(area[j].longitude);
                const yj = parseFloat(area[j].latitude);

                const intersect = ((yi > lat) !== (yj > lat)) &&
                    (lng < (xj - xi) * (lat - yi) / ((yj - yi) || 0.0000001) + xi);
                if (intersect) {
                    inside = !inside;
                }
            }
            return inside;
        }

        function fetchJson(url) {
            return $.ajax({
                url,
                method: 'GET',
                dataType: 'json'
            });
        }

        function showError(messages) {
            const list = Array.isArray(messages) ? messages : [messages];
            const $error = $('.error_top');
            $error.html('');
            list.filter(Boolean).forEach(msg => {
                $error.append(`<p>${msg}</p>`);
            });
            if (list.length) {
                $error.show();
                window.scrollTo(0, 0);
            }
        }

        function clearError() {
            $('.error_top').hide().html('');
        }

        // Legacy callbacks retained for inline handlers
        window.addMoreButton = function (day) {
            addSpecialDiscountSlot(day);
        };
        window.deleteOffer = function (day, index) {
            removeSpecialDiscountSlot(day, index);
        };
        window.addMoreFunctionButton = function () {};
        window.updateMoreFunctionButton = function () {};
        window.addMorehour = function (day) {
            addWorkingHourSlot(day);
        };
        window.deleteWorkingHour = function (day, index) {
            removeWorkingHourSlot(day, index);
        };
        window.addMoreFunctionhour = function (day) {
            addWorkingHourSlot(day);
        };
        window.updatehoursFunctionButton = function () {};
        window.handleFileSelectMenuCard = function (evt) {
            handleFileSelect(evt, 'menu');
        };

        // Story video upload functions
        async function handleStoryFileSelect(evt) {
            const f = evt.target.files[0];
            if (!f) return false;

            const fileInput = document.getElementById('video_file');
            const filePath = fileInput.value;
            const allowedExtensions = /(\.mp4)$/i;

            if (!allowedExtensions.exec(filePath)) {
                showError('Error: Invalid video type. Only MP4 files are allowed.');
                fileInput.value = '';
                return false;
            }

            // Check video duration if needed
            const video = document.createElement('video');
            video.preload = 'metadata';
            video.onloadedmetadata = async function () {
                window.URL.revokeObjectURL(video.src);

                // Optional: Check duration limit (30 seconds default)
                if (storevideoDuration > 0 && video.duration > storevideoDuration) {
                    showError('Error: Story video duration maximum allowed is ' + storevideoDuration + ' seconds');
                    evt.target.value = '';
                    return false;
                }

                // Read file as base64
                const reader = new FileReader();
                reader.onload = async function (e) {
                    const filePayload = e.target.result;
                    const val = f.name;
                    const ext = val.split('.').pop();
                    let filename = (f.name).replace(/C:\\fakepath\\/i, '');
                    const timestamp = Number(new Date());
                    filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;

                    try {
                        jQuery("#uploding_story_video").text("Video is uploading...");

                        // Upload video via Laravel API
                        const response = await $.ajax({
                            url: routes.uploadImage,
                            method: 'POST',
                            data: {
                                image: filePayload,
                                folder: 'restaurants/story',
                                filename: filename
                            }
                        });

                        jQuery("#uploding_story_video").text("Upload completed");
                        setTimeout(function () {
                            jQuery("#uploding_story_video").empty();
                        }, 3000);

                        const nextCount = $("#story_vedios").children().length;
                        const uploadedVideoUrl = response.url;

                        const html = `
<div class="col-md-3" id="story_div_${nextCount}">
    <div class="video-inner">
        <video width="320" height="240" controls autoplay muted>
            <source src="${uploadedVideoUrl}" type="video/mp4">
        </video>
        <span class="remove-story-video" data-id="${nextCount}" data-img="${uploadedVideoUrl}">
            <i class="fa fa-remove"></i>
        </span>
    </div>
</div>`;

                        jQuery("#story_vedios").append(html);
                        story_vedios.push(uploadedVideoUrl);
                        $("#video_file").val('');
                    } catch (error) {
                        console.error('Error uploading story video:', error);
                        jQuery("#uploding_story_video").text("Upload failed");
                        showError('Error uploading video: ' + (error.responseJSON?.message || error.message));
                    }
                };
                reader.readAsDataURL(f);
            };
            video.src = URL.createObjectURL(f);
        }

        function handleStoryThumbnailFileSelect(evt) {
            const f = evt.target.files[0];
            if (!f) return;

            const reader = new FileReader();
            const fileInput = document.getElementById('file');
            const filePath = fileInput.value;

            // Allowing file type
            const allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif)$/i;

            if (!allowedExtensions.exec(filePath)) {
                showError('Error: Invalid File type');
                fileInput.value = '';
                return false;
            }

            reader.onload = function (e) {
                const filePayload = e.target.result;
                const val = f.name;
                const ext = val.split('.').pop();
                let filename = (f.name).replace(/C:\\fakepath\\/i, '');
                const timestamp = Number(new Date());
                filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;

                story_thumbnail = filePayload;
                story_thumbnail_filename = filename;

                const html = `<div class="col-md-3"><div class="thumbnail-inner"><span class="remove-story-thumbnail" data-img="${story_thumbnail}"><i class="fa fa-remove"></i></span><img id="story_thumbnail_image" src="${story_thumbnail}" width="150px" height="150px;"></div></div>`;
                jQuery("#story_thumbnail").html(html);
            };
            reader.readAsDataURL(f);
        }

        async function storeStoryImageData() {
            const newPhoto = [];
            try {
                if (story_thumbnail && story_thumbnail !== '' && story_thumbnail.startsWith('data:')) {
                    // Only upload if it's a new base64 image, not an existing URL
                    const response = await $.ajax({
                        url: routes.uploadImage,
                        method: 'POST',
                        data: {
                            image: story_thumbnail,
                            folder: 'restaurants/story',
                            filename: story_thumbnail_filename || 'story_thumbnail_' + Date.now() + '.jpg'
                        }
                    });
                    newPhoto['storyThumbnailImage'] = response.url;
                } else if (story_thumbnail && story_thumbnail !== '') {
                    // Already a URL, use it directly
                    newPhoto['storyThumbnailImage'] = story_thumbnail;
                } else {
                    newPhoto['storyThumbnailImage'] = '';
                }
                return newPhoto;
            } catch (error) {
                console.error('Error storing story thumbnail:', error);
                newPhoto['storyThumbnailImage'] = story_thumbnail || '';
                return newPhoto;
            }
        }

        function renderStoryVideos() {
            if (!story_vedios.length) {
                jQuery("#story_vedios").html('');
                return;
            }
            let html = '';
            story_vedios.forEach((videoUrl, index) => {
                html += `
<div class="col-md-3" id="story_div_${index}">
    <div class="video-inner">
        <video width="320" height="240" controls autoplay muted>
            <source src="${videoUrl}" type="video/mp4">
        </video>
        <span class="remove-story-video" data-id="${index}" data-img="${videoUrl}">
            <i class="fa fa-remove"></i>
        </span>
    </div>
</div>`;
            });
            jQuery("#story_vedios").html(html);
        }

        function renderStoryThumbnail() {
            if (!story_thumbnail) {
                jQuery("#story_thumbnail").html('');
                return;
            }
            const html = `<div class="col-md-3"><div class="thumbnail-inner"><span class="remove-story-thumbnail" data-img="${story_thumbnail}"><i class="fa fa-remove"></i></span><img id="story_thumbnail_image" src="${story_thumbnail}" width="150px" height="150px;"></div></div>`;
            jQuery("#story_thumbnail").html(html);
        }

        // Bind story event handlers
        $(document).on("click", ".remove-story-video", function () {
            const id = $(this).attr('data-id');
            const photo_remove = $(this).attr('data-img');

            $("#story_div_" + id).remove();
            const index = story_vedios.indexOf(photo_remove);
            $("#video_file").val('');
            if (index > -1) {
                story_vedios.splice(index, 1);
            }

            renderStoryVideos();
        });

        $(document).on("click", ".remove-story-thumbnail", function () {
            const photo_remove = $(this).attr('data-img');

            $("#story_thumbnail").empty();
            $('#file').val('');
            story_thumbnail = '';
            story_thumbnail_filename = '';
        });

        // Make functions globally available for inline handlers
        window.handleStoryFileSelect = handleStoryFileSelect;
        window.handleStoryThumbnailFileSelect = handleStoryThumbnailFileSelect;
    </script>
@endsection
