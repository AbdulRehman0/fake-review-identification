<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(["prefix"=>"v1"],function(){
    Route::controller(RegisteredUserController::class)->group(function(){
        Route::post('login', 'login');
    });

    Route::group(["middleware"=>"auth:sanctum"],function(){
        Route::controller(ReviewController::class)->group(function(){
            Route::group(["prefix"=>"review"],function(){
                Route::get('/', 'index');

                Route::get('/show/{review}', 'show');
                Route::post('/store', 'store');
            });

        });
        Route::controller(ProductController::class)->group(function(){
            Route::group(["prefix"=>"product"],function(){
                Route::get('/', 'index');

                Route::get('/show/{product}', 'show');
                Route::post('/store', 'store');
            });

        });
    });


});
