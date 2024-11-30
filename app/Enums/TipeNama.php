<?php

namespace App\Enums;

enum TipeNama: string
{
    case PENJUAL = 'penjual'; // lowercase sesuai database
    case PEKERJA = 'pekerja';
    case USER = 'user';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENJUAL => '🏢 Penjual',
            self::PEKERJA => '👷 Pekerja',
            self::USER => '👨‍💼 Karyawan',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
