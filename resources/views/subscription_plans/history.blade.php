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
                        <div class="d-flex top-title-right align-self-center">
                            <div class="select-box pl-3">
                                <select class="form-control business_model_selector">
                                    <option value="" disabled selected>{{trans('lang.business_model')}}</option>
                                </select>
                            </div>
                            <div class="select-box pl-3">
                                <select class="form-control zone_selector">
                                    <option value="" selected>{{trans('lang.select_zone')}}</option>
                                </select>
                            </div>
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
                            <div class="card-header-right d-flex align-items-center">
                                <div class="card-header-btn mr-3">
                                    <!-- <a class="btn-primary btn rounded-full" href="{!! route('users.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.user_create')}}</a> -->
                                </div>

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
                                            <th>Zone Management</th>
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
    var userId = '{{$id}}';

    $(document).ready(function() {
        jQuery("#data-table_processing").show();

        var dataUrl = userId
            ? "{{ url('/vendor/subscription-plan/history') }}/" + userId + "/data"
            : "{{ route('vendor.subscriptionPlanHistory.data.all') }}";

        const table = $('#subscriptionHistoryTable').DataTable({
            pageLength: 30,
            lengthMenu: [[10,30, 50, 100], [10,30, 50, 100,]],
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: dataUrl,
                type: 'GET',
                data: function (d) {
                    d.business_model = window.selectedBusinessModel || '';
                    d.zone = window.selectedZone || '';
                },
                dataSrc: function(json) {
                    jQuery("#data-table_processing").hide();
                    if (json.data) {
                        $('.total_count').text(json.recordsTotal);
                        return json.data;
                    }
                    return [];
                },
                error: function(xhr, error, code) {
                    jQuery("#data-table_processing").hide();
                    console.error('Error loading subscription history:', error);
                }
            },
            order: [[<?php echo $id == '' ? 1 : 0; ?>, 'desc']],
            columns: [
                { data: 0, orderable: false }, // Checkbox
                <?php if ($id == '') { ?>
                {
                    data: 1,
                    render: function(data, type, row) {
                        // Render as HTML for display, plain text for other types
                        if (type === 'display') {
                            return data;
                        }
                        return data;
                    }
                }, // Vendor name (with link)
                <?php } ?>
                { data: <?php echo $id == '' ? 2 : 1; ?> }, // Plan name
                { data: <?php echo $id == '' ? 3 : 2; ?> }, // Plan type
                { data: <?php echo $id == '' ? 4 : 3; ?> }, // Zone
                { data: <?php echo $id == '' ? 5 : 4; ?> }, // Expires at
                { data: <?php echo $id == '' ? 6 : 5; ?> }  // Purchase date
            ],
            language: {
                "zeroRecords": "{{ trans('lang.no_record_found') }}",
                "emptyTable": "{{ trans('lang.no_record_found') }}",
                "processing": ""
            }
        });

        $('.business_model_selector').on('change', function () {
            window.selectedBusinessModel = $(this).val() || '';
            $('#subscriptionHistoryTable').DataTable().ajax.reload();
        });

        $('.zone_selector').on('change', function () {
            window.selectedZone = $(this).val() || '';
            $('#subscriptionHistoryTable').DataTable().ajax.reload();
        });

        $('#clearFilters').click(function () {
            window.selectedBusinessModel = '';
            window.selectedZone = '';

            $('.business_model_selector').val(null).trigger('change');
            $('.zone_selector').val(null).trigger('change');
        });

        // Load subscription plans from SQL
        $.ajax({
            url: '/subscription-plans/fetch',
            method: 'GET',
            success: function(response) {
                console.log('Loading subscription plans from SQL');
                if (response.data && response.data.length > 0) {
                    response.data.forEach(function(plan) {
                        if (plan.isEnable) {
                            $('.business_model_selector').append($("<option>").attr("value", plan.id).text(plan.name));
                        }
                    });
                }
            },
            error: function(error) {
                console.error('Error loading subscription plans from SQL:', error);
            }
        });

        var zoneIdToName = {}; // Map zone IDs to names
        var zonesLoaded = false;

        // Load zones from SQL and create mapping
        var loadZonesPromise = new Promise(function(resolve){
            console.log('🔄 Loading zones from SQL for restaurants...');
            $.ajax({
                url: '{{ route("zone.data") }}',
                method: 'GET',
                success: function(response) {
                    console.log('📊 Zones API response:', response);
                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function(zone) {
                            console.log('Zone found:', zone.name, 'ID:', zone.id);
                            // Store zone ID to name mapping
                            zoneIdToName[zone.id] = zone.name;
                            // Add zone to selector
                            $('.zone_selector').append($("<option></option>")
                                .attr("value", zone.id)
                                .text(zone.name));
                        });
                        console.log('✅ Zones loaded from SQL (' + response.data.length + ' zones):', zoneIdToName);
                    } else {
                        console.warn('⚠️ No zones found in database');
                    }
                    // Enable the zone selector after zones are loaded
                    $('.zone_selector').prop('disabled', false);
                    zonesLoaded = true;
                    resolve(zoneIdToName);
                },
                error: function(error) {
                    console.error('❌ Error loading zones from SQL:', error);
                    $('.zone_selector').prop('disabled', false);
                    zonesLoaded = true;
                    resolve(zoneIdToName);
                }
            });
        });
        $('.business_model_selector').select2({
            placeholder: "{{trans('lang.business_model')}}",
            minimumResultsForSearch: Infinity,
            allowClear: true
        });
        $('.zone_selector').select2({
            placeholder: "{{trans('lang.select_zone')}}",
            minimumResultsForSearch: Infinity,
            allowClear: true
        });

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = function() {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        $('#search-input').keyup(debounce(function() {
            table.search($(this).val()).draw();
        }, 300));

        <?php if ($id != '') { ?>
        $('.menu-tab').show();
        <?php } ?>
    });

    $("#is_active").click(function() {
        $("#subscriptionHistoryTable .is_open").prop('checked', $(this).prop('checked'));
    });

    $("#deleteAll").click(function() {
        if ($('#subscriptionHistoryTable .is_open:checked').length) {
            if (confirm("{{ trans('lang.selected_delete_alert') }}")) {
                jQuery("#data-table_processing").show();
                // Bulk delete not implemented for history
                alert('{{ trans("lang.delete_not_allowed") }}');
                jQuery("#data-table_processing").hide();
            }
        } else {
            alert("{{ trans('lang.select_delete_alert') }}");
        }
    });
</script>
@endsection

