@extends('layouts.main')
@push('css')
<style>
/* Page-specific styles only */
</style>
@endpush
@section('page-title')
    {{ __('Create Ticket') }}
@endsection

@section('page-breadcrumb')
    {{ __('Tickets') }},{{ __('Create') }}
@endsection
@push('css')
    <link href="{{  asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css')  }}" rel="stylesheet">
@endpush
@section('content')
    <form action="{{ route('helpdesk.store') }}" class="mt-3" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-12 col-xs-12">
                <div class="card">
                    <div class="card-body">
                        @if(Auth::user()->type == 'super admin')
                            <div class="row">
                                @php
                                    $name =  'Customers';
                                @endphp
                                <div class="form-group col-md-6" id="customname">
                                    <label class="require form-label">{{ $name}}</label>
                                    <select  class="form-control select_person_email" name="name"  {{ !empty($errors->first('name')) ? 'is-invalid' : '' }} required="">
                                        <option value="">{{ __('Select User') }}</option>
                                        @foreach ($users as $key=>$value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>

                                    <div class="invalid-feedback">
                                        {{ $errors->first('name') }}
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Email') }}</label>
                                    <input class="form-control emailAddressField {{ !empty($errors->first('email')) ? 'is-invalid' : '' }}"
                                        type="email" name="email" required="" placeholder="{{ __('Email') }}"  readonly >
                                    <div class="invalid-feedback">
                                        {{ $errors->first('email') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Category') }}</label>
                                <select class="form-control {{ !empty($errors->first('category')) ? 'is-invalid' : '' }}"
                                    name="category" required="" id="category">
                                    <option value="">{{ __('Select Category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    {{ $errors->first('category') }}
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Status') }}</label>
                                <select class="form-control {{ !empty($errors->first('status')) ? 'is-invalid' : '' }}"
                                    name="status" required="" id="status">
                                    <option value="">{{ __('Select Status') }}</option>
                                    <option value="In Progress">{{ __('In Progress') }}</option>
                                    <option value="On Hold">{{ __('On Hold') }}</option>
                                    <option value="Closed">{{ __('Closed') }}</option>
                                </select>
                                <div class="invalid-feedback">
                                    {{ $errors->first('status') }}
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Subject') }}</label>
                                <input class="form-control {{ !empty($errors->first('subject')) ? 'is-invalid' : '' }}"
                                    type="text" name="subject" required="" placeholder="{{ __('Subject') }}">
                                <div class="invalid-feedback">
                                    {{ $errors->first('subject') }}
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="form-label">{{ __('Attachments') }}
                                    <small>({{ __('You can select multiple files') }})</small> </label>
                                <div class="choose-files">
                                    <label for="file">
                                        <div class="bg-primary">
                                            <i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                        </div>
                                        <input type="file" name="attachments[]" id="file"
                                            multiple="" data-filename="multiple_file_selection">
                                    </label>
                                    <p class="multiple_file_selection mt-2"></p>
                                </div>
                                @error('attachments.*')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group col-md-12">
                                <label class="require form-label">{{ __('Description') }}</label>
                                <textarea name="description"
                                    class="form-control summernote {{ !empty($errors->first('description')) ? 'is-invalid' : '' }}" 
                                    id="help-desc"></textarea>
                                <div class="invalid-feedback">
                                    {{ $errors->first('description') }}
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a class="btn btn-rc-outline"
                                href="{{ route('helpdesk.index') }}">{{ __('Cancel') }}</a>
                            <button class="btn btn-rc-primary"
                            type="submit">{{ __('Submit') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
    <script>

        $(document).on('change', '.select_person_email', function() {
            var userId = $(this).val();
            $.ajax({
                url: '{{ route('helpdesk-tickets.getuser') }}',
                type: 'POST',
                data: {
                    "user_id": userId,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    if(data.email)
                    {
                        $('.emailAddressField').val(data.email);
                        $('.emailAddressField').prop('readonly', true);
                        $('.emailAddressField').css('background-color', '#e9ecef');
                    }else{
                        $('.emailAddressField').val('');
                        $('.emailAddressField').prop('readonly', false);
                        $('.emailAddressField').css('background-color', '');
                    }
                }
            });
        });

        $('form').on('submit', function(e) {
            // Sync Summernote content to textarea before validation
            $('#help-desc').val($('#help-desc').summernote('code'));

            // Clear previous description error
            $('#help-desc').closest('.form-group').find('.desc-validation-error').remove();

            var desc = $('#help-desc').val();
            if (!desc || desc.replace(/<[^>]*>/g, '').trim() === '') {
                e.preventDefault();
                var $noteEditor = $('#help-desc').closest('.form-group').find('.note-editor');
                $noteEditor.after('<p class="text-danger desc-validation-error">{{ __("Description field is required.") }}</p>');
                $noteEditor.css('border-color', '#dc3545');
                // Don't return false — let the global handler also run to show other field errors
            }
        });

        // Clear description error when user types in Summernote
        $('#help-desc').on('summernote.change', function() {
            $(this).closest('.form-group').find('.desc-validation-error').remove();
            $(this).closest('.form-group').find('.note-editor').css('border-color', '');
        });
    </script>
@endpush
