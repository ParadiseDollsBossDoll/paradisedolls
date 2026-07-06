<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: ui-sans-serif, system-ui, sans-serif; line-height: 1.65; color: #1a1a1a; max-width: 36rem; margin: 0 auto; padding: 1.5rem;">
    <p style="margin: 0 0 1rem;"><strong>{{ __('Verification received, :name Doll! 💖✨', ['name' => $profile->user->name]) }}</strong></p>
    <p style="margin: 0 0 1rem;">{{ __('Congratulations! 🎉 We’ve successfully received your verification documents. The Paradise Dolls team will now review them and let you know as soon as your account is fully approved.') }}</p>
    <p style="margin: 0 0 1rem;">{{ __('Your onboarding information is complete, and one of our team members will be in touch to guide you through the final steps, introduce you to your training, and help you get everything set up for a successful start.') }}</p>
    <p style="margin: 0 0 1rem;">{{ __('This is where your Paradise Dolls journey truly begins, and we can’t wait to support you every step of the way.') }}</p>
    <p style="margin: 0 0 0.65rem;">{{ __('If you need any assistance in the meantime, please contact us:') }}</p>
    <p style="margin: 0 0 0.5rem;">📧 <a href="mailto:admin@getrichwithparadisedolls.com" style="color: #b94f72;">admin@getrichwithparadisedolls.com</a> – {{ __('Speak with one of our friendly advisors.') }}</p>
    <p style="margin: 0 0 1.5rem;">📧 <a href="mailto:bossdoll@getrichwithparadisedolls.com" style="color: #b94f72;">bossdoll@getrichwithparadisedolls.com</a> – {{ __('Speak directly with Kayla.') }}</p>
    <p style="margin: 0 0 1rem;">{{ __('Welcome aboard, Doll! 💖 We’re so excited to have you as part of the team and can’t wait to see everything you’ll achieve.') }}</p>
    <p style="margin: 0;">{{ __('With love,') }}<br><strong>{{ __('The Paradise Dolls Team 💕') }}</strong></p>
</body>
</html>
