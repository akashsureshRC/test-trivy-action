<!-- resources/views/filing.blade.php -->
@extends('layouts.main')
@section('page-title')
    {{ __('Monthly Submission') }}
@endsection
@section('page-breadcrumb')
    {{ __('Views') }}
@endsection

@section('content')
    <div class="card mt-4">
        <div class="card-body p-3">
            <div class="container mt-4">
                <!-- View & Export Section -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h4 class="" style="text-transform: uppercase; color: var(--rc-primary);">EMP201 Payment Details - {{ isset($month) ? formatMonthYear($month) : 'Current Month' }}</h4>
                </div>
                
                @if(isset($emp201Data))
                <div class="row border p-2 bg-light">
                    <div class="col-md-3">PAYE Liability</div>
                    <div class="col-md-1 text-primary">R {{ number_format($emp201Data['paye_liability'], 2) }}</div>
                    <div class="col-md-3">ETI Brought Forward</div>
                    <div class="col-md-1 fw-bold">R {{ number_format($emp201Data['eti_brought_forward'] ?? 0, 2) }}</div>
                    <div class="col-md-3">PAYE Payable</div>
                    <div class="col-md-1 fw-bold" style="text-align: right">R {{ number_format($emp201Data['paye_liability'], 2) }}</div>
                </div>

                <div class="row border p-2">
                    <div class="col-md-3">UIF Liability</div>
                    <div class="col-md-1 text-primary">R {{ number_format($emp201Data['uif_liability'], 2) }}</div>
                    <div class="col-md-3">SDL Liability</div>
                    <div class="col-md-1 fw-bold">R {{ number_format($emp201Data['sdl_liability'], 2) }}</div>
                    <div class="col-md-3">UIF Payable</div>
                    <div class="col-md-1 fw-bold" style="text-align: right">R {{ number_format($emp201Data['uif_liability'], 2) }}</div>
                </div>

                <div class="row border p-2 bg-light">
                    <div class="col-md-3">SDL Liability</div>
                    <div class="col-md-1 text-primary">R {{ number_format($emp201Data['sdl_liability'], 2) }}</div>
                    <div class="col-md-3">Total Employees</div>
                    <div class="col-md-1 fw-bold">{{ isset($payslips) ? $payslips->count() : 0 }}</div>
                    <div class="col-md-3">SDL Payable</div>
                    <div class="col-md-1 fw-bold" style="text-align: right">R {{ number_format($emp201Data['sdl_liability'], 2) }}</div>
                </div>

                <div class="row border p-2">
                    <div class="col-md-10 fw-bold">Total Payable</div>
                    <div class="col-md-2 fw-bold" style="text-align: right">R {{ number_format($emp201Data['total_payable'], 2) }}</div>
                </div>
                @else
                <div class="alert alert-warning" role="alert">
                    No EMP201 data available. Please ensure payslips are finalized for this month.
                </div>
                @endif

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="d-flex">
                            <div class="col-md-6">
                                ETI under-claim generated
                            </div>
                            <div class="col-md-6">
                                R {{ isset($emp201Data) ? number_format($emp201Data['eti_brought_forward'] ?? 0, 2) : '0.00' }}
                            </div>
                        </div>
                        <div class="d-flex mt-4">
                            <div class="col-md-6">
                                Submission Status
                            </div>
                            <div class="col-md-6">
                                @if(isset($payslips) && $payslips->where('emp201_status', 'finalized')->count() > 0)
                                    <span class="badge bg-success">Finalized</span>
                                @else
                                    <span class="badge bg-warning">Draft</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 justify-content-end d-flex">
                        <div class="card mt-4 col-md-9 border">
                            <div class="card-header bg-secondary text-white fw-bold">ETI Calculated</div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between border-bottom pb-2">
                                    <span>ETI for this month:</span>
                                    <span>R {{ isset($emp201Data) ? number_format($emp201Data['eti_brought_forward'] ?? 0, 2) : '0.00' }}</span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span>ETI for prior months:</span>
                                    <span>R 0.00</span>
                                </div>
                                <div class="d-flex justify-content-between pt-2 fw-bold">
                                    <span>Total:</span>
                                    <span>R {{ isset($emp201Data) ? number_format($emp201Data['eti_brought_forward'] ?? 0, 2) : '0.00' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-4">
                    <div class="modal-footer gap-2">
                        <input type="button" value="{{ __('Cancel') }}"
                            onclick="location.href = '{{ route('monthly-submission.index') }}';" class="btn btn-rc-outline">
                        <input type="submit" value="{{ __('Export PDF') }}" class="btn btn-rc-primary">
                        @if(isset($payslips) && $payslips->where('emp201_status', 'finalized')->count() === 0)
                        <form method="POST" action="{{ route('monthly-submission.finalize-emp201', $month ?? date('Y-m')) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-rc-primary">Finalize EMP201</button>
                        </form>
                        @endif
                    </div>
                </div>
                <!-- ETI SUMMARY CARD -->

            </div>
        </div>
    </div>
    <script>
        function changeLabel(label) {
            document.getElementById('exportButton').innerText = label;
        }
    </script>
@endsection