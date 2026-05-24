<?php

namespace App\Http\Controllers;

use App\Services\BillingService;
use App\Services\InvoicePdfService;
use App\Models\Billing\BillingCycle;
use App\Models\Billing\BillingSetting;
use App\Models\Billing\BillingTier;
use App\Models\Billing\Invoice;
use App\Models\Billing\PayslipUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserBillingController extends Controller
{
    protected BillingService $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    /**
     * Display the billing dashboard for the user
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get comprehensive billing status
        $billingStatus = $this->billingService->getBillingStatus($user);
        
        // Get pricing tiers for display
        $tiers = BillingTier::getActiveTiers();
        
        // Get recent usage
        $recentUsage = $this->billingService->getUsageHistory($user, 10);
        
        // Get recent invoices
        $invoices = Invoice::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('billing.index', compact(
            'billingStatus',
            'tiers',
            'recentUsage',
            'invoices'
        ));
    }

    /**
     * Display usage history
     */
    public function usage(Request $request)
    {
        $user = Auth::user();
        
        $query = PayslipUsage::where('user_id', $user->id)
            ->with(['payslip.employee_profile', 'tier', 'billingCycle', 'workspace']);
        
        // Filter by month
        if ($request->has('month')) {
            $query->where('salary_month', $request->month);
        }
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $usages = $query->orderBy('created_at', 'desc')
            ->paginate(25);
        
        // Get available months for filter
        $months = PayslipUsage::where('user_id', $user->id)
            ->select('salary_month')
            ->distinct()
            ->orderBy('salary_month', 'desc')
            ->pluck('salary_month');

        return view('billing.usage', compact('usages', 'months'));
    }

    /**
     * Display invoices list
     */
    public function invoices()
    {
        $user = Auth::user();
        
        $invoices = Invoice::where('user_id', $user->id)
            ->with(['billingCycle', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('billing.invoices', compact('invoices'));
    }

    /**
     * Display a single invoice
     */
    public function showInvoice($id)
    {
        $user = Auth::user();
        
        $invoice = Invoice::where('user_id', $user->id)
            ->where('id', $id)
            ->with(['billingCycle', 'items', 'payments'])
            ->firstOrFail();

        // Get company details from billing settings
        $company = [
            'name' => BillingSetting::get('company_name') ?: adminSetting('company_name') ?: 'RC ClearPay',
            'address' => BillingSetting::get('company_address') ?: adminSetting('company_address') ?: '',
            'phone' => BillingSetting::get('company_phone') ?: adminSetting('company_phone') ?: '',
            'email' => BillingSetting::get('company_email') ?: adminSetting('company_email') ?: '',
            'vat_number' => BillingSetting::get('company_vat_number') ?: adminSetting('company_vat_number') ?: '',
            'bank_name' => BillingSetting::get('bank_name') ?: adminSetting('bank_name') ?: '',
            'bank_account_name' => BillingSetting::get('bank_account_name') ?: adminSetting('bank_account_name') ?: '',
            'bank_account_number' => BillingSetting::get('bank_account_number') ?: adminSetting('bank_account_number') ?: '',
            'bank_branch_code' => BillingSetting::get('bank_branch_code') ?: adminSetting('bank_branch_code') ?: '',
        ];

        // Get EFT proof submissions for this invoice
        $eftSubmissions = \App\Models\Billing\BankTransferPayment::where('invoice_id', $invoice->id)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('billing.invoice-detail', compact('invoice', 'company', 'eftSubmissions'));
    }

    /**
     * Download invoice as PDF
     */
    public function downloadInvoice($id)
    {
        $user = Auth::user();
        
        $invoice = Invoice::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $pdfService = app(InvoicePdfService::class);
        
        return $pdfService->download($invoice);
    }

    /**
     * View invoice PDF in browser
     */
    public function viewInvoicePdf($id)
    {
        $user = Auth::user();
        
        $invoice = Invoice::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $pdfService = app(InvoicePdfService::class);
        
        return $pdfService->stream($invoice);
    }

    /**
     * Display pricing information
     */
    public function pricing()
    {
        $tiers = BillingTier::getActiveTiers();
        $currencySymbol = BillingSetting::getCurrencySymbol();
        $taxEnabled = BillingSetting::isTaxEnabled();
        $taxPercentage = BillingSetting::getTaxPercentage();

        return view('billing.pricing', compact(
            'tiers',
            'currencySymbol',
            'taxEnabled',
            'taxPercentage'
        ));
    }

    /**
     * Calculate estimated bill (API endpoint)
     */
    public function calculateEstimate(Request $request)
    {
        $request->validate([
            'payslip_count' => 'required|integer|min:1|max:10000',
        ]);

        $estimate = $this->billingService->calculateEstimatedBill($request->payslip_count);

        return response()->json($estimate);
    }

    /**
     * Get billing status (API endpoint)
     */
    public function status()
    {
        $user = Auth::user();
        $status = $this->billingService->getBillingStatus($user);

        return response()->json($status);
    }

    /**
     * Get current cycle summary (API endpoint)
     */
    public function currentCycle()
    {
        $user = Auth::user();
        $summary = $this->billingService->getCurrentCycleSummary($user);

        return response()->json($summary);
    }

    /**
     * Upgrade from trial to paid plan
     */
    public function upgradeFromTrial()
    {
        $user = Auth::user();
        
        // Check if user is company type
        if ($user->type !== 'company') {
            return redirect()->back()->with('error', __('Only company accounts can upgrade.'));
        }
        
        // Check if user is actually on trial
        $billingStatus = $this->billingService->getBillingStatus($user);
        if (!$billingStatus['is_in_trial'] && $user->billing_status === 'active') {
            return redirect()->route('my-billing.index')
                ->with('info', __('Your account is already on a paid plan.'));
        }
        
        // End trial and convert to paid
        $this->billingService->endTrial($user);
        
        return redirect()->route('my-billing.index')
            ->with('upgrade_celebration', true)
            ->with('success', __('Congratulations! Your account has been upgraded to a paid plan. You can now create payslips and will be invoiced at the end of each billing cycle.'));
    }
}
