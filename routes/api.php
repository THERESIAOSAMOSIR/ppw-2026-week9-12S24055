<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\BukuApiController;
use App\Http\Controllers\Api\V1\GithubPortfolioController;
use App\Http\Controllers\Api\V1\PeminjamanApiController;

Route::prefix('v1')->group(function () {

    // =========================================================================
    // 1. ENDPOINT PUBLIK (Dapat diakses tanpa token: Login, Katalog Baca, & Consumer)
    // =========================================================================
    Route::post('/auth/login', [AuthTokenController::class, 'login']);

    Route::get('/bukus', [BukuApiController::class, 'index']);
    Route::get('/bukus/{id}', [BukuApiController::class, 'show']);
    Route::get('/bukus/{id}/referensi-eksternal', [BukuApiController::class, 'referensiEksternal']);

    Route::get('/portfolios/github/{username}', [GithubPortfolioController::class, 'show']);

    // =========================================================================
    // 2. ENDPOINT TERPROTEKSI STATELESS BEARER TOKEN (Wajib auth:sanctum)
    // =========================================================================
    Route::middleware('auth:sanctum')->group(function () {
        // Profil & Pencabutan Token Pustakawan
        Route::get('/auth/me', [AuthTokenController::class, 'me']);
        Route::post('/auth/logout', [AuthTokenController::class, 'logout']);

        // Mutasi Katalog Buku (POST, PUT vs PATCH, DELETE)
        Route::post('/bukus', [BukuApiController::class, 'store']);
        Route::put('/bukus/{id}', [BukuApiController::class, 'update']);
        Route::patch('/bukus/{id}', [BukuApiController::class, 'update']);
        Route::delete('/bukus/{id}', [BukuApiController::class, 'destroy']);

        // Transaksi Sirkulasi Peminjaman & Pengembalian Buku (ACID + lockForUpdate)
        Route::get('/peminjamans', [PeminjamanApiController::class, 'index']);
        Route::get('/peminjamans/{id}', [PeminjamanApiController::class, 'show']);
        Route::post('/peminjamans', [PeminjamanApiController::class, 'store']);
        Route::patch('/peminjamans/{id}/kembalikan', [PeminjamanApiController::class, 'kembalikan']);
    });
});
