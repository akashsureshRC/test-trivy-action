@extends('layouts.main')

@section('page-title')
    {{ __('Tax Settings') }}
@endsection

@section('page-breadcrumb')
    {{ __('Tax Settings') }}
@endsection

@section('page-action')
<div>
    <a href="{{ route('tax-years.create') }}" class="btn btn-sm btn-rc-icon" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}">
        <i class="ti ti-plus text-white"></i>
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <x-rc-table>
            <x-rc-table.content>
                <table class="rc-table">
                    <thead>
                        <tr>
                            <th class="col-sno">{{ __('S.No') }}</th>
                            <th>{{ __('Label') }}</th>
                            <th>{{ __('Effective From') }}</th>
                            <th>{{ __('Effective To') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Locked At') }}</th>
                            <th class="col-actions">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($taxYears as $taxYear)
                            <tr>
                                <td class="col-sno">{{ $taxYears->firstItem() + $loop->index }}</td>
                                <td class="font-style">{{ $taxYear->label }}</td>
                                <td>{{ $taxYear->effective_from->format('d M Y') }}</td>
                                <td>{{ $taxYear->effective_to->format('d M Y') }}</td>
                                <td>
                                    @if($taxYear->is_locked)
                                        <span class="badge bg-success">{{ __('Locked') }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark">{{ __('Draft') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($taxYear->locked_at)
                                        {{ $taxYear->locked_at->format('d M Y H:i') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="col-actions">
                                    @if(!$taxYear->is_locked)
                                        {{-- Edit --}}
                                        <a href="{{ route('tax-years.edit', $taxYear->id) }}"
                                            class="rc-table-action rc-table-action-view" data-bs-toggle="tooltip"
                                            data-bs-original-title="{{ __('Edit') }}">
                                            <i class="ti ti-pencil"></i>
                                        </a>

                                        {{-- Lock --}}
                                        <form action="{{ route('tax-years.lock', $taxYear->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="rc-table-action border-0 bg-transparent show_confirm" data-bs-toggle="tooltip"
                                                data-bs-original-title="{{ __('Lock') }}"
                                                data-confirm="{{ __('Are You Sure?') }}"
                                                data-text="{{ __('This will lock the tax year and it will be used for payroll calculations.') }}">
                                                <i class="ti ti-lock text-success"></i>
                                            </button>
                                        </form>

                                        {{-- Delete --}}
                                        <form action="{{ route('tax-years.destroy', $taxYear->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rc-table-action rc-table-action-delete border-0 bg-transparent show_confirm" data-bs-toggle="tooltip"
                                                data-bs-original-title="{{ __('Delete') }}"
                                                data-confirm="{{ __('Are You Sure?') }}"
                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        {{-- View --}}
                                        <a href="{{ route('tax-years.edit', $taxYear->id) }}"
                                            class="rc-table-action rc-table-action-view" data-bs-toggle="tooltip"
                                            data-bs-original-title="{{ __('View') }}">
                                            <i class="ti ti-eye"></i>
                                        </a>

                                        {{-- Unlock --}}
                                        <form action="{{ route('tax-years.unlock', $taxYear->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="rc-table-action border-0 bg-transparent show_confirm" data-bs-toggle="tooltip"
                                                data-bs-original-title="{{ __('Unlock') }}"
                                                data-confirm="{{ __('Are You Sure?') }}"
                                                data-text="{{ __('This will unlock the tax year. Only possible if no payslips reference it.') }}">
                                                <i class="ti ti-lock-open text-warning"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <x-rc-table.empty :asRow="true" :colspan="7" icon="ti ti-receipt-tax" title="{{ __('No Tax Years') }}" message="{{ __('No tax year configurations found. Create one to get started.') }}" />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>
            <x-rc-table.footer :paginator="$taxYears" />
        </x-rc-table>
    </div>
</div>
@endsection
