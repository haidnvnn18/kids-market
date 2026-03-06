@extends('seller.layout')

@section('title', 'Chỉnh sửa thông tin cửa hàng')

@section('content')
<div class="mb-8">
    <div class="flex items-center gap-x-3">
        <a href="{{ route('seller.shop.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Chỉnh sửa thông tin cửa hàng</h1>
            <p class="mt-2 text-sm text-gray-700">Cập nhật thông tin, hình ảnh của cửa hàng</p>
        </div>
    </div>
</div>

<form action="{{ route('seller.shop.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')

    <!-- Banner Section -->
    <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="px-6 py-5">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Banner cửa hàng</h3>
            <p class="mt-1 text-sm text-gray-500">Tải lên ảnh banner cho cửa hàng của bạn (Tối đa 5MB)</p>
        </div>
        <div class="border-t border-gray-200 px-6 py-5">
            <div class="space-y-4">
                <!-- Current Banner Preview -->
                @if($supplier->banner)
                <div class="relative">
                    <img src="{{ $supplier->banner }}" alt="Current Banner" class="h-48 w-full rounded-lg object-cover" id="current-banner">
                    <div class="mt-3 flex gap-x-3">
                        <button type="button" onclick="document.getElementById('banner-delete-form').submit()" class="inline-flex items-center gap-x-2 rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                            <svg class="-ml-0.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                            Xóa banner
                        </button>
                    </div>
                </div>
                @endif

                <!-- Upload New Banner -->
                <div>
                    <label for="banner" class="block text-sm font-medium text-gray-900 mb-2">
                        {{ $supplier->banner ? 'Thay đổi banner' : 'Tải lên banner' }}
                    </label>
                    <div class="flex items-center justify-center w-full">
                        <label for="banner-input" class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                                <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Nhấp để tải lên</span> hoặc kéo thả</p>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF (Tối đa 5MB)</p>
                            </div>
                        </label>
                    </div>
                    <input id="banner-input" name="banner" type="file" class="hidden" accept="image/*" onchange="previewImage(this, 'banner-preview')">
                    <img id="banner-preview" class="mt-4 hidden h-48 w-full rounded-lg object-cover">
                    @error('banner')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Avatar Section -->
    <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="px-6 py-5">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Avatar cửa hàng</h3>
            <p class="mt-1 text-sm text-gray-500">Tải lên ảnh đại diện cho cửa hàng của bạn (Tối đa 2MB)</p>
        </div>
        <div class="border-t border-gray-200 px-6 py-5">
            <div class="flex items-start gap-x-6">
                <!-- Current Avatar -->
                <div class="flex-shrink-0">
                    @if($supplier->avatar)
                        <img src="{{ $supplier->avatar }}" alt="Current Avatar" class="h-32 w-32 rounded-full object-cover" id="current-avatar">
                    @else
                        <div class="h-32 w-32 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                            <span class="text-4xl font-bold text-white">{{ substr($supplier->brand_name, 0, 1) }}</span>
                        </div>
                    @endif
                </div>

                <div class="flex-1">
                    <!-- Upload New Avatar -->
                    <div>
                        <label for="avatar-input" class="block text-sm font-medium text-gray-900 mb-2">
                            {{ $supplier->avatar ? 'Thay đổi avatar' : 'Tải lên avatar' }}
                        </label>
                        <div class="flex items-center gap-x-3">
                            <label for="avatar-input" class="cursor-pointer rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                Chọn ảnh
                            </label>
                            <input id="avatar-input" name="avatar" type="file" class="hidden" accept="image/*" onchange="previewImage(this, 'avatar-preview')">
                            @if($supplier->avatar)
                                <button type="button" onclick="document.getElementById('avatar-delete-form').submit()" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                                    Xóa avatar
                                </button>
                            @endif
                        </div>
                        <p class="mt-2 text-xs text-gray-500">PNG, JPG, GIF (Tối đa 2MB)</p>
                        @error('avatar')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Preview New Avatar -->
                    <div class="mt-4">
                        <img id="avatar-preview" class="hidden h-32 w-32 rounded-full object-cover">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Basic Information -->
    <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="px-6 py-5">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Thông tin cơ bản</h3>
            <p class="mt-1 text-sm text-gray-500">Thông tin chi tiết về cửa hàng của bạn</p>
        </div>
        <div class="border-t border-gray-200 px-6 py-5">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Brand Name -->
                <div class="sm:col-span-2">
                    <label for="brand_name" class="block text-sm font-medium leading-6 text-gray-900">
                        Tên thương hiệu <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-2">
                           <input type="text" name="brand_name" id="brand_name" 
                               value="{{ old('brand_name', $supplier->brand_name) }}"
                               class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('brand_name') ring-red-300 focus:ring-red-500 @enderror"
                               required>
                    </div>
                    @error('brand_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Country -->
                <div class="sm:col-span-2">
                    <label for="country" class="block text-sm font-medium leading-6 text-gray-900">
                        Quốc gia <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-2">
                        <select name="country" id="country" data-tom-select placeholder="Chọn quốc gia"
                            class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('country') ring-red-300 focus:ring-red-500 @enderror"
                                required>
                            <option value="VN" {{ old('country', $supplier->country) == 'VN' ? 'selected' : '' }}>🇻🇳 Việt Nam</option>
                            <option value="CN" {{ old('country', $supplier->country) == 'CN' ? 'selected' : '' }}>🇨🇳 Trung Quốc</option>
                            <option value="US" {{ old('country', $supplier->country) == 'US' ? 'selected' : '' }}>🇺🇸 Hoa Kỳ</option>
                            <option value="JP" {{ old('country', $supplier->country) == 'JP' ? 'selected' : '' }}>🇯🇵 Nhật Bản</option>
                            <option value="KR" {{ old('country', $supplier->country) == 'KR' ? 'selected' : '' }}>🇰🇷 Hàn Quốc</option>
                        </select>
                    </div>
                    @error('country')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-medium leading-6 text-gray-900">
                        Mô tả
                    </label>
                    <div class="mt-2">
                        <textarea name="description" id="description" rows="4" 
                                  class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('description') ring-red-300 focus:ring-red-500 @enderror"
                                  placeholder="Mô tả ngắn về cửa hàng của bạn...">{{ old('description', $supplier->description) }}</textarea>
                    </div>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center justify-end gap-x-6">
        <a href="{{ route('seller.shop.index') }}" class="rounded-md px-4 py-2 text-sm font-semibold text-gray-900 hover:bg-gray-100">
            Hủy
        </a>
        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Lưu thay đổi
        </button>
    </div>
</form>

<!-- Hidden forms for deleting avatar/banner -->
@if($supplier->avatar)
<form id="avatar-delete-form" action="{{ route('seller.shop.delete-avatar') }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
@endif

@if($supplier->banner)
<form id="banner-delete-form" action="{{ route('seller.shop.delete-banner') }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
@endif

<script>
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
