<?php

use Illuminate\Support\Facades\Auth;

define('IS_ADMIN', config('ADMIN_TOKEN', 'admin545'));

if (!function_exists('isAdmin')) {
    function isAdmin()
    {
        return Auth::user()->role === IS_ADMIN;
    }
}
