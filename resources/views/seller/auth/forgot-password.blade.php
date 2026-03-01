@extends('seller.auth.layout')

@section('title', 'Quên mật khẩu')

@section('heading', 'Quên mật khẩu')

@section('content')
<div class="mb-4 text-sm text-gray-600">
    Quên mật khẩu? Không vấn đề gì. Chỉ cần nhập địa chỉ email của bạn và chúng tôi sẽ gửi cho bạn liên kết đặt lại mật khẩu qua email.
</div>

<form class="space-y-6" action="{{ route('seller.password.email') }}" method="POST">
    @csrf

    <div>
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

    <div>
        <button type="submit" 
                class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Gửi liên kết đặt lại mật khẩu
        </button>
    </div>
</form>
@endsection

@section('footer')
<p class="mt-10 text-center text-sm text-gray-500">
    <a href="{{ route('seller.login') }}" class="font-semibold leading-6 text-indigo-600 hover:text-indigo-500">
        Quay lại đăng nhập
    </a>
</p>
@endsection
