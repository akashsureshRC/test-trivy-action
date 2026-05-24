<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use \App\Models\Concerns\BelongsToWorkspace;

    protected $table = 'invoice_items';

    protected $fillable = [
        'invoice_id',
        'workspace_id',
        'workspace_name',
        'description',
        'quantity',
        'unit_price',
        'amount',
        'tier_name',
        'tier_sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'tier_sort_order' => 'integer',
    ];

    /**
     * Get the invoice this item belongs to
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return BillingSetting::getCurrencySymbol() . number_format($this->amount, 2);
    }

    /**
     * Get formatted unit price
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return BillingSetting::getCurrencySymbol() . number_format($this->unit_price, 2);
    }
}
