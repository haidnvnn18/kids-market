@extends('seller.layout')

@section('title', 'Quản lý đơn hàng')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Quản lý đơn hàng</h1>
            <p class="mt-2 text-sm text-gray-700">Danh sách tất cả đơn hàng của cửa hàng</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Tổng đơn</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $stats['total'] }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-yellow-50 px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-yellow-800">Chờ xử lý</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-yellow-900">{{ $stats['pending'] }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-blue-50 px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-blue-800">Đã thanh toán</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-blue-900">{{ $stats['paid'] }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-purple-50 px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-purple-800">Đang giao</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-purple-900">{{ $stats['shipping'] }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-green-50 px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-green-800">Hoàn thành</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-green-900">{{ $stats['completed'] }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-red-50 px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-red-800">Đã hủy</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-red-900">{{ $stats['cancelled'] }}</dd>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="mt-8 space-y-4">
        <form method="GET" action="{{ route('seller.orders.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Tìm kiếm</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Mã đơn, tên hoặc email khách hàng..." class="mt-1 block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Trạng thái</label>
                    <select name="status" id="status" data-tom-select placeholder="Tất cả trạng thái" class="mt-1 block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                        <option value="shipping" {{ request('status') === 'shipping' ? 'selected' : '' }}>Đang giao</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                </div>

                <!-- Filter Button -->
                <div class="flex items-end">
                    <button type="submit" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 mr-3">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                        </svg>
                        Lọc
                    </button>
                    <a href="{{ route('seller.orders.index') }}" class="text-sm font-semibold text-gray-700 hover:text-gray-900">Xóa bộ lọc</a>
                </div>
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="mt-4 rounded-md bg-green-50 p-4">
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

    <!-- Orders Table -->
    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Mã đơn</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Khách hàng</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Tổng tiền</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Trạng thái</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Ngày đặt</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">Hành động</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($orders as $order)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                        #{{ $order->id }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <div>{{ $order->user->name }}</div>
                                        <div class="text-gray-400">{{ $order->user->email }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 font-medium">
                                        {{ number_format($order->total_amount, 0, ',', '.') }} ₫
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        @if($order->status === 'pending')
                                            <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">Chờ xử lý</span>
                                        @elseif($order->status === 'paid')
                                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">Đã thanh toán</span>
                                        @elseif($order->status === 'shipping')
                                            <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-700/10">Đang giao</span>
                                        @elseif($order->status === 'completed')
                                            <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Hoàn thành</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">Đã hủy</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ $order->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <a href="{{ route('seller.orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-900">
                                            Xem chi tiết
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-12 text-center text-sm text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-semibold text-gray-900">Chưa có đơn hàng</h3>
                                        <p class="mt-1 text-sm text-gray-500">Các đơn hàng từ khách hàng sẽ hiển thị tại đây.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
