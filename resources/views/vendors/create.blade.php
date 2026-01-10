@extends('layouts.app')

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
            <h3 class="text-themecolor">{{trans('lang.create_vendor')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a
                        href="{!! route('restaurants') !!}">{{trans('lang.restaurant_plural')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.create_vendor')}}</li>
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
                            <legend>{{trans('lang.admin_area')}}</legend>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.first_name')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control user_first_name" required>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.user_first_name_help") }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.last_name')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control user_last_name">
                                    <div class="form-text text-muted">
                                        {{ trans("lang.user_last_name_help") }}
                                    </div>
                                </div>
                            </div>


                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.email')}}</label>
                                <div class="col-7">
                                    <input type="email" class="form-control user_email" required>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.user_email_help") }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.password')}}</label>
                                <div class="col-7">
                                    <input type="password" class="form-control user_password res_password" required>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.user_password_help") }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-3 control-label">{{trans('lang.user_phone')}}</label>
                                <div class="col-md-6">
                                    <div class="phone-box position-relative" id="phone-box">
                                        <select name="country" id="country_selector">
                                            <?php foreach ($newcountries as $keycy => $valuecy) { ?>
                                            <?php    $selected = ""; ?>
                                            <option <?php    echo $selected; ?> code="<?php    echo $valuecy->code; ?>"
                                                value="<?php    echo $keycy; ?>">
                                                +<?php    echo $valuecy->phoneCode; ?> {{$valuecy->countryName}}
                                            </option>
                                            <?php } ?>
                                        </select>


                                        <input type="text" class="form-control user_phone"
                                            onkeypress="return chkAlphabets2(event,'error2')">
                                        <div id="error2" class="err"></div>
                                    </div>
                                </div>
                                <div class="form-text text-muted w-50">
                                    {{ trans("lang.user_phone_help") }}
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.vendor_type')}}</label>
                                <div class="col-7">
                                    <select class="form-control" id="vendor_type">
                                        <option value="restaurant" selected>{{trans('lang.restaurant')}} (Default)</option>
                                        <option value="mart">{{trans('lang.mart')}}</option>
                                    </select>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.vendor_type_help") }} - Restaurant is selected by default
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{trans('lang.restaurant_image')}}</label>
                                <input type="file" onChange="handleFileSelectowner(event)" class="col-7">
                                <div id="uploding_image_owner"></div>
                                <div class="uploaded_image_owner" style="display:none;"><img id="uploaded_image_owner"
                                        src="" width="150px" height="150px;"></div>
                            </div>

                        </fieldset>
                        <fieldset>
                            <legend>{{ trans('lang.subscription_details') }}</legend>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.select_subscription_plan') }}</label>
                                <div class="col-7">
                                    <select class="form-control" id="subscription_plan">
                                        <option value="" selected> {{ trans('lang.select_subscription_plan') }}</option>
                                    </select>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend>{{trans('restaurant')}} {{trans('lang.active_deactive')}}</legend>
                            <div class="form-group row">

                                <div class="form-group row width-50">
                                    <div class="form-check width-100">
                                        <input type="checkbox" id="is_active">
                                        <label class="col-3 control-label"
                                            for="is_active">{{trans('lang.active')}}</label>
                                    </div>
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
                <a href="{!! route('vendors') !!}" class="btn btn-default"><i
                        class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
            </div>

        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/compressorjs/1.1.1/compressor.min.js"
    integrity="sha512-VaRptAfSxXFAv+vx33XixtIVT9A/9unb1Q8fp63y1ljF+Sbka+eMJWoDAArdm7jOYuLQHVx5v60TQ+t3EA8weA=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
    var photo="";
    var ownerphoto='';
    var ownerFileName='';

    // Load subscription plans from SQL
    $(document).ready(async function() {
        jQuery("#data-table_processing").show();

        // Set default vendor type to restaurant
        $("#vendor_type").val('restaurant');

        jQuery("#country_selector").select2({
            templateResult: formatState,
            templateSelection: formatState2,
            placeholder: "Select Country",
            allowClear: true
        });

        // Load subscription plans via AJAX
        $.ajax({
            url: '{{ route("vendors.subscription-plans") }}',
            method: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    response.data.forEach(function(plan) {
                        $('#subscription_plan').append($("<option></option>")
                            .attr("value", plan.id)
                            .text(plan.name));
                    });
                }
                jQuery("#data-table_processing").hide();
            },
            error: function(error) {
                console.error('Error loading subscription plans:', error);
                jQuery("#data-table_processing").hide();
            }
        });
    })

    $(".save-form-btn").click(async function() {
        $(".error_top").hide();

        var userFirstName=$(".user_first_name").val();
        var userLastName=$(".user_last_name").val();
        var email=$(".user_email").val();
        var password=$(".user_password").val();
        var country_code=$("#country_selector").val();
        var userPhone=$(".user_phone").val();
        var vendorType=$("#vendor_type").val();

        // Set default vendor type to 'restaurant' if empty or not selected
        if(!vendorType || vendorType=='' || vendorType==null || vendorType==undefined || vendorType=='') {
            vendorType='restaurant';
        }

        var restaurant_active=false;
        if($("#is_active").is(':checked')) {
            restaurant_active=true;
        }

        var subscriptionPlanId=$('#subscription_plan').val();

        // Validation
        if(userFirstName=='') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.enter_owners_name_error')}}</p>");
            window.scrollTo(0,0);
        } else if(userLastName=='') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.enter_owners_last_name_error')}}</p>");
            window.scrollTo(0,0);
        } else if(email=='') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.enter_owners_email')}}</p>");
            window.scrollTo(0,0);
        } else if(password=='') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.enter_owners_password_error')}}</p>");
            window.scrollTo(0,0);
        } else if(!country_code) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.select_country_code')}}</p>");
            window.scrollTo(0,0);
        } else if(userPhone=='') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.enter_owners_phone')}}</p>");
            window.scrollTo(0,0);
        } else {
            jQuery("#data-table_processing").show();

            // Get subscription plan data if selected
            var subscriptionData = null;
            if(subscriptionPlanId && subscriptionPlanId != '') {
                // You might want to fetch the subscription plan details via AJAX here
                // For now, we'll pass just the ID
            }

            // Upload image first if exists
            var profilePictureURL = '';
            if(ownerphoto != '') {
                profilePictureURL = await uploadImage(ownerphoto, ownerFileName);
            }

            // Create vendor via AJAX
            $.ajax({
                url: '{{ route("vendors.create.post") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    firstName: userFirstName,
                    lastName: userLastName,
                    email: email,
                    password: password,
                    countryCode: '+' + country_code,
                    phoneNumber: userPhone,
                    vType: vendorType,
                    active: restaurant_active ? 1 : 0,
                    profilePictureURL: profilePictureURL,
                    subscriptionPlanId: subscriptionPlanId
                },
                success: function(response) {
                    if(response.success) {
                        jQuery("#data-table_processing").hide();
                        window.location.href='{{ route("vendors")}}';
                    } else {
                        jQuery("#data-table_processing").hide();
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>"+response.message+"</p>");
                        window.scrollTo(0,0);
                    }
                },
                error: function(error) {
                    jQuery("#data-table_processing").hide();
                    $(".error_top").show();
                    $(".error_top").html("");
                    var errorMessage = error.responseJSON && error.responseJSON.message ? error.responseJSON.message : 'An error occurred';
                    $(".error_top").append("<p>"+errorMessage+"</p>");
                    window.scrollTo(0,0);
                }
            });
        }
    })

    async function uploadImage(imageData, fileName) {
        // In a real implementation, you would upload the image to your storage
        // For now, we'll return the data URL (base64)
        // You should implement proper image upload to your server or Firebase Storage
        return imageData;
    }

    function handleFileSelectowner(evt) {
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
                        ownerphoto=filePayload;
                        ownerFileName=filename;
                        $("#uploaded_image_owner").attr('src',ownerphoto);
                        $(".uploaded_image_owner").show();
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

</script>
@endsection

