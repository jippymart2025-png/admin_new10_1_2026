@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.cuisines_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.cuisines_plural')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/category.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.cuisines_table')}}</h3>
                        <span class="counter ml-3 category_count"></span>
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
                    <h3 class="text-dark-2 mb-2 h4">Bulk Import Cuisines</h3>
                    <p class="mb-0 text-dark-2">Upload Excel file to import multiple cuisines at once</p>
                </div>
                <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3">
                        <a href="{{ route('cuisines.download-template') }}" class="btn btn-outline-primary rounded-full">
                            <i class="mdi mdi-download mr-2"></i>Download Template
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('cuisines.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="importFile" class="control-label">Select Excel File (.xls/.xlsx)</label>
                                <input type="file" name="file" id="importFile" accept=".xls,.xlsx" class="form-control" required>
                                <div class="form-text text-muted">
                                    <i class="mdi mdi-information-outline mr-1"></i>
                                    File should contain: title, description, photo, publish
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary rounded-full">
                                <i class="mdi mdi-upload mr-2"></i>Import Cuisines
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
               <div class="card border">
                 <div class="card-header d-flex justify-content-between align-items-center border-0">
                   <div class="card-header-title">
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.cuisines_table')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.cuisines_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3">
                        <a class="btn-primary btn rounded-full" href="{!! route('cuisines.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.cuisines_create')}}</a>
                     </div>
                   </div>
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="categoriesTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <?php if (in_array('category.delete', json_decode(@session('user_permissions'),true))) { ?>
                                    <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active">
                                            <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                    <?php } ?>
                                    <th>{{trans('lang.faq_category_name')}}</th>
                                    <th>Description</th>
                                    <th>{{trans('lang.item_publish')}}</th>
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
    var user_permissions = '<?php echo @session("user_permissions")?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = ($.inArray('category.delete', user_permissions) >= 0);
    $(document).ready(function () {
        const table = $('#categoriesTable').DataTable({
            pageLength: 10,
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: '{{ route("cuisines.data") }}',
                data: function(d){ /* DataTables sends pagination/search/order automatically */ },
                dataSrc: function(json) {
                    // Update count display
                    if (json.stats && json.stats.total) {
                        $('.category_count').text(json.stats.total);
                        console.log('📊 Total cuisines:', json.stats.total);
                    }
                    return json.data;
                }
            },
            order: (checkDeletePermission) ? [1, 'asc'] : [0,'asc'],
            columnDefs: [
                { orderable: false, targets: (checkDeletePermission) ? [0,3,4] : [2, 3] },
            ],
            language: {
                zeroRecords: "{{trans('lang.no_record_found')}}",
                emptyTable: "{{trans('lang.no_record_found')}}",
                processing: ""
            }
        });
        table.columns.adjust().draw();

        $(document).on('click', '.delete-btn', function(e){
            e.preventDefault();
            var deleteUrl = $(this).attr('href');
            var cuisineName = $(this).closest('tr').find('a').text().trim();

            if(confirm("{{trans('lang.selected_delete_alert')}}")){
                // Log activity
                if (typeof logActivity === 'function') {
                    logActivity('cuisines', 'deleted', 'Deleted cuisine: ' + cuisineName);
                }
                window.location.href = deleteUrl;
            }
        });

        $(document).on('change', '.toggle-publish', function(){
            var id = $(this).data('id');
            var publish = $(this).is(':checked');
            var cuisineName = $(this).closest('tr').find('a').text().trim();
            var action = publish ? 'published' : 'unpublished';

            $.post({
                url: '{{ url('/cuisines') }}' + '/' + id + '/toggle',
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                data: { publish: publish },
                success: function(response) {
                    console.log('✅ Cuisine publish toggled:', response);

                    // Log activity
                    if (typeof logActivity === 'function') {
                        logActivity('cuisines', action, action.charAt(0).toUpperCase() + action.slice(1) + ' cuisine: ' + cuisineName);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error toggling publish:', error);
                    // Revert checkbox on error
                    $(this).prop('checked', !publish);
                }
            });
        });

        $("#is_active").click(function () {
            $("#categoriesTable .is_open").prop('checked', $(this).prop('checked'));
        });
        $("#deleteAll").click(function () {
            if ($('#categoriesTable .is_open:checked').length) {
                var selectedCount = $('#categoriesTable .is_open:checked').length;

                if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                    // Log activity for bulk delete
                    if (typeof logActivity === 'function') {
                        logActivity('cuisines', 'bulk_deleted', 'Bulk deleted ' + selectedCount + ' cuisines');
                    }

                    var deleteUrl = '{{ url('/cuisines/delete') }}';
                    $('#categoriesTable .is_open:checked').first().each(function(){
                        var dataId = $(this).attr('dataId');
                        window.location.href = deleteUrl + '/' + dataId;
                    });
                }
            } else {
                alert("{{trans('lang.select_delete_alert')}}");
            }
        });
    });
</script>
@endsection
