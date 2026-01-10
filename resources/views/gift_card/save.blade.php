@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            @if($id=='')
            <h3 class="text-themecolor">{{trans('lang.create_gift_card')}}</h3>
            @else
            <h3 class="text-themecolor">{{trans('lang.edit_gift_card')}}</h3>
            @endif
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{{ url('gift-card') }}">{{trans('lang.gift_card')}}</a>
                </li>
                @if($id=='')
                <li class="breadcrumb-item active">{{trans('lang.create_gift_card')}}</li>
                @else
                <li class="breadcrumb-item active">{{trans('lang.edit_gift_card')}}</li>
                @endif
            </ol>
        </div>
    </div>
    <div>
        <div class="card-body">
            <div class="error_top" style="display:none"></div>
            <div class="success_top" style="display:none"></div>
            <div class="row restaurant_payout_create">
                <div class="restaurant_payout_create-inner">
                    <fieldset>
                        <legend>{{trans('lang.gift_card')}}</legend>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.title')}}</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="title" >
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.message')}}</label>
                            <div class="col-7">
                            <textarea rows="8" class="form-control col-7" name="message" id="message"></textarea>
                        </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.image')}}</label>
                            <div class="col-7">
                                <input type="file" class="form-control" id="gift_card_image">
                                 <div class="placeholder_img_thumb gift_card_image"></div>
                                 <div id="uploding_image"></div>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.expiry_day')}}</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="expiry">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <div class="form-check width-100">
                                <input type="checkbox" id="status">
                                <label class="col-3 control-label"
                                    for="status">{{trans('lang.status')}}</label>
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
            <a href="{{url('gift-card')}}" class="btn btn-default"><i class="fa fa-undo"></i>{{
                trans('lang.cancel')}}</a>
        </div>
    </div>
    @endsection
    @section('scripts')
    <script>
        var requestId = "<?php echo $id; ?>";
        $(document).ready(function(){
            if (requestId) {
                console.log('🔄 Loading gift card data for ID:', requestId);

                $.get('{{ url('gift-card/json') }}/' + requestId, function(resp){
                    console.log('✅ Gift card data loaded:', resp);

                    $("#title").val(resp.title || '');
                    $('#message').val(resp.message || '');
                    $('#expiry').val(resp.expiryDay || '');
                    if (resp.isEnable) { $("#status").prop('checked', true); }

                    if (resp.image) {
                        // Handle both full URLs and storage paths
                        var imgSrc = resp.image;
                        if (resp.image.indexOf('http') !== 0 && resp.image.indexOf('storage/') !== 0) {
                            imgSrc = '{{ asset('storage') }}/' + resp.image;
                        } else if (resp.image.indexOf('storage/') === 0) {
                            imgSrc = '{{ asset('') }}' + resp.image;
                        }
                        $(".gift_card_image").html('<span class="image-item"><img class="rounded" style="width:50px" src="'+imgSrc+'" alt="image"></span>');
                    }

                    console.log('📝 Form populated with gift card data');
                })
                .fail(function(xhr){
                    console.error('❌ Failed to load gift card data:', xhr);
                    $(".error_top").show().html('<p>Error loading gift card data</p>');
                });
            }

            $(".edit-form-btn").click(function(){
                $(".success_top").hide(); $(".error_top").hide();
                var title = $("#title").val();
                var message = $('#message').val();
                var expiryDay = $('#expiry').val();
                var isEnable = $("#status").is(":checked");

                if (!title || !message || !expiryDay || parseInt(expiryDay) <= 0) {
                    $(".error_top").show().html('<p>Please fill all required fields correctly</p>');
                    window.scrollTo(0,0); return;
                }

                var action = requestId ? 'Updating' : 'Creating';
                console.log('💾 ' + action + ' gift card:', { title: title, expiryDay: expiryDay });

                jQuery("#data-table_processing").show();

                var fd = new FormData();
                fd.append('title', title);
                fd.append('message', message);
                fd.append('expiryDay', expiryDay);
                fd.append('isEnable', isEnable ? 1 : 0);
                var file = document.getElementById('gift_card_image').files[0];
                if (file) {
                    fd.append('image', file);
                    console.log('📤 Including image file:', file.name);
                }
                var url = requestId ? ('{{ url('gift-card') }}' + '/' + requestId) : '{{ route('gift-card.store') }}';

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .done(function(response){
                    console.log('✅ Gift card ' + (requestId ? 'updated' : 'created') + ' successfully:', response);

                    // Log activity
                    if (typeof logActivity === 'function') {
                        var activityAction = requestId ? 'updated' : 'created';
                        logActivity('gift_cards', activityAction, (requestId ? 'Updated' : 'Created') + ' gift card: ' + title);
                    }

                    window.location.href='{{ route('gift-card.index') }}';
                })
                .fail(function(xhr){
                    console.error('❌ Save failed:', xhr);
                    jQuery("#data-table_processing").hide();
                    $(".error_top").show().html('<p>Failed ('+xhr.status+'): '+xhr.statusText+'</p>');
                });
            });
        });
    </script>
    @endsection
