<?php

namespace App\Services;

use App\Mail\Billing\PaymentConfirmation;
use App\Models\Billing\BillingPayment;
use App\Models\Billing\Invoice;
use App\Models\Billing\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * PayFast Payment Gateway Service
 * 
 * Implements PayFast Custom Integration following official documentation:
 * https://developers.payfast.co.za/docs
 * 
 * Features:
 * - Secure signature generation with passphrase
 * - ITN (Instant Transaction Notification) handling with full security checks
 * - Server-side validation with PayFast
 * - IP validation for production
 * - Sandbox/Production mode support
 */
class PayFastService
{
    protected string $merchantId;
    protected string $merchantKey;
    protected string $passphrase;
    protected bool $sandbox;
    protected string $processUrl;
    protected string $validateUrl;

    /**
     * Valid PayFast IP ranges for ITN validation
     * Updated per PayFast documentation: https://developers.payfast.co.za/docs#ports-and-ip-addresses
     */
    protected array $validHosts = [
        'www.payfast.co.za',
        'sandbox.payfast.co.za',
        'w1w.payfast.co.za',
        'w2w.payfast.co.za',
    ];

    public function __construct()
    {
        $this->merchantId = config('payfast.merchant_id', '');
        $this->merchantKey = config('payfast.merchant_key', '');
        $this->passphrase = config('payfast.passphrase', '');
        $this->sandbox = config('payfast.sandbox', true);
        
        // Set URLs based on environment
        $this->processUrl = $this->sandbox 
            ? 'https://sandbox.payfast.co.za/eng/process' 
            : 'https://www.payfast.co.za/eng/process';
            
        $this->validateUrl = $this->sandbox 
            ? 'https://sandbox.payfast.co.za/eng/query/validate'
            : 'https://www.payfast.co.za/eng/query/validate';
    }

    /**
     * Generate PayFast payment form data with proper signature
     * 
     * Following PayFast documentation:
     * - Step 1: Create checkout form with all required fields
     * - Step 2: Create security signature
     * 
     * @param Invoice $invoice
     * @param BillingPayment $payment
     * @return array
     */
    public function generatePaymentData(Invoice $invoice, BillingPayment $payment): array
    {
        $user = $invoice->user;
        
        // Split name into first and last (PayFast prefers this)
        $nameParts = $this->splitName($user->name ?? 'Customer');
        
        // Build payment data in the EXACT order required by PayFast
        // Order matters for signature generation!
        // Only include fields that have values - empty fields cause signature issues
        $data = [];
        
        // Merchant details (required)
        $data['merchant_id'] = $this->merchantId;
        $data['merchant_key'] = $this->merchantKey;
        
        // Return URLs (required)
        $data['return_url'] = route('my-billing.payment.success');
        $data['cancel_url'] = route('my-billing.payment.cancel');
        
        // Use custom notify URL if set (for ngrok/tunneling), otherwise use route
        $data['notify_url'] = config('payfast.notify_url') ?: route('payfast.notify');
        
        // Buyer details - only add if not empty
        $firstName = $this->sanitize($nameParts['first']);
        $lastName = $this->sanitize($nameParts['last']);
        
        if (!empty($firstName)) {
            $data['name_first'] = $firstName;
        }
        if (!empty($lastName)) {
            $data['name_last'] = $lastName;
        }
        if (!empty($user->email)) {
            $data['email_address'] = $user->email;
        }
        
        // Transaction details (required)
        $data['m_payment_id'] = $payment->payment_number;
        $data['amount'] = $this->formatAmount($invoice->total_amount);
        $data['item_name'] = $this->sanitize('Invoice ' . $invoice->invoice_number);

        // Generate signature as final step
        $data['signature'] = $this->generateSignature($data);

        Log::info('PayFast payment data generated', [
            'invoice_id' => $invoice->id,
            'payment_id' => $payment->id,
            'amount' => $data['amount'],
            'm_payment_id' => $data['m_payment_id'],
        ]);

        return $data;
    }

