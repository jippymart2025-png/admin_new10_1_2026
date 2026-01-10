@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor restaurantTitle">Master Products</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item active">Master Products</li>
                </ol>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="container-fluid">
            <div class="admin-top-section">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex top-title-section pb-4 justify-content-between">
                            <div class="d-flex top-title-left align-self-center">
                                <span class="icon mr-3"><img src="{{ asset('images/food.png') }}"></span>
                                <h3 class="mb-0">{{trans('lang.food_master_table')}}</h3>
                                <span class="counter ml-3 product_count"></span>
                            </div>
                            <div class="d-flex top-title-right align-self-center">
                                <div class="select-box pl-3" style="width: 180px;">
                                    <select class="form-control food_type_selector">
                                        <option value="" selected>{{trans("lang.type")}}</option>
                                        <option value="veg">{{trans("lang.veg")}}</option>
                                        <option value="non-veg">{{trans("lang.non_veg")}}</option>
                                    </select>
                                </div>
                                <div class="select-box pl-3" style="width: 180px;">
                                    <select class="form-control category_selector">
                                        <option value="" selected>{{trans("lang.category_plural")}}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bulk Import Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">Bulk Import Master Products</h3>
                                <p class="mb-0 text-dark-2">Upload Excel file to import multiple master products at once</p>
                                <small class="text-info">
                                    <i class="mdi mdi-lightbulb-outline mr-1"></i>
                                    <strong>Tip:</strong> You can use category names instead of IDs for easier data entry!
                                </small>
                            </div>
                            <div class="card-header-right d-flex align-items-center">
                                <div class="card-header-btn mr-3">
                                    <a href="{{ route('master-products.download-template') }}" class="btn btn-outline-primary rounded-full">
                                        <i class="mdi mdi-download mr-2"></i>Download Template
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('master-products.import') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="importFile" class="control-label">Select Excel File (.xls/.xlsx)</label>
                                            <input type="file" name="file" id="importFile" accept=".xls,.xlsx" class="form-control" required>
                                            <div class="form-text text-muted">
                                                <i class="mdi mdi-information-outline mr-1"></i>
                                                File should contain: name, suggested_price, description, categoryID, dis_price, publish, nonveg, isAvailable, photo
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary rounded-full">
                                            <i class="mdi mdi-upload mr-2"></i>Import Master Products
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-list">
                <div class="row">
                    <div class="col-12">
                        <?php if (!empty($restaurantId)) { ?>
                        <div class="menu-tab">
                            <ul>
                                <li>
                                    <a href="{{route('restaurants.view', $restaurantId)}}">{{trans('lang.tab_basic')}}</a>
                                </li>
                                <li class="active">
                                    <a href="{{route('restaurants.foods', $restaurantId)}}">{{trans('lang.tab_foods')}}</a>
                                </li>
                                <li>
                                    <a href="{{route('restaurants.orders', $restaurantId)}}">{{trans('lang.tab_orders')}}</a>
                                </li>
                                <li>
                                    <a href="{{route('restaurants.coupons', $restaurantId)}}">{{trans('lang.tab_promos')}}</a>
                                <li>
                                    <a href="{{route('restaurants.payout', $restaurantId)}}">{{trans('lang.tab_payouts')}}</a>
                                </li>
                                <li>
                                    <a
                                        href="{{route('payoutRequests.restaurants.view', $restaurantId)}}">{{trans('lang.tab_payout_request')}}</a>
                                </li>
                                <li>
                                    <a href="{{route('restaurants.booktable', $restaurantId)}}">{{trans('lang.dine_in_future')}}</a>
                                </li>
                                <li id="restaurant_wallet"></li>
                                <li id="subscription_plan"></li>
                            </ul>
                        </div>
                        <?php } ?>
                        <div class="card-header bg-white d-flex justify-content-between align-items-center border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">Master Products</h3>
                                <p class="mb-0 text-dark-2">Manage master product catalog</p>
                            </div>
                            <div class="card-header-btn mr-3 text-right">
                                <a class="btn-primary btn rounded-full" href="{!! route('master-products.create') !!}"><i
                                        class="mdi mdi-plus mr-2"></i>Add Master Product</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="masterProductsTable"
                                       class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                       cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <?php if (in_array('foods.delete', json_decode(@session('user_permissions'), true))) { ?>
                                        <th class="delete-all"><input type="checkbox" id="select-all">
                                            <label class="col-3 control-label" for="select-all">
                                                <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i
                                                        class="mdi mdi-delete"></i> {{trans('lang.all')}}</a>
                                            </label>
                                        </th>
                                        <?php } ?>
                                        <th>Product Name</th>
                                        <th>suggested Price</th>
                                        <th>Discount Price</th>
                                        <th>Category</th>
                                        <th>Publish</th>
                                        <th>Available</th>
                                        <th>{{trans('lang.actions')}}</th>
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
@endsection
@section('scripts')
    <script type="text/javascript">
        const urlParams=new URLSearchParams(location.search);
        var categoryID = '';
        for(const [key,value] of urlParams) {
            if(key=='categoryID') {
                categoryID=value;
            }
        }
        var currentCurrency='$';
        var currencyAtRight=false;
        var decimal_degits=0;
        var user_permissions='<?php echo @session("user_permissions") ?>';
        user_permissions=Object.values(JSON.parse(user_permissions));
        var checkDeletePermission=false;
        if($.inArray('foods.delete',user_permissions)>=0) {
            checkDeletePermission=true;
        }
        var placeholderImage='{{ asset('images/placeholder.png') }}';

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        function applyCurrencySettings(data) {
            if (!data) {
                return;
            }
            currentCurrency = data.symbol || data.currency_symbol || '$';
            currencyAtRight = data.symbolAtRight ?? data.currencyAtRight ?? false;
            decimal_degits = data.decimal_degits ?? 0;
        }

        function loadCurrencyFromSettingsFallback() {
            $.ajax({
                url: '{{ route("settings.get", "payment") }}',
                type: 'GET',
                async: false,
                success: function(response) {
                    if(response.success && response.data) {
                        applyCurrencySettings(response.data);
                    }
                }
            });
        }

        $.ajax({
            url: '{{ route("api.currencies.active") }}',
            type: 'GET',
            async: false,
            success: function(response) {
                if(response.success && response.data) {
                    applyCurrencySettings(response.data);
                } else {
                    loadCurrencyFromSettingsFallback();
                }
            },
            error: function() {
                loadCurrencyFromSettingsFallback();
            }
        });

        // Load categories for filter dropdown
        $.ajax({
            url: '{{ route("master-products.options") }}?type=categories',
            type: 'GET',
            success: function(response) {
                if(response.success && response.data) {
                    response.data.forEach(function(category) {
                        if(category.title && category.title !== '') {
                            $('.category_selector').append($("<option></option>")
                                .attr("value", category.id)
                                .text(category.title));
                        }
                    });
                }
            }
        });


        $(document).ready(function() {
            $('.food_type_selector').select2({
                placeholder: "{{trans('lang.type')}}",
                minimumResultsForSearch: Infinity,
                allowClear: true
            });
            $('.category_selector').select2({
                placeholder: "{{trans('lang.category')}}",
                minimumResultsForSearch: Infinity,
                allowClear: true,
                dropdownAutoWidth: false,
                width: '100%'
            });
            // Force dropdown list width to match selector width
            $('.category_selector').on('select2:open', function() {
                var $dropdown = $('.select2-container--open .select2-dropdown');
                var $selector = $(this).closest('.select-box');
                if ($selector.length && $dropdown.length) {
                    $dropdown.css('width', $selector.outerWidth() + 'px');
                }
            });

            $('select').on("select2:unselecting", function(e) {
                var self = $(this);
                setTimeout(function() {
                    self.select2('close');
                }, 0);
            });

            $('.category_selector, .food_type_selector').change(async function() {
                $('#masterProductsTable').DataTable().ajax.reload();
            });

            jQuery("#data-table_processing").show();

            const table=$('#masterProductsTable').DataTable({
                pageLength: 30,
                lengthMenu: [[10,30, 50, 100], [10,30, 50, 100,]],
                processing: false,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route("master-products.data") }}',
                    type: 'GET',
                    data: function(d) {
                        d.category = $('.category_selector').val();
                        d.foodType = $('.food_type_selector').val();
                        d.categoryId = categoryID;
                    },
                    dataSrc: function(json) {
                        $('#data-table_processing').hide();
                        $('.product_count').text(json.recordsFiltered);
                        return json.data;
                    }
                },
                order: (checkDeletePermission)? [1,'asc']:[0,'asc'],
                columns: [
                        @if(in_array('foods.delete', json_decode(@session('user_permissions'), true)))
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            return '<td class="delete-all"><input type="checkbox" id="is_open_'+row.id+'" class="is_open" dataId="'+row.id+'"><label class="col-3 control-label" for="is_open_'+row.id+'"></label></td>';
                        }
                    },
                        @endif
                    {
                        data: 'name',
                        render: function(data, type, row) {
                            var imageHtml = '';
                            if(row.photo && row.photo != '') {
                                imageHtml = '<img onerror="this.onerror=null;this.src=\''+placeholderImage+'\'" class="rounded" style="width:70px;height:70px;object-fit:cover;" src="'+row.photo+'" alt="image">';
                            } else {
                                imageHtml = '<img style="width:70px;height:70px;" src="'+placeholderImage+'" alt="image">';
                            }
                            var foodType = row.nonveg ? '<span class="badge badge-danger ml-2">Non-Veg</span>' : '<span class="badge badge-success ml-2">Veg</span>';
                            return '<div class="d-flex align-items-center">' + imageHtml + '<div class="ml-3">' + data + foodType + '</div></div>';
                        }
                    },
                    // {
                    //     data: 'price',
                    //     render: function(data, type, row) {
                    //         if (data == null || data == '' || data == '0') {
                    //             return '-';
                    //         }
                    //         var price = parseFloat(data).toFixed(decimal_degits);
                    //         return currencyAtRight ? price + ' ' + currentCurrency : currentCurrency + ' ' + price;
                    //     }
                    // },
                    // {
                    //     data: 'price',
                    //     render: function(data, type, row) {
                    //
                    //         let price = parseFloat(data).toFixed(decimal_degits);
                    //         let formatted = currencyAtRight ? price + ' ' + currentCurrency : currentCurrency + ' ' + price;
                    //
                    //         // ADD DISCOUNT BADGE HERE ⬇⬇⬇
                    //         if (row.discount && row.discount > 0) {
                    //             formatted += ` <span class="badge badge-success ml-2">-${row.discount}%</span>`;
                    //         }
                    //
                    //         return formatted;
                    //     }
                    // },
                    {
                        data: 'suggested_price',
                        render: function (data, type, row) {
                            if (data == null || data === '' || data == '0') {
                                return `<span class="editable-suggested-price text-muted"
                        data-id="${row.id}"
                        data-value="0">-</span>`;
                            }

                            let price = parseFloat(data).toFixed(decimal_degits);
                            let formatted = currencyAtRight
                                ? price + ' ' + currentCurrency
                                : currentCurrency + ' ' + price;

                            return `
            <span class="editable-suggested-price text-primary cursor-pointer"
                  data-id="${row.id}"
                  data-value="${data}">
                ${formatted}
            </span>
        `;
                        }
                    },

                    {
                        data: 'dis_price',
                        render: function(data, type, row) {
                            if (data == null || data == '' || data == '0') {
                                return '-';
                            }
                            var price = parseFloat(data).toFixed(decimal_degits);
                            return currencyAtRight ? price + ' ' + currentCurrency : currentCurrency + ' ' + price;
                        }
                    },
                    {
                        data: 'categoryTitle',
                        render: function(data, type, row) {
                            return data || row.category_name || '-';
                        }
                    },
                    {
                        data: 'publish',
                        orderable: false,
                        render: function(data, type, row) {
                            var checked = data ? 'checked' : '';
                            return '<label class="switch"><input type="checkbox" ' + checked + ' id="publish_'+row.id+'" data-id="'+row.id+'"><span class="slider round"></span></label>';
                        }
                    },
                    {
                        data: 'isAvailable',
                        orderable: false,
                        render: function(data, type, row) {
                            var checked = data ? 'checked' : '';
                            return '<label class="switch"><input type="checkbox" ' + checked + ' id="available_'+row.id+'" data-id="'+row.id+'"><span class="slider round"></span></label>';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            var route1 = '{{route("master-products.edit", ":id")}}'.replace(':id', row.id);
                            var actions = '<span class="action-btn"><a href="'+route1+'"><i class="mdi mdi-pencil font-18"></i></a></span>';
                            @if(in_array('foods.delete', json_decode(@session('user_permissions'), true)))
                                actions += '<span class="action-btn"><a href="javascript:void(0)" class="text-danger delete-product" data-id="'+row.id+'"><i class="mdi mdi-delete font-18"></i></a></span>';
                            @endif
                                return actions;
                        }
                    }
                ],
                columnDefs: [
                    {
                        orderable: false,
                        targets: checkDeletePermission ? [0,5,6,7] : [4,5,6]
                    }
                ],
                "language": {
                    "zeroRecords": "{{trans("lang.no_record_found")}}",
                    "emptyTable": "{{trans("lang.no_record_found")}}",
                    "processing": ""
                },
                dom: 'lfrtipB',
                buttons: [
                    {
                        extend: 'collection',
                        text: '<i class="mdi mdi-cloud-download"></i> Export as',
                        className: 'btn btn-info',
                        buttons: ['csv', 'excel', 'pdf']
                    }
                ],
                initComplete: function() {
                    $(".dataTables_filter").append($(".dt-buttons").detach());
                    $('.dataTables_filter input').attr('placeholder', 'Search here...').attr('autocomplete','new-password');
                    $('.dataTables_filter label').contents().filter(function() {
                        return this.nodeType === 3;
                    }).remove();
                }
            });

            // Handle publish toggle
            $(document).on('change', 'input[id^="publish_"]', function() {
                var id = $(this).data('id');
                var isPublish = $(this).is(':checked');

                $.ajax({
                    url: '{{ route("master-products.togglePublish", ":id") }}'.replace(':id', id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        publish: isPublish
                    },
                    success: function(response) {
                        if(response.success) {
                            console.log('Publish status updated');
                        }
                    },
                    error: function() {
                        console.error('Error updating publish status');
                        // Revert checkbox
                        $('input[id="publish_'+id+'"]').prop('checked', !isPublish);
                    }
                });
            });

            // Handle available toggle
            $(document).on('change', 'input[id^="available_"]', function () {
                var id = $(this).data('id');
                var isAvailable = $(this).is(':checked');

                $.ajax({
                    url: '{{ route("master-products.toggleAvailable", ":id") }}'.replace(':id', id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        isAvailable: isAvailable
                    },
                    success: function (response) {
                        if (response.success) {
                            console.log('Available status updated');
                        }
                    },
                    error: function () {
                        console.error('Error updating available status');
                        // revert toggle if error
                        $('#available_' + id).prop('checked', !isAvailable);
                    }
                });
            });
            $(document).on('mousedown', '.editable-suggested-price', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const $this = $(this);

                // Prevent double input
                if ($this.data('editing')) return;
                $this.data('editing', true);

                const id = $this.data('id');
                const currentValue = parseFloat($this.data('value')) || 0;

                const input = $('<input>', {
                    type: 'number',
                    step: '0.01',
                    min: 0,
                    class: 'form-control form-control-sm',
                    value: currentValue,
                    css: { width: '90px', display: 'inline-block' }
                });

                $this.hide().after(input);
                input.focus().select();

                function cleanup() {
                    input.remove();
                    $this.show();
                    $this.data('editing', false);
                }

                function saveValue() {
                    let newValue = parseFloat(input.val());
                    if (isNaN(newValue) || newValue < 0) newValue = 0;

                    $this.text('Updating...').addClass('text-info');
                    cleanup();

                    $.ajax({
                        url: '{{ route("master-products.inlineUpdateSuggestedPrice", ":id") }}'.replace(':id', id),
                        method: 'PATCH',
                        data: {
                            suggested_price: newValue,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (res) {
                            if (res.success) {
                                $this
                                    .data('value', res.value)
                                    .text(res.formatted)
                                    .removeClass('text-info')
                                    .addClass('text-success');

                                setTimeout(() => $this.removeClass('text-success'), 800);
                            } else {
                                alert(res.message || 'Update failed');
                                $this.text(currentValue.toFixed(2));
                            }
                        },
                        error: function () {
                            alert('Update failed');
                            $this.text(currentValue.toFixed(2));
                        }
                    });
                }

                input.on('blur', saveValue);
                input.on('keydown', function (e) {
                    if (e.which === 13) saveValue();   // Enter
                    if (e.which === 27) cleanup();     // Esc
                });
            });

            // Select all checkboxes
            $("#select-all").click(function() {
                $(".is_open").prop('checked', $(this).prop('checked'));
            });

            // Delete selected items
            $("#deleteAll").click(function() {
                if ($('.is_open:checked').length == 0) {
                    alert("{{trans('lang.select_delete_alert')}}");
                    return false;
                }
                if(!confirm("{{trans('lang.delete_alert')}}")){
                    return false;
                }

                var selectedIds = [];
                $('.is_open:checked').each(function() {
                    selectedIds.push($(this).attr('dataId'));
                });

                jQuery("#data-table_processing").show();

                // Delete via AJAX (you'll need to create a batch delete endpoint)
                $.ajax({
                    url: '{{ route('master-products.delete-multiple') }}',
                    type: 'POST',
                    data: {
                        ids: selectedIds
                    },
                    success: function(response) {
                        jQuery("#data-table_processing").hide();
                        if(response.success) {
                            alert('{{trans("lang.delete_success")}}');
                            table.ajax.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        jQuery("#data-table_processing").hide();
                        alert('{{trans("lang.error_deleting")}}');
                    }
                });
            });

            $(document).on('click', '.delete-product', function() {
                var id = $(this).data('id');
                if(!confirm("{{ trans('lang.delete_alert') }}")) {
                    return;
                }

                $.ajax({
                    url: '{{ route("master-products.delete", ":id") }}'.replace(':id', id),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        table.ajax.reload();
                    },
                    error: function() {
                        alert('{{trans("lang.error_deleting")}}');
                    }
                });
            });
        });
    </script>
@endsection
