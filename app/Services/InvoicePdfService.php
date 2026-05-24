<?php

namespace App\Services;

use App\Models\Billing\Invoice;
use App\Models\Billing\BillingSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class InvoicePdfService
{
    /**
     * Generate PDF for an invoice
     */
    public function generate(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->load(['user', 'billingCycle', 'items']);
        
        $data = $this->prepareInvoiceData($invoice);
        
        $pdf = Pdf::loadView('pdf.invoice', $data);
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf;
    }

    /**
     * Generate PDF and save to S3
     * Stores the filename in the invoice record
     * 
     * @param Invoice $invoice
     * @return string|null The filename (for storing in database)
     */
    public function generateAndSave(Invoice $invoice): ?string
    {
        try {
            $pdf = $this->generate($invoice);
            
            // Generate unique filename with invoice number for readability
            $filename = 'INV-' . $invoice->invoice_number . '-' . bin2hex(random_bytes(8)) . '.pdf';
            
            // Upload to S3
            $result = uploadContentToS3(
                $pdf->output(),
                S3_FOLDER_INVOICES,
                $filename,
                'application/pdf'
            );
            
            if ($result['flag'] == 1) {
                // Store only the filename in database
                $invoice->pdf_filename = $filename;
                $invoice->save();
                
                return $filename;
            }
            
            Log::error('Failed to upload invoice to S3', [
                'invoice_id' => $invoice->id,
                'error' => $result['msg']
            ]);
            return null;
            
        } catch (\Exception $e) {
            Log::error('Failed to generate invoice PDF', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get signed URL for an invoice stored in S3
     * 
     * @param Invoice $invoice
     * @param int $expiresIn Expiration in seconds (default 5 minutes)
     * @return string|null The signed URL
     */
    public function getSignedUrl(Invoice $invoice, int $expiresIn = 300): ?string
    {
        if (empty($invoice->pdf_filename)) {
            return null;
        }
        
        return getS3SignedUrl($invoice->pdf_filename, S3_FOLDER_INVOICES, $expiresIn);
    }

    /**
     * Stream PDF for download (generates on-the-fly)
     */
    public function download(Invoice $invoice)
    {
        $pdf = $this->generate($invoice);
        $filename = $this->getFilename($invoice);
        
        return $pdf->download($filename);
    }

    /**
     * Stream PDF in browser (generates on-the-fly)
     */
    public function stream(Invoice $invoice)
    {
        $pdf = $this->generate($invoice);
        $filename = $this->getFilename($invoice);
        
        return $pdf->stream($filename);
    }

    /**
     * Prepare data for the invoice template
     */
    protected function prepareInvoiceData(Invoice $invoice): array
    {
        $settings = BillingSetting::first();
        
        // Calculate tier breakdown if we have line items
        $tierBreakdown = $this->calculateTierBreakdown($invoice);
        
        return [
            'invoice' => $invoice,
            'user' => $invoice->user,
            'billingCycle' => $invoice->billingCycle,
            'lineItems' => $invoice->items ?? collect(),
            'tierBreakdown' => $tierBreakdown,
            'company' => [
                'name' => BillingSetting::get('company_name') ?: (adminSetting('company_name') ?? 'Reliance Corporation (Pty) Ltd'),
                'address' => BillingSetting::get('company_address') ?: (adminSetting('company_address') ?? ''),
                'phone' => BillingSetting::get('company_phone') ?: (adminSetting('company_phone') ?? ''),
                'email' => BillingSetting::get('company_email') ?: (adminSetting('company_email') ?? 'billing@reliancecorp.co.za'),
                'vat_number' => BillingSetting::get('company_vat_number') ?: (adminSetting('company_vat_number') ?? ''),
                'registration_number' => BillingSetting::get('company_registration') ?: (adminSetting('company_registration') ?? ''),
                'bank_name' => BillingSetting::get('bank_name') ?: (adminSetting('bank_name') ?? 'First National Bank'),
                'bank_account_name' => BillingSetting::get('bank_account_name') ?: (adminSetting('bank_account_name') ?? 'Reliance Corporation (Pty) Ltd'),
                'bank_account_number' => BillingSetting::get('bank_account_number') ?: (adminSetting('bank_account_number') ?? ''),
                'bank_branch_code' => BillingSetting::get('bank_branch_code') ?: (adminSetting('bank_branch_code') ?? ''),
                'bank_reference' => $invoice->invoice_number,
            ],
            'settings' => $settings,
        ];
    }

    /**
     * Calculate tier breakdown for display
     */
    protected function calculateTierBreakdown(Invoice $invoice): array
    {
        $breakdown = [];
        
        // If we have usage data stored, parse it
        if ($invoice->billingCycle && $invoice->billingCycle->tier_breakdown) {
            $tierData = is_array($invoice->billingCycle->tier_breakdown) 
                ? $invoice->billingCycle->tier_breakdown 
                : json_decode($invoice->billingCycle->tier_breakdown, true);
            
            if (is_array($tierData)) {
                foreach ($tierData as $tier) {
                    $breakdown[] = [
                        'tier_name' => $tier['tier_name'] ?? 'Tier',
                        'quantity' => $tier['quantity'] ?? 0,
                        'rate' => $tier['rate'] ?? 0,
                        'amount' => $tier['amount'] ?? 0,
                    ];
                }
            }
        }
        
        // If no breakdown, create a simple one
        if (empty($breakdown) && $invoice->payslip_count > 0) {
            $avgRate = $invoice->payslip_count > 0 
                ? $invoice->subtotal / $invoice->payslip_count 
                : 0;
            
            $breakdown[] = [
                'tier_name' => 'Payslip Processing',
                'quantity' => $invoice->payslip_count,
                'rate' => $avgRate,
                'amount' => $invoice->subtotal,
            ];
        }
        
        return $breakdown;
    }

    /**
     * Get filename for the invoice PDF
     */
    protected function getFilename(Invoice $invoice): string
    {
        return 'Invoice-' . $invoice->invoice_number . '.pdf';
    }
}
