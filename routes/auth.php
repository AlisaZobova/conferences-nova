<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest', 'web'])->group(
    function () {

        Route::post('register', [RegisteredUserController::class, 'store']);

        Route::post('register/{user}', [RegisteredUserController::class, 'store_additional'])
        ->name('store_additional');

        Route::post('login', [AuthenticatedSessionController::class, 'store']);
    }
);


Route::middleware('auth')->group(
    function () {

        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
    }
);
