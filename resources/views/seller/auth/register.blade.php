@extends('seller.auth.layout')

@section('title', 'Đăng ký')

@section('heading', 'Đăng ký tài khoản người bán')

@section('content')
<form class="space-y-6" action="{{ route('seller.register') }}" method="POST">
    @csrf

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Họ và tên</label>
            <div class="mt-2">
                <input id="name" name="name" type="text" required 
                       value="{{ old('name') }}"
                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 px-3 @error('name') ring-red-300 focus:ring-red-500 @enderror">
            </div>
            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email</label>
            <div class="mt-2">
                <input id="email" name="email" type="email" autocomplete="email" required 
                       value="{{ old('email') }}"
                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 px-3 @error('email') ring-red-300 focus:ring-red-500 @enderror">
            </div>
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="phone" class="block text-sm font-medium leading-6 text-gray-900">Số điện thoại</label>
            <div class="mt-2">
                <input id="phone" name="phone" type="tel" 
                       value="{{ old('phone') }}"
                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 px-3 @error('phone') ring-red-300 focus:ring-red-500 @enderror">
            </div>
            @error('phone')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="brand_name" class="block text-sm font-medium leading-6 text-gray-900">Tên thương hiệu / Cửa hàng</label>
            <div class="mt-2">
                <input id="brand_name" name="brand_name" type="text" required 
                       value="{{ old('brand_name') }}"
                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 px-3 @error('brand_name') ring-red-300 focus:ring-red-500 @enderror">
            </div>
            @error('brand_name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="country" class="block text-sm font-medium leading-6 text-gray-900">Quốc gia</label>
            <div class="mt-2">
                <select id="country" name="country" required 
                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 px-3 @error('country') ring-red-300 focus:ring-red-500 @enderror">
                    <option value="VN" {{ old('country') == 'VN' ? 'selected' : '' }}>Việt Nam</option>
                    <option value="CN" {{ old('country') == 'CN' ? 'selected' : '' }}>Trung Quốc</option>
                    <option value="US" {{ old('country') == 'US' ? 'selected' : '' }}>Hoa Kỳ</option>
                    <option value="JP" {{ old('country') == 'JP' ? 'selected' : '' }}>Nhật Bản</option>
                    <option value="KR" {{ old('country') == 'KR' ? 'selected' : '' }}>Hàn Quốc</option>
                </select>
            </div>
            @error('country')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="description" class="block text-sm font-medium leading-6 text-gray-900">Mô tả về cửa hàng</label>
            <div class="mt-2">
                <textarea id="description" name="description" rows="3" 
                          class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 px-3 @error('description') ring-red-300 focus:ring-red-500 @enderror">{{ old('description') }}</textarea>
            </div>
            @error('description')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Mật khẩu</label>
            <div class="mt-2">
                <input id="password" name="password" type="password" autocomplete="new-password" required 
                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 px-3 @error('password') ring-red-300 focus:ring-red-500 @enderror">
            </div>
            @error('password')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="password_confirmation" class="block text-sm font-medium leading-6 text-gray-900">Xác nhận mật khẩu</label>
            <div class="mt-2">
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required 
                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 px-3">
            </div>
        </div>
    </div>

    <div>
        <button type="submit" 
                class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Đăng ký
        </button>
    </div>
</form>
@endsection

@section('footer')
<p class="mt-10 text-center text-sm text-gray-500">
    Đã có tài khoản?
    <a href="{{ route('seller.login') }}" class="font-semibold leading-6 text-indigo-600 hover:text-indigo-500">
        Đăng nhập ngay
    </a>
</p>
@endsection
