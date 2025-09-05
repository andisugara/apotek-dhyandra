<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'tanggal',
        'keterangan',
        'status',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(StockOpnameDetail::class);
    }

    // Custom accessor for status badge
    public function getStatusBadgeAttribute()
    {
        return $this->status === 'selesai'
            ? '<span class="badge badge-success">Selesai</span>'
            : '<span class="badge badge-warning">Draft</span>';
    }
}
