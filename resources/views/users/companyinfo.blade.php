<style>
    .ci-stat-card {
        border: 1px solid var(--rc-border);
        border-radius: var(--rc-radius-md);
        padding: 14px 16px;
        text-align: center;
        background: var(--rc-gray-50);
        transition: var(--rc-transition-fast);
    }
    .ci-stat-card .ci-stat-value {
        font-size: var(--rc-font-2xl);
        font-weight: var(--rc-font-bold);
        line-height: 1.2;
    }
    .ci-stat-card .ci-stat-label {
        font-size: var(--rc-font-xs);
        color: var(--rc-gray-500);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 2px;
    }
    .ci-ws-toggle-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fef3c7;
        border: 1px solid #fcd34d;
        border-radius: var(--rc-radius-md);
        padding: 10px 16px;
        gap: 12px;
    }
    .ci-ws-toggle-bar .ci-ws-warning {
        font-size: var(--rc-font-xs);
        color: #92400e;
        flex: 1;
        line-height: 1.4;
    }
    .ci-user-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        border: 1px solid var(--rc-border);
        border-radius: var(--rc-radius-md);
        background: #fff;
        transition: var(--rc-transition-fast);
    }
    .ci-user-item:hover {
        border-color: var(--rc-primary-light);
        background: var(--rc-gray-50);
    }
    .ci-user-item .ci-user-info {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }
    .ci-user-item .ci-user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }
    .ci-user-item .ci-user-avatar-placeholder {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--rc-primary), var(--rc-primary-hover));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: var(--rc-font-semibold);
        flex-shrink: 0;
    }
    .ci-user-item .ci-user-name {
        font-size: var(--rc-font-sm);
        font-weight: var(--rc-font-medium);
        color: var(--rc-gray-800);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .ci-mini-stats {
        display: flex;
        gap: 8px;
    }
    .ci-mini-stat {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: var(--rc-font-xs);
        color: var(--rc-gray-600);
        padding: 4px 10px;
        border-radius: var(--rc-radius-sm);
        background: var(--rc-gray-50);
        border: 1px solid var(--rc-border-light);
    }
    .ci-mini-stat .ci-mini-stat-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .ci-mini-stat .ci-mini-stat-count {
        font-weight: var(--rc-font-semibold);
        color: var(--rc-gray-800);
    }
    .ci-section-label {
        font-size: var(--rc-font-xs);
        font-weight: var(--rc-font-semibold);
        color: var(--rc-gray-500);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
    }
    .ci-store-info {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border: 1px solid var(--rc-border);
        border-radius: var(--rc-radius-md);
        background: var(--rc-gray-50);
        font-size: var(--rc-font-sm);
        color: var(--rc-gray-700);
    }
    .ci-store-info i {
        color: var(--rc-primary);
    }
</style>

<div class="modal-body p-0">

    {{-- Workspace Summary Stats --}}
    <div class="px-4 pt-4 pb-2">
        <div class="row g-3">
            <div class="col-4">
                <div class="ci-stat-card">
                    <div class="ci-stat-value text-primary total_workspace">{{ $workspce_data['total_workspace'] ?? 0 }}</div>
                    <div class="ci-stat-label">{{ __('Total') }}</div>
                </div>
            </div>
            <div class="col-4">
                <div class="ci-stat-card">
                    <div class="ci-stat-value text-success active_workspace">{{ $workspce_data['active_workspace'] ?? 0 }}</div>
                    <div class="ci-stat-label">{{ __('Active') }}</div>
                </div>
            </div>
            <div class="col-4">
                <div class="ci-stat-card">
                    <div class="ci-stat-value text-danger disable_workspace">{{ $workspce_data['disable_workspace'] ?? 0 }}</div>
                    <div class="ci-stat-label">{{ __('Disabled') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Workspace Tabs --}}
    <div class="px-4 pt-2 pb-0">
        <ul class="nav nav-pills nav-fill gap-4" id="pills-tab" role="tablist">
            @foreach ($users_data as $key => $user_data)
                @php
                    $workspace = \App\Models\WorkSpace::where('id', $user_data['workspace_id'])->first();
                @endphp
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-capitalize {{ $loop->index == 0 ? 'active' : '' }}"
                        id="pills-{{ $workspace->id }}-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-{{ $workspace->id }}"
                        type="button">
                        {{ $workspace->name }}
                    </button>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Tab Content --}}
    <div class="tab-content px-4 py-3" id="pills-tabContent">
        @foreach ($users_data as $key => $user_data)
            @php
                $users = \App\Models\User::where('created_by', $id)
                    ->where('workspace_id', $user_data['workspace_id'])
                    ->get();
                $workspace = \App\Models\WorkSpace::where('id', $user_data['workspace_id'])->first();
            @endphp
            <div class="tab-pane fade {{ $loop->index == 0 ? 'show active' : '' }}"
                id="pills-{{ $workspace->id }}" role="tabpanel"
                aria-labelledby="pills-{{ $workspace->id }}-tab">

                {{-- Workspace Enable/Disable Banner --}}
                <div class="ci-ws-toggle-bar mb-3">
                    <div class="ci-ws-warning">
                        <i class="ti ti-alert-triangle me-1"></i>
                        {{ __('Disabling this workspace will also disable all users within it.') }}
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <span class="rc-status {{ $workspace->is_disable == 1 ? 'rc-status-success' : 'rc-status-inactive' }}" style="font-size: 11px;" id="ws-status-{{ $workspace->id }}">
                            {{ $workspace->is_disable == 1 ? __('Enabled') : __('Disabled') }}
                        </span>
                        <div class="form-check form-switch custom-switch-v1 mb-0">
                            <input type="checkbox" name="workspace_disable"
                                class="form-check-input input-primary is_disable" value="1"
                                data-id="{{ $user_data['workspace_id'] }}" data-company="{{ $id }}"
                                data-name="{{ __('workspace') }}"
                                {{ $workspace->is_disable == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="workspace_disable"></label>
                        </div>
                    </div>
                </div>

                {{-- User Stats + User List --}}
                <div class="d-flex align-items-center justify-content-between mb-3 workspace" data-workspace-id="{{ $workspace->id }}">
                    <div class="ci-section-label mb-0">{{ __('Users') }} ({{ $users->count() }})</div>
                    <div class="ci-mini-stats">
                        <div class="ci-mini-stat">
                            <span class="ci-mini-stat-dot" style="background: var(--rc-primary);"></span>
                            <span class="ci-mini-stat-count total_users">{{ $user_data['total_users'] }}</span> {{ __('Total') }}
                        </div>
                        <div class="ci-mini-stat">
                            <span class="ci-mini-stat-dot" style="background: var(--rc-success);"></span>
                            <span class="ci-mini-stat-count active_users">{{ $user_data['active_users'] }}</span> {{ __('Active') }}
                        </div>
                        <div class="ci-mini-stat">
                            <span class="ci-mini-stat-dot" style="background: var(--rc-danger);"></span>
                            <span class="ci-mini-stat-count disable_users">{{ $user_data['disable_users'] }}</span> {{ __('Disabled') }}
                        </div>
                    </div>
                </div>

                <div class="row g-2" id="user_section_{{ $workspace->id }}">
                    @foreach ($users as $user)
                        <div class="col-md-6">
                            <div class="ci-user-item">
                                <div class="ci-user-info">
                                    @php
                                        $avatarUrl = getAvatarUrl($user->avatar);
                                        $initials = strtoupper(substr($user->name, 0, 1));
                                    @endphp
                                    @if (!empty($avatarUrl))
                                        <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" class="ci-user-avatar">
                                    @else
                                        <div class="ci-user-avatar-placeholder">{{ $initials }}</div>
                                    @endif
                                    <span class="ci-user-name">{{ $user->name }}</span>
                                </div>
                                <div class="form-check form-switch custom-switch-v1 mb-0">
                                    <input type="checkbox" name="user_disable"
                                        class="form-check-input input-primary is_disable"
                                        value="1" data-id="{{ $user->id }}" data-company="{{ $id }}"
                                        data-name="{{ __('user') }}"
                                        {{ $user->is_disable == 1 ? 'checked' : '' }}
                                        {{ $workspace->is_disable == 1 ? '' : 'disabled' }}>
                                    <label class="form-check-label" for="user_disable"></label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @if($users->isEmpty())
                        <div class="col-12 text-center py-3">
                            <span class="text-muted" style="font-size: var(--rc-font-sm);">{{ __('No users in this workspace.') }}</span>
                        </div>
                    @endif
                </div>

                @if (moduleIsActive('LMS'))
                    @php
                        $stores = \Modules\LMS\Entities\Store::where('workspace_id', $workspace->id)->first();
                    @endphp
                    @if(isset($stores))
                        <div class="mt-3">
                            <div class="ci-section-label">{{ __('Store') }}</div>
                            <div class="ci-store-info">
                                <i class="ti ti-building-store"></i>
                                <span>{{ $stores->name }}</span>
                            </div>
                        </div>
                    @endif
                @endif

            </div>
        @endforeach
    </div>
</div>

<script>
    $(document).on("click", ".is_disable", function() {
        var id = $(this).attr('data-id');
        var name = $(this).attr('data-name');
        var company_id = $(this).attr('data-company');
        var is_disable = ($(this).is(':checked')) ? $(this).val() : 0;

        $.ajax({
            url: '{{ route('user.unable') }}',
            type: 'POST',
            data: {
                "is_disable": is_disable,
                "id": id,
                "name": name,
                "company_id": company_id,
                "_token": "{{ csrf_token() }}",
            },
            success: function(data) {
                if (data.success) {
                    if (name == 'workspace') {
                        var container = document.getElementById('user_section_' + id);
                        var checkboxes = container.querySelectorAll('input[type="checkbox"]');
                        checkboxes.forEach(function(checkbox) {
                            if (is_disable == 0) {
                                checkbox.disabled = true;
                                checkbox.checked = false;
                            } else {
                                checkbox.disabled = false;
                            }
                        });

                        // Update workspace status badge
                        var statusBadge = document.getElementById('ws-status-' + id);
                        if (statusBadge) {
                            if (is_disable == 1) {
                                statusBadge.className = 'rc-status rc-status-success';
                                statusBadge.style.fontSize = '11px';
                                statusBadge.textContent = '{{ __("Enabled") }}';
                            } else {
                                statusBadge.className = 'rc-status rc-status-inactive';
                                statusBadge.style.fontSize = '11px';
                                statusBadge.textContent = '{{ __("Disabled") }}';
                            }
                        }
                    }
                    $('.active_workspace').text(data.workspce_data.active_workspace);
                    $('.disable_workspace').text(data.workspce_data.disable_workspace);
                    $('.total_workspace').text(data.workspce_data.total_workspace);
                    $.each(data.users_data, function(workspaceName, userData) {
                        var $workspaceElements = $('.workspace[data-workspace-id="' + userData.workspace_id + '"]');
                        $workspaceElements.find('.total_users').text(userData.total_users);
                        $workspaceElements.find('.active_users').text(userData.active_users);
                        $workspaceElements.find('.disable_users').text(userData.disable_users);
                    });

                    toastrs('success', data.success, 'success');
                } else {
                    toastrs('error', data.error, 'error');
                }
            }
        });
    });
</script>
