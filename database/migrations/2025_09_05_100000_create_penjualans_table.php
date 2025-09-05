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
        Schema::create('penjualans', function (Blueprint $table) {
            $table->id();
            $table->string('no_faktur')->unique()->comment('Nomor Faktur Penjualan');
            $table->date('tanggal_penjualan')->comment('Tanggal Penjualan');
            $table->foreignId('pasien_id')->nullable()->constrained('pasien')->comment('ID Pasien');
            $table->enum('jenis', ['TUNAI', 'NON_TUNAI'])->default('TUNAI')->comment('Jenis Penjualan');
            $table->decimal('subtotal', 15, 2)->default(0)->comment('Subtotal Penjualan');
            $table->decimal('diskon_total', 15, 2)->default(0)->comment('Diskon Total');
            $table->decimal('ppn_total', 15, 2)->default(0)->comment('PPN Total');
            $table->decimal('grand_total', 15, 2)->default(0)->comment('Grand Total');
            $table->decimal('bayar', 15, 2)->default(0)->comment('Jumlah Pembayaran');
            $table->decimal('kembalian', 15, 2)->default(0)->comment('Jumlah Kembalian');
            $table->foreignId('user_id')->constrained('users')->comment('User yang membuat');
            $table->text('keterangan')->nullable()->comment('Keterangan tambahan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualans');
    }
};
