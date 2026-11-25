<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PetaController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\ProfilSekolahController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\Admin\UserStatusController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/health', function () {
    try {
        DB::connection()->getPdo();

        return response()->json([
            'status' => 'ok',
            'application' => 'SIG Sekolah',
            'database' => 'connected',
        ], 200);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'application' => 'SIG Sekolah',
            'database' => 'disconnected',
        ], 503);
    }
})->name('health');

// Admin: kelola pengguna
Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class)->except('show');

    Route::patch('/admin/users/{user}/toggle-status', [UserStatusController::class, 'toggle'])->name('admin.users.toggle-status');
});

// Publik
Route::get('/', [PetaController::class, 'index'])->name('peta.index');
Route::get('/data/sekolah', [PetaController::class, 'geojson'])->name('peta.data');

Route::get('/profil-sekolah/{sekolah}', [PetaController::class, 'profil'])->name('peta.profil');

Route::get('/pencarian', [PetaController::class, 'cari'])->name('peta.cari');
Route::get('/klaster', [PetaController::class, 'klaster'])->name('peta.klaster');
Route::get('/peta-panas', [PetaController::class, 'panas'])->name('peta.panas');
Route::get('/wilayah', [PetaController::class, 'wilayah'])->name('peta.wilayah');
Route::get('/data/wilayah', [PetaController::class, 'geojsonWilayah'])->name('peta.data.wilayah');
Route::get('/rute', [PetaController::class, 'rute'])->name('peta.rute');
Route::get('/cek-alamat', [PetaController::class, 'alamat'])->name('peta.alamat');

// Tamu
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');
});
// Pengguna yang sudah login
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard');

    Route::get('/ubah-kata-sandi', [PasswordController::class, 'edit'])
        ->name('password.edit');

    Route::put('/ubah-kata-sandi', [PasswordController::class, 'update'])
        ->name('password.update');
    // Dinas: daftar, tambah, dan hapus sekolah
    Route::middleware('role:dinas')->prefix('dinas')->name('dinas.')
        ->group(function () {
            Route::resource('sekolah', SekolahController::class)
                ->only(['index', 'create', 'store', 'destroy']);
        });
    // Dinas dan Sekolah: ubah data (dibatasi Policy)
    Route::middleware('role:dinas,sekolah')->group(function () {
        Route::get('sekolah/{sekolah}/edit', [SekolahController::class, 'edit'])
            ->name('sekolah.edit');
        Route::put('sekolah/{sekolah}', [SekolahController::class, 'update'])
            ->name('sekolah.update');
        Route::get(
            'sekolah/{sekolah}/profil',
            [ProfilSekolahController::class, 'edit']
        )->name('sekolah.profil.edit');
        Route::put(
            'sekolah/{sekolah}/profil',
            [ProfilSekolahController::class, 'update']
        )->name('sekolah.profil.update');
    });
});



