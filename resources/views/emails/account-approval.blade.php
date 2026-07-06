<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: ui-sans-serif, system-ui, sans-serif; line-height: 1.65; color: #1a1a1a; max-width: 36rem; margin: 0 auto; padding: 1.5rem;">
    <p style="margin: 0 0 1rem;"><strong>{{ __('Welcome to the Team, :name Doll! 💖🥂', ['name' => $profile->user->name]) }}</strong></p>
    <p style="margin: 0 0 1rem;"><strong>{{ __('YOU DID IT! 🎉✨') }}</strong></p>
    <p style="margin: 0 0 1rem;">{{ __('Your verification has been successfully approved, and we’re absolutely thrilled to officially welcome you to the Paradise Dolls family! 💕') }}</p>
    <p style="margin: 0 0 1rem;">{{ __('This is the beginning of an exciting new chapter, and we couldn’t be happier to have you joining our incredible community of ambitious, supportive, and successful women.') }}</p>
    <p style="margin: 0 0 1rem;">{{ __('Over the coming days, we’ll be setting up your platforms, arranging your personalised training, and giving you full access to the Boss Doll Blueprint, so you have everything you need to hit the ground running.') }}</p>
    <p style="margin: 0 0 0.75rem;"><strong>{{ __('✨ Your Dashboard is now ready! ✨') }}</strong></p>
    <p style="margin: 0 0 1.5rem;">
        <a href="{{ $dashboardUrl }}" style="display: inline-block; background-color: #EEB4C3; color: #181318; text-decoration: none; padding: 0.75rem 1.35rem; border-radius: 0.35rem; font-weight: 700;">{{ __('Go to Dashboard') }}</a>
    </p>
    <p style="margin: 0 0 0.5rem;"><strong>{{ __('What’s next? 💕') }}</strong></p>
    <p style="margin: 0 0 1rem;">{{ __('Very soon, you’ll receive an invitation to our private Discord server, where you’ll find your training, tutorials, announcements, and the Paradise Dolls community. Your Discord invite will follow separately once the team has assigned the correct role.') }}</p>
    <p style="margin: 0 0 1rem;">{{ __('You’re also invited to our Paradise Dolls WhatsApp community, where you can meet the other Dolls, celebrate your wins, stay motivated, ask questions, and receive ongoing support from both the team and your fellow creators. 💖') }}</p>
    <p style="margin: 0 0 1.5rem;">
        <a href="{{ $whatsappCommunityUrl }}" style="display: inline-block; background-color: #EEB4C3; color: #181318; text-decoration: none; padding: 0.75rem 1.35rem; border-radius: 0.35rem; font-weight: 700;">{{ __('Join Our WhatsApp Community') }}</a>
    </p>
    <p style="margin: 0 0 1rem;">{{ __('We can’t wait to welcome you into both communities—they’re where the magic really begins! ✨') }}</p>
    <p style="margin: 0 0 0.65rem;">{{ __('If you need any assistance in the meantime, please contact us:') }}</p>
    <p style="margin: 0 0 0.5rem;">📧 <a href="mailto:admin@getrichwithparadisedolls.com" style="color: #b94f72;">admin@getrichwithparadisedolls.com</a> – {{ __('Speak with one of our friendly advisors.') }}</p>
    <p style="margin: 0 0 1.5rem;">📧 <a href="mailto:bossdoll@getrichwithparadisedolls.com" style="color: #b94f72;">bossdoll@getrichwithparadisedolls.com</a> – {{ __('Speak directly with Kayla.') }}</p>
    <p style="margin: 0 0 1rem;">{{ __('Welcome aboard, Doll! 💖 We’re so excited to have you as part of the team and can’t wait to see everything you’ll achieve.') }}</p>
    <p style="margin: 0;">{{ __('With love,') }}<br><strong>{{ __('Kayla & The Paradise Dolls Team 💕👑') }}</strong></p>
</body>
</html>
