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
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\ReturPembelianController;
use App\Http\Controllers\ReturPenjualanController;
use App\Http\Controllers\SatuanObatController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Laporan\LabaRugiController;
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
    Route::get('/dashboard/data', [DashboardController::class, 'getUpdatedData'])->name('dashboard.data');
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
    Route::get('obat/import', [ObatController::class, 'importForm'])->name('obat.import');
    Route::post('obat/import', [ObatController::class, 'importProcess'])->name('obat.import.process');
    Route::get('obat/template', [ObatController::class, 'downloadTemplate'])->name('obat.template');
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
    Route::post('pembelian/{pembelian}/update-status', [PembelianController::class, 'updateStatus'])->name('pembelian.update-status');
    Route::get('pembelian/obat-satuans/{obat}', [PembelianController::class, 'getObatSatuans'])->name('pembelian.obat-satuans');    // Retur Pembelian management
    Route::post('retur_pembelian/search', [ReturPembelianController::class, 'searchPembelian'])->name('retur_pembelian.search');
    Route::post('retur_pembelian/search_select2', [ReturPembelianController::class, 'searchSelect2'])->name('retur_pembelian.search_select2');
    Route::post('retur_pembelian/search_by_id', [ReturPembelianController::class, 'searchById'])->name('retur_pembelian.search_by_id');
    Route::resource('retur_pembelian', ReturPembelianController::class)->except(['edit', 'update']);

    // Retur Penjualan management
    Route::get('retur_penjualan/search_select2', [ReturPenjualanController::class, 'searchSelect2'])->name('retur_penjualan.search_select2');
    Route::post('retur_penjualan/search_by_id', [ReturPenjualanController::class, 'searchById'])->name('retur_penjualan.search_by_id');
    Route::resource('retur_penjualan', ReturPenjualanController::class)->except(['edit', 'update']);

    // Penjualan management
    Route::get('penjualan/{id}/print', [PenjualanController::class, 'printPdf'])->name('penjualan.print');
    Route::post('penjualan/search-obat', [PenjualanController::class, 'searchObat'])->name('penjualan.search_obat');
    Route::post('penjualan/get-stok-detail', [PenjualanController::class, 'getStokDetail'])->name('penjualan.get_stok_detail');
    Route::resource('penjualan', PenjualanController::class)->except(['edit', 'update', 'destroy']);

    // Settings management (view only for Apoteker, edit for Superadmin)
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::match(['put', 'patch'], 'settings', [SettingController::class, 'update'])
        ->name('settings.update')
        ->middleware('role:Superadmin');

    // Laporan Laba Rugi routes
    Route::get('laporan/laba-rugi', [LabaRugiController::class, 'index'])->name('laporan.laba-rugi.index');
    Route::get('laporan/laba-rugi/pdf', [LabaRugiController::class, 'generatePdf'])->name('laporan.laba-rugi.pdf');
    Route::get('laporan/laba-rugi/print', [LabaRugiController::class, 'print'])->name('laporan.laba-rugi.print');

    // laporan penjualan
    Route::get('laporan/penjualan', [PenjualanController::class, 'index'])->name('laporan.penjualan.index');
    Route::get('laporan/penjualan/pdf', [PenjualanController::class, 'generatePdf'])->name('laporan.penjualan.pdf');
    Route::get('laporan/penjualan/print', [PenjualanController::class, 'print'])->name('laporan.penjualan.print');

    // Stock Opname - Note: specific routes must come BEFORE the resource route
    Route::get('stock_opname/search-obat', [StockOpnameController::class, 'searchObat'])->name('stock_opname.search_obat');
    Route::get('stock_opname/get-stok-detail', [StockOpnameController::class, 'getStokDetail'])->name('stock_opname.get_stok_detail');
    Route::post('stock_opname/{stockOpname}/add-obat', [StockOpnameController::class, 'addObat'])->name('stock_opname.add_obat');
    Route::delete('stock_opname/{stockOpname}/remove-obat/{detail}', [StockOpnameController::class, 'removeObat'])->name('stock_opname.remove_obat');
    Route::put('stock_opname/{stockOpname}/complete', [StockOpnameController::class, 'complete'])->name('stock_opname.complete');
    Route::get('stock_opname/{stockOpname}/print', [StockOpnameController::class, 'print'])->name('stock_opname.print');
    Route::resource('stock_opname', StockOpnameController::class);
});
