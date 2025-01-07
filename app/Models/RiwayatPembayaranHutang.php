<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatPembayaranHutang extends Model
{
    use SoftDeletes;

    protected $table = 'riwayat_pembayaran_hutangs';

    protected $fillable = [
        'tanggal',
        'nominal',
        'tipe',
        'penjual_id',
        'pekerja_id',
        'supir_id',
        'operasional_id',
        'keterangan'
    ];
    // const TIPE_PINJAMAN = 'pinjaman';
    protected $casts = [
        'tanggal' => 'datetime',
        'nominal' => 'decimal:0'
    ];

    // Relations
    public function penjual(): BelongsTo
    {
        return $this->belongsTo(Penjual::class);
    }

    public function supir()
    {
        return $this->belongsTo(Supir::class, 'pekerja_id')
            ->where('tipe', 'supir');
    }

    public function pekerja(): BelongsTo
    {
        return $this->belongsTo(Pekerja::class);
    }

    public function operasional(): BelongsTo
    {
        return $this->belongsTo(Operasional::class);
    }

    // Helper method untuk format nominal
    public function getFormattedNominalAttribute(): string
    {
        return 'Rp ' . number_format($this->nominal, 0, ',', '.');
    }

    // Helper untuk ambil nama pembayar
    public function getNamaPembayarAttribute(): string
    {
        return match ($this->tipe) {
            'penjual' => $this->penjual?->nama ?? '-',
            'pekerja' => $this->pekerja?->nama ?? '-',
            default => '-'
        };
    }
}