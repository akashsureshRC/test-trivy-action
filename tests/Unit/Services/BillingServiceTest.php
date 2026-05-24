<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\Billing\BillingCycle;
use App\Models\Billing\BillingSetting;
use App\Models\Billing\BillingTier;
use App\Models\Billing\PayslipUsage;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BillingService $billingService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->billingService = new BillingService();
        
        // Create test user
        $this->user = User::factory()->create([
            'type' => 'company',
            'trial_ends_at' => now()->addDays(14),
            'trial_payslips_used' => 0,
            'billing_status' => 'active',
        ]);
        
        // Create billing settings
        BillingSetting::create([
            'base_rate_per_payslip' => 15.00,
            'minimum_monthly_fee' => 0,
            'vat_rate' => 15,
            'trial_days' => 14,
            'trial_payslips' => 10,
            'billing_cycle_day' => 1,
            'grace_period_days' => 7,
            'currency' => 'ZAR',
            'is_active' => true,
        ]);
        
        // Create billing tiers
        $this->createBillingTiers();
    }

    protected function createBillingTiers(): void
    {
        BillingTier::create([
            'name' => 'Tier 1',
            'min_payslips' => 1,
            'max_payslips' => 50,
            'price_per_payslip' => 15.00,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        
        BillingTier::create([
            'name' => 'Tier 2',
            'min_payslips' => 51,
            'max_payslips' => 200,
            'price_per_payslip' => 12.00,
            'is_active' => true,
            'sort_order' => 2,
        ]);
        
        BillingTier::create([
            'name' => 'Tier 3',
            'min_payslips' => 201,
            'max_payslips' => 500,
            'price_per_payslip' => 9.00,
            'is_active' => true,
            'sort_order' => 3,
        ]);
        
        BillingTier::create([
            'name' => 'Tier 4',
            'min_payslips' => 501,
            'max_payslips' => 1000,
            'price_per_payslip' => 6.00,
            'is_active' => true,
            'sort_order' => 4,
        ]);
        
        BillingTier::create([
            'name' => 'Tier 5',
            'min_payslips' => 1001,
            'max_payslips' => null,
            'price_per_payslip' => 4.00,
            'is_active' => true,
            'sort_order' => 5,
        ]);
    }

    /** @test */
    public function it_calculates_cost_for_single_tier()
    {
        // 25 payslips should all be at Tier 1 rate (R15 each)
        $estimate = $this->billingService->calculateEstimatedBill(25);
        
        $this->assertEquals(25, $estimate['payslip_count']);
        $this->assertEquals(375.00, $estimate['subtotal']); // 25 * R15
        $this->assertGreaterThan(0, $estimate['vat']);
    }

    /** @test */
    public function it_calculates_cost_with_cumulative_tiers()
    {
        // 75 payslips: 50 at R15 (Tier 1) + 25 at R12 (Tier 2)
        $estimate = $this->billingService->calculateEstimatedBill(75);
        
        $expectedSubtotal = (50 * 15) + (25 * 12); // 750 + 300 = 1050
        
        $this->assertEquals(75, $estimate['payslip_count']);
        $this->assertEquals($expectedSubtotal, $estimate['subtotal']);
    }

    /** @test */
    public function it_calculates_cost_across_multiple_tiers()
    {
        // 250 payslips: 50 at R15 + 150 at R12 + 50 at R9
        $estimate = $this->billingService->calculateEstimatedBill(250);
        
        $expectedSubtotal = (50 * 15) + (150 * 12) + (50 * 9); // 750 + 1800 + 450 = 3000
        
        $this->assertEquals(250, $estimate['payslip_count']);
        $this->assertEquals($expectedSubtotal, $estimate['subtotal']);
    }

    /** @test */
    public function it_calculates_cost_for_large_volumes()
    {
        // 1500 payslips: 50@15 + 150@12 + 300@9 + 500@6 + 500@4
        $estimate = $this->billingService->calculateEstimatedBill(1500);
        
        $expectedSubtotal = (50 * 15) + (150 * 12) + (300 * 9) + (500 * 6) + (500 * 4);
        // 750 + 1800 + 2700 + 3000 + 2000 = 10250
        
        $this->assertEquals(1500, $estimate['payslip_count']);
        $this->assertEquals($expectedSubtotal, $estimate['subtotal']);
    }

    /** @test */
    public function it_applies_vat_correctly()
    {
        $estimate = $this->billingService->calculateEstimatedBill(50);
        
        $expectedVat = $estimate['subtotal'] * 0.15;
        $expectedTotal = $estimate['subtotal'] + $expectedVat;
        
        $this->assertEquals($expectedVat, $estimate['vat']);
        $this->assertEquals($expectedTotal, $estimate['total']);
    }

    /** @test */
    public function it_identifies_user_in_active_trial()
    {
        $this->user->update([
            'trial_ends_at' => now()->addDays(7),
            'trial_payslips_used' => 5,
        ]);
        
        $trialStatus = $this->billingService->getTrialStatus($this->user);
        
        $this->assertTrue($trialStatus['in_trial']);
        $this->assertFalse($trialStatus['trial_exhausted']);
        $this->assertEquals(7, $trialStatus['days_remaining']);
    }

    /** @test */
    public function it_identifies_expired_trial()
    {
        $this->user->update([
            'trial_ends_at' => now()->subDays(1),
            'trial_payslips_used' => 5,
        ]);
        
        $trialStatus = $this->billingService->getTrialStatus($this->user);
        
        $this->assertFalse($trialStatus['in_trial']);
    }

    /** @test */
    public function it_identifies_trial_exhausted_by_payslips()
    {
        $this->user->update([
            'trial_ends_at' => now()->addDays(7),
            'trial_payslips_used' => 15, // Exceeds 10 trial payslips
        ]);
        
        $trialStatus = $this->billingService->getTrialStatus($this->user);
        
        $this->assertTrue($trialStatus['trial_exhausted']);
    }

    /** @test */
    public function it_creates_billing_cycle_for_user()
    {
        $cycle = $this->billingService->getOrCreateCurrentCycle($this->user);
        
        $this->assertInstanceOf(BillingCycle::class, $cycle);
        $this->assertEquals($this->user->id, $cycle->user_id);
        $this->assertEquals(BillingCycle::STATUS_ACTIVE, $cycle->status);
    }

    /** @test */
    public function it_returns_existing_active_cycle()
    {
        // Create first cycle
        $cycle1 = $this->billingService->getOrCreateCurrentCycle($this->user);
        
        // Should return the same cycle
        $cycle2 = $this->billingService->getOrCreateCurrentCycle($this->user);
        
        $this->assertEquals($cycle1->id, $cycle2->id);
    }

    /** @test */
    public function it_tracks_payrun_billing()
    {
        // Simulate a payrun with 10 payslips
        $result = $this->billingService->trackPayrunProcessed(
            $this->user,
            10, // payslip count
            null, // payrun (optional)
            '2025-12' // salary month
        );
        
        $this->assertTrue($result['success']);
        $this->assertEquals(10, $result['payslips_count']);
        
        // Check PayslipUsage was created
        $usage = PayslipUsage::where('user_id', $this->user->id)->first();
        $this->assertNotNull($usage);
        $this->assertEquals(10, $usage->payslip_count);
    }

    /** @test */
    public function it_tracks_trial_usage_separately()
    {
        $this->user->update([
            'trial_ends_at' => now()->addDays(7),
            'trial_payslips_used' => 0,
        ]);
        
        $result = $this->billingService->trackPayrunProcessed(
            $this->user,
            5,
            null,
            '2025-12'
        );
        
        $this->assertTrue($result['is_trial']);
        
        // Check trial payslips were incremented
        $this->user->refresh();
        $this->assertEquals(5, $this->user->trial_payslips_used);
    }

    /** @test */
    public function it_provides_current_cycle_summary()
    {
        // Create usage
        $this->billingService->trackPayrunProcessed($this->user, 30, null, '2025-12');
        
        $summary = $this->billingService->getCurrentCycleSummary($this->user);
        
        $this->assertEquals(30, $summary['payslips_count']);
        $this->assertArrayHasKey('current_charges', $summary);
        $this->assertArrayHasKey('estimated_total', $summary);
    }

    /** @test */
    public function it_returns_correct_billing_status()
    {
        $status = $this->billingService->getBillingStatus($this->user);
        
        $this->assertArrayHasKey('trial', $status);
        $this->assertArrayHasKey('current_cycle', $status);
        $this->assertArrayHasKey('billing_status', $status);
    }

    /** @test */
    public function it_returns_usage_history()
    {
        // Create multiple usage records
        $this->billingService->trackPayrunProcessed($this->user, 10, null, '2025-10');
        $this->billingService->trackPayrunProcessed($this->user, 15, null, '2025-11');
        $this->billingService->trackPayrunProcessed($this->user, 20, null, '2025-12');
        
        $history = $this->billingService->getUsageHistory($this->user, 10);
        
        $this->assertCount(3, $history);
    }
}
