@extends('layouts.main')
@section('page-title')
{{ __('Record Leave') }}
@endsection
@section('page-breadcrumb')
{{ __('Leave') }},
{{ __('Record Leave') }}
@endsection
@push('css')
<style>
    /* ---- Calendar ---- */
    .leave-calendar {
        width: 100%;
        border-collapse: collapse;
    }

    .leave-calendar th {
        text-align: center;
        font-size: var(--rc-font-sm);
        font-weight: var(--rc-font-semibold);
        color: var(--rc-gray-500);
        padding: var(--rc-space-3);
        text-transform: uppercase;
        border: 1px solid var(--rc-border);
        background: var(--rc-gray-50);
    }

    .leave-calendar td {
        text-align: center;
        vertical-align: middle;
        height: 48px;
        cursor: pointer;
        font-size: var(--rc-font-md);
        font-weight: var(--rc-font-medium);
        color: var(--rc-gray-700);
        border: 1px solid var(--rc-border);
        transition: all var(--rc-transition-fast);
    }

    .leave-calendar td:empty {
        cursor: default;
    }

    .leave-calendar td.day-cell:hover {
        background-color: var(--rc-primary-light);
        color: var(--rc-primary);
    }

    .leave-calendar .selected,
    .leave-calendar .date-selected {
        background-color: var(--rc-primary) !important;
        color: #fff !important;
        font-weight: var(--rc-font-semibold);
    }

    .leave-calendar .in-range {
        background-color: var(--rc-primary-light);
        color: var(--rc-primary);
    }

    .leave-calendar .today-cell {
        font-weight: var(--rc-font-bold);
        color: var(--rc-primary);
    }

    /* ---- Calendar Navigation ---- */
    .calendar-nav {
        display: flex;
        align-items: center;
        gap: var(--rc-space-2);
        margin-bottom: var(--rc-space-4);
    }

    .calendar-nav-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--rc-border);
        background: #fff;
        border-radius: var(--rc-radius-sm);
        cursor: pointer;
        color: var(--rc-gray-600);
        font-size: 16px;
        transition: all var(--rc-transition-fast);
    }

    .calendar-nav-btn:hover {
        background-color: var(--rc-gray-100);
        border-color: var(--rc-gray-300);
    }

    .calendar-nav .calendar-today-btn {
        padding: var(--rc-space-1) var(--rc-space-3);
        border: 1px solid var(--rc-border);
        background: #fff;
        border-radius: var(--rc-radius-sm);
        cursor: pointer;
        font-size: var(--rc-font-sm);
        font-weight: var(--rc-font-medium);
        color: var(--rc-gray-600);
        transition: all var(--rc-transition-fast);
    }

    .calendar-nav .calendar-today-btn:hover {
        background-color: var(--rc-gray-100);
        border-color: var(--rc-gray-300);
    }

    .calendar-month-label {
        font-size: var(--rc-font-lg);
        font-weight: var(--rc-font-semibold);
        color: var(--rc-gray-800);
        margin-left: var(--rc-space-2);
    }

    /* ---- Partials Table ---- */
    .partials-scroll {
        max-height: 320px;
        overflow-y: auto;
        border: 1px solid var(--rc-border);
        border-radius: var(--rc-radius-sm);
    }

    .partials-table {
        width: 100%;
        font-size: var(--rc-font-md);
        margin-bottom: 0;
    }

    .partials-table thead {
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .partials-table th {
        font-weight: var(--rc-font-semibold);
        color: var(--rc-gray-500);
        text-transform: uppercase;
        font-size: var(--rc-font-xs);
        letter-spacing: 0.5px;
        padding: var(--rc-space-2) var(--rc-space-4);
        background: var(--rc-gray-50);
        border-bottom: 2px solid var(--rc-border);
        text-align: left;
    }

    .partials-table td {
        padding: var(--rc-space-2) var(--rc-space-4);
        border-bottom: 1px solid var(--rc-border-light);
        vertical-align: middle;
    }

    .partials-table .partial-hours-cell {
        width: 200px;
    }

    .partial-hours-wrapper {
        display: flex;
        align-items: center;
        gap: var(--rc-space-2);
        min-height: 32px;
    }

    .partial-hours-wrapper .form-check-input {
        margin: 0;
        flex-shrink: 0;
    }

    .partial-hours-wrapper .partial-hours {
        width: 90px;
        visibility: hidden;
    }

    .partial-hours-wrapper .partial-hours.visible {
        visibility: visible;
    }

    /* ---- Leave Rate Card ---- */
    .rate-row {
        display: flex;
        justify-content: space-between;
        padding: var(--rc-space-2) 0;
        font-size: var(--rc-font-md);
        color: var(--rc-gray-600);
    }

    .rate-row.rate-total {
        font-weight: var(--rc-font-semibold);
        color: var(--rc-gray-800);
        border-top: 1px solid var(--rc-border);
        padding-top: var(--rc-space-3);
        margin-top: var(--rc-space-2);
    }

    /* ---- Leave Balances Table ---- */
    .balance-table {
        width: 100%;
        font-size: var(--rc-font-md);
    }

    .balance-table th {
        font-weight: var(--rc-font-semibold);
        color: var(--rc-gray-500);
        text-transform: uppercase;
        font-size: var(--rc-font-xs);
        letter-spacing: 0.5px;
        padding: var(--rc-space-2) var(--rc-space-3);
        border-bottom: 2px solid var(--rc-border);
        text-align: left;
    }

    .balance-table td {
        padding: var(--rc-space-3);
        border-bottom: 1px solid var(--rc-border-light);
        color: var(--rc-gray-700);
    }

    .balance-table .empty-message td {
        text-align: center;
        color: var(--rc-gray-400);
        padding: var(--rc-space-6) var(--rc-space-3);
    }

    /* ---- Date Range Inline ---- */
    .date-range-inline {
        display: flex;
        gap: var(--rc-space-3);
        align-items: end;
    }

    .date-range-inline .date-field {
        flex: 1;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <form action="{{ route('leaverecord.store') }}" method="POST">
            @csrf
            <div class="row g-4">

                {{-- ===== LEFT COLUMN: Context & Info ===== --}}
                <div class="col-lg-4">

                    {{-- 1. Employee & Leave Type --}}
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('Employee & Leave Type') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Employee') }}</label>
                                <select class="form-select" name="employee_id">
                                    <option value="">{{ __('Select Employee') }}</option>
                                    @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label">{{ __('Leave Type') }}</label>
                                <select class="form-select" name="leave_type_id" id="leave_type_id">
                                    <option value="">{{ __('Select Leave Type') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Leave Balances --}}
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('Leave Balances') }}</h5>
                        </div>
                        <div class="card-body">
                            <table class="balance-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Days') }}</th>
                                        <th>{{ __('Last Updated') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="leaveBalancesBody">
                                    <tr class="empty-message">
                                        <td colspan="3">
                                            <i class="ti ti-user-search d-block mb-2" style="font-size: 24px;"></i>
                                            {{ __('Select an employee to view balances.') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- 3. Leave Rate --}}
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('Annual Leave Rate (per hour)') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="rate-row">
                                <span>{{ __('Base rate') }}:</span>
                                <span>R 173.08</span>
                            </div>
                            <div class="rate-row">
                                <span>{{ __('Fluctuating rate') }}:</span>
                                <span>R 0.00</span>
                            </div>
                            <div class="rate-row rate-total">
                                <span>{{ __('Total rate') }}:</span>
                                <span>R 173.08</span>
                            </div>
                            <div class="mt-4">
                                <label class="form-label">{{ __('Custom Fluctuating Rate') }}:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="customRate" placeholder="{{ __('Enter rate') }}">
                                    <button type="button" class="btn btn-rc-primary">{{ __('Save') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== RIGHT COLUMN: Calendar & Actions ===== --}}
                <div class="col-lg-8">

                    {{-- 1. Calendar --}}
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('Calendar') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="calendar-nav">
                                <button type="button" class="calendar-nav-btn" id="prevMonth">
                                    <i class="ti ti-chevron-left"></i>
                                </button>
                                <button type="button" class="calendar-today-btn" id="todayBtn">{{ __('Today') }}</button>
                                <button type="button" class="calendar-nav-btn" id="nextMonth">
                                    <i class="ti ti-chevron-right"></i>
                                </button>
                                <span class="calendar-month-label" id="monthYear"></span>
                            </div>

                            <table class="leave-calendar">
                                <thead>
                                    <tr>
                                        <th>{{ __('Sun') }}</th>
                                        <th>{{ __('Mon') }}</th>
                                        <th>{{ __('Tue') }}</th>
                                        <th>{{ __('Wed') }}</th>
                                        <th>{{ __('Thu') }}</th>
                                        <th>{{ __('Fri') }}</th>
                                        <th>{{ __('Sat') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="calendarBody"></tbody>
                            </table>

                            <p class="text-muted mb-0 mt-3" id="calendarHint">
                                <i class="ti ti-info-circle me-1"></i>{{ __('Click a start date, then click an end date to select a leave period.') }}
                            </p>
                        </div>
                    </div>

                    {{-- 2. Selected Period & Partials (appears after date selection) --}}
                    <div class="card d-none" id="selectionCard">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5><i class="ti ti-calendar-stats me-2"></i>{{ __('Selected Leave Period') }}</h5>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-rc-outline btn-sm" id="cancelBtn">
                                    <i class="ti ti-x me-1"></i>{{ __('Clear') }}
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Date Range --}}
                            <div class="date-range-inline mb-4">
                                <div class="date-field">
                                    <label class="form-label">{{ __('From') }}</label>
                                    <input type="text" name="start_date" class="form-control" id="fromDate" readonly>
                                </div>
                                <div class="date-field">
                                    <label class="form-label">{{ __('To') }}</label>
                                    <input type="text" name="end_date" class="form-control" id="toDate" readonly>
                                </div>
                            </div>

                            {{-- Partials --}}
                            <div id="partials-container"></div>

                            <input type="hidden" name="selected_date" id="selectedDate">

                            {{-- Actions --}}
                            <div class="d-flex gap-2 mt-4 pt-3" style="border-top: 1px solid var(--rc-border);">
                                <button type="submit" class="btn btn-rc-primary">
                                    <i class="ti ti-check me-1"></i>{{ __('Submit Leave Record') }}
                                </button>
                                <button type="button" class="btn btn-rc-danger" id="removeDatesBtn">
                                    <i class="ti ti-trash me-1"></i>{{ __('Remove Dates') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
let isSelectingFromDate = true;
let fromDateValue = '';
let toDateValue = '';
let selectedDates = [];

const calendarBody = document.getElementById('calendarBody');
const monthYear = document.getElementById('monthYear');
const fromDate = document.getElementById('fromDate');
const toDate = document.getElementById('toDate');
const selectionCard = document.getElementById('selectionCard');
const partialsContainer = document.getElementById('partials-container');
const cancelBtn = document.getElementById('cancelBtn');
const removeDatesBtn = document.getElementById('removeDatesBtn');
const calendarHint = document.getElementById('calendarHint');

const todayDate = new Date();
let currentMonth = todayDate.getMonth();
let currentYear = todayDate.getFullYear();

document.addEventListener('DOMContentLoaded', function () {
    const employeeSelect = document.querySelector('select[name="employee_id"]');
    const leaveTypeSelect = document.getElementById('leave_type_id');
    const leaveBalancesBody = document.getElementById('leaveBalancesBody');

    employeeSelect.addEventListener('change', function () {
        const employeeId = this.value;
        leaveTypeSelect.innerHTML = '<option value="">{{ __("Loading...") }}</option>';
        leaveBalancesBody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">{{ __("Loading...") }}</td></tr>';

        if (!employeeId) {
            leaveTypeSelect.innerHTML = '<option value="">{{ __("Select Leave Type") }}</option>';
            leaveBalancesBody.innerHTML = '<tr class="empty-message"><td colspan="3"><i class="ti ti-user-search d-block mb-2" style="font-size:24px;"></i>{{ __("Select an employee to view balances.") }}</td></tr>';
            return;
        }

        fetch(`/leaverecord/balances/${employeeId}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                leaveTypeSelect.innerHTML = '<option value="">{{ __("Select Leave Type") }}</option>';
                leaveBalancesBody.innerHTML = '';

                if (data && data.length > 0) {
                    data.forEach(leaveType => {
                        const option = document.createElement('option');
                        option.value = leaveType.leave_management_id;
                        option.textContent = `${leaveType.leave_name} (${leaveType.remaining_balance} days)`;
                        leaveTypeSelect.appendChild(option);

                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${leaveType.leave_name}</td>
                            <td><span class="badge bg-primary-subtle text-primary">${leaveType.remaining_balance}</span></td>
                            <td>${leaveType.updated_at}</td>
                        `;
                        leaveBalancesBody.appendChild(row);
                    });
                } else {
                    leaveTypeSelect.innerHTML = '<option value="">{{ __("No eligible leave types found") }}</option>';
                    leaveBalancesBody.innerHTML = '<tr class="empty-message"><td colspan="3">{{ __("No leave balances found.") }}</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error fetching leave balances:', error);
                leaveTypeSelect.innerHTML = '<option value="">{{ __("Error loading leave types") }}</option>';
                leaveBalancesBody.innerHTML = '<tr class="empty-message"><td colspan="3">{{ __("Error loading data.") }}</td></tr>';
            });
    });
});

