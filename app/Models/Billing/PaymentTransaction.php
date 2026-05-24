<?php

namespace App\Models\Billing;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $table = 'payment_transactions';

    protected $fillable = [
        'billing_payment_id',
        'invoice_id',
        'user_id',
        'transaction_type',
        'gateway',
        'gateway_transaction_id',
        'gateway_reference',
        'amount',
        'status',
        'payment_status',
        'request_data',
        'response_data',
        'ip_address',
        'signature',
        'signature_valid',
        'error_message',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'request_data' => 'array',
        'response_data' => 'array',
        'signature_valid' => 'boolean',
    ];

    // Transaction types
    const TYPE_INITIATE = 'initiate';
    const TYPE_ITN_RECEIVED = 'itn_received';
    const TYPE_VERIFY = 'verify';
    const TYPE_COMPLETE = 'complete';
    const TYPE_FAIL = 'fail';
    const TYPE_CANCEL = 'cancel';

    /**
     * Get the payment this transaction belongs to
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(BillingPayment::class, 'billing_payment_id');
    }

    /**
     * Get the invoice this transaction is for
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log a transaction
     */
    public static function log(
        int $userId,
        string $type,
        array $data = []
    ): self {
        return self::create([
            'user_id' => $userId,
            'transaction_type' => $type,
            'gateway' => $data['gateway'] ?? 'payfast',
            'billing_payment_id' => $data['billing_payment_id'] ?? null,
            'invoice_id' => $data['invoice_id'] ?? null,
            'gateway_transaction_id' => $data['gateway_transaction_id'] ?? null,
            'gateway_reference' => $data['gateway_reference'] ?? null,
            'amount' => $data['amount'] ?? null,
            'status' => $data['status'] ?? null,
            'payment_status' => $data['payment_status'] ?? null,
            'request_data' => $data['request_data'] ?? null,
            'response_data' => $data['response_data'] ?? null,
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'signature' => $data['signature'] ?? null,
            'signature_valid' => $data['signature_valid'] ?? null,
            'error_message' => $data['error_message'] ?? null,
        ]);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        if (!$this->amount) {
            return '-';
        }
        return BillingSetting::getCurrencySymbol() . number_format($this->amount, 2);
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->transaction_type) {
            self::TYPE_INITIATE => 'Payment Initiated',
            self::TYPE_ITN_RECEIVED => 'ITN Received',
            self::TYPE_VERIFY => 'Verification',
            self::TYPE_COMPLETE => 'Completed',
            self::TYPE_FAIL => 'Failed',
            self::TYPE_CANCEL => 'Cancelled',
            default => ucfirst($this->transaction_type),
        };
    }
}
