<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MainController extends Controller
{
    public function setLocale($locale): RedirectResponse
    {
        session(['user_locale' => $locale]);

        return redirect()->back();
    }

    public function privacy(): View
    {
        return view('privacy', [
            'metaTitle' => __('Политика конфиденциальности'),
            'metaDesc' => __('Как :app собирает, хранит и защищает персональные данные посетителей.', ['app' => config('app.name')]),
        ]);
    }
}
