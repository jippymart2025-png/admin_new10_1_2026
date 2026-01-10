@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.document_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('documents') !!}">{{trans('lang.document_plural')}}</a>
                </li>
                <li class="breadcrumb-item active">{{trans('lang.document_create')}}</li>
            </ol>
        </div>
    </div>
    <div class="card-body">
        <div class="error_top" style="display:none"></div>
        <div class="row restaurant_payout_create">
            <div class="restaurant_payout_create-inner">
                <fieldset>
                    <legend>{{trans('lang.document_create')}}</legend>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">{{trans('lang.title')}}</label>
                        <div class="col-7">
                            <input type="text" type="text" class="form-control title">
                            <div class="form-text text-muted">{{ trans("lang.document_title_help") }}</div>
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">{{trans('lang.document_for')}}</label>
                        <div class="col-7">
                            <select id="document_for" class="form-control">
                                <option value="restaurant">{{trans('lang.restaurant')}}</option>
                                <option value="driver">{{trans('lang.driver')}}</option>
                            </select>
                            <div class="form-text text-muted">{{ trans("lang.select_document_for") }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <div class="form-check">
                            <input type="checkbox" class="frontside" id="frontside">
                            <label class="col-3 control-label" for="frontside">{{trans('lang.frontside')}}</label>
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <div class="form-check">
                            <input type="checkbox" class="backside" id="backside">
                            <label class="col-3 control-label" for="backside">{{trans('lang.backside')}}</label>
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <div class="form-check">
                            <input type="checkbox" class="enable" id="enable">
                            <label class="col-3 control-label" for="enable">{{trans('lang.enable')}}</label>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
    <div class="form-group col-12 text-center btm-btn">
        <button type="button" class="btn btn-primary save-setting-btn"><i class="fa fa-save"></i> {{
            trans('lang.save')}}
        </button>
        <a href="{!! route('documents') !!}" class="btn btn-default"><i
                class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
    </div>
</div>
</div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet">
<script>
    $(document).ready(function(){
        console.log('📄 Document create page initialized');

        $(".save-setting-btn").click(function(){
            $(".error_top").hide().html('');

            var title = $(".title").val();
            var document_for = $("#document_for").val();
            var isEnabled = $(".enable").is(":checked");
            var forntend = $(".frontside").is(":checked");
            var backend = $(".backside").is(":checked");

            console.log('💾 Creating document:', { title: title, type: document_for });

            if(!title){
                $(".error_top").show().html('<p>{{trans('lang.document_title_help')}}</p>');
                window.scrollTo(0,0);
                return;
            }
            if(!forntend && !backend){
                $(".error_top").show().html('<p>{{trans('lang.check_atleast_one_side_of_document_from_front_or_back')}}</p>');
                window.scrollTo(0,0);
                return;
            }

            jQuery("#data-table_processing").show();

            var fd = new FormData();
            fd.append('title', title);
            fd.append('type', document_for);
            fd.append('enable', isEnabled ? 1 : 0);
            fd.append('frontSide', forntend ? 1 : 0);
            fd.append('backSide', backend ? 1 : 0);

            $.ajax({
                url: '{{ route('documents.store') }}',
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .done(function(response){
                console.log('✅ Document created successfully:', response);

                // Log activity
                if (typeof logActivity === 'function') {
                    logActivity('documents', 'created', 'Created document: ' + title);
                }

                window.location.href = '{{ route('documents') }}';
            })
            .fail(function(xhr){
                console.error('❌ Create failed:', xhr);
                jQuery("#data-table_processing").hide();
                $(".error_top").show().html('<p>Failed ('+xhr.status+'): '+(xhr.responseJSON?.message || xhr.statusText)+'</p>');
                window.scrollTo(0,0);
            });
        });
    });
</script>
@endsection
