<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonContentBlock;
use Illuminate\Database\Seeder;

class AdultWorkCourseSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::updateOrCreate(
            ['slug' => 'adultwork-boss-doll-blueprint'],
            [
                'title'          => 'Boss Doll Blueprint — AdultWork',
                'platform_label' => 'AdultWork',
                'platform_color' => '#7B2D8B',
                'is_published'   => true,
                'sort_order'     => 13,
                'description'    => 'Your complete desktop walkthrough for AdultWork — one of the longest-running premium adult platforms online. Learn how to broadcast, set your rates, position yourself for maximum traffic, feature your profile, and upload content.',
            ]
        );

        $modules = [

            // (1) Introduction
            [
                'title'       => 'Introduction',
                'description' => 'A welcome introduction to AdultWork and what you can expect from this course.',
                'lessons'     => [
                    [
                        'title'      => 'Introduction',
                        'intro_only' => true,
                        'overview'   => 'Welcome to the AdultWork Boss Doll Blueprint. Kayla will introduce you to AdultWork — one of the longest-running premium adult entertainment platforms online, originally launched in the UK in 2003. This platform combines webcam streaming, private calls, phone chat, messaging services, adult content sales, and profile advertising all in one place, giving you multiple income streams from a single account. Your dedicated support team — Julio, Ely, and Jhonpaul — are here to help you every step of the way with live support, real-time help, and a safe and secure environment. Your account has already been fully set up by the Paradise Dolls team, so you will be ready to start broadcasting straight away.',
                        'steps'      => json_encode([
                            'Watch the welcome video from Kayla',
                            'Note that your dedicated support team (Julio, Ely, Jhonpaul) is available for real-time help',
                            'Understand that AdultWork offers multiple income streams: webcam, private calls, phone chat, messaging, content sales',
                            'Know that this is a premium pay-per-minute platform as well as a content sales marketplace',
                            'Your account is already fully set up — you do not need to register again',
                        ]),
                        'tips'       => json_encode([
                            'AdultWork has been running since 2003 and has a large, established international audience',
                            'Your support team is always here — live support is available in real time',
                            'Many creators combine AdultWork with other platforms as part of a wider online business strategy',
                            'This platform rewards consistency and investment — follow the guide closely for best results',
                        ]),
                    ],
                ],
            ],

            // (2) About Adultwork
            [
                'title'       => 'About Adultwork',
                'description' => 'Learn what AdultWork is, how it works, and why it is one of the most established adult platforms online.',
                'lessons'     => [
                    [
                        'title'    => 'About Adultwork',
                        'overview' => 'AdultWork is one of the most established premium adult platforms in the world, operating since 2003. Unlike token-based freemium sites, AdultWork operates on a pay-per-minute model — meaning customers pay directly for every minute they spend in your room. It is a multi-service marketplace offering webcam broadcasting, private video calls, phone chat, messaging, adult content sales, and profile advertising all under one roof. This gives you multiple income streams from a single account.',
                        'steps'    => json_encode([
                            'Understand that AdultWork is a pay-per-minute platform — customers pay for every minute they spend with you',
                            'Note the multiple income streams available: live webcam, private calls, phone chat, messaging, content sales',
                            'AdultWork has been running since 2003 and has a large, established UK and international audience',
                            'Your account is fully set up by the Paradise Dolls team — you do not need to register again',
                            'The platform operates in GBP (British pounds) — all rates and earnings are in £',
                        ]),
                        'tips'     => json_encode([
                            'AdultWork rewards consistency — the more regularly you go live, the better your ranking and traffic',
                            'The pay-per-minute model means serious customers who stay in your room generate consistent earnings',
                            'Unlike freemium sites, AdultWork customers expect premium content — position yourself confidently',
                            'Take time to understand the platform before going live — this course covers everything you need',
                        ]),
                    ],
                ],
            ],

            // (3) Logging in And Get Started
            [
                'title'       => 'Logging in And Get Started',
                'description' => 'How to access your AdultWork account using your provided login credentials.',
                'lessons'     => [
                    [
                        'title'    => 'Logging in And Get Started',
                        'overview' => 'AdultWork is one of the longest-running premium adult platforms on the web. When you first come onto the site, head to the official website at www.adultwork.com and you will be taken straight to the login page. From there, simply enter your provided login details to access your account, navigate the platform, manage your profile, and start exploring all the different earning features available to you.',
                        'steps'    => json_encode([
                            'Open your desktop browser and go to www.adultwork.com',
                            'You will arrive at the login page with the login box displayed in the centre of the screen',
                            'Enter your Nickname in the field highlighted Yellow',
                            'Enter your Password in the field highlighted Orange',
                            'Optionally tick Remember Me so your details are saved',
                            'Click the Purple Login Button to access your account',
                            'IMPORTANT: Do NOT create a new account or register again — your account is already set up for you',
                        ]),
                        'tips'     => json_encode([
                            'Your login details (Nickname and Password) will have been provided by the Paradise Dolls team',
                            'You can also log in via Google if your account is connected',
                            'If you forget your password or nickname, use the Forgot Password or Nickname link on the login page',
                            'Never create a duplicate account — always use the provided login credentials',
                        ]),
                    ],
                ],
            ],

            // (4) Accessing Broadcast Interface
            [
                'title'       => 'Accessing Broadcast Interface',
                'description' => 'Locate the Broadcast Now button and understand the broadcaster interface layout.',
                'lessons'     => [
                    [
                        'title'    => 'Accessing Broadcast Interface',
                        'overview' => 'Before exploring the rest of the platform, the first priority is learning how to go live and start your broadcast. Your account is already fully set up with your profile, bio, and account details completed, meaning you can begin broadcasting straight away. To open the broadcaster, click the Broadcast Now button at the top of the navigation bar. This opens the AdultWork broadcaster interface — where you will host all your live shows, whether in premium pay-per-minute or freemium/public chat format.',
                        'steps'    => json_encode([
                            'Locate the Broadcast Now button in the top navigation bar (highlighted yellow)',
                            'Click Broadcast Now — a new browser window will open with the broadcaster interface',
                            'On the left side you will see your webcam window where your camera feed will display',
                            'On the right side you will see the chat/message area',
                            'At the top right you will see the green Start button and orange Boost button',
                            'You will see the status OFFLINE until the Start button is clicked',
                            'Do not press Start yet — go through the settings lessons first to ensure everything is configured correctly',
                        ]),
                        'tips'     => json_encode([
                            'The broadcaster opens in a separate window from the main AdultWork website',
                            'Your account is fully set up so you can start broadcasting without any additional setup',
                            'Do not press Start yet — review your settings in the following lessons first',
                            'The orange Boost button is used to boost your viewership via DirectCam Boost',
                        ]),
                    ],
                ],
            ],

            // (5) Consent Required
            [
                'title'       => 'Consent Required',
                'description' => 'Understand and complete the consent confirmation required before broadcasting.',
                'lessons'     => [
                    [
                        'title'    => 'Consent Required',
                        'overview' => 'Before you can start broadcasting on AdultWork, a Consent Required confirmation box may appear on screen when you click Start. This asks you to confirm that the broadcast declaration is true before continuing live. Below the consent declaration you will also see the broadcasting modes currently enabled on your account — typically Group Mode and Private Mode. Always read the declaration carefully before accepting.',
                        'steps'    => json_encode([
                            'When you click Start, a Consent Required box may appear on screen',
                            'Read the declaration: "I am the verified account holder and confirm I am the only person to appear in this broadcast"',
                            'Tick the checkbox to confirm the declaration is true',
                            'Below the consent you will see Modes Enabled — Group Mode and Private Mode listed',
                            'Group Mode: allows multiple customers to join and pay for your session simultaneously',
                            'Private Mode: a one-to-one exclusive paid session with a single customer',
                            'Once you have ticked the consent box, click the purple Confirm button to enter the broadcaster',
                        ]),
                        'tips'     => json_encode([
                            'Always read the consent declaration before accepting — it is a legal confirmation',
                            'Your broadcasting modes (Group and Private) will already be pre-selected and enabled',
                            'You must be the verified account holder and the only person appearing on camera',
                            'Broadcast modes will be configured in detail in the Broadcast Mode Settings module',
                        ]),
                    ],
                ],
            ],

            // (6) Broadcast Overview
            [
                'title'       => 'Broadcast Overview',
                'description' => 'A full breakdown of the broadcaster interface once you are connected and live.',
                'lessons'     => [
                    [
                        'title'    => 'Broadcast Overview',
                        'overview' => 'Once you press the Start button, your broadcaster will begin connecting and preparing your stream to go live. System messages will appear in the chat panel confirming connection status: Connecting to Server, Connected, Ready. The webcam window on the left (highlighted blue) shows your live camera feed. Near your webcam window is a microphone icon — Green means microphone ON, Red means microphone OFF. Next to the microphone button is a small settings cog where you can adjust stream preferences.',
                        'steps'    => json_encode([
                            'Click the green Start button on the right-hand side of the broadcaster',
                            'Watch the chat panel for system messages: "Connecting to Server, Please Wait..." → "Connected to the Server, Ready"',
                            'A connection test will run — wait until it displays Connected',
                            'Your webcam window (highlighted blue, left side) shows your live camera feed',
                            'Below the webcam window you will see three icons: microphone, settings cog, and a third icon',
                            'Microphone icon: Green = ON, Red = OFF — always check this before going live',
                            'Settings cog: opens stream preferences (modes, devices, chat, sounds)',
                            'Always check your camera, always check your microphone, double-check streaming mode, make sure you are ready before pressing Start',
                        ]),
                        'tips'     => json_encode([
                            'Always double-check which streaming mode you are in BEFORE pressing Start',
                            'In premium mode your camera is hidden from free viewers — they must pay to see you',
                            'In free mode you are visible to all visitors immediately on entering your room',
                            'Wait for the Connected confirmation before expecting viewers or interactions',
                        ]),
                    ],
                ],
            ],

            // (7) Broadcast Mode Settings
            [
                'title'       => 'Broadcast Mode Settings',
                'description' => 'Access the broadcaster settings Modes tab and understand how to save your configuration.',
                'lessons'     => [
                    [
                        'title'    => 'Broadcast Mode Settings',
                        'overview' => 'Inside the broadcaster settings (accessed via the settings cog), the Modes tab gives you full control over how viewers access your stream. You will see options for Free Mode, Group Mode, and Private Mode. After configuring all your modes and rates, you must click the purple Save Changes button to apply everything before going live.',
                        'steps'    => json_encode([
                            'Click the settings cog (gear icon) below your webcam window to open the Settings panel',
                            'Click the Modes tab at the top of the settings panel',
                            'You will see three mode sections: Free Mode (Green), Group Mode (Pink), Private Mode (Yellow)',
                            'Free Mode: optional — allows clothed free previews for a limited time period',
                            'Group Mode: tick Enable Group Mode and set your Group Rate Per Minute',
                            'Private Mode: tick Enable Private Mode and set your Private Rate Per Minute (always higher than group)',
                            'After adjusting all modes and rates, click the purple Save Changes button at the bottom',
                            'Close the settings panel — your settings are now live for your broadcast',
                        ]),
                        'tips'     => json_encode([
                            'Always save before going live — unsaved settings will revert to previous values',
                            'Keep both Group Mode and Private Mode ticked at all times for maximum earning flexibility',
                            'Never enable Free Preview to Guests — only allow registered members access to your free preview',
                            'Pre-live final checklist: camera visible, microphone working, mode confirmed, rates saved',
                        ]),
                    ],
                ],
            ],

            // (8) Devices & Video Settings
            [
                'title'       => 'Devices & Video Settings',
                'description' => 'Configure your camera, microphone, and video quality in the broadcaster Devices tab.',
                'lessons'     => [
                    [
                        'title'    => 'Devices & Video Settings',
                        'overview' => 'The Devices tab inside broadcaster settings is where you configure your camera, microphone, and video quality. The Video Device section (highlighted green) shows your currently active camera. The Audio Device section (highlighted red) is where you select your microphone. The Video Quality section (highlighted yellow) lets you set your stream resolution. Always verify all three before going live.',
                        'steps'    => json_encode([
                            'Click the settings cog below your webcam window to open the Settings panel',
                            'Click the Devices tab (highlighted blue) at the top of the settings panel',
                            'VIDEO DEVICE: find the Video Device section (highlighted green), click the dropdown to select your camera (e.g. HD Webcam, SplitCam Virtual Camera)',
                            'If your camera is not listed, click Refresh Devices at the bottom of the panel',
                            'AUDIO DEVICE: find the Audio Device section (highlighted red), click the dropdown to select your microphone',
                            'Check the audio level bar below the dropdown to confirm your mic is picking up sound',
                            'VIDEO QUALITY: find the Video Quality section (highlighted yellow), recommended setting: 1280x720 HD',
                            'If your stream lags or freezes, reduce the video quality setting to match your internet speed',
                            'Always verify: correct camera selected, correct microphone selected, quality matched to your setup',
                        ]),
                        'tips'     => json_encode([
                            'Use an HD webcam or high-quality laptop camera for the best stream quality',
                            'Always verify your correct microphone is selected — poor audio drives customers away',
                            '1280x720 HD is the recommended setting for most setups — stable and clear',
                            'If the camera or mic is not working after selection, click Refresh Devices and try again',
                        ]),
                    ],
                ],
            ],

            // (9) Important Reminder
            [
                'title'       => 'Important Reminder',
                'description' => 'Key rules and important reminders to keep in mind before and during your broadcasts.',
                'lessons'     => [
                    [
                        'title'    => 'Important Reminder',
                        'overview' => 'Before going live on AdultWork there are some important rules and reminders to keep in mind. These platform guidelines protect both you and your account. Always ensure you are the only person appearing in the broadcast, never share personal contact details in chat or on your profile, and always complete your consent before your content goes live. Following these rules keeps your account in good standing and ensures a safe, professional broadcasting experience.',
                        'steps'    => json_encode([
                            'You must be the verified account holder — only you should appear in your broadcast',
                            'Never share personal contact details (phone number, email, social media) in chat or on your profile',
                            'Always complete the consent confirmation before broadcasting or uploading new content',
                            'Do NOT create a second account — always use your provided login credentials',
                            'Never skip the pre-live checklist: camera, microphone, mode, rates — check everything before pressing Start',
                            'Never enable Free Preview to Guests — only registered members should have free access',
                            'If you have any questions or run into issues, contact the Paradise Dolls support team immediately',
                        ]),
                        'tips'     => json_encode([
                            'Your account safety is your top priority — following platform rules protects your earnings and your account',
                            'If a customer asks for personal contact details, politely decline — keep all communication within the platform',
                            'When in doubt, reach out to your support team (Julio, Ely, Jhonpaul) before taking any action',
                            'Consistent rule-following builds a trusted, long-term account that earns more over time',
                        ]),
                    ],
                ],
            ],

            // (10) Accessing your profile details & webcam
            [
                'title'       => 'Accessing your profile details & webcam',
                'description' => 'Navigate to your Profile Details and open the Webcam management tab.',
                'lessons'     => [
                    [
                        'title'    => 'Accessing your profile details & webcam',
                        'overview' => 'To manage your profile settings, rates, positioning, and webcam configuration, navigate to My Details in the top navigation bar. From there go to Edit Profile > Profile Details. Inside Profile Details you will find tabs along the top. Click the Webcam tab to access your full DirectCam management panel.',
                        'steps'    => json_encode([
                            'Click My Details (highlighted purple) in the top navigation bar',
                            'A dropdown menu appears with sections including Registration Details, Edit Profile, Movies, Pictures, Manage Consent, Availability, and more',
                            'Hover over Edit Profile — a small triangle arrow appears to the right',
                            'Click the triangle arrow to open a sub-menu',
                            'Click Profile Details to access your main profile settings area',
                            'Inside Profile Details, look at the tabs along the top: General, Personal Details, Pictures, Escort, Webcam',
                            'Click the Webcam tab — this opens your DirectCam management panel',
                        ]),
                        'tips'     => json_encode([
                            'Profile Details is where you manage your bio, summary, photos, personal details, and webcam settings',
                            'The Webcam tab is your primary hub for everything related to stream management on AdultWork',
                            'Your profile was fully set up during onboarding but you can personalise it at any time',
                            'If using the new beta interface, you can also reach your profile via clicking your username initial (top right) then My Profile',
                        ]),
                    ],
                ],
            ],

            // (11) Understanding your webcam settings menu
            [
                'title'       => 'Understanding your webcam settings menu',
                'description' => 'Learn what each section of the Webcam management menu controls.',
                'lessons'     => [
                    [
                        'title'    => 'Understanding your webcam settings menu',
                        'overview' => 'Once you open the Webcam tab, a new page appears with a management menu on the left side. This menu controls how your profile appears, how your stream works, and how viewers interact with your room. Your account will already have been set up during onboarding, but this walkthrough shows you where to go when you need to update anything.',
                        'steps'    => json_encode([
                            'After clicking the Webcam tab, a new page loads with a left-side management menu',
                            'Under Management you will see: Dashboard, Availability, Positioning, Shows, Special Offers',
                            'Under Account Settings you will see: DirectCam Settings, Category & Strapline',
                            'Dashboard: overview of your DirectCam activity and availability calendar',
                            'Availability: set your streaming schedule',
                            'Positioning: bid for your position on the front page (covered in detail in a later module)',
                            'DirectCam Settings: manage your rates, Free Preview Mode, Group Mode, Private Mode',
                            'Category & Strapline: set your category and tagline for search discoverability',
                        ]),
                        'tips'     => json_encode([
                            'Ideally your account is already set up from onboarding — use this menu when you need to change or update anything',
                            'DirectCam Settings is where you manage rates and toggle modes on and off',
                            'Positioning and Featuring are both accessed through this menu and are essential daily tasks',
                            'The Webcam tab can also be accessed from the new beta interface via username initial > My DirectCam',
                        ]),
                    ],
                ],
            ],

            // (12) Understanding Rates, Free & Private Modes
            [
                'title'       => 'Understanding Rates, Free & Private Modes',
                'description' => 'Learn how Free Mode, Group Mode, and Private Mode work and how to configure them correctly.',
                'lessons'     => [
                    [
                        'title'    => 'Understanding Rates, Free & Private Modes',
                        'overview' => 'Inside DirectCam Settings you will find three broadcasting modes: Free Mode, Group Mode, and Private Mode. Free Mode (highlighted green) is optional — it allows clothed free previews for a limited time to attract viewers. Group Mode (highlighted pink) allows multiple customers to pay per minute simultaneously. Private Mode (highlighted yellow) is a one-to-one exclusive session at a higher rate. Understanding how each mode works and how to price them correctly is essential for maximising your earnings.',
                        'steps'    => json_encode([
                            'Go to My Details > Edit Profile > Profile Details > Webcam tab > DirectCam Settings',
                            'FREE MODE (green section): optionally tick Enable Free Preview Mode — set Max period to 5 minutes, Within any period to 12 hours',
                            'IMPORTANT: NEVER enable Free Preview to Guests — only allow registered members',
                            'GROUP MODE (pink section): tick Enable Group Mode, set Group rate per min (example: £1.99/min)',
                            'Group rate is lower per customer but earns from multiple viewers simultaneously',
                            'PRIVATE MODE (yellow section): tick Enable Private Mode, set Private rate per min (example: £3.30/min)',
                            'Private rate must always be HIGHER than group rate — it is an exclusive one-to-one session',
                            'Always keep both Group Mode and Private Mode ticked — never untick them',
                            'Click Save to confirm all rate and mode settings',
                        ]),
                        'tips'     => json_encode([
                            'Never enable free preview to guests — only allow registered site members',
                            'Group rate is lower per person but earns from multiple customers at the same time',
                            'Private rate should always be higher — customers pay a premium for exclusive undivided attention',
                            'Always keep both Group and Private Mode enabled for maximum earning flexibility every session',
                        ]),
                    ],
                ],
            ],

            // (13) Changing your Rates
            [
                'title'       => 'Changing your Rates',
                'description' => 'Update your Group and Private per-minute rates in DirectCam Settings.',
                'lessons'     => [
                    [
                        'title'    => 'Changing your Rates',
                        'overview' => 'Your rates directly control how much customers pay per minute during your live sessions. Group and Private rates are set inside the DirectCam Settings section of your Webcam menu. The example rates shown in training are £1.99 per minute for Group and £3.30 per minute for Private — all rates are in GBP (British pounds). Always have the mode checkboxes ticked.',
                        'steps'    => json_encode([
                            'Go to My Details > Edit Profile > Profile Details > Webcam tab > DirectCam Settings (left-side menu)',
                            'On the DirectCam Settings page, find the Group Mode section (highlighted green)',
                            'Ensure Enable Group Mode checkbox is ticked',
                            'Set Group rate per min in the text field (example: £1.99)',
                            'Find the Private Mode section (highlighted yellow)',
                            'Ensure Enable Private Mode checkbox is ticked',
                            'Set Private rate per min in the text field (example: £3.30)',
                            'All rates are in GBP (£) — check regional settings if unsure',
                            'Click the Save button to confirm your rates',
                        ]),
                        'tips'     => json_encode([
                            'Always keep both Group and Private Mode boxes ticked — never untick them',
                            'Do not underprice yourself — confidence and positioning matter as much as rate',
                            'Higher rates often attract more serious, committed, and higher-spending customers',
                            'As your audience and confidence grow, gradually increase your rates over time',
                        ]),
                    ],
                ],
            ],

            // (14) Turning Free & Private modes on/off
            [
                'title'       => 'Turning Free & Private modes on/off',
                'description' => 'Configure Free Preview Mode in DirectCam Settings to attract and convert viewers.',
                'lessons'     => [
                    [
                        'title'    => 'Turning Free & Private modes on/off',
                        'overview' => 'Inside DirectCam Settings, you can also configure Free Preview Mode — an optional setting that allows clothed free sessions to entice viewers into paid group or private sessions. When premium traffic is quiet, free mode can bring significantly more viewers to your room. The recommended approach is a maximum of 5 minutes free preview every 12 hours to prevent freeloaders.',
                        'steps'    => json_encode([
                            'In DirectCam Settings, find the Free Preview Mode section (highlighted pink)',
                            'Optionally tick Enable Free Preview Mode to allow free clothed previews',
                            'Set Max period to the recommended dropdown option: 5 minutes',
                            'Set Within any period of to the recommended dropdown option: 12 hours',
                            'This controls how long a viewer can watch for free before being prompted to pay',
                            'Leave Enable Group Mode and Enable Private Mode ticked at all times regardless',
                            'Click Save to apply your changes',
                        ]),
                        'tips'     => json_encode([
                            'Free mode is entirely optional — many models run purely on premium Group and Private modes',
                            'If traffic is slow, enabling free mode for short periods can attract viewers who then convert to paying customers',
                            'A 5-minute max every 12 hours strikes the right balance between visibility and preventing freeloading',
                            'Free chat viewers can tip credits and are natural candidates to convert to private or group sessions',
                        ]),
                    ],
                ],
            ],

            // (15) Understanding Positioning
            [
                'title'       => 'Understanding Positioning',
                'description' => 'Master the AdultWork positioning bidding system to get maximum front-page traffic.',
                'lessons'     => [
                    [
                        'title'    => 'Understanding Positioning',
                        'overview' => 'Positioning controls where your profile appears in AdultWork search results and on the front page. It is a bidding system where you use credits to bid on 1-hour time slots. The colour-coded status system tells you where you will be placed: Red (1–2 credits) = bottom of page, Amber (3–5 credits) = middle of page, Green/Strong (5–9 credits) = top of page. The top of the page brings the most traffic. This is a three-step process: Select Bidding Hours → Refine Bids → Confirmation.',
                        'steps'    => json_encode([
                            'Go to My Details > Edit Profile > Profile Details > Webcam tab > Positioning (left-side menu, highlighted green arrow)',
                            'A calendar view appears showing hourly time slots with columns: From, To, Bid, VAT, Status',
                            'Slots without bids display "No bid placed" in the Status column',
                            'Click the blue "place some bids now" link to start the bidding process',
                            'Step 1 — Select Bidding Hours: click the hourly time slots you want to be positioned for (they highlight when selected), click the purple Next button',
                            'Step 2 — Refine Bids: for each selected slot enter your bid amount (example: 5, 5, 3, 3 credits), check the Status column — aim for Strong (green), if status shows Poor increase your bid, click the purple Next button again',
                            'Step 3 — Confirmation: review the total credits to be deducted, tick the "I accept and understand the charges" checkbox, click the purple Place Bids button',
                            'Your bids are now confirmed and locked for the selected time slots',
                            'Credits are deducted from your account one slot at a time as each hour starts',
                        ]),
                        'tips'     => json_encode([
                            'Positioning is one of the single most important actions for getting traffic on AdultWork — do this before every session',
                            'Strong/green status means top of the page — maximum traffic and maximum earning potential',
                            'If someone outbids you (status changes to Poor), go back and increase your bid before the slot starts',
                            'Think of positioning as an investment — it consistently pays for itself through increased customer traffic',
                        ]),
                    ],
                ],
            ],

            // (16) Featuring Daily - What it means
            [
                'title'       => 'Featuring Daily - What it means',
                'description' => 'Understand what featuring is and why you should feature your profile every day you go live.',
                'lessons'     => [
                    [
                        'title'    => 'Featuring Daily - What it means',
                        'overview' => 'Featuring is a powerful visibility boost that places your profile on the AdultWork front page for full 24-hour periods. It increases your traffic, attracts new viewers, gains followers, and is especially effective for newer creators building their audience. Featuring is accessed through your account dropdown by clicking your username initial at the top right of the page. The recommendation is to feature every single day you plan to go live.',
                        'steps'    => json_encode([
                            'Click your username initial (e.g. T for TiaDoll) at the top right corner of the AdultWork page (highlighted with green arrow)',
                            'A dropdown account panel opens on the right side of the page',
                            'Scroll down and click Featuring (highlighted pink with a star icon)',
                            'The Featuring page opens — click Feature Profile (highlighted red with a black arrow)',
                            'A calendar appears showing the current and upcoming weeks',
                            'Click the dates you want to feature — selected dates turn gold with purple tick marks beside them',
                            'The credit costs per day are shown next to each date',
                            'Once your dates are selected, click the purple button at the bottom of the page (e.g. "Feature 2 Days AW £20.99")',
                            'Your featuring is confirmed and active for the selected dates',
                        ]),
                        'tips'     => json_encode([
                            'Feature every day you plan to go live — maximum exposure leads to maximum customers',
                            'Think of featuring as advertising spend — it is an investment that pays for itself through increased traffic',
                            'Book featuring in advance when you know your streaming schedule for the week',
                            'You can also feature your Movie Library, Private Gallery, and Erotica for additional content exposure',
                        ]),
                    ],
                ],
            ],

            // (17) Profile Photos
            [
                'title'       => 'Profile Photos',
                'description' => 'Upload and update your profile photos to attract more traffic to your room.',
                'lessons'     => [
                    [
                        'title'    => 'Profile Photos',
                        'overview' => 'Your profile pictures are among the first things customers see in search results and on the AdultWork homepage. Keeping them updated, clear, and high quality directly affects how much traffic your profile attracts. Profile pictures are managed in the Pictures tab of your Profile Details page, with slots for up to 3 images.',
                        'steps'    => json_encode([
                            'Go to My Details > Edit Profile > Profile Details > Pictures tab',
                            'Find the Profile Pictures section showing numbered slots (1, 2, 3)',
                            'To upload or change an image: click the Choose File button next to the relevant slot',
                            'Select your image file from your computer or device',
                            'The filename will appear next to the Choose File button confirming your selection',
                            'Do NOT include contact information, URLs, or other websites\' watermarks in any photos',
                            'Repeat for each image slot you want to fill or update',
                            'Click Save or Save and View at the bottom of the page to apply all changes',
                            'Existing uploaded images are shown as small thumbnails — click Delete to remove them',
                        ]),
                        'tips'     => json_encode([
                            'Fresh and updated profile photos consistently improve traffic and profile performance',
                            'Your best photo should go in slot 1 — it is the first image shown in search results',
                            'If profile pictures are not square, specify which cropped area to use as the square thumbnail version',
                            'Active profiles with high-quality, current photos attract significantly more viewers than stale profiles',
                        ]),
                    ],
                ],
            ],

            // (18) Personal Details
            [
                'title'       => 'Personal Details',
                'description' => 'Manage your profile bio, interests, and personal details for maximum search discoverability.',
                'lessons'     => [
                    [
                        'title'    => 'Personal Details',
                        'overview' => 'Your Personal Details section in Profile Details is where you manage your core profile information including date of birth, gender, orientation, interests, and your bio and summary. The interests tick-box section is particularly important — each category you select increases how often your profile appears in search results across AdultWork.',
                        'steps'    => json_encode([
                            'Go to My Details > Edit Profile > Profile Details',
                            'Click the Personal Details tab (highlighted green at top of the page)',
                            'Date of Birth (green highlight): enter or update — does not need to be your real DOB for privacy',
                            'Gender (yellow highlight): select your gender setting for the profile',
                            'Orientation (red highlight): select your orientation for the profile',
                            'I Enjoy The Following (pink highlight): tick all relevant categories — more categories ticked = more searchable = more traffic',
                            'Summary (purple highlight): short headline customers see first in search — already written by the team',
                            'Details/Bio (purple highlight): full bio and description — already written by team but you can personalise',
                            'Click Save and View or Save at the bottom of the page to apply all changes',
                        ]),
                        'tips'     => json_encode([
                            'Tick as many relevant interest categories as possible — each one adds to your search discoverability',
                            'Your bio and summary were crafted by the Paradise Dolls team during onboarding and can be personalised',
                            'Do NOT include email addresses, phone numbers, URLs, or social media handles in your bio or summary',
                            'Do NOT write "Available Today" or specific dates in your summary — use the Availability page for scheduling',
                        ]),
                    ],
                ],
            ],

            // (19) How to complete your consent
            [
                'title'       => 'How to complete your consent',
                'description' => 'Step-by-step walkthrough of completing the consent declaration for your uploaded content.',
                'lessons'     => [
                    [
                        'title'    => 'How to complete your consent',
                        'overview' => 'Content cannot appear on AdultWork until consent has been confirmed. The Manage Consent button is located on the Pictures page. When clicked, it opens a consent declaration where you confirm the content is yours and that everyone appearing in it has been verified and consented. This step is mandatory — without it, your photos will not appear on the platform. You must complete this process every time you upload new content.',
                        'steps'    => json_encode([
                            'On the Pictures page, locate the warning: "Your content will not appear on the Site until you have confirmed consent"',
                            'Click the green Manage Consent button (highlighted green)',
                            'A Content Consent panel opens',
                            'If only you appear in the content: tick "I am the only person to appear in the content"',
                            'If others appear: tick the relevant options — written consent obtained, identity verified, consent to distribute, all persons over 18',
                            'Tick "I confirm I\'ve done what\'s asked in the above points"',
                            'Click the purple Confirm button',
                            'Your consent is now confirmed and content will be approved to display on the platform',
                            'Repeat this process every time you upload new photos or content',
                        ]),
                        'tips'     => json_encode([
                            'Consent must be completed every time you upload new photos or content',
                            'Without completing consent, your photos and content will NOT appear on AdultWork',
                            'The Reset Consent button is available if you need to start the consent process over',
                            'The platform may occasionally switch between old and new interface versions — if this happens, simply refresh and try again',
                        ]),
                    ],
                ],
            ],

            // (20) Uploading Your photos
            [
                'title'       => 'Uploading Your photos',
                'description' => 'Upload images to your public free gallery to build an attractive, active-looking profile.',
                'lessons'     => [
                    [
                        'title'    => 'Uploading Your photos',
                        'overview' => 'After your profile photos are set up, uploading images to your public free gallery is the next step to building an attractive and active-looking profile. The free gallery shows customers additional photos when they visit your profile page. Access the upload section via the Upload Your Pictures and Movies section of the Pictures tab.',
                        'steps'    => json_encode([
                            'In My Details > Edit Profile > Profile Details > Pictures tab, scroll down past the profile photo section',
                            'Find the "Upload Your Pictures and Movies, and Make Money!" section (highlighted green with green arrow)',
                            'Click the Upload Pictures button next to "Upload pictures to your Free Gallery"',
                            'The Upload To Free Gallery page opens',
                            'Drag and drop your image files into the upload area or click "browse files" to select from your device',
                            'Click the purple Upload 1 file (or Upload X files) button to begin the upload',
                            'Once complete, click Next to proceed',
                            'On the Reorder Images page: drag photos to your preferred order if uploading multiple, click Next',
                            'On the Consent page: tick "I am the only person to appear in the content", tick "I confirm I\'ve done what\'s asked", click the purple Upload button',
                            'Your photos will appear in your public free gallery immediately',
                        ]),
                        'tips'     => json_encode([
                            'Public gallery photos are visible to all customers browsing your profile — keep them attractive and current',
                            'Upload regularly to keep your profile looking active — active profiles consistently outperform dormant ones',
                            'The platform may switch between old and new upload interfaces — if this happens, just refresh the page and try again',
                            'You can also upload to your Private Gallery (paid access) and Movie Clips Library for additional passive income streams',
                        ]),
                    ],
                ],
            ],

            // (21) Congratulations you are now ready
            [
                'title'       => 'Congratulations you are now ready',
                'description' => 'You are now fully trained on AdultWork — go live and start earning.',
                'lessons'     => [
                    [
                        'title'      => 'Congratulations you are now ready',
                        'intro_only' => true,
                        'overview'   => 'Amazing — you are now fully trained and understand the basics of how to stream, broadcast, and navigate your way around AdultWork. By this stage this should be your third or fourth platform setup, so everything should be coming together much more naturally and confidently. You now understand the power of multi-streaming — not only across token and freemium websites but also across premium platforms — while learning how to alternate and balance between the two styles. To go live: click the camcorder icon on the left-hand side of the newer interface (highlighted green), or use the Broadcast Now button from the top navigation. The broadcaster software will open, complete your pre-live checklist, hit the Start button, and you are ready to make money. Welcome to AdultWork with Paradise Dolls!',
                        'steps'      => json_encode([
                            'Ensure your positioning bids are placed for every hour you plan to stream today',
                            'Ensure your featuring is active for today if applicable',
                            'Click the camcorder icon on the left-hand side of the page (highlighted green) OR click Broadcast Now from the top nav',
                            'The broadcaster software opens in a new window',
                            'Complete your pre-live checklist: camera selected and showing, microphone confirmed green (ON), mode checked, rates saved',
                            'Click the green Start button — you are live and ready to make money!',
                        ]),
                        'tips'       => json_encode([
                            'Multi-streaming across AdultWork and other platforms builds multiple simultaneous income streams',
                            'Confidence and consistency come with time — every successful creator started exactly where you are now',
                            'Position and feature every single day you stream for maximum traffic and the best earnings',
                            'Build regulars by engaging in chat, showing up consistently, and delivering a great experience',
                            'This is one of the best and most established premium platforms on the market — follow this guide and you will succeed',
                        ]),
                    ],
                ],
            ],

            // (22) Outro
            [
                'title'       => 'Outro',
                'description' => 'The closing outro for the AdultWork Boss Doll Blueprint course.',
                'lessons'     => [
                    [
                        'title'      => 'Outro',
                        'intro_only' => true,
                        'overview'   => 'You have now completed the AdultWork Boss Doll Blueprint. You are fully equipped with everything you need to broadcast confidently, manage your profile, set your rates, position yourself for maximum traffic, and build a successful presence on one of the most established premium adult platforms in the world. Keep going live consistently, position and feature every day you stream, engage with your customers in chat, and keep your profile updated. The Paradise Dolls team is always here to support you. Now go out there and make it happen — you have got everything you need.',
                        'steps'      => json_encode([
                            'Go live consistently — every session builds your reputation and your regular customer base',
                            'Position and feature every day you plan to stream for maximum visibility',
                            'Keep your profile photos and bio updated to maintain an active, attractive profile',
                            'Engage with customers in chat — connection converts viewers into regulars',
                            'Reach out to your Paradise Dolls support team any time you need help or guidance',
                        ]),
                        'tips'       => json_encode([
                            'Consistency is the single biggest factor in long-term success on AdultWork',
                            'Your support team is always here — never hesitate to reach out for help',
                            'Every session is a learning experience — keep improving and keep going',
                            'You are now a Paradise Doll on AdultWork — own it and make it work for you',
                        ]),
                    ],
                ],
            ],

        ];

        $moduleOrder = 1;
        foreach ($modules as $moduleData) {
            $module = CourseModule::updateOrCreate(
                ['course_id' => $course->id, 'title' => $moduleData['title']],
                [
                    'description' => $moduleData['description'],
                    'is_published' => true,
                    'sort_order'  => $moduleOrder++,
                ]
            );

            $lessonOrder = 1;
            foreach ($moduleData['lessons'] as $lessonData) {
                $isIntroOnly = $lessonData['intro_only'] ?? false;

                $lesson = Lesson::updateOrCreate(
                    ['course_module_id' => $module->id, 'title' => $lessonData['title']],
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
                            ['block_type' => 'heading', 'title' => $lessonData['title'],    'content' => null,                    'sort_order' => 1],
                            ['block_type' => 'text',    'title' => 'Overview',              'content' => $lessonData['overview'], 'sort_order' => 2],
                            ['block_type' => 'video',   'title' => 'Presentation Video',    'content' => null,                    'sort_order' => 3],
                            ['block_type' => 'steps',   'title' => 'Key Steps',             'content' => $lessonData['steps'],    'sort_order' => 4],
                            ['block_type' => 'tips',    'title' => 'Tips & Reminders',      'content' => $lessonData['tips'],     'sort_order' => 5],
                        ];
                    } else {
                        $blocks = [
                            ['block_type' => 'heading', 'title' => $lessonData['title'],    'content' => null,                    'sort_order' => 1],
                            ['block_type' => 'text',    'title' => 'Overview',              'content' => $lessonData['overview'], 'sort_order' => 2],
                            ['block_type' => 'video',   'title' => 'Presentation Video',    'content' => null,                    'sort_order' => 3],
                            ['block_type' => 'heading', 'title' => 'Now Follow Along',      'content' => null,                    'sort_order' => 4],
                            ['block_type' => 'video',   'title' => 'Walkthrough Video',     'content' => null,                    'sort_order' => 5],
                            ['block_type' => 'steps',   'title' => 'Step-by-Step Guide',    'content' => $lessonData['steps'],    'sort_order' => 6],
                            ['block_type' => 'tips',    'title' => 'Tips & Reminders',      'content' => $lessonData['tips'],     'sort_order' => 7],
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
