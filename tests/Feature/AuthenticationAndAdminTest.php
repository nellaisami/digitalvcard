<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use App\Notifications\LoginOtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationAndAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_signs_in_a_staff_user_and_opens_dashboard(): void
    {
        $response = $this->post('/register', [
            'name' => 'New Member',
            'email' => 'new@example.com',
            'password' => 'strong-password',
            'password_confirmation' => 'strong-password',
            'role' => 'superadmin',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'new@example.com', 'role' => 'staff', 'status' => 'active']);
        $this->assertTrue(Hash::check('strong-password', User::where('email', 'new@example.com')->first()->password));
        $this->get('/dashboard')->assertOk()->assertSee('Dashboard overview');
    }

    public function test_password_login_requires_a_valid_email_otp_before_dashboard_access(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'member@example.com', 'password' => 'password123']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password123', 'remember' => '1'])
            ->assertRedirect('/login/verify');
        $this->assertGuest();
        $this->assertDatabaseHas('login_otps', ['user_id' => $user->id]);
        Notification::assertSentOnDemand(LoginOtpNotification::class);
        $notification = Notification::sent(new AnonymousNotifiable, LoginOtpNotification::class)->first();

        $this->post('/login/verify', ['code' => '000000'])->assertSessionHasErrors('code');
        $this->assertGuest();

        $this->post('/login/verify', ['code' => $notification->code])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseMissing('login_otps', ['user_id' => $user->id]);
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->get('/dashboard')->assertOk();
    }

    public function test_only_superadmins_can_access_user_management_and_manage_users(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $this->actingAs($staff)->get('/users')->assertForbidden();
        $this->get('/admin')->assertForbidden();

        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($superadmin)->get('/users')->assertOk();
        $this->post('/users', [
            'name' => 'Managed User',
            'email' => 'managed@example.com',
            'password' => 'strong-password',
            'password_confirmation' => 'strong-password',
            'role' => 'superadmin',
            'status' => 'inactive',
        ])->assertRedirect('/users');
        $this->assertDatabaseHas('users', ['email' => 'managed@example.com', 'role' => 'staff', 'status' => 'inactive']);
    }

    public function test_superadmin_can_search_update_and_delete_a_user(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $user = User::factory()->create(['name' => 'Find This User', 'email' => 'find@example.com']);
        $this->actingAs($superadmin);

        $this->get('/users?search=find%40example.com')->assertOk()->assertSee('Find This User');
        $this->put('/users/'.$user->id, [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'password' => '',
            'password_confirmation' => '',
            'role' => 'superadmin',
            'status' => 'inactive',
        ])->assertRedirect('/users');
        $this->get('/users/'.$user->id)->assertOk()->assertSee('Updated User');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => 'updated@example.com', 'role' => 'staff', 'status' => 'inactive']);

        $this->delete('/users/'.$user->id)->assertRedirect('/users');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_logout_invalidates_an_authenticated_session(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_account_settings_update_profile_and_password(): void
    {
        $user = User::factory()->create(['password' => 'password123']);
        $this->actingAs($user)->get('/account')->assertOk()->assertSee('Change password');

        $this->put('/account', ['name' => 'Updated Profile', 'email' => $user->email, 'city' => 'Portland'])
            ->assertRedirect('/account');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Profile', 'city' => 'Portland']);

        $this->put('/account/password', [
            'current_password' => 'password123',
            'password' => 'another-strong-password',
            'password_confirmation' => 'another-strong-password',
        ])->assertRedirect('/account');
        $this->assertTrue(Hash::check('another-strong-password', $user->fresh()->password));
    }

    public function test_organization_list_uses_the_migrated_pivot_table(): void
    {
        $user = User::factory()->create();
        $organization = Organization::create(['name' => 'Northwind Studio', 'status' => 'active']);
        $organization->users()->attach($user, ['status' => 'active']);

        $this->assertSame(1, $organization->users()->count());
        $this->actingAs($user)->get('/organizations')
            ->assertOk()
            ->assertSee('Northwind Studio');
    }
}
