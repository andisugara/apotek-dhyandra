<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Obat extends Model
{
    use HasFactory;

    protected $table = 'obat';

    protected $fillable = [
        'kode_obat',
        'nama_obat',
        'pabrik_id',
        'golongan_id',
        'kategori_id',
        'jenis_obat',
        'minimal_stok',
        'deskripsi',
        'indikasi',
        'kandungan',
        'dosis',
        'kemasan',
        'efek_samping',
        'zat_aktif_prekursor',
        'aturan_pakai',
        'is_active'
    ];

    // Status label accessor (is_active field)
    public function getStatusLabelAttribute()
    {
        return $this->is_active == '1' ? 'Aktif' : 'Non Aktif';
    }

    // Relationships
    public function pabrik()
    {
        return $this->belongsTo(Pabrik::class, 'pabrik_id');
    }

    public function golongan()
    {
        return $this->belongsTo(GolonganObat::class, 'golongan_id');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriObat::class, 'kategori_id');
    }

    public function satuans()
    {
        return $this->hasMany(ObatSatuan::class, 'obat_id');
    }

    public function satuan()
    {
        return $this->hasManyThrough(
            SatuanObat::class,
            ObatSatuan::class,
            'obat_id', // Foreign key on ObatSatuan table...
            'id', // Foreign key on SatuanObat table...
            'id', // Local key on Obat table...
            'satuan_id' // Local key on ObatSatuan table...
        )->distinct();
    }

    public function stok()
    {
        return $this->hasMany(Stok::class, 'obat_id');
    }

    public function lokasi()
    {
        return $this->hasManyThrough(
            LokasiObat::class,
            Stok::class,
            'obat_id', // Foreign key on Stok table...
            'id', // Foreign key on LokasiObat table...
            'id', // Local key on Obat table...
            'lokasi_id' // Local key on Stok table...
        )->distinct();
    }
}
