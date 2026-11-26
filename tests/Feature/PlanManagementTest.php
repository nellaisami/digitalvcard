<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\SuperAdmin;
use App\Models\Tax;
use App\Models\User;
use App\Models\VcardTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_superadmins_can_access_plan_and_tax_management(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $this->actingAs($staff)->get('/plans')->assertForbidden();
        $this->get('/taxes')->assertForbidden();
        $this->get('/vcard-templates')->assertForbidden();
        $this->post('/plans', [])->assertForbidden();
        $this->post('/taxes', [])->assertForbidden();
        $this->post('/vcard-templates', [])->assertForbidden();

        $superadmin = SuperAdmin::factory()->create();
        $this->actingAs($superadmin, 'superadmin')->get('/plans')
            ->assertOk()
            ->assertSee('<span>Plan Management</span>', false)
            ->assertSee('Add new plan');
        $this->get('/plans/create')->assertOk()->assertSee('Select all features');
        $this->get('/taxes')->assertOk()->assertSee('Tax settings');
        $this->get('/taxes/create')->assertOk()->assertSee('Tax percentage');
        $this->get('/vcard-templates')->assertOk()->assertSee('Add template');
        $this->get('/vcard-templates/create')->assertOk()->assertSee('Template name');
    }

    public function test_plan_configuration_features_limits_default_and_assignment_deletion_safety(): void
    {
        $superadmin = SuperAdmin::factory()->create();
        $this->actingAs($superadmin, 'superadmin');
        $templateOne = VcardTemplate::create([
            'name' => 'Jewellery',
            'image_path' => 'vcard-templates/jewellery.png',
            'status' => 'active',
        ]);
        $templateTwo = VcardTemplate::create([
            'name' => 'Phone Accessories',
            'image_path' => 'vcard-templates/phone.png',
            'status' => 'active',
        ]);

        $this->post('/plans', [
            'name' => 'Growth',
            'price' => '100.50',
            'frequency' => 'yearly',
            'trial_days' => 14,
            'status' => 'active',
            'is_default' => '1',
            'features' => [
                'services' => '1',
                'no_of_visitors' => '1',
                'no_of_organization_users' => '1',
            ],
            'templates' => [$templateOne->id, $templateTwo->id],
            'unlimited_limits' => [
                'no_of_visitors' => '1',
                'no_of_organization_users' => '0',
            ],
            'feature_limits' => ['no_of_organization_users' => 5],
        ])->assertRedirect();

        $growth = Plan::where('name', 'Growth')->firstOrFail();
        $this->get('/plans/'.$growth->id.'/edit')
            ->assertOk()
            ->assertSee('<span>Edit plan</span>', false)
            ->assertSee('Number of Organization Users');
        $this->assertSame('yearly', $growth->frequency);
        $this->assertSame(14, $growth->trial_days);
        $this->assertSame(['services', 'no_of_visitors', 'no_of_organization_users', 'no_of_vcards'], $growth->features);
        $this->assertEqualsCanonicalizing([$templateOne->id, $templateTwo->id], $growth->vcardTemplates->modelKeys());
        $this->assertSame(['no_of_visitors' => null, 'no_of_organization_users' => 5], $growth->feature_limits);
        $this->assertTrue($growth->is_default);
        $this->get('/plans/'.$growth->id)->assertOk()->assertSee('Number of Organization Users')->assertSee('Unlimited')->assertSee('Jewellery');
        $this->delete('/vcard-templates/'.$templateOne->id)->assertRedirect();
        $this->assertDatabaseHas('vcard_templates', ['id' => $templateOne->id]);
        $this->patchJson('/vcard-templates/'.$templateTwo->id.'/status')->assertOk()->assertJson(['status' => 'inactive']);
        $this->get('/plans/'.$growth->id.'/edit')->assertOk()->assertSee('Phone Accessories')->assertSee('Inactive');

        $starter = Plan::create([
            'name' => 'Starter',
            'price' => 10,
            'frequency' => 'monthly',
            'trial_days' => 0,
            'features' => [],
            'feature_limits' => [],
            'is_default' => false,
            'status' => 'active',
        ]);
        $this->patch('/plans/'.$starter->id.'/default')->assertRedirect('/plans');
        $this->assertFalse($growth->fresh()->is_default);
        $this->assertTrue($starter->fresh()->is_default);
        $this->patchJson('/plans/'.$growth->id.'/status')->assertOk()->assertJson(['status' => 'inactive']);
        $this->patchJson('/plans/'.$growth->id.'/status')->assertOk()->assertJson(['status' => 'active']);

        $this->put('/plans/'.$growth->id, [
            'name' => 'Growth Plus',
            'price' => '125.25',
            'frequency' => 'yearly',
            'trial_days' => 30,
            'status' => 'active',
            'is_default' => '0',
            'features' => ['analytics' => '1', 'no_of_visitors' => '1'],
            'templates' => [$templateTwo->id],
            'unlimited_limits' => ['no_of_visitors' => '1', 'no_of_organization_users' => '0'],
            'feature_limits' => [],
        ])->assertRedirect('/plans/'.$growth->id);
        $this->assertDatabaseHas('plans', ['id' => $growth->id, 'name' => 'Growth Plus', 'price' => '125.25']);

        $user = User::factory()->create(['plan_id' => $starter->id]);
        $this->delete('/plans/'.$starter->id)->assertRedirect();
        $this->assertDatabaseHas('plans', ['id' => $starter->id]);
        $this->assertSame($starter->id, $user->fresh()->plan_id);
        $this->delete('/plans/'.$growth->id)->assertRedirect('/plans');
        $this->assertDatabaseMissing('plans', ['id' => $growth->id]);
        $templateTwo->delete();
    }

    public function test_default_plan_is_unique_and_must_remain_active(): void
    {
        $superadmin = SuperAdmin::factory()->create();
        $this->actingAs($superadmin, 'superadmin');
        $template = VcardTemplate::create([
            'name' => 'Default Template',
            'image_path' => 'vcard-templates/default.png',
            'status' => 'active',
        ]);
        $baseData = [
            'price' => 0,
            'frequency' => 'monthly',
            'trial_days' => 0,
            'status' => 'active',
            'is_default' => '1',
            'features' => [],
            'templates' => [$template->id],
            'unlimited_limits' => ['no_of_visitors' => '1', 'no_of_organization_users' => '1'],
        ];

        $this->post('/plans', $baseData + ['name' => 'First default'])->assertRedirect();
        $this->post('/plans', $baseData + ['name' => 'Second default'])->assertRedirect();
        $this->assertSame(1, Plan::where('is_default', true)->count());

        $defaultPlan = Plan::where('is_default', true)->firstOrFail();
        $this->patchJson('/plans/'.$defaultPlan->id.'/status')->assertUnprocessable();
        $this->assertSame('active', $defaultPlan->fresh()->status);

        $invalid = $baseData;
        $invalid['name'] = 'Inactive default';
        $invalid['status'] = 'inactive';
        $this->from('/plans/create')->post('/plans', $invalid)->assertSessionHasErrors('is_default');

        $invalidFeatures = $baseData;
        $invalidFeatures['name'] = 'Unknown feature';
        $invalidFeatures['is_default'] = '0';
        $invalidFeatures['features'] = ['arbitrary_privilege' => '1'];
        $this->from('/plans/create')->post('/plans', $invalidFeatures)->assertSessionHasErrors('features');
    }

    public function test_tax_crud_validation_and_additive_plan_tax_estimate(): void
    {
        $superadmin = SuperAdmin::factory()->create();
        $this->actingAs($superadmin, 'superadmin');
        $plan = Plan::create([
            'name' => 'Taxable',
            'price' => 1000,
            'frequency' => 'monthly',
            'trial_days' => 0,
            'features' => [],
            'feature_limits' => [],
            'is_default' => false,
            'status' => 'active',
        ]);

        $this->post('/taxes', ['tax_name' => 'GST', 'tax_value' => '18', 'status' => 'active'])->assertRedirect('/taxes');
        $gst = Tax::where('tax_name', 'GST')->firstOrFail();
        $this->get('/taxes/'.$gst->id.'/edit')->assertOk()->assertSee('GST');
        $this->post('/taxes', ['tax_name' => 'Service Tax', 'tax_value' => '2.5', 'status' => 'active'])->assertRedirect('/taxes');
        $inactive = Tax::create(['tax_name' => 'Inactive', 'tax_value' => 5, 'status' => 'inactive']);

        $this->get('/plans/'.$plan->id)
            ->assertOk()
            ->assertSee('GST')
            ->assertSee('180.00')
            ->assertSee('Service Tax')
            ->assertSee('25.00')
            ->assertSee('1,205.00')
            ->assertDontSee('Inactive');

        $this->from('/taxes/create')->post('/taxes', [
            'tax_name' => 'Invalid',
            'tax_value' => '100.01',
            'status' => 'active',
        ])->assertSessionHasErrors('tax_value');
        $this->patchJson('/taxes/'.$gst->id.'/status')->assertOk()->assertJson(['status' => 'inactive']);
        $this->put('/taxes/'.$gst->id, [
            'tax_name' => 'GST Updated',
            'tax_value' => '7.125',
            'status' => 'active',
        ])->assertRedirect('/taxes');
        $this->assertDatabaseHas('taxes', ['id' => $gst->id, 'tax_name' => 'GST Updated', 'tax_value' => '7.1250']);
        $this->delete('/taxes/'.$inactive->id)->assertRedirect('/taxes');
        $this->assertDatabaseMissing('taxes', ['id' => $inactive->id]);
    }

    public function test_plan_search_pagination_and_user_plan_display_include_frequency_and_price(): void
    {
        $superadmin = SuperAdmin::factory()->create();
        $plan = Plan::create([
            'name' => 'Quarterly Team',
            'price' => 45,
            'frequency' => 'yearly',
            'trial_days' => 7,
            'features' => [],
            'feature_limits' => [],
            'is_default' => false,
            'status' => 'active',
        ]);
        User::factory()->create(['plan_id' => $plan->id]);
        foreach (range(1, 11) as $index) {
            Plan::create([
                'name' => 'Additional plan '.$index,
                'price' => $index,
                'frequency' => 'monthly',
                'trial_days' => 0,
                'features' => [],
                'feature_limits' => [],
                'is_default' => false,
                'status' => 'active',
            ]);
        }

        $this->actingAs($superadmin, 'superadmin')
            ->get('/plans?search=Quarterly')
            ->assertOk()
            ->assertSee('Quarterly Team')
            ->assertSee('45.00');
        $this->get('/plans?page=2')->assertOk()->assertSee('Showing 11–12 of 12 plans');
        $this->get('/users?plan_id='.$plan->id)->assertOk()
            ->assertSee('Quarterly Team')
            ->assertSee('Yearly');
    }
}
