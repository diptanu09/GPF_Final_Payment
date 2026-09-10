<?php

namespace App\Services\Format;

class IndianCurrencyFormatter
{
    private static array $units = [
        0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
        6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
        11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen',
        15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
        19 => 'Nineteen'
    ];

    private static array $tens = [
        2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
        6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
    ];

    /**
     * Convert an amount into Indian English Words (e.g. "One Lakh Twenty Three Thousand Four Hundred Fifty Six Rupees Only").
     */
    public static function toWords(float|int|string $amount): string
    {
        $amount = (float) $amount;
        if ($amount <= 0) {
            return 'Zero Rupees Only';
        }

        $rupees = (int) floor($amount);
        $paise = (int) round(($amount - $rupees) * 100);

        $rupeesWords = self::convertNumberToIndianWords($rupees);
        $words = $rupeesWords . ' Rupees';

        if ($paise > 0) {
            $paiseWords = self::convertNumberToIndianWords($paise);
            $words .= ' and ' . $paiseWords . ' Paise';
        }

        return trim($words) . ' Only';
    }

    /**
     * Convert an integer number to Indian English words.
     */
    private static function convertNumberToIndianWords(int $num): string
    {
        if ($num === 0) {
            return '';
        }

        if ($num < 20) {
            return self::$units[$num];
        }

        if ($num < 100) {
            $ten = (int) ($num / 10);
            $unit = $num % 10;
            return self::$tens[$ten] . ($unit > 0 ? ' ' . self::$units[$unit] : '');
        }

        if ($num < 1000) {
            $hundred = (int) ($num / 100);
            $remainder = $num % 100;
            return self::$units[$hundred] . ' Hundred' . ($remainder > 0 ? ' ' . self::convertNumberToIndianWords($remainder) : '');
        }

        if ($num < 100000) {
            $thousand = (int) ($num / 1000);
            $remainder = $num % 1000;
            return self::convertNumberToIndianWords($thousand) . ' Thousand' . ($remainder > 0 ? ' ' . self::convertNumberToIndianWords($remainder) : '');
        }

        if ($num < 10000000) {
            $lakh = (int) ($num / 100000);
            $remainder = $num % 100000;
            return self::convertNumberToIndianWords($lakh) . ' Lakh' . ($remainder > 0 ? ' ' . self::convertNumberToIndianWords($remainder) : '');
        }

        $crore = (int) ($num / 10000000);
        $remainder = $num % 10000000;
        return self::convertNumberToIndianWords($crore) . ' Crore' . ($remainder > 0 ? ' ' . self::convertNumberToIndianWords($remainder) : '');
    }

    /**
     * Format an amount into standard Indian currency format (e.g. ₹ 12,34,567.00).
     */
    public static function formatInr(float|int|string $amount, bool $showSymbol = true): string
    {
        $amount = (float) $amount;
        $isNegative = $amount < 0;
        $amount = abs($amount);

        $parts = explode('.', number_format($amount, 2, '.', ''));
        $intPart = $parts[0];
        $decPart = $parts[1];

        $len = strlen($intPart);
        if ($len > 3) {
            $last3 = substr($intPart, -3);
            $rest = substr($intPart, 0, $len - 3);
            $restFormatted = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            $formattedInt = $restFormatted . ',' . $last3;
        } else {
            $formattedInt = $intPart;
        }

        $formatted = ($isNegative ? '-' : '') . ($showSymbol ? '₹ ' : '') . $formattedInt . '.' . $decPart;
        return $formatted;
    }
}
