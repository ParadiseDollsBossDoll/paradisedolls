<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@agency.test'],
            [
                'name' => 'Agency Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        foreach ($this->bossDollBlueprintCourses() as $courseData) {
            $lessons = $courseData['lessons'];
            unset($courseData['lessons']);

            $course = Course::query()->updateOrCreate(
                ['slug' => $courseData['slug']],
                $courseData
            );

            if ($course->lessons()->count() === 0) {
                $course->lessons()->createMany($lessons);
            }
        }

        foreach ($this->starterTestimonials() as $testimonialData) {
            Testimonial::query()->updateOrCreate(
                ['name' => $testimonialData['name'], 'headline' => $testimonialData['headline']],
                $testimonialData
            );
        }
    }

    private function bossDollBlueprintCourses(): array
    {
        return [
            [
                'title' => 'Boss Doll Blueprint Orientation',
                'slug' => 'boss-doll-blueprint-orientation',
                'platform_label' => 'Foundation',
                'platform_color' => '#C9A96E',
                'description' => 'Introduction to Kayla, Paradise Dolls, the community standards, and how the academy is structured.',
                'is_published' => true,
                'sort_order' => 1,
                'lessons' => [
                    [
                        'title' => 'Introduction to Kayla & Paradise Dolls',
                        'body' => 'Meet the founder story, the mission behind Paradise Dolls, and the support structure members can expect.',
                        'duration' => '08:00',
                        'has_pdf' => true,
                        'sort_order' => 1,
                    ],
                    [
                        'title' => 'How the Boss Doll Blueprint works',
                        'body' => 'Overview of the three learning formats: written PDF guides, visual presentations, and screen-recorded walkthroughs.',
                        'duration' => '06:30',
                        'has_pdf' => true,
                        'sort_order' => 2,
                    ],
                ],
            ],
            [
                'title' => 'Safety & Professionalism',
                'slug' => 'safety-professionalism',
                'platform_label' => 'Onboarding',
                'platform_color' => '#C4687A',
                'description' => 'Professional standards, privacy, age verification, boundaries, and the safety-first expectations for going live.',
                'is_published' => true,
                'sort_order' => 2,
                'lessons' => [
                    [
                        'title' => 'Safety standards and privacy',
                        'body' => 'Core safety guidance for identity, privacy, platform rules, personal boundaries, and professional conduct.',
                        'duration' => '10:00',
                        'has_pdf' => true,
                        'sort_order' => 1,
                    ],
                    [
                        'title' => 'Professional communication',
                        'body' => 'How to stay confident, calm, and consistent while interacting with customers and the support team.',
                        'duration' => '09:30',
                        'has_pdf' => false,
                        'sort_order' => 2,
                    ],
                ],
            ],
            [
                'title' => 'Stream Preparation & Setup',
                'slug' => 'stream-preparation-setup',
                'platform_label' => 'Setup',
                'platform_color' => '#E8C88A',
                'description' => 'Equipment, lighting, framing, internet, workspace, and preparation before platform walkthroughs begin.',
                'is_published' => true,
                'sort_order' => 3,
                'lessons' => [
                    [
                        'title' => 'Equipment and room setup',
                        'body' => 'A practical setup checklist covering camera, lighting, sound, internet, privacy, and comfort.',
                        'duration' => '12:00',
                        'has_pdf' => true,
                        'sort_order' => 1,
                    ],
                    [
                        'title' => 'Pre-stream preparation',
                        'body' => 'How to prepare your schedule, mindset, visuals, goals, and workflow before each live session.',
                        'duration' => '11:15',
                        'has_pdf' => true,
                        'sort_order' => 2,
                    ],
                ],
            ],
            [
                'title' => 'Core Platform Walkthrough System',
                'slug' => 'core-platform-walkthrough-system',
                'platform_label' => 'Walkthroughs',
                'platform_color' => '#FF3E4D',
                'description' => 'The main academy section for platform navigation, stream controls, rankings, tools, earnings systems, and customer retention.',
                'is_published' => true,
                'sort_order' => 4,
                'lessons' => [
                    [
                        'title' => 'Platform navigation',
                        'body' => 'Walkthrough of key areas, dashboards, menus, earnings pages, settings, and common platform controls.',
                        'duration' => '14:00',
                        'has_pdf' => true,
                        'sort_order' => 1,
                    ],
                    [
                        'title' => 'Monetisation tools and rankings',
                        'body' => 'Understand earning tools, user value signals, ranking factors, and features that help drive visibility.',
                        'duration' => '16:30',
                        'has_pdf' => true,
                        'sort_order' => 2,
                    ],
                    [
                        'title' => 'Customer interaction and retention',
                        'body' => 'Customer engagement, return visits, conversation flow, and retention systems for sustainable growth.',
                        'duration' => '18:00',
                        'has_pdf' => true,
                        'sort_order' => 3,
                    ],
                ],
            ],
            [
                'title' => 'Growth, Conversion & Passive Income',
                'slug' => 'growth-conversion-passive-income',
                'platform_label' => 'Strategy',
                'platform_color' => '#9B6DFF',
                'description' => 'Customer psychology, conversion, objection handling, high-value customers, content creation, messaging income, and monetisation strategy.',
                'is_published' => true,
                'sort_order' => 5,
                'lessons' => [
                    [
                        'title' => 'Customer psychology and conversion',
                        'body' => 'How customers think, what builds trust, and how to move interactions toward stronger conversions.',
                        'duration' => '15:00',
                        'has_pdf' => true,
                        'sort_order' => 1,
                    ],
                    [
                        'title' => 'Messaging income and passive systems',
                        'body' => 'Ways to support live income with messaging, content assets, customer follow-up, and retention workflows.',
                        'duration' => '13:30',
                        'has_pdf' => true,
                        'sort_order' => 2,
                    ],
                ],
            ],
        ];
    }

    private function starterTestimonials(): array
    {
        return [
            [
                'name' => 'New Paradise Dolls member',
                'headline' => 'From nervous beginner to confident online earner',
                'quote' => 'I joined because I wanted more freedom but had no clue where to start. Having structure, support, and walkthroughs made the whole process feel achievable.',
                'location' => 'Remote',
                'result_label' => 'Beginner confidence',
                'image_url' => 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&q=85&w=900',
                'is_published' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Boss Doll Blueprint student',
                'headline' => 'The system made the platforms less overwhelming',
                'quote' => 'The biggest difference was not feeling left alone. The platform walkthroughs helped me understand controls, customer flow, and what to focus on first.',
                'location' => 'United Kingdom',
                'result_label' => 'Platform clarity',
                'image_url' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&q=85&w=900',
                'is_published' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Paradise Dolls community member',
                'headline' => 'I finally felt like I had a team behind me',
                'quote' => 'The community and onboarding gave me the confidence to take the opportunity seriously while still building around my own schedule.',
                'location' => 'Travel / Remote',
                'result_label' => 'Community support',
                'image_url' => 'https://images.unsplash.com/photo-1506929562872-bb421503ef21?auto=format&fit=crop&q=85&w=900',
                'is_published' => true,
                'sort_order' => 3,
            ],
        ];
    }
}
