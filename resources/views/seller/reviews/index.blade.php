@extends('seller.layout')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('seller.reviews.title') }}</h1>
            <p class="mt-2 text-sm text-gray-700">{{ __('seller.reviews.description') }}</p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">{{ __('seller.reviews.total') }}</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($stats->total) }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-yellow-50 px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-yellow-800">{{ __('seller.reviews.avg_rating') }}</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-yellow-900">{{ $stats->total > 0 ? number_format($stats->avg_rating, 1) : '0.0' }} ⭐</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-green-50 px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-green-800">{{ __('seller.reviews.replied') }}</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-green-900">{{ number_format($stats->replied) }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-orange-50 px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-orange-800">{{ __('seller.reviews.not_replied') }}</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-orange-900">{{ number_format($stats->not_replied) }}</dd>
        </div>
    </div>

    <!-- Rating Distribution -->
    <div class="mt-8 rounded-lg bg-white px-4 py-6 shadow sm:p-6">
        <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">{{ __('seller.reviews.distribution') }}</h3>
        <div class="space-y-4">
            @foreach([5, 4, 3, 2, 1] as $rating)
                @php
                    $count = $stats->{"${rating}_star"} ?? 0;
                    $percentage = $stats->total > 0 ? ($count / $stats->total) * 100 : 0;
                @endphp
                <div class="flex items-center gap-4">
                    <div class="text-sm font-medium text-gray-700 w-12">{{ $rating }} ⭐</div>
                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                        <div class="bg-yellow-400 h-2 rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                    </div>
                    <div class="text-sm font-medium text-gray-900 w-12 text-right">{{ $count }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Filters -->
    <div class="mt-8 space-y-4">
        <form method="GET" action="{{ route('seller.reviews.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">{{ __('seller.common.search') }}</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="{{ __('seller.reviews.search_placeholder') }}" class="mt-1 block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                </div>

                <!-- Rating Filter -->
                <div>
                    <label for="rating" class="block text-sm font-medium text-gray-700">{{ __('seller.reviews.rating') }}</label>
                    <select name="rating" id="rating" data-tom-select placeholder="{{ __('seller.common.all') }}" class="mt-1 block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                        <option value="">{{ __('seller.common.all') }}</option>
                        <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 ⭐</option>
                        <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 ⭐</option>
                        <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 ⭐</option>
                        <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 ⭐</option>
                        <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 ⭐</option>
                    </select>
                </div>

                <!-- Product Filter -->
                <div>
                    <label for="product_id" class="block text-sm font-medium text-gray-700">{{ __('seller.common.product') }}</label>
                    <select name="product_id" id="product_id" data-tom-select placeholder="{{ __('seller.reviews.all_products') }}" class="mt-1 block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                        <option value="">{{ __('seller.reviews.all_products') }}</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Reply Status Filter -->
                <div>
                    <label for="reply_status" class="block text-sm font-medium text-gray-700">{{ __('seller.common.status') }}</label>
                    <select name="reply_status" id="reply_status" data-tom-select placeholder="{{ __('seller.common.all') }}" class="mt-1 block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                        <option value="">{{ __('seller.common.all') }}</option>
                        <option value="not_replied" {{ request('reply_status') == 'not_replied' ? 'selected' : '' }}>{{ __('seller.reviews.not_replied') }}</option>
                        <option value="replied" {{ request('reply_status') == 'replied' ? 'selected' : '' }}>{{ __('seller.reviews.replied') }}</option>
                    </select>
                </div>

                <!-- Filter Button -->
                <div class="flex items-end col-span-1 sm:col-span-4">
                    <button type="submit" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 mr-3">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                        </svg>
                        {{ __('seller.common.filter') }}
                    </button>
                    <a href="{{ route('seller.reviews.index') }}" class="text-sm font-semibold text-gray-700 hover:text-gray-900">{{ __('seller.common.clear_filter') }}</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Reviews List -->
    <div class="mt-8 space-y-4">
        @forelse($reviews as $review)
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-start gap-4">
                        <!-- Product Info -->
                        <div class="flex-shrink-0">
                            @if($review->product->images->first())
                                <img src="{{ $review->product->images->first()->image_url }}" 
                                    alt="{{ $review->product->name }}" 
                                    class="h-20 w-20 rounded-lg object-cover">
                            @else
                                <div class="flex h-20 w-20 items-center justify-center rounded-lg bg-gray-200">
                                    <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Review Content -->
                        <div class="flex-1">
                            <div class="mb-2 flex items-start justify-between">
                                <div>
                                    <div class="mb-1 flex items-center gap-2">
                                        <span class="font-medium text-gray-900">{{ $review->user->name }}</span>
                                        <span class="text-sm text-gray-500">•</span>
                                        <span class="text-sm text-gray-500">{{ $review->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <div class="mb-1 text-sm text-gray-600">
                                        <a href="{{ route('seller.products.edit', $review->product->id) }}" class="hover:text-indigo-600">
                                            {{ $review->product->name }}
                                        </a>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <svg class="h-4 w-4 fill-current text-yellow-400" viewBox="0 0 20 20">
                                                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                                </svg>
                                            @else
                                                <svg class="h-4 w-4 fill-current text-gray-300" viewBox="0 0 20 20">
                                                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                                </svg>
                                            @endif
                                        @endfor
                                    </div>
                                </div>

                                @if(!$review->seller_reply)
                                    <button onclick="openReplyModal({{ $review->id }})" 
                                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                                        {{ __('seller.reviews.reply_btn') }}
                                    </button>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-green-50 px-3 py-1 text-sm font-medium text-green-700">{{ __('seller.reviews.replied') }}</span>
                                @endif
                            </div>

                        <!-- Customer Comment -->
                        @if($review->comment)
                            <div class="mb-3 rounded-lg bg-gray-50 p-3">
                                <p class="text-sm text-gray-700">{{ $review->comment }}</p>
                            </div>
                        @endif

                        <!-- Seller Reply -->
                        @if($review->seller_reply)
                            <div class="rounded-lg border-l-4 border-indigo-500 bg-indigo-50 p-3">
                                <div class="mb-1 flex items-center gap-2">
                                    <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                    </svg>
                                    <span class="text-sm font-medium text-indigo-900">{{ __('seller.reviews.shop_reply') }}</span>
                                    <span class="text-sm text-indigo-600">• {{ $review->replied_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <p class="text-sm text-gray-700">{{ $review->seller_reply }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-lg bg-white text-center shadow px-4 py-8 sm:p-6">
                <svg class="mx-auto mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                </svg>
                <p class="text-gray-600">{{ __('seller.reviews.empty') }}</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($reviews->hasPages())
        <div class="mt-8">
            {{ $reviews->links() }}
        </div>
    @endif
</div>

<!-- Reply Modal -->
<div id="replyModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-50">
    <div class="mx-4 w-full max-w-lg rounded-lg bg-white p-6">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">{{ __('seller.reviews.reply_btn') }}</h3>
            <button onclick="closeReplyModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form id="replyForm" method="POST">
            @csrf
            <div class="mb-4">
                <label for="seller_reply" class="block text-sm font-medium text-gray-700">{{ __('seller.reviews.reply_btn') }}</label>
                <textarea id="seller_reply" name="seller_reply" rows="4" required
                    placeholder="{{ __('seller.reviews.reply_placeholder') }}"
                    class="mt-1 block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"></textarea>
            </div>
            
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeReplyModal()" 
                    class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    {{ __('seller.reviews.cancel_reply') }}
                </button>
                <button type="submit" 
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    {{ __('seller.reviews.submit_reply') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openReplyModal(reviewId) {
    const modal = document.getElementById('replyModal');
    const form = document.getElementById('replyForm');
    form.action = `/seller/reviews/${reviewId}/reply`;
    modal.classList.remove('hidden');
}

function closeReplyModal() {
    const modal = document.getElementById('replyModal');
    modal.classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('replyModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeReplyModal();
    }
});
</script>
@endsection
