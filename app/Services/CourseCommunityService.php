<?php

namespace App\Services;

use App\Models\CommunityChannel;
use App\Models\CommunityChannelAccess;
use App\Models\CommunityMessage;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CourseCommunityService
{
    private const CATEGORY = 'ACADEMY COURSES';

    /**
     * Idempotently ensure one community channel exists for this course.
     * Safe to call multiple times — creates only if missing.
     */
    public function ensureForCourse(Course $course): CommunityChannel
    {
        $existing = CommunityChannel::query()
            ->where('course_id', $course->id)
            ->where('is_archived', false)
            ->first();

        if ($existing) {
            return $existing;
        }

        $adminId = User::query()
            ->where('role', 'admin')
            ->orderBy('id')
            ->value('id');

        $slug = CommunityChannel::makeUniqueSlug($course->slug);

        return CommunityChannel::create([
            'name'            => $course->title,
            'slug'            => $slug,
            'category'        => self::CATEGORY,
            'description'     => "Community chat for {$course->title} enrolled members.",
            'course_id'       => $course->id,
            'course_name'     => $course->title,
            'created_by'      => $adminId,
            'is_private'      => true,
            'access_mode'     => CommunityChannel::ACCESS_INVITE,
            'denied_behavior' => CommunityChannel::DENIED_HIDDEN,
            'is_locked'       => false,
            'slowmode_seconds' => 0,
        ]);
    }

    /**
     * Grant a user access to the course community channel.
     * Posts a one-time welcome message on first join.
     */
    public function joinCourse(User $user, Course $course): void
    {
        $channel = $this->ensureForCourse($course);

        DB::transaction(function () use ($user, $channel, $course): void {
            CommunityChannelAccess::query()->firstOrCreate([
                'community_channel_id' => $channel->id,
                'user_id'              => $user->id,
            ], [
                'invited_by' => null,
            ]);

            $alreadyWelcomed = CommunityMessage::query()
                ->where('channel_id', $channel->id)
                ->where('user_id', $user->id)
                ->where('message', 'like', 'Just joined%')
                ->exists();

            if (! $alreadyWelcomed) {
                CommunityMessage::create([
                    'channel_id' => $channel->id,
                    'user_id'    => $user->id,
                    'message'    => "Just joined the **{$course->title}** community. Excited to learn! 🎉",
                ]);

                $channel->update(['last_message_at' => now()]);
            }
        });
    }

    /**
     * Revoke access. Messages and history are preserved.
     */
    public function leaveCourse(User $user, Course $course): void
    {
        $channelIds = CommunityChannel::query()
            ->where('course_id', $course->id)
            ->pluck('id');

        CommunityChannelAccess::query()
            ->whereIn('community_channel_id', $channelIds)
            ->where('user_id', $user->id)
            ->delete();
    }

    /**
     * Backfill access for all currently enrolled members.
     */
    public function syncEnrollees(Course $course): void
    {
        foreach ($course->enrolledUsers()->get() as $user) {
            $this->joinCourse($user, $course);
        }
    }

    /**
     * Archive the course channel when the course is deleted.
     */
    public function archiveForCourse(Course $course): void
    {
        CommunityChannel::query()
            ->where('course_id', $course->id)
            ->update(['is_archived' => true]);
    }
}
