<?php

namespace Tests;

use App\Models\ModelProfile;
use App\Models\User;
use App\Support\CommunityPresence;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function grantCommunityAccess(User $user): User
    {
        ModelProfile::query()->updateOrCreate([
            'user_id' => $user->id,
        ], [
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_VERIFIED,
            'verification_submitted_at' => now(),
            'id_document_path' => 'verifications/'.$user->id.'/id.jpg',
            'selfie_with_id_path' => 'verifications/'.$user->id.'/selfie.jpg',
            'community_invited_at' => now(),
            'community_role_assigned_at' => now(),
        ]);

        $user->unsetRelation('modelProfile');
        CommunityPresence::forgetMemberDirectory();

        return $user;
    }
}
