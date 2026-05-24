{{ Form::open(['route' => 'workspace.store', 'enctype' => 'multipart/form-data', 'id' => 'workspaceCreateForm']) }}
<div class="modal-body">
    <div class="form-group">
        {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
        <span class="text-danger">*</span>
        {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Workspace Name'), 'id' => 'ws_name']) }}
        <p class="text-danger d-none" id="ws_name_validation">{{ __('Workspace Name field is required.') }}</p>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="button" id="wsCreateBtn" class="btn btn-rc-primary">{{ __('Create') }}</button>
</div>
{{ Form::close() }}

<script>
    $('#ws_name').on('input', function() {
        $('#ws_name_validation').addClass('d-none');
    });

    $('#wsCreateBtn').on('click', function() {
        var name = $('#ws_name').val();
        if (!name || name.trim() === '') {
            $('#ws_name_validation').removeClass('d-none');
            return;
        }
        $('#ws_name_validation').addClass('d-none');
        document.getElementById('workspaceCreateForm').submit();
    });
</script>
