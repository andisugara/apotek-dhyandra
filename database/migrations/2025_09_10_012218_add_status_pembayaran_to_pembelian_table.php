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
        Schema::table('pembelian', function (Blueprint $table) {
            $table->enum('status_pembayaran', ['BELUM', 'SEBAGIAN', 'LUNAS'])
                ->default('BELUM')
                ->after('jenis')
                ->comment('Status pembayaran untuk transaksi HUTANG');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelian', function (Blueprint $table) {
            $table->dropColumn('status_pembayaran');
        });
    }
};
