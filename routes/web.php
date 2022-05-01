<?php

use App\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__ . '/auth.php';

// Contact
Route::get('contact', function () {
    return view('contact');
})->name('contact');

Route::controller(ArticleController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('article.{id}', 'show')->name('articleShow');
    Route::get('category.{categoryId}', 'showByCategory')->name('showByCategory');
});

// Route::get('tag.{tagId}', 'SiteController@showByTag')->name('showByTag');
