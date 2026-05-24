{{--
    RC Table System - Usage Examples & Documentation
    
    This file demonstrates how to use the RC Table System components.
    You can reference this file when implementing tables in your views.
--}}

{{-- ============================================
    EXAMPLE 1: Complete Table with All Features
    ============================================ --}}

<x-rc-table title="Employees" titleIcon="ti ti-users">
    {{-- Header Actions Slot --}}
    <x-slot name="headerActions">
        <a href="#" class="btn btn-rc-primary btn-rc-sm">
            <i class="ti ti-plus me-1"></i> Add New
        </a>
        <a href="#" class="btn btn-rc-outline btn-rc-sm">
            <i class="ti ti-download me-1"></i> Export
        </a>
    </x-slot>
    
    {{-- Filter Bar --}}
    <x-rc-table.filter action="{{ url()->current() }}">
        <x-rc-table.filter-group label="Search">
            <input type="text" name="search" class="rc-filter-input" placeholder="Search employees..." value="{{ request('search') }}">
        </x-rc-table.filter-group>
        
        <x-rc-table.filter-group label="Department">
            <select name="department" class="rc-filter-select">
                <option value="">All Departments</option>
                <option value="hr">Human Resources</option>
                <option value="it">IT</option>
                <option value="finance">Finance</option>
            </select>
        </x-rc-table.filter-group>
        
        <x-rc-table.filter-group label="Status">
            <select name="status" class="rc-filter-select">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </x-rc-table.filter-group>
        
        <x-rc-table.filter-group label="From Date" narrow>
            <input type="date" name="from_date" class="rc-filter-input" value="{{ request('from_date') }}">
        </x-rc-table.filter-group>
        
        <x-rc-table.filter-group label="To Date" narrow>
            <input type="date" name="to_date" class="rc-filter-input" value="{{ request('to_date') }}">
        </x-rc-table.filter-group>
    </x-rc-table.filter>
    
    {{-- Table Content --}}
    <x-rc-table.content>
        <table class="rc-table">
            <thead>
                <tr>
                    <th class="col-sno">S.No</th>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th class="col-date">Joined</th>
                    <th class="col-status">Status</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                {{-- Example Row --}}
                <tr>
                    <td class="col-sno">1</td>
                    <td class="col-id">EMP-001</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="rc-avatar-initials me-2">JD</span>
                            <div>
                                <span class="text-primary-cell">John Doe</span>
                                <div class="text-secondary-cell">john@example.com</div>
                            </div>
                        </div>
                    </td>
                    <td>Human Resources</td>
                    <td class="col-date">
                        <span class="date-primary">15 Jan 2024</span>
                        <div class="date-secondary">Monday</div>
                    </td>
                    <td class="col-status">
                        <span class="rc-status rc-status-active">Active</span>
                    </td>
                    <td class="col-actions">
                        <div class="rc-table-actions">
                            <a href="#" class="rc-table-action rc-table-action-view" title="View">
                                <i class="ti ti-eye"></i>
                            </a>
                            <a href="#" class="rc-table-action rc-table-action-edit" title="Edit">
                                <i class="ti ti-edit"></i>
                            </a>
                            <button class="rc-table-action rc-table-action-delete" title="Delete">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                
                {{-- More rows... --}}
            </tbody>
        </table>
    </x-rc-table.content>
    
    {{-- Footer with Pagination --}}
    {{-- <x-rc-table.footer :paginator="$employees" /> --}}
</x-rc-table>


{{-- ============================================
    EXAMPLE 2: Simple Table (No Filters)
    ============================================ --}}

<x-rc-table title="Leave Types" titleIcon="ti ti-calendar">
    <x-rc-table.content>
        <table class="rc-table">
            <thead>
                <tr>
                    <th class="col-sno">#</th>
                    <th>Leave Type</th>
                    <th class="col-amount">Days Allowed</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="col-sno">1</td>
                    <td class="text-primary-cell">Annual Leave</td>
                    <td class="col-amount">21</td>
                    <td class="col-actions">
                        <div class="rc-table-actions">
                            <a href="#" class="rc-table-action rc-table-action-edit"><i class="ti ti-edit"></i></a>
                            <button class="rc-table-action rc-table-action-delete"><i class="ti ti-trash"></i></button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </x-rc-table.content>
</x-rc-table>


{{-- ============================================
    EXAMPLE 3: Table with Empty State
    ============================================ --}}

<x-rc-table title="Pending Approvals">
    <x-rc-table.content>
        <table class="rc-table">
            <thead>
                <tr>
                    <th>Request</th>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                {{-- When no data --}}
                <x-rc-table.empty 
                    :asRow="true" 
                    :colspan="4"
                    icon="ti ti-clipboard-off"
                    title="No Pending Approvals"
                    message="All leave requests have been processed."
                />
            </tbody>
        </table>
    </x-rc-table.content>
</x-rc-table>


{{-- ============================================
    EXAMPLE 4: Compact Table
    ============================================ --}}

<x-rc-table>
    <x-rc-table.content>
        <table class="rc-table rc-table-compact rc-table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Clock In</th>
                    <th>Clock Out</th>
                    <th>Hours</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>05 Feb 2026</td>
                    <td>08:30 AM</td>
                    <td>05:30 PM</td>
                    <td class="col-amount">9.0</td>
                </tr>
            </tbody>
        </table>
    </x-rc-table.content>
</x-rc-table>


{{-- ============================================
    STATUS BADGE EXAMPLES
    ============================================ --}}

{{-- Available status classes:
    - rc-status-active / rc-status-success / rc-status-paid / rc-status-approved
    - rc-status-inactive / rc-status-cancelled / rc-status-rejected
    - rc-status-pending / rc-status-draft / rc-status-warning
    - rc-status-danger / rc-status-failed / rc-status-overdue
    - rc-status-info / rc-status-processing
    - rc-status-primary
--}}

<span class="rc-status rc-status-active">Active</span>
<span class="rc-status rc-status-pending">Pending</span>
<span class="rc-status rc-status-danger">Overdue</span>
<span class="rc-status rc-status-info">Processing</span>


{{-- ============================================
    MIGRATING EXISTING TABLES
    ============================================ 
    
    Quick conversion guide:
    
    BEFORE:
    <div class="card">
        <div class="card-body table-border-style">
            <div class="table-responsive">
                <table class="table mb-0 pc-dt-simple">
                    ...
                </table>
            </div>
        </div>
    </div>
    
    AFTER:
    <x-rc-table title="Your Title">
        <x-rc-table.content>
            <table class="rc-table">
                ...
            </table>
        </x-rc-table.content>
    </x-rc-table>
    
    
    BEFORE (Filter):
    <div class="card">
        <div class="card-body">
            <form>
                <div class="row align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-rc-primary">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    AFTER:
    <x-rc-table.filter action="{{ route('your.route') }}">
        <x-rc-table.filter-group label="Name">
            <input type="text" name="name" class="rc-filter-input">
        </x-rc-table.filter-group>
    </x-rc-table.filter>
    
--}}
