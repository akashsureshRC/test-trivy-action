@extends('layouts.main')
@section('page-title')
    {{ __('Create Customer') }}
@endsection
@section('page-breadcrumb')
    {{ __('Customers') }}
@endsection
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Create Customer') }}</h5>
            </div>
            <div class="card-body">
                <form id="masterAdminCompanyCreateForm" action="{{ route('master-admin.companies.store') }}" method="POST" onsubmit="return false;">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                                <span class="text-danger">*</span>
                                {{ Form::text('name', null, ['class' => 'form-control','placeholder' => __('Enter Customer Name'),'id' => 'company_name', 'onkeypress' => 'blockNumbers(event)']) }}
                                <p class="text-danger d-none" id="company_name_validation">{{ __('Name field is required.') }}</p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                {{ Form::label('workSpace_name', __('WorkSpace Name'), ['class' => 'form-label']) }}
                                <span class="text-danger">*</span>
                                {{ Form::text('workSpace_name', null, ['class' => 'form-control','placeholder' => __('Enter WorkSpace Name'),'id' => 'workspace_name']) }}
                                <p class="text-danger d-none" id="workspace_name_validation">{{ __('WorkSpace Name field is required.') }}</p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                {{ Form::label('plan_type', __('Plan Type'), ['class' => 'form-label']) }}
                                <span class="text-danger">*</span>
                                <select name="plan_type" id="plan_type" class="form-control">
                                    <option value="trial" selected>{{ __('Trial Plan') }}</option>
                                    <option value="paid">{{ __('Paid Plan') }}</option>
                                </select>
                                <div class="text-xs text-muted mt-1">
                                    {{ __('Trial: Customer starts with free trial. Paid: Customer starts with paid billing immediately.') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
                                <span class="text-danger">*</span>
                                {{ Form::email('email', null, ['class' => 'form-control','placeholder' => __('Enter Customer Email'),'id' => 'company_email']) }}
                                <p class="text-danger d-none" id="company_email_validation">{{ __('Email field is required.') }}</p>
                            </div>
                        </div>

                        <div class="col-md-5 mb-3">
                            <label for="password_switch">{{ __('Login is enable') }}</label>
                            <div class="form-check form-switch custom-switch-v1 float-end">
                                <input type="checkbox" name="password_switch" class="form-check-input input-primary pointer" value="on" id="password_switch" {{ company_setting('password_switch')=='on'?' checked ':'' }}>
                                <label class="form-check-label" for="password_switch"></label>
                            </div>
                        </div>
                        <div class="col-md-12 ps_div d-none">
                            <div class="form-group">
                                {{ Form::label('password', __('Password'), ['class' => 'form-label']) }}
                                {{ Form::password('password', ['class' => 'form-control','placeholder' => __('Enter Password'),'minlength' => "6",'id' => 'company_password']) }}
                                <p class="text-danger d-none" id="password_validation">{{ __('Password must be at least 6 characters.') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="{{ route('master-admin.companies') }}" class="btn btn-rc-outline">{{ __('Cancel') }}</a>
                        <button type="button" id="companySubmitBtn" class="btn btn-rc-primary">{{ __('Create') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('companySubmitBtn').addEventListener('click', function() {
    var isValid = true;

    var name = document.getElementById('company_name').value;
    var nameValidation = document.getElementById('company_name_validation');
    if (!name || name.trim() === '') {
        nameValidation.classList.remove('d-none');
        nameValidation.style.display = 'block';
        isValid = false;
    } else {
        nameValidation.classList.add('d-none');
        nameValidation.style.display = 'none';
    }

    var workspaceName = document.getElementById('workspace_name').value;
    var workspaceNameValidation = document.getElementById('workspace_name_validation');
    if (!workspaceName || workspaceName.trim() === '') {
        workspaceNameValidation.classList.remove('d-none');
        workspaceNameValidation.style.display = 'block';
        isValid = false;
    } else {
        workspaceNameValidation.classList.add('d-none');
        workspaceNameValidation.style.display = 'none';
    }

    var email = document.getElementById('company_email').value;
    var emailValidation = document.getElementById('company_email_validation');
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email || email.trim() === '') {
        emailValidation.textContent = '{{ __("Email field is required.") }}';
        emailValidation.classList.remove('d-none');
        emailValidation.style.display = 'block';
        isValid = false;
    } else if (!emailRegex.test(email)) {
        emailValidation.textContent = '{{ __("Please enter a valid email address.") }}';
        emailValidation.classList.remove('d-none');
        emailValidation.style.display = 'block';
        isValid = false;
    } else {
        emailValidation.classList.add('d-none');
        emailValidation.style.display = 'none';
    }

    var passwordSwitch = document.getElementById('password_switch');
    var passwordValidation = document.getElementById('password_validation');
    if (passwordSwitch && passwordSwitch.checked) {
        var password = document.getElementById('company_password').value;
        if (!password || password.length < 6) {
            passwordValidation.classList.remove('d-none');
            passwordValidation.style.display = 'block';
            isValid = false;
        } else {
            passwordValidation.classList.add('d-none');
            passwordValidation.style.display = 'none';
        }
    } else {
        passwordValidation.classList.add('d-none');
        passwordValidation.style.display = 'none';
    }

    if (!isValid) {
        return;
    }

    if (window.jQuery) {
        jQuery(".loader-wrapper").removeClass('d-none');
    }

    var formData = new FormData(document.getElementById('masterAdminCompanyCreateForm'));

    fetch('{{ route("master-admin.companies.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (window.jQuery) {
            jQuery(".loader-wrapper").addClass('d-none');
        }

        if (data.success) {
            window.location.href = '{{ route("master-admin.companies") }}';
        } else if (data.error) {
            if (data.error.includes('email') || data.error.includes('Email')) {
                var emailValidation = document.getElementById('company_email_validation');
                emailValidation.textContent = data.error;
                emailValidation.classList.remove('d-none');
                emailValidation.style.display = 'block';
            } else {
                var nameValidation = document.getElementById('company_name_validation');
                nameValidation.textContent = data.error;
                nameValidation.classList.remove('d-none');
                nameValidation.style.display = 'block';
            }
        }
    })
    .catch(error => {
        if (window.jQuery) {
            jQuery(".loader-wrapper").addClass('d-none');
        }
        console.error('Error:', error);
        alert('An error occurred while processing your request.');
    });
});

document.getElementById('company_name').addEventListener('input', function() {
    var nameValidation = document.getElementById('company_name_validation');
    nameValidation.classList.add('d-none');
    nameValidation.style.display = 'none';
});

document.getElementById('workspace_name').addEventListener('input', function() {
    var workspaceNameValidation = document.getElementById('workspace_name_validation');
    workspaceNameValidation.classList.add('d-none');
    workspaceNameValidation.style.display = 'none';
});

document.getElementById('company_email').addEventListener('input', function() {
    var emailValidation = document.getElementById('company_email_validation');
    emailValidation.classList.add('d-none');
    emailValidation.style.display = 'none';
});

if (window.jQuery) {
    jQuery(document).on('change', '#password_switch', function() {
        if(jQuery(this).is(':checked'))
        {
            jQuery('.ps_div').removeClass('d-none');
            jQuery('#company_password').attr("required",true);

        } else {
            jQuery('.ps_div').addClass('d-none');
            jQuery('#company_password').val(null);
            jQuery('#company_password').removeAttr("required");
        }
    });
}
</script>
@endpush
