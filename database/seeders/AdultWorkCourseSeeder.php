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
            // MODULE 1
            [
                'title'       => 'Introduction',
                'description' => 'Welcome to the AdultWork Boss Doll Blueprint.',
                'lessons'     => [
                    [
                        'title'      => 'Welcome to AdultWork',
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
            // MODULE 2
            [
                'title'       => 'Logging In & Getting Started',
                'description' => 'How to access your AdultWork account, navigate the platform, and open the broadcaster.',
                'lessons'     => [
                    [
                        'title'    => 'Logging In & Getting Started',
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
                    [
                        'title'    => 'Navigating the AdultWork Home Page',
                        'overview' => 'Once you have successfully logged in, you will arrive at the AdultWork home page. At the top of the screen you will see the main navigation bar containing sections including Home, Logout, Search, My Details, Emails Bookings & Messages, Help, and Broadcast Now. You may also notice featured sections across the platform such as Escort of the Day, Direct Cam of the Day, Phone Chat Member of the Day, and Content Creator of the Day.',
                        'steps'    => json_encode([
                            'After logging in, take a moment to familiarise yourself with the home page layout',
                            'Locate the main navigation bar at the top of the screen',
                            'Identify key sections: Home, Logout, Search, My Details, Emails Bookings & Messages, Help',
                            'Locate the Broadcast Now button at the top right of the navigation bar (highlighted yellow)',
                            'Notice the featured sections on the homepage — these highlight popular and active profiles',
                            'Be aware: some areas of AdultWork include escort or meet-up related services — Paradise Dolls does NOT promote this',
                            'Read the platform rules and stay aware of personal safety at all times',
                        ]),
                        'tips'     => json_encode([
                            'AdultWork is a large multi-service marketplace — your focus is webcam streaming as the primary income stream',
                            'The featured sections show popular profiles — positioning and featuring (covered later) gets you there',
                            'Familiarise yourself with the navigation before going live so you can find things quickly',
                            'Your profile will usually already be set up by the team, but you can personalise or update it whenever needed',
                        ]),
                    ],
                    [
                        'title'    => 'Accessing the Broadcast Now Button',
                        'overview' => 'Before exploring the rest of the platform, the first priority is learning how to go live and start your broadcast. Your account is already fully set up with your profile, bio, and account details completed, meaning you can begin broadcasting straight away. To open the broadcaster, click the Broadcast Now button at the top of the navigation bar.',
                        'steps'    => json_encode([
                            'Locate the Broadcast Now button in the top navigation bar (highlighted yellow)',
                            'Click Broadcast Now — a new browser window will open',
                            'This new window is where you will begin setting up your live stream and entering the broadcast interface',
                            'Your profile, bio, and account details are already completed — you are ready to broadcast straight away',
                        ]),
                        'tips'     => json_encode([
                            'The broadcaster opens in a separate window from the main AdultWork website',
                            'Your account is fully set up so you can start broadcasting without any additional setup',
                            'Do not press Start yet — review your settings in the following lessons first',
                            'The broadcaster will show OFFLINE status until you click the Start button',
                        ]),
                    ],
                    [
                        'title'    => 'Accessing the Broadcaster Interface',
                        'overview' => 'This is the AdultWork broadcaster interface — where you will host all your live shows, whether you choose to work in a premium pay-per-minute format or a freemium/public chat style format. Once you have clicked Broadcast Now, the broadcaster loads and you will see your webcam window on the left, the chat panel on the right, and the green Start button at the top right.',
                        'steps'    => json_encode([
                            'The broadcaster interface opens in a new browser window after clicking Broadcast Now',
                            'On the left side you will see your webcam window where your camera feed will display',
                            'On the right side you will see the chat/message area',
                            'At the top right you will see the green Start button and orange Boost button',
                            'You will see the status OFFLINE until the Start button is clicked',
                            'Do not press Start yet — go through the settings lessons first to ensure everything is configured correctly',
                        ]),
                        'tips'     => json_encode([
                            'You can work in premium pay-per-minute format or freemium/public chat format',
                            'The broadcaster shows OFFLINE status until you click Start — viewers cannot see you yet',
                            'The orange Boost button is used to boost your viewership in the next hour via DirectCam Boost',
                            'Take time to explore the layout before going live so nothing catches you off guard',
                        ]),
                    ],
                ],
            ],
            // MODULE 3
            [
                'title'       => 'Consent & Broadcaster Overview',
                'description' => 'Understanding the consent requirement and a full breakdown of the broadcaster interface once you go live.',
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
                    [
                        'title'    => 'AdultWork Broadcaster Overview',
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
            // MODULE 4
            [
                'title'       => 'Broadcast Mode Settings',
                'description' => 'Configuring Free Mode, Group Mode, Private Mode, and saving your broadcaster settings.',
                'lessons'     => [
                    [
                        'title'    => 'Broadcast Mode Settings Overview & Save Changes',
                        'overview' => 'Inside the broadcaster settings (accessed via the settings cog), the Modes tab gives you full control over how viewers access your stream. You will see options for Free Mode, Group Mode, and Private Mode. After configuring all your modes and rates, you must click the purple Save Changes button to apply everything before going live.',
                        'steps'    => json_encode([
                            'Click the settings cog (gear icon) below your webcam window to open the Settings panel',
                            'Click the Modes tab at the top of the settings panel',
                            'You will see three mode sections: Free Mode (Green), Group Mode (Pink), Private Mode (Yellow)',
                            'After adjusting all modes and rates, review your settings one final time',
                            'Confirm your Group Rate Per Minute is correct',
                            'Confirm your Private Rate Per Minute is correct',
                            'Click the purple Save Changes button at the bottom of the settings panel',
                            'Close the settings panel — your settings are now live for your broadcast',
                        ]),
                        'tips'     => json_encode([
                            'Always save before going live — unsaved settings will revert to previous values',
                            'Check your rates at the start of every session to ensure they are what you intend to charge',
                            'Pre-live final checklist: camera visible, microphone working, mode confirmed, rates saved',
                            'If you change rates between sessions, always save and verify before going live again',
                        ]),
                    ],
                    [
                        'title'    => 'Free Mode',
                        'overview' => 'The Free Mode section (highlighted green) lets you enable Free Preview Mode — allowing viewers to watch a clothed session for a limited time period before being prompted to enter a paid private or group session. This is entirely optional and depends on your preferred streaming style.',
                        'steps'    => json_encode([
                            'In the Modes tab of the Settings panel, find the Free Mode section (highlighted green at top)',
                            'You will see: Enable Free Preview Mode checkbox',
                            'Below this you will see: Enable Free Preview to Guests option',
                            'IMPORTANT: NEVER enable Free Preview to Guests',
                            '"Guests" means anyone visiting AdultWork without a registered account — this exposes your room to the entire internet',
                            'If using free mode, only allow it for registered site members (not guests)',
                            'Set Max period (recommended: 5 minutes) and Within any period of (recommended: 12 hours)',
                        ]),
                        'tips'     => json_encode([
                            'Never enable free preview to guests — only allow registered members access to your free preview',
                            'Free mode is optional — you can run purely on Group and Private modes with no free access',
                            'Setting max 5 minutes every 12 hours limits freeloaders while still attracting paying customers',
                            'Free preview viewers can tip you and are potential private/group session customers',
                        ]),
                    ],
                    [
                        'title'    => 'Group Mode',
                        'overview' => 'Group Mode (highlighted in pink in the settings) allows multiple customers to join your session simultaneously, each paying a per-minute rate. This is a great way to earn from several viewers at the same time. Make sure Enable Group Mode is ticked and your Group Rate Per Minute is set correctly before going live.',
                        'steps'    => json_encode([
                            'In the Modes tab of the Settings panel, find the Group Mode section (highlighted pink)',
                            'Tick the Enable Group Mode checkbox',
                            'Set your Group Rate Per Minute in the text field below (example: £1.50 per minute)',
                            'Each customer who joins the group session is charged this amount per minute',
                            'Group rate is typically lower than private rate as multiple customers pay simultaneously',
                            'Ensure the Group Mode checkbox remains ticked — always keep this enabled',
                        ]),
                        'tips'     => json_encode([
                            'Group rate is lower per customer but earns from multiple people at the same time',
                            'Always keep Group Mode enabled — it provides an additional income opportunity every session',
                            'The example rate shown in training is £1.50/min — adjust to match your audience and confidence',
                            'Group mode works well alongside private mode — offer both for maximum earnings',
                        ]),
                    ],
                    [
                        'title'    => 'Private Mode',
                        'overview' => 'Private Mode (highlighted in yellow in the settings) is a one-to-one exclusive session between yourself and a single customer. Private sessions are charged at a higher rate because they offer the customer undivided, exclusive access to you. Make sure Enable Private Mode is ticked and your Private Rate Per Minute reflects this premium pricing.',
                        'steps'    => json_encode([
                            'In the Modes tab of the Settings panel, find the Private Mode section (highlighted yellow)',
                            'Tick the Enable Private Mode checkbox',
                            'Set your Private Rate Per Minute in the text field below (example: £2.99 per minute)',
                            'Private rate should always be higher than your group rate',
                            'Private sessions are one-to-one — the customer pays for exclusive undivided attention',
                            'Ensure Private Mode remains ticked — always keep this enabled alongside Group Mode',
                        ]),
                        'tips'     => json_encode([
                            'Private sessions command a premium — always charge more than your group rate',
                            'The example private rate shown in training is £2.99/min — increase this as your confidence grows',
                            'Always keep both Private and Group Mode enabled for maximum earning flexibility',
                            'Higher private rates attract more serious and committed spenders over time',
                        ]),
                    ],
                ],
            ],
            // MODULE 5
            [
                'title'       => 'Devices & Video Settings',
                'description' => 'Configuring your camera, microphone, video quality, chat display, and notification sounds.',
                'lessons'     => [
                    [
                        'title'    => 'Video Device — Camera Selection',
                        'overview' => 'The Devices tab inside broadcaster settings (highlighted blue) is where you configure your camera. The Video Device section (highlighted green) shows your currently active camera and allows you to switch between devices using a dropdown.',
                        'steps'    => json_encode([
                            'Click the settings cog below your webcam window to open the Settings panel',
                            'Click the Devices tab (highlighted blue) at the top of the settings panel',
                            'Find the Video Device section (highlighted green)',
                            'Your currently selected camera will appear (e.g. SplitCam Virtual Camera)',
                            'Click the dropdown arrow next to the device name to view all available cameras',
                            'Options may include: Flip Camera, Virtual Camera, HD Webcam, Built-in Camera',
                            'Select the camera you want to use for your stream',
                            'If your camera is not listed, click Refresh Devices at the bottom of the panel',
                            'If problems persist, contact the Paradise Dolls team for support',
                        ]),
                        'tips'     => json_encode([
                            'Use an HD webcam or high-quality laptop camera for the best stream quality',
                            'Virtual cameras (e.g. OBS, SplitCam) will also appear in the dropdown list',
                            'Make sure your browser has camera and microphone permissions enabled',
                            'If the camera is not showing video after selection, click Refresh Devices and try again',
                        ]),
                    ],
                    [
                        'title'    => 'Audio Device — Microphone Selection',
                        'overview' => 'Beneath the video device settings, the Audio Device section (highlighted red) is where you select your microphone. Always double-check your correct microphone is selected, it is working, and your sound levels are clear before going live.',
                        'steps'    => json_encode([
                            'In the Devices tab, find the Audio Device section (highlighted red)',
                            'Click the dropdown arrow to select your microphone (e.g. Microphone - High Definition Audio Device)',
                            'Ensure your correct microphone is selected before going live',
                            'Check the audio level bar below the dropdown to confirm your mic is picking up sound',
                            'Always double-check: correct microphone selected, microphone is working, sound levels are clear',
                        ]),
                        'tips'     => json_encode([
                            'Always verify your correct microphone is selected before going live',
                            'The microphone icon in the broadcaster shows Green = ON and Red = OFF — always check it',
                            'Poor audio quality will drive customers away — a working mic is as important as a working camera',
                            'If mic is not working, try selecting a different device from the dropdown and refreshing',
                        ]),
                    ],
                    [
                        'title'    => 'Video Quality & Resolution',
                        'overview' => 'The Video Quality section (highlighted yellow) is where you set your stream resolution. Matching your video quality to your hardware and internet speed ensures a stable, professional stream.',
                        'steps'    => json_encode([
                            'In the Devices tab, find the Video Quality section (highlighted yellow)',
                            'Recommended setting: 1280x720 — High Quality (HD) for a clear and stable stream',
                            'If using a 4K camera or high-resolution webcam, you may also choose 4K or Ultra HD options',
                            'Always match your video quality to your internet speed, computer performance, and camera capability',
                            'Using settings that are too high for your setup may cause: Lag, Freezing, Audio delay, Stream disconnections',
                            'If your stream lags or freezes, reduce the video quality setting',
                        ]),
                        'tips'     => json_encode([
                            '1280x720 HD is the recommended setting for most setups — clear quality with stable performance',
                            'A stable HD stream is always better than an unstable ultra-high quality stream',
                            'Too high a video quality for your internet speed causes lag, freezing, and audio delay',
                            'Test your stream quality before going live to catch any issues early',
                        ]),
                    ],
                    [
                        'title'    => 'Chat Settings & Notification Sounds',
                        'overview' => 'The Chat tab inside broadcaster settings lets you personalise the chat display including timestamps and font size. The Sounds tab lets you set custom audio alerts for when customers enter, exit, send messages, or request private and group sessions.',
                        'steps'    => json_encode([
                            'In Settings, click the Chat tab',
                            'Toggle Timestamps on or off as preferred',
                            'Set your chat font Size using the dropdown (options: Small, Medium, Large, XL, 2XL, 3XL)',
                            'Click the Sounds tab in Settings',
                            'Customise sounds for: Chat Enter, Chat Exit, Chat Message, Group/Private Request, Group/Private Exit, Tip, Offline',
                            'Click the play button (triangle) next to each sound to preview it before selecting',
                            'Set a clearly audible sound for Group/Private Request — this is the most important alert',
                        ]),
                        'tips'     => json_encode([
                            'Large or XL font size makes chat easier to read at a glance during a live show',
                            'The Group/Private Request sound is the most important — make it audible so you never miss a customer requesting a session',
                            'A cash register-style sound for Group/Private Exit gives you a satisfying earnings reminder',
                            'Customising sounds makes your streaming setup feel more professional and helps you stay on top of activity in your room',
                        ]),
                    ],
                ],
            ],
            // MODULE 6
            [
                'title'       => 'Customer Information Panel & Chat Box',
                'description' => 'Understanding the customer information columns in your broadcaster and using the chat box effectively.',
                'lessons'     => [
                    [
                        'title'    => 'Customer Information Panel',
                        'overview' => 'Below your webcam window in the broadcaster, you will see a set of information columns that track all the customers currently in your room. These columns give you a real-time view of who is watching, how long they have been there, and how much they are spending.',
                        'steps'    => json_encode([
                            'Look at the area directly below your webcam window in the broadcaster',
                            'You will see columns: User (customer username), Mode (Free/Private/Group), Time (time customer entered your room), Duration (how long they have been in your room), Total (total amount earned from that session), Country (where the customer is viewing from), Actions (moderation and interaction controls)',
                            'Use this panel to monitor active viewers and track session earnings in real time',
                            'Before going live: always check your camera is showing, always check your microphone is on (green), double-check which streaming mode you are in, make sure you are fully ready before pressing Start',
                        ]),
                        'tips'     => json_encode([
                            'The Customer Information Panel helps you keep track of who is in your room and what mode they are on',
                            'Monitoring the Total column lets you see your earnings building in real time during a session',
                            'The Actions column lets you moderate your room — remove or block disruptive customers',
                            'Always run through your pre-live checklist before pressing Start every single session',
                        ]),
                    ],
                    [
                        'title'    => 'Chat Box & Messaging',
                        'overview' => 'At the bottom of the broadcaster interface on the right-hand side, you will find the chat box where you type messages to customers in your room. Staying active in chat is one of the most important habits for a successful live stream — it builds connection, keeps viewers engaged, and increases the likelihood of private or group session bookings.',
                        'steps'    => json_encode([
                            'Locate the chat box at the very bottom of the right-hand broadcaster panel',
                            'Click inside the message box and type your message',
                            'Click the smiley face emoji button (green arrow indicator) to open the emoji picker',
                            'Select an emoji to add it to your message',
                            'Click the arrow/triangle send button (yellow arrow indicator) to send your message',
                            'Alternatively, press Enter or Return on your keyboard to send',
                            'Your message will appear in the chat room visible to all customers in the room',
                        ]),
                        'tips'     => json_encode([
                            'Always acknowledge customers by name when they enter your room — it builds instant connection',
                            'Stay active in chat even between private sessions — keep the energy up',
                            'Use emojis to make your chat lively, friendly, and engaging',
                            'Responding quickly to messages builds rapport and turns one-time visitors into regulars',
                        ]),
                    ],
                ],
            ],
            // MODULE 7
            [
                'title'       => 'Profile Details & Webcam Settings',
                'description' => 'Navigating to your profile details and understanding your full webcam settings management menu.',
                'lessons'     => [
                    [
                        'title'    => 'Accessing Your Profile Details & Webcam Settings',
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
                    [
                        'title'    => 'Understanding Your Webcam Settings Menu',
                        'overview' => 'Once you open the Webcam tab, a new page appears with a management menu on the left side. This menu controls how your profile appears, how your stream works, and how viewers interact with your room. Your account will already have been set up during onboarding, but this walkthrough shows you where to go when you need to update anything.',
                        'steps'    => json_encode([
                            'After clicking the Webcam tab, a new page loads with a left-side management menu',
                            'Under Management you will see: Dashboard, Availability, Positioning, Shows, Special Offers',
                            'Under Account Settings you will see: DirectCam Settings, Category & Strapline',
                            'Dashboard: overview of your DirectCam activity and availability calendar',
                            'Availability: set your streaming schedule',
                            'Positioning: bid for your position on the front page (covered in detail in the next module)',
                            'DirectCam Settings: manage your rates, Free Preview Mode, Group Mode, Private Mode',
                            'Category & Strapline: set your category and tagline for search discoverability',
                        ]),
                        'tips'     => json_encode([
                            'Ideally your account is already set up from onboarding — use this menu when you need to change or update anything',
                            'DirectCam Settings is where you manage rates and toggle modes on and off (covered next)',
                            'Positioning and Featuring are both accessed through this menu and are essential daily tasks',
                            'The Webcam tab can also be accessed from the new beta interface via username initial > My DirectCam',
                        ]),
                    ],
                ],
            ],
            // MODULE 8
            [
                'title'       => 'Rates, Modes & Positioning',
                'description' => 'How to change your rates, control free and private modes, and master the positioning bidding system.',
                'lessons'     => [
                    [
                        'title'    => 'Changing Your Rates',
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
                    [
                        'title'    => 'Turning Free & Private Modes On/Off',
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
            // MODULE 9
            [
                'title'       => 'Featuring & Profile Photos',
                'description' => 'Featuring your profile for front-page visibility, managing your personal details, profile photos, consent, and public gallery.',
                'lessons'     => [
                    [
                        'title'    => 'Featuring Daily',
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
                    [
                        'title'    => 'Understanding Your Personal Details Section',
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
                    [
                        'title'    => 'Uploading & Changing Your Pictures',
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
                    [
                        'title'    => 'Understanding The Manage Consent Button',
                        'overview' => 'Content cannot appear on AdultWork until consent has been confirmed. The Manage Consent button is located on the Pictures page. When clicked, it opens a consent declaration where you confirm the content is yours and that everyone appearing in it has been verified and consented. This step is mandatory — without it, your photos will not appear on the platform.',
                        'steps'    => json_encode([
                            'On the Pictures page, locate the warning: "Your content will not appear on the Site until you have confirmed consent"',
                            'Click the green Manage Consent button (highlighted green)',
                            'A Content Consent panel opens',
                            'If only you appear in the content: tick "I am the only person to appear in the content"',
                            'If others appear: tick the relevant options — written consent obtained, identity verified, consent to distribute, all persons over 18',
                            'Tick "I confirm I\'ve done what\'s asked in the above points"',
                            'Click the purple Confirm button',
                            'Your consent is now confirmed and content will be approved to display on the platform',
                        ]),
                        'tips'     => json_encode([
                            'Consent must be completed every time you upload new photos or content',
                            'Without completing consent, your photos and content will NOT appear on AdultWork',
                            'The Reset Consent button is available if you need to start the consent process over',
                            'Keep consent up to date — missing or expired consent removes content visibility from the platform',
                        ]),
                    ],
                    [
                        'title'    => 'How To Complete Consent',
                        'overview' => 'When you click the Manage Consent button, a Content Consent box opens where you select the correct consent option and confirm. This final step is extremely important because it refreshes and updates the consent status for your uploaded content. Without completing this correctly your photos or content may not approve or appear on the platform properly.',
                        'steps'    => json_encode([
                            'Click the Manage Consent button to open the Content Consent panel',
                            'You will see consent options: "I am the only person to appear in the content" OR options for content featuring other verified persons',
                            'If only you appear: tick "I am the only person to appear in the content"',
                            'If other verified persons appear: tick all relevant checkboxes (written consent, identity verified, consent to distribute, all over 18)',
                            'Tick "I confirm I\'ve done what\'s asked in the above points"',
                            'Click the purple Confirm button',
                            'This refreshes and updates the consent status for your uploaded content — your photos will now appear on the platform',
                        ]),
                        'tips'     => json_encode([
                            'This step must be completed every time you upload new photos or content',
                            'Without this confirmation your content will not appear on the site',
                            'The Reset Consent button is available at the bottom if you need to restart the process',
                            'The platform may occasionally switch between old and new interface versions — if this happens, simply refresh and try again',
                        ]),
                    ],
                    [
                        'title'    => 'Uploading Your Photos',
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
                            'To view your profile as a customer would: click your username initial > My Profile > View Profile',
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
            // MODULE 10
            [
                'title'       => 'You\'re Ready',
                'description' => 'You are now fully trained on AdultWork — go live and start earning.',
                'lessons'     => [
                    [
                        'title'      => 'You\'re Ready — Go Live on AdultWork',
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
                            [
                                'block_type' => 'heading',
                                'title'      => $lessonData['title'],
                                'content'    => null,
                                'sort_order' => 1,
                            ],
                            [
                                'block_type' => 'text',
                                'title'      => 'Overview',
                                'content'    => $lessonData['overview'] ?? '',
                                'sort_order' => 2,
                            ],
                            [
                                'block_type' => 'video',
                                'title'      => 'Presentation Video',
                                'content'    => null,
                                'sort_order' => 3,
                            ],
                            [
                                'block_type' => 'steps',
                                'title'      => 'Key Steps',
                                'content'    => $lessonData['steps'] ?? null,
                                'sort_order' => 4,
                            ],
                            [
                                'block_type' => 'tips',
                                'title'      => 'Tips & Reminders',
                                'content'    => $lessonData['tips'] ?? null,
                                'sort_order' => 5,
                            ],
                        ];
                    } else {
                        $blocks = [
                            [
                                'block_type' => 'heading',
                                'title'      => $lessonData['title'],
                                'content'    => null,
                                'sort_order' => 1,
                            ],
                            [
                                'block_type' => 'text',
                                'title'      => 'Overview',
                                'content'    => $lessonData['overview'] ?? '',
                                'sort_order' => 2,
                            ],
                            [
                                'block_type' => 'video',
                                'title'      => 'Presentation Video',
                                'content'    => null,
                                'sort_order' => 3,
                            ],
                            [
                                'block_type' => 'heading',
                                'title'      => 'Now Follow Along',
                                'content'    => null,
                                'sort_order' => 4,
                            ],
                            [
                                'block_type' => 'video',
                                'title'      => 'Walkthrough Video',
                                'content'    => null,
                                'sort_order' => 5,
                            ],
                            [
                                'block_type' => 'steps',
                                'title'      => 'Step-by-Step Guide',
                                'content'    => $lessonData['steps'] ?? null,
                                'sort_order' => 6,
                            ],
                            [
                                'block_type' => 'tips',
                                'title'      => 'Tips & Reminders',
                                'content'    => $lessonData['tips'] ?? null,
                                'sort_order' => 7,
                            ],
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
