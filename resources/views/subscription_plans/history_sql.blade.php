@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.vendor_subscription_history_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.subscription_history_table')}}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">
            {{trans('lang.processing')}}</div>

        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><img src="{{ asset('images/subscription.png') }}"></span>
                            <h3 class="mb-0">{{trans('lang.vendor_subscription_history_plural')}}</h3>
                            <span class="counter ml-3 total_count"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <?php if ($id != '') { ?>
                    <div class="menu-tab" style="display:none">
                        <ul>
                            <li id="basic_tab"></li>
                            <li id="food_tab"> </li>
                            <li id="order_tab"></li>
                            <li id="promos_tab"></li>
                            <li id="payout_tab"></li>
                            <li id="payout_request"></li>
                            <li id="dine_in"></li>
                            <li id="restaurant_wallet"></li>
                            <li class="active" id="subscription_plan"></li>
                        </ul>
                    </div>
                    <?php } ?>
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{trans('lang.subscription_history_table')}}</h3>
                                <p class="mb-0 text-dark-2">{{trans('lang.subscription_history_table_text')}}</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="subscriptionHistoryTable"
                                    class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                    class="col-3 control-label" for="is_active"><a id="deleteAll"
                                                        class="do_not_delete" href="javascript:void(0)"><i
                                                            class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label>
                                            </th>
                                            <?php if ($id == '') { ?>
                                            <th>{{ trans('lang.vendor')}}</th>
                                            <?php } ?>
                                            <th>{{trans('lang.plan_name')}}</th>
                                            <th>{{trans('lang.plan_type')}}</th>
                                            <th>{{trans('lang.plan_expires_at')}}</th>
                                            <th>{{trans('lang.purchase_date')}}</th>
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
<script>
    // SQL mode - no Firebase
    console.log('Loading subscription history from SQL database...');
    
    var userId = '{{$id}}';
    
    $(document).ready(function() {
        jQuery("#data-table_processing").show();
        
        var dataUrl = userId 
            ? "{{ route('vendor.subscriptionPlanHistory.data', ':id') }}".replace(':id', userId)
            : "{{ route('vendor.subscriptionPlanHistory.data', '') }}";
        
        const table = $('#subscriptionHistoryTable').DataTable({
            pageLength: 10,
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: dataUrl,
                type: 'GET',
                dataSrc: function(json) {
                    console.log('Subscription history loaded:', json);
                    $('.total_count').text(json.recordsTotal);
                    return json.data;
                },
                error: function(xhr, error, code) {
                    console.error('Error loading subscription history:', error);
                    console.error('Response:', xhr.responseText);
                    $('#data-table_processing').hide();
                }
            },
            order: [[<?php echo $id == '' ? 1 : 0; ?>, 'desc']],
            columnDefs: [{
                orderable: false,
                targets: [0]
            }],
            language: {
                "zeroRecords": "{{ trans('lang.no_record_found') }}",
                "emptyTable": "{{ trans('lang.no_record_found') }}",
                "processing": ""
            },
            drawCallback: function() {
                $('#data-table_processing').hide();
            }
        });
        
        function debounce(func, wait) {
            let timeout;
            const context = this;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }
        
        $('#search-input').on('input', debounce(function() {
            const searchValue = $(this).val();
            if (searchValue.length >= 3 || searchValue.length === 0) {
                $('#data-table_processing').show();
                table.search(searchValue).draw();
            }
        }, 300));
        
        // Setup menu tabs if vendor ID is provided
        <?php if ($id != '') { ?>
        setupVendorMenuTabs('{{$id}}');
        <?php } ?>
    });
    
    // Select all checkbox
    $("#is_active").click(function() {
        $("#subscriptionHistoryTable .is_open").prop('checked', $(this).prop('checked'));
    });
    
    // Delete all selected (if needed in the future)
    $("#deleteAll").click(function() {
        if ($('#subscriptionHistoryTable .is_open:checked').length) {
            if (confirm("{{ trans('lang.selected_delete_alert') }}")) {
                // Implement bulk delete if needed
                alert('Bulk delete for subscription history not implemented yet');
            }
        } else {
            alert("{{ trans('lang.select_delete_alert') }}");
        }
    });
</script>
@endsection

