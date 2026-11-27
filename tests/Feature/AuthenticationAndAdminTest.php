<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\SuperAdmin;
use App\Models\User;
use App\Notifications\LoginOtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthenticationAndAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_requires_email_otp_before_dashboard_access(): void
    {
        Notification::fake();
        $response = $this->post('/register', [
            'name' => 'New Member',
            'email' => 'new@example.com',
            'password' => 'strong-password',
            'password_confirmation' => 'strong-password',
            'role' => 'superadmin',
        ]);

        $response->assertRedirect('/login/verify');
        $this->assertGuest();
        $this->assertDatabaseHas('users', ['email' => 'new@example.com', 'role' => 'staff', 'status' => 'active']);
        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertNull($user->email_verified_at);
        $this->assertTrue(Hash::check('strong-password', $user->password));
        Notification::assertSentOnDemand(LoginOtpNotification::class);
        $notification = Notification::sent(new AnonymousNotifiable, LoginOtpNotification::class)->first();

        $this->post('/login/verify', ['code' => $notification->code])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->email_verified_at);
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

        $superadmin = SuperAdmin::factory()->create();
        $this->actingAs($superadmin, 'superadmin')->get('/users')
            ->assertOk()
            ->assertDontSee($superadmin->email);
        $this->assertDatabaseMissing('users', ['email' => $superadmin->email]);
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
        $superadmin = SuperAdmin::factory()->create();
        $user = User::factory()->create(['name' => 'Find This User', 'email' => 'find@example.com']);
        $this->actingAs($superadmin, 'superadmin');

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
        $user = User::factory()->create(['password' => 'password123', 'address' => 'Original address']);
        $this->actingAs($user)->get('/account')
            ->assertOk()
            ->assertSee('Change password')
            ->assertSee('data-client-validation="account"', false)
            ->assertSee('name="username"', false)
            ->assertDontSee('name="address"', false)
            ->assertDontSee('name="city"', false)
            ->assertDontSee('name="country"', false)
            ->assertDontSee('name="postal_code"', false);

        $this->put('/account', [
            'name' => 'Updated Profile',
            'username' => 'updated_profile',
            'email' => $user->email,
            'city' => 'Should not update',
        ])
            ->assertRedirect('/account');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Profile',
            'username' => 'updated_profile',
            'address' => 'Original address',
            'city' => null,
        ]);

        $this->put('/account/password', [
            'current_password' => 'password123',
            'password' => 'Another-strong1!',
            'password_confirmation' => 'Another-strong1!',
        ])->assertRedirect('/account');
        $this->assertTrue(Hash::check('Another-strong1!', $user->fresh()->password));
    }

    public function test_account_settings_require_valid_name_username_and_email(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from('/account')->put('/account', [
            'name' => '',
            'username' => '',
            'email' => 'not-an-email',
        ])->assertRedirect('/account')
            ->assertSessionHasErrors(['name', 'username', 'email']);
    }

    public function test_account_username_must_be_unique_across_user_and_superadmin_accounts(): void
    {
        $user = User::factory()->create();
        $superAdmin = SuperAdmin::factory()->create(['username' => 'taken_username']);

        $this->actingAs($user)->from('/account')->put('/account', [
            'name' => $user->name,
            'username' => $superAdmin->username,
            'email' => $user->email,
        ])->assertRedirect('/account')
            ->assertSessionHasErrors('username');
    }

    public function test_superadmin_can_sign_in_from_separate_table_and_change_password(): void
    {
        Notification::fake();
        $superadmin = SuperAdmin::factory()->unverified()->create([
            'email' => 'root@example.com',
            'password' => 'password123',
        ]);

        $this->post('/login', ['email' => $superadmin->email, 'password' => 'password123'])
            ->assertRedirect('/login/verify');
        $notification = Notification::sent(new AnonymousNotifiable, LoginOtpNotification::class)->first();
        $this->post('/login/verify', ['code' => $notification->code])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($superadmin, 'superadmin');
        $this->assertNotNull($superadmin->fresh()->email_verified_at);

        $this->from('/account')->put('/account/password', [
            'current_password' => 'password123',
            'password' => 'New-superadmin1!',
            'password_confirmation' => 'New-superadmin1!',
        ])->assertRedirect('/account');
        $this->assertTrue(Hash::check('New-superadmin1!', $superadmin->fresh()->password));
    }

    public function test_superadmin_table_does_not_store_address_fields(): void
    {
        foreach (['address', 'city', 'country', 'postal_code'] as $column) {
            $this->assertFalse(Schema::hasColumn('super_admins', $column));
        }
    }

    public function test_account_password_requires_mixed_case_number_and_symbol(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $this->actingAs($user)->from('/account')->put('/account/password', [
            'current_password' => 'password123',
            'password' => 'weakpassword',
            'password_confirmation' => 'weakpassword',
        ])->assertRedirect('/account')
            ->assertSessionHasErrors('password');
    }

    public function test_unverified_accounts_cannot_access_protected_pages(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->get('/dashboard')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
        $this->assertGuest('web');

        $superadmin = SuperAdmin::factory()->unverified()->create();
        $this->actingAs($superadmin, 'superadmin')->get('/dashboard')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
        $this->assertGuest('superadmin');
    }

    public function test_organization_access_is_scoped_to_superadmins_and_owners(): void
    {
        $user = User::factory()->create();
        $organization = Organization::create([
            'name' => 'Northwind Studio',
            'email' => 'northwind@example.test',
            'owner_user_id' => $user->id,
            'status' => 'active',
        ]);
        $organization->users()->attach($user, ['role' => 'owner', 'status' => 'active']);

        $this->assertSame(1, $organization->users()->count());
        $this->actingAs($user)
            ->get('/organizations/my-organization')
            ->assertOk()
            ->assertSee('Northwind Studio');
        $this->get('/organizations')->assertForbidden();

        $superadmin = SuperAdmin::factory()->create();
        $this->actingAs($superadmin, 'superadmin')->get('/organizations')
            ->assertOk()
            ->assertSee('Northwind Studio');
    }
}
