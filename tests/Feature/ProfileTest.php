<?php

namespace Tests\Feature;

use App\Models\ModelApplication;
use App\Models\ModelProfile;
use App\Models\ModelReferral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_model_profile_email_update_syncs_linked_application_email(): void
    {
        $member = User::factory()->create([
            'name' => 'Model Member',
            'email' => 'old-model@example.com',
            'role' => 'model',
            'email_verified_at' => now(),
        ]);
        $application = new ModelApplication([
            'name' => $member->name,
            'email' => $member->email,
            'experience_level' => 'beginner',
            'age_confirmed' => true,
        ]);
        $application->forceFill([
            'status' => ModelApplication::STATUS_APPROVED,
            'user_id' => $member->id,
        ])->save();
        $referrer = User::factory()->create(['role' => 'model']);
        $referral = ModelReferral::create([
            'referrer_id' => $referrer->id,
            'model_application_id' => $application->id,
            'candidate_name' => $member->name,
            'candidate_email' => $member->email,
            'experience_level' => 'beginner',
            'consent_confirmed' => true,
            'source' => ModelReferral::SOURCE_APPLY_LINK,
            'status' => ModelReferral::STATUS_JOINED,
            'reward_status' => ModelReferral::REWARD_ELIGIBLE,
        ]);
        ModelProfile::create([
            'user_id' => $member->id,
            'model_application_id' => $application->id,
        ]);

        $this
            ->actingAs($member)
            ->patch('/profile', [
                'name' => 'Model Member',
                'email' => 'updated-model@example.com',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertSame('updated-model@example.com', $member->fresh()->email);
        $this->assertSame('updated-model@example.com', $application->fresh()->email);
        $this->assertSame('updated-model@example.com', $referral->fresh()->candidate_email);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_profile_photo_can_be_uploaded_and_removed(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'profile_photo' => $this->fakePngUpload(),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $photoPath = $user->refresh()->profile_photo_path;

        $this->assertNotNull($photoPath);
        Storage::disk('public')->assertExists($photoPath);

        $photoUrl = $user->profilePhotoUrl();
        $this->assertNotNull($photoUrl);
        $this->assertStringContainsString('/profile-photos/'.$user->id, $photoUrl);
        $this->assertStringNotContainsString('/storage/', $photoUrl);

        $photoResponse = $this->get($photoUrl)
            ->assertOk()
            ->assertHeader('x-content-type-options', 'nosniff');

        $cacheControl = (string) $photoResponse->headers->get('cache-control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=31536000', $cacheControl);
        $this->assertStringContainsString('immutable', $cacheControl);

        $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'remove_profile_photo' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNull($user->refresh()->profile_photo_path);
        Storage::disk('public')->assertMissing($photoPath);
    }

    public function test_profile_photo_can_be_uploaded_and_removed_from_dedicated_photo_form(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->post(route('profile.photo.update'), [
                'profile_photo' => $this->fakePngUpload(),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $photoPath = $user->refresh()->profile_photo_path;

        $this->assertNotNull($photoPath);
        Storage::disk('public')->assertExists($photoPath);

        $this
            ->actingAs($user)
            ->post(route('profile.photo.update'), [
                'remove_profile_photo' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNull($user->refresh()->profile_photo_path);
        Storage::disk('public')->assertMissing($photoPath);
    }

    public function test_profile_photo_form_rejects_non_images(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->post(route('profile.photo.update'), [
                'profile_photo' => UploadedFile::fake()->create('avatar.txt', 1, 'text/plain'),
            ])
            ->assertSessionHasErrors('profile_photo')
            ->assertRedirect();

        $this->assertNull($user->refresh()->profile_photo_path);
        Storage::disk('public')->assertMissing('profile-photos/avatar.txt');
    }

    public function test_profile_photo_can_be_uploaded_when_existing_email_has_uppercase_letters(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'email' => 'Admin@getrichwithparadisedolls.com',
            'email_verified_at' => now(),
        ]);

        $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'profile_photo' => $this->fakePngUpload(),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('admin@getrichwithparadisedolls.com', $user->email);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNotNull($user->profile_photo_path);
        Storage::disk('public')->assertExists($user->profile_photo_path);
    }

    private function fakePngUpload(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'avatar');
        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='
        ));

        return new UploadedFile($path, 'avatar.png', 'image/png', null, true);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
