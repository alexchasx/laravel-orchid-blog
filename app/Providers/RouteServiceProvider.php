<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Маршруты приложения регистрируются через bootstrap/app.php (withRouting),
     * поэтому данный провайдер хранит только константу HOME.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
