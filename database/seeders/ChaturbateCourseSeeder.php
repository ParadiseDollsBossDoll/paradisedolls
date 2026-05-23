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

        $this->seedModules($course);
    }

    private function seedModules(Course $course): void
    {
        foreach ($this->moduleData() as $moduleIndex => $moduleRow) {
            $lessons = $moduleRow['lessons'];
            unset($moduleRow['lessons']);

            $module = CourseModule::query()->updateOrCreate(
                ['course_id' => $course->id, 'title' => $moduleRow['title']],
                array_merge($moduleRow, [
                    'course_id'    => $course->id,
                    'is_published' => true,
                    'sort_order'   => $moduleIndex + 1,
                ])
            );

            foreach ($lessons as $lessonIndex => $lessonRow) {
                $blocks = $lessonRow['blocks'];
                unset($lessonRow['blocks']);

                $lesson = Lesson::query()->updateOrCreate(
                    ['course_module_id' => $module->id, 'title' => $lessonRow['title']],
                    array_merge($lessonRow, [
                        'course_id'        => $course->id,
                        'course_module_id' => $module->id,
                        'is_published'     => true,
                        'sort_order'       => $lessonIndex + 1,
                    ])
                );

                if ($lesson->contentBlocks()->count() === 0) {
                    foreach ($blocks as $blockIndex => $block) {
                        LessonContentBlock::query()->create(array_merge($block, [
                            'lesson_id'  => $lesson->id,
                            'sort_order' => $blockIndex + 1,
                        ]));
                    }
                }
            }
        }
    }

    private function moduleData(): array
    {
        return [
            // ─── MODULE 1 ───────────────────────────────────────────────────
            [
                'title'       => 'Introduction',
                'description' => 'A warm welcome from your Boss Doll mentor before you dive into the platform.',
                'lessons'     => [
                    [
                        'title'    => 'Welcome & Meet Your Mentor',
                        'overview' => 'Kayla introduces herself, explains the Boss Doll philosophy, and gives you a roadmap of what this course covers and how to get the most out of each lesson.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Welcome & Meet Your Mentor'],
                            ['block_type' => 'text',    'content' => 'Kayla introduces herself, explains the Boss Doll philosophy, and gives you a roadmap of what this course covers and how to get the most out of each lesson.'],
                            ['block_type' => 'video',   'title'   => 'Introduction — Presentation Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Watch the full introduction before moving on',
                                'Note down any questions to bring to the community',
                                'Set a goal for what you want to achieve by the end of this course',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'There are no stupid questions — the community is here to support you',
                                'Work through each module in order for the best experience',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 2 ───────────────────────────────────────────────────
            [
                'title'       => 'Getting Started on Chaturbate',
                'description' => 'Everything you need to know to create your account and get logged in for the first time.',
                'lessons'     => [
                    [
                        'title'    => 'What is Chaturbate?',
                        'overview' => 'An overview of what Chaturbate is, how it works as a platform, who the audience is, and why it is one of the most popular live-streaming platforms for models.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'What is Chaturbate?'],
                            ['block_type' => 'text',    'content' => 'An overview of what Chaturbate is, how it works as a platform, who the audience is, and why it is one of the most popular live-streaming platforms for models.'],
                            ['block_type' => 'video',   'title'   => 'What is Chaturbate? — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'What is Chaturbate? — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Visit chaturbate.com and explore the homepage as a viewer',
                                'Take note of the model categories and how broadcasts are listed',
                                'Understand the token economy before you proceed to account creation',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Never create a personal account on the same device you use for work',
                                'Chaturbate operates in US time zones for payout schedules — keep this in mind',
                            ])],
                        ],
                    ],
                    [
                        'title'    => 'Creating Your Chaturbate Account',
                        'overview' => 'A step-by-step walkthrough of signing up on Chaturbate as a broadcaster, including what information you need and the initial account settings to configure.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Creating Your Chaturbate Account'],
                            ['block_type' => 'text',    'content' => 'A step-by-step walkthrough of signing up on Chaturbate as a broadcaster, including what information you need and the initial account settings to configure.'],
                            ['block_type' => 'video',   'title'   => 'Creating Your Account — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Creating Your Account — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Use a dedicated work email address — not your personal one',
                                'Choose a username that fits your brand and is easy to remember',
                                'Save your login credentials somewhere secure immediately after signing up',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Your username cannot be changed later, so choose carefully',
                                'Use a strong, unique password — never reuse passwords from other accounts',
                            ])],
                        ],
                    ],
                    [
                        'title'    => 'Logging In & Verifying Your Details',
                        'overview' => 'How to log in to your new Chaturbate account, locate your broadcaster dashboard, and confirm your email and basic account details are in order.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Logging In & Verifying Your Details'],
                            ['block_type' => 'text',    'content' => 'How to log in to your new Chaturbate account, locate your broadcaster dashboard, and confirm your email and basic account details are in order.'],
                            ['block_type' => 'video',   'title'   => 'Logging In & Verifying Details — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Logging In & Verifying Details — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Log in and confirm your email address is verified',
                                'Locate the broadcaster dashboard and familiarise yourself with the layout',
                                'Check that your account type is set to Broadcaster, not just Member',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'If you do not receive a verification email, check your spam folder',
                                'Keep your login session active on your work device only',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 3 ───────────────────────────────────────────────────
            [
                'title'       => 'Building Your Profile',
                'description' => 'Create a profile that attracts viewers, communicates your brand, and sets the right expectations.',
                'lessons'     => [
                    [
                        'title'    => 'Setting Up Your Bio Section',
                        'overview' => 'How to write a compelling broadcaster bio, what information to include, and how to position yourself in a way that attracts the right viewers.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Setting Up Your Bio Section'],
                            ['block_type' => 'text',    'content' => 'How to write a compelling broadcaster bio, what information to include, and how to position yourself in a way that attracts the right viewers.'],
                            ['block_type' => 'video',   'title'   => 'Bio Section — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Bio Section — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Write a short, friendly bio that tells viewers what to expect',
                                'Mention your schedule, what you enjoy, and what tokens unlock',
                                'Use natural language — avoid sounding robotic or copied',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Keep your bio under 300 characters for readability',
                                'Never include personal contact details in your bio',
                            ])],
                        ],
                    ],
                    [
                        'title'    => 'Uploading Your Profile Photo & Videos',
                        'overview' => 'Where to upload your profile photo and any preview videos, what resolution and format Chaturbate accepts, and how to make your profile stand out visually.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Uploading Your Profile Photo & Videos'],
                            ['block_type' => 'text',    'content' => 'Where to upload your profile photo and any preview videos, what resolution and format Chaturbate accepts, and how to make your profile stand out visually.'],
                            ['block_type' => 'video',   'title'   => 'Profile Photo & Videos — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Profile Photo & Videos — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Upload a clear, high-quality profile photo that represents your brand',
                                'Add at least one preview video clip to your profile',
                                'Check how your profile looks from the public viewer side after uploading',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Use a thumbnail that stands out in the model grid — good lighting matters',
                                'Profile photos must comply with Chaturbate content guidelines',
                            ])],
                        ],
                    ],
                    [
                        'title'    => 'Completing Your About Me Section',
                        'overview' => 'How to fill in the extended About Me panel, including your show type preferences, your schedule, tip menu, and any social links or additional notes for viewers.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Completing Your About Me Section'],
                            ['block_type' => 'text',    'content' => 'How to fill in the extended About Me panel, including your show type preferences, your schedule, tip menu, and any social links or additional notes for viewers.'],
                            ['block_type' => 'video',   'title'   => 'About Me Section — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'About Me Section — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Fill in your show preferences, schedule, and tip menu information',
                                'Keep your tip menu updated and aligned with what you are comfortable offering',
                                'Proofread everything before saving — viewers will read every word',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'A tip menu builds expectation and reduces repetitive viewer questions',
                                'Keep token amounts round and easy to remember',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 4 ───────────────────────────────────────────────────
            [
                'title'       => 'Settings & Privacy',
                'description' => 'Configure your privacy controls, audience visibility, room rules, and location blocks before you go live.',
                'lessons'     => [
                    [
                        'title'    => 'Settings Overview & Password Warning',
                        'overview' => 'A tour of the Chaturbate settings panel and an important warning about password security, two-factor authentication, and keeping your account safe.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Settings Overview & Password Warning'],
                            ['block_type' => 'text',    'content' => 'A tour of the Chaturbate settings panel and an important warning about password security, two-factor authentication, and keeping your account safe.'],
                            ['block_type' => 'video',   'title'   => 'Settings Overview — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Settings Overview — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Open the Settings panel and locate every subsection listed in the walkthrough',
                                'Enable two-factor authentication immediately',
                                'Change your password to something unique and save it in a password manager',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Never share your password with anyone — including agency staff',
                                'Enable 2FA before you upload any ID documents',
                            ])],
                        ],
                    ],
                    [
                        'title'    => 'Audience Visibility Settings',
                        'overview' => 'How to control who can see your stream — setting viewer restrictions, age gating, and audience type so your room reaches the right people.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Audience Visibility Settings'],
                            ['block_type' => 'text',    'content' => 'How to control who can see your stream — setting viewer restrictions, age gating, and audience type so your room reaches the right people.'],
                            ['block_type' => 'video',   'title'   => 'Audience Visibility — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Audience Visibility — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Decide whether your room is public, followers-only, or private',
                                'Set your audience gender preference if applicable',
                                'Review visitor type settings to filter anonymous or free account viewers',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Restricting anonymous viewers can improve the quality of your audience',
                                'You can adjust visibility mid-stream without ending your broadcast',
                            ])],
                        ],
                    ],
                    [
                        'title'    => 'Setting Up Your Room Rules',
                        'overview' => 'How to write and display room rules that set clear boundaries, reduce bad behaviour, and communicate your expectations to viewers from the moment they enter.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Setting Up Your Room Rules'],
                            ['block_type' => 'text',    'content' => 'How to write and display room rules that set clear boundaries, reduce bad behaviour, and communicate your expectations to viewers from the moment they enter.'],
                            ['block_type' => 'video',   'title'   => 'Room Rules — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Room Rules — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Write clear room rules covering tipping etiquette, respect, and off-limits requests',
                                'Add rules to your room description so they appear on your profile page',
                                'Set up auto-messages to post rules at regular intervals during your stream',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Shorter, numbered rules are easier for viewers to read quickly',
                                'Never negotiate your rules — enforce them consistently from day one',
                            ])],
                        ],
                    ],
                    [
                        'title'    => 'Location Blocking',
                        'overview' => 'How to block specific countries or regions from seeing your stream, why this matters for privacy, and how to apply the settings without disrupting your audience.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Location Blocking'],
                            ['block_type' => 'text',    'content' => 'How to block specific countries or regions from seeing your stream, why this matters for privacy, and how to apply the settings without disrupting your audience.'],
                            ['block_type' => 'video',   'title'   => 'Location Blocking — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Location Blocking — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Navigate to the Geoblocking section in your settings',
                                'Block any countries you need to for personal safety or privacy',
                                'Consider blocking your home country if you have privacy concerns',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Blocking countries does not affect your earnings from other regions',
                                'Always block your local area before your first stream if privacy is a concern',
                            ])],
                        ],
                    ],
                    [
                        'title'    => 'Network Visibility Settings',
                        'overview' => 'Understanding the Chaturbate affiliate and network system, how your stream appears across third-party sites, and how to control or limit external exposure.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Network Visibility Settings'],
                            ['block_type' => 'text',    'content' => 'Understanding the Chaturbate affiliate and network system, how your stream appears across third-party sites, and how to control or limit external exposure.'],
                            ['block_type' => 'video',   'title'   => 'Network Visibility — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Network Visibility — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Review your network and affiliate embed settings',
                                'Decide whether you want third-party sites to embed your stream',
                                'Turn off embedding if you want to control exactly where you appear',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Network embedding can increase views but also reduces privacy control',
                                'Revisit this setting regularly as your comfort level grows',
                            ])],
                        ],
                    ],
                    [
                        'title'    => 'Satisfaction Score',
                        'overview' => 'What the Chaturbate satisfaction score is, how it is calculated, how it affects your ranking and visibility, and what you can do to maintain a high score.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Satisfaction Score'],
                            ['block_type' => 'text',    'content' => 'What the Chaturbate satisfaction score is, how it is calculated, how it affects your ranking and visibility, and what you can do to maintain a high score.'],
                            ['block_type' => 'video',   'title'   => 'Satisfaction Score — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Satisfaction Score — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Find your current satisfaction score in the broadcaster dashboard',
                                'Review what actions lower your score and avoid them',
                                'Focus on consistency — stream regularly to maintain a healthy score',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Cancelling private shows is the fastest way to lower your score',
                                'A high satisfaction score boosts your placement in search results',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 5 ───────────────────────────────────────────────────
            [
                'title'       => 'Earnings & Monetisation',
                'description' => 'Understand every earnings tool Chaturbate offers and configure them correctly before your first stream.',
                'lessons'     => [
                    [
                        'title'    => 'Fan Club Setup',
                        'overview' => 'How to create and configure your Fan Club on Chaturbate, set the monthly subscription price, decide what Fan Club members get access to, and promote it effectively.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Fan Club Setup'],
                            ['block_type' => 'text',    'content' => 'How to create and configure your Fan Club on Chaturbate, set the monthly subscription price, decide what Fan Club members get access to, and promote it effectively.'],
                            ['block_type' => 'video',   'title'   => 'Fan Club Setup — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Fan Club Setup — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Enable Fan Club in your broadcaster settings',
                                'Set a monthly price that reflects the value of your exclusive content',
                                'Write a clear Fan Club description explaining what subscribers receive',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Start with a lower price and increase it as your fan base grows',
                                'Exclusivity is your selling point — make Fan Club access feel special',
                            ])],
                        ],
                    ],
                    [
                        'title'    => 'Private Show Settings',
                        'overview' => 'How to enable private shows, set your per-minute token rate, configure minimum durations, and understand how private shows appear to viewers during a public broadcast.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Private Show Settings'],
                            ['block_type' => 'text',    'content' => 'How to enable private shows, set your per-minute token rate, configure minimum durations, and understand how private shows appear to viewers during a public broadcast.'],
                            ['block_type' => 'video',   'title'   => 'Private Show Settings — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Private Show Settings — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Enable private shows in your broadcaster settings',
                                'Set a token-per-minute rate you are comfortable with',
                                'Decide whether to allow spy mode for non-paying viewers',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Never go below your minimum rate — know your worth',
                                'You can end a private show at any time if rules are broken',
                            ])],
                        ],
                    ],
                    [
                        'title'    => 'Tokens Per Minute & Minimum Minutes',
                        'overview' => 'Understanding the relationship between tokens per minute, minimum minutes, and total earnings per private show — and how to price yourself competitively without undervaluing your time.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Tokens Per Minute & Minimum Minutes'],
                            ['block_type' => 'text',    'content' => 'Understanding the relationship between tokens per minute, minimum minutes, and total earnings per private show — and how to price yourself competitively without undervaluing your time.'],
                            ['block_type' => 'video',   'title'   => 'Tokens Per Minute & Minimums — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Tokens Per Minute & Minimums — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Calculate your target hourly earnings and work backwards from that to set your token rate',
                                'Set a minimum minute requirement to protect your time',
                                'Review competitor rates in your niche to stay positioned appropriately',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Setting a minimum of 5–10 minutes filters out low-intent requests',
                                'Increase your rate as your follower count and reputation grow',
                            ])],
                        ],
                    ],
                    [
                        'title'    => 'Spy Mode Settings',
                        'overview' => 'What Spy Mode is, how it lets non-paying viewers watch private shows at a lower token rate, and how to use it strategically to increase private show revenue.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Spy Mode Settings'],
                            ['block_type' => 'text',    'content' => 'What Spy Mode is, how it lets non-paying viewers watch private shows at a lower token rate, and how to use it strategically to increase private show revenue.'],
                            ['block_type' => 'video',   'title'   => 'Spy Mode — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Spy Mode — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Decide whether to enable Spy Mode for your private shows',
                                'Set a Spy Mode token-per-minute rate lower than your private show rate',
                                'Monitor how Spy Mode viewers interact and whether they convert to privates',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Spy Mode can act as a teaser that encourages viewers to book their own private show',
                                'You can disable Spy Mode at any time without affecting your private show settings',
                            ])],
                        ],
                    ],
                    [
                        'title'    => 'Token Stats & Earnings Dashboard',
                        'overview' => 'Where to find your earnings data on Chaturbate, how to read your token stats, understand the payout schedule, and track your income over time.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Token Stats & Earnings Dashboard'],
                            ['block_type' => 'text',    'content' => 'Where to find your earnings data on Chaturbate, how to read your token stats, understand the payout schedule, and track your income over time.'],
                            ['block_type' => 'video',   'title'   => 'Token Stats & Earnings — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Token Stats & Earnings — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Navigate to your Token Stats page and review the data breakdown',
                                'Note the minimum payout threshold and your chosen payment method',
                                'Set up a spreadsheet or app to track your weekly earnings independently',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Payouts are typically processed on a set weekly schedule — plan your finances around this',
                                'Always verify payout amounts yourself rather than relying solely on the platform',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 6 ───────────────────────────────────────────────────
            [
                'title'       => 'Going Live',
                'description' => 'Set up your stream correctly, pass age verification, configure your camera and audio, and go live for the first time with confidence.',
                'lessons'     => [
                    [
                        'title'    => 'Understanding the Broadcaster Interface',
                        'overview' => 'A full tour of the Chaturbate broadcaster interface — the controls you will use every time you go live, what each button does, and where to find what you need in the moment.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Understanding the Broadcaster Interface'],
                            ['block_type' => 'text',    'content' => 'A full tour of the Chaturbate broadcaster interface — the controls you will use every time you go live, what each button does, and where to find what you need in the moment.'],
                            ['block_type' => 'video',   'title'   => 'Broadcaster Interface — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Broadcaster Interface — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Open the broadcast page and identify every panel shown in the walkthrough',
                                'Locate the chat window, token tracker, viewer count, and tip goals',
                                'Practise switching between panels before you go live for the first time',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Knowing the interface in advance means fewer distractions during your live stream',
                                'Keep the broadcaster page bookmarked for quick access',
                            ])],
                        ],
                    ],
                    [
                        'title'    => 'Age Verification Before You Go Live',
                        'overview' => 'The ID verification process Chaturbate requires before you can broadcast, what documents are accepted, how to submit them correctly, and how long approval typically takes.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Age Verification Before You Go Live'],
                            ['block_type' => 'text',    'content' => 'The ID verification process Chaturbate requires before you can broadcast, what documents are accepted, how to submit them correctly, and how long approval typically takes.'],
                            ['block_type' => 'video',   'title'   => 'Age Verification — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Age Verification — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Navigate to the ID verification section in your broadcaster settings',
                                'Prepare a clear, high-quality photo of your accepted ID document',
                                'Submit your ID and check your email for confirmation — allow 24–72 hours for approval',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Make sure your photo is well-lit and all text on the ID is clearly readable',
                                'Do not submit until your ID is fully valid — expired documents will be rejected',
                            ])],
                        ],
                    ],
                    [
                        'title'    => 'Setting Profile Visibility & Room Title',
                        'overview' => 'How to configure your room title, subject line, and profile visibility settings so you appear in the right categories and attract the right viewers when you go live.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Setting Profile Visibility & Room Title'],
                            ['block_type' => 'text',    'content' => 'How to configure your room title, subject line, and profile visibility settings so you appear in the right categories and attract the right viewers when you go live.'],
                            ['block_type' => 'video',   'title'   => 'Profile Visibility & Room Title — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Profile Visibility & Room Title — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Write a room title that is descriptive, engaging, and within the character limit',
                                'Set your room subject to reflect what you are doing in the current stream',
                                'Choose the correct broadcast category and add relevant tags',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Update your room title regularly — it appears in Chaturbate search results',
                                'Including a tip goal or current activity in your title increases click-through rates',
                            ])],
                        ],
                    ],
                    [
                        'title'    => 'Camera, Resolution & Mic Setup',
                        'overview' => 'How to select the correct camera and microphone in the broadcaster panel, choose the right resolution for your internet speed, and test your audio and video before going live.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Camera, Resolution & Mic Setup'],
                            ['block_type' => 'text',    'content' => 'How to select the correct camera and microphone in the broadcaster panel, choose the right resolution for your internet speed, and test your audio and video before going live.'],
                            ['block_type' => 'video',   'title'   => 'Camera, Resolution & Mic — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Camera, Resolution & Mic — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Open the broadcaster page and allow camera and microphone permissions when prompted',
                                'Select your correct camera device from the dropdown',
                                'Run the audio test and confirm your mic input is showing signal',
                                'Set resolution to 720p if your internet speed is below 10 Mbps upload',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Always do a test broadcast in a private room before your first public stream',
                                'Buffering and lag are usually caused by insufficient upload speed, not the camera',
                            ])],
                        ],
                    ],
                    [
                        'title'    => 'Going Live for the First Time',
                        'overview' => 'The final checklist before hitting Start Broadcast — what to check, what to expect in the first few minutes, and how to stay calm and in control as viewers start arriving.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Going Live for the First Time'],
                            ['block_type' => 'text',    'content' => 'The final checklist before hitting Start Broadcast — what to check, what to expect in the first few minutes, and how to stay calm and in control as viewers start arriving.'],
                            ['block_type' => 'video',   'title'   => 'Going Live — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Going Live — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Run through the full pre-stream checklist: camera, lighting, audio, title, rules',
                                'Start your stream in private mode first to confirm everything looks and sounds right',
                                'Switch to public and stay calm — it is normal for the room to be quiet at first',
                                'Engage with every person who enters, even if they do not tip immediately',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Your first stream is about building confidence, not hitting earnings targets',
                                'Stay online for at least 90 minutes — Chaturbate rewards consistency in its algorithm',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 7 ───────────────────────────────────────────────────
            [
                'title'       => 'Managing Your Stream',
                'description' => 'Handle chat, private messages, followers, bots, and apps like a pro during your live sessions.',
                'lessons'     => [
                    [
                        'title'    => 'Chat Interface Overview',
                        'overview' => 'A walkthrough of the broadcaster chat panel — how to read incoming messages, use moderator tools, pin announcements, and stay on top of the chat during a busy stream.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Chat Interface Overview'],
                            ['block_type' => 'text',    'content' => 'A walkthrough of the broadcaster chat panel — how to read incoming messages, use moderator tools, pin announcements, and stay on top of the chat during a busy stream.'],
                            ['block_type' => 'video',   'title'   => 'Chat Interface — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Chat Interface — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Locate the chat moderation controls in your broadcaster interface',
                                'Set up at least one trusted moderator before your first busy stream',
                                'Practise pinning a message and silencing a test account',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'A good moderator is one of the most valuable assets you can have on stream',
                                'Never engage with trolls in public chat — silence or ban immediately',
                            ])],
                        ],
                    ],
                    [
                        'title'    => 'Handling Public Chat',
                        'overview' => 'Strategies for keeping your public chat engaged, rewarding active tippers verbally, responding to regulars, and maintaining a positive room energy that keeps viewers watching.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Handling Public Chat'],
                            ['block_type' => 'text',    'content' => 'Strategies for keeping your public chat engaged, rewarding active tippers verbally, responding to regulars, and maintaining a positive room energy that keeps viewers watching.'],
                            ['block_type' => 'video',   'title'   => 'Public Chat — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Public Chat — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Acknowledge every tip by name, no matter how small',
                                'Ask open questions to keep conversation flowing during quiet periods',
                                'Use chat to drive tip goals and remind viewers of your active offers',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Viewers who feel seen and acknowledged are far more likely to tip again',
                                'Have a bank of 5–10 conversation topics ready so you never run out of things to say',
                            ])],
                        ],
                    ],
                    [
                        'title'    => 'Managing Private Messages',
                        'overview' => 'How to handle private messages during a stream — responding efficiently without losing public chat momentum, setting expectations with viewers, and converting DMs into private shows.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Managing Private Messages'],
                            ['block_type' => 'text',    'content' => 'How to handle private messages during a stream — responding efficiently without losing public chat momentum, setting expectations with viewers, and converting DMs into private shows.'],
                            ['block_type' => 'video',   'title'   => 'Private Messages — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Private Messages — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Check private messages between public chat interactions — not mid-sentence',
                                'Keep DM responses short and redirect serious requests toward private shows',
                                'Never agree to off-platform contact in private messages',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Private messages are a sales channel — treat them as such',
                                'Do not leave DMs unread for long periods during a stream, it can cost you a private show',
                            ])],
                        ],
                    ],
                    [
                        'title'    => 'Followers & Announce Online',
                        'overview' => 'How the Chaturbate follower system works, what it means to go live to an existing audience, and how to use the Announce Online feature to notify followers when you start a stream.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Followers & Announce Online'],
                            ['block_type' => 'text',    'content' => 'How the Chaturbate follower system works, what it means to go live to an existing audience, and how to use the Announce Online feature to notify followers when you start a stream.'],
                            ['block_type' => 'video',   'title'   => 'Followers & Announce Online — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Followers & Announce Online — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Locate your follower list and review your current follower count',
                                'Enable the Announce Online notification so followers are alerted when you go live',
                                'Set a consistent stream schedule so followers know when to expect you',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Followers who get notified are significantly more likely to return and tip',
                                'Consistency in your schedule is more important than streaming more hours',
                            ])],
                        ],
                    ],
                    [
                        'title'    => 'Apps, Bots & Tools',
                        'overview' => 'An introduction to the Chaturbate apps and bots system — tip-activated games, countdowns, tip menus, and other interactive tools that keep your room entertaining and encourage tipping.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => 'Apps, Bots & Tools'],
                            ['block_type' => 'text',    'content' => 'An introduction to the Chaturbate apps and bots system — tip-activated games, countdowns, tip menus, and other interactive tools that keep your room entertaining and encourage tipping.'],
                            ['block_type' => 'video',   'title'   => 'Apps & Tools — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Apps & Tools — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Browse the Chaturbate app store and identify 2–3 apps that suit your stream style',
                                'Install a tip menu app and configure it with your current token rates',
                                'Test each app in private mode before running it in a public stream',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Interactive apps and countdowns create urgency and encourage group tipping behaviour',
                                'Do not run too many apps at once — it clutters the chat and confuses viewers',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 8 ───────────────────────────────────────────────────
            [
                'title'       => "You're Ready",
                'description' => "Kayla's closing message to send you off with confidence.",
                'lessons'     => [
                    [
                        'title'    => "Final Words from Your Boss Doll",
                        'overview' => "Kayla wraps up the course with encouragement, a reminder of the support available to you, and the most important mindset to carry into your first — and every — stream.",
                        'blocks'   => [
                            ['block_type' => 'heading', 'title' => "Final Words from Your Boss Doll"],
                            ['block_type' => 'text',    'content' => "Kayla wraps up the course with encouragement, a reminder of the support available to you, and the most important mindset to carry into your first — and every — stream."],
                            ['block_type' => 'video',   'title'   => "Closing — Presentation Video"],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                "Review your notes from each module before your first live session",
                                "Join the Paradise Dolls community and introduce yourself",
                                "Set a date for your first stream and commit to it",
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                "Progress matters more than perfection — your first stream does not need to be flawless",
                                "Reach out to the team any time you feel stuck or unsure",
                            ])],
                        ],
                    ],
                ],
            ],
        ];
    }
}
