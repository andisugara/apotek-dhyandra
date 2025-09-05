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
            $table->string('no_batch', 50);
            $table->date('tanggal_expired');
            $table->integer('stok_sistem');
            $table->integer('stok_fisik');
            $table->integer('selisih');
            $table->string('tindakan')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('stock_opname_id')->references('id')->on('stock_opnames')->onDelete('cascade');
            $table->foreign('obat_id')->references('id')->on('obat')->onDelete('restrict');
            $table->foreign('satuan_id')->references('id')->on('obat_satuan')->onDelete('restrict');
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
