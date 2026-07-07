<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin: 0; padding: 0; background: #f8f3f5; color: #241f23; font-family: Arial, sans-serif; line-height: 1.65;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background: #f8f3f5; padding: 24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; background: #ffffff; border: 1px solid #f2dce3;">
                    <tr>
                        <td style="padding: 24px 28px 12px; text-align: center; color: #b94f72; font-family: Georgia, serif; font-size: 22px;">Paradise Dolls</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 28px 28px; font-size: 15px;">
                            <div>{!! nl2br(e($renderedBody)) !!}</div>

                            @if ($campaignRun->action_label && $campaignRun->action_url)
                                <p style="margin: 26px 0 8px; text-align: center;">
                                    <a href="{{ $campaignRun->action_url }}" style="display: inline-block; padding: 12px 20px; background: #EEB4C3; color: #181318; text-decoration: none; font-weight: 700;">{{ $campaignRun->action_label }}</a>
                                </p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 18px 28px; border-top: 1px solid #f2dce3; color: #7d7278; font-size: 11px; text-align: center;">
                            <p style="margin: 0 0 6px;">{{ __('You are receiving this email as a Paradise Dolls member.') }}</p>
                            <a href="{{ $unsubscribeUrl }}" style="color: #a65370;">{{ __('Manage email preferences') }}</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
