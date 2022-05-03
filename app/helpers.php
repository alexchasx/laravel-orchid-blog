<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

if (!function_exists('isAdmin')) {
    function isAdmin()
    {
        // return Auth::user()->role === env('ADMIN_TOKEN');
        return (new User())->role === env('ADMIN_TOKEN');
    }
}
