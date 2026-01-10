@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.zone_bonus_settings')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{{url('/settings')}}">{{trans('lang.settings')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.zone_bonus_settings')}}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
       <div class="admin-top-section"> 
        <div class="row">
            <div class="col-12">
                <div class="d-flex top-title-section pb-4 justify-content-between">
                    <div class="d-flex top-title-left align-self-center">
                        <span class="icon mr-3"><img src="{{ asset('images/zone.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.zone_bonus_settings')}}</h3>
                        <span class="counter ml-3 zone_bonus_count"></span>
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

       <div class="table-list">
       <div class="row">
           <div class="col-12">
               <div class="card border">
                 <div class="card-header d-flex justify-content-between align-items-center border-0">
                   <div class="card-header-title">
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.zone_bonus_configuration')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.zone_bonus_configuration_help')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                        <button type="button" class="btn-primary btn rounded-full" data-toggle="modal" data-target="#addZoneBonusModal">
                            <i class="mdi mdi-plus mr-2"></i>{{trans('lang.add_zone_bonus')}}
                        </button>
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="zoneBonusTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <?php if (in_array('zone-bonus-settings', json_decode(@session('user_permissions'),true))) { ?>
                                    <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active">
                                            <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                    <?php } ?>
                                    <th>{{trans('lang.zone_name')}}</th>
                                    <th>{{trans('lang.required_orders')}}</th>
                                    <th>{{trans('lang.bonus_amount')}}</th>
                                    <th>{{trans('lang.status')}}</th>
                                    <th>{{trans('lang.created_at')}}</th>
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

