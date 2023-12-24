<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Authorization\app\Http\Controllers\UserController;

Route::post('/login', [UserController::class, 'Login']);
//User Logged in or Not Checking middleware
Route::post('user/new/create/non-company-user', [UserController::class, 'createNonCompanyUser']);
Route::post('/logout', [UserController::class, 'Logout']);

Route::group(['middleware' => ['auth:sanctum']], function () use ($router) {
    Route::group(["prefix" => "user", "controller" => UserController::class], function () use ($router) {

        //* SA
        Route::group([], function () use ($router) {
            Route::post('/new/create/company-user', 'createCompanyUser');
            Route::get('/view-all/companies', 'getAllCompany');
            Route::post('/update/role-of/{id}', 'updateUserRole');
        });
        //* A
        Route::group([], function () use ($router) {
            Route::post('/new/create/non-company-user-with-admin', 'createNonCompanyUser');
            Route::get('/view-all/farmers', 'getAllFarmer');
            Route::get('/view-all/traders', 'getAllTrader');
            Route::post('/update/non-end-user/details-of/{id}', 'updateNonEndUserDetails');
            Route::post('/delete/with/id/{id}', 'deleteUser');
        });
        //* FF,FT
        Route::group([], function () use ($router) {
            Route::post('/update/end-user/details-of/{id}', 'updateEndUserDetails');
        });
        //* C
        Route::group([], function () use ($router) {
            //Route::post('/new/create/company-user', 'createCompanyUser');

        });

    });
});
