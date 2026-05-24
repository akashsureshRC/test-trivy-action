<style>
    .company-checkbox-list {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid var(--rc-border);
        border-radius: var(--rc-radius-sm);
        padding: 0;
    }
    .company-checkbox-item {
        padding: var(--rc-space-2) var(--rc-space-3);
        border-bottom: 1px solid var(--rc-border-light);
    }
    .company-checkbox-item:last-child {
        border-bottom: none;
    }
    .company-checkbox-item:hover {
        background-color: var(--rc-gray-50);
    }
</style>

<div class="bg-none card-box">
    {{ Form::open(['route' => 'master-admin.store', 'method' => 'post', 'id' => 'masterAdminForm', 'data-custom-submit' => 'true', 'autocomplete' => 'off']) }}
    <input type="text" name="fake_username" autocomplete="username" class="d-none" tabindex="-1" aria-hidden="true">
    <input type="password" name="fake_password" autocomplete="new-password" class="d-none" tabindex="-1" aria-hidden="true">
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('role', __('Role'), ['class' => 'form-label']) }}
                    <span class="text-danger">*</span>
                    <select name="role" id="ma_role" class="form-control">
                        @foreach($roleOptions as $value => $label)
                        <option value="{{ $value }}">{{ __($label) }}</option>
                        @endforeach
                    </select>
                    <p class="text-danger d-none" id="ma_role_validation">{{ __('Role field is required.') }}</p>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                    <span class="text-danger">*</span>
                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Name'), 'id' => 'ma_name']) }}
                    <p class="text-danger d-none" id="ma_name_validation">{{ __('Name field is required.') }}</p>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
                    <span class="text-danger">*</span>
                    {{ Form::email('email', null, ['class' => 'form-control', 'placeholder' => __('Enter Email'), 'id' => 'ma_email', 'autocomplete' => 'off']) }}
                    <p class="text-danger d-none" id="ma_email_validation">{{ __('Email field is required.') }}</p>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('password', __('Password'), ['class' => 'form-label']) }}
                    <span class="text-danger">*</span>
                    {{ Form::password('password', ['class' => 'form-control', 'placeholder' => __('Enter Password (min 6 characters)'), 'id' => 'ma_password', 'autocomplete' => 'new-password']) }}
                    <p class="text-danger d-none" id="ma_password_validation">{{ __('Password must be at least 6 characters.') }}</p>
                </div>
            </div>
            <div class="col-md-12" id="companies_section">
                <div class="form-group">
                    {{ Form::label('companies', __('Assign Customers'), ['class' => 'form-label']) }}
                    <div class="text-muted small mb-2">{{ __('Select customers this administrator will manage') }}</div>
                    @if($companies->count() > 0)
                        <div class="company-checkbox-list">
                            @foreach($companies as $id => $name)
                                <div class="company-checkbox-item">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="companies[]" 
                                               value="{{ $id }}" id="company_{{ $id }}">
                                        <label class="form-check-label" for="company_{{ $id }}">
                                            {{ $name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            {{ __('No companies available. Create companies first.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="btn btn-rc-primary">{{ __('Create') }}</button>
    </div>
    {{ Form::close() }}
</div>

<script>
    $(document).ready(function() {
        // Toggle Assign Customers section based on role
        function toggleCompaniesSection() {
            var role = $('#ma_role').val();
            if (role === 'master_admin') {
                $('#companies_section').slideDown(200);
            } else {
                $('#companies_section').slideUp(200);
                $('#companies_section input[type="checkbox"]').prop('checked', false);
            }
        }

        // Initial state — default first option is 'super admin', so hide companies
        toggleCompaniesSection();

        $('#ma_role').on('change', function() {
            toggleCompaniesSection();
        });

        $(document).on('shown.bs.modal', function(e) {
            if ($(e.target).find('#masterAdminForm').length) {
                $('#ma_email, #ma_password').val('');
                toggleCompaniesSection();
            }
        });

        $('#ma_name, #ma_email, #ma_password').on('input', function() {
            $(this).closest('.form-group').find('.text-danger[id$="_validation"]').addClass('d-none');
        });

        $('#masterAdminForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            var isValid = true;

            var name = $('#ma_name').val();
            if (!name || name.trim() === '') {
                $('#ma_name_validation').removeClass('d-none');
                isValid = false;
            } else {
                $('#ma_name_validation').addClass('d-none');
            }

            var email = $('#ma_email').val();
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email || email.trim() === '') {
                $('#ma_email_validation').text('{{ __("Email field is required.") }}').removeClass('d-none');
                isValid = false;
            } else if (!emailRegex.test(email)) {
                $('#ma_email_validation').text('{{ __("Please enter a valid email address.") }}').removeClass('d-none');
                isValid = false;
            } else {
                $('#ma_email_validation').addClass('d-none');
            }

            var password = $('#ma_password').val();
            if (!password || password.length < 6) {
                $('#ma_password_validation').removeClass('d-none');
                isValid = false;
            } else {
                $('#ma_password_validation').addClass('d-none');
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
                    var error = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred';
                    toastrs('Error', error, 'error');
                }
            });
        });
    });
</script>
