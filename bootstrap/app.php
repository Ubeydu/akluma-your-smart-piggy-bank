<?php

use App\Http\Middleware\CurrencySwitcher;
use App\Http\Middleware\ConditionalLayoutMiddleware;
use App\Http\Middleware\RouteTrackingMiddleware;
use App\Http\Middleware\SetLocaleFromUrl;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // The LanguageSwitcher middleware was previously used to set the locale from the session.
        // However, the application now uses SetLocaleFromUrl, which reads the locale from the URL segment (e.g., /en, /fr).
        // This middleware is no longer needed globally and has been disabled to avoid conflicts.
        //
        // $middleware->web(append: LanguageSwitcher::class);

        $middleware->append(RouteTrackingMiddleware::class);

        $middleware->web(append: CurrencySwitcher::class);


        $middleware->alias([
            'conditional.layout' => ConditionalLayoutMiddleware::class,
            'locale' => SetLocaleFromUrl::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
