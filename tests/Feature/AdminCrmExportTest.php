<?php

namespace Tests\Feature;

use App\Models\ModelApplication;
use App\Models\ModelProfile;
use App\Models\ModelReferral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class AdminCrmExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_applications_csv_for_crm_import(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $reviewer = User::factory()->create(['role' => 'admin', 'name' => 'Agency Admin']);
        $referrer = User::factory()->create([
            'role' => 'model',
            'name' => 'Referral Boss',
            'email' => 'referrer@example.com',
        ]);

        $application = ModelApplication::create([
            'name' => 'CRM Applicant',
            'email' => 'crm-applicant@example.com',
            'phone' => '+15550100',
            'message' => 'Ready to join.',
            'experience_level' => 'beginner',
            'social_handle' => '@crm',
            'age_confirmed' => true,
            'photo_paths' => ['applications/photos/crm.jpg'],
        ]);
        $application->forceFill([
            'status' => ModelApplication::STATUS_REJECTED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ])->save();

        ModelReferral::create([
            'referrer_id' => $referrer->id,
            'model_application_id' => $application->id,
            'candidate_name' => 'CRM Applicant',
            'candidate_email' => 'crm-applicant@example.com',
            'experience_level' => 'beginner',
            'consent_confirmed' => true,
            'source' => ModelReferral::SOURCE_APPLY_LINK,
            'status' => ModelReferral::STATUS_REJECTED,
            'reward_status' => ModelReferral::REWARD_NOT_ELIGIBLE,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.applications.export'));

        $response
            ->assertOk()
            ->assertDownload('paradise-applications-'.now()->format('Y-m-d').'.csv');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('"Application ID",Status,Name,Email', $csv);
        $this->assertStringContainsString('CRM Applicant', $csv);
        $this->assertStringContainsString('crm-applicant@example.com', $csv);
        $this->assertStringContainsString('Referral Boss', $csv);
        $this->assertStringContainsString('/admin/applications/'.$application->id.'/photos/0/view', $csv);
    }

    public function test_applications_csv_neutralizes_spreadsheet_formulas(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        ModelApplication::create([
            'name' => '=2+2',
            'email' => 'formula-applicant@example.com',
            'phone' => '+15550101',
            'message' => '+SUM(1,1)',
            'experience_level' => 'beginner',
            'social_handle' => '@cmd',
            'age_confirmed' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.applications.export'));

        $csv = $response->streamedContent();

        $this->assertStringContainsString("'=2+2", $csv);
        $this->assertStringContainsString("'+SUM(1,1)", $csv);
        $this->assertStringContainsString("'@cmd", $csv);
    }

    public function test_admin_can_download_designed_onboarding_workbook_for_crm_import(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $reviewer = User::factory()->create(['role' => 'admin', 'name' => 'Verifier Admin']);
        $member = User::factory()->create([
            'role' => 'model',
            'name' => 'CRM Model',
            'email' => 'crm-model@example.com',
        ]);

        $application = ModelApplication::create([
            'name' => 'CRM Model',
            'email' => 'crm-model@example.com',
            'experience_level' => 'experienced',
            'age_confirmed' => true,
        ]);
        $application->forceFill([
            'status' => ModelApplication::STATUS_APPROVED,
            'user_id' => $member->id,
        ])->save();

        ModelProfile::create([
            'user_id' => $member->id,
            'model_application_id' => $application->id,
            'legal_name' => 'Legal CRM',
            'stage_name' => 'Stage CRM',
            'date_of_birth' => now()->subYears(23)->format('Y-m-d'),
            'phone' => '+15550200',
            'country' => 'United States',
            'city' => 'Miami',
            'platforms' => ['Stripchat', 'OnlyFans'],
            'fetishes_checklist' => ['Latex' => 'Yes', 'Feet' => 'Maybe'],
            'equipment' => ['Phone', 'Ring light'],
            'emergency_contact_name' => 'Emergency Person',
            'emergency_contact_phone' => '+15550300',
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_SUBMITTED,
            'verification_submitted_at' => now(),
            'verification_reviewed_by' => $reviewer->id,
            'verification_reviewed_at' => now(),
            'verification_notes' => 'Looks good.',
            'id_document_path' => 'verifications/'.$member->id.'/id.jpg',
            'selfie_with_id_path' => 'verifications/'.$member->id.'/selfie.jpg',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.onboarding.export'));

        $response
            ->assertOk()
            ->assertDownload('paradise-onboarding-all-models-'.now()->format('Y-m-d').'.xlsx');

        $text = $this->xlsxText($response->streamedContent());

        $this->assertStringContainsString('Model Directory', $text);
        $this->assertStringContainsString('Verification Tracker', $text);
        $this->assertStringContainsString('Work Preferences', $text);
        $this->assertStringContainsString('Onboarding Status', $text);
        $this->assertStringContainsString('CRM Model', $text);
        $this->assertStringContainsString('Stripchat; OnlyFans', $text);
        $this->assertStringContainsString('Verifier Admin', $text);
    }

    public function test_admin_can_download_single_designed_onboarding_profile_workbook_for_crm_import(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $selectedMember = User::factory()->create([
            'role' => 'model',
            'name' => 'Selected CRM Model',
            'email' => 'selected-crm@example.com',
        ]);
        $otherMember = User::factory()->create([
            'role' => 'model',
            'name' => 'Other CRM Model',
            'email' => 'other-crm@example.com',
        ]);

        $selectedProfile = ModelProfile::create([
            'user_id' => $selectedMember->id,
            'legal_name' => 'Selected Legal',
            'stage_name' => 'Selected Stage',
            'information_submitted_at' => now(),
        ]);

        ModelProfile::create([
            'user_id' => $otherMember->id,
            'legal_name' => 'Other Legal',
            'information_submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.onboarding.show', $selectedProfile))
            ->assertOk()
            ->assertSee(route('admin.onboarding.export-profile', $selectedProfile), false);

        $response = $this->actingAs($admin)
            ->get(route('admin.onboarding.export-profile', $selectedProfile));

        $response
            ->assertOk()
            ->assertDownload('paradise-onboarding-selected-crm-model-designed.xlsx');

        $text = $this->xlsxText($response->streamedContent());

        $this->assertStringContainsString('Profile Overview', $text);
        $this->assertStringContainsString('Verification', $text);
        $this->assertStringContainsString('Content Checklist', $text);
        $this->assertStringContainsString('Summary Dashboard', $text);
        $this->assertStringContainsString('Selected CRM Model', $text);
        $this->assertStringContainsString('Selected Legal', $text);
        $this->assertStringNotContainsString('Other CRM Model', $text);
        $this->assertStringNotContainsString('Other Legal', $text);
    }

    private function xlsxText(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'pd-xlsx-test-');
        $this->assertNotFalse($path);

        file_put_contents($path, $contents);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path));

        $text = '';

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = $zip->getNameIndex($index);

            if (! str_ends_with($name, '.xml')) {
                continue;
            }

            $entry = $zip->getFromIndex($index);

            if ($entry !== false) {
                $text .= html_entity_decode($entry, ENT_QUOTES | ENT_XML1, 'UTF-8');
            }
        }

        $zip->close();
        @unlink($path);

        return $text;
    }
}
