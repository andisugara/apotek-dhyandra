<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiAkun extends Model
{
    use HasFactory;

    protected $table = 'transaksi_akun';

    protected $fillable = [
        'akun_id',
        'tanggal',
        'kode_referensi',
        'tipe_referensi',
        'referensi_id',
        'deskripsi',
        'debit',
        'kredit',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'debit' => 'decimal:2',
        'kredit' => 'decimal:2',
    ];

    // Relationships
    public function akun()
    {
        return $this->belongsTo(Akun::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope for referensi pembelian
    public function scopePembelian($query)
    {
        return $query->where('tipe_referensi', 'PEMBELIAN');
    }

    // Scope for referensi penjualan
    public function scopePenjualan($query)
    {
        return $query->where('tipe_referensi', 'PENJUALAN');
    }

    // Scope for referensi pengeluaran
    public function scopePengeluaran($query)
    {
        return $query->where('tipe_referensi', 'PENGELUARAN');
    }

    // Accessor for formatted values
    public function getFormattedDebitAttribute()
    {
        return number_format($this->debit, 0, ',', '.');
    }

    public function getFormattedKreditAttribute()
    {
        return number_format($this->kredit, 0, ',', '.');
    }
}
