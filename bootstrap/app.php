<?php

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
        // Использовать кастомный TrustProxies (расширенные заголовки, включая AWS ELB).
        $middleware->replace(
            \Illuminate\Http\Middleware\TrustProxies::class,
            \App\Http\Middleware\TrustProxies::class
        );

        // Использовать кастомный CSRF-middleware приложения.
        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class
        );

        // Добавить кастомный middleware локализации в группу "web".
        $middleware->web(append: [
            \App\Http\Middleware\Localize::class,
        ]);

        // Алиас для доступа к панели Orchid.
        $middleware->alias([
            'access' => \Orchid\Platform\Http\Middleware\Access::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
