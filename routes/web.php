<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ConsentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LegacyRedirectController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\TestController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

// robots.txt — динамическая ссылка на sitemap.
Route::get('/robots.txt', RobotsController::class)->name('robots');

// Sitemap — без кэширования в роутах, кэш внутри контроллера.
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// RSS 2.0-лента — без кэширования в роутах, кэш внутри контроллера.
Route::get('/rss', FeedController::class)->name('feed');

// Тестовые маршруты для кастомных страниц ошибок (не удалять).
// Статусы: 400, 401, 403, 404, 405, 408, 419, 429, 500, 502, 503, 504.
Route::get('/test-{status}', TestController::class)
    ->whereNumber('status')
    ->name('test');

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
Route::get('consent/revoke', [ConsentController::class, 'revokeForm'])
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
Route::get('rubric/{legacyId}', [LegacyRedirectController::class, 'rubric'])
    ->whereNumber('legacyId')
    ->name('rubric.legacy.redirect');

Route::get('tag/{legacyId}', [LegacyRedirectController::class, 'tag'])
    ->whereNumber('legacyId')
    ->name('tag.legacy.redirect');

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
Route::get('/dashboard', [MainController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Маршруты аутентификации Breeze.
require __DIR__.'/auth.php';
