@extends('layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">Create Master Product</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('master-products.index') }}">Master Products</a></li>
                    <li class="breadcrumb-item active">Create</li>
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

                <div class="card-body">
                    @if($selectedCategory)
                        <div class="alert alert-info d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Selected Category:</strong> {{ $selectedCategory->title }}
                                <input type="hidden" id="selected_category_id" value="{{ $selectedCategory->id }}">
                            </div>
                            <button type="button" class="btn btn-sm btn-danger remove-category-btn" id="remove_category_btn">
                                <i class="mdi mdi-close"></i> Remove
                            </button>
                        </div>
                    @else
                        <div class="form-group">
                            <label class="control-label font-weight-bold">
                                Choose a Category to Create Master Product
                            </label>
                            <div class="row d-flex justify-content-end">
                                <!-- LEFT: Search + Dropdown -->
                                <div class="col-md-8 col-sm-12">
                                    <input type="text"
                                           id="category_search"
                                           class="form-control mb-2"
                                           placeholder="🔍 Search categories...">

                                    <select id="category_selector" class="form-control" required>
                                        <option value="">-- Select Vendor Category --</option>
                                        @foreach($vendorCategories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ $selectedCategoryId == $category->id ? 'selected' : '' }}>
                                                {{ $category->title }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <small class="form-text text-muted mt-1">
                                        Select a category first, then fill the form below
                                    </small>
                                </div>

                                <!-- RIGHT: Manage Button -->
                                <div class="col-md-4 mt-0 mt-md-0 text-md-right">
                                    <a href="{{ route('categories') }}"
                                       class="btn btn-primary btn-sm w-100 w-md-auto">
                                        <i class="mdi mdi-settings"></i> Manage Categories
                                    </a>
                                </div>
                            </div>
                        </div>

                    @endif
                </div>
            </div>

            <!-- Master Product Form -->
            <div class="card" id="product_form" style="display:none;">
                <div class="card-header">
                    <h4 class="mb-0 px-4">Master Product Information</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('master-products.store') }}" enctype="multipart/form-data" id="master_product_form">
                        @csrf
                        <input type="hidden" name="categoryID" id="form_category_id" value="{{ $selectedCategoryId ?? '' }}" required>

                        <div class="row">
                            <div class="col-md-12">
                                <fieldset>

                                    <div class="form-group row">
                                        <label class="col-md-3 control-label">Product Name <span class="text-danger">*</span></label>
                                        <div class="col-md-9">
                                            <input type="text" name="name" value="{{ old('name') }}" class="form-control" required maxlength="255">
                                            <div class="form-text text-muted">Enter the product name (e.g., Chicken Dum Biryani)</div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-3 control-label">Description</label>
                                        <div class="col-md-9">
                                            <textarea name="description" rows="4" class="form-control">{{ old('description') }}</textarea>
                                            <div class="form-text text-muted">Detailed description of the product</div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-3 control-label">Product Image</label>
                                        <div class="col-md-9">
                                            <input type="file" name="photo" class="form-control" accept="image/*">
                                            <div class="form-text text-muted">Upload product image (max 2MB)</div>
                                        </div>
                                    </div>
                                </fieldset>

                                <fieldset>
                                    <legend>Pricing Information</legend>

                                    <div class="form-group row">
                                        <label class="col-md-3 control-label">Price</label>
                                        <div class="col-md-9">
                                            <input type="number" step="0.01" name="suggested_price" value="{{ old('suggested_price') }}" class="form-control" min="0">
                                            <div class="form-text text-muted">Product price</div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-3 control-label">Discount Price</label>
                                        <div class="col-md-9">
                                            <input type="number" step="0.01" name="dis_price" value="{{ old('dis_price') }}" class="form-control" min="0">
                                            <div class="form-text text-muted">Discounted price for this product</div>
                                        </div>
                                    </div>
                                </fieldset>

{{--                                <fieldset>--}}
{{--                                    <legend>Food Type & Attributes</legend>--}}

