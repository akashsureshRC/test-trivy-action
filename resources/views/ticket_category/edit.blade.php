
{{ Form::model($category, ['route' => ['helpdeskticket-category.update', $category->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
            {{ Form::text('name', null, ['class' => 'form-control font-style', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('color', __('Color'), ['class' => 'form-label']) }}
            {{ Form::color('color', null, ['class' => 'form-control jscolor', 'required' => 'required']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <div class="text-end">
        <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="btn btn-rc-primary">{{ __('Update') }}</button>
    </div>
</div>
{{ Form::close() }}
