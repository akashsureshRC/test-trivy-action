@extends('layouts.main')

@section('page-title')
    {{ __('Tickets') }}
@endsection

@section('page-breadcrumb')
    {{ __('Helpdesk') }},{{ __('Tickets') }}
@endsection

@section('page-action')

    <div class="col-auto pe-0">
        <select class="form-select" id="projects" onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);" style="width: 121px;">
            <option value="{{route('helpdesk-tickets.search')}}">{{__('All Tickets')}}</option>
            <option value="{{route('helpdesk-tickets.search', 'in-progress')}}" @if($status == 'in-progress') selected @endif>{{__('In Progress')}}</option>
            <option value="{{route('helpdesk-tickets.search', 'on-hold')}}" @if($status == 'on-hold') selected @endif>{{__('On Hold')}}</option>
            <option value="{{route('helpdesk-tickets.search', 'closed')}}" @if($status == 'closed') selected @endif>{{__('Closed')}}</option>
        </select>
    </div>
    <div class="col-auto ps-3 mt-1">
        @permission('helpdesk ticket create')
                <a href="{{route('helpdesk.create')}}" class="btn btn-sm btn-rc-icon"
                data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Create')}}"><i class="ti ti-plus"></i></a>
        @endpermission
    </div>

@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12">
            @if(session()->has('ticket_id') || session()->has('smtp_error'))
                <div class="alert alert-info bg-pr">
                    @if(session()->has('ticket_id'))
                        {!! Session::get('ticket_id') !!}
                        {{ Session::forget('ticket_id') }}
                    @endif
                    @if(session()->has('smtp_error'))
                        {!! Session::get('smtp_error') !!}
                        {{ Session::forget('smtp_error') }}
                    @endif
                </div>
            @endif
        </div>
        <div class="col-lg-12 col-md-12">
            <x-rc-table>
                <x-rc-table.content>
                    <table class="rc-table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Ticket ID') }}</th>
                                <th>{{ __('Assigned To') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Created By') }}</th>
                                <th>{{ __('Subject') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th class="col-status">{{ __('Status') }}</th>
                                <th class="col-date">{{ __('Created') }}</th>
                                <th class="col-actions">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tickets as $ticket)
                                <tr>
                                    <td>{{ $tickets->firstItem() + $loop->index }}</td>
                                    <td>
                                        <a @permission('helpdesk ticket show')href="{{ route('helpdesk.edit', $ticket->id) }}" @else href="#" @endpermission>
                                            {{ $ticket->ticket_id }}
                                        </a>
                                    </td>
                                    <td><span class="white-space">{{ $ticket->name }}</span></td>
                                    <td>{{ $ticket->email }}</td>
                                    <td class="text-primary">{{ $ticket->createdBy->name }}</td>
                                    <td><span class="white-space">{{ $ticket->subject }}</span></td>
                                    <td><span class="rc-status" style="background: {{ $ticket->color }}; color: #fff;">{{ $ticket->category_name }}</span></td>
                                    <td class="col-status">
                                        <span class="rc-status @if($ticket->status == 'In Progress') rc-status-warning @elseif($ticket->status == 'On Hold') rc-status-danger @else rc-status-success @endif">
                                            {{ __($ticket->status) }}
                                        </span>
                                    </td>
                                    <td class="col-date">{{ $ticket->created_at->diffForHumans() }}</td>
                                    <td class="col-actions">
                                        @permission('helpdesk ticket show')
                                            <a href="{{ route('helpdesk.edit', $ticket->id) }}" 
                                               class="rc-table-action rc-table-action-view" 
                                               data-bs-toggle="tooltip" 
                                               title="{{ __('Edit & Reply') }}">
                                                <i class="ti ti-corner-up-left"></i>
                                            </a>
                                            <a href="{{ route('helpdesk.show', [\Illuminate\Support\Facades\Crypt::encrypt($ticket->ticket_id)]) }}" 
                                               class="rc-table-action rc-table-action-warning" 
                                               data-bs-toggle="tooltip" 
                                               title="{{ __('Details') }}">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                        @endpermission
                                        @permission('helpdesk ticket delete')
                                            @if(Auth::user()->id == $ticket->created_by || Auth::user()->type == 'super admin')
                                                <form method="POST" action="{{ route('helpdesk.destroy', $ticket->id) }}" id="user-form-{{ $ticket->id }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" 
                                                            class="rc-table-action rc-table-action-delete show_confirm" 
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endpermission
                                    </td>
                                </tr>
                            @empty
                                <x-rc-table.empty :asRow="true" :colspan="10" icon="ti ti-ticket-off" title="{{ __('No Tickets Found') }}" message="{{ __('There are no tickets to display.') }}" />
                            @endforelse
                        </tbody>
                    </table>
                </x-rc-table.content>

                <x-rc-table.footer :paginator="$tickets" />
            </x-rc-table>
        </div>
    </div>
@endsection
