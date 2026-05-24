@php
    $company_settings = getCompanyAllSetting();
@endphp
{{ Form::model($designation, ['route' => ['designation.update', $designation->id], 'method' => 'PUT', 'id' => 'designationForm']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('branch_id', !empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch'), ['class' => 'form-label']) }}<span
                    class="text-danger">*</span>
                {{ Form::select('branch_id', $branchs, null, ['class' => 'form-control', 'placeholder' => __('Select ' . (!empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch'))), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('department_id', !empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department'), ['class' => 'form-label']) }}<span
                    class="text-danger">*</span>
                {{ Form::select('department_id', $departments, null, ['class' => 'form-control', 'placeholder' => __('Select ' . (!empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department'))), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Designation Name')]) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Save Changes'), ['class' => 'btn btn-rc-primary', 'id' => 'saveDesignationBtn']) }}
</div>
{{ Form::close() }}
<script>
    $('#saveDesignationBtn').click(function(e) {
        e.preventDefault();

        let form = $('#designationForm');
        let formData = form.serialize();
        form.find('.text-danger').remove();

        $.ajax({
            url: form.attr(
            'action'), 
            method: 'POST', 
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#commonModal').modal('hide');
                    toastr.success(response.message);
                
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, message) {
                        let field = form.find('[name="' + key + '"]');
                        if (field.length) {
                            field.after('<small class="text-danger">' + message +
                                '</small>');
                        }
                    });
                } else if (xhr.status === 401 || xhr.status === 403) {
                    toastr.error(xhr.responseJSON.error || 'Permission denied.');
                } else {
                    toastr.error('An unexpected error occurred.');
                }
            }
        });
    });
</script>
