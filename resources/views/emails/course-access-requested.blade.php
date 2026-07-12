<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin: 0; padding: 0; background-color: #fff6f8; font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #21151b;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; background-color: #fff6f8;">
        <tr>
            <td align="center" style="padding: 32px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; max-width: 620px; overflow: hidden; border: 1px solid #f8cfd8; border-radius: 18px; background-color: #ffffff; box-shadow: 0 18px 45px rgba(105, 44, 64, 0.10);">
                    <tr>
                        <td style="padding: 28px 32px 22px; background-color: #fff0f4; border-bottom: 1px solid #f8cfd8;">
                            <p style="margin: 0 0 8px; color: #c76887; font-size: 11px; font-weight: 800; letter-spacing: 0.22em; text-transform: uppercase;">{{ __('Paradise Dolls Admin') }}</p>
                            <h1 style="margin: 0; color: #2a171f; font-family: Georgia, 'Times New Roman', serif; font-size: 30px; font-weight: 500; line-height: 1.15;">{{ __('Course access request') }}</h1>
                            <p style="margin: 14px 0 0; max-width: 520px; color: #6f5b64; font-size: 15px; line-height: 1.65;">{{ __('A model has requested access to a course. Review the details below, then unlock the course from the admin onboarding panel when approved.') }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 28px 32px 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; border: 1px solid #f8d6df; border-radius: 14px; background-color: #fff8fa;">
                                <tr>
                                    <td style="padding: 18px 20px 10px;">
                                        <p style="margin: 0 0 4px; color: #c76887; font-size: 10px; font-weight: 800; letter-spacing: 0.18em; text-transform: uppercase;">{{ __('Model') }}</p>
                                        <p style="margin: 0; color: #24141a; font-size: 16px; font-weight: 800;">{{ $accessRequest->user->name }}</p>
                                        <p style="margin: 4px 0 0; color: #7e6972; font-size: 13px;"><a href="mailto:{{ $accessRequest->user->email }}" style="color: #b94f72; text-decoration: none;">{{ $accessRequest->user->email }}</a></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 20px 18px; border-top: 1px solid #f8d6df;">
                                        <p style="margin: 0 0 4px; color: #c76887; font-size: 10px; font-weight: 800; letter-spacing: 0.18em; text-transform: uppercase;">{{ __('Course') }}</p>
                                        <p style="margin: 0; color: #24141a; font-size: 15px; font-weight: 700; line-height: 1.5;">{{ $accessRequest->course->title }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    @if (filled($accessRequest->course->course_access_requirements))
                        <tr>
                            <td style="padding: 14px 32px 0;">
                                <div style="padding: 18px 20px; border: 1px solid #ead3aa; border-left: 5px solid #c9a96e; border-radius: 14px; background-color: #fffaf0;">
                                    <p style="margin: 0 0 8px; color: #8f6f2d; font-size: 11px; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase;">{{ __('Kayla access requirements') }}</p>
                                    <p style="margin: 0; color: #34251a; font-size: 14px; line-height: 1.65; white-space: pre-line;">{{ $accessRequest->course->course_access_requirements }}</p>
                                </div>
                            </td>
                        </tr>
                    @endif

                    @if ($accessRequest->course->accessPhaseInstructions() !== [])
                        <tr>
                            <td style="padding: 14px 32px 0;">
                                <div style="padding: 18px 20px; border: 1px solid #ead3aa; border-left: 5px solid #c9a96e; border-radius: 14px; background-color: #fffaf0;">
                                    <p style="margin: 0 0 8px; color: #8f6f2d; font-size: 11px; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase;">{{ __('Website verification process') }}</p>
                                    @foreach ($accessRequest->course->accessPhaseInstructions() as $phase)
                                        <p style="margin: 12px 0 4px; color: #2d1f15; font-size: 14px; font-weight: 800;">{{ $phase['label'] }}</p>
                                        <p style="margin: 0; color: #4d3e34; font-size: 14px; line-height: 1.65; white-space: pre-line;">{{ $phase['instructions'] }}</p>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endif

                    @if (filled($accessRequest->member_notes))
                        <tr>
                            <td style="padding: 14px 32px 0;">
                                <div style="padding: 18px 20px; border: 1px solid #f8d6df; border-radius: 14px; background-color: #fff8fa;">
                                    <p style="margin: 0 0 8px; color: #c76887; font-size: 11px; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase;">{{ __('Model note') }}</p>
                                    <p style="margin: 0; color: #33242b; font-size: 14px; line-height: 1.65; white-space: pre-line;">{{ $accessRequest->member_notes }}</p>
                                </div>
                            </td>
                        </tr>
                    @endif

                    @if ($accessRequest->proofFiles->isNotEmpty())
                        <tr>
                            <td style="padding: 14px 32px 0;">
                                <div style="padding: 18px 20px; border: 1px solid #f8d6df; border-radius: 14px; background-color: #fff8fa;">
                                    <p style="margin: 0 0 8px; color: #c76887; font-size: 11px; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase;">{{ __('Course proof files') }}</p>
                                    <ul style="margin: 0; padding-left: 18px; color: #33242b; font-size: 14px; line-height: 1.7;">
                                        @foreach ($accessRequest->proofFiles as $file)
                                            <li>{{ $file->original_name }} @if ($file->displaySize())({{ $file->displaySize() }})@endif</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td align="center" style="padding: 28px 32px 8px;">
                            <a href="{{ $adminUrl }}" style="display: inline-block; min-width: 220px; border-radius: 10px; background-color: #EEB4C3; color: #181318; font-size: 13px; font-weight: 800; letter-spacing: 0.08em; line-height: 1; padding: 16px 22px; text-align: center; text-decoration: none; text-transform: uppercase;">{{ __('Review request') }}</a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 14px 32px 30px;">
                            <p style="margin: 0; color: #7e6972; font-size: 13px; line-height: 1.6; text-align: center;">{{ __('Approve by unlocking the course for this model from the onboarding panel.') }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
