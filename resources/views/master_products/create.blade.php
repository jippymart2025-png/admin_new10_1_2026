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
                           <div class="col-md-8 col-sm-12">
                            <input type="text"
                                   id="category_search"
                                   class="form-control"
                                   placeholder="🔍 Search category..."
                                   autocomplete="off">

                            <input type="hidden" name="categoryID" id="selected_category_id" required>

                            <div id="category_suggestions" class="list-group position-absolute w-100"
                                 style="z-index:1000; display:none; max-height:220px; overflow-y:auto;">
                            </div>

                            <small class="form-text text-muted mt-1">
                                Start typing and select a category
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
                                            <input
                                                type="text"
                                                id="product_name"
                                                name="name"
                                                class="form-control"
                                                required
                                            >
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


                            </div>
                        </div>
                        <!-- Options Configuration -->
                        <fieldset>
                            <legend>Options Configuration</legend>

                            <div class="form-check width-100">
                                <input type="checkbox" class="has_options" id="has_options" name="has_options" value="1">
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

                        <!-- Options Management -->
                        <!-- OPTIONS LIST -->
                        <fieldset id="options_fieldset" class="mt-4" style="display:none;">
                            <legend>Product Options</legend>

                            <div class="options-list"></div>

                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-primary" onclick="addNewOption()">
                                    <i class="mdi mdi-plus"></i> Add Option
                                </button>
                            </div>
                        </fieldset>

                        <input type="hidden" name="options_data" id="options_data">


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
                    </form>

                    <!-- Option Template (Hidden) -->
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
                                    <input type="text" class="form-control option-title" placeholder="e.g. Family Pack" required>
                                </div>

                                <div class="col-md-6">
                                    <label>Option Subtitle</label>
                                    <input type="text" class="form-control option-subtitle" placeholder="e.g. Serves 3–4">
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-4">
                                    <label>Price (₹)</label>
                                    <input type="number" class="form-control option-price" min="0" required>
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
                </div>
                </div>
            </div>
