<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SellerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('seller.login');
        }

        if (Auth::user()->role !== 'seller') {
            Auth::logout();
            return redirect()->route('seller.login')->withErrors([
                'email' => 'Tài khoản không có quyền truy cập.'
            ]);
        }

        return $next($request);
    }
}
