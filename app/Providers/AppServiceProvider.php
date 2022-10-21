<?php

namespace App\Providers;

use App\Services\ArticleService;
use App\Services\ServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Paginator::useBootstrap();

        // Создание своей blade-директивы "@hasAccess"
        Blade::if('hasAccess', function (string $value) {
            /** @var User $user */
            $user = Auth::user();
            if ($user === null) {
                return false;
            }

            return $user->hasAccess($value);
        });

        $this->app->bind(ServiceInterface::class, ArticleService::class);
    }
}
