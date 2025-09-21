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
        Schema::table('pembelian_detail', function (Blueprint $table) {
            $table->unsignedBigInteger('obat_satuan_id')->nullable()->after('obat_id');
            $table->foreign('obat_satuan_id')->references('id')->on('obat_satuan')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelian_detail', function (Blueprint $table) {
            $table->dropForeign(['obat_satuan_id']);
            $table->dropColumn('obat_satuan_id');
        });
    }
};
