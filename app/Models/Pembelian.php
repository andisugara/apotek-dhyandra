<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    use HasFactory;

    protected $table = 'pembelian';

    protected $fillable = [
        'no_po',
        'no_faktur',
        'tanggal_faktur',
        'supplier_id',
        'jenis',
        'akun_kas_id',
        'tanggal_jatuh_tempo',
        'subtotal',
        'diskon_total',
        'ppn_total',
        'grand_total',
        'user_id',
    ];

    protected $casts = [
        'tanggal_faktur' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'subtotal' => 'decimal:2',
        'diskon_total' => 'decimal:2',
        'ppn_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function akunKas()
    {
        return $this->belongsTo(Akun::class, 'akun_kas_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(PembelianDetail::class);
    }

    public function transaksiAkun()
    {
        return $this->hasMany(TransaksiAkun::class, 'referensi_id')
            ->where('tipe_referensi', 'PEMBELIAN');
    }

    public function returPembelians()
    {
        return $this->hasMany(ReturPembelian::class);
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

    public function getStatusJatuhTempoAttribute()
    {
        if ($this->jenis !== 'HUTANG' || !$this->tanggal_jatuh_tempo) {
            return null;
        }

        $today = now()->startOfDay();
        $jatuhTempo = $this->tanggal_jatuh_tempo->startOfDay();

        if ($today->gt($jatuhTempo)) {
            return 'TERLAMBAT';
        } elseif ($today->eq($jatuhTempo)) {
            return 'JATUH TEMPO HARI INI';
        } else {
            $diff = $today->diffInDays($jatuhTempo);
            return $diff <= 7 ? 'MENDEKATI JATUH TEMPO' : 'BELUM JATUH TEMPO';
        }
    }
}
