@php
    $keywords = [
        'webcam agency',
        'webcam model agency',
        'adult webcam agency',
        'streaming agency',
        'cam model agency',
        'Paradise Dolls webcam agency',
    ];
@endphp

<x-marketing-seo-page
    title="Webcam Agency"
    description="Paradise Dolls is a webcam agency offering private onboarding, model training, multistreaming education, platform setup guidance, and ongoing support."
    :keywords="$keywords"
    eyebrow="Webcam Agency"
    headline="A webcam agency built around training and support"
    intro="Paradise Dolls is a webcam agency for women who want more than a sign-up link. The team focuses on onboarding, education, account setup guidance, safety standards, and a supportive community."
    heroImage="{{ asset('images/17.jpeg') }}"
    primaryKeyword="Webcam agency"
    :secondaryKeywords="['Webcam model agency', 'Streaming agency', 'Cam model agency', 'Adult webcam agency']"
    :sections="[
        [
            'eyebrow' => 'Agency support',
            'title' => 'More structure than a basic referral',
            'body' => 'The agency helps approved models understand setup, profile preparation, platform rules, streaming tools, and the habits that support consistent online work.',
        ],
        [
            'eyebrow' => 'Education',
            'title' => 'Built around the Boss Doll Blueprint',
            'body' => 'Members receive training content designed to explain multistreaming, platform controls, customer interaction, earning systems, safety, and professionalism.',
        ],
        [
            'eyebrow' => 'Community',
            'title' => 'A more supportive agency experience',
            'body' => 'Paradise Dolls is positioned as a feminine, girls-girl agency environment where applicants can learn privately and grow with guidance.',
        ],
    ]"
    :faq="[
        [
            'question' => 'What does a webcam agency do?',
            'answer' => 'A webcam agency can help with onboarding, account setup guidance, platform education, training, strategy, support, and professional standards. Paradise Dolls focuses on support before and after a model goes live.',
        ],
        [
            'question' => 'Does Paradise Dolls support multiple webcam platforms?',
            'answer' => 'Yes. Multistreaming education is a core part of the system, helping models understand how visibility and earning tools differ across platforms.',
        ],
        [
            'question' => 'Is Paradise Dolls only for experienced webcam models?',
            'answer' => 'No. The agency is beginner-friendly and reviews applications privately. Experience can help, but training and consistency matter more.',
        ],
    ]"
/>
