@extends('seller.layout')

@section('title', __('seller.products.create_title'))

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-x-3">
            <a href="{{ route('seller.products.index') }}" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ __('seller.products.create_title') }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ __('seller.products.create_desc') }}</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('seller.products.store') }}" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <!-- Basic Information -->
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            <div class="px-4 py-6 sm:p-8">
                <h2 class="text-base font-semibold leading-7 text-gray-900">{{ __('seller.products.basic_info') }}</h2>
                <div class="mt-6 grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                    <!-- Product Name -->
                    <div class="col-span-full">
                        <label for="name" class="block text-sm font-medium leading-6 text-gray-900">{{ __('seller.products.name') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-2 block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>

                    <!-- Category -->
                    <div class="sm:col-span-3">
                        <label for="category_id" class="block text-sm font-medium leading-6 text-gray-900">{{ __('seller.common.category') }} <span class="text-red-500">*</span></label>
                        <select name="category_id" id="category_id" required placeholder="{{ __('seller.products.select_category') }}" data-tom-select class="mt-2 block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="">{{ __('seller.products.select_category') }}</option>
                            @foreach($categories as $category)
                                @if($category->children->isNotEmpty())
                                    <optgroup label="{{ $category->name }}">
                                        @foreach($category->children as $child)
                                            <option value="{{ $child->id }}" {{ old('category_id') == $child->id ? 'selected' : '' }}>
                                                {{ $child->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @else
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- Status -->
                    <div class="sm:col-span-3">
                        <label for="status" class="block text-sm font-medium leading-6 text-gray-900">{{ __('seller.common.status') }} <span class="text-red-500">*</span></label>
                        <select name="status" id="status" required placeholder="{{ __('seller.products.select_status') }}" data-tom-select class="mt-2 block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>{{ __('seller.common.active') }}</option>
                            <option value="hidden" {{ old('status') === 'hidden' ? 'selected' : '' }}>{{ __('seller.common.hidden') }}</option>
                        </select>
                    </div>

                    <!-- Description -->
                    <div class="col-span-full">
                        <label for="description" class="block text-sm font-medium leading-6 text-gray-900">{{ __('seller.common.description') }}</label>
                        <textarea name="description" id="description" rows="4" class="mt-2 block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Variants -->
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            <div class="px-4 py-6 sm:p-8">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold leading-7 text-gray-900">{{ __('seller.products.variants_title') }}</h2>
                    <button type="button" onclick="addVariant()" class="inline-flex items-center gap-x-1.5 rounded-md bg-indigo-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        <svg class="-ml-0.5 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                        </svg>
                        {{ __('seller.products.add_variant') }}
                    </button>
                </div>

                <div id="variants-container" class="mt-6 space-y-4">
                    <!-- Initial variant row -->
                    <div class="variant-row border border-gray-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-12">
                            <!-- SKU -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">SKU <span class="text-red-500">*</span></label>
                                <input type="text" name="variants[0][sku]" required class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <!-- Color -->
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-medium text-gray-700">{{ __('seller.products.color') }}</label>
                                <input type="text" name="variants[0][color]" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <!-- Size -->
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-medium text-gray-700">{{ __('seller.products.size') }}</label>
                                <input type="text" name="variants[0][size]" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <!-- Price -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">{{ __('seller.products.retail_price') }} <span class="text-red-500">*</span></label>
                                <input type="number" name="variants[0][price]" required min="0" step="0.01" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <!-- Wholesale Price -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">{{ __('seller.products.wholesale_price') }} <span class="text-red-500">*</span></label>
                                <input type="number" name="variants[0][wholesale_price]" required min="0" step="0.01" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <!-- Min Quantity -->
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-medium text-gray-700">{{ __('seller.products.min_wholesale_qty') }} <span class="text-red-500">*</span></label>
                                <input type="number" name="variants[0][min_quantity]" required min="1" value="1" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <!-- Stock -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">{{ __('seller.common.stock') }} <span class="text-red-500">*</span></label>
                                <input type="number" name="variants[0][stock]" required min="0" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <!-- Remove Button -->
                            <div class="sm:col-span-1 flex items-end">
                                <button type="button" onclick="removeVariant(this)" class="w-full rounded-md bg-red-600 px-2 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                                    <svg class="h-4 w-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="mt-4 text-sm text-gray-500">{{ __('seller.products.variant_note') }}</p>
            </div>
        </div>

        <!-- Product Images -->
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            <div class="px-4 py-6 sm:p-8">
                <h2 class="text-base font-semibold leading-7 text-gray-900">{{ __('seller.products.images_title') }}</h2>
                <div class="mt-6">
                    <label for="images" class="block text-sm font-medium text-gray-700">
                        {{ __('seller.products.upload_images') }}
                    </label>
                    <input type="file" name="images[]" id="images" multiple accept="image/*" class="mt-2 block w-full rounded-md border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100 focus:ring-2 focus:ring-inset focus:ring-indigo-600">
                    <p class="mt-2 text-xs text-gray-500">{{ __('seller.products.first_image_note') }}</p>
                    
                    <!-- Image Preview -->
                    <div id="image-preview" class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-5"></div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-x-6">
            <a href="{{ route('seller.products.index') }}" class="text-sm font-semibold leading-6 text-gray-900">{{ __('seller.common.cancel') }}</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">{{ __('seller.products.save') }}</button>
        </div>
    </form>
</div>

<script>
let variantIndex = 1;

function addVariant() {
    const container = document.getElementById('variants-container');
    const variantHtml = `
        <div class="variant-row border border-gray-200 rounded-lg p-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-12">
                <!-- SKU -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">SKU <span class="text-red-500">*</span></label>
                    <input type="text" name="variants[${variantIndex}][sku]" required class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- Color -->
                <div class="sm:col-span-1">
                    <label class="block text-sm font-medium text-gray-700">@lang('seller.products.color')</label>
                    <input type="text" name="variants[${variantIndex}][color]" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- Size -->
                <div class="sm:col-span-1">
                    <label class="block text-sm font-medium text-gray-700">@lang('seller.products.size')</label>
                    <input type="text" name="variants[${variantIndex}][size]" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- Price -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">@lang('seller.products.retail_price') <span class="text-red-500">*</span></label>
                    <input type="number" name="variants[${variantIndex}][price]" required min="0" step="0.01" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- Wholesale Price -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">@lang('seller.products.wholesale_price') <span class="text-red-500">*</span></label>
                    <input type="number" name="variants[${variantIndex}][wholesale_price]" required min="0" step="0.01" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- Min Quantity -->
                <div class="sm:col-span-1">
                    <label class="block text-sm font-medium text-gray-700">@lang('seller.products.min_wholesale_qty') <span class="text-red-500">*</span></label>
                    <input type="number" name="variants[${variantIndex}][min_quantity]" required min="1" value="1" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- Stock -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">@lang('seller.common.stock') <span class="text-red-500">*</span></label>
                    <input type="number" name="variants[${variantIndex}][stock]" required min="0" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- Remove Button -->
                <div class="sm:col-span-1 flex items-end">
                    <button type="button" onclick="removeVariant(this)" class="w-full rounded-md bg-red-600 px-2 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                        <svg class="h-4 w-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', variantHtml);
    variantIndex++;
}

function removeVariant(button) {
    const variantRows = document.querySelectorAll('.variant-row');
    if (variantRows.length > 1) {
        button.closest('.variant-row').remove();
    } else {
        alert('@lang("seller.products.min_one_variant")');
    }
}

// Image preview
document.getElementById('images').addEventListener('change', function(e) {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';
    
    const files = Array.from(e.target.files);
    
    if (files.length > 5) {
        alert('@lang("seller.products.max_images")');
        e.target.value = '';
        return;
    }
    
    files.forEach((file, index) => {
        if (file.size > 2 * 1024 * 1024) {
            alert('@lang("seller.products.image_over_limit")'.replace(':name', file.name));
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'relative';
            div.innerHTML = `
                <img src="${e.target.result}" class="h-32 w-full object-cover rounded-lg">
                ${index === 0 ? '<span class="absolute top-2 left-2 bg-indigo-600 text-white text-xs px-2 py-1 rounded">@lang("seller.products.main_image_label")</span>' : ''}
            `;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
});
</script>
@endsection