@endsection
@section('scripts')
    <script>
        /* ===============================
           CATEGORY SELECTION LOGIC
        ================================ */
        $(document).ready(function () {

            initCategorySearch();

            @if($selectedCategory && $selectedCategoryId)
            $('#form_category_id').val('{{ $selectedCategoryId }}');
            $('#product_form').show();
            @endif

            $('#category_selector').on('change', function () {
                const categoryId = $(this).val();
                const categoryTitle = $(this).find('option:selected').text();

                if (!categoryId) {
                    $('#product_form').slideUp();
                    $('#form_category_id').val('');
                    return;
                }

                $.ajax({
                    url: '{{ route("master-products.create") }}',
                    type: 'GET',
                    data: { category_id: categoryId, ajax: true },
                    dataType: 'json',
                    success: function (res) {
                        $('#form_category_id').val(categoryId);
                        $('#product_form').slideDown();
                    },
                    error: function () {
                        $('#form_category_id').val(categoryId);
                        $('#product_form').slideDown();
                    }
                });
            });

            $('#remove_category_btn').on('click', function () {
                if (!confirm('Remove selected category?')) return;
                $('#category_selector').val('');
                $('#form_category_id').val('');
                $('#product_form').slideUp();
            });

            function initCategorySearch() {
                const categories = @json($vendorCategories);

                $('#category_search').on('keyup', function () {
                    const keyword = $(this).val().toLowerCase();
                    const suggestions = $('#category_suggestions');

                    suggestions.empty();

                    if (!keyword) {
                        suggestions.hide();
                        return;
                    }

                    const matches = categories.filter(c =>
                        c.title.toLowerCase().includes(keyword)
                    );

                    if (!matches.length) {
                        suggestions.hide();
                        return;
                    }

                    matches.forEach(cat => {
                        suggestions.append(`
            <button type="button"
                    class="list-group-item list-group-item-action"
                    data-id="${cat.id}"
                    data-title="${cat.title}">
                ${cat.title}
            </button>
        `);
                    });

                    suggestions.show();
                });

// Select category
                $(document).on('click', '#category_suggestions button', function () {
                    const id = $(this).data('id');
                    const title = $(this).data('title');

                    $('#category_search').val(title);
                    $('#form_category_id').val(id);
                    $('#category_suggestions').hide();

                    // OPTIONAL: show product form
                    $('#product_form').slideDown();
                });

// Hide dropdown when clicking outside
                $(document).on('click', function (e) {
                    if (!$(e.target).closest('#category_search, #category_suggestions').length) {
                        $('#category_suggestions').hide();
                    }
                });

            }

            $('#master_product_form').on('submit', function (e) {
                if (!$('#form_category_id').val()) {
                    e.preventDefault();
                    alert('Please select a category first');
                }
            });
        });


        /* ===============================
           OPTIONS TOGGLE
        ================================ */
        $(document).ready(function () {
            function toggleOptions() {
                $('#has_options').is(':checked')
                    ? $('#options_config, #options_fieldset').slideDown()
                    : $('#options_config, #options_fieldset').slideUp();
            }

            toggleOptions();
            $('#has_options').on('change', toggleOptions);
        });


        /* ===============================
           OPTIONS ENGINE
        ================================ */
        // let optionsList = [];
        // let optionCounter = 0;
        //
        // function addNewOption() {
        //     optionCounter++;
        //     const id = 'opt_' + Date.now();
        //
        //     const tpl = $('#option_template .option-item').clone();
        //     tpl.attr('data-option-id', id);
        //     tpl.find('.option-number').text(optionCounter);
        //     tpl.find('.option-title').val($('#name').val());
        //
        //     $('.options-list').append(tpl.show());
        //
        //     optionsList.push({
        //         id,
        //         title: $('#name').val(),
        //         unit_price: 0,
        //         quantity: 0,
        //         price: 0,
        //         is_featured: false
        //     });
        //
        //     bindOptionEvents(id);
        //     updateOptionsSummary();
        // }
        //
        // function removeOption(btn) {
        //     const box = $(btn).closest('.option-item');
        //     const id = box.data('option-id');
        //     optionsList = optionsList.filter(o => o.id !== id);
        //     box.remove();
        //     updateOptionsSummary();
        // }
        //
        // function bindOptionEvents(id) {
        //     const el = $(`[data-option-id="${id}"]`);
        //     const idx = optionsList.findIndex(o => o.id === id);
        //
        //     el.find('.option-unit-price, .option-quantity').on('input', function () {
        //         const price = parseFloat(el.find('.option-unit-price').val()) || 0;
        //         const qty = parseFloat(el.find('.option-quantity').val()) || 0;
        //         const total = price * qty;
        //
        //         el.find('.option-total-price').val(total.toFixed(2));
        //         optionsList[idx].unit_price = price;
        //         optionsList[idx].quantity = qty;
        //         optionsList[idx].price = total;
        //
        //         updateOptionsSummary();
        //     });
        // }
        //
        // function updateOptionsSummary() {
        //     if (!optionsList.length) {
        //         $('.options-summary').hide();
        //         return;
        //     }
        //
        //     const prices = optionsList.map(o => o.price).filter(p => p > 0);
        //     if (!prices.length) return;
        //
        //     $('.options-summary').show();
        //     $('.summary-content').html(
        //         `Total Options: ${optionsList.length}`
        //     );
        // }
        let optionsList = [];
        let optionCounter = 0;

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

        function bindOptionEvents(id) {
            const el = $(`[data-option-id="${id}"]`);
            const idx = optionsList.findIndex(o => o.id === id);

            el.find('.option-title').on('input', function () {
                optionsList[idx].title = this.value;
            });

            el.find('.option-subtitle').on('input', function () {
                optionsList[idx].subtitle = this.value;
            });

            el.find('.option-price').on('input', function () {
                optionsList[idx].price = parseFloat(this.value) || 0;
            });

            el.find('.option-original-price').on('input', function () {
                optionsList[idx].original_price = parseFloat(this.value) || 0;
            });

            el.find('.option-available').on('change', function () {
                optionsList[idx].is_available = this.checked;
            });

            el.find('.option-featured').on('change', function () {
                optionsList[idx].is_featured = this.checked;
            });
        }

        /* ===============================
           SERIALIZE OPTIONS
        ================================ */
        $('#master_product_form').on('submit', function () {
            $('#options_data').val(JSON.stringify(optionsList));
        });
    </script>
@endsection
