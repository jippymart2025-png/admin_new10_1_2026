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
                <h3 class="text-themecolor">{{trans('lang.mart_plural')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('marts') !!}">{{trans('lang.mart_plural')}}</a></li>
                    <li class="breadcrumb-item active">{{trans('lang.mart_edit')}}</li>
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
                                    <a class="profileRoute">{{trans('lang.profile')}}</a>
                                </li>
                                <li class="active">
                                    <a href="{{route('marts.edit',$id)}}">{{trans('lang.mart')}}</a>
                                </li>
                            </ul>
                        </div>
                        <div class="error_top"></div>
                        <div class="row restaurant_payout_create">
                            <div class="restaurant_payout_create-inner">
                                <fieldset>
                                    <legend>{{trans('lang.mart_details')}}</legend>
                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label">{{trans('lang.mart_name')}}</label>
                                        <div class="col-7">
                                            <input type="text" class="form-control restaurant_name">
                                            <div class="form-text text-muted">{{ trans("lang.mart_name_help") }}</div>
                                        </div>
                                    </div>
                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label">{{trans('lang.category')}}</label>
                                        <div class="col-7">
                                            <div id="selected_categories" class="mb-2"></div>
                                            <input type="text" id="category_search" class="form-control mb-2" placeholder="Search categories...">
                                            <select id='restaurant_cuisines' class="form-control" multiple>
                                                <option value="">Select Categories</option>
                                            </select>
                                            <div class="form-text text-muted">
                                                {{ trans("lang.mart_cuisines_help") }} (Hold Ctrl/Cmd to select multiple)
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label">{{trans('lang.mart_phone')}}</label>
                                        <div class="col-md-12">
                                            <div class="phone-box position-relative">
                                                <select name="country" id="country_selector1">
                                                    <?php foreach ($newcountries as $keycy => $valuecy) { ?>
                                                        <?php $selected = ""; ?>
                                                    <option <?php echo $selected; ?> code="<?php echo $valuecy->code; ?>" value="<?php echo $keycy; ?>">
                                                        +<?php echo $valuecy->phoneCode; ?> {{$valuecy->countryName}}
                                                    </option>
                                                    <?php } ?>
                                                </select>
                                                <input type="text" class="form-control restaurant_phone" onkeypress="return chkAlphabets2(event,'error2')">
                                                <div id="error2" class="err"></div>
                                            </div>
                                        </div>
                                        <div class="form-text text-muted">{{ trans("lang.mart_phone_help") }}</div>
                                    </div>
                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label">{{trans('lang.mart_address')}}</label>
                                        <div class="col-7">
                                            <input type="text" class="form-control restaurant_address">
                                            <div class="form-text text-muted">{{ trans("lang.mart_address_help") }}</div>
                                        </div>
                                    </div>
                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label">{{trans('lang.zone')}}<span class="required-field"></span></label>
                                        <div class="col-7">
                                            <select id='zone' class="form-control">
                                                <option value="">{{ trans("lang.select_zone") }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label">{{trans('lang.mart_latitude')}}</label>
                                        <div class="col-7">
                                            <input type="text" class="form-control restaurant_latitude">
                                            <div class="form-text text-muted">{{ trans("lang.mart_latitude_help") }}</div>
                                        </div>
                                        <div class="form-text text-muted ml-3">
                                            Don't Know your coordinates? use <a target="_blank" href="https://www.latlong.net/">Latitude and Longitude Finder</a>
                                        </div>
                                    </div>
                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label">{{trans('lang.mart_longitude')}}</label>
                                        <div class="col-7">
                                            <input type="text" class="form-control restaurant_longitude">
                                            <div class="form-text text-muted">{{ trans("lang.mart_longitude_help") }}</div>
                                        </div>
                                    </div>
                                    <div class="form-group row width-100">
                                        <label class="col-3 control-label">{{trans('lang.mart_description')}}</label>
                                        <div class="col-7">
                                            <textarea rows="7" class="restaurant_description form-control" id="restaurant_description"></textarea>
                                        </div>
                                    </div>
                                </fieldset>

                                <fieldset>
                                    <legend>{{ trans('lang.mart_admin_commission_details') }}</legend>
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
                                                <button type="button" class="btn btn-primary add_working_hours_restaurant_btn">
                                                    <i></i>{{trans('lang.add_working_hours')}}
                                                </button>
                                            </div>
                                        </div>
                                        <div class="working_hours_div" style="display:none">
                                            @foreach(['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day)
                                                <div class="form-group row mb-0">
                                                    <label class="col-1 control-label">{{trans('lang.'.strtolower($day))}}</label>
                                                    <div class="col-12">
                                                        <button type="button" class="btn btn-primary" onclick="addMorehour('{{$day}}','{{strtolower($day)}}','1')">
                                                            {{trans('lang.add_more')}}
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="restaurant_working_hour_{{$day}}_div restaurant_discount mb-5" style="display:none">
                                                    <table class="booking-table" id="working_hour_table_{{$day}}">
                                                        <tr>
                                                            <th><label class="col-3 control-label">{{trans('lang.from')}}</label></th>
                                                            <th><label class="col-3 control-label">{{trans('lang.to')}}</label></th>
                                                            <th><label class="col-3 control-label">{{trans('lang.actions')}}</label></th>
                                                        </tr>
                                                    </table>
                                                </div>
                                            @endforeach
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
                                                <input type="checkbox" id="dine_in_feature">
                                                <label class="col-3 control-label" for="dine_in_feature">{{trans('lang.enable_dine_in_feature')}}</label>
                                            </div>
                                        </div>
                                        <div class="divein_div" style="display:none">
                                            <div class="form-group row width-50">
                                                <label class="col-3 control-label">{{trans('lang.Opening_Time')}}</label>
                                                <div class="col-7">
                                                    <input type="time" class="form-control" id="openDineTime">
                                                </div>
                                            </div>
                                            <div class="form-group row width-50">
                                                <label class="col-3 control-label">{{trans('lang.Closing_Time')}}</label>
                                                <div class="col-7">
                                                    <input type="time" class="form-control" id="closeDineTime">
                                                </div>
                                            </div>
                                            <div class="form-group row width-50">
                                                <label class="col-3 control-label">Cost</label>
                                                <div class="col-7">
                                                    <input type="number" class="form-control restaurant_cost">
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
                                            <label class="col-4 control-label">{{ trans('lang.delivery_charges_per')}} <span class="global_distance_type"></span></label>
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
                                            <label class="col-4 control-label">{{ trans('lang.minimum_delivery_charges_within')}} <span class="global_distance_type"></span></label>
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
                                        <label class="col-3 control-label" for="specialDiscountEnable">{{trans('lang.special_discount_enable')}}</label>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-12 control-label" style="color:red;font-size:15px;">NOTE: Please Click on Edit Button After Making Changes in Special Discount, Otherwise Data may not Save!!</label>
                                    </div>
                                    <div class="form-group row">
                                        <div class="form-group row width-100">
                                            <div class="col-7">
                                                <button type="button" class="btn btn-primary add_special_offer_restaurant_btn">
                                                    <i></i>{{trans('lang.add_special_offer')}}
                                                </button>
                                            </div>
                                        </div>
                                        <div class="special_offer_div" style="display:none">
                                            @foreach(['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day)
                                                <div class="form-group row">
                                                    <label class="col-1 control-label">{{trans('lang.'.strtolower($day))}}</label>
                                                    <div class="col-12">
                                                        <button type="button" class="btn btn-primary" onclick="addMoreButton('{{$day}}','{{strtolower($day)}}','1')">
                                                            {{trans('lang.add_more')}}
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="restaurant_discount_options_{{$day}}_div restaurant_discount" style="display:none">
                                                    <table class="booking-table" id="special_offer_table_{{$day}}">
                                                        <tr>
                                                            <th><label class="col-3 control-label">{{trans('lang.Opening_Time')}}</label></th>
                                                            <th><label class="col-3 control-label">{{trans('lang.Closing_Time')}}</label></th>
                                                            <th><label class="col-3 control-label">{{trans('lang.coupon_discount')}}</label></th>
                                                            <th><label class="col-3 control-label">{{trans('lang.coupon_discount')}} {{trans('lang.type')}}</label></th>
                                                            <th><label class="col-3 control-label">{{trans('lang.actions')}}</label></th>
                                                        </tr>
                                                    </table>
                                                </div>
                                            @endforeach
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
            </div>
            <div class="form-group col-12 text-center btm-btn">
                <button type="button" class="btn btn-primary edit-form-btn"><i class="fa fa-save"></i> {{trans('lang.save')}}</button>
                <a href="{!! route('marts') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const martId = "{{ $id }}";
        const placeholderImage = '{{ asset('images/placeholder.png') }}';
        const routes = {
            getMart:            "{{ route('marts.getById', ['id' => $id]) }}",
            categories:         "{{ route('marts.categories') }}",
            zones:              "{{ route('vendors.zones') }}",
            deliverySettings:   "{{ route('api.deliveryCharge.settings') }}",
            currency:           "{{ route('api.currencies.active') }}",
            specialOfferSettings: "{{ route('api.specialoffer.settings') }}",
            uploadImage:        "{{ route('api.upload.image') }}",
            update:             "{{ route('marts.update', ['id' => $id]) }}",
        };

        const newcountriesjs = @json($newcountriesjs);
        const dayOrder = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

        // ── State variables ──────────────────────────────────────────────
        let restaurnt_photos = [];
        let new_added_restaurant_photos = [];
        let new_added_restaurant_photos_filename = [];
        let restaurant_menu_photos = [];
        let new_added_restaurant_menu = [];
        let new_added_restaurant_menu_filename = [];
        let photocount = 0;
        let menuPhotoCount = 0;

        let story_vedios = [];
        let story_thumbnail = '';
        let story_thumbnail_filename = '';
        let storevideoDuration = 0;

        let specialDiscountOfferisEnable = false;
        let vendorCanModifyDeliveryCharge = false;
        let currentCurrency = '';
        let currencyAtRight = false;
        let deliverySettingsDefaults = {};

        let timeslotSunday    = [], timeslotMonday   = [], timeslotTuesday  = [];
        let timeslotWednesday = [], timeslotThursday = [], timeslotFriday   = [], timeslotSaturday = [];

        let timeslotworkSunday    = [], timeslotworkMonday   = [], timeslotworkTuesday  = [];
        let timeslotworkWednesday = [], timeslotworkThursday = [], timeslotworkFriday   = [], timeslotworkSaturday = [];

        // ── Boot ─────────────────────────────────────────────────────────
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

        // ── Init helpers ─────────────────────────────────────────────────
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
                    if ($(this).val() === '') { $(this).show(); return; }
                    $(this).toggle($(this).text().toLowerCase().indexOf(search) > -1);
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
                const status   = $(this).data('status');
                const photoUrl = $(this).data('img');
                const elementId= $(this).data('id');
                if (status === 'old') {
                    restaurnt_photos = restaurnt_photos.filter(u => u !== photoUrl);
                } else {
                    const idx = new_added_restaurant_photos.indexOf(photoUrl);
                    if (idx > -1) { new_added_restaurant_photos.splice(idx,1); new_added_restaurant_photos_filename.splice(idx,1); }
                }
                $('#photo_' + elementId).remove();
                if (!$('#photos .image-item').length) $('#photos').html('<p>Photos not available.</p>');
            });
        }

        function bindMenuEvents() {
            $('#photos_menu_card').on('click', '.remove-menu-btn', function () {
                const status   = $(this).data('status');
                const photoUrl = $(this).data('img');
                const elementId= $(this).data('id');
                if (status === 'old') {
                    restaurant_menu_photos = restaurant_menu_photos.filter(u => u !== photoUrl);
                } else {
                    const idx = new_added_restaurant_menu.indexOf(photoUrl);
                    if (idx > -1) { new_added_restaurant_menu.splice(idx,1); new_added_restaurant_menu_filename.splice(idx,1); }
                }
                $('#photo_menu_' + elementId).remove();
                if (!$('#photos_menu_card .image-item').length) $('#photos_menu_card').html('<p>Menu card photos not available.</p>');
            });
        }

        function bindMiscEvents() {
            $('#dine_in_feature').on('change', function () { $('.divein_div').toggle(this.checked); });

            $('.add_special_offer_restaurant_btn').on('click', function () {
                if (!specialDiscountOfferisEnable) { alert("{{ trans('lang.special_offer_disabled') }}"); return; }
                $('.special_offer_div').show();
                if (!anySpecialDiscountSlots()) addSpecialDiscountSlot('Sunday');
            });

            $('.add_working_hours_restaurant_btn').on('click', function () {
                $('.working_hours_div').show();
                if (!anyWorkingHourSlots()) addWorkingHourSlot('Monday');
            });

            $('.edit-form-btn').on('click', async function (e) {
                e.preventDefault();
                await saveMart();
            });

            $(document).on('click', '.remove-story-video', function () {
                const id  = $(this).data('id');
                const img = $(this).data('img');
                $('#story_div_' + id).remove();
                const idx = story_vedios.indexOf(img);
                if (idx > -1) story_vedios.splice(idx, 1);
                $('#video_file').val('');
                renderStoryVideos();
            });

            $(document).on('click', '.remove-story-thumbnail', function () {
                $('#story_thumbnail').empty();
                $('#file').val('');
                story_thumbnail = '';
                story_thumbnail_filename = '';
            });
        }

        // ── Load all data from MySQL API ─────────────────────────────────
        async function loadInitialData() {
            jQuery('#data-table_processing').show();
            clearError();
            try {
                // 1. Mart data (required)
                const martRes = await fetchJson(routes.getMart);
                if (!martRes || !martRes.success || !martRes.data) {
                    throw new Error(martRes && martRes.message ? martRes.message : 'Unable to load mart data.');
                }

                // 2. Categories
                try {
                    const catRes = await fetchJson(routes.categories);
                    const cats = catRes && catRes.success ? catRes.data : [];
                    populateCategorySelect(cats);
                } catch(e) { populateCategorySelect([]); }

                // 3. Zones
                try {
                    const zoneRes = await fetchJson(routes.zones);
                    populateZonesSelect(zoneRes && zoneRes.success ? zoneRes.data : []);
                } catch(e) { console.warn('Zones unavailable', e); populateZonesSelect([]); }

                // 4. Delivery settings
                try {
                    const delRes = await fetchJson(routes.deliverySettings);
                    if (delRes) {
                        vendorCanModifyDeliveryCharge = !!delRes.vendor_can_modify;
                        deliverySettingsDefaults = delRes;
                    }
                } catch(e) { console.warn('Delivery settings unavailable', e); }

                // 5. Currency
                try {
                    const curRes = await fetchJson(routes.currency);
                    if (curRes && curRes.success && curRes.data) {
                        currentCurrency = curRes.data.symbol || '';
                        currencyAtRight = !!curRes.data.symbolAtRight;
                    }
                } catch(e) { console.warn('Currency unavailable', e); }

                // 6. Special offer settings
                try {
                    const soRes = await fetchJson(routes.specialOfferSettings);
                    if (soRes && typeof soRes.isEnable !== 'undefined') {
                        specialDiscountOfferisEnable = !!soRes.isEnable;
                    }
                } catch(e) { console.warn('Special offer settings unavailable', e); }

                // 7. Populate the form
                populateForm(martRes.data);

            } catch (error) {
                console.error(error);
                showError(error.message || 'Failed to load mart details.');
            } finally {
                jQuery('#data-table_processing').hide();
            }
        }

        // ── Populate selects ─────────────────────────────────────────────
        function populateCategorySelect(categories) {
            const $s = $('#restaurant_cuisines');
            $s.empty().append('<option value="">Select Categories</option>');
            categories.forEach(c => $s.append($('<option></option>').attr('value', String(c.id)).text(c.title)));
        }

        function populateZonesSelect(zones) {
            const $s = $('#zone');
            $s.empty().append('<option value="">{{ trans("lang.select_zone") }}</option>');
            zones.forEach(z => {
                $s.append($('<option></option>').attr('value', z.id).text(z.name || '').data('area', z.area || []));
            });
        }

        // ── Populate form fields from mart data ──────────────────────────
        function populateForm(mart) {
            $('.restaurant_name').val(mart.title || '');
            $('.restaurant_phone').val(shortEditNumber(mart.phonenumber || ''));
            $('.restaurant_address').val(mart.location || '');
            $('.restaurant_latitude').val(mart.latitude || '');
            $('.restaurant_longitude').val(mart.longitude || '');
            $('.restaurant_description').val(mart.description || '');

            // Country code
            if (mart.countryCode) {
                $('#country_selector1').val(mart.countryCode.replace('+','')).trigger('change');
            }

            // Zone
            if (mart.zoneId) $('#zone').val(mart.zoneId).trigger('change');

            // Categories — support both categoryID and cuisineID field names
            const rawCatID = mart.categoryID || mart.cuisineID || null;
            if (rawCatID) {
                const catVal = Array.isArray(rawCatID) ? rawCatID.map(String) : [String(rawCatID)];
                $('#restaurant_cuisines').val(catVal).trigger('change');
            }
            updateSelectedCategoryTags();

            // Admin commission
            if (mart.adminCommission) {
                const ac = typeof mart.adminCommission === 'string' ? JSON.parse(mart.adminCommission) : mart.adminCommission;
                if (ac) {
                    $('#commission_type').val(ac.commissionType || 'Percent');
                    $('.commission_fix').val(ac.fix_commission ?? 0);
                }
            }

            // Open/closed
            $('#is_open').prop('checked', !!mart.isOpen);

            // Dine-in
            if (mart.enabledDiveInFuture) {
                $('#dine_in_feature').prop('checked', true);
                $('.divein_div').show();
            }
            $('#openDineTime').val(convertTo24Hour(mart.openDineTime) || '');
            $('#closeDineTime').val(convertTo24Hour(mart.closeDineTime) || '');
            $('.restaurant_cost').val(mart.restaurantCost || '');

            // Delivery charges
            if (mart.DeliveryCharge) {
                $('#delivery_charges_per_km').val(mart.DeliveryCharge.delivery_charges_per_km || '');
                $('#minimum_delivery_charges').val(mart.DeliveryCharge.minimum_delivery_charges || '');
                $('#minimum_delivery_charges_within_km').val(mart.DeliveryCharge.minimum_delivery_charges_within_km || '');
            } else {
                $('#delivery_charges_per_km').val(deliverySettingsDefaults.delivery_charges_per_km || '');
                $('#minimum_delivery_charges').val(deliverySettingsDefaults.minimum_delivery_charges || '');
                $('#minimum_delivery_charges_within_km').val(deliverySettingsDefaults.minimum_delivery_charges_within_km || '');
            }
            if (!vendorCanModifyDeliveryCharge) {
                $('#delivery_charges_per_km, #minimum_delivery_charges, #minimum_delivery_charges_within_km').prop('disabled', true);
            }

            // Filters / services
            populateFilters(mart.filters || {});

            // Special discount
            $('#specialDiscountEnable').prop('checked', !!mart.specialDiscountEnable);
            populateSpecialDiscount(mart.specialDiscount || []);

            // Working hours
            populateWorkingHours(mart.workingHours || []);

            // Gallery photos
            restaurnt_photos = Array.isArray(mart.photos) ? mart.photos.slice() : [];
            renderPhotoGallery();

            // Menu photos
            restaurant_menu_photos = Array.isArray(mart.restaurantMenuPhotos) ? mart.restaurantMenuPhotos.slice() : [];
            renderMenuGallery();

            // Story
            if (mart.story) {
                if (Array.isArray(mart.story.videoUrl)) {
                    story_vedios = mart.story.videoUrl.slice();
                    renderStoryVideos();
                }
                if (mart.story.videoThumbnail) {
                    story_thumbnail = mart.story.videoThumbnail;
                    renderStoryThumbnail();
                }
            }

            // Profile route
            if (mart.author) {
                let r = '{{ route("vendor.edit", ":id") }}'.replace(':id', mart.author);
                $('.profileRoute').attr('href', r);
            }
        }

        function populateFilters(filters) {
            $('#Free_Wi_Fi').prop('checked',          filters['Free Wi-Fi']         === 'Yes');
            $('#Good_for_Breakfast').prop('checked',  filters['Good for Breakfast'] === 'Yes');
            $('#Good_for_Dinner').prop('checked',     filters['Good for Dinner']    === 'Yes');
            $('#Good_for_Lunch').prop('checked',      filters['Good for Lunch']     === 'Yes');
            $('#Live_Music').prop('checked',          filters['Live Music']         === 'Yes');
            $('#Outdoor_Seating').prop('checked',     filters['Outdoor Seating']    === 'Yes');
            $('#Takes_Reservations').prop('checked',  filters['Takes Reservations'] === 'Yes');
            $('#Vegetarian_Friendly').prop('checked', filters['Vegetarian Friendly']=== 'Yes');
        }

        function populateSpecialDiscount(rows) {
            timeslotSunday=[];timeslotMonday=[];timeslotTuesday=[];timeslotWednesday=[];
            timeslotThursday=[];timeslotFriday=[];timeslotSaturday=[];
            rows.forEach(row => {
                if (!row || !row.day || !Array.isArray(row.timeslot)) return;
                const target = getSpecialDiscountArray(row.day);
                if (target) {
                    row.timeslot.forEach(slot => target.push({
                        discount: slot.discount ?? 0, from: slot.from||'', to: slot.to||'',
                        type: slot.type||'percentage', discount_type: slot.discount_type||'delivery'
                    }));
                }
            });
            renderSpecialDiscountTables();
        }

        function populateWorkingHours(rows) {
            timeslotworkSunday=[];timeslotworkMonday=[];timeslotworkTuesday=[];timeslotworkWednesday=[];
            timeslotworkThursday=[];timeslotworkFriday=[];timeslotworkSaturday=[];
            rows.forEach(row => {
                if (!row || !row.day || !Array.isArray(row.timeslot)) return;
                const target = getWorkingHoursArray(row.day);
                if (target) row.timeslot.forEach(slot => target.push({ from: slot.from||'', to: slot.to||'' }));
            });
            renderWorkingHoursTables();
        }

        // ── Render galleries ─────────────────────────────────────────────
        function renderPhotoGallery() {
            photocount = 0;
            if (!restaurnt_photos.length) { $('#photos').html('<p>Photos not available.</p>'); return; }
            $('#photos').html(restaurnt_photos.map(url => {
                photocount++;
                return `<span class="image-item" id="photo_${photocount}">
                <span class="remove-btn" data-id="${photocount}" data-img="${url}" data-status="old"><i class="fa fa-remove"></i></span>
                <img width="100px" height="auto" src="${url}" onerror="this.src='${placeholderImage}'">
            </span>`;
            }).join(''));
        }

        function renderMenuGallery() {
            menuPhotoCount = 0;
            if (!restaurant_menu_photos.length) { $('#photos_menu_card').html('<p>Menu card photos not available.</p>'); return; }
            $('#photos_menu_card').html(restaurant_menu_photos.map(url => {
                menuPhotoCount++;
                return `<span class="image-item" id="photo_menu_${menuPhotoCount}">
                <span class="remove-menu-btn" data-id="${menuPhotoCount}" data-img="${url}" data-status="old"><i class="fa fa-remove"></i></span>
                <img width="100px" height="auto" src="${url}" onerror="this.src='${placeholderImage}'">
            </span>`;
            }).join(''));
        }

        function renderStoryVideos() {
            if (!story_vedios.length) { $('#story_vedios').html(''); return; }
            $('#story_vedios').html(story_vedios.map((url, i) => `
            <div class="col-md-3" id="story_div_${i}">
                <div class="video-inner">
                    <video width="320" height="240" controls autoplay muted><source src="${url}" type="video/mp4"></video>
                    <span class="remove-story-video" data-id="${i}" data-img="${url}"><i class="fa fa-remove"></i></span>
                </div>
            </div>`).join(''));
        }

        function renderStoryThumbnail() {
            if (!story_thumbnail) { $('#story_thumbnail').html(''); return; }
            $('#story_thumbnail').html(`<div class="col-md-3"><div class="thumbnail-inner">
            <span class="remove-story-thumbnail" data-img="${story_thumbnail}"><i class="fa fa-remove"></i></span>
            <img src="${story_thumbnail}" width="150px" height="150px">
        </div></div>`);
        }

        // ── Render special discount tables ───────────────────────────────
        function renderSpecialDiscountTables() {
            dayOrder.forEach(day => {
                const slots = getSpecialDiscountArray(day);
                const $container = $(`.restaurant_discount_options_${day}_div`);
                const $table = $(`#special_offer_table_${day}`);
                $table.find('tr:gt(0)').remove();
                if (!slots || !slots.length) { $container.hide(); return; }
                $container.show();
                slots.forEach((slot, index) => {
                    const row = $('<tr></tr>');
                    const fromInput    = $('<input type="time" class="form-control">').val(slot.from || '');
                    const toInput      = $('<input type="time" class="form-control">').val(slot.to || '');
                    const discInput    = $('<input type="number" class="form-control" min="0" max="100">').val(slot.discount ?? 0);
                    const typeSelect   = $('<select class="form-control"></select>')
                        .append('<option value="percentage">%</option>')
                        .append(`<option value="amount">${currentCurrency || '{{ trans("lang.coupon_fixed") }}'}</option>`)
                        .val(slot.type || 'percentage');
                    const slotSelect   = $('<select class="form-control"></select>')
                        .append('<option value="delivery">Delivery Discount</option>')
                        .append('<option value="dinein">Dine-In Discount</option>')
                        .val(slot.discount_type || 'delivery');
                    const delBtn = $('<button type="button" class="btn btn-primary"><i class="mdi mdi-delete"></i></button>');

                    fromInput.on('change',  function(){ updateSpecialDiscountSlot(day, index, 'from', this.value); });
                    toInput.on('change',    function(){ updateSpecialDiscountSlot(day, index, 'to',   this.value); });
                    discInput.on('change',  function(){ updateSpecialDiscountSlot(day, index, 'discount', parseFloat(this.value||0)); });
                    typeSelect.on('change', function(){ updateSpecialDiscountSlot(day, index, 'type', this.value); });
                    slotSelect.on('change', function(){ updateSpecialDiscountSlot(day, index, 'discount_type', this.value); });
                    delBtn.on('click',      function(){ removeSpecialDiscountSlot(day, index); });

                    row.append($('<td style="width:10%"></td>').append(fromInput));
                    row.append($('<td style="width:10%"></td>').append(toInput));
                    const discCell = $('<td style="width:30%;display:flex;gap:8px"></td>');
                    discCell.append($('<div style="width:60%"></div>').append(discInput));
                    discCell.append($('<div style="width:40%"></div>').append(typeSelect));
                    row.append(discCell);
                    row.append($('<td style="width:30%"></td>').append(slotSelect));
                    row.append($('<td class="action-btn" style="width:20%"></td>').append(delBtn));
                    $table.append(row);
                });
            });
        }

        // ── Render working hours tables ──────────────────────────────────
        function renderWorkingHoursTables() {
            dayOrder.forEach(day => {
                const slots = getWorkingHoursArray(day);
                const $container = $(`.restaurant_working_hour_${day}_div`);
                const $table = $(`#working_hour_table_${day}`);
                $table.find('tr:gt(0)').remove();
                if (!slots || !slots.length) { $container.hide(); return; }
                $container.show();
                slots.forEach((slot, index) => {
                    const row   = $('<tr></tr>');
                    const from  = $('<input type="time" class="form-control">').val(slot.from || '');
                    const to    = $('<input type="time" class="form-control">').val(slot.to   || '');
                    const delBtn= $('<button type="button" class="btn btn-primary"><i class="mdi mdi-delete"></i></button>');
                    from.on('change',  function(){ updateWorkingHourSlot(day, index, 'from', this.value); });
                    to.on('change',    function(){ updateWorkingHourSlot(day, index, 'to',   this.value); });
                    delBtn.on('click', function(){ removeWorkingHourSlot(day, index); });
                    row.append($('<td style="width:50%"></td>').append(from));
                    row.append($('<td style="width:50%"></td>').append(to));
                    row.append($('<td class="action-btn" style="width:20%"></td>').append(delBtn));
                    $table.append(row);
                });
            });
        }

        // ── Save ─────────────────────────────────────────────────────────
        async function saveMart() {
            clearError();
            const errors = [];

            const restaurantname  = $('.restaurant_name').val().trim();
            const categoryIDs     = ($('#restaurant_cuisines').val() || []).filter(Boolean);
            const categoryTitles  = $('#restaurant_cuisines option:selected').map(function(){ return $(this).val() ? $(this).text() : null; }).get().filter(Boolean);
            const address         = $('.restaurant_address').val().trim();
            const latitude        = parseFloat($('.restaurant_latitude').val());
            const longitude       = parseFloat($('.restaurant_longitude').val());
            const description     = $('.restaurant_description').val().trim();
            const countryCode     = $('#country_selector1').val();
            const phonenumber     = $('.restaurant_phone').val().trim();
            const zoneId          = $('#zone').val();
            const zoneArea        = $('#zone option:selected').data('area') || [];
            const isOpen          = $('#is_open').is(':checked');
            const enabledDiveIn   = $('#dine_in_feature').is(':checked');
            const openDineTimeVal = $('#openDineTime').val();
            const closeDineTimeVal= $('#closeDineTime').val();
            const restaurantCost  = $('.restaurant_cost').val();
            const specialDiscountEnable = $('#specialDiscountEnable').is(':checked');

            if (!restaurantname) errors.push("{{ trans('lang.restaurant_name_error') }}");
            if (!categoryIDs.length) errors.push('Please select the vendor category.');
            if (!phonenumber) errors.push("{{ trans('lang.restaurant_phone_error') }}");
            if (!address) errors.push("{{ trans('lang.restaurant_address_error') }}");
            if (!zoneId) errors.push("{{ trans('lang.select_zone_help') }}");
            if (!Number.isFinite(latitude)) errors.push("{{ trans('lang.restaurant_lattitude_error') }}");
            else if (latitude < -90 || latitude > 90) errors.push("{{ trans('lang.restaurant_lattitude_limit_error') }}");
            if (!Number.isFinite(longitude)) errors.push("{{ trans('lang.restaurant_longitude_error') }}");
            else if (longitude < -180 || longitude > 180) errors.push("{{ trans('lang.restaurant_longitude_limit_error') }}");
            if (zoneArea.length && !checkLocationInZone(zoneArea, longitude, latitude)) errors.push("{{ trans('lang.invalid_location_zone') }}");
            if (!description) errors.push("{{ trans('lang.restaurant_description_error') }}");

            if (errors.length) { showError(errors); return; }

            jQuery('#data-table_processing').show();
            $('.edit-form-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

            try {
                const filters = {
                    'Free Wi-Fi':         $('#Free_Wi_Fi').is(':checked')          ? 'Yes' : 'No',
                    'Good for Breakfast': $('#Good_for_Breakfast').is(':checked')  ? 'Yes' : 'No',
                    'Good for Dinner':    $('#Good_for_Dinner').is(':checked')     ? 'Yes' : 'No',
                    'Good for Lunch':     $('#Good_for_Lunch').is(':checked')      ? 'Yes' : 'No',
                    'Live Music':         $('#Live_Music').is(':checked')          ? 'Yes' : 'No',
                    'Outdoor Seating':    $('#Outdoor_Seating').is(':checked')     ? 'Yes' : 'No',
                    'Takes Reservations': $('#Takes_Reservations').is(':checked')  ? 'Yes' : 'No',
                    'Vegetarian Friendly':$('#Vegetarian_Friendly').is(':checked') ? 'Yes' : 'No',
                };
                const adminCommission = {
                    commissionType: $('#commission_type').val(),
                    fix_commission: parseInt($('.commission_fix').val() || 0, 10),
                    isEnabled: true
                };
                const DeliveryCharge = {
                    delivery_charges_per_km:              $('#delivery_charges_per_km').val(),
                    minimum_delivery_charges:             $('#minimum_delivery_charges').val(),
                    minimum_delivery_charges_within_km:   $('#minimum_delivery_charges_within_km').val()
                };

                const galleryUrls = await storeGalleryImageData();
                const menuUrls    = await storeMenuImageData();
                const storyData   = await storeStoryImageData();

                const payload = {
                    title:              restaurantname,
                    description,
                    latitude,
                    longitude,
                    location:           address,
                    categoryID:         categoryIDs,
                    categoryTitle:      categoryTitles,
                    phonenumber,
                    countryCode:        countryCode ? '+' + countryCode : '',
                    zoneId,
                    filters,
                    isOpen,
                    enabledDiveInFuture: enabledDiveIn,
                    openDineTime:       openDineTimeVal || null,
                    closeDineTime:      closeDineTimeVal || null,
                    restaurantCost,
                    DeliveryCharge,
                    specialDiscount:        buildSpecialDiscountPayload(),
                    specialDiscountEnable,
                    workingHours:           buildWorkingHoursPayload(),
                    adminCommission,
                    photo:    galleryUrls.length ? galleryUrls[0] : null,
                    photos:   galleryUrls,
                    restaurantMenuPhotos: menuUrls,
                    vType:    'mart',
                    storyData: {
                        thumbnail: storyData.storyThumbnailImage || '',
                        videos:    story_vedios || []
                    }
                };

                await $.ajax({
                    url:         routes.update,
                    method:      'POST',
                    contentType: 'application/json; charset=utf-8',
                    data:        JSON.stringify(payload)
                });

                window.location.href = "{{ route('marts') }}";

            } catch (error) {
                console.error('Failed to update mart', error);
                showError(error?.responseJSON?.message || error.message || 'Failed to update mart.');
            } finally {
                jQuery('#data-table_processing').hide();
                $('.edit-form-btn').prop('disabled', false).html('<i class="fa fa-save"></i> {{trans("lang.save")}}');
            }
        }

        // ── Build payloads ────────────────────────────────────────────────
        function buildSpecialDiscountPayload() {
            return dayOrder.map(day => ({
                day,
                timeslot: (getSpecialDiscountArray(day) || []).filter(s => s.from && s.to).map(s => ({
                    from: s.from, to: s.to, discount: s.discount ?? 0,
                    type: s.type || 'percentage', discount_type: s.discount_type || 'delivery'
                }))
            }));
        }

        function buildWorkingHoursPayload() {
            return dayOrder.map(day => ({
                day,
                timeslot: (getWorkingHoursArray(day) || []).filter(s => s.from && s.to).map(s => ({ from: s.from, to: s.to }))
            }));
        }

        // ── Image upload helpers ──────────────────────────────────────────
        async function storeGalleryImageData() {
            let final = restaurnt_photos.slice();
            for (let i = 0; i < new_added_restaurant_photos.length; i++) {
                const url = await uploadBase64Image(new_added_restaurant_photos[i], 'mart_gallery', new_added_restaurant_photos_filename[i]);
                final.push(url);
            }
            restaurnt_photos = final.slice();
            new_added_restaurant_photos = []; new_added_restaurant_photos_filename = [];
            return final;
        }

        async function storeMenuImageData() {
            let final = restaurant_menu_photos.slice();
            for (let i = 0; i < new_added_restaurant_menu.length; i++) {
                const url = await uploadBase64Image(new_added_restaurant_menu[i], 'mart_menu', new_added_restaurant_menu_filename[i]);
                final.push(url);
            }
            restaurant_menu_photos = final.slice();
            new_added_restaurant_menu = []; new_added_restaurant_menu_filename = [];
            return final;
        }

        async function storeStoryImageData() {
            const newPhoto = [];
            try {
                if (story_thumbnail && story_thumbnail.startsWith('data:')) {
                    const res = await $.ajax({ url: routes.uploadImage, method: 'POST', data: {
                            image: story_thumbnail, folder: 'marts/story',
                            filename: story_thumbnail_filename || 'story_thumbnail_' + Date.now() + '.jpg'
                        }});
                    newPhoto['storyThumbnailImage'] = res.url;
                } else {
                    newPhoto['storyThumbnailImage'] = story_thumbnail || '';
                }
            } catch(e) {
                newPhoto['storyThumbnailImage'] = story_thumbnail || '';
            }
            return newPhoto;
        }

        function uploadBase64Image(base64, folder, filename) {
            return $.ajax({ url: routes.uploadImage, method: 'POST', dataType: 'json',
                data: { image: base64, folder: folder || 'uploads', filename: filename || 'image_' + Date.now() + '.jpg' }
            }).then(r => {
                if (!r || !r.success || !r.url) throw new Error(r && r.message ? r.message : 'Upload failed');
                return r.url;
            });
        }

        // ── File select handlers ──────────────────────────────────────────
        function handleFileSelect(evt, type) {
            const file = evt.target.files[0];
            if (!file) return;
            new Compressor(file, {
                quality: {{ env('IMAGE_COMPRESSOR_QUALITY', 0.8) }},
                success(result) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const payload  = e.target.result;
                        const orig     = result.name || 'image.jpg';
                        const ext      = orig.split('.').pop();
                        const filename = orig.replace(/\.[^/.]+$/, '') + '_' + Date.now() + '.' + ext;
                        if (type === 'photos') {
                            photocount++;
                            new_added_restaurant_photos.push(payload);
                            new_added_restaurant_photos_filename.push(filename);
                            const html = `<span class="image-item" id="photo_${photocount}">
                            <span class="remove-btn" data-id="${photocount}" data-img="${payload}" data-status="new"><i class="fa fa-remove"></i></span>
                            <img width="100px" height="auto" src="${payload}" onerror="this.src='${placeholderImage}'">
                        </span>`;
                            if ($('#photos p').length) $('#photos').html(html); else $('#photos').append(html);
                        } else if (type === 'menu') {
                            menuPhotoCount++;
                            new_added_restaurant_menu.push(payload);
                            new_added_restaurant_menu_filename.push(filename);
                            const html = `<span class="image-item" id="photo_menu_${menuPhotoCount}">
                            <span class="remove-menu-btn" data-id="${menuPhotoCount}" data-img="${payload}" data-status="new"><i class="fa fa-remove"></i></span>
                            <img width="100px" height="auto" src="${payload}" onerror="this.src='${placeholderImage}'">
                        </span>`;
                            if ($('#photos_menu_card p').length) $('#photos_menu_card').html(html); else $('#photos_menu_card').append(html);
                        }
                    };
                    reader.readAsDataURL(result);
                },
                error(err) { showError('Unable to process image: ' + err.message); }
            });
            if (evt.target) evt.target.value = '';
        }

        async function handleStoryFileSelect(evt) {
            const f = evt.target.files[0];
            if (!f) return;
            const allowedExtensions = /(\.mp4)$/i;
            if (!allowedExtensions.exec(document.getElementById('video_file').value)) {
                showError('Error: Invalid video type. Only MP4 files are allowed.');
                evt.target.value = ''; return;
            }
            const video = document.createElement('video');
            video.preload = 'metadata';
            video.onloadedmetadata = async function() {
                window.URL.revokeObjectURL(video.src);
                if (storevideoDuration > 0 && video.duration > storevideoDuration) {
                    showError('Error: Story video duration maximum allowed is ' + storevideoDuration + ' seconds');
                    evt.target.value = ''; return;
                }
                const reader = new FileReader();
                reader.onload = async function(e) {
                    const payload  = e.target.result;
                    const ext      = f.name.split('.').pop();
                    const filename = f.name.replace(/\.[^/.]+$/, '') + '_' + Date.now() + '.' + ext;
                    try {
                        jQuery('#uploding_story_video').text('Video is uploading...');
                        const res = await $.ajax({ url: routes.uploadImage, method: 'POST',
                            data: { image: payload, folder: 'marts/story', filename }
                        });
                        jQuery('#uploding_story_video').text('Upload completed');
                        setTimeout(() => jQuery('#uploding_story_video').empty(), 3000);
                        const idx = $('#story_vedios').children().length;
                        $('#story_vedios').append(`
                        <div class="col-md-3" id="story_div_${idx}">
                            <div class="video-inner">
                                <video width="320" height="240" controls autoplay muted><source src="${res.url}" type="video/mp4"></video>
                                <span class="remove-story-video" data-id="${idx}" data-img="${res.url}"><i class="fa fa-remove"></i></span>
                            </div>
                        </div>`);
                        story_vedios.push(res.url);
                        $('#video_file').val('');
                    } catch(err) {
                        jQuery('#uploding_story_video').text('Upload failed');
                        showError('Error uploading video: ' + (err.responseJSON?.message || err.message));
                    }
                };
                reader.readAsDataURL(f);
            };
            video.src = URL.createObjectURL(f);
        }

        function handleStoryThumbnailFileSelect(evt) {
            const f = evt.target.files[0];
            if (!f) return;
            const allowed = /(\.jpg|\.jpeg|\.png|\.gif)$/i;
            if (!allowed.exec(document.getElementById('file').value)) {
                showError('Error: Invalid file type'); evt.target.value = ''; return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                const payload  = e.target.result;
                const ext      = f.name.split('.').pop();
                const filename = f.name.replace(/\.[^/.]+$/, '') + '_' + Date.now() + '.' + ext;
                story_thumbnail          = payload;
                story_thumbnail_filename = filename;
                $('#story_thumbnail').html(`<div class="col-md-3"><div class="thumbnail-inner">
                <span class="remove-story-thumbnail" data-img="${story_thumbnail}"><i class="fa fa-remove"></i></span>
                <img src="${story_thumbnail}" width="150px" height="150px">
            </div></div>`);
            };
            reader.readAsDataURL(f);
        }

        // ── Slot helpers ──────────────────────────────────────────────────
        function getSpecialDiscountArray(day) {
            switch(day){ case'Sunday':return timeslotSunday; case'Monday':return timeslotMonday;
                case'Tuesday':return timeslotTuesday; case'Wednesday':return timeslotWednesday;
                case'Thursday':return timeslotThursday; case'Friday':return timeslotFriday;
                case'Saturday':return timeslotSaturday; default:return null; }
        }
        function getWorkingHoursArray(day) {
            switch(day){ case'Sunday':return timeslotworkSunday; case'Monday':return timeslotworkMonday;
                case'Tuesday':return timeslotworkTuesday; case'Wednesday':return timeslotworkWednesday;
                case'Thursday':return timeslotworkThursday; case'Friday':return timeslotworkFriday;
                case'Saturday':return timeslotworkSaturday; default:return null; }
        }
        function anySpecialDiscountSlots() {
            return [timeslotSunday,timeslotMonday,timeslotTuesday,timeslotWednesday,timeslotThursday,timeslotFriday,timeslotSaturday].some(a=>a.length>0);
        }
        function anyWorkingHourSlots() {
            return [timeslotworkSunday,timeslotworkMonday,timeslotworkTuesday,timeslotworkWednesday,timeslotworkThursday,timeslotworkFriday,timeslotworkSaturday].some(a=>a.length>0);
        }
        function addSpecialDiscountSlot(day) {
            const t = getSpecialDiscountArray(day); if (!t) return;
            t.push({ from:'09:30', to:'22:00', discount:10, type:'percentage', discount_type:'delivery' });
            renderSpecialDiscountTables();
        }
        function removeSpecialDiscountSlot(day, index) {
            const t = getSpecialDiscountArray(day);
            if (t && t[index] !== undefined) { t.splice(index,1); renderSpecialDiscountTables(); }
        }
        function updateSpecialDiscountSlot(day, index, field, value) {
            const t = getSpecialDiscountArray(day); if (t && t[index]) t[index][field] = value;
        }
        function addWorkingHourSlot(day) {
            const t = getWorkingHoursArray(day); if (!t) return;
            t.push({ from:'09:30', to:'22:00' }); renderWorkingHoursTables();
        }
        function removeWorkingHourSlot(day, index) {
            const t = getWorkingHoursArray(day);
            if (t && t[index] !== undefined) { t.splice(index,1); renderWorkingHoursTables(); }
        }
        function updateWorkingHourSlot(day, index, field, value) {
            const t = getWorkingHoursArray(day); if (t && t[index]) t[index][field] = value;
        }

        // ── Misc helpers ─────────────────────────────────────────────────
        function shortEditNumber(n) { return n ? n.replace(/[^0-9+]/g,'') : ''; }

        function convertTo24Hour(t) {
            if (!t) return '';
            if (/^\d{2}:\d{2}$/.test(t)) return t;
            return moment(t, ['h:mm A','hh:mm A']).format('HH:mm');
        }

        function updateSelectedCategoryTags() {
            const selected = $('#restaurant_cuisines option:selected').map(function(){
                return $(this).val() ? { value: $(this).val(), text: $(this).text() } : null;
            }).get().filter(Boolean);
            $('#selected_categories').html(selected.map(item =>
                `<span class="selected-category-tag" data-value="${item.value}">${item.text}<span class="remove-tag">&times;</span></span>`
            ).join(''));
        }

        function checkLocationInZone(area, lng, lat) {
            if (!Array.isArray(area) || !area.length) return true;
            let inside = false;
            for (let i=0, j=area.length-1; i<area.length; j=i++) {
                const xi=parseFloat(area[i].longitude), yi=parseFloat(area[i].latitude);
                const xj=parseFloat(area[j].longitude), yj=parseFloat(area[j].latitude);
                if (((yi>lat)!==(yj>lat)) && (lng<(xj-xi)*(lat-yi)/((yj-yi)||0.0000001)+xi)) inside=!inside;
            }
            return inside;
        }

        function formatState(state) {
            if (!state.id) return state.text;
            const code = newcountriesjs[state.id];
            if (!code) return state.text;
            const base = "{{ URL::to('/') }}/scss/icons/flag-icon-css/flags";
            return $(`<span><img src="${base}/${code.toLowerCase()}.svg" class="img-flag"/> ${state.text}</span>`);
        }
        function formatStateSelection(state) {
            if (!state.id) return state.text;
            const code = newcountriesjs[state.id];
            if (!code) return state.text;
            const base = "{{ URL::to('/') }}/scss/icons/flag-icon-css/flags";
            return $(`<span><img class="img-flag" src="${base}/${code.toLowerCase()}.svg"/> ${state.text}</span>`);
        }

        function fetchJson(url) { return $.ajax({ url, method:'GET', dataType:'json' }); }

        function showError(msgs) {
            const list = Array.isArray(msgs) ? msgs : [msgs];
            const $e = $('.error_top');
            $e.html('');
            list.filter(Boolean).forEach(m => $e.append(`<p>${m}</p>`));
            if (list.length) { $e.show(); window.scrollTo(0,0); }
        }
        function clearError() { $('.error_top').hide().html(''); }

        function chkAlphabets2(event, msg) {
            if (!(event.which>=48 && event.which<=57)) {
                document.getElementById(msg).innerHTML = "Accept only Number"; return false;
            }
            document.getElementById(msg).innerHTML = ""; return true;
        }

        // Global aliases for any remaining inline onclick handlers
        window.addMoreButton       = day => addSpecialDiscountSlot(day);
        window.deleteOffer         = (day, index) => removeSpecialDiscountSlot(day, index);
        window.addMorehour         = day => addWorkingHourSlot(day);
        window.deleteWorkingHour   = (day, index) => removeWorkingHourSlot(day, index);
        window.addMoreFunctionButton  = () => {};
        window.updateMoreFunctionButton = () => {};
        window.addMoreFunctionhour    = day => addWorkingHourSlot(day);
        window.updatehoursFunctionButton = () => {};
        window.handleFileSelectMenuCard  = evt => handleFileSelect(evt, 'menu');
        window.handleStoryFileSelect          = handleStoryFileSelect;
        window.handleStoryThumbnailFileSelect = handleStoryThumbnailFileSelect;
    </script>
@endsection
