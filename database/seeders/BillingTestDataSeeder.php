<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Billing\BillingCycle;
use App\Models\Billing\BillingSetting;
use App\Models\Billing\BillingTier;
use App\Models\Billing\Invoice;
use App\Models\Billing\InvoiceItem;
use App\Models\Billing\BillingPayment;
use App\Models\Billing\PayslipUsage;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillingTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a company user (or create one)
        $user = User::where('type', 'company')->first();
        
        if (!$user) {
            $user = User::first();
        }
        
        if (!$user) {
            $this->command->error('No user found to seed billing data.');
            return;
        }

        $this->command->info("Seeding billing data for user: {$user->name} (ID: {$user->id})");

        // Ensure billing settings exist
        $this->seedBillingSettings();
        
        // Seed billing tiers
        $this->seedBillingTiers();
        
        // Seed billing cycles (past 3 months + current)
        $cycles = $this->seedBillingCycles($user);
        
        // Seed invoices for closed cycles
        $invoices = $this->seedInvoices($user, $cycles);
        
        // Seed payments for some invoices
        $this->seedPayments($user, $invoices);
        
        // Seed payslip usage records
        $this->seedPayslipUsage($user, $cycles);
        
        // Update user billing fields
        $this->updateUserBillingFields($user);

        $this->command->info('Billing test data seeded successfully!');
    }

    protected function seedBillingSettings(): void
    {
        $settings = [
            ['key' => 'billing_enabled', 'value' => 'true'],
            ['key' => 'trial_days', 'value' => '30'],
            ['key' => 'trial_payslips_limit', 'value' => '10'],
            ['key' => 'tax_enabled', 'value' => 'true'],
            ['key' => 'tax_percentage', 'value' => '15'],
            ['key' => 'invoice_due_days', 'value' => '30'],
            ['key' => 'grace_period_days', 'value' => '7'],
            ['key' => 'currency', 'value' => 'ZAR'],
            ['key' => 'currency_symbol', 'value' => 'R'],
            ['key' => 'company_name', 'value' => 'Reliance Corporation ZA'],
            ['key' => 'company_address', 'value' => '123 Main Street, Cape Town, 8001'],
            ['key' => 'company_email', 'value' => 'billing@reliancecorp.co.za'],
            ['key' => 'company_phone', 'value' => '+27 21 123 4567'],
            ['key' => 'company_vat_number', 'value' => '4123456789'],
        ];

        foreach ($settings as $setting) {
            BillingSetting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }

        $this->command->info('  - Billing settings created/updated');
    }

    protected function seedBillingTiers(): void
    {
        // Check if tiers already exist
        if (BillingTier::count() > 0) {
            $this->command->info('  - Billing tiers already exist, skipping');
            return;
        }

        $tiers = [
            ['name' => 'Starter', 'min_payslips' => 1, 'max_payslips' => 20, 'price_per_payslip' => 17.00, 'sort_order' => 1],
            ['name' => 'Growth', 'min_payslips' => 21, 'max_payslips' => 100, 'price_per_payslip' => 12.00, 'sort_order' => 2],
            ['name' => 'Business', 'min_payslips' => 101, 'max_payslips' => 300, 'price_per_payslip' => 11.00, 'sort_order' => 3],
            ['name' => 'Enterprise', 'min_payslips' => 301, 'max_payslips' => null, 'price_per_payslip' => 9.50, 'sort_order' => 4],
        ];

        foreach ($tiers as $tier) {
            BillingTier::create($tier);
        }

        $this->command->info('  - Billing tiers created');
    }

    protected function seedBillingCycles(User $user): array
    {
        $cycles = [];
        
        // Create cycles for the past 4 months
        for ($i = 3; $i >= 0; $i--) {
            $periodStart = Carbon::now()->subMonths($i)->startOfMonth();
            $periodEnd = Carbon::now()->subMonths($i)->endOfMonth();
            
            $isCurrent = $i === 0;
            $totalPayslips = $isCurrent ? rand(5, 20) : rand(15, 75);
            
            $cycle = BillingCycle::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'period_start' => $periodStart->format('Y-m-d'),
                ],
                [
                    'period_end' => $periodEnd->format('Y-m-d'),
                    'total_payslips' => $totalPayslips,
                    'status' => $isCurrent ? 'active' : 'closed',
                    'closed_at' => $isCurrent ? null : $periodEnd->copy()->addDay(),
                ]
            );
            
            $cycles[] = $cycle;
        }

        $this->command->info('  - Billing cycles created: ' . count($cycles));
        
        return $cycles;
    }

    protected function seedInvoices(User $user, array $cycles): array
    {
        $invoices = [];
        $invoiceNumber = 1001;
        
        foreach ($cycles as $index => $cycle) {
            // Skip current month (no invoice yet)
            if ($cycle->status === 'active') {
                continue;
            }
            
            // Calculate amounts based on tiered pricing
            $subtotal = $this->calculateTieredAmount($cycle->total_payslips);
            $taxPercentage = 15;
            $taxAmount = round($subtotal * ($taxPercentage / 100), 2);
            $totalAmount = $subtotal + $taxAmount;
            
            // Determine invoice status
            $statuses = ['paid', 'paid', 'pending', 'overdue'];
            $status = $statuses[$index] ?? 'pending';
            
            $issueDate = Carbon::parse($cycle->period_end)->addDay();
            $dueDate = $issueDate->copy()->addDays(30);
            $paidAt = null;
            
            if ($status === 'paid') {
                $paidAt = $dueDate->copy()->subDays(rand(5, 20));
            } elseif ($status === 'overdue') {
                $dueDate = Carbon::now()->subDays(rand(5, 15));
            }
            
            $invoice = Invoice::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'billing_cycle_id' => $cycle->id,
                ],
                [
                    'invoice_number' => 'INV-' . date('Y') . '-' . str_pad($invoiceNumber++, 5, '0', STR_PAD_LEFT),
                    'period_start' => $cycle->period_start,
                    'period_end' => $cycle->period_end,
                    'total_payslips' => $cycle->total_payslips,
                    'subtotal' => $subtotal,
                    'tax_percentage' => $taxPercentage,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'status' => $status,
                    'issue_date' => $issueDate,
                    'due_date' => $dueDate,
                    'paid_at' => $paidAt,
                    'sent_at' => $issueDate,
                    'notes' => $status === 'overdue' ? 'Payment overdue. Please pay immediately to avoid service interruption.' : null,
                ]
            );
            
            // Create invoice items (tiered breakdown)
            $this->createInvoiceItems($invoice, $cycle->total_payslips);
            
            $invoices[] = $invoice;
        }

        $this->command->info('  - Invoices created: ' . count($invoices));
        
        return $invoices;
    }

    /**
     * Create invoice line items based on tiered pricing
     */
    protected function createInvoiceItems(Invoice $invoice, int $payslipCount): void
    {
        // Delete existing items for this invoice
        InvoiceItem::where('invoice_id', $invoice->id)->delete();
        
        $tiers = BillingTier::orderBy('sort_order')->get();
        $remaining = $payslipCount;
        
        foreach ($tiers as $tier) {
            if ($remaining <= 0) break;
            
            $tierMax = $tier->max_payslips ?? PHP_INT_MAX;
            $tierMin = $tier->min_payslips;
            $tierRange = $tierMax - $tierMin + 1;
            
            $quantity = min($remaining, $tierRange);
            $amount = $quantity * $tier->price_per_payslip;
            
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'workspace_id' => 1,
                'workspace_name' => 'Default Workspace',
                'description' => "{$tier->name}: {$quantity} payslips @ R" . number_format($tier->price_per_payslip, 2),
                'quantity' => $quantity,
                'unit_price' => $tier->price_per_payslip,
                'amount' => $amount,
                'tier_name' => $tier->name,
                'tier_sort_order' => $tier->sort_order,
            ]);
            
            $remaining -= $quantity;
        }
    }

    protected function calculateTieredAmount(int $payslipCount): float
    {
        $tiers = BillingTier::orderBy('sort_order')->get();
        $total = 0;
        $remaining = $payslipCount;
        
        foreach ($tiers as $tier) {
            if ($remaining <= 0) break;
            
            $tierMax = $tier->max_payslips ?? PHP_INT_MAX;
            $tierMin = $tier->min_payslips;
            $tierRange = $tierMax - $tierMin + 1;
            
            $quantity = min($remaining, $tierRange);
            $total += $quantity * $tier->price_per_payslip;
            $remaining -= $quantity;
        }
        
        return round($total, 2);
    }

    protected function seedPayments(User $user, array $invoices): void
    {
        $paymentNumber = 1001;
        
        foreach ($invoices as $invoice) {
            if ($invoice->status === 'paid') {
                BillingPayment::updateOrCreate(
                    [
                        'invoice_id' => $invoice->id,
                        'status' => 'completed',
                    ],
                    [
                        'user_id' => $user->id,
                        'payment_number' => 'PAY-' . date('Y') . '-' . str_pad($paymentNumber++, 5, '0', STR_PAD_LEFT),
                        'amount' => $invoice->total_amount,
                        'currency' => 'ZAR',
                        'payment_method' => ['payfast', 'bank_transfer', 'card'][rand(0, 2)],
                        'status' => 'completed',
                        'gateway_reference' => 'PF' . rand(100000000, 999999999),
                        'gateway_status' => 'COMPLETE',
                        'gateway_response' => json_encode([
                            'pf_payment_id' => rand(10000000, 99999999),
                            'payment_status' => 'COMPLETE',
                            'amount_gross' => $invoice->total_amount,
                            'amount_fee' => round($invoice->total_amount * 0.025, 2),
                            'amount_net' => round($invoice->total_amount * 0.975, 2),
                        ]),
                        'paid_at' => $invoice->paid_at,
                    ]
                );
            } elseif ($invoice->status === 'pending' || $invoice->status === 'overdue') {
                // Create a failed payment attempt for some
                if (rand(0, 1)) {
                    BillingPayment::updateOrCreate(
                        [
                            'invoice_id' => $invoice->id,
                            'status' => 'failed',
                        ],
                        [
                            'user_id' => $user->id,
                            'payment_number' => 'PAY-' . date('Y') . '-' . str_pad($paymentNumber++, 5, '0', STR_PAD_LEFT),
                            'amount' => $invoice->total_amount,
                            'currency' => 'ZAR',
                            'payment_method' => 'payfast',
                            'status' => 'failed',
                            'gateway_reference' => 'PF' . rand(100000000, 999999999),
                            'gateway_status' => 'FAILED',
                            'gateway_response' => json_encode([
                                'pf_payment_id' => rand(10000000, 99999999),
                                'payment_status' => 'FAILED',
                                'error' => 'Insufficient funds',
                            ]),
                            'paid_at' => null,
                        ]
                    );
                }
            }
        }

        $this->command->info('  - Payments created');
    }

    protected function seedPayslipUsage(User $user, array $cycles): void
    {
        $tiers = BillingTier::orderBy('sort_order')->get();
        
        // Get actual payslip IDs from the database
        $actualPayslipIds = DB::table('pay_slips')->pluck('id')->toArray();
        $actualEmployeeIds = DB::table('employees')->pluck('id')->toArray();
        
        // If no payslips exist, skip usage seeding
        if (empty($actualPayslipIds)) {
            $this->command->warn('  - No payslips found in database, skipping usage records');
            return;
        }
        
        // Track which payslip IDs have been used (unique constraint)
        $usedPayslipIds = [];
        $payslipIndex = 0;
        
        foreach ($cycles as $cycle) {
            // Clear existing usage for this cycle
            PayslipUsage::where('billing_cycle_id', $cycle->id)->delete();
            
            $cumulativeCount = 0;
            $cycleUsageCount = 0;
            
            // Calculate how many usages we can create for this cycle
            $maxUsage = min($cycle->total_payslips, count($actualPayslipIds) - count($usedPayslipIds));
            
            if ($maxUsage <= 0) {
                $this->command->warn("  - No more payslips available for cycle {$cycle->id}, limiting usage records");
                continue;
            }
            
            while ($cycleUsageCount < $maxUsage && $payslipIndex < count($actualPayslipIds)) {
                $payslipId = $actualPayslipIds[$payslipIndex];
                $payslipIndex++;
                
                // Skip if already used
                if (in_array($payslipId, $usedPayslipIds)) {
                    continue;
                }
                
                $usedPayslipIds[] = $payslipId;
                $cumulativeCount++;
                $cycleUsageCount++;
                
                // Find the appropriate tier
                $tier = $tiers->first(function ($t) use ($cumulativeCount) {
                    $max = $t->max_payslips ?? PHP_INT_MAX;
                    return $cumulativeCount >= $t->min_payslips && $cumulativeCount <= $max;
                });
                
                // Get employee ID for this payslip
                $employeeId = DB::table('pay_slips')->where('id', $payslipId)->value('employee_id') ?? 1;
                
                PayslipUsage::create([
                    'user_id' => $user->id,
                    'workspace_id' => 1,
                    'billing_cycle_id' => $cycle->id,
                    'payslip_id' => $payslipId,
                    'employee_id' => $employeeId,
                    'salary_month' => Carbon::parse($cycle->period_start)->format('Y-m'),
                    'amount_charged' => $tier ? $tier->price_per_payslip : 10.00,
                    'tier_id' => $tier?->id,
                    'cumulative_count' => $cumulativeCount,
                    'status' => $cycle->status === 'active' ? 'pending' : 'invoiced',
                ]);
            }
            
            // Update cycle's total_payslips to match actual usage
            $cycle->update(['total_payslips' => $cycleUsageCount]);
        }

        $this->command->info('  - Payslip usage records created: ' . count($usedPayslipIds));
    }

    protected function updateUserBillingFields(User $user): void
    {
        $user->update([
            'trial_ends_at' => Carbon::now()->subDays(60), // Trial ended
            'trial_payslips_used' => 10,
            'payslips_count' => PayslipUsage::where('user_id', $user->id)->count(),
            'billing_status' => 'active',
        ]);

        $this->command->info('  - User billing fields updated');
    }
}
