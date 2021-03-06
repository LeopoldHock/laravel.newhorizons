<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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

// JSONApi (local, not authenticated)
Route::group([], function ($router) {
    JsonApi::register('default')->withNamespace('App\Http\Controllers\Api')->singularControllers()->routes(function ($api) {
        $api->resource('bug-reports')->only("create");
        $api->resource("users")->only("create")->middleware("throttle:sign-up")->controller()->relationships(function ($relations) {
            $relations->hasMany("userRoles");
        });
        $api->resource("email-verifications")->only("read")->controller();
        $api->resource("password-resets")->only("read", "update")->controller()->middleware("throttle:reset-password-update");/*->routes(function ($actions) {
            $actions->post("{record}/update-password", "updatePassword");
        })*/;
    });
});

/*// JSONApi (local, authenticated)
Route::group([
    'middleware' => ['api-local']
]);*/

// Authentication
Route::group([
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', 'App\Http\Controllers\AuthController@login');
    Route::post('logout', 'App\Http\Controllers\AuthController@logout');
    Route::post('refresh', 'App\Http\Controllers\AuthController@refresh');
    Route::post('me', 'App\Http\Controllers\AuthController@me');
});

// Actions that don't require authentication
Route::group([
    "prefix" => "actions"
], function ($router) {
    Route::get("send-verification-code", "App\Http\Controllers\ActionController@sendVerificationCode")->middleware("throttle:email-verification-request");
    Route::get("send-password-reset-code", "App\Http\Controllers\ActionController@sendPasswordResetCode")->middleware("throttle:reset-password-request");
});

Route::fallback(function () {
    return response()->json(["message" => "Route not found."], 404);
})->name("api.fallback.404");
