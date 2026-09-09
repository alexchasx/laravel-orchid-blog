<?php

use App\Orchid\PlatformProvider;
use Orchid\Attachment\Engines\Generator;
use Orchid\Support\BootstrapIconsPath;

return [

    /*
    |--------------------------------------------------------------------------
    | Sub-Domain Routing
    |--------------------------------------------------------------------------
    |
    | This value represents the "domain name" associated with your application.
    |
    */

    'domain' => env('PLATFORM_DOMAIN', env('DASHBOARD_DOMAIN')),

    /*
    |--------------------------------------------------------------------------
    | Route Prefixes
    |--------------------------------------------------------------------------
    |
    | This prefix method can be used to specify the prefix of every route in
    | the administrator dashboard.
    |
    */

    'prefix' => env('PLATFORM_PREFIX', env('DASHBOARD_PREFIX', '/admin')),

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | This middleware will be assigned to every route in the administration
    | dashboard. You can add your custom middleware to this stack.
    |
    */

    'middleware' => [
        'public'  => ['web', 'cache.headers:private;must_revalidate;etag'],
        'private' => ['web', 'platform', 'cache.headers:private;must_revalidate;etag'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Guard
    |--------------------------------------------------------------------------
    |
    | This option specifies the name of the guard that should be used for
    | authentication when accessing the administration dashboard.
    |
    */

    'guard' => env('AUTH_GUARD', 'web'),

    /*
    |--------------------------------------------------------------------------
    | Authentication Page
    |--------------------------------------------------------------------------
    |
    | This option controls the visibility of Orchid's built-in authentication pages.
    |
    */

    'auth' => true,

    /*
    |--------------------------------------------------------------------------
    | Main Route
    |--------------------------------------------------------------------------
    |
    | This route is the starting page of the dashboard application.
    |
    */

    'index' => 'platform.main',

    /*
    |--------------------------------------------------------------------------
    | User Profile Route
    |--------------------------------------------------------------------------
    |
    | This route is used to access the user profile page.
    |
    */

    'profile' => 'platform.profile',

    /*
    |--------------------------------------------------------------------------
    | Dashboard Resource
    |--------------------------------------------------------------------------
    |
    | This option is used to store links for stylesheets and scripts automatically
    | connected to your dashboard.
    |
    */

    'resource' => [
        'stylesheets' => [],
        'scripts'     => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Vite Resource
    |--------------------------------------------------------------------------
    |
    | Within the 'vite' associative array, specify input files to be parsed by Vite.
    |
    */

    'vite' => [],

    /*
    |--------------------------------------------------------------------------
    | Template View
    |--------------------------------------------------------------------------
    |
    | This configuration option is utilized to determine which templates will be
    | displayed in the application and used on pages.
    |
    */

    'template' => [
        'header' => '',
        'footer' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Attachment Configuration
    |--------------------------------------------------------------------------
    |
    | This option allows you to specify the default settings for file attachments
    | in your application.
    |
    */

    'attachment' => [
        'disk'      => env('PLATFORM_FILESYSTEM_DISK', 'public'),
        'generator' => Generator::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Icons Path
    |--------------------------------------------------------------------------
    |
    | Provide the path from your app to your SVG icons directory.
    |
    */

    'icons' => [
        'bs' => BootstrapIconsPath::getFolder(),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Notifications are an excellent way to inform your users about what is
    | happening in your application.
    |
    */

    'notifications' => [
        'enabled'  => true,
        'interval' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    |
    | This configuration option determines which models will be searchable in
    | the sidebar search feature.
    |
    */

    'search' => [
        // \App\Models\User::class
    ],

    /*
    |--------------------------------------------------------------------------
    | Hotwire Turbo
    |--------------------------------------------------------------------------
    |
    | Turbo Drive maintains a cache of recently visited pages.
    |
    */

    'turbo' => [
        'cache'          => true,
        'prefetch'       => true,
        'refresh-method' => 'replace',
        'refresh-scroll' => 'preserve',
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Page
    |--------------------------------------------------------------------------
    |
    | If the request does not match any route and arguments, Orchid will
    | automatically generate its own 404 page.
    |
    */

    'fallback' => true,

    /*
    |--------------------------------------------------------------------------
    | Workspace
    |--------------------------------------------------------------------------
    |
    | The workspace option sets the template that wraps the content of the screens.
    |
    | Options: 'platform::workspace.compact', 'platform::workspace.full'
    |
    */

    'workspace' => 'platform::workspace.compact',

    /*
    |--------------------------------------------------------------------------
    | Prevents Abandonment
    |--------------------------------------------------------------------------
    |
    | This option determines whether the Prevents Abandonment feature is enabled.
    |
    */

    'prevents_abandonment' => true,

    /*
    |--------------------------------------------------------------------------
    | Service Provider
    |--------------------------------------------------------------------------
    |
    | This value is a class namespace of the platform's service provider.
    |
    */

    'provider' => PlatformProvider::class,

];
