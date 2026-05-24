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
    var name = 0;
    var email = 0;
    var column_data = [];
    
    function initializeColumnMapping()
    {
        column_data = [];
        
        // Initialize column_data with pre-selected values
        $('.set_column_data').each(function() {
            var column_name = $(this).val();
            var column_number = $(this).data('column_number');
            
            if (column_name != '') {
                column_data[column_name] = column_number;
            }
        });

        // Check if we have all required columns selected (name + email)
        var total_selection = Object.keys(column_data).length;
        if (total_selection >= 2 && 'name' in column_data && 'email' in column_data) {
            $("#import").removeAttr("disabled");
            name = column_data.name;
            email = column_data.email;
        } else {
            $('#import').attr('disabled', 'disabled');
        }
    }

    $(document).ready(function()
    {
        var total_selection = 0;
        var first_name = 0;
        var last_name = 0;

        // Initialize when modal is shown
        $('#commonModalOver').on('shown.bs.modal', function () {
            setTimeout(function() {
                initializeColumnMapping();
            }, 300);
        });

        $(document).on('change', '.set_column_data', function() {
            var column_name = $(this).val();
            var column_number = $(this).data('column_number');

            if (column_name in column_data) {
                toastrs('Error', 'You have already defined ' + column_name + ' column', 'error');
                $(this).val('');
                return false;
            }
            
            if (column_name != '') {
                column_data[column_name] = column_number;
            } else {
                const entries = Object.entries(column_data);
                for (const [key, value] of entries) {
                    if (value == column_number) {
                        delete column_data[key];
                    }
                }
            }

            total_selection = Object.keys(column_data).length;
            if (total_selection == 2) {
                $("#import").removeAttr("disabled");
                name = column_data.name;
                email = column_data.email;
            } else {
                $('#import').attr('disabled', 'disabled');
            }
        });

        $(document).on('click', '#import', function(event) {

            event.preventDefault();
            $.ajax({
                url: "{{ route('users.import.data') }}",
                method: "POST",
                data: {
                    name: name,
                    email: email,
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

