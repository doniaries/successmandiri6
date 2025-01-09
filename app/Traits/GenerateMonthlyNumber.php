<?php

namespace App\Traits;

trait GenerateMonthlyNumber
{
    public static function generateMonthlyNumber()
    {
        $today = now();
        $day = $today->format('d');
        $month = $today->format('m');
        $year = $today->format('Y');

        // Get all numbers for today (both active and trashed)
        $lastNumbers = static::whereDate('tanggal', $today->toDateString())
            ->withTrashed()
            ->pluck('nomor')
            ->toArray();

        $maxSequence = 0;

        // Extract and find the highest sequence number
        foreach ($lastNumbers as $number) {
            if (preg_match('/DO-\d{8}-(\d+)/', $number, $matches)) {
                $sequence = (int)$matches[1];
                $maxSequence = max($maxSequence, $sequence);
            }
        }

        // Increment the highest sequence number
        $newNumber = $maxSequence + 1;

        // Format: DO-YYYYMMDD-XXXX
        return sprintf(
            'DO-%s%s%s-%s',
            $year,
            $month,
            $day,
            str_pad($newNumber, 4, '0', STR_PAD_LEFT)
        );
    }
}
