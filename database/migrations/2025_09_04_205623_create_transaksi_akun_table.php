<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksi_akun', function (Blueprint $table) {
            $table->id();
            $table->foreignId('akun_id')->constrained('akun')->comment('ID Akun');
            $table->date('tanggal')->comment('Tanggal Transaksi');
            $table->string('kode_referensi')->comment('Kode Referensi Transaksi');
            $table->enum('tipe_referensi', ['PEMBELIAN', 'PENJUALAN', 'PENGELUARAN', 'LAINNYA'])->comment('Tipe Referensi');
            $table->foreignId('referensi_id')->nullable()->comment('ID Referensi (pembelian_id, penjualan_id, etc)');
            $table->text('deskripsi')->nullable()->comment('Deskripsi Transaksi');
            $table->decimal('debit', 15, 2)->default(0)->comment('Jumlah Debit');
            $table->decimal('kredit', 15, 2)->default(0)->comment('Jumlah Kredit');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_akun');
    }
};
