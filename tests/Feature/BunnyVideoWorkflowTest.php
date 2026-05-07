<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BunnyVideoWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_existing_bunny_videos_without_exposing_api_key(): void
    {
        config()->set('services.bunny.library_id', '654926');
        config()->set('services.bunny.api_key', 'secret-key');
        config()->set('services.bunny.cdn_hostname', 'vz-049511ff-031.b-cdn.net');

        Http::fake([
            'video.bunnycdn.com/library/654926/videos*' => Http::response([
                'totalItems' => 1,
                'currentPage' => 1,
                'items' => [
                    [
                        'guid' => '9c5c0f62-dddd-4444-8888-111111111111',
                        'videoLibraryId' => 654926,
                        'title' => 'Course Orientation',
                        'length' => 32,
                        'thumbnailFileName' => 'thumbnail.jpg',
                        'status' => 4,
                    ],
                ],
            ]),
        ]);

        $response = $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->getJson(route('admin.bunny.videos.index', ['search' => 'orientation']))
            ->assertOk()
            ->assertJsonPath('items.0.id', '9c5c0f62-dddd-4444-8888-111111111111')
            ->assertJsonPath('items.0.duration', '0:32');

        $this->assertStringNotContainsString('secret-key', $response->getContent());

        Http::assertSent(fn ($request) => $request->hasHeader('AccessKey', 'secret-key'));
    }

    public function test_admin_can_create_signed_direct_upload_intent(): void
    {
        config()->set('services.bunny.library_id', '654926');
        config()->set('services.bunny.api_key', 'secret-key');
        config()->set('services.bunny.cdn_hostname', 'vz-049511ff-031.b-cdn.net');

        Http::fake([
            'video.bunnycdn.com/library/654926/videos' => Http::response([
                'guid' => '9c5c0f62-dddd-4444-8888-111111111111',
                'videoLibraryId' => 654926,
                'title' => 'New Upload',
                'length' => 0,
                'status' => 0,
            ], 200),
        ]);

        $response = $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->postJson(route('admin.bunny.videos.upload-intent'), [
                'title' => 'New Upload',
                'file_name' => 'new-upload.mp4',
                'file_size' => 123456,
                'fingerprint' => 'new-upload.mp4:123456:999',
            ])
            ->assertOk()
            ->assertJsonPath('duplicate', false)
            ->assertJsonPath('video.id', '9c5c0f62-dddd-4444-8888-111111111111')
            ->assertJsonPath('upload.library_id', '654926')
            ->assertJsonPath('upload.video_id', '9c5c0f62-dddd-4444-8888-111111111111');

        $payload = $response->json();

        $this->assertSame(
            hash('sha256', '654926'.'secret-key'.$payload['upload']['expires_at'].'9c5c0f62-dddd-4444-8888-111111111111'),
            $payload['upload']['signature']
        );
        $this->assertStringNotContainsString('secret-key', $response->getContent());
    }

    public function test_upload_intent_reuses_existing_bunny_video_when_fingerprint_matches(): void
    {
        config()->set('services.bunny.library_id', '654926');
        config()->set('services.bunny.api_key', 'secret-key');

        $course = Course::create([
            'title' => 'Existing Upload Course',
            'slug' => 'existing-upload-course',
            'platform_label' => 'Training',
            'description' => 'Course using a saved Bunny upload.',
            'is_published' => true,
        ]);
        $course->lessons()->create([
            'title' => 'Existing Bunny Lesson',
            'bunny_video_id' => '9c5c0f62-dddd-4444-8888-111111111111',
            'bunny_library_id' => '654926',
            'bunny_video_title' => 'Existing Bunny Upload',
            'bunny_upload_fingerprint' => 'existing.mp4:123456:999',
            'duration' => '0:32',
            'sort_order' => 1,
        ]);

        Http::fake();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->postJson(route('admin.bunny.videos.upload-intent'), [
                'title' => 'Existing Bunny Upload',
                'file_name' => 'existing.mp4',
                'file_size' => 123456,
                'fingerprint' => 'existing.mp4:123456:999',
            ])
            ->assertOk()
            ->assertJsonPath('duplicate', true)
            ->assertJsonPath('video.id', '9c5c0f62-dddd-4444-8888-111111111111')
            ->assertJsonPath('upload', null);

        Http::assertNothingSent();
    }

    public function test_upload_intent_reuses_existing_course_intro_bunny_video_when_fingerprint_matches(): void
    {
        config()->set('services.bunny.library_id', '654926');
        config()->set('services.bunny.api_key', 'secret-key');

        Course::create([
            'title' => 'Intro Upload Course',
            'slug' => 'intro-upload-course',
            'platform_label' => 'Training',
            'description' => 'Course using a saved Bunny intro upload.',
            'has_intro' => true,
            'intro_title' => 'Course Orientation',
            'intro_bunny_video_id' => 'aaaaaaaa-dddd-4444-8888-111111111111',
            'intro_bunny_library_id' => '654926',
            'intro_bunny_video_title' => 'Saved Intro Upload',
            'intro_bunny_upload_fingerprint' => 'intro.mp4:123456:999',
            'intro_duration' => '0:32',
            'is_published' => true,
        ]);

        Http::fake();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->postJson(route('admin.bunny.videos.upload-intent'), [
                'title' => 'Saved Intro Upload',
                'file_name' => 'intro.mp4',
                'file_size' => 123456,
                'fingerprint' => 'intro.mp4:123456:999',
            ])
            ->assertOk()
            ->assertJsonPath('duplicate', true)
            ->assertJsonPath('video.id', 'aaaaaaaa-dddd-4444-8888-111111111111')
            ->assertJsonPath('upload', null);

        Http::assertNothingSent();
    }

    public function test_course_creation_persists_bunny_lesson_metadata(): void
    {
        config()->set('services.bunny.library_id', '654926');

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.courses.store'), [
            'title' => 'Bunny Course',
            'slug' => 'bunny-course',
            'platform_label' => 'Training',
            'description' => 'Course using Bunny Stream.',
            'is_published' => '1',
            'sort_order' => 1,
            'lessons' => [
                [
                    'title' => 'Bunny Lesson',
                    'body' => 'Watch this Bunny-hosted lesson.',
                    'bunny_video_id' => '9c5c0f62-dddd-4444-8888-111111111111',
                    'bunny_library_id' => '654926',
                    'bunny_video_title' => 'Bunny Lesson Upload',
                    'bunny_thumbnail_url' => 'https://vz-049511ff-031.b-cdn.net/9c5c0f62-dddd-4444-8888-111111111111/thumbnail.jpg',
                    'bunny_upload_fingerprint' => 'lesson.mp4:123456:999',
                    'bunny_status' => 4,
                    'duration' => '1:02',
                    'sort_order' => 1,
                ],
            ],
        ])->assertRedirect(route('admin.courses.index'));

        $lesson = Course::where('slug', 'bunny-course')->firstOrFail()->lessons()->firstOrFail();

        $this->assertSame('9c5c0f62-dddd-4444-8888-111111111111', $lesson->bunny_video_id);
        $this->assertSame('654926', $lesson->bunny_library_id);
        $this->assertSame('Bunny Lesson Upload', $lesson->bunny_video_title);
        $this->assertStringContainsString('/embed/654926/9c5c0f62-dddd-4444-8888-111111111111', $lesson->video_url);
    }

    public function test_course_creation_persists_bunny_intro_video_metadata(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.courses.store'), [
            'title' => 'Bunny Intro Course',
            'slug' => 'bunny-intro-course',
            'platform_label' => 'Training',
            'description' => 'Course with a Bunny Stream intro.',
            'has_intro' => '1',
            'intro_title' => 'Course Orientation',
            'intro_bunny_video_id' => 'bbbbbbbb-dddd-4444-8888-111111111111',
            'intro_bunny_library_id' => '654926',
            'intro_bunny_video_title' => 'Course Orientation Upload',
            'intro_bunny_thumbnail_url' => 'https://vz-049511ff-031.b-cdn.net/bbbbbbbb-dddd-4444-8888-111111111111/thumbnail.jpg',
            'intro_bunny_upload_fingerprint' => 'intro.mp4:123456:999',
            'intro_bunny_status' => 4,
            'intro_duration' => '1:15',
            'is_published' => '1',
            'sort_order' => 1,
            'lessons' => [
                [
                    'title' => 'First Lesson',
                    'sort_order' => 1,
                ],
            ],
        ])->assertRedirect(route('admin.courses.index'));

        $course = Course::where('slug', 'bunny-intro-course')->firstOrFail();

        $this->assertSame('bbbbbbbb-dddd-4444-8888-111111111111', $course->intro_bunny_video_id);
        $this->assertSame('654926', $course->intro_bunny_library_id);
        $this->assertSame('Course Orientation Upload', $course->intro_bunny_video_title);
        $this->assertStringContainsString('/embed/654926/bbbbbbbb-dddd-4444-8888-111111111111', $course->intro_video_url);
        $this->assertSame($course->intro_video_url, $course->introVideoEmbedUrl());
    }
}
