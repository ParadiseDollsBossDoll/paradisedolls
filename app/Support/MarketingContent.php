<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;

class MarketingContent
{
    public const SETTINGS_KEY = 'marketing_content';

    public static function pages(): array
    {
        return [
            'blank_page_1' => self::blankPage(1),
            'blank_page_2' => self::blankPage(2),
            'doll_after_dark' => self::dollAfterDarkPage(),
            'home' => [
                'label' => 'Home',
                'route' => 'home',
                'fields' => [
                    ['key' => 'hero.image', 'label' => 'Hero image', 'type' => 'image', 'default' => 'images/home/boss-doll-lounge-extended.png'],
                    ['key' => 'hero.title', 'label' => 'Hero title', 'type' => 'text', 'default' => 'Welcome to Boss Doll Creators'],
                    ['key' => 'hero.body', 'label' => 'Hero body', 'type' => 'paragraphs', 'default' => [
                        'A luxury feminine community and Academy for beginners, creators, and ambitious women who want remote income, confidence, freedom, friendships and a life they truly love.',
                        'Through the Boss Doll Blueprint, mentorship, multi-streaming, and a supportive girls-girl community, we help women grow in confidence, step into their rich girl era, and become the best version of themselves, together.',
                    ]],
                    ['key' => 'hero.primary_label', 'label' => 'Primary button label', 'type' => 'text', 'default' => 'Become A Doll'],
                    ['key' => 'hero.primary_url', 'label' => 'Primary button link', 'type' => 'url', 'default' => '#apply'],
                    ['key' => 'hero.secondary_label', 'label' => 'Secondary button label', 'type' => 'text', 'default' => 'Boss Doll MultiStream'],
                    ['key' => 'hero.secondary_url', 'label' => 'Secondary button link', 'type' => 'url', 'default' => '/multistreaming'],

                    ['key' => 'intro.eyebrow', 'label' => 'Intro eyebrow', 'type' => 'text', 'default' => 'Beginner Friendly'],
                    ['key' => 'intro.title', 'label' => 'Intro title', 'type' => 'text', 'default' => 'Luxury support without the intimidating agency feeling'],
                    ['key' => 'intro.body', 'label' => 'Intro body', 'type' => 'textarea', 'default' => 'Boss Doll Creators is built for women from real backgrounds, not only influencers with huge followings. You bring ambition and consistency. The team brings systems, guidance, onboarding, account preparation, safety standards, and a clear learning path.'],
                    ['key' => 'intro.image', 'label' => 'Intro image', 'type' => 'image', 'default' => 'images/17.jpeg'],
                    ['key' => 'intro.agency_title', 'label' => 'Agency card title', 'type' => 'text', 'default' => 'The agency handles'],
                    ['key' => 'intro.agency_items', 'label' => 'Agency card bullets', 'type' => 'list', 'default' => ['onboarding and account setup', 'verification preparation', 'profile guidance', 'support systems and structure']],
                    ['key' => 'intro.learn_title', 'label' => 'Learning card title', 'type' => 'text', 'default' => 'You learn'],
                    ['key' => 'intro.learn_items', 'label' => 'Learning card bullets', 'type' => 'list', 'default' => ['how to stream professionally', 'how platforms and tools work', 'how to engage customers', 'how to maximise earnings confidently']],
                    ['key' => 'intro.badge_title', 'label' => 'Intro image badge title', 'type' => 'text', 'default' => 'Support'],
                    ['key' => 'intro.badge_text', 'label' => 'Intro image badge text', 'type' => 'text', 'default' => 'from application to going live'],

                    ['key' => 'lifestyle.eyebrow', 'label' => 'Lifestyle eyebrow', 'type' => 'text', 'default' => 'Freedom & Lifestyle'],
                    ['key' => 'lifestyle.title', 'label' => 'Lifestyle title', 'type' => 'text', 'default' => 'Work remotely, build income, and create a life that feels bigger'],
                    ['key' => 'lifestyle.cards', 'label' => 'Lifestyle cards', 'type' => 'cards', 'card_fields' => ['image' => 'image', 'title' => 'text', 'body' => 'textarea'], 'default' => [
                        ['image' => 'images/18.jpeg', 'title' => 'Paradise living', 'body' => 'Tropical locations, villas, cafes, beach clubs, and flexible schedules that make remote income feel tangible.'],
                        ['image' => 'images/16.jpeg', 'title' => 'Professional systems', 'body' => 'Walkthroughs, strategy, equipment guidance, and platform education designed to make the work practical.'],
                        ['image' => 'images/19.jpeg', 'title' => 'Feminine community', 'body' => 'A supportive movement with mentorship, motivation, and structure so members are not left alone.'],
                    ]],

                    ['key' => 'founder.eyebrow', 'label' => 'Founder eyebrow', 'type' => 'text', 'default' => 'Meet Kayla'],
                    ['key' => 'founder.title', 'label' => 'Founder title', 'type' => 'text', 'default' => 'Why Boss Doll Creators exists'],
                    ['key' => 'founder.body', 'label' => 'Founder body', 'type' => 'textarea', 'default' => 'Kayla built Boss Doll Creators after more than 15 years in the industry, seeing too many women dropped into agencies without the support, confidence, structure, or business education they needed to succeed.'],
                    ['key' => 'founder.link_label', 'label' => 'Founder link label', 'type' => 'text', 'default' => 'Read the Founder Story'],
                    ['key' => 'founder.link_url', 'label' => 'Founder link URL', 'type' => 'url', 'default' => '/our-story'],
                    ['key' => 'founder.cards', 'label' => 'Founder cards', 'type' => 'cards', 'card_fields' => ['title' => 'text', 'body' => 'textarea'], 'default' => [
                        ['title' => 'Survival became strategy', 'body' => 'From no safety net to building online businesses, brands, and networks that created real financial freedom.'],
                        ['title' => 'Experience became education', 'body' => 'The hard lessons became a blueprint for confidence, consistency, branding, mindset, and platform strategy.'],
                        ['title' => 'Agency became community', 'body' => 'The goal is not to leave girls figuring it out alone. It is support, mentorship, and a team that wants members to win.'],
                        ['title' => 'Opportunity became the mission', 'body' => 'Boss Doll Creators exists to help women step into income, travel, flexibility, and the most successful version of themselves.'],
                    ]],

                    ['key' => 'system.eyebrow', 'label' => 'System eyebrow', 'type' => 'text', 'default' => 'The Core System'],
                    ['key' => 'system.title', 'label' => 'System title', 'type' => 'text', 'default' => 'One stream. Multiple platforms. More visibility.'],
                    ['key' => 'system.body', 'label' => 'System body', 'type' => 'textarea', 'default' => 'Boss Doll Creators positions multistreaming as the main advantage: simultaneous visibility across platforms, diversified income, stronger traffic, and a smarter system for turning one live session into multiple opportunities.'],
                    ['key' => 'system.cards', 'label' => 'System cards', 'type' => 'cards', 'card_fields' => ['title' => 'text', 'body' => 'textarea'], 'default' => [
                        ['title' => 'Traffic', 'body' => 'Reach audiences across multiple platforms without multiplying your workload.'],
                        ['title' => 'Monetisation', 'body' => 'Understand rankings, customer value systems, earnings tools, and retention.'],
                        ['title' => 'Confidence', 'body' => 'Use walkthroughs to navigate controls, messages, platform tools, and live-stream flow.'],
                    ]],

                    ['key' => 'blueprint.eyebrow', 'label' => 'Blueprint eyebrow', 'type' => 'text', 'default' => 'Private LMS'],
                    ['key' => 'blueprint.title', 'label' => 'Blueprint title', 'type' => 'text', 'default' => 'Boss Doll Blueprint'],
                    ['key' => 'blueprint.body', 'label' => 'Blueprint body', 'type' => 'textarea', 'default' => 'The members area is designed as a luxury feminine streaming operating system, not a generic course library. The core is the walkthrough system: platform navigation, monetisation tools, stream controls, customer interaction, rankings, earnings systems, and customer retention.'],
                    ['key' => 'blueprint.formats', 'label' => 'Blueprint formats', 'type' => 'list', 'default' => ['PDF guides with screenshots', 'Canva-style presentations', 'Screen-recorded walkthroughs']],
                    ['key' => 'blueprint.order_title', 'label' => 'Blueprint order title', 'type' => 'text', 'default' => 'Academy order'],
                    ['key' => 'blueprint.order_items', 'label' => 'Blueprint order items', 'type' => 'list', 'default' => ['Introduction to Kayla & Boss Doll Creators', 'Safety & professionalism', 'Stream preparation', 'Equipment & setup guidance', 'Platform walkthrough systems', 'Customer psychology and conversion strategy', 'Passive income, content, and messaging income']],

                    ['key' => 'grounded.eyebrow', 'label' => 'Grounded section eyebrow', 'type' => 'text', 'default' => 'Community, Safety, Professionalism'],
                    ['key' => 'grounded.title', 'label' => 'Grounded section title', 'type' => 'text', 'default' => 'Glamorous, but grounded'],
                    ['key' => 'grounded.cards', 'label' => 'Grounded cards', 'type' => 'cards', 'card_fields' => ['title' => 'text', 'body' => 'textarea'], 'default' => [
                        ['title' => 'Safety guidance', 'body' => 'Clear standards around age, verification, privacy, and professional conduct.'],
                        ['title' => 'Structured support', 'body' => 'Onboarding, checklists, mentorship, and admin review before training begins.'],
                        ['title' => 'All-girl energy', 'body' => 'A motivating community that feels aspirational, feminine, and achievable.'],
                    ]],

                    ['key' => 'testimonials.eyebrow', 'label' => 'Success stories eyebrow', 'type' => 'text', 'default' => 'Success Stories'],
                    ['key' => 'testimonials.title', 'label' => 'Testimonials title', 'type' => 'text', 'default' => 'Community wins make the opportunity feel real'],
                    ['key' => 'testimonials.link_label', 'label' => 'Testimonials link label', 'type' => 'text', 'default' => 'View stories'],

                    ['key' => 'apply.eyebrow', 'label' => 'Application section eyebrow', 'type' => 'text', 'default' => 'Application'],
                    ['key' => 'apply.title', 'label' => 'Application section title', 'type' => 'text', 'default' => 'Apply to Boss Doll Creators'],
                    ['key' => 'apply.body', 'label' => 'Application section body', 'type' => 'textarea', 'default' => 'No experience is required. The onboarding team reviews every application privately and will guide approved members through the next steps.'],
                    ['key' => 'apply.success_title', 'label' => 'Application success title', 'type' => 'text', 'default' => 'Application Received'],
                    ['key' => 'apply.success_body', 'label' => 'Application success body', 'type' => 'textarea', 'default' => "We're so excited you've taken your first step with Boss Doll Creators.\nYour application has been received successfully and our team will contact you soon."],
                    ['key' => 'apply.footer_note', 'label' => 'Application footer note', 'type' => 'textarea', 'default' => 'Approved applicants receive account instructions and the Model Information Form next.'],
                ],
            ],

            'our_story' => [
                'label' => 'Our Story',
                'route' => 'our-story',
                'fields' => [
                    ['key' => 'hero.image', 'label' => 'Hero image', 'type' => 'image', 'default' => 'https://images.unsplash.com/photo-1679931992295-a8d77544a807?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1920'],
                    ['key' => 'hero.eyebrow', 'label' => 'Hero eyebrow', 'type' => 'text', 'default' => 'About Us'],
                    ['key' => 'hero.title', 'label' => 'Hero title', 'type' => 'text', 'default' => 'Our Story'],
                    ['key' => 'hero.body', 'label' => 'Hero body', 'type' => 'textarea', 'default' => 'How one woman turned survival into a global mission for feminine success.'],
                    ['key' => 'why.eyebrow', 'label' => 'Why section eyebrow', 'type' => 'text', 'default' => 'Why Boss Doll Creators Exists'],
                    ['key' => 'why.title', 'label' => 'Why section title', 'type' => 'text', 'default' => 'The industry needed something different.'],
                    ['key' => 'why.body', 'label' => 'Why section body', 'type' => 'paragraphs', 'default' => [
                        "I've been in this industry for over 15 years, and I've seen it all.",
                        "I've watched agencies take huge commissions while offering very little in return.",
                        "I've seen girls signed, dropped into a group chat, and left to figure everything out alone.",
                        "I've seen talent burn out, confidence break, and potential wasted - not because the girls weren't capable, but because the support simply wasn't there.",
                        "That's why Boss Doll Creators exists.",
                    ]],
                    ['key' => 'why.image', 'label' => 'Why section image', 'type' => 'image', 'default' => 'images/16.jpeg'],
                    ['key' => 'why.badge_number', 'label' => 'Why image badge number', 'type' => 'text', 'default' => '15+'],
                    ['key' => 'why.badge_text', 'label' => 'Why image badge text', 'type' => 'text', 'default' => 'years in the industry'],
                    ['key' => 'story.eyebrow', 'label' => 'Personal story eyebrow', 'type' => 'text', 'default' => 'My Story'],
                    ['key' => 'story.title', 'label' => 'Personal story title', 'type' => 'text', 'default' => 'From nothing... to building a global business'],
                    ['key' => 'story.body', 'label' => 'Personal story body', 'type' => 'paragraphs', 'default' => [
                        "I didn't come from money, connections, or a perfect start.",
                        "I left school early and had to figure life out for myself at a young age. Everything I've built came from learning as I went, trusting my instincts, staying resilient, and refusing to give up on creating a bigger life for myself.",
                        'Over the years, I built networks, learned this industry from the ground up, and turned my passion into multiple successful businesses focused on branding, online marketing, and multi-streaming. Of course there were setbacks along the way, but every challenge taught me something valuable and pushed me to grow even more.',
                        'What started as simply wanting more for myself eventually became something much bigger.',
                        'Today, Boss Doll Creators and the Boss Doll Blueprint are built to help women create confidence, freedom, friendships, opportunities, and online success from anywhere in the world.',
                        'I also turned my personal brand into opportunities I once only dreamed about. Including becoming an official Playboy cover model and internationally published feature model.',
                        "But the biggest achievement for me has never been the features, followers, or lifestyle. It's being able to inspire other women to realise they are capable of building a bigger life too.",
                        "No matter your background, your past, your age, your body, or where you're starting from, you are still allowed to dream bigger. You are still allowed to become confident, successful, feminine, independent, powerful, and completely yourself all at once.",
                        "Boss Doll Creators was created to remind women that they don't have to fit into one box to be successful. You can be soft and strong. Feminine and ambitious. Beautiful and business-minded. This is more than just a platform. It's a movement built to empower women to believe in themselves again.",
                    ]],
                    ['key' => 'story.link_label', 'label' => 'Personal story link label', 'type' => 'text', 'default' => 'Playboy feature'],
                    ['key' => 'story.link_url', 'label' => 'Personal story link URL', 'type' => 'url', 'default' => 'https://playboy.co.za/2026/04/30/sets-the-tone/'],
                    ['key' => 'story.images', 'label' => 'Personal story images', 'type' => 'cards', 'card_fields' => ['image' => 'image', 'alt' => 'text'], 'default' => [
                        ['image' => 'images/our-story/my-story-laptop-beach-1.jpeg', 'alt' => 'Kayla working from the beach at sunset'],
                        ['image' => 'images/our-story/my-story-laptop-beach-2.jpeg', 'alt' => 'Kayla on the beach with a laptop at sunset'],
                        ['image' => 'images/our-story/my-story-laptop-beach-3.jpeg', 'alt' => 'Kayla building her online business by the ocean'],
                    ]],
                    ['key' => 'mission.eyebrow', 'label' => 'Mission eyebrow', 'type' => 'text', 'default' => 'The Mission'],
                    ['key' => 'mission.title', 'label' => 'Mission title', 'type' => 'text', 'default' => 'This is more than just an agency.'],
                    ['key' => 'mission.body', 'label' => 'Mission body', 'type' => 'paragraphs', 'default' => [
                        "Because this industry can change your life when you're guided properly. With me and my team guiding you, you'll learn how to handle everything this industry throws at you - from confidence, branding, mindset, and consistency, to the behind-the-scenes business strategies most girls never get taught.",
                        "You won't be left alone trying to figure it out. You'll have support, structure, mentorship, and a team behind you that genuinely wants to see you win.",
                        'This is your chance to build the life you deserve.',
                    ]],
                    ['key' => 'mission.cta_label', 'label' => 'Mission button label', 'type' => 'text', 'default' => 'Become A Doll'],
                    ['key' => 'mission.cta_url', 'label' => 'Mission button link', 'type' => 'url', 'default' => '/#apply'],
                    ['key' => 'mission.cards', 'label' => 'Mission cards', 'type' => 'cards', 'card_fields' => ['title' => 'text', 'body' => 'textarea'], 'default' => [
                        ['title' => 'Confidence', 'body' => "You'll learn everything needed - from platform strategy to personal branding and the mindset to stay consistent."],
                        ['title' => 'Structure', 'body' => 'Walkthroughs, checklists, onboarding, and a clear training path. Nothing left to guesswork.'],
                        ['title' => 'Community', 'body' => "An all-girl environment that's motivating, supportive, and ambitious without being competitive or intimidating."],
                        ['title' => 'Mentorship', 'body' => 'Real guidance from someone who has built, failed, rebuilt, and succeeded across multiple income streams and platforms.'],
                    ]],
                    ['key' => 'timeline.eyebrow', 'label' => 'Timeline eyebrow', 'type' => 'text', 'default' => 'The Journey'],
                    ['key' => 'timeline.title', 'label' => 'Timeline title', 'type' => 'text', 'default' => 'From ambition to agency'],
                    ['key' => 'timeline.cards', 'label' => 'Timeline cards', 'type' => 'cards', 'card_fields' => ['year' => 'text', 'title' => 'text', 'body' => 'textarea'], 'default' => [
                        ['year' => '2012', 'title' => 'The Beginning', 'body' => 'Left home early and started building my own path through music, modelling, dancing, and live TV work. I learned quickly how powerful confidence, performance, and personal branding could be.'],
                        ['year' => '2016', 'title' => 'Going Online', 'body' => 'As the world shifted online, I started exploring streaming, content creation, and digital platforms. I realised women could create income and freedom from anywhere in the world with just a phone, consistency, and confidence.'],
                        ['year' => '2018', 'title' => 'Building Businesses', 'body' => 'I opened studios, created agencies, and developed systems that helped models grow online. I also invested into multiple businesses including clubs, beauty, photography, and online ventures across different countries.'],
                        ['year' => '2019-2021', 'title' => 'Adapting & Evolving', 'body' => 'The industry changed fast. Covid, social media growth, and content creation completely transformed the online world. I spent years testing platforms, learning what actually worked, adapting to the changes, and rebuilding stronger.'],
                        ['year' => '2025-2026', 'title' => 'The Boss Doll Blueprint Era', 'body' => 'After stepping away for a few years, I came back with a whole new vision, combining multi-streaming, content creation, social media growth, mentorship, and online education into one supportive girls-girl community and Academy.'],
                    ]],
                    ['key' => 'timeline.closing', 'label' => 'Timeline closing paragraphs', 'type' => 'paragraphs', 'default' => [
                        "This journey hasn't been easy, but it's taught me everything.",
                        "For over 15 years I've tested platforms, studied the industry, built businesses, failed, rebuilt, learned the systems myself, and discovered what truly works online.",
                        "Now I'm passing that knowledge, structure, and experience onto other women, so they can build confidence, freedom, income and a life bigger than they ever imagined.",
                    ]],
                    ['key' => 'cta.eyebrow', 'label' => 'Final CTA eyebrow', 'type' => 'text', 'default' => 'Ready?'],
                    ['key' => 'cta.title', 'label' => 'Final CTA title', 'type' => 'text', 'default' => 'Step into your highest level'],
                    ['key' => 'cta.body', 'label' => 'Final CTA body', 'type' => 'textarea', 'default' => 'No experience necessary. The team handles onboarding, verification, and setup. You bring consistency and ambition.'],
                    ['key' => 'cta.label', 'label' => 'Final CTA button label', 'type' => 'text', 'default' => 'Become A Doll'],
                    ['key' => 'cta.url', 'label' => 'Final CTA button link', 'type' => 'url', 'default' => '/#apply'],
                ],
            ],

            'work_from_home' => self::simplePage(
                'Work From Home',
                'work-from-home',
                'images/2.jpeg',
                'Flexibility & Freedom',
                'Work From Home',
                'Studio-quality setups, full training, and a flexible schedule - without leaving the comfort or privacy of your own space.',
                [
                    ['key' => 'main.eyebrow', 'label' => 'Main section eyebrow', 'type' => 'text', 'default' => 'Your Space, Your Schedule'],
                    ['key' => 'main.title', 'label' => 'Main section title', 'type' => 'text', 'default' => 'Your home becomes headquarters.'],
                    ['key' => 'main.body', 'label' => 'Main section body', 'type' => 'textarea', 'default' => 'Boss Doll Creators is built for women who want professional income without having to leave home. Everything you need - training, systems, guidance, and community - is available remotely from day one.'],
                    ['key' => 'main.benefits', 'label' => 'Main benefit cards', 'type' => 'cards', 'card_fields' => ['title' => 'text', 'body' => 'textarea'], 'default' => [
                        ['title' => 'Work from your own space', 'body' => 'No commute, no office, no dress code outside your stream. Your bedroom, studio corner, or dedicated room becomes your place of business.'],
                        ['title' => 'Flexible schedule you control', 'body' => 'You decide when you go live. Morning, evening, or night - the platforms work around your lifestyle, not the other way around.'],
                        ['title' => 'Full training and walkthroughs provided', 'body' => 'The Boss Doll Blueprint gives you everything: platform navigation, monetisation systems, customer interaction, and stream controls.'],
                        ['title' => 'Multi-platform earning system', 'body' => 'Learn to stream across multiple platforms simultaneously - so your income is never reliant on a single site or audience.'],
                        ['title' => 'Privacy & discretion', 'body' => 'Your privacy is protected at every step. The team manages verification and account setup before you ever go public.'],
                    ]],
                    ['key' => 'main.image', 'label' => 'Main image', 'type' => 'image', 'default' => 'images/1.jpeg'],
                    ['key' => 'team.title', 'label' => 'Team handles title', 'type' => 'text', 'default' => 'What the team handles'],
                    ['key' => 'team.items', 'label' => 'Team handles bullets', 'type' => 'list', 'default' => ['Account setup on every platform', 'Identity verification and age checks', 'Profile preparation and review', 'Onboarding structure and guidance', 'Support systems throughout']],
                    ['key' => 'technical.eyebrow', 'label' => 'Technical section eyebrow', 'type' => 'text', 'default' => 'Technical Setup'],
                    ['key' => 'technical.title', 'label' => 'Technical section title', 'type' => 'text', 'default' => 'Professional quality, from wherever you are'],
                    ['key' => 'technical.body', 'label' => 'Technical section body', 'type' => 'textarea', 'default' => 'The Boss Doll Blueprint includes step-by-step guidance on lighting, audio, framing, and connectivity - so your home stream looks premium on every platform from day one.'],
                    ['key' => 'technical.cards', 'label' => 'Technical cards', 'type' => 'cards', 'card_fields' => ['title' => 'text', 'body' => 'textarea'], 'default' => [
                        ['title' => 'Lighting guidance', 'body' => 'Ring light, softbox, and natural light setups depending on your space and budget.'],
                        ['title' => 'Audio quality', 'body' => 'Clear, professional sound without expensive studio equipment.'],
                        ['title' => 'Camera framing', 'body' => 'Optimal positioning, angle, and background styling for every platform.'],
                        ['title' => 'Equipment upgrades', 'body' => 'Recommended upgrades at every income level - from beginner to advanced.'],
                    ]],
                    ['key' => 'technical.image', 'label' => 'Technical image', 'type' => 'image', 'default' => 'images/3.jpeg'],
                    ['key' => 'cta.eyebrow', 'label' => 'CTA eyebrow', 'type' => 'text', 'default' => 'Start from home'],
                    ['key' => 'cta.title', 'label' => 'CTA title', 'type' => 'text', 'default' => 'Apply and the team will guide you from there'],
                    ['key' => 'cta.body', 'label' => 'CTA body', 'type' => 'textarea', 'default' => 'No experience needed. The onboarding team reviews every application privately and handles setup before training begins.'],
                    ['key' => 'cta.label', 'label' => 'CTA button label', 'type' => 'text', 'default' => 'Become A Doll'],
                    ['key' => 'cta.url', 'label' => 'CTA button link', 'type' => 'url', 'default' => '/#apply'],
                ],
            ),

            'work_from_paradise' => self::simplePage(
                'Work From Paradise',
                'work-from-paradise',
                'images/6.jpeg',
                'Travel & Income',
                'Work From Paradise',
                'Portable rigs, flexible schedules, and an all-girl expat community so your office can be anywhere on the planet.',
                [
                    ['key' => 'hero.primary_label', 'label' => 'Hero primary button label', 'type' => 'text', 'default' => 'Become A Doll'],
                    ['key' => 'hero.primary_url', 'label' => 'Hero primary button link', 'type' => 'url', 'default' => '/#apply'],
                    ['key' => 'hero.secondary_label', 'label' => 'Hero secondary button label', 'type' => 'text', 'default' => 'Our Story'],
                    ['key' => 'hero.secondary_url', 'label' => 'Hero secondary button link', 'type' => 'url', 'default' => '/our-story'],
                    ['key' => 'lifestyle.eyebrow', 'label' => 'Lifestyle eyebrow', 'type' => 'text', 'default' => 'The Lifestyle'],
                    ['key' => 'lifestyle.title', 'label' => 'Lifestyle title', 'type' => 'text', 'default' => 'Luxury for less. Freedom for more.'],
                    ['key' => 'lifestyle.body', 'label' => 'Lifestyle body', 'type' => 'paragraphs', 'default' => [
                        "Some of the world's most exciting locations offer luxury living at a fraction of the cost of major cities - and Boss Doll Creators models know how to find them.",
                        "When you're earning online, your location is your choice. Tropical destinations, iconic nightlife, incredible food, and a growing expat girl boss community await.",
                    ]],
                    ['key' => 'lifestyle.items', 'label' => 'Lifestyle bullets', 'type' => 'list', 'default' => ['Tropical paradise', 'Luxury for less', 'Iconic nightlife', 'Amazing food', 'Expat girl boss hotspot', 'Remote-friendly infrastructure']],
                    ['key' => 'lifestyle.image', 'label' => 'Lifestyle image', 'type' => 'image', 'default' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&q=85&w=900'],
                    ['key' => 'lifestyle.badge_title', 'label' => 'Lifestyle badge title', 'type' => 'text', 'default' => 'Any location'],
                    ['key' => 'lifestyle.badge_text', 'label' => 'Lifestyle badge text', 'type' => 'text', 'default' => 'your schedule, your paradise'],
                    ['key' => 'studio.image', 'label' => 'Studio image', 'type' => 'image', 'default' => 'images/4.jpeg'],
                    ['key' => 'studio.eyebrow', 'label' => 'Studio eyebrow', 'type' => 'text', 'default' => 'Studio Living'],
                    ['key' => 'studio.title', 'label' => 'Studio title', 'type' => 'text', 'default' => 'Exclusive studio spaces worldwide'],
                    ['key' => 'studio.body', 'label' => 'Studio body', 'type' => 'textarea', 'default' => 'Boss Doll Creators coordinates access to exclusive studio spaces designed specifically for professional streaming. These are not generic co-working spaces - they are purpose-built, fully equipped, and aesthetically stunning.'],
                    ['key' => 'studio.cards', 'label' => 'Studio cards', 'type' => 'cards', 'card_fields' => ['title' => 'text', 'body' => 'textarea'], 'default' => [
                        ['title' => 'Luxury private spaces', 'body' => 'Purpose-built streaming rooms with full privacy, professional aesthetic, and premium finishes.'],
                        ['title' => 'Fully equipped webcam studios', 'body' => 'High-quality lighting, stable connectivity, gorgeous backdrops - everything optimised for professional streaming.'],
                        ['title' => 'Professional glam setup', 'body' => 'The environment reflects the brand: polished, aspirational, and built for the highest-quality content.'],
                    ]],
                    ['key' => 'community.image', 'label' => 'Community image', 'type' => 'image', 'default' => 'images/5.jpeg'],
                    ['key' => 'community.eyebrow', 'label' => 'Community eyebrow', 'type' => 'text', 'default' => 'Community'],
                    ['key' => 'community.title', 'label' => 'Community title', 'type' => 'text', 'default' => 'An all-girl environment built for growth'],
                    ['key' => 'community.body', 'label' => 'Community body', 'type' => 'textarea', 'default' => "When you work from a Boss Doll Creators studio, you're not alone. You're surrounded by a supportive all-girl community where everyone shares tips, encourages consistency, and motivates each other daily."],
                    ['key' => 'community.items', 'label' => 'Community bullets', 'type' => 'list', 'default' => ['All-girl supportive environment', 'Share tips and strategies as you grow', 'Motivating, ambitious atmosphere', 'Community events and group moments']],
                    ['key' => 'security.eyebrow', 'label' => 'Security eyebrow', 'type' => 'text', 'default' => 'Security & Lifestyle'],
                    ['key' => 'security.title', 'label' => 'Security title', 'type' => 'text', 'default' => 'Private. Safe. Yours.'],
                    ['key' => 'security.body', 'label' => 'Security body', 'type' => 'textarea', 'default' => 'Every studio and location recommendation from Boss Doll Creators is vetted for safety, privacy, and professional suitability. Your discretion and security are non-negotiable.'],
                    ['key' => 'security.cards', 'label' => 'Security cards', 'type' => 'cards', 'card_fields' => ['title' => 'text', 'body' => 'textarea'], 'default' => [
                        ['title' => 'Total privacy & discretion', 'body' => 'Your work, your identity, and your content are protected at every location.'],
                        ['title' => 'Safe, secure environments', 'body' => 'Every recommended space is vetted for security, reliability, and professional standard.'],
                        ['title' => 'Make it your home base', 'body' => 'Studio living means routine, community, and structure - not just a room with a camera.'],
                    ]],
                    ['key' => 'cta.eyebrow', 'label' => 'CTA eyebrow', 'type' => 'text', 'default' => 'Ready to explore?'],
                    ['key' => 'cta.title', 'label' => 'CTA title', 'type' => 'text', 'default' => 'Your paradise is waiting'],
                    ['key' => 'cta.body', 'label' => 'CTA body', 'type' => 'textarea', 'default' => 'Become a doll today. The team handles onboarding, verification, and setup - then you decide where in the world you want to work.'],
                    ['key' => 'cta.label', 'label' => 'CTA button label', 'type' => 'text', 'default' => 'Become A Doll'],
                    ['key' => 'cta.url', 'label' => 'CTA button link', 'type' => 'url', 'default' => '/#apply'],
                ],
            ),

            'perks' => self::simplePage(
                'Perks',
                'perks',
                'images/7.jpeg',
                'VIP Perks & Rewards',
                'Live the VIP Lifestyle',
                "When you're a top earner, you don't just make money... You unlock a VIP lifestyle most people only dream of.",
                [
                    ['key' => 'rewards.eyebrow', 'label' => 'Rewards eyebrow', 'type' => 'text', 'default' => 'Exclusive Rewards'],
                    ['key' => 'rewards.title', 'label' => 'Rewards title', 'type' => 'text', 'default' => 'Perks that grow with your earnings'],
                    ['key' => 'rewards.body', 'label' => 'Rewards body', 'type' => 'textarea', 'default' => 'Boss Doll Creators rewards its top earners with real-world luxury experiences that make the work feel like the lifestyle.'],
                    ['key' => 'rewards.cards', 'label' => 'VIP reward cards', 'type' => 'cards', 'card_fields' => ['image' => 'image', 'title' => 'text', 'body' => 'textarea'], 'default' => [
                        ['image' => 'images/8.jpeg', 'title' => 'All-Inclusive Luxury Getaways', 'body' => 'Fully paid trips, villas, hotels, and beachfront escapes. The agency rewards top earners with unforgettable travel experiences.'],
                        ['image' => 'images/9.jpeg', 'title' => 'Luxury Yacht Trips', 'body' => 'Private yacht experiences with champagne, food, and island escapes. Access to events most people only see on social media.'],
                        ['image' => 'images/10.jpeg', 'title' => 'Spa & Beauty Treatments', 'body' => 'VIP wellness and beauty access - from premium spas to full beauty treatments that help you look and feel your best on and off camera.'],
                        ['image' => 'images/11.jpeg', 'title' => 'Fine Dining', 'body' => 'Exclusive restaurant experiences and high-end dining at world-class venues across luxury destinations.'],
                        ['image' => 'images/12.jpeg', 'title' => 'VIP Parties & DJs', 'body' => 'Guest list access to private events, celebrity-style parties, and premium nightlife experiences worldwide.'],
                        ['image' => 'images/13.jpeg', 'title' => 'Photoshoots & Brand Building', 'body' => 'Professional shoots, full styling, and brand-building opportunities to grow your image, audience, and personal brand.'],
                    ]],
                    ['key' => 'support.eyebrow', 'label' => 'Support eyebrow', 'type' => 'text', 'default' => 'Community & Support'],
                    ['key' => 'support.title', 'label' => 'Support title', 'type' => 'text', 'default' => 'Ongoing support, every step of the way'],
                    ['key' => 'support.cards', 'label' => 'Support cards', 'type' => 'cards', 'card_fields' => ['title' => 'text', 'body' => 'textarea'], 'default' => [
                        ['title' => 'Priority Mentoring', 'body' => 'Focused check-ins for strategy, confidence, consistency, and growth planning.'],
                        ['title' => 'Creative Direction', 'body' => 'Portfolio, profile, and content guidance to sharpen your personal brand.'],
                        ['title' => 'Community Moments', 'body' => 'Events, celebrations, and group support that make the journey feel less solo.'],
                        ['title' => 'Upgrade Pathways', 'body' => 'Equipment, setup, and workflow recommendations as your income grows.'],
                        ['title' => 'Professional Support', 'body' => 'Safety guidance, platform advice, and boundaries support from the team.'],
                        ['title' => 'Structured Onboarding', 'body' => 'Every member is guided through the same professional process - no one is left to figure it out alone.'],
                    ]],
                    ['key' => 'cta.eyebrow', 'label' => 'CTA eyebrow', 'type' => 'text', 'default' => 'Ready to earn your perks?'],
                    ['key' => 'cta.title', 'label' => 'CTA title', 'type' => 'text', 'default' => 'Join Boss Doll Creators today'],
                    ['key' => 'cta.body', 'label' => 'CTA body', 'type' => 'textarea', 'default' => 'No experience required. The onboarding team reviews every application privately. Perks are earned - and the path starts here.'],
                    ['key' => 'cta.label', 'label' => 'CTA button label', 'type' => 'text', 'default' => 'Become A Doll'],
                    ['key' => 'cta.url', 'label' => 'CTA button link', 'type' => 'url', 'default' => '/#apply'],
                ],
            ),

            'multistreaming' => self::simplePage(
                'Multistreaming',
                'multistreaming',
                'images/14.jpeg',
                'The System',
                'The Power of Multistreaming',
                'One show. Multiple platforms. Multiple incomes.',
                [
                    ['key' => 'different.eyebrow', 'label' => 'Difference eyebrow', 'type' => 'text', 'default' => 'What Makes Us Different'],
                    ['key' => 'different.title', 'label' => 'Difference title', 'type' => 'text', 'default' => 'True simultaneous multistreaming'],
                    ['key' => 'different.body', 'label' => 'Difference body', 'type' => 'paragraphs', 'default' => [
                        'Boss Doll Creators is built around true simultaneous multistreaming. Not switching platforms. Not taking turns. Streaming everywhere at once.',
                        'The highest-earning models today are no longer choosing one side only - they are learning how to combine both systems together intelligently.',
                        'You go live once... the system works everywhere.',
                    ]],
                    ['key' => 'different.cta_label', 'label' => 'Difference button label', 'type' => 'text', 'default' => 'Become A Doll'],
                    ['key' => 'different.cta_url', 'label' => 'Difference button link', 'type' => 'url', 'default' => '/#apply'],
                    ['key' => 'different.cards', 'label' => 'Difference cards', 'type' => 'cards', 'card_fields' => ['title' => 'text', 'body' => 'textarea'], 'default' => [
                        ['title' => 'Increased Visibility', 'body' => 'Reach multiple audiences simultaneously without multiplying your workload.'],
                        ['title' => 'Higher Earnings Per Hour', 'body' => 'More viewers across more platforms means more tips, more privates, more income.'],
                        ['title' => 'Multiple Income Streams', 'body' => 'Earn from several platforms at once - diversify so no single site defines your month.'],
                        ['title' => 'Less Risk', 'body' => 'If one site is slow or down, the others still perform. Your income stays protected.'],
                        ['title' => 'More Private Opportunities', 'body' => 'Higher-paying clients exist across premium platforms - multistreaming puts you in front of them.'],
                        ['title' => 'Faster Growth', 'body' => 'Build your fanbase everywhere at once rather than growing one audience at a time.'],
                    ]],
                    ['key' => 'change.eyebrow', 'label' => 'Change section eyebrow', 'type' => 'text', 'default' => 'How the Industry is Changing'],
                    ['key' => 'change.title', 'label' => 'Change section title', 'type' => 'text', 'default' => 'The lines are blurring. Smart models are ahead.'],
                    ['key' => 'change.body', 'label' => 'Change section body', 'type' => 'paragraphs', 'default' => [
                        'Premium websites are beginning to adopt freemium-style features because they realise modern customers enjoy tipping, interacting publicly, games, menus, and live engagement.',
                        'Some premium platforms now allow models to use tip menus, receive public tips, and create interactive rooms - blending token culture with premium earning.',
                        'This means modern creators can now combine BOTH sectors together. Freemium helps you build fans. Premium helps you maximise earnings. One side feeds the other.',
                    ]],
                    ['key' => 'strategy.eyebrow', 'label' => 'Strategy box eyebrow', 'type' => 'text', 'default' => 'The Smart Strategy'],
                    ['key' => 'strategy.cards', 'label' => 'Strategy steps', 'type' => 'cards', 'card_fields' => ['title' => 'text', 'body' => 'textarea'], 'default' => [
                        ['title' => 'Stream publicly on', 'body' => 'Public freemium-style platforms to build traffic, visibility, and fans without revealing the exact websites before enrollment.'],
                        ['title' => 'Convert and earn on', 'body' => 'Premium private-session platforms for higher-paying clients and serious spenders.'],
                        ['title' => 'More Platforms. More Income.', 'body' => 'Both sides working simultaneously - the system earns across every platform at once.'],
                    ]],
                    ['key' => 'built.eyebrow', 'label' => 'Built section eyebrow', 'type' => 'text', 'default' => 'Built For Maximum Earnings'],
                    ['key' => 'built.title', 'label' => 'Built section title', 'type' => 'text', 'default' => 'Boss Doll Creators teaches both sectors'],
                    ['key' => 'built.body', 'label' => 'Built section body', 'type' => 'textarea', 'default' => 'We are not teaching girls to rely on only one website or one income source. We teach you how to build traffic, build fans, convert viewers, maximise private earnings, diversify your income, and create long-term online success.'],
                    ['key' => 'built.cards', 'label' => 'Built section cards', 'type' => 'cards', 'card_fields' => ['title' => 'text', 'body' => 'textarea'], 'default' => [
                        ['title' => 'Full setup', 'body' => 'Multi-platform onboarding and optimised stream quality guidance.'],
                        ['title' => 'Smart scheduling', 'body' => 'Platform-aware session timing to reach the right viewers at the right moments.'],
                        ['title' => 'Monetisation strategy', 'body' => 'Tips, privates, goals, games, exclusive content - all taught inside the Boss Doll Blueprint.'],
                    ]],
                    ['key' => 'cta.eyebrow', 'label' => 'CTA eyebrow', 'type' => 'text', 'default' => 'Start the System'],
                    ['key' => 'cta.title', 'label' => 'CTA title', 'type' => 'text', 'default' => 'You go live once. The system works everywhere.'],
                    ['key' => 'cta.label', 'label' => 'CTA button label', 'type' => 'text', 'default' => 'Become A Doll'],
                    ['key' => 'cta.url', 'label' => 'CTA button link', 'type' => 'url', 'default' => '/#apply'],
                ],
            ),

            'success_stories' => self::simplePage(
                'Success Stories',
                'success-stories',
                'images/15.jpeg',
                'BOSS DOLL CREATORS COMMUNITY',
                'Real Stories from Our Boss Doll Creators 💎',
                "Behind every success is a woman who had the courage to take the first step. Discover the inspiring journeys of our Boss Doll Creators as they share how they’ve built confidence, embraced new opportunities, formed lifelong friendships, and transformed their lives with the support of a community that truly believes in their success.\n\nEvery Boss Doll Creator’s journey is unique, but they all began with one decision to believe in themselves. Today, they’re inspiring others to do the same, proving that with the right support, mindset, and determination, incredible things are possible.\n\nAt Boss Doll Creators, we believe success is about more than reaching your goals. It’s about becoming part of a sisterhood that celebrates every milestone, lifts each other up, and grows stronger together. Here, you’ll find encouragement, friendship, inspiration, and a community that’s genuinely invested in seeing every Doll succeed.\n\nYour story starts with a single step… and this could be the beginning of your own success story. 💖✨",
                [
                    ['key' => 'testimonials.eyebrow', 'label' => 'Success stories eyebrow', 'type' => 'text', 'default' => 'Success Stories'],
                    ['key' => 'testimonials.title', 'label' => 'Testimonials title', 'type' => 'text', 'default' => 'Real words from approved Boss Doll Creators members'],
                    ['key' => 'review_photos', 'label' => 'Review photos', 'type' => 'cards', 'card_fields' => ['image' => 'image', 'title' => 'text'], 'default' => [
                        ['image' => 'images/reviews/review-1.jpeg', 'title' => 'Nova - Model'],
                        ['image' => 'images/reviews/review-2.jpeg', 'title' => 'Franky - Model'],
                        ['image' => 'images/reviews/review-3.jpeg', 'title' => 'Blazing Baddie - Model'],
                        ['image' => 'images/reviews/review-4.jpeg', 'title' => 'Julius - Staff member / Chatter'],
                        ['image' => 'images/reviews/review-5.jpeg', 'title' => 'Judie - Staff member / Chatter'],
                        ['image' => 'images/reviews/ruby.jpeg', 'title' => 'Ruby - Model'],
                        ['image' => 'images/reviews/precious.jpeg', 'title' => 'Precious - Chatter'],
                        ['image' => 'images/reviews/jhon-paul.jpeg', 'title' => 'Jhon Paul - Chatter'],
                        ['image' => 'images/reviews/alina.jpeg', 'title' => 'Alina - Model'],
                    ]],
                    ['key' => 'empty.title', 'label' => 'Empty state title', 'type' => 'text', 'default' => 'Success stories are coming soon'],
                    ['key' => 'empty.body', 'label' => 'Empty state body', 'type' => 'textarea', 'default' => 'The team can add approved member testimonials from the admin dashboard as the community grows.'],
                ],
            ),

            'shared' => [
                'label' => 'Shared Navbar/Footer',
                'route' => 'home',
                'fields' => [
                    ['key' => 'nav.our_story_label', 'label' => 'Nav: Our Story label', 'type' => 'text', 'default' => 'Our Story'],
                    ['key' => 'nav.work_from_home_label', 'label' => 'Nav: Work From Home label', 'type' => 'text', 'default' => 'Work From Home'],
                    ['key' => 'nav.work_from_paradise_label', 'label' => 'Nav: Work From Paradise label', 'type' => 'text', 'default' => 'Work From Paradise'],
                    ['key' => 'nav.perks_label', 'label' => 'Nav: Perks label', 'type' => 'text', 'default' => 'Perks'],
                    ['key' => 'nav.multistreaming_label', 'label' => 'Nav: Multistreaming label', 'type' => 'text', 'default' => 'Multistreaming'],
                    ['key' => 'nav.success_stories_label', 'label' => 'Nav: Success Stories label', 'type' => 'text', 'default' => 'Success Stories'],
                    ['key' => 'nav.members_label', 'label' => 'Nav: Members label', 'type' => 'text', 'default' => 'Members'],
                    ['key' => 'nav.login_label', 'label' => 'Nav: Login label', 'type' => 'text', 'default' => 'Log in'],
                    ['key' => 'nav.apply_label', 'label' => 'Nav: Apply button label', 'type' => 'text', 'default' => 'Become A Doll'],
                    ['key' => 'nav.apply_url', 'label' => 'Nav: Apply button link', 'type' => 'url', 'default' => '/#apply'],
                    ['key' => 'footer.description', 'label' => 'Footer description', 'type' => 'textarea', 'default' => 'A luxury feminine opportunity platform and Boss Doll Blueprint academy for remote income, community, and confident online success.'],
                    ['key' => 'footer.explore_label', 'label' => 'Footer explore heading', 'type' => 'text', 'default' => 'Explore'],
                    ['key' => 'footer.members_label', 'label' => 'Footer members heading', 'type' => 'text', 'default' => 'Members'],
                    ['key' => 'footer.tiktok_url', 'label' => 'Footer TikTok URL', 'type' => 'url', 'default' => 'https://www.tiktok.com/@paradisedollsstreaming'],
                    ['key' => 'footer.snapchat_url', 'label' => 'Footer Snapchat URL', 'type' => 'url', 'default' => 'https://snapchat.com/t/XDWG3Kkz'],
                    ['key' => 'footer.instagram_url', 'label' => 'Footer Instagram URL', 'type' => 'url', 'default' => 'https://www.instagram.com/barbiebossdoll/'],
                    ['key' => 'footer.whatsapp_url', 'label' => 'Footer WhatsApp URL', 'type' => 'url', 'default' => 'https://api.whatsapp.com/send?phone=447346924436'],
                    ['key' => 'footer.telegram_url', 'label' => 'Footer Telegram URL', 'type' => 'url', 'default' => 'https://t.me/paradisedolls26'],
                    ['key' => 'footer.facebook_url', 'label' => 'Footer Facebook URL', 'type' => 'url', 'default' => 'https://www.facebook.com/share/19BBXuqjvS/?mibextid=wwXIfr'],
                    ['key' => 'footer.made_with_care', 'label' => 'Footer closing line', 'type' => 'text', 'default' => 'Made with care for members worldwide'],
                ],
            ],
        ];
    }

    public static function value(string $key, mixed $fallback = null): mixed
    {
        $saved = data_get(SiteSetting::get(self::SETTINGS_KEY, []), $key);

        if (self::hasContent($saved)) {
            return $saved;
        }

        $default = data_get(self::defaults(), $key, $fallback);

        return self::hasContent($default) ? $default : $fallback;
    }

    public static function text(string $key, ?string $fallback = ''): string
    {
        $value = self::value($key, $fallback);

        if (is_array($value)) {
            return trim(self::stringify($value));
        }

        return trim((string) $value);
    }

    public static function paragraphs(string $key): array
    {
        $value = self::value($key, []);

        if (is_array($value)) {
            return array_values(array_filter(
                array_map(fn ($item) => trim(self::stringify($item)), $value),
                fn ($item) => $item !== ''
            ));
        }

        return self::splitParagraphs((string) $value);
    }

    public static function items(string $key): array
    {
        $value = self::value($key, []);

        if (is_array($value)) {
            return array_values(array_filter(
                array_map(fn ($item) => self::normalizeItem($item), $value),
                fn ($item) => self::hasContent($item)
            ));
        }

        return self::splitLines((string) $value);
    }

    public static function image(string $key): string
    {
        return self::imageUrl(self::text($key));
    }

    public static function imageUrl(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if (preg_match('~^marketing/drafts/(blank_page_[12])/([a-zA-Z0-9._-]+)$~', $value, $matches)) {
            return route('editable-page.asset', ['page' => $matches[1], 'file' => $matches[2]]);
        }

        if (preg_match('/^https?:\/\//i', $value) || str_starts_with($value, '//')) {
            return $value;
        }

        if (str_starts_with($value, '/')) {
            return $value;
        }

        if (str_starts_with($value, 'images/') || str_starts_with($value, 'build/')) {
            return asset($value);
        }

        return Storage::disk('public')->url($value);
    }

    public static function link(string $key, string $fallback = '#'): string
    {
        $value = self::text($key, $fallback);

        return self::allowedLink($value) ? $value : $fallback;
    }

    public static function allowedLink(?string $value): bool
    {
        $value = trim((string) $value);

        return $value === ''
            || str_starts_with($value, '#')
            || str_starts_with($value, '/')
            || (bool) preg_match('/^https?:\/\/[^\s]+$/i', $value);
    }

    public static function fieldId(string $key): string
    {
        return str_replace('.', '__', $key);
    }

    public static function textareaValue(string $key, string $type): string
    {
        $value = self::value($key);

        if ($type === 'paragraphs') {
            return implode("\n\n", self::paragraphs($key));
        }

        if ($type === 'list') {
            return implode("\n", self::items($key));
        }

        if (is_array($value)) {
            return self::stringify($value);
        }

        return (string) $value;
    }

    public static function defaults(): array
    {
        static $defaults = null;

        if ($defaults !== null) {
            return $defaults;
        }

        $defaults = [];

        foreach (self::pages() as $pageKey => $page) {
            foreach ($page['fields'] as $field) {
                data_set($defaults, $pageKey.'.'.$field['key'], $field['default'] ?? null);
            }
        }

        return $defaults;
    }

    public static function splitLines(string $value): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/u', $value) ?: []), fn ($item) => $item !== ''));
    }

