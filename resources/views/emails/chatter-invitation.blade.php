<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0;background:#f8f3f5;font-family:Arial,sans-serif;color:#2d1b2e;">
    <div style="max-width:560px;margin:0 auto;padding:32px 18px;">
        <div style="background:#fff;border:1px solid #f0d9e0;border-radius:8px;overflow:hidden;">
            <div style="height:7px;background:#eeb4c3;"></div>
            <div style="padding:30px;">
                <p style="margin:0 0 8px;color:#c45f7d;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;">{{ __('Paradise Dolls Team') }}</p>
                <h1 style="margin:0 0 18px;font-family:Georgia,serif;font-size:28px;font-weight:500;">{{ __('Welcome, :name', ['name' => $chatter->name]) }}</h1>
                <p style="margin:0 0 16px;line-height:1.65;">{{ __('Your chatter workspace is ready. Use it to clock in and out, record breaks, review your hours, and submit weekly timesheets.') }}</p>
                <p style="margin:0 0 24px;line-height:1.65;">{{ __('Create your password using the secure button below. This invitation link expires automatically for your protection.') }}</p>
                <p style="margin:0 0 24px;"><a href="{{ $setupUrl }}" style="display:inline-block;background:#eeb4c3;color:#23171f;text-decoration:none;padding:13px 20px;border-radius:6px;font-weight:700;">{{ __('Set Password & Open Workspace') }}</a></p>
                <p style="margin:0;color:#74646f;font-size:13px;line-height:1.55;">{{ __('If you were not expecting this invitation, please contact admin@getrichwithparadisedolls.com.') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
