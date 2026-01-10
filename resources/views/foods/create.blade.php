@extends('layouts.app')
@php
    $restaurantId = $restaurantId ?? ($id ?? '');
@endphp
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.food_create') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{!! route('dashboard') !!}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('foods') !!}">{{ trans('lang.food_plural') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.food_create') }}</li>
                </ol>
            </div>
        </div>

        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('foods.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row restaurant_payout_create">
                    <div class="restaurant_payout_create-inner">
                        <fieldset>
                            <legend>{{ trans('lang.food_information') }}</legend>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.food_name') }}</label>
                                <div class="col-7">
                                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                                    <div class="form-text text-muted">{{ trans('lang.food_name_help') }}</div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.food_price') }}</label>
                                <div class="col-7">
                                    <input type="number" step="0.01" name="price" value="{{ old('price') }}" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">Merchant Price</label>
                                <div class="col-7">
                                    <input type="number" step="0.01" name="merchant_price" value="{{ old('merchant_price') }}" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.food_discount') }}</label>
                                <div class="col-7">
                                    <input type="number" step="0.01" name="disPrice" value="{{ old('disPrice') }}" class="form-control">
                                    <div class="form-text text-muted">{{ trans('lang.food_discount_help') }}</div>
                                </div>
                            </div>

                            <div class="form-group row width-50 food_restaurant_div">
                                <label class="col-3 control-label">{{ trans('lang.food_restaurant_id') }}</label>
                                <div class="col-7">
                                    <select name="vendorID" class="form-control" {{ !empty($restaurantId) ? 'disabled' : '' }} required>
                                        <option value="">{{ trans('lang.select_restaurant') }}</option>
                                        @foreach($restaurants as $id => $title)
                                            <option value="{{ $id }}" {{ old('vendorID', $restaurantId) == $id ? 'selected' : '' }}>{{ $title }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text text-muted">{{ trans('lang.food_restaurant_id_help') }}</div>
                                    @if(!empty($restaurantId))
                                        <input type="hidden" name="vendorID" value="{{ $restaurantId }}">
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.food_category_id') }}</label>
                                <div class="col-7">
                                    <select name="categoryID" class="form-control" required>
                                        <option value="">{{ trans('lang.select_category') }}</option>
                                        @foreach($categories as $id => $title)
                                            <option value="{{ $id }}" {{ old('categoryID') == $id ? 'selected' : '' }}>{{ $title }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text text-muted">{{ trans('lang.food_category_id_help') }}</div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.item_quantity') }}</label>
                                <div class="col-7">
                                    <input type="number" name="quantity" value="{{ old('quantity', -1) }}" class="form-control" min="-1">
                                    <div class="form-text text-muted">{{ trans('lang.item_quantity_help') }}</div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ __('Food Attributes') }}</label>
                                <div class="col-7">
                                    <select name="item_attribute[]" class="form-control" multiple>
                                        @foreach($attributes as $id => $title)
                                            <option value="{{ $id }}" {{ collect(old('item_attribute', []))->contains($id) ? 'selected' : '' }}>{{ $title }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text text-muted">{{ __('Select Attribute') }}</div>
                                </div>
                            </div>

                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.food_image') }}</label>
                                <div class="col-7">
                                    <input type="file" name="photo" class="form-control" accept="image/*">
                                    <div class="form-text text-muted">{{ trans('lang.food_image_help') }}</div>
                                </div>
                            </div>

                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.food_description') }}</label>
                                <div class="col-7">
                                    <textarea name="description" rows="6" class="form-control" required>{{ old('description') }}</textarea>
                                </div>
                            </div>

                            <div class="form-check width-100">
                                <input type="checkbox" class="food_publish" id="food_publish" name="publish" value="1" {{ old('publish', true) ? 'checked' : '' }}>
                                <label class="col-3 control-label" for="food_publish">{{ trans('lang.food_publish') }}</label>
                            </div>
                            <div class="form-check width-100">
                                <input type="checkbox" class="food_nonveg" id="food_nonveg" name="nonveg" value="1" {{ old('nonveg') ? 'checked' : '' }}>
                                <label class="col-3 control-label" for="food_nonveg">{{ trans('lang.non_veg') }}</label>
                            </div>
                            <div class="form-check width-100" style="display:none;">
                                <input type="checkbox" class="food_take_away_option" id="food_take_away_option" name="takeawayOption" value="1" {{ old('takeawayOption') ? 'checked' : '' }}>
                                <label class="col-3 control-label" for="food_take_away_option">{{ trans('lang.food_take_away') }}</label>
                            </div>
                            <div class="form-check width-100">
                                <input type="checkbox" class="food_is_available" id="food_is_available" name="isAvailable" value="1" {{ old('isAvailable', true) ? 'checked' : '' }}>
                                <label class="col-3 control-label" for="food_is_available">{{ __('Available') }}</label>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>{{ trans('lang.ingredients') }}</legend>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.calories') }}</label>
                                <div class="col-7">
                                    <input type="number" name="calories" value="{{ old('calories') }}" class="form-control" min="0">
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.grams') }}</label>
                                <div class="col-7">
                                    <input type="number" name="grams" value="{{ old('grams') }}" class="form-control" min="0">
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.fats') }}</label>
                                <div class="col-7">
                                    <input type="number" name="fats" value="{{ old('fats') }}" class="form-control" min="0">
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.proteins') }}</label>
                                <div class="col-7">
                                    <input type="number" name="proteins" value="{{ old('proteins') }}" class="form-control" min="0">
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>

                <div class="form-group col-12 text-center btm-btn">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('lang.save') }}</button>
                    @if(request('eid'))
                        <a href="{{ route('restaurants.foods', request('eid')) }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                    @else
                        <a href="{!! route('foods') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection
