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

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Order Items -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                <div class="px-4 py-6 sm:p-8">
                    <h2 class="text-base font-semibold leading-7 text-gray-900 flex items-center gap-x-2">
                        <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        Sản phẩm đặt hàng
                    </h2>
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
                    <h2 class="text-base font-semibold leading-7 text-gray-900 flex items-center gap-x-2">
                        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        Thông tin khách hàng
                    </h2>
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

            <!-- Shipping Address -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                <div class="px-4 py-6 sm:p-8">
                    <h2 class="text-base font-semibold leading-7 text-gray-900 flex items-center gap-x-2">
                        <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        Địa chỉ giao hàng
                    </h2>
                    <div class="mt-6 space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Người nhận</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $order->shipping_full_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Số điện thoại</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $order->shipping_phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Địa chỉ</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $order->shipping_address_detail }}<br>
                                @if($order->shippingDistrict)
                                    {{ $order->shippingDistrict->name }},
                                @endif
                                @if($order->shippingProvince)
                                    {{ $order->shippingProvince->name }}
                                @endif
                            </dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
            <!-- Order Summary & Status -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                <div class="px-4 py-6 sm:p-8">
                    <h2 class="text-base font-semibold leading-7 text-gray-900 flex items-center gap-x-2">
                        <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                        </svg>
                        Tổng quan đơn hàng
                    </h2>
                    <div class="mt-6 space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Trạng thái</dt>
                            <dd class="mt-2">
                                @if($order->status === 'pending')
                                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-800">
                                        Chờ xử lý
                                    </span>
                                @elseif($order->status === 'paid')
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800">
                                        Đã thanh toán
                                    </span>
                                @elseif($order->status === 'shipping')
                                    <span class="inline-flex items-center rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-800">
                                        Đang giao hàng
                                    </span>
                                @elseif($order->status === 'completed')
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">
                                        Hoàn thành
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-800">
                                        Đã hủy
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div class="border-t border-gray-200 pt-4 flex justify-between text-sm">
                            <dt class="text-gray-600">Tổng tiền hàng</dt>
                            <dd class="font-medium text-gray-900">{{ number_format($order->total_amount, 0, ',', '.') }} ₫</dd>
                        </div>
                        <div class="flex justify-between text-base font-semibold">
                            <dt class="text-gray-900">Tổng cộng</dt>
                            <dd class="text-indigo-600">{{ number_format($order->total_amount, 0, ',', '.') }} ₫</dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Information & Update -->
            @if($order->payment)
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h2 class="text-base font-semibold leading-7 text-gray-900 flex items-center gap-x-2">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                            </svg>
                            Thanh toán
                        </h2>
                        <div class="mt-6 space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Phương thức</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $order->payment->method }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Trạng thái</dt>
                                <dd class="mt-2">
                                    @if($order->payment->status === 'pending')
                                        <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                            Chờ thanh toán
                                        </span>
                                    @elseif($order->payment->status === 'success')
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                            Đã thanh toán
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                            Thất bại
                                        </span>
                                    @endif
                                </dd>
                            </div>
                            @if($order->payment->paid_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Ngày thanh toán</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $order->payment->paid_at->format('d/m/Y H:i') }}</dd>
                                </div>
                            @endif

                            @if($order->payment->status !== 'success' && $order->status !== 'cancelled')
                                <div class="border-t border-gray-200 pt-4">
                                    <form method="POST" action="{{ route('seller.orders.update-payment-status', $order) }}">
                                        @csrf
                                        @method('PATCH')
                                        <label for="payment_status" class="block text-sm font-medium text-gray-700 mb-2">Cập nhật trạng thái</label>
                                        <select name="payment_status" id="payment_status" required data-tom-select class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm mb-4">
                                            <option value="">Chọn trạng thái...</option>
                                            @if($order->payment->status === 'pending')
                                                <option value="success">Đã thanh toán</option>
                                                <option value="failed">Thất bại</option>
                                            @elseif($order->payment->status === 'failed')
                                                <option value="success">Đã thanh toán</option>
                                            @endif
                                        </select>
                                        <button type="submit" class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                            Cập nhật
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Shipment Information & Update -->
            @if($order->shipment)
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h2 class="text-base font-semibold leading-7 text-gray-900 flex items-center gap-x-2">
                            <svg class="h-5 w-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                            </svg>
                            Vận chuyển
                        </h2>
                        <div class="mt-6 space-y-4">
                            @if($order->shipment->tracking_code)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Mã vận đơn</dt>
                                    <dd class="mt-1 text-sm font-mono text-gray-900">{{ $order->shipment->tracking_code }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Trạng thái</dt>
                                <dd class="mt-2">
                                    @if($order->shipment->status === 'pending')
                                        <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                            Chờ xử lý
                                        </span>
                                    @elseif($order->shipment->status === 'processing')
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                            Đang xử lý
                                        </span>
                                    @elseif($order->shipment->status === 'shipped')
                                        <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800">
                                            Đang giao
                                        </span>
                                    @elseif($order->shipment->status === 'delivered')
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                            Đã giao
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                            Đã hủy
                                        </span>
                                    @endif
                                </dd>
                            </div>

                            @if($order->shipment->status !== 'delivered' && $order->status !== 'cancelled')
                                <div class="border-t border-gray-200 pt-4">
                                    <form method="POST" action="{{ route('seller.orders.update-shipment-status', $order) }}">
                                        @csrf
                                        @method('PATCH')
                                        <label for="shipment_status" class="block text-sm font-medium text-gray-700 mb-2">Cập nhật trạng thái</label>
                                        <select name="shipment_status" id="shipment_status" required data-tom-select class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm mb-4">
                                            <option value="">Chọn trạng thái...</option>
                                            @if($order->shipment->status === 'pending')
                                                <option value="processing">Đang xử lý</option>
                                                <option value="cancelled">Hủy đơn</option>
                                            @elseif($order->shipment->status === 'processing')
                                                <option value="shipped">Đang giao hàng</option>
                                                <option value="cancelled">Hủy đơn</option>
                                            @elseif($order->shipment->status === 'shipped')
                                                <option value="delivered">Đã giao hàng</option>
                                                <option value="cancelled">Hủy đơn</option>
                                            @endif
                                        </select>

                                        @if($order->shipment->status === 'pending' || $order->shipment->status === 'processing')
                                            <div class="mb-4">
                                                <label for="tracking_code" class="block text-sm font-medium text-gray-700 mb-2">Mã vận đơn (tùy chọn)</label>
                                                <input type="text" name="tracking_code" id="tracking_code" value="{{ $order->shipment->tracking_code }}" 
                                                       class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                                                       placeholder="Nhập mã vận đơn...">
                                            </div>
                                        @endif

                                        <button type="submit" class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                            Cập nhật
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
