<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

abstract class BaseController extends Controller
{
    const EMPTY_IMAGE = 'upload/no-image.png';
    const PAGINATE = 7; //TODO Создать AdminProfileController для настройки админ-панели из браузера

    /**
     * Проверяет пользователя на наличие администраторских прав
     *
     * @return true
     *
     * @throws HttpException
     */
    public static function checkAdmin()
    {
        if (Auth::check() && isAdmin()) {
            return true;
        }

        abort(403, 'Доступ запрещён!');
    }
}
