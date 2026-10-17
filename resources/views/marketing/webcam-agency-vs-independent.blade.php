@php
    $heroImage = asset('images/19.jpeg');

    $sections = [
        [
            'id' => 'core-difference',
            'eyebrow' => 'The core difference',
            'title' => 'Support and structure versus direct control',
            'intro' => 'Independent models manage platform setup, training, strategy, technical problems, safety, records, and decisions themselves. Agency models receive defined services in exchange for agreed fees, commitments, or working terms.',
            'paragraphs' => [
                'Neither route is automatically better for everyone. The right choice depends on experience, confidence, available time, technical knowledge, need for support, and the quality of the specific agency agreement.',
                'Compare real responsibilities and written terms, not social-media claims. A professional agency should make both the benefits and the model\'s obligations clear before an agreement is accepted.',
            ],
        ],
        [
            'id' => 'comparison',
            'eyebrow' => 'Compare the routes',
            'title' => 'What changes when you work with an agency',
            'intro' => 'The useful question is not simply whether an agency charges a fee. It is whether the documented service, accountability, and support are worth the complete cost and conditions.',
            'items' => [
                ['title' => 'Setup and verification', 'body' => 'Independent models research and complete everything alone. An agency may guide applications, profiles, platform checks, and technical preparation.'],
                ['title' => 'Training and strategy', 'body' => 'Independent models source their own education. An agency may provide structured learning, feedback, platform guidance, and professional standards.'],
                ['title' => 'Problem support', 'body' => 'Independent models contact platforms directly. An agency may provide an escalation path for account, safety, payment, or technical issues.'],
                ['title' => 'Control and commitments', 'body' => 'Independent models retain direct operational control. Agency models must follow agreed terms, processes, fees, and minimum commitments.'],
            ],
        ],
        [
            'id' => 'fees-and-contracts',
            'eyebrow' => 'Read before signing',
            'title' => 'Understand agency fees, contracts, and account ownership',
            'intro' => 'A clear agreement should explain what is charged, when it is charged, how long the arrangement lasts, what happens when someone leaves, and who controls each account.',
            'items' => [
                'The percentage, fixed fee, training cost, repayment condition, or other charge and exactly what it covers.',
                'The minimum term, notice period, exit process, and any cost connected to ending the agreement early.',
                'How platform earnings are reported, received, reconciled, and paid to the model.',
                'Who owns or controls platform accounts, content, usernames, social profiles, and customer data.',
                'What happens to pending earnings, account access, and content when the relationship ends.',
            ],
            'note_title' => 'Boss Doll Creators terms',
            'note' => 'Boss Doll Creators applicants should read the complete application and training terms, including the minimum agency commitment and any early-exit training condition, before agreeing to join.',
        ],
        [
            'id' => 'red-flags',
            'eyebrow' => 'Agency due diligence',
            'title' => 'How to identify webcam agency warning signs',
            'intro' => 'A polished website or confident recruiter does not replace transparent terms and verifiable support.',
            'items' => [
                ['title' => 'Guaranteed earnings', 'body' => 'No responsible agency can promise a specific income because results depend on the model, platform, audience, schedule, and market conditions.'],
                ['title' => 'Hidden or changing fees', 'body' => 'Costs should be written clearly before work begins, not introduced after accounts or earnings are under someone else\'s control.'],
                ['title' => 'Pressure around identity documents', 'body' => 'Verification must use secure, legitimate processes. Do not send sensitive documents through unverified personal accounts.'],
                ['title' => 'Boundary pressure', 'body' => 'Leave conversations that shame, threaten, or pressure you to perform activity outside your stated limits.'],
                ['title' => 'No exit explanation', 'body' => 'An agency should be able to explain account access, pending payment, content, and obligations after departure.'],
            ],
        ],
        [
            'id' => 'questions-to-ask',
            'eyebrow' => 'Before deciding',
            'title' => 'Questions to ask any webcam agency',
            'intro' => 'Ask for written answers and compare them with the contract rather than relying on a sales conversation.',
            'items' => [
                'Which exact services are included before and after I start working?',
                'What fees, percentages, training conditions, and other deductions can apply?',
                'Who owns and controls my platform accounts and login recovery details?',
                'How are earnings verified, when are payments made, and what records will I receive?',
                'How do I leave, and what happens to accounts, content, data, and pending earnings?',
                'Who handles urgent safety, privacy, technical, or account problems?',
            ],
        ],
    ];

    $faqs = [
        ['question' => 'Is it better to use a webcam agency or work independently?', 'answer' => 'It depends on the individual and the agreement. Independent work offers direct control but requires self-training and self-management. A good agency can provide structure and support, but the fees, term, account ownership, and exit conditions must be worthwhile and clear.'],
        ['question' => 'What do webcam agency fees pay for?', 'answer' => 'They may cover onboarding, training, account support, technical guidance, strategy, administration, payment handling, or ongoing management. Ask for the exact included services and full cost in writing.'],
        ['question' => 'How can I find a legitimate webcam agency?', 'answer' => 'Look for clear adult eligibility, written fees and terms, realistic income language, secure document handling, boundary respect, identifiable support, a documented payment process, and a clear exit procedure.'],
        ['question' => 'Should a webcam agency control my accounts?', 'answer' => 'Account arrangements vary, but ownership, access, recovery, data, and what happens on exit must be stated clearly in the agreement. Never assume you will automatically keep an account when a relationship ends.'],
        ['question' => 'Can a webcam agency guarantee my income?', 'answer' => 'No. A responsible agency can provide training and support, but cannot guarantee viewer demand, platform performance, or a specific earning result.'],
    ];
@endphp

<x-marketing-guide-page
    route-name="webcam-agency-vs-independent"
    title="Webcam Agency vs Independent Modelling"
    description="Compare a webcam agency with independent modelling, including support, control, fees, contracts, account ownership, payment records, red flags, and questions to ask."
    :keywords="['webcam agency vs independent', 'webcam agency fees', 'webcam agency contract', 'how to choose a webcam agency', 'legitimate webcam agencies']"
    eyebrow="Choosing a working model"
    heading="Webcam agency vs independent: compare the full agreement"
    lead="Understand the trade-off between direct control and structured support, then compare fees, training, account ownership, payment records, contract length, safety help, and exit terms before deciding."
    :hero-image="$heroImage"
    hero-alt="Group of women discussing online creator work and agency support together"
    answer="Independent models keep direct responsibility for the business. Agency models receive defined support under written terms. The better route is the one whose complete responsibilities, cost, control, and exit conditions fit you."
    :facts="[
        ['label' => 'Independent route', 'value' => 'More direct control'],
        ['label' => 'Agency route', 'value' => 'More structured support'],
        ['label' => 'Always compare', 'value' => 'Full cost and services'],
        ['label' => 'Never accept', 'value' => 'Hidden terms or pressure'],
    ]"
    :sections="$sections"
    :faqs="$faqs"
    :related="[
        ['route' => 'webcam-agency', 'label' => 'Boss Doll Creators webcam agency', 'detail' => 'Review the support, responsibilities, process, and published terms'],
        ['route' => 'webcam-model-earnings-payments', 'label' => 'Webcam model earnings and payments', 'detail' => 'Understand gross earnings, deductions, payout timing, and records'],
        ['route' => 'webcam-model-safety-privacy', 'label' => 'Webcam model safety and privacy', 'detail' => 'Compare how each route handles account, privacy, and incident support'],
    ]"
/>
