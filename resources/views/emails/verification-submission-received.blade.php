<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: ui-sans-serif, system-ui, sans-serif; line-height: 1.6; color: #1a1a1a; max-width: 32rem; margin: 0 auto; padding: 1.5rem;">
    <p style="margin: 0 0 1rem;">{{ __('Hi :name,', ['name' => $profile->user->name]) }}</p>
    <p style="margin: 0 0 1rem;">{{ __('We received your verification documents. The Paradise Dolls team will review them and let you know when your account is fully approved.') }}</p>
    <p style="margin: 0; font-size: 0.875rem; color: #717182;">{{ __('If anything needs updating, we will contact you through your registered email address.') }}</p>
</body>
</html>
