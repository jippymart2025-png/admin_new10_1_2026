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
            <h3 class="text-themecolor">{{trans('lang.user_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('users') !!}">{{trans('lang.user_plural')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.user_edit')}}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="resttab-sec mb-4">
            <div class="row justify-content-center">
                  <div class="col-md-4">
                        <a href="{{route('orders','userId='.$id)}}">
                          <div class="card card-box-with-icon bg--1">
                              <div class="card-body d-flex justify-content-between align-items-center">
                              <div class="card-box-with-content">
                                  <h4 class="text-dark-2 mb-1 h4 total_orders" id="total_orders">0</h4>
                                  <p class="mb-0 small text-dark-2">{{trans('lang.dashboard_total_orders')}}</p>
                              </div>
                                  <span class="box-icon ab"><img src="https://staging.foodie.siswebapp.com/images/active_restaurant.png"></span>
                              </div>
                          </div>
                      </a>
                  </div>
                  <div class="col-md-4">
                    <a href="{{route('users.walletstransaction',$id)}}">
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

        <div>
            <div class="card-body">
                <div class="error_top"></div>
                <div class="row restaurant_payout_create">
                    <div class="restaurant_payout_create-inner">
                        <fieldset>
                            <legend>{{trans('lang.user_edit')}}</legend>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.first_name')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control user_first_name" onkeypress="return chkAlphabets(event,'error')">
                                    <div id="error" class="err"></div>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.user_first_name_help") }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.last_name')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control user_last_name" onkeypress="return chkAlphabets(event,'error1')">
                                    <div id="error1" class="err"></div>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.user_last_name_help") }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.email')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control user_email" disabled>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.user_email_help") }}
                                    </div>
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
                                            <input type="text" class="form-control user_phone"  disabled onkeypress="return chkAlphabets2(event,'error2')">
                                            <div id="error2" class="err"></div>
                                        </div>
                                </div>
                                <div class="form-text text-muted w-50">
                                    {{ trans("lang.user_phone_help") }}
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{trans('lang.restaurant_image')}}</label>
                                <input type="file" onChange="handleFileSelect(event)" class="col-7">
                                <div class="placeholder_img_thumb user_image"></div>
                                <div id="uploding_image"></div>
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend>{{trans('user')}} {{trans('lang.active_deactive')}}</legend>
                            <div class="form-group row width-100">
                                <div class="form-check">
                                    <input type="checkbox" class="user_active" id="user_active">
                                    <label class="col-3 control-label" for="user_active">{{trans('lang.active')}}</label>
                                </div>
                                    <div class="form-check provider_type">
                                        <input type="checkbox" id="reset_password">
                                        <label class="col-3 control-label" for="reset_password">{{trans('lang.reset_password')}}</label>
                                        <div class="form-text text-muted w-100">
                                            {{ trans("lang.note_reset_password_email") }}
                                        </div>
                                    </div>
                                    <div class="form-button provider_type" style="margin-top: 16px;margin-left: 20px;">
                                        <button type="button" class="btn btn-primary" id="send_mail">{{trans('lang.send_mail')}}</button>
                                    </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>
            <div class="form-group col-12 text-center btm-btn">
                <button type="button" class="btn btn-primary  edit-form-btn"><i class="fa fa-save"></i> {{ trans('lang.save')}}</button>
                <a href="{!! route('users') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel')}}</a>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    var id = "<?php echo $id; ?>";
    var apiBase = '{{ url('/api') }}';
    var currentCurrency = '';
    var provider  = '';
    var currencyAtRight = false;
    var decimal_degits = 0;
    var photo = "";
    var fileName = "";
    var userImageFile = '';
    var userData = null;
    var placeholderImage = '{{ asset('images/placeholder.png') }}';

    // Use default currency settings (can be loaded from settings if needed)
    currentCurrency = '$';
    currencyAtRight = false;
    decimal_degits = 2;
    $("#send_mail").click(function() {
        if ($("#reset_password").is(":checked")) {
            var email = $(".user_email").val();
            // For SQL-based system, we'll need to implement password reset via Laravel
            $.ajax({
                url: '{{ route("password.email") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    email: email
                },
                success: function(response) {
                    alert('{{trans("lang.mail_sent")}}');
                },
                error: function(xhr, status, error) {
                    console.log('Error password reset: ', error);
                    alert('Error sending password reset email');
                }
            });
        } else {
            alert('{{trans("lang.mail_send_error")}}');
        }
    });
    $(document).ready(function() {
        jQuery("#data-table_processing").show();
        jQuery("#country_selector").select2({
            templateResult: formatState,
            templateSelection: formatState2,
            placeholder: "Select Country",
            allowClear: true
        });

        // Load user data from SQL API
        $.ajax({
            url: apiBase + '/app-users/' + id,
            method: 'GET',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(response) {
                console.log('User data loaded:', response);
                if (response.status && response.data) {
                    var user = response.data;
                    userData = user;
                    provider = user.provider || 'email';

                    // Show/hide provider-specific fields
                    if (!user.provider || user.provider == "email") {
                        $(".provider_type").show();
                    } else {
                        $(".provider_type").hide();
                    }

                    // Populate form fields
                    $(".user_first_name").val(user.firstName || '');
                    $(".user_last_name").val(user.lastName || '');

                    if(user.email != "" && user.email != null){
                        $(".user_email").val(user.email);
                    } else {
                        $(".user_email").val("");
                    }

                    // Set country code
                    if (user.countryCode) {
                        var countryCodeValue = user.countryCode.replace('+', '');
                        $("#country_selector").val(countryCodeValue).trigger('change');
                    }

                    // Set phone number
                    if(user.phoneNumber != "" && user.phoneNumber != null){
                        $(".user_phone").val(user.phoneNumber);
                    } else {
                        $(".user_phone").val("");
                    }

                    // Set profile picture
                    if (user.profilePictureURL != '' && user.profilePictureURL != null) {
                        photo = user.profilePictureURL;
                        userImageFile = user.profilePictureURL;
                        $(".user_image").append('<img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" class="rounded" style="width:50px" src="' + photo + '" alt="image">');
                    } else {
                        $(".user_image").append('<img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image">');
                    }

                    // Set active checkbox
                    if (user.active || user.isActive) {
                        $(".user_active").prop('checked', true);
                    }

                    // Display wallet amount
                    var walletAmount = parseFloat(user.wallet_amount || 0);
                    if (isNaN(walletAmount)) {
                        walletAmount = 0;
                    }
                    var wallet_amount_display;
                    if (currencyAtRight) {
                        wallet_amount_display = walletAmount.toFixed(decimal_degits) + currentCurrency;
                    } else {
                        wallet_amount_display = currentCurrency + walletAmount.toFixed(decimal_degits);
                    }
                    $("#wallet_amount").text(wallet_amount_display);

                    // Display total orders
                    $("#total_orders").text(user.totalOrders || 0);

                    jQuery("#data-table_processing").hide();
                } else {
                    jQuery("#data-table_processing").hide();
                    alert('User not found');
                    window.location.href = '{{ route("users") }}';
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading user:', error);
                jQuery("#data-table_processing").hide();
                alert('Error loading user data');
                window.location.href = '{{ route("users") }}';
            }
        });
        $(".edit-form-btn").click(function() {
            var userFirstName = $(".user_first_name").val();
            var userLastName = $(".user_last_name").val();
            var email = $(".user_email").val();
            var countryCode = '+' + jQuery("#country_selector").val();
            var userPhone = $(".user_phone").val();
            var active = $(".user_active").is(":checked");
            var user_name = userFirstName + " " + userLastName;

            if (userFirstName == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.user_firstname_error')}}</p>");
                window.scrollTo(0, 0);
            } else if (userLastName == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.user_lastname_error')}}</p>");
                window.scrollTo(0, 0);
            } else if (userPhone == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.user_phone_error')}}</p>");
                window.scrollTo(0, 0);
            } else {
                jQuery("#data-table_processing").show();

                // Prepare data for API
                var updateData = {
                    firstName: userFirstName,
                    lastName: userLastName,
                    countryCode: countryCode,
                    phoneNumber: userPhone,
                    active: active
                };

                // Add photo if changed
                if (photo && photo !== userImageFile) {
                    updateData.photo = photo;
                    updateData.fileName = fileName;
                }

                // Update user via SQL API
                $.ajax({
                    url: apiBase + '/app-users/' + id,
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    data: JSON.stringify(updateData),
                    success: function(response) {
                        console.log('✅ User updated successfully:', response);

                        // Log activity
                        if (typeof logActivity === 'function') {
                            logActivity('users', 'updated', 'Updated user: ' + userFirstName + ' ' + userLastName)
                                .then(() => {
                                    console.log('✅ Activity logged successfully');
                                })
                                .catch(err => {
                                    console.error('❌ Error logging activity:', err);
                                })
                                .finally(() => {
                                    jQuery("#data-table_processing").hide();
                                    window.location.href = '{{ route("users")}}';
                                });
                        } else {
                            jQuery("#data-table_processing").hide();
                            window.location.href = '{{ route("users")}}';
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ Error updating user:', error);
                        jQuery("#data-table_processing").hide();
                        $(".error_top").show();
                        $(".error_top").html("");

                        var errorMessage = 'Error updating user';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        $(".error_top").append("<p>" + errorMessage + "</p>");
                        window.scrollTo(0, 0);
                    }
                });
            }
        });
    });
    function formatState(state) {
            if (!state.id) {
                return state.text;
            }
            var baseUrl = "<?php echo URL::to('/');?>/scss/icons/flag-icon-css/flags";
            var $state = $(
                '<span><img src="' + baseUrl + '/' + newcountriesjs[state.element.value].toLowerCase() + '.svg" class="img-flag" /> ' + state.text + '</span>'
            );
            return $state;
        }
        function formatState2(state) {
            if (!state.id) {
                return state.text;
            }
            var baseUrl = "<?php echo URL::to('/');?>/scss/icons/flag-icon-css/flags"
            var $state = $(
                '<span><img class="img-flag" /> <span></span></span>'
            );
            $state.find("span").text(state.text);
            $state.find("img").attr("src", baseUrl + "/" + newcountriesjs[state.element.value].toLowerCase() + ".svg");
            return $state;
        }
        var newcountriesjs = '<?php echo json_encode($newcountriesjs); ?>';
        var newcountriesjs = JSON.parse(newcountriesjs);
    function handleFileSelect(evt) {
        var f = evt.target.files[0];
        var reader = new FileReader();
        reader.onload = (function(theFile) {
            return function(e) {
                var filePayload = e.target.result;
                var val = f.name;
                var ext = val.split('.')[1];
                var docName = val.split('fakepath')[1];
                var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                var timestamp = Number(new Date());
                var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                photo = filePayload;
                fileName = filename;
                $(".user_image").empty();
                $(".user_image").append('<img class="rounded" style="width:50px" src="' + photo + '" alt="image">');
            };
        })(f);
        reader.readAsDataURL(f);
    }
    function chkAlphabets(event, msg) {
        if (!(event.which >= 97 && event.which <= 122) && !(event.which >= 65 && event.which <= 90)) {
            document.getElementById(msg).innerHTML = "Accept only Alphabets";
            return false;
        } else {
            document.getElementById(msg).innerHTML = "";
            return true;
        }
    }
    function chkAlphabets2(event, msg) {
        if (!(event.which >= 48 && event.which <= 57)) {
            document.getElementById(msg).innerHTML = "Accept only Number";
            return false;
        } else {
            document.getElementById(msg).innerHTML = "";
            return true;
        }
    }
    function chkAlphabets3(event, msg) {
        if (!((event.which >= 48 && event.which <= 57) || (event.which >= 97 && event.which <= 122))) {
            document.getElementById(msg).innerHTML = "Special characters not accepted ";
            return false;
        } else {
            document.getElementById(msg).innerHTML = "";
            return true;
        }
    }
</script>
@endsection
