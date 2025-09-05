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
        Schema::create('pembelian_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembelian_id')->constrained('pembelian')->onDelete('cascade');
            $table->foreignId('obat_id')->constrained('obat');
            $table->foreignId('satuan_id')->constrained('satuan_obat');
            $table->integer('jumlah')->default(0);
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('diskon_persen', 5, 2)->default(0);
            $table->decimal('diskon_nominal', 15, 2)->default(0);
            $table->decimal('hpp_per_unit', 15, 2)->default(0)->comment('Harga Pokok Pembelian Per Unit');
            $table->decimal('hna_ppn_per_unit', 15, 2)->default(0)->comment('Harga Neto Apotek + PPN Per Unit');
            $table->decimal('margin_jual_persen', 5, 2)->default(0);
            $table->decimal('harga_jual_per_unit', 15, 2)->default(0);
            $table->string('no_batch');
            $table->date('tanggal_expired');
            $table->decimal('total', 15, 2)->default(0)->comment('Subtotal - Diskon Nominal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelian_detail');
    }
};
