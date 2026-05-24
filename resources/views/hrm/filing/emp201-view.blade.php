@extends('layouts.main')
@section('page-title')
{{ __('Monthly Submission') }}
@endsection
@section('page-breadcrumb')
{{ __('EMP201 View') }}
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
                        EMP201 Payment Details - {{ isset($month) ? formatMonthYear($month) : 'Current Month' }}
                    </h6>
                </div>

                @if(isset($emp201Data))
                {{-- Summary Rows --}}
                <table class="filing-detail-table" style="margin-bottom: var(--rc-space-5);">
                    <thead>
                        <tr>
                            <th style="width: 16%;">Description</th>
                            <th style="width: 17%;">Value</th>
                            <th style="width: 16%;">Description</th>
                            <th style="width: 17%;">Value</th>
                            <th style="width: 16%;">Description</th>
                            <th style="width: 18%; text-align: right;">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>PAYE Liability</td>
                            <td style="color: var(--rc-primary); font-weight: var(--rc-font-semibold);">R {{ number_format($emp201Data['paye_liability'], 2) }}</td>
                            <td>ETI Brought Forward</td>
                            <td style="font-weight: var(--rc-font-bold);">R {{ number_format($emp201Data['eti_brought_forward'] ?? 0, 2) }}</td>
                            <td>PAYE Payable</td>
                            <td style="text-align: right; font-weight: var(--rc-font-bold);">R {{ number_format($emp201Data['paye_liability'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>UIF Liability</td>
                            <td style="color: var(--rc-primary); font-weight: var(--rc-font-semibold);">R {{ number_format($emp201Data['uif_liability'], 2) }}</td>
                            <td>SDL Liability</td>
                            <td style="font-weight: var(--rc-font-bold);">R {{ number_format($emp201Data['sdl_liability'], 2) }}</td>
                            <td>UIF Payable</td>
                            <td style="text-align: right; font-weight: var(--rc-font-bold);">R {{ number_format($emp201Data['uif_liability'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>SDL Liability</td>
                            <td style="color: var(--rc-primary); font-weight: var(--rc-font-semibold);">R {{ number_format($emp201Data['sdl_liability'], 2) }}</td>
                            <td>Total Employees</td>
                            <td style="font-weight: var(--rc-font-bold);">{{ isset($payslips) ? $payslips->count() : 0 }}</td>
                            <td>SDL Payable</td>
                            <td style="text-align: right; font-weight: var(--rc-font-bold);">R {{ number_format($emp201Data['sdl_liability'], 2) }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 2px solid var(--rc-border);">
                            <td colspan="5" style="font-weight: var(--rc-font-bold); padding: var(--rc-space-4);">Total Payable</td>
                            <td style="text-align: right; font-weight: var(--rc-font-bold); font-size: var(--rc-font-lg); color: var(--rc-primary); padding: var(--rc-space-4);">R {{ number_format($emp201Data['total_payable'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
                @else
                <div class="filing-alert" style="margin-bottom: var(--rc-space-5);">
                    <span><i class="ti ti-alert-triangle"></i> No EMP201 data available. Please ensure payslips are processed through payrun for this month.</span>
                </div>
                @endif

                {{-- Status & ETI Breakdown Row --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center py-3" style="border-bottom: 1px solid var(--rc-border-light);">
                            <span style="color: var(--rc-gray-600);">ETI under-claim generated</span>
                            <span style="font-weight: var(--rc-font-semibold);">R {{ isset($emp201Data) ? number_format($emp201Data['eti_brought_forward'] ?? 0, 2) : '0.00' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-3">
                            <span style="color: var(--rc-gray-600);">Submission Status</span>
                            @if(isset($payslips) && $payslips->where('emp201_status', 'finalized')->count() > 0)
                            <span class="rc-status rc-status-success">Finalized</span>
                            @else
                            <span class="rc-status rc-status-draft">Draft</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end">
                        <div class="filing-accordion-item" style="width: 75%; margin-top: var(--rc-space-4);">
                            <div class="filing-section-header" style="padding: var(--rc-space-3) var(--rc-space-4); border-bottom: 1px solid var(--rc-border); background: var(--rc-gray-50);">
                                <h6 class="filing-section-title" style="font-size: var(--rc-font-md);">ETI Calculated</h6>
                            </div>
                            <div style="padding: var(--rc-space-4);">
                                <div class="d-flex justify-content-between pb-2" style="border-bottom: 1px solid var(--rc-border-light);">
                                    <span style="color: var(--rc-gray-600); font-size: var(--rc-font-sm);">ETI for this month:</span>
                                    <span style="font-weight: var(--rc-font-semibold); font-size: var(--rc-font-sm);">R {{ isset($emp201Data) ? number_format($emp201Data['eti_current_month'] ?? 0, 2) : '0.00' }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-2" style="border-bottom: 1px solid var(--rc-border-light);">
                                    <span style="color: var(--rc-gray-600); font-size: var(--rc-font-sm);">ETI for prior months:</span>
                                    <span style="font-weight: var(--rc-font-semibold); font-size: var(--rc-font-sm);">R {{ isset($emp201Data) ? number_format($emp201Data['eti_brought_forward'] ?? 0, 2) : '0.00' }}</span>
                                </div>
                                <div class="d-flex justify-content-between pt-2">
                                    <span style="font-weight: var(--rc-font-bold); font-size: var(--rc-font-sm);">Total ETI:</span>
                                    <span style="font-weight: var(--rc-font-bold); color: var(--rc-primary); font-size: var(--rc-font-sm);">R {{ isset($emp201Data) ? number_format($emp201Data['total_eti'] ?? 0, 2) : '0.00' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Footer --}}
                <div class="d-flex justify-content-end gap-2 mt-4 pt-4" style="border-top: 1px solid var(--rc-border);">
                    <a href="{{ route('monthly-submission.emp201-pdf', $month ?? date('Y-m')) }}" class="filing-btn-export">
                        <i class="ti ti-file-type-pdf"></i> Export PDF
                    </a>
                    @if(isset($payslips) && $payslips->where('emp201_status', 'finalized')->count() === 0)
                    <form method="POST" action="{{ route('monthly-submission.finalize-emp201', $month ?? date('Y-m')) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-rc-primary">
                            <i class="ti ti-check me-1"></i> Finalize EMP201
                        </button>
                    </form>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
@endsection