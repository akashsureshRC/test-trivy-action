@extends('layouts.main')

@section('page-title')
    {{ __('Add Extra Pay ') }}
@endsection

@section('page-breadcrumb')
    {{ __('extra-pay') }}, {{ __('create') }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="mb-3">Self-Service General Settings</h4>

            <!-- Tabs (if needed in future) -->
            {{--<ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link active" href="#">Settings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Employee Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Approval Groups</a>
                </li>
            </ul>--}}

            <form method="POST" action="{{ route('general-self-service-settings.update') }}">
                @csrf
                @method('put')
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
                    <input class="form-check-input" type="checkbox" id="attach_payslips" name="attach_payslips" checked>
                    <label class="form-check-label" for="attach_payslips">
                        Attach payslips to emails on Self-Service release
                    </label>
                </div>
                <div class="form-check mb-3 ms-4">
                    <input class="form-check-input" type="checkbox" id="enable_password_protection" name="enable_password_protection" checked>
                    <label class="form-check-label" for="enable_password_protection">
                        Enable password protection for attached payslips
                        <small class="text-muted d-block">(using employee identity numbers or birthdates)</small>
                    </label>
                </div>

                <!-- Allow tax certificates -->
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="allow_tax_certificates" name="allow_tax_certificates" checked>
                    <label class="form-check-label" for="allow_tax_certificates">
                        Allow tax certificates to be released to Self-Service
                    </label>
                </div>
                <div class="form-check mb-4 ms-4">
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
                    <button type="reset" class="btn btn-rc-outline me-2">Cancel</button>
                    <button type="submit" class="btn btn-rc-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection