@php
    $isSuperAdmin = Auth::user()->type == 'super admin';
    $isMasterAdmin = Auth::user()->type == 'master_admin';
    $isSuperOrMaster = $isSuperAdmin || $isMasterAdmin;

    if($isSuperOrMaster)
    {
        $name = __('Customer');
    }
    else{

        $name =__('User');
    }

    $formAction = $isMasterAdmin ? route('master-admin.companies.store') : route('users.store');
    $defaultRedirect = request()->is('ma/*') ? route('master-admin.companies') : route('users.index');
@endphp
<form id="userCreateForm" action="{{ $formAction }}" method="POST" onsubmit="return false;" autocomplete="off">
@csrf
    <div class="modal-body">
        <input type="text" name="fake_username" autocomplete="username" class="d-none" tabindex="-1" aria-hidden="true">
        <input type="password" name="fake_password" autocomplete="new-password" class="d-none" tabindex="-1" aria-hidden="true">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('name',__('Name'),['class'=>'form-label']) }}
                    <span class="text-danger">*</span>
                    {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter '.($name).' Name'),'id'=>'user_name','autocomplete'=>'off','autocorrect'=>'off','autocapitalize'=>'off','spellcheck'=>'false','onkeypress' => 'blockNumbers(event)'))}}
                    <p class="text-danger d-none" id="name_validation">{{ __('Name field is required.') }}</p>
                </div>
            </div>
            @if($isSuperOrMaster)
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('workSpace_name',__('Workspace Name'),['class'=>'form-label']) }}
                        <span class="text-danger">*</span>
                        {{Form::text('workSpace_name',null,array('class'=>'form-control','placeholder'=>__('Enter Workspace Name'),'id'=>'workspace_name','autocomplete'=>'off','autocorrect'=>'off','autocapitalize'=>'off','spellcheck'=>'false'))}}
                        <p class="text-danger d-none" id="workspace_name_validation">{{ __('Workspace Name field is required.') }}</p>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('plan_type',__('Plan Type'),['class'=>'form-label']) }}
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
            @endif
            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('email',__('Email'),['class'=>'form-label'])}}
                    <span class="text-danger">*</span>
                    {{Form::email('email',null,array('class'=>'form-control','placeholder'=>__('Enter '.($name).' Email'),'id'=>'user_email'))}}
                    <p class="text-danger d-none" id="email_validation">{{ __('Email field is required.') }}</p>
                </div>
            </div>
            @if(!$isSuperOrMaster)
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('mobile_no',__('Mobile No'),['class'=>'form-label'])}}
                        {{Form::text('mobile_no',null,array('class'=>'form-control','placeholder'=>__('Enter User Mobile No'),'id'=>'user_mobile','pattern'=>'[0-9+]+','maxlength'=>'15','oninput'=>"this.value = this.value.replace(/[^0-9+]/g, '');"))}}
                        <div class=" text-xs text-danger">
                            {{ __('Please add mobile number with country code. (ex. +91)') }}
                        </div>
                        <p class="text-danger d-none" id="mobile_validation">{{ __('Mobile number must be at least 9 characters.') }}</p>
                    </div>
                </div>
            @endif

            <div class="col-md-5 mb-3">
                <label for="password_switch">{{ __('Login is enable') }}</label>
                <div class="form-check form-switch custom-switch-v1 float-end">
                    <input type="checkbox" name="password_switch" class="form-check-input input-primary pointer" value="on" id="password_switch" {{ companySetting('password_switch')=='on'?' checked ':'' }}>
                    <label class="form-check-label" for="password_switch"></label>
                </div>
            </div>
            <div class="col-md-12 ps_div d-none">
                <div class="form-group">
                    {{Form::label('password',__('Password'),['class'=>'form-label'])}}
                    <span class="text-danger">*</span>
                    {{Form::password('password',array('class'=>'form-control','placeholder'=>__('Enter User Password'),'minlength'=>"6",'id'=>'user_password'))}}
                    <p class="text-danger d-none" id="password_validation">{{ __('Password must be at least 6 characters.') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{__('Cancel')}}</button>
        <button type="button" id="userSubmitBtn" class="btn btn-rc-primary">{{__('Create')}}</button>
    </div>
</form>

<script>
document.getElementById('userSubmitBtn').addEventListener('click', function() {
    var isValid = true;
    
    var name = document.getElementById('user_name').value;
    var nameValidation = document.getElementById('name_validation');
    if (!name || name.trim() === '') {
        nameValidation.classList.remove('d-none');
        nameValidation.style.display = 'block';
        isValid = false;
    } else {
        nameValidation.classList.add('d-none');
        nameValidation.style.display = 'none';
    }
    
    @if($isSuperOrMaster)
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
    @endif
    
    var email = document.getElementById('user_email').value;
    var emailValidation = document.getElementById('email_validation');
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
    
    @if(!$isSuperOrMaster)
    var mobile = document.getElementById('user_mobile').value;
    var mobileValidation = document.getElementById('mobile_validation');
    if (mobile && mobile.trim() !== '') {
        var mobileRegex = /^([0-9\s\-\+\(\)]*)$/;
        if (!mobileRegex.test(mobile) || mobile.length < 9) {
            mobileValidation.classList.remove('d-none');
            mobileValidation.style.display = 'block';
            isValid = false;
        } else {
            mobileValidation.classList.add('d-none');
            mobileValidation.style.display = 'none';
        }
    } else {
        mobileValidation.classList.add('d-none');
        mobileValidation.style.display = 'none';
    }
    @endif
    
    var passwordSwitch = document.getElementById('password_switch');
    var passwordValidation = document.getElementById('password_validation');
    if (passwordSwitch && passwordSwitch.checked) {
        var password = document.getElementById('user_password').value;
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
    
    $(".loader-wrapper").removeClass('d-none');
    
    var formData = new FormData(document.getElementById('userCreateForm'));
    
    fetch('{{ $formAction }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        $(".loader-wrapper").addClass('d-none');
        
        if (data.success) {
            window.location.href = data.redirect ? data.redirect : '{{ $defaultRedirect }}';
        } else if (data.error) {
            if (data.error.includes('email') || data.error.includes('Email')) {
                var emailValidation = document.getElementById('email_validation');
                emailValidation.textContent = data.error;
                emailValidation.classList.remove('d-none');
                emailValidation.style.display = 'block';
            } else {
                var nameValidation = document.getElementById('name_validation');
                nameValidation.textContent = data.error;
                nameValidation.classList.remove('d-none');
                nameValidation.style.display = 'block';
            }
        }
    })
    .catch(error => {
        $(".loader-wrapper").addClass('d-none');
        console.error('Error:', error);
        alert('An error occurred while processing your request.');
    });
});

document.getElementById('user_name').addEventListener('input', function() {
    var nameValidation = document.getElementById('name_validation');
    nameValidation.classList.add('d-none');
    nameValidation.style.display = 'none';
});

@if($isSuperOrMaster)
document.getElementById('workspace_name').addEventListener('input', function() {
    var workspaceNameValidation = document.getElementById('workspace_name_validation');
    workspaceNameValidation.classList.add('d-none');
    workspaceNameValidation.style.display = 'none';
});
@endif

document.getElementById('user_email').addEventListener('input', function() {
    var emailValidation = document.getElementById('email_validation');
    emailValidation.classList.add('d-none');
    emailValidation.style.display = 'none';
});

@if(!$isSuperOrMaster)
document.getElementById('user_mobile').addEventListener('input', function() {
    var mobileValidation = document.getElementById('mobile_validation');
    mobileValidation.classList.add('d-none');
    mobileValidation.style.display = 'none';
});
@endif

document.getElementById('user_password').addEventListener('input', function() {
    var passwordValidation = document.getElementById('password_validation');
    passwordValidation.classList.add('d-none');
    passwordValidation.style.display = 'none';
});

document.getElementById('password_switch').addEventListener('change', function() {
    var psDiv = document.querySelector('.ps_div');
    if (this.checked) {
        psDiv.classList.remove('d-none');
    } else {
        psDiv.classList.add('d-none');
        var passwordValidation = document.getElementById('password_validation');
        passwordValidation.classList.add('d-none');
        passwordValidation.style.display = 'none';
    }
});

document.getElementById('userCreateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    return false;
});
</script>
<script>
    function blockNumbers(event) {
        const char = String.fromCharCode(event.which);
        const regex = /^[A-Za-z\s]+$/;
        if (!regex.test(char)) {
            event.preventDefault();
        }
    }
</script>
<script>
    function allowOnlyNumbers(event) {
        const char = String.fromCharCode(event.which);
        const regex = /^[0-9]+$/;
        if (!regex.test(char)) {
            event.preventDefault();
        }
    }
</script>