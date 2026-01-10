@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">Mart Items</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('mart-items') }}">Mart Items</a></li>
                    <li class="breadcrumb-item active">Create</li>
            </ol>
        </div>
    </div>

        <div class="container-fluid">
            <div class="card border">
        <div class="card-body">
                    <h4 class="card-title mb-4">New Mart Item</h4>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                                        </div>
                    @endif

                    <form method="POST" action="{{ route('mart-items.store') }}" enctype="multipart/form-data">
                        @csrf

                        @if ($restaurantId)
                            <input type="hidden" name="vendorID" value="{{ $restaurantId }}">
                            <div class="alert alert-info">
                                <strong>Mart:</strong>
                                {{ optional($vendors->firstWhere('id', $restaurantId))->title ?? 'Selected mart' }}
                            </div>
                        @else
                            <div class="form-group">
                                <label for="vendorID">Mart <span class="text-danger">*</span></label>
                                <select class="form-control" name="vendorID" id="vendorID" required>
                                    <option value="">Select Mart</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ old('vendorID') == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->title }}
                                        </option>
                                    @endforeach
                                    </select>
                                    </div>
                        @endif

                        <div class="form-group">
                            <label for="name">Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="price">Price <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" value="{{ old('price') }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="disPrice">Discount Price</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="disPrice" name="disPrice" value="{{ old('disPrice') }}">
                        </div>
                            </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="categoryID">Category <span class="text-danger">*</span></label>
                                <select class="form-control" id="categoryID" name="categoryID" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" data-section="{{ $category->section }}" {{ old('categoryID') == $category->id ? 'selected' : '' }}>
                                            {{ $category->title }}
                                        </option>
                                    @endforeach
                                </select>
                </div>
                            <div class="form-group col-md-6">
                                <label for="subcategoryID">Subcategory</label>
                                <select class="form-control" id="subcategoryID" name="subcategoryID">
                                    <option value="">Select Subcategory</option>
                                    @foreach($subcategories as $subcategory)
                                        <option value="{{ $subcategory->id }}"
                                                data-parent="{{ $subcategory->parent_category_id }}"
                                                data-section="{{ $subcategory->section }}"
                                                {{ old('subcategoryID') == $subcategory->id ? 'selected' : '' }}>
                                            {{ $subcategory->title }}
                                        </option>
                                    @endforeach
                    </select>
                </div>
            </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="brandID">Brand</label>
                                <select class="form-control" id="brandID" name="brandID">
                                    <option value="">Select Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ old('brandID') == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                    </select>
                </div>
                            <div class="form-group col-md-6">
                                <label for="section">Section</label>
                                <input type="text" class="form-control" id="section" name="section" value="{{ old('section', 'General') }}" readonly>
            </div>
        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="quantity">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="-1" value="{{ old('quantity', -1) }}">
                                <small class="form-text text-muted">Use -1 for unlimited stock.</small>
                </div>
                            <div class="form-group col-md-6">
                                <label for="photo">Item Image</label>
                                <input type="file" class="form-control-file" id="photo" name="photo" accept="image/*">
            </div>
        </div>

                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="calories">Calories</label>
                                <input type="number" class="form-control" id="calories" name="calories" min="0" value="{{ old('calories') }}">
                </div>
                            <div class="form-group col-md-3">
                                <label for="grams">Grams</label>
                                <input type="number" class="form-control" id="grams" name="grams" min="0" value="{{ old('grams') }}">
            </div>
                            <div class="form-group col-md-3">
                                <label for="proteins">Proteins</label>
                                <input type="number" class="form-control" id="proteins" name="proteins" min="0" value="{{ old('proteins') }}">
                </div>
                            <div class="form-group col-md-3">
                                <label for="fats">Fats</label>
                                <input type="number" class="form-control" id="fats" name="fats" min="0" value="{{ old('fats') }}">
            </div>
        </div>

                <div class="form-group">
                            <label for="description">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="5" required>{{ old('description') }}</textarea>
            </div>

                <div class="form-group">
                            <label>Item Features</label>
        <div class="row">
                                @php
                                    $featureFields = [
                                        'isSpotlight' => 'Spotlight',
                                        'isStealOfMoment' => 'Steal of Moment',
                                        'isFeature' => 'Featured',
                                        'isTrending' => 'Trending',
                                        'isNew' => 'New Arrival',
                                        'isBestSeller' => 'Best Seller',
                                        'isSeasonal' => 'Seasonal',
                                    ];
                                @endphp
                                @foreach($featureFields as $field => $label)
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="{{ $field }}" name="{{ $field }}" value="1" {{ old($field) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="{{ $field }}">{{ $label }}</label>
                </div>
            </div>
                                @endforeach
            </div>
        </div>

                <div class="form-group">
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" id="publish" name="publish" value="1" {{ old('publish', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="publish">Publish</label>
                </div>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" id="isAvailable" name="isAvailable" value="1" {{ old('isAvailable', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isAvailable">Available</label>
            </div>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" id="nonveg" name="nonveg" value="1" {{ old('nonveg') ? 'checked' : '' }}>
                                <label class="form-check-label" for="nonveg">Non Veg</label>
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

                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">Default Option</label>
                                    <div class="col-7">
                                        <select id="default_option" class="form-control">
                                            <option value="">Select default option</option>
                                        </select>
                                        <div class="form-text text-muted">
                                            The default option will be automatically selected when customers view this item.
                                            This is typically the featured option or the most popular choice.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <!-- Options Management -->
                        <fieldset id="options_fieldset" style="display:none;">
                            <legend>Item Options</legend>

                            <div class="options-list">
                                <!-- Dynamic options will be added here -->
                            </div>

                            <div class="form-group row width-100">
                                <div class="col-12 text-center">
                                    <button type="button" class="btn btn-primary" onclick="addNewOption()">
                                        <i class="mdi mdi-plus"></i> Add Option
                                    </button>
                                </div>
                            </div>

                            <div class="options-summary" style="display:none;">
                                <h5>Options Summary</h5>
                                <div class="summary-content">
                                    <!-- Will show price range and option count -->
                                </div>
                            </div>
                        </fieldset>

                        <!-- Hidden input for options data -->
                        <input type="hidden" name="options_data" id="options_data" value="">

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Save Item
                            </button>
                            <a href="{{ route('mart-items') }}" class="btn btn-secondary">Cancel</a>
                </div>
                    </form>
                    
                    <!-- Option Template (Hidden) -->
                    <div id="option_template" style="display:none;">
                        <div class="option-item" data-option-id="">
                            <div class="option-header">
                                <h5>Option #<span class="option-number"></span></h5>
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this)">
                                    <i class="mdi mdi-delete"></i>
                                </button>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Option Type</label>
                                        <select class="form-control option-type">
                                            <option value="size">Size/Weight (kg, g, mg)</option>
                                            <option value="volume">Volume (L, ml, cl)</option>
                                            <option value="quantity">Quantity (pcs, units)</option>
                                            <option value="pack">Pack (dozen, bundle)</option>
                                            <option value="bundle">Bundle (mixed items)</option>
                                            <option value="addon">Add-on (extras)</option>
                                            <option value="variant">Variant (organic, premium)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Unit</label>
                                        <select class="form-control option-quantity-unit">
                                            <option value="g">Grams (g)</option>
                                            <option value="kg">Kilograms (kg)</option>
                                            <option value="mg">Milligrams (mg)</option>
                                            <option value="L">Liters (L)</option>
                                            <option value="ml">Milliliters (ml)</option>
                                            <option value="pcs">Pieces (pcs)</option>
                                            <option value="units">Units</option>
                                            <option value="dozen">Dozen</option>
                                            <option value="custom">Custom</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Unit Price (₹)</label>
                                        <input type="number" class="form-control option-unit-price" step="0.01" min="0" placeholder="Price per unit">
                                        <small class="form-text text-muted">Price per unit (will calculate total price)</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Original Unit Price (₹)</label>
                                        <input type="number" class="form-control option-original-unit-price" step="0.01" min="0" placeholder="Original price per unit">
                                        <small class="form-text text-muted">Original price per unit (for discount calculation)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="number" class="form-control option-quantity" min="0">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Unit Measure Base</label>
                                        <input type="number" class="form-control option-unit-measure" value="100">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Option Title</label>
                                        <input type="text" class="form-control option-title" placeholder="Auto-filled from item name" readonly>
                                        <small class="form-text text-muted">Auto-filled from main item name</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Option Subtitle</label>
                                        <input type="text" class="form-control option-subtitle" placeholder="Auto-generated: unit_measure + quantity_unit + x + quantity" readonly>
                                        <small class="form-text text-muted">Auto-generated format: 500ml x 2</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Total Price (₹)</label>
                                        <input type="number" class="form-control option-total-price" step="0.01" readonly>
                                        <small class="form-text text-muted">Auto-calculated: Unit Price × Quantity</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Original Total Price (₹)</label>
                                        <input type="number" class="form-control option-original-total-price" step="0.01" readonly>
                                        <small class="form-text text-muted">Auto-calculated: Original Unit Price × Quantity</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Discount Amount (₹)</label>
                                        <input type="number" class="form-control option-discount-amount" step="0.01" readonly>
                                        <small class="form-text text-muted">Auto-calculated: Original Total - Total Price</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Discount Percentage (%)</label>
                                        <input type="number" class="form-control option-discount-percentage" step="0.01" readonly>
                                        <small class="form-text text-muted">Auto-calculated discount percentage</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Savings Display</label>
                                        <input type="text" class="form-control option-savings-display" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Smart Suggestions</label>
                                        <div class="smart-suggestions-display">
                                            <small class="text-muted">Select option type for smart defaults</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Option Image</label>
                                <input type="file" class="option-image-input" accept="image/*">
                                <div class="option-image-preview"></div>
                            </div>

                            <div class="form-check">
                                <input type="checkbox" class="option-available" id="option_available_" checked>
                                <label class="form-check-label" for="option_available_">Available</label>
                            </div>

                            <div class="form-check">
                                <input type="checkbox" class="option-featured" id="option_featured_">
                                <label class="form-check-label" for="option_featured_">Featured (Show first)</label>
                            </div>
                            
                            <div class="validation-feedback"></div>
                        </div>
                    </div>
            </div>
                    </div>
                </div>
            </div>
@endsection

@section('scripts')
    <script>
        (function () {
            const categorySelect = document.getElementById('categoryID');
            const subcategorySelect = document.getElementById('subcategoryID');
            const sectionInput = document.getElementById('section');
            const subcategories = @json($subcategories);

            function filterSubcategories(categoryId) {
                const current = subcategorySelect.value;
                subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';

                subcategories.forEach(sub => {
                    if (!categoryId || String(sub.parent_category_id) === String(categoryId)) {
                        const option = document.createElement('option');
                        option.value = sub.id;
                        option.dataset.section = sub.section || '';
                        option.textContent = sub.title;
                        if (current && String(current) === String(sub.id)) {
                            option.selected = true;
                        }
                        subcategorySelect.appendChild(option);
                    }
                });
            }

            function updateSectionFromSelection() {
                const subOption = subcategorySelect.options[subcategorySelect.selectedIndex];
                if (subOption && subOption.dataset.section) {
                    sectionInput.value = subOption.dataset.section;
                    return;
                }

                const catOption = categorySelect.options[categorySelect.selectedIndex];
                if (catOption && catOption.dataset.section) {
                    sectionInput.value = catOption.dataset.section;
                } else {
                    sectionInput.value = 'General';
                }
            }

            categorySelect.addEventListener('change', function () {
                filterSubcategories(this.value);
                updateSectionFromSelection();
            });

            subcategorySelect.addEventListener('change', updateSectionFromSelection);

            // Initialise
            filterSubcategories(categorySelect.value);
            updateSectionFromSelection();
        })();

        // Options Configuration Toggle
        $(document).ready(function() {
            const hasOptionsCheckbox = $('#has_options');
            const optionsConfig = $('#options_config');
            const optionsFieldset = $('#options_fieldset');

            // Initialize visibility based on checkbox state (immediate, no animation)
            function toggleOptionsVisibility(animate = true) {
                if (hasOptionsCheckbox.is(':checked')) {
                    if (animate) {
                        optionsConfig.slideDown(300);
                        optionsFieldset.slideDown(300);
                    } else {
                        optionsConfig.show();
                        optionsFieldset.show();
                    }
                } else {
                    if (animate) {
                        optionsConfig.slideUp(300);
                        optionsFieldset.slideUp(300);
                    } else {
                        optionsConfig.hide();
                        optionsFieldset.hide();
                    }
                }
            }

            // Set initial state (no animation on page load)
            toggleOptionsVisibility(false);

            // Handle checkbox change (with animation)
            hasOptionsCheckbox.on('change', function() {
                toggleOptionsVisibility(true);
            });
        });

        // Options Management
        let optionsList = [];
        let optionCounter = 0;

        // Add new option
        function addNewOption() {
            optionCounter++;
            const optionId = 'option_' + Date.now() + '_' + optionCounter;
            const template = $('#option_template .option-item').clone();
            
            template.attr('data-option-id', optionId);
            template.find('.option-number').text(optionCounter);
            
            // Set option title from main item name
            const itemName = $('#name').val() || '';
            template.find('.option-title').val(itemName);
            
            // Generate unique IDs for checkboxes
            const availableId = 'option_available_' + optionCounter;
            const featuredId = 'option_featured_' + optionCounter;
            template.find('.option-available').attr('id', availableId);
            template.find('.option-featured').attr('id', featuredId);
            template.find('.option-available').next('label').attr('for', availableId);
            template.find('.option-featured').next('label').attr('for', featuredId);
            
            $('.options-list').append(template);
            template.show();
            
            // Add to options list
            optionsList.push({
                id: optionId,
                type: 'size',
                title: itemName,
                subtitle: '',
                unit_price: 0,
                original_unit_price: 0,
                total_price: 0,
                original_total_price: 0,
                quantity: 0,
                quantity_unit: 'g',
                unit_measure: 100,
                discount_amount: 0,
                discount_percentage: 0,
                image: '',
                is_available: true,
                is_featured: false
            });
            
            attachOptionEventListeners(optionId);
            updateOptionsSummary();
            updateDefaultOptionSelect();
        }

        // Remove option
        function removeOption(button) {
            const optionItem = $(button).closest('.option-item');
            const optionId = optionItem.data('option-id');
            
            // Remove from array
            optionsList = optionsList.filter(opt => opt.id !== optionId);
            
            // Remove from DOM
            optionItem.remove();
            
            // Update option numbers
            updateOptionNumbers();
            updateOptionsSummary();
            updateDefaultOptionSelect();
        }

        // Update option numbers
        function updateOptionNumbers() {
            $('.option-item').each(function(index) {
                $(this).find('.option-number').text(index + 1);
            });
        }

        // Attach event listeners to option
        function attachOptionEventListeners(optionId) {
            const optionItem = $(`[data-option-id="${optionId}"]`);
            const optionIndex = optionsList.findIndex(opt => opt.id === optionId);
            
            if (optionIndex === -1) return;
            
            // Option type change
            optionItem.find('.option-type').on('change', function() {
                const type = $(this).val();
                optionsList[optionIndex].type = type;
                updateSmartSuggestions(optionId, type);
                calculateOptionCalculations(optionId);
            });
            
            // Unit change
            optionItem.find('.option-quantity-unit').on('change', function() {
                const unit = $(this).val();
                optionsList[optionIndex].quantity_unit = unit;
                calculateOptionCalculations(optionId);
            });
            
            // Unit price change
            optionItem.find('.option-unit-price').on('input', function() {
                const price = parseFloat($(this).val()) || 0;
                optionsList[optionIndex].unit_price = price;
                calculateOptionCalculations(optionId);
            });
            
            // Original unit price change
            optionItem.find('.option-original-unit-price').on('input', function() {
                const price = parseFloat($(this).val()) || 0;
                optionsList[optionIndex].original_unit_price = price;
                calculateOptionCalculations(optionId);
            });
            
            // Quantity change
            optionItem.find('.option-quantity').on('input', function() {
                const qty = parseFloat($(this).val()) || 0;
                optionsList[optionIndex].quantity = qty;
                calculateOptionCalculations(optionId);
                validateOption(optionId);
            });
            
            // Unit measure change
            optionItem.find('.option-unit-measure').on('input', function() {
                const measure = parseFloat($(this).val()) || 100;
                optionsList[optionIndex].unit_measure = measure;
                calculateOptionCalculations(optionId);
            });
            
            // Available checkbox
            optionItem.find('.option-available').on('change', function() {
                const isChecked = $(this).is(':checked');
                optionsList[optionIndex].is_available = isChecked;
                
                // Visual feedback
                if (isChecked) {
                    optionItem.removeClass('option-disabled').addClass('option-enabled');
                } else {
                    optionItem.removeClass('option-enabled').addClass('option-disabled');
                }
                validateOption(optionId);
            });
            
            // Featured checkbox
            optionItem.find('.option-featured').on('change', function() {
                const isFeatured = $(this).is(':checked');
                optionsList[optionIndex].is_featured = isFeatured;
                
                // Uncheck other featured options
                if (isFeatured) {
                    $('.option-featured').not(this).prop('checked', false);
                    optionsList.forEach(opt => {
                        if (opt.id !== optionId) {
                            opt.is_featured = false;
                            $(`[data-option-id="${opt.id}"]`).removeClass('option-featured-highlight');
                        }
                    });
                    
                    // Visual feedback
                    optionItem.addClass('option-featured-highlight');
                } else {
                    optionItem.removeClass('option-featured-highlight');
                }
                updateDefaultOptionSelect();
            });
            
            // Image upload
            optionItem.find('.option-image-input').on('change', function(e) {
                handleOptionImageUpload(e.target, optionId);
            });
            
            // Listen for main item name changes to update all option titles
            $('#name').on('input', function() {
                $('.option-title').val($(this).val());
                optionsList.forEach(opt => {
                    opt.title = $(this).val();
                });
                updateOptionsSummary();
                updateDefaultOptionSelect();
            });
            
            // Initial calculations
            calculateOptionCalculations(optionId);
            updateSmartSuggestions(optionId, 'size');
        }

        // Calculate option values
        function calculateOptionCalculations(optionId) {
            const optionItem = $(`[data-option-id="${optionId}"]`);
            const optionIndex = optionsList.findIndex(opt => opt.id === optionId);
            
            if (optionIndex === -1) return;
            
            const unitPrice = parseFloat(optionItem.find('.option-unit-price').val()) || 0;
            const originalUnitPrice = parseFloat(optionItem.find('.option-original-unit-price').val()) || 0;
            const quantity = parseFloat(optionItem.find('.option-quantity').val()) || 0;
            const unitMeasure = parseFloat(optionItem.find('.option-unit-measure').val()) || 100;
            const quantityUnit = optionItem.find('.option-quantity-unit').val() || 'g';
            
            // Calculate total prices
            const totalPrice = unitPrice * quantity;
            const originalTotalPrice = originalUnitPrice * quantity;
            
            // Calculate discount
            let discountAmount = 0;
            let discountPercentage = 0;
            if (originalTotalPrice > 0 && originalTotalPrice > totalPrice) {
                discountAmount = originalTotalPrice - totalPrice;
                discountPercentage = (discountAmount / originalTotalPrice) * 100;
            }
            
            // Update calculated fields
            optionItem.find('.option-total-price').val(totalPrice.toFixed(2));
            optionItem.find('.option-original-total-price').val(originalTotalPrice.toFixed(2));
            optionItem.find('.option-discount-amount').val(discountAmount.toFixed(2));
            optionItem.find('.option-discount-percentage').val(discountPercentage.toFixed(2));
            
            // Update savings display
            let savingsDisplay = '';
            if (discountAmount > 0) {
                savingsDisplay = `Save ₹${discountAmount.toFixed(2)} (${discountPercentage.toFixed(1)}%)`;
            }
            optionItem.find('.option-savings-display').val(savingsDisplay);
            
            // Auto-generate subtitle
            const formattedUnitMeasure = unitMeasure % 1 === 0 ? unitMeasure.toString() : unitMeasure.toFixed(2);
            const subtitle = `${formattedUnitMeasure}${quantityUnit} x ${quantity}`;
            optionItem.find('.option-subtitle').val(subtitle);
            
            // Update options list
            optionsList[optionIndex].total_price = totalPrice;
            optionsList[optionIndex].original_total_price = originalTotalPrice;
            optionsList[optionIndex].discount_amount = discountAmount;
            optionsList[optionIndex].discount_percentage = discountPercentage;
            optionsList[optionIndex].subtitle = subtitle;
            
            updateOptionsSummary();
        }

        // Update smart suggestions
        function updateSmartSuggestions(optionId, optionType) {
            const optionItem = $(`[data-option-id="${optionId}"]`);
            const suggestions = optionItem.find('.smart-suggestions-display');
            
            const typeDefaults = {
                'size': { desc: 'Weight-based options', units: 'g, kg' },
                'volume': { desc: 'Volume-based options', units: 'L, ml' },
                'quantity': { desc: 'Count-based options', units: 'pcs, units' },
                'pack': { desc: 'Packaged options', units: 'pcs, dozen' },
                'bundle': { desc: 'Mixed item bundles', units: 'pcs, custom' },
                'addon': { desc: 'Additional items', units: 'pcs, units' },
                'variant': { desc: 'Product variants', units: 'pcs, units' }
            };
            
            const defaults = typeDefaults[optionType] || typeDefaults['size'];
            suggestions.html(`<small class="text-muted">${defaults.desc}<br>Suggested units: ${defaults.units}</small>`);
        }

        // Validate option
        function validateOption(optionId) {
            const optionItem = $(`[data-option-id="${optionId}"]`);
            const optionIndex = optionsList.findIndex(opt => opt.id === optionId);
            const feedback = optionItem.find('.validation-feedback');
            
            if (optionIndex === -1) return;
            
            const option = optionsList[optionIndex];
            let warnings = [];
            
            // Check quantity
            if (option.quantity === 0) {
                warnings.push('Option automatically disabled due to zero quantity');
                if (option.is_available) {
                    optionItem.find('.option-available').prop('checked', false);
                    option.is_available = false;
                    optionItem.removeClass('option-enabled').addClass('option-disabled');
                }
            }
            
            // Display warnings
            if (warnings.length > 0) {
                let html = '<div class="alert alert-warning mt-2"><strong>Warnings:</strong><ul class="mb-0">';
                warnings.forEach(warning => {
                    html += `<li>${warning}</li>`;
                });
                html += '</ul></div>';
                feedback.html(html);
            } else {
                feedback.html('');
            }
        }

        // Handle option image upload
        function handleOptionImageUpload(input, optionId) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const optionItem = $(`[data-option-id="${optionId}"]`);
                    const base64Image = e.target.result;
                    
                    // Show preview
                    optionItem.find('.option-image-preview').html(
                        `<img src="${base64Image}" style="max-width: 100px; max-height: 100px; border-radius: 4px; margin-top: 10px;" class="img-thumbnail">`
                    );
                    
                    // Store in options list (will be converted to URL on save)
                    const optionIndex = optionsList.findIndex(opt => opt.id === optionId);
                    if (optionIndex !== -1) {
                        optionsList[optionIndex].image = base64Image;
                    }
                };
                reader.readAsDataURL(file);
            }
        }

        // Update options summary
        function updateOptionsSummary() {
            if (optionsList.length > 0) {
                const prices = optionsList.map(opt => parseFloat(opt.total_price) || 0).filter(p => p > 0 && isFinite(p));
                if (prices.length > 0) {
                    const minPrice = Math.min(...prices);
                    const maxPrice = Math.max(...prices);
                    $('.options-summary').show();
                    $('.summary-content').html(`
                        <div class="row">
                            <div class="col-md-4"><strong>Price Range:</strong> ₹${minPrice.toFixed(2)} - ₹${maxPrice.toFixed(2)}</div>
                            <div class="col-md-4"><strong>Total Options:</strong> ${optionsList.length}</div>
                            <div class="col-md-4"><strong>Featured Option:</strong> ${optionsList.find(opt => opt.is_featured)?.title || 'None'}</div>
                        </div>
                    `);
                } else {
                    $('.options-summary').show();
                    $('.summary-content').html(`
                        <div class="row">
                            <div class="col-md-4"><strong>Price Range:</strong> Not set</div>
                            <div class="col-md-4"><strong>Total Options:</strong> ${optionsList.length}</div>
                            <div class="col-md-4"><strong>Featured Option:</strong> ${optionsList.find(opt => opt.is_featured)?.title || 'None'}</div>
                        </div>
                    `);
                }
            } else {
                $('.options-summary').hide();
            }
        }

        // Update default option select
        function updateDefaultOptionSelect() {
            const select = $('#default_option');
            select.empty();
            select.append('<option value="">Select default option</option>');
            optionsList.forEach(option => {
                const isFeatured = option.is_featured ? ' (Featured)' : '';
                select.append(`<option value="${option.id}">${option.title || 'Option'}${isFeatured}</option>`);
            });
        }

        // Serialize options data before form submission
        $(document).ready(function() {
            $('form').on('submit', function(e) {
                // Serialize options data
                const optionsData = optionsList.map((option, index) => ({
                    id: option.id || `option_${Date.now()}_${index}`,
                    option_type: option.type || 'size',
                    option_title: option.title || $('#name').val() || '',
                    option_subtitle: option.subtitle || '',
                    unit_price: parseFloat(option.unit_price) || 0,
                    original_unit_price: parseFloat(option.original_unit_price) || 0,
                    price: parseFloat(option.total_price) || 0,
                    original_price: parseFloat(option.original_total_price) || 0,
                    discount_amount: parseFloat(option.discount_amount) || 0,
                    discount_percentage: parseFloat(option.discount_percentage) || 0,
                    unit_measure: parseFloat(option.unit_measure) || 100,
                    unit_measure_type: option.quantity_unit || 'g',
                    quantity: parseFloat(option.quantity) || 0,
                    quantity_unit: option.quantity_unit || 'g',
                    image: option.image || '',
                    is_available: option.is_available !== false,
                    is_featured: option.is_featured === true,
                    sort_order: index + 1
                }));
                
                // Store in hidden input
                $('#options_data').val(JSON.stringify(optionsData));
            });
        });
    </script>

    <style>
        .option-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: #f9f9f9;
        }
        .option-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .option-header h5 {
            margin: 0;
            color: #333;
        }
        .option-image-preview {
            margin-top: 10px;
        }
        .option-image-preview img {
            border: 1px solid #ddd;
            border-radius: 4px;
            max-width: 100px;
            max-height: 100px;
        }
        .option-enabled {
            border-color: #28a745 !important;
            background: #f8fff9 !important;
        }
        .option-disabled {
            border-color: #dc3545 !important;
            background: #fff8f8 !important;
            opacity: 0.7;
        }
        .option-featured-highlight {
            border-color: #ffc107 !important;
            background: #fffdf0 !important;
            box-shadow: 0 0 10px rgba(255, 193, 7, 0.3);
        }
        .option-item .form-check {
            margin: 15px 0;
            padding: 10px;
            border-radius: 4px;
            background: #f8f9fa;
        }
        .option-item .form-check input[type="checkbox"] {
            margin-right: 8px;
            transform: scale(1.2);
        }
        .option-item .form-check label {
            font-weight: 500;
            cursor: pointer;
            margin-bottom: 0;
        }
        .option-item .form-check:hover {
            background: #e9ecef;
        }
        .options-summary {
            background: #e8f5e8;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            padding: 15px;
            margin-top: 20px;
        }
        .summary-content {
            margin: 0;
        }
        #options_config {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-top: 15px;
        }
        .validation-feedback .alert {
            margin-top: 10px;
            margin-bottom: 0;
        }
        .smart-suggestions-display {
            padding: 10px;
            background: #e7f3ff;
            border-radius: 4px;
            border: 1px solid #b3d9ff;
        }
    </style>
@endsection
