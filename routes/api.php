<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Routes publiques (pas besoin d'être connecté)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées (il faut avoir un token valide pour y accéder)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // NOUVELLES ROUTES CRYPTO ICI :
    Route::get('/cryptos', [App\Http\Controllers\CryptoController::class, 'index']);
    Route::get('/my-crypto-wallets', [App\Http\Controllers\CryptoController::class, 'myWallets']);
});