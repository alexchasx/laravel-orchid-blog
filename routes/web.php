<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Auth;
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
//     return redirect('adminIndex');
// })->middleware(['auth'])->name('dashboard');



Route::get('/issues', function () {
    return view('issues');
})->name('issues');

require __DIR__ . '/auth.php';

Route::get('/setlocale/{locale}', [MainController::class, 'setLocale'])->name('setlocale');

Route::get('contact', [ContactController::class, 'index'])->name('contact');
Route::post('contact.store', [ContactController::class, 'store'])->name('contact.store');

Route::controller(ArticleController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('/notpublic', 'showNotPublic')->name('notpublic')
        ->middleware(['auth', 'access:platform.custom.articles']);
    Route::get('rubric.{id}', 'showByRubric')->name('showByRubric');
    Route::get('tag.{id}', 'showByTag')->name('showByTag');
    Route::get('/{article}', 'show')->name('articleShow');
});

Route::controller(CommentController::class)->group(function () {
    Route::post('comment.create', 'store')->name('commentStore');
    Route::post('comment.update', 'update')->name('commentUpdate');
    Route::delete('delete.{comment}', 'delete')->name('commentDelete');
    // Route::get('comment.status.{comment}', 'statusChange')->name('commentStatusChange');
    // Route::get('ajax/ validation', 'ajaxValidation')->name('ajax_validation');
    // Route::post('ajax/validation/store', 'ajaxValidationStore')->name('ajax_validation_store');
});

//Route::fallback(function () {
//    return view('errors.404');
//});

Auth::routes();

