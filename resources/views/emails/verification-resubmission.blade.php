<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: ui-sans-serif, system-ui, sans-serif; line-height: 1.6; color: #1a1a1a; max-width: 32rem; margin: 0 auto; padding: 1.5rem;">
    <p style="margin: 0 0 1rem;">{{ __('Hi :name,', ['name' => $profile->user->name]) }}</p>
    <p style="margin: 0 0 1rem;">{{ __('The Paradise Dolls onboarding team needs an update before your verification can be approved.') }}</p>
    <div style="margin: 0 0 1.5rem; border-left: 3px solid #c9a96e; padding: 0.75rem 1rem; background-color: #f8f3ea;">
        <p style="margin: 0 0 0.35rem; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #8a6a32;">{{ __('Resubmission instructions') }}</p>
        <p style="margin: 0; white-space: pre-line;">{{ $profile->verification_notes }}</p>
    </div>
    <p style="margin: 0 0 2rem;">
        <a href="{{ $verificationUrl }}" style="display: inline-block; background-color: #c9a96e; color: #ffffff; text-decoration: none; padding: 0.65rem 1.25rem; font-weight: 600;">{{ __('Update verification') }}</a>
    </p>
    <p style="margin: 0; font-size: 0.875rem; color: #717182;">{{ __('You can also see this note from your member dashboard and verification page.') }}</p>
</body>
</html>
