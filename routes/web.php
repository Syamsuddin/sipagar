<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/login', [LoginController::class, 'create'])->name('login');

// S0: dashboard dummy tanpa auth; middleware auth + Policy masuk di S1 (F01).
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
