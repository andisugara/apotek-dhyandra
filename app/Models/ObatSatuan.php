<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ObatSatuan extends Model
{
    use HasFactory;

    protected $table = 'obat_satuan';

    protected $fillable = [
        'obat_id',
        'satuan_id',
        'harga_beli',
        'diskon_persen',
        'profit_persen',
        'harga_jual'
    ];

    // Cast attributes
    protected $casts = [
        'harga_beli' => 'float',
        'diskon_persen' => 'float',
        'profit_persen' => 'float',
        'harga_jual' => 'float',
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

    public function stok()
    {
        return $this->hasMany(Stok::class, 'satuan_id', 'satuan_id')
            ->where('obat_id', $this->obat_id);
    }

    // Get harga beli after discount
    public function getHargaBeliAfterDiskonAttribute()
    {
        if ($this->diskon_persen > 0) {
            return $this->harga_beli - ($this->harga_beli * $this->diskon_persen / 100);
        }
        return $this->harga_beli;
    }
}
