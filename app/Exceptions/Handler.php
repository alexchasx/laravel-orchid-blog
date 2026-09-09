<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * Кастомная регистрация обработки исключений приложения
     * вынесена в bootstrap/app.php (withExceptions).
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
