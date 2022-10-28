<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class MainController extends Controller
{
    public function setLocale($locale): RedirectResponse
    {
        session(['user_locale' => $locale]);

        return redirect()->back();
    }
}
