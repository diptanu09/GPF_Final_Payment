<?php

namespace Tests\Unit;

use App\Services\Format\IndianCurrencyFormatter;
use PHPUnit\Framework\TestCase;

class IndianCurrencyFormatterTest extends TestCase
{
    public function test_converts_zero_to_words(): void
    {
        $this->assertEquals('Zero Rupees Only', IndianCurrencyFormatter::toWords(0));
    }

    public function test_converts_hundreds_to_words(): void
    {
        $this->assertEquals('Five Hundred Rupees Only', IndianCurrencyFormatter::toWords(500));
        $this->assertEquals('Five Hundred Fifty Rupees Only', IndianCurrencyFormatter::toWords(550));
    }

    public function test_converts_thousands_and_lakhs_to_words(): void
    {
        $this->assertEquals('One Lakh Twenty Three Thousand Four Hundred Fifty Six Rupees Only', IndianCurrencyFormatter::toWords(123456));
        $this->assertEquals('Sixty Thousand Rupees Only', IndianCurrencyFormatter::toWords(60000));
        $this->assertEquals('Eleven Lakh Thirty Four Thousand Nine Hundred Twenty Three Rupees Only', IndianCurrencyFormatter::toWords(1134923));
    }

    public function test_converts_crores_to_words(): void
    {
        $this->assertEquals('Two Crore Fifty Lakh Rupees Only', IndianCurrencyFormatter::toWords(25000000));
    }

    public function test_converts_decimals_to_words(): void
    {
        $this->assertEquals('One Hundred Rupees and Fifty Paise Only', IndianCurrencyFormatter::toWords(100.50));
    }

    public function test_formats_inr(): void
    {
        $this->assertEquals('₹ 1,23,456.00', IndianCurrencyFormatter::formatInr(123456));
        $this->assertEquals('₹ 11,34,923.50', IndianCurrencyFormatter::formatInr(1134923.5));
    }
}
