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
            // Tambahkan kolom baru yang diperlukan
            if (!Schema::hasColumn('stock_opname_details', 'stok_sistem')) {
                $table->decimal('stok_sistem', 15, 2)->default(0)->after('lokasi_id');
            }

            if (!Schema::hasColumn('stock_opname_details', 'stok_fisik')) {
                $table->decimal('stok_fisik', 15, 2)->default(0)->after('stok_sistem');
            }

            if (!Schema::hasColumn('stock_opname_details', 'selisih')) {
                $table->decimal('selisih', 15, 2)->default(0)->after('stok_fisik');
            }

            if (!Schema::hasColumn('stock_opname_details', 'keterangan')) {
                $table->string('keterangan')->nullable()->after('selisih');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_opname_details', function (Blueprint $table) {
            $table->dropColumn([
                'stok_sistem',
                'stok_fisik',
                'selisih',
                'keterangan'
            ]);
        });
    }
};
