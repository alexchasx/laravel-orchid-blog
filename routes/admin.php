<?php

use App\Http\Controllers\Admin\AdminArticleController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminTagController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin'], function () {

    Route::get('/login', function () {
        return redirect('/');
    });

    Route::group(['prefix' => 'article'], function () {
        // Route::group(['prefix' => 'admin/article', 'middleware' => ['auth', 'admin']], function () {

        Route::controller(AdminArticleController::class)->group(function () {
            Route::get('index', 'index')->name('adminIndex');
            Route::post('create', 'store')->name('articleStore');
            Route::get('create', 'create')->name('articleCreate');
            Route::get('update.{id}', 'edit')->name('articleEdit');
            Route::post('update', 'update')->name('articleUpdate');
            Route::delete('destroy.{id}', 'destroy')->name('articleDelete');
            Route::delete('delete.{article}.{tag}', 'deleteTag')->name('articleTagDelete');
            Route::get('restore.{article}', 'restore')->name('articleRestore');
            Route::get('status.{article}', 'statusChange')->name('articleStatusChange');
        });
    });

    // Route::group(['prefix' => 'user'], function () {

    //     Route::controller(AdminUserController::class)->group(function () {
    //         Route::get('index', 'index')->name('userIndex');
    //         Route::get('update.{id}', 'edit')->name('userEdit');
    //         Route::post('update', 'update')->name('userUpdate');
    //         Route::delete('delete.{id}', 'destroy')->name('userDelete');
    //         Route::get('restore.{user}', 'restore')->name('userRestore');
    //     });
    // });

    Route::group(['prefix' => 'tag'], function () {

        Route::controller(AdminTagController::class)->group(function () {
            Route::get('index', 'index')->name('tagIndex');
            Route::post('create', 'store')->name('tagStore');
            Route::get('create', 'create')->name('tagCreate');
            Route::get('update.{id}', 'edit')->name('tagEdit');
            Route::any('update', 'update')->name('tagUpdate');
            Route::delete('delete.{id}', 'destroy')->name('tagDelete');
            Route::get('restore.{tag}', 'restore')
                ->name('tagRestore');
            Route::get('status.{tag}', 'statusChange')
                ->name('tagStatusChange');
        });
    });

    Route::group(['prefix' => 'category'], function () {

        Route::controller(AdminCategoryController::class)->group(function () {
            Route::get('index', 'index')->name('categoryIndex');
            Route::post('create', 'store')->name('categoryStore');
            Route::get('create', 'create')->name('categoryCreate');
            Route::get('update.{id}', 'edit')->name('categoryEdit');
            Route::any('update', 'update')->name('categoryUpdate');
            Route::delete('category.{category}', 'destroy')
                ->name('categoryDelete');
            Route::get('category.restore.{category}', 'restore')
                ->name('categoryRestore');
            Route::get('status.{category}', 'statusChange')
                ->name('categoryStatusChange');
        });
    });

    // Route::resource('file', 'Admin\AdminFileController', [
    //     'only' => [
    //         'store',
    //         'update',
    //         'destroy',
    //     ],
    //     [
    //         'names' => [
    //             'store' => 'file.store',
    //             'update' => 'file.update',
    //             'destroy' => 'file.destroy',
    //         ]
    //     ]
    // ]);
});