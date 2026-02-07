@extends('layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">Edit Master Product</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('master-products.index') }}">Master Products</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>

        <div class="container-fluid">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Category Selection Section at Top -->
            <div class="mb-4">
                <div class="text-white d-flex justify-content-between align-items-center">
                    <h4 class="btn btn-primary btn-sm">
                        <i class="mdi mdi-folder-multiple"></i> Select Vendor Category
                    </h4>

                    <a href="{{ route('categories') }}" class="btn btn-primary btn-sm">
                        Manage Categories
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div id="selected_category_display">
                    <div class="alert alert-info d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Selected Category:</strong> <span id="selected_category_title">{{ $product->categoryTitle ?? 'N/A' }}</span>
                            <input type="hidden" id="selected_category_id" value="{{ $product->categoryID }}">
                        </div>
                        <button type="button" class="btn btn-sm btn-danger remove-category-btn" id="remove_category_btn">
                            <i class="mdi mdi-close"></i> Remove
                        </button>
                    </div>
                </div>
                <div id="category_selector_display" style="display: none;">
                    <div class="form-group">
                        <label class="control-label">Choose a Category to Update Master Product</label>
                        <input type="text" id="category_search" class="form-control mb-2" placeholder="Search categories...">
                        <select id="category_selector" class="form-control" required>
                            <option value="">-- Select Vendor Category --</option>
                            @foreach($vendorCategories as $category)
                                <option value="{{ $category->id }}"
                                    {{ $product->categoryID == $category->id ? 'selected' : '' }}>
                                    {{ $category->title }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted">Select a category first, then fill the form below</div>
                    </div>
                </div>
            </div>

            <!-- Master Product Form -->
            <div class="card" id="product_form">
                <div class="card-header">
                    <h4 class="mb-0 px-4">Master Product Information</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('master-products.update', $product->id) }}" enctype="multipart/form-data" id="master_product_form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="categoryID" id="form_category_id" value="{{ $product->categoryID }}" required>

                        <div class="row">
                            <div class="col-md-12">
                                <fieldset>
                                    <div class="form-group row">
                                        <label class="col-md-3 control-label">Product Name <span class="text-danger">*</span></label>
                                        <div class="col-md-9">
                                            <input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control" required maxlength="255">
                                            <div class="form-text text-muted">Enter the product name (e.g., Chicken Dum Biryani)</div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-3 control-label">Description</label>
                                        <div class="col-md-9">
                                            <textarea name="description" rows="4" class="form-control">{{ old('description', $product->description) }}</textarea>
                                            <div class="form-text text-muted">Detailed description of the product</div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-3 control-label">Product Image</label>
                                        <div class="col-md-9">
                                            @if($product->photo)
                                                <div class="mb-2">
                                                    <img src="{{ $product->photo }}" alt="Product Image" style="max-width: 200px; max-height: 200px; object-fit: cover;" class="img-thumbnail">
                                                    <div class="mt-2">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" name="remove_photo" value="1" class="form-check-input">
                                                            Remove current image
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                            <input type="file" name="photo" class="form-control" accept="image/*">
                                            <div class="form-text text-muted">Upload product image (max 2MB). Leave empty to keep current image.</div>
                                        </div>
                                    </div>
                                </fieldset>

                                <fieldset>
                                    <legend>Pricing Information</legend>

                                    <div class="form-group row">
                                        <label class="col-md-3 control-label">Price</label>
                                        <div class="col-md-9">
                                            <input type="number" step="0.01" name="suggested_price" value="{{ old('suggested_price', $product->suggested_price) }}" class="form-control" min="0">
                                            <div class="form-text text-muted">Product price</div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-3 control-label">Discount Price</label>
                                        <div class="col-md-9">
                                            <input type="number" step="0.01" name="dis_price" value="{{ old('dis_price', $product->dis_price) }}" class="form-control" min="0">
                                            <div class="form-text text-muted">Discounted price for this product</div>
                                        </div>
                                    </div>
                                </fieldset>

                                <fieldset>
                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="is_recommended" name="is_recommended" value="1" {{ old('is_recommended', $product->is_recommended) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_recommended">Mark as Recommended</label>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="publish" name="publish" value="1" {{ old('publish', $product->publish) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="publish">Publish</label>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="isAvailable" name="isAvailable" value="1" {{ old('isAvailable', $product->isAvailable) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="isAvailable">Available</label>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" name="nonveg" id="nonveg" value="1" {{ old('nonveg', $product->nonveg) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="nonveg">Non-Veg</label>
                                    </div>
                                    <div class="form-text text-muted">If unchecked, item will be considered as Veg</div>
                                </fieldset>
                            </div>
                        </div>

                        <!-- Options Configuration -->
                        <fieldset>
                            <legend>Options Configuration</legend>

                            <div class="form-check width-100">
                                <input type="checkbox" class="has_options" id="has_options" name="has_options" value="1" {{ old('has_options', $product->has_options) ? 'checked' : '' }}>
                                <label class="col-3 control-label" for="has_options">
                                    <strong>Enable Options for this item</strong>
                                </label>
                                <div class="form-text text-muted">
                                    Enable this to create different variants/sizes for this item
                                </div>
                            </div>

                            <div id="options_config" style="display:none;">
                                <div class="alert alert-info">
                                    <i class="mdi mdi-information-outline"></i>
                                    <strong>Options will be stored as part of this item.</strong>
                                    Each option can have its own price, image, and specifications.
                                </div>
                            </div>
                        </fieldset>
                        <fieldset id="options_fieldset" style="display:none;">
                            <legend>Product Options</legend>

                            <div class="options-list"></div>

                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-primary" onclick="addNewOption()">
                                    <i class="mdi mdi-plus"></i> Add Option
                                </button>
                            </div>
                        </fieldset>
                        <!-- Options Management -->
                        <div id="option_template" style="display:none;">
                            <div class="option-item border rounded p-3 mb-3" data-option-id="">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Option <span class="option-number"></span></h6>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeOption(this)">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Option Title</label>
                                        <input type="text" class="form-control option-title" placeholder="e.g. Family Pack">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Option Subtitle</label>
                                        <input type="text" class="form-control option-subtitle" placeholder="e.g. Serves 3–4">
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-4">
                                        <label>Price (₹)</label>
                                        <input type="number" class="form-control option-price" min="0">
                                    </div>

                                    <div class="col-md-4">
                                        <label>Original Price (₹)</label>
                                        <input type="number" class="form-control option-original-price" min="0">
                                    </div>

                                    <div class="col-md-4 d-flex align-items-center mt-4">
                                        <div class="form-check mr-3">
                                            <input type="checkbox" class="form-check-input option-available" checked>
                                            <label class="form-check-label">Available</label>
                                        </div>

                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input option-featured">
                                            <label class="form-check-label">Featured</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mt-4">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="mdi mdi-content-save"></i> Update Master Product
                                </button>
                                <a href="{{ route('master-products.index') }}" class="btn btn-secondary btn-lg">
                                    Back
                                </a>
                            </div>
                        </div>
                        <!-- Hidden input for options data -->
                        <input type="hidden" name="options_data" id="options_data" value="">
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        /* =========================
           STATE
        ========================= */
        let optionsList = @json($options ?? []);
        let optionCounter = 0;

        /* =========================
           TOGGLE
        ========================= */
        $(document).ready(function () {

            function toggleOptions() {
                if ($('#has_options').is(':checked')) {
                    $('#options_config').show();
                    $('#options_fieldset').show();
                } else {
                    $('#options_config').hide();
                    $('#options_fieldset').hide();
                }
            }

            toggleOptions();
            $('#has_options').on('change', toggleOptions);

            if (optionsList.length > 0) {
                $('#has_options').prop('checked', true);
                toggleOptions();
                renderExistingOptions();
            }
        });

        /* =========================
           RENDER EXISTING
        ========================= */
        function renderExistingOptions() {
            $('.options-list').html('');
            optionCounter = 0;

            optionsList.forEach(option => {
                optionCounter++;

                const tpl = $('#option_template .option-item').clone();
                tpl.attr('data-option-id', option.id);
                tpl.find('.option-number').text(optionCounter);

                tpl.find('.option-title').val(option.title || '');
                tpl.find('.option-subtitle').val(option.subtitle || '');
                tpl.find('.option-price').val(option.price || 0);
                tpl.find('.option-original-price').val(option.original_price || 0);
                tpl.find('.option-available').prop('checked', option.is_available);
                tpl.find('.option-featured').prop('checked', option.is_featured);

                $('.options-list').append(tpl.show());
                bindOptionEvents(option.id);
            });
        }

        /* =========================
           ADD / REMOVE
        ========================= */
        function addNewOption() {
            optionCounter++;
            const id = 'opt_' + Date.now();

            const tpl = $('#option_template .option-item').clone();
            tpl.attr('data-option-id', id);
            tpl.find('.option-number').text(optionCounter);

            $('.options-list').append(tpl.show());

            optionsList.push({
                id,
                title: '',
                subtitle: '',
                price: 0,
                original_price: 0,
                is_available: true,
                is_featured: false
            });

            bindOptionEvents(id);
        }

        function removeOption(btn) {
            const box = $(btn).closest('.option-item');
            const id = box.data('option-id');
            optionsList = optionsList.filter(o => o.id !== id);
            box.remove();
        }

        /* =========================
           EVENTS
        ========================= */
        function bindOptionEvents(id) {
            const el = $(`[data-option-id="${id}"]`);
            const idx = optionsList.findIndex(o => o.id === id);

            el.find('.option-title').on('input', e => optionsList[idx].title = e.target.value);
            el.find('.option-subtitle').on('input', e => optionsList[idx].subtitle = e.target.value);
            el.find('.option-price').on('input', e => optionsList[idx].price = parseFloat(e.target.value) || 0);
            el.find('.option-original-price').on('input', e => optionsList[idx].original_price = parseFloat(e.target.value) || 0);
            el.find('.option-available').on('change', e => optionsList[idx].is_available = e.target.checked);

            el.find('.option-featured').on('change', function () {
                if (this.checked) {
                    $('.option-featured').not(this).prop('checked', false);
                    optionsList.forEach(o => o.is_featured = false);
                    optionsList[idx].is_featured = true;
                }
            });
        }
        /* =====================================
         SUBMIT SERIALIZATION
      ===================================== */
        $('#master_product_form').on('submit', function () {
            $('#options_data').val(JSON.stringify(optionsList));
        });
    </script>

@endsection
