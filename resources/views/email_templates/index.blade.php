@extends('layouts.main')
@section('page-title')
    {{__('Email Templates')}}
@endsection
@section("page-breadcrumb")
    {{__('Email Templates')}}
@endsection
@section('page-action')

@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <x-rc-table>
            <x-rc-table.content>
                <table class="rc-table" id="d">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th class="col-actions">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($email_templates as $email_template)
                            <tr>
                                <td>{{ $email_template->name }}</td>
                                <td class="col-actions">
                                    <a href="{{ route('manage.email.language',[$email_template->id,getActiveLanguage()]) }}" class="rc-table-action rc-table-action-view" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('View')}}">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <x-rc-table.empty :asRow="true" :colspan="3" icon="ti ti-mail-off" title="{{ __('No Email Templates') }}" message="{{ __('No email templates found.') }}" />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>

            <x-rc-table.footer :paginator="$email_templates" />
        </x-rc-table>
    </div>
</div>

@endsection
