<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class MainController extends Controller
{
    public function __construct(
        protected ServiceInterface $service,
    ) {}

    public function setLocale($locale): RedirectResponse
    {
        session(['user_locale' => $locale]);

        return redirect()->back();
    }

    protected function checkAccess(Model $model): void
    {
        /** @var User $user */
        if (!$model->is_published 
            && ( $user = Auth::user() ) 
            && !$user->isAdmin()
        ) {
            abort(403);
        }
    }
}
