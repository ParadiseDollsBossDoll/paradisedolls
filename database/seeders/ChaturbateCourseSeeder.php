<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonContentBlock;
use Illuminate\Database\Seeder;

class ChaturbateCourseSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::query()->updateOrCreate(
            ['slug' => 'chaturbate-boss-doll-blueprint'],
            [
                'title'             => 'Chaturbate Boss Doll Blueprint',
                'platform_label'    => 'Chaturbate',
                'platform_color'    => '#FF8C00',
                'short_description' => 'A complete step-by-step guide to setting up, going live, and earning on Chaturbate.',
                'description'       => "Everything you need to start streaming on Chaturbate — from creating your account and configuring your profile, to understanding earnings, going live for the first time, and managing your stream like a pro. This course follows the Boss Doll Blueprint format: a presentation video explains every concept, then a screen-recorded walkthrough shows you exactly where to click.",
                'what_you_will_learn' => implode("\n", [
                    'Create and fully configure your Chaturbate account',
                    'Set up privacy, location blocking, and audience visibility',
                    'Understand Fan Club, private shows, tokens, and earnings tools',
                    'Prepare your camera, mic, and resolution for your first live',
                    'Manage chat, private messages, followers, and apps during a stream',
                    'Read your token stats and track your earnings',
                ]),
                'requirements' => implode("\n", [
                    'A device with a working camera and microphone',
                    'A stable internet connection',
                    'Government-issued ID for age verification on Chaturbate',
                ]),
                'is_published' => true,
                'sort_order'   => 10,
            ]
        );

        $modules = [

            // (1) Introduction
            [
                'title'       => 'Introduction',
                'description' => 'A warm welcome from your Boss Doll mentor before you dive into the platform.',
                'lessons'     => [
                    [
                        'title'      => 'Introduction',
                        'intro_only' => true,
                        'overview'   => 'Kayla introduces herself, explains the Boss Doll philosophy, and gives you a roadmap of what this course covers and how to get the most out of each lesson. By the end of this course you will have everything you need to set up your Chaturbate account, go live, and start earning with confidence.',
                        'steps'      => implode("\n", [
                            'Watch the full introduction before moving on',
                            'Note down any questions to bring to the community',
                            'Set a goal for what you want to achieve by the end of this course',
                        ]),
                        'tips'       => implode("\n", [
                            'There are no stupid questions — the community is here to support you',
                            'Work through each module in order for the best experience',
                            'Your support team is available any time you need help',
                        ]),
                    ],
                ],
            ],

            // (2) About Chaturbate
            [
                'title'       => 'About Chaturbate',
                'description' => 'An overview of what Chaturbate is, how it works, and why it is one of the top platforms for live-streaming models.',
                'lessons'     => [
                    [
                        'title'    => 'About Chaturbate',
                        'overview' => 'Chaturbate is one of the most popular live-streaming platforms for adult content creators in the world. It operates on a token-based tipping model where viewers purchase tokens and tip performers during live streams. Performers earn a percentage of every token received. Chaturbate also offers Fan Club subscriptions, private shows, spy mode, and a large app and bot ecosystem that makes streams interactive and engaging.',
                        'steps'    => implode("\n", [
                            'Visit chaturbate.com and explore the homepage as a viewer',
                            'Take note of how broadcaster rooms are listed and categorised',
                            'Understand the token economy — viewers buy tokens, performers earn from tips and private shows',
                            'Familiarise yourself with the platform before moving to account setup',
                        ]),
                        'tips'     => implode("\n", [
                            'Chaturbate is one of the highest-traffic adult platforms in the world — the audience is already there',
                            'Your success depends on consistency, engagement, and smart configuration — this course covers all of it',
                            'Never create a personal member account on the same device you use for broadcasting',
                        ]),
                    ],
                ],
            ],

            // (3) Logging in & Get Started
            [
                'title'       => 'Logging in & Get Started',
                'description' => 'How to log in to your Chaturbate account and navigate the broadcaster dashboard for the first time.',
                'lessons'     => [
                    [
                        'title'    => 'Logging in & Get Started',
                        'overview' => 'Your Chaturbate account has already been fully set up by the Paradise Dolls team. To get started, go to www.chaturbate.com and log in using your provided credentials. Once logged in you will land on your broadcaster dashboard — the central hub where you manage your profile, settings, earnings, and stream.',
                        'steps'    => implode("\n", [
                            'Open your browser and go to www.chaturbate.com',
                            'Click the Login button and enter your provided username and password',
                            'Once logged in, locate your broadcaster dashboard',
                            'Familiarise yourself with the main navigation: Home, Broadcast, Settings, My Profile, Token Stats',
                            'Do NOT create a new account — your account is already set up for you',
                        ]),
                        'tips'     => implode("\n", [
                            'Bookmark the login page for quick access before every session',
                            'Keep your login credentials saved in a secure password manager',
                            'Your login details were provided by the Paradise Dolls team — use those credentials only',
                        ]),
                    ],
                ],
            ],

            // (4) Login & Details
            [
                'title'       => 'Login & Details',
                'description' => 'Verify your account details and confirm your broadcaster settings are correctly configured.',
                'lessons'     => [
                    [
                        'title'    => 'Login & Details',
                        'overview' => 'Once logged in, it is important to verify that your account details are correctly set up. This includes confirming your email address is verified, checking that your account type is set to Broadcaster, and reviewing your basic account information. Your account was fully configured during onboarding, but this walkthrough shows you where everything lives.',
                        'steps'    => implode("\n", [
                            'After logging in, navigate to your account settings',
                            'Confirm your email address is verified — look for a verification badge or confirmation',
                            'Check that your account type is Broadcaster, not Member',
                            'Review your username and basic account details to ensure everything is correct',
                            'If anything is incorrect, contact the Paradise Dolls support team for help',
                        ]),
                        'tips'     => implode("\n", [
                            'If you do not receive a verification email, check your spam or junk folder',
                            'Keep your login session active on your dedicated work device only',
                            'Never share your account login details with anyone outside the Paradise Dolls team',
                        ]),
                    ],
                ],
            ],

            // (5) Bio Section
            [
                'title'       => 'Bio Section',
                'description' => 'Write a compelling broadcaster bio that attracts the right viewers and sets expectations.',
                'lessons'     => [
                    [
                        'title'    => 'Bio Section',
                        'overview' => 'Your bio is one of the first things viewers see when they visit your profile page. It should be short, friendly, and tell viewers exactly what to expect when they enter your room — your schedule, your show style, what tokens can unlock, and anything that makes you unique. A well-written bio turns profile visitors into live viewers.',
                        'steps'    => implode("\n", [
                            'Navigate to your broadcaster profile settings',
                            'Find the Bio or Profile Description field',
                            'Write a short, friendly bio that tells viewers what to expect in your room',
                            'Mention your schedule, what you enjoy streaming, and what tokens unlock',
                            'Use natural, conversational language — avoid sounding robotic or copied',
                            'Save your changes and preview your profile from the public viewer side',
                        ]),
                        'tips'     => implode("\n", [
                            'Keep your bio under 300 characters for readability — viewers scan, not read',
                            'Never include personal contact details, social media handles, or external URLs in your bio',
                            'Update your bio regularly to reflect your current show style and schedule',
                        ]),
                    ],
                ],
            ],

            // (6) Video Section
            [
                'title'       => 'Video Section',
                'description' => 'Upload your profile photo and preview video clips to make your profile stand out visually.',
                'lessons'     => [
                    [
                        'title'    => 'Video Section',
                        'overview' => 'Your profile photo and preview video clips are the visual first impression of your room. They appear in the model grid and search results, which is where most viewers decide whether to click into your stream. High-quality, well-lit images and compelling preview clips dramatically increase your click-through rate from the homepage.',
                        'steps'    => implode("\n", [
                            'Navigate to your profile settings and locate the Photos/Videos upload section',
                            'Upload a clear, high-quality profile photo that represents your brand and show style',
                            'Add at least one preview video clip to your profile',
                            'Ensure all media complies with Chaturbate content guidelines before uploading',
                            'After uploading, check how your profile looks from the public viewer side',
                        ]),
                        'tips'     => implode("\n", [
                            'Use a thumbnail that stands out in the model grid — good lighting and a clear face make the biggest difference',
                            'Profile photos and preview clips must comply with Chaturbate content guidelines',
                            'Update your profile media regularly to keep your profile looking fresh and active',
                        ]),
                    ],
                ],
            ],

            // (7) About Me
            [
                'title'       => 'About Me',
                'description' => 'Complete your extended About Me section with your schedule, tip menu, and show preferences.',
                'lessons'     => [
                    [
                        'title'    => 'About Me',
                        'overview' => 'The About Me section is your extended profile panel — it goes beyond the bio to give viewers a fuller picture of who you are, what you offer, and how to interact with you. This is where you list your streaming schedule, your tip menu, show preferences, and any additional information that helps viewers understand your room before they enter.',
                        'steps'    => implode("\n", [
                            'Navigate to your profile settings and find the About Me or extended profile section',
                            'Fill in your show preferences and streaming style',
                            'Add your weekly streaming schedule so viewers know when to expect you',
                            'Include your tip menu — list what each token amount unlocks',
                            'Keep your tip menu updated and aligned with what you are comfortable offering',
                            'Proofread everything before saving — viewers will read every word',
                        ]),
                        'tips'     => implode("\n", [
                            'A tip menu sets viewer expectations and dramatically reduces repetitive questions in chat',
                            'Keep token amounts round and easy to remember',
                            'Be honest about your schedule — viewers who show up when you are offline will not come back',
                        ]),
                    ],
                ],
            ],

            // (8) Settings & Privacy
            [
                'title'       => 'Settings & Privacy',
                'description' => 'Navigate the Chaturbate settings panel and understand the key privacy controls.',
                'lessons'     => [
                    [
                        'title'    => 'Settings & Privacy',
                        'overview' => 'The Chaturbate settings panel is where you control your account security, privacy options, and broadcasting preferences. This walkthrough gives you a full tour of the settings panel so you know where everything lives. It also includes an important reminder about password security and keeping your account safe.',
                        'steps'    => implode("\n", [
                            'Navigate to the Settings panel from your broadcaster dashboard',
                            'Review every section listed in the settings walkthrough',
                            'Enable two-factor authentication (2FA) immediately if it is not already active',
                            'Confirm your password is strong and unique to this account',
                            'Save your password in a secure password manager',
                        ]),
                        'tips'     => implode("\n", [
                            'Never share your password with anyone — including support staff',
                            'Enable 2FA before you upload any ID documents or payment details',
                            'A compromised account means lost earnings and lost access — security is non-negotiable',
                        ]),
                    ],
                ],
            ],

            // (9) Audience Visibility
            [
                'title'       => 'Audience Visibility',
                'description' => 'Control who can see your stream by configuring viewer restrictions and audience type settings.',
                'lessons'     => [
                    [
                        'title'    => 'Audience Visibility',
                        'overview' => 'Audience Visibility settings let you control exactly who can see your stream. You can restrict your room to registered members only, set viewer gender preferences, filter anonymous or guest viewers, and decide whether your stream is public, followers-only, or private. Getting these settings right ensures your room is seen by the right audience.',
                        'steps'    => implode("\n", [
                            'Navigate to the Audience Visibility section in your settings',
                            'Decide whether your room will be public, followers-only, or private',
                            'Set your audience gender preference if applicable to your show style',
                            'Review visitor type settings — consider filtering anonymous or non-registered viewers',
                            'Save your settings and confirm they apply correctly',
                        ]),
                        'tips'     => implode("\n", [
                            'Restricting anonymous viewers can improve the quality and engagement of your audience',
                            'You can adjust visibility settings mid-stream without ending your broadcast',
                            'Public rooms get the most traffic — start public and adjust as you learn your audience',
                        ]),
                    ],
                ],
            ],

            // (10) Room Rules
            [
                'title'       => 'Room Rules',
                'description' => 'Write and display room rules that set clear boundaries and communicate your expectations.',
                'lessons'     => [
                    [
                        'title'    => 'Room Rules',
                        'overview' => 'Room rules set the tone for your stream from the moment a viewer enters. Clear rules reduce bad behaviour, communicate your expectations, and protect your experience. Rules are displayed on your profile page and can also be set up to auto-post in chat at regular intervals, ensuring new viewers always see them.',
                        'steps'    => implode("\n", [
                            'Navigate to the Room Rules or Profile Description section in your settings',
                            'Write clear, numbered rules covering tipping etiquette, respect, and off-limits requests',
                            'Add your rules to your room description so they appear on your profile page',
                            'Set up auto-messages in your chat bot or apps to post rules at regular intervals',
                            'Keep rules short and scannable — viewers will not read a wall of text',
                        ]),
                        'tips'     => implode("\n", [
                            'Shorter numbered rules are easier for viewers to read and remember quickly',
                            'Never negotiate your rules — enforce them consistently from day one',
                            'Posting rules regularly in chat ensures even late arrivals know your expectations',
                        ]),
                    ],
                ],
            ],

            // (11) Location Blocking
            [
                'title'       => 'Location Blocking',
                'description' => 'Block specific countries or regions from seeing your stream to protect your privacy.',
                'lessons'     => [
                    [
                        'title'    => 'Location Blocking',
                        'overview' => 'Location Blocking (Geoblocking) lets you prevent viewers from specific countries or regions from seeing your stream. This is an important privacy tool — if you have concerns about people in your home country or region recognising you, blocking that location before your first stream is essential. It does not affect your earnings from other regions.',
                        'steps'    => implode("\n", [
                            'Navigate to the Geoblocking or Location Blocking section in your settings',
                            'Review the list of countries and identify any you need to block for privacy or safety',
                            'Block your home country or region if you have any privacy concerns',
                            'Save your settings and confirm the blocks are active before going live',
                        ]),
                        'tips'     => implode("\n", [
                            'Always set up location blocking before your very first stream — not after',
                            'Blocking countries does not affect your earnings from other regions',
                            'You can add or remove country blocks at any time from your settings',
                        ]),
                    ],
                ],
            ],

            // (12) Network Visibility
            [
                'title'       => 'Network Visibility',
                'description' => 'Control how your stream appears across third-party affiliate and embed sites.',
                'lessons'     => [
                    [
                        'title'    => 'Network Visibility',
                        'overview' => 'Chaturbate has an affiliate and network system that can embed your stream on third-party websites. Network Visibility settings let you control whether your stream appears on these external sites. Enabling network embedding can significantly increase your viewer count, but it also reduces your control over where you appear.',
                        'steps'    => implode("\n", [
                            'Navigate to the Network or Affiliate settings section',
                            'Review your current network embedding settings',
                            'Decide whether you want third-party sites to embed your stream',
                            'If privacy is a priority, turn off network embedding',
                            'If you want maximum reach, enable embedding and monitor where your stream appears',
                            'Save your settings',
                        ]),
                        'tips'     => implode("\n", [
                            'Network embedding can increase views significantly — but also reduces privacy control',
                            'Revisit this setting regularly as your comfort level and confidence grow',
                            'If you are new to streaming, start with embedding off and enable it once you are comfortable',
                        ]),
                    ],
                ],
            ],

            // (13) Satisfaction Score
            [
                'title'       => 'Satisfaction Score',
                'description' => 'Understand what the Chaturbate satisfaction score is and how it affects your ranking and visibility.',
                'lessons'     => [
                    [
                        'title'    => 'Satisfaction Score',
                        'overview' => 'The Chaturbate Satisfaction Score is a metric that reflects the quality of your private show experience for viewers. It affects your ranking in search results and on the platform homepage — a higher score means more visibility. Your score is calculated based on private show completion rates, viewer ratings, and other engagement signals.',
                        'steps'    => implode("\n", [
                            'Find your current Satisfaction Score in your broadcaster dashboard',
                            'Review what actions lower your score — primarily cancelling private shows',
                            'Review what actions increase your score — completing shows, positive viewer ratings',
                            'Focus on consistency — stream regularly and complete private shows to maintain a healthy score',
                        ]),
                        'tips'     => implode("\n", [
                            'Cancelling private shows is the fastest way to lower your satisfaction score',
                            'A high satisfaction score boosts your placement in search results and the homepage',
                            'Treat every private show as a VIP experience — the score reflects viewer satisfaction directly',
                        ]),
                    ],
                ],
            ],

            // (14) Fan Club
            [
                'title'       => 'Fan Club',
                'description' => 'Set up and configure your Chaturbate Fan Club to earn recurring monthly subscriptions.',
                'lessons'     => [
                    [
                        'title'    => 'Fan Club',
                        'overview' => 'The Chaturbate Fan Club is a monthly subscription system where viewers pay a recurring fee to become your Fan Club members. Members typically receive exclusive benefits such as private show discounts, access to exclusive content, a special badge in chat, and other perks you define. Fan Club is one of the most powerful recurring income streams on Chaturbate.',
                        'steps'    => implode("\n", [
                            'Navigate to the Fan Club settings in your broadcaster dashboard',
                            'Enable Fan Club if it is not already active',
                            'Set a monthly subscription price that reflects the value of your exclusive benefits',
                            'Write a clear Fan Club description explaining exactly what members receive',
                            'Decide what exclusive benefits you will offer: discounts, exclusive content, special access',
                            'Save your settings and promote your Fan Club during live streams',
                        ]),
                        'tips'     => implode("\n", [
                            'Start with a lower price and increase it as your fan base and reputation grow',
                            'Exclusivity is your selling point — make Fan Club access feel genuinely special',
                            'Mention your Fan Club regularly in chat and on your profile to drive subscriptions',
                        ]),
                    ],
                ],
            ],

            // (15) Private Show Settings
            [
                'title'       => 'Private Show Settings',
                'description' => 'Navigate and understand your private show configuration options.',
                'lessons'     => [
                    [
                        'title'    => 'Private Show Settings',
                        'overview' => 'Private Show Settings is the section in your broadcaster dashboard where you configure all aspects of your one-on-one paid sessions. This includes enabling or disabling private shows, setting your token-per-minute rate, configuring minimum session durations, managing spy mode, and controlling private show recording permissions. Understanding this section fully before going live is essential.',
                        'steps'    => implode("\n", [
                            'Navigate to the Private Show Settings section in your broadcaster dashboard',
                            'Review all available options: enable/disable, rate, minimum minutes, spy mode, recordings',
                            'Familiarise yourself with each setting before making any changes',
                            'Ensure private shows are enabled before going live',
                            'The following lessons will walk you through each individual setting in detail',
                        ]),
                        'tips'     => implode("\n", [
                            'Private shows are one of the highest-earning features on Chaturbate — configure them carefully',
                            'Know your settings inside out so you can adjust confidently mid-session if needed',
                            'Never feel pressured to accept a private show on terms you are not comfortable with',
                        ]),
                    ],
                ],
            ],

            // (16) Allow Private Shows
            [
                'title'       => 'Allow Private Shows',
                'description' => 'Enable private shows and understand who is permitted to request them.',
                'lessons'     => [
                    [
                        'title'    => 'Allow Private Shows',
                        'overview' => 'The Allow Private Shows setting controls whether viewers can request a one-on-one private session with you. When this is enabled, viewers will see an option to start a private show from your room. You can also configure who is allowed to request private shows — all viewers, followers only, Fan Club members only, or paid members only. This control lets you keep your private shows exclusive to your most engaged and committed audience.',
                        'steps'    => implode("\n", [
                            'In Private Show Settings, find the Allow Private Shows toggle',
                            'Enable private shows so viewers can request sessions',
                            'Review the viewer eligibility settings — who is allowed to request private shows',
                            'Set eligibility to your preference: all viewers, followers only, or paid members only',
                            'Save your settings',
                        ]),
                        'tips'     => implode("\n", [
                            'Restricting private shows to followers or Fan Club members increases their exclusivity and perceived value',
                            'You can always accept or decline individual private show requests regardless of your settings',
                            'Always have private shows enabled — they are a key income stream on Chaturbate',
                        ]),
                    ],
                ],
            ],

            // (17) Private Show Recordings
            [
                'title'       => 'Private Show Recordings',
                'description' => 'Understand and configure whether your private shows can be recorded by viewers.',
                'lessons'     => [
                    [
                        'title'    => 'Private Show Recordings',
                        'overview' => 'Chaturbate gives viewers the option to purchase recordings of private shows. The Private Show Recordings setting lets you control whether this option is available. Enabling recordings means viewers pay an additional fee to save their private session, which is an additional income stream for you. Disabling it gives you more control over how your content is distributed.',
                        'steps'    => implode("\n", [
                            'In Private Show Settings, find the Private Show Recordings option',
                            'Decide whether you want to allow viewers to purchase recordings of private shows',
                            'If enabling: understand that viewers will pay an additional fee for the recording',
                            'If disabling: your private show content stays within the live session only',
                            'Save your setting and confirm it matches your content comfort level',
                        ]),
                        'tips'     => implode("\n", [
                            'Enabling recordings is an additional passive income stream — viewers pay extra for the privilege',
                            'If you are not comfortable with recordings, disable this option — your comfort comes first',
                            'You can change this setting at any time from your broadcaster settings',
                        ]),
                    ],
                ],
            ],

            // (18) Tokens Per Minute
            [
                'title'       => 'Tokens Per Minute',
                'description' => 'Set your private show token-per-minute rate to reflect the value of your exclusive time.',
                'lessons'     => [
                    [
                        'title'    => 'Tokens Per Minute',
                        'overview' => 'Tokens Per Minute is the rate viewers pay for every minute of a private show with you. Setting this rate correctly is one of the most important decisions you will make — too low and you undervalue your time, too high and you may deter new viewers. The rate you set should reflect your experience level, the quality of your show, and what your audience is willing to pay.',
                        'steps'    => implode("\n", [
                            'In Private Show Settings, locate the Tokens Per Minute (or Private Show Rate) field',
                            'Consider your target hourly income and work backwards to set your token rate',
                            'Research comparable performers in your category to understand the market rate',
                            'Set your rate — recommended starting point for new performers is 30–60 tokens per minute',
                            'Save your rate before going live',
                        ]),
                        'tips'     => implode("\n", [
                            'Never go below your minimum comfortable rate — know your worth and hold to it',
                            'Increase your rate as your follower count, reputation, and confidence grow',
                            'A higher token rate signals premium value — serious viewers will pay for quality',
                        ]),
                    ],
                ],
            ],

            // (19) Minimum Minutes
            [
                'title'       => 'Minimum Minutes',
                'description' => 'Set a minimum private show duration to protect your time and filter low-intent requests.',
                'lessons'     => [
                    [
                        'title'    => 'Minimum Minutes',
                        'overview' => 'The Minimum Minutes setting requires viewers to commit to a minimum session length when requesting a private show. This filters out casual or low-intent requests and ensures that every private show you accept is worth your time. For example, setting a 5-minute minimum at 40 tokens per minute means the viewer must spend at least 200 tokens to book a session.',
                        'steps'    => implode("\n", [
                            'In Private Show Settings, locate the Minimum Minutes field',
                            'Set a minimum duration that reflects the value of your private show time',
                            'Recommended starting point: 5 minutes minimum for new performers',
                            'Calculate the minimum token commitment: minimum minutes × tokens per minute',
                            'Save your setting',
                        ]),
                        'tips'     => implode("\n", [
                            'Setting a minimum of 5–10 minutes filters out low-intent requests and time-wasters',
                            'A minimum commitment means every viewer who books a private show has genuine intent to spend',
                            'Increase your minimum as your confidence and rate grow — your time is valuable',
                        ]),
                    ],
                ],
            ],

            // (20) Spy Mode
            [
                'title'       => 'Spy Mode',
                'description' => 'Understand Spy Mode and decide whether to enable it to generate additional private show income.',
                'lessons'     => [
                    [
                        'title'    => 'Spy Mode',
                        'overview' => 'Spy Mode allows viewers who are not the primary private show buyer to watch your private session at a lower token-per-minute rate. The viewer who booked the private show gets one-on-one interaction with you while spy mode viewers watch silently without participating. Spy Mode is a way to earn from multiple viewers during what would otherwise be a single-viewer private session.',
                        'steps'    => implode("\n", [
                            'In Private Show Settings, find the Spy Mode option',
                            'Decide whether to enable Spy Mode for your private shows',
                            'If enabling: set a Spy Mode token-per-minute rate lower than your private show rate',
                            'If disabling: your private shows will be completely exclusive — one viewer only',
                            'Save your setting',
                            'Monitor how Spy Mode viewers interact and whether they convert to booking their own private shows',
                        ]),
                        'tips'     => implode("\n", [
                            'Spy Mode can act as a teaser that encourages watching viewers to book their own private show',
                            'Set your spy rate at roughly 30–50% of your private show rate',
                            'You can disable Spy Mode at any time without affecting your private show settings',
                        ]),
                    ],
                ],
            ],

            // (21) Next Step
            [
                'title'       => 'Next Step',
                'description' => 'A transition that bridges private show settings with your earnings and stats overview.',
                'lessons'     => [
                    [
                        'title'    => 'Next Step',
                        'overview' => 'You have now configured the full suite of private show settings — enable/disable, viewer eligibility, recordings, tokens per minute, minimum minutes, and spy mode. The next step is to understand your earnings dashboard so you can track how all these settings translate into income. Knowing your numbers is what separates a casual streamer from a Boss Doll.',
                        'steps'    => implode("\n", [
                            'Review your private show settings one final time to confirm everything is correctly configured',
                            'Check: Private shows enabled, rate set, minimum minutes set, spy mode decision made',
                            'Confirm recordings setting matches your content comfort level',
                            'Move on to the earnings and token stats section',
                        ]),
                        'tips'     => implode("\n", [
                            'Every setting you have just configured directly affects how much you earn per session — get them right now',
                            'You can update any of these settings at any time from your broadcaster dashboard',
                            'A strong configuration means you can focus on performing, not troubleshooting, when you go live',
                        ]),
                    ],
                ],
            ],

            // (22) Token Stats & Earnings
            [
                'title'       => 'Token Stats & Earnings',
                'description' => 'Navigate your earnings dashboard and understand how to track your token income over time.',
                'lessons'     => [
                    [
                        'title'    => 'Token Stats & Earnings',
                        'overview' => 'The Token Stats and Earnings section of your Chaturbate dashboard shows you a full breakdown of your income: tokens received from tips, private shows, spy mode, Fan Club subscriptions, and other sources. Understanding this data helps you identify which income streams are performing best and where to focus your energy to grow your earnings.',
                        'steps'    => implode("\n", [
                            'Navigate to your Token Stats page from the broadcaster dashboard',
                            'Review the data breakdown: tips, private shows, spy mode, Fan Club, and other sources',
                            'Note the minimum payout threshold and confirm your chosen payment method is set up',
                            'Check the payout schedule — Chaturbate pays on a set weekly cycle',
                            'Set up a personal spreadsheet or app to track your weekly earnings independently',
                        ]),
                        'tips'     => implode("\n", [
                            'Payouts are processed on a set weekly schedule — plan your finances around this',
                            'Always verify payout amounts yourself rather than relying solely on the platform display',
                            'Identify which income streams earn the most and invest more time growing those',
                        ]),
                    ],
                ],
            ],

            // (23) Satisfaction Score
            [
                'title'       => 'Satisfaction Score',
                'description' => 'Understand how your satisfaction score connects to your earnings and platform ranking.',
                'lessons'     => [
                    [
                        'title'    => 'Satisfaction Score',
                        'overview' => 'Your Satisfaction Score does not just affect your search ranking — it directly impacts the quality of traffic that comes to your room. A high score means Chaturbate\'s algorithm pushes your room to more viewers, which translates into more tips, more private show requests, and higher overall earnings. Maintaining a strong score is one of the most powerful long-term earnings strategies on the platform.',
                        'steps'    => implode("\n", [
                            'Review your current Satisfaction Score in your dashboard',
                            'Cross-reference your score with your recent Token Stats — note any correlations',
                            'Identify if any recent private show cancellations have impacted your score',
                            'Make a commitment to complete private shows once accepted — cancellations hurt your score significantly',
                            'Focus on viewer experience quality to earn positive ratings that push your score up',
                        ]),
                        'tips'     => implode("\n", [
                            'Your satisfaction score is a compounding asset — every positive session makes the next one more likely',
                            'Viewers who had a great private show experience are far more likely to return and spend again',
                            'A high score is free advertising — Chaturbate shows your room to more people automatically',
                        ]),
                    ],
                ],
            ],

            // (24) Accessing the Broadcast Interface
            [
                'title'       => 'Accessing the Broadcast Interface',
                'description' => 'Open the Chaturbate broadcaster panel and take a full tour of the interface.',
                'lessons'     => [
                    [
                        'title'    => 'Accessing the Broadcast Interface',
                        'overview' => 'The Chaturbate broadcast interface is where your live stream happens. It contains your camera feed, chat panel, token tracker, viewer count, tip goals, and all the controls you will use during every session. Before going live for the first time, it is essential to take a full tour of this interface so you know exactly where everything is when you need it.',
                        'steps'    => implode("\n", [
                            'Click the Broadcast button from your dashboard or navigate to chaturbate.com/broadcast',
                            'Allow browser permissions for camera and microphone when prompted',
                            'The broadcaster page loads — identify every panel shown in the walkthrough video',
                            'Locate the chat window on the right side of the screen',
                            'Locate the token tracker, viewer count, and tip goal controls',
                            'Find the Start Broadcast button and identify the settings cog for stream configuration',
                            'Do not click Start Broadcast yet — complete the remaining setup lessons first',
                        ]),
                        'tips'     => implode("\n", [
                            'Knowing the interface in advance means fewer distractions and fewer mistakes during your live stream',
                            'Keep the broadcaster page bookmarked for quick access before every session',
                            'Practise switching between panels and finding controls before your first public stream',
                        ]),
                    ],
                ],
            ],

            // (25) Age Verification
            [
                'title'       => 'Age Verification',
                'description' => 'Complete the Chaturbate ID verification process required before you can broadcast.',
                'lessons'     => [
                    [
                        'title'    => 'Age Verification',
                        'overview' => 'Before you can go live on Chaturbate, you must complete the age verification process by submitting a valid government-issued ID. This is a mandatory legal requirement. Chaturbate accepts passports, driver\'s licences, and national ID cards. Your ID is reviewed by the Chaturbate team and approval typically takes 24–72 hours.',
                        'steps'    => implode("\n", [
                            'Navigate to the ID verification section in your broadcaster settings',
                            'Prepare a clear, high-quality photo or scan of your accepted ID document',
                            'Ensure the photo is well-lit with all text on the ID clearly readable',
                            'Submit your ID through the verification portal',
                            'Check your email for a confirmation message — allow 24–72 hours for approval',
                            'Do not attempt to go live until you receive confirmation that your verification is approved',
                        ]),
                        'tips'     => implode("\n", [
                            'Make sure your photo is well-lit and all text is clearly readable — blurry or dark photos will be rejected',
                            'Do not submit an expired ID — it will be rejected and delay your approval',
                            'If you have not received a response after 72 hours, contact Chaturbate support',
                        ]),
                    ],
                ],
            ],

            // (26) Important Rule
            [
                'title'       => 'Important Rule',
                'description' => 'A critical Chaturbate platform rule every broadcaster must know before going live.',
                'lessons'     => [
                    [
                        'title'    => 'Important Rule',
                        'overview' => 'Before you go live on Chaturbate, there is an important platform rule you must understand: any person who appears on camera during your broadcast must be age-verified with Chaturbate before they can appear. This applies to guests, co-performers, and anyone else who enters your camera frame. Broadcasting with an unverified person — even briefly — is a serious violation that can result in an immediate account ban.',
                        'steps'    => implode("\n", [
                            'Never allow another person to appear on camera without prior Chaturbate age verification',
                            'If you plan to broadcast with a co-performer, both parties must be individually verified with Chaturbate',
                            'Review the full list of Chaturbate Terms of Service before going live for the first time',
                            'If someone unexpectedly enters your camera frame during a live session, move out of frame or end the stream immediately',
                            'When in doubt, stream alone until you fully understand the co-broadcasting requirements',
                        ]),
                        'tips'     => implode("\n", [
                            'This rule protects the platform\'s legal compliance — violations result in permanent bans with no appeal',
                            'Read the Chaturbate Terms of Service in full — ignorance is not accepted as an excuse',
                            'Your account represents real income — protect it by following the rules exactly',
                        ]),
                    ],
                ],
            ],

            // (27) Profile Visibility Room Title
            [
                'title'       => 'Profile Visibility Room Title',
                'description' => 'Configure your room title and visibility settings so you appear in the right categories.',
                'lessons'     => [
                    [
                        'title'    => 'Profile Visibility Room Title',
                        'overview' => 'Your room title and subject line are what viewers see in the Chaturbate model grid and search results. A compelling, accurate title drives click-throughs from the homepage. Alongside the title, your Profile Visibility settings control which categories your room appears in, what tags are associated with your stream, and how Chaturbate classifies your content.',
                        'steps'    => implode("\n", [
                            'Navigate to the broadcaster interface or profile settings',
                            'Write a room title that is descriptive, engaging, and within the character limit',
                            'Include a current activity, tip goal, or offer in your title to increase click-through rates',
                            'Set your room subject to reflect exactly what you are doing in the current stream',
                            'Choose the correct broadcast category for your content type',
                            'Add relevant tags to improve your discoverability in search',
                        ]),
                        'tips'     => implode("\n", [
                            'Update your room title regularly — it appears in Chaturbate search results and directly affects traffic',
                            'Including a tip goal or current activity in your title increases click-through rates significantly',
                            'Accurate category and tag selection gets you in front of the right audience',
                        ]),
                    ],
                ],
            ],

            // (28) Camera Resolution & Mic Setup
            [
                'title'       => 'Camera Resolution & Mic Setup',
                'description' => 'Select your camera and microphone, set your resolution, and test audio before going live.',
                'lessons'     => [
                    [
                        'title'    => 'Camera Resolution & Mic Setup',
                        'overview' => 'Before you click Start Broadcast, your camera, microphone, and stream resolution must be correctly configured. The wrong settings can cause a blurry stream, poor audio, or technical lag that drives viewers away before you even get started. This walkthrough covers exactly how to select the right devices and resolution for your setup.',
                        'steps'    => implode("\n", [
                            'Open the broadcaster page and allow camera and microphone permissions when prompted',
                            'Click the camera/settings icon to open device selection',
                            'Select your correct camera device from the dropdown',
                            'Select your microphone from the audio dropdown',
                            'Run the audio test and confirm your mic input is registering signal',
                            'Set video resolution — recommended: 720p if your upload speed is below 10 Mbps, 1080p if higher',
                            'Check your live preview to confirm the image is clear before going live',
                        ]),
                        'tips'     => implode("\n", [
                            'Always do a test in private mode before your first public stream to confirm everything looks and sounds right',
                            'Buffering and lag are usually caused by insufficient upload speed — check your connection first',
                            'Good lighting makes more difference to stream quality than camera resolution',
                        ]),
                    ],
                ],
            ],

            // (29) Going Live
            [
                'title'       => 'Going Live',
                'description' => 'Start your broadcast and go live on Chaturbate for the first time.',
                'lessons'     => [
                    [
                        'title'    => 'Going Live',
                        'overview' => 'This lesson walks you through the process of clicking Start Broadcast and going live on Chaturbate. Once you are live, your room appears in the Chaturbate model grid and viewers can start entering. You will learn what to expect in the first few minutes, how to stay calm as viewers arrive, and how to set the tone for a successful session.',
                        'steps'    => implode("\n", [
                            'Confirm all pre-stream checks are complete: camera, audio, room title, settings',
                            'Click the green Start Broadcast button',
                            'Confirm you are live — check that your room now appears in the model grid',
                            'Stay calm as the first viewers arrive — it is normal for the room to be quiet at first',
                            'Engage with every person who enters, even before they tip',
                            'Keep streaming — Chaturbate\'s algorithm rewards consistency and time online',
                        ]),
                        'tips'     => implode("\n", [
                            'Your first stream is about building confidence, not hitting earnings targets',
                            'Stay online for at least 90 minutes — Chaturbate rewards consistency in its algorithm',
                            'Engage with chat from the very start — empty-feeling rooms cause viewers to leave',
                        ]),
                    ],
                ],
            ],

            // (30) Before Going Live
            [
                'title'       => 'Before Going Live',
                'description' => 'Run through the complete pre-stream checklist before every session.',
                'lessons'     => [
                    [
                        'title'    => 'Before Going Live',
                        'overview' => 'Before you click Start Broadcast on any session, running through a pre-stream checklist ensures you go live looking and sounding your best, with all settings correctly configured. Skipping this step leads to technical issues mid-stream that interrupt your momentum and frustrate viewers. Make this checklist a non-negotiable habit before every single session.',
                        'steps'    => implode("\n", [
                            'Camera: confirm your camera feed is clear, well-lit, and framed correctly',
                            'Audio: confirm your microphone is on and your voice is clearly audible',
                            'Room title: update your room title to reflect what you are doing in this session',
                            'Settings: confirm your private show rate, minimum minutes, and spy mode are set correctly',
                            'Fan Club and apps: confirm any apps or bots you are running are active and configured',
                            'Location blocking: confirm your geoblocking settings are active',
                            'Perform a 60-second test in private mode before switching to public',
                        ]),
                        'tips'     => implode("\n", [
                            'The first 5 minutes of a stream are the most important — viewers decide whether to stay based on those moments',
                            'A consistent pre-stream routine makes going live feel natural and confident every time',
                            'Never rush to go live — 5 minutes of preparation prevents 30 minutes of technical problems',
                        ]),
                    ],
                ],
            ],

            // (31) Chat Interface Overview
            [
                'title'       => 'Chat Interface Overview',
                'description' => 'A full tour of the broadcaster chat panel and how to use moderator tools during a live stream.',
                'lessons'     => [
                    [
                        'title'    => 'Chat Interface Overview',
                        'overview' => 'The chat interface is the primary way you communicate with viewers during your stream. Understanding all the tools available to you in the chat panel — including moderator controls, tipping notifications, pinned messages, and viewer information — is essential for running a smooth, professional, and engaging live session.',
                        'steps'    => implode("\n", [
                            'Identify every element of the chat panel shown in the walkthrough video',
                            'Locate the message input box, the emoji picker, and the send button',
                            'Find the moderator controls: mute, silence, timeout, and ban options',
                            'Identify the tip notification area and how incoming tips appear in chat',
                            'Locate the pinned message tool for posting rules or announcements',
                        ]),
                        'tips'     => implode("\n", [
                            'Knowing the chat interface before going live means fewer distractions and faster responses during a busy stream',
                            'A good moderator handles problem viewers so you can stay focused on performing',
                            'Never engage with trolls in public chat — silence or ban immediately and move on',
                        ]),
                    ],
                ],
            ],

            // (32) Chat Box Overview
            [
                'title'       => 'Chat Box Overview',
                'description' => 'Understand the full layout and functionality of the Chaturbate chat box during a live session.',
                'lessons'     => [
                    [
                        'title'    => 'Chat Box Overview',
                        'overview' => 'The Chaturbate chat box is your live communication channel with viewers during a broadcast. It displays viewer messages, tip notifications, system messages, and viewer activity in real time. Understanding how the chat box is laid out — and how different types of messages appear — helps you stay on top of what is happening in your room without getting overwhelmed.',
                        'steps'    => implode("\n", [
                            'During a live session or test stream, open and review the full chat box panel',
                            'Identify how regular viewer messages appear versus tip notifications',
                            'Note how system messages (viewer enters, viewer leaves) appear in the feed',
                            'Identify the colour-coding or formatting differences between message types',
                            'Locate the scroll controls and how to keep up with a fast-moving chat',
                        ]),
                        'tips'     => implode("\n", [
                            'During busy streams the chat can move fast — focus on tip notifications and new viewer arrivals first',
                            'Use a moderator to help manage chat during high-traffic sessions',
                            'Acknowledge tips verbally as they appear — viewers who feel seen tip more',
                        ]),
                    ],
                ],
            ],

            // (33) What's Inside the Chatbox
            [
                'title'       => "What's Inside the Chatbox",
                'description' => 'Explore every tool and feature available inside the Chaturbate chatbox.',
                'lessons'     => [
                    [
                        'title'    => "What's Inside the Chatbox",
                        'overview' => "Inside the Chaturbate chatbox there are more tools than just a text input field. You have access to emojis, shortcut commands, menu options for moderating individual users, and controls for managing tip goals and other interactive features. This lesson walks you through everything that lives inside the chatbox so nothing catches you off guard during a live session.",
                        'steps'    => implode("\n", [
                            'Open the chatbox and click on a message or username to see moderation options',
                            'Explore the emoji picker and identify any quick-access shortcuts',
                            'Find the commands or shortcut menu within the chatbox if available',
                            'Understand how to respond directly to a specific viewer message',
                            'Test the moderation controls: silence, timeout, and ban on a test account',
                        ]),
                        'tips'     => implode("\n", [
                            'Familiarity with every chatbox tool means you can moderate confidently without breaking your stream flow',
                            'Using emojis and expressive language in chat keeps the energy high and viewers engaged',
                            'Quick, decisive moderation of problem viewers protects the experience for everyone else in the room',
                        ]),
                    ],
                ],
            ],

            // (34) Public Chat
            [
                'title'       => 'Public Chat',
                'description' => 'Strategies for keeping your public chat engaged and converting viewers into tippers.',
                'lessons'     => [
                    [
                        'title'    => 'Public Chat',
                        'overview' => 'Managing public chat effectively is one of the most important skills for a successful live streamer. Keeping the energy high, acknowledging tippers, engaging lurkers, and maintaining a positive room atmosphere are what separate rooms with high earnings from rooms that stagnate. This lesson covers the key strategies for managing public chat like a pro.',
                        'steps'    => implode("\n", [
                            'Acknowledge every tip by name, no matter how small — "Thank you [username]!" goes a long way',
                            'Ask open questions to keep conversation flowing during quiet periods',
                            'Use chat to drive your tip goals — remind viewers what they are working toward',
                            'Respond to regular viewers by name — making them feel known encourages repeat visits',
                            'Use energy and enthusiasm even when the room is quiet — your vibe sets the room\'s tone',
                        ]),
                        'tips'     => implode("\n", [
                            'Viewers who feel seen and acknowledged are far more likely to tip again and return',
                            'Have a bank of 5–10 conversation starters ready so you never run dry during quiet moments',
                            'Keep energy levels up consistently — viewers leave quiet, low-energy rooms quickly',
                        ]),
                    ],
                ],
            ],

            // (35) Important to Remember
            [
                'title'       => 'Important to Remember',
                'description' => 'Key reminders about managing your chat and protecting your room environment.',
                'lessons'     => [
                    [
                        'title'    => 'Important to Remember',
                        'overview' => 'There are a few critical things to keep in mind when managing your chat that will protect your room, your account, and your income. These are not optional — they are non-negotiable practices that every Boss Doll follows to maintain a professional, safe, and profitable streaming environment.',
                        'steps'    => implode("\n", [
                            'Never agree to off-platform contact in public or private chat — no social handles, no personal details',
                            'Never negotiate your room rules with viewers — enforce them consistently every time',
                            'Silence or ban problem viewers immediately — do not engage or argue',
                            'Never perform content you are not comfortable with regardless of the token amount being offered',
                            'If something feels wrong, end the session — your safety and comfort are non-negotiable',
                        ]),
                        'tips'     => implode("\n", [
                            'Your boundaries protect your business — viewers who do not respect them are not customers worth keeping',
                            'A viewer who pushes your boundaries is never going to be a loyal, respectful regular',
                            'Act decisively and without guilt — the right audience will respect and reward clear standards',
                        ]),
                    ],
                ],
            ],

            // (36) Private Message
            [
                'title'       => 'Private Message',
                'description' => 'Handle private messages during a live stream efficiently and use them to convert viewers into private show bookings.',
                'lessons'     => [
                    [
                        'title'    => 'Private Message',
                        'overview' => 'Private messages arrive from viewers during your live stream and can be a valuable sales channel — a viewer who messages you privately is often close to booking a private show. The key is responding efficiently without losing your public chat momentum, setting clear expectations, and guiding serious inquiries toward a paid private session.',
                        'steps'    => implode("\n", [
                            'Check private messages between public chat interactions — not mid-sentence or mid-performance',
                            'Keep DM responses short, warm, and action-oriented',
                            'Redirect serious inquiries toward booking a private show',
                            'Never agree to off-platform contact, personal details, or unpaid requests via DM',
                            'If a DM takes too long to respond to mid-stream, let the viewer know you will reply shortly',
                        ]),
                        'tips'     => implode("\n", [
                            'Private messages are a sales channel — treat every DM as a potential private show booking',
                            'Do not leave DMs unread for long during a stream — a viewer ready to book will not wait',
                            'Keep responses concise — long back-and-forth in DMs pulls you away from your public audience',
                        ]),
                    ],
                ],
            ],

            // (37) How Messaging Works
            [
                'title'       => 'How Messaging Works',
                'description' => 'Understand the mechanics of Chaturbate\'s messaging system from both the viewer and broadcaster perspective.',
                'lessons'     => [
                    [
                        'title'    => 'How Messaging Works',
                        'overview' => 'Understanding how the Chaturbate messaging system works from both sides helps you manage it more effectively. Viewers can send you private messages from your profile page or from within your room. You receive these in your Messages inbox. Some message types may require the viewer to have a certain account standing or token balance, which naturally filters out lower-quality contacts.',
                        'steps'    => implode("\n", [
                            'Navigate to your Messages inbox from the broadcaster dashboard',
                            'Review any existing messages and understand how they are organised',
                            'Understand which types of viewers can send you private messages',
                            'Know the difference between messages received during a live session and messages sent to your profile',
                            'Set up a response habit — check messages at the start of every session and immediately after going offline',
                        ]),
                        'tips'     => implode("\n", [
                            'Responding to messages promptly — even with a short reply — builds a reputation as an engaged, accessible broadcaster',
                            'Messages sent after a session are often from viewers who wanted to book private but did not get the chance — follow up',
                            'Keep your message responses professional and aligned with your brand',
                        ]),
                    ],
                ],
            ],

            // (38) Key Tip
            [
                'title'       => 'Key Tip',
                'description' => 'A key piece of advice that will significantly improve your messaging and communication strategy.',
                'lessons'     => [
                    [
                        'title'    => 'Key Tip',
                        'overview' => 'One of the most powerful things you can do as a Chaturbate broadcaster is to respond to every message — even briefly. Viewers who feel ignored do not come back. Viewers who feel acknowledged, seen, and valued become regulars. This single habit — responding promptly and personally to every private message — compounds over time into a loyal base of returning, high-spending viewers.',
                        'steps'    => implode("\n", [
                            'Make it a personal rule to respond to every private message you receive',
                            'Even a short reply ("Thanks for stopping by — hope to see you in my next stream!") makes a lasting impression',
                            'Check messages immediately after every session before logging off',
                            'For viewers who expressed interest in a private show, follow up with a warm, direct invite',
                        ]),
                        'tips'     => implode("\n", [
                            'The broadcaster who responds always wins — most others do not bother',
                            'A single follow-up message after a session can convert a lurker into a paying regular',
                            'Keep follow-up messages short, warm, and focused on getting them back to your room',
                        ]),
                    ],
                ],
            ],

            // (39) What You Can See
            [
                'title'       => 'What You Can See',
                'description' => 'Understand what information is visible to you as a broadcaster during a live session.',
                'lessons'     => [
                    [
                        'title'    => 'What You Can See',
                        'overview' => 'As a broadcaster on Chaturbate, you have access to a range of real-time information during your live session that viewers cannot see. This includes a full list of everyone in your room, their token balances (for tipping ability), whether they are followers or Fan Club members, and individual viewer actions. Understanding what data is available to you lets you make smarter, more targeted engagement decisions.',
                        'steps'    => implode("\n", [
                            'During a live session, locate the viewer list on your broadcaster interface',
                            'Identify the information visible per viewer: username, follower status, Fan Club status, token level',
                            'Use this information to prioritise engagement — focus on high-token or repeat viewers',
                            'Identify which viewers are likely to tip based on their history and token balance',
                            'Use the viewer data to personalise interactions and increase conversion to private shows',
                        ]),
                        'tips'     => implode("\n", [
                            'Viewers with tokens are actively considering spending — engage them directly and warmly',
                            'Fan Club members in your room are already invested in you — give them a moment of recognition',
                            'The more you know about who is in your room, the more targeted and effective your engagement becomes',
                        ]),
                    ],
                ],
            ],

            // (40) Followers
            [
                'title'       => 'Followers',
                'description' => 'Understand the Chaturbate follower system and what it means to have a growing follower base.',
                'lessons'     => [
                    [
                        'title'    => 'Followers',
                        'overview' => 'Followers on Chaturbate are viewers who have clicked the Follow button on your profile. When you go live, Chaturbate can notify your followers — bringing them directly back to your room. A growing follower base is one of the most valuable long-term assets you can build on the platform because it means you start every session with a ready-made audience instead of relying entirely on discovery traffic.',
                        'steps'    => implode("\n", [
                            'Navigate to your profile or dashboard and check your current follower count',
                            'Understand the difference between a follower and a Fan Club member',
                            'Review your recent follower growth to understand which sessions are converting the most new followers',
                            'Actively encourage viewers to follow you during every live session',
                        ]),
                        'tips'     => implode("\n", [
                            'Followers who get notified when you go live are significantly more likely to return and tip',
                            'Consistency in your streaming schedule makes your follower base more valuable — they know when to show up',
                            'Every new follower is a potential future regular — treat them accordingly from the first interaction',
                        ]),
                    ],
                ],
            ],

            // (41) Announce Your Online
            [
                'title'       => 'Announce Your Online',
                'description' => 'Use the Announce Online feature to notify your followers every time you go live.',
                'lessons'     => [
                    [
                        'title'    => 'Announce Your Online',
                        'overview' => 'The Announce Online feature sends a notification to all your followers the moment you start a broadcast. This is one of the simplest and most effective ways to drive immediate traffic to your room at the start of every session. Ensuring this feature is enabled and correctly configured means you never go live to an empty room unnecessarily.',
                        'steps'    => implode("\n", [
                            'Navigate to your broadcaster settings and find the Announce Online or Follower Notifications option',
                            'Ensure the Announce Online toggle is enabled',
                            'Confirm that your follower notifications are set to send when you start a broadcast',
                            'Check that your followers will receive the notification via their preferred notification channel',
                            'Enable this feature before going live for your first session',
                        ]),
                        'tips'     => implode("\n", [
                            'Followers who are notified when you go live are significantly more likely to return and tip',
                            'The first 10–15 minutes of your stream see the biggest benefit from the announce notification',
                            'Keep your stream schedule consistent — followers who expect you at a certain time are more likely to show up',
                        ]),
                    ],
                ],
            ],

            // (42) Why This Matters
            [
                'title'       => 'Why This Matters',
                'description' => 'Understand why building your follower base and announcing online are essential to long-term income growth.',
                'lessons'     => [
                    [
                        'title'    => 'Why This Matters',
                        'overview' => 'Growing your follower base and consistently announcing when you go live are not just nice-to-haves — they are the foundation of long-term income growth on Chaturbate. The difference between a broadcaster who earns unpredictably and one who earns consistently is almost always the size and engagement of their follower base. This is the audience you build over time — and it compounds.',
                        'steps'    => implode("\n", [
                            'Understand that your follower base is your most valuable long-term asset on Chaturbate',
                            'Commit to actively growing followers every session: mention it in chat, thank new followers by name',
                            'Ensure Announce Online is always enabled before every stream',
                            'Set a follower growth milestone — e.g. 100 followers, 500 followers, 1000 followers — and work toward it',
                        ]),
                        'tips'     => implode("\n", [
                            'Every new follower is a step toward more predictable, consistent income — growth compounds over time',
                            'Broadcasters with large follower bases are insulated from discovery algorithm changes',
                            'A session where you gain 20 new followers is as valuable as a session where you earn a high tip total — both are assets',
                        ]),
                    ],
                ],
            ],

            // (43) Grow Your Followers
            [
                'title'       => 'Grow Your Followers',
                'description' => 'Strategies and tactics to actively grow your Chaturbate follower count every session.',
                'lessons'     => [
                    [
                        'title'    => 'Grow Your Followers',
                        'overview' => 'Growing your follower base is an active strategy, not a passive one. The broadcasters who grow the fastest are the ones who consistently ask for follows, reward followers, and create a stream environment where viewers feel motivated to hit that Follow button. This lesson covers proven tactics to grow your followers every single session.',
                        'steps'    => implode("\n", [
                            'Verbally ask viewers to follow your room at the start and end of every session',
                            'Thank new followers by name when they appear in chat — make following feel rewarding',
                            'Run a follower goal using an app or countdown: "When we hit 500 followers I will..."',
                            'Offer Fan Club members or followers exclusive perks to make the follow feel valuable',
                            'Maintain a consistent streaming schedule so followers know when to come back',
                        ]),
                        'tips'     => implode("\n", [
                            'Consistency in your schedule is more important than streaming more hours — followers need to know when to find you',
                            'Viewers who follow you after a great session are already warm leads for future tips and private shows',
                            'Running a follower countdown goal turns growing your audience into an interactive, tipping-driven activity',
                        ]),
                    ],
                ],
            ],

            // (44) Apps & Tools
            [
                'title'       => 'Apps & Tools',
                'description' => 'An introduction to the Chaturbate apps and bots ecosystem and how to use it effectively.',
                'lessons'     => [
                    [
                        'title'    => 'Apps & Tools',
                        'overview' => 'Chaturbate has a large library of apps and bots that make your stream more interactive, more engaging, and more profitable. From tip-activated games and countdown goals to automated tip menus and follower trackers, the right combination of apps can transform a passive stream into an active, tipping-driven experience that keeps viewers engaged for longer.',
                        'steps'    => implode("\n", [
                            'Navigate to the Chaturbate App Store from your broadcaster interface',
                            'Browse the app categories: tip menus, countdown goals, games, follower trackers, and more',
                            'Identify 2–3 apps that suit your show style and streaming goals',
                            'Read reviews and usage notes for each app before installing',
                            'Install your chosen apps and configure them in the settings panel',
                            'Test each app in private mode before running it in a public stream',
                        ]),
                        'tips'     => implode("\n", [
                            'Interactive apps and countdown goals create urgency and encourage group tipping behaviour',
                            'Do not run too many apps at once — it clutters the chat and confuses viewers',
                            'Start with one core app (e.g. tip menu) and add more as you get comfortable',
                        ]),
                    ],
                ],
            ],

            // (45) What are Apps
            [
                'title'       => 'What are Apps',
                'description' => 'Understand what Chaturbate apps are, how they work, and which types are available.',
                'lessons'     => [
                    [
                        'title'    => 'What are Apps',
                        'overview' => 'Chaturbate apps are third-party scripts and programs that run inside your broadcaster interface and add interactive features to your stream. They are written by developers and approved by Chaturbate, and they range from simple tip menus to complex tip-activated games, goal trackers, and automated moderators. Apps are how the most successful Chaturbate broadcasters create structured, engaging, and highly tipping-driven shows.',
                        'steps'    => implode("\n", [
                            'Understand that apps run inside your broadcaster panel — they do not require separate software',
                            'Browse the app types: tip menus, countdown goals, dice games, wheel spinners, follower goals, moderator bots',
                            'Identify which types of apps align with your show style',
                            'Read the description and configuration options for each app before installing',
                            'Start with a tip menu app — it is the single most useful app for any new broadcaster',
                        ]),
                        'tips'     => implode("\n", [
                            'A tip menu app eliminates repetitive "what do tokens unlock?" questions in chat',
                            'Goal-based apps create community participation — everyone in the room works toward the same reward',
                            'Check app reviews and usage counts before installing — popular apps with high ratings are the safest choice',
                        ]),
                    ],
                ],
            ],

            // (46) Important to Know
            [
                'title'       => 'Important to Know',
                'description' => 'Key things to know about using apps and tools on Chaturbate before running them live.',
                'lessons'     => [
                    [
                        'title'    => 'Important to Know',
                        'overview' => 'Before running apps in a live public stream, there are several important things to understand. Apps can sometimes conflict with each other, send unexpected messages in chat, or behave differently than expected. Testing everything in private mode first is non-negotiable. You should also understand how to remove or pause an app mid-stream without disrupting your broadcast.',
                        'steps'    => implode("\n", [
                            'Always test any new app in private mode before running it in a public session',
                            'Run apps one at a time when starting out — only combine multiple apps once you understand how each behaves',
                            'Know how to stop or pause an app mid-stream: find the app management panel in your broadcaster interface',
                            'Monitor chat after launching an app to confirm it is behaving correctly',
                            'Remove any app that sends unexpected or inappropriate auto-messages immediately',
                        ]),
                        'tips'     => implode("\n", [
                            'An app that floods chat with auto-messages is more harmful than helpful — configure message frequency carefully',
                            'If an app causes confusion or complaints in chat, pause it and review the settings before re-enabling',
                            'The best-configured apps run invisibly in the background while boosting tips and engagement',
                        ]),
                    ],
                ],
            ],

            // (47) You're Ready to Go
            [
                'title'       => "You're Ready to Go",
                'description' => 'A final confirmation that you have everything in place to go live and start earning.',
                'lessons'     => [
                    [
                        'title'    => "You're Ready to Go",
                        'overview' => "You have now worked through the full Chaturbate Boss Doll Blueprint. Your account is set up, your profile is complete, your privacy settings are configured, your private show rates are set, your earnings dashboard is understood, your broadcast interface is familiar, and your apps and tools are ready. Everything is in place. The only thing left is to go live and start earning.",
                        'steps'    => implode("\n", [
                            'Run your pre-stream checklist: camera, audio, room title, settings, apps',
                            'Confirm age verification is approved',
                            'Confirm private show settings are correctly configured',
                            'Confirm Announce Online is enabled so followers are notified',
                            'Open the broadcaster interface, click Start Broadcast, and go live',
                            'Engage every viewer from the first moment — the tone you set in the first 5 minutes defines your session',
                        ]),
                        'tips'     => implode("\n", [
                            'Your first stream is about building confidence, not hitting an earnings target',
                            'Every session gets easier — consistency is the most important habit you can build',
                            'Your support team is always available — never hesitate to ask for help',
                        ]),
                    ],
                ],
            ],

            // (48) What's Next
            [
                'title'       => "What's Next",
                'description' => "A look at what comes after Chaturbate — multi-streaming, other platforms, and building your income.",
                'lessons'     => [
                    [
                        'title'    => "What's Next",
                        'overview' => "Now that you are set up on Chaturbate, the next phase of the Boss Doll Blueprint is about growth and multi-streaming. The most successful creators in the Paradise Dolls network earn across multiple platforms simultaneously — Chaturbate gives you a strong foundation, and adding other platforms expands your income dramatically. Your support team will guide you through what comes next.",
                        'steps'    => implode("\n", [
                            'Continue streaming consistently on Chaturbate and track your earnings week by week',
                            'Build your follower base — every session should result in new followers',
                            'When you feel confident on Chaturbate, speak to your support team about the next platform',
                            'Review your token stats regularly to understand which income streams are growing',
                            'Stay engaged with the Paradise Dolls community for ongoing support, advice, and motivation',
                        ]),
                        'tips'     => implode("\n", [
                            'Multi-streaming across platforms is how Boss Dolls build serious, resilient income',
                            'Focus on mastering one platform before adding another — quality over quantity',
                            'Your support team knows the roadmap — trust the process and follow the Blueprint',
                        ]),
                    ],
                ],
            ],

            // (49) Time to go Live
            [
                'title'       => 'Time to go Live',
                'description' => 'Your final send-off — everything is ready, it is time to go live.',
                'lessons'     => [
                    [
                        'title'    => 'Time to go Live',
                        'overview' => "This is it — everything is set up, every setting is configured, every checklist is complete. It is time to go live on Chaturbate and start earning. The Boss Doll Blueprint has given you everything you need to walk into your first session with confidence. Trust the process, show up consistently, engage every viewer, and the income will follow.",
                        'steps'    => implode("\n", [
                            'Open the Chaturbate broadcaster interface',
                            'Run your pre-stream checklist one final time',
                            'Take a breath, feel confident — you are prepared',
                            'Click Start Broadcast',
                            'Smile, engage, and welcome your first viewers',
                            'Stay live for at least 90 minutes — do not give up if it is quiet at first',
                        ]),
                        'tips'     => implode("\n", [
                            'The first stream is always the hardest — every stream after gets more natural and more profitable',
                            'Energy is everything — bring enthusiasm from the very first second',
                            'You are not alone — your support team is always just a message away',
                        ]),
                    ],
                ],
            ],

            // (50) Final Words from Boss Doll
            [
                'title'       => 'Final Words from Boss Doll',
                'description' => "Kayla's closing message with encouragement and the most important mindset to carry into every stream.",
                'lessons'     => [
                    [
                        'title'      => 'Final Words from Boss Doll',
                        'intro_only' => true,
                        'overview'   => "Kayla wraps up the Chaturbate Boss Doll Blueprint with encouragement, a reminder of the support available to you, and the most important mindset to carry into your first — and every — stream. You have done the work. You have learned the platform. You are ready. Now it is time to go live, stay consistent, and build the income and life you came here for. Welcome to Chaturbate, Boss Doll.",
                        'steps'      => implode("\n", [
                            'Review your notes from each module before your first live session',
                            'Join the Paradise Dolls community and introduce yourself',
                            'Set a date and time for your first public stream and commit to it',
                            'Reach out to your support team any time you feel stuck or unsure',
                        ]),
                        'tips'       => implode("\n", [
                            'Progress matters more than perfection — your first stream does not need to be flawless',
                            'Consistency beats intensity — showing up regularly is worth more than one perfect session',
                            'You have everything you need — trust yourself and the Blueprint',
                        ]),
                    ],
                ],
            ],

            // (51) Outro
            [
                'title'       => 'Outro',
                'description' => 'The official course outro.',
                'lessons'     => [
                    [
                        'title'      => 'Outro',
                        'intro_only' => true,
                        'overview'   => "That is a wrap on the Chaturbate Boss Doll Blueprint. You now have everything you need to set up, go live, and earn on one of the world's biggest cam platforms. Remember: consistency, engagement, and a Boss Doll mindset are what build real income over time. Your team is here. Your community is here. Let's go.",
                        'steps'      => implode("\n", [
                            'Return to any module you need a refresher on at any time',
                            'Reach out to your support team for any questions not covered in this course',
                            'Stay consistent — the income builds with every session',
                        ]),
                        'tips'       => implode("\n", [
                            'The best Boss Dolls never stop learning — revisit this course as you grow',
                            'Your first stream is just the beginning',
                            'Welcome to the Paradise Dolls family — we are proud of you',
                        ]),
                    ],
                ],
            ],

        ];

        $moduleOrder = 1;
        foreach ($modules as $moduleData) {
            $module = CourseModule::query()->updateOrCreate(
                ['course_id' => $course->id, 'title' => $moduleData['title']],
                [
                    'course_id'    => $course->id,
                    'description'  => $moduleData['description'],
                    'is_published' => true,
                    'sort_order'   => $moduleOrder++,
                ]
            );

            $lessonOrder = 1;
            foreach ($moduleData['lessons'] as $lessonData) {
                $isIntroOnly = $lessonData['intro_only'] ?? false;

                $lesson = Lesson::query()->updateOrCreate(
                    ['course_module_id' => $module->id, 'title' => $lessonData['title']],
                    [
                        'course_id'        => $course->id,
                        'course_module_id' => $module->id,
                        'body'             => $lessonData['overview'] ?? '',
                        'overview'         => $lessonData['overview'] ?? '',
                        'steps'            => $lessonData['steps'] ?? null,
                        'tips'             => $lessonData['tips'] ?? null,
                        'is_published'     => true,
                        'sort_order'       => $lessonOrder++,
                    ]
                );

                if ($lesson->contentBlocks()->count() === 0) {
                    if ($isIntroOnly) {
                        $blocks = [
                            ['block_type' => 'heading', 'title' => $lessonData['title'],                        'content' => null,                    'sort_order' => 1],
                            ['block_type' => 'text',    'title' => null,                                         'content' => $lessonData['overview'],  'sort_order' => 2],
                            ['block_type' => 'video',   'title' => $lessonData['title'].' — Presentation Video', 'content' => null,                    'sort_order' => 3],
                            ['block_type' => 'steps',   'title' => null,                                         'content' => $lessonData['steps'],     'sort_order' => 4],
                            ['block_type' => 'tips',    'title' => null,                                         'content' => $lessonData['tips'],      'sort_order' => 5],
                        ];
                    } else {
                        $blocks = [
                            ['block_type' => 'heading', 'title' => $lessonData['title'],                          'content' => null,                    'sort_order' => 1],
                            ['block_type' => 'text',    'title' => null,                                           'content' => $lessonData['overview'],  'sort_order' => 2],
                            ['block_type' => 'video',   'title' => $lessonData['title'].' — Presentation Video',   'content' => null,                    'sort_order' => 3],
                            ['block_type' => 'heading', 'title' => 'Now Follow Along',                             'content' => null,                    'sort_order' => 4],
                            ['block_type' => 'video',   'title' => $lessonData['title'].' — Walkthrough Video',    'content' => null,                    'sort_order' => 5],
                            ['block_type' => 'steps',   'title' => null,                                           'content' => $lessonData['steps'],     'sort_order' => 6],
                            ['block_type' => 'tips',    'title' => null,                                           'content' => $lessonData['tips'],      'sort_order' => 7],
                        ];
                    }

                    foreach ($blocks as $block) {
                        LessonContentBlock::query()->create([
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
