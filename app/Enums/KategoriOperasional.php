<?php
// File: app/Enums/KategoriOperasional.php

namespace App\Enums;

enum KategoriOperasional: string
{
    case PINJAMAN = 'pinjaman';
    case BAYAR_HUTANG = 'bayar_hutang';
    case GAJI = 'gaji';
    case UANG_JALAN = 'uang_jalan';
    case BAHAN_BAKAR = 'bahan_bakar';
    case PERAWATAN = 'perawatan';
    case LAIN_LAIN = 'lain_lain';

    public function label(): string
    {
        return match ($this) {
            self::PINJAMAN => 'Pinjaman',
            self::BAYAR_HUTANG => 'Bayar Hutang',
            self::GAJI => 'Gaji',
            self::UANG_JALAN => 'Uang Jalan',
            self::BAHAN_BAKAR => 'Bahan Bakar',
            self::PERAWATAN => 'Perawatan',
            self::LAIN_LAIN => 'Lain-lain',
        };
    }

    // Tambahkan method helper untuk cek jenis operasional
    public function getJenisOperasional(): string
    {
        return match ($this) {
            self::BAYAR_HUTANG => 'pemasukan',
            self::PINJAMAN => 'pengeluaran',
            default => 'pengeluaran'
        };
    }

    // Helper untuk cek apakah kategori terkait hutang
    public function isHutangRelated(): bool
    {
        return in_array($this, [self::PINJAMAN, self::BAYAR_HUTANG]);
    }

    // Helper untuk mendapatkan semua kategori pemasukan
    public static function forPemasukan(): array
    {
        return [
            self::BAYAR_HUTANG->value => self::BAYAR_HUTANG->label(),
        ];
    }

    // Helper untuk mendapatkan semua kategori pengeluaran
    public static function forPengeluaran(): array
    {
        return [
            self::PINJAMAN->value => self::PINJAMAN->label(),
            self::GAJI->value => self::GAJI->label(),
            self::UANG_JALAN->value => self::UANG_JALAN->label(),
            self::BAHAN_BAKAR->value => self::BAHAN_BAKAR->label(),
            self::PERAWATAN->value => self::PERAWATAN->label(),
            self::LAIN_LAIN->value => self::LAIN_LAIN->label(),
        ];
    }
}
