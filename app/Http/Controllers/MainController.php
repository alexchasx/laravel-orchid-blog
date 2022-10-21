<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ResponseFormat;
use App\Services\Sidebar;
use Illuminate\Support\Facades\Auth;

class MainController extends Controller
{
    const PAGINATE = 12;

    public function __construct(
        protected ResponseFormat $responseFormat,
        protected Sidebar $sidebar
        ) {}

    public function setLocale($locale)
    {
        session(['user_locale' => $locale]);

        return redirect()->back();
    }

    protected function accessToNotPublic()
    {
        /** @var User $user */
        $user = Auth::user();
        if (($user && !$user->isAdmin()) || !$user) {
            abort(403);
        }
    }
}
