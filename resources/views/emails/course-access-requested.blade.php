<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: ui-sans-serif, system-ui, sans-serif; line-height: 1.6; color: #1a1a1a; max-width: 34rem; margin: 0 auto; padding: 1.5rem;">
    <p style="margin: 0 0 1rem;">{{ __('A model requested course access.') }}</p>
    <p style="margin: 0 0 0.5rem;"><strong>{{ __('Model:') }}</strong> {{ $accessRequest->user->name }} ({{ $accessRequest->user->email }})</p>
    <p style="margin: 0 0 1rem;"><strong>{{ __('Course:') }}</strong> {{ $accessRequest->course->title }}</p>

    @if (filled($accessRequest->course->course_access_requirements))
        <div style="margin: 0 0 1rem; padding: 1rem; background-color: #f8f2e6; border-left: 4px solid #c9a96e;">
            <p style="margin: 0 0 0.5rem; font-weight: 700;">{{ __('Kayla access requirements') }}</p>
            <p style="margin: 0; white-space: pre-line;">{{ $accessRequest->course->course_access_requirements }}</p>
        </div>
    @endif

    @if ($accessRequest->course->accessPhaseInstructions() !== [])
        <div style="margin: 0 0 1rem; padding: 1rem; background-color: #f8f2e6; border-left: 4px solid #c9a96e;">
            <p style="margin: 0 0 0.5rem; font-weight: 700;">{{ __('Website verification process') }}</p>
            @foreach ($accessRequest->course->accessPhaseInstructions() as $phase)
                <p style="margin: 0.65rem 0 0.25rem; font-weight: 700;">{{ $phase['label'] }}</p>
                <p style="margin: 0; white-space: pre-line;">{{ $phase['instructions'] }}</p>
            @endforeach
        </div>
    @endif

    @if (filled($accessRequest->member_notes))
        <div style="margin: 0 0 1rem; padding: 1rem; background-color: #f7f7f7;">
            <p style="margin: 0 0 0.5rem; font-weight: 700;">{{ __('Model note') }}</p>
            <p style="margin: 0; white-space: pre-line;">{{ $accessRequest->member_notes }}</p>
        </div>
    @endif

    @if ($accessRequest->proofFiles->isNotEmpty())
        <div style="margin: 0 0 1rem; padding: 1rem; background-color: #f7f7f7;">
            <p style="margin: 0 0 0.5rem; font-weight: 700;">{{ __('Course proof files') }}</p>
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach ($accessRequest->proofFiles as $file)
                    <li>{{ $file->original_name }} @if ($file->displaySize())({{ $file->displaySize() }})@endif</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p style="margin: 0 0 2rem;">
        <a href="{{ $adminUrl }}" style="display: inline-block; background-color: #c9a96e; color: #ffffff; text-decoration: none; padding: 0.65rem 1.25rem; font-weight: 600;">{{ __('Review in admin onboarding') }}</a>
    </p>
    <p style="margin: 0; font-size: 0.875rem; color: #717182;">{{ __('Approve by unlocking the course for this model from the onboarding panel.') }}</p>
</body>
</html>
