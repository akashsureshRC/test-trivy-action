{{ Form::open(['route' => 'billing.tiers.store', 'method' => 'POST']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('name', __('Tier Name'), ['class' => 'form-label']) }}
                <span class="text-danger">*</span>
                {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('e.g., Starter, Growth, Enterprise'), 'required' => true]) }}
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('min_payslips', __('Minimum Payslips'), ['class' => 'form-label']) }}
                <span class="text-danger">*</span>
                {{ Form::number('min_payslips', 1, ['class' => 'form-control', 'min' => 1, 'required' => true]) }}
                <small class="text-muted">{{ __('Starting payslip count for this tier') }}</small>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('max_payslips', __('Maximum Payslips'), ['class' => 'form-label']) }}
                {{ Form::number('max_payslips', null, ['class' => 'form-control', 'min' => 1, 'placeholder' => __('Leave empty for unlimited')]) }}
                <small class="text-muted">{{ __('Leave empty for no upper limit') }}</small>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('price_per_payslip', __('Price per Payslip (R)'), ['class' => 'form-label']) }}
                <span class="text-danger">*</span>
                <div class="input-group">
                    <span class="input-group-text">R</span>
                    {{ Form::number('price_per_payslip', null, ['class' => 'form-control', 'step' => '0.01', 'min' => '0', 'placeholder' => __('e.g., 10.00'), 'required' => true]) }}
                </div>
                <small class="text-muted">{{ __('Rate charged per payslip in this tier') }}</small>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('sort_order', __('Sort Order'), ['class' => 'form-label']) }}
                <span class="text-danger">*</span>
                {{ Form::number('sort_order', 0, ['class' => 'form-control', 'min' => 0, 'required' => true]) }}
                <small class="text-muted">{{ __('Order in which tiers are displayed and processed') }}</small>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="submit" class="btn btn-rc-primary">{{ __('Create Tier') }}</button>
</div>
{{ Form::close() }}
