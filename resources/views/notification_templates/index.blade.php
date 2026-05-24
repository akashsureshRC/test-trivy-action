@extends('layouts.main')
@section('page-title')
    {{ __('Notification Templates') }}
@endsection
@section('page-breadcrumb')
    {{ __('Notification Templates') }}
@endsection
@section('page-action')
@endsection

@php
$activeModule = '';
foreach ($notifications as $key => $value) {
    $txt = moduleIsActive($key);
    if ($txt == true) {
        $activeModule = $key;
        break;
    }
}
@endphp


@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end mb-4">
                <div class="col-md-6">
                    <ul class="nav nav-pills nav-fill cust-nav information-tab" id="pills-tab" role="tablist">
                        @foreach ($notifications as $key => $value)
                            @if (moduleIsActive($key) && $key == 'Slack')
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="slack" data-bs-toggle="pill" data-bs-target="#slack-tab"
                                        type="button">{{ __('Slack') }}</button>
                                </li>
                            @endif
                            @if (moduleIsActive($key) && $key == 'Telegram')
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="telegram" data-bs-toggle="pill"
                                        data-bs-target="#telegram-tab" type="button">{{ __('Telegram') }}</button>
                                </li>
                            @endif
                            @if (moduleIsActive($key) && $key == 'Twilio')
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="twilio" data-bs-toggle="pill"
                                        data-bs-target="#twilio-tab" type="button">{{ __('Twilio') }}</button>
                                </li>
                            @endif
                            @if (moduleIsActive($key) && $key == 'Whatsapp')
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="whatsapp" data-bs-toggle="pill" data-bs-target="#whatsapp-tab"
                                        type="button">{{ __('Whatsapp') }}</button>
                                </li>
                            @endif
                            @if (moduleIsActive($key) && $key == 'WhatsAppAPI')
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="whatsappapi" data-bs-toggle="pill" data-bs-target="#whatsappapi-tab"
                                        type="button">{{ __('Whatsapp Api') }}</button>
                                </li>
                            @endif
                            @if (moduleIsActive($key) && $key == 'SMS')
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="sms" data-bs-toggle="pill" data-bs-target="#sms-tab"
                                        type="button">{{ __('SMS') }}</button>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-lg-12">
            <x-rc-table title="Notification Templates" titleIcon="ti ti-bell">
                <x-rc-table.content>
                    @if($activeModule == '')
                        <div class="text-center py-4">
                            <h5 class="text-danger">{{ __('Make sure to activate at least one notification add-on. A notification template will be visible after that.') }}</h5>
                        </div>
                    @endif
                    <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show " id="slack-tab" role="tabpanel"
                                aria-labelledby="pills-user-tab-1">
                                <table class="rc-table" id="slack-notify">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Module') }}</th>
                                            <th class="col-actions">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($notifications as $key => $notification)
                                    @if (moduleIsActive($key) && $key == 'Slack')
                                            @foreach ($notification as $value)
                                                <tr>
                                                    <td>{{ $value->action }}</td>
                                                    <td class="text-capitalize">{{ moduleAliasName($value->module) }}</td>
                                                    <td class="col-actions">
                                                        <a href="{{ route('notification-template.show', [$value->id, getActiveLanguage()]) }}"
                                                            class="rc-table-action rc-table-action-warning"
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="{{ __('Manage Your Slack Message') }}">
                                                            <i class="ti ti-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @endif
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="tab-pane fade show " id="telegram-tab" role="tabpanel"
                                aria-labelledby="pills-user-tab-1">
                                <table class="rc-table" id="telegram-notify">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Module') }}</th>
                                            <th class="col-actions">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($notifications as $key => $notification)
                                        @if (moduleIsActive($key) && $key == 'Telegram')
                                            @foreach ($notification as $value)
                                                <tr>
                                                    <td>{{ $value->action }}</td>
                                                    <td class="text-capitalize">{{ moduleAliasName($value->module) }}</td>
                                                    <td class="col-actions">
                                                        <a href="{{ route('notification-template.show', [$value->id, getActiveLanguage()]) }}"
                                                            class="rc-table-action rc-table-action-warning"
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="{{ __('Manage Your Telegram Message') }}">
                                                            <i class="ti ti-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane fade show " id="twilio-tab" role="tabpanel"
                                aria-labelledby="pills-user-tab-1">
                                <table class="rc-table" id="twilio-notify">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Module') }}</th>
                                            <th class="col-actions">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($notifications as $key => $notification)
                                        @if (moduleIsActive($key) && $key == 'Twilio')
                                            @foreach ($notification as $value)
                                                <tr>
                                                    <td>{{ $value->action }}</td>
                                                    <td class="text-capitalize">{{ moduleAliasName($value->module) }}</td>
                                                    <td class="col-actions">
                                                        <a href="{{ route('notification-template.show', [$value->id, getActiveLanguage()]) }}"
                                                            class="rc-table-action rc-table-action-warning"
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="{{ __('Manage Your Twilio Message') }}">
                                                            <i class="ti ti-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane fade show " id="whatsapp-tab" role="tabpanel"
                                aria-labelledby="pills-user-tab-1">
                                <table class="rc-table" id="whatsapp-notify">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Module') }}</th>
                                            <th class="col-actions">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($notifications as $key => $notification)
                                        @if (moduleIsActive($key) && $key == 'Whatsapp')
                                            @foreach ($notification as $value)
                                                <tr>
                                                    <td>{{ $value->action }}</td>
                                                    <td class="text-capitalize">{{ moduleAliasName($value->module) }}</td>
                                                    <td class="col-actions">
                                                        <a href="{{ route('notification-template.show', [$value->id, getActiveLanguage()]) }}"
                                                            class="rc-table-action rc-table-action-warning"
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="{{ __('Manage Your Whatsapp Message') }}">
                                                            <i class="ti ti-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane fade show " id="whatsappapi-tab" role="tabpanel"
                                aria-labelledby="pills-user-tab-1">
                                <table class="rc-table" id="whatsappapi-notify">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Module') }}</th>
                                            <th class="col-actions">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($notifications as $key => $notification)
                                        @if (moduleIsActive($key) && $key == 'WhatsAppAPI')
                                            @foreach ($notification as $value)
                                                <tr>
                                                    <td>{{ $value->action }}</td>
                                                    <td class="text-capitalize">{{ moduleAliasName($value->module) }}</td>
                                                    <td class="col-actions">
                                                        <a href="{{ route('notification-template.show', [$value->id, getActiveLanguage()]) }}"
                                                            class="rc-table-action rc-table-action-warning"
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="{{ __('Manage Your WhatsAppAPI Message') }}">
                                                            <i class="ti ti-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane fade show " id="sms-tab" role="tabpanel"
                            aria-labelledby="pills-user-tab-1">
                            <table class="rc-table" id="sms-notify">
                                <thead>
                                    <tr>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Module') }}</th>
                                        <th class="col-actions">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach ($notifications as $key => $notification)
                                    @if (moduleIsActive($key) && $key == 'SMS')
                                        @foreach ($notification as $value)
                                            <tr>
                                                <td>{{ $value->action }}</td>
                                                <td class="text-capitalize">{{ moduleAliasName($value->module) }}</td>
                                                <td class="col-actions">
                                                    <a href="{{ route('notification-template.show', [$value->id, getActiveLanguage()]) }}"
                                                        class="rc-table-action rc-table-action-warning"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="{{ __('Manage Your SMS Message') }}">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                </x-rc-table.content>
            </x-rc-table>
        </div>
    </div>
@endsection

@push('scripts')

    <script>
        $(document).ready(function() {
            var moduleName = '{{ $activeModule }}';

            if (moduleName == 'Slack') {
                $('#slack').addClass('active');
                $('#slack-tab').addClass('active');
            } else if (moduleName == 'Telegram') {
                $('#telegram').addClass('active');
                $('#telegram-tab').addClass('active');
            } else if (moduleName == 'Twilio') {
                $('#twilio').addClass('active');
                $('#twilio-tab').addClass('active');
            } else if (moduleName == 'Whatsapp') {
                $('#whatsapp').addClass('active');
                $('#whatsapp-tab').addClass('active');
            } else if (moduleName == 'WhatsAppAPI') {
                $('#whatsappapi').addClass('active');
                $('#whatsappapi-tab').addClass('active');
            }else if (moduleName == 'SMS') {
                $('#sms').addClass('active');
                $('#sms-tab').addClass('active');
            }
        });
    </script>
@endpush
