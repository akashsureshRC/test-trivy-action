@php
    $company_settings = getCompanyAllSetting();
@endphp
{{ Form::model($department, ['route' => ['department.update', $department->id], 'method' => 'PUT', 'id' => 'departmentForm']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('branch_id', !empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch'), ['class' => 'form-label']) }}<span
                    class="text-danger">*</span>
                {{ Form::select('branch_id', $branch, null, ['class' => 'form-control', 'placeholder' => __('Select '.(!empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('select Branch'))), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}<span
                    class="text-danger">*</span>
                {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Department Name')]) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Save Changes'), ['class' => 'btn btn-rc-primary', 'id' => 'saveDepartmentBtn']) }}
</div>
{{ Form::close() }}
<script>
    $('#saveDepartmentBtn').click(function (e) {
    e.preventDefault();

    let form = $('#departmentForm');
    let formData = form.serialize();

    // Clear old errors
    form.find('.text-danger').remove();
    $.ajax({
        url: form.attr('action'),
        method: 'POST',
        data: formData,
        success: function (response) {
            if (response.success) {
                $('#commonModal').modal('hide');
                toastr.success(response.message);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                // Validation error
                let errors = xhr.responseJSON.errors;
                $.each(errors, function (key, messages) {
                    let field = form.find('[name="' + key + '"]');
                    if (field.length) {
                        field.after('<small class="text-danger">' + messages[0] + '</small>');
                    }
                });
            } else if (xhr.status === 403) {
                // Permission denied
                toastr.error(xhr.responseJSON.error || 'Permission denied.');
            } else {
                // Other errors
                toastr.error('An unexpected error occurred.');
            }
        }
    });
});
    </script>
