<?php

namespace App\Models;

use App\Models\{Operasional, TransaksiDo};
use App\Traits\{LaporanKeuanganTrait, DokumentasiTrait};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\TipeNama;

class LaporanKeuangan extends Model
{
    use SoftDeletes, LaporanKeuanganTrait, DokumentasiTrait;

    protected $table = 'laporan_keuangan';

    protected $fillable = [
        'tanggal',
        'jenis_transaksi',
        'kategori',
        'sub_kategori',
        'nominal',
        'sumber_transaksi',
        'referensi_id',
        'nomor_referensi',
        'pihak_terkait',
        'tipe_pihak',
        'cara_pembayaran',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'nominal' => 'decimal:0',
        'saldo_sebelum' => 'decimal:0',
        'saldo_sesudah' => 'decimal:0',
        'mempengaruhi_kas' => 'boolean',
        'tipe_pihak' => TipeNama::class
    ];

    // Tambahkan konstanta di model LaporanKeuangan
    const KATEGORI_TRANSAKSI = [
        'DO' => 'DO',
        'OPERASIONAL' => 'Operasional',
        'SALDO' => 'Saldo'
    ];

    const SUB_KATEGORI_SALDO = [
        'TAMBAH' => 'Tambah Saldo',
        'KOREKSI' => 'Koreksi Saldo'
    ];

    // Relations

    public function transaksiDo()
    {
        return $this->belongsTo(TransaksiDo::class, 'referensi_id')
            ->where('sumber_transaksi', 'DO');
    }

    public function operasional()
    {
        return $this->belongsTo(Operasional::class, 'referensi_id')
            ->where('sumber_transaksi', 'Operasional');
    }


    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePemasukan($query)
    {
        return $query->where('jenis', 'masuk');
    }

    public function scopePengeluaran($query)
    {
        return $query->where('jenis', 'keluar');
    }

    public function scopeFromDO($query)
    {
        return $query->where('tipe_transaksi', 'transaksi_do');
    }

    public function scopeFromOperasional($query)
    {
        return $query->where('tipe_transaksi', 'operasional');
    }

    public function scopeAffectsCash($query)
    {
        return $query->where('mempengaruhi_kas', true);
    }

    // Di dalam model LaporanKeuangan

    public function getFormattedNominalAttribute()
    {
        return 'Rp ' . number_format($this->nominal, 0, ',', '.');
    }

    public function getBadgeColorAttribute()
    {
        return $this->jenis_transaksi === 'Pemasukan' ? 'success' : 'danger';
    }

    // Scope untuk filtering by date range
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }
}
