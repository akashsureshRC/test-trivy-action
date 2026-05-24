@extends('layouts.main')
@section('page-title')
{{ __('Monthly Submission') }}
@endsection
@section('page-breadcrumb')
{{ __('Monthly Submission') }}
@endsection

@section('page-action')
<div class="d-flex align-items-center gap-2">
    <label for="yearFilter" class="form-label mb-0">{{ __('Year') }}</label>
    <form method="GET" action="{{ route('monthly-submission.index') }}">
        <select name="year" id="yearFilter" class="form-select" style="min-width: 120px;" onchange="this.form.submit()">
            @forelse(($availableYears ?? []) as $year)
                <option value="{{ $year }}" {{ (string)($selectedYear ?? '') === (string)$year ? 'selected' : '' }}>{{ $year }}</option>
            @empty
                <option value="{{ date('Y') }}">{{ date('Y') }}</option>
            @endforelse
        </select>
    </form>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        @if(isset($finalizedPayruns) && count($finalizedPayruns) > 0)
            @foreach($finalizedPayruns as $payrun)
                <div class="filing-accordion-item">
                    {{-- Accordion Header --}}
                    <button class="filing-accordion-header {{ $loop->first ? '' : 'collapsed' }}"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#filingMonth{{ $loop->index }}"
                            aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                        <span class="filing-month-label">{{ $payrun['month_name'] }}</span>
                        <i class="ti ti-chevron-down filing-accordion-chevron"></i>
                    </button>

                    {{-- Accordion Body --}}
                    <div id="filingMonth{{ $loop->index }}" class="collapse {{ $loop->first ? 'show' : '' }}">
                        <div class="filing-accordion-body">

                            @if($payrun['status'] === 'New')
                                <div class="filing-alert">
                                    <span>
                                        <i class="ti ti-info-circle"></i>
                                        {{ $payrun['payslip_count'] }} payrun payslips ready for EMP201 submission
                                    </span>
                                </div>
                            @endif

                            {{-- EMP201 Section --}}
                            <div class="filing-section">
                                <div class="filing-section-header">
                                    <h6 class="filing-section-title">EMP201</h6>
                                    <a href="#" class="filing-section-link" data-bs-toggle="modal" data-bs-target="#etiInputsModal{{ $loop->index }}">
                                        Inputs
                                    </a>
                                </div>
                                <table class="filing-detail-table">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th>Date Finalised</th>
                                            <th>Action</th>
                                            <th>View & Export</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                @if($payrun['status'] === 'Finalized')
                                                    <span class="rc-status rc-status-success">Finalized</span>
                                                @else
                                                    <span class="rc-status rc-status-pending">{{ $payrun['status'] }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($payrun['finalized_date'])
                                                    {{ formatDateTime($payrun['finalized_date']) }}
                                                @else
                                                    <span class="text-muted">–</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($payrun['status'] === 'New')
                                                    <form method="POST" action="{{ route('monthly-submission.finalize-emp201', $payrun['term']) }}" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-rc-primary" style="padding: 4px 10px; font-size: 12px;">
                                                            <i class="ti ti-check me-1" style="font-size: 14px;"></i>Finalise
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="rc-status rc-status-success">Finalized</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="filing-actions">
                                                    <a href="{{ route('monthly-submission.show', $payrun['term']) }}" class="filing-btn-view">
                                                        <i class="ti ti-eye"></i> View
                                                    </a>
                                                    <a href="{{ route('monthly-submission.emp201-pdf', $payrun['term']) }}" class="filing-btn-export">
                                                        <i class="ti ti-file-type-pdf"></i> Export PDF
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- UIF Declaration Section --}}
                            <div class="filing-section">
                                <div class="filing-section-header">
                                    <h6 class="filing-section-title">UIF Declaration</h6>
                                </div>
                                <table class="filing-detail-table">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th>Date Finalised</th>
                                            <th>Electronic Status</th>
                                            <th>View & Export</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                @if($payrun['status'] === 'Finalized')
                                                    <span class="rc-status rc-status-success">Finalised</span>
                                                @else
                                                    <span class="rc-status rc-status-pending">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($payrun['finalized_date'])
                                                    {{ formatDateTime($payrun['finalized_date']) }}
                                                @else
                                                    <span class="text-muted">–</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($payrun['status'] === 'Finalized')
                                                    <span class="rc-status rc-status-info">Ready for submission</span>
                                                @else
                                                    <span class="rc-status rc-status-warning">Cannot be submitted</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="filing-actions">
                                                    <a href="{{ route('monthly-submission.show-uif', $payrun['term']) }}" class="filing-btn-view">
                                                        <i class="ti ti-eye"></i> View
                                                    </a>
                                                    <a href="{{ route('monthly-submission.uif-pdf', $payrun['term']) }}" class="filing-btn-export">
                                                        <i class="ti ti-file-type-pdf"></i> Export PDF
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ETI Inputs Modal --}}
                <div class="modal fade" id="etiInputsModal{{ $loop->index }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">ETI Inputs – {{ $payrun['month_name'] }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('monthly-submission.eti-inputs', $payrun['term']) }}">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="notEtiCompliant{{ $loop->index }}" name="not_eti_compliant" onchange="toggleETIFields({{ $loop->index }})">
                                        <label class="form-check-label" for="notEtiCompliant{{ $loop->index }}">Not ETI Compliant</label>
                                    </div>
                                    <div id="etiFields{{ $loop->index }}">
                                        <div class="mb-3">
                                            <label class="form-label">Amount Claimed</label>
                                            <input type="number" class="form-control" name="amount_claimed" step="0.01">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Amount Forfeited</label>
                                            <input type="number" class="form-control" name="amount_forfeited" step="0.01">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Notes</label>
                                            <textarea class="form-control" name="notes" rows="3"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Last Version Submit Before Deadline</label>
                                            <select class="form-select" name="last_version" required>
                                                <option value="Version 1">Version 1</option>
                                                <option value="draft">Draft</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-rc-primary">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            {{-- Empty State --}}
            <div class="card">
                <div class="card-body">
                    <div class="filing-empty">
                        <div class="filing-empty-icon">
                            <i class="ti ti-file-invoice"></i>
                        </div>
                        <h5>No Payrun Data Found</h5>
                        <p>There are no payrun records available for monthly submission.<br>Please ensure payslips are processed through the payrun system first.</p>
                        <a href="{{ route('payrun.index') }}" class="btn btn-rc-primary">
                            <i class="ti ti-arrow-right me-1"></i>Go to Payrun
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    function toggleETIFields(index) {
        const checkbox = document.getElementById('notEtiCompliant' + index);
        const fields = document.getElementById('etiFields' + index);
        fields.style.display = checkbox.checked ? 'none' : 'block';
    }

    // Auto-collapse other accordions when one is opened
    document.addEventListener('DOMContentLoaded', function() {
        const headers = document.querySelectorAll('.filing-accordion-header');
        headers.forEach(header => {
            header.addEventListener('click', function() {
                const targetId = this.getAttribute('data-bs-target');
                const allPanels = document.querySelectorAll('.filing-accordion-item .collapse');

                allPanels.forEach(panel => {
                    if ('#' + panel.id !== targetId) {
                        const bsCollapse = bootstrap.Collapse.getInstance(panel);
                        if (bsCollapse) {
                            bsCollapse.hide();
                        }
                    }
                });
            });
        });
    });
</script>
@endsection