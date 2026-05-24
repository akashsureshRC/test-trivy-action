<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\BillingPayment;
use App\Models\Billing\BillingSetting;
use App\Models\Billing\Invoice;
use App\Models\Billing\PaymentTransaction;
use App\Services\InvoiceService;
use App\Services\InvoicePdfService;
use App\Services\PayFastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected InvoiceService $invoiceService;
    protected PayFastService $payFastService;

    public function __construct(InvoiceService $invoiceService, PayFastService $payFastService)
    {
        $this->invoiceService = $invoiceService;
        $this->payFastService = $payFastService;
    }

    /**
     * Initiate payment for an invoice (redirect to checkout)
     */
    public function initiate(Request $request, Invoice $invoice)
    {
        $user = Auth::user();

        // Verify ownership
        if ($invoice->user_id !== $user->id) {
            abort(403, 'You do not have access to this invoice.');
        }

        // Check if already paid
        if ($invoice->isPaid()) {
            return redirect()->route('my-billing.invoices.show', $invoice->id)
                ->with('info', 'This invoice has already been paid.');
        }

        // Check if PayFast is configured
        if (!$this->payFastService->isConfigured()) {
            abort(503, 'Payment gateway is not configured. Please contact support.');
        }

        // Create pending payment record
        $payment = $this->createPendingPayment($invoice);

        // Generate PayFast data
        $payFastData = $this->payFastService->generatePaymentData($invoice, $payment);
        $payFastUrl = $this->payFastService->getPaymentUrl();
        $isSandbox = $this->payFastService->isSandbox();

        // Log payment initiation for audit trail
        PaymentTransaction::create([
            'billing_payment_id' => $payment->id,
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'transaction_type' => PaymentTransaction::TYPE_INITIATE,
            'gateway' => 'payfast',
            'amount' => $invoice->total_amount,
            'status' => 'initiated',
            'ip_address' => $request->ip(),
        ]);

        return view('billing.payment.checkout', compact(
            'invoice',
            'payment',
            'payFastData',
            'payFastUrl',
            'isSandbox'
        ));
    }

    /**
     * Create a pending payment record for the invoice
     */
    protected function createPendingPayment(Invoice $invoice): BillingPayment
    {
        // Check if there's an existing pending payment
        $existingPayment = BillingPayment::where('invoice_id', $invoice->id)
            ->where('status', BillingPayment::STATUS_PENDING)
            ->first();

        if ($existingPayment) {
            return $existingPayment;
        }

        // Create new pending payment
        return BillingPayment::create([
            'invoice_id' => $invoice->id,
            'user_id' => $invoice->user_id,
            'payment_number' => BillingPayment::generatePaymentNumber(),
            'amount' => $invoice->total_amount,
            'currency' => 'ZAR',
            'payment_method' => BillingPayment::METHOD_PAYFAST,
            'status' => BillingPayment::STATUS_PENDING,
        ]);
    }

    /**
     * Handle PayFast return (success page after payment)
     * Note: This doesn't mean payment is complete - ITN confirms that
     */
    public function success(Request $request)
    {
        $user = Auth::user();
        
        // Get the most recent pending or completed payment for this user
        $payment = BillingPayment::where('user_id', $user->id)
            ->whereIn('status', [BillingPayment::STATUS_PENDING, BillingPayment::STATUS_COMPLETED])
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$payment) {
            return redirect()->route('my-billing.invoices')
                ->with('info', 'No recent payment found.');
        }

        $invoice = $payment->invoice;

        // If payment is already completed, show success
        if ($payment->status === BillingPayment::STATUS_COMPLETED) {
            return view('billing.payment.success', compact('payment', 'invoice'));
        }

        // Payment is pending - ITN will confirm it
        return view('billing.payment.pending', compact('payment', 'invoice'));
    }

    /**
     * Handle PayFast cancel (user cancelled payment)
     */
    public function cancel(Request $request)
    {
        $user = Auth::user();
        
        // Get the most recent pending payment for this user
        $payment = BillingPayment::where('user_id', $user->id)
            ->where('status', BillingPayment::STATUS_PENDING)
            ->orderBy('created_at', 'desc')
            ->first();

        $invoice = null;

        if ($payment) {
            $payment->update([
                'status' => BillingPayment::STATUS_FAILED,
                'notes' => 'Cancelled by user at payment gateway',
            ]);

            $invoice = $payment->invoice;

            Log::info('Payment cancelled by user', [
                'payment_id' => $payment->id,
                'invoice_id' => $invoice?->id,
                'user_id' => $user->id,
            ]);
        }

        return view('billing.payment.cancel', compact('payment', 'invoice'));
    }

    /**
     * Handle PayFast ITN (Instant Transaction Notification)
     * This is the webhook callback from PayFast - MUST be accessible without auth
     */
    public function notify(Request $request)
    {
        // Return 200 OK header immediately to acknowledge receipt
        // This prevents PayFast from retrying
        
        Log::info('PayFast ITN endpoint hit', [
            'ip' => $request->ip(),
            'method' => $request->method(),
            'data_count' => count($request->all()),
        ]);

        // Process the ITN
        $result = $this->payFastService->processITN($request->all());

        if ($result['success']) {
            Log::info('PayFast ITN processed successfully', [
                'message' => $result['message'] ?? 'OK',
            ]);
            return response('OK', 200);
        }

        Log::error('PayFast ITN processing failed', [
            'message' => $result['message'] ?? 'Unknown error',
        ]);
        return response($result['message'], 400);
    }

    // ==========================================
    // Super Admin Invoice Management Methods
    // ==========================================

    /**
     * List all invoices (admin)
     */
    public function adminInvoices(Request $request)
    {
        // Verify super admin or master_admin
        if (Auth::user()->type !== 'super admin' && Auth::user()->type !== 'master_admin') {
            abort(403, 'Unauthorized access.');
        }

        $perPage = $request->get('per_page', 25);

        $query = Invoice::with(['user', 'billingCycle'])
            ->withCount(['bankTransferSubmissions as pending_eft_count' => function ($query) {
                $query->where('status', 'pending');
            }]);

        // For master_admin, filter to only assigned companies
        if (Auth::user()->type === 'master_admin') {
            $assignedCompanyIds = \App\Models\MasterAdminCompany::where('master_admin_id', Auth::user()->id)
                ->pluck('company_id')
                ->toArray();
            $query->whereIn('user_id', $assignedCompanyIds);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Overdue filter
        if ($request->boolean('overdue')) {
            $query->whereIn('status', ['pending', 'overdue'])
                ->where('due_date', '<', now());
        }

        $invoices = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)->appends($request->query());

        // Get stats - filtered for master_admin
        if (Auth::user()->type === 'master_admin') {
            $assignedCompanyIds = \App\Models\MasterAdminCompany::where('master_admin_id', Auth::user()->id)
                ->pluck('company_id')
                ->toArray();
            
            $overdueCount = Invoice::whereIn('user_id', $assignedCompanyIds)
                ->where('status', 'pending')
                ->where('due_date', '<', now())
                ->count();
            
            $stats = [
                'total' => Invoice::whereIn('user_id', $assignedCompanyIds)->count(),
                'pending' => Invoice::whereIn('user_id', $assignedCompanyIds)->where('status', 'pending')->count(),
                'overdue' => $overdueCount,
                'paid_this_month' => Invoice::whereIn('user_id', $assignedCompanyIds)
                    ->where('status', 'paid')
                    ->whereMonth('paid_at', now()->month)
                    ->count(),
                'revenue_this_month' => Invoice::whereIn('user_id', $assignedCompanyIds)
                    ->where('status', 'paid')
                    ->whereMonth('paid_at', now()->month)
                    ->sum('total_amount'),
            ];
            
            $customers = \App\Models\User::whereIn('id', $assignedCompanyIds)
                ->whereHas('invoices')
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        } else {
            $overdueCount = Invoice::where('status', 'pending')
                ->where('due_date', '<', now())
                ->count();
            
            $stats = [
                'total' => Invoice::count(),
                'pending' => Invoice::where('status', 'pending')->count(),
                'overdue' => $overdueCount,
                'paid_this_month' => Invoice::where('status', 'paid')
                    ->whereMonth('paid_at', now()->month)
                    ->count(),
                'revenue_this_month' => Invoice::where('status', 'paid')
                    ->whereMonth('paid_at', now()->month)
                    ->sum('total_amount'),
            ];

            // Get list of customers who have invoices
            $customers = \App\Models\User::whereHas('invoices')
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        }

        return view('super-admin.billing.invoices.index', compact('invoices', 'stats', 'customers'));
    }

    /**
     * Show invoice details (admin)
     */
    public function adminShowInvoice($id)
    {
        // Verify super admin or master_admin
        if (!in_array(Auth::user()->type, ['super admin', 'master_admin'])) {
            abort(403, 'Unauthorized access.');
        }

        $invoice = Invoice::with(['user', 'billingCycle', 'payments', 'items'])
            ->findOrFail($id);
        
        // For master_admin, verify the invoice belongs to their assigned companies
        if (Auth::user()->type === 'master_admin') {
            $assignedCompanyIds = \App\Models\MasterAdminCompany::where('master_admin_id', Auth::user()->id)
                ->pluck('company_id')
                ->toArray();
            
            if (!in_array($invoice->user_id, $assignedCompanyIds)) {
                abort(403, 'Unauthorized access to this invoice.');
            }
        }

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
            ->orderBy('created_at', 'desc')
            ->get();

        return view('super-admin.billing.invoices.show', compact('invoice', 'company', 'eftSubmissions'));
    }

    /**
     * Download invoice PDF (admin)
     */
    public function adminDownloadInvoice($id)
    {
        // Verify super admin or master_admin
        if (!in_array(Auth::user()->type, ['super admin', 'master_admin'])) {
            abort(403, 'Unauthorized access.');
        }

        $invoice = Invoice::findOrFail($id);
        
        // For master_admin, verify the invoice belongs to their assigned companies
        if (Auth::user()->type === 'master_admin') {
            $assignedCompanyIds = \App\Models\MasterAdminCompany::where('master_admin_id', Auth::user()->id)
                ->pluck('company_id')
                ->toArray();
            
            if (!in_array($invoice->user_id, $assignedCompanyIds)) {
                abort(403, 'Unauthorized access to this invoice.');
            }
        }
        
        $pdfService = app(InvoicePdfService::class);
        
        return $pdfService->download($invoice);
    }

    /**
     * Process manual payment (admin)
     */
    public function processManualPayment(Request $request, $id)
    {
        // Verify super admin or master_admin
        if (!in_array(Auth::user()->type, ['super admin', 'master_admin'])) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'payment_reference' => 'required|string|max:255',
            'payment_method' => 'required|in:eft,cash,cheque,card,payfast,other',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $invoice = Invoice::findOrFail($id);
        
        // For master_admin, verify the invoice belongs to their assigned companies
        if (Auth::user()->type === 'master_admin') {
            $assignedCompanyIds = \App\Models\MasterAdminCompany::where('master_admin_id', Auth::user()->id)
                ->pluck('company_id')
                ->toArray();
            
            if (!in_array($invoice->user_id, $assignedCompanyIds)) {
                abort(403, 'Unauthorized access to this invoice.');
            }
        }

        if ($invoice->isPaid()) {
            return redirect()->back()
                ->with('error', 'This invoice has already been paid.');
        }

        // Map form payment methods to database enum values
        $paymentMethodMap = [
            'eft' => BillingPayment::METHOD_BANK_TRANSFER,
            'cash' => BillingPayment::METHOD_MANUAL,
            'cheque' => BillingPayment::METHOD_MANUAL,
            'card' => BillingPayment::METHOD_MANUAL,
            'payfast' => BillingPayment::METHOD_PAYFAST,
            'other' => BillingPayment::METHOD_OTHER,
        ];
        
        $dbPaymentMethod = $paymentMethodMap[$request->payment_method] ?? BillingPayment::METHOD_OTHER;

        // Create manual payment record
        $payment = BillingPayment::create([
            'invoice_id' => $invoice->id,
            'user_id' => $invoice->user_id,
            'payment_number' => BillingPayment::generatePaymentNumber(),
            'amount' => $invoice->total_amount,
            'payment_method' => $dbPaymentMethod,
            'payment_reference' => $request->payment_reference,
            'gateway_reference' => $request->payment_reference,
            'status' => BillingPayment::STATUS_COMPLETED,
            'paid_at' => $request->payment_date,
            'notes' => $request->notes ? $request->notes . ' (Original method: ' . ucfirst($request->payment_method) . ')' : 'Manual payment: ' . ucfirst($request->payment_method),
            'processed_by' => Auth::id(),
        ]);

        // Mark invoice as paid
        $invoice->update([
            'status' => 'paid',
            'paid_at' => $request->payment_date,
            'payment_reference' => $request->payment_reference,
        ]);

        // Reactivate user if suspended
        if ($invoice->user && $invoice->user->billing_status === 'suspended') {
            $invoice->user->reinstate();
        }

        // Auto-resolve any pending EFT submissions for this invoice
        \App\Models\Billing\BankTransferPayment::where('invoice_id', $invoice->id)
            ->where('status', \App\Models\Billing\BankTransferPayment::STATUS_PENDING)
            ->update([
                'status' => \App\Models\Billing\BankTransferPayment::STATUS_APPROVED,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

        return redirect()->back()
            ->with('success', 'Payment processed successfully.');
    }

    /**
     * Generate invoices for all eligible users (admin)
     */
    public function adminGenerateInvoices(Request $request)
    {
        // Verify super admin
        if (Auth::user()->type !== 'super admin') {
            abort(403, 'Unauthorized access.');
        }

        try {
            // Force generation for all active cycles with payslips
            $result = $this->invoiceService->processAllDueCycles(true);
            $invoices = $result['invoices'] ?? [];

            if (empty($invoices)) {
                return redirect()->back()
                    ->with('info', 'No billing cycles to close at this time.');
            }

            return redirect()->back()
                ->with('success', count($invoices) . ' invoice(s) generated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to generate invoices: ' . $e->getMessage());
        }
    }

    /**
     * Export invoices to CSV (admin)
     */
    public function exportInvoices(Request $request)
    {
        // Verify super admin or master_admin
        if (!in_array(Auth::user()->type, ['super admin', 'master_admin'])) {
            abort(403, 'Unauthorized access.');
        }

        $query = Invoice::with(['user', 'items']);
        
        // Filter by assigned companies for master_admin
        if (Auth::user()->type === 'master_admin') {
            $assignedCompanyIds = \App\Models\MasterAdminCompany::where('master_admin_id', Auth::user()->id)
                ->pluck('company_id')
                ->toArray();
            $query->whereIn('user_id', $assignedCompanyIds);
        }

        // Apply same filters as index
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $invoices = $query->orderBy('created_at', 'desc')->get();

        $filename = 'invoices_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($invoices) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Invoice #',
                'User',
                'Email',
                'Status',
                'Payslips',
                'Subtotal',
                'VAT',
                'Total',
                'Issue Date',
                'Due Date',
                'Paid Date',
            ]);

            foreach ($invoices as $invoice) {
                // Calculate values - use direct fields first, fall back to items calculation
                $payslips = $invoice->total_payslips;
                if (empty($payslips) && $invoice->items->count() > 0) {
                    $payslips = $invoice->items->sum('quantity');
                }
                
                $subtotal = $invoice->subtotal;
                if (empty($subtotal) && $invoice->items->count() > 0) {
                    $subtotal = $invoice->items->sum('amount');
                }
                
                $vatAmount = $invoice->tax_amount ?: 0;
                
                fputcsv($file, [
                    $invoice->invoice_number,
                    $invoice->user->name ?? 'N/A',
                    $invoice->user->email ?? 'N/A',
                    $invoice->status,
                    $payslips ?: 0,
                    number_format((float)($subtotal ?: 0), 2, '.', ''),
                    number_format((float)$vatAmount, 2, '.', ''),
                    number_format((float)$invoice->total_amount, 2, '.', ''),
                    $invoice->created_at->format('Y-m-d'),
                    $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '',
                    $invoice->paid_at ? $invoice->paid_at->format('Y-m-d') : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Send invoice reminder (admin)
     */
    public function sendReminder($id)
    {
        // Verify super admin or master_admin
        if (!in_array(Auth::user()->type, ['super admin', 'master_admin'])) {
            abort(403, 'Unauthorized access.');
        }

        $invoice = Invoice::with('user')->findOrFail($id);
        
        // For master_admin, verify the invoice belongs to their assigned companies
        if (Auth::user()->type === 'master_admin') {
            $assignedCompanyIds = \App\Models\MasterAdminCompany::where('master_admin_id', Auth::user()->id)
                ->pluck('company_id')
                ->toArray();
            
            if (!in_array($invoice->user_id, $assignedCompanyIds)) {
                abort(403, 'Unauthorized access to this invoice.');
            }
        }

        if ($invoice->isPaid()) {
            return redirect()->back()
                ->with('error', 'Cannot send reminder for a paid invoice.');
        }

        try {
            // Use admin's configured SMTP for billing communication
            setAdminConfigEmail();

            // Send payment reminder email
            \Mail::to($invoice->user->email)->send(new \App\Mail\Billing\PaymentReminder($invoice));
            
            // Update reminder count and timestamp
            $invoice->increment('reminder_count');
            $invoice->update(['last_reminder_sent_at' => now()]);
            
            \Log::info('Payment reminder sent manually', [
                'invoice_id' => $invoice->id,
                'user_email' => $invoice->user->email,
                'reminder_count' => $invoice->reminder_count,
            ]);

            return redirect()->back()
                ->with('success', 'Payment reminder sent successfully to ' . $invoice->user->email);
        } catch (\Exception $e) {
            \Log::error('Failed to send payment reminder', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to send reminder: ' . $e->getMessage());
        }
    }

    /**
     * Submit EFT/Bank Transfer proof of payment (user)
     */
    public function submitBankTransferProof(Request $request, Invoice $invoice)
    {
        $user = Auth::user();

        // Verify ownership
        if ($invoice->user_id !== $user->id) {
            abort(403, 'You do not have access to this invoice.');
        }

        // Check if already paid
        if ($invoice->isPaid()) {
            return redirect()->back()
                ->with('info', 'This invoice has already been paid.');
        }

        $request->validate([
            'bank_reference' => 'required|string|max:255',
            'payment_date' => 'required|date|before_or_equal:today',
            'amount' => 'required|numeric|min:0.01',
            'attachment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
            'notes' => 'nullable|string|max:1000',
        ]);

        // Store the attachment in S3
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = 'eft-proofs/' . $user->id . '/' . time() . '_' . $file->getClientOriginalName();
            $attachmentPath = \Storage::disk('s3')->putFileAs('', $file, $filename, 'private');
        }

        // Create EFT submission record
        $submission = \App\Models\Billing\BankTransferPayment::create([
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'bank_reference' => $request->bank_reference,
            'payment_date' => $request->payment_date,
            'amount' => $request->amount,
            'attachment' => $attachmentPath,
            'notes' => $request->notes,
            'status' => \App\Models\Billing\BankTransferPayment::STATUS_PENDING,
        ]);

        Log::info('EFT proof submitted', [
            'submission_id' => $submission->id,
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'amount' => $request->amount,
        ]);

        // Send notification to admins
        $this->notifyAdminsOfEftSubmission($submission, $invoice);

        return redirect()->back()
            ->with('success', 'Your payment proof has been submitted successfully. We will review it shortly.');
    }

    /**
     * Notify global admin and assigned master admin of EFT submission
     */
    protected function notifyAdminsOfEftSubmission($submission, $invoice)
    {
        try {
            // Get global admin (super admin)
            $globalAdmins = \App\Models\User::where('type', 'super admin')->get();
            
            // Get assigned master admin for this company
            $assignedMasterAdmins = \App\Models\User::where('type', 'master_admin')
                ->whereHas('assignedCompanies', function ($query) use ($invoice) {
                    $query->where('company_id', $invoice->user_id);
                })
                ->get();

            $allAdmins = $globalAdmins->merge($assignedMasterAdmins);

            // Use admin's configured SMTP for billing communication
            setAdminConfigEmail();

            foreach ($allAdmins as $admin) {
                \Mail::to($admin->email)->send(new \App\Mail\Billing\BankTransferSubmitted($submission, $invoice, $admin));
            }

            Log::info('EFT submission notification sent', [
                'submission_id' => $submission->id,
                'notified_admins' => $allAdmins->pluck('email')->toArray(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send EFT submission notification', [
                'submission_id' => $submission->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Review (approve/reject) EFT submission (admin)
     */
    public function reviewBankTransferProof(Request $request, $id)
    {
        // Verify super admin or master_admin
        if (!in_array(Auth::user()->type, ['super admin', 'master_admin'])) {
            abort(403, 'Unauthorized access.');
        }

        $submission = \App\Models\Billing\BankTransferPayment::with(['invoice', 'user'])->findOrFail($id);
        
        // For master_admin, verify access
        if (Auth::user()->type === 'master_admin') {
            $assignedCompanyIds = \App\Models\MasterAdminCompany::where('master_admin_id', Auth::user()->id)
                ->pluck('company_id')
                ->toArray();
            
            if (!in_array($submission->user_id, $assignedCompanyIds)) {
                abort(403, 'Unauthorized access to this submission.');
            }
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:1000',
        ]);

        $invoice = $submission->invoice;

        if ($request->action === 'approve') {
            // Create payment record
            $payment = BillingPayment::create([
                'invoice_id' => $invoice->id,
                'user_id' => $invoice->user_id,
                'payment_number' => BillingPayment::generatePaymentNumber(),
                'amount' => $submission->amount,
                'payment_method' => BillingPayment::METHOD_BANK_TRANSFER,
                'payment_reference' => $submission->bank_reference,
                'gateway_reference' => $submission->bank_reference,
                'status' => BillingPayment::STATUS_COMPLETED,
                'paid_at' => $submission->payment_date,
                'notes' => 'EFT payment verified from proof submission #' . $submission->id,
                'processed_by' => Auth::id(),
            ]);

            // Mark invoice as paid
            $invoice->update([
                'status' => 'paid',
                'paid_at' => $submission->payment_date,
                'payment_reference' => $submission->bank_reference,
            ]);

            // Update submission status
            $submission->update([
                'status' => \App\Models\Billing\BankTransferPayment::STATUS_APPROVED,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            // Reactivate user if suspended
            if ($invoice->user && $invoice->user->billing_status === 'suspended') {
                $invoice->user->reinstate();
            }

            Log::info('EFT submission approved', [
                'submission_id' => $submission->id,
                'invoice_id' => $invoice->id,
                'approved_by' => Auth::id(),
                'payment_id' => $payment->id,
            ]);

            // Notify user of approval
            try {
                setAdminConfigEmail();
                \Mail::to($submission->user->email)->send(new \App\Mail\Billing\BankTransferApproved($submission, $invoice));
            } catch (\Exception $e) {
                Log::error('Failed to send approval notification', ['error' => $e->getMessage()]);
            }

            return redirect()->back()
                ->with('success', 'Payment proof approved and invoice marked as paid.');
        } else {
            // Reject submission
            $submission->update([
                'status' => \App\Models\Billing\BankTransferPayment::STATUS_REJECTED,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'rejection_reason' => $request->rejection_reason,
            ]);

            Log::info('EFT submission rejected', [
                'submission_id' => $submission->id,
                'invoice_id' => $invoice->id,
                'rejected_by' => Auth::id(),
                'reason' => $request->rejection_reason,
            ]);

            // Notify user of rejection
            try {
                setAdminConfigEmail();
                \Mail::to($submission->user->email)->send(new \App\Mail\Billing\BankTransferRejected($submission, $invoice));
            } catch (\Exception $e) {
                Log::error('Failed to send rejection notification', ['error' => $e->getMessage()]);
            }

            return redirect()->back()
                ->with('info', 'Payment proof has been rejected. The user has been notified.');
        }
    }
}

