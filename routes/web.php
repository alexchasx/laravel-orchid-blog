<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriberController;
use Illuminate\Support\Facades\Route;

// Тестовые маршруты (не удалять)
Route::get('/test-400', fn () => abort(400));
Route::get('/test-401', fn () => abort(401));
Route::get('/test-403', fn () => abort(403));
Route::get('/test-404', fn () => abort(404));
Route::get('/test-405', fn () => abort(405));
Route::get('/test-408', fn () => abort(408));
Route::get('/test-419', fn () => abort(419));
Route::get('/test-429', fn () => abort(429));
Route::get('/test-500', fn () => abort(500));
Route::get('/test-502', fn () => abort(502));
Route::get('/test-503', fn () => abort(503));
Route::get('/test-504', fn () => abort(504));

Route::get('/setlocale/{locale}', [MainController::class, 'setLocale'])->name('setlocale');

Route::get('contact', [ContactController::class, 'index'])->name('contact');
Route::post('contact.store', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::get('about', [MainController::class, 'about'])->name('about');
Route::get('privacy', [MainController::class, 'privacy'])->name('privacy');

// Подписка на новые статьи.
Route::post('subscribe', [SubscriberController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('subscribe.store');
Route::get('unsubscribe/{token}', [SubscriberController::class, 'unsubscribe'])
    ->name('subscribe.unsubscribe');

Route::controller(ArticleController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('notpublic', 'showNotPublic')->name('notpublic')
        ->middleware(['auth', 'access:platform.custom.articles']);
    Route::get('rubric/{rubric}', 'showByRubric')->name('showByRubric');
    Route::get('tag/{tag}', 'showByTag')->name('showByTag');
    Route::get('article/{article:slug}', 'show')->name('articleShow');
});

Route::post('comment.create', [CommentController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('commentStore');

Route::middleware('auth')->delete('delete.{comment}', [CommentController::class, 'delete'])
    ->name('commentDelete');

// Breeze dashboard (используется для редиректов после входа).
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Маршруты аутентификации Breeze.
require __DIR__.'/auth.php';
