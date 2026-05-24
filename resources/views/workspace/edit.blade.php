{{Form::model($workSpace,array('route' => array('workspace.update', $workSpace->id), 'method' => 'PUT', 'id' => 'workspace-edit-form')) }}
<div class="modal-body">
    <div class="form-group">
        {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
        <span class="text-danger">*</span>
        {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Workspace Name'), 'id' => 'ws_edit_name']) }}
        <p class="text-danger d-none" id="ws_edit_name_validation">{{ __('Workspace Name field is required.') }}</p>
    </div>
    <div class="form-group">
        {{ Form::label('slug', __('Slug'), ['class' => 'form-label']) }}
        <span class="text-danger">*</span>
        {{ Form::text('slug', null, ['class' => 'form-control', 'placeholder' => __('Enter Workspace Slug'), 'id' => 'ws_edit_slug']) }}
        <p class="text-danger d-none" id="ws_edit_slug_validation">{{ __('Slug field is required.') }}</p>
        <span id="slug-msg"></span>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{Form::submit(__('Update'),array('class'=>'btn btn-rc-primary'))}}
</div>
{{ Form::close() }}

<script>
    $('#ws_edit_name, #ws_edit_slug').on('input', function() {
        $(this).closest('.form-group').find('.text-danger[id$="_validation"]').addClass('d-none');
    });

    $('#workspace-edit-form').submit(function (e) {
        e.preventDefault();
        var isValid = true;

        var name = $('#ws_edit_name').val();
        if (!name || name.trim() === '') {
            $('#ws_edit_name_validation').removeClass('d-none');
            isValid = false;
        } else {
            $('#ws_edit_name_validation').addClass('d-none');
        }

        var slug = $('#ws_edit_slug').val();
        if (!slug || slug.trim() === '') {
            $('#ws_edit_slug_validation').removeClass('d-none');
            isValid = false;
        } else {
            $('#ws_edit_slug_validation').addClass('d-none');
        }

        if (!isValid) return;

        $.ajax({
            url: '{{ route('workspace.check') }}',
            type: 'POST',
            data: {
                "_token": "{{ csrf_token() }}",
                "workspace": "{{ $workSpace->id }}",
                "slug": slug,
            },
            beforeSend: function () {
                $(".loader-wrapper").removeClass('d-none');
            },
            success: function(data)
            {
                $('#slug-msg').empty();
                if(data.success)
                {
                    // Submit form via AJAX
                    var formData = new FormData($('#workspace-edit-form')[0]);
                    
                    $.ajax({
                        url: $('#workspace-edit-form').attr('action'),
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $(".loader-wrapper").addClass('d-none');
                            if (response.success) {
                                toastrs('Success', response.success, 'success');
                                $("#commonModal").modal('hide');
                                
                                // Update workspace name in header without page reload
                                var newName = $('#ws_edit_name').val();
                                $('.dash-head-link .hide-mob').text(newName);
                                
                                // Small delay to ensure modal closes, then reload
                                setTimeout(function() {
                                    location.reload();
                                }, 500);
                            } else if (response.error) {
                                toastrs('Error', response.error, 'error');
                            }
                        },
                        error: function(xhr) {
                            $(".loader-wrapper").addClass('d-none');
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                toastrs('Error', xhr.responseJSON.error, 'error');
                            } else {
                                toastrs('Error', 'Something went wrong', 'error');
                            }
                        }
                    });
                }
                else
                {
                    $(".loader-wrapper").addClass('d-none');
                    $('#slug-msg').addClass('text-danger').text(data.error);
                }
            },
            error: function(xhr) {
                $(".loader-wrapper").addClass('d-none');
                toastrs('Error', 'Slug validation failed', 'error');
            }
        });
    });
</script>
