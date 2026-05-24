{{ Form::open(['route' => 'helpdeskticket-category.store']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
            {{ Form::text('name', '', ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('color', __('Color'), ['class' => 'form-label']) }}
            {{ Form::color('color', '', ['class' => 'form-control jscolor', 'required' => 'required']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
        <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="btn btn-rc-primary">{{ __('Create') }}</button>
</div>
{{ Form::close() }}
