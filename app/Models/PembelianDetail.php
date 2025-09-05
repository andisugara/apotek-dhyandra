<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianDetail extends Model
{
    use HasFactory;

    protected $table = 'pembelian_detail';

    protected $fillable = [
        'pembelian_id',
        'obat_id',
        'satuan_id',
        'jumlah',
        'harga_beli',
        'subtotal',
        'diskon_persen',
        'diskon_nominal',
        'hpp_per_unit',
        'hna_ppn_per_unit',
        'margin_jual_persen',
        'harga_jual_per_unit',
        'no_batch',
        'tanggal_expired',
        'total'
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'harga_beli' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'diskon_persen' => 'decimal:2',
        'diskon_nominal' => 'decimal:2',
        'hpp_per_unit' => 'decimal:2',
        'hna_ppn_per_unit' => 'decimal:2',
        'margin_jual_persen' => 'decimal:2',
        'harga_jual_per_unit' => 'decimal:2',
        'tanggal_expired' => 'date',
        'total' => 'decimal:2',
    ];

    // Relationships
    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class);
    }

    public function obat()
    {
        return $this->belongsTo(Obat::class);
    }

    public function satuan()
    {
        return $this->belongsTo(SatuanObat::class, 'satuan_id');
    }

    public function stok()
    {
        return $this->hasMany(Stok::class, 'pembelian_detail_id');
    }
    
    public function returDetails()
    {
        return $this->hasMany(ReturPembelianDetail::class, 'pembelian_detail_id');
    }

    // Accessor for formatted values
    public function getFormattedHargaBeliAttribute()
    {
        return number_format($this->harga_beli, 0, ',', '.');
    }

    public function getFormattedSubtotalAttribute()
    {
        return number_format($this->subtotal, 0, ',', '.');
    }

    public function getFormattedDiskonNominalAttribute()
    {
        return number_format($this->diskon_nominal, 0, ',', '.');
    }

    public function getFormattedHppPerUnitAttribute()
    {
        return number_format($this->hpp_per_unit, 0, ',', '.');
    }

    public function getFormattedHnaPpnPerUnitAttribute()
    {
        return number_format($this->hna_ppn_per_unit, 0, ',', '.');
    }

    public function getFormattedHargaJualPerUnitAttribute()
    {
        return number_format($this->harga_jual_per_unit, 0, ',', '.');
    }

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total, 0, ',', '.');
    }
}
