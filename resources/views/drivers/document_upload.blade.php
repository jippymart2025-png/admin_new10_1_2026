@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.upload_document')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('drivers') !!}">{{trans('lang.driver_plural')}}</a>
                </li>
                <li class="breadcrumb-item active">{{trans('lang.upload_document')}}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card  pb-4">
            <div class="card-body">
                <div class="error_top"></div>
                <div class="row restaurant_payout_create">
                    <div class="restaurant_payout_create-inner doc-body">
                    </div>
                </div>
                <div class="form-group col-12 text-center btm-btn">
                    <button type="button" class="btn btn-primary save-form-btn"><i class="fa fa-save"></i> {{
    trans('lang.save')}}
                    </button>
                    <a href="{{url('drivers/document-list/' . $driverId)}}" class="btn btn-default"><i
                            class="fa fa-undo"></i>{{
    trans('lang.cancel')}}</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    var docId = "{{$id}}";
    var id = "{{$driverId}}";
    var back_photo = '';
    var front_photo = '';
    var backFileName = '';
    var frontFileName = '';
    var backFileOld = '';
    var frontFileOld = '';
    var placeholderImage = '{{ asset('images/placeholder.png') }}';

    $(document).ready(function () {
        jQuery("#data-table_processing").show();

        // Load document upload data from SQL
        $.ajax({
            url: '{{ route("api.drivers.document.upload.data", [":driverId", ":docId"]) }}'.replace(':driverId', id).replace(':docId', docId),
            method: 'GET',
            success: function(response) {
                console.log('✅ Document upload data loaded:', response);

                if (response.success && response.document) {
                    var doc = response.document;
                    var verification = response.verification || {};
                    var keydata = response.keyData || 0;
                    var isAdd = response.isAdd;

                    var html = '';

                    if (doc.enable) {
                        html += '<fieldset><legend>' + doc.title + '</legend>';

                        // Front image section
                        if (doc.frontSide) {
                            html += '<div class="form-group row width-' + (doc.backSide ? '50' : '100') + '">';
                            html += '<input type="hidden" name="frontSide" id="frontSide" value="true">';
                            front_photo = verification.frontImage || '';
                            frontFileOld = verification.frontImage || '';
                            html += '<label class="col-3 control-label">{{trans("lang.front_image")}}<span class="required-field"></span></label>';
                            html += '<div class="col-7">';
                            html += '<input type="file" onChange="handleFrontFileSelect(event)" class="form-control image">';
                            html += '<div class="placeholder_img_thumb front_image">';
                            html += '<span class="image-item"><span class="remove-btn" id="front_image"><i class="fa fa-remove"></i></span>';
                            html += '<img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" class="rounded" style="width:200px; height:auto" src="' + (verification.frontImage || placeholderImage) + '" alt="image"></span>';
                            html += '</div><div id="uploding_image"></div>';
                            html += '</div></div>';
                        }

                        // Back image section
                        if (doc.backSide) {
                            html += '<div class="form-group row width-50">';
                            html += '<input type="hidden" name="backSide" id="backSide" value="true">';
                            back_photo = verification.backImage || '';
                            backFileOld = verification.backImage || '';
                            html += '<label class="col-3 control-label">{{trans("lang.back_image")}}<span class="required-field"></span></label>';
                            html += '<div class="col-7">';
                            html += '<input type="file" onChange="handleBackFileSelect(event)" class="form-control image">';
                            html += '<div class="placeholder_img_thumb back_image">';
                            html += '<span class="image-item"><span class="remove-btn" id="back_image"><i class="fa fa-remove"></i></span>';
                            html += '<img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" class="rounded" style="width:200px; height:auto" src="' + (verification.backImage || placeholderImage) + '" alt="image"></span>';
                            html += '</div><div id="uploding_image"></div>';
                            html += '</div></div>';
                        }

                        html += '<input type="hidden" name="docId" id="docId" value="' + doc.id + '">';
                        html += '<input type="hidden" name="keydata" id="keydata" value="' + keydata + '">';
                        html += '<input type="hidden" name="isAdd" id="isAdd" value="' + isAdd + '">';
                        html += '<input type="hidden" name="docTitle" id="docTitle" value="' + doc.title + '">';
                        html += '</fieldset>';
                    }

                    $(".doc-body").html(html);
                    jQuery("#data-table_processing").hide();
                } else {
                    console.error('❌ Failed to load document data');
                    jQuery("#data-table_processing").hide();
                    $(".doc-body").html('<p class="text-danger">Error loading document. Please try again.</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Error loading document data:', error);
                jQuery("#data-table_processing").hide();
                $(".doc-body").html('<p class="text-danger">Error loading document: ' + error + '</p>');
            }
        });
    });

    function handleFrontFileSelect(evt) {
        var f = evt.target.files[0];
        var validExtensions = ['jpg', 'jpeg', 'png'];
        var fileExtension = f.name.split('.').pop().toLowerCase();
        if (validExtensions.indexOf(fileExtension) === -1) {
            alert("{{trans('lang.invalid_file_extension')}}");
            return;
        }
        var reader = new FileReader();
        reader.onload = (function (theFile) {
            return function (e) {
                var filePayload = e.target.result;
                var val = f.name;
                var ext = val.split('.')[1];
                var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                var timestamp = Number(new Date());
                var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                front_photo = filePayload;
                frontFileName = filename;
                $(".front_image").empty();
                $(".front_image").append('<span class="image-item"><span class="remove-btn" id="front_image"><i class="fa fa-remove"></i></span><img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" class="rounded" style="width:200px; height:auto" src="' + filePayload + '" alt="image"></span>');
            };
        })(f);
        reader.readAsDataURL(f);
    }

    function handleBackFileSelect(evt) {
        var f = evt.target.files[0];
        var validExtensions = ['jpg', 'jpeg', 'png'];
        var fileExtension = f.name.split('.').pop().toLowerCase();
        if (validExtensions.indexOf(fileExtension) === -1) {
            alert("{{trans('lang.invalid_file_extension')}}");
            return;
        }
        var reader = new FileReader();
        reader.onload = (function (theFile) {
            return function (e) {
                var filePayload = e.target.result;
                var val = f.name;
                var ext = val.split('.')[1];
                var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                var timestamp = Number(new Date());
                var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                back_photo = filePayload;
                backFileName = filename;
                $(".back_image").empty();
                $(".back_image").append('<span class="image-item"><span class="remove-btn" id="back_image"><i class="fa fa-remove"></i></span><img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" class="rounded" style="width:200px; height:auto" src="' + filePayload + '" alt="image"></span>');
            };
        })(f);
        reader.readAsDataURL(f);
    }

    $(document).on('click', '.save-form-btn', function () {
        var docId = $("#docId").val();
        var isAdd = $("#isAdd").val();
        var keydata = $("#keydata").val();
        var backSide = $("#backSide").val();
        var frontSide = $("#frontSide").val();
        var docTitle = $("#docTitle").val();

        // Validate required fields
        if (backSide && back_photo == "") {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.document_back_side_help')}}</p>");
            window.scrollTo(0, 0);
            return;
        } else if (frontSide && front_photo == "") {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.document_front_side_help')}}</p>");
            window.scrollTo(0, 0);
            return;
        }

        jQuery("#data-table_processing").show();

        console.log('📤 Uploading driver document:', { driverId: id, docId: docId, frontImage: frontFileName, backImage: backFileName });

        // Upload via SQL API
        $.ajax({
            url: '{{ route("api.drivers.document.upload.save", [":driverId", ":docId"]) }}'.replace(':driverId', id).replace(':docId', docId),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: {
                frontImage: front_photo != frontFileOld ? front_photo : null,
                backImage: back_photo != backFileOld ? back_photo : null,
                frontFilename: frontFileName,
                backFilename: backFileName,
                isAdd: isAdd,
                keyData: keydata
            },
            success: function(response) {
                console.log('✅ Document uploaded successfully:', response);

                // Log activity
                if (typeof logActivity === 'function') {
                    logActivity('drivers', 'document_uploaded', 'Uploaded document "' + docTitle + '" for driver ID: ' + id);
                }

                jQuery("#data-table_processing").hide();
                window.location.href = "/drivers/document-list/" + id;
            },
            error: function(xhr, status, error) {
                console.error('❌ Error uploading document:', error);
                jQuery("#data-table_processing").hide();
                $(".error_top").show();
                $(".error_top").html("");
                var errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : error;
                $(".error_top").append("<p>Error uploading document: " + errorMsg + "</p>");
                window.scrollTo(0, 0);
            }
        });
    });

    $(document).on('click', '.remove-btn', function () {
        var currentId = $(this).attr('id')
        if (currentId == "back_image") {
            $(".back_image").find('img').attr('src', placeholderImage);
            back_photo = '';
            backFileName = '';
        }
        if (currentId == "front_image") {
            $(".front_image").find('img').attr('src', placeholderImage);
            front_photo = '';
            frontFileName = '';
        }
    });
</script>
@endsection
