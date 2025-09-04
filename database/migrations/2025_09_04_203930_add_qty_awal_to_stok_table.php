<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stok', function (Blueprint $table) {
            $table->integer('qty_awal')->default(0)->after('qty')->comment('Jumlah stok awal');
        });

        // Update existing records to set qty_awal = qty
        DB::statement('UPDATE stok SET qty_awal = qty WHERE qty_awal = 0');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stok', function (Blueprint $table) {
            $table->dropColumn('qty_awal');
        });
    }
};
