<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PayFast Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your PayFast payment gateway credentials here.
    | Get your credentials from https://www.payfast.co.za
    |
    | For sandbox testing, use:
    | - Merchant ID: 10000100
    | - Merchant Key: 46f0cd694581a
    | - Passphrase: jt7NOE43FZPn
    |
    | Sandbox buyer credentials:
    | - Username: sbtu01@payfast.io
    | - Password: clientpass
    |
    */

    'merchant_id' => env('PAYFAST_MERCHANT_ID', ''),
    'merchant_key' => env('PAYFAST_MERCHANT_KEY', ''),
    'passphrase' => env('PAYFAST_PASSPHRASE', ''),
    
    /*
    |--------------------------------------------------------------------------
    | Sandbox Mode
    |--------------------------------------------------------------------------
    |
    | Set to true for testing with PayFast sandbox.
    | Set to false for production.
    |
    | Sandbox URL: https://sandbox.payfast.co.za
    | Live URL: https://www.payfast.co.za
    |
    */
    'sandbox' => env('PAYFAST_SANDBOX', true),
    
    /*
    |--------------------------------------------------------------------------
    | PayFast URLs
    |--------------------------------------------------------------------------
    |
    | These are the PayFast API endpoints.
    | Do not change unless PayFast updates their URLs.
    |
    */
    'url' => env('PAYFAST_SANDBOX', true) 
        ? 'https://sandbox.payfast.co.za/eng/process'
        : 'https://www.payfast.co.za/eng/process',
        
    'validate_url' => env('PAYFAST_SANDBOX', true)
        ? 'https://sandbox.payfast.co.za/eng/query/validate'
        : 'https://www.payfast.co.za/eng/query/validate',
        
    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | Enable detailed logging of PayFast transactions.
    | Useful for debugging integration issues.
    |
    */
    'debug' => env('PAYFAST_DEBUG', false),
    
    /*
    |--------------------------------------------------------------------------
    | Custom Notify URL (for local development with ngrok)
    |--------------------------------------------------------------------------
    |
    | Override the ITN notify URL for local testing with ngrok or similar.
    | Leave empty to use the default route.
    | Example: https://your-subdomain.ngrok-free.dev/payfast/notify
    |
    */
    'notify_url' => env('PAYFAST_NOTIFY_URL', ''),
];
