<?php

namespace App\Models;

use App\Models\Obat;
use App\Models\SatuanObat;
use App\Models\LokasiObat;
use App\Models\StockOpname;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpnameDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_opname_id',
        'obat_id',
        'satuan_id',
        'lokasi_id',
        'no_batch',
        'tanggal_expired',
        'stok_sistem',
        'stok_fisik',
        'selisih',
        'tindakan',
        'catatan',
    ];

    protected $casts = [
        'tanggal_expired' => 'date',
        'stok_sistem' => 'integer',
        'stok_fisik' => 'integer',
        'selisih' => 'integer',
    ];

    // Relationships
    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class);
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
}
