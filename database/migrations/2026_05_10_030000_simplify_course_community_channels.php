<?php

use App\Models\CommunityChannel;
use App\Models\CommunityChannelAccess;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // For each course, keep only the *general* channel as the primary chat.
        // Archive announcements and support channels (messages are preserved).
        // Rename "X general" → "X" (just the course title) and fix the slug.

        $courseIds = DB::table('community_channels')
            ->whereNotNull('course_id')
            ->distinct()
            ->pluck('course_id');

        foreach ($courseIds as $courseId) {
            $channels = DB::table('community_channels')
                ->where('course_id', $courseId)
                ->where('is_archived', false)
                ->orderBy('id')
                ->get(['id', 'name', 'slug', 'course_name']);

            if ($channels->isEmpty()) {
                continue;
            }

            // Identify primary channel: prefer one ending in " general", otherwise first
            $primary = $channels->first(fn ($c) => Str::endsWith($c->name, ' general'))
                ?? $channels->first();

            $secondaries = $channels->reject(fn ($c) => $c->id === $primary->id);

            // Rename primary to just the course title and fix slug
            $courseName  = $primary->course_name ?? Str::before($primary->name, ' general');
            $desiredSlug = Str::slug($courseName);

            // Avoid slug collision with other channels (course or global)
            $slug   = $desiredSlug;
            $suffix = 2;
            while (
                DB::table('community_channels')
                    ->where('slug', $slug)
                    ->where('id', '!=', $primary->id)
                    ->exists()
            ) {
                $slug = $desiredSlug.'-'.$suffix++;
            }

            DB::table('community_channels')
                ->where('id', $primary->id)
                ->update([
                    'name'       => $courseName,
                    'slug'       => $slug,
                    'updated_at' => now(),
                ]);

            // Move any access grants from secondary channels to the primary
            foreach ($secondaries as $secondary) {
                $existingUserIds = DB::table('community_channel_accesses')
                    ->where('community_channel_id', $primary->id)
                    ->pluck('user_id')
                    ->all();

                $grants = DB::table('community_channel_accesses')
                    ->where('community_channel_id', $secondary->id)
                    ->get();

                foreach ($grants as $grant) {
                    if (! in_array($grant->user_id, $existingUserIds, true)) {
                        DB::table('community_channel_accesses')->insert([
                            'community_channel_id' => $primary->id,
                            'user_id'              => $grant->user_id,
                            'invited_by'           => $grant->invited_by,
                            'created_at'           => $grant->created_at,
                            'updated_at'           => $grant->updated_at,
                        ]);
                    }
                }
            }

            // Archive secondary channels
            $secondaryIds = $secondaries->pluck('id')->all();
            if ($secondaryIds !== []) {
                DB::table('community_channels')
                    ->whereIn('id', $secondaryIds)
                    ->update([
                        'is_archived' => true,
                        'updated_at'  => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Reversing channel renames would require storing originals — not worth it.
        // Simply unarchive any course channels that were archived by this migration.
        DB::table('community_channels')
            ->whereNotNull('course_id')
            ->where('is_archived', true)
            ->update(['is_archived' => false, 'updated_at' => now()]);
    }
};
