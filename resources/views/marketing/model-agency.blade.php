@php
    $keywords = [
        'model agency',
        'online model agency',
        'webcam model agency',
        'adult model agency',
        'remote model agency',
        'Paradise Dolls model agency',
    ];
@endphp

<x-marketing-seo-page
    title="Model Agency"
    description="Paradise Dolls is an online model agency for women seeking remote income, webcam model training, platform education, onboarding support, and community."
    :keywords="$keywords"
    eyebrow="Model Agency"
    headline="An online model agency for ambitious women"
    intro="Paradise Dolls supports women who want to become online models with structured onboarding, platform education, privacy guidance, multistreaming training, and a feminine community."
    heroImage="{{ asset('images/18.jpeg') }}"
    primaryKeyword="Model agency"
    :secondaryKeywords="['Online model agency', 'Webcam model agency', 'Adult model agency', 'Remote model agency']"
    :sections="[
        [
            'eyebrow' => 'Online modelling',
            'title' => 'Built for digital platforms',
            'body' => 'Paradise Dolls focuses on online modelling, streaming, platform education, and remote income systems rather than traditional walk-in casting.',
        ],
        [
            'eyebrow' => 'Professional guidance',
            'title' => 'Private onboarding and preparation',
            'body' => 'Approved applicants receive guidance around verification, profile preparation, account setup, presentation, privacy, and professional conduct.',
        ],
        [
            'eyebrow' => 'Growth',
            'title' => 'Training for confidence and consistency',
            'body' => 'The agency teaches systems that help models understand how platforms work, how to build better habits, and how to approach online work professionally.',
        ],
    ]"
    :faq="[
        [
            'question' => 'Is Paradise Dolls a traditional model agency?',
            'answer' => 'Paradise Dolls is focused on online modelling, webcam platform education, remote work, and digital income training rather than traditional fashion casting.',
        ],
        [
            'question' => 'Can beginners apply to this model agency?',
            'answer' => 'Yes. Beginners can apply and, if approved, receive onboarding and training before working online.',
        ],
        [
            'question' => 'What makes Paradise Dolls different from a basic model agency?',
            'answer' => 'The focus is on education, support, multistreaming, privacy, and a structured member area so models are not left to figure everything out alone.',
        ],
    ]"
/>
