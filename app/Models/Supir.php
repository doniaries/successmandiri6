<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supir extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'supir';

    protected $fillable = [
        'nama',
        'alamat',
        'telepon',
        'hutang',
        'riwayat_bayar'
    ];

    protected $casts = [
        'hutang' => 'decimal:0',
    ];

    // Relations
    public function transaksiDo(): HasMany
    {
        return $this->hasMany(TransaksiDo::class);
    }

    public function kendaraan(): HasMany
    {
        return $this->hasMany(Kendaraan::class);
    }

    // Helpers
    public function getFormattedHutangAttribute(): string
    {
        return 'Rp ' . number_format($this->hutang ?? 0, 0, ',', '.');
    }

    // Scopes
    public function scopeHasHutang($query)
    {
        return $query->where('hutang', '>', 0);
    }

    public function scopeWithTransaksiStats($query)
    {
        return $query->withCount('transaksiDo')
            ->withSum('transaksiDo', 'total');
    }
}