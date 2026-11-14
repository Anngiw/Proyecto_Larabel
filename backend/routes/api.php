<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// Sprint 3 - HU-03
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
// Sprint 4 - HU-04 (Ruta protegida para cerrar sesión)
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);