<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: ui-sans-serif, system-ui, sans-serif; line-height: 1.6; color: #1a1a1a; max-width: 32rem; margin: 0 auto; padding: 1.5rem;">
    <p style="margin: 0 0 1rem;">{{ __('Hi :name,', ['name' => $profile->user->name]) }}</p>
    <p style="margin: 0 0 1rem;">{{ __('Your Model Information Form has been reviewed. Please complete verification by uploading your valid ID and a selfie holding your ID from inside your member account.') }}</p>
    <p style="margin: 0 0 2rem;">
        <a href="{{ $verificationUrl }}" style="display: inline-block; background-color: #c9a96e; color: #ffffff; text-decoration: none; padding: 0.65rem 1.25rem; font-weight: 600;">{{ __('Complete verification') }}</a>
    </p>
    <p style="margin: 0; font-size: 0.875rem; color: #717182;">{{ __('Your documents are reviewed privately by the onboarding team.') }}</p>
</body>
</html>
