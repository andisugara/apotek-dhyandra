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
        Schema::table('obat_satuan', function (Blueprint $table) {
            $table->decimal('profit_persen', 8, 2)->default(10)->after('diskon_persen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('obat_satuan', function (Blueprint $table) {
            $table->dropColumn('profit_persen');
        });
    }
};
