<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SiteController;
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

Route::get('/dashboard', function () {
    return redirect('admin/article/index');
})->middleware(['auth'])->name('dashboard');

require __DIR__ . '/auth.php';

Route::get('contact', [ContactController::class, 'show'])->name('contact');

Route::controller(SiteController::class)->group(function () {
    Route::get('/', 'index')->name('blog');
    Route::get('article.{id}', 'show')->name('articleShow');
    Route::get('category.{id}', 'showByCategory')->name('showByCategory');
    Route::get('tag.{id}', 'showByTag')->name('showByTag');
});

Route::controller(CommentController::class)->group(function () {
    Route::get('comment.index', 'index')->name('commentIndex');
    Route::get('comment.{id}', 'show')->name('commentShow');
    Route::post('comment.create', 'store')->name('commentStore');
    Route::post('comment.update', 'update')->name('commentUpdate');
    Route::delete('delete.{comment}', 'delete')->name('commentDelete');
    Route::get('restore.{comment}', 'restore')->name('commentRestore');
    Route::get('comment.status.{comment}', 'statusChange')->name('commentStatusChange');
});

Route::post('send.simple.email', 'MailController@simplePHPEmail')->name('simplePHPEmail');
