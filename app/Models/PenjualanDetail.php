<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'penjualan_id',
        'obat_id',
        'satuan_id',
        'jumlah',
        'harga_beli',
        'harga',
        'subtotal',
        'diskon',
        'ppn',
        'tuslah',
        'embalase',
        'total',
        'no_batch',
        'tanggal_expired',
        'lokasi_id',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'harga_beli' => 'decimal:2',
        'harga' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'diskon' => 'decimal:2',
        'ppn' => 'decimal:2',
        'tuslah' => 'decimal:2',
        'embalase' => 'decimal:2',
        'total' => 'decimal:2',
        'tanggal_expired' => 'date',
    ];

    // Relationships
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function obat()
    {
        return $this->belongsTo(Obat::class);
    }

    public function satuan()
    {
        return $this->belongsTo(SatuanObat::class, 'satuan_id');
    }

    public function lokasi()
    {
        return $this->belongsTo(LokasiObat::class, 'lokasi_id');
    }

    public function stok()
    {
        return $this->belongsTo(Stok::class, 'no_batch', 'no_batch');
    }

    public function returDetails()
    {
        return $this->hasMany(ReturPenjualanDetail::class, 'penjualan_detail_id');
    }

    // Accessor for formatted values
    public function getFormattedHargaBeliAttribute()
    {
        return number_format($this->harga_beli, 0, ',', '.');
    }

    public function getFormattedHargaAttribute()
    {
        return number_format($this->harga, 0, ',', '.');
    }

    public function getFormattedSubtotalAttribute()
    {
        return number_format($this->subtotal, 0, ',', '.');
    }

    public function getFormattedDiskonAttribute()
    {
        return number_format($this->diskon, 0, ',', '.');
    }

    public function getFormattedPpnAttribute()
    {
        return number_format($this->ppn, 0, ',', '.');
    }

    public function getFormattedTuslahAttribute()
    {
        return number_format($this->tuslah, 0, ',', '.');
    }

    public function getFormattedEmbalaseAttribute()
    {
        return number_format($this->embalase, 0, ',', '.');
    }

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total, 0, ',', '.');
    }
}
