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
        Schema::create('pembelian', function (Blueprint $table) {
            $table->id();
            $table->string('no_po')->nullable()->comment('Nomor Purchase Order');
            $table->string('no_faktur')->comment('Nomor Faktur');
            $table->date('tanggal_faktur')->comment('Tanggal Faktur');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->enum('jenis', ['TUNAI', 'HUTANG', 'KONSINYASI'])->comment('Jenis Pembayaran');
            $table->foreignId('akun_kas_id')->nullable()->constrained('akun')->comment('Akun Kas (wajib jika TUNAI)');
            $table->date('tanggal_jatuh_tempo')->nullable()->comment('Tanggal Jatuh Tempo (wajib jika HUTANG)');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('diskon_total', 15, 2)->default(0);
            $table->decimal('ppn_total', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelian');
    }
};
