@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.currency_table')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.currency_table')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/currency.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.currency_table')}}</h3>
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
               <div class="card border">
                 <div class="card-header d-flex justify-content-between align-items-center border-0">
                   <div class="card-header-title">
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.currency_table')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.currency_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                        <a class="btn-primary btn rounded-full" href="{!! route('currencies.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.currency_create')}}</a>
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                         <table id="currenciesTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <?php if (in_array('currency.delete', json_decode(@session('user_permissions'),true))) { ?>
                                    <th class="delete-all">
                                        <input type="checkbox" id="select-all">
                                        <label class="col-3 control-label" for="select-all">
                                            <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a>
                                        </label>
                                    </th>
                                    <?php } ?>
                                    <th>{{trans('lang.currency_name')}}</th>
                                    <th>{{trans('lang.currency_symbol')}}</th>
                                    <th>{{trans('lang.currency_code')}}</th>
                                    <th>{{trans('lang.symbole_at_right')}}</th>
                                    <th>{{trans('lang.active')}}</th>
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
    const currenciesDataUrl = "{{ route('currencies.data') }}";
    const currencyToggleUrlBase = "{{ url('settings/currencies') }}";
    const currencyDeleteUrlBase = "{{ url('settings/currencies/delete') }}";
    var checkDeletePermission = @json(in_array('currency.delete', json_decode(@session('user_permissions'), true) ?? []));

    $(document).ready(function () {
        jQuery("#data-table_processing").show();
        const table = $('#currenciesTable').DataTable({
            pageLength: 10,
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: currenciesDataUrl
            },
            order: (checkDeletePermission) ? [1, 'asc'] : [0,'asc'],
            columnDefs: [
                { orderable: false, targets: (checkDeletePermission) ? [0,4,5,6] : [3,4,5] },
            ],
            language: {
                zeroRecords: "{{ trans('lang.no_record_found') }}",
                emptyTable: "{{ trans('lang.no_record_found') }}",
                processing: ""
            }
        });
        $('#currenciesTable').on('xhr.dt', function(e, settings, json){
            if(json && typeof json.recordsTotal !== 'undefined'){
                $('.total_count').text(json.recordsTotal);
            }
            jQuery("#data-table_processing").hide();
        });

        // Delete single
        $(document).on("click", "a.delete-btn", function(e){
            if(!confirm("{{trans('lang.selected_delete_alert')}}")){
                e.preventDefault();
            }
        });

        // Select all
        $('#select-all').change(function() {
            var isChecked = $(this).prop('checked');
            $('input[type="checkbox"].is_open').prop('checked', isChecked);
        });

        // Bulk delete
        $('#deleteAll').click(function() {
            if ($('#currenciesTable .is_open:checked').length) {
                if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                    $('#currenciesTable .is_open:checked').each(function(){
                        var id = $(this).attr('dataId');
                        window.location.href = currencyDeleteUrlBase + '/' + id;
                    });
                }
            } else {
                alert("{{trans('lang.select_delete_alert')}}");
            }
        });

        // Toggle active
        $(document).on("click", "input[name='isSwitch']", function(e) {
            var ischeck = $(this).is(':checked');
            var id = this.id;
            $.ajax({
                url: currencyToggleUrlBase + '/' + id + '/toggle',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                data: { isActive: ischeck ? 1 : 0 },
                success: function(resp){
                    if (resp && resp.activated) {
                        // Uncheck all others
                        $("#append_list1 tr").each(function() {
                            $(this).find(".switch input[type='checkbox']").not('#'+resp.activated).prop('checked', false);
                        });
                    }
                },
                error: function(xhr){
                    if (xhr.status === 422) {
                        alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Validation error');
                        // revert checkbox
                        $('#'+id).prop('checked', !ischeck);
                    }
                }
            });
        });
    });
</script>
@endsection
