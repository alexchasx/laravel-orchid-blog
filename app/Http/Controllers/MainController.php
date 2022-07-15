<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class MainController extends Controller
{
    const EMPTY_IMAGE = 'upload/no-image.png';
    const PAGINATE = 7;

    /**
     * Проверяет пользователя на наличие администраторских прав
     *
     * @return true
     *
     * @throws HttpException
     */
    public static function checkAdmin()
    {
        if (Auth::check()) {
            return true;
        }

        abort(403, 'Доступ запрещён!');
    }

    public function setLocale($locale)
    {
        session(['user_locale' => $locale]);

        return redirect()->back();
    }
}
