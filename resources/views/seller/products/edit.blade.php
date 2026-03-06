@extends('seller.layout')

@section('title', 'Chỉnh sửa sản phẩm')

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
                <h1 class="text-2xl font-semibold text-gray-900">Chỉnh sửa sản phẩm</h1>
                <p class="mt-1 text-sm text-gray-500">Cập nhật thông tin sản phẩm và các phân loại</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('seller.products.update', $product) }}" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Basic Information -->
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            <div class="px-4 py-6 sm:p-8">
                <h2 class="text-base font-semibold leading-7 text-gray-900">Thông tin cơ bản</h2>
                <div class="mt-6 grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                    <!-- Product Name -->
                    <div class="col-span-full">
                        <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Tên sản phẩm <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required class="mt-2 block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>

                    <!-- Category -->
                    <div class="sm:col-span-3">
                        <label for="category_id" class="block text-sm font-medium leading-6 text-gray-900">Danh mục <span class="text-red-500">*</span></label>
                        <select name="category_id" id="category_id" required placeholder="Chọn danh mục" data-tom-select class="mt-2 block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="">Chọn danh mục</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status -->
                    <div class="sm:col-span-3">
                        <label for="status" class="block text-sm font-medium leading-6 text-gray-900">Trạng thái <span class="text-red-500">*</span></label>
                        <select name="status" id="status" required placeholder="Chọn trạng thái" data-tom-select class="mt-2 block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="hidden" {{ old('status', $product->status) === 'hidden' ? 'selected' : '' }}>Ẩn</option>
                        </select>
                    </div>

                    <!-- Wholesale Price -->
                    <div class="sm:col-span-3">
                        <label for="wholesale_price" class="block text-sm font-medium leading-6 text-gray-900">Giá sỉ <span class="text-red-500">*</span></label>
                        <input type="number" name="wholesale_price" id="wholesale_price" value="{{ old('wholesale_price', $product->wholesale_price) }}" required min="0" step="0.01" class="mt-2 block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>

                    <!-- Min Quantity -->
                    <div class="sm:col-span-3">
                        <label for="min_quantity" class="block text-sm font-medium leading-6 text-gray-900">Số lượng tối thiểu <span class="text-red-500">*</span></label>
                        <input type="number" name="min_quantity" id="min_quantity" value="{{ old('min_quantity', $product->min_quantity) }}" required min="1" class="mt-2 block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>

                    <!-- Description -->
                    <div class="col-span-full">
                        <label for="description" class="block text-sm font-medium leading-6 text-gray-900">Mô tả</label>
                        <textarea name="description" id="description" rows="4" class="mt-2 block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Variants -->
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            <div class="px-4 py-6 sm:p-8">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold leading-7 text-gray-900">Phân loại sản phẩm</h2>
                    <button type="button" onclick="addVariant()" class="inline-flex items-center gap-x-1.5 rounded-md bg-indigo-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        <svg class="-ml-0.5 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                        </svg>
                        Thêm phân loại
                    </button>
                </div>

                <div id="variants-container" class="mt-6 space-y-4">
                    @foreach($product->variants as $index => $variant)
                        <div class="variant-row border border-gray-200 rounded-lg p-4" data-variant-id="{{ $variant->id }}">
                            <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-12">
                                <!-- SKU -->
                                <div class="sm:col-span-3">
                                    <label class="block text-sm font-medium text-gray-700">SKU <span class="text-red-500">*</span></label>
                                    <input type="text" name="variants[{{ $index }}][sku]" value="{{ $variant->sku }}" required class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm">
                                </div>

                                <!-- Color -->
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Màu sắc</label>
                                    <input type="text" name="variants[{{ $index }}][color]" value="{{ $variant->color }}" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm">
                                </div>

                                <!-- Size -->
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Kích thước</label>
                                    <input type="text" name="variants[{{ $index }}][size]" value="{{ $variant->size }}" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm">
                                </div>

                                <!-- Price -->
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Giá <span class="text-red-500">*</span></label>
                                    <input type="number" name="variants[{{ $index }}][price]" value="{{ $variant->price }}" required min="0" step="0.01" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm">
                                </div>

                                <!-- Stock -->
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Tồn kho <span class="text-red-500">*</span></label>
                                    <input type="number" name="variants[{{ $index }}][stock]" value="{{ $variant->stock }}" required min="0" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm">
                                </div>

                                <!-- Remove Button -->
                                <div class="sm:col-span-1 flex items-end">
                                    <button type="button" onclick="markVariantForDeletion(this)" class="w-full rounded-md bg-red-600 px-2 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                                        <svg class="h-4 w-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="mt-4 text-sm text-gray-500">* Mỗi phân loại cần có SKU, giá và số lượng tồn kho riêng. Màu sắc và kích thước là tùy chọn.</p>
            </div>
        </div>

        <!-- Existing Images -->
        @if($product->images->count() > 0)
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                <div class="px-4 py-6 sm:p-8">
                    <h2 class="text-base font-semibold leading-7 text-gray-900">Hình ảnh hiện có</h2>
                    <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-5">
                        @foreach($product->images as $index => $image)
                            <div class="relative group">
                                <img src="{{ asset('storage/' . $image->image_url) }}" alt="{{ $product->name }}" class="h-32 w-full object-cover rounded-lg">
                                @if($index === 0)
                                    <span class="absolute top-2 left-2 bg-indigo-600 text-white text-xs px-2 py-1 rounded">Ảnh chính</span>
                                @endif
                                <button type="button" onclick="deleteImage({{ $image->id }})" class="absolute top-2 right-2 bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Add New Images -->
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            <div class="px-4 py-6 sm:p-8">
                <h2 class="text-base font-semibold leading-7 text-gray-900">Thêm hình ảnh mới</h2>
                <div class="mt-6">
                    <label for="images" class="block text-sm font-medium text-gray-700">
                        Tải lên hình ảnh (Tối đa {{ 5 - $product->images->count() }} ảnh, mỗi ảnh không quá 2MB)
                    </label>
                    <input type="file" name="images[]" id="images" multiple accept="image/*" {{ $product->images->count() >= 5 ? 'disabled' : '' }} class="mt-2 block w-full rounded-md border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100 focus:ring-2 focus:ring-inset focus:ring-indigo-600 disabled:cursor-not-allowed disabled:opacity-50">
                    @if($product->images->count() >= 5)
                        <p class="mt-2 text-xs text-red-600">Bạn đã đạt giới hạn 5 ảnh. Vui lòng xóa ảnh cũ trước khi thêm ảnh mới.</p>
                    @else
                        <p class="mt-2 text-xs text-gray-500">Ảnh mới sẽ được thêm vào sau các ảnh hiện có.</p>
                    @endif
                    
                    <!-- Image Preview -->
                    <div id="image-preview" class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-5"></div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-x-6">
            <a href="{{ route('seller.products.index') }}" class="text-sm font-semibold leading-6 text-gray-900">Hủy</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Cập nhật sản phẩm</button>
        </div>
    </form>
