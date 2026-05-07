<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: ui-sans-serif, system-ui, sans-serif; line-height: 1.6; color: #1a1a1a; max-width: 32rem; margin: 0 auto; padding: 1.5rem;">
    <p style="margin: 0 0 1rem;">{{ __('Hi :name,', ['name' => $profile->user->name]) }}</p>
    <p style="margin: 0 0 1rem;">{{ __('Welcome to the Paradise Dolls community. Use the invite below to join and the team will assign your :role role.', ['role' => $roleName]) }}</p>
    <p style="margin: 0 0 2rem;">
        <a href="{{ $communityUrl }}" style="display: inline-block; background-color: #c9a96e; color: #ffffff; text-decoration: none; padding: 0.65rem 1.25rem; font-weight: 600;">{{ __('Join community') }}</a>
    </p>
    <p style="margin: 0; font-size: 0.875rem; color: #717182;">{{ $communityUrl }}</p>
</body>
</html>
