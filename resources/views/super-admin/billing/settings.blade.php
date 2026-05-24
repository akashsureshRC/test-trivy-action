@extends('layouts.main')

@section('page-title')
{{ __('Billing Settings') }}
@endsection

@section('page-breadcrumb')
{{ __('Billing') }}, {{ __('Settings') }}
@endsection

@section('content')

<div class="row">
    <div class="col-sm-12">
        {{ Form::open(['route' => 'billing.settings.update', 'method' => 'POST']) }}
        <div class="row">
            <!-- General Settings -->
            <div class="col-lg-6 d-flex">
                <div class="card w-100">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('General Settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">{{ __('Invoice Due Days') }}</label>
                            <input type="number" name="invoice_due_days" class="form-control" value="{{ $settings['invoice_due_days'] ?? 7 }}" min="1" max="90">
                            <small class="text-muted">{{ __('Days until invoice is due after generation') }}</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">{{ __('Grace Period (Days)') }}</label>
                            <input type="number" name="grace_period_days" class="form-control" value="{{ $settings['grace_period_days'] ?? 7 }}" min="1" max="90">
                            <small class="text-muted">{{ __('Days after due date before account suspension') }}</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">{{ __('Invoice Prefix') }}</label>
                            <input type="text" name="invoice_prefix" class="form-control" value="{{ $settings['invoice_prefix'] ?? 'INV' }}" maxlength="10">
                            <small class="text-muted">{{ __('Prefix for invoice numbers (e.g., INV-2025-00001)') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trial & Tax Settings -->
            <div class="col-lg-6 d-flex">
                <div class="card w-100">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('Trial & Tax Settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">{{ __('Trial Period (Days)') }}</label>
                            <input type="number" name="trial_days" class="form-control" value="{{ $settings['trial_days'] ?? 30 }}" min="0" max="365">
                            <small class="text-muted">{{ __('Free trial period for new customers (0 to disable)') }}</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">{{ __('Trial Payslips Limit') }}</label>
                            <input type="number" name="trial_payslips_limit" class="form-control" value="{{ $settings['trial_payslips_limit'] ?? 10 }}" min="0" max="1000">
                            <small class="text-muted">{{ __('Free payslips during trial period') }}</small>
                        </div>

                        <hr class="my-4">

                        <div class="form-group mb-4">
                            <label class="form-label">{{ __('Enable VAT/Tax') }}</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="tax_enabled" id="tax_enabled" value="true" {{ ($settings['tax_enabled'] ?? 'true') == 'true' ? 'checked' : '' }}>
                                <label class="form-check-label" for="tax_enabled">{{ __('Add VAT to invoices') }}</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">{{ __('Tax Percentage') }}</label>
                            <div class="input-group">
                                <input type="number" name="tax_percentage" class="form-control" value="{{ $settings['tax_percentage'] ?? 15 }}" min="0" max="100" step="0.01">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">{{ __('VAT percentage (South Africa: 15%)') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Company Details -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('Company Details (for Invoices)') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Company Name') }}</label>
                                    <input type="text" name="company_name" class="form-control" value="{{ $settings['company_name'] ?? '' }}" placeholder="Reliance Corporation (Pty) Ltd">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Company Email') }}</label>
                                    <input type="email" name="company_email" class="form-control" value="{{ $settings['company_email'] ?? '' }}" placeholder="billing@company.co.za">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Company Phone') }}</label>
                                    <input type="text" name="company_phone" class="form-control" value="{{ $settings['company_phone'] ?? '' }}" placeholder="+27 21 123 4567">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('VAT Number') }}</label>
                                    <input type="text" name="company_vat_number" class="form-control" value="{{ $settings['company_vat_number'] ?? '' }}" placeholder="4123456789">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Company Address') }}</label>
                                    <textarea name="company_address" class="form-control" rows="2" placeholder="123 Main Street, Cape Town, 8001">{{ $settings['company_address'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank Details -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('Bank Details (for Invoices)') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Bank Name') }}</label>
                                    <input type="text" name="bank_name" class="form-control" value="{{ $settings['bank_name'] ?? '' }}" placeholder="First National Bank">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Account Name') }}</label>
                                    <input type="text" name="bank_account_name" class="form-control" value="{{ $settings['bank_account_name'] ?? '' }}" placeholder="Company Name (Pty) Ltd">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Account Number') }}</label>
                                    <input type="text" name="bank_account_number" class="form-control" value="{{ $settings['bank_account_number'] ?? '' }}" placeholder="62123456789">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Branch Code') }}</label>
                                    <input type="text" name="bank_branch_code" class="form-control" value="{{ $settings['bank_branch_code'] ?? '' }}" placeholder="250655">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-rc-primary">
                                <i class="ti ti-device-floppy me-1"></i>{{ __('Save Settings') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{ Form::close() }}
@endsection