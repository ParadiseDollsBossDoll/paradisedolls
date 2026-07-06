<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: ui-sans-serif, system-ui, sans-serif; line-height: 1.65; color: #1a1a1a; max-width: 36rem; margin: 0 auto; padding: 1.5rem;">
    <p style="margin: 0 0 1rem;">{{ __('Hi Doll, 💖✨') }}</p>
    <p style="margin: 0 0 1rem;"><strong>{{ __('Amazing news… your Paradise Dolls application has been approved! 🎉') }}</strong></p>
    <p style="margin: 0 0 1rem;">{{ __('We’re so excited to welcome you to the next stage of your journey. The next step is to log in and complete your Model Onboarding Information Form, which helps our onboarding team prepare your account and create a personalised plan just for you.') }}</p>
    <p style="margin: 0 0 1rem;">{{ __('Please log in using the email address you applied with and the temporary password below:') }}</p>
    <p style="margin: 0 0 0.35rem;"><strong>{{ __('Temporary Password:') }}</strong></p>
    <p style="margin: 0 0 1.5rem; font-size: 1.125rem; font-weight: 700; letter-spacing: 0.05em; font-family: ui-monospace, monospace;">{{ $temporaryPassword }}</p>
    <p style="margin: 0 0 1rem;">{{ __('🔐 For your security, please change your password once you’ve logged in by visiting your Profile page.') }}</p>
    <p style="margin: 0 0 1.5rem;">{{ __('Once you’ve completed your onboarding form, our team will review your information and guide you through the next stage of the onboarding process.') }}</p>
    <p style="margin: 0 0 1.5rem;">
        <a href="{{ $onboardingUrl }}" style="display: inline-block; background-color: #EEB4C3; color: #181318; text-decoration: none; padding: 0.75rem 1.35rem; border-radius: 0.35rem; font-weight: 700;">{{ __('Complete Model Information Form') }}</a>
    </p>
    <p style="margin: 0 0 1rem; font-size: 0.875rem; color: #717182;">{{ __('Login link:') }} <a href="{{ $loginUrl }}" style="color: #b94f72;">{{ $loginUrl }}</a></p>
    <p style="margin: 0 0 1rem;">{{ __('Please note: This is a no-reply email address, so please do not reply to this message.') }}</p>
    <p style="margin: 0 0 0.65rem;">{{ __('If you need any assistance, please contact us:') }}</p>
    <p style="margin: 0 0 0.5rem;">📧 <a href="mailto:admin@getrichwithparadisedolls.com" style="color: #b94f72;">admin@getrichwithparadisedolls.com</a> – {{ __('Speak with one of our friendly advisors.') }}</p>
    <p style="margin: 0 0 1.5rem;">📧 <a href="mailto:bossdoll@getrichwithparadisedolls.com" style="color: #b94f72;">bossdoll@getrichwithparadisedolls.com</a> – {{ __('Speak directly with Kayla.') }}</p>
    <p style="margin: 0 0 1rem;">{{ __('We’re so excited to have you join the Paradise Dolls family and can’t wait to help you begin your journey. 🩷✨') }}</p>
    <p style="margin: 0;">{{ __('With love,') }}<br><strong>{{ __('The Paradise Dolls Team 💕') }}</strong></p>
</body>
</html>
