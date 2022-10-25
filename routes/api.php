<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ApiAuthController;

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


Route::group(["prefix" => "v1"], function () { 
    Route::group(["prefix" => "users"], function () { 
        // public routes
        Route::post("login",[ApiAuthController::class,"login"]);
        Route::post("register",[ApiAuthController::class,"register"]);
    });    

    Route::middleware('auth:api')->group(function () {
        Route::post("logout",[ApiAuthController::class,"logout"]);
    });
});