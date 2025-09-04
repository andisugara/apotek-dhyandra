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
        Schema::create('stok', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('obat_id');
            $table->unsignedBigInteger('satuan_id');
            $table->unsignedBigInteger('lokasi_id');
            $table->string('no_batch');
            $table->date('tanggal_expired');
            $table->integer('qty')->default(0);
            $table->unsignedBigInteger('pembelian_detail_id')->nullable();
            $table->timestamps();

            $table->foreign('obat_id')->references('id')->on('obat');
            $table->foreign('satuan_id')->references('id')->on('satuan_obat');
            $table->foreign('lokasi_id')->references('id')->on('lokasi_obat');

            // We're not creating a foreign key for pembelian_detail_id since it's optional
            // and we don't know the structure of that table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok');
    }
};
