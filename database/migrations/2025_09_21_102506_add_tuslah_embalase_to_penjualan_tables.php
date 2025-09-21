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
        // Add tuslah and embalase to the penjualan table
        Schema::table('penjualans', function (Blueprint $table) {
            $table->decimal('tuslah_total', 12, 2)->default(0)->after('ppn_total');
            $table->decimal('embalase_total', 12, 2)->default(0)->after('tuslah_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove columns from penjualan table
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropColumn('tuslah_total');
            $table->dropColumn('embalase_total');
        });
    }
};
