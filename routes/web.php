<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\SirkulasiController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [BukuController::class, 'index'])->name('bukus.index');

    Route::get('/sirkulasi', [SirkulasiController::class, 'index'])->name('sirkulasi.index');
    Route::post('/sirkulasi/pinjam', [SirkulasiController::class, 'pinjamStore'])->name('sirkulasi.pinjam');
    Route::patch('/sirkulasi/kembali/{id}', [SirkulasiController::class, 'kembalikanStore'])->name('sirkulasi.kembali');
});