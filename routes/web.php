<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/setlocale/{locale}', [MainController::class, 'setLocale'])->name('setlocale');

Route::get('contact', [ContactController::class, 'index'])->name('contact');
Route::post('contact.store', [ContactController::class, 'store'])->name('contact.store');

Route::controller(ArticleController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('notpublic', 'showNotPublic')->name('notpublic')
        ->middleware(['auth', 'access:platform.custom.articles']);
    Route::get('rubric.{rubric}', 'showByRubric')->name('showByRubric');
    Route::get('tag.{tag}', 'showByTag')->name('showByTag');
    Route::get('article.{article}', 'show')->name('articleShow');
});

Route::controller(CommentController::class)->group(function () {
    Route::post('comment.create', 'store')->name('commentStore');
    Route::post('comment.update', 'update')->name('commentUpdate');
    Route::delete('delete.{comment}', 'delete')->name('commentDelete');
});

Auth::routes();

