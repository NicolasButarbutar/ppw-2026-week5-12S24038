<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - SIPUS-Del (Sistem Informasi Perpustakaan IT Del)
|--------------------------------------------------------------------------
*/

// Redirect root ke katalog buku
Route::get('/', function () {
    return redirect()->route('buku.index');
});

// Autentikasi Pustakawan (Hanya untuk Guest)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
});

// Rute Publik (Katalog & Detail Buku / Kategori)
Route::get('buku', [BukuController::class, 'index'])->name('buku.index');
Route::get('kategori', [KategoriController::class, 'index'])->name('kategori.index');

// Rute Terproteksi Otentikasi Pustakawan (Middleware auth)
Route::middleware('auth')->group(function () {
    // Logout Pustakawan
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Mutasi Data Kategori
    Route::get('kategori/create', [KategoriController::class, 'create'])->name('kategori.create');
    Route::post('kategori', [KategoriController::class, 'store'])->name('kategori.store');
    Route::get('kategori/{kategori}/edit', [KategoriController::class, 'edit'])->name('kategori.edit');
    Route::match(['put', 'patch'], 'kategori/{kategori}', [KategoriController::class, 'update'])->name('kategori.update');
    Route::delete('kategori/{kategori}', [KategoriController::class, 'destroy'])->name('kategori.destroy');

    // Mutasi Data Buku
    Route::get('buku/create', [BukuController::class, 'create'])->name('buku.create');
    Route::post('buku', [BukuController::class, 'store'])->name('buku.store');
    Route::get('buku/{buku}/edit', [BukuController::class, 'edit'])->name('buku.edit');
    Route::match(['put', 'patch'], 'buku/{buku}', [BukuController::class, 'update'])->name('buku.update');
    Route::delete('buku/{buku}', [BukuController::class, 'destroy'])->name('buku.destroy');

    // Sirkulasi Peminjaman & Pengembalian Buku Berbasis ACID
    Route::get('peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
    Route::get('peminjaman/create', [PeminjamanController::class, 'create'])->name('peminjaman.create');
    Route::post('peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
    Route::post('peminjaman/{peminjaman}/kembali', [PeminjamanController::class, 'kembalikan'])->name('peminjaman.kembali');
});

// Detail Publik (ditempatkan setelah route /create agar tidak tumpang tindih)
Route::get('buku/{buku}', [BukuController::class, 'show'])->name('buku.show');
Route::get('kategori/{kategori}', [KategoriController::class, 'show'])->name('kategori.show');
