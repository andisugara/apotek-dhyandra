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
        Schema::create('obat', function (Blueprint $table) {
            $table->id();
            $table->string('kode_obat', 20)->unique();
            $table->string('nama_obat');
            $table->unsignedBigInteger('pabrik_id');
            $table->unsignedBigInteger('golongan_id');
            $table->unsignedBigInteger('kategori_id');
            $table->string('jenis_obat');
            $table->integer('minimal_stok')->default(0);
            $table->text('deskripsi')->nullable();
            $table->text('indikasi')->nullable();
            $table->text('kandungan')->nullable();
            $table->text('dosis')->nullable();
            $table->string('kemasan')->nullable();
            $table->text('efek_samping')->nullable();
            $table->text('zat_aktif_prekursor')->nullable();
            $table->text('aturan_pakai')->nullable();
            $table->enum('is_active', ['0', '1'])->default('1');
            $table->timestamps();

            $table->foreign('pabrik_id')->references('id')->on('pabrik');
            $table->foreign('golongan_id')->references('id')->on('golongan_obat');
            $table->foreign('kategori_id')->references('id')->on('kategori_obat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obat');
    }
};
