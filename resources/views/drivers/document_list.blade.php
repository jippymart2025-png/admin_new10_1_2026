@extends('layouts.app')
@section('content')
<div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor restaurantTitle">{{trans('lang.driver_document_details')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('drivers') !!}">{{trans('lang.driver_plural')}}</a></li>
                    <li class="breadcrumb-item active">{{trans('lang.driver_document_details')}}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                                <li class="nav-item">
                                    <a class="nav-link active vendor-name"
                                       href="{!! url()->current() !!}">{{trans('lang.driver_document_details')}}</a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10 doc-body"></div>
                            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog"
                                 aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document" style="max-width: 50%;">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close"
                                                    data-dismiss="modal"
                                                    aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <div id="image-loading" class="text-muted" style="display:none;">
                                                <i class="fa fa-spinner fa-spin"></i> Loading image...
                                            </div>
                                            <div class="form-group">
                                                <img id="docImage"
                                                     src=""
                                                     style="max-width: 100%; max-height: 600px; height: auto; display:none;"
                                                     alt="Document Image"
                                                />
                                            </div>
                                            <div id="image-error" class="alert alert-danger" style="display:none;">
                                                Failed to load image. Please check if the file exists.
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">{{trans('lang.close')}}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
<script>
    var id = "<?php echo $id;?>";
    var fcmToken = "";

    $(document).ready(function () {
        jQuery("#data-table_processing").show();

        // Modal for viewing images
        $('#exampleModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var img = button.data('image');
            var modal = $(this);

            console.log('🖼️ Opening image modal with URL:', img);

            // Reset modal state
            modal.find('#image-loading').show();
            modal.find('#docImage').hide();
            modal.find('#image-error').hide();

            // Create new image object to preload
            var imageObj = new Image();

            imageObj.onload = function() {
                console.log('✅ Image loaded successfully');
                modal.find('#image-loading').hide();
                modal.find('#docImage').attr('src', img).show();
            };

            imageObj.onerror = function() {
                console.error('❌ Failed to load image:', img);
                modal.find('#image-loading').hide();
                modal.find('#image-error').show();
                modal.find('#image-error').html('Failed to load image.<br>URL: ' + img + '<br>Please check if the file exists at this location.');
            };

            // Start loading image
            imageObj.src = img;
        });

        // Load driver document data from SQL
        $.ajax({
            url: '{{ route("api.drivers.document.data", ":id") }}'.replace(':id', id),
            method: 'GET',
            success: function(response) {
                console.log('✅ Driver document data loaded:', response);

                if (response.success && response.driver && response.documents) {
                    var driver = response.driver;
                    var documents = response.documents;
                    var verification = response.verification || [];

                    fcmToken = driver.fcmToken || '';

                    // Set driver name in header
                    $(".vendor-name").text(driver.firstName + ' ' + driver.lastName + "'s {{trans('lang.driver_document_details')}}");

                    // Build table HTML
                    var html = '';
                    html += '<table id="taxTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">';
                    html += '<thead>';
                    html += '<tr>';
                    html += '<th>Name</th>';
                    html += '<th>Status</th>';
                    html += '<th>Action</th>';
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';

                    // Loop through documents
                    documents.forEach(function(doc) {
                        // Find verification status for this document
                        var docVerification = verification.find(v => v.documentId == doc.id);

                        var trhtml = '<tr>';

                        // Document name with view links
                        trhtml += '<td>' + doc.title;
                        if (docVerification) {
                            console.log('📄 Document verification data:', docVerification);
                            if (docVerification.frontImage && docVerification.frontImage.trim() !== '' && doc.frontSide) {
                                var frontImgUrl = docVerification.frontImage;
                                console.log('🖼️ Front image URL:', frontImgUrl);
                                trhtml += '&nbsp;<a href="#" class="badge badge-info view-image-btn" data-toggle="modal" data-target="#exampleModal" data-image="' + frontImgUrl + '">{{trans('lang.view_front_image')}}</a>';
                            }
                            if (docVerification.backImage && docVerification.backImage.trim() !== '' && doc.backSide) {
                                var backImgUrl = docVerification.backImage;
                                console.log('🖼️ Back image URL:', backImgUrl);
                                trhtml += '&nbsp;<a href="#" class="badge badge-info view-image-btn" data-toggle="modal" data-target="#exampleModal" data-image="' + backImgUrl + '">{{trans('lang.view_back_image')}}</a>';
                            }
                        }
                        trhtml += '</td>';

                        // Status badge
                        var status = docVerification && docVerification.status ? docVerification.status : 'pending';
                        var statusBadge = '';
                        if (status == 'approved') {
                            statusBadge = '<span class="badge badge-success py-2 px-3">approved</span>';
                        } else if (status == 'rejected') {
                            statusBadge = '<span class="badge badge-danger py-2 px-3">rejected</span>';
                        } else if (status == 'uploaded') {
                            statusBadge = '<span class="badge badge-primary py-2 px-3">uploaded</span>';
                        } else {
                            statusBadge = '<span class="badge badge-warning py-2 px-3">pending</span>';
                        }
                        trhtml += '<td>' + statusBadge + '</td>';

                        // Actions
                        trhtml += '<td class="action-btn">';
                        trhtml += '<a href="/drivers/document/upload/' + id + '/' + doc.id + '"><i class="mdi mdi-lead-pencil" title="Edit"></i></a>';

                        // Show approve/reject buttons based on status
                        if (status !== 'pending') {
                            if (status == 'rejected') {
                                trhtml += '&nbsp;<a href="javascript:void(0);" class="btn btn-sm btn-success verify-doc" data-action="approved" data-title="' + doc.title + '" data-id="' + doc.id + '">{{trans('lang.approve')}}</a>';
                            } else if (status == 'approved') {
                                trhtml += '&nbsp;<a href="javascript:void(0);" class="btn btn-sm btn-danger verify-doc" data-action="rejected" data-title="' + doc.title + '" data-id="' + doc.id + '">{{trans('lang.reject')}}</a>';
                            } else {
                                trhtml += '&nbsp;<a href="javascript:void(0);" class="btn btn-sm btn-success verify-doc" data-action="approved" data-title="' + doc.title + '" data-id="' + doc.id + '">{{trans('lang.approve')}}</a>';
                                trhtml += '&nbsp;<a href="javascript:void(0);" class="btn btn-sm btn-danger verify-doc" data-action="rejected" data-title="' + doc.title + '" data-id="' + doc.id + '">{{trans('lang.reject')}}</a>';
                            }
                        }

                        trhtml += '</td>';
                        trhtml += '</tr>';
                        html += trhtml;
                    });

                    html += '</tbody>';
                    html += '</table>';

                    // Append table to DOM
                    $(".doc-body").html(html);

                    // Initialize DataTable
                    $('#taxTable').DataTable({
                        order: [[0, 'asc']],
                        columnDefs: [
                            {orderable: false, targets: [1, 2]}
                        ]
                    });

                    jQuery("#data-table_processing").hide();
                } else {
                    console.error('❌ Failed to load driver document data');
                    jQuery("#data-table_processing").hide();
                    $(".doc-body").html('<p class="text-danger">Error loading driver documents. Please refresh the page.</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Error loading driver document data:', error);
                jQuery("#data-table_processing").hide();
                $(".doc-body").html('<p class="text-danger">Error loading driver documents: ' + error + '</p>');
            }
        });
    });

    // Handle approve/reject button clicks
    $(document).on('click', '.verify-doc', function () {
        jQuery("#data-table_processing").show();

        var status = $(this).attr('data-action'); // 'approved' or 'rejected'
        var docId = $(this).attr('data-id');
        var docTitle = $(this).attr('data-title');

        console.log('Updating document status:', { driverId: id, docId: docId, status: status });

        // Update via SQL API
        $.ajax({
            url: '{{ route("api.drivers.document.status", [":driverId", ":docId"]) }}'.replace(':driverId', id).replace(':docId', docId),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: {
                status: status,
                docTitle: docTitle
            },
            success: function(response) {
                console.log('✅ Document status updated:', response);

                // Log activity
                if (typeof logActivity === 'function') {
                    var action = status == 'approved' ? 'approved' : 'rejected';
                    logActivity('drivers', action + '_document', action.charAt(0).toUpperCase() + action.slice(1) + ' document "' + docTitle + '" for driver ID: ' + id);
                }

                jQuery("#data-table_processing").hide();
                window.location.reload();
            },
            error: function(xhr, status, error) {
                console.error('❌ Error updating document status:', error);
                jQuery("#data-table_processing").hide();
                alert('Error updating document status: ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : error));
            }
        });
    });
</script>
@endsection
