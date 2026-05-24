<style>
    .modal-header {
        background: #f0f8ff !important;
    }
    .company-checkbox-list {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #e6e6e6;
        border-radius: 6px;
    }
    .company-checkbox-item {
        padding: 12px 15px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .company-checkbox-item:last-child {
        border-bottom: none;
    }
    .company-checkbox-item:hover {
        background-color: #f8f9fa;
    }
    .company-info {
        flex: 1;
    }
    .company-name {
        font-weight: 600;
        color: #333;
    }
    .company-email {
        font-size: 12px;
        color: #666;
    }
    .select-all-bar {
        background: #f4f4f4;
        padding: 10px 15px;
        border-radius: 6px 6px 0 0;
        border-bottom: 1px solid #e6e6e6;
    }
</style>

<div class="bg-none card-box">
    {{ Form::open(['route' => ['master-admin.update-companies', $masterAdmin->id], 'method' => 'POST', 'id' => 'manageCompaniesForm']) }}
    <div class="modal-body">
        <div class="mb-3">
            <h6 class="mb-1">{{ $masterAdmin->name }}</h6>
            <small class="text-muted">{{ $masterAdmin->email }}</small>
        </div>
        
        @if($companies->count() > 0)
            <div class="select-all-bar">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAllCompanies">
                    <label class="form-check-label" for="selectAllCompanies">
                        <strong>{{ __('Select All Customers') }}</strong>
                    </label>
                </div>
            </div>
            <div class="company-checkbox-list" style="border-radius: 0 0 6px 6px;">
                @foreach($companies as $company)
                    <div class="company-checkbox-item">
                        <div class="form-check mb-0">
                            <input class="form-check-input company-checkbox" type="checkbox" 
                                   name="companies[]" value="{{ $company->id }}" 
                                   id="manage_company_{{ $company->id }}"
                                   {{ in_array($company->id, $assignedCompanyIds) ? 'checked' : '' }}>
                        </div>
                        <label class="company-info mb-0" for="manage_company_{{ $company->id }}" style="cursor: pointer; margin-left: 10px;">
                            <div class="company-name">{{ $company->name }}</div>
                            <div class="company-email">{{ $company->email }}</div>
                        </label>
                    </div>
                @endforeach
            </div>
            <div class="mt-2 text-muted small">
                <span id="selectedCount">{{ count($assignedCompanyIds) }}</span> {{ __('of') }} {{ $companies->count() }} {{ __('companies selected') }}
            </div>
        @else
            <div class="alert alert-info mb-0">
                {{ __('No companies available to assign.') }}
            </div>
        @endif
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="btn btn-rc-primary">{{ __('Save Changes') }}</button>
    </div>
    {{ Form::close() }}
</div>

<script>
    $(document).ready(function() {
        // Select all functionality
        $('#selectAllCompanies').on('change', function() {
            $('.company-checkbox').prop('checked', $(this).is(':checked'));
            updateSelectedCount();
        });
        
        // Update select all checkbox state
        $('.company-checkbox').on('change', function() {
            var totalCheckboxes = $('.company-checkbox').length;
            var checkedCheckboxes = $('.company-checkbox:checked').length;
            $('#selectAllCompanies').prop('checked', totalCheckboxes === checkedCheckboxes);
            updateSelectedCount();
        });
        
        // Initial state check
        var totalCheckboxes = $('.company-checkbox').length;
        var checkedCheckboxes = $('.company-checkbox:checked').length;
        $('#selectAllCompanies').prop('checked', totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0);
        
        function updateSelectedCount() {
            var count = $('.company-checkbox:checked').length;
            $('#selectedCount').text(count);
        }
        
        // Form submission
        $('#manageCompaniesForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            
            $.ajax({
                type: 'POST',
                url: url,
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        toastrs('Success', response.message, 'success');
                        $('.modal').modal('hide');
                        location.reload();
                    }
                },
                error: function(xhr) {
                    var error = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred';
                    toastrs('Error', error, 'error');
                }
            });
        });
    });
</script>
