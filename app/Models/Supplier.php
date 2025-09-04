<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'nama',
        'alamat',
        'kota',
        'telepone',
        'lead_time',
        'status'
    ];

    public function getStatusLabelAttribute()
    {
        return $this->status == '1' ? 'Aktif' : 'Non Aktif';
    }
}
