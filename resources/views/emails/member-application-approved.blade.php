<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: ui-sans-serif, system-ui, sans-serif; line-height: 1.6; color: #1a1a1a; max-width: 32rem; margin: 0 auto; padding: 1.5rem;">
    <p style="margin: 0 0 1rem;">{{ __('Hi :name,', ['name' => $memberName]) }}</p>
    <p style="margin: 0 0 1rem;">{{ __('Your Paradise Dolls application has been approved. The next step is to log in and complete your Model Information Form so the onboarding team can prepare your account properly.') }}</p>
    <p style="margin: 0 0 1rem;">{{ __('Use your email address and this temporary password:') }}</p>
    <p style="margin: 0 0 1.5rem; font-size: 1.125rem; font-weight: 600; letter-spacing: 0.05em; font-family: ui-monospace, monospace;">{{ $temporaryPassword }}</p>
    <p style="margin: 0 0 1rem;">{{ __('For security, change your password after you log in from your profile page.') }}</p>
    <p style="margin: 0 0 2rem;">
        <a href="{{ $onboardingUrl }}" style="display: inline-block; background-color: #c9a96e; color: #ffffff; text-decoration: none; padding: 0.65rem 1.25rem; font-weight: 600;">{{ __('Complete Model Information Form') }}</a>
    </p>
    <p style="margin: 0 0 1rem; font-size: 0.875rem; color: #717182;">{{ __('Login link: :url', ['url' => $loginUrl]) }}</p>
    <p style="margin: 0; font-size: 0.875rem; color: #717182;">{{ __('If you did not apply, please ignore this email.') }}</p>
</body>
</html>
