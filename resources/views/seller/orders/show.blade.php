@extends('seller.layout')

@section('title', 'Chi tiết đơn hàng #' . $order->id)

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-x-3">
            <a href="{{ route('seller.orders.index') }}" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Chi tiết đơn hàng #{{ $order->id }}</h1>
                <p class="mt-1 text-sm text-gray-500">Ngày đặt: {{ $order->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-md bg-green-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-md bg-red-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Có lỗi xảy ra:</h3>
                    <ul class="mt-2 list-disc list-inside text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Order Items -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                <div class="px-4 py-6 sm:p-8">
                    <h2 class="text-base font-semibold leading-7 text-gray-900">Sản phẩm đặt hàng</h2>
                    <div class="mt-6 flow-root">
                        <ul role="list" class="-my-6 divide-y divide-gray-200">
                            @foreach($order->items as $item)
                                <li class="flex py-6">
                                    <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-md border border-gray-200">
                                        @if($item->productVariant->product->images->first())
                                            <img src="{{ asset('storage/' . $item->productVariant->product->images->first()->image_url) }}" 
                                                 alt="{{ $item->productVariant->product->name }}" 
                                                 class="h-full w-full object-cover object-center">
                                        @else
                                            <div class="h-full w-full bg-gray-100 flex items-center justify-center">
                                                <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="ml-4 flex flex-1 flex-col">
                                        <div>
                                            <div class="flex justify-between text-base font-medium text-gray-900">
                                                <h3>{{ $item->productVariant->product->name }}</h3>
                                                <p class="ml-4">{{ number_format($item->price * $item->quantity, 0, ',', '.') }} ₫</p>
                                            </div>
                                            <p class="mt-1 text-sm text-gray-500">
                                                SKU: {{ $item->productVariant->sku }}
                                                @if($item->productVariant->color)
                                                    - Màu: {{ $item->productVariant->color }}
                                                @endif
                                                @if($item->productVariant->size)
                                                    - Size: {{ $item->productVariant->size }}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="flex flex-1 items-end justify-between text-sm">
                                            <p class="text-gray-500">Số lượng: {{ $item->quantity }}</p>
                                            <p class="font-medium text-gray-900">{{ number_format($item->price, 0, ',', '.') }} ₫/sp</p>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                <div class="px-4 py-6 sm:p-8">
                    <h2 class="text-base font-semibold leading-7 text-gray-900">Thông tin khách hàng</h2>
                    <div class="mt-6 space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Tên khách hàng</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $order->user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $order->user->email }}</dd>
                        </div>
                        @if($order->user->phone)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Số điện thoại</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $order->user->phone }}</dd>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
            <!-- Order Status -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                <div class="px-4 py-6 sm:p-8">
                    <h2 class="text-base font-semibold leading-7 text-gray-900">Trạng thái đơn hàng</h2>
                    <div class="mt-6">
                        <div class="text-center">
                            @if($order->status === 'pending')
                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-4 py-2 text-sm font-medium text-yellow-800">
                                    Chờ xử lý
                                </span>
                            @elseif($order->status === 'paid')
                                <span class="inline-flex items-center rounded-full bg-blue-100 px-4 py-2 text-sm font-medium text-blue-800">
                                    Đã thanh toán
                                </span>
                            @elseif($order->status === 'shipping')
                                <span class="inline-flex items-center rounded-full bg-purple-100 px-4 py-2 text-sm font-medium text-purple-800">
                                    Đang giao hàng
                                </span>
                            @elseif($order->status === 'completed')
                                <span class="inline-flex items-center rounded-full bg-green-100 px-4 py-2 text-sm font-medium text-green-800">
                                    Hoàn thành
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-red-100 px-4 py-2 text-sm font-medium text-red-800">
                                    Đã hủy
                                </span>
                            @endif
                        </div>

                        @if($order->status !== 'completed' && $order->status !== 'cancelled')
                            <form method="POST" action="{{ route('seller.orders.update-status', $order) }}" class="mt-6">
                                @csrf
                                @method('PATCH')
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Cập nhật trạng thái</label>
                                <select name="status" id="status" required class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm mb-4">
                                    @if($order->status === 'pending')
                                        <option value="paid">Đã thanh toán</option>
                                        <option value="cancelled">Hủy đơn</option>
                                    @elseif($order->status === 'paid')
                                        <option value="shipping">Đang giao hàng</option>
                                        <option value="cancelled">Hủy đơn</option>
                                    @elseif($order->status === 'shipping')
                                        <option value="completed">Hoàn thành</option>
                                        <option value="cancelled">Hủy đơn</option>
                                    @endif
                                </select>
                                <button type="submit" class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                    Cập nhật
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                <div class="px-4 py-6 sm:p-8">
                    <h2 class="text-base font-semibold leading-7 text-gray-900">Tổng quan đơn hàng</h2>
                    <div class="mt-6 space-y-4">
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-600">Tổng tiền hàng</dt>
                            <dd class="font-medium text-gray-900">{{ number_format($order->total_amount, 0, ',', '.') }} ₫</dd>
                        </div>
                        <div class="border-t border-gray-200 pt-4 flex justify-between text-base font-semibold">
                            <dt class="text-gray-900">Tổng cộng</dt>
                            <dd class="text-indigo-600">{{ number_format($order->total_amount, 0, ',', '.') }} ₫</dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            @if($order->payment)
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Thông tin thanh toán</h2>
                        <div class="mt-6 space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Phương thức</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $order->payment->method }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Trạng thái thanh toán</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $order->payment->status }}</dd>
                            </div>
                            @if($order->payment->paid_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Ngày thanh toán</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $order->payment->paid_at->format('d/m/Y H:i') }}</dd>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Shipping Information -->
            @if($order->shipment)
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Thông tin vận chuyển</h2>
                        <div class="mt-6 space-y-4">
                            @if($order->shipment->tracking_code)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Mã vận đơn</dt>
                                    <dd class="mt-1 text-sm font-mono text-gray-900">{{ $order->shipment->tracking_code }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Trạng thái vận chuyển</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $order->shipment->status }}</dd>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
