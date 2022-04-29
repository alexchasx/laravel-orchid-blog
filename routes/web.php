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

Route::controller(ArticleController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('article.{id}', 'show')->name('articleShow');
});

// Route::get('/', ['as' => 'index', 'uses' => 'SiteController@index']);
// Route::get('article.{id}', 'SiteController@show')->name('articleShow');
// Route::get('category.{categoryId}', 'SiteController@showByCategory')->name('showByCategory');
// Route::get('tag.{tagId}', 'SiteController@showByTag')->name('showByTag');
