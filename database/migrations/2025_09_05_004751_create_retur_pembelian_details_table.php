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
        Schema::create('retur_pembelian_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retur_pembelian_id')->constrained('retur_pembelians')->onDelete('cascade');
            $table->foreignId('pembelian_detail_id')->constrained('pembelian_detail')->onDelete('restrict');
            $table->foreignId('obat_id')->constrained('obat')->onDelete('restrict');
            $table->foreignId('satuan_id')->constrained('satuan_obat')->onDelete('restrict');
            $table->integer('jumlah')->default(0);
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('ppn', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('no_batch')->nullable();
            $table->date('tanggal_expired');
            $table->foreignId('lokasi_id')->constrained('lokasi_obat')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_pembelian_details');
    }
};
