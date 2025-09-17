<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE transaksi_akun MODIFY COLUMN tipe_referensi ENUM('PEMBELIAN', 'PENJUALAN', 'PENGELUARAN', 'LAINNYA', 'RETUR_PEMBELIAN', 'RETUR_PENJUALAN') NOT NULL COMMENT 'Tipe Referensi'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE transaksi_akun MODIFY COLUMN tipe_referensi ENUM('PEMBELIAN', 'PENJUALAN', 'PENGELUARAN', 'LAINNYA', 'RETUR_PEMBELIAN') NOT NULL COMMENT 'Tipe Referensi'");
    }
};
