@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">Mart Sub-Categories</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('mart-categories') !!}">Mart Categories</a></li>
                    <li class="breadcrumb-item"><a href="{{route('mart-subcategories.index', ['category_id' => $categoryId])}}">Sub-Categories</a></li>
                    <li class="breadcrumb-item active">Create Sub-Category</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="cat-edite-page max-width-box">
                <div class="card  pb-4">
                    <div class="card-header">
                        <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                            <li role="presentation" class="nav-item">
                                <a href="#subcategory_information" aria-controls="description" role="tab" data-toggle="tab"
                                   class="nav-link active">Sub-Category Information</a>
                            </li>
                            <li role="presentation" class="nav-item">
                                <a href="#review_attributes" aria-controls="review_attributes" role="tab" data-toggle="tab"
                                   class="nav-link">{{trans('lang.reviewattribute_plural')}}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="error_top" style="display:none"></div>
                        <div class="row restaurant_payout_create" role="tabpanel">
                            <div class="restaurant_payout_create-inner tab-content">
                                <div role="tabpanel" class="tab-pane active" id="subcategory_information">
                                    <fieldset>
                                        <legend>Create Mart Sub-Category</legend>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Sub-Category Name</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control subcategory-name">
                                                <div class="form-text text-muted">Enter the name for this sub-category
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label ">Sub-Category Description</label>
                                            <div class="col-7">
                            <textarea rows="7" class="subcategory_description form-control"
                                      id="subcategory_description"></textarea>
                                                <div class="form-text text-muted">Enter a description for this sub-category
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Sub-Category Image</label>
                                            <div class="col-7">
                                                <input type="file" id="subcategory_image">
                                                <div class="placeholder_img_thumb subcategory_image"></div>
                                                <div id="uploding_image"></div>
                                                <div class="form-text text-muted w-50">Upload an image for this sub-category
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Order</label>
                                            <div class="col-7">
                                                <input type="number" class="form-control" id="subcategory_order" value="1" min="1">
                                                <div class="form-text text-muted w-50">Display order within parent category</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Section</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control" id="section_info" readonly>
                                                <div class="form-text text-muted w-50">Inherited from parent category</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Parent Category</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control" id="parent_category_info" readonly>
                                                <div class="form-text text-muted w-50">Parent category information</div>
                                            </div>
                                        </div>
                                       <div class="form-check row width-100">
                                        <input type="checkbox" class="item_publish" id="item_publish">
                                        <label class="col-3 control-label"
                                               for="item_publish">Publish</label>
                                       </div>
                                        <div class="form-check row width-100" id="show_in_home">
                                            <input type="checkbox" id="show_in_homepage">
                                            <label class="col-3 control-label" for="show_in_homepage">{{trans('lang.show_in_home')}}</label>
                                            <div class="form-text text-muted w-50">{{trans('lang.show_in_home_desc')}}<span id="forsection"></span></div>
                                        </div>
                                    </fieldset>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="review_attributes">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-12 text-center btm-btn">
                        <button type="button" class="btn btn-primary save-setting-btn"><i class="fa fa-save"></i>
                            {{trans('lang.save')}}
                        </button>
                        <a href="{{route('mart-subcategories.index', ['category_id' => $categoryId])}}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
<script>
const categoryId = "{{ $categoryId }}";
const placeholderImage = '{{ asset("images/placeholder.png") }}';
let selectedFile = null;
let initialLoadsPending = 2;

$(document).ready(function () {
    renderImagePreview('');
    jQuery("#data-table_processing").show();

    loadParentCategoryInfo();
    loadReviewAttributes();
    registerEvents();
});

function loadParentCategoryInfo() {
    $.ajax({
        url: '/api/mart-categories/' + categoryId + '/info',
        type: 'GET',
        success: function(category) {
            $('#parent_category_info').val(category.title);
            $('#section_info').val(category.section || 'General');
        },
        error: function(xhr) {
            console.error('Error loading parent category:', xhr);
        },
        complete: function() {
            markInitialLoadComplete();
        }
    });
}

function loadReviewAttributes() {
    $.ajax({
        url: '/api/review-attributes',
        type: 'GET',
        success: function(reviewAttributes) {
            const html = reviewAttributes.map(function(attr) {
                return '<div class="form-check width-100">' +
                    '<input type="checkbox" id="review_attribute_' + attr.id + '" value="' + attr.id + '">' +
                    '<label class="col-3 control-label" for="review_attribute_' + attr.id + '">' + attr.title + '</label>' +
                '</div>';
            }).join('');

            $('#review_attributes').html(html);
        },
        error: function(xhr) {
            console.error('Error loading review attributes:', xhr);
        },
        complete: function() {
            markInitialLoadComplete();
        }
    });
}

function markInitialLoadComplete() {
    initialLoadsPending = Math.max(0, initialLoadsPending - 1);
    if (initialLoadsPending === 0) {
        jQuery("#data-table_processing").hide();
    }
}

function registerEvents() {
    $('#subcategory_image').on('change', function(event) {
        const file = event.target.files[0];
        if (!file) {
            return;
        }

        selectedFile = file;
        const reader = new FileReader();
        reader.onload = function(e) {
            renderImagePreview(e.target.result);
        };
        reader.readAsDataURL(file);
    });

    $(".save-setting-btn").on('click', function () {
        $(".error_top").hide().empty();

        const title = $(".subcategory-name").val().trim();
        if (!title) {
            $(".error_top").show().html("<p>Please enter a sub-category name</p>");
            window.scrollTo(0, 0);
            return;
        }

        const description = $(".subcategory_description").val();
        const subcategory_order = parseInt($("#subcategory_order").val(), 10) || 1;
        const item_publish = $("#item_publish").is(":checked");
        const show_in_homepage = $("#show_in_homepage").is(":checked");

        const review_attributes = [];
        $('#review_attributes input:checked').each(function () {
            review_attributes.push($(this).val());
        });

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('title', title);
        formData.append('description', description || '');
        formData.append('parent_category_id', categoryId);
        formData.append('subcategory_order', subcategory_order);
        formData.append('publish', item_publish ? 1 : 0);
        formData.append('show_in_homepage', show_in_homepage ? 1 : 0);

        if (selectedFile) {
            formData.append('photo_file', selectedFile);
        }

        review_attributes.forEach(function(attrId) {
            formData.append('review_attributes[]', attrId);
        });

        jQuery("#data-table_processing").show();

        $.ajax({
            url: '{{ route("api.mart-subcategories.store") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function() {
                jQuery("#data-table_processing").hide();
                window.location.href = '{{ route("mart-subcategories.index", ["category_id" => $categoryId]) }}';
            },
            error: function(xhr) {
                jQuery("#data-table_processing").hide();
                const message = xhr.responseJSON && xhr.responseJSON.error
                    ? xhr.responseJSON.error
                    : (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error saving sub-category');
                $(".error_top").show().html("<p>" + message + "</p>");
                window.scrollTo(0, 0);
            }
        });
    });
}

function renderImagePreview(url) {
    const src = url ? url : placeholderImage;
    $('.subcategory_image').html('<img class="rounded" style="width:50px" src="' + src + '" alt="image">');
}
</script>
@endsection