    /**
     * Generate PayFast MD5 signature
     * 
     * Following PayFast specification:
     * 1. Concatenate all non-blank values with '&' in specified order
     * 2. Add passphrase at the end (if set)
     * 3. URL encode values properly
     * 4. MD5 hash the result
     * 
     * IMPORTANT: PayFast requires raw URL encoding without unnecessary encoding
     * 
     * @param array $data Payment data array
     * @param string|null $passphrase Optional passphrase override
     * @return string MD5 signature
     */
    public function generateSignature(array $data, ?string $passphrase = null): string
    {
        // Remove existing signature if present
        unset($data['signature']);
        
        // Create parameter string - PayFast specific encoding
        $pfOutput = '';
        foreach ($data as $key => $val) {
            if ($val !== '' && $val !== null) {
                // Use rawurlencode but then replace %20 with + for spaces (PayFast requirement)
                $pfOutput .= $key . '=' . urlencode(trim((string)$val)) . '&';
            }
        }
        
        // Remove last ampersand
        $getString = substr($pfOutput, 0, -1);
        
        // Add passphrase if set
        $passphraseToUse = $passphrase ?? $this->passphrase;
        if (!empty($passphraseToUse)) {
            $getString .= '&passphrase=' . urlencode(trim($passphraseToUse));
        }
        
        // Debug log the signature string
        if (config('payfast.debug')) {
            Log::debug('PayFast signature string', ['string' => $getString]);
        }

        return md5($getString);
    }

    /**
     * Process PayFast ITN (Instant Transaction Notification)
     * 
     * Following PayFast documentation Step 4: Confirm payment is successful
     * Implements all four security checks:
     * 1. Verify signature
     * 2. Verify source IP
     * 3. Compare payment data
     * 4. Perform server confirmation
     * 
     * @param array $postData POST data from PayFast
     * @return array Result with success status and message
     */
    public function processITN(array $postData): array
    {
        // Log ITN receipt
        Log::info('PayFast ITN received', [
            'ip' => request()->ip(),
            'm_payment_id' => $postData['m_payment_id'] ?? null,
            'pf_payment_id' => $postData['pf_payment_id'] ?? null,
            'payment_status' => $postData['payment_status'] ?? null,
            'amount_gross' => $postData['amount_gross'] ?? null,
        ]);

        // Strip slashes from data
        $pfData = [];
        foreach ($postData as $key => $val) {
            $pfData[$key] = stripslashes($val);
        }

        // Get signature and remove from data for verification
        $signature = $pfData['signature'] ?? '';
        
        // Build parameter string for verification (excluding signature)
        $pfParamString = $this->buildParamString($pfData);

        // Security Check 1: Verify signature
        if (!$this->pfValidSignature($pfData, $pfParamString)) {
            Log::error('PayFast ITN: Signature verification failed');
            return ['success' => false, 'message' => 'Signature verification failed'];
        }

        // Security Check 2: Verify source IP
        if (!$this->pfValidIP()) {
            Log::error('PayFast ITN: Invalid source IP', ['ip' => request()->ip()]);
            return ['success' => false, 'message' => 'Invalid source IP'];
        }

        // Get payment record using m_payment_id (payment_number)
        $paymentNumber = $pfData['m_payment_id'] ?? null;
        if (!$paymentNumber) {
            Log::error('PayFast ITN: Payment number not found in callback');
            return ['success' => false, 'message' => 'Payment ID not found'];
        }

        $payment = BillingPayment::where('payment_number', $paymentNumber)->first();
        if (!$payment) {
            Log::error('PayFast ITN: Payment record not found', ['payment_number' => $paymentNumber]);
            return ['success' => false, 'message' => 'Payment not found'];
        }

        // Security Check 3: Compare payment data (amount)
        $cartTotal = $payment->amount;
        if (!$this->pfValidPaymentData($cartTotal, $pfData)) {
            Log::error('PayFast ITN: Amount mismatch', [
                'expected' => $cartTotal,
                'received' => $pfData['amount_gross'] ?? 0,
            ]);
            return ['success' => false, 'message' => 'Amount verification failed'];
        }

        // Security Check 4: Server confirmation
        $pfHost = $this->sandbox ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
        if (!$this->pfValidServerConfirmation($pfParamString, $pfHost)) {
            Log::error('PayFast ITN: Server confirmation failed');
            return ['success' => false, 'message' => 'Server confirmation failed'];
        }

        // All checks passed - log transaction
        PaymentTransaction::create([
            'billing_payment_id' => $payment->id,
            'invoice_id' => $payment->invoice_id,
            'user_id' => $payment->user_id ?? $payment->invoice->user_id ?? null,
            'transaction_type' => PaymentTransaction::TYPE_ITN_RECEIVED,
            'gateway' => 'payfast',
            'gateway_transaction_id' => $pfData['pf_payment_id'] ?? null,
            'gateway_reference' => $pfData['pf_payment_id'] ?? null,
            'amount' => $pfData['amount_gross'] ?? 0,
            'status' => $pfData['payment_status'] ?? 'unknown',
            'payment_status' => $pfData['payment_status'] ?? null,
            'response_data' => $pfData,
            'ip_address' => request()->ip(),
            'signature' => $pfData['signature'] ?? null,
            'signature_valid' => true,
        ]);

        // Process based on payment status
        return $this->handlePaymentStatus($payment, $pfData);
    }

