@extends('seller.auth.layout')

@section('title', __('seller.auth.login_title'))

@section('heading', __('seller.auth.login_heading'))

@section('content')
<form class="space-y-6" action="{{ route('seller.login') }}" method="POST">
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
        <label for="password" class="block text-sm font-medium leading-6 text-gray-900">{{ __('seller.auth.password') }}</label>
        <div class="mt-2">
            <input id="password" name="password" type="password" autocomplete="current-password" required 
                   class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 px-3 @error('password') ring-red-300 focus:ring-red-500 @enderror">
        </div>
        @error('password')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <input id="remember" name="remember" type="checkbox" 
                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
            <label for="remember" class="ml-3 block text-sm leading-6 text-gray-900">{{ __('seller.auth.remember_me') }}</label>
        </div>

        <div class="text-sm leading-6">
            <a href="{{ route('seller.password.request') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">
                {{ __('seller.auth.forgot_password') }}
            </a>
        </div>
    </div>

    <div>
        <button type="submit" 
                class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            {{ __('seller.auth.login_btn') }}
        </button>
    </div>
</form>
@endsection

@section('footer')
<p class="mt-10 text-center text-sm text-gray-500">
    {{ __('seller.auth.no_account') }}
    <a href="{{ route('seller.register') }}" class="font-semibold leading-6 text-indigo-600 hover:text-indigo-500">
        {{ __('seller.auth.register_now') }}
    </a>
</p>
@endsection
