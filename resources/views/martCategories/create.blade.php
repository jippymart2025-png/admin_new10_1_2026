@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">Mart Categories</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('mart-categories') }}">Mart Categories</a></li>
                    <li class="breadcrumb-item active">Create Mart Category</li>
                </ol>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-10">

                    <div class="card shadow-sm rounded border-0">
                        <div class="card-body">

                            <!-- TAB NAVIGATION -->
                            <ul class="nav nav-tabs mb-4">
                                <li class="nav-item">
                                    <a class="nav-link active font-weight-bold text-orange" href="#">Mart Category Information</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-muted" href="#">Review Attributes</a>
                                </li>
                            </ul>

                            <!-- HEADER BUTTON -->
                            <button class="btn btn-warning text-white mb-4 font-weight-bold" style="background:#ff6a00;border:none;">
                                CREATE MART CATEGORY
                            </button>

                            <!-- FORM -->
                            <form method="POST" action="{{ route('mart-categories.store') }}" enctype="multipart/form-data">
                                @csrf

                                <div class="form-group">
                                    <label>Mart Category Name <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" placeholder="Enter the name for this mart category" required>
                                    <small class="text-muted">Enter the name for this mart category</small>
                                </div>

                                <div class="form-group">
                                    <label>Mart Category Description</label>
                                    <textarea name="description" class="form-control" rows="5" placeholder="Enter a description for this mart category"></textarea>
                                    <small class="text-muted">Enter a description for this mart category</small>
                                </div>

                                <div class="form-group">
                                    <label>Mart Category Image</label>
                                    <input type="file" name="photo" class="form-control-file">
                                    <small class="text-muted">Upload an image for this mart category</small>
                                </div>

                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="publish" name="publish" value="1" checked>
                                    <label class="form-check-label" for="publish">Publish</label>
                                </div>

                                <div class="form-group">
                                    <label>Section</label>
                                    <input type="text" name="section" class="form-control" placeholder="e.g., Essentials & Daily Needs, Health & Wellness">
                                    <small class="text-muted">Group categories by sections for better organization</small>
                                </div>

                                <div class="form-group">
                                    <label>Order</label>
                                    <input type="number" name="category_order" min="1" value="1" class="form-control">
                                    <small class="text-muted">Display order within section</small>
                                </div>

                                <!-- ✅ REVIEW ATTRIBUTES (STAYS HERE) -->
                                <div class="form-group mt-4">
                                    <label class="font-weight-bold">Review Attributes</label>
                                    <div class="row">
                                        @foreach($reviewAttributes as $attribute)
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input"
                                                           id="review_attribute_{{ $attribute->id }}"
                                                           name="review_attributes[]"
                                                           value="{{ $attribute->id }}"
                                                        {{ collect(old('review_attributes', []))->contains($attribute->id) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="review_attribute_{{ $attribute->id }}">
                                                        {{ $attribute->title }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- ✅ CENTER SAVE BUTTONS -->
                                <div class="text-center mt-4">
                                    <button class="btn btn-orange px-4" style="background:#ff6a00;color:white;">
                                        <i class="fa fa-save"></i> Save Category
                                    </button>
                                    <a href="{{ route('mart-categories') }}" class="btn btn-secondary px-4">Cancel</a>
                                </div>

                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <style>
        .text-orange { color: #ff6a00 !important; }
        .nav-tabs .nav-link.active { border-bottom: 3px solid #ff6a00 !important; }
    </style>
@endsection
