@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">Coins Settings</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">Coins Settings</li>
                </ol>
            </div>
        </div>
        <div class="card-body">
            <div class="error_top"></div>
            <div class="row restaurant_payout_create">
                <div class="restaurant_payout_create-inner">
                    <fieldset>
                        <legend>Wallet / Coins Config</legend>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Version</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="version" placeholder="1" min="0" step="1">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Coins per 100 Rupees</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="coins_per_100_rupees" placeholder="1000" min="0" step="1">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Min Redeem Coins</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="min_redeem_coins" placeholder="1000" min="0" step="1">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Daily Redeem Cap (Rupees)</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="daily_redeem_cap_rupees" placeholder="100" min="0" step="0.01">
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>Check-in</legend>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Coins per day</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="checkin_coins_per_day" placeholder="25" min="0" step="1">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Streak bonus – Day 10</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="streak_day_10" placeholder="100" min="0" step="1">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Streak bonus – Day 20</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="streak_day_20" placeholder="250" min="0" step="1">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Streak bonus – Day 30</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="streak_day_30" placeholder="500" min="0" step="1">
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>Referral</legend>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Referee first order coins</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="referee_first_order_coins" placeholder="50" min="0" step="1">
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
            <div class="form-group col-12 text-center">
                <button type="button" class="btn btn-primary edit-setting-btn"><i class="fa fa-save"></i>
                    {{ trans('lang.save') }}</button>
                <a href="{{ url('/dashboard') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <style>
        .error_top {
            margin: 15px 0;
            padding: 10px 15px;
            border-radius: 4px;
            display: none;
        }
        .error_top.alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error_top.alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .error_top p {
            margin: 0;
            font-weight: 500;
        }
    </style>
    <script>
        const coinsGetUrl = "{{ route('coins.get') }}";
        const coinsPostUrl = "{{ route('coins.update') }}";

        $(document).ready(function() {
            jQuery("#data-table_processing").show();

            $.get(coinsGetUrl, function(data) {
                jQuery("#data-table_processing").hide();
                try {
                    $("#version").val(data.version ?? '');
                    $("#coins_per_100_rupees").val(data.coins_per_100_rupees ?? '');
                    $("#min_redeem_coins").val(data.min_redeem_coins ?? '');
                    $("#daily_redeem_cap_rupees").val(data.daily_redeem_cap_rupees ?? '');
                    $("#checkin_coins_per_day").val(data.checkin_coins_per_day ?? '');
                    $("#streak_day_10").val(data.streak_day_10 ?? '');
                    $("#streak_day_20").val(data.streak_day_20 ?? '');
                    $("#streak_day_30").val(data.streak_day_30 ?? '');
                    $("#referee_first_order_coins").val(data.referee_first_order_coins ?? '');
                } catch (error) { console.error('Error loading coins settings:', error); }
            }).fail(function() {
                jQuery("#data-table_processing").hide();
                $(".error_top").removeClass('alert-success').addClass('alert-danger').show().html("<p><i class='fa fa-exclamation-triangle'></i> Error loading coins settings.</p>");
            });

            $(".edit-setting-btn").click(function() {
                var version = $("#version").val().trim();
                var coinsPer100 = $("#coins_per_100_rupees").val().trim();
                var minRedeem = $("#min_redeem_coins").val().trim();
                var dailyCap = $("#daily_redeem_cap_rupees").val().trim();
                var checkinCoins = $("#checkin_coins_per_day").val().trim();
                var streak10 = $("#streak_day_10").val().trim();
                var streak20 = $("#streak_day_20").val().trim();
                var streak30 = $("#streak_day_30").val().trim();
                var refereeCoins = $("#referee_first_order_coins").val().trim();

                if (!coinsPer100 || isNaN(parseInt(coinsPer100)) || parseInt(coinsPer100) < 0) {
                    $(".error_top").show().html("<p>Please enter a valid Coins per 100 Rupees (0 or greater).</p>").removeClass('alert-success').addClass('alert-danger');
                    window.scrollTo(0, 0);
                    return;
                }
                if (!minRedeem || isNaN(parseInt(minRedeem)) || parseInt(minRedeem) < 0) {
                    $(".error_top").show().html("<p>Please enter a valid Min Redeem Coins (0 or greater).</p>").removeClass('alert-success').addClass('alert-danger');
                    window.scrollTo(0, 0);
                    return;
                }
                if (!dailyCap || isNaN(parseFloat(dailyCap)) || parseFloat(dailyCap) < 0) {
                    $(".error_top").show().html("<p>Please enter a valid Daily Redeem Cap in Rupees (0 or greater).</p>").removeClass('alert-success').addClass('alert-danger');
                    window.scrollTo(0, 0);
                    return;
                }
                if (!checkinCoins || isNaN(parseInt(checkinCoins)) || parseInt(checkinCoins) < 0) {
                    $(".error_top").show().html("<p>Please enter a valid Check-in coins per day (0 or greater).</p>").removeClass('alert-success').addClass('alert-danger');
                    window.scrollTo(0, 0);
                    return;
                }
                if (!streak10 || isNaN(parseInt(streak10)) || parseInt(streak10) < 0) {
                    $(".error_top").show().html("<p>Please enter a valid Streak bonus Day 10 (0 or greater).</p>").removeClass('alert-success').addClass('alert-danger');
                    window.scrollTo(0, 0);
                    return;
                }
                if (!streak20 || isNaN(parseInt(streak20)) || parseInt(streak20) < 0) {
                    $(".error_top").show().html("<p>Please enter a valid Streak bonus Day 20 (0 or greater).</p>").removeClass('alert-success').addClass('alert-danger');
                    window.scrollTo(0, 0);
                    return;
                }
                if (!streak30 || isNaN(parseInt(streak30)) || parseInt(streak30) < 0) {
                    $(".error_top").show().html("<p>Please enter a valid Streak bonus Day 30 (0 or greater).</p>").removeClass('alert-success').addClass('alert-danger');
                    window.scrollTo(0, 0);
                    return;
                }
                if (!refereeCoins || isNaN(parseInt(refereeCoins)) || parseInt(refereeCoins) < 0) {
                    $(".error_top").show().html("<p>Please enter a valid Referee first order coins (0 or greater).</p>").removeClass('alert-success').addClass('alert-danger');
                    window.scrollTo(0, 0);
                    return;
                }

                var payload = {
                    version: version !== '' ? parseInt(version) : 1,
                    coins_per_100_rupees: parseInt(coinsPer100),
                    min_redeem_coins: parseInt(minRedeem),
                    daily_redeem_cap_rupees: parseFloat(dailyCap),
                    checkin_coins_per_day: parseInt(checkinCoins),
                    streak_day_10: parseInt(streak10),
                    streak_day_20: parseInt(streak20),
                    streak_day_30: parseInt(streak30),
                    referee_first_order_coins: parseInt(refereeCoins)
                };

                $.post({
                    url: coinsPostUrl,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: payload
                }).done(function() {
                    $(".error_top").hide();
                    $(".error_top").removeClass('alert-danger').addClass('alert-success').show().html("<p><i class='fa fa-check'></i> Coins settings updated successfully!</p>");
                    window.scrollTo(0, 0);
                    setTimeout(function() { $(".error_top").hide(); }, 3000);
                }).fail(function() {
                    $(".error_top").removeClass('alert-success').addClass('alert-danger').show().html("<p><i class='fa fa-exclamation-triangle'></i> Error updating coins settings. Please try again.</p>");
                    window.scrollTo(0, 0);
                });
            });
        });
    </script>
@endsection
