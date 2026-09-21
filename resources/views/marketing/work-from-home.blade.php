@php
    $heroImage = asset('images/2.jpeg');

    $sections = [
        [
            'id' => 'what-remote-work-means',
            'eyebrow' => 'Remote work explained',
            'title' => 'What work-from-home webcam modelling actually involves',
            'intro' => 'A remote webcam model runs live sessions from a private space, communicates with viewers, manages an online profile, and follows the rules of each platform she uses.',
            'paragraphs' => [
                'It offers location flexibility, but it is still structured work. A reliable routine, preparation, audience communication, account security, and consistent boundaries matter more than simply switching on a camera.',
                'Paradise Dolls supports approved models with onboarding, platform education, professional training, and ongoing guidance. Models work on a self-employed basis and remain responsible for their own legal and tax obligations.',
            ],
            'items' => [
                ['title' => 'Private workspace', 'body' => 'Use a room where interruptions, identifying documents, family photographs, and location clues can be kept out of view.'],
                ['title' => 'Reliable connection', 'body' => 'Stable upload speed and a wired connection where possible reduce dropped streams and protect the viewer experience.'],
                ['title' => 'Professional routine', 'body' => 'Allow time for preparation, live work, breaks, messages, records, and a clear finish to the working day.'],
                ['title' => 'Personal boundaries', 'body' => 'Decide what you will and will not do before going live, and use platform tools to enforce those limits.'],
            ],
        ],
        [
            'id' => 'home-setup',
            'eyebrow' => 'Workspace essentials',
            'title' => 'Build a setup that is private, stable, and comfortable',
            'intro' => 'Beginners do not need the most expensive equipment on day one, but the basics must be dependable and suitable for live video.',
            'items' => [
                ['title' => 'Computer and camera', 'body' => 'Use equipment that can handle the chosen streaming software and produce a clear, stable picture.'],
                ['title' => 'Lighting and sound', 'body' => 'Soft front lighting and clean audio usually improve a stream more than decorative equipment.'],
                ['title' => 'Internet and backup plan', 'body' => 'Test upload speed before each session and know what you will do if the connection or platform fails.'],
                ['title' => 'Comfort and ergonomics', 'body' => 'Choose seating, camera height, ventilation, water, and scheduled breaks that make longer sessions sustainable.'],
            ],
            'note_title' => 'Start with privacy',
            'note' => 'Before testing camera quality, inspect the full frame for windows, mail, school or workplace items, photographs, reflections, and anything else that could reveal personal information.',
        ],
        [
            'id' => 'schedule-and-safety',
            'eyebrow' => 'Flexible, not unstructured',
            'title' => 'Plan working hours without giving up your safety',
            'intro' => 'Working from home can make scheduling easier, but healthy boundaries protect both performance and wellbeing.',
            'items' => [
                'Use separate creator contact details and strong, unique passwords with two-factor authentication.',
                'Tell trusted people when you are working without sharing public details about your exact schedule or address.',
                'Keep platform conversations and payments inside approved systems unless trained otherwise.',
                'Schedule breaks, meals, sleep, and days away from live work instead of treating every free hour as working time.',
                'Block and report harassment, threats, doxxing attempts, or pressure to cross a stated boundary.',
            ],
        ],
        [
            'id' => 'before-applying',
            'eyebrow' => 'Before you apply',
            'title' => 'Check whether remote webcam work fits your circumstances',
            'intro' => 'The opportunity may suit women who value independence and can work consistently, but it is adult creator work and should be considered carefully.',
            'items' => [
                ['title' => 'You must be 18 or older', 'body' => 'Age and identity verification are mandatory. Eligibility also depends on your country and the platforms available there.'],
                ['title' => 'You need genuine privacy', 'body' => 'A shared home or unsafe environment may make live work unsuitable until a secure arrangement is available.'],
                ['title' => 'Income varies', 'body' => 'Hours online do not guarantee a fixed result. Performance depends on consistency, engagement, platform conditions, and many other factors.'],
                ['title' => 'The work is self-employed', 'body' => 'You must understand your local tax, legal, record-keeping, and financial responsibilities.'],
            ],
        ],
    ];

    $faqs = [
        ['question' => 'Can webcam models work from home?', 'answer' => 'Yes. Many webcam models work remotely, provided they have a private and safe room, reliable internet, suitable equipment, and legal access to the platforms they plan to use.'],
        ['question' => 'Do I need experience for a work-from-home webcam job?', 'answer' => 'No previous webcam experience is required to apply to Paradise Dolls. Approved beginners complete onboarding and training before they begin working.'],
        ['question' => 'Can I choose my own hours?', 'answer' => 'Remote work offers flexibility, but consistent planned sessions usually create a more professional routine than logging on only occasionally. Any agency expectations should be understood before accepting terms.'],
        ['question' => 'Can I do webcam modelling without showing my home?', 'answer' => 'You can control the camera frame and use a neutral background, but no setup can promise complete anonymity. Remove location clues and use privacy and blocking tools carefully.'],
        ['question' => 'Is work-from-home webcam modelling guaranteed income?', 'answer' => 'No. Earnings vary and are never guaranteed. Results depend on factors including time, consistency, viewer engagement, platform performance, and market conditions.'],
    ];
@endphp

<x-marketing-guide-page
    route-name="work-from-home"
    title="Work From Home Webcam Jobs"
    description="Learn how work-from-home webcam jobs operate, including private setup, equipment, safety, scheduling, self-employed responsibilities, and support for beginners."
    :keywords="['webcam jobs from home', 'work from home webcam jobs', 'remote webcam model jobs', 'online model jobs for women']"
    eyebrow="Remote webcam work for women 18+"
    heading="Work from home webcam jobs, explained honestly"
    lead="Learn what remote webcam modelling requires, how to prepare a private home setup, and which practical, safety, and self-employed responsibilities to understand before applying."
    :hero-image="$heroImage"
    hero-alt="Woman preparing a private home workspace with a laptop, camera, and ring light"
    answer="Webcam modelling can be done from home, but it needs a private workspace, reliable equipment, strong boundaries, platform compliance, and a consistent professional routine."
    :facts="[
        ['label' => 'Minimum age', 'value' => '18+ only'],
        ['label' => 'Work location', 'value' => 'Private remote setup'],
        ['label' => 'Experience', 'value' => 'Beginners may apply'],
        ['label' => 'Work status', 'value' => 'Self-employed'],
    ]"
    :sections="$sections"
    :faqs="$faqs"
    :related="[
        ['route' => 'how-to-become-a-webcam-model', 'label' => 'How to become a webcam model', 'detail' => 'A practical beginner path from research to a first live session'],
        ['route' => 'webcam-model-safety-privacy', 'label' => 'Webcam model safety and privacy', 'detail' => 'Protect your identity, accounts, location, and personal boundaries'],
        ['route' => 'multistreaming', 'label' => 'Multistreaming for webcam models', 'detail' => 'Understand the technology before adding more than one platform'],
    ]"
/>
