<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <div id="process_area" class="overflow-auto import-data-table">
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal" onclick="location.reload();">{{ __('Cancel') }}</button>
    <button type="submit" name="import" id="import" class="btn btn-rc-primary" disabled>{{__('Import')}}</button>
</div>

<script>
    var column_data = {};
    var employee_id = 0;
    var date = 0;
    var clock_in = 0;
    var clock_out = 0;

    $(document).ready(function()
    {
        // Check for auto-mapping from window after modal loads
        setTimeout(function() {
            if (window.attendanceColumnMapping && Object.keys(window.attendanceColumnMapping).length > 0) {
                column_data = window.attendanceColumnMapping;
                
                // Update button state if all 4 columns are mapped
                if (Object.keys(column_data).length == 4) {
                    $("#import").removeAttr("disabled");
                    employee_id = column_data.employee_id;
                    date = column_data.date;
                    clock_in = column_data.clock_in;
                    clock_out = column_data.clock_out;
                }
                
                // Clear the window variable
                window.attendanceColumnMapping = null;
            }
        }, 1000);

        $(document).on('change', '.set_column_data', function() {
            var column_name = $(this).val();
            var column_number = $(this).data('column_number');

            // Check if this column name is already assigned to another column
            if (column_name != '' && column_name in column_data && column_data[column_name] != column_number) {
                toastrs('Error', 'You have already defined ' + column_name + ' column', 'error');
                $(this).val('');
                return false;
            }

            // Remove any previous assignment for this column number
            for (const [key, value] of Object.entries(column_data)) {
                if (value == column_number) {
                    delete column_data[key];
                }
            }

            // Add new assignment
            if (column_name != '') {
                column_data[column_name] = column_number;
            }

            // Check if all 4 columns are selected
            var total_selection = Object.keys(column_data).length;
            if (total_selection == 4) {
                $("#import").removeAttr("disabled");
                employee_id = column_data.employee_id;
                date = column_data.date;
                clock_in = column_data.clock_in;
                clock_out = column_data.clock_out;
            } else {
                $('#import').attr('disabled', 'disabled');
            }
        });

        $(document).on('click', '#import', function(event) {

            event.preventDefault();
            $.ajax({
                url: "{{ route('attendance.import.data') }}",
                method: "POST",
                data: {
                    employee_id: employee_id,
                    date: date,
                    clock_in: clock_in,
                    clock_out: clock_out,
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function() {
                    $('#import').attr('disabled', 'disabled');
                    $('#import').text('Importing...');
                },
                success: function(data) {
                    $('#import').attr('disabled', false);
                    $('#import').text('Import');
                    $('#upload_form')[0].reset();

                    if (data.html == true) {
                        $('#process_area').html(data.response);
                        $("button").hide();
                        toastrs('Error', 'These data are not inserted', 'error');

                    } else {
                        $('#message').html(data.response);
                        $('#commonModalOver').modal('hide')
                        toastrs('Success', data.response, 'success');
                        location.reload();
                    }

                }
            })

        });
    });
</script>

