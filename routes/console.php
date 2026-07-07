<?php

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonContentBlock;
use App\Services\CourseCommunityService;
use App\Services\EmailCampaignDispatcher;
use App\Support\SqlDumpInsertParser;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:test {email? : Recipient address (defaults to MAIL_FROM_ADDRESS)}', function () {
    $to = $this->argument('email') ?: config('mail.from.address');

    Mail::raw('Paradise Dolls mail test - if you received this, outbound mail is configured.', function ($message) use ($to) {
        $message->to($to)->subject(config('app.name').' mail test');
    });

    $this->components->info('Sent via '.config('mail.default').' to '.$to);
})->purpose('Send one test email using the configured mail driver');

Artisan::command('email-campaigns:dispatch', function () {
    $count = app(EmailCampaignDispatcher::class)->dispatchDue();

    $this->components->info("Dispatched {$count} due email campaign(s).");
})->purpose('Queue all due email campaigns');

Schedule::command('email-campaigns:dispatch')
    ->everyMinute()
    ->withoutOverlapping();

Artisan::command('courses:import-from-sql {path : SQL dump path} {--slug=boss-doll-blueprint-the-ultimate-multi-streaming-online-brand-mastery-course : Course slug to import} {--dry-run : Parse only; do not write to the database} {--force : Skip production confirmation}', function () {
    $resolvePath = function (string $path): ?string {
        $candidates = [
            $path,
            base_path($path),
            storage_path($path),
            storage_path('app/'.$path),
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    };

    $filePath = $resolvePath((string) $this->argument('path'));
    if (! $filePath) {
        $this->components->error('SQL dump not found. Use an absolute path or a path relative to the project/storage folder.');

        return 1;
    }

    $parser = new SqlDumpInsertParser((string) file_get_contents($filePath));
    $slug = (string) $this->option('slug');

    $sourceCourse = collect($parser->rowsForTable('courses'))
        ->firstWhere('slug', $slug);

    if (! $sourceCourse) {
        $this->components->error("No course with slug [{$slug}] was found in the dump.");

        return 1;
    }

    $sourceCourseId = (int) $sourceCourse['id'];
    $sourceModules = collect($parser->rowsForTable('course_modules'))
        ->filter(fn (array $row): bool => (int) $row['course_id'] === $sourceCourseId)
        ->sortBy(fn (array $row): string => str_pad((string) $row['sort_order'], 6, '0', STR_PAD_LEFT).'-'.str_pad((string) $row['id'], 6, '0', STR_PAD_LEFT))
        ->values();

    $sourceLessons = collect($parser->rowsForTable('lessons'))
        ->filter(fn (array $row): bool => (int) $row['course_id'] === $sourceCourseId)
        ->sortBy(fn (array $row): string => str_pad((string) $row['sort_order'], 6, '0', STR_PAD_LEFT).'-'.str_pad((string) $row['id'], 6, '0', STR_PAD_LEFT))
        ->values();

    $sourceLessonIds = $sourceLessons
        ->pluck('id')
        ->map(fn (mixed $id): int => (int) $id)
        ->flip();

    $sourceBlocks = collect($parser->rowsForTable('lesson_content_blocks'))
        ->filter(fn (array $row): bool => $sourceLessonIds->has((int) $row['lesson_id']))
        ->sortBy(fn (array $row): string => str_pad((string) $row['lesson_id'], 6, '0', STR_PAD_LEFT).'-'.str_pad((string) $row['sort_order'], 6, '0', STR_PAD_LEFT).'-'.str_pad((string) $row['id'], 6, '0', STR_PAD_LEFT))
        ->values();

    $assetPaths = collect([$sourceCourse])
        ->merge($sourceLessons)
        ->merge($sourceBlocks)
        ->flatMap(function (array $row): array {
            return Arr::only($row, [
                'thumbnail_url',
                'course_cover_image',
                'course_outline_url',
                'intro_bunny_thumbnail_url',
                'lesson_banner_image',
                'pdf_url',
                'presentation_url',
                'bunny_thumbnail_url',
                'image_path',
                'file_path',
            ]);
        })
        ->filter(fn (mixed $path): bool => is_string($path) && $path !== '' && ! preg_match('/^https?:\/\//i', $path))
        ->map(fn (string $path): string => trim(str_replace('\\', '/', $path), '/'))
        ->unique()
        ->values();

    $missingAssets = $assetPaths
        ->filter(fn (string $path): bool => str_starts_with($path, 'academy/') && ! Storage::disk('public')->exists($path))
        ->values();

    $this->components->info("Found course [{$sourceCourse['title']}].");
    $this->line("Modules: {$sourceModules->count()}");
    $this->line("Lessons: {$sourceLessons->count()}");
    $this->line("Content blocks: {$sourceBlocks->count()}");

    if ($missingAssets->isNotEmpty()) {
        $this->components->warn('The dump references files that are not currently on the public storage disk:');
        foreach ($missingAssets as $path) {
            $this->line(" - {$path}");
        }
    }

    if ($this->option('dry-run')) {
        $this->components->info('Dry run complete. No database changes were made.');

        return 0;
    }

    if (app()->isProduction() && ! $this->option('force')) {
        $confirmed = $this->confirm('Import this course into production? Existing lessons/content for the same course slug will be replaced, but users/enrollments are kept.', false);

        if (! $confirmed) {
            $this->components->warn('Import cancelled.');

            return 1;
        }
    }

    $decodeJson = function (mixed $value, mixed $fallback): mixed {
        if ($value === null || $value === '') {
            return $fallback;
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $fallback;
    };

    $course = null;

    DB::transaction(function () use ($sourceCourse, $sourceModules, $sourceLessons, $sourceBlocks, $decodeJson, &$course): void {
        $courseData = Arr::only($sourceCourse, (new Course)->getFillable());
        $course = Course::query()->where('slug', $courseData['slug'])->first();

        if ($course) {
            Lesson::query()->where('course_id', $course->id)->delete();
            CourseModule::query()->where('course_id', $course->id)->delete();

            $course->forceFill($courseData)->save();
        } else {
            $course = Course::create($courseData);
        }

        $course->chatRoom()->updateOrCreate([], [
            'name' => $course->title.' Community',
        ]);

        $moduleMap = [];
        foreach ($sourceModules as $sourceModule) {
            $module = CourseModule::create([
                ...Arr::only($sourceModule, (new CourseModule)->getFillable()),
                'course_id' => $course->id,
            ]);

            $moduleMap[(int) $sourceModule['id']] = $module->id;
        }

        $lessonMap = [];
        foreach ($sourceLessons as $sourceLesson) {
            $lessonData = Arr::only($sourceLesson, (new Lesson)->getFillable());
            $lessonData['course_id'] = $course->id;
            $lessonData['course_module_id'] = $sourceLesson['course_module_id'] === null
                ? null
                : ($moduleMap[(int) $sourceLesson['course_module_id']] ?? null);
            $lessonData['lesson_images'] = $decodeJson($lessonData['lesson_images'] ?? null, []);

            $lesson = Lesson::create($lessonData);
            $lessonMap[(int) $sourceLesson['id']] = $lesson->id;
        }

        foreach ($sourceBlocks as $sourceBlock) {
            $blockData = Arr::only($sourceBlock, (new LessonContentBlock)->getFillable());
            $blockData['lesson_id'] = $lessonMap[(int) $sourceBlock['lesson_id']] ?? null;
            $blockData['settings'] = $decodeJson($blockData['settings'] ?? null, null);

            if ($blockData['lesson_id']) {
                LessonContentBlock::create($blockData);
            }
        }

        app(CourseCommunityService::class)->ensureForCourse($course);
    });

    $this->components->info("Imported course id {$course->id}: {$course->title}");
    $this->components->warn('Users, enrollments, lesson progress, access requests, and old dump users were not imported.');

    return 0;
})->purpose('Import one course tree from a phpMyAdmin SQL dump without importing users or enrollments');
