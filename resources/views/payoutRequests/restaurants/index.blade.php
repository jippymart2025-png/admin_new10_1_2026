@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.payout_request')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.payout_request')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/payment.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.payout_request')}}</h3>
                        <span class="counter ml-3 total_count"></span>
                    </div>
                    <div class="d-flex top-title-right align-self-center">
                        <div class="select-box pl-3">
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
                    <div class="menu-tab">
                        <ul>
                            <li>
                                <a href="{{route('restaurants.view', $id)}}">{{trans('lang.tab_basic')}}</a>
                            </li>
                            <li>
                                <a href="{{route('restaurants.foods', $id)}}">{{trans('lang.tab_foods')}}</a>
                            </li>
                            <li>
                                <a href="{{route('restaurants.orders', $id)}}">{{trans('lang.tab_orders')}}</a>
                            </li>
                            <li>
                                <a href="{{route('restaurants.coupons', $id)}}">{{trans('lang.tab_promos')}}</a>
                            <li>
                                <a href="{{route('restaurants.payout', $id)}}">{{trans('lang.tab_payouts')}}</a>
                            </li>
                            <li class="active">
                                <a href="{{route('payoutRequests.restaurants.view', $id)}}">{{trans('lang.tab_payout_request')}}</a>
                            </li>
                            <li>
                                <a href="{{route('restaurants.booktable', $id)}}">{{trans('lang.dine_in_future')}}</a>
                            </li>
                            @if(!empty($vendor) && !empty($vendor->author))
                                <li>
                                    <a href="{{ route('users.walletstransaction', $vendor->author) }}">{{ trans('lang.wallet_transaction') }}</a>
                                </li>
                                <li>
                                    <a href="{{ route('vendor.subscriptionPlanHistory', $vendor->author) }}">{{ trans('lang.subscription_history') }}</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                <?php } ?>
               <div class="card border">
                 <div class="card-header d-flex justify-content-between align-items-center border-0">
                   <div class="card-header-title">
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.payout_request')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.payout_request_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3">
                        @if(request()->is('payoutRequests/drivers'))
                            <a class="btn-primary btn rounded-full" href="{!! url('payoutRequests/restaurants') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.restaurant_payout_request')}}</a>
                        @endif
                        @if ($id == '' && request()->is('payoutRequests/restaurants'))
                        <a class="btn-primary btn rounded-full" href="{!! url('payoutRequests/drivers') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.drivers_payout_request')}}</a>
                        @endif
                     </div>
                   </div>
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <div class="error_top" style="display:none"></div>
                            <div class="success_top" style="display:none"></div>
                            <table id="payoutRequestTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                        <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                        class="col-3 control-label" for="is_active"><a id="deleteAll"
                                                            class="do_not_delete" href="javascript:void(0)"><i
                                                                class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label>
                                                </th>
                                            <?php if ($id == "") { ?>
                                            <th>{{ trans('lang.vendor')}}</th>
                                            <?php } ?>
                                            <th>{{trans('lang.paid_amount')}}</th>
                                            <th>{{trans('lang.restaurants_payout_note')}}</th>
                                            <th>{{trans('lang.date')}}</th>
                                            <th>{{trans('lang.status')}}</th>
                                            <th>{{trans('lang.withdraw_method')}}</th>
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
    <div class="modal fade" id="bankdetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered location_modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title locationModalTitle">{{trans('lang.bankdetails')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="">
                        <div class="form-row">
                            <input type="hidden" name="vendorId" id="vendorId">
                            <div class="form-group row">
                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{trans('lang.bank_name')}}</label>
                                    <div class="col-12">
                                        <input type="text" name="bank_name" class="form-control" id="bankName">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{trans('lang.branch_name')}}</label>
                                    <div class="col-12">
                                        <input type="text" name="branch_name" class="form-control" id="branchName">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{trans('lang.holer_name')}}</label>
                                    <div class="col-12">
                                        <input type="text" name="holer_name" class="form-control" id="holderName">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{trans('lang.account_number')}}</label>
                                    <div class="col-12">
                                        <input type="text" name="account_number" class="form-control"
                                               id="accountNumber">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{trans('lang.other_information')}}</label>
                                    <div class="col-12">
                                        <input type="text" name="other_information" class="form-control"
                                               id="otherDetails">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary save-form-btn" id="submit_accept">
                            {{trans('lang.accept')}}</a>
                        </button>
                        <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                            {{trans('lang.close')}}</a>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="cancelRequestModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title locationModalTitle">{{trans('lang.cancel_payout_request')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="data-table_processing_modal" class="dataTables_processing panel panel-default"
                            style="display: none;">{{trans('lang.processing')}}
                    </div>
                    <form class="">
                        <div class="form-row">
                            <div class="form-group row">
                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{trans('lang.notes')}}</label>
                                    <div class="col-12">
                                        <textarea name="admin_note" class="form-control" id="admin_note" cols="5" rows="5"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary save-form-btn" id="submit_cancel">
                            {{trans('lang.submit')}}</a>
                        </button>
                        <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                            {{trans('lang.close')}}</a>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="payoutResponseModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{trans('lang.payout_response')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="payout-response"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                        {{trans('lang.close')}}</a>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
<script>
    const vendorId = '{{ $id }}';

    $(function () {
        const columns = [
            { data: 'checkbox', orderable: false, searchable: false }
        ];

        @if(empty($id))
            columns.push({ data: 'vendor', orderable: true, searchable: true });
        @endif

        columns.push(
            { data: 'amount', orderable: true, searchable: false },
            { data: 'note', orderable: true, searchable: true },
            { data: 'paidDate', orderable: true, searchable: false },
            { data: 'status', orderable: true, searchable: true },
            { data: 'withdrawMethod', orderable: true, searchable: true },
            { data: 'actions', orderable: false, searchable: false }
        );

        const defaultOrder = vendorId ? [[3, 'desc']] : [[4, 'desc']];

        $('#payoutRequestTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            order: defaultOrder,
            ajax: {
                url: '{{ route('payoutRequests.restaurants.data') }}',
                data: function (d) {
                    d.vendor_id = vendorId;
                },
                dataSrc: function (json) {
                    $('.total_count').text(json.recordsTotal || 0);
                    return json.data;
                },
                error: function (xhr) {
                    console.error('Failed to load restaurant payout requests:', xhr.responseText);
                }
            },
            columns: columns,
            columnDefs: [
                { targets: '_all', defaultContent: '-' }
            ],
            language: {
                zeroRecords: "{{ trans('lang.no_record_found') }}",
                emptyTable: "{{ trans('lang.no_record_found') }}",
                processing: "{{ trans('lang.processing') }}"
            },
            dom: 'lfrtip'
        });

        $('.dataTables_filter input')
            .attr('placeholder', 'Search here...')
            .attr('autocomplete', 'off');
    });
</script>
@endsection
