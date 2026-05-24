@extends('layouts.main')

@section('page-title')
    {{ __('Ticket') }} - {{ $ticket->ticket_id }}
@endsection

@section('page-breadcrumb')
    {{ __('Tickets') }},{{ __('View') }}
@endsection

@section('page-action')
<div>
    @permission('helpdesk ticket edit')
        @if(Auth::user()->id == $ticket->created_by || Auth::user()->type == 'super admin')
            <a href="{{ route('helpdesk.edit', $ticket->id) }}" class="btn btn-sm btn-rc-icon" 
                data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Edit Ticket') }}">
                <i class="ti ti-pencil"></i>
            </a>
        @endif
    @endpermission
</div>
@endsection

@push('css')
    <link href="{{  asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css')  }}" rel="stylesheet">
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6>{{ $ticket->name }} <small class="text-muted">({{ $ticket->created_at->diffForHumans() }})</small></h6>
                </div>
                <div class="card-body">
                    <div>
                        <p>{!! $ticket->description !!}</p>
                    </div>
                    @php
                        $attachments = json_decode($ticket->attachments);
                    @endphp
                    @if (!is_null($attachments) && count($attachments) > 0)
                        <div class="m-1">
                            <h6>{{ __('Attachments') }}:</h6>
                            <ul class="list-group list-group-flush">
                                @foreach ($attachments as $index => $attachment)
                                    <li class="list-group-item px-0">
                                        {{ $attachment->name }}<a download="{{ $attachment->name }}"
                                            href="{{ getHelpdeskAttachmentUrl($attachment->path) }}" class="edit-icon py-1 ms-2"
                                            title="{{ __('Download') }}"><i class="ti ti-download"></i></a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            @foreach ($ticket->conversions as $conversion)
                <div class="card">
                    <div class="card-header">
                        <h6>{{ $conversion->replyBy()->name }}
                            <small class="text-muted">({{ $conversion->created_at->diffForHumans() }})</small></h6>
                    </div>
                    <div class="card-body">
                        <div>{!! $conversion->description !!}</div>
                        @php
                            $attachments = json_decode($conversion->attachments);
                        @endphp
                        @if (count($attachments))
                            <div class="m-1">
                                <h6>{{ __('Attachments') }}:</h6>
                                <ul class="list-group list-group-flush">
                                    @foreach ($attachments as $index => $attachment)
                                        <li class="list-group-item px-0">
                                            {{ $attachment->name }}<a download="{{ $attachment->name }}"
                                                href="{{ getHelpdeskAttachmentUrl($attachment->path) }}"
                                                class="edit-icon py-1 ms-2" title="{{ __('Download') }}"><i
                                                    class="ti ti-download"></i></a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            @if ($ticket->status != 'Closed')
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Reply to Ticket') }}</h5>
                    </div>
                    <div class="card-body">
                        <form method="post"
                            action="{{ route('helpdesk-ticket.reply', [$ticket->ticket_id]) }}"
                            enctype="multipart/form-data" id="SummernoteForm">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label class="form-label">{{ __('Description') }} <span class="text-danger">*</span></label>
                                    <textarea name="reply_description"
                                        class="form-control summernote {{ $errors->has('reply_description') ? ' is-invalid' : '' }}" id="reply_description">{{ old('reply_description') }}</textarea>
                                    <div class="invalid-feedback">
                                        {{ $errors->first('reply_description') }}
                                    </div>
                                    <p class="text-danger d-none" id="skill_validation">{{__('Description field is required.')}}</p>
                                </div>
                                <div class="form-group col-md-12">
                                    <label class="form-label">{{ __('Attachments') }}</label>
                                    <small class="form-text text-muted">({{ __('You can select multiple files') }})</small>
                                    <div class="choose-files mt-2">
                                        <label for="reply_file">
                                            <div class="bg-primary">
                                                <i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                            </div>
                                            <input type="file" multiple="" name="reply_attachments[]" id="reply_file" data-filename="multiple_reply_file_selection">
                                        </label>
                                        <p class="multiple_reply_file_selection mt-2"></p>
                                    </div>
                                    @error('reply_attachments')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group col-md-12 text-end mt-3">
                                <input type="hidden" name="status" value="In Progress" />
                                <button type="submit" class="btn btn-rc-primary">{{ __('Submit Reply') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center">
                        <p class="text-primary font-weight-bold mb-0">
                            {{ __('Ticket is closed, you cannot reply.') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Initialize summernote
            if ($.fn.summernote) {
                $('.summernote').summernote({
                    height: 200,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline', 'clear']],
                        ['fontname', ['fontname']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ]
                });
            }

            $("#SummernoteForm").submit(function(e)
            {
                var desc = $("#reply_description").val();
                if(!isNaN(desc))
                {
                    $('#skill_validation').removeClass('d-none')
                    event.preventDefault();
                }
                else
                {
                    $('#skill_validation').addClass('d-none')
                }
            });

            // for Choose file - show selected file names
            $('#reply_file').on('change', function() {
                var names = '';
                var files = this.files;

                if (files.length > 0) {
                    for (var i = 0; i < files.length; i++) {
                        names += files[i].name + '<br>';
                    }
                    $('.multiple_reply_file_selection').html(names);
                } else {
                    $('.multiple_reply_file_selection').html('');
                }
            });
        });
    </script>
@endpush
