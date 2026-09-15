<?php

use App\Http\Controllers\Api\ContactApiController;
use App\Http\Controllers\Api\PublicApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Публичный JSON API для Nuxt-фронтенда
|--------------------------------------------------------------------------
*/

Route::get('articles', [PublicApiController::class, 'articles']);
Route::get('articles/{article}', [PublicApiController::class, 'article']);
Route::get('rubrics', [PublicApiController::class, 'rubrics']);
Route::get('tags', [PublicApiController::class, 'tags']);
Route::post('contact', [ContactApiController::class, 'store']);
