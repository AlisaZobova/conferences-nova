<?php

use App\Http\Controllers\Auth\NovaLoginController;
use Illuminate\Support\Facades\Route;

Route::post('nova/login', [ NovaLoginController::class, 'login' ])->name('nova.login');