function renderCalendar(month, year) {
    calendarBody.innerHTML = '';
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const now = new Date();
    const todayStr = `${now.getFullYear()}-${(now.getMonth() + 1).toString().padStart(2, '0')}-${now.getDate().toString().padStart(2, '0')}`;
    monthYear.textContent = `${new Date(year, month).toLocaleString('default', { month: 'long' })} ${year}`;

    let date = 1;
    for (let i = 0; i < 6; i++) {
        const row = document.createElement('tr');

        for (let j = 0; j < 7; j++) {
            const cell = document.createElement('td');

            if (i === 0 && j < firstDay) {
                cell.innerHTML = '';
            } else if (date > daysInMonth) {
                cell.innerHTML = '';
                row.appendChild(cell);
                continue;
            } else {
                const fullDate = `${year}-${(month + 1).toString().padStart(2, '0')}-${date.toString().padStart(2, '0')}`;
                cell.textContent = date;
                cell.classList.add('day-cell');
                cell.dataset.fullDate = fullDate;

                if (fullDate === todayStr) {
                    cell.classList.add('today-cell');
                }

                if (fromDateValue && toDateValue) {
                    const cellDate = new Date(fullDate);
                    const from = new Date(fromDateValue);
                    const to = new Date(toDateValue);
                    if (cellDate >= from && cellDate <= to) {
                        cell.classList.add('in-range');
                    }
                }

                if (fullDate === fromDateValue && !toDateValue) {
                    cell.classList.add('selected');
                }

                if (selectedDates.includes(fullDate)) {
                    cell.classList.add('date-selected');
                }

                cell.addEventListener('click', () => {
                    handleDateSelection(fullDate, cell);
                });

                date++;
            }

            row.appendChild(cell);
        }

        calendarBody.appendChild(row);
        if (date > daysInMonth) break;
    }
}

