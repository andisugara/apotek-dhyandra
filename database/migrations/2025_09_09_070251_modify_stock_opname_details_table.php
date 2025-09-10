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
        Schema::table('stock_opname_details', function (Blueprint $table) {
            // Modify existing columns
            $table->string('no_batch', 50)->nullable();
            $table->date('tanggal_expired')->nullable();
            $table->string('tindakan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_opname_details', function (Blueprint $table) {
            $table->dropColumn(['no_batch', 'tanggal_expired', 'tindakan']);
        });
    }
};
