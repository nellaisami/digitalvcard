<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Plan;
use App\Models\SuperAdmin;
use App\Models\User;
use App\Models\Vcard;
use App\Notifications\OrganizationMemberEmailVerificationNotification;
use App\Notifications\OrganizationOwnerEmailVerificationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class OrganizationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_create_organization_owner_and_assign_plan(): void
    {
        Notification::fake();
        $superadmin = SuperAdmin::factory()->create();
        $plan = Plan::create(['name' => 'Growth', 'price' => 25, 'status' => 'active']);

        $this->actingAs($superadmin, 'superadmin')->post('/organizations', $this->organizationData([
            'plan_id' => $plan->id,
        ]))->assertRedirect('/organizations');

        $organization = Organization::where('email', 'contact@northwind.example')->firstOrFail();
        $owner = $organization->owner;
        $this->assertSame('Jordan Lee', $owner->name);
        $this->assertSame('staff', $owner->role);
        $this->assertSame($plan->id, $organization->plan_id);
        $this->assertTrue(Hash::check('owner-password', $owner->password));
        $this->assertNull($owner->email_verified_at);
        $this->assertDatabaseHas('organization_users', [
            'organization_id' => $organization->id,
            'user_id' => $owner->id,
            'role' => 'owner',
            'status' => 'active',
        ]);
        Notification::assertSentTo($owner, OrganizationOwnerEmailVerificationNotification::class);

        $verificationUrl = URL::temporarySignedRoute('organizations.owner.verify', now()->addMinutes(10), [
            'organization' => $organization->id,
            'hash' => sha1($owner->email),
        ]);
        $this->get($verificationUrl)->assertRedirect('/login');
        $this->assertNotNull($owner->fresh()->email_verified_at);
        $this->get('/organizations/'.$organization->id)->assertOk()->assertSee('Northwind Studio');
    }

    public function test_superadmin_can_update_owner_and_reset_password_separately(): void
    {
        Notification::fake();
        $superadmin = SuperAdmin::factory()->create();
        $firstPlan = Plan::create(['name' => 'Starter', 'price' => 10, 'status' => 'active']);
        $secondPlan = Plan::create(['name' => 'Enterprise', 'price' => 100, 'status' => 'active']);
        $organization = $this->makeOrganization($firstPlan);
        $owner = $organization->owner;
        $oldPassword = $owner->password;
        $owner->forceFill(['email_verified_at' => now()])->save();

        $this->actingAs($superadmin, 'superadmin')->put('/organizations/'.$organization->id, $this->organizationData([
            'name' => 'Northwind Group',
            'email' => 'hello@northwind.example',
            'owner_first_name' => 'Taylor',
            'owner_last_name' => 'Morgan',
            'owner_email' => 'taylor@northwind.example',
            'contact_no' => '+1 555 123 4567',
            'password' => '',
            'password_confirmation' => '',
            'plan_id' => $secondPlan->id,
        ]))->assertRedirect('/organizations/'.$organization->id);

        $organization->refresh();
        $owner->refresh();
        $this->assertSame('Northwind Group', $organization->name);
        $this->assertSame($secondPlan->id, $organization->plan_id);
        $this->assertSame('Taylor Morgan', $owner->name);
        $this->assertNull($owner->email_verified_at);
        $this->assertSame($oldPassword, $owner->password);
        Notification::assertSentTo($owner, OrganizationOwnerEmailVerificationNotification::class);

        $this->patch('/organizations/'.$organization->id.'/owner/password', [
            'password' => 'new-owner-password',
            'password_confirmation' => 'new-owner-password',
        ])->assertRedirect('/organizations/'.$organization->id);
        $this->assertTrue(Hash::check('new-owner-password', $owner->fresh()->password));
    }

    public function test_owner_can_create_and_verify_a_new_organization_user(): void
    {
        Notification::fake();
        $organization = $this->makeOrganization(null);
        $owner = $organization->owner;
        $owner->forceFill(['email_verified_at' => now()])->save();
        $this->actingAs($owner)
            ->get('/organizations/'.$organization->id.'/users/create')
            ->assertOk()
            ->assertSee('Create a new account')
            ->assertSee('Add an existing account');

        $this->post('/organizations/'.$organization->id.'/users', [
            'account_type' => 'create',
            'name' => 'Casey Member',
            'email' => 'casey@example.test',
            'contact_no' => '+1 555 222 3333',
            'password' => 'member-password',
            'password_confirmation' => 'member-password',
            'organization_role' => 'manager',
            'status' => 'active',
        ])->assertRedirect('/organizations/'.$organization->id);

        $member = User::where('email', 'casey@example.test')->firstOrFail();
        $this->assertSame('staff', $member->role);
        $this->assertNull($member->email_verified_at);
        $this->assertDatabaseHas('organization_users', [
            'organization_id' => $organization->id,
            'user_id' => $member->id,
            'role' => 'manager',
            'status' => 'active',
        ]);
        Notification::assertSentTo($member, OrganizationMemberEmailVerificationNotification::class);

        $verificationUrl = URL::temporarySignedRoute('organizations.member.verify', now()->addMinutes(10), [
            'organization' => $organization->id,
            'member' => $member->id,
            'hash' => sha1($member->email),
        ]);
        $this->get($verificationUrl)->assertRedirect('/login');
        $this->assertNotNull($member->fresh()->email_verified_at);
    }

    public function test_owner_can_attach_update_toggle_and_remove_existing_user_without_deleting_account_or_vcard(): void
    {
        $organization = $this->makeOrganization(null);
        $owner = $organization->owner;
        $owner->forceFill(['email_verified_at' => now()])->save();
        $member = User::factory()->create(['email' => 'existing@example.test']);
        $vcard = Vcard::create([
            'title' => 'Member card',
            'user_id' => $member->id,
            'organization_id' => $organization->id,
            'status' => 'active',
        ]);

        $this->actingAs($owner)->post('/organizations/'.$organization->id.'/users', [
            'account_type' => 'existing',
            'email' => $member->email,
            'organization_role' => 'member',
            'status' => 'active',
        ])->assertRedirect('/organizations/'.$organization->id);
        $this->assertDatabaseHas('organization_users', [
            'organization_id' => $organization->id,
            'user_id' => $member->id,
            'role' => 'member',
            'status' => 'active',
        ]);

        $this->patch('/organizations/'.$organization->id.'/users/'.$member->id, [
            'organization_role' => 'manager',
            'status' => 'active',
        ])->assertRedirect('/organizations/'.$organization->id);
        $this->assertDatabaseHas('organization_users', [
            'organization_id' => $organization->id,
            'user_id' => $member->id,
            'role' => 'manager',
        ]);

        $this->patchJson('/organizations/'.$organization->id.'/users/'.$member->id.'/status')
            ->assertOk()
            ->assertJson(['status' => 'inactive']);
        $this->delete('/organizations/'.$organization->id.'/users/'.$member->id)
            ->assertRedirect('/organizations/'.$organization->id);
        $this->assertDatabaseMissing('organization_users', [
            'organization_id' => $organization->id,
            'user_id' => $member->id,
        ]);
        $this->assertDatabaseHas('users', ['id' => $member->id]);
        $this->assertDatabaseHas('vcards', ['id' => $vcard->id]);
    }

    public function test_organization_user_management_is_scoped_and_cannot_change_owner_or_global_roles(): void
    {
        $organization = $this->makeOrganization(null);
        $otherOrganization = $this->makeOrganization(null, [
            'name' => 'Other Studio',
            'email' => 'other@example.test',
            'owner_email' => 'other-owner@example.test',
        ]);
        $owner = $organization->owner;
        $owner->forceFill(['email_verified_at' => now()])->save();
        $otherMember = User::factory()->create();
        $otherOrganization->users()->attach($otherMember, ['role' => 'member', 'status' => 'active']);
        $regularMember = User::factory()->create();
        $organization->users()->attach($regularMember, ['role' => 'member', 'status' => 'active']);
        $manager = User::factory()->create();
        $organization->users()->attach($manager, ['role' => 'manager', 'status' => 'active']);

        $this->actingAs($owner)
            ->get('/organizations/'.$organization->id.'/users/'.$owner->id.'/edit')
            ->assertForbidden();
        $this->patch('/organizations/'.$otherOrganization->id.'/users/'.$otherMember->id, [
            'organization_role' => 'manager',
            'status' => 'inactive',
        ])->assertForbidden();
        $this->actingAs($regularMember)
            ->get('/organizations/'.$organization->id.'/users/create')
            ->assertForbidden();

        $this->actingAs($owner)->patch('/organizations/'.$organization->id.'/users/'.$regularMember->id, [
            'organization_role' => 'superadmin',
            'status' => 'active',
        ])->assertSessionHasErrors('organization_role');
        $this->assertSame('staff', $regularMember->fresh()->role);

        $this->actingAs($regularMember)
            ->get('/organizations/'.$organization->id)
            ->assertForbidden();
        $this->actingAs($manager)
            ->get('/organizations/my-organization')
            ->assertOk()
            ->assertSee($organization->name);
        $this->get('/organizations/'.$organization->id.'/edit')->assertForbidden();
        $this->patch('/organizations/'.$organization->id.'/users/'.$regularMember->id, [
            'organization_role' => 'member',
            'status' => 'active',
        ])->assertRedirect('/organizations/'.$organization->id);
    }

    public function test_listing_filters_status_and_deletion_retain_users_and_vcards(): void
    {
        $superadmin = SuperAdmin::factory()->create();
        $plan = Plan::create(['name' => 'Growth', 'price' => 25, 'status' => 'active']);
        $organization = $this->makeOrganization($plan, ['name' => 'Northwind Studio']);
        $organization->owner->forceFill(['email_verified_at' => now()])->save();
        $other = $this->makeOrganization(null, [
            'name' => 'Contoso Works',
            'email' => 'contoso@example.test',
            'owner_email' => 'contoso-owner@example.test',
            'status' => 'inactive',
        ]);
        Vcard::create(['title' => 'Northwind card', 'user_id' => $organization->owner_user_id, 'status' => 'active']);

        $this->actingAs($superadmin, 'superadmin')
            ->get('/organizations?search=Northwind&plan_id='.$plan->id.'&verification=verified&status=active')
            ->assertOk()
            ->assertSee('Northwind Studio')
            ->assertDontSee('Contoso Works');
        $this->get('/organizations?search=Contoso&plan_id=none&verification=unverified&status=inactive')
            ->assertOk()
            ->assertSee('Contoso Works')
            ->assertDontSee('Northwind Studio');

        $ownerId = $organization->owner_user_id;
        $this->delete('/organizations/'.$organization->id)->assertRedirect('/organizations');
        $this->assertDatabaseMissing('organizations', ['id' => $organization->id]);
        $this->assertDatabaseHas('users', ['id' => $ownerId]);
        $this->assertDatabaseHas('vcards', ['user_id' => $ownerId, 'title' => 'Northwind card']);
        $this->assertDatabaseMissing('organization_users', ['organization_id' => $organization->id]);
        $this->assertDatabaseHas('organizations', ['id' => $other->id]);
    }

    public function test_owner_can_only_view_their_active_organization_and_cannot_promote_themselves(): void
    {
        $superadmin = SuperAdmin::factory()->create();
        $organization = $this->makeOrganization(null);
        $otherOrganization = $this->makeOrganization(null, [
            'name' => 'Other Studio',
            'email' => 'other@example.test',
            'owner_email' => 'other-owner@example.test',
        ]);
        $owner = $organization->owner;
        $owner->forceFill(['email_verified_at' => now()])->save();
        $member = User::factory()->create(['email_verified_at' => now()]);
        $organization->users()->attach($member, ['role' => 'member', 'status' => 'active']);
        Vcard::create([
            'title' => 'Private organization card',
            'user_id' => $owner->id,
            'organization_id' => $organization->id,
            'status' => 'active',
        ]);

        $this->actingAs($owner)
            ->get('/organizations/my-organization')
            ->assertOk()
            ->assertSee('Northwind Studio');
        $this->get('/organizations/'.$otherOrganization->id)->assertForbidden();
        $this->actingAs($member)->get('/organizations/'.$organization->id)->assertForbidden();
        $this->actingAs($owner)->get('/organizations/'.$organization->id.'/edit')->assertForbidden();
        $this->post('/organizations', [])->assertForbidden();
        $this->get('/vcards')->assertOk()->assertSee('Private organization card');

        $this->actingAs($superadmin, 'superadmin')
            ->patch('/users/'.$owner->id.'/role', ['role' => 'admin'])
            ->assertSessionHasErrors('role');
        $this->patchJson('/organizations/'.$organization->id.'/status')
            ->assertOk()
            ->assertJson(['status' => 'inactive']);
        Auth::guard('superadmin')->logout();
        Auth::guard('web')->logout();
        $this->actingAs($owner->fresh(), 'web')->get('/organizations/my-organization')->assertForbidden();
        $this->get('/vcards')->assertOk()->assertDontSee('Private organization card');
        $this->assertDatabaseHas('vcards', ['title' => 'Private organization card']);
    }

    public function test_owner_dashboard_is_scoped_to_their_organization(): void
    {
        $organization = $this->makeOrganization(null);
        $otherOrganization = $this->makeOrganization(null, [
            'name' => 'Other Studio',
            'email' => 'other@example.test',
            'owner_email' => 'other-owner@example.test',
        ]);
        $owner = $organization->owner;
        $owner->forceFill(['email_verified_at' => now()])->save();
        Vcard::create([
            'title' => 'Northwind card',
            'user_id' => $owner->id,
            'organization_id' => $organization->id,
            'status' => 'active',
        ]);
        Vcard::create([
            'title' => 'Other organization card',
            'user_id' => $otherOrganization->owner_user_id,
            'organization_id' => $otherOrganization->id,
            'status' => 'active',
        ]);

        $this->actingAs($owner)->get('/dashboard')
            ->assertOk()
            ->assertViewHas('recentUsers', fn ($users) => $users->modelKeys() === [$owner->id])
            ->assertViewHas('stats', function (array $stats): bool {
                return collect($stats)->firstWhere('label', 'Active users')['value'] === 1
                    && collect($stats)->firstWhere('label', 'Active vCards')['value'] === 1;
            });
    }

    public function test_user_management_email_changes_resend_verification_for_organization_owners(): void
    {
        Notification::fake();
        $superadmin = SuperAdmin::factory()->create();
        $organization = $this->makeOrganization(null);
        $owner = $organization->owner;
        $owner->forceFill(['email_verified_at' => now()])->save();

        $this->actingAs($superadmin, 'superadmin')->put('/users/'.$owner->id, [
            'name' => $owner->name,
            'email' => 'new-owner@example.test',
            'password' => '',
            'password_confirmation' => '',
            'status' => 'active',
            'plan_id' => '',
        ])->assertRedirect('/users');

        $this->assertNull($owner->fresh()->email_verified_at);
        Notification::assertSentTo($owner, OrganizationOwnerEmailVerificationNotification::class);
        $this->delete('/users/'.$owner->id)->assertSessionHasErrors('user');
        $this->assertDatabaseHas('users', ['id' => $owner->id]);
    }

    public function test_superadmin_can_toggle_organization_status(): void
    {
        $superadmin = SuperAdmin::factory()->create();
        $organization = $this->makeOrganization(null);

        $this->actingAs($superadmin, 'superadmin')
            ->patchJson('/organizations/'.$organization->id.'/status')
            ->assertOk()
            ->assertJson(['status' => 'inactive']);
        $this->assertSame('inactive', $organization->fresh()->status);
    }

    public function test_organization_listing_is_paginated(): void
    {
        $superadmin = SuperAdmin::factory()->create();
        for ($index = 1; $index <= 11; $index++) {
            $this->makeOrganization(null, [
                'name' => 'Organization '.$index,
                'email' => "org{$index}@example.test",
                'owner_email' => "owner{$index}@example.test",
            ]);
        }

        $this->actingAs($superadmin, 'superadmin')
            ->get('/organizations?page=2')
            ->assertOk()
            ->assertSee('Showing 11–11 of 11 organizations');
    }

    public function test_superadmin_can_resend_owner_verification_and_cannot_delete_assigned_plan(): void
    {
        Notification::fake();
        $superadmin = SuperAdmin::factory()->create();
        $plan = Plan::create(['name' => 'Assigned', 'price' => 20, 'status' => 'active']);
        $organization = $this->makeOrganization($plan);

        $this->actingAs($superadmin, 'superadmin')
            ->post('/organizations/'.$organization->id.'/verification/resend')
            ->assertRedirect();
        Notification::assertSentTo($organization->owner, OrganizationOwnerEmailVerificationNotification::class);

        $this->delete('/plans/'.$plan->id)->assertRedirect();
        $this->assertDatabaseHas('plans', ['id' => $plan->id]);
    }

    public function test_inactive_plans_cannot_be_assigned_to_new_organizations(): void
    {
        $superadmin = SuperAdmin::factory()->create();
        $plan = Plan::create(['name' => 'Archived', 'price' => 5, 'status' => 'inactive']);

        $this->actingAs($superadmin, 'superadmin')
            ->from('/organizations/create')
            ->post('/organizations', $this->organizationData(['plan_id' => $plan->id]))
            ->assertSessionHasErrors('plan_id');

        $this->assertDatabaseMissing('organizations', ['email' => 'contact@northwind.example']);
        $this->assertDatabaseMissing('users', ['email' => 'jordan@northwind.example']);
    }

    private function organizationData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Northwind Studio',
            'email' => 'contact@northwind.example',
            'owner_first_name' => 'Jordan',
            'owner_last_name' => 'Lee',
            'owner_email' => 'jordan@northwind.example',
            'contact_no' => '+1 555 000 1234',
            'password' => 'owner-password',
            'password_confirmation' => 'owner-password',
            'plan_id' => '',
            'status' => 'active',
        ], $overrides);
    }

    private function makeOrganization(?Plan $plan, array $overrides = []): Organization
    {
        $owner = User::factory()->create([
            'name' => 'Jordan Lee',
            'email' => $overrides['owner_email'] ?? 'jordan@northwind.example',
            'role' => 'staff',
            'email_verified_at' => null,
        ]);
        $organization = Organization::create([
            'name' => $overrides['name'] ?? 'Northwind Studio',
            'email' => $overrides['email'] ?? 'contact@northwind.example',
            'owner_user_id' => $owner->id,
            'plan_id' => $plan?->id,
            'status' => $overrides['status'] ?? 'active',
        ]);
        $organization->users()->attach($owner, ['role' => 'owner', 'status' => 'active']);

        return $organization;
    }
}
