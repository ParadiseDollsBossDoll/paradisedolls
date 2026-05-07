<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: ui-sans-serif, system-ui, sans-serif; line-height: 1.6; color: #1a1a1a; max-width: 34rem; margin: 0 auto; padding: 1.5rem;">
    <p style="margin: 0 0 1rem; font-weight: 700;">{{ __('New application submitted') }}</p>
    <p style="margin: 0 0 1rem;">{{ __(':name has applied to Paradise Dolls.', ['name' => $application->name]) }}</p>
    <ul style="margin: 0 0 1.5rem; padding-left: 1.25rem;">
        <li>{{ __('Email: :email', ['email' => $application->email]) }}</li>
        <li>{{ __('Experience: :experience', ['experience' => $application->experience_level ?: 'Not provided']) }}</li>
        <li>{{ __('18+ confirmed: :confirmed', ['confirmed' => $application->age_confirmed ? 'Yes' : 'No']) }}</li>
        <li>{{ __('Photos attached: :count', ['count' => count($application->photo_paths ?? [])]) }}</li>
    </ul>
    <p style="margin: 0;">
        <a href="{{ $adminUrl }}" style="display: inline-block; background-color: #c9a96e; color: #ffffff; text-decoration: none; padding: 0.65rem 1.25rem; font-weight: 600;">{{ __('Review application') }}</a>
    </p>
</body>
</html>
