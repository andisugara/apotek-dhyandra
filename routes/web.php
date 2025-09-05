<?php

use App\Http\Controllers\AkunController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GolonganObatController;
use App\Http\Controllers\KategoriObatController;
use App\Http\Controllers\LokasiObatController;
use App\Http\Controllers\ObatController;
use App\Http\Controllers\PabrikController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\ReturPembelianController;
use App\Http\Controllers\SatuanObatController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])->name('login.post');
Route::get('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// Main routes
Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Routes for Superadmin only
Route::middleware(['role:Superadmin'])->group(function () {
    // Supplier management
    Route::resource('supplier', SupplierController::class);

    // User management
    Route::resource('user', UserController::class);

    // Pasien management
    Route::resource('pasien', PasienController::class);

    // Golongan Obat management
    Route::resource('golongan_obat', GolonganObatController::class);

    // Kategori Obat management
    Route::resource('kategori_obat', KategoriObatController::class);

    // Lokasi Obat management
    Route::resource('lokasi_obat', LokasiObatController::class);

    // Satuan Obat management
    Route::resource('satuan_obat', SatuanObatController::class);

    // Pabrik management
    Route::resource('pabrik', PabrikController::class);

    // Akun management
    Route::resource('akun', AkunController::class);

    // Obat management
    Route::resource('obat', ObatController::class);
    Route::post('obat/{obat}/satuan', [ObatController::class, 'addSatuan'])->name('obat.satuan.add');
    Route::delete('obat/{obat}/satuan/{satuan}', [ObatController::class, 'removeSatuan'])->name('obat.satuan.remove');
    Route::post('obat/{obat}/stok', [ObatController::class, 'addStock'])->name('obat.stok.add');
    Route::put('obat/{obat}/stok', [ObatController::class, 'updateStock'])->name('obat.stok.update');
    Route::delete('obat/{obat}/stok/{stok}', [ObatController::class, 'removeStock'])->name('obat.stok.remove');
});

// Routes for both Superadmin and Apoteker
Route::middleware(['auth'])->group(function () {
    // Pengeluaran management
    Route::resource('pengeluaran', PengeluaranController::class);

    // Pembelian management
    Route::resource('pembelian', PembelianController::class);
    Route::get('pembelian/obat/{id}/satuans', [PembelianController::class, 'getObatSatuans']);

    // Retur Pembelian management
    Route::post('retur_pembelian/search', [ReturPembelianController::class, 'searchPembelian'])->name('retur_pembelian.search');
    Route::post('retur_pembelian/search_select2', [ReturPembelianController::class, 'searchSelect2'])->name('retur_pembelian.search_select2');
    Route::post('retur_pembelian/search_by_id', [ReturPembelianController::class, 'searchById'])->name('retur_pembelian.search_by_id');
    Route::resource('retur_pembelian', ReturPembelianController::class)->except(['edit', 'update']);

    // Settings management (view only for Apoteker, edit for Superadmin)
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::match(['put', 'patch'], 'settings', [SettingController::class, 'update'])
        ->name('settings.update')
        ->middleware('role:Superadmin');
});
