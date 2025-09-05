<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_faktur',
        'tanggal_penjualan',
        'pasien_id',
        'jenis',
        'subtotal',
        'diskon_total',
        'ppn_total',
        'grand_total',
        'bayar',
        'kembalian',
        'user_id',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_penjualan' => 'date',
        'subtotal' => 'decimal:2',
        'diskon_total' => 'decimal:2',
        'ppn_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'bayar' => 'decimal:2',
        'kembalian' => 'decimal:2',
    ];

    // Relationships
    public function pasien()
    {
        return $this->belongsTo(Pasien::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(PenjualanDetail::class);
    }

    public function transaksiAkun()
    {
        return $this->hasMany(TransaksiAkun::class, 'referensi_id')
            ->where('tipe_referensi', 'PENJUALAN');
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

    public function getFormattedBayarAttribute()
    {
        return number_format($this->bayar, 0, ',', '.');
    }

    public function getFormattedKembalianAttribute()
    {
        return number_format($this->kembalian, 0, ',', '.');
    }

    public function getJenisDisplayAttribute()
    {
        return $this->jenis === 'TUNAI' ? 'Tunai' : 'Non Tunai';
    }
}