{{--                                    <div class="form-group row">--}}
{{--                                        <label class="col-md-3 control-label">Food Type</label>--}}
{{--                                    </div>--}}

{{--                                    <div class="form-group row">--}}
{{--                                        <label class="col-md-3 control-label">Food Type</label>--}}
{{--                                        <div class="col-md-9">--}}
{{--                                            <input type="text" name="food_type" value="{{ old('food_type') }}" class="form-control" maxlength="50" placeholder="e.g., Main Course, Appetizer">--}}
{{--                                        </div>--}}
{{--                                    </div>--}}

{{--                                    <div class="form-group row">--}}
{{--                                        <label class="col-md-3 control-label">Cuisine Type</label>--}}
{{--                                        <div class="col-md-9">--}}
{{--                                            <input type="text" name="cuisine_type" value="{{ old('cuisine_type') }}" class="form-control" maxlength="100" placeholder="e.g., Indian, Chinese, Italian">--}}
{{--                                        </div>--}}
{{--                                    </div>--}}

{{--                                    <div class="form-group row">--}}
{{--                                        <label class="col-md-3 control-label">Tags</label>--}}
{{--                                        <div class="col-md-9">--}}
{{--                                            <input type="text" name="tags" value="{{ old('tags') }}" class="form-control" maxlength="500" placeholder="e.g., spicy, popular, bestseller">--}}
{{--                                            <div class="form-text text-muted">Comma-separated tags</div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </fieldset>--}}

{{--                                <fieldset>--}}
{{--                                    <legend>Nutritional Information</legend>--}}

