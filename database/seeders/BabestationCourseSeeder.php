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

            // (2) About Babestation
            [
                'title'       => 'About Babestation',
                'description' => 'An introduction to what Babestation is and why it is one of the most lucrative cam platforms for performers.',
                'lessons'     => [
                    [
                        'title'      => 'About Babestation',
                        'overview'   => 'Babestation is a premium UK-based cam platform where clients come specifically to spend money on one-on-one private interaction. Unlike tip-based sites, you earn per minute in Group or Private shows, with multiple additional income streams running in the background — live chat, tips, toys, gallery sales, direct messages, and phone calls. This is where you stop working for the room and start working one-on-one, in your high-value era: less noise, better clients, bigger money.',
                        'intro_only' => true,
                    ],
                ],
            ],

            // (3) Login Overview
            [
                'title'       => 'Login Overview',
                'description' => 'Access the Babestation Cams performer dashboard for the first time.',
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
                ],
            ],

            // (4) Login Rules & Acknowledgement
            [
                'title'       => 'Login Rules & Acknowledgement',
                'description' => 'Understand and accept the platform rules before you start broadcasting.',
                'lessons'     => [
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
                ],
            ],

            // (5) Interface Overview
            [
                'title'       => 'Interface Overview',
                'description' => 'Get familiar with the full Camstation single-page dashboard layout.',
                'lessons'     => [
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

            // (6) Todays Estimated Earnings
            [
                'title'       => "Today's Estimated Earnings",
                'description' => 'Track how much you have earned today and understand what the figure means.',
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
                            'Slow days happen — peak hours and returning regulars make the biggest difference to your daily total',
                        ]),
                    ],
                ],
            ],

            // (7) Todays Top Spender
            [
                'title'       => "Today's Top Spender",
                'description' => 'Identify and engage your highest-spending client of the day.',
                'lessons'     => [
                    [
                        'title'    => "Today's Top Spender",
                        'overview' => "Today's Top Spender (marked with a whale emoji) shows the username of the client who has spent the most on you today. These are your most valuable clients — acknowledge them, give them attention, and build that connection. Your whale changes throughout the day as different clients spend more, so keep checking this widget while you are live.",
                        'steps'    => implode("\n", [
                            "Locate the Today's Top Spender box in the top bar — it shows your whale's username in real time",
                            'Note their username as soon as they appear as your top spender',
                            'Make a point of acknowledging and engaging them directly during your session',
                            'Check this widget regularly throughout your session as the top spender can change',
                        ]),
                        'tips'     => implode("\n", [
                            'Your whale changes throughout the day — keep checking and always acknowledge big spenders by name',
                            'Big spenders want to feel seen and valued — a personal mention from you can dramatically increase what they spend',
                            'Track your top spenders over time to identify your most reliable returning clients',
                        ]),
                    ],
                ],
            ],

            // (8) Daily Performer Ranking
            [
                'title'       => 'Daily Performer Ranking',
                'description' => 'Understand how your ranking is calculated and how to improve your visibility on the platform.',
                'lessons'     => [
                    [
                        'title'    => 'Daily Performer Ranking',
                        'overview' => 'The Daily Performer Ranking shows your current position among all active performers on Babestation that day. As you earn more, your ranking improves — a lower number means a higher position. A higher ranking means Babestation features your profile more prominently on the site, driving more organic traffic to your room without any extra effort from you.',
                        'steps'    => implode("\n", [
                            'Find your Daily Performer Ranking number in the top bar — lower is better, it means you are ranked higher',
                            'Note your ranking at the start and end of each session to track your consistency',
                            'Track your ranking across sessions to see how your hours and earnings affect your position',
                            'Use your ranking as a motivational target — aim to improve it each session',
                        ]),
                        'tips'     => implode("\n", [
                            'A better ranking means more visibility on the site, which means more clients discovering you organically',
                            'Consistent streaming hours and strong engagement are the fastest ways to improve your ranking over time',
                            'Top-ranked performers get more platform exposure — treat your ranking as free advertising for your room',
                        ]),
                    ],
                ],
            ],

            // (9) This Months Estimated Earnings
            [
                'title'       => "This Month's Estimated Earnings",
                'description' => 'Monitor your running monthly income total and stay on track for your financial goals.',
                'lessons'     => [
                    [
                        'title'    => "This Month's Estimated Earnings",
                        'overview' => "This Month's Estimated Earnings shows your running total for the current calendar month before the agency fee is deducted. Use this to gauge whether you are on track for your monthly income goal — payments go directly to your bank at month end. This is your big-picture number: your daily figure tells you how today went, your monthly figure tells you where you stand overall.",
                        'steps'    => implode("\n", [
                            "Locate This Month's Estimated Earnings in the top bar",
                            'Note this figure at the start of every session to know where you are in your monthly total',
                            'Compare this figure against your monthly income goal',
                            'If you are behind target, consider adding extra sessions or peak-hour availability this week',
                        ]),
                        'tips'     => implode("\n", [
                            'Divide your monthly target into weekly milestones to keep yourself motivated and on track',
                            'Your agency fee is deducted before payout — factor this into your target so there are no surprises',
                            'Babestation pays at month end — keep this number visible to avoid losing track of your income',
                        ]),
                    ],
                ],
            ],

            // (10) Favourites
            [
                'title'       => 'Favourites',
                'description' => 'Understand what Favourites mean and how to grow your loyal audience on Babestation.',
                'lessons'     => [
                    [
                        'title'    => 'Favourites',
                        'overview' => "Favourites shows how many clients have saved your profile — it works like a follow button. When a client favourites you, they get notified every time you go live. This means your Favourites count is directly tied to your consistent live traffic: every new favourite is a future viewer who will return. Growing this number is one of the most valuable long-term investments in your Babestation income.",
                        'steps'    => implode("\n", [
                            'Check your Favourites count in the top bar at the start of each session',
                            'Track your Favourites growth week over week to see if your content and engagement are working',
                            'Focus on delivering great private show experiences to encourage clients to favourite your profile',
                            'Mention to clients in free chat that they can favourite you to get notified when you are next live',
                        ]),
                        'tips'     => implode("\n", [
                            'Favourites are future income — every client who favourites you is likely to return and spend again',
                            'Clients who favourite your profile are notified when you go live — this drives free returning traffic to your show',
                            'The more favourites you have, the more stable and predictable your session traffic becomes over time',
                        ]),
                    ],
                ],
            ],

            // (11) What This Means for You
            [
                'title'       => 'What This Means for You',
                'description' => 'Understand how the dashboard metrics translate into real income strategy and decisions.',
                'lessons'     => [
                    [
                        'title'    => 'What This Means for You',
                        'overview' => "Now that you understand each metric in your top bar — Today's Earnings, Top Spender, Daily Ranking, Monthly Earnings, and Favourites — the key is knowing how to use this information to make decisions. These numbers are not just statistics: they are your business dashboard. They tell you whether today is a good day, whether your consistency is paying off, and whether your client relationships are deepening. A Boss Doll reads these numbers and acts on them.",
                        'steps'    => implode("\n", [
                            "Check Today's Estimated Earnings at the start and end of each session — is it meeting your daily target?",
                            "Look at Today's Top Spender to identify and engage your most valuable client in the room right now",
                            'Track your Daily Performer Ranking — are you moving up or down compared to your last session?',
                            "Monitor This Month's Estimated Earnings — are you ahead or behind your monthly income goal?",
                            'Watch your Favourites count grow — each one represents future live traffic and repeat income',
                        ]),
                        'tips'     => implode("\n", [
                            'The dashboard gives you real-time business intelligence — use it to make decisions, not just observations',
                            'If your ranking is dropping, increase your hours or improve your engagement in free chat',
                            'If your Favourites are not growing, focus on making your free chat experience memorable enough that clients want to return',
                        ]),
                    ],
                ],
            ],

            // (12) My Show
            [
                'title'       => 'My Show',
                'description' => 'Understand the My Show section — your live camera feed and broadcast status.',
                'lessons'     => [
                    [
                        'title'    => 'My Show',
                        'overview' => 'My Show is your main working area where your live camera feed appears in the centre of the Camstation interface. When the status shows green "Available" but "Not Broadcasting," you are visible on the platform and ready to receive calls but not yet streaming. This is your command centre — everything you do on Babestation happens through this section.',
                        'steps'    => implode("\n", [
                            'Check that your camera feed is displaying clearly in the My Show preview box',
                            'Verify that Current Show Status shows green "Available" before going live',
                            'Ensure your lighting and background look professional before starting your broadcast',
                            'Use the My Show preview to confirm your frame, angle, and appearance before clicking Go Live',
                        ]),
                        'tips'     => implode("\n", [
                            'Always check your camera preview in My Show before going live — never assume it looks fine',
                            'Good lighting is the single most impactful change you can make to your show quality',
                            'Your frame should be clean and professional — what clients see in the preview is what they pay for',
                        ]),
                    ],
                ],
            ],

            // (13) Video Settings
            [
                'title'       => 'Video Settings',
                'description' => 'Configure your webcam, microphone, and stream quality before going live.',
                'lessons'     => [
                    [
                        'title'    => 'Video Settings',
                        'overview' => 'Video Settings (purple button) lets you select your webcam, microphone, and stream quality — 1080p HD, 720p (recommended for most computers and mobiles), or SD for lower-bandwidth connections. This is where you fix any camera or audio issues before you go live. Always check these settings before your first session of the day.',
                        'steps'    => implode("\n", [
                            'Click the Video Settings button in the My Show section',
                            'Select your webcam from the Camera dropdown (e.g. SplitCam Video Driver)',
                            'Select your microphone from the Microphone dropdown',
                            'Choose 720p for the best balance of quality and performance on most setups',
                            'Click Clear Device Cache if you experience any connection or display issues',
                            'Close the settings panel and confirm your camera feed is displaying correctly',
                        ]),
                        'tips'     => implode("\n", [
                            '720p is the recommended quality setting for most desktops and mobiles — only use 1080p if your internet connection is consistently strong',
                            'If your image looks dark or grainy, fix your lighting before adjusting any camera settings',
                            'Always check video settings at the start of each session — hardware sometimes resets between sessions',
                        ]),
                    ],
                ],
            ],

            // (14) Show Settings
            [
                'title'       => 'Show Settings',
                'description' => 'Configure your show modes — Free, Group, Private, Lovense, Phone, and Second Camera.',
                'lessons'     => [
                    [
                        'title'    => 'Show Settings',
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
                ],
            ],

            // (15) Pricing
            [
                'title'       => 'Pricing',
                'description' => 'Set your per-minute rates for Group and Private shows.',
                'lessons'     => [
                    [
                        'title'    => 'Pricing',
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
                ],
            ],

            // (16) Go Live Button
            [
                'title'       => 'Go Live Button',
                'description' => 'Go live on Babestation and manage your active broadcast.',
                'lessons'     => [
                    [
                        'title'    => 'Go Live Button',
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

            // (17) Current Viewers
            [
                'title'       => 'Current Viewers',
                'description' => 'Monitor who is in your room right now across Free, Group, and Private shows.',
                'lessons'     => [
                    [
                        'title'    => 'Current Viewers',
                        'overview' => 'Current Viewers (yellow section, top right) shows who is in your room right now across Free, Group, and Private shows. This is your live audience at a glance — you can see usernames, which show mode they are in, and use this information to direct your attention and engagement in real time. Knowing who is in your room at every moment is essential for delivering a personal, high-converting performance.',
                        'steps'    => implode("\n", [
                            'Monitor the Current Viewers panel while streaming to know who is in your room at all times',
                            'Note who enters and exits — use their arrival as a natural moment to engage them directly',
                            'Identify viewers who have been in your room for a while without booking private — target them with engagement',
                            'Use names from the Current Viewers panel to personalise your performance',
                        ]),
                        'tips'     => implode("\n", [
                            'When a new viewer enters, acknowledge them — a simple greeting can be the difference between them staying or leaving',
                            'Viewers lingering in free chat without booking private are potential conversions — engage them and give them a reason to step up',
                            'Watch for your top spenders appearing in Current Viewers and prioritise their experience immediately',
                        ]),
                    ],
                ],
            ],

            // (18) Top Spenders
            [
                'title'       => 'Top Spenders',
                'description' => 'Identify your most valuable regular clients and build lasting relationships.',
                'lessons'     => [
                    [
                        'title'    => 'Top Spenders',
                        'overview' => "Your Top Spenders This Month (brown section) shows the clients who have spent the most on you during the current calendar month. These are your most valuable relationships — they have already proven they are willing to invest in you. Protect these relationships, give them VIP treatment, and make them feel uniquely valued. A loyal top spender can account for a significant portion of your monthly income.",
                        'steps'    => implode("\n", [
                            "Check Your Top Spenders This Month panel at the start of each session",
                            'Note if any of your top spenders are currently in your room (cross-reference with Current Viewers)',
                            'When a top spender enters your room, acknowledge them personally and warmly',
                            'Track changes in your top spenders list — a client dropping off the list is a signal to re-engage them',
                        ]),
                        'tips'     => implode("\n", [
                            'Top spenders are your repeat income — treat them like VIPs and they will keep coming back',
                            'Never ignore a top spender — they have chosen you specifically and they expect to feel that',
                            'A consistent relationship with even two or three high-spending regulars can make your monthly income far more predictable',
                        ]),
                    ],
                ],
            ],

            // (19) Todays Session Earnings
            [
                'title'       => "Today's Session Earnings",
                'description' => 'Review every session from today including earnings, duration, and show mode.',
                'lessons'     => [
                    [
                        'title'    => "Today's Session Earnings",
                        'overview' => "Today's Sessions with Earnings at the bottom of the Camstation page gives a full breakdown of every session you have had today: date, viewer, show mode, status, duration, and earnings per session. This is your performance data. It tells you which show modes are earning the most, which clients are spending the most in sessions, and how your time is being distributed across Free, Group, and Private shows.",
                        'steps'    => implode("\n", [
                            "Scroll to the bottom of the Camstation dashboard to find Today's Sessions with Earnings",
                            'Review each session line: note the show mode, duration, and earnings',
                            'Identify which show mode is generating the most income per session',
                            'Note which clients appear most frequently in your session history',
                            'Use this data to decide whether to adjust your show mode strategy for the next session',
                        ]),
                        'tips'     => implode("\n", [
                            'The session breakdown helps you identify your most profitable hours and show modes — use this data to optimise your schedule',
                            'If Group sessions are consistently outperforming Private, consider increasing your private rate',
                            'Track your average session duration — longer sessions generally mean stronger client engagement and higher per-session income',
                        ]),
                    ],
                ],
            ],

            // (20) View Your Earnings
            [
                'title'       => 'View Your Earnings',
                'description' => 'Access the full My Earnings section to review all your income streams.',
                'lessons'     => [
                    [
                        'title'    => 'View Your Earnings',
                        'overview' => "To view detailed earnings across any time period, click the money bag icon in the left-hand sidebar to open My Earnings. This section gives you a comprehensive view of all your income streams: Chat Earnings, Tip Earnings, Toy Earnings, Gallery Earnings, Direct Message Earnings, and Phone Call Earnings. This is your full financial picture — not just your live show income, but everything Babestation earns you.",
                        'steps'    => implode("\n", [
                            'Click the money bag icon in the left sidebar to open My Earnings',
                            'Set your "From" date using the date picker on the left',
                            'Set your "To" date for the end of the period you want to review',
                            'Click the green "View Earnings" button to load the results',
                            'Review your total earnings and each income stream listed below the total',
                        ]),
                        'tips'     => implode("\n", [
                            'Check your earnings daily so you are always aware of your income trajectory',
                            'My Earnings shows more detail than the dashboard — use it for your weekly and monthly reviews',
                            'Identify which income streams are performing best and focus on growing those strategically',
                        ]),
                    ],
                ],
            ],

            // (21) Filtering Your Earnings
            [
                'title'       => 'Filtering Your Earnings',
                'description' => 'Use date filters and the session breakdown to analyse your income patterns.',
                'lessons'     => [
                    [
                        'title'    => 'Filtering Your Earnings',
                        'overview' => "Filtering your earnings lets you drill into specific time periods to understand performance patterns. Set a date range and view your earnings broken down into every income stream: Total Earnings, Chat, Tips, Toys, Gallery, Direct Messages, and Phone Calls. Below that, My Sessions gives a full line-by-line breakdown of every individual session — rate, duration, and income — within your selected date range.",
                        'steps'    => implode("\n", [
                            'Open My Earnings from the left sidebar',
                            'Set a custom "From" and "To" date range to analyse a specific period',
                            'Click View Earnings to filter the results',
                            'Review each income stream total for the selected period',
                            'Scroll down to My Sessions for the full session-level breakdown',
                            'Compare different date ranges to identify your best-performing periods',
                        ]),
                        'tips'     => implode("\n", [
                            'Compare this week to last week to track whether your income is growing or needs attention',
                            'The session breakdown shows your rate and duration — use this data to optimise your show length and pricing strategy',
                            'A week where your gallery and DM earnings spike is a signal that your content is resonating — create more like it',
                        ]),
                    ],
                ],
            ],

            // (22) Profile Section
            [
                'title'       => 'Profile Section',
                'description' => 'Navigate to your profile and understand what makes a high-converting Babestation profile.',
                'lessons'     => [
                    [
                        'title'    => 'Profile Section',
                        'overview' => 'Your profile is your shop window on Babestation. It includes your Profile Image (main photo — high quality and eye-catching), Cam Window, Headshot, Turn-Ons, Availability, Preferred Age, and your About Me bio. Click your profile image in the top corner of Camstation to access the Profile section. Everything a client sees before they decide to enter your room starts here.',
                        'steps'    => implode("\n", [
                            'Click your profile image in the top corner of Camstation to access the Profile section',
                            'Review all the fields available in your profile: image, bio, turn-ons, availability, and preferred age',
                            'Upload a high-quality, professional Profile Image — this is the first thing clients see',
                            'Upload a Cam Window image (a shot of you at your streaming setup) and a Headshot',
                            'Set your Availability to accurately reflect when you are actually online',
                        ]),
                        'tips'     => implode("\n", [
                            'Your profile is the first impression — make it look professional and inviting',
                            'Clients browse profiles before deciding whose room to enter — treat your profile as your primary marketing tool',
                            'Keep your profile images updated — fresh photos signal that you are actively streaming',
                        ]),
                    ],
                ],
            ],

            // (23) Highlighted Areas
            [
                'title'       => 'Highlighted Areas',
                'description' => 'Understand the most impactful sections of your profile and how to optimise them.',
                'lessons'     => [
                    [
                        'title'    => 'Highlighted Areas',
                        'overview' => 'Within your profile, certain areas have the highest impact on whether a client chooses to enter your room. The profile image is the most important — it appears in search results and browsing lists. Your About Me bio is your chance to communicate your personality, show style, and what makes you unique. Turn-Ons and Cam Categories help Babestation match you with the right clients. Availability tells clients when to come back if you are offline.',
                        'steps'    => implode("\n", [
                            'Prioritise your Profile Image above everything else — make it striking, professional, and representative of your show',
                            'Write a compelling About Me bio that describes your personality, show style, and what clients can expect in private',
                            'Fill in your Turn-Ons — this guides the right clients toward your room',
                            'Set your Preferred Age to help attract the audience that spends the most with you',
                            'Set accurate Availability hours — clients plan their sessions around your schedule',
                            'Review all these sections together and ask: does this profile make someone want to book a private show?',
                        ]),
                        'tips'     => implode("\n", [
                            'Your bio is your biggest sales tool — write it like a premium advertisement, not a casual description',
                            'Clients read your bio before deciding to book — every sentence should make them more confident they want a private show with you',
                            'Review your completed profile from the perspective of a client — what would make you click into this room?',
                        ]),
                    ],
                ],
            ],

            // (24) Gallery Upload
            [
                'title'       => 'Gallery Upload',
                'description' => 'Navigate to your gallery section and begin adding public photos to your profile.',
                'lessons'     => [
                    [
                        'title'    => 'Gallery Upload',
                        'overview' => 'Your public gallery is your photo showcase on Babestation — it is what clients browse before deciding to enter your live room or book a private session. Click your profile image, then click Gallery in the side menu. Here you can see your existing gallery images and begin adding new ones. A well-populated gallery signals that you are active, professional, and worth the investment.',
                        'steps'    => implode("\n", [
                            'Click your profile image to enter the Profile section',
                            'Click "Gallery" in the left side menu',
                            'Review any existing gallery images already uploaded',
                            'Click the "Add New Images" button to begin uploading',
                            'Select high-quality photos that represent your show style and personality',
                        ]),
                        'tips'     => implode("\n", [
                            'Your gallery is how potential clients decide whether to book a private session — quality matters enormously',
                            'A gallery with many high-quality images signals professionalism and active engagement with the platform',
                            'Choose photos that tease your content without giving everything away — intrigue sells',
                        ]),
                    ],
                ],
            ],

            // (25) Uploading Your Content
            [
                'title'       => 'Uploading Your Content',
                'description' => 'Upload your photos to your gallery using the drag-and-drop or file browser.',
                'lessons'     => [
                    [
                        'title'    => 'Uploading Your Content',
                        'overview' => 'Once you are in the Gallery section, you can upload your photos using the drag-and-drop area or the file browser. Select your best, highest-quality images — these will represent you publicly on the Babestation platform. The upload process is straightforward: drag your files into the box or browse for them, then submit. Babestation will then review your content before it goes live.',
                        'steps'    => implode("\n", [
                            'Click "Add New Images" in the Gallery section',
                            'Drag and drop your selected photos into the upload box, or click Browse to navigate to your files',
                            'Select multiple images at once to upload your gallery in batches',
                            'Review the selected files before submitting — remove any that do not meet quality standards',
                            'Click Save to submit your images for review',
                        ]),
                        'tips'     => implode("\n", [
                            'Upload in batches to make the most of the review process — do not drip-feed one photo at a time',
                            'Use high-resolution photos — blurry or poorly lit images create a negative impression and may be rejected',
                            'Variety is important — upload a mix of professional headshots, setup shots, and show-style images',
                        ]),
                    ],
                ],
            ],

            // (26) Content Review
            [
                'title'       => 'Content Review',
                'description' => 'Understand the Babestation content review process and what to expect after uploading.',
                'lessons'     => [
                    [
                        'title'    => 'Content Review',
                        'overview' => 'After you upload photos to your gallery, Babestation reviews all content for compliance before it goes live publicly on your profile. This is a standard process on the platform — do not panic if your photos do not appear immediately. The review exists to ensure all content meets the platform\'s quality and compliance standards. Once approved, your gallery goes live automatically.',
                        'steps'    => implode("\n", [
                            'After uploading, note that your images will show as "pending review"',
                            'Allow time for Babestation to complete the review — do not re-upload the same images while waiting',
                            'Check your gallery after a few hours to see if your images have been approved and published',
                            'If an image is rejected, review the platform content guidelines to understand why',
                            'Make any necessary adjustments and re-upload compliant versions if needed',
                        ]),
                        'tips'     => implode("\n", [
                            'Do not upload the same photos repeatedly while waiting for review — this can slow down the process',
                            'If images are consistently being rejected, read the full content guidelines in the Support Centre',
                            'High-quality, professional content with clear lighting typically passes review fastest',
                        ]),
                    ],
                ],
            ],

            // (27) Private Gallery
            [
                'title'       => 'Private Gallery',
                'description' => 'Understand what Private Galleries are and how they generate passive income on Babestation.',
                'lessons'     => [
                    [
                        'title'    => 'Private Gallery',
                        'overview' => 'Private Galleries are locked photo and video sets that clients pay to unlock — passive income that earns even while you are offline. Unlike your public gallery (which is free to view), Private Galleries are behind a paywall that you set. Clients browse the teaser preview, decide they want the full set, and pay to unlock it. This income happens 24/7, with or without you being live.',
                        'steps'    => implode("\n", [
                            'Click your profile image to enter the Profile section',
                            'Click "Private Galleries" in the left side menu (the option with the lock icon)',
                            'Review any existing private galleries you have already created',
                            'Plan your next private gallery — decide on the content theme, collection size, and price point',
                        ]),
                        'tips'     => implode("\n", [
                            'Private galleries are passive income — set them up as a priority, before your first live session if possible',
                            'Think of each private gallery as a product — give it a compelling title and price it to reflect the quality of the content',
                            'A strong library of private galleries means you earn while you sleep, on holiday, and between live sessions',
                        ]),
                    ],
                ],
            ],

            // (28) Adding Private Gallery
            [
                'title'       => 'Adding Private Gallery',
                'description' => 'Create a new private gallery, upload your content, set a price, and submit for review.',
                'lessons'     => [
                    [
                        'title'    => 'Adding Private Gallery',
                        'overview' => 'To create a new Private Gallery, click the pink "Add Gallery" button in the Private Galleries section. Select the photos or videos you want to include, give the gallery a compelling title, set your price, and click Save. Babestation will review the gallery before it goes live. Once approved, clients can browse the teaser and pay to unlock the full set.',
                        'steps'    => implode("\n", [
                            'Click the pink "Add Gallery" button in the Private Galleries section',
                            'Select the photos or videos you want to include in this paid gallery',
                            'Write a gallery title that teases the content and entices buyers — make it intriguing',
                            'Set a price that reflects the quality and exclusivity of the content',
                            'Click Save to submit the gallery for review',
                            'Once approved, the gallery will appear as a paid unlock option on your profile page',
                        ]),
                        'tips'     => implode("\n", [
                            'Use teasing, high-quality content that makes clients want to unlock the full gallery — give them a taste, not the full thing',
                            'Price your galleries based on the quality and quantity of content — do not undersell exclusive sets',
                            'Create multiple galleries with different themes or price points to appeal to a range of client budgets',
                        ]),
                    ],
                ],
            ],

            // (29) Approval Process
            [
                'title'       => 'Approval Process',
                'description' => 'Understand how Babestation reviews and approves your content before publication.',
                'lessons'     => [
                    [
                        'title'    => 'Approval Process',
                        'overview' => 'All content on Babestation — public gallery images, private galleries, shorts, and profile photos — goes through a review and approval process before it is visible to clients. This process exists to ensure all content meets the platform\'s compliance and quality standards. Understanding how it works helps you plan your uploads strategically and avoid unnecessary delays.',
                        'steps'    => implode("\n", [
                            'Submit your content (gallery images, private galleries, or shorts) and wait for the review notification',
                            'Check your email or platform notifications for an approval or rejection update',
                            'If approved: your content goes live automatically and clients can view or purchase it',
                            'If rejected: read the rejection reason carefully, adjust your content to meet compliance requirements, and re-submit',
                            'Do not re-upload pending content repeatedly — wait for the review result before submitting again',
                        ]),
                        'tips'     => implode("\n", [
                            'Plan ahead — allow time for review when preparing for a launch or promotion',
                            'Content that clearly meets the guidelines is approved faster — quality, compliance, and clarity matter',
                            'If you are unsure whether content meets the guidelines, check the Support Centre before uploading',
                        ]),
                    ],
                ],
            ],

            // (30) Cam Category Settings
            [
                'title'       => 'Cam Category Settings',
                'description' => 'Set your cam categories to attract the right clients and improve your discoverability.',
                'lessons'     => [
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

            // (31) Notifications
            [
                'title'       => 'Notifications',
                'description' => 'Access and configure your notification settings on Babestation.',
                'lessons'     => [
                    [
                        'title'    => 'Notifications',
                        'overview' => 'Notifications keep you updated on everything happening on your Babestation account — new messages, content approvals, gallery unlocks, new favourites, and more. Click your profile image, then select Notifications in the side menu. For each type of event you can choose Site notifications, Push notifications, and Email notifications independently. Configuring this correctly means you never miss a paying client reaching out.',
                        'steps'    => implode("\n", [
                            'Click your profile image to enter the Profile section',
                            'Click "Notifications" in the left side menu',
                            'Review the full list of notification event types available',
                            'Understand the three notification channels: Site (in-platform), Push (browser/device), and Email',
                            'Decide which channel works best for you for each event type',
                        ]),
                        'tips'     => implode("\n", [
                            'Push notifications let you know when a client messages you even when you are away from your desk',
                            'At minimum, enable notifications for New Direct Message across all channels — a missed DM is missed income',
                            'Email notifications are best for events that are not time-sensitive, like content approval results',
                        ]),
                    ],
                ],
            ],

            // (32) What You'll Get Notified For
            [
                'title'       => "What You'll Get Notified For",
                'description' => 'Review the specific events that trigger notifications and configure them to match your workflow.',
                'lessons'     => [
                    [
                        'title'    => "What You'll Get Notified For",
                        'overview' => "The Notifications settings page lists every event you can receive alerts for. Key events include: New Direct Message (a client has paid to message you), Media Finished Processing (your upload is ready), Media Unlocked (a client has purchased your gallery), New Comments and Likes on Shorts, view milestones (100 views, 500 views, 1000 views on your content), and New Favourite (a client has favourited your profile).",
                        'steps'    => implode("\n", [
                            'Enable all channels for New Direct Message — never miss a paying client reaching out',
                            'Enable Media Unlocked notifications — these confirm someone has purchased your content',
                            'Enable New Favourite notifications to track audience growth in real time',
                            'Enable view milestone notifications — these are motivating signals your content is performing',
                            'Enable Media Finished Processing so you know when your uploads are live and ready',
                            'Click Save when you have configured all your notification preferences',
                        ]),
                        'tips'     => implode("\n", [
                            'A missed DM is a missed sale — always have New Direct Message notifications active across all channels',
                            'View milestone notifications (100 views, 500 views, 1000 views) are signals that your content is resonating — celebrate these and create more',
                            'New Favourite alerts help you track momentum — if you are gaining favourites fast, double down on what you are doing',
                        ]),
                    ],
                ],
            ],

            // (33) Important Rule
            [
                'title'       => 'Important Rule',
                'description' => 'A critical platform rule every Babestation performer must know and follow at all times.',
                'lessons'     => [
                    [
                        'title'    => 'Important Rule',
                        'overview' => 'One of the most important rules on Babestation: if another person appears on your camera during a live show, they must have been pre-verified by the Babestation admin team before appearing. This is non-negotiable. Broadcasting with an unverified person — even briefly — can result in an immediate account ban and loss of all your earnings. Always contact the admin team in advance if you plan to broadcast with anyone else.',
                        'steps'    => implode("\n", [
                            'Never allow another person to appear on camera during a live session without prior admin verification',
                            'If you plan to broadcast with another performer, contact Babestation admin before your session',
                            'Wait for written confirmation from the admin team that the other person is verified before going live together',
                            'If someone unexpectedly enters your space during a live show, move away from the camera or end the session immediately',
                            'Review the platform rules in the Support Centre to stay current on all compliance requirements',
                        ]),
                        'tips'     => implode("\n", [
                            'This rule protects the platform\'s legal compliance — violating it risks your entire account, not just a warning',
                            'When in doubt, do not appear with anyone on camera — it is never worth the risk to your earnings and account',
                            'Pre-verification takes time — always plan ahead if you want to broadcast with another performer',
                        ]),
                    ],
                ],
            ],

            // (34) Paid Messages
            [
                'title'       => 'Paid Messages',
                'description' => 'Set up paid direct messaging to earn between live sessions.',
                'lessons'     => [
                    [
                        'title'    => 'Paid Messages',
                        'overview' => 'Paid Messages let you charge clients to message you and unlock your content — income that runs in the background even when you are not live. In the left sidebar, click the messages/envelope icon to access Direct Messages, then click Settings. Here you can set your prices: what clients pay to send you a message, an image, or a video. This is a revenue stream that works around the clock.',
                        'steps'    => implode("\n", [
                            'Click the envelope/messages icon in the left sidebar to open Direct Messages',
                            'Click Settings in the top-right area of the Direct Messages page',
                            'Review the three price settings: Per Message, Per Image, and Per Video',
                            'Set your Price (£) Per Message The User Sends',
                            'Set your Price (£) Per Image The User Sends — a suggested starting point is £5',
                            'Set your Price (£) Per Video The User Sends',
                        ]),
                        'tips'     => implode("\n", [
                            'Paid DMs are one of your most consistent income streams — keep them active and priced properly at all times',
                            'Do not set your DM prices too low — you are providing value with every message and your time is worth charging for',
                            'Clients who pay to message you are already invested — they are warm leads for private shows and gallery unlocks',
                        ]),
                    ],
                ],
            ],

            // (35) How It Works
            [
                'title'       => 'How It Works',
                'description' => 'Understand how the Babestation paid messaging system works from a client perspective.',
                'lessons'     => [
                    [
                        'title'    => 'How It Works',
                        'overview' => 'When a client wants to message you on Babestation, they are prompted to pay your set price before the message is sent. They see your price, agree, pay, and their message arrives in your inbox — and you earn immediately. The same applies to images and videos they send. You also have the option to send them content from your side, charging for each piece of media you send.',
                        'steps'    => implode("\n", [
                            'Understand that clients see your DM price before they send a message — your pricing is transparent',
                            'When a client pays and sends a message, their payment is credited to your earnings immediately',
                            'Reply to messages promptly — fast responses encourage clients to keep the conversation going and spending more',
                            'Use the conversation to build rapport and guide clients toward private shows or private gallery unlocks',
                            'Write a short, inviting inbox bio to encourage clients to reach out — tell them what they can expect when they message you',
                        ]),
                        'tips'     => implode("\n", [
                            'Think of paid DMs as a bridge between your offline time and your live sessions — keep conversations going even when you are not streaming',
                            'Clients who are active in DMs are your most engaged audience — they are the most likely to book private shows',
                            'Use DMs to announce upcoming live sessions and drive viewers directly to your room when you go live',
                        ]),
                    ],
                ],
            ],

            // (36) Message Overview
            [
                'title'       => 'Message Overview',
                'description' => 'Navigate the Direct Messages section and use bulk messaging to drive traffic to your live shows.',
                'lessons'     => [
                    [
                        'title'    => 'Message Overview',
                        'overview' => 'The Direct Messages section shows all incoming paid DMs and gives you tools to manage your client conversations. At the top of the page you will see Upload Media from Mobile (to send content straight from your phone) and Bulk Messaging (to send a promotion or live session announcement to all your DM contacts at once). Bulk messaging is one of the most powerful free tools available to you on Babestation.',
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

            // (37) Whats Next
            [
                'title'       => "What's Next",
                'description' => 'Transition from messaging setup into the compliance and platform rules section.',
                'lessons'     => [
                    [
                        'title'    => "What's Next",
                        'overview' => "You have now set up the core income-generating features of your Babestation account: your profile, galleries, private galleries, cam categories, notifications, and paid messaging. What comes next is just as important — understanding the platform's compliance rules and content policies. Knowing what is and is not allowed protects your account, your earnings, and your reputation on the platform.",
                        'steps'    => implode("\n", [
                            'Review everything you have set up so far: profile, galleries, private galleries, notifications, and paid DMs',
                            'Confirm that your profile is complete and all content has been submitted for review',
                            'Make a note of any sections you want to revisit or improve',
                            'Prepare to move into the compliance section — understanding the rules is essential before you go live',
                        ]),
                        'tips'     => implode("\n", [
                            'The setup phase is an investment — everything you have configured will work for you passively from day one',
                            'The next section on compliance protects everything you have just built — do not skip it',
                            'Return to each setup section periodically to update your content, prices, and categories as you grow on the platform',
                        ]),
                    ],
                ],
            ],

            // (38) Short Upload & Compliance
            [
                'title'       => 'Short Upload & Compliance',
                'description' => 'Upload short teaser videos and understand the strict compliance rules that govern them.',
                'lessons'     => [
                    [
                        'title'    => 'Short Upload & Compliance',
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
                ],
            ],

            // (39) What is Allowed
            [
                'title'       => 'What is Allowed',
                'description' => 'Understand exactly what content is and is not permitted in Babestation shorts.',
                'lessons'     => [
                    [
                        'title'    => 'What is Allowed',
                        'overview' => "Understanding exactly what is and is not allowed in Babestation shorts protects your account from violations that could cost you your earnings. The rules are clear: implied nudity is allowed, explicit nudity is not. You can be suggestive, you can tease, and you can show off your personality — but the content must stay within the boundaries Babestation has set for the platform's public-facing spaces.",
                        'steps'    => implode("\n", [
                            'ALLOWED: Implied nudity — teasing angles, covered poses (hand bra, arms across chest)',
                            'ALLOWED: Lingerie and swimwear looks, suggestive camera angles, personality-led content',
                            'ALLOWED: Body movement and dance within compliance boundaries',
                            'NOT ALLOWED: Below-the-waist nudity or exposure of genitalia',
                            'NOT ALLOWED: Open-leg poses that expose intimate areas',
                            'NOT ALLOWED: Visible toys or adult props of any kind',
                            'RULE OF THUMB: If you cannot do it in free chat, you cannot do it in a short',
                        ]),
                        'tips'     => implode("\n", [
                            'When in doubt, err on the side of caution — a rejected short wastes time and delays your platform visibility',
                            'The best shorts are not the most explicit — they are the most intriguing and personality-driven',
                            'Read the full compliance reminder in the Shorts section every time you upload, especially after any platform rule updates',
                        ]),
                    ],
                ],
            ],

            // (40) Banned Users
            [
                'title'       => 'Banned Users',
                'description' => 'Manage your banned users list and maintain a positive, professional room environment.',
                'lessons'     => [
                    [
                        'title'    => 'Banned Users',
                        'overview' => "If a client is disrespectful, breaks your room rules, or makes you uncomfortable, you can ban them. Click the ban/no-entry icon in the left sidebar to open your Banned Users list. This shows each banned user's username, ban start and end date, the reason for the ban, and available actions. You are the boss of your room — never feel pressured to keep a viewer who is not serving you or your business.",
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
                ],
            ],

            // (41) Support Center
            [
                'title'       => 'Support Center',
                'description' => 'Access the Babestation Support Centre for guides, resources, and platform help.',
                'lessons'     => [
                    [
                        'title'    => 'Support Center',
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

            // (42) Platform Overview
            [
                'title'       => 'Platform Overview',
                'description' => 'A final review of the full Babestation platform and how all the pieces work together.',
                'lessons'     => [
                    [
                        'title'    => 'Platform Overview',
                        'overview' => 'You have now completed a full walkthrough of the Babestation Cams desktop platform. From login and interface to live shows, earnings tracking, profile building, gallery management, paid messaging, compliance, and room management — every piece is now in place. Babestation is a premium, multi-stream earning platform where consistency, professionalism, and engagement compound over time into serious income.',
                        'steps'    => implode("\n", [
                            'Confirm your login credentials are saved and your password is secure',
                            'Verify your profile is complete: profile image, bio, turn-ons, availability, and cam categories',
                            'Confirm your public gallery and at least one private gallery have been uploaded and submitted for review',
                            'Set your show mode toggles and pricing in Show Settings — Private enabled at £5/min minimum',
                            'Configure your paid DM pricing and inbox bio in Direct Messages Settings',
                            'Enable essential notifications: New DM, Media Unlocked, New Favourite',
                            'Upload at least one short teaser video within compliance guidelines',
                            'Familiarise yourself with the Banned Users panel and the Support Centre',
                        ]),
                        'tips'     => implode("\n", [
                            'The setup is done — now the work is consistency: regular streaming hours, fresh content, and engaged client relationships',
                            'Every piece you have set up (galleries, DMs, shorts) earns passively — the more you build, the more you earn without extra live hours',
                            'Review this course periodically — each section becomes more relevant as you gain real experience on the platform',
                        ]),
                    ],
                ],
            ],

            // (43) Outro
            [
                'title'       => 'Outro',
                'description' => 'Final wrap-up and send-off for your Babestation journey.',
                'lessons'     => [
                    [
                        'title'      => 'Outro',
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
                            ['block_type' => 'heading', 'title' => $lessonData['title'],                        'content' => null,                    'sort_order' => 1],
                            ['block_type' => 'text',    'title' => null,                                         'content' => $lessonData['overview'],  'sort_order' => 2],
                            ['block_type' => 'video',   'title' => $lessonData['title'].' — Presentation Video', 'content' => null,                    'sort_order' => 3],
                            ['block_type' => 'steps',   'title' => null,                                         'content' => implode("\n", [
                                'Watch the full presentation video above',
                                'Take notes on the key points that apply to your current situation',
                                'Return to this lesson any time you need a refresher',
                            ]), 'sort_order' => 4],
                            ['block_type' => 'tips',    'title' => null,                                         'content' => implode("\n", [
                                'Babestation is a premium platform — approach every session with a premium mindset',
                                'Less noise, better clients, bigger money: that is the Babestation difference',
                                'Multiple income streams running at once is what separates a Boss Doll from the average performer',
                            ]), 'sort_order' => 5],
                        ];
                    } else {
                        $blocks = [
                            ['block_type' => 'heading', 'title' => $lessonData['title'],                          'content' => null,                    'sort_order' => 1],
                            ['block_type' => 'text',    'title' => null,                                           'content' => $lessonData['overview'],  'sort_order' => 2],
                            ['block_type' => 'video',   'title' => $lessonData['title'].' — Presentation Video',   'content' => null,                    'sort_order' => 3],
                            ['block_type' => 'heading', 'title' => 'Now Follow Along',                             'content' => null,                    'sort_order' => 4],
                            ['block_type' => 'video',   'title' => $lessonData['title'].' — Walkthrough Video',    'content' => null,                    'sort_order' => 5],
                            ['block_type' => 'steps',   'title' => null,                                           'content' => $lessonData['steps'] ?? '', 'sort_order' => 6],
                            ['block_type' => 'tips',    'title' => null,                                           'content' => $lessonData['tips'] ?? '',  'sort_order' => 7],
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
