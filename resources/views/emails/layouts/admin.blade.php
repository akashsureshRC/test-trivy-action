{{--
    Admin → Customer Email Layout
    Uses Admin (Service Provider) Branding Settings.

    Usage in child templates:
      @extends('emails.layouts.admin')
      @section('preheader', 'Preview text here')
      @section('content')
          ... your email body ...
      @endsection
--}}
@php
    $brand = getEmailBranding('admin');
@endphp

@extends('emails.layouts.base')
