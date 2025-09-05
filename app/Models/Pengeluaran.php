<?php

namespace App\Models;

use App\Models\TransaksiAkun;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pengeluaran';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'tanggal',
        'jumlah',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'float',
    ];

    /**
     * Get the formatted amount with thousand separator.
     *
     * @return string
     */
    public function getFormattedJumlahAttribute()
    {
        return number_format($this->jumlah, 0, ',', '.');
    }

    /**
     * Check if the expense can be deleted.
     * Only expenses created within 1 month can be deleted.
     *
     * @return bool
     */
    public function canBeDeleted()
    {
        $oneMonthAgo = Carbon::now()->subMonth();
        return $this->created_at?->gt($oneMonthAgo) ?? false;
    }

    /**
     * Get the user who created this expense.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the accounting transactions related to this expense.
     */
    public function transaksiAkun()
    {
        return $this->hasMany(TransaksiAkun::class, 'referensi_id')
            ->where('tipe_referensi', 'PENGELUARAN');
    }
}