    /**
     * Build parameter string from ITN data
     */
    protected function buildParamString(array $pfData): string
    {
        $pfParamString = '';
        foreach ($pfData as $key => $val) {
            if ($key !== 'signature') {
                $pfParamString .= $key . '=' . urlencode($val) . '&';
            } else {
                break; // Signature is always last, stop here
            }
        }
        return substr($pfParamString, 0, -1);
    }

    /**
     * Security Check 1: Verify signature
     */
    protected function pfValidSignature(array $pfData, string $pfParamString): bool
    {
        // Calculate security signature
        $tempParamString = $pfParamString;
        if (!empty($this->passphrase)) {
            $tempParamString .= '&passphrase=' . urlencode($this->passphrase);
        }

        $signature = md5($tempParamString);
        return ($pfData['signature'] ?? '') === $signature;
    }

    /**
     * Security Check 2: Verify source IP
     * Validates that the ITN came from a valid PayFast server
     */
    protected function pfValidIP(): bool
    {
        // In sandbox mode, allow any IP for testing
        if ($this->sandbox) {
            return true;
        }

        // Get all valid IPs from PayFast hostnames
        $validIps = [];
        foreach ($this->validHosts as $pfHostname) {
            $ips = gethostbynamel($pfHostname);
            if ($ips !== false) {
                $validIps = array_merge($validIps, $ips);
            }
        }
        
        // Add known static IPs from PayFast documentation
        $staticIps = [
            // 197.97.145.144/28
            '197.97.145.144', '197.97.145.145', '197.97.145.146', '197.97.145.147',
            '197.97.145.148', '197.97.145.149', '197.97.145.150', '197.97.145.151',
            '197.97.145.152', '197.97.145.153', '197.97.145.154', '197.97.145.155',
            '197.97.145.156', '197.97.145.157', '197.97.145.158', '197.97.145.159',
            // 41.74.179.192/27
            '41.74.179.192', '41.74.179.193', '41.74.179.194', '41.74.179.195',
            '41.74.179.196', '41.74.179.197', '41.74.179.198', '41.74.179.199',
            '41.74.179.200', '41.74.179.201', '41.74.179.202', '41.74.179.203',
            '41.74.179.204', '41.74.179.205', '41.74.179.206', '41.74.179.207',
            '41.74.179.208', '41.74.179.209', '41.74.179.210', '41.74.179.211',
            '41.74.179.212', '41.74.179.213', '41.74.179.214', '41.74.179.215',
            '41.74.179.216', '41.74.179.217', '41.74.179.218', '41.74.179.219',
            '41.74.179.220', '41.74.179.221', '41.74.179.222', '41.74.179.223',
            // 102.216.36.0/28
            '102.216.36.0', '102.216.36.1', '102.216.36.2', '102.216.36.3',
            '102.216.36.4', '102.216.36.5', '102.216.36.6', '102.216.36.7',
            '102.216.36.8', '102.216.36.9', '102.216.36.10', '102.216.36.11',
            '102.216.36.12', '102.216.36.13', '102.216.36.14', '102.216.36.15',
            // 102.216.36.128/28
            '102.216.36.128', '102.216.36.129', '102.216.36.130', '102.216.36.131',
            '102.216.36.132', '102.216.36.133', '102.216.36.134', '102.216.36.135',
            '102.216.36.136', '102.216.36.137', '102.216.36.138', '102.216.36.139',
            '102.216.36.140', '102.216.36.141', '102.216.36.142', '102.216.36.143',
            // Additional single IP
            '144.126.193.139',
        ];
        
        $validIps = array_unique(array_merge($validIps, $staticIps));

        $clientIp = request()->ip();
        
        // Check if IP is in valid list
        return in_array($clientIp, $validIps, true);
    }

