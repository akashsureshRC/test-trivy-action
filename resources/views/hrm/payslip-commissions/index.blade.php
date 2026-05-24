@extends('layouts.main')

@section('page-title')
    {{ __('PayslipCommission') }}
@endsection

@section('page-breadcrumb')
    {{ __('PayslipCommission') }}
@endsection

@section('page-action')
    <div class="col-auto pe-0">
        <a href="{{ route('payslip-commissions.create') }}" class="btn btn-sm btn-rc-primary btn-icon"
            data-bs-toggle="tooltip" title="{{ __('Create') }}">
            <i class="ti ti-plus text-white"></i>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12">
            @if (session()->has('success') || session()->has('error'))
                <div class="alert alert-info">
                    @if (session()->has('success'))
                        {!! session('success') !!}
                    @endif
                    @if (session()->has('error'))
                        {!! session('error') !!}
                    @endif
                </div>
            @endif
        </div>
        <div class="col-lg-12 col-md-12">
            <x-rc-table title="{{ __('PayslipCommission') }}" titleIcon="ti ti-percentage">
                <x-rc-table.content>
                    <table class="rc-table" id="pc-dt-simple">
                        <thead class="thead-light">
                            <tr>
                                <th class="col-sno">SNO</th>
                                <th>{{ __('Name_Payslip') }}</th>
                                <th class="col-amount">{{ __('Commission') }}%</th>
                                <th>{{ __('Commission_Type') }}</th>
                                <th class="col-status">{{ __('status') }}</th>
                                <th class="col-actions">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($commissions as $PayslipCommission)
                                <tr id="row-{{ $PayslipCommission->id }}">
                                    <td class="col-sno">{{ $loop->index + 1 }}</td>
                                    <td>{{ $PayslipCommission->name_payslip }}</td>
                                    <td class="col-amount">{{ $PayslipCommission->commission_amount }}%</td>
                                    <td>{{ $PayslipCommission->commission_type }}</td>
                                    <td class="col-status">
                                        <label class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input toggle-status" type="checkbox"
                                                {{ $PayslipCommission->status == "Active" ? 'checked' : '' }}
                                                data-id="{{ $PayslipCommission->id }}" />
                                            <span class="rc-status {{ $PayslipCommission->status == 'Active' ? 'rc-status-success' : 'rc-status-danger'}}">
                                                {{ $PayslipCommission->status }}
                                            </span>
                                        </label>
                                    </td>
                                    <td class="col-actions">
                                        <a href="{{ route('payslip-commissions.edit', $PayslipCommission->id) }}"
                                            class="rc-table-action rc-table-action-edit"
                                            data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        <form method="POST"
                                            action="{{ route('payslip-commissions.destroy', $PayslipCommission->id) }}"
                                            id="delete-form-{{ $PayslipCommission->id }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                class="rc-table-action rc-table-action-delete show_confirm"
                                                data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <x-rc-table.empty :asRow="true" :colspan="6" icon="ti ti-percentage" title="{{ __('No Commissions Found') }}" message="{{ __('No payslip commissions have been created yet.') }}" />
                            @endforelse
                        </tbody>
                    </table>
                </x-rc-table.content>
            </x-rc-table>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            $('.toggle-status').on('change', function(){
                let checkbox = $(this);
                let status = checkbox.is(':checked') ? 'Active' : 'Inactive';
                let id = checkbox.data('id');
                
                $.ajax({
                    url: '/payslip-commissions/' + id + '/toggle-status',
                    method: 'PATCH',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: status
                    },
                    success: function(response) {
                        // Update the label based on the response
                        let label = checkbox.closest('label').find('.form-check-label');
                        if(response.status === 'active'){
                            label.removeClass('text-danger').addClass('text-success').text('Active');
                            toastr.success('Status changed to Active');
                        } else {
                            label.removeClass('text-success').addClass('text-danger').text('Inactive');
                            toastr.success('Status changed to Inactive');
                        }
                    },
                    error: function() {
                        toastr.error('Error toggling status');
                    }
                });
            });
        });
    </script>
        
@endsection
