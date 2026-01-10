@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.item_attribute_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.attribute_table')}}</li>
            </ol>
        </div>
        <div>
        </div>
    </div>
    <div class="container-fluid">
       <div class="admin-top-section">
        <div class="row">
            <div class="col-12">
                <div class="d-flex top-title-section pb-4 justify-content-between">
                    <div class="d-flex top-title-left align-self-center">
                        <span class="icon mr-3"><img src="{{ asset('images/attribute.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.item_attribute_plural')}}</h3>
                        <span class="counter ml-3 attribute_count"></span>
                    </div>
                    <div class="d-flex top-title-right align-self-center">
                        <div class="select-box pl-3">
                            <select class="form-control restaurant_type_selector d-none">
                                <option value="">Sample Selection</option>
                                <option value="true">Sample</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row d-none">
            <div class="col-12">
                <div class="card border">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card card-box-with-icon">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                       <div class="card-box-with-content">
                                        <h4 class="text-dark-2 mb-1 h4 rest_count">00</h4>
                                        <p class="mb-0 small text-dark-2">Sample Block</p>
                                       </div>
                                        <span class="box-icon ab"><img src="{{ asset('images/restaurant_icon.png') }}"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-box-with-icon">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                       <div class="card-box-with-content">
                                        <h4 class="text-dark-2 mb-1 h4 rest_active_count">00</h4>
                                        <p class="mb-0 small text-dark-2">Active restaurants</p>
                                       </div>
                                        <span class="box-icon ab"><img src="{{ asset('images/active_restaurant.png') }}"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
       </div>
       <div class="table-list">
       <div class="row">
           <div class="col-12">
               <div class="card border">
                 <div class="card-header d-flex justify-content-between align-items-center border-0">
                   <div class="card-header-title">
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.attribute_table')}}</h3>
                    <p class="mb-0 text-dark-2">View and manage all the attribute</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3">
                        <a class="btn-primary btn rounded-full" href="{!! route('attributes.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.attribute_create')}}</a>
                     </div>
                   </div>
                 </div>
                 <div class="card-body">
                        <div class="table-responsive m-t-10">
                            <table id="attributeTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>{{trans('lang.attribute_name')}}</th>
                                        <th>{{trans('lang.actions')}}</th>
                                    </tr>
                                </thead>
                                <tbody id="append_list1">
                                </tbody>
                            </table>
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
<script type="text/javascript">
    // SQL mode - no Firebase
    var user_permissions = '<?php echo @session("user_permissions") ?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;
    if ($.inArray('attributes.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }

    $(document).ready(function () {
        console.log('Loading attributes from SQL database...');
        jQuery("#data-table_processing").show();

        const table = $('#attributeTable').DataTable({
            pageLength: 30,
            lengthMenu: [[10, 25, 30, 50, 100, -1], [10, 25, 30, 50, 100, "All"]],
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ route('attributes.data') }}",
                type: 'GET',
                data: function (d) {
                    console.log('📡 Fetching attributes:', d);
                },
                dataSrc: function(json) {
                    console.log('📥 Attributes response:', json);

                    // Update count display
                    if (json.stats && json.stats.total) {
                        $('.attribute_count').text(json.stats.total);
                        console.log('📊 Total attributes:', json.stats.total);
                    } else {
                        $('.attribute_count').text(json.recordsTotal || 0);
                    }

                    return json.data;
                },
                error: function(xhr, error, code) {
                    console.error('❌ DataTables error:', error, code);
                    console.error('Response:', xhr.responseText);
                    $('#data-table_processing').hide();
                }
            },
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: [1] },
            ],
            language: {
                "zeroRecords": "{{trans("lang.no_record_found")}}",
                "emptyTable": "{{trans("lang.no_record_found")}}",
                "processing": ""
            },
            drawCallback: function() {
                $('#data-table_processing').hide();
            }
        });

        table.columns.adjust().draw();

        function debounce(func, wait) {
            let timeout;
            const context = this;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        $('#search-input').on('input', debounce(function () {
            const searchValue = $(this).val();
            if (searchValue.length >= 3 || searchValue.length === 0) {
                $('#data-table_processing').show();
                table.search(searchValue).draw();
            }
        }, 300));
    });

    // Delete attribute - SQL version (Fixed to match class name)
    $(document).on("click", ".delete-attribute", function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var attributeName = $(this).closest('tr').find('a').text().trim() || 'Unknown';

        console.log('🗑️ Delete attribute clicked:', { id: id, name: attributeName });

        if (!confirm('Are you sure you want to delete this attribute?')) {
            return;
        }

        jQuery("#data-table_processing").show();

        $.ajax({
            url: "{{ url('attributes') }}/" + id,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('✅ Attribute deleted successfully:', response);

                // Log activity
                if (typeof logActivity === 'function') {
                    logActivity('attributes', 'deleted', 'Deleted attribute: ' + attributeName);
                }

                jQuery("#data-table_processing").hide();
                $('#attributeTable').DataTable().ajax.reload();

                if (typeof toastr !== 'undefined') {
                    toastr.success(response.message || 'Attribute deleted successfully');
                } else {
                    alert('Attribute deleted successfully');
                }
            },
            error: function(xhr) {
                console.error('❌ Delete error:', xhr);
                jQuery("#data-table_processing").hide();

                var errorMsg = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Error deleting attribute: ' + xhr.statusText;

                alert(errorMsg);
            }
        });
    });
</script>
@endsection