    /**
     * Security Check 3: Compare payment data
     * Verify the amount matches what we expected
     */
    protected function pfValidPaymentData(float $cartTotal, array $pfData): bool
    {
        $amountGross = (float) ($pfData['amount_gross'] ?? 0);
        return !(abs($cartTotal - $amountGross) > 0.01);
    }

    /**
     * Security Check 4: Perform server confirmation
     * Validates the transaction directly with PayFast servers
     */
    protected function pfValidServerConfirmation(string $pfParamString, string $pfHost): bool
    {
        $url = 'https://' . $pfHost . '/eng/query/validate';

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ])
                ->withBody($pfParamString, 'application/x-www-form-urlencoded')
                ->post($url);

            $result = trim($response->body());
            
            Log::info('PayFast server confirmation response', [
                'url' => $url,
                'response' => $result,
            ]);

            return $result === 'VALID';
        } catch (\Exception $e) {
            Log::error('PayFast server confirmation error', [
                'error' => $e->getMessage(),
                'url' => $url,
            ]);
            return false;
        }
    }

    /**
     * Handle payment status from ITN
     */
    protected function handlePaymentStatus(BillingPayment $payment, array $pfData): array
    {
        $paymentStatus = $pfData['payment_status'] ?? '';
        
        switch ($paymentStatus) {
            case 'COMPLETE':
                $this->processSuccessfulPayment($payment, $pfData);
                return ['success' => true, 'message' => 'Payment completed'];

            case 'FAILED':
                $this->processFailedPayment($payment, 'Payment failed at gateway', $pfData);
                return ['success' => true, 'message' => 'Payment failed - recorded'];

            case 'PENDING':
                $payment->update([
                    'gateway_status' => $paymentStatus,
                    'gateway_response' => $pfData,
                    'notes' => 'Payment pending - awaiting confirmation',
                ]);
                return ['success' => true, 'message' => 'Payment pending'];

            case 'CANCELLED':
                $this->processFailedPayment($payment, 'Payment cancelled by user', $pfData);
                return ['success' => true, 'message' => 'Payment cancelled - recorded'];

            default:
                Log::warning('PayFast ITN: Unknown payment status', ['status' => $paymentStatus]);
                $payment->update([
                    'gateway_status' => $paymentStatus,
                    'gateway_response' => $pfData,
                ]);
                return ['success' => false, 'message' => 'Unknown payment status: ' . $paymentStatus];
        }
    }

    /**
     * Process successful payment
     */
    protected function processSuccessfulPayment(BillingPayment $payment, array $pfData): void
    {
        $payment->update([
            'status' => BillingPayment::STATUS_COMPLETED,
            'gateway_reference' => $pfData['pf_payment_id'] ?? null,
            'gateway_status' => 'COMPLETE',
            'gateway_response' => $pfData,
            'paid_at' => now(),
            'notes' => 'Payment completed via PayFast',
        ]);

        // Mark invoice as paid
        $invoice = $payment->invoice;
        if ($invoice) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_reference' => $pfData['pf_payment_id'] ?? $payment->payment_number,
            ]);

            // Reactivate user if suspended due to non-payment
            $user = $invoice->user;
            if ($user && $user->billing_status === 'suspended') {
                $user->reinstate();
                
                Log::info('User reactivated after payment', ['user_id' => $user->id]);
            }
        }

        Log::info('PayFast payment completed', [
            'payment_id' => $payment->id,
            'pf_payment_id' => $pfData['pf_payment_id'] ?? null,
            'amount' => $pfData['amount_gross'] ?? null,
        ]);

        // Send payment confirmation email to user
        try {
            if ($invoice && $invoice->user) {
                if (function_exists('setAdminConfigEmail')) {
                    setAdminConfigEmail();
                }
                Mail::to($invoice->user->email)->send(new PaymentConfirmation($invoice, $payment));
                Log::info('Payment confirmation email sent', ['user_email' => $invoice->user->email]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation email', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Process failed payment
     */
    protected function processFailedPayment(BillingPayment $payment, string $reason, array $pfData): void
    {
        $payment->update([
            'status' => BillingPayment::STATUS_FAILED,
            'gateway_status' => $pfData['payment_status'] ?? 'FAILED',
            'gateway_response' => $pfData,
            'notes' => $reason,
        ]);

        Log::warning('PayFast payment failed', [
            'payment_id' => $payment->id,
            'reason' => $reason,
            'pf_payment_id' => $pfData['pf_payment_id'] ?? null,
        ]);
    }

    /**
     * Get PayFast payment URL
     */
    public function getPaymentUrl(): string
    {
        return $this->processUrl;
    }

    /**
     * Check if PayFast is properly configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->merchantId) 
            && !empty($this->merchantKey)
            && $this->merchantId !== 'your_merchant_id'
            && $this->merchantKey !== 'your_merchant_key';
    }

    /**
     * Check if in sandbox mode
     */
    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    /**
     * Get merchant ID (for debugging)
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * Format amount for PayFast (2 decimal places, no thousands separator)
     */
    protected function formatAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }

    /**
     * Sanitize string for PayFast
     * Removes non-ASCII characters and limits length
     */
    protected function sanitize(string $value): string
    {
        // Remove any non-ASCII characters
        $value = preg_replace('/[^\x20-\x7E]/', '', $value);
        // Limit to 100 characters (PayFast limit)
        return substr(trim($value), 0, 100);
    }

    /**
     * Split full name into first and last name
     */
    protected function splitName(string $fullName): array
    {
        $parts = explode(' ', trim($fullName), 2);
        return [
            'first' => $parts[0] ?? '',
            'last' => $parts[1] ?? '',
        ];
    }

    /**
     * Get invoice description for PayFast
     */
    protected function getInvoiceDescription(Invoice $invoice): string
    {
        $description = 'Billing';
        
        if ($invoice->billingCycle) {
            $description .= ' for ' . $invoice->billingCycle->period_label;
        } elseif ($invoice->period_label) {
            $description .= ' for ' . $invoice->period_label;
        }
        
        if ($invoice->total_payslips) {
            $description .= ' (' . $invoice->total_payslips . ' payslips)';
        }
        
        return $description;
    }
}
