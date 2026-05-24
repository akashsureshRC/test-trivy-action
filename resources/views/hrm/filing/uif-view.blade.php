@extends('layouts.main')
@section('page-title')
{{ __('Monthly Submission') }}
@endsection
@section('page-breadcrumb')
{{ __('UIF Declaration View') }}
@endsection

@section('page-action')
<div>
    <a href="{{ route('monthly-submission.index') }}" class="btn btn-rc-outline btn-sm">
        <i class="ti ti-arrow-left me-1"></i> {{ __('Back to Monthly Submissions') }}
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="filing-accordion-item">
            <div class="filing-accordion-body">

                {{-- Title --}}
                <div class="filing-section-header" style="margin-bottom: var(--rc-space-5);">
                    <h6 class="filing-section-title">
                        UIF Declaration Details - {{ isset($month) ? formatMonthYear($month) : 'Current Month' }}
                    </h6>
                </div>

                @if(isset($uifData))
                {{-- Summary Rows --}}
                <table class="filing-detail-table" style="margin-bottom: var(--rc-space-5);">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Description</th>
                            <th style="width: 25%;">Value</th>
                            <th style="width: 25%;">Description</th>
                            <th style="width: 25%; text-align: right;">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Total UIF Liability</td>
                            <td style="color: var(--rc-primary); font-weight: var(--rc-font-semibold);">R {{ number_format($uifData['total_uif_liability'], 2) }}</td>
                            <td>Employee Contribution</td>
                            <td style="text-align: right; font-weight: var(--rc-font-bold);">R {{ number_format($uifData['employee_contribution'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>Employer Contribution</td>
                            <td style="color: var(--rc-primary); font-weight: var(--rc-font-semibold);">R {{ number_format($uifData['employer_contribution'], 2) }}</td>
                            <td>Total Employees</td>
                            <td style="text-align: right; font-weight: var(--rc-font-bold);">{{ isset($payslips) ? $payslips->count() : 0 }}</td>
                        </tr>
                        <tr>
                            <td>Total Remuneration</td>
                            <td style="color: var(--rc-primary); font-weight: var(--rc-font-semibold);">R {{ number_format($uifData['total_remuneration'], 2) }}</td>
                            <td>UIF Rate</td>
                            <td style="text-align: right; font-weight: var(--rc-font-bold);">1.0%</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 2px solid var(--rc-border);">
                            <td colspan="3" style="font-weight: var(--rc-font-bold); padding: var(--rc-space-4);">Total UIF Payable</td>
                            <td style="text-align: right; font-weight: var(--rc-font-bold); font-size: var(--rc-font-lg); color: var(--rc-primary); padding: var(--rc-space-4);">R {{ number_format($uifData['total_uif_liability'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
                @else
                <div class="filing-alert" style="margin-bottom: var(--rc-space-5);">
                    <span><i class="ti ti-alert-triangle"></i> No UIF data available. Please ensure payslips are processed through payrun for this month.</span>
                </div>
                @endif

                {{-- Status & Breakdown Row --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center py-3" style="border-bottom: 1px solid var(--rc-border-light);">
                            <span style="color: var(--rc-gray-600);">Declaration Status</span>
                            @if(isset($payslips) && $payslips->where('emp201_status', 'finalized')->count() > 0)
                            <span class="rc-status rc-status-success">Finalized</span>
                            @else
                            <span class="rc-status rc-status-draft">Draft</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-3">
                            <span style="color: var(--rc-gray-600);">Electronic Submission</span>
                            @if(isset($payslips) && $payslips->where('emp201_status', 'finalized')->count() > 0)
                            <span class="rc-status rc-status-info">Ready for submission</span>
                            @else
                            <span class="rc-status rc-status-warning">Cannot be submitted</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end">
                        <div class="filing-accordion-item" style="width: 75%; margin-top: var(--rc-space-4);">
                            <div class="filing-section-header" style="padding: var(--rc-space-3) var(--rc-space-4); border-bottom: 1px solid var(--rc-border); background: var(--rc-info-light);">
                                <h6 class="filing-section-title" style="font-size: var(--rc-font-md); color: var(--rc-info);">UIF Breakdown</h6>
                            </div>
                            <div style="padding: var(--rc-space-4);">
                                <div class="d-flex justify-content-between pb-2" style="border-bottom: 1px solid var(--rc-border-light);">
                                    <span style="color: var(--rc-gray-600); font-size: var(--rc-font-sm);">Employee UIF (1%):</span>
                                    <span style="font-weight: var(--rc-font-semibold); font-size: var(--rc-font-sm);">R {{ isset($uifData) ? number_format($uifData['employee_contribution'], 2) : '0.00' }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-2" style="border-bottom: 1px solid var(--rc-border-light);">
                                    <span style="color: var(--rc-gray-600); font-size: var(--rc-font-sm);">Employer UIF (1%):</span>
                                    <span style="font-weight: var(--rc-font-semibold); font-size: var(--rc-font-sm);">R {{ isset($uifData) ? number_format($uifData['employer_contribution'], 2) : '0.00' }}</span>
                                </div>
                                <div class="d-flex justify-content-between pt-2">
                                    <span style="font-weight: var(--rc-font-bold); font-size: var(--rc-font-sm);">Total UIF:</span>
                                    <span style="font-weight: var(--rc-font-bold); color: var(--rc-primary); font-size: var(--rc-font-sm);">R {{ isset($uifData) ? number_format($uifData['total_uif_liability'], 2) : '0.00' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Footer --}}
                <div class="d-flex justify-content-end gap-2 mt-4 pt-4" style="border-top: 1px solid var(--rc-border);">
                    <a href="{{ route('monthly-submission.uif-pdf', $month ?? date('Y-m')) }}" class="filing-btn-export">
                        <i class="ti ti-file-type-pdf"></i> Export PDF
                    </a>
                    @if(isset($payslips) && $payslips->where('emp201_status', 'finalized')->count() === 0)
                    <form method="POST" action="{{ route('monthly-submission.finalize-emp201', $month ?? date('Y-m')) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-rc-primary">
                            <i class="ti ti-check me-1"></i> Finalize UIF Declaration
                        </button>
                    </form>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
@endsection