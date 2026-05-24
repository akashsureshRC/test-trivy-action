<style>
    .modal-header {
        background: var(--rc-gray-50, #f8f9fa) !important;
    }
    .working-hours-table {
        margin-bottom: 0;
    }
    .working-hours-table th,
    .working-hours-table td {
        vertical-align: middle;
        padding: 8px 12px;
    }
    .time-input {
        width: 110px;
    }
    .day-toggle {
        width: 50px;
    }
    .lunch-inputs {
        display: flex;
        gap: 5px;
        align-items: center;
    }
</style>

<form id="employeeWorkingHoursForm" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body">
        @if($employee->branch_id)
        <div class="mb-3">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="copyFromBranchBtn">
                <i class="ti ti-copy"></i> {{ __('Copy from Branch') }}
            </button>
            <small class="text-muted ms-2">{{ __('Reset to branch default working hours') }}</small>
        </div>
        @endif
        
        <div class="table-responsive">
            <table class="table working-hours-table">
                <thead>
                    <tr>
                        <th>{{ __('Day') }}</th>
                        <th class="day-toggle">{{ __('Working') }}</th>
                        <th>{{ __('Start') }}</th>
                        <th>{{ __('End') }}</th>
                        <th>{{ __('Lunch') }}</th>
                        <th>{{ __('Hours') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                    @endphp
                    @foreach($days as $day)
                        @php
                            $wh = $workingHours[$day] ?? null;
                            $isWorking = $wh ? $wh->is_working_day : in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']);
                            $startTime = $wh && $wh->start_time ? \Carbon\Carbon::parse($wh->start_time)->format('H:i') : '08:00';
                            $endTime = $wh && $wh->end_time ? \Carbon\Carbon::parse($wh->end_time)->format('H:i') : '17:00';
                            $lunchStart = $wh && $wh->lunch_start_time ? \Carbon\Carbon::parse($wh->lunch_start_time)->format('H:i') : '12:00';
                            $lunchEnd = $wh && $wh->lunch_end_time ? \Carbon\Carbon::parse($wh->lunch_end_time)->format('H:i') : '13:00';
                            $hasLunch = $wh && $wh->lunch_start_time && $wh->lunch_end_time;
                        @endphp
                        <tr data-day="{{ $day }}">
                            <td>
                                <strong>{{ ucfirst($day) }}</strong>
                                <input type="hidden" name="working_hours[{{ $loop->index }}][day]" value="{{ $day }}">
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input is-working-toggle" 
                                           type="checkbox" 
                                           name="working_hours[{{ $loop->index }}][is_working_day]"
                                           value="1"
                                           data-day="{{ $day }}"
                                           {{ $isWorking ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td>
                                <input type="time" 
                                       class="form-control time-input start-time" 
                                       name="working_hours[{{ $loop->index }}][start_time]"
                                       value="{{ $startTime }}"
                                       data-day="{{ $day }}"
                                       {{ !$isWorking ? 'disabled' : '' }}>
                            </td>
                            <td>
                                <input type="time" 
                                       class="form-control time-input end-time" 
                                       name="working_hours[{{ $loop->index }}][end_time]"
                                       value="{{ $endTime }}"
                                       data-day="{{ $day }}"
                                       {{ !$isWorking ? 'disabled' : '' }}>
                            </td>
                            <td>
                                <div class="lunch-inputs">
                                    <input type="time" 
                                           class="form-control time-input lunch-start" 
                                           name="working_hours[{{ $loop->index }}][lunch_start_time]"
                                           value="{{ $hasLunch ? $lunchStart : '' }}"
                                           placeholder="Start"
                                           data-day="{{ $day }}"
                                           {{ !$isWorking ? 'disabled' : '' }}>
                                    <span>-</span>
                                    <input type="time" 
                                           class="form-control time-input lunch-end" 
                                           name="working_hours[{{ $loop->index }}][lunch_end_time]"
                                           value="{{ $hasLunch ? $lunchEnd : '' }}"
                                           placeholder="End"
                                           data-day="{{ $day }}"
                                           {{ !$isWorking ? 'disabled' : '' }}>
                                </div>
                            </td>
                            <td class="hours-display" data-day="{{ $day }}">
                                @if($isWorking)
                                    @php
                                        $workMinutes = \Carbon\Carbon::parse($startTime)->diffInMinutes(\Carbon\Carbon::parse($endTime));
                                        $lunchMinutes = $hasLunch ? \Carbon\Carbon::parse($lunchStart)->diffInMinutes(\Carbon\Carbon::parse($lunchEnd)) : 0;
                                        $netMinutes = $workMinutes - $lunchMinutes;
                                    @endphp
                                    {{ number_format($netMinutes / 60, 1) }}h
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-secondary">
                        <td colspan="5"><strong>{{ __('Total Weekly Hours') }}</strong></td>
                        <td><strong id="totalWeeklyHours">0h</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="mt-2">
            <small class="text-muted">
                <i class="ti ti-info-circle"></i> {{ __('Leave lunch times empty if no lunch break applies.') }}
            </small>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="btn btn-rc-primary">{{ __('Save Working Hours') }}</button>
    </div>
</form>

<script>
$(document).ready(function() {
    // Calculate hours when times change (accounting for lunch)
    function calculateHours(day) {
        const row = $(`tr[data-day="${day}"]`);
        const isWorking = row.find('.is-working-toggle').is(':checked');
        const startTime = row.find('.start-time').val();
        const endTime = row.find('.end-time').val();
        const lunchStart = row.find('.lunch-start').val();
        const lunchEnd = row.find('.lunch-end').val();
        
        if (!isWorking || !startTime || !endTime) {
            row.find('.hours-display').text('-');
            return 0;
        }
        
        const start = new Date(`2000-01-01T${startTime}`);
        const end = new Date(`2000-01-01T${endTime}`);
        let diffMs = end - start;
        
        // Subtract lunch time if both lunch start and end are set
        if (lunchStart && lunchEnd) {
            const lunchStartDate = new Date(`2000-01-01T${lunchStart}`);
            const lunchEndDate = new Date(`2000-01-01T${lunchEnd}`);
            const lunchMs = lunchEndDate - lunchStartDate;
            if (lunchMs > 0) {
                diffMs -= lunchMs;
            }
        }
        
        const diffHours = diffMs / (1000 * 60 * 60);
        
        row.find('.hours-display').text(diffHours > 0 ? diffHours.toFixed(1) + 'h' : '-');
        return diffHours > 0 ? diffHours : 0;
    }

    // Calculate total weekly hours
    function calculateTotalHours() {
        let total = 0;
        const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        days.forEach(day => {
            total += calculateHours(day);
        });
        $('#totalWeeklyHours').text(total.toFixed(1) + 'h');
    }

    // Toggle working day
    $('.is-working-toggle').on('change', function() {
        const day = $(this).data('day');
        const isWorking = $(this).is(':checked');
        const row = $(`tr[data-day="${day}"]`);
        
        row.find('.start-time, .end-time, .lunch-start, .lunch-end').prop('disabled', !isWorking);
        
        if (!isWorking) {
            row.find('.hours-display').text('-');
        } else {
            calculateHours(day);
        }
        calculateTotalHours();
    });

    // Update hours on time change
    $('.start-time, .end-time, .lunch-start, .lunch-end').on('change', function() {
        calculateTotalHours();
    });

    // Initial calculation
    calculateTotalHours();

    // Copy from branch button
    @if($employee->branch_id)
    $('#copyFromBranchBtn').on('click', function() {
        openRcConfirmDialog('{{ __("Are you sure?") }}', '{{ __("This will reset all working hours to branch defaults. Continue?") }}').then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            const btn = $(this);
            btn.prop('disabled', true).html('<i class="ti ti-loader ti-spin"></i> {{ __("Copying...") }}');
            
            $.ajax({
                url: '{{ route("employee.working-hours.copy-from-branch", $employee->id) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastrs('Success', response.message || '{{ __("Working hours copied successfully") }}', 'success');
                        // Delay reload to show toast
                        setTimeout(function() {
                            location.reload();
                        }, 500);
                    } else {
                        toastrs('Error', response.message || '{{ __("Something went wrong") }}', 'error');
                        btn.prop('disabled', false).html('<i class="ti ti-copy"></i> {{ __("Copy from Branch") }}');
                    }
                },
                error: function(xhr) {
                    toastrs('Error', xhr.responseJSON?.message || '{{ __("Something went wrong") }}', 'error');
                    btn.prop('disabled', false).html('<i class="ti ti-copy"></i> {{ __("Copy from Branch") }}');
                }
            });
        });
    });
    @endif

    // Form submission
    $('#employeeWorkingHoursForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="ti ti-loader"></i> {{ __("Saving...") }}');
        
        const formData = [];
        const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        days.forEach((day, index) => {
            const row = $(`tr[data-day="${day}"]`);
            const isWorking = row.find('.is-working-toggle').is(':checked');
            const lunchStart = row.find('.lunch-start').val();
            const lunchEnd = row.find('.lunch-end').val();
            
            formData.push({
                day: day,
                is_working_day: isWorking ? 1 : 0,
                start_time: isWorking ? row.find('.start-time').val() : null,
                end_time: isWorking ? row.find('.end-time').val() : null,
                lunch_start_time: (isWorking && lunchStart) ? lunchStart : null,
                lunch_end_time: (isWorking && lunchEnd) ? lunchEnd : null
            });
        });

        $.ajax({
            url: '{{ route("employee.working-hours.update", $employee->id) }}',
            method: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                working_hours: formData
            },
            success: function(response) {
                if (response.success) {
                    toastrs('Success', response.message || '{{ __("Working hours saved successfully") }}', 'success');
                    // Small delay to show toast before closing modal
                    setTimeout(function() {
                        $('#commonModal').modal('hide');
                    }, 300);
                } else {
                    toastrs('Error', response.message || '{{ __("Something went wrong") }}', 'error');
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let errorMsg = '';
                    $.each(errors, function(field, messages) {
                        errorMsg += messages[0] + '\n';
                    });
                    toastrs('Error', errorMsg, 'error');
                } else {
                    toastrs('Error', xhr.responseJSON?.message || '{{ __("Something went wrong") }}', 'error');
                }
            },
            complete: function(xhr, status) {
                // Only re-enable button if request failed
                if (status !== 'success' && xhr.responseJSON && !xhr.responseJSON.success) {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            }
        });

        return false; // Prevent any default form submission
    });
});
</script>
