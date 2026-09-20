@php
    $keywords = [
        'webcam jobs',
        'webcam model jobs',
        'online model jobs',
        'work from home webcam jobs',
        'remote webcam jobs',
        'adult webcam jobs',
        'Paradise Dolls webcam jobs',
    ];
@endphp

<x-marketing-seo-page
    title="Webcam Jobs"
    description="Apply for webcam jobs with Paradise Dolls. Beginner-friendly online model jobs with private onboarding, training, multi-platform support, and flexible remote work."
    :keywords="$keywords"
    eyebrow="Webcam Jobs"
    headline="Webcam jobs with training, structure, and support"
    intro="Paradise Dolls helps women apply for online webcam model jobs with professional onboarding, safety guidance, platform education, and a private training path before going live."
    heroImage="{{ asset('images/2.jpeg') }}"
    primaryKeyword="Webcam jobs"
    :secondaryKeywords="['Webcam model jobs', 'Online model jobs', 'Work from home webcam jobs', 'Remote webcam jobs']"
    :sections="[
        [
            'eyebrow' => 'Beginner friendly',
            'title' => 'No experience required',
            'body' => 'Approved applicants are guided through account preparation, verification, privacy basics, streaming setup, and the Boss Doll Blueprint before they begin working online.',
        ],
        [
            'eyebrow' => 'Flexible work',
            'title' => 'Work from home or abroad',
            'body' => 'The work is online and schedule-led, so models can build income around their life while still following professional standards and platform rules.',
        ],
        [
            'eyebrow' => 'Training system',
            'title' => 'Learn the platforms properly',
            'body' => 'Paradise Dolls teaches platform navigation, multistreaming, customer communication, profile setup, equipment basics, and earning tools in a structured way.',
        ],
    ]"
    :faq="[
        [
            'question' => 'Are Paradise Dolls webcam jobs suitable for beginners?',
            'answer' => 'Yes. The agency is designed for beginners as well as experienced models. The important requirements are age eligibility, verification, consistency, professionalism, and willingness to learn.',
        ],
        [
            'question' => 'Can webcam models work remotely?',
            'answer' => 'Yes. Paradise Dolls supports remote work, including work-from-home setups, as long as the applicant can meet privacy, safety, platform, and technical requirements.',
        ],
        [
            'question' => 'How do I apply for webcam jobs with Paradise Dolls?',
            'answer' => 'Use the application form and the team will privately review the details. Approved applicants receive next-step guidance for onboarding, setup, and training.',
        ],
    ]"
/>
