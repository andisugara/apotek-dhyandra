<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stok extends Model
{
    use HasFactory;

    protected $table = 'stok';

    protected $fillable = [
        'obat_id',
        'satuan_id',
        'lokasi_id',
        'no_batch',
        'tanggal_expired',
        'qty',
        'pembelian_detail_id'
    ];

    // Cast attributes
    protected $casts = [
        'tanggal_expired' => 'date',
        'qty' => 'integer',
    ];

    // Relationships
    public function obat()
    {
        return $this->belongsTo(Obat::class, 'obat_id');
    }

    public function satuan()
    {
        return $this->belongsTo(SatuanObat::class, 'satuan_id');
    }

    public function lokasi()
    {
        return $this->belongsTo(LokasiObat::class, 'lokasi_id');
    }

    public function obatSatuan()
    {
        return $this->belongsTo(ObatSatuan::class, 'satuan_id', 'satuan_id')
            ->where('obat_id', $this->obat_id);
    }

    // Check if stock is expired
    public function getIsExpiredAttribute()
    {
        return $this->tanggal_expired->isPast();
    }

    // Get days remaining until expiration
    public function getDaysUntilExpiredAttribute()
    {
        return max(0, now()->diffInDays($this->tanggal_expired, false));
    }
}
