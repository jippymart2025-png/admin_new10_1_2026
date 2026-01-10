@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Edit Catering Request</h3>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card border">
            <div class="card-body">
                <form id="cateringForm">
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" id="date" name="date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="name">Customer Name</label>
                        <input type="text" id="name" name="name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="mobile">Mobile</label>
                        <input type="text" id="mobile" name="mobile" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="function_type">Event Type</label>
                        <input type="text" id="function_type" name="function_type" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="place">Place</label>
                        <input type="text" id="place" name="place" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="guests">Guests</label>
                        <input type="number" id="guests" name="guests" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="meal_preference">Meal Preference</label>
                        <select id="meal_preference" name="meal_preference" class="form-control">
                            <option value="veg">Veg</option>
                            <option value="non-veg">Non Veg</option>
                            <option value="both">Both</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="pending">pending</option>
                            <option value="confirmed">confirmed</option>
                            <option value="cancelled">cancelled</option>
                        </select>
                    </div>
                    <button type="button" id="saveBtn" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var database = firebase.firestore();
    var docId = '{{ $id ?? '' }}';
    var ref = database.collection('catering_requests').doc(docId);

    function loadDoc() {
        ref.get().then(function(doc) {
            if (doc.exists) {
                var data = doc.data();
                jQuery('#date').val(data.date || '');
                jQuery('#name').val(data.name || '');
                jQuery('#email').val(data.email || '');
                jQuery('#mobile').val(data.mobile || '');
                jQuery('#function_type').val(data.function_type || '');
                jQuery('#place').val(data.place || '');
                jQuery('#guests').val(data.guests || '');
                jQuery('#meal_preference').val(data.meal_preference || '');
                jQuery('#status').val(data.status || '');
            }
        });
    }

    jQuery(document).ready(function() {
        if(docId) loadDoc();
        jQuery('#saveBtn').on('click', function(){
            var payload = {
                date: jQuery('#date').val(),
                name: jQuery('#name').val(),
                email: jQuery('#email').val(),
                mobile: jQuery('#mobile').val(),
                function_type: jQuery('#function_type').val(),
                place: jQuery('#place').val(),
                guests: parseInt(jQuery('#guests').val()) || 0,
                meal_preference: jQuery('#meal_preference').val(),
                status: jQuery('#status').val(),
                updated_at: new Date().toISOString()
            };
            ref.update(payload).then(function(){
                alert('Saved');
                window.location.href = '/catering';
            }).catch(function(error){
                console.error('Error updating:', error);
                alert('Error saving');
            });
        });
    });
</script>
@endsection
