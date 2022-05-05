<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

if (!function_exists('isAdmin')) {
    function isAdmin()
    {
        // return Auth::user()->role === env('ADMIN_TOKEN');
        return (new User())->role === env('ADMIN_TOKEN');
    }
}

if (!function_exists('active_link')) {
    function active_link(string $route, string $active = 'active'): string
    {
        return Route::is($route) ? $active : '';
    }
}

if (!function_exists('alert')) {
    function alert(string $value, string $type = 'success'): void
    {
        session(['alert' => __($value)]);
        session(['type' => __($type)]);
    }
}
