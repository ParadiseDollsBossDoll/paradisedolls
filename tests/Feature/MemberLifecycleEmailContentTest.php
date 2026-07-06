<?php

namespace Tests\Feature;

use App\Mail\AccountApprovalMail;
use App\Mail\CommunityAccessMail;
use App\Mail\MemberApplicationApprovedMail;
use App\Mail\ModelInformationSubmittedMail;
use App\Mail\VerificationSubmissionReceivedMail;
use App\Models\ModelProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberLifecycleEmailContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_approval_email_contains_the_new_copy_and_pink_action(): void
    {
        $html = (new MemberApplicationApprovedMail(
            memberName: 'Approved Doll',
            temporaryPassword: 'Temporary123',
            loginUrl: 'https://example.com/login',
            onboardingUrl: 'https://example.com/onboarding',
        ))->render();

        $this->assertStringContainsString('Amazing news', $html);
        $this->assertStringContainsString('Temporary123', $html);
        $this->assertStringContainsString('Complete Model Information Form', $html);
        $this->assertStringContainsString('background-color: #EEB4C3', $html);
        $this->assertContactDetailsArePresent($html);
    }

    public function test_onboarding_and_verification_emails_match_their_actual_stage(): void
    {
        $member = User::factory()->create([
            'name' => 'Journey Model',
            'role' => 'model',
        ]);
        $profile = ModelProfile::create(['user_id' => $member->id])->load('user');

        $onboardingHtml = (new ModelInformationSubmittedMail($profile))->render();
        $this->assertStringContainsString('received everything successfully', $onboardingHtml);
        $this->assertStringContainsString('next step is to complete your identity verification', $onboardingHtml);
        $this->assertContactDetailsArePresent($onboardingHtml);

        $submissionHtml = (new VerificationSubmissionReceivedMail($profile))->render();
        $this->assertStringContainsString('successfully received your verification documents', $submissionHtml);
        $this->assertStringContainsString('team will now review them', $submissionHtml);
        $this->assertStringNotContainsString('YOU DID IT', $submissionHtml);
        $this->assertContactDetailsArePresent($submissionHtml);

        $approvalHtml = (new AccountApprovalMail(
            profile: $profile,
            dashboardUrl: 'https://example.com/dashboard',
            whatsappCommunityUrl: 'https://chat.whatsapp.com/example',
        ))->render();
        $this->assertStringContainsString('Journey Model Doll', $approvalHtml);
        $this->assertStringContainsString('YOU DID IT', $approvalHtml);
        $this->assertStringContainsString('successfully approved', $approvalHtml);
        $this->assertStringContainsString('background-color: #EEB4C3', $approvalHtml);
        $this->assertStringContainsString('Join Our WhatsApp Community', $approvalHtml);
        $this->assertContactDetailsArePresent($approvalHtml);
    }

    public function test_community_email_contains_discord_and_whatsapp_invitations(): void
    {
        $member = User::factory()->create([
            'name' => 'Community Model',
            'role' => 'model',
        ]);
        $profile = ModelProfile::create(['user_id' => $member->id])->load('user');

        $html = (new CommunityAccessMail(
            profile: $profile,
            communityUrl: 'https://discord.gg/GvKNFmeRm',
            whatsappCommunityUrl: 'https://chat.whatsapp.com/example',
            roleName: 'Boss Doll Blueprint',
        ))->render();

        $this->assertStringContainsString('Join Our Discord Community', $html);
        $this->assertStringContainsString('https://discord.gg/GvKNFmeRm', $html);
        $this->assertStringContainsString('Join Our WhatsApp Community', $html);
        $this->assertStringContainsString('https://chat.whatsapp.com/example', $html);
        $this->assertStringContainsString('Kayla &amp; The Paradise Dolls Team', $html);
    }

    private function assertContactDetailsArePresent(string $html): void
    {
        $this->assertStringContainsString('admin@getrichwithparadisedolls.com', $html);
        $this->assertStringContainsString('bossdoll@getrichwithparadisedolls.com', $html);
    }
}
