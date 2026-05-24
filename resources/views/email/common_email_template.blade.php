{{-- Dynamic Email Templates (from database) --}}
{{-- Layout is auto-selected: admin branding when sent by Super Admin, company branding otherwise --}}
@php
    $companyUserId = $user_id ?? null;
    $workspace     = $workspace_id ?? null;

    // Determine layout based on sending user type
    $__emailLayout = 'emails.layouts.company';
    if (!empty($companyUserId)) {
        $__sendingUser = \App\Models\User::find($companyUserId);
        if ($__sendingUser && $__sendingUser->type === 'super admin') {
            $__emailLayout = 'emails.layouts.admin';
        }
    }
@endphp
@extends($__emailLayout)

@section('content')
    {!! $content !!}
@endsection
