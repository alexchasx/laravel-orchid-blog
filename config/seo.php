<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Мета-теги по умолчанию
    |--------------------------------------------------------------------------
    */

    'default_title' => env('SEO_DEAFULT_TITLE', ''),
    'default_description' => env('SEO_DEAFULT_DESCRIPTION', ''),

    /*
    |--------------------------------------------------------------------------
    | Open Graph / Twitter
    |--------------------------------------------------------------------------
    */

    'og_site_name' => '{app_name}',
    'og_locale' => 'ru_RU',
    'og_image' => env('OG_IMAGE', ''), // абсолютный URL дефолтной OG-картинки
    'twitter_card' => 'summary',
    'twitter_handle' => '',

    /*
    |--------------------------------------------------------------------------
    | Organization / Publisher
    |--------------------------------------------------------------------------
    */

    'organization_name' => '{app_name}',
    // Внимание: значения должны быть абсолютными URL (http(s)://...) —
    // в JSON-LD они выводятся как есть, без приведения через \App\Support\Seo::absoluteUrl().
    'organization_url' => '',
    'organization_logo' => '',
    'organization_social' => [
        'github'  => config('my_config.my_github', ''),
        'telegram' => config('my_config.my_telegram', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Canonical / Robots
    |--------------------------------------------------------------------------
    */

    'canonical_self' => true, // по умолчанию canonical = self URL

];
