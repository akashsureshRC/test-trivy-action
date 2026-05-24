{{ Form::open(['url' => 'payslip/bulkpayment/' . $date, 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group">
            {{ __('Total Unpaid Employee') }} <b>{{ count($unpaidEmployees) }}</b> {{ _('out of') }}
            <b>{{ count($Employees) }}</b>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">Cancel</button>
    <button type="submit" class="btn btn-rc-primary">{{ __('Bulk Payment') }}</button>
</div>
{{ Form::close() }}
