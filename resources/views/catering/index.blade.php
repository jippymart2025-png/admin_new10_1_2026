@extends('layouts.app')
@section('content')
<div class="page-wrapper">
	<div class="row page-titles">
		<div class="col-md-5 align-self-center">
			<h3 class="text-themecolor">Catering Requests</h3>
		</div>
		<div class="col-md-7 align-self-center">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="{{url('/dashboard')}}">Dashboard</a></li>
				<li class="breadcrumb-item active">Catering Requests</li>
			</ol>
		</div>
	</div>
	<div class="container-fluid">
		<div class="card border">
			<div class="card-header">
				<h3 class="mb-0">Catering Requests</h3>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table id="cateringTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>Date</th>
								<th>Customer</th>
								<th>Event Details</th>
								<th>Guest</th>
								<th>Meal Preference</th>
								<th>Status</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody id="catering_list"></tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('scripts')
<script>
	var database = firebase.firestore();
	var refData = database.collection('catering_requests');

	function renderRow(doc) {
		var data = doc.data();
		var tr = '<tr>' +
			'<td>' + (data.date || '') + '</td>' +
			'<td>' + (data.name || '') + '<br/>' + (data.email || '') + '</td>' +
			'<td>' + (data.place || '') + '<br/>' + (data.function_type || '') + '</td>' +
			'<td>' + (data.guests || '') + '</td>' +
			'<td>' + (data.meal_preference || '') + '</td>' +
			'<td>' + (data.status || '') + '</td>' +
			'<td><a class="btn btn-sm btn-primary" href="/catering/edit/' + doc.id + '">Edit</a></td>' +
			'</tr>';
		jQuery('#catering_list').append(tr);
	}

	refData.orderBy('created_at','desc').get().then(function(snapshot) {
		snapshot.forEach(function(doc) {
			renderRow(doc);
		});
	}).catch(function(error) {
		console.error('Error loading catering requests:', error);
	});
</script>
@endsection
