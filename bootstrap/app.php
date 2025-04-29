<?php

use App\Http\Middleware\CurrencySwitcher;
use App\Http\Middleware\ConditionalLayoutMiddleware;
use App\Http\Middleware\DetectLanguage;
use App\Http\Middleware\LanguageSwitcher;
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
        $middleware->web(append: LanguageSwitcher::class);
        $middleware->web(append: CurrencySwitcher::class);
        $middleware->web(append: DetectLanguage::class);

        $middleware->alias([
            'conditional.layout' => ConditionalLayoutMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
