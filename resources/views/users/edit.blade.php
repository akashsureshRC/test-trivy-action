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
@endphp
<form id="userEditForm" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('name',__('Name'),['class'=>'form-label']) }}
                    <span class="text-danger">*</span>
                    {{Form::text('name',$user->name,array('class'=>'form-control','placeholder'=>__('Enter '.($name).' Name'),'id'=>'user_name'))}}
                    <p class="text-danger d-none" id="name_validation">{{ __('Name field is required.') }}</p>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('email',__('Email'),['class'=>'form-label'])}}
                    <span class="text-danger">*</span>
                    {{Form::email('email',$user->email,array('class'=>'form-control','placeholder'=>__('Enter '.($name).' Email'),'id'=>'user_email'))}}
                    <p class="text-danger d-none" id="email_validation">{{ __('Email field is required.') }}</p>
                </div>
            </div>
            @if(!$isSuperOrMaster)
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('mobile_no',__('Mobile No'),['class'=>'form-label'])}}
                        {{Form::text('mobile_no',$user->mobile_no,array('class'=>'form-control','placeholder'=>__('Enter User Mobile No'),'id'=>'user_mobile','maxlength'=>'15','pattern'=>'[0-9+]+','oninput'=>'this.value = this.value.replace(/[^0-9+]/g, "")'))}}
                        <div class=" text-xs text-danger">
                            {{ __('Please add mobile number with country code. (ex. +91)') }}
                        </div>
                        <p class="text-danger d-none" id="mobile_validation">{{ __('Mobile number must be at least 9 characters.') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{__('Cancel')}}</button>
        <button type="button" id="userSubmitBtn" class="btn btn-rc-primary">{{__('Update')}}</button>
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
    
    if (!isValid) {
        return;
    }
    
    $(".loader-wrapper").removeClass('d-none');
    
    var formData = new FormData(document.getElementById('userEditForm'));
    
    fetch('{{ route("users.update", $user->id) }}', {
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
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                location.reload();
            }
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

document.getElementById('userEditForm').addEventListener('submit', function(e) {
    e.preventDefault();
    return false;
});
</script>

