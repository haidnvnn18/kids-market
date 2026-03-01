@extends('seller.auth.layout')

@section('title', 'Đặt lại mật khẩu')

@section('heading', 'Đặt lại mật khẩu')

@section('content')
<form class="space-y-6" action="{{ route('seller.password.update') }}" method="POST">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <div>
        <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email</label>
        <div class="mt-2">
            <input id="email" name="email" type="email" autocomplete="email" required 
                   value="{{ old('email', $email) }}"
                   class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 px-3 @error('email') ring-red-300 focus:ring-red-500 @enderror">
        </div>
        @error('email')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Mật khẩu mới</label>
        <div class="mt-2">
            <input id="password" name="password" type="password" autocomplete="new-password" required 
                   class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 px-3 @error('password') ring-red-300 focus:ring-red-500 @enderror">
        </div>
        @error('password')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-medium leading-6 text-gray-900">Xác nhận mật khẩu</label>
        <div class="mt-2">
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required 
                   class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 px-3">
        </div>
    </div>

    <div>
        <button type="submit" 
                class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Đặt lại mật khẩu
        </button>
    </div>
</form>
@endsection
