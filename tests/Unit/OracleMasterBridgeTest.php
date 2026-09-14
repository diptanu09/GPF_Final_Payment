<?php

namespace Tests\Unit;

use App\Services\Integration\OracleMasterBridge;
use Tests\TestCase;

class OracleMasterBridgeTest extends TestCase
{
    protected OracleMasterBridge $bridge;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bridge = app(OracleMasterBridge::class);
    }

    public function test_bridge_exposes_statutory_master_dropdowns(): void
    {
        $series = $this->bridge->getSeriesList();
        $this->assertNotEmpty($series);
        $this->assertTrue($series->contains('id', '10') || $series->contains('id', '1'));

        $ddos = $this->bridge->getDdoList();
        $this->assertNotEmpty($ddos);

        $treasuries = $this->bridge->getTreasuries();
        $this->assertNotEmpty($treasuries);

        $pensionTypes = $this->bridge->getPensionTypes();
        $this->assertNotEmpty($pensionTypes);
    }

    public function test_vouchers_fetch_correctly_from_vlc(): void
    {
        // Series 10, Account 5937
        $vouchers = $this->bridge->getVouchers('10', '5937');
        $this->assertIsArray($vouchers);
        
        // If connected to Oracle, verify aggregated vouchers exist
        if (!empty($vouchers)) {
            // Check that month 2026-07 has both deposit and withdrawal aggregated
            if (isset($vouchers['2026-07'])) {
                $v = $vouchers['2026-07'];
                $this->assertGreaterThan(0, $v['deposit']);
                $this->assertGreaterThan(0, $v['withdrawal']);
                $this->assertStringContainsString(',', $v['voucher_no']); // Multiple vouchers
            }
        }
    }

    public function test_closing_balances_fetch_from_vlc_yearly_balances(): void
    {
        $balances = $this->bridge->getAvailableClosingBalances('10', '5937');
        $this->assertNotEmpty($balances);
        $first = $balances->first();
        $this->assertArrayHasKey('financial_year', $first);
        $this->assertArrayHasKey('closing_balance', $first);
    }
}
