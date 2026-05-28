<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonContentBlock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StripchatCourseSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::query()->updateOrCreate(
            ['slug' => 'stripchat-boss-doll-blueprint'],
            [
                'title'             => 'Stripchat Boss Doll Blueprint',
                'platform_label'    => 'Stripchat',
                'platform_color'    => '#FF3E4D',
                'short_description' => 'The complete mobile walkthrough for navigating Stripchat, building your database, going live, and maximising every income stream.',
                'description'       => "A full Phase One training course for the Stripchat platform — covering everything from logging in and understanding the dashboard, to building your customer database with mass messages, setting your rates, going live, and running private and exclusive shows. This is the Boss Doll Blueprint mobile walkthrough: every concept explained in a presentation video, then shown step-by-step in a screen-recorded walkthrough.",
                'what_you_will_learn' => implode("\n", [
                    'Navigate the Stripchat dashboard and broadcaster interface from your mobile',
                    'Build and message your customer database using Friend Requests and Mass Messages',
                    'Understand token value, earnings types, and how to identify your top spenders',
                    'Set up your Broadcast Center, rates, tip goals, and show controls',
                    'Use your Feed to stay visible and attract new and returning customers',
                    'Go live with confidence, manage your live room, and handle private and exclusive show requests',
                ]),
                'requirements' => implode("\n", [
                    'A mobile device (iPhone or Android) with camera and microphone',
                    'A stable internet connection',
                    'A Stripchat broadcaster account (set up by the agency before you start)',
                ]),
                'is_published' => true,
                'sort_order'   => 11,
            ]
        );

        DB::transaction(fn () => $this->seedModules($course));
    }

    private function seedModules(Course $course): void
    {
        $syncedModuleIds = [];
        $syncedLessonIds = [];

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
            $syncedModuleIds[] = $module->id;

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
                $syncedLessonIds[] = $lesson->id;

                $syncedBlockIds = [];
                foreach ($blocks as $blockIndex => $block) {
                    $blockModel = LessonContentBlock::query()
                        ->where('lesson_id', $lesson->id)
                        ->where('sort_order', $blockIndex + 1)
                        ->first();

                    $blockData = array_merge($block, [
                        'lesson_id' => $lesson->id,
                        'sort_order' => $blockIndex + 1,
                    ]);

                    if ($blockModel) {
                        $blockModel->forceFill($blockData)->save();
                    } else {
                        $blockModel = LessonContentBlock::query()->create($blockData);
                    }

                    $syncedBlockIds[] = $blockModel->id;
                }

                LessonContentBlock::query()
                    ->where('lesson_id', $lesson->id)
                    ->whereNotIn('id', $syncedBlockIds)
                    ->delete();
            }
        }

        Lesson::query()
            ->where('course_id', $course->id)
            ->whereNotIn('id', $syncedLessonIds)
            ->delete();

        CourseModule::query()
            ->where('course_id', $course->id)
            ->whereNotIn('id', $syncedModuleIds)
            ->delete();
    }

    private function moduleData(): array
    {
        return [

            // ─── MODULE 1 ── Introduction ────────────────────────────────────
            [
                'title'       => 'Introduction',
                'description' => 'A warm welcome from Kayla before you begin your Stripchat training.',
                'lessons'     => [
                    [
                        'title'    => 'Introduction',
                        'overview' => "Kayla introduces herself and explains what this course covers — how to broadcast and stream on Stripchat, use paid and private features, and sell content directly on the platform. This is Phase One: the basics and setup.",
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'Introduction'],
                            ['block_type' => 'text',    'content' => "Kayla introduces herself and explains what this course covers — how to broadcast and stream on Stripchat, use paid and private features, and sell content directly on the platform. This is Phase One: the basics and setup."],
                            ['block_type' => 'video',   'title'   => 'Introduction — Presentation Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Watch the introduction in full before moving to the next lesson',
                                'Note the two phases: Phase One (this course) is basics and setup; Phase Two covers advanced tips',
                                'Bookmark this course so you can return to any section easily',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Your account will already have been created for you before you start this training',
                                'Work through each module in order — each section builds on the last',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 2 ── About Stripchat ─────────────────────────────────
            [
                'title'       => 'About Stripchat',
                'description' => 'Understand the platform, how it works, and why Stripchat is one of the most powerful income opportunities for models.',
                'lessons'     => [
                    [
                        'title'    => 'About Stripchat',
                        'overview' => 'Stripchat is one of the leading live-streaming platforms in the world, attracting over 700 million visitors per month. This lesson covers how the freemium model works, the premium features available to broadcasters, and why the platform is a powerful income opportunity.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'About Stripchat'],
                            ['block_type' => 'text',    'content' => 'Stripchat is one of the leading live-streaming platforms in the world, attracting over 700 million visitors per month. This lesson covers how the freemium model works, the premium features available to broadcasters, and why the platform is a powerful income opportunity.'],
                            ['block_type' => 'video',   'title'   => 'About Stripchat — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'About Stripchat — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Visit stripchat.com as a viewer to see how the platform looks from a customer perspective',
                                'Note the model grid, categories, and how broadcasts appear in the listings',
                                'Understand the difference between free viewing and premium paid features',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Stripchat has a built-in social network — treat it like a brand platform, not just a streaming tool',
                                'The platform has won multiple industry awards including XBIZ Cam Site of the Year',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 3 ── Stripchat Login Page ────────────────────────────
            [
                'title'       => 'Stripchat Login Page',
                'description' => 'How to log in to Stripchat on mobile and navigate to your main dashboard.',
                'lessons'     => [
                    [
                        'title'    => 'Stripchat Login Page',
                        'overview' => 'How to log in to Stripchat on mobile, what you see on the main homepage dashboard, and how to find the key areas you will use every session — including the Broadcast Center button.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'Stripchat Login Page'],
                            ['block_type' => 'text',    'content' => 'How to log in to Stripchat on mobile, what you see on the main homepage dashboard, and how to find the key areas you will use every session — including the Broadcast Center button.'],
                            ['block_type' => 'video',   'title'   => 'Stripchat Login Page — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Stripchat Login Page — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Go to stripchat.com and tap Login at the top right',
                                'Enter the username and password provided by the agency — do not create a new account',
                                'Locate the green Broadcast Center button on your homepage — this is your most-used shortcut',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Your account was set up by the agency — never create a second account from the same device',
                                'The homepage blurs certain areas in training materials for privacy, but you will see everything when logged in on your own device',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 4 ── Live Models & Top Models ────────────────────────
            [
                'title'       => 'Live Models & Top Models',
                'description' => 'Learn how customers discover models and how the Live Models and Top Models sections work.',
                'lessons'     => [
                    [
                        'title'    => 'Live Models & Top Models',
                        'overview' => "How to navigate the Live Models section to understand what other broadcasters are doing, how the Top Models rankings work, and what the platform looks like from a viewer's perspective — helping you understand how customers discover and choose who to watch.",
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'Live Models & Top Models'],
                            ['block_type' => 'text',    'content' => "How to navigate the Live Models section to understand what other broadcasters are doing, how the Top Models rankings work, and what the platform looks like from a viewer's perspective — helping you understand how customers discover and choose who to watch."],
                            ['block_type' => 'video',   'title'   => 'Live Models & Top Models — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Live Models & Top Models — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'From the homepage, scroll through the Live Models grid to see who is currently broadcasting',
                                'Tap Top Models to see the highest-ranked broadcasters on the platform',
                                'Note how profile photos, show topics, and viewer counts affect which models appear at the top',
                                'Observe how models in the top positions interact with their rooms to understand what works',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Understanding the viewer experience helps you optimise your own profile and room for maximum appeal',
                                'Top Models rankings are driven by viewer count, StripScore, and activity — consistency is the key factor',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 5 ── Chat & User Value ───────────────────────────────
            [
                'title'       => 'Chat & User Value',
                'description' => 'Learn how to use your messages inbox to identify and prioritise paying users.',
                'lessons'     => [
                    [
                        'title'    => 'Chat & User Value',
                        'overview' => 'Understanding your messages inbox — how to identify which users have tokens, how to find previous tippers using the "With Tokens" filter, and why focusing on paying users is the key to maximising every session.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'Chat & User Value'],
                            ['block_type' => 'text',    'content' => 'Understanding your messages inbox — how to identify which users have tokens, how to find previous tippers using the "With Tokens" filter, and why focusing on paying users is the key to maximising every session.'],
                            ['block_type' => 'video',   'title'   => 'Chat & User Value — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Chat & User Value — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Open your inbox by tapping the white bell icon in the top right of the app',
                                'Switch between All Chats and With Tokens to see which users hold tokens',
                                'Prioritise responding to users with tokens — they are statistically more likely to spend',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Users who have spent before are your warmest leads — always respond to them first',
                                'Do not ignore low-token users completely; engagement builds loyalty over time',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 6 ── Notifications ───────────────────────────────────
            [
                'title'       => 'Notifications',
                'description' => 'How to track tips, messages, and friend requests in real time using the Notifications tab.',
                'lessons'     => [
                    [
                        'title'    => 'Notifications',
                        'overview' => 'How to use the Notifications tab to track tips, messages, and incoming friend requests in real time — and why keeping an eye on this panel during a live session is essential.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'Notifications'],
                            ['block_type' => 'text',    'content' => 'How to use the Notifications tab to track tips, messages, and incoming friend requests in real time — and why keeping an eye on this panel during a live session is essential.'],
                            ['block_type' => 'video',   'title'   => 'Notifications — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Notifications — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Tap the bell icon and switch to the Notifications tab',
                                'Review the list — you will see tips, accepted friend requests, and new friend request alerts',
                                'Get into the habit of checking notifications every few minutes during a live stream',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'A missed friend request is a missed database entry — always accept them',
                                'Tip notifications let you acknowledge big tippers by name on stream, which builds loyalty',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 7 ── Friend Requests & Database ──────────────────────
            [
                'title'       => 'Friend Requests & Database',
                'description' => 'Why accepting every friend request builds your customer database and grows your income potential.',
                'lessons'     => [
                    [
                        'title'    => 'Friend Requests & Database',
                        'overview' => 'Why accepting every friend request is one of the most important habits on Stripchat — how it builds your customer database, where to find your full friends list, and how growing this list directly grows your income potential.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'Friend Requests & Database'],
                            ['block_type' => 'text',    'content' => 'Why accepting every friend request is one of the most important habits on Stripchat — how it builds your customer database, where to find your full friends list, and how growing this list directly grows your income potential.'],
                            ['block_type' => 'video',   'title'   => 'Friend Requests & Database — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Friend Requests & Database — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Accept every friend request — each accepted request adds a user to your personal database',
                                'To view your friends list: tap the 3-dot icon (top right) → scroll down to "My Friends" → tap to open',
                                'Review your friends list regularly to see how your database is growing',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'A large friends list means a large mass-message audience — this is your most powerful marketing tool',
                                'Your chatter will accept requests during your live if you have one — if not, check notifications after each stream',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 8 ── Mass Message Strategy ───────────────────────────
            [
                'title'       => 'Mass Message Strategy',
                'description' => 'How to send a mass message to your entire database the moment you go live.',
                'lessons'     => [
                    [
                        'title'    => 'Mass Message Strategy',
                        'overview' => 'How to send a mass message to all your friends or Fan Club subscribers — the step-by-step process for composing the message, attaching media, selecting your audience, and hitting send. This is how you announce that you are online and bring your database straight back into your room.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'Mass Message Strategy'],
                            ['block_type' => 'text',    'content' => 'How to send a mass message to all your friends or Fan Club subscribers — the step-by-step process for composing the message, attaching media, selecting your audience, and hitting send. This is how you announce that you are online and bring your database straight back into your room.'],
                            ['block_type' => 'video',   'title'   => 'Mass Message Strategy — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Mass Message Strategy — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Tap the bell icon → tap the green pen icon to open Create Mass Message',
                                'Choose Free Mass Message or Tokens (paid) — you can only send ONE per day',
                                'Select your recipients: Friends or Fan Club subscribers',
                                'Attach a photo or short video, then write your message (e.g. "Hey baby, I just logged on, I missed you, come join me")',
                                'Tap Send Mass Message — your entire database now knows you are online',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Send your mass message the moment you go live — timing is everything',
                                'Always attach media; messages with a photo or video have higher open and response rates',
                                'If you have a chatter, they will send this for you — but you should know how to do it yourself',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 9 ── Favourites ──────────────────────────────────────
            [
                'title'       => 'Favourites',
                'description' => 'How the Favourites system works and why growing your Favourites count is one of the best passive marketing tools on Stripchat.',
                'lessons'     => [
                    [
                        'title'    => 'Favourites',
                        'overview' => "How the Favourites system works on Stripchat — why getting customers to favourite your profile is one of the most powerful passive marketing tools available, how favourites receive notifications when you go live, and how to encourage viewers to tap the heart icon.",
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'Favourites'],
                            ['block_type' => 'text',    'content' => "How the Favourites system works on Stripchat — why getting customers to favourite your profile is one of the most powerful passive marketing tools available, how favourites receive notifications when you go live, and how to encourage viewers to tap the heart icon."],
                            ['block_type' => 'video',   'title'   => 'Favourites — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Favourites — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Encourage viewers to favourite your profile by tapping the heart icon during your live sessions',
                                "Every customer who favourites you receives a notification when you go live — growing this number grows your automatic audience",
                                'Check your Favourites count in the Broadcast Center to track growth over time',
                                "Mention your Favourites in stream — \"tap the heart to get notified when I'm live\" is a simple and effective call to action",
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Every favourite is a guaranteed live notification — this is free, passive marketing you never have to pay for',
                                'Your Favourites count improves your StripScore and profile visibility in search rankings',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 10 ── Tokens & Earnings ─────────────────────────────
            [
                'title'       => 'Tokens & Earnings',
                'description' => 'Where to find your earnings dashboard and how to read your token history.',
                'lessons'     => [
                    [
                        'title'    => 'Tokens & Earnings',
                        'overview' => 'Where to find your earnings dashboard on Stripchat, how to view your token history across different time periods, and how to read your earnings breakdown by user, date, and income type.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'Tokens & Earnings'],
                            ['block_type' => 'text',    'content' => 'Where to find your earnings dashboard on Stripchat, how to view your token history across different time periods, and how to read your earnings breakdown by user, date, and income type.'],
                            ['block_type' => 'video',   'title'   => 'Tokens & Earnings — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Tokens & Earnings — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Tap the token icon (shown as "0 TK") at the top of your screen',
                                'Tap the dollar sign / Earnings section to open your earnings dashboard',
                                'Use the calendar icon to switch between time periods: Today, Last 24 Hours, Last 7 Days, Last 30 Days, Last 365 Days, Lifetime',
                                'Review the columns: user name (first), date earned (middle), income type (third)',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Check your earnings after every session — tracking performance helps you spot patterns',
                                'Income types include public tips, private tips, private shows, and exclusive shows',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 11 ── Tokens & Value (Important) ────────────────────
            [
                'title'       => 'Tokens & Value (Important)',
                'description' => 'Understand exactly how tokens convert to dollars and what you take home after agency fees.',
                'lessons'     => [
                    [
                        'title'    => 'Tokens & Value (Important)',
                        'overview' => 'A clear breakdown of how tokens convert to dollars: one token equals approximately 5 cents, so 1,000 tokens equals $50. This lesson also explains the agency fee structure — 20% with no chatter, 30% with a chatter — so you always know exactly what to expect in your pocket.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'Tokens & Value (Important)'],
                            ['block_type' => 'text',    'content' => 'A clear breakdown of how tokens convert to dollars: one token equals approximately 5 cents, so 1,000 tokens equals $50. This lesson also explains the agency fee structure — 20% with no chatter, 30% with a chatter — so you always know exactly what to expect in your pocket.'],
                            ['block_type' => 'video',   'title'   => 'Tokens & Value — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Tokens & Value — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Remember the formula: Tokens × 0.05 = your gross dollar amount',
                                'Then subtract the agency fee: 20% if no chatter, 30% if you have a chatter',
                                'Example: 1,000 tokens = $50 gross → $40 net (no chatter) or $35 net (with chatter)',
                                'Track your token totals after each session and calculate your take-home using this formula',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                '80% of the token value goes to you — the platform keeps 20%, then the agency fee applies after',
                                'Never estimate your earnings in tokens alone — always convert to understand your real income',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 12 ── Paid Tippers (Whales) ─────────────────────────
            [
                'title'       => 'Paid Tippers (Whales)',
                'description' => 'How to identify your highest-spending customers and why nurturing them is your most reliable income strategy.',
                'lessons'     => [
                    [
                        'title'    => 'Paid Tippers (Whales)',
                        'overview' => 'How to identify your highest-spending customers using the Paying Users tab, and why building personal relationships with your top spenders is critical to consistent, growing income on Stripchat.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'Paid Tippers (Whales)'],
                            ['block_type' => 'text',    'content' => 'How to identify your highest-spending customers using the Paying Users tab, and why building personal relationships with your top spenders is critical to consistent, growing income on Stripchat.'],
                            ['block_type' => 'video',   'title'   => 'Paid Tippers (Whales) — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Paid Tippers (Whales) — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Open Earnings → switch from Tokens History to Paying Users',
                                'Scroll right to see the Total Tokens column — this ranks your biggest spenders',
                                'Note your top 5 spenders and make a habit of engaging them personally when they are online',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Whales (your top spenders) often account for 60–80% of total earnings — nurture them',
                                'Give attention, build relationships, and keep them coming back — that is your most reliable income strategy',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 13 ── Types of Income ───────────────────────────────
            [
                'title'       => 'Types of Income',
                'description' => 'A full breakdown of the four income types on Stripchat and how to maximise each one.',
                'lessons'     => [
                    [
                        'title'    => 'Types of Income',
                        'overview' => 'A full breakdown of the four income types on Stripchat — public tips, private tips, private shows, and exclusive shows — and how understanding each one helps you maximise every session.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'Types of Income'],
                            ['block_type' => 'text',    'content' => 'A full breakdown of the four income types on Stripchat — public tips, private tips, private shows, and exclusive shows — and how understanding each one helps you maximise every session.'],
                            ['block_type' => 'video',   'title'   => 'Types of Income — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Types of Income — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Understand the four income streams: Public Tips (tokens tipped in your open room), Private Tips (tokens sent in private messages), Private Shows (per-minute sessions viewable by spy viewers), Exclusive Shows (per-minute fully one-to-one sessions)',
                                'Review your earnings breakdown to see which income type is generating the most for you',
                                'Focus on building private show volume once your database is established',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Public tips build in the early sessions; private shows drive the majority of income once you have regulars',
                                'Every income type compounds — the more you stream, the more each type grows simultaneously',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 14 ── Broadcast Center ──────────────────────────────
            [
                'title'       => 'Broadcast Center',
                'description' => 'A full tour of your Broadcast Center — the command centre you access before every live session.',
                'lessons'     => [
                    [
                        'title'    => 'Broadcast Center',
                        'overview' => 'A full tour of the Broadcast Center — the command centre you access before every live session. This covers your StripScore, Favourites count, Fan Club subscribers, private show rating, income per hour, and weekly earnings tracker.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'Broadcast Center'],
                            ['block_type' => 'text',    'content' => 'A full tour of the Broadcast Center — the command centre you access before every live session. This covers your StripScore, Favourites count, Fan Club subscribers, private show rating, income per hour, and weekly earnings tracker.'],
                            ['block_type' => 'video',   'title'   => 'Broadcast Center — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Broadcast Center — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Open Broadcast Center from the green button on the homepage or from the top navigation bar',
                                'Locate each metric: StripScore (chart icon), Favourites (heart icon), Fan Club (diamond/heart), Private Show Rating (camera), Income Per Hour (coin icon), Earned This Week (wallet)',
                                'Scroll to the bottom of the page and tap Set Up Broadcast to enter the broadcaster room',
                                'Note the security reminder before you go live — never share personal details or respond to token manipulation offers',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Your StripScore climbs with consistent streaming — a higher score improves your position in model rankings',
                                'Favourites act as a watchlist for customers — the more you have, the more people are notified when you go live',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 15 ── Promo Period ───────────────────────────────────
            [
                'title'       => 'Promo Period',
                'description' => 'Your first 30 days are peak traffic — understand the Promo Period and how to make the most of it.',
                'lessons'     => [
                    [
                        'title'    => 'Promo Period',
                        'overview' => 'During your first 30 days after logging in, Stripchat places you at the top of the page for new customers. This is your peak traffic period — going live as much as possible during this window is the single fastest way to build your database and fanbase from the start.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'Promo Period'],
                            ['block_type' => 'text',    'content' => 'During your first 30 days after logging in, Stripchat places you at the top of the page for new customers. This is your peak traffic period — going live as much as possible during this window is the single fastest way to build your database and fanbase from the start.'],
                            ['block_type' => 'video',   'title'   => 'Promo Period — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Promo Period — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Go live as frequently as possible during your first 30 days',
                                'Each session during this period builds your Friends list, Favourites count, and StripScore simultaneously',
                                'Engage with every person who enters your room — first impressions create loyal regulars',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'This is the single most important period of your Stripchat career — treat it like a launch campaign',
                                'Consistency beats duration: three 2-hour sessions per week beats one 6-hour session',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 16 ── Setting Your Rates ────────────────────────────
            [
                'title'       => 'Setting Your Rates',
                'description' => 'A clear pricing guide for your private show rates and how to set them correctly.',
                'lessons'     => [
                    [
                        'title'    => 'Setting Your Rates',
                        'overview' => "A clear pricing guide for your Private Show, Exclusive Private Show, Spy rate, Group Show, and Ticket Show — based on Kayla's personally tested rates. These prices have been set by the agency for you, but this lesson explains what each rate means and when and how to adjust them.",
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'Setting Your Rates'],
                            ['block_type' => 'text',    'content' => "A clear pricing guide for your Private Show, Exclusive Private Show, Spy rate, Group Show, and Ticket Show — based on Kayla's personally tested rates. These prices have been set by the agency for you, but this lesson explains what each rate means and when and how to adjust them."],
                            ['block_type' => 'video',   'title'   => 'Setting Your Rates — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Setting Your Rates — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Locate Your Pricing in the Broadcast Center settings',
                                'Confirm your rates match the agency-recommended guide: Private Show 44 tk (3-min min), Exclusive Private 60 tk (2-min min), Spy 16 tk, Group Show 32 tk, Ticket Show 90 tk',
                                'If a regular is willing to pay more, you can increase your rate for that session',
                                'If your room is quiet, you can temporarily lower rates to attract more volume',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'The minimum minute settings protect you from time-wasters — do not remove them',
                                'You can offer Fan Club discounts on your rates — use this as a loyalty reward for subscribers',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 17 ── My Show Controls ──────────────────────────────
            [
                'title'       => 'My Show Controls',
                'description' => 'Set up your show activities, tip goals, interactive toys, and offline status.',
                'lessons'     => [
                    [
                        'title'    => 'My Show Controls',
                        'overview' => "A walkthrough of the My Show Controls section inside the broadcaster — covering your show activities (what you do in public, private, and exclusive shows), tip goals, interactive toys (Lovense/Kiiroo), today's show topic, and how to enable or disable show recording.",
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'My Show Controls'],
                            ['block_type' => 'text',    'content' => "A walkthrough of the My Show Controls section inside the broadcaster — covering your show activities (what you do in public, private, and exclusive shows), tip goals, interactive toys (Lovense/Kiiroo), today's show topic, and how to enable or disable show recording."],
                            ['block_type' => 'video',   'title'   => 'My Show Controls — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'My Show Controls — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                "Go to My Details → My Show's Activities and select only what you are comfortable performing",
                                'Set your tip Goal in My Show Controls — start with a small goal and increase it as tipping builds',
                                "Update your Topic of Today's Show before every live session so viewers know what to expect",
                                'Enable Record Show if you want to offer recorded content as a passive income stream',
                                'Set an Offline Status message so customers know when to expect you back',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Private shows can be spied on by other users — exclusive shows are fully one-to-one; choose based on your comfort level',
                                'Interactive toys (Lovense) greatly increase engagement and tip volume — worth investing in once you are settled',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 18 ── My Feed ────────────────────────────────────────
            [
                'title'       => 'My Feed',
                'description' => 'How to use your Stripchat Feed to stay visible to your audience between live sessions.',
                'lessons'     => [
                    [
                        'title'    => 'My Feed',
                        'overview' => 'How to use your Stripchat Feed like a social media profile — posting photos, videos, updates, and highlights to stay visible to your audience between live sessions. The Feed automatically creates posts when you upload content, keeping your profile active at all times.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'My Feed'],
                            ['block_type' => 'text',    'content' => 'How to use your Stripchat Feed like a social media profile — posting photos, videos, updates, and highlights to stay visible to your audience between live sessions. The Feed automatically creates posts when you upload content, keeping your profile active at all times.'],
                            ['block_type' => 'video',   'title'   => 'My Feed — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'My Feed — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Navigate to My Feed from the top navigation in the broadcaster area',
                                'Tap Create Post and upload a photo, video, or short update',
                                'Aim to post to your Feed daily — consistency keeps your profile attractive to new and returning customers',
                                'Upload photos and videos to your profile — the Feed will automatically create posts from them',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Customers who visit your profile between streams stay warmer and are more likely to return when you go live',
                                'Treat your Feed exactly like Instagram — regular, engaging content builds a loyal audience',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 19 ── Rules & Safety ────────────────────────────────
            [
                'title'       => 'Rules & Safety',
                'description' => 'The non-negotiable safety rules that keep your account and your income protected.',
                'lessons'     => [
                    [
                        'title'    => 'Rules & Safety',
                        'overview' => 'The non-negotiable safety rules for streaming on Stripchat — including the strict rule that anyone who appears on camera must be age-verified and registered in advance. Appearing on stream with an unverified person can result in an instant account ban.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'Rules & Safety'],
                            ['block_type' => 'text',    'content' => 'The non-negotiable safety rules for streaming on Stripchat — including the strict rule that anyone who appears on camera must be age-verified and registered in advance. Appearing on stream with an unverified person can result in an instant account ban.'],
                            ['block_type' => 'video',   'title'   => 'Rules & Safety — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Rules & Safety — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Never allow anyone on camera who has not been verified and approved in advance',
                                'If you want to add someone to your stream: contact the admin/team and they will guide you through verification',
                                'Report any suspicious activity — token manipulation offers, pressure to share personal details — directly to the team',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Official Stripchat admin accounts have blue nicknames — any other account claiming to be admin is a scam',
                                'Never share your personal address, phone number, or off-platform contact details with any user',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 20 ── Going to Live / Camera & Mic / Start Show ─────
            [
                'title'       => 'Going to Live / Camera & Mic / Start Show',
                'description' => 'The complete guide to preparing for your live session, checking your camera and mic, and starting your show.',
                'lessons'     => [
                    [
                        'title'    => 'Going to Live / Camera & Mic / Start Show',
                        'overview' => "The complete guide to preparing for your live session — from the pre-stream checklist and checking your camera and mic setup, to tapping Start Show and going live with confidence. Everything you need to do before and during the moment you go live.",
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'Going to Live / Camera & Mic / Start Show'],
                            ['block_type' => 'text',    'content' => "The complete guide to preparing for your live session — from the pre-stream checklist and checking your camera and mic setup, to tapping Start Show and going live with confidence. Everything you need to do before and during the moment you go live."],
                            ['block_type' => 'video',   'title'   => 'Going to Live / Camera & Mic / Start Show — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Going to Live / Camera & Mic / Start Show — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Open Broadcast Center and scroll down past the chat window to review your settings',
                                "Confirm your tip goal, topic of show, and show activities are updated for today's session",
                                'Send your mass message to your Friends list before going live',
                                'Scroll back up to the broadcaster interface — you should see yourself in the preview screen',
                                'If prompted, allow camera and microphone permissions in your device settings',
                                'Confirm your camera is showing a clear, well-lit image before proceeding',
                                'If the stream quality shows as BAD (red indicator), check your internet connection before going live',
                                'Tap the green Start Show button in your broadcaster interface',
                                "A grey pop-up will appear — confirm: Persons in show, Goal, and Topic of Today's Show",
                                'Check the terms and conditions checkbox',
                                'Tap the green Go Live button (camera emoji) — you are now live',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Your rates, tip menus, and goals have already been set up by the agency — you can adjust them but they are ready to go',
                                'Always do a quick visual check before every stream — lighting and framing make a significant difference to how viewers perceive you',
                                'This confirmation pop-up appears before every stream — do not skip reading it; it confirms your settings are correct',
                                'If the indicator shows BAD (red), stop and check your internet before continuing',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 21 ── Live Room ──────────────────────────────────────
            [
                'title'       => 'Live Room',
                'description' => 'How to manage your live room, interact with viewers, and read the chat during a stream.',
                'lessons'     => [
                    [
                        'title'    => 'Live Room',
                        'overview' => 'What happens once you are live — how to interact with viewers in the public chat, how to use the viewer panel to track who is in your room, and how to respond to the room to keep energy high and viewers engaged.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'Live Room'],
                            ['block_type' => 'text',    'content' => 'What happens once you are live — how to interact with viewers in the public chat, how to use the viewer panel to track who is in your room, and how to respond to the room to keep energy high and viewers engaged.'],
                            ['block_type' => 'video',   'title'   => 'Live Room — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Live Room — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Monitor the chat box in the centre of the screen — respond to messages by speaking or typing',
                                'Check the viewer count at the bottom of the screen (e.g. "6 viewers")',
                                'Use mute, block, or report for any user who behaves inappropriately — then escalate to the team',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Always greet new viewers by name when they enter — it immediately makes them feel seen',
                                'Do not worry if the room is quiet at first — stay engaging, and viewers will come',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 22 ── Users ──────────────────────────────────────────
            [
                'title'       => 'Users',
                'description' => 'How to view individual user information to identify your best potential spenders while live.',
                'lessons'     => [
                    [
                        'title'    => 'Users',
                        'overview' => 'How to view individual user information while you are live — including their token balance, account age, and location — and how to use this to identify who in your room is most likely to tip or request a private show.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'Users'],
                            ['block_type' => 'text',    'content' => 'How to view individual user information while you are live — including their token balance, account age, and location — and how to use this to identify who in your room is most likely to tip or request a private show.'],
                            ['block_type' => 'video',   'title'   => 'Users — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Users — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                "Tap or hover over a user's name in the live chat to open their profile card",
                                'Review their token balance, account registration date, and location',
                                'Users with high token balances and older accounts are your most likely spenders',
                                'Use this information to prioritise who you engage with directly during a busy session',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Greeting a viewer by name when you can see they have tokens immediately increases the chance they tip',
                                'Account age matters — longer-registered users are more experienced buyers and understand the platform',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 23 ── Private & Exclusive Shows ──────────────────────
            [
                'title'       => 'Private & Exclusive Shows',
                'description' => 'Understand the difference between private and exclusive shows and how to manage each type.',
                'lessons'     => [
                    [
                        'title'    => 'Private & Exclusive Shows',
                        'overview' => 'What happens when a viewer requests a private or exclusive show, how to accept quickly, what the session interface looks like, and the key difference between the two: private shows allow spy viewers, while exclusive shows are strictly one-to-one.',
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'Private & Exclusive Shows'],
                            ['block_type' => 'text',    'content' => 'What happens when a viewer requests a private or exclusive show, how to accept quickly, what the session interface looks like, and the key difference between the two: private shows allow spy viewers, while exclusive shows are strictly one-to-one.'],
                            ['block_type' => 'video',   'title'   => 'Private & Exclusive Shows — Presentation Video'],
                            ['block_type' => 'heading', 'title'   => 'Now Follow Along'],
                            ['block_type' => 'video',   'title'   => 'Private & Exclusive Shows — Walkthrough Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'When a private or exclusive show pop-up appears, tap Accept as quickly as possible — most users will not wait long',
                                'Your public chat room disappears and you enter the private session interface',
                                'Communicate with the customer by speaking or typing — ask questions, build connection, keep them in the session longer',
                                'Private shows: other viewers can pay to spy on the session (adds to your total earnings)',
                                'Exclusive shows: fully one-to-one — no spy viewers under any circumstances',
                                'Note: Exclusive shows do NOT offer a recording save pop-up when they end — this is by design',
                                'When the private session ends, choose whether to save the recording to your video library',
                                'Tap Start Show to return to your public live room immediately',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Private shows can be spied on by others — this is a feature, not a flaw; spy viewers add to your total earnings',
                                'Exclusive shows command a higher price because of the one-to-one privacy guarantee — use this as a selling point',
                                'If you want to create content to reuse or sell later, choose private shows — recording is available on private but not exclusive',
                            ])],
                        ],
                    ],
                ],
            ],

            // ─── MODULE 24 ── Final Words from Boss Doll ────────────────────
            [
                'title'       => 'Final Words from Boss Doll',
                'description' => "Kayla's final words as you complete your Stripchat Phase One training.",
                'lessons'     => [
                    [
                        'title'    => 'Final Words from Boss Doll',
                        'overview' => "You have now completed your full Stripchat walkthrough. You understand how to navigate the platform, build your customer database, go live, manage your room, set your rates, and run private and exclusive shows. This is where you step into your power — stay consistent, stay confident, and watch your results grow.",
                        'blocks'   => [
                            ['block_type' => 'heading', 'title'   => 'Final Words from Boss Doll'],
                            ['block_type' => 'text',    'content' => "You have now completed your full Stripchat walkthrough. You understand how to navigate the platform, build your customer database, go live, manage your room, set your rates, and run private and exclusive shows. This is where you step into your power — stay consistent, stay confident, and watch your results grow."],
                            ['block_type' => 'video',   'title'   => 'Final Words — Presentation Video'],
                            ['block_type' => 'steps',   'content' => implode("\n", [
                                'Review each module before your very first live session',
                                'Send your first mass message to your Friends list the moment you go live',
                                'Set a target for your first 30-day Promo Period and commit to streaming consistently',
                                'Join the Paradise Dolls community and reach out if you ever need support',
                            ])],
                            ['block_type' => 'tips',    'content' => implode("\n", [
                                'Phase Two of this course covers all the advanced tips, tricks, and extras that take your earnings to the next level — complete it once you have found your rhythm',
                                'Kayla and the team are always available — never hesitate to ask for help',
                            ])],
                        ],
                    ],
                ],
            ],

        ];
    }
}
