@php
    $heroImage = asset('images/17.jpeg');

    $sections = [
        [
            'id' => 'privacy-reality',
            'eyebrow' => 'Start with the truth',
            'title' => 'Privacy can be strengthened, but anonymity cannot be guaranteed',
            'intro' => 'Location blocks, stage names, separate accounts, and careful framing can reduce exposure. They cannot guarantee that a viewer will never record, recognise, or redistribute public content.',
            'paragraphs' => [
                'Make the decision using the realistic possibility that someone you know could eventually see your public work. If that risk is unacceptable, webcam modelling may not be the right option.',
                'A strong safety plan uses several layers at once: limited personal information, secure accounts, a controlled background, clear boundaries, platform moderation tools, and a response plan for harassment or leaks.',
            ],
            'note_title' => 'No false promises',
            'note' => 'No agency, platform, or technical setting can promise complete anonymity or permanent removal of copied content from the internet.',
        ],
        [
            'id' => 'identity-separation',
            'eyebrow' => 'Separate work from personal life',
            'title' => 'Create a distinct creator identity',
            'intro' => 'The fewer links between creator accounts and personal accounts, the harder it is for strangers to connect your work to your private life.',
            'items' => [
                ['title' => 'Use a stage name', 'body' => 'Do not reuse personal usernames, nicknames, profile photographs, or biographies that already appear elsewhere online.'],
                ['title' => 'Create separate contact details', 'body' => 'Use dedicated email, phone, social, and payment contact information where legally and operationally possible.'],
                ['title' => 'Remove metadata and clues', 'body' => 'Check images, reflections, landmarks, accents, schedules, mail, certificates, and conversation details that may reveal identity or location.'],
                ['title' => 'Search your own footprint', 'body' => 'Review what a stranger can find from your creator name, images, and public profile before publishing more information.'],
            ],
        ],
        [
            'id' => 'account-security',
            'eyebrow' => 'Protect every login',
            'title' => 'Secure platform, email, payment, and social accounts',
            'intro' => 'The email account connected to your creator work is often the key to everything else, so protect it as carefully as the platform account itself.',
            'items' => [
                'Use a password manager and a different strong password for every service.',
                'Enable two-factor authentication with an authenticator app or security key where available.',
                'Never send passwords, one-time codes, recovery keys, or full identity documents through casual messages.',
                'Review active sessions and connected apps regularly, especially after using a shared device.',
                'Verify support messages inside the official platform before opening links or sharing information.',
            ],
        ],
        [
            'id' => 'live-boundaries',
            'eyebrow' => 'Safety while live',
            'title' => 'Set boundaries before viewers test them',
            'intro' => 'Clear limits are easier to enforce when they are decided in advance and supported by moderation tools.',
            'items' => [
                ['title' => 'Define non-negotiables', 'body' => 'Write down prohibited topics, requests, off-platform contact, meetings, and personal questions before your first session.'],
                ['title' => 'Use moderation immediately', 'body' => 'Block, mute, and report users who threaten, harass, manipulate, or repeatedly ignore a stated limit.'],
                ['title' => 'Keep payments on approved systems', 'body' => 'Off-platform payment requests can create fraud, privacy, and account-compliance risks.'],
                ['title' => 'Have an emergency stop', 'body' => 'Know how to end the stream, cover the camera, disconnect audio, save evidence, and contact support quickly.'],
            ],
        ],
        [
            'id' => 'incident-response',
            'eyebrow' => 'If something goes wrong',
            'title' => 'Preserve evidence and escalate the right way',
            'intro' => 'A calm documented response is more useful than negotiating with an abusive viewer or paying someone who claims they can remove content.',
            'items' => [
                'Capture usernames, URLs, dates, messages, screenshots, and platform report references.',
                'Report the user or content through the relevant platform and notify agency support if you have it.',
                'Change compromised credentials, revoke active sessions, and secure the connected email account.',
                'Contact local law enforcement or a qualified legal professional for credible threats, stalking, blackmail, or unlawful distribution.',
            ],
        ],
    ];

    $faqs = [
        ['question' => 'Is webcam modelling safe?', 'answer' => 'It has real privacy, harassment, fraud, recording, and digital-footprint risks. Those risks can be reduced with secure accounts, controlled personal information, clear boundaries, moderation, and trustworthy support, but they cannot be eliminated.'],
        ['question' => 'Can webcam models work without showing their face?', 'answer' => 'Some platforms or formats may permit limited face visibility, but identity verification is still required and anonymity is never guaranteed. Models should check platform rules and understand that restricted visibility may affect audience growth.'],
        ['question' => 'Do location blocks guarantee privacy?', 'answer' => 'No. Geo-blocking can reduce access from selected areas but may be bypassed and cannot prevent recording or recognition. Use it as one layer, not as a promise of anonymity.'],
        ['question' => 'What should I do if someone threatens to expose me?', 'answer' => 'Do not pay or negotiate. Preserve evidence, block and report the account, secure your logins, notify trusted support, and contact local police or qualified legal help when the threat is credible.'],
        ['question' => 'Should webcam models use personal social media?', 'answer' => 'Using separate creator accounts is safer. Reusing personal names, images, usernames, contacts, or follower networks can make identity matching much easier.'],
    ];
@endphp

<x-marketing-guide-page
    route-name="webcam-model-safety-privacy"
    title="Webcam Model Safety and Privacy"
    description="Practical webcam model safety and privacy guidance covering anonymity limits, separate identities, secure accounts, home setup, boundaries, harassment, and incident response."
    :keywords="['is webcam modelling safe', 'webcam agency privacy', 'webcam model privacy', 'can you webcam model without showing your face']"
    eyebrow="Safety and privacy guide"
    heading="Webcam model safety and privacy without false promises"
    lead="Learn how to reduce identity, account, location, harassment, and content risks while understanding the most important truth: no public online work can guarantee complete anonymity."
    :hero-image="$heroImage"
    hero-alt="Women reviewing a private creator setup with cameras, lighting, and a laptop"
    answer="Webcam modelling has real digital and personal risks. Strong privacy layers, secure accounts, controlled information, clear boundaries, moderation tools, and a documented incident plan can reduce them, but not remove them entirely."
    :facts="[
        ['label' => 'Anonymity', 'value' => 'Never guaranteed'],
        ['label' => 'Best protection', 'value' => 'Several privacy layers'],
        ['label' => 'Account standard', 'value' => 'Unique passwords + 2FA'],
        ['label' => 'When threatened', 'value' => 'Document and report'],
    ]"
    :sections="$sections"
    :faqs="$faqs"
    :related="[
        ['route' => 'how-to-become-a-webcam-model', 'label' => 'How to become a webcam model', 'detail' => 'Include privacy decisions in the beginner process'],
        ['route' => 'work-from-home', 'label' => 'Work-from-home webcam jobs', 'detail' => 'Prepare a private room and sustainable remote routine'],
        ['route' => 'webcam-agency-vs-independent', 'label' => 'Webcam agency vs independent', 'detail' => 'Compare the safety support and responsibilities of each route'],
    ]"
/>