</div>

<!-- Delete Image Form -->
<form id="delete-image-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
let variantIndex = {{ $product->variants->count() }};
const maxImages = 5;
const currentImageCount = {{ $product->images->count() }};

function addVariant() {
    const container = document.getElementById('variants-container');
    const variantHtml = `
        <div class="variant-row border border-gray-200 rounded-lg p-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-12">
                <!-- SKU -->
                <div class="sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">SKU <span class="text-red-500">*</span></label>
                    <input type=\"text\" name=\"variants[${variantIndex}][sku]\" required class=\"mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm\">
                </div>

                <!-- Color -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Màu sắc</label>
                    <input type=\"text\" name=\"variants[${variantIndex}][color]\" class=\"mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm\">
                </div>

                <!-- Size -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Kích thước</label>
                    <input type="text" name="variants[${variantIndex}][size]" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- Price -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Giá <span class="text-red-500">*</span></label>
                    <input type="number" name="variants[${variantIndex}][price]" required min="0" step="0.01" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- Stock -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Tồn kho <span class="text-red-500">*</span></label>
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

function markVariantForDeletion(button) {
    const variantRow = button.closest('.variant-row');
    const variantId = variantRow.dataset.variantId;
    
    const variantRows = document.querySelectorAll('.variant-row');
    if (variantRows.length > 1) {
        if (variantId) {
            // Mark existing variant for deletion
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'delete_variants[]';
            input.value = variantId;
            variantRow.appendChild(input);
        }
        variantRow.style.opacity = '0.5';
        variantRow.querySelectorAll('input').forEach(input => input.disabled = true);
        button.disabled = true;
        button.innerHTML = '<span class="text-xs">Đã xóa</span>';
    } else {
        alert('Sản phẩm phải có ít nhất một phân loại.');
    }
}

function removeVariant(button) {
    const variantRows = document.querySelectorAll('.variant-row:not([style*="opacity"])');
    if (variantRows.length > 1) {
        button.closest('.variant-row').remove();
    } else {
        alert('Sản phẩm phải có ít nhất một phân loại.');
    }
}

function deleteImage(imageId) {
    if (confirm('Bạn có chắc chắn muốn xóa ảnh này?')) {
        const form = document.getElementById('delete-image-form');
        form.action = `/seller/products/images/${imageId}`;
        form.submit();
    }
}

// Image preview
document.getElementById('images')?.addEventListener('change', function(e) {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';
    
    const files = Array.from(e.target.files);
    const availableSlots = maxImages - currentImageCount;
    
    if (files.length > availableSlots) {
        alert(`Bạn chỉ có thể tải lên thêm ${availableSlots} ảnh.`);
        e.target.value = '';
        return;
    }
    
    files.forEach((file) => {
        if (file.size > 2 * 1024 * 1024) {
            alert(`Ảnh ${file.name} vượt quá 2MB.`);
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'relative';
            div.innerHTML = `<img src="${e.target.result}" class="h-32 w-full object-cover rounded-lg">`;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
});
</script>
@endsection
