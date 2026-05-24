<?php

namespace Tests\Feature\Billing;

use App\Models\User;
use App\Models\Billing\BillingCycle;
use App\Models\Billing\BillingSetting;
use App\Models\Billing\BillingTier;
use App\Models\Billing\Invoice;
use App\Models\Billing\BillingPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBillingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $companyUser;
    protected BillingSetting $billingSettings;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = User::factory()->create([
            'type' => 'super_admin',
            'email' => 'admin@example.com',
            'name' => 'Super Admin',
        ]);
        
        // Create company user
        $this->companyUser = User::factory()->create([
            'type' => 'company',
            'email' => 'company@example.com',
            'name' => 'Test Company',
            'trial_ends_at' => now()->addDays(14),
            'billing_status' => 'trial',
        ]);
        
        // Create billing settings
        $this->billingSettings = BillingSetting::create([
            'trial_period_days' => 30,
            'free_payslips_per_month' => 5,
            'max_payslips_per_month' => 10000,
            'vat_percentage' => 15.00,
            'invoice_due_days' => 30,
            'grace_period_days' => 7,
            'payfast_merchant_id' => 'test_merchant',
            'payfast_merchant_key' => 'test_key',
            'payfast_passphrase' => 'test_pass',
            'payfast_sandbox_mode' => true,
        ]);
        
        // Create billing tiers
        $tiers = [
            ['from_payslips' => 1, 'to_payslips' => 50, 'price_per_payslip' => 15.00],
            ['from_payslips' => 51, 'to_payslips' => 100, 'price_per_payslip' => 12.00],
            ['from_payslips' => 101, 'to_payslips' => 500, 'price_per_payslip' => 10.00],
            ['from_payslips' => 501, 'to_payslips' => 1000, 'price_per_payslip' => 8.00],
            ['from_payslips' => 1001, 'to_payslips' => null, 'price_per_payslip' => 5.00],
        ];
        
        foreach ($tiers as $order => $tier) {
            BillingTier::create([
                'billing_setting_id' => $this->billingSettings->id,
                'tier_order' => $order + 1,
                'from_payslips' => $tier['from_payslips'],
                'to_payslips' => $tier['to_payslips'],
                'price_per_payslip' => $tier['price_per_payslip'],
            ]);
        }
    }

    /** @test */
    public function admin_can_view_billing_settings()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.billing.settings'));
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.billing.settings');
    }

    /** @test */
    public function admin_can_update_billing_settings()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.billing.settings.update'), [
                'trial_period_days' => 45,
                'free_payslips_per_month' => 10,
                'max_payslips_per_month' => 15000,
                'vat_percentage' => 15.00,
                'invoice_due_days' => 30,
                'grace_period_days' => 14,
                'payfast_merchant_id' => 'new_merchant',
                'payfast_merchant_key' => 'new_key',
                'payfast_passphrase' => 'new_pass',
                'payfast_sandbox_mode' => false,
            ]);
        
        $response->assertRedirect();
        
        $this->billingSettings->refresh();
        $this->assertEquals(45, $this->billingSettings->trial_period_days);
        $this->assertEquals(10, $this->billingSettings->free_payslips_per_month);
    }

    /** @test */
    public function admin_can_view_billing_tiers()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.billing.tiers'));
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.billing.tiers');
        $response->assertViewHas('tiers');
    }

    /** @test */
    public function admin_can_create_billing_tier()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.billing.tiers.store'), [
                'from_payslips' => 2001,
                'to_payslips' => 5000,
                'price_per_payslip' => 3.00,
            ]);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('billing_tiers', [
            'from_payslips' => 2001,
            'to_payslips' => 5000,
            'price_per_payslip' => 3.00,
        ]);
    }

    /** @test */
    public function admin_can_update_billing_tier()
    {
        $tier = BillingTier::first();
        
        $response = $this->actingAs($this->admin)
            ->put(route('admin.billing.tiers.update', $tier), [
                'from_payslips' => 1,
                'to_payslips' => 75,
                'price_per_payslip' => 14.00,
            ]);
        
        $response->assertRedirect();
        
        $tier->refresh();
        $this->assertEquals(75, $tier->to_payslips);
        $this->assertEquals(14.00, $tier->price_per_payslip);
    }

    /** @test */
    public function admin_can_delete_billing_tier()
    {
        $tier = BillingTier::latest('id')->first();
        $tierId = $tier->id;
        
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.billing.tiers.destroy', $tier));
        
        $response->assertRedirect();
        
        $this->assertDatabaseMissing('billing_tiers', ['id' => $tierId]);
    }

    /** @test */
    public function admin_can_view_all_invoices()
    {
        Invoice::create([
            'user_id' => $this->companyUser->id,
            'invoice_number' => 'INV-2024-001',
            'payslip_count' => 25,
            'subtotal' => 375.00,
            'vat_amount' => 56.25,
            'total_amount' => 431.25,
            'status' => Invoice::STATUS_PENDING,
            'due_date' => now()->addDays(30),
        ]);
        
        $response = $this->actingAs($this->admin)
            ->get(route('admin.billing.invoices'));
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.billing.invoices');
        $response->assertViewHas('invoices');
    }

    /** @test */
    public function admin_can_view_single_invoice()
    {
        $invoice = Invoice::create([
            'user_id' => $this->companyUser->id,
            'invoice_number' => 'INV-2024-001',
            'payslip_count' => 25,
            'subtotal' => 375.00,
            'vat_amount' => 56.25,
            'total_amount' => 431.25,
            'status' => Invoice::STATUS_PENDING,
            'due_date' => now()->addDays(30),
        ]);
        
        $response = $this->actingAs($this->admin)
            ->get(route('admin.billing.invoice', $invoice));
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.billing.invoice-detail');
    }

    /** @test */
    public function admin_can_view_all_payments()
    {
        $invoice = Invoice::create([
            'user_id' => $this->companyUser->id,
            'invoice_number' => 'INV-2024-001',
            'payslip_count' => 25,
            'subtotal' => 375.00,
            'vat_amount' => 56.25,
            'total_amount' => 431.25,
            'status' => Invoice::STATUS_PAID,
            'due_date' => now()->addDays(30),
            'paid_at' => now(),
        ]);
        
        BillingPayment::create([
            'invoice_id' => $invoice->id,
            'user_id' => $this->companyUser->id,
            'payment_number' => 'PAY-2024-001',
            'amount' => 431.25,
            'currency' => 'ZAR',
            'payment_method' => BillingPayment::METHOD_PAYFAST,
            'status' => BillingPayment::STATUS_COMPLETED,
            'paid_at' => now(),
        ]);
        
        $response = $this->actingAs($this->admin)
            ->get(route('admin.billing.payments'));
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.billing.payments');
        $response->assertViewHas('payments');
    }

    /** @test */
    public function admin_can_view_user_billing_details()
    {
        BillingCycle::create([
            'user_id' => $this->companyUser->id,
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'payslip_count' => 15,
            'amount' => 225.00,
            'status' => BillingCycle::STATUS_ACTIVE,
        ]);
        
        $response = $this->actingAs($this->admin)
            ->get(route('admin.billing.user', $this->companyUser));
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.billing.user-detail');
        $response->assertViewHas('user');
        $response->assertViewHas('billingCycles');
    }

    /** @test */
    public function admin_can_view_billing_reports()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.billing.reports'));
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.billing.reports');
    }

    /** @test */
    public function admin_can_manually_generate_invoice()
    {
        $cycle = BillingCycle::create([
            'user_id' => $this->companyUser->id,
            'start_date' => now()->subMonth()->startOfMonth(),
            'end_date' => now()->subMonth()->endOfMonth(),
            'payslip_count' => 50,
            'amount' => 750.00,
            'status' => BillingCycle::STATUS_ACTIVE,
        ]);
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.billing.generate-invoice', $cycle));
        
        $response->assertRedirect();
        
        // Should have created an invoice
        $this->assertDatabaseHas('invoices', [
            'user_id' => $this->companyUser->id,
            'payslip_count' => 50,
        ]);
    }

    /** @test */
    public function admin_can_mark_invoice_as_paid()
    {
        $invoice = Invoice::create([
            'user_id' => $this->companyUser->id,
            'invoice_number' => 'INV-2024-001',
            'payslip_count' => 25,
            'subtotal' => 375.00,
            'vat_amount' => 56.25,
            'total_amount' => 431.25,
            'status' => Invoice::STATUS_PENDING,
            'due_date' => now()->addDays(30),
        ]);
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.billing.invoice.mark-paid', $invoice), [
                'payment_method' => 'manual',
                'notes' => 'Paid via bank transfer',
            ]);
        
        $response->assertRedirect();
        
        $invoice->refresh();
        $this->assertEquals(Invoice::STATUS_PAID, $invoice->status);
    }

    /** @test */
    public function admin_can_void_invoice()
    {
        $invoice = Invoice::create([
            'user_id' => $this->companyUser->id,
            'invoice_number' => 'INV-2024-001',
            'payslip_count' => 25,
            'subtotal' => 375.00,
            'vat_amount' => 56.25,
            'total_amount' => 431.25,
            'status' => Invoice::STATUS_PENDING,
            'due_date' => now()->addDays(30),
        ]);
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.billing.invoice.void', $invoice), [
                'reason' => 'Invoice created in error',
            ]);
        
        $response->assertRedirect();
        
        $invoice->refresh();
        $this->assertEquals(Invoice::STATUS_CANCELLED, $invoice->status);
    }

    /** @test */
    public function admin_can_extend_user_trial()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.billing.extend-trial', $this->companyUser), [
                'days' => 30,
            ]);
        
        $response->assertRedirect();
        
        $this->companyUser->refresh();
        $this->assertTrue($this->companyUser->trial_ends_at->greaterThan(now()->addDays(20)));
    }

    /** @test */
    public function admin_can_change_user_billing_status()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.billing.change-status', $this->companyUser), [
                'status' => 'active',
            ]);
        
        $response->assertRedirect();
        
        $this->companyUser->refresh();
        $this->assertEquals('active', $this->companyUser->billing_status);
    }

    /** @test */
    public function non_admin_cannot_access_admin_billing_routes()
    {
        $response = $this->actingAs($this->companyUser)
            ->get(route('admin.billing.settings'));
        
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_export_billing_data()
    {
        Invoice::create([
            'user_id' => $this->companyUser->id,
            'invoice_number' => 'INV-2024-001',
            'payslip_count' => 25,
            'subtotal' => 375.00,
            'vat_amount' => 56.25,
            'total_amount' => 431.25,
            'status' => Invoice::STATUS_PAID,
            'due_date' => now()->addDays(30),
            'paid_at' => now(),
        ]);
        
        $response = $this->actingAs($this->admin)
            ->get(route('admin.billing.export', ['type' => 'invoices']));
        
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
