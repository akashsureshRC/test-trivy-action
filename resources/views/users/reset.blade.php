{{Form::model($user,array('route' => array('user.password.update', $user->id), 'method' => 'post', 'id' => 'resetPasswordForm')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('password', __('Password'), ['class' => 'form-label']) }}
                <span class="text-danger">*</span>
                <input id="reset_password" type="password" class="form-control" name="password" autocomplete="new-password" placeholder="{{ __('Enter Password') }}">
                <p class="text-danger d-none" id="reset_password_validation">{{ __('Password must be at least 6 characters.') }}</p>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('password_confirmation', __('Confirm Password'), ['class' => 'form-label']) }}
                <span class="text-danger">*</span>
                <input id="reset_password_confirm" type="password" class="form-control" name="password_confirmation" autocomplete="new-password" placeholder="{{ __('Enter Confirm Password') }}">
                <p class="text-danger d-none" id="reset_confirm_validation">{{ __('Confirm Password field is required.') }}</p>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{__('Cancel')}}</button>
    <button type="button" id="resetPasswordBtn" class="btn btn-rc-primary">{{__('Reset')}}</button>
</div>
{{Form::close()}}

<script>
$(document).ready(function() {
    $('#reset_password, #reset_password_confirm').on('input', function() {
        $(this).closest('.form-group').find('.text-danger[id$="_validation"]').addClass('d-none');
    });

    $('#resetPasswordBtn').on('click', function() {
        var isValid = true;

        var password = $('#reset_password').val();
        if (!password || password.length < 6) {
            $('#reset_password_validation').removeClass('d-none');
            isValid = false;
        } else {
            $('#reset_password_validation').addClass('d-none');
        }

        var confirm = $('#reset_password_confirm').val();
        if (!confirm || confirm.trim() === '') {
            $('#reset_confirm_validation').text('{{ __("Confirm Password field is required.") }}').removeClass('d-none');
            isValid = false;
        } else if (confirm !== password) {
            $('#reset_confirm_validation').text('{{ __("Passwords do not match.") }}').removeClass('d-none');
            isValid = false;
        } else {
            $('#reset_confirm_validation').addClass('d-none');
        }

        if (!isValid) return;

        var form = $('#resetPasswordForm');
        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    toastrs('Success', response.message || '{{ __("Password reset successfully.") }}', 'success');
                    $('.modal').modal('hide');
                    location.reload();
                } else if (response.error) {
                    toastrs('Error', response.error, 'error');
                } else {
                    toastrs('Success', '{{ __("Password reset successfully.") }}', 'success');
                    $('.modal').modal('hide');
                    location.reload();
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON;
                if (errors && errors.errors) {
                    if (errors.errors.password) {
                        $('#reset_password_validation').text(errors.errors.password[0]).removeClass('d-none');
                    }
                    if (errors.errors.password_confirmation) {
                        $('#reset_confirm_validation').text(errors.errors.password_confirmation[0]).removeClass('d-none');
                    }
                } else {
                    toastrs('Error', '{{ __("An error occurred.") }}', 'error');
                }
            }
        });
    });
});
</script>