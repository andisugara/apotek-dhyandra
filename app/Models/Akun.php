<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Akun extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'akun';

    protected $fillable = [
        'kode',
        'nama',
        'status',
    ];

    public function getStatusLabelAttribute()
    {
        return $this->status == '1' ? 'Aktif' : 'Non Aktif';
    }
}
