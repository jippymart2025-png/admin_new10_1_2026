@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.reviewattribute_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('reviewattributes') !!}">{{trans('lang.reviewattribute_plural')}}</a>
                </li>
                <li class="breadcrumb-item active">{{trans('lang.reviewattribute_create')}}</li>
            </ol>
        </div>
    </div>
    <div class="card-body">
        <div class="error_top" style="display:none"></div>
        <div class="row restaurant_payout_create">
            <div class="restaurant_payout_create-inner">
                <fieldset>
                    <legend>{{trans('lang.reviewattribute_create')}}</legend>
                    <div class="form-group row width-100">
                        <label class="col-3 control-label">{{trans('lang.reviewattribute_name')}}</label>
                        <div class="col-7">
                            <input type="text" class="form-control cat-name">
                            <div class="form-text text-muted">{{ trans("lang.reviewattribute_name_help") }}</div>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
    <div class="form-group col-12 text-center btm-btn">
        <button type="button" class="btn btn-primary save-form-btn"><i class="fa fa-save"></i>
            {{trans('lang.save')}}
        </button>
        <a href="{!! route('reviewattributes') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
    </div>
</div>
</div>
</div>
@endsection
@section('scripts')
<script>
    // SQL mode - no Firebase
    $(document).ready(function () {
        console.log('Create review attribute - SQL mode');
        jQuery("#data-table_processing").hide();

        $(".save-form-btn").click(function () {
            var title = $(".cat-name").val();
            $(".error_top").hide();
            $(".error_top").html("");

            if (title == '') {
                $(".error_top").show();
                $(".error_top").append("<p>{{trans('lang.enter_reviewattribute_title_error')}}</p>");
                window.scrollTo(0, 0);
            } else {
                jQuery("#data-table_processing").show();

                console.log('💾 Creating review attribute:', { title: title });

                $.ajax({
                    url: "{{ route('reviewattributes.store') }}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        title: title
                    },
                    success: function(response) {
                        console.log('✅ Review attribute created successfully:', response);

                        // Log activity
                        if (typeof logActivity === 'function') {
                            logActivity('review_attributes', 'created', 'Created review attribute: ' + title);
                        }

                        window.location.href = '{{ route("reviewattributes")}}';
                    },
                    error: function(xhr) {
                        console.error('❌ Create error:', xhr);
                        jQuery("#data-table_processing").hide();
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>Error creating review attribute</p>");
                        window.scrollTo(0, 0);
                    }
                });
            }
        });
    });
</script>
@endsection
