<?php

namespace Database\Seeders;

use App\Models\InterestRateSlab;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InterestRateSlabSeeder extends Seeder
{
    public function run(): void
    {
        $historicalRates = [
            // [startYear, endYear, rate]
            [1995, 2000, 12.0000],
            [2000, 2001, 11.0000],
            [2001, 2002, 9.5000],
            [2002, 2003, 9.0000],
            [2003, 2011, 8.0000],
            [2011, 2012, 8.6000],
            [2012, 2013, 8.8000],
            [2013, 2016, 8.7000],
            [2016, 2017, 8.0000],
            [2017, 2018, 7.8000],
            [2018, 2019, 8.0000],
            [2019, 2020, 7.9000],
            [2020, 2027, 7.1000],
        ];

        foreach ($historicalRates as [$startYear, $endYear, $annualRate]) {
            for ($year = $startYear; $year < $endYear; $year++) {
                $finYear = $year . '-' . ($year + 1);

                for ($month = 1; $month <= 12; $month++) {
                    // Accounting month 1 = April ($year), 12 = March ($year + 1)
                    if ($month <= 9) {
                        $calMonth = $month + 3; // April(4) to Dec(12)
                        $calYear = $year;
                    } else {
                        $calMonth = $month - 9; // Jan(1) to Mar(3)
                        $calYear = $year + 1;
                    }

                    $startDate = Carbon::create($calYear, $calMonth, 1)->startOfMonth();
                    $endDate = (clone $startDate)->endOfMonth();

                    InterestRateSlab::updateOrCreate(
                        [
                            'financial_year' => $finYear,
                            'accounting_month' => $month,
                        ],
                        [
                            'effective_from' => $startDate->toDateString(),
                            'effective_to' => $endDate->toDateString(),
                            'rate_percentage' => $annualRate,
                            'year_desc' => strtoupper($startDate->format('01-M-Y')),
                            'notification_reference' => 'Govt Notification GPF/' . $finYear,
                        ]
                    );
                }
            }
        }
    }
}
