@extends('layouts.main')

@section('page-title')
    {{ __('Beneficiaries') }}
@endsection

@section('page-breadcrumb')
    {{ __('beneficiary') }}
@endsection

@section('page-action')
    <div class="col-auto pe-0">
        <a href="{{ route('beneficiary.create') }}" class="btn btn-sm btn-rc-primary btn-icon" data-bs-toggle="tooltip" title="{{ __('Create') }}">
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
            <x-rc-table title="Beneficiaries" titleIcon="ti ti-users">
                <x-rc-table.content>
                    <table class="rc-table" id="pc-dt-simple">
                        <thead class="thead-light">
                            <tr>
                                <th class="col-sno">SNO</th>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Beneficiary Name') }}</th>
                                <th>{{ __('Relationship') }}</th>
                                <th class="col-amount">{{ __('Amount per Month') }}</th>
                                <th class="col-status">{{ __('Status') }}</th>
                                <th class="col-actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($beneficiaries as $beneficiary)
                                <tr id="row-{{ $beneficiary->id }}">
                                    <td class="col-sno">{{ $loop->index + 1 }}</td>
                                    <td>{{ $beneficiary->employee->first_name }} {{ $beneficiary->employee->last_name }}</td>
                                    <td>{{ $beneficiary->name }}</td>
                                    <td>{{ $beneficiary->relationship }}</td>
                                    <td class="col-amount">{{ number_format($beneficiary->amount_per_month, 2) }}</td>
                                    <td class="col-status">
                                        <label class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input toggle-status" type="checkbox"
                                                {{ $beneficiary->status == "Active" ? 'checked' : '' }}
                                                data-id="{{ $beneficiary->id }}" />
                                            <span class="form-check-label {{ $beneficiary->status == 'Active' ? 'text-success' : 'text-danger' }}">
                                                {{ $beneficiary->status }}
                                            </span>
                                        </label>
                                    </td>
                                    <td class="col-actions">
                                        <a class="rc-table-action rc-table-action-edit" title="Edit"
                                            href="{{ route('beneficiary.edit', $beneficiary->id) }}">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('beneficiary.destroy', $beneficiary->id) }}" id="delete-form-{{ $beneficiary->id }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="rc-table-action rc-table-action-delete show_confirm" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <x-rc-table.empty :asRow="true" :colspan="7" icon="ti ti-users" title="No Beneficiaries" message="No beneficiaries have been added yet." />
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
