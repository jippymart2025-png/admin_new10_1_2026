@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor restaurantTitle">{{ trans('lang.subscription_plans') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.subscription_plans') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-items-center">
                            <span class="icon mr-3"><img src="{{ asset('images/subscription.png') }}"></span>
                            <h3 class="mb-0">{{ trans('lang.subscription_plans') }}</h3>
                            <span class="counter ml-3 total_count"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="overview-sec">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{trans("lang.overview")}}</h3>
                                <p class="mb-0 text-dark-2">{{trans("lang.see_overview_of_package_earning")}}</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row subscription-list">
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
                                <h3 class="text-dark-2 mb-2 h4">{{trans("lang.subscription_package_list")}}</h3>
                                <p class="mb-0 text-dark-2">{{trans("lang.manage_all_package_in_single_click")}}</p>
                            </div>
                            <div class="card-header-right d-flex align-items-center">
                                <div class="card-header-btn mr-3">
                                    <a href="{!! route('subscription-plans.save') !!}"
                                        class="btn-primary btn rounded-full"><i
                                            class="mdi mdi-plus mr-2"></i>{{ trans('lang.create_subscription_plan') }}</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="subscriptionPlansTable"
                                    class="display nowrap table table-hover table-striped table-bordered table table-striped dataTable no-footer dtr-inline collapsed"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <?php if (in_array('subscription-plans.delete', json_decode(@session('user_permissions'), true))) { ?>
                                            <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                    class="col-3 control-label" for="is_active">
                                                    <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i
                                                            class="mdi mdi-delete"></i>
                                                        {{ trans('lang.all') }}</a></label>
                                            </th>
                                            <?php } ?>
                                            <th>{{ trans('lang.plan_name') }}</th>
                                            <th>{{ trans('lang.plan_price') }}</th>
                                            <th>{{ trans('lang.duration') }}</th>
                                            <th>Commission</th>
                                            <th>Zone</th>
                                            <th>{{ trans('lang.current_subscriber') }}</th>
                                            <th>{{ trans('lang.status') }}</th>
                                            <th>{{ trans('lang.actions') }}</th>
                                        </tr>
                                    </thead>
                                </table>
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
<script type="text/javascript">
    // SQL mode - no Firebase
    console.log('Loading subscription plans from SQL database...');

    var user_permissions = '<?php echo @session('user_permissions'); ?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;
    if ($.inArray('subscription-plans.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }

    var currentCurrency = '₹';
    var currencyAtRight = false;
    var decimal_degits = 2;

    // Load currency from SQL
    $.get("{{ route('payments.currency') }}", function(response) {
        if (response.success && response.data) {
            currentCurrency = response.data.symbol;
            currencyAtRight = response.data.symbolAtRight;
            decimal_degits = response.data.decimal_degits;
        }
    });

    var placeholderImage = '';
    // Load placeholder image from SQL
    $.get("{{ route('api.placeholderimage.settings') }}", function(data) {
        placeholderImage = data.image || '';
    });

    $(document).ready(function() {
        $(document.body).on('click', '.redirecttopage', function() {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });

        jQuery("#data-table_processing").show();

        const table = $('#subscriptionPlansTable').DataTable({
            pageLength: 10,
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ route('subscription-plans.data') }}",
                type: 'GET',
                dataSrc: function(json) {
                    console.log('Subscription plans loaded:', json);
                    $('.total_count').text(json.recordsTotal);
                    return json.data;
                },
                error: function(xhr, error, code) {
                    console.error('Error loading subscription plans:', error);
                    console.error('Response:', xhr.responseText);
                    $('#data-table_processing').hide();
                }
            },
            order: (checkDeletePermission) ? [1, 'asc'] : [0, 'asc'],
            columnDefs: [{
                orderable: false,
                targets: (checkDeletePermission) ? [0, 5, 6] : [5, 6]
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

        // Load overview earnings
        $.get("{{ route('subscription-plans.overview') }}", function(plans) {
            var html = '';
            plans.forEach(function(plan) {
                var earnings = currencyAtRight
                    ? parseFloat(plan.earnings).toFixed(decimal_degits) + currentCurrency
                    : currentCurrency + parseFloat(plan.earnings).toFixed(decimal_degits);

                html += `
                    <div class="col-md-4">
                        <div class="card card-box-with-icon">
                            <div class="card-body">
                                <span class="box-icon"><img src="${plan.image || placeholderImage}"></span>
                                <div class="card-box-with-content mt-3">
                                    <h4 class="text-dark-2 mb-1 h4">${earnings}</h4>
                                    <p class="mb-0 text-dark-2">${plan.name}</p>
                                </div>
                                <span class="background-img"><img src="${plan.image || placeholderImage}"></span>
                            </div>
                        </div>
                    </div>`;
            });
            $('.subscription-list').append(html);
        });
    });

    // Toggle plan status - SQL version
    $(document).on("click", ".plan-toggle", function(e) {
        var isChecked = $(this).is(':checked');
        var id = $(this).data('id');

        $.ajax({
            url: "{{ url('subscription-plans') }}/" + id + "/toggle",
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                isEnable: isChecked
            },
            success: function(response) {
                if (response.success) {
                    console.log('Plan status updated');
                } else {
                    alert(response.message || 'Failed to update status');
                    location.reload();
                }
            },
            error: function(xhr) {
                if (xhr.status == 422) {
                    var response = JSON.parse(xhr.responseText);
                    alert(response.message || 'At least one subscription plan should be active');
                } else {
                    alert('Error updating plan status');
                }
                location.reload();
            }
        });
    });

    // Delete plan - SQL version
    $(document).on("click", ".delete-plan", function(e) {
        e.preventDefault();
        var id = $(this).data('id');

        if (!confirm('Are you sure you want to delete this subscription plan?')) {
            return;
        }

        jQuery("#data-table_processing").show();

        $.ajax({
            url: "{{ url('subscription-plans') }}/" + id,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    console.log('Plan deleted successfully');
                    window.location.reload();
                } else {
                    alert(response.message || 'Failed to delete plan');
                    jQuery("#data-table_processing").hide();
                }
            },
            error: function(xhr) {
                console.error('Delete error:', xhr.responseText);
                alert('Error deleting plan');
                jQuery("#data-table_processing").hide();
            }
        });
    });

    // Select all checkbox
    $("#is_active").click(function() {
        $("#subscriptionPlansTable .is_open").prop('checked', $(this).prop('checked'));
    });

    // Delete all selected
    $("#deleteAll").click(function() {
        if ($('#subscriptionPlansTable .is_open:checked').length) {
            if (confirm("{{ trans('lang.selected_delete_alert') }}")) {
                jQuery("#data-table_processing").show();

                var ids = [];
                $('#subscriptionPlansTable .is_open:checked').each(function() {
                    ids.push($(this).attr('dataId'));
                });

                // Delete each plan
                var deletePromises = ids.map(function(id) {
                    return $.ajax({
                        url: "{{ url('subscription-plans') }}/" + id,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                });

                Promise.all(deletePromises).then(function() {
                    window.location.reload();
                }).catch(function(error) {
                    console.error('Error deleting plans:', error);
                    alert('Some plans failed to delete');
                    window.location.reload();
                });
            }
        } else {
            alert("{{ trans('lang.select_delete_alert') }}");
        }
    });
</script>
@endsection

