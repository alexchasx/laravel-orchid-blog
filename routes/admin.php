<?php

use App\Http\Controllers\Admin\AdminArticleController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin'], function () {
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

    Route::group(['prefix' => 'user'], function () {

        Route::get('index', 'Admin\AdminUserController@index')->name('userIndex');
        Route::get('update.{id}', 'Admin\AdminUserController@edit')->name('userEdit');
        Route::post('update', 'Admin\AdminUserController@update')->name('userUpdate');
        Route::delete('delete.{id}', 'Admin\AdminUserController@destroy')->name('userDelete');
        Route::get('restore.{user}', 'Admin\AdminUserController@restore')->name('userRestore');
    });

    Route::group(['prefix' => 'tag'], function () {

        Route::get('index', 'Admin\AdminTagController@index')->name('tagIndex');
        Route::post('create', 'Admin\AdminTagController@store')->name('tagStore');
        Route::get('create', 'Admin\AdminTagController@create')->name('tagCreate');
        Route::get('update.{id}', 'Admin\AdminTagController@edit')->name('tagEdit');
        Route::any('update', 'Admin\AdminTagController@update')->name('tagUpdate');
        Route::delete('delete.{id}', 'Admin\AdminTagController@destroy')->name('tagDelete');
        Route::get('restore.{tag}', 'Admin\AdminTagController@restore')
            ->name('tagRestore');
        Route::get('status.{tag}', 'Admin\AdminTagController@statusChange')
            ->name('tagStatusChange');
    });

    Route::group(['prefix' => 'category'], function () {

        Route::get('index', 'Admin\AdminCategoryController@index')->name('categoryIndex');
        Route::post('create', 'Admin\AdminCategoryController@store')->name('categoryStore');
        Route::get('create', 'Admin\AdminCategoryController@create')->name('categoryCreate');
        Route::get('update.{id}', 'Admin\AdminCategoryController@edit')->name('categoryEdit');
        Route::any('update', 'Admin\AdminCategoryController@update')->name('categoryUpdate');
        Route::delete('category.{category}', 'Admin\AdminCategoryController@destroy')
            ->name('categoryDelete');
        Route::get('category.restore.{category}', 'Admin\AdminCategoryController@restore')
            ->name('categoryRestore');
        Route::get('status.{category}', 'Admin\AdminCategoryController@statusChange')
            ->name('categoryStatusChange');
    });

    Route::resource('file', 'Admin\AdminFileController', [
        'only' => [
            'store',
            'update',
            'destroy',
        ],
        [
            'names' => [
                'store' => 'file.store',
                'update' => 'file.update',
                'destroy' => 'file.destroy',
            ]
        ]
    ]);
});
