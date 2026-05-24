<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\Billing\BillingCycle;
use App\Models\Billing\BillingPayment;
use App\Models\Billing\BillingSetting;
use App\Models\Billing\BillingTier;
use App\Models\Billing\Invoice;
use App\Models\Billing\PayslipUsage;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InvoiceService $invoiceService;
    protected User $user;
    protected BillingCycle $cycle;

    protected function setUp(): void
    {
        parent::setUp();
        
        Mail::fake();
        
        $this->invoiceService = new InvoiceService();
        
        // Create test user
        $this->user = User::factory()->create([
            'type' => 'company',
            'email' => 'test@example.com',
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
            'payment_terms_days' => 30,
            'currency' => 'ZAR',
            'is_active' => true,
        ]);
        
        // Create billing tiers
        BillingTier::create([
            'name' => 'Tier 1',
            'min_payslips' => 1,
            'max_payslips' => 50,
            'price_per_payslip' => 15.00,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        
        // Create billing cycle
        $this->cycle = BillingCycle::create([
            'user_id' => $this->user->id,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'status' => BillingCycle::STATUS_ACTIVE,
            'total_payslips' => 25,
            'total_amount' => 375.00,
        ]);
    }

    /** @test */
    public function it_generates_invoice_from_billing_cycle()
    {
        $invoice = $this->invoiceService->generateInvoice($this->cycle);
        
        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals($this->user->id, $invoice->user_id);
        $this->assertEquals($this->cycle->id, $invoice->billing_cycle_id);
        $this->assertEquals(25, $invoice->payslip_count);
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);
    }

    /** @test */
    public function it_does_not_generate_invoice_for_zero_payslips()
    {
        $this->cycle->update(['total_payslips' => 0]);
        
        $invoice = $this->invoiceService->generateInvoice($this->cycle);
        
        $this->assertNull($invoice);
    }

    /** @test */
    public function it_does_not_duplicate_invoices()
    {
        // Generate first invoice
        $invoice1 = $this->invoiceService->generateInvoice($this->cycle);
        
        // Mark cycle as invoiced
        $this->cycle->update(['status' => BillingCycle::STATUS_INVOICED]);
        
        // Try to generate again
        $invoice2 = $this->invoiceService->generateInvoice($this->cycle);
        
        $this->assertNull($invoice2);
    }

    /** @test */
    public function it_closes_cycle_and_generates_invoice()
    {
        $invoice = $this->invoiceService->closeCycleAndInvoice($this->cycle);
        
        $this->assertInstanceOf(Invoice::class, $invoice);
        
        $this->cycle->refresh();
        $this->assertEquals(BillingCycle::STATUS_CLOSED, $this->cycle->status);
    }

    /** @test */
    public function it_creates_pending_payment()
    {
        $invoice = $this->invoiceService->generateInvoice($this->cycle);
        
        $payment = $this->invoiceService->createPayment($invoice);
        
        $this->assertInstanceOf(BillingPayment::class, $payment);
        $this->assertEquals($invoice->id, $payment->invoice_id);
        $this->assertEquals($invoice->total_amount, $payment->amount);
        $this->assertEquals(BillingPayment::STATUS_PENDING, $payment->status);
    }

    /** @test */
    public function it_processes_successful_payment()
    {
        $invoice = $this->invoiceService->generateInvoice($this->cycle);
        $payment = $this->invoiceService->createPayment($invoice);
        
        $this->invoiceService->processSuccessfulPayment(
            $payment,
            'PAY-123456',
            'COMPLETE',
            ['pf_payment_id' => '123456']
        );
        
        $payment->refresh();
        $invoice->refresh();
        
        $this->assertEquals(BillingPayment::STATUS_COMPLETED, $payment->status);
        $this->assertEquals('PAY-123456', $payment->gateway_reference);
        $this->assertEquals(Invoice::STATUS_PAID, $invoice->status);
        $this->assertNotNull($invoice->paid_at);
    }

    /** @test */
    public function it_processes_failed_payment()
    {
        $invoice = $this->invoiceService->generateInvoice($this->cycle);
        $payment = $this->invoiceService->createPayment($invoice);
        
        $this->invoiceService->processFailedPayment(
            $payment,
            'Insufficient funds',
            ['error' => 'declined']
        );
        
        $payment->refresh();
        
        $this->assertEquals(BillingPayment::STATUS_FAILED, $payment->status);
        $this->assertEquals('Insufficient funds', $payment->notes);
    }

    /** @test */
    public function it_gets_unpaid_invoices()
    {
        // Create paid invoice
        $paidInvoice = Invoice::create([
            'user_id' => $this->user->id,
            'billing_cycle_id' => $this->cycle->id,
            'invoice_number' => 'INV-001',
            'payslip_count' => 10,
            'subtotal' => 150.00,
            'vat_amount' => 22.50,
            'total_amount' => 172.50,
            'status' => Invoice::STATUS_PAID,
            'due_date' => now()->addDays(30),
            'paid_at' => now(),
        ]);
        
        // Create unpaid invoice
        $unpaidInvoice = Invoice::create([
            'user_id' => $this->user->id,
            'invoice_number' => 'INV-002',
            'payslip_count' => 20,
            'subtotal' => 300.00,
            'vat_amount' => 45.00,
            'total_amount' => 345.00,
            'status' => Invoice::STATUS_PENDING,
            'due_date' => now()->addDays(30),
        ]);
        
        $unpaidInvoices = $this->invoiceService->getUnpaidInvoices($this->user);
        
        $this->assertCount(1, $unpaidInvoices);
        $this->assertEquals('INV-002', $unpaidInvoices->first()->invoice_number);
    }

    /** @test */
    public function it_returns_invoice_summary()
    {
        // Create various invoices
        Invoice::create([
            'user_id' => $this->user->id,
            'invoice_number' => 'INV-001',
            'payslip_count' => 10,
            'subtotal' => 150.00,
            'total_amount' => 172.50,
            'status' => Invoice::STATUS_PAID,
            'due_date' => now(),
        ]);
        
        Invoice::create([
            'user_id' => $this->user->id,
            'invoice_number' => 'INV-002',
            'payslip_count' => 20,
            'subtotal' => 300.00,
            'total_amount' => 345.00,
            'status' => Invoice::STATUS_PENDING,
            'due_date' => now()->addDays(30),
        ]);
        
        Invoice::create([
            'user_id' => $this->user->id,
            'invoice_number' => 'INV-003',
            'payslip_count' => 15,
            'subtotal' => 225.00,
            'total_amount' => 258.75,
            'status' => Invoice::STATUS_OVERDUE,
            'due_date' => now()->subDays(10),
        ]);
        
        $summary = $this->invoiceService->getInvoiceSummary($this->user);
        
        $this->assertEquals(3, $summary['total_invoices']);
        $this->assertEquals(1, $summary['total_paid']);
        $this->assertEquals(1, $summary['total_pending']);
        $this->assertEquals(1, $summary['total_overdue']);
        $this->assertEquals(172.50, $summary['total_amount_paid']);
    }

    /** @test */
    public function it_sends_invoice_email()
    {
        $invoice = $this->invoiceService->generateInvoice($this->cycle);
        
        $result = $this->invoiceService->sendInvoiceEmail($invoice);
        
        $this->assertTrue($result);
        
        Mail::assertSent(\App\Mail\Billing\InvoiceGenerated::class, function ($mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    /** @test */
    public function it_sends_payment_confirmation_email()
    {
        $invoice = $this->invoiceService->generateInvoice($this->cycle);
        $payment = $this->invoiceService->createPayment($invoice);
        $payment->update(['status' => BillingPayment::STATUS_COMPLETED]);
        
        $result = $this->invoiceService->sendPaymentConfirmationEmail($invoice, $payment);
        
        $this->assertTrue($result);
        
        Mail::assertSent(\App\Mail\Billing\PaymentReceived::class);
    }

    /** @test */
    public function it_sends_payment_reminder_email()
    {
        $invoice = $this->invoiceService->generateInvoice($this->cycle);
        
        $result = $this->invoiceService->sendPaymentReminderEmail($invoice);
        
        $this->assertTrue($result);
        
        Mail::assertSent(\App\Mail\Billing\PaymentReminder::class);
    }

    /** @test */
    public function it_processes_all_due_cycles()
    {
        // Create multiple cycles that have ended
        $cycle2 = BillingCycle::create([
            'user_id' => $this->user->id,
            'period_start' => now()->subMonths(2)->startOfMonth(),
            'period_end' => now()->subMonths(2)->endOfMonth(),
            'status' => BillingCycle::STATUS_ACTIVE,
            'total_payslips' => 15,
            'total_amount' => 225.00,
        ]);
        
        // Update main cycle to have ended
        $this->cycle->update([
            'period_start' => now()->subMonth()->startOfMonth(),
            'period_end' => now()->subMonth()->endOfMonth(),
        ]);
        
        $result = $this->invoiceService->processAllDueCycles();
        
        $this->assertEquals(2, $result['processed']);
        $this->assertCount(2, $result['invoices']);
        $this->assertEmpty($result['errors']);
    }
}
