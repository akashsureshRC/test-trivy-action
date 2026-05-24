{{--
    Customer → Employee Email Layout
    Uses Company Branding Settings.

    Usage in child templates:
      @extends('emails.layouts.company')
      @section('preheader', 'Preview text here')
      @section('content')
          ... your email body ...
      @endsection

    If sending from a queued job without Auth context, the Mailable
    should pass $companyUserId and $workspace to the view, then override:
      @php $brand = getEmailBranding('company', $companyUserId, $workspace); @endphp
--}}
@php
    if (!isset($brand)) {
        $cUserId  = $companyUserId ?? null;
        $wId      = $workspace ?? null;

        // Auto-resolve from Employee model (ESS emails have $employee but no $companyUserId)
        if (empty($cUserId) && isset($employee) && !empty($employee->workspace_id)) {
            $ws       = \App\Models\WorkSpace::find($employee->workspace_id);
            $cUserId  = $ws->created_by ?? null;
            $wId      = $employee->workspace_id;
        }

        $brand = getEmailBranding('company', $cUserId, $wId);
    }
@endphp

@extends('emails.layouts.base')
