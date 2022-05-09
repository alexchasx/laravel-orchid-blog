<?php

namespace App\Http\Controllers;

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

        // $dfdf = auth()->user();

        // dd($dfdf->role);

        // return true; //TODO убрать


        if (
            Auth::check() &&
            isAdmin()
        ) {
            return true;
        }

        abort(403, 'Доступ запрещён!');
    }
}
