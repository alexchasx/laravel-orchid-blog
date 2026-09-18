<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/setlocale/{locale}', [MainController::class, 'setLocale'])->name('setlocale');

Route::get('contact', [ContactController::class, 'index'])->name('contact');
Route::post('contact.store', [ContactController::class, 'store'])->name('contact.store');

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
