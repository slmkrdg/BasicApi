<?php


use Illuminate\Support\Facades\Route;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Controllers\Users\UserController;
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
        Route::post("login",[ApiAuthController::class,"login"])->middleware(["middlewareInputValidator:". LoginRequest::class]);
        Route::post("register",[UserController::class,"register"]);
        Route::post("validation",[UserController::class,"validation"]);
    });    

    Route::middleware('auth:api')->group(function () {
        Route::group(["prefix" => "twitter"], function () { 
            Route::get("tweets/page/{pageNumber?}",[UserController::class,"getUserTweets"]);
            Route::post("tweets",[UserController::class,"setUserTweets"]);
            Route::patch("tweets/{tweetId}",[UserController::class,"updateUserTweets"]);
        });
    });
});