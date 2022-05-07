<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentController extends Controller
{
    /**
     * Количество статей на странице
     */
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

        if (
            // Auth::check() &&
            isAdmin()
        ) {
            return true;
        }

        abort(403, 'Доступ запрещён!');
    }
}
