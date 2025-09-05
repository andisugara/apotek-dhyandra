<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturPembelianDetail extends Model
{
    use HasFactory;

    protected $table = 'retur_pembelian_details';

    protected $fillable = [
        'retur_pembelian_id',
        'pembelian_detail_id',
        'obat_id',
        'satuan_id',
        'jumlah',
        'harga_beli',
        'subtotal',
        'ppn',
        'total',
        'no_batch',
        'tanggal_expired',
        'lokasi_id',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'harga_beli' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'ppn' => 'decimal:2',
        'total' => 'decimal:2',
        'tanggal_expired' => 'date',
    ];

    // Relationships
    public function returPembelian()
    {
        return $this->belongsTo(ReturPembelian::class, 'retur_pembelian_id');
    }

    public function pembelianDetail()
    {
        return $this->belongsTo(PembelianDetail::class, 'pembelian_detail_id');
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

    // Accessor for formatted values
    public function getFormattedHargaBeliAttribute()
    {
        return number_format($this->harga_beli, 0, ',', '.');
    }

    public function getFormattedSubtotalAttribute()
    {
        return number_format($this->subtotal, 0, ',', '.');
    }

    public function getFormattedPpnAttribute()
    {
        return number_format($this->ppn, 0, ',', '.');
    }

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total, 0, ',', '.');
    }
}
