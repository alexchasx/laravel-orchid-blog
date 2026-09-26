<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ConsentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SubscriberController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

// Sitemap — без кэширования в роутах, кэш внутри контроллера.
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

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

// Публичные страницы текстов согласий (152-ФЗ).
Route::get('consent/processing', [ConsentController::class, 'processing'])
    ->name('consent.processing');
Route::get('consent/distribution', [ConsentController::class, 'distribution'])
    ->name('consent.distribution');

// Отзыв согласия на обработку/распространение ПДн.
Route::get('consent/revoke', fn () => view('consent.revoke'))
    ->name('consent.revoke.form');
Route::post('consent/revoke', [ConsentController::class, 'revoke'])
    ->middleware('throttle:10,1')
    ->name('consent.revoke');

// Подписка на новые статьи.
Route::post('subscribe', [SubscriberController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('subscribe.store');
Route::get('unsubscribe/{token}', [SubscriberController::class, 'unsubscribe'])
    ->name('subscribe.unsubscribe');

// Редирект со старых числовых URL на slug-URL (301).
Route::get('rubric/{legacyId}', function (int $legacyId): RedirectResponse {
    $rubric = \App\Models\Rubric::withTrashed()->find($legacyId);

    if ($rubric?->slug) {
        return redirect()->route('showByRubric', ['rubric' => $rubric->slug], 301);
    }

    abort(404);
})->whereNumber('legacyId')->name('rubric.legacy.redirect');

Route::get('tag/{legacyId}', function (int $legacyId): RedirectResponse {
    $tag = \App\Models\Tag::withTrashed()->find($legacyId);

    if ($tag?->slug) {
        return redirect()->route('showByTag', ['tag' => $tag->slug], 301);
    }

    abort(404);
})->whereNumber('legacyId')->name('tag.legacy.redirect');

Route::controller(ArticleController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('notpublic', 'showNotPublic')->name('notpublic')
        ->middleware(['auth', 'access:platform.custom.articles']);
    Route::get('rubric/{rubric:slug}', 'showByRubric')->name('showByRubric');
    Route::get('tag/{tag:slug}', 'showByTag')->name('showByTag');
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
