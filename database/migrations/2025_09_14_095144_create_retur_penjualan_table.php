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
        Schema::create('retur_penjualans', function (Blueprint $table) {
            $table->id();
            $table->string('no_retur')->unique();
            $table->date('tanggal_retur');
            $table->foreignId('penjualan_id')->constrained('penjualans')->onDelete('restrict');
            $table->text('alasan')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('diskon_total', 15, 2)->default(0);
            $table->decimal('ppn_total', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_penjualans');
    }
};
