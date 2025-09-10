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
        Schema::create('stock_opname_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_opname_id');
            $table->unsignedBigInteger('obat_id');
            $table->unsignedBigInteger('satuan_id');
            $table->unsignedBigInteger('lokasi_id');
            // No longer needed: no_batch and tanggal_expired
            $table->decimal('stok_sistem', 10, 2);
            $table->decimal('stok_fisik', 10, 2);
            $table->decimal('selisih', 10, 2);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('stock_opname_id')->references('id')->on('stock_opnames')->onDelete('cascade');
            $table->foreign('obat_id')->references('id')->on('obat')->onDelete('restrict');
            $table->foreign('satuan_id')->references('id')->on('satuan_obat')->onDelete('restrict');
            $table->foreign('lokasi_id')->references('id')->on('lokasi_obat')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_details');
    }
};
