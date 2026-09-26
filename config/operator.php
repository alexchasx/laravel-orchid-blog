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

    /*
    |--------------------------------------------------------------------------
    | Реестр Роскомнадзора
    |--------------------------------------------------------------------------
    */

    'rkn_notification_date' => env('RKN_NOTIFICATION_DATE', ''),

    'rkn_registry_number' => env('RKN_REGISTRY_NUMBER', ''),

    /*
    |--------------------------------------------------------------------------
    | Сроки хранения данных
    |--------------------------------------------------------------------------
    */

    'comment_retention_period' => env('COMMENT_RETENTION_PERIOD', ''),

    'contact_retention_period' => env('CONTACT_RETENTION_PERIOD', ''),

    'backup_retention_days' => env('BACKUP_RETENTION_DAYS', ''),

];