<!-- Add Zone Bonus Modal -->
<div class="modal fade" id="addZoneBonusModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{trans('lang.add_zone_bonus')}}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addZoneBonusForm">
                    <div class="form-group">
                        <label class="control-label">{{trans('lang.select_zone')}}</label>
                        <select class="form-control" id="zoneSelect" required>
                            <option value="">{{trans('lang.select_zone')}}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">{{trans('lang.required_orders_for_bonus')}}</label>
                        <input type="number" class="form-control" id="requiredOrders" min="1" max="50" required>
                        <small class="form-text text-muted">{{trans('lang.required_orders_help')}}</small>
                    </div>
                    <div class="form-group">
                        <label class="control-label">{{trans('lang.bonus_amount')}}</label>
                        <input type="number" class="form-control" id="bonusAmount" min="1" step="0.01" required>
                        <small class="form-text text-muted">{{trans('lang.bonus_amount_help')}}</small>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="isActive" checked>
                            <label class="form-check-label" for="isActive">{{trans('lang.active')}}</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{trans('lang.cancel')}}</button>
                <button type="button" class="btn btn-primary" id="saveZoneBonus">{{trans('lang.save')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Zone Bonus Modal -->
<div class="modal fade" id="editZoneBonusModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{trans('lang.edit_zone_bonus')}}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editZoneBonusForm">
                    <input type="hidden" id="editZoneBonusId">
                    <div class="form-group">
                        <label class="control-label">{{trans('lang.zone_name')}}</label>
                        <input type="text" class="form-control" id="editZoneName" readonly>
                    </div>
                    <div class="form-group">
                        <label class="control-label">{{trans('lang.required_orders_for_bonus')}}</label>
                        <input type="number" class="form-control" id="editRequiredOrders" min="1" max="50" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label">{{trans('lang.bonus_amount')}}</label>
                        <input type="number" class="form-control" id="editBonusAmount" min="1" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="editIsActive">
                            <label class="form-check-label" for="editIsActive">{{trans('lang.active')}}</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{trans('lang.cancel')}}</button>
                <button type="button" class="btn btn-primary" id="updateZoneBonus">{{trans('lang.update')}}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
var database = firebase.firestore();
var zones = [];
var zoneBonusSettings = [];
var user_permissions = '<?php echo @session("user_permissions")?>';
user_permissions = Object.values(JSON.parse(user_permissions));
var checkDeletePermission = false;
if ($.inArray('zone-bonus-settings', user_permissions) >= 0) {
    checkDeletePermission = true;
}

$(document).ready(function() {
    loadZones();
    
    // Initialize DataTable with empty data first
    const table = $('#zoneBonusTable').DataTable({
        pageLength: 10,
        processing: false,
        serverSide: false,
        responsive: true,
        autoWidth: false,
        scrollX: true,
        order: [[1, 'asc']],
        columnDefs: [
            { orderable: false, targets: [0, 6] }
        ],
        language: {
            searchPlaceholder: "Search zone bonus settings...",
            lengthMenu: "Show _MENU_ entries per page",
            zeroRecords: "No zone bonus settings found",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries available",
            infoFiltered: "(filtered from _MAX_ total entries)"
        },
        data: [] // Start with empty data
    });
    
    // Load data after table initialization
    loadZoneBonusSettings();
    
    // Save zone bonus
    $('#saveZoneBonus').click(function() {
        saveZoneBonus();
    });
    
    // Update zone bonus
    $('#updateZoneBonus').click(function() {
        updateZoneBonus();
    });
    
    // Delete all functionality
    $('#deleteAll').click(function() {
        if (confirm('Are you sure you want to delete all selected zone bonus settings?')) {
            var selectedIds = [];
            $('#zoneBonusTable tbody tr').each(function() {
                if ($(this).find('input[type="checkbox"]').is(':checked')) {
                    selectedIds.push($(this).data('id'));
                }
            });
            
            if (selectedIds.length > 0) {
                deleteMultipleZoneBonuses(selectedIds);
            } else {
                showError('Please select zone bonus settings to delete');
            }
        }
    });
    
    // Checkbox change handler
    $('#is_active').change(function() {
        $('#zoneBonusTable tbody tr input[type="checkbox"]').prop('checked', $(this).is(':checked'));
    });
});

// Load zones for dropdown
function loadZones() {
    database.collection('zone').where('publish', '==', true).orderBy('name', 'asc').get()
        .then(function(snapshots) {
            snapshots.docs.forEach(function(doc) {
                var data = doc.data();
                zones.push(data);
                $('#zoneSelect').append($('<option></option>')
                    .attr('value', data.id)
                    .text(data.name));
            });
        })
        .catch(function(error) {
            console.error('Error loading zones:', error);
            showError('Error loading zones');
        });
}

// Load zone bonus settings
function loadZoneBonusSettings() {
    database.collection('zone_bonus_settings').orderBy('zoneName', 'asc').get()
        .then(function(snapshots) {
            zoneBonusSettings = [];
            var tableData = [];
            
            snapshots.docs.forEach(function(doc) {
                var data = doc.data();
                data.id = doc.id;
                zoneBonusSettings.push(data);
                
                var statusBadge = data.isActive ? 
                    '<span class="badge badge-success">Active</span>' : 
                    '<span class="badge badge-secondary">Inactive</span>';
                
                var actions = '';
                if (checkDeletePermission) {
                    actions += '<button class="btn btn-sm btn-primary edit-zone-bonus mr-1" data-id="' + data.id + '">' +
                        '<i class="mdi mdi-pencil"></i>' +
                    '</button>';
                }
                actions += '<button class="btn btn-sm btn-danger delete-zone-bonus" data-id="' + data.id + '">' +
                    '<i class="mdi mdi-delete"></i>' +
                '</button>';
                
                tableData.push([
                    checkDeletePermission ? '<input type="checkbox" class="row-checkbox" data-id="' + data.id + '">' : '',
                    data.zoneName,
                    data.requiredOrdersForBonus,
                    '₹' + data.bonusAmount,
                    statusBadge,
                    formatDate(data.createdAt),
                    actions
                ]);
            });
            
            // Update DataTable with new data
            var table = $('#zoneBonusTable').DataTable();
            table.clear().rows.add(tableData).draw();
            
            // Update counter
            $('.zone_bonus_count').text('(' + zoneBonusSettings.length + ')');
        })
        .catch(function(error) {
            console.error('Error loading zone bonus settings:', error);
            showError('Error loading zone bonus settings');
        });
}

// Save zone bonus
function saveZoneBonus() {
    var zoneId = $('#zoneSelect').val();
    var requiredOrders = parseInt($('#requiredOrders').val());
    var bonusAmount = parseFloat($('#bonusAmount').val());
    var isActive = $('#isActive').is(':checked');
    
    if (!zoneId || !requiredOrders || !bonusAmount) {
        showError('Please fill all required fields');
        return;
    }
    
    // Check if zone already has bonus settings
    var existingSetting = zoneBonusSettings.find(function(setting) {
        return setting.zoneId === zoneId;
    });
    
    if (existingSetting) {
        showError('Bonus settings already exist for this zone');
        return;
    }
    
    // Get zone name
    var zone = zones.find(function(z) {
        return z.id === zoneId;
    });
    
    if (!zone) {
        showError('Zone not found');
        return;
    }
    
    var bonusId = database.collection('temp').doc().id;
    var bonusData = {
        id: bonusId,
        zoneId: zoneId,
        zoneName: zone.name,
        requiredOrdersForBonus: requiredOrders,
        bonusAmount: bonusAmount,
        isActive: isActive,
        createdAt: firebase.firestore.FieldValue.serverTimestamp(),
        updatedAt: firebase.firestore.FieldValue.serverTimestamp()
    };
    
    database.collection('zone_bonus_settings').doc(bonusId).set(bonusData)
        .then(function() {
            $('#addZoneBonusModal').modal('hide');
            $('#addZoneBonusForm')[0].reset();
            loadZoneBonusSettings();
            showSuccess('Zone bonus settings saved successfully');
        })
        .catch(function(error) {
            console.error('Error saving zone bonus:', error);
            showError('Error saving zone bonus settings');
        });
}

// Edit zone bonus
$(document).on('click', '.edit-zone-bonus', function() {
    var id = $(this).data('id');
    var setting = zoneBonusSettings.find(function(s) {
        return s.id === id;
    });
    
    if (setting) {
        $('#editZoneBonusId').val(setting.id);
        $('#editZoneName').val(setting.zoneName);
        $('#editRequiredOrders').val(setting.requiredOrdersForBonus);
        $('#editBonusAmount').val(setting.bonusAmount);
        $('#editIsActive').prop('checked', setting.isActive);
        $('#editZoneBonusModal').modal('show');
    }
});

// Update zone bonus
function updateZoneBonus() {
    var id = $('#editZoneBonusId').val();
    var requiredOrders = parseInt($('#editRequiredOrders').val());
    var bonusAmount = parseFloat($('#editBonusAmount').val());
    var isActive = $('#editIsActive').is(':checked');
    
    if (!requiredOrders || !bonusAmount) {
        showError('Please fill all required fields');
        return;
    }
    
    var updateData = {
        requiredOrdersForBonus: requiredOrders,
        bonusAmount: bonusAmount,
        isActive: isActive,
        updatedAt: firebase.firestore.FieldValue.serverTimestamp()
    };
    
    database.collection('zone_bonus_settings').doc(id).update(updateData)
        .then(function() {
            $('#editZoneBonusModal').modal('hide');
            loadZoneBonusSettings();
            showSuccess('Zone bonus settings updated successfully');
        })
        .catch(function(error) {
            console.error('Error updating zone bonus:', error);
            showError('Error updating zone bonus settings');
        });
}

// Delete zone bonus
$(document).on('click', '.delete-zone-bonus', function() {
    var id = $(this).data('id');
    var setting = zoneBonusSettings.find(function(s) {
        return s.id === id;
    });
    
    if (confirm('Are you sure you want to delete bonus settings for ' + setting.zoneName + '?')) {
        database.collection('zone_bonus_settings').doc(id).delete()
            .then(function() {
                loadZoneBonusSettings();
                showSuccess('Zone bonus settings deleted successfully');
            })
            .catch(function(error) {
                console.error('Error deleting zone bonus:', error);
                showError('Error deleting zone bonus settings');
            });
    }
});

// Delete multiple zone bonuses
function deleteMultipleZoneBonuses(ids) {
    var promises = ids.map(function(id) {
        return database.collection('zone_bonus_settings').doc(id).delete();
    });
    
    Promise.all(promises)
        .then(function() {
            loadZoneBonusSettings();
            showSuccess('Selected zone bonus settings deleted successfully');
        })
        .catch(function(error) {
            console.error('Error deleting zone bonuses:', error);
            showError('Error deleting zone bonus settings');
        });
}

// Utility functions
function formatDate(timestamp) {
    if (!timestamp) return 'N/A';
    var date = timestamp.toDate ? timestamp.toDate() : new Date(timestamp);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

function showError(message) {
    $('.error_top').show().html('<div class="alert alert-danger">' + message + '</div>');
    setTimeout(function() {
        $('.error_top').hide();
    }, 5000);
}

function showSuccess(message) {
    $('.error_top').show().html('<div class="alert alert-success">' + message + '</div>');
    setTimeout(function() {
        $('.error_top').hide();
    }, 3000);
}
</script>
@endsection


