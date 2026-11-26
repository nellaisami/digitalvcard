<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\SuperAdmin;
use App\Models\User;
use App\Notifications\AdminEmailVerificationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class UserAdministrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_can_be_created_searched_updated_toggled_and_deleted_with_photos(): void
    {
        Storage::fake('public');
        $superadmin = SuperAdmin::factory()->create();
        $this->actingAs($superadmin, 'superadmin')->get('/admin/create')->assertOk()->assertDontSee('name="role"', false);

        $this->post('/admin', [
            'name' => 'Workspace Admin',
            'email' => 'workspace-admin@example.com',
            'password' => 'Strong-pass1!',
            'password_confirmation' => 'Strong-pass1!',
            'contact_no' => '555-0198',
            'status' => 'active',
            'role' => 'superadmin',
            'profile_photo' => $this->png('admin.png'),
        ])->assertRedirect('/admin');

        $admin = User::where('email', 'workspace-admin@example.com')->firstOrFail();
        $this->assertSame('admin', $admin->role);
        $this->assertNotNull($admin->profile_photo);
        Storage::disk('public')->assertExists($admin->profile_photo);
        $oldPhoto = $admin->profile_photo;
        $this->get('/admin?search=workspace-admin')->assertOk()->assertSee('Workspace Admin');
        $this->get('/admin/'.$admin->id.'/edit')->assertOk();
        $this->assertDatabaseMissing('users', ['email' => $superadmin->email]);

        $this->put('/admin/'.$admin->id, [
            'name' => 'Updated Admin',
            'email' => 'updated-admin@example.com',
            'password' => '',
            'password_confirmation' => '',
            'contact_no' => '555-0101',
            'status' => 'active',
            'role' => 'staff',
            'profile_photo' => $this->png('updated-admin.png'),
        ])->assertRedirect('/admin');
        $admin->refresh();
        $this->assertSame('admin', $admin->role);
        $this->assertSame('updated-admin@example.com', $admin->email);
        Storage::disk('public')->assertMissing($oldPhoto);
        Storage::disk('public')->assertExists($admin->profile_photo);

        $this->patchJson('/admin/'.$admin->id.'/status')->assertOk()->assertJson(['status' => 'inactive']);
        $currentPhoto = $admin->profile_photo;
        $this->delete('/admin/'.$admin->id)->assertRedirect('/admin');
        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
        Storage::disk('public')->assertMissing($currentPhoto);
    }

    public function test_admin_creation_requires_a_strong_password_but_contact_is_optional(): void
    {
        $superadmin = SuperAdmin::factory()->create();
        $this->actingAs($superadmin, 'superadmin');

        $this->from('/admin/create')->post('/admin', [
            'name' => 'Invalid Admin',
            'email' => 'invalid-admin@example.com',
            'password' => 'weakpassword',
            'password_confirmation' => 'weakpassword',
            'contact_no' => '555-0198',
            'status' => 'active',
        ])->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'invalid-admin@example.com']);

        $this->post('/admin', [
            'name' => 'Invalid Admin',
            'email' => 'invalid-admin@example.com',
            'password' => 'Strong-pass1!',
            'password_confirmation' => 'Strong-pass1!',
            'status' => 'active',
        ])->assertRedirect('/admin');
        $this->assertDatabaseHas('users', ['email' => 'invalid-admin@example.com', 'contact_no' => null]);
    }

    public function test_admin_creation_sends_a_signed_email_verification_link(): void
    {
        Notification::fake();
        $superadmin = SuperAdmin::factory()->create();
        $this->actingAs($superadmin, 'superadmin')->post('/admin', [
            'name' => 'Email Admin',
            'email' => 'email-admin@example.com',
            'password' => 'Strong-pass1!',
            'password_confirmation' => 'Strong-pass1!',
            'contact_no' => '555-0198',
            'status' => 'active',
        ])->assertRedirect('/admin');

        $admin = User::where('email', 'email-admin@example.com')->firstOrFail();
        Notification::assertSentTo($admin, AdminEmailVerificationNotification::class);
        $this->assertNull($admin->email_verified_at);

        $invalidUrl = URL::temporarySignedRoute('admin.email.verify', now()->addMinutes(10), [
            'user' => $admin->id,
            'hash' => sha1('different@example.com'),
        ]);
        $this->get($invalidUrl)->assertForbidden();

        $verificationUrl = URL::temporarySignedRoute('admin.email.verify', now()->addMinutes(10), [
            'user' => $admin->id,
            'hash' => sha1($admin->email),
        ]);
        $this->get($verificationUrl)
            ->assertRedirect('/login')
            ->assertSessionHas('status', 'Email address verified. You can now sign in.');
        $this->assertNotNull($admin->fresh()->email_verified_at);
    }

    public function test_user_creation_role_plan_photo_and_verification_are_superadmin_controlled(): void
    {
        Storage::fake('public');
        $superadmin = SuperAdmin::factory()->create();
        $starter = Plan::create(['name' => 'Starter', 'price' => 0, 'status' => 'active']);
        $growth = Plan::create(['name' => 'Growth', 'price' => 20, 'status' => 'active']);
        $this->actingAs($superadmin, 'superadmin')->get('/users/create')->assertOk()->assertDontSee('name="role"', false);

        $this->post('/users', [
            'name' => 'Plan Member',
            'email' => 'plan-member@example.com',
            'password' => 'strong-password',
            'password_confirmation' => 'strong-password',
            'status' => 'active',
            'role' => 'superadmin',
            'plan_id' => $starter->id,
            'profile_photo' => $this->png('member.png'),
        ])->assertRedirect('/users');

        $user = User::where('email', 'plan-member@example.com')->firstOrFail();
        $this->assertSame('staff', $user->role);
        $this->assertSame($starter->id, $user->plan_id);
        $oldPhoto = $user->profile_photo;
        Storage::disk('public')->assertExists($oldPhoto);
        $this->get('/users?search=plan-member&role=staff&plan_id='.$starter->id.'&verification=unverified&status=active')
            ->assertOk()
            ->assertSee('Plan Member')
            ->assertSee('Starter');

        $inactive = Plan::create(['name' => 'Archived', 'price' => 5, 'status' => 'inactive']);
        $this->post('/users', [
            'name' => 'Invalid Plan Member',
            'email' => 'invalid-plan@example.com',
            'password' => 'strong-password',
            'password_confirmation' => 'strong-password',
            'status' => 'active',
            'plan_id' => $inactive->id,
        ])->assertSessionHasErrors('plan_id');
        $this->assertDatabaseMissing('users', ['email' => 'invalid-plan@example.com']);

        $this->patchJson('/users/'.$user->id.'/verification')->assertOk()->assertJson(['verified' => true]);
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->patchJson('/users/'.$user->id.'/verification')->assertOk()->assertJson(['verified' => false]);
        $this->assertNull($user->fresh()->email_verified_at);
        $this->patchJson('/users/'.$user->id.'/status')->assertOk()->assertJson(['status' => 'inactive']);

        $this->put('/users/'.$user->id, [
            'name' => 'Plan Member Updated',
            'email' => 'plan-member-updated@example.com',
            'password' => '',
            'password_confirmation' => '',
            'status' => 'inactive',
            'role' => 'superadmin',
            'plan_id' => $growth->id,
            'profile_photo' => $this->png('member-updated.png'),
        ])->assertRedirect('/users');
        $user->refresh();
        $this->assertSame('staff', $user->role);
        $this->assertSame($growth->id, $user->plan_id);
        $this->assertNull($user->email_verified_at);
        Storage::disk('public')->assertMissing($oldPhoto);
        Storage::disk('public')->assertExists($user->profile_photo);

        $currentPhoto = $user->profile_photo;
        $this->put('/users/'.$user->id, [
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
            'status' => $user->status,
            'plan_id' => $user->plan_id,
            'remove_photo' => '1',
        ])->assertRedirect('/users');
        $this->assertNull($user->fresh()->profile_photo);
        Storage::disk('public')->assertMissing($currentPhoto);

        $growth->delete();
        $this->assertNull($user->fresh()->plan_id);
    }

    public function test_role_changes_are_separate_and_restricted_to_superadmins(): void
    {
        $superadmin = SuperAdmin::factory()->create();
        $user = User::factory()->create(['role' => 'staff']);

        $this->actingAs($superadmin, 'superadmin')->get('/users/'.$user->id.'/role')->assertOk();
        $this->put('/users/'.$user->id, [
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
            'status' => $user->status,
            'role' => 'admin',
        ])->assertRedirect('/users');
        $this->assertSame('staff', $user->fresh()->role);
        $this->patch('/users/'.$user->id.'/role', ['role' => 'manager'])->assertRedirect('/users');
        $this->assertSame('manager', $user->fresh()->role);

        $this->patch('/users/'.$user->id.'/role', ['role' => 'superadmin'])->assertSessionHasErrors('role');
        $this->assertDatabaseHas('super_admins', ['id' => $superadmin->id, 'email' => $superadmin->email]);
        $this->assertDatabaseMissing('users', ['email' => $superadmin->email]);

        $this->actingAs($user->fresh())->patchJson('/users/'.$user->id.'/verification')->assertForbidden();
        $this->patchJson('/users/'.$user->id.'/role', ['role' => 'staff'])->assertForbidden();
        $this->post('/users', [])->assertForbidden();
        $this->post('/admin', [])->assertForbidden();
    }

    public function test_superadmin_can_update_and_remove_their_own_profile_photo(): void
    {
        Storage::fake('public');
        $superadmin = SuperAdmin::factory()->create(['email' => 'profile@example.com']);
        $this->actingAs($superadmin, 'superadmin');
        $this->get('/account')->assertOk();

        $this->from('/account')->put('/account', [
            'name' => $superadmin->name,
            'email' => $superadmin->email,
            'contact_no' => '555-0123',
            'profile_photo' => $this->png('profile.png'),
        ])->assertRedirect('/account');
        $firstPhoto = $superadmin->fresh()->profile_photo;
        Storage::disk('public')->assertExists($firstPhoto);

        $this->from('/account')->put('/account', [
            'name' => $superadmin->name,
            'email' => $superadmin->email,
            'profile_photo' => $this->png('profile-updated.png'),
        ])->assertRedirect('/account');
        $photo = $superadmin->fresh()->profile_photo;
        Storage::disk('public')->assertMissing($firstPhoto);
        Storage::disk('public')->assertExists($photo);

        $this->from('/account')->put('/account', [
            'name' => $superadmin->name,
            'email' => $superadmin->email,
            'remove_photo' => '1',
        ])->assertRedirect('/account');
        $this->assertNull($superadmin->fresh()->profile_photo);
        Storage::disk('public')->assertMissing($photo);
    }

    public function test_profile_photo_urls_keep_the_application_subdirectory(): void
    {
        $user = User::factory()->create([
            'profile_photo' => 'profile-photos/avatar.jpg',
        ]);

        URL::forceRootUrl('http://localhost/digitalvc/public');

        try {
            $this->assertSame(
                'http://localhost/digitalvc/public/storage/profile-photos/avatar.jpg',
                $user->profile_photo_url,
            );
        } finally {
            URL::forceRootUrl(null);
        }
    }

    public function test_user_listing_is_paginated_and_invalid_photos_are_rejected(): void
    {
        Storage::fake('public');
        $superadmin = SuperAdmin::factory()->create();
        User::factory(11)->create();
        $this->actingAs($superadmin, 'superadmin')->get('/users?page=2')
            ->assertOk()
            ->assertSee('Showing 11–11 of 11 users');

        $this->from('/users/create')->post('/users', [
            'name' => 'Invalid Photo User',
            'email' => 'invalid-photo@example.com',
            'password' => 'strong-password',
            'password_confirmation' => 'strong-password',
            'status' => 'active',
            'profile_photo' => UploadedFile::fake()->createWithContent('too-large.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/rWQAAAAASUVORK5CYII=', true))->mimeType('image/png')->size(2049),
        ])->assertSessionHasErrors('profile_photo');
        $this->assertDatabaseMissing('users', ['email' => 'invalid-photo@example.com']);
    }

    private function png(string $name): UploadedFile
    {
        $content = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/rWQAAAAASUVORK5CYII=', true);

        return UploadedFile::fake()->createWithContent($name, $content)->mimeType('image/png');
    }
}
