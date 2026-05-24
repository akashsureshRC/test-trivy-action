<style>
    .company-checkbox-list {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid var(--rc-border);
        border-radius: 6px;
        padding: 10px;
    }
    .company-checkbox-item {
        padding: 8px 12px;
        border-bottom: 1px solid var(--rc-border-light, var(--rc-border));
    }
    .company-checkbox-item:last-child {
        border-bottom: none;
    }
    .company-checkbox-item:hover {
        background-color: var(--rc-gray-50);
    }
</style>

<div class="bg-none card-box">
    {{ Form::open(['route' => ['master-admin.update', $admin->id], 'method' => 'PUT', 'id' => 'masterAdminEditForm', 'data-custom-submit' => 'true']) }}
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('role', __('Role'), ['class' => 'form-label']) }}
                    <span class="text-danger">*</span>
                    <select name="role" id="ma_edit_role" class="form-control">
                        @foreach($roleOptions as $value => $label)
                        <option value="{{ $value }}" {{ $admin->type === $value ? 'selected' : '' }}>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                    <span class="text-danger">*</span>
                    {{ Form::text('name', $admin->name, ['class' => 'form-control', 'placeholder' => __('Enter Name'), 'id' => 'ma_edit_name']) }}
                    <p class="text-danger d-none" id="ma_edit_name_validation">{{ __('Name field is required.') }}</p>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
                    <span class="text-danger">*</span>
                    {{ Form::email('email', $admin->email, ['class' => 'form-control', 'placeholder' => __('Enter Email'), 'id' => 'ma_edit_email']) }}
                    <p class="text-danger d-none" id="ma_edit_email_validation">{{ __('Email field is required.') }}</p>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('password', __('Password'), ['class' => 'form-label']) }}
                    {{ Form::password('password', ['class' => 'form-control', 'minlength' => '6', 'placeholder' => __('Leave blank to keep current password'), 'autocomplete' => 'new-password']) }}
                    <p class="text-danger d-none" id="ma_edit_password_validation">{{ __('Password must be at least 6 characters.') }}</p>
                    <small class="text-muted">{{ __('Leave blank to keep the current password') }}</small>
                </div>
            </div>
            <div class="col-md-12" id="edit_companies_section">
                <div class="form-group">
                    {{ Form::label('companies', __('Assign Customers'), ['class' => 'form-label']) }}
                    <div class="text-muted small mb-2">{{ __('Select customers this administrator will manage') }}</div>
                    @if($companies->count() > 0)
                        <div class="company-checkbox-list">
                            @foreach($companies as $id => $name)
                                <div class="company-checkbox-item">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="companies[]" 
                                               value="{{ $id }}" id="company_edit_{{ $id }}"
                                               {{ in_array($id, $assignedCompanyIds) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="company_edit_{{ $id }}">
                                            {{ $name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            {{ __('No companies available.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="btn btn-rc-primary">{{ __('Update') }}</button>
    </div>
    {{ Form::close() }}
</div>

<script>
    $(document).ready(function() {
        // Toggle Assign Customers section based on role
        function toggleEditCompaniesSection() {
            var role = $('#ma_edit_role').val();
            if (role === 'master_admin') {
                $('#edit_companies_section').slideDown(200);
            } else {
                $('#edit_companies_section').slideUp(200);
            }
        }

        // Initial state
        toggleEditCompaniesSection();

        $('#ma_edit_role').on('change', function() {
            toggleEditCompaniesSection();
        });

        $('#ma_edit_name, #ma_edit_email, #masterAdminEditForm input[name="password"]').on('input', function() {
            $(this).closest('.form-group').find('.text-danger[id$="_validation"]').addClass('d-none');
        });

        $('#masterAdminEditForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            var isValid = true;

            var name = $('#ma_edit_name').val();
            if (!name || name.trim() === '') {
                $('#ma_edit_name_validation').removeClass('d-none');
                isValid = false;
            } else {
                $('#ma_edit_name_validation').addClass('d-none');
            }

            var email = $('#ma_edit_email').val();
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email || email.trim() === '') {
                $('#ma_edit_email_validation').text('{{ __("Email field is required.") }}').removeClass('d-none');
                isValid = false;
            } else if (!emailRegex.test(email)) {
                $('#ma_edit_email_validation').text('{{ __("Please enter a valid email address.") }}').removeClass('d-none');
                isValid = false;
            } else {
                $('#ma_edit_email_validation').addClass('d-none');
            }

            // Client-side password length check (only if a value was entered)
            var password = $('#masterAdminEditForm input[name="password"]').val();
            if (password && password.length < 6) {
                $('#ma_edit_password_validation').removeClass('d-none');
                isValid = false;
            } else {
                $('#ma_edit_password_validation').addClass('d-none');
            }

            if (!isValid) return;

            $.ajax({
                type: 'POST',
                url: url,
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        toastrs('Success', response.message, 'success');
                        $('.modal').modal('hide');
                        location.reload();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        if (errors.password) {
                            $('#ma_edit_password_validation').text(errors.password[0]).removeClass('d-none');
                            return;
                        }
                        if (errors.name) {
                            $('#ma_edit_name_validation').text(errors.name[0]).removeClass('d-none');
                            return;
                        }
                        if (errors.email) {
                            $('#ma_edit_email_validation').text(errors.email[0]).removeClass('d-none');
                            return;
                        }
                    }
                    var error = xhr.responseJSON ? (xhr.responseJSON.error || xhr.responseJSON.message) : 'An error occurred';
                    toastrs('Error', error, 'error');
                }
            });
        });
    });
</script>
