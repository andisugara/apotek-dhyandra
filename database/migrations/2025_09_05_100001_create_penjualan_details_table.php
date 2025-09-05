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
        Schema::create('penjualan_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->constrained('penjualans')->onDelete('cascade')->comment('ID Penjualan');
            $table->foreignId('obat_id')->constrained('obat')->comment('ID Obat');
            $table->foreignId('satuan_id')->constrained('satuan_obat')->comment('ID Satuan');
            $table->integer('jumlah')->comment('Jumlah Obat');
            $table->decimal('harga', 15, 2)->default(0)->comment('Harga Satuan');
            $table->decimal('subtotal', 15, 2)->default(0)->comment('Subtotal');
            $table->decimal('diskon', 15, 2)->default(0)->comment('Diskon');
            $table->decimal('ppn', 15, 2)->default(0)->comment('PPN');
            $table->decimal('total', 15, 2)->default(0)->comment('Total');
            $table->string('no_batch')->nullable()->comment('Nomor Batch');
            $table->date('tanggal_expired')->nullable()->comment('Tanggal Expired');
            $table->foreignId('lokasi_id')->constrained('lokasi_obat')->comment('ID Lokasi Obat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan_details');
    }
};
