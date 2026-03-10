<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSellerLocale
{
    /**
     * Đọc ngôn ngữ đã chọn từ session và áp dụng cho request hiện tại.
     * Mặc định là 'vi'. Chỉ chấp nhận 'vi' hoặc 'zh'.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('seller_locale', 'vi');

        if (!in_array($locale, ['vi', 'zh'])) {
            $locale = 'vi';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
