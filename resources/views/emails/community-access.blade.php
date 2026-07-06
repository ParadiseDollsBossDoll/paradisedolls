<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: ui-sans-serif, system-ui, sans-serif; line-height: 1.65; color: #1a1a1a; max-width: 36rem; margin: 0 auto; padding: 1.5rem;">
    <p style="margin: 0 0 1rem;"><strong>{{ __('Welcome to the Paradise Dolls Community, :name! 🎉', ['name' => $profile->user->name]) }}</strong></p>
    <p style="margin: 0 0 1rem;">{{ __('We’re so excited to officially welcome you to our growing community of ambitious, supportive women. This is where your journey truly begins, and we’re here to support you every step of the way.') }}</p>
    <p style="margin: 0 0 1.5rem;">{{ __('To make sure you have everything you need, we’ve created two exclusive communities for all of our members.') }}</p>

    <p style="margin: 0 0 0.5rem;"><strong>{{ __('💎 Join Our Discord Community') }}</strong></p>
    <p style="margin: 0 0 0.75rem;">{{ __('Our Discord Community is your dedicated support and training hub. Inside you’ll have access to:') }}</p>
    <p style="margin: 0 0 1rem;">{{ __('✨ 24/7 support from our team') }}<br>{{ __('🎓 The Boss Doll Blueprint and all of your training resources') }}<br>{{ __('💬 Live chatter support and onboarding assistance') }}<br>{{ __('📚 Helpful guides, updates, and announcements') }}<br>{{ __('👑 Direct access to our trainers, chatters, and support staff whenever you need help') }}</p>
    <p style="margin: 0 0 0.5rem;">{{ __('The team will assign your :role role after you join.', ['role' => $roleName]) }}</p>
    <p style="margin: 0 0 1rem;">
        <a href="{{ $communityUrl }}" style="display: inline-block; background-color: #EEB4C3; color: #181318; text-decoration: none; padding: 0.75rem 1.35rem; border-radius: 0.35rem; font-weight: 700;">{{ __('Join Our Discord Community') }}</a>
    </p>
    <p style="margin: 0 0 1.25rem; font-size: 0.875rem; color: #717182;"><a href="{{ $communityUrl }}" style="color: #b94f72;">{{ $communityUrl }}</a></p>
    <p style="margin: 0 0 1.5rem;">{{ __('Once you’ve joined, send us a quick message to let us know you’ve arrived. A team member will assign you to your dedicated training room and introduce you to your support staff.') }}</p>

    <p style="margin: 0 0 0.5rem;"><strong>{{ __('💞 Join Our WhatsApp Community') }}</strong></p>
    <p style="margin: 0 0 0.75rem;">{{ __('Our WhatsApp Group Chat is where the Dolls come together. It’s a friendly, women-only space where you can:') }}</p>
    <p style="margin: 0 0 1rem;">{{ __('💖 Meet the other Dolls and make new friendships') }}<br>{{ __('🥂 Celebrate your wins and milestones together') }}<br>{{ __('💬 Ask questions, share experiences, and support one another') }}<br>{{ __('✨ Stay motivated, inspired, and connected with the community every day') }}</p>
    <p style="margin: 0 0 1rem;">
        <a href="{{ $whatsappCommunityUrl }}" style="display: inline-block; background-color: #EEB4C3; color: #181318; text-decoration: none; padding: 0.75rem 1.35rem; border-radius: 0.35rem; font-weight: 700;">{{ __('Join Our WhatsApp Community') }}</a>
    </p>
    <p style="margin: 0 0 1.25rem; font-size: 0.875rem; color: #717182;"><a href="{{ $whatsappCommunityUrl }}" style="color: #b94f72;">{{ $whatsappCommunityUrl }}</a></p>
    <p style="margin: 0 0 1.5rem;">{{ __('When you join, please introduce yourself with a friendly message in the chat saying hello, telling everyone a little about yourself, and sharing something interesting about you. It’s the perfect way for the other Dolls to get to know you and give you a warm Paradise Dolls welcome. 💗') }}</p>

    <p style="margin: 0 0 1rem;">{{ __('At Paradise Dolls, we believe success is even more rewarding when it’s shared. This is more than just a community—it’s a sisterhood of women who encourage one another, celebrate each other’s achievements, and grow together.') }}</p>
    <p style="margin: 0 0 1rem;">{{ __('Here’s to new friendships, exciting opportunities, and achieving incredible things together.') }}</p>
    <p style="margin: 0 0 1rem;">{{ __('Welcome to the Dolls. 💖✨') }}</p>
    <p style="margin: 0;">{{ __('With love,') }}<br><strong>{{ __('Kayla & The Paradise Dolls Team 💕👑') }}</strong></p>
</body>
</html>
