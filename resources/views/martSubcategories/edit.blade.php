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
                    <li class="breadcrumb-item"><a href="#" id="subcategoriesLink">Sub-Categories</a></li>
                    <li class="breadcrumb-item active">Edit Sub-Category</li>
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
                                <a href="#review_attributes" aria-controls="review_attributes" role="tab"
                                   data-toggle="tab"
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
                                        <legend>Edit Mart Sub-Category</legend>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Sub-Category Name</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control subcategory-name">
                                                <div class="form-text text-muted">Enter the name for this sub-category</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label ">Sub-Category Description</label>
                                            <div class="col-7">
                                <textarea rows="7" class="subcategory_description form-control"
                                          id="subcategory_description"></textarea>
                                                <div class="form-text text-muted">Enter a description for this sub-category</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Sub-Category Image</label>
                                            <div class="col-7">
                                                <input type="file" id="subcategory_image">
                                                <div class="placeholder_img_thumb subcategory_image"></div>
                                                <div id="uploding_image"></div>
                                                <div class="form-text text-muted w-50">Upload an image for this sub-category</div>
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
                        <button type="button" class="btn btn-primary edit-setting-btn"><i
                                    class="fa fa-save"></i> {{trans('lang.save')}}</button>
                        <a href="#" id="cancelLink" class="btn btn-default"><i
                                    class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
<script>
const id = "{{ $id }}";
const placeholderImage = '{{ asset("images/placeholder.png") }}';
let existingPhoto = '';
let selectedFile = null;
let categoryId = '';

$(document).ready(function () {
    jQuery("#data-table_processing").show();
    loadSubcategory();
    registerEvents();
});

function loadSubcategory() {
    $.ajax({
        url: '/api/mart-subcategories/' + id,
        type: 'GET',
        success: function(subcategory) {
            categoryId = subcategory.parent_category_id;
            $('#subcategoriesLink').attr('href', '{{ url("mart-categories") }}/' + categoryId + '/subcategories');
            $('#cancelLink').attr('href', '{{ url("mart-categories") }}/' + categoryId + '/subcategories');

            $(".subcategory-name").val(subcategory.title || '');
            $(".subcategory_description").val(subcategory.description || '');
            $("#subcategory_order").val(subcategory.subcategory_order || 1);
            $("#section_info").val(subcategory.section || 'General');
            $("#parent_category_info").val(subcategory.parent_category_title || 'Unknown');

            existingPhoto = subcategory.photo || '';
            renderImagePreview(existingPhoto);

            $("#item_publish").prop('checked', !!subcategory.publish);
            $("#show_in_homepage").prop('checked', !!subcategory.show_in_homepage);

            loadReviewAttributes(subcategory.review_attributes || []);
        },
        error: function(xhr) {
            jQuery("#data-table_processing").hide();
            const message = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Unable to load sub-category information.';
            $(".error_top").show().html("<p>" + message + "</p>");
            window.scrollTo(0, 0);
        }
    });
}

function loadReviewAttributes(selectedAttributes) {
    $.ajax({
        url: '/api/review-attributes',
        type: 'GET',
        success: function(reviewAttributes) {
            const html = reviewAttributes.map(function(attr) {
                const checked = selectedAttributes.includes(attr.id) ? 'checked' : '';
                return '<div class="form-check width-100">' +
                    '<input type="checkbox" id="review_attribute_' + attr.id + '" value="' + attr.id + '" ' + checked + '>' +
                    '<label class="col-3 control-label" for="review_attribute_' + attr.id + '">' + attr.title + '</label>' +
                '</div>';
            }).join('');

            $('#review_attributes').html(html);
            jQuery("#data-table_processing").hide();
        },
        error: function(xhr) {
            jQuery("#data-table_processing").hide();
            console.error('Error loading review attributes:', xhr);
        }
    });
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

    $(".edit-setting-btn").on('click', function () {
        $(".error_top").hide().empty();

        const title = $(".subcategory-name").val().trim();
        if (!title) {
            $(".error_top").show().html("<p>Please enter a sub-category name</p>");
            window.scrollTo(0, 0);
            return;
        }

        const description = $(".subcategory_description").val();
        const item_publish = $("#item_publish").is(":checked");
        const show_in_homepage = $("#show_in_homepage").is(":checked");
        const subcategory_order = parseInt($("#subcategory_order").val(), 10) || 1;

        const review_attributes = [];
        $('#review_attributes input:checked').each(function () {
            review_attributes.push($(this).val());
        });

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('title', title);
        formData.append('description', description || '');
        formData.append('subcategory_order', subcategory_order);
        formData.append('publish', item_publish ? 1 : 0);
        formData.append('show_in_homepage', show_in_homepage ? 1 : 0);

        if (selectedFile) {
            formData.append('photo_file', selectedFile);
        } else if (existingPhoto) {
            formData.append('existing_photo', existingPhoto);
        }

        review_attributes.forEach(function(attrId) {
            formData.append('review_attributes[]', attrId);
        });

        jQuery("#data-table_processing").show();

        $.ajax({
            url: '/api/mart-subcategories/' + id + '/update',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function () {
                jQuery("#data-table_processing").hide();
                const redirectUrl = categoryId
                    ? '{{ url("mart-categories") }}/' + categoryId + '/subcategories'
                    : '{{ url("mart-categories") }}';
                window.location.href = redirectUrl;
            },
            error: function(xhr) {
                jQuery("#data-table_processing").hide();
                const message = xhr.responseJSON && xhr.responseJSON.error
                    ? xhr.responseJSON.error
                    : (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error updating sub-category');
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
