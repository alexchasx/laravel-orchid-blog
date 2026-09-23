<?php

namespace App\Providers;

use App\View\Composers\RubricsComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
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
        // Кастомная пагинация публичной части в стиле techlog
        // (админ-панель Orchid рендерит свою пагинацию, на неё это не влияет).
        Paginator::defaultView('vendor.pagination.techlog');
        Paginator::defaultSimpleView('vendor.pagination.techlog');

        // Создание своей blade-директивы "@hasAccess"
        Blade::if('hasAccess', function (string $value) {
            /** @var User $user */
            $user = Auth::user();
            if ($user === null) {
                return false;
            }

            return $user->hasAccess($value);
        });

        // Рубрики с опубликованными статьями для выпадающего меню «Темы» в хедере.
        View::composer('layouts.techlog', RubricsComposer::class);
    }
}
