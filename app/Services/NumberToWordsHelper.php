<?php

namespace App\Services;

class NumberToWordsHelper
{
    private static array $ones = [
        0 => 'Zero',
        1 => 'One',
        2 => 'Two',
        3 => 'Three',
        4 => 'Four',
        5 => 'Five',
        6 => 'Six',
        7 => 'Seven',
        8 => 'Eight',
        9 => 'Nine',
        10 => 'Ten',
        11 => 'Eleven',
        12 => 'Twelve',
        13 => 'Thirteen',
        14 => 'Fourteen',
        15 => 'Fifteen',
        16 => 'Sixteen',
        17 => 'Seventeen',
        18 => 'Eighteen',
        19 => 'Nineteen',
    ];

    private static array $tens = [
        2 => 'Twenty',
        3 => 'Thirty',
        4 => 'Forty',
        5 => 'Fifty',
        6 => 'Sixty',
        7 => 'Seventy',
        8 => 'Eighty',
        9 => 'Ninety',
    ];

    public static function spell(float|int|string $number, ?string $currency = null): string
    {
        $amount = (float) $number;
        $whole = (int) floor($amount);
        $fraction = (int) round(($amount - $whole) * 100);

        if ($fraction === 100) {
            $whole += 1;
            $fraction = 0;
        }

        $words = self::convertWhole($whole);

        if ($fraction > 0) {
            $words .= ' and '.str_pad((string) $fraction, 2, '0', STR_PAD_LEFT).'/100';
        } else {
            $words .= ' and 00/100';
        }

        if (! empty($currency)) {
            $words .= ' '.strtoupper($currency);
        }

        return trim($words);
    }

    private static function convertWhole(int $number): string
    {
        if ($number === 0) {
            return self::$ones[0];
        }

        if ($number < 0) {
            return 'Negative '.self::convertWhole(abs($number));
        }

        $units = [
            1000000000 => 'Billion',
            1000000 => 'Million',
            1000 => 'Thousand',
            100 => 'Hundred',
        ];

        $parts = [];

        foreach ($units as $unit => $name) {
            if ($number >= $unit) {
                $count = (int) floor($number / $unit);
                $number %= $unit;
                $parts[] = self::convertWhole($count).' '.$name;
            }
        }

        if ($number > 0) {
            if ($number < 20) {
                $parts[] = self::$ones[$number];
            } else {
                $ten = (int) floor($number / 10);
                $unit = $number % 10;
                $parts[] = self::$tens[$ten].($unit > 0 ? ' '.self::$ones[$unit] : '');
            }
        }

        return implode(' ', $parts);
    }
}
