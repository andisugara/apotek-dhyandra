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

    public function stok()
    {
        return $this->hasMany(Stok::class, 'obat_id');
    }
}
