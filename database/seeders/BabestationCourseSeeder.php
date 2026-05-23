<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonContentBlock;
use Illuminate\Database\Seeder;

class BabestationCourseSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::updateOrCreate(
            ['slug' => 'babestation-boss-doll-blueprint'],
            [
                'title'             => 'Boss Doll Blueprint — Babestation',
                'platform_label'    => 'Babestation',
                'platform_color'    => '#E91E8C',
                'short_description' => 'Your complete desktop walkthrough for Babestation Cams — log in, configure your show, manage earnings, and build a premium income stream.',
                'description'       => 'This module walks you through the full Babestation Cams desktop platform (Camstation). You will learn how to log in, navigate the single-page interface, configure your show modes and pricing, track your earnings across multiple income streams, build your profile and private galleries, manage paid DMs, understand compliance rules for shorts, and use the support centre — everything you need to go live with confidence and earn like a Boss Doll.',
                'what_you_will_learn' => implode("\n", [
                    'Log in to Babestation Cams and accept the platform rules',
                    'Navigate the full Camstation interface on a single page',
                    'Understand your daily and monthly earnings dashboard',
                    'Configure Free, Group, and Private show modes',
                    'Set competitive per-minute pricing for Group and Private shows',
                    'Track your top spenders and build loyal returning clients',
                    'Build a compelling profile, bio, and cam categories',
                    'Upload public gallery content and paid private galleries',
                    'Set up paid DMs and earn between live sessions',
                    'Upload short teaser videos within platform compliance rules',
                    'Manage notifications, banned users, and the support centre',
                ]),
                'requirements' => implode("\n", [
                    'A verified Babestation performer account',
                    'A desktop or laptop computer',
                    'A quality webcam and microphone',
                    'Good lighting and a professional streaming space',
                ]),
                'difficulty_level'   => 'Beginner',
                'estimated_duration' => '3 hours',
                'is_published'       => true,
                'sort_order'         => 12,
            ]
        );

        $modules = [

            // ─── MODULE 1 ───────────────────────────────────────────────────────────
            [
                'title'       => 'Introduction',
                'description' => 'Welcome to the Babestation Boss Doll Blueprint desktop walkthrough.',
                'lessons'     => [
                    [
                        'title'      => 'Welcome to Babestation',
                        'overview'   => 'Babestation is a premium UK-based cam platform where clients come specifically to spend money on one-on-one private interaction. Unlike tip-based sites, you earn per minute in Group or Private shows, with multiple additional income streams running in the background — live chat, tips, toys, gallery sales, direct messages, and phone calls. This is where you stop working for the room and start working one-on-one, in your high-value era: less noise, better clients, bigger money.',
                        'intro_only' => true,
                    ],
                ],
            ],

            // ─── MODULE 2 ───────────────────────────────────────────────────────────
            [
                'title'       => 'Getting Started — Login & Interface',
                'description' => 'Access the performer dashboard and get familiar with the Camstation interface.',
                'lessons'     => [
                    [
                        'title'    => 'Login Overview',
                        'overview' => 'To access Babestation as a performer, go to www.babestationcams.stream. Enter your username and password in the Performer Login panel on the right-hand side of the screen, then click the pink Log In button to be taken to the integrated Camstation streaming software.',
                        'steps'    => implode("\n", [
                            'Open your browser and navigate to www.babestationcams.stream',
                            'Locate the Performer Login panel on the right-hand side of the screen',
                            'Enter your username in the green-highlighted field',
                            'Enter your password in the yellow-highlighted field',
                            'Click the pink "Log In" button to enter Camstation',
                        ]),
                        'tips'     => implode("\n", [
                            'Bookmark the login URL so you can reach it quickly before every session',
                            'Use a secure, unique password — never share your login details with anyone',
                            'If you forget your password, use the "Forgot Password" link on the login screen',
                        ]),
                    ],
                    [
                        'title'    => 'Login Rules & Acknowledgement',
                        'overview' => 'When you first arrive at the login page a pop-up reminder will appear listing the platform rules you must follow before broadcasting. You must click "I acknowledge and understand" to proceed. Key rules: all performers must be 18+, no underage content or fantasy, no incest roleplay, no abusive themes, no hate speech, and no revealing real personal details on camera.',
                        'steps'    => implode("\n", [
                            'Read the full rules listed in the pop-up carefully before clicking through',
                            'Confirm that all performers who may appear on camera are 18+ and pre-verified',
                            'Click the pink "I acknowledge and understand" button to continue',
                            'Visit the Camstation Documents page for the full performer agreement',
                        ]),
                        'tips'     => implode("\n", [
                            'These rules protect both you and the platform — read them every time, not just once',
                            'If you want another performer on camera with you, contact the admin team first to get them verified — appearing unverified can result in an instant ban',
                            'Violating platform rules even once can result in account suspension',
                        ]),
                    ],
                    [
                        'title'    => 'Interface Overview',
                        'overview' => 'Once logged in you will see the full Camstation dashboard on a single page. Everything you need — your show controls, earnings stats, current viewers, settings, and session history — is displayed right here. You do not need to switch between tabs. When you go live you will appear on the Babestation website as available and clients can click directly into your room.',
                        'steps'    => implode("\n", [
                            'Spend a few minutes exploring the interface before going live for the first time',
                            'Note the top bar showing Today\'s Estimated Earnings, Today\'s Top Spender, Daily Performer Ranking, This Month\'s Estimated Earnings, and Favourites',
                            'Identify the My Show section with your live camera feed in the centre',
                            'Locate the Show Settings panel to the right of your camera preview',
                            'Find the Current Viewers and Your Top Spenders This Month sections on the far right',
                            'Note the Today\'s Sessions with Earnings breakdown table at the bottom of the page',
                        ]),
                        'tips'     => implode("\n", [
                            'Everything is on one page — nothing is hidden in other tabs or menus',
                            'Familiarise yourself with the layout before your first live session so you never scramble during a show',
                            'If your camera or microphone is not showing correctly, go to Video Settings before going live',
                        ]),
                    ],
                ],
            ],

            // ─── MODULE 3 ───────────────────────────────────────────────────────────
            [
                'title'       => 'Your Dashboard — Stats & Earnings',
                'description' => 'Understand the key metrics displayed across the top of your Camstation dashboard.',
                'lessons'     => [
                    [
                        'title'    => "Today's Estimated Earnings",
                        'overview' => "The Today's Estimated Earnings widget (highlighted green) shows how much you have earned so far in the current day. This figure is an estimate shown before your agency fee is deducted, so your final payout will be slightly lower. Babestation pays monthly, directly to your bank account. Tracking this daily keeps you on pace with your income goals.",
                        'steps'    => implode("\n", [
                            "Locate the Today's Estimated Earnings box in the top-left section of the dashboard",
                            'Check this figure regularly throughout your session to track your performance',
                            'Remember: the displayed amount is pre-agency-fee — your actual payout will be lower',
                            'Click "Why Estimated?" for more information on how the figure is calculated',
                        ]),
                        'tips'     => implode("\n", [
                            'Babestation pays monthly — know your running total so you always know what to expect at payout',
                            'Set a daily earnings target and check this widget throughout your session to stay on track',
                            "Slow days happen — peak hours and returning regulars make the biggest difference to your daily total",
                        ]),
                    ],
                    [
                        'title'    => "Today's Top Spender & Daily Performer Ranking",
                        'overview' => "Today's Top Spender (marked with a whale emoji) shows the username of the client who has spent the most on you today. These are your most valuable clients — acknowledge them, give them attention, and build that connection. The Daily Performer Ranking shows your current position among all active performers. As you earn more, your ranking improves, pushing you higher on the Babestation site for greater visibility and more inbound traffic.",
                        'steps'    => implode("\n", [
                            "Locate the Today's Top Spender box in the top bar — it shows your whale's username in real time",
                            'Note their username and make a point of acknowledging and engaging them during your session',
                            'Find your Daily Performer Ranking number — lower is better, as it means you are ranked higher',
                            'Track your ranking across sessions to see how your consistency is paying off',
                        ]),
                        'tips'     => implode("\n", [
                            'Your whale changes throughout the day — keep checking and always acknowledge big spenders by name',
                            'A better ranking means more visibility on the site, which means more clients discovering you',
                            'Consistent streaming hours and strong engagement are the fastest ways to improve your ranking',
                        ]),
                    ],
                    [
                        'title'    => "This Month's Estimated Earnings & Favourites",
                        'overview' => "This Month's Estimated Earnings shows your running total for the current calendar month before the agency fee is deducted. Use this to gauge whether you are on track for your monthly income goal — payments go directly to your bank at month end. Favourites shows how many clients have saved your profile, a feature that works like a follow button, helping returning clients find you easily and building your loyal audience over time.",
                        'steps'    => implode("\n", [
                            "Locate This Month's Estimated Earnings in the top bar — this is your running monthly income",
                            'Compare this figure against your monthly goal to see whether you need to increase your hours or session quality',
                            'Check your Favourites count to monitor how your loyal audience is growing',
                            'Focus on delivering great private show experiences to encourage clients to favourite your profile',
                        ]),
                        'tips'     => implode("\n", [
                            'Divide your monthly target into weekly milestones to keep yourself motivated and on track',
                            'Favourites are future income — every client who favourites you is likely to return and spend again',
                            'Clients who favourite your profile are notified when you go live — this drives free traffic to your show',
                        ]),
                    ],
                    [
                        'title'    => "Current Viewers, Top Spenders & Today's Sessions",
                        'overview' => "Current Viewers (yellow section, top right) shows who is in your room right now across Free, Group, and Private shows — keep an eye on this so you always know who you are interacting with. Your Top Spenders This Month (brown section) shows your consistent high-spending regulars. Today's Sessions with Earnings at the bottom of the page gives a full breakdown of every session: date, viewer, mode, status, duration, and earnings.",
                        'steps'    => implode("\n", [
                            'Monitor the Current Viewers panel while streaming to know who is in your room at all times',
                            'Note who enters and exits — use this to tailor your engagement and upsell private shows',
                            "Check Your Top Spenders This Month regularly to identify your most valuable client relationships",
                            "Review Today's Sessions table at the bottom after each session to understand which show modes earn the most",
                        ]),
                        'tips'     => implode("\n", [
                            "Top spenders are your repeat income — treat them like VIPs and they will keep coming back",
                            "The session breakdown helps you identify your most profitable hours and show modes — use this data to optimise your schedule",
                            "If a viewer lingers in free chat without booking private, engage them directly and give them a reason to step into a private show",
                        ]),
                    ],
                ],
            ],

            // ─── MODULE 4 ───────────────────────────────────────────────────────────
            [
                'title'       => 'Live Show Settings',
                'description' => 'Configure your video, show modes, pricing, and go live with confidence.',
                'lessons'     => [
                    [
                        'title'    => 'My Show & Video Settings',
                        'overview' => 'My Show is your main working area where your live camera feed appears. When the status shows green "Available" but "Not Broadcasting," you are visible on the platform and ready to receive calls but not yet live. Video Settings (purple button) lets you select your webcam, microphone, and stream quality — 1080p HD, 720p (recommended for most computers and mobiles), or SD for lower-bandwidth connections.',
                        'steps'    => implode("\n", [
                            'Check that your camera feed is displaying clearly in the My Show preview box',
                            'Verify that Current Show Status shows green "Available" before going live',
                            'Click the Video Settings button if your camera or microphone is not showing correctly',
                            'Select your webcam from the Camera dropdown (e.g. SplitCam Video Driver)',
                            'Select your microphone from the Microphone dropdown',
                            'Choose 720p for the best balance of quality and performance on most setups',
                            'Click Clear Device Cache if you experience any connection or display issues',
                        ]),
                        'tips'     => implode("\n", [
                            'Always check your camera and microphone before going live — never assume they are working correctly',
                            '720p is the recommended quality setting for most desktops and mobiles — only use 1080p if your internet connection is consistently strong',
                            'If your image looks dark or grainy, fix your lighting before adjusting any camera settings',
                        ]),
                    ],
                    [
                        'title'    => 'Show Settings & Show Modes',
                        'overview' => 'Show Settings (orange section) is where you control how you work and earn. Toggle Free Chat on to open your room to all viewers like a tip-based platform. Toggle Group Chat on to let multiple paying clients share your room at a per-minute group rate. Toggle Private Chat on to enable exclusive one-on-one paid sessions. You can also enable Lovense (pink), connect your phone for phone shows (red), and add a second camera (purple).',
                        'steps'    => implode("\n", [
                            'Locate the Show Settings panel in the centre of the interface',
                            'Decide on your show mode strategy before going live',
                            'Toggle Free Enabled on if you want an open, traffic-building show like a tip-based platform',
                            'Toggle Group Enabled on to allow multiple paying viewers in your room at a per-minute rate',
                            'Toggle Private Enabled on to allow exclusive one-on-one private sessions',
                            'Toggle Lovense Enabled on if you have a compatible Lovense toy connected',
                            'Toggle Phone Enabled on if you want to accept phone call sessions',
                            'Toggle Second Camera on if you are streaming with multiple cameras',
                        ]),
                        'tips'     => implode("\n", [
                            'For maximum earnings, run Group and Private — this is the premium model Babestation is built for',
                            'Free Chat drives traffic and visibility but earns less per minute — use it to warm up the room and funnel viewers into paid shows',
                            'Private shows are where the serious money is — always have Private enabled',
                        ]),
                    ],
                    [
                        'title'    => 'Pricing — Setting Your Per-Minute Rates',
                        'overview' => 'In the Show Settings area you will see Group Price Per Minute and Private Price Per Minute input fields. These are fully editable. Babestation recommends £2–£5 per minute. The defaults are Group at £3/min and Private at £5/min. This is a premium platform — do not undervalue your time. Clients here are used to paying and expect a certain level of attention in return.',
                        'steps'    => implode("\n", [
                            'Locate the Group Price Per Minute field in the Show Settings panel',
                            'Set your Group rate — £3/min is a solid starting point for new performers',
                            'Locate the Private Price Per Minute field immediately to the right',
                            'Set your Private rate — £5/min is the recommended starting point',
                            'As you build your reputation and a base of regulars, consider increasing your rates',
                            'Save your settings before clicking Go Live',
                        ]),
                        'tips'     => implode("\n", [
                            'Never price below £2/min — it devalues your time and tends to attract lower-quality clients',
                            'A 10-minute Private show at £5/min earns £50 — one good private can set the tone for your whole session',
                            'During peak hours you can trial slightly higher rates — test what the market will bear for your room',
                        ]),
                    ],
                    [
                        'title'    => 'Go Live & Managing Your Active Show',
                        'overview' => 'When everything is configured — camera is on, show modes are set, and pricing is confirmed — click the large green "Go Live" button. You are immediately live and visible on the Babestation site. Clients can enter your room straight away. Monitor your Current Viewers panel while live, engage with everyone in the room, and use free chat to funnel viewers into paid Group or Private sessions.',
                        'steps'    => implode("\n", [
                            'Double-check your camera feed, lighting, and microphone one final time',
                            'Confirm your show mode toggles and pricing are correct',
                            'Click the large green "Go Live" button',
                            'Check that the Current Show Status updates to confirm you are broadcasting',
                            'Monitor the Current Viewers panel to see who enters your room',
                            'Engage with all viewers — even free viewers can become paying private clients',
                            'Keep an eye on your Today\'s Top Spender while live and acknowledge them',
                        ]),
                        'tips'     => implode("\n", [
                            'Keeping a consistent schedule (same days and times each week) is the single fastest way to build a loyal returning audience',
                            'Engage every viewer by name — personal attention is what makes clients choose private shows over free chat',
                            'During peak times, a Group show with multiple paying viewers can sometimes out-earn a single private session — stay flexible',
                        ]),
                    ],
                ],
            ],

            // ─── MODULE 5 ───────────────────────────────────────────────────────────
            [
                'title'       => 'Tracking Your Earnings',
                'description' => 'View, filter, and understand all your income streams on Babestation.',
                'lessons'     => [
                    [
                        'title'    => 'Viewing & Filtering Your Earnings',
                        'overview' => "To view detailed earnings, click the money bag icon in the left-hand sidebar to open My Earnings. Set a From and To date range and click the green View Earnings button. Your earnings are broken down into multiple streams: Total Earnings, Chat Earnings, Tip Earnings, Toy Earnings, Gallery Earnings, Direct Message Earnings, and Phone Call Earnings. Below that, My Sessions gives a full line-by-line breakdown of every individual session.",
                        'steps'    => implode("\n", [
                            'Click the money bag icon in the left sidebar to open My Earnings',
                            'Set your "From" date using the date picker on the left',
                            'Set your "To" date for the end of the period you want to review',
                            'Click the green "View Earnings" button to load the results',
                            'Review each income stream: Chat, Tips, Toys, Gallery, Direct Messages, and Phone Calls',
                            'Scroll down to My Sessions for a full breakdown of each individual session including rate and duration',
                        ]),
                        'tips'     => implode("\n", [
                            'Check your earnings daily so you are always aware of your income trajectory and can adjust if needed',
                            'Identify which income streams are performing best and focus on growing those',
                            'The session breakdown shows your rate and duration — use this data to optimise your show length and pricing strategy',
                        ]),
                    ],
                    [
                        'title'    => 'Understanding Your Multiple Income Streams',
                        'overview' => 'Babestation is powerful because you have multiple income streams running simultaneously. Chat Earnings come from live show minutes. Tip Earnings are tips sent during sessions. Toy Earnings come from Lovense interactions. Gallery Earnings come from paid photo and video content. Direct Message Earnings are from paid DM access. Phone Call Earnings come from phone show sessions. A Boss Doll earns from all of these at once.',
                        'steps'    => implode("\n", [
                            'Review your Chat Earnings first — this is your base live show income from per-minute sessions',
                            'Check your Tip Earnings — tips are bonus income on top of your per-minute rate',
                            'Review Toy Earnings if you have Lovense enabled — this runs passively while you stream',
                            'Check Gallery Earnings to see how your paid content is selling in the background',
                            'Review Direct Message Earnings to see how your paid DM pricing is performing',
                            'Note Phone Call Earnings if you have the phone mode enabled on your account',
                        ]),
                        'tips'     => implode("\n", [
                            'A Boss Doll earns from multiple streams at the same time — never rely on just one source of income',
                            'Gallery and DM earnings continue while you are offline — setting these up properly is essential',
                            'Lovense earnings are passive during your show — connecting a compatible toy can significantly increase your per-session income without extra effort',
                        ]),
                    ],
                ],
            ],

            // ─── MODULE 6 ───────────────────────────────────────────────────────────
            [
                'title'       => 'Profile, Gallery & Content',
                'description' => 'Build a profile that converts visitors and upload content that earns while you sleep.',
                'lessons'     => [
                    [
                        'title'    => 'Building Your Profile',
                        'overview' => 'Your profile is your shop window on Babestation. It includes your Profile Image (main photo — high quality and eye-catching), Cam Window, Headshot, Turn-Ons, Availability, Preferred Age, and your About Me bio. The bio is your most important selling tool — show your personality, your vibe, what to expect in Group and Private shows, and make it clear why clients should choose you over anyone else on the platform.',
                        'steps'    => implode("\n", [
                            'Click your profile image in the top corner of Camstation to access the Profile section',
                            'Upload a high-quality, professional Profile Image — this is the first thing clients see',
                            'Upload a Cam Window image (a shot of you at your streaming setup) and a Headshot',
                            'Set your Availability to accurately reflect when you are actually online',
                            'Fill in your Turn-Ons — this guides clients and increases relevant engagement',
                            'Set your Preferred Age to help attract the right audience',
                            'Write a strong, compelling About Me bio that showcases your personality and what you offer',
                            'Save all changes',
                        ]),
                        'tips'     => implode("\n", [
                            'Your bio is your biggest sales tool — write it like a premium advertisement for your show, not a casual description',
                            'Clearly describe your show style (e.g. private shows, roleplay, domination) so clients arrive knowing what to expect',
                            'Keep your profile images updated — fresh, high-quality photos attract new clients and signal you are actively streaming',
                        ]),
                    ],
                    [
                        'title'    => 'Gallery Upload & Content Review',
                        'overview' => 'Your public gallery is your photo showcase on Babestation. Click your profile image, then click Gallery in the side menu. Use the "Add New Images" button and drag-and-drop or browse for photos. Once uploaded, Babestation reviews all content for compliance before it goes live — this is normal and may take a short time. Do not panic if your photos do not appear immediately.',
                        'steps'    => implode("\n", [
                            'Click your profile image to enter the Profile section',
                            'Click "Gallery" in the left side menu',
                            'Click the "Add New Images" button',
                            'Drag and drop your photos into the upload box, or click Browse to select files',
                            'Click Save to submit your images for review',
                            'Wait for Babestation to review and approve your content before it appears publicly on your profile',
                        ]),
                        'tips'     => implode("\n", [
                            'Use high-quality photos with good lighting — your gallery is how potential clients decide whether to book a private session with you',
                            'Babestation is a premium platform and reviews all content before publication — allow time for approval and do not upload the same photos repeatedly',
                            'Update your gallery regularly to keep your profile feeling fresh and to attract returning visitors',
                        ]),
                    ],
                    [
                        'title'    => 'Private Galleries — Passive Income From Your Content',
                        'overview' => 'Private Galleries are locked photo and video sets that clients pay to unlock — passive income that earns even while you are offline. Click your profile image, then select Private Galleries (the lock icon in the side menu). Click "Add Gallery," upload your content, set a title and a price, and click Save. Babestation will review the gallery before it goes live for purchase.',
                        'steps'    => implode("\n", [
                            'Click your profile image to enter the Profile section',
                            'Click "Private Galleries" in the left side menu (the option with the lock icon)',
                            'Click the pink "Add Gallery" button',
                            'Select the photos or videos you want to include in this paid gallery',
                            'Set a gallery title that teases the content and entices buyers',
                            'Set a price that reflects the quality and exclusivity of the content',
                            'Click Save to submit the gallery for review',
                            'Once approved, the gallery will appear as a paid unlock option on your profile page',
                        ]),
                        'tips'     => implode("\n", [
                            'Private galleries are passive income — set them up as a priority, before your first live session if possible',
                            'Use teasing, high-quality content that makes clients want to unlock the full gallery — give them a taste, not the full thing',
                            'Babestation has stricter content standards than some other platforms — make sure all photos are clear, well-lit, and professional',
                        ]),
                    ],
                    [
                        'title'    => 'Cam Category Settings',
                        'overview' => 'Cam Categories tell the Babestation platform how to categorise your profile and which clients to show you to. Click your profile image, then select Cam Categories. Set your Age, Build, Ethnicity, and Hair, and choose your fetishes and preferences from the available checkboxes. Be accurate but also strategic — think about the categories that will attract the clients you want. Click Save when done.',
                        'steps'    => implode("\n", [
                            'Click your profile image to enter the Profile section',
                            'Click "Cam Categories" in the left side menu',
                            'Set your physical attributes: Age, Build, Ethnicity, and Hair from the dropdowns',
                            'Browse the fetishes and preferences list and check everything that applies to your show style',
                            'Think strategically — select popular categories that attract high-spending clients',
                            'Click the green Save button to apply your category settings',
                        ]),
                        'tips'     => implode("\n", [
                            'Accurate categories mean the right clients find you — this directly impacts your booking rate and per-session earnings',
                            'Being too restrictive in your categories reduces your discoverability — cover a broad but honest range',
                            'Review and update your categories if you notice your traffic or booking patterns changing',
                        ]),
                    ],
                ],
            ],

            // ─── MODULE 7 ───────────────────────────────────────────────────────────
            [
                'title'       => 'Notifications & Paid Messages',
                'description' => 'Stay on top of client activity and earn through paid DMs between live sessions.',
                'lessons'     => [
                    [
                        'title'    => 'Setting Up Notifications',
                        'overview' => 'Notifications keep you updated on everything happening on your Babestation account. Click your profile image, then select Notifications in the side menu. For each type of event you can choose Site notifications, Push notifications, and Email notifications. Key events to enable include: New Direct Message, Media Finished Processing, Media Unlocked, New Comments and Likes on Shorts, view milestones, and New Favourite.',
                        'steps'    => implode("\n", [
                            'Click your profile image to enter the Profile section',
                            'Click "Notifications" in the left side menu',
                            'Review the full list of notification event types',
                            'Toggle Site, Push, and Email for each event type based on your preference',
                            'Enable all New Direct Message notifications — never miss a paying client reaching out',
                            'Enable Media Unlocked and New Favourite notifications to track client engagement',
                            'Click Save when done',
                        ]),
                        'tips'     => implode("\n", [
                            'Push notifications let you know when a client messages you even when you are away from your desk',
                            'A missed DM is a missed sale — always have New Direct Message notifications active',
                            'View milestone notifications (100 views, 500 views, 1000 views) are motivating signals that your content is performing',
                        ]),
                    ],
                    [
                        'title'    => 'Paid Messages (DM Settings)',
                        'overview' => 'Paid Messages let you charge clients to message you and unlock your content — income that runs in the background even when you are not live. In the left sidebar, click the messages/envelope icon to access Direct Messages, then click Settings. Set your Price (£) Per Message the user sends, Price (£) Per Image the user sends, and Price (£) Per Video the user sends. Clients pay each time they initiate contact or send media. Do not be afraid to charge for your time.',
                        'steps'    => implode("\n", [
                            'Click the envelope/messages icon in the left sidebar to open Direct Messages',
                            'Click Settings in the top-right area of the Direct Messages page',
                            'Set your Price (£) Per Message The User Sends',
                            'Set your Price (£) Per Image The User Sends — a suggested starting point is £5',
                            'Set your Price (£) Per Video The User Sends',
                            'Write a short inbox bio to invite clients in and let them know you are available',
                            'Click Save to apply your DM pricing',
                        ]),
                        'tips'     => implode("\n", [
                            'Paid DMs are one of your most consistent income streams — keep them active and priced properly at all times',
                            'Clients who pay to message you are already invested — use DMs to upsell private show bookings and private gallery unlocks',
                            'You are providing value with every message — charge accordingly and never feel guilty about your prices',
                        ]),
                    ],
                    [
                        'title'    => 'Messages Overview & Bulk Messaging',
                        'overview' => 'The Direct Messages section shows all incoming paid DMs. Once you have traffic, this is where conversations and content unlock requests come through. At the top of the page you will see Upload Media from Mobile (to send content straight from your phone) and Bulk Messaging (to send a message or promotion to all your DM contacts at once). Bulk messaging is a powerful tool for driving traffic to your room when you go live.',
                        'steps'    => implode("\n", [
                            'Click the messages icon in the left sidebar to open Direct Messages',
                            'Review any incoming messages and reply to active clients promptly',
                            'Use "Upload Media from Mobile" to send content directly from your phone without downloading to desktop first',
                            'Use "Bulk Messaging" to send a live session announcement to all your DM contacts',
                            'Keep conversations warm and engaging — guide clients toward private shows and gallery unlocks',
                        ]),
                        'tips'     => implode("\n", [
                            'Messages are where viewers become spenders — respond quickly, warmly, and with intention',
                            'Announce every live session via bulk message to drive immediate traffic to your room when you go live',
                            'Keep your inbox bio updated with your schedule so clients know exactly when to expect you online',
                        ]),
                    ],
                ],
            ],

            // ─── MODULE 8 ───────────────────────────────────────────────────────────
            [
                'title'       => 'Compliance, Safety & Support',
                'description' => 'Understand platform content rules, manage your room, and use the support centre.',
                'lessons'     => [
                    [
                        'title'    => 'Short Upload & Compliance Rules',
                        'overview' => 'Shorts are short teaser videos that advertise you on the Babestation platform and pull clients into your room. Navigate to the Shorts section in the left sidebar and click "Create Short." Shorts must follow strict compliance rules: no below-the-waist nudity, no genitalia, no open-leg poses, and no toys. Implied nudity is allowed — teasing angles, a hand bra, arms covering. Safe rule: if you cannot do it in free chat, you cannot do it in a short.',
                        'steps'    => implode("\n", [
                            'Navigate to the Shorts section in the left sidebar',
                            'Read the Shorts Compliance Reminder in full before uploading anything',
                            'Click "Create Short" to begin your upload',
                            'Select a video that teases your content and personality without violating the compliance rules',
                            'Upload and submit — Babestation will review the video before it goes live',
                            'Monitor your Overall Stats: Total Shorts, Total Views, Total Likes, and Total Comments',
                        ]),
                        'tips'     => implode("\n", [
                            'Think of your short as a trailer, not the full movie — tease just enough to make clients click into your room',
                            'Shorts that perform well increase your platform visibility and drive organic traffic to your live show',
                            'Upload new shorts regularly — fresh content keeps your profile active in the platform discovery feed',
                        ]),
                    ],
                    [
                        'title'    => 'Banned Users & Room Management',
                        'overview' => 'If a client is disrespectful, breaks your room rules, or makes you uncomfortable, you can ban them. Click the ban/no-entry icon in the left sidebar to open your Banned Users list. This shows each banned user\'s username, ban start and end date, the reason for the ban, and available actions. You are the boss of your room — never feel pressured to keep a viewer who is not serving you or your business.',
                        'steps'    => implode("\n", [
                            'Click the ban/no-entry icon in the left sidebar to view your Banned Users list',
                            'Review any currently active bans',
                            'To ban someone during a live session, use the viewer controls in your Current Viewers panel',
                            'Note the reason for every ban for your own records',
                            'If a banned user attempts to return on a different account, report them to the Babestation support team immediately',
                        ]),
                        'tips'     => implode("\n", [
                            'You are in charge of your room — removing disrespectful viewers quickly keeps the environment positive for genuine paying clients',
                            'If someone appears on camera with you who has not been pre-verified by the admin team, it can result in an instant account ban — never skip the verification process',
                            'Do not hesitate to ban — protecting your room protects your income',
                        ]),
                    ],
                    [
                        'title'    => 'Support Centre & Platform Resources',
                        'overview' => 'The Support Centre is your go-to resource for anything you are unsure about. Access it via the gear/settings icon at the bottom of the left sidebar. Inside you will find guides on streaming modes, paywall countdown, free-to-view and private-only streaming, second camera setup, messaging, PPV, and banning users. You will also find the Quick Start Guide and a direct link to the platform rules. If something is not covered in this course, it will be in the Support Centre.',
                        'steps'    => implode("\n", [
                            'Click the gear/settings icon at the bottom of the left sidebar',
                            'Open the Support Centre',
                            'Read the Getting Started Introduction and Quick Start Guide',
                            'Browse the Broadcasting Essentials section — this covers all show modes in depth',
                            'Click the link to read the full platform rules — this is mandatory before you stream',
                            'Bookmark the Support Centre page for quick reference during sessions',
                        ]),
                        'tips'     => implode("\n", [
                            'The Support Centre answers the vast majority of questions — always check here before contacting the support team',
                            'Reading the full platform rules thoroughly will protect your account from accidental violations that could cost you your earnings',
                            'The Documents page in the left sidebar contains your performer agreement — read it and keep a copy for your own records',
                        ]),
                    ],
                ],
            ],

            // ─── MODULE 9 ───────────────────────────────────────────────────────────
            [
                'title'       => "You're Ready",
                'description' => 'Final wrap-up and next steps for your Babestation journey.',
                'lessons'     => [
                    [
                        'title'      => "You're Ready — Go Live and Earn",
                        'overview'   => "That is everything — you now have a complete understanding of the Babestation Cams desktop platform. You know how to log in, navigate the interface, configure your show modes and pricing, track your multiple income streams, build a profile that converts, upload gallery and private content, manage your paid DMs, stay compliant with short video rules, and manage your room. As long as you can log in and go live, you are ready. If you ever get stuck, the Support Centre has the answers — or reach out for help. Welcome to Babestation, Doll.",
                        'intro_only' => true,
                    ],
                ],
            ],

        ];

        $moduleOrder = 1;
        foreach ($modules as $moduleData) {
            $module = CourseModule::updateOrCreate(
                [
                    'course_id' => $course->id,
                    'title'     => $moduleData['title'],
                ],
                [
                    'description'  => $moduleData['description'],
                    'is_published' => true,
                    'sort_order'   => $moduleOrder++,
                ]
            );

            $lessonOrder = 1;
            foreach ($moduleData['lessons'] as $lessonData) {
                $isIntroOnly = $lessonData['intro_only'] ?? false;

                $lesson = Lesson::updateOrCreate(
                    [
                        'course_module_id' => $module->id,
                        'title'            => $lessonData['title'],
                    ],
                    [
                        'course_id'    => $course->id,
                        'body'         => $lessonData['overview'] ?? '',
                        'overview'     => $lessonData['overview'] ?? '',
                        'steps'        => $lessonData['steps'] ?? null,
                        'tips'         => $lessonData['tips'] ?? null,
                        'is_published' => true,
                        'sort_order'   => $lessonOrder++,
                    ]
                );

                if ($lesson->contentBlocks()->count() === 0) {
                    if ($isIntroOnly) {
                        $blocks = [
                            ['block_type' => 'heading', 'title' => $lessonData['title'],                              'content' => null,                'sort_order' => 1],
                            ['block_type' => 'text',    'title' => null,                                               'content' => $lessonData['overview'], 'sort_order' => 2],
                            ['block_type' => 'video',   'title' => $lessonData['title'].' — Presentation Video',       'content' => null,                'sort_order' => 3],
                            ['block_type' => 'steps',   'title' => null,                                               'content' => implode("\n", [
                                'Watch the full presentation video above',
                                'Take notes on the key points that apply to your current situation',
                                'Return to this lesson any time you need a refresher',
                            ]), 'sort_order' => 4],
                            ['block_type' => 'tips',    'title' => null,                                               'content' => implode("\n", [
                                'Babestation is a premium platform — approach every session with a premium mindset',
                                'Less noise, better clients, bigger money: that is the Babestation difference',
                                'Multiple income streams running at once is what separates a Boss Doll from the average performer',
                            ]), 'sort_order' => 5],
                        ];
                    } else {
                        $blocks = [
                            ['block_type' => 'heading', 'title' => $lessonData['title'],                                'content' => null,                    'sort_order' => 1],
                            ['block_type' => 'text',    'title' => null,                                                 'content' => $lessonData['overview'],  'sort_order' => 2],
                            ['block_type' => 'video',   'title' => $lessonData['title'].' — Presentation Video',         'content' => null,                    'sort_order' => 3],
                            ['block_type' => 'heading', 'title' => 'Now Follow Along',                                   'content' => null,                    'sort_order' => 4],
                            ['block_type' => 'video',   'title' => $lessonData['title'].' — Walkthrough Video',          'content' => null,                    'sort_order' => 5],
                            ['block_type' => 'steps',   'title' => null,                                                 'content' => $lessonData['steps'] ?? '', 'sort_order' => 6],
                            ['block_type' => 'tips',    'title' => null,                                                 'content' => $lessonData['tips'] ?? '',  'sort_order' => 7],
                        ];
                    }

                    foreach ($blocks as $block) {
                        LessonContentBlock::create([
                            'lesson_id'  => $lesson->id,
                            'block_type' => $block['block_type'],
                            'title'      => $block['title'],
                            'content'    => $block['content'],
                            'sort_order' => $block['sort_order'],
                        ]);
                    }
                }
            }
        }
    }
}
