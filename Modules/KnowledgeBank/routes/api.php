<?php

use Illuminate\Support\Facades\Route;
use Modules\KnowledgeBank\app\Http\Controllers\ContentController;
use Modules\KnowledgeBank\app\Http\Controllers\CategoryController;
use Modules\KnowledgeBank\app\Http\Controllers\TabCategoryController;

Route::group(['middleware' => ['auth:sanctum']], function () use ($router) {
    Route::group(["prefix" => "tab-category", "controller" => TabCategoryController::class], function () use ($router) {

        //* A
        Route::group([], function () use ($router) {
            Route::post('/new/create', 'createTabCategory');
            Route::get('/view-all/tab-categories', 'getAllTabCategory');
            Route::post('/update/tab-category-of/{id}', 'updateTabCategory');
            Route::post('/delete/tab-category-of/{id}', 'deleteTabCategory');
        });

        //* All others except Company
        Route::group([], function () use ($router) {

        });
    });

    Route::group(["prefix" => "category", "controller" => CategoryController::class], function () use ($router) {

        //* A
        Route::group([], function () use ($router) {
            Route::post('/new/create', 'createCategory');
            Route::get('/view-all/categories', 'getAllCategory');
            Route::post('/update/category-of/{id}', 'updateCategory');
            Route::post('/delete/category-of/{id}', 'deleteCategory');
        });
    });

    Route::group(["prefix" => "content", "controller" => ContentController::class], function () use ($router) {

        //* A
        Route::group([], function () use ($router) {
            Route::post('/new/create', 'createContent');
            Route::get('/view-all/contents', 'getAllContent');
            Route::post('/update/content-of/{id}', 'updateContent');
            Route::post('/delete/content-of/{id}', 'deleteContent');
        });

        //*All others except Company
        Route::group([], function () use ($router) {
            Route::get('/view-all/active-contents', 'getAllActiveContent');
            Route::get('/view-single/{id}', 'getSingleContent');
        });
    });
});
