@extends('layouts.main')

@section('page-title')
    {{ __('Create Self-Service Settings') }}
@endsection

@section('page-breadcrumb')
    {{ __('Self-Service Settings') }}, {{ __('Create') }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="mb-3">Create Self-Service General Settings</h4>

            <form method="POST" action="{{ route('general-self-service-settings.store') }}">
                @csrf

                <!-- Auto-enable -->
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="auto_enable" name="auto_enable">
                    <label class="form-check-label" for="auto_enable">
                        Auto-enable Self-Service
                        <span class="badge bg-success ms-2">NEW</span>
                        <small class="text-muted d-block">When an email address is provided for an employee</small>
                    </label>
                </div>

                <!-- Attach payslips -->
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="attach_payslips" name="attach_payslips">
                    <label class="form-check-label" for="attach_payslips">
                        Attach payslips to emails on Self-Service release
                    </label>
                </div>
               <!-- Password protection (initially hidden) -->
<div class="form-check mb-3 ms-4 d-none">
    <input class="form-check-input" type="checkbox" id="enable_password_protection" name="enable_password_protection">
    <label class="form-check-label" for="enable_password_protection">
        Enable password protection for attached payslips
        <small class="text-muted d-block">(using employee identity numbers or birthdates)</small>
    </label>
</div>

                <!-- Allow tax certificates -->
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="allow_tax_certificates" name="allow_tax_certificates">
                    <label class="form-check-label" for="allow_tax_certificates">
                        Allow tax certificates to be released to Self-Service
                    </label>
                </div>
                <!-- Attach certificates (initially hidden) -->
<div class="form-check mb-4 ms-4 d-none">
    <input class="form-check-input" type="checkbox" id="attach_certificates" name="attach_certificates">
    <label class="form-check-label" for="attach_certificates">
        Attach certificates when notifying employees of release via email
    </label>
</div>

                <!-- Request Types -->
                <h6 class="text-primary">Self-Service Request Types</h6>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="disable_leave_requests" name="disable_leave_requests">
                    <label class="form-check-label" for="disable_leave_requests">
                        Disable Leave requests?
                    </label>
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="disable_info_requests" name="disable_info_requests">
                    <label class="form-check-label" for="disable_info_requests">
                        Disable Info Update requests?
                    </label>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-end">
                    <a href="{{ route('general-self-service-settings') }}" class="btn btn-rc-outline me-2">Cancel</a>
                    <button type="submit" class="btn btn-rc-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const attachPayslips = document.getElementById('attach_payslips');
        const passwordProtection = document.getElementById('enable_password_protection');

        const allowTaxCertificates = document.getElementById('allow_tax_certificates');
        const attachCertificates = document.getElementById('attach_certificates');

        const passwordProtectionWrapper = passwordProtection.closest('.form-check');
        const attachCertificatesWrapper = attachCertificates.closest('.form-check');

        // Initial state
        toggleVisibility(passwordProtectionWrapper, attachPayslips.checked);
        toggleVisibility(attachCertificatesWrapper, allowTaxCertificates.checked);

        attachPayslips.addEventListener('change', function () {
            toggleVisibility(passwordProtectionWrapper, this.checked);
            if (!this.checked) {
                passwordProtection.checked = false;
            }
        });

        allowTaxCertificates.addEventListener('change', function () {
            toggleVisibility(attachCertificatesWrapper, this.checked);
            if (!this.checked) {
                attachCertificates.checked = false;
            }
        });

        function toggleVisibility(element, show) {
            if (show) {
                element.classList.remove('d-none');
            } else {
                element.classList.add('d-none');
            }
        }
    });
</script>
@endpush
