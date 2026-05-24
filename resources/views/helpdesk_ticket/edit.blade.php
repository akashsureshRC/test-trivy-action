@extends('layouts.main')

@section('page-title')
    {{ __('Reply Ticket') }} - {{ $ticket->ticket_id }}
@endsection

@section('page-breadcrumb')
    {{ __('Tickets') }},{{ __('Reply') }}
@endsection

@section('page-action')
<div>
    @permission('helpdesk ticket edit')
        @if(Auth::user()->id == $ticket->created_by || Auth::user()->type == 'super admin')
            <a href="#ticket-info" class="btn btn-sm btn-rc-icon" type="button" data-bs-toggle="collapse"
                title="{{ __('Edit Ticket') }}"><i class="ti ti-pencil"></i></a>
        @endif
    @endpermission
</div>
@endsection
@push('css')
    <link href="{{  asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css')  }}" rel="stylesheet">
@endpush
@section('content')
    @permission('helpdesk ticket edit')
        {{ Form::model($ticket, ['route' => ['helpdesk.update', $ticket->id], 'id' => 'ticket-info', 'class' => 'collapse mt-3', 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="card">
                    <div class="card-body">
                        @if(Auth::user()->type == 'super admin')
                            <div class="row">
                                <div class="form-group col-md-6" id="customname">
                                    @php
                                        $name =  'Customers';
                                    @endphp
                                    <label class="require form-label">{{$name}}</label>
                                            <select  class="form-control select_person_email" name="name"  {{ !empty($errors->first('name')) ? 'is-invalid' : '' }} required="">
                                                <option value="">{{ __('Select User') }}</option>
                                                @foreach ($users as $key=>$value)
                                                    <option value="{{ $key }} " {{ $ticket->user_id == $key ? 'selected' : '' }}>{{ $value }}</option>
                                                @endforeach
                                            </select>
                                    <div class="invalid-feedback">
                                        {{ $errors->first('name') }}
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Email') }}</label>
                                    <input class="form-control emailAddressField {{ !empty($errors->first('email')) ? 'is-invalid' : '' }}"
                                        type="email" name="email" id="emailAddressField" required="" value="{{ $ticket->email }}"
                                        placeholder="{{ __('Email') }}" readonly  style="background-color:#e9ecef ">
                                    @if ($errors->has('email'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('email') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Category') }}</label>
                                <select class="form-select {{ !empty($errors->first('category')) ? 'is-invalid' : '' }}"
                                    name="category" required="">
                                    <option value="">{{ __('Select Category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @if ($ticket->category == $category->id) selected @endif>
                                            {{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('category'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('category') }}
                                    </div>
                                @endif
                            </div>
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Status') }}</label>
                                <select class="form-select {{ !empty($errors->first('status')) ? 'is-invalid' : '' }}"
                                    name="status" required="">
                                    <option value="In Progress" @if ($ticket->status == 'In Progress') selected @endif>
                                        {{ __('In Progress') }}</option>
                                    <option value="On Hold" @if ($ticket->status == 'On Hold') selected @endif>
                                        {{ __('On Hold') }}</option>
                                    <option value="Closed" @if ($ticket->status == 'Closed') selected @endif>
                                        {{ __('Closed') }}</option>
                                </select>
                                @if ($errors->has('status'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('status') }}
                                    </div>
                                @endif
                            </div>

                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Subject') }}</label>
                                <input class="form-control {{ !empty($errors->first('subject')) ? 'is-invalid' : '' }}"
                                    type="text" name="subject" required="" value="{{ $ticket->subject }}"
                                    placeholder="{{ __('Subject') }}">
                                @if ($errors->has('subject'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('subject') }}
                                    </div>
                                @endif
                            </div>

                            <div class="form-group col-md-6">
                                <label class="form-label">{{ __('Attachments') }}
                                    <small>({{ __('You can select multiple files') }})</small> </label>
                                <div class="choose-files">
                                    <label for="edit_ticket_file">
                                        <div class="bg-primary">
                                            <i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                        </div>
                                        <input type="file" name="attachments[]" id="edit_ticket_file"
                                            multiple="" data-filename="multiple_file_selection">
                                    </label>
                                </div>
                                @error('attachments')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div class="pt-2">
                                    <p class="multiple_file_selection mb-0"></p>
                                    <ul class="list-group list-group-flush attachment_list">
                                        @if (!empty($ticket->attachments))
                                            @php $attachments = json_decode($ticket->attachments); @endphp
                                            @foreach ($attachments as $index => $attachment)
                                                <li class="list-group-item py-1 px-0">
                                                    {{ $attachment->name }}
                                                    <a download="{{ $attachment->name }}"
                                                        href="{{ getHelpdeskAttachmentUrl($attachment->path) }}" class="edit-icon py-1 ml-2"
                                                        title="{{ __('Download') }}"><i class="fas fa-download ms-2"></i></a>

                                                    <a class="bg-danger mx-3 btn btn-sm d-inline-flex align-items-center"
                                                        title="{{ __('Delete') }}"
                                                        onclick="event.preventDefault(); openRcConfirmDialog('Are you sure?', 'This action can not be undone. Do you want to continue?').then((result) => { if (result.isConfirmed) { document.getElementById('user-form-{{ $index }}').submit(); } });"><i
                                                            class="ti ti-trash text-white"></i></a>
                                                </li>
                                            @endforeach
                                        @endif
                                    </ul>
                                </div>
                            </div>

                            <div class="form-group col-md-12 mt-2">
                                <label class="require form-label">{{ __('Description') }}</label>
                                <textarea name="description"
                                    class="form-control summernote {{ !empty($errors->first('description')) ? 'is-invalid' : '' }}" id="description">{!! $ticket->description !!}</textarea>
                                @if ($errors->has('description'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('description') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a class="btn btn-rc-outline"
                                href="{{ route('helpdesk.index') }}">{{ __('Cancel') }}</a>
                            <button class="btn btn-rc-primary" type="submit">{{ __('Update') }}</button>
                        </div>

                    </div>

                </div>
            </div>

        </div>
        {{ Form::close() }}
        @if (!empty($ticket->attachments))
            @foreach ($attachments as $index => $attachment)
                <form method="post" id="user-form-{{ $index }}"
                    action="{{ route('helpdesk-ticket.attachment.destroy', [$ticket->id, $index]) }}">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
        @endif
    @endpermission
    <div class="row mt-3">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h6>
                            <span class="text-left">
                                {{ $ticket->name }} <small>({{ $ticket->created_at->diffForHumans() }})</small>
                                <span class="d-block"><small>{{ $ticket->email }}</small></span>
                            </span>
                        </h6>
                        <small>
                            <span class="text-right">
                                {{ __('Status') }} : <span
                                    class="badge rounded @if ($ticket->status == 'In Progress') badge bg-warning @elseif($ticket->status == 'On Hold') badge bg-danger @else badge bg-success @endif">{{ __($ticket->status) }}</span>
                            </span>
                            <span class="d-block mt-2">
                                {{ __('Category') }} : <span
                                    class="rc-status" style="background: {{ $ticket->tcategory ? $ticket->tcategory->color : '#6c757d' }}; color: #fff;">{{ $ticket->tcategory ? $ticket->tcategory->name : '-' }}</span>
                            </span>
                        </small>
                    </div>
                </div>
                <div class="card-body">
                    <div>
                        <p>{!! $ticket->description !!}</p>
                    </div>
                    @if (!empty($ticket->attachments))
                        @php $attachments = json_decode($ticket->attachments); @endphp
                        @if (count($attachments))
                            <div class="m-1">
                                <h6>{{ __('Attachments') }} :</h6>
                                <ul class="list-group list-group-flush">
                                    @foreach ($attachments as $index => $attachment)
                                        <li class="list-group-item p-0">
                                            {{ $attachment->name }} <a download="{{ $attachment->name }}"
                                                href="{{ getHelpdeskAttachmentUrl($attachment->path) }}" class="edit-icon py-1 ml-2"
                                                title="{{ __('Download') }}"><i class="fas fa-download ms-2"></i></a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
            @foreach ($ticket->conversions as $conversion)
                <div class="card">
                    <div class="card-header">
                        <h6>{{ $conversion->replyBy()->name }}
                            <small>({{ $conversion->created_at->diffForHumans() }})</small>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div>{!! $conversion->description !!}</div>
                        @php $attachments = json_decode($conversion->attachments); @endphp
                        @if (count($attachments))
                            <div class="m-1">
                                <h6>{{ __('Attachments') }} :</h6>
                                <ul class="list-group list-group-flush">
                                    @foreach ($attachments as $index => $attachment)
                                        <li class="list-group-item px-0">
                                            {{ $attachment->name }}<a download="{{ $attachment->name }}"
                                                href="{{ getHelpdeskAttachmentUrl($attachment->path) }}" class="edit-icon py-1 ml-2"
                                                title="{{ __('Download') }}"><i class="fa fa-download ms-2"></i></a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                @permission('helpdesk ticket reply')
                    <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                        <div class="card">
                            <div class="card-header">
                                <h6>{{ __('Add Reply') }}
                                </h6>
                            </div>
                            <form method="post" id="SummernoteForm" action="{{ route('helpdesk-ticket.conversion.store', $ticket->id) }}"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="require form-label">{{ __('Description') }}</label>
                                        <textarea name="reply_description" class="form-control summernote" id="reply_description"></textarea>
                                        <div class="invalid-feedback d-block ">
                                            {{ $errors->first('reply_description') }}
                                        </div>
                                    </div>

                                    <p class="text-danger d-none" id="skill_validation">{{__('Description filed is required.')}}</p>
                                    <div class="form-group file-group">
                                        <label class="form-label">{{ __('Attachments') }}</label>
                                        <small class="form-text text-muted">({{ __('You can select multiple files') }})</small>
                                        <div class="choose-files mt-2">
                                            <label for="reply_file">
                                                <div class="bg-primary">
                                                    <i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                </div>
                                                <input type="file" name="reply_attachments[]" id="reply_file"
                                                    multiple="" data-filename="multiple_reply_file_selection">
                                            </label>
                                            <p class="multiple_reply_file_selection mt-2"></p>
                                        </div>
                                        @error('reply_attachments.*')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="text-end">
                                        <button class="btn btn-rc-primary mt-2"
                                            type="submit" id="save">{{ __('Submit') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                        <div class="card">
                            <div class="card-header">
                                <h6>{{ __('Note') }}
                                </h6>
                            </div>
                            <form method="post" id="notesform" action="{{ route('helpdesk-ticket.note.store', $ticket->id) }}">
                                @csrf
                                <div class="card-body adjust_card_width">
                                    <div class="form-group ckfix_height">
                                        <textarea name="note" class="form-control summernote" id="note">{{ $ticket->note }}</textarea>

                                        <div class="invalid-feedback">
                                            {{ $errors->first('note') }}
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <button class="btn btn-rc-primary mt-2"
                                            type="submit">{{ __('Add Note') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endpermission
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>

    <script>

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
    </script>
    <script>
        $("#notesform").submit(function(e)
        {
            var desc = $("#notesform iframe").val().find("body").text();
            if(!isNaN(desc))
            {
                $('#note_validation').removeClass('d-none')
                event.preventDefault();
            }
            else
            {
                $('#note_validation').addClass('d-none')
            }

        });
    </script>
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
    </script>
@endpush

@push('css')
    <style>
        .attachment_list li {
            list-style: none;
            display: inline;
        }
    </style>
@endpush
@push('scripts')
@endpush
