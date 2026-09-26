<?php

/*
|--------------------------------------------------------------------------
| Оператор персональных данных (152-ФЗ)
|--------------------------------------------------------------------------
| Реквизиты оператора. Плейсхолдеры по умолчанию — проект является
| публичным шаблоном, без привязки к конкретному сайту.
*/

return [

    'name' => env('OPERATOR_NAME', ''),

    'address' => env('OPERATOR_ADDRESS', ''),

    'inn' => env('OPERATOR_INN', ''),

    'ogrn' => env('OPERATOR_OGRN', ''),

    'email' => env('OPERATOR_EMAIL', ''),

    'phone' => env('OPERATOR_PHONE', ''),

    'site_url' => env('APP_URL', 'http://localhost:8080'),

    'hosting_provider' => env('HOSTING_PROVIDER', ''),

];
