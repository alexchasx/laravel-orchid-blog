<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

abstract class ParentController extends Controller
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
}
