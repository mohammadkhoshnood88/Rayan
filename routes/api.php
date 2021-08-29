<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1'/* , 'middleware' => 'api.validation'*/], function () {

    Route::post('login' , [\App\Http\Controllers\API\AuthController::class , 'login']);

    Route::group(['prefix' => 'invoice'], function () {
        Route::get('/' , [\App\Http\Controllers\API\InvoiceController::class , 'index']);
        Route::get('/{id}' , [\App\Http\Controllers\API\InvoiceController::class , 'show']);
    });

    Route::group(['prefix' => 'payment'], function () {
        Route::get('/' , [\App\Http\Controllers\API\PaymentController::class , 'index']);
        Route::get('/{id}' , [\App\Http\Controllers\API\PaymentController::class , 'show']);
        Route::post('/{id}/status' , [\App\Http\Controllers\API\PaymentController::class , 'change_status']);
    });

});