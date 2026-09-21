@php
    $heroImage = asset('images/1.jpeg');

    $sections = [
        [
            'id' => 'understand-the-work',
            'eyebrow' => 'Step one',
            'title' => 'Understand the work before creating accounts',
            'intro' => 'Webcam modelling is adult live-streaming work. It combines on-camera presentation, conversation, audience building, platform tools, safety decisions, and self-employed business responsibilities.',
            'paragraphs' => [
                'The work can be done remotely and beginners can learn it, but it should not be treated as instant money. Income is variable, content may be copied without permission, and public exposure cannot be completely eliminated.',
                'Start by deciding whether the adult nature of the work, the possibility of being recognised, and the need to enforce personal boundaries are acceptable to you.',
            ],
            'items' => [
                'Be at least 18 years old and able to complete identity and age verification.',
                'Confirm that adult creator work and the intended platforms are legal and accessible where you live.',
                'Consider how the work could affect employment, relationships, housing, family, and your digital footprint.',
                'Understand that models are self-employed and responsible for local tax, legal, and record-keeping duties.',
            ],
        ],
        [
            'id' => 'choose-support',
            'eyebrow' => 'Step two',
            'title' => 'Choose between independent work and agency support',
            'intro' => 'Independent models keep direct control of setup and decision-making. A responsible agency can add onboarding, training, technical guidance, account support, and a team to ask when problems arise.',
            'items' => [
                ['title' => 'Read every agreement', 'body' => 'Check the term, fees, exit conditions, account ownership, content rights, payout process, and what support is actually included.'],
                ['title' => 'Verify the support', 'body' => 'Ask who provides training, how problems are escalated, and whether help continues after the first setup.'],
                ['title' => 'Reject pressure', 'body' => 'A legitimate agency should not rush identity documents, hide fees, promise guaranteed income, or push you beyond stated boundaries.'],
                ['title' => 'Keep copies', 'body' => 'Save agreements, payout statements, platform messages, and any important agency instructions.'],
            ],
            'note_title' => 'Paradise Dolls applications',
            'note' => 'Paradise Dolls reviews applications privately. Acceptance is not automatic, and approved applicants should read the complete agency and training terms before agreeing to join.',
        ],
        [
            'id' => 'prepare-your-setup',
            'eyebrow' => 'Step three',
            'title' => 'Prepare a private workspace and a reliable basic setup',
            'intro' => 'A beginner setup should prioritise privacy, connection stability, clear sound, and usable lighting before decorative upgrades.',
            'items' => [
                ['title' => 'Private room', 'body' => 'Remove names, mail, photographs, windows, reflections, and background details that could identify your location.'],
                ['title' => 'Reliable equipment', 'body' => 'Use a suitable computer, camera, microphone, lighting, and stable internet connection that meet the platform requirements.'],
                ['title' => 'Separate accounts', 'body' => 'Create dedicated creator email, contact, payment, and social accounts instead of mixing work with personal profiles.'],
                ['title' => 'Written boundaries', 'body' => 'Decide your limits, blocked locations, schedule, and emergency stop procedure before the first live session.'],
            ],
        ],
        [
            'id' => 'onboarding',
            'eyebrow' => 'Step four',
            'title' => 'Complete verification and learn the platform controls',
            'intro' => 'Platforms require identity and age checks. Once approved, learn the interface in a controlled order instead of trying every earning feature immediately.',
            'items' => [
                'Confirm profile details, payment information, privacy settings, location blocking, and account security.',
                'Learn how to start and end a session, mute, block, report, moderate chat, and handle private-session requests.',
                'Understand the platform earning unit, fees, payout minimum, payment schedule, and prohibited activity.',
                'Run private technical tests for camera framing, audio, lighting, connection stability, and notifications.',
            ],
        ],
        [
            'id' => 'first-sessions',
            'eyebrow' => 'Step five',
            'title' => 'Treat the first sessions as structured learning',
            'intro' => 'Early sessions are where a model learns pacing, communication, technical habits, and which boundaries need clearer enforcement.',
            'items' => [
                ['title' => 'Set a realistic schedule', 'body' => 'Start with planned sessions and breaks that you can repeat without exhausting yourself.'],
                ['title' => 'Review, do not panic', 'body' => 'Record useful metrics and lessons after each stream instead of judging the entire opportunity from one result.'],
                ['title' => 'Protect consistency', 'body' => 'A stable routine, clear profile, and reliable viewer experience usually matter more than constant changes.'],
                ['title' => 'Ask for help early', 'body' => 'Raise technical, safety, platform, or payment concerns before they become larger problems.'],
            ],
        ],
    ];

    $faqs = [
        ['question' => 'How do I become a webcam model?', 'answer' => 'Confirm that you are 18 or older and legally eligible, understand the adult nature and risks of the work, choose whether to work independently or with an agency, prepare a private setup, complete platform verification, and learn the controls before going live.'],
        ['question' => 'Do you need experience to be a webcam model?', 'answer' => 'No. Beginners can learn the work, but they should receive or seek reliable education about safety, platform tools, boundaries, customer communication, earnings, and self-employment.'],
        ['question' => 'What equipment does a beginner webcam model need?', 'answer' => 'At minimum, use a suitable computer or supported device, a clear camera, usable microphone, stable internet, front lighting, and a private background. Exact requirements depend on the platform and streaming method.'],
        ['question' => 'Can I become a webcam model without showing my face?', 'answer' => 'Some platforms or working styles may allow limited face visibility, but identity verification is still required and no method guarantees anonymity. Platform rules and commercial limitations must be checked first.'],
        ['question' => 'How long does it take to start webcam modelling?', 'answer' => 'Timing varies according to application review, identity verification, equipment readiness, platform approval, and training. A careful setup is more important than going live as quickly as possible.'],
    ];
@endphp

<x-marketing-guide-page
    route-name="how-to-become-a-webcam-model"
    title="How to Become a Webcam Model"
    description="A candid beginner guide to becoming a webcam model: eligibility, safety, agency choices, equipment, verification, training, and preparing for a first live session."
    :keywords="['how to become a webcam model', 'webcam modelling for beginners', 'do you need experience to be a webcam model', 'become a webcam model']"
    eyebrow="Beginner webcam model guide"
    heading="How to become a webcam model, step by step"
    lead="A practical path for adult women considering webcam modelling, from eligibility and privacy decisions to choosing support, preparing equipment, completing verification, and learning before going live."
    :hero-image="$heroImage"
    hero-alt="Woman planning her first webcam model setup with a laptop, camera, notebook, and ring light"
    answer="Start by understanding the adult work and its risks, confirm legal and platform eligibility, choose trustworthy support, build a private setup, complete verification, and learn the controls before your first live session."
    :facts="[
        ['label' => 'Minimum age', 'value' => '18+ only'],
        ['label' => 'Experience', 'value' => 'Not always required'],
        ['label' => 'First priority', 'value' => 'Safety and privacy'],
        ['label' => 'Income', 'value' => 'Variable, not guaranteed'],
    ]"
    :sections="$sections"
    :faqs="$faqs"
    :related="[
        ['route' => 'webcam-model-safety-privacy', 'label' => 'Webcam model safety and privacy', 'detail' => 'Build privacy layers before creating public accounts'],
        ['route' => 'webcam-agency-vs-independent', 'label' => 'Webcam agency vs independent', 'detail' => 'Compare support, control, fees, contracts, and responsibilities'],
        ['route' => 'work-from-home', 'label' => 'Work-from-home webcam jobs', 'detail' => 'Plan a private workspace and sustainable remote routine'],
    ]"
/>
