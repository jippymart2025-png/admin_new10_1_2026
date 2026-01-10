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
                <h3 class="text-themecolor">{{trans('lang.driver_plural')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('drivers') !!}">{{trans('lang.driver_plural')}}</a></li>
                    <li class="breadcrumb-item active">{{trans('lang.driver_edit')}}</li>
                </ol>
            </div>

        </div>


        <div class="container-fluid">

            <div class="resttab-sec mb-4">
                <div class="row justify-content-center">
                    <div class="col-md-4">
                        <a href="{{route('orders')}}?driverId={{$id}}">
                            <div class="card card-box-with-icon bg--1">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div class="card-box-with-content">
                                        <h4 class="text-dark-2 mb-1 h4 rest_count" id="total_orders">1</h4>
                                        <p class="mb-0 small text-dark-2">{{trans('lang.dashboard_total_orders')}}</p>
                                    </div>
                                    <span class="box-icon ab"><img src="https://staging.foodie.siswebapp.com/images/active_restaurant.png"></span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{route('payoutRequests.drivers.view',$id)}}">
                            <div class="card card-box-with-icon bg--2">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div class="card-box-with-content">
                                        <h4 class="text-dark-2 mb-1 h4 wallet_amount" id="wallet_amount">$0.00</h4>
                                        <p class="mb-0 small text-dark-2">{{trans('lang.wallet_Balance')}}</p>
                                    </div>
                                    <span class="box-icon ab"><img src="https://staging.foodie.siswebapp.com/images/total_earning.png"></span>
                                </div>
                            </div>
                        </a>
                    </div>


                </div>
            </div>



            <div class="card-body">
                <div class="error_top"></div>
                <div class="row restaurant_payout_create">
                    <div class="restaurant_payout_create-inner">
                        <fieldset>
                            <legend>{{trans('lang.driver_details')}}</legend>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.first_name')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control user_first_name">
                                    <div class="form-text text-muted">{{trans('lang.first_name_help')}}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.last_name')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control user_last_name">
                                    <div class="form-text text-muted">{{trans('lang.last_name_help')}}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.email')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control user_email" disabled>
                                    <div class="form-text text-muted">{{trans('lang.user_email_help')}}</div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-3 control-label">{{trans('lang.user_phone')}}</label>
                                <div class="col-md-6">
                                    <div class="phone-box position-relative" id="phone-box">
                                        <select name="country" id="country_selector">
                                            <?php foreach ($newcountries as $keycy => $valuecy) { ?>
                                                <?php $selected = ""; ?>
                                            <option <?php echo $selected; ?> code="<?php echo $valuecy->code; ?>"
                                                    value="<?php echo $keycy; ?>">
                                                +<?php echo $valuecy->phoneCode; ?> {{$valuecy->countryName}}</option>
                                            <?php } ?>
                                        </select>
                                        <input type="text" class="form-control user_phone" disabled onkeypress="return chkAlphabets2(event,'error2')">
                                        <div id="error2" class="err"></div>
                                    </div>
                                </div>
                                <div class="form-text text-muted">{{trans('lang.user_phone_help')}}</div>
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
                            <div class="form-group row width-100">
                                <div class="col-12">
                                    <h6>{{ trans("lang.know_your_cordinates") }}<a target="_blank" href="https://www.latlong.net/">{{
                                            trans("lang.latitude_and_longitude_finder") }}</a></h6>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.user_latitude')}}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control user_latitude">
                                    <div class="form-text text-muted">{{trans('lang.user_latitude_help')}}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.user_longitude')}}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control user_longitude">
                                    <div class="form-text text-muted">{{trans('lang.user_longitude_help')}}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.profile_image')}}</label>
                                <div class="col-7">
                                    <input type="file" onChange="handleFileSelect(event)" class="">
                                    <div class="form-text text-muted">{{trans('lang.profile_image_help')}}</div>
                                </div>
                                <div class="placeholder_img_thumb user_image">
                                </div>
                                <div id="uploding_image"></div>
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend>{{trans('driver')}} {{trans('lang.active_deactive')}}</legend>
                            <div class="form-group row width-100">
                                <div class="form-check">
                                    <input type="checkbox" id="is_active">
                                    <label class="col-3 control-label" for="is_active">{{trans('lang.active')}}</label>
                                </div>
                                <div class="form-check provider_type">
                                    <input type="checkbox" id="reset_password">
                                    <label class="col-3 control-label" for="reset_password">{{trans('lang.reset_driver_password')}}</label>
                                    <div class="form-text text-muted w-100">
                                        {{ trans("lang.note_reset_driver_password_email") }}
                                    </div>
                                </div>
                                <div class="form-button provider_type" style="margin-top: 16px;margin-left: 20px;">
                                    <button type="button" class="btn btn-primary" id="send_mail">{{trans('lang.send_mail')}}
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend>{{trans('lang.bankdetails')}}</legend>
                            <div class="form-group row">
                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{
                                        trans('lang.bank_name')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="bank_name" class="form-control" id="bankName">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{
                                        trans('lang.branch_name')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="branch_name" class="form-control" id="branchName">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{
                                        trans('lang.holer_name')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="holer_name" class="form-control" id="holderName">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{
                                        trans('lang.account_number')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="account_number" class="form-control" id="accountNumber">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{
                                        trans('lang.other_information')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="other_information" class="form-control" id="otherDetails">
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>
            <div class="form-group col-12 text-center btm-btn">
                <button type="button" class="btn btn-primary edit-form-btn"><i class="fa fa-save"></i> {{
                    trans('lang.save')}}
                </button>
                <a href="{!! route('drivers') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{
                    trans('lang.cancel')}}</a>
            </div>
        </div>




    </div>
@endsection
@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.26.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/compressorjs/1.1.1/compressor.min.js" integrity="sha512-VaRptAfSxXFAv+vx33XixtIVT9A/9unb1Q8fp63y1ljF+Sbka+eMJWoDAArdm7jOYuLQHVx5v60TQ+t3EA8weA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        const driverId = "{{ $id }}";
        const placeholderImage = '{{ asset('images/placeholder.png') }}';
        const routes = {
            driver: "{{ route('drivers.getById', ['id' => $id]) }}",
            stats: "{{ route('drivers.stats', ['id' => $id]) }}",
            zones: "{{ route('drivers.zones') }}",
            update: "{{ route('drivers.update', ['id' => $id]) }}",
            currency: "{{ route('api.currencies.active') }}",
            uploadImage: "{{ route('api.upload.image') }}",
        };

        const newcountriesjs = @json($newcountriesjs);
        let currentCurrency = '';
        let currencyAtRight = false;
        let decimalDigits = 0;
        let profileImageBase64 = null;
        let profileImageFilename = null;
        let existingProfileImageUrl = '';
        let provider = 'email';

        $(document).ready(() => {
            window.csrfToken = $('meta[name="csrf-token"]').attr('content');
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': window.csrfToken } });

            initCountrySelect();
            bindEvents();
            loadInitialData();
        });

        function initCountrySelect() {
            $('#country_selector').select2({
                templateResult: formatState,
                templateSelection: formatStateSelection,
                placeholder: 'Select Country',
                allowClear: true
            });
        }

        function bindEvents() {
            $('#zone').empty().append('<option value="">{{ trans('lang.select_zone') }}</option>');

            $(document).on('change', 'input[type="file"]', function (event) {
                if (this.id === 'country_selector') return;
                handleProfileFileSelect(event);
                $(this).val('');
            });

            $('.edit-form-btn').on('click', async (event) => {
                event.preventDefault();
                await saveDriver();
            });

            $('#send_mail').on('click', function () {
                alert('{{ trans('lang.error_reset_driver_password') }}');
            });
        }

        async function loadInitialData() {
            jQuery('#data-table_processing').show();
            clearError();

            try {
                const [currencyRes, zonesRes] = await Promise.all([
                    fetchJson(routes.currency),
                    fetchJson(routes.zones)
                ]);

                if (currencyRes && currencyRes.success && currencyRes.data) {
                    currentCurrency = currencyRes.data.symbol || '';
                    currencyAtRight = !!currencyRes.data.symbolAtRight;
                    decimalDigits = currencyRes.data.decimal_degits || 0;
                }

                if (zonesRes && zonesRes.success && Array.isArray(zonesRes.data)) {
                    populateZones(zonesRes.data);
                }

                const driverRes = await fetchJson(routes.driver);
                if (!driverRes || !driverRes.success || !driverRes.data) {
                    throw new Error(driverRes && driverRes.message ? driverRes.message : 'Unable to load driver details.');
                }

                populateDriverForm(driverRes.data);

                try {
                    const statsRes = await fetchJson(routes.stats);
                    if (statsRes && statsRes.success) {
                        updateStats(statsRes);
                    }
                } catch (statsError) {
                    console.warn('Unable to load driver stats', statsError);
                }
            } catch (error) {
                console.error('Driver edit init error:', error);
                showError(error.message || 'Failed to load driver information.');
            } finally {
                jQuery('#data-table_processing').hide();
            }
        }

        function populateZones(zones) {
            const $zoneSelect = $('#zone');
            zones.forEach(zone => {
                $zoneSelect.append($('<option></option>').attr('value', zone.id).text(zone.name));
            });
        }

        function populateDriverForm(driver) {
            $('.user_first_name').val(driver.firstName || '');
            $('.user_last_name').val(driver.lastName || '');
            $('.user_email').val(driver.email || '');

            if (driver.countryCode) {
                const code = (driver.countryCode || '').replace('+', '');
                $('#country_selector').val(code).trigger('change');
            }

            $('.user_phone').val(cleanPhone(driver.phoneNumber || ''));

            if (driver.location) {
                const lat = parseFloat(driver.location.latitude || driver.location.lat || 0);
                const lng = parseFloat(driver.location.longitude || driver.location.lng || 0);
                $('.user_latitude').val(Number.isFinite(lat) ? lat : '');
                $('.user_longitude').val(Number.isFinite(lng) ? lng : '');
            }

            if (driver.zoneId) {
                $('#zone').val(driver.zoneId).trigger('change');
            }

            $('#is_active').prop('checked', !!driver.active || !!driver.isActive);

            provider = driver.provider || 'email';
            toggleProviderControls();

            existingProfileImageUrl = driver.profilePictureURL || '';
            renderProfileImage(existingProfileImageUrl || placeholderImage);

            if (driver.userBankDetails) {
                const details = driver.userBankDetails;
                $('#bankName').val(details.bankName || '');
                $('#branchName').val(details.branchName || '');
                $('#holderName').val(details.holderName || '');
                $('#accountNumber').val(details.accountNumber || '');
                $('#otherDetails').val(details.otherDetails || '');
            }
        }

        function updateStats(stats) {
            $('#total_orders').text(stats.totalOrders ?? 0);
            const wallet = parseFloat(stats.walletBalance ?? stats.wallet_amount ?? 0);
            $('#wallet_amount').text(formatCurrency(wallet));
        }

        function toggleProviderControls() {
            if (!provider || provider === 'email') {
                $('.provider_type').show();
            } else {
                $('.provider_type').hide();
            }
        }

        async function saveDriver() {
            clearError();
            const errors = [];

            const firstName = $('.user_first_name').val().trim();
            const lastName = $('.user_last_name').val().trim();
            const email = $('.user_email').val().trim();
            const countryCode = $('#country_selector').val();
            const phone = $('.user_phone').val().trim();
            const zoneId = $('#zone').val();
            const latitude = parseFloat($('.user_latitude').val());
            const longitude = parseFloat($('.user_longitude').val());
            const isActive = $('#is_active').is(':checked');

            if (!firstName) errors.push("{{ trans('lang.enter_owners_name_error') }}");
            if (!lastName) errors.push("{{ trans('lang.enter_owners_last_name_error') }}");
            if (!phone) errors.push("{{ trans('lang.enter_owners_phone') }}");
            if (!zoneId) errors.push("{{ trans('lang.select_zone_help') }}");

            if (errors.length) {
                showError(errors);
                return;
            }

            jQuery('#data-table_processing').show();

            try {
                let profileImageUrl = existingProfileImageUrl;
                if (profileImageBase64) {
                    profileImageUrl = await uploadBase64Image(profileImageBase64, 'drivers/profile', profileImageFilename);
                }

                const bankDetails = {
                    bankName: $('#bankName').val() || '',
                    branchName: $('#branchName').val() || '',
                    holderName: $('#holderName').val() || '',
                    accountNumber: $('#accountNumber').val() || '',
                    otherDetails: $('#otherDetails').val() || ''
                };

                const payload = {
                    firstName,
                    lastName,
                    email,
                    countryCode: countryCode ? '+' + countryCode : '',
                    phoneNumber: phone,
                    zoneId,
                    isActive,
                    active: isActive,
                    profilePictureURL: profileImageUrl,
                    location: {
                        latitude: Number.isFinite(latitude) ? latitude : null,
                        longitude: Number.isFinite(longitude) ? longitude : null,
                    },
                    userBankDetails: bankDetails,
                };

                await $.ajax({
                    url: routes.update,
                    method: 'PUT',
                    contentType: 'application/json; charset=utf-8',
                    data: JSON.stringify(payload)
                });

                window.location.href = "{{ route('drivers') }}";
            } catch (error) {
                console.error('Driver update failed:', error);
                const message = error?.responseJSON?.message || error.message || 'Failed to update driver.';
                showError(message);
            } finally {
                jQuery('#data-table_processing').hide();
            }
        }

        function handleProfileFileSelect(evt) {
            const file = evt.target.files[0];
            if (!file) {
                return;
            }

            new Compressor(file, {
                quality: {{ env('IMAGE_COMPRESSOR_QUALITY', 0.8) }},
                success(result) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        profileImageBase64 = e.target.result;
                        const originalName = result.name || 'profile.jpg';
                        const extension = originalName.split('.').pop();
                        profileImageFilename = `${originalName.replace(/\.[^/.]+$/, '')}_${Date.now()}.${extension}`;
                        renderProfileImage(profileImageBase64);
                    };
                    reader.readAsDataURL(result);
                },
                error(err) {
                    console.error('Profile image compression error', err);
                    showError('Unable to process profile image: ' + err.message);
                }
            });
        }

        function renderProfileImage(src) {
            $('.user_image').empty().append(`<img class="rounded" style="width:50px" src="${src}" onerror="this.onerror=null;this.src='${placeholderImage}'" alt="profile">`);
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

        function fetchJson(url) {
            return $.ajax({
                url,
                method: 'GET',
                dataType: 'json'
            });
        }

        function formatCurrency(value) {
            const amount = Number.isFinite(value) ? value : 0;
            const formatted = amount.toFixed(decimalDigits);
            return currencyAtRight ? `${formatted}${currentCurrency}` : `${currentCurrency}${formatted}`;
        }

        function cleanPhone(value) {
            return (value || '').replace(/[^0-9+]/g, '');
        }

        function showError(messages) {
            const list = Array.isArray(messages) ? messages : [messages];
            const $error = $('.error_top');
            $error.html('');
            list.filter(Boolean).forEach(msg => $error.append(`<p>${msg}</p>`));
            if (list.length) {
                $error.show();
                window.scrollTo(0, 0);
            }
        }

        function clearError() {
            $('.error_top').hide().html('');
        }

        function formatState(state) {
            if (!state.id) {
                return state.text;
            }
            const code = newcountriesjs[state.element.value];
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
            const code = newcountriesjs[state.element.value];
            if (!code) {
                return state.text;
            }
            const baseUrl = "{{ URL::to('/') }}/scss/icons/flag-icon-css/flags";
            const $state = $(`<span><img class="img-flag" src="${baseUrl}/${code.toLowerCase()}.svg" /> ${state.text}</span>`);
            return $state;
        }
    </script>
@endsection
