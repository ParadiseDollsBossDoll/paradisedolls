<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: ui-sans-serif, system-ui, sans-serif; line-height: 1.6; color: #1a1a1a; max-width: 32rem; margin: 0 auto; padding: 1.5rem;">
    <p style="margin: 0 0 1rem;">{{ __('Hi :name,', ['name' => $profile->user->name]) }}</p>
    <p style="margin: 0 0 1rem;">{{ __('We received your Model Information Form. The Paradise Dolls onboarding team will check it and request verification when your member profile is ready for the next step.') }}</p>
    <p style="margin: 0; font-size: 0.875rem; color: #717182;">{{ __('Thank you for helping us set up your account properly.') }}</p>
</body>
</html>
