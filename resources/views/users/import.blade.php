{{ Form::open(['method' => 'post', 'enctype' => 'multipart/form-data', 'id' => 'upload_form']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12 mb-6">
            {{ Form::label('file', __('Download Sample User CSV File'), ['class' => 'form-label text-danger ']) }}
            <a href="{{ asset('uploads/sample/sample_user.csv') }}" download
                class="btn btn-sm btn-rc-icon mx-2" data-bs-toggle="tooltip" 
                title="{{ __('Download Sample CSV') }}">
                <i class="ti ti-download"></i>
            </a>
        </div>
        <div class="col-md-12 mt-1">
            {{ Form::label('file', __('Select CSV File'), ['class' => 'form-label']) }}
            <div class="choose-file form-group">
                <label for="file" class="form-label w-100">
                    <input type="file" class="form-control" name="file" id="file" accept=".csv" name="upload_file" data-filename="upload_file" required>
                </label>
                <p class="upload_file"></p>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="submit" value="{{ __('Upload') }}" class="btn btn-rc-primary">{{__('Upload')}}</button>
    <a href="" data-url="{{ route('users.import.modal') }}" data-ajax-popup-over="true" title="{{ __('Create') }}" data-size="xl" data-title="{{ __('Import User CSV Data') }}"  class="d-none import_modal_show"></a>
</div>
{{ Form::close() }}
<script>
    $('#upload_form').on('submit', function(event)
    {
        event.preventDefault();
        event.stopImmediatePropagation();
        let data = new FormData(this);
        data.append('_token', "{{ csrf_token() }}");
        $.ajax({
            url: "{{ route('users.import') }}",
            method: "POST",
            data: data,
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            success: function(data) {
                if (data.error != '')
                {
                    toastrs('Error',data.error, 'error');
                } else {
                    $('#commonModal').modal('hide');
                    $(".import_modal_show").trigger( "click" );
                    setTimeout(function() {
                        SetData(data.output);
                    }, 1000);
                }
            }
        });

    });
    function SetData(params, count = 0)
    {
        if(count < 10)
        {
            var process_area = document.getElementById("process_area");
            if(process_area)
            {
                $('#process_area').html(params);
                
                // Initialize column mapping after data is loaded
                setTimeout(function() {
                    if (typeof initializeColumnMapping === 'function') {
                        initializeColumnMapping();
                    }
                }, 100);
            }
            else
            {
                setTimeout(function() {
                    SetData(params, count + 1);
                }, 500);
            }
        }
        else
        {
            toastrs('Error', '{{ __("Unable to load import data. Please try again.") }}', 'error');
        }
    }
</script>
