@extends('seller.layout')

@section('title', __('seller.shop.title'))

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">{{ __('seller.shop.title') }}</h1>
        <p class="mt-2 text-sm text-gray-700">{{ __('seller.shop.description') }}</p>
    </div>
    <a href="{{ route('seller.shop.edit') }}" class="inline-flex items-center gap-x-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
        <svg class="-ml-0.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
        </svg>
        {{ __('seller.shop.edit_btn') }}
    </a>
</div>

<!-- Banner -->
<div class="overflow-hidden rounded-lg bg-white shadow mb-6">
    <div class="relative h-64 w-full bg-gradient-to-r from-indigo-500 to-purple-600">
        @if($supplier->banner)
            <img src="{{ $supplier->banner }}" alt="Banner" class="h-full w-full object-cover">
        @else
            <div class="flex h-full items-center justify-center">
                <div class="text-center text-white">
                    <svg class="mx-auto h-12 w-12 opacity-50" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    <p class="mt-2 text-sm font-medium">{{ __('seller.shop.no_banner') }}</p>
                </div>
            </div>
        @endif
    </div>
    
    <!-- Avatar overlapping banner -->
    <div class="px-6 pb-6">
        <div class="-mt-16 mb-6 flex items-end">
            <div class="relative">
                @if($supplier->avatar)
                    <img src="{{ $supplier->avatar }}" alt="{{ $supplier->brand_name }}" class="h-32 w-32 rounded-full border-4 border-white bg-white object-cover shadow-lg">
                @else
                    <div class="h-32 w-32 rounded-full border-4 border-white bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg flex items-center justify-center">
                        <span class="text-4xl font-bold text-white">{{ substr($supplier->brand_name, 0, 1) }}</span>
                    </div>
                @endif
            </div>
            <div class="ml-6 flex-1">
                <h2 class="text-3xl font-bold text-gray-900">{{ $supplier->brand_name }}</h2>
                <div class="mt-2 flex items-center gap-x-4 text-sm text-gray-600">
                    <div class="flex items-center gap-x-1">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <span class="font-semibold">{{ number_format($supplier->rating_avg, 2) }}</span>
                    </div>
                    <span class="text-gray-400">•</span>
                    <div class="flex items-center gap-x-1">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                        </svg>
                        <span>{{ $supplier->country }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Information -->
        <div class="border-t border-gray-200 pt-6">
            <dl class="divide-y divide-gray-100">
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">{{ __('seller.shop.brand_name_label') }}</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">{{ $supplier->brand_name }}</dd>
                </div>
                
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">{{ __('seller.shop.country_label') }}</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">
                        @switch($supplier->country)
                            @case('VN')
                                {{ __('seller.countries.VN') }}
                                @break
                            @case('CN')
                                {{ __('seller.countries.CN') }}
                                @break
                            @case('US')
                                {{ __('seller.countries.US') }}
                                @break
                            @case('JP')
                                {{ __('seller.countries.JP') }}
                                @break
                            @case('KR')
                                {{ __('seller.countries.KR') }}
                                @break
                            @default
                                {{ $supplier->country }}
                        @endswitch
                    </dd>
                </div>
                
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">{{ __('seller.common.avg_rating') }}</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">
                        <div class="flex items-center gap-x-2">
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= floor($supplier->rating_avg))
                                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    @endif
                                @endfor
                            </div>
                            <span class="font-semibold">{{ number_format($supplier->rating_avg, 2) }}</span>
                        </div>
                    </dd>
                </div>
                
                @if($supplier->description)
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">{{ __('seller.shop.description_label') }}</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">{{ $supplier->description }}</dd>
                </div>
                @endif
                
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">{{ __('seller.shop.created_at') }}</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">{{ $supplier->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">{{ __('seller.shop.updated_at') }}</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">{{ $supplier->updated_at->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
