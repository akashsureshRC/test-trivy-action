@extends('layouts.main')
@section('page-title')
    {{ __('Categories') }}
@endsection
@section('page-breadcrumb')
    {{ __('Helpdesk') }},{{ __('Categories') }}
@endsection

@section('page-action')
    <div>
        @permission('helpdesk ticketcategory create')
            <a data-url="{{ route('helpdeskticket-category.create') }}" data-ajax-popup="true"
                data-bs-toggle="tooltip" title="{{ __('Create') }}"
                data-title="{{ __('Create New Category') }}" class="btn btn-sm btn-rc-icon">
                <i class="ti ti-plus text-white"></i>
            </a>
        @endpermission
    </div>

@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <x-rc-table>
                <x-rc-table.content>
                    <table class="rc-table" id="helpdesk-ticketcategory">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Color') }}</th>
                                @if (Laratrust::hasPermission('helpdesk ticketcategory edit') || Laratrust::hasPermission('helpdesk ticketcategory delete'))
                                    <th class="col-actions">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $category)
                                <tr>
                                    <td>{{ $categories->firstItem() + $loop->index }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td><span class="badge" style="background: {{ $category->color }}">&nbsp;&nbsp;&nbsp;</span></td>
                                    @if (Laratrust::hasPermission('helpdesk ticketcategory edit') || Laratrust::hasPermission('helpdesk ticketcategory delete'))
                                        <td class="col-actions">
                                            @permission('helpdesk ticketcategory edit')
                                                <a class="rc-table-action rc-table-action-edit"
                                                    data-url="{{ route('helpdeskticket-category.edit', $category->id) }}"
                                                    data-ajax-popup="true"
                                                    data-title="{{ __('Edit Product Category') }}"
                                                    data-bs-toggle="tooltip"
                                                    data-original-title="{{ __('Edit') }}">
                                                    <i class="ti ti-pencil"></i>
                                                </a>
                                            @endpermission
                                            @permission('helpdesk ticketcategory delete')
                                                <form method="POST"
                                                    action="{{ route('helpdeskticket-category.destroy', $category->id) }}"
                                                    id="user-form-{{ $category->id }}" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button"
                                                        class="rc-table-action rc-table-action-delete show_confirm"
                                                        data-bs-toggle="tooltip" title='Delete'>
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                            @endpermission
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <x-rc-table.empty :asRow="true" :colspan="4" icon="ti ti-category" title="{{ __('No Categories Found') }}" message="{{ __('No ticket categories have been created yet.') }}" />
                            @endforelse
                        </tbody>
                    </table>
                </x-rc-table.content>

                <x-rc-table.footer :paginator="$categories" />
            </x-rc-table>
        </div>
    </div>
@endsection
