{{ Form::model($holiday, ['route' => ['holiday.update', $holiday->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="text-end">
        @if (moduleIsActive('AIAssistant'))
            @include('aiassistant::ai.generate_ai_btn',['template_module' => 'holiday','module'=>'Hrm'])
        @endif
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('occasion', __('Occasion'), ['class' => 'form-label']) }}
                {{ Form::text('occasion', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Occasion']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                {{ Form::date('start_date', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => 'Select Date']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                {{ Form::date('end_date', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => 'Select Date']) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Save Changes'), ['class' => 'btn btn-rc-primary']) }}
</div>
{{ Form::close() }}