function handleDateSelection(fullDate, cell) {
    if (isSelectingFromDate) {
        document.querySelectorAll('.day-cell').forEach(td => {
            td.classList.remove('selected', 'date-selected', 'in-range');
        });

        fromDateValue = fullDate;
        toDateValue = '';
        fromDate.value = formatDateForDisplay(fullDate);
        toDate.value = '';
        cell.classList.add('selected');

        isSelectingFromDate = false;
    } else {
        // Ensure from < to
        if (new Date(fullDate) < new Date(fromDateValue)) {
            toDateValue = fromDateValue;
            fromDateValue = fullDate;
        } else {
            toDateValue = fullDate;
        }

        fromDate.value = formatDateForDisplay(fromDateValue);
        toDate.value = formatDateForDisplay(toDateValue);

        highlightDateRange(fromDateValue, toDateValue);
        createPartialDates(fromDateValue, toDateValue);

        isSelectingFromDate = true;
    }

    selectionCard.classList.remove('d-none');
    calendarHint.style.display = 'none';
}

function formatDateForDisplay(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function highlightDateRange(from, to) {
    document.querySelectorAll('.day-cell').forEach(cell => {
        cell.classList.remove('in-range', 'date-selected', 'selected');

        const cellDate = new Date(cell.dataset.fullDate);
        const fromD = new Date(from);
        const toD = new Date(to);

        if (cellDate >= fromD && cellDate <= toD) {
            cell.classList.add('in-range');
        }
    });
}

function createPartialDates(from, to) {
    const start = new Date(from);
    const end = new Date(to);

    if (start > end) {
        alert("{{ __('From date must be before To date.') }}");
        return;
    }

    let weekdays = 0;
    const tempLoop = new Date(start);
    while (tempLoop <= end) {
        const d = tempLoop.getDay();
        if (d !== 0 && d !== 6) weekdays++;
        tempLoop.setDate(tempLoop.getDate() + 1);
    }

    let html = `<div class="d-flex align-items-center justify-content-between mb-2">
            <label class="form-label mb-0">{{ __('Working Days in Period') }}</label>
            <span class="badge bg-primary-subtle text-primary">${weekdays} {{ __('days') }}</span>
        </div>
        <div class="partials-scroll">
        <table class="partials-table">
            <thead><tr>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Day') }}</th>
                <th class="partial-hours-cell">{{ __('Partial Leave') }}</th>
            </tr></thead>
            <tbody>`;

    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const loop = new Date(start);
    while (loop <= end) {
        const dayOfWeek = loop.getDay();

        if (dayOfWeek !== 0 && dayOfWeek !== 6) {
            const formatted = loop.toISOString().split('T')[0];
            html += `
                <tr>
                    <td>${formatDateForDisplay(formatted)}</td>
                    <td class="text-muted">${dayNames[dayOfWeek].substring(0, 3)}</td>
                    <td class="partial-hours-cell">
                        <div class="partial-hours-wrapper">
                            <input type="checkbox" class="form-check-input partial-checkbox" data-date="${formatted}">
                            <input type="number" name="partial_hours[${formatted}]" class="form-control form-control-sm partial-hours" placeholder="{{ __('Hours') }}" min="1" max="8">
                        </div>
                    </td>
                </tr>`;
        }

        loop.setDate(loop.getDate() + 1);
    }

    html += `</tbody></table></div>`;
    partialsContainer.innerHTML = html;

    document.querySelectorAll('.partial-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            const input = checkbox.parentElement.querySelector('.partial-hours');
            if (checkbox.checked) {
                input.classList.add('visible');
            } else {
                input.classList.remove('visible');
                input.value = '';
            }
        });
    });
}

document.getElementById('prevMonth').addEventListener('click', () => {
    currentMonth--;
    if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
    }
    renderCalendar(currentMonth, currentYear);
});

document.getElementById('nextMonth').addEventListener('click', () => {
    currentMonth++;
    if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
    }
    renderCalendar(currentMonth, currentYear);
});

document.getElementById('todayBtn').addEventListener('click', () => {
    const t = new Date();
    currentMonth = t.getMonth();
    currentYear = t.getFullYear();
    renderCalendar(currentMonth, currentYear);
});

cancelBtn.addEventListener('click', () => { resetSelection(); });
removeDatesBtn.addEventListener('click', () => { resetSelection(); });

function resetSelection() {
    fromDateValue = '';
    toDateValue = '';
    fromDate.value = '';
    toDate.value = '';
    partialsContainer.innerHTML = '';
    selectionCard.classList.add('d-none');
    calendarHint.style.display = '';

    document.querySelectorAll('.day-cell').forEach(cell => {
        cell.classList.remove('selected', 'date-selected', 'in-range');
    });

    isSelectingFromDate = true;
}

renderCalendar(currentMonth, currentYear);
</script>

@endsection