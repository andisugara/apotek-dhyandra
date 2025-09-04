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
        Schema::create('obat_satuan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('obat_id');
            $table->unsignedBigInteger('satuan_id');
            $table->decimal('harga_beli', 12, 2)->default(0);
            $table->decimal('diskon_persen', 5, 2)->default(0);
            $table->decimal('harga_jual', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('obat_id')->references('id')->on('obat');
            $table->foreign('satuan_id')->references('id')->on('satuan_obat');

            // Ensure unique combination of obat_id and satuan_id
            $table->unique(['obat_id', 'satuan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obat_satuan');
    }
};
