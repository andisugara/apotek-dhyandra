<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturPenjualan extends Model
{
    use HasFactory;

    protected $table = 'retur_penjualans';

    protected $fillable = [
        'no_retur',
        'tanggal_retur',
        'penjualan_id',
        'alasan',
        'subtotal',
        'diskon_total',
        'ppn_total',
        'grand_total',
        'user_id',
    ];

    protected $casts = [
        'tanggal_retur' => 'date',
        'subtotal' => 'decimal:2',
        'diskon_total' => 'decimal:2',
        'ppn_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    // Relationships
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(ReturPenjualanDetail::class, 'retur_penjualan_id');
    }

    public function transaksiAkun()
    {
        return $this->hasMany(TransaksiAkun::class, 'referensi_id')
            ->where('tipe_referensi', 'RETUR_PENJUALAN');
    }

    // Accessor for formatted values
    public function getFormattedSubtotalAttribute()
    {
        return number_format($this->subtotal, 0, ',', '.');
    }

    public function getFormattedDiskonTotalAttribute()
    {
        return number_format($this->diskon_total, 0, ',', '.');
    }

    public function getFormattedPpnTotalAttribute()
    {
        return number_format($this->ppn_total, 0, ',', '.');
    }

    public function getFormattedGrandTotalAttribute()
    {
        return number_format($this->grand_total, 0, ',', '.');
    }
}
