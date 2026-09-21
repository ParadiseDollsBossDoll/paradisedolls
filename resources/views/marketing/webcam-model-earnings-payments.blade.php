@php
    $heroImage = asset('images/13.jpeg');

    $sections = [
        [
            'id' => 'how-earnings-work',
            'eyebrow' => 'Income basics',
            'title' => 'Webcam earnings are performance-based, not guaranteed wages',
            'intro' => 'Most webcam platforms use tokens, credits, private-session rates, tips, subscriptions, or other earning features. The visible customer spend is not always the same as the model\'s payable amount.',
            'paragraphs' => [
                'Results can vary widely between models and between weeks. Time online matters, but so do audience fit, communication, consistency, platform traffic, profile quality, session timing, retention, and market conditions.',
                'Treat income examples as illustrations, not promises. Ask whether any figure is gross customer spend, gross platform earnings, agency-reported earnings, or the amount that actually reaches the model after deductions.',
            ],
        ],
        [
            'id' => 'earnings-funnel',
            'eyebrow' => 'Follow the money',
            'title' => 'Separate gross activity from final take-home pay',
            'intro' => 'A transparent payment process lets the model trace the calculation from platform activity to the final amount received.',
            'items' => [
                ['title' => 'Customer activity', 'body' => 'Tokens, credits, tips, private time, subscriptions, or purchases are recorded according to the platform system.'],
                ['title' => 'Platform payable earnings', 'body' => 'The platform applies its value, revenue share, rules, adjustments, and payout eligibility.'],
                ['title' => 'Agency calculation', 'body' => 'Any agreed agency fee, service charge, training condition, advance, or other authorised adjustment should be itemised.'],
                ['title' => 'Payment conversion and fees', 'body' => 'Currency conversion, transfer costs, processor fees, and payout timing may affect the received amount.'],
                ['title' => 'Tax and personal costs', 'body' => 'Self-employed models may still need to reserve money for tax, equipment, internet, software, and other business expenses.'],
            ],
        ],
        [
            'id' => 'payment-questions',
            'eyebrow' => 'Before you start',
            'title' => 'Ask how and when you will be paid',
            'intro' => 'Payment terms should be understandable before accounts begin earning.',
            'items' => [
                'Which legal currency and earning unit does each platform use?',
                'Who receives the platform payout first, and where can the model verify the source balance?',
                'What percentage, fee, exchange rate, transfer cost, hold, or adjustment may be deducted?',
                'What is the payout schedule, minimum threshold, processing time, and procedure for a delayed payment?',
                'Which statement, invoice, or report will show the calculation for each pay period?',
                'What happens to pending earnings if the agency relationship or platform account ends?',
            ],
        ],
        [
            'id' => 'records',
            'eyebrow' => 'Operate like a business',
            'title' => 'Keep records that let you verify every payment',
            'intro' => 'Consistent records help models identify missing earnings, understand performance, prepare tax information, and compare platforms accurately.',
            'items' => [
                ['title' => 'Platform statements', 'body' => 'Save balance reports, session history, tips, adjustments, and payout confirmations for the relevant period.'],
                ['title' => 'Agency statements', 'body' => 'Keep the calculation showing gross earnings, agreed charges, conversions, additions, deductions, and final pay.'],
                ['title' => 'Bank or processor receipts', 'body' => 'Match the final transfer to the statement and note any receiving or conversion fee.'],
                ['title' => 'Business expenses', 'body' => 'Record equipment, approved software, internet, and professional costs according to local tax rules.'],
            ],
            'note_title' => 'Tax and legal guidance',
            'note' => 'Paradise Dolls can explain its own payment records, but models should use a qualified local accountant or adviser for personal tax and legal decisions.',
        ],
        [
            'id' => 'improve-responsibly',
            'eyebrow' => 'Review useful signals',
            'title' => 'Improve the process without chasing guarantees',
            'intro' => 'A professional review looks beyond one large tip or one quiet session and compares consistent working patterns over time.',
            'items' => [
                'Track planned hours, active hours, platform mix, repeat viewers, private-session activity, and payable earnings.',
                'Compare gross results with the final amount received instead of measuring performance by token totals alone.',
                'Review privacy, wellbeing, and boundary costs alongside revenue; a higher total is not automatically a healthier result.',
                'Change one part of the routine at a time so you can understand whether it helped.',
            ],
        ],
    ];

    $faqs = [
        ['question' => 'How much do webcam models earn?', 'answer' => 'There is no guaranteed or standard result. Earnings vary according to platform traffic, hours, consistency, audience engagement, services used, market conditions, fees, and the individual model.'],
        ['question' => 'How do webcam models get paid?', 'answer' => 'Platforms or agencies usually pay according to a scheduled cycle and supported payout method after any threshold, verification, revenue share, agreed fees, adjustments, and currency conversion. Exact terms must be checked for each arrangement.'],
        ['question' => 'Are tokens the same as cash?', 'answer' => 'No. A token or credit has a platform-specific value, and the viewer purchase price may differ from the model payout value. Use the platform statement and written agency calculation to determine payable earnings.'],
        ['question' => 'What deductions can affect webcam model pay?', 'answer' => 'Possible deductions include platform revenue share, agency fees, authorised training or service conditions, currency conversion, payment processing, transfer fees, refunds or adjustments, and personal tax obligations. Only applicable written terms should be used.'],
        ['question' => 'Does a webcam agency guarantee earnings?', 'answer' => 'No. An agency may provide training, management, and support, but cannot guarantee audience demand, platform traffic, or a specific income.'],
    ];
@endphp

<x-marketing-guide-page
    route-name="webcam-model-earnings-payments"
    title="Webcam Model Earnings and Payments"
    description="Understand how webcam model earnings and payments work, including tokens, gross versus final pay, agency fees, currency conversion, payout timing, records, and variable income."
    :keywords="['how much do webcam models earn', 'how do webcam models get paid', 'webcam model earnings', 'webcam agency fees']"
    eyebrow="Earnings and payment guide"
    heading="Webcam model earnings and payments, without the hype"
    lead="Learn how platform activity becomes payable income, which deductions and currency steps may affect final pay, what records to expect, and why no responsible agency can guarantee earnings."
    :hero-image="$heroImage"
    hero-alt="Professional online creator studio prepared for recording and earnings tracking"
    answer="Webcam income is variable. To understand real pay, trace platform activity through platform value, agreed agency terms, adjustments, currency conversion, payment fees, and personal tax responsibilities."
    :facts="[
        ['label' => 'Income type', 'value' => 'Variable performance income'],
        ['label' => 'Never assume', 'value' => 'Tokens equal cash'],
        ['label' => 'Always request', 'value' => 'Clear payment records'],
        ['label' => 'Earnings promise', 'value' => 'Never guaranteed'],
    ]"
    :sections="$sections"
    :faqs="$faqs"
    :related="[
        ['route' => 'webcam-agency-vs-independent', 'label' => 'Webcam agency vs independent', 'detail' => 'Compare fees, control, services, and payment responsibilities'],
        ['route' => 'webcam-agency', 'label' => 'Paradise Dolls webcam agency', 'detail' => 'Review the published agency support and applicant responsibilities'],
        ['route' => 'multistreaming', 'label' => 'Multistreaming for webcam models', 'detail' => 'Learn how multi-platform work changes tracking and operations'],
    ]"
/>
