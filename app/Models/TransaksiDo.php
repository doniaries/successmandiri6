<?php

namespace App\Models;

use App\Models\Supir;
use App\Models\Penjual;
use App\Models\Kendaraan;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\DokumentasiTrait;
use App\Traits\LaporanKeuanganTrait;
use App\Traits\GenerateMonthlyNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\{DB, Log};
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsTo};
// use BaconQrCode\Renderer\ImageRenderer;
// use BaconQrCode\Renderer\Image\SvgImageBackEnd;
// use BaconQrCode\Renderer\RendererStyle\RendererStyle;
// use BaconQrCode\Writer;

class TransaksiDo extends Model
{
    use HasFactory, SoftDeletes, LaporanKeuanganTrait, DokumentasiTrait, GenerateMonthlyNumber;

    protected $table = 'transaksi_do';
    protected $with = ['penjual']; // Default eager loading


    protected $fillable = [
        'id',
        'nomor',
        'tanggal',
        'penjual_id',
        'supir_id',
        'kendaraan_id',
        'tonase',
        'harga_satuan',
        'sub_total',
        'upah_bongkar',
        'biaya_lain',
        'keterangan_biaya_lain',
        'hutang_awal',          // Updated
        'pembayaran_hutang',    // Updated
        'sisa_hutang_penjual',  // Updated
        'cara_bayar',
        'sisa_bayar',
        // 'file_do',
        // 'status_bayar',
        // 'catatan',
    ];

    // MASIH DIPAKAI - Tidak berubah
    protected $dates = [
        'tanggal',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // MASIH DIPAKAI - Update casting untuk kolom baru
    protected $casts = [
        'tanggal' => 'datetime',
        'tonase' => 'decimal:0',
        'harga_satuan' => 'decimal:0',
        'sub_total' => 'decimal:0',
        'upah_bongkar' => 'integer',
        'biaya_lain' => 'integer',
        'hutang_awal' => 'decimal:0',         // Updated
        'pembayaran_hutang' => 'decimal:0',   // Updated
        'sisa_hutang_penjual' => 'decimal:0', // Updated
        'sisa_bayar' => 'decimal:0',
        // 'status_bayar' => 'string',
    ];

    protected $attributes = [
        'sub_total' => 0,
        'upah_bongkar' => 0,
        'biaya_lain' => 0,
        'hutang_awal' => 0,           // Updated
        'pembayaran_hutang' => 0,     // Updated
        'sisa_hutang_penjual' => 0,   // Updated
        'sisa_bayar' => 0,
        // 'status_bayar' => 'Belum Lunas',
    ];

    const CARA_BAYAR = [
        'Tunai' => 'Tunai',
        'Transfer' => 'Transfer',
        'cair di luar' => 'cair di luar',
        'belum bayar' => 'Belum Bayar',
    ];

    // Panggil method dari trait
    public function handlePembayaran()
    {
        $this->validateSaldoTunai($this->sisa_bayar);
        $this->handlePembayaranHutang($this->pembayaran_hutang);
        $this->updateSaldoPerusahaan();
    }

    // Di Model TransaksiDo
    // public function handleFileUpload($file)
    // {
    //     if ($file) {
    //         // Simpan file dengan nama unik
    //         $fileName = Str::slug($this->nomor) . '-' . time() . '.' . $file->getClientOriginalExtension();
    //         $path = $file->storeAs('do-files', $fileName, 'public');

    //         $this->update([
    //             'file_do' => $path
    //         ]);

    //         // Catat di log
    //         Log::info('File DO uploaded:', [
    //             'nomor_do' => $this->nomor,
    //             'file_path' => $path
    //         ]);

    //         return $path;
    //     }
    //     return null;
    // }

    public function penjual(): BelongsTo
    {
        return $this->belongsTo(Penjual::class);
    }

    // Tambahkan relation ke laporan keuangan
    public function laporanKeuangan()
    {
        return $this->hasMany(LaporanKeuangan::class, 'referensi_id')
            ->where('sumber_transaksi', 'DO');
    }

    public function operasional()
    {
        return $this->hasMany(Operasional::class, 'transaksi_do_id');
    }

    // Accessor untuk hutang penjual
    public function getHutangPenjualAttribute(): int
    {
        return $this->penjual ? $this->penjual->hutang : 0;
    }



    //cetak pdf di transaksi DO
    public function generatePdf()
    {
        try {
            $perusahaan = Perusahaan::first();

            $pdf = PDF::loadView('pdf.transaksi-do', [
                'transaksi' => $this,
                'perusahaan' => $perusahaan,
                // 'qrCode' => $qrHtml
            ]);

            $pdf->setPaper('F4', 'portrait');

            return $pdf->stream("DO-{$this->nomor}.pdf");
        } catch (\Exception $e) {
            Log::error('Error generating PDF:', [
                'error' => $e->getMessage(),
                'transaksi' => $this->toArray()
            ]);
            throw $e;
        }
    }

    public function supir(): BelongsTo
    {
        return $this->belongsTo(Supir::class);
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class);
    }

    public function getTotalAttribute()
    {
        return $this->sub_total;
    }

    public function scopeWithTotals($query)
    {
        return $query->select('*')
            ->addSelect(DB::raw('sub_total as total_amount'));  // Use sub_total consistently
    }
}