{{--                                    <div class="row">--}}
{{--                                        <div class="col-md-6">--}}
{{--                                            <div class="form-group">--}}
{{--                                                <label class="control-label">Calories</label>--}}
{{--                                                <input type="number" name="calories" value="{{ old('calories', 0) }}" class="form-control" min="0">--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <div class="col-md-6">--}}
{{--                                            <div class="form-group">--}}
{{--                                                <label class="control-label">Grams</label>--}}
{{--                                                <input type="number" name="grams" value="{{ old('grams', 0) }}" class="form-control" min="0">--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}

{{--                                    <div class="row">--}}
{{--                                        <div class="col-md-4">--}}
{{--                                            <div class="form-group">--}}
{{--                                                <label class="control-label">Proteins (g)</label>--}}
{{--                                                <input type="number" name="proteins" value="{{ old('proteins', 0) }}" class="form-control" min="0">--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <div class="col-md-4">--}}
{{--                                            <div class="form-group">--}}
{{--                                                <label class="control-label">Fats (g)</label>--}}
{{--                                                <input type="number" name="fats" value="{{ old('fats', 0) }}" class="form-control" min="0">--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <div class="col-md-4">--}}
{{--                                            <div class="form-group">--}}
{{--                                                <label class="control-label">Carbs (g)</label>--}}
{{--                                                <input type="number" name="carbs" value="{{ old('carbs', 0) }}" class="form-control" min="0">--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </fieldset>--}}

                                <fieldset>
{{--                                    <legend>Display Settings</legend>--}}

{{--                                    <div class="form-group row">--}}
{{--                                        <label class="col-md-3 control-label">Display Order</label>--}}
{{--                                        <div class="col-md-9">--}}
{{--                                            <input type="number" name="display_order" value="{{ old('display_order', 0) }}" class="form-control" min="0">--}}
{{--                                            <div class="form-text text-muted">Lower numbers appear first</div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}

                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="is_recommended" name="is_recommended" value="1" {{ old('is_recommended') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_recommended">Mark as Recommended</label>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="publish" name="publish" value="1" {{ old('publish', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="publish">Publish</label>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="isAvailable" name="isAvailable" value="1" {{ old('isAvailable', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="isAvailable">Available</label>
                                    </div>

{{--                                    <div class="col-md-9">--}}
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="nonveg" id="nonveg" value="1" {{ old('nonveg') == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="nonveg">Non-Veg</label>
                                        </div>
                                        <div class="form-text text-muted">If unchecked, item will be considered as Veg</div>
{{--                                    </div>--}}
                                </fieldset>

                                <div class="form-group row mt-4">
                                    <div class="col-md-12 text-center">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="mdi mdi-content-save"></i> Create Master Product
                                        </button>
                                        <a href="{{ route('master-products.index') }}" class="btn btn-secondary btn-lg">
                                            Back
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize category search
        initCategorySearch();

        // Initialize: Show selected category if page loaded with one
        @if($selectedCategory && $selectedCategoryId)
            $('#selected_category_id').val('{{ $selectedCategoryId }}');
            $('#selected_category_title').text('{{ $selectedCategory->title }}');
            $('#selected_category_display').show();
            $('#category_selector_display').hide();
            $('#category_selector').val('{{ $selectedCategoryId }}');
            $('#form_category_id').val('{{ $selectedCategoryId }}');
            $('#product_form').show();
        @endif

        // Category selector change handler - AJAX call
        $('#category_selector').on('change', function() {
            var categoryId = $(this).val();
            var categoryTitle = $(this).find('option:selected').text();

            if (categoryId && categoryId !== '') {
                // Show loading state
                var originalHtml = $('#category_selector_display').html();
                $('#category_selector_display').html('<div class="text-center py-3"><i class="fa fa-spinner fa-spin"></i> Loading category...</div>');

                // Make AJAX call to verify category and get details
                $.ajax({
                    url: '{{ route("master-products.create") }}',
                    type: 'GET',
                    data: {
                        category_id: categoryId,
                        ajax: true
                    },
                    dataType: 'json',
                    success: function(response) {
                        // Update hidden fields
                        $('#form_category_id').val(categoryId);
                        $('#selected_category_id').val(categoryId);

                        // Use category title from response or fallback to select option
                        var displayTitle = (response.category && response.category.title)
                            ? response.category.title
                            : categoryTitle;

                        $('#selected_category_title').text(displayTitle);

                        // Show selected category display and hide selector
                        $('#selected_category_display').slideDown();
                        $('#category_selector_display').hide();

                        // Show form
                        $('#product_form').slideDown();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading category:', error);

                        // Even on error, use the selected option's title
                        $('#form_category_id').val(categoryId);
                        $('#selected_category_id').val(categoryId);
                        $('#selected_category_title').text(categoryTitle);

                        // Show selected category display and hide selector
                        $('#selected_category_display').slideDown();
                        $('#category_selector_display').hide();

                        // Show form
                        $('#product_form').slideDown();
                    }
                });
            } else {
                // Hide form if no category selected
                $('#product_form').slideUp();
                $('#selected_category_display').slideUp();
                $('#category_selector_display').show();
                $('#form_category_id').val('');
                $('#selected_category_id').val('');
            }
        });

        // Remove category button handler
        $('#remove_category_btn').on('click', function() {
            if (confirm('Are you sure you want to remove the selected category?')) {
                // Clear selection
                $('#category_selector').val('');
                $('#form_category_id').val('');
                $('#selected_category_id').val('');

                // Hide selected category display and show selector
                $('#selected_category_display').slideUp();
                $('#category_selector_display').show();

                // Hide form
                $('#product_form').slideUp();
            }
        });

        // Initialize category search function
        function initCategorySearch() {
            $('#category_search').on('keyup', function() {
                var search = $(this).val().toLowerCase();
                $('#category_selector option').each(function() {
                    if ($(this).val() === "") {
                        $(this).show();
                        return;
                    }
                    var text = $(this).text().toLowerCase();
                    if (text.indexOf(search) > -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        }

        // Form validation
        $('#master_product_form').on('submit', function(e) {
            var categoryId = $('#form_category_id').val();
            if (!categoryId) {
                e.preventDefault();
                alert('Please select a category first');
                return false;
            }
        });

        // Handle non-veg checkbox - ensure veg is set when unchecked
        $('#nonveg').on('change', function() {
            // If non-veg is unchecked, the form will submit nonveg=0 (veg)
            // If checked, nonveg=1 (non-veg)
        });
    });
</script>
@endsection

