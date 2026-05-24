@extends('layouts.main')
@section('page-title')
    {{__('Email Templates')}}
@endsection
@section("page-breadcrumb")
    {{__('Email Templates')}}
@endsection
@push('css')
    <link href="{{  asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css')  }}" rel="stylesheet">
@endpush

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                {{Form::model($emailTemplate, array('route' => array('email-templates.update', $emailTemplate->id), 'method' => 'PUT')) }}
                <div class="mb-3">
                    {{Form::label('name',__('Name'),['class'=>'form-label'])}}
                    {{Form::text('name',null,array('class'=>'form-control','disabled'=>'disabled'))}}
                </div>
                <div class="mb-3">
                    {{Form::label('from',__('From'),['class'=>'form-label'])}}
                    {{Form::text('from',null,array('class'=>'form-control','required'=>'required'))}}
                </div>
                {{Form::hidden('lang',$currEmailTempLang->lang,array('class'=>''))}}
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-rc-primary">{{__('Save')}}</button>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h6 class="mb-3">{{__('Variables')}}</h6>
                @php
                    $variables = json_decode($currEmailTempLang->variables);
                @endphp
                @if(!empty($variables) > 0)
                <div class="row">
                    @foreach  ($variables as $key => $var)
                    <div class="col-md-6 mb-2">
                        <div class="d-flex justify-content-between align-items-center p-2" style="background: var(--rc-gray-50); border-radius: var(--rc-radius-sm);">
                            <span style="font-size: var(--rc-font-sm); color: var(--rc-gray-700);">{{__($key)}}</span>
                            <code style="font-size: var(--rc-font-sm); color: var(--rc-primary); background: var(--rc-primary-light); padding: 2px 8px; border-radius: var(--rc-radius-xs);">{{ '{'.$var.'}' }}</code>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-12">
        <div class="row">
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($languages as $key => $lang)
                            <a class="list-group-item list-group-item-action {{($currEmailTempLang->lang == $key)?'active':''}}" 
                               href="{{route('manage.email.language',[$emailTemplate->id,$key])}}"
                               style="border-left: 3px solid {{($currEmailTempLang->lang == $key) ? 'var(--rc-primary)' : 'transparent'}}; {{($currEmailTempLang->lang == $key) ? 'background: var(--rc-primary-light); font-weight: var(--rc-font-semibold);' : ''}}">
                                {{Str::ucfirst($lang)}}
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="card">
                    <div class="card-body">
                        {{Form::model($currEmailTempLang, array('route' => array('store.email.language',$currEmailTempLang->parent_id), 'method' => 'PUT')) }}
                        <div class="mb-3">
                            {{Form::label('subject',__('Subject'),['class'=>'form-label'])}}
                            {{Form::text('subject',null,array('class'=>'form-control','required'=>'required'))}}
                        </div>
                        <div class="mb-3">
                            {{Form::label('content',__('Email Message'),['class'=>'form-label'])}}
                            {{Form::textarea('content',$currEmailTempLang->content,array('class'=>'summernote','id'=>'content','required'=>'required'))}}
                        </div>
                        {{Form::hidden('lang',null)}}
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-rc-primary">{{__('Save')}}</button>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
@endpush
