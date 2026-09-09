<?php

namespace Tests\Unit;

use App\Enums\CaseType;
use App\Models\InwardCase;
use App\Services\Calculation\CutoffRuleResolver;
use Carbon\Carbon;
use Tests\TestCase;

class CutoffRuleResolverTest extends TestCase
{
    protected CutoffRuleResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new CutoffRuleResolver();
    }

    public function test_normal_superannuation_statutory_cutoff(): void
    {
        $case = new InwardCase([
            'case_type' => CaseType::NORMAL_SUPERANNUATION,
            'event_date' => '2024-03-31',
        ]);

        $cutoff = $this->resolver->resolveCutoffDate($case);
        $this->assertNotNull($cutoff);
        $this->assertTrue(Carbon::parse('2024-03-31')->lessThanOrEqualTo($cutoff));
    }

    public function test_death_in_service_interest_cutoff(): void
    {
        $case = new InwardCase([
            'case_type' => CaseType::DEATH_IN_SERVICE,
            'event_date' => '2023-08-15',
        ]);

        $cutoff = $this->resolver->resolveCutoffDate($case);
        $this->assertNotNull($cutoff);
        $this->assertEquals('2024-02-29', $cutoff->toDateString());
    }

    public function test_family_pension_interest_cutoff(): void
    {
        $case = new InwardCase([
            'case_type' => CaseType::FAMILY_PENSION,
            'event_date' => '2023-08-15',
        ]);

        $cutoff = $this->resolver->resolveCutoffDate($case);
        $this->assertNotNull($cutoff);
        $this->assertEquals('2024-02-29', $cutoff->toDateString());
    }
}