    public static function splitParagraphs(string $value): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R{2,}/u', $value) ?: []), fn ($item) => $item !== ''));
    }

    public static function blankPageKeys(): array
    {
        return ['blank_page_1', 'blank_page_2'];
    }

    private static function dollAfterDarkPage(): array
    {
        return [
            'label' => 'Doll After Dark',
            'route' => 'doll-after-dark',
            'fields' => [
                ['key' => 'hero.title', 'label' => 'Page title', 'type' => 'text', 'default' => 'Doll After Dark'],
                ['key' => 'hero.image', 'label' => 'Hero image', 'type' => 'image', 'default' => 'images/doll-after-dark/hero.png'],
                ['key' => 'hero.alt', 'label' => 'Hero image description', 'type' => 'text', 'default' => 'Boss Doll Creators presents Doll After Dark: pink Angel and black Devil holding phones. Heavenly feet. Devilish heels.'],
                ['key' => 'hero.body', 'label' => 'Hero body / search description', 'type' => 'textarea', 'default' => 'Find your creator identity and learn how to build an online brand around your personality, style and boundaries.'],
                ['key' => 'hero.course_label', 'label' => 'Explore course button', 'type' => 'text', 'default' => 'EXPLORE THE COURSE'],
                ['key' => 'hero.agency_label', 'label' => 'Explore agency button', 'type' => 'text', 'default' => 'EXPLORE AGENCY SUPPORT'],
                ['key' => 'about.eyebrow', 'label' => 'About eyebrow', 'type' => 'text', 'default' => 'Two sides. Your choice.'],
                ['key' => 'about.title', 'label' => 'About title', 'type' => 'text', 'default' => 'Which Doll are you?'],
                ['key' => 'about.body', 'label' => 'About body', 'type' => 'textarea', 'default' => 'Soft, spoiled and a little bratty. Bold, confident and commanding. Or a little of both.'],
                ['key' => 'angel.image', 'label' => 'Angel image', 'type' => 'image', 'default' => 'images/doll-after-dark/angel.jpg'],
                ['key' => 'angel.alt', 'label' => 'Angel image description', 'type' => 'text', 'default' => 'Angel styling with white feathered wings and an embellished ivory outfit'],
                ['key' => 'angel.title', 'label' => 'Angel title', 'type' => 'text', 'default' => 'Angel energy'],
                ['key' => 'angel.body', 'label' => 'Angel body', 'type' => 'textarea', 'default' => 'Pretty details, playful personality and pampered princess energy. From bare feet to cosy collections, explore a softer style with standards of your own.'],
                ['key' => 'devil.image', 'label' => 'Devil image', 'type' => 'image', 'default' => 'images/doll-after-dark/devil.jpg'],
                ['key' => 'devil.alt', 'label' => 'Devil image description', 'type' => 'text', 'default' => 'Darker fashion styling with a black corset'],
                ['key' => 'devil.title', 'label' => 'Devil title', 'type' => 'text', 'default' => 'Devil attitude'],
                ['key' => 'devil.body', 'label' => 'Devil body', 'type' => 'textarea', 'default' => 'Statement heels, darker styling and confident Goddess energy. Bring your presence, individuality and attitude — with your own limits leading the way.'],
                ['key' => 'about.note', 'label' => 'About closing note', 'type' => 'textarea', 'default' => 'You can explore either mood or combine both. Your look never decides what you have to offer. That choice stays with you.'],
                ['key' => 'styles.eyebrow', 'label' => 'Styles eyebrow', 'type' => 'text', 'default' => 'Make it yours'],
                ['key' => 'styles.title', 'label' => 'Styles title', 'type' => 'textarea', 'default' => "Your personality.\nYour brand."],
                ['key' => 'styles.body', 'label' => 'Styles body', 'type' => 'textarea', 'default' => 'Discover a creative direction that fits your interests, comfort and confidence. Start with one style or bring a few together.'],
                ['key' => 'styles.items', 'label' => 'Creator styles', 'type' => 'list', 'default' => ['Barefoot Angel', 'Sporty Brat', 'Spoiled Princess', 'Stiletto Goddess', 'Devil Doll', 'Alternative Doll']],
                ['key' => 'training.image', 'label' => 'Training image', 'type' => 'image', 'default' => 'images/doll-after-dark/lifestyle.jpg'],
                ['key' => 'training.alt', 'label' => 'Training image description', 'type' => 'text', 'default' => 'Kayla in a grey coat, photographed in a car'],
                ['key' => 'training.eyebrow', 'label' => 'Training eyebrow', 'type' => 'text', 'default' => 'Personality meets practical skills'],
                ['key' => 'training.title', 'label' => 'Training title', 'type' => 'textarea', 'default' => "You bring the ideas.\nLearn the business."],
                ['key' => 'training.body', 'label' => 'Training body', 'type' => 'textarea', 'default' => 'Build your direction with practical training designed to help you move from inspiration to a clear starting plan.'],
                ['key' => 'training.items', 'label' => 'Training bullets', 'type' => 'list', 'default' => ['Develop your creator identity and page', 'Plan content with a simple setup', 'Build clear offers and understand pricing', 'Write captions and communicate confidently', 'Promote your page and stay organised', 'Protect your privacy and boundaries']],
                ['key' => 'join.eyebrow', 'label' => 'Join eyebrow', 'type' => 'text', 'default' => 'Choose your way in'],
                ['key' => 'join.title', 'label' => 'Join title', 'type' => 'textarea', 'default' => "Build independently.\nOr build with support."],
                ['key' => 'join.body', 'label' => 'Join body', 'type' => 'textarea', 'default' => 'Two routes into Doll After Dark, depending on how you want to run your brand.'],
                ['key' => 'course.eyebrow', 'label' => 'Course eyebrow', 'type' => 'text', 'default' => 'The course'],
                ['key' => 'course.title', 'label' => 'Course title', 'type' => 'text', 'default' => 'Your brand, your way.'],
                ['key' => 'course.body', 'label' => 'Course body', 'type' => 'textarea', 'default' => 'Learn at your own pace and manage your business independently. The course is in preparation and is not available to purchase yet.'],
                ['key' => 'course.items', 'label' => 'Course bullets', 'type' => 'list', 'default' => ['Structured training and practical exercises', 'Develop your own content direction', 'Manage your page and customer conversations']],
                ['key' => 'course.button_label', 'label' => 'Disabled course button label', 'type' => 'text', 'default' => 'COURSE COMING SOON'],
                ['key' => 'agency.eyebrow', 'label' => 'Agency eyebrow', 'type' => 'text', 'default' => 'The agency'],
                ['key' => 'agency.title', 'label' => 'Agency title', 'type' => 'text', 'default' => 'A team behind your ideas.'],
                ['key' => 'agency.body', 'label' => 'Agency body', 'type' => 'textarea', 'default' => 'Training included, ongoing guidance and a dedicated chat team as part of your agreed agency package.'],
                ['key' => 'agency.items', 'label' => 'Agency bullets', 'type' => 'list', 'default' => ['Support with persona and content direction', 'Approved offers and boundaries agreed together', 'Responsibilities and revenue share explained before joining']],
                ['key' => 'agency.button_label', 'label' => 'Application button label', 'type' => 'text', 'default' => 'APPLY TO THE AGENCY'],
                ['key' => 'faq.eyebrow', 'label' => 'FAQ eyebrow', 'type' => 'text', 'default' => 'A few things to know'],
                ['key' => 'faq.title', 'label' => 'FAQ title', 'type' => 'text', 'default' => 'Your questions, answered.'],
                ['key' => 'faq.items', 'label' => 'FAQs', 'type' => 'cards', 'card_fields' => ['question' => 'text', 'answer' => 'textarea'], 'default' => [
                    ['question' => 'Do I need previous experience or a following?', 'answer' => 'No previous experience or established following is needed to start learning. Building your brand takes practice, time and consistency.'],
                    ['question' => 'Do I have to show my face or create nude content?', 'answer' => 'No. You can explore faceless and non-nude approaches. Platform identity-verification requirements still apply.'],
                    ['question' => 'Do I have to choose Angel or Devil?', 'answer' => 'No. They are moods to explore. You can choose one, combine both or develop your own style.'],
                    ['question' => 'Is joining the agency compulsory?', 'answer' => 'No. The standalone course is a separate route for women who want to manage their own page. The course is coming soon.'],
                    ['question' => 'Are earnings guaranteed?', 'answer' => 'No. Results depend on your content, audience, consistency, time and effort.'],
                ]],
                ['key' => 'closing.eyebrow', 'label' => 'Closing eyebrow', 'type' => 'text', 'default' => 'Doll After Dark'],
                ['key' => 'closing.title', 'label' => 'Closing title', 'type' => 'text', 'default' => 'Find your Doll energy.'],
                ['key' => 'closing.body', 'label' => 'Closing body', 'type' => 'textarea', 'default' => "Heavenly feet. Devilish heels.\nLearn the business of soles and stilettos."],
                ['key' => 'closing.button_label', 'label' => 'Closing button label', 'type' => 'text', 'default' => 'CHOOSE YOUR NEXT STEP'],
                ['key' => 'disclaimer.age', 'label' => 'Age and consent notice', 'type' => 'textarea', 'default' => 'For women aged 18+. All interactions must be consensual.'],
                ['key' => 'disclaimer.terms', 'label' => 'Platform and earnings notice', 'type' => 'textarea', 'default' => 'Platform rules apply. Earnings are not guaranteed. Agency terms apply.'],
            ],
        ];
    }

    private static function blankPage(int $number): array
    {
        return [
            'label' => "Blank Page {$number}",
            'route' => 'admin.site-editor.preview',
            'parameters' => ['page' => "blank_page_{$number}"],
            'fields' => [
                ['key' => 'published', 'label' => 'Published', 'type' => 'boolean', 'default' => false],
                ['key' => 'title', 'label' => 'Page title', 'type' => 'text', 'default' => ''],
                ['key' => 'description', 'label' => 'Search description', 'type' => 'textarea', 'default' => ''],
                ['key' => 'logo', 'label' => 'Page logo', 'type' => 'image', 'default' => ''],
                ['key' => 'hero.image', 'label' => 'Banner image', 'type' => 'image', 'default' => ''],
                ['key' => 'body', 'label' => 'Introduction', 'type' => 'paragraphs', 'default' => []],
                ['key' => 'sections', 'label' => 'Content sections', 'type' => 'cards', 'card_fields' => ['image' => 'image', 'title' => 'text', 'body' => 'textarea'], 'default' => []],
            ],
        ];
    }

    private static function simplePage(string $label, string $route, string $heroImage, string $heroEyebrow, string $heroTitle, string $heroBody, array $extraFields): array
    {
        return [
            'label' => $label,
            'route' => $route,
            'fields' => array_merge([
                ['key' => 'hero.image', 'label' => 'Hero image', 'type' => 'image', 'default' => $heroImage],
                ['key' => 'hero.eyebrow', 'label' => 'Hero eyebrow', 'type' => 'text', 'default' => $heroEyebrow],
                ['key' => 'hero.title', 'label' => 'Hero title', 'type' => 'text', 'default' => $heroTitle],
                ['key' => 'hero.body', 'label' => 'Hero body', 'type' => 'textarea', 'default' => $heroBody],
            ], $extraFields),
        ];
    }

    private static function hasContent(mixed $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                if (self::hasContent($item)) {
                    return true;
                }
            }

            return false;
        }

        return trim((string) $value) !== '';
    }

    private static function normalizeItem(mixed $item): mixed
    {
        if (! is_array($item)) {
            return trim((string) $item);
        }

        $normalized = [];

        foreach ($item as $key => $value) {
            $normalized[$key] = trim(self::stringify($value));
        }

        return $normalized;
    }

    private static function stringify(mixed $value, string $separator = "\n"): string
    {
        if (! is_array($value)) {
            return (string) $value;
        }

        $parts = [];

        foreach ($value as $item) {
            $text = trim(self::stringify($item, $separator));

            if ($text !== '') {
                $parts[] = $text;
            }
        }

        return implode($separator, $parts);
    }
}
