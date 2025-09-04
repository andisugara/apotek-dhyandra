<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pasien extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pasien';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'nama',
        'jenis_kelamin',
        'tanggal_lahir',
        'alamat',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_lahir' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Generate a unique patient code.
     *
     * @return string
     */
    public static function generateCode()
    {
        $prefix = 'P';
        $date = date('Ymd');
        $lastPatient = self::where('code', 'like', "{$prefix}{$date}%")->orderBy('code', 'desc')->first();

        $sequence = '001';
        if ($lastPatient) {
            $lastCode = substr($lastPatient->code, -3);
            $sequence = str_pad((int)$lastCode + 1, 3, '0', STR_PAD_LEFT);
        }

        return "{$prefix}{$date}{$sequence}";
    }

    /**
     * Get the formatted status label.
     *
     * @return string
     */
    public function getStatusLabelAttribute()
    {
        return $this->is_active ? 'Aktif' : 'Non-Aktif';
    }
}
