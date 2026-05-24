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

class UserBillingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected BillingSetting $billingSettings;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user with company type
        $this->user = User::factory()->create([
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
    public function user_can_view_billing_dashboard()
    {
        $response = $this->actingAs($this->user)
            ->get(route('billing.dashboard'));
        
        $response->assertStatus(200);
        $response->assertViewIs('billing.dashboard');
    }

    /** @test */
    public function billing_dashboard_shows_current_status()
    {
        $response = $this->actingAs($this->user)
            ->get(route('billing.dashboard'));
        
        $response->assertStatus(200);
        $response->assertViewHas('billingStatus');
    }

    /** @test */
    public function user_can_view_invoice_list()
    {
        // Create some invoices
        Invoice::create([
            'user_id' => $this->user->id,
            'invoice_number' => 'INV-2024-001',
            'payslip_count' => 25,
            'subtotal' => 375.00,
            'vat_amount' => 56.25,
            'total_amount' => 431.25,
            'status' => Invoice::STATUS_PENDING,
            'due_date' => now()->addDays(30),
        ]);
        
        $response = $this->actingAs($this->user)
            ->get(route('billing.invoices'));
        
        $response->assertStatus(200);
        $response->assertViewIs('billing.invoices');
        $response->assertViewHas('invoices');
    }

    /** @test */
    public function user_can_view_single_invoice()
    {
        $invoice = Invoice::create([
            'user_id' => $this->user->id,
            'invoice_number' => 'INV-2024-001',
            'payslip_count' => 25,
            'subtotal' => 375.00,
            'vat_amount' => 56.25,
            'total_amount' => 431.25,
            'status' => Invoice::STATUS_PENDING,
            'due_date' => now()->addDays(30),
        ]);
        
        $response = $this->actingAs($this->user)
            ->get(route('billing.invoice', $invoice));
        
        $response->assertStatus(200);
        $response->assertViewIs('billing.invoice-detail');
        $response->assertViewHas('invoice');
    }

    /** @test */
    public function user_cannot_view_other_users_invoice()
    {
        $otherUser = User::factory()->create(['type' => 'company']);
        
        $invoice = Invoice::create([
            'user_id' => $otherUser->id,
            'invoice_number' => 'INV-2024-002',
            'payslip_count' => 25,
            'subtotal' => 375.00,
            'vat_amount' => 56.25,
            'total_amount' => 431.25,
            'status' => Invoice::STATUS_PENDING,
            'due_date' => now()->addDays(30),
        ]);
        
        $response = $this->actingAs($this->user)
            ->get(route('billing.invoice', $invoice));
        
        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_view_payment_history()
    {
        $invoice = Invoice::create([
            'user_id' => $this->user->id,
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
            'user_id' => $this->user->id,
            'payment_number' => 'PAY-2024-001',
            'amount' => 431.25,
            'currency' => 'ZAR',
            'payment_method' => BillingPayment::METHOD_PAYFAST,
            'status' => BillingPayment::STATUS_COMPLETED,
            'paid_at' => now(),
        ]);
        
        $response = $this->actingAs($this->user)
            ->get(route('billing.payments'));
        
        $response->assertStatus(200);
        $response->assertViewIs('billing.payments');
        $response->assertViewHas('payments');
    }

    /** @test */
    public function user_can_download_invoice_pdf()
    {
        $invoice = Invoice::create([
            'user_id' => $this->user->id,
            'invoice_number' => 'INV-2024-001',
            'payslip_count' => 25,
            'subtotal' => 375.00,
            'vat_amount' => 56.25,
            'total_amount' => 431.25,
            'status' => Invoice::STATUS_PENDING,
            'due_date' => now()->addDays(30),
        ]);
        
        $response = $this->actingAs($this->user)
            ->get(route('billing.invoice.download', $invoice));
        
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function user_can_initiate_payment()
    {
        $invoice = Invoice::create([
            'user_id' => $this->user->id,
            'invoice_number' => 'INV-2024-001',
            'payslip_count' => 25,
            'subtotal' => 375.00,
            'vat_amount' => 56.25,
            'total_amount' => 431.25,
            'status' => Invoice::STATUS_PENDING,
            'due_date' => now()->addDays(30),
        ]);
        
        $response = $this->actingAs($this->user)
            ->post(route('billing.pay', $invoice));
        
        // Should redirect to payment page or create payment
        $response->assertStatus(200);
    }

    /** @test */
    public function user_cannot_pay_already_paid_invoice()
    {
        $invoice = Invoice::create([
            'user_id' => $this->user->id,
            'invoice_number' => 'INV-2024-001',
            'payslip_count' => 25,
            'subtotal' => 375.00,
            'vat_amount' => 56.25,
            'total_amount' => 431.25,
            'status' => Invoice::STATUS_PAID,
            'due_date' => now()->addDays(30),
            'paid_at' => now(),
        ]);
        
        $response = $this->actingAs($this->user)
            ->post(route('billing.pay', $invoice));
        
        $response->assertStatus(400);
    }

    /** @test */
    public function payment_success_callback_works()
    {
        $invoice = Invoice::create([
            'user_id' => $this->user->id,
            'invoice_number' => 'INV-2024-001',
            'payslip_count' => 25,
            'subtotal' => 375.00,
            'vat_amount' => 56.25,
            'total_amount' => 431.25,
            'status' => Invoice::STATUS_PENDING,
            'due_date' => now()->addDays(30),
        ]);
        
        $payment = BillingPayment::create([
            'invoice_id' => $invoice->id,
            'user_id' => $this->user->id,
            'payment_number' => 'PAY-2024-001',
            'amount' => 431.25,
            'currency' => 'ZAR',
            'payment_method' => BillingPayment::METHOD_PAYFAST,
            'status' => BillingPayment::STATUS_PENDING,
        ]);
        
        $response = $this->actingAs($this->user)
            ->get(route('billing.payment.success', ['payment' => $payment->id]));
        
        $response->assertStatus(200);
    }

    /** @test */
    public function payment_cancel_callback_works()
    {
        $invoice = Invoice::create([
            'user_id' => $this->user->id,
            'invoice_number' => 'INV-2024-001',
            'payslip_count' => 25,
            'subtotal' => 375.00,
            'vat_amount' => 56.25,
            'total_amount' => 431.25,
            'status' => Invoice::STATUS_PENDING,
            'due_date' => now()->addDays(30),
        ]);
        
        $payment = BillingPayment::create([
            'invoice_id' => $invoice->id,
            'user_id' => $this->user->id,
            'payment_number' => 'PAY-2024-001',
            'amount' => 431.25,
            'currency' => 'ZAR',
            'payment_method' => BillingPayment::METHOD_PAYFAST,
            'status' => BillingPayment::STATUS_PENDING,
        ]);
        
        $response = $this->actingAs($this->user)
            ->get(route('billing.payment.cancel', ['payment' => $payment->id]));
        
        $response->assertStatus(200);
    }

    /** @test */
    public function guest_cannot_access_billing_dashboard()
    {
        $response = $this->get(route('billing.dashboard'));
        
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function billing_dashboard_shows_trial_information()
    {
        $this->user->update([
            'trial_ends_at' => now()->addDays(10),
            'billing_status' => 'trial',
        ]);
        
        $response = $this->actingAs($this->user)
            ->get(route('billing.dashboard'));
        
        $response->assertStatus(200);
        $response->assertSee('Trial');
    }

    /** @test */
    public function billing_dashboard_shows_current_cycle_usage()
    {
        // Create a billing cycle with usage
        BillingCycle::create([
            'user_id' => $this->user->id,
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'payslip_count' => 15,
            'amount' => 225.00,
            'status' => BillingCycle::STATUS_ACTIVE,
        ]);
        
        $response = $this->actingAs($this->user)
            ->get(route('billing.dashboard'));
        
        $response->assertStatus(200);
        $response->assertViewHas('currentCycle');
    }
}
