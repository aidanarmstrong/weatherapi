<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use App\Http\Controllers\api\HomeController;
use App\Http\Controllers\api\PostController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\WeatherController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware([EnsureFrontendRequestsAreStateful::class, 'throttle:api'])
    ->group(function () {
        Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
            return $request->user();
        });
});

// Posts

Route::controller(PostController::class)
    ->prefix('posts')
    ->name('posts')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::post('/', 'store');
        Route::patch('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });

// Users
Route::controller(UserController::class)
    ->prefix('users') 
    ->name('users.')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}', 'show')->name('show');
        Route::patch('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

// Auth Routes 
Route::post('register', [UserController::class, 'register'])->name('register');
Route::post('login', [UserController::class, 'login'])->name('login');

// Authenticated Routes
Route::post('logout', [UserController::class, 'logout'])->middleware('auth:sanctum')->name('logout');


// Weather 
Route::get('weather', [WeatherController::class, 'index']);

