<?php

namespace App\Http\Controllers;

/*
|--------------------------------------------------------------------------
| Контроллер тестовых маршрутов
|--------------------------------------------------------------------------
|
| Возвращает HTTP-ошибки (400–504) для тестирования кастомных страниц
| ошибок в `resources/views/errors/`. Не удалять.
*/

class TestController extends Controller
{
    /**
     * Список всех тестовых маршрутов (для robots.txt Disallow).
     *
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return [
            400, 401, 403, 404, 405, 408, 419, 429,
            500, 502, 503, 504,
        ];
    }

    /**
     * Обработчик тестового статуса.
     */
    public function __invoke(int $status): never
    {
        abort($status);
    }
}
