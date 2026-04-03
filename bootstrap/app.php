<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/seller.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.token'          => \App\Http\Middleware\AuthToken::class,
            'auth.token.optional' => \App\Http\Middleware\AuthTokenOptional::class,
            'auth.token.seller'   => \App\Http\Middleware\AuthTokenSeller::class,
            'seller'              => \App\Http\Middleware\SellerMiddleware::class,
            'seller.locale'       => \App\Http\Middleware\SetSellerLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
