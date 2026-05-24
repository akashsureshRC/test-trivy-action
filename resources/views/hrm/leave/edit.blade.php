{{ Form::model($leave, ['route' => ['leave.update', $leave->id], 'method' => 'PUT']) }}
<style>
    .form-group {
        margin-bottom: 4px !important;
    }
</style>
<div class="modal-body">
    <div class="text-end">
        @if (moduleIsActive('AIAssistant'))
        @include('aiassistant::ai.generate_ai_btn',['template_module' => 'leave','module'=>'Hrm'])
        @endif
    </div>
    <div class="row">
        {{-- Employee is not editable - keep original employee --}}
        {!! Form::hidden('employee_id', $leave->employee_id) !!}
        
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('leave_type_id', __('Leave Type'), ['class' => 'form-label']) }}
                <select name="leave_type_id" id="leave_type_id" class="form-control" required='required'>
                    <option value="">{{ __('Select Leave Type') }}</option>
                    @foreach ($leavetypes as $type)
                    @php
                        $balance = isset($leaveBalances[$type->id]) ? $leaveBalances[$type->id] : 0;
                    @endphp
                    <option value="{{ $type->id }}" @if ($type->id == $leave->leave_management_id || $type->id == $leave->leave_type_id) selected @endif>
                        {{ $type->leave_name }} (Balance: {{ $balance }} days)</option>
                    @endforeach
                </select>
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
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('leave_reason', __('Leave Reason'), ['class' => 'form-label']) }}
                {{ Form::textarea('leave_reason', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Leave Reason'), 'rows' => '3']) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('remark', __('Remark'), ['class' => 'form-label']) }}
                {{ Form::textarea('remark', '', ['class' => 'form-control', 'placeholder' => __('Leave Remark (Optional)'), 'rows' => '3']) }}
            </div>
        </div>
        @if ($leave->status == 'Pending')
        @permission('leave approver manage')
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                <select name="status" id="leave_status" class="form-control select2">
                    <option value="Pending" @if ($leave->status == 'Pending') selected @endif>
                        {{ __('Pending') }}
                    </option>
                    <option value="Approved" @if ($leave->status == 'Approved') selected @endif>
                        {{ __('Approved') }}
                    </option>
                    <option value="Rejected" @if ($leave->status == 'Rejected') selected @endif>
                        {{ __('Rejected') }}
                    </option>
                </select>
            </div>
        </div>
        <div class="col-md-12" id="rejection_reason_group" style="display: none;">
            <div class="form-group">
                {{ Form::label('rejection_reason', __('Rejection Reason'), ['class' => 'form-label']) }}
                {{ Form::textarea('rejection_reason', $leave->rejection_reason, ['class' => 'form-control', 'placeholder' => __('Please provide a reason for rejection...'), 'rows' => '3']) }}
            </div>
        </div>
        @endpermission
        @endif
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Save Changes'), ['class' => 'btn btn-rc-primary']) }}
</div>
{{ Form::close() }}

<script>
(function() {
    var statusSelect = document.getElementById('leave_status');
    var reasonGroup = document.getElementById('rejection_reason_group');
    
    if (statusSelect && reasonGroup) {
        function toggleReasonField() {
            if (statusSelect.value === 'Rejected') {
                reasonGroup.style.display = 'block';
            } else {
                reasonGroup.style.display = 'none';
            }
        }
        
        statusSelect.addEventListener('change', toggleReasonField);
        toggleReasonField();
    }
})();
</script>