@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.brands')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.brands')}}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
       <div class="admin-top-section">
        <div class="row">
            <div class="col-12">
                <div class="d-flex top-title-section pb-4 justify-content-between">
                    <div class="d-flex top-title-left align-self-center">
                        <span class="icon mr-3"><img src="{{ asset('images/brand.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.brands_table')}}</h3>
                        <span class="counter ml-3 brand_count"></span>
                    </div>
                    <div class="d-flex top-title-right align-self-center">
                        <div class="select-box pl-3">
                        </div>
                    </div>
                </div>
            </div>
        </div>
       </div>
        @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="row mb-4">
    <div class="col-12">
        <div class="card border">
            <div class="card-header d-flex justify-content-between align-items-center border-0">
                <div class="card-header-title">
                    <h3 class="text-dark-2 mb-2 h4">Bulk Import Brands</h3>
                    <p class="mb-0 text-dark-2">Upload Excel or CSV file to import multiple brands at once</p>
                </div>
                <div class="card-header-right d-flex align-items-center">
                    <?php if (in_array('brands.create', json_decode(@session('user_permissions'), true))) { ?>
                    <div class="card-header-btn mr-3">
                        <div class="btn-group" role="group">
                            <a href="{{ route('brands.download-template', ['format' => 'excel']) }}" class="btn btn-outline-success rounded-full">
                                <i class="mdi mdi-download mr-2"></i>Excel Template
                            </a>
                            <a href="{{ route('brands.download-template', ['format' => 'csv']) }}" class="btn btn-outline-primary rounded-full">
                                <i class="mdi mdi-download mr-2"></i>CSV Template
                            </a>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('brands.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="importFile" class="control-label">Select File (.csv/.xls/.xlsx)</label>
                                <input type="file" name="file" id="importFile" accept=".csv,.xls,.xlsx" class="form-control" required>
                                <div class="form-text text-muted">
                                    <i class="mdi mdi-information-outline mr-1"></i>
                                    File should contain: name, slug, description, status, logo_url
                                    <br><strong>Recommended:</strong> Use Excel format for better formatting and instructions.
                                    <br><strong>Note:</strong> Use CSV format if you encounter ZipArchive errors with Excel files.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <?php if (in_array('brands.create', json_decode(@session('user_permissions'), true))) { ?>
                            <button type="submit" class="btn btn-primary rounded-full">
                                <i class="mdi mdi-upload mr-2"></i>Import Brands
                            </button>
                            <?php } ?>
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
               <div class="card border">
                 <div class="card-header d-flex justify-content-between align-items-center border-0">
                   <div class="card-header-title">
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.brands_table')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.brands_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3">
                        <?php if (in_array('brands.create', json_decode(@session('user_permissions'), true))) { ?>
                        <a class="btn-primary btn rounded-full" href="{!! route('brands.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.add_brand')}}</a>
                        <?php } ?>
                     </div>
                   </div>
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="brandsTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <?php if (in_array('brands.delete', json_decode(@session('user_permissions'),true))) { ?>
                                    <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active">
                                            <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                    <?php } ?>
                                    <th>{{trans('lang.brand_name')}}</th>
                                    <th>{{trans('lang.brand_slug')}}</th>
                                    <th>{{trans('lang.brand_logo')}}</th>
                                    <th>{{trans('lang.brand_description')}}</th>
                                    <th> {{trans('lang.status')}}</th>
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
    const dataUrl = '{{ route('brands.data') }}';
    const editUrlTpl = '{{ route("brands.edit", ":id") }}';
    const toggleUrlTpl = '{{ route("brands.toggle", ":id") }}';
    const deleteUrlTpl = '{{ route("brands.delete.post", ":id") }}';
    const bulkDeleteUrl = '{{ route("brands.bulkDelete") }}';
    const checkDeletePermission = @json(in_array('brands.delete', json_decode(@session('user_permissions'), true) ?? []));
    $(document).ready(function () {
        console.log('📡 Initializing Brands DataTable...');

        const table = $('#brandsTable').DataTable({
            pageLength: 30,
            lengthMenu: [[10, 25, 30, 50, 100, -1], [10, 25, 30, 50, 100, "All"]],
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: dataUrl,
                data: function(d){
                    d.withDelete = checkDeletePermission ? 1 : 0;
                    console.log('📡 Fetching brands:', d);
                },
                dataSrc: function(json){
                    console.log('📥 Brands response:', json);

                    // Update count display
                    if (json.stats && json.stats.total) {
                        $('.brand_count').text(json.stats.total);
                        console.log('📊 Total brands:', json.stats.total);
                    } else {
                        $('.brand_count').text(json.recordsTotal || 0);
                    }

                    return json.data;
                },
                error: function(xhr, error, code) {
                    console.error('❌ DataTables error:', error, code);
                    console.error('Response:', xhr.responseText);
                }
            },
            order: (checkDeletePermission) ? [1, 'asc'] : [0,'asc'],
            columnDefs: [
                { orderable: false, targets: (checkDeletePermission) ? [0,2,6] : [2,5] },
            ],
            language: {
                zeroRecords: "{{trans("lang.no_record_found")}}",
                emptyTable: "{{trans("lang.no_record_found")}}",
            },
        });
        table.columns.adjust().draw();
        // Toggle status
        $(document).on('click', "input[name='isSwitch']", function(){
            const id = $(this).data('id');
            const $cb = $(this);
            const brandName = $cb.closest('tr').find('a').first().text().trim();
            const intended = $cb.is(':checked');
            const action = intended ? 'activated' : 'deactivated';
            const url = toggleUrlTpl.replace(':id', id);

            console.log('🔄 Toggle brand status:', { id: id, name: brandName, status: intended });

            $cb.prop('disabled', true);
            $.post(url, {_token: '{{ csrf_token() }}' })
                .done(function(resp){
                    console.log('✅ Brand status toggled:', resp);

                    // Log activity
                    if (typeof logActivity === 'function') {
                        logActivity('brands', action, action.charAt(0).toUpperCase() + action.slice(1) + ' brand: ' + brandName);
                    }

                    $cb.prop('checked', !!resp.status);
                })
                .fail(function(xhr){
                    console.error('❌ Toggle failed:', xhr);
                    $cb.prop('checked', !intended);
                })
                .always(function(){
                    $cb.prop('disabled', false);
                });
        });
    });

    $(document).on('click', '.brand-delete', function(){
        const id = $(this).data('id');
        const brandName = $(this).closest('tr').find('a').first().text().trim() || 'Unknown';

        console.log('🗑️ Delete brand clicked:', { id: id, name: brandName });

        if(!confirm("{{trans('lang.are_you_sure')}}")) return;

        const url = deleteUrlTpl.replace(':id', id);
        jQuery("#data-table_processing").show();

        $.post(url, {_token: '{{ csrf_token() }}'})
            .done(function(response){
                console.log('✅ Brand deleted successfully:', response);

                // Log activity
                if (typeof logActivity === 'function') {
                    logActivity('brands', 'deleted', 'Deleted brand: ' + brandName);
                }

                $('#brandsTable').DataTable().ajax.reload();

                if (typeof toastr !== 'undefined') {
                    toastr.success(response.message || 'Brand deleted successfully');
                }
            })
            .fail(function(xhr){
                console.error('❌ Delete failed:', xhr);
                alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error deleting brand');
            })
            .always(function(){
                jQuery("#data-table_processing").hide();
            });
    });
    $("#is_active").click(function () {
        $("#brandsTable .is_open").prop('checked', $(this).prop('checked'));
    });
    $("#deleteAll").click(async function () {
        if ($('#brandsTable .is_open:checked').length) {
            const selectedCount = $('#brandsTable .is_open:checked').length;

            console.log('🗑️ Bulk delete brands requested:', { count: selectedCount });

            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#data-table_processing").show();

                const ids = [];
                $('#brandsTable .is_open:checked').each(function(){ ids.push($(this).attr('dataId')); });

                $.post(bulkDeleteUrl, { ids: ids, _token: '{{ csrf_token() }}' })
                    .done(function(response){
                        console.log('✅ Bulk delete completed:', response);

                        // Log activity
                        if (typeof logActivity === 'function') {
                            logActivity('brands', 'bulk_deleted', 'Bulk deleted ' + response.deleted + ' brands');
                        }

                        $('#brandsTable').DataTable().ajax.reload();

                        let message = 'Deleted ' + response.deleted + ' brand(s)';
                        if (response.blocked && response.blocked.length > 0) {
                            message += '. ' + response.blocked.length + ' brand(s) are in use and cannot be deleted.';
                        }

                        if (typeof toastr !== 'undefined') {
                            toastr.success(message);
                        } else {
                            alert(message);
                        }
                    })
                    .fail(function(xhr){
                        console.error('❌ Bulk delete failed:', xhr);
                        alert('Error deleting brands');
                    })
                    .always(function(){
                        jQuery("#data-table_processing").hide();
                    });
            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });
    // legacy handlers removed
</script>
@endsection
