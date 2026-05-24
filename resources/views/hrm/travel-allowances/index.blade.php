@extends('layouts.main')

@section('page-title')
    {{ __('TravelAllowance') }}
@endsection

@section('page-breadcrumb')
    {{ __('TravelAllowance') }}
@endsection

@section('page-action')
    <div class="col-auto pe-0">
        <a href="{{ route('travel-allowances.create') }}" class="btn btn-sm btn-rc-primary btn-icon"
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
            <x-rc-table title="{{ __('TravelAllowance') }}" titleIcon="ti ti-car">
                <x-rc-table.content>
                    <table class="rc-table" id="pc-dt-simple">
                        <thead class="thead-light">
                            <tr>
                                <th class="col-sno">SNO</th>
                                <th class="col-amount">{{ __('FixedAmount') }}</th>
                                <th class="col-amount">{{ __('Rate perKM') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($travelAllowances as $travelAllowance)
                                <tr id="row-{{ $travelAllowance->id }}">
                                    <td class="col-sno">{{ $loop->index + 1 }}</td>
                                    <td class="col-amount">{{ $travelAllowance->fixed_amount }}</td>
                                    <td class="col-amount">{{ $travelAllowance->rate_per_km }}</td>
                                </tr>
                            @empty
                                <x-rc-table.empty :asRow="true" :colspan="3" icon="ti ti-car" title="No Travel Allowances" message="No travel allowance records found." />
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
