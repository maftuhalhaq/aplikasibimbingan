<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- DAFTAR CONTROLLER (Pastikan semua ada di sini) ---
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BimbinganController;
use App\Http\Controllers\Api\SkripsiController;
use App\Http\Controllers\Api\DosenController;
use App\Http\Controllers\Api\ChatController;// <--- INI YANG BARU
// -----------------------------------------------------

// 1. Route Public (Bisa diakses tanpa login)
Route::post('/login', [AuthController::class, 'login']);

Route::get('/cek-koneksi', function () {
    return response()->json([
        'status' => 'Sukses!',
        'pesan' => 'Halo Android, Laptop sudah terhubung!'
    ]);
});

// 2. Route Private (Harus Login & Punya Token)
Route::middleware('auth:sanctum')->group(function () {

    // Cek User Login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Fitur Bimbingan (Upload Revisi)
    Route::post('/bimbingan', [BimbinganController::class, 'store']);
    Route::get('/bimbingan', [BimbinganController::class, 'index']);  // Lihat List (INI YANG BARU)

    // --- FITUR SKRIPSI (BARU) ---
    Route::post('/skripsi', [SkripsiController::class, 'store']); // Ajukan Judul
    Route::get('/skripsi', [SkripsiController::class, 'index']);  // Lihat Status


    // ... route lainnya ...

    // Menu Dosen
    Route::get('/dosen/bimbingan', [BimbinganController::class, 'indexDosen']);
    Route::post('/dosen/bimbingan/{id}', [BimbinganController::class, 'update']);

    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::get('/list-dosen', [DosenController::class, 'index']); // <-- BARU


    // Route Chat
    Route::post('/chat/send', [ChatController::class, 'sendMessage']);
    Route::get('/chat/{user_id}', [ChatController::class, 'getMessages']); // user_id adalah lawan bicara
    Route::get('/my-dosen', [ChatController::class, 'getMyDosen']); // Helper buat Mhs

});