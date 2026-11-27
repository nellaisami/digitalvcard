<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Plan;
use App\Models\SuperAdmin;
use App\Models\User;
use App\Models\VcardTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VcardTemplateManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_create_edit_preview_and_delete_templates(): void
    {
        Storage::fake('public');
        $superadmin = SuperAdmin::factory()->create();
        $this->actingAs($superadmin, 'superadmin');

        $this->post('/vcard-templates', [
            'name' => 'Jewellery',
            'price' => '24.50',
            'html_code' => '<!doctype html><html><body><h1>A warm portrait layout</h1></body></html>',
            'status' => 'active',
            'image' => $this->png('jewellery.png'),
        ])->assertRedirect('/vcard-templates');

        $template = VcardTemplate::where('name', 'Jewellery')->firstOrFail();
        $this->assertSame('24.50', $template->price);
        $oldImage = $template->image_path;
        Storage::disk('public')->assertExists($oldImage);
        $this->assertSame('storage/'.$oldImage, $template->getRawOriginal('image_url'));
        $this->get('/vcard-templates')
            ->assertOk()
            ->assertSee('Jewellery')
            ->assertSee('<th>USED COUNT</th>', false)
            ->assertDontSee('Description');
        $this->get('/vcard-templates/'.$template->id.'/edit')
            ->assertOk()
            ->assertSee('A warm portrait layout')
            ->assertSee('data-template-html-preview', false)
            ->assertSee('template-editor-layout', false)
            ->assertSee('data-template-html-editor', false);

        $this->put('/vcard-templates/'.$template->id, [
            'name' => 'Jewellery Deluxe',
            'price' => '39.99',
            'html_code' => '<!doctype html><html><body><h1>Updated portrait layout</h1></body></html>',
            'status' => 'active',
            'image' => $this->png('jewellery-deluxe.png'),
        ])->assertRedirect('/vcard-templates');
        $template->refresh();
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($template->image_path);
        $this->assertSame('storage/'.$template->image_path, $template->getRawOriginal('image_url'));
        $this->assertSame('39.99', $template->price);
        $this->assertStringContainsString('Updated portrait layout', $template->html_code);

        $plan = Plan::create([
            'name' => 'Template plan',
            'price' => 10,
            'frequency' => 'monthly',
            'trial_days' => 0,
            'features' => ['no_of_vcards'],
            'feature_limits' => [],
            'is_default' => false,
            'status' => 'active',
        ]);
        $plan->vcardTemplates()->attach($template);
        $this->delete('/vcard-templates/'.$template->id)->assertRedirect();
        $this->assertDatabaseHas('vcard_templates', ['id' => $template->id]);

        $plan->vcardTemplates()->detach($template);
        $selectedUser = User::factory()->create(['vcard_template_id' => $template->id]);
        $organization = Organization::create([
            'name' => 'Template assigned workspace',
            'vcard_template_id' => $template->id,
        ]);
        $imagePath = $template->image_path;
        $this->delete('/vcard-templates/'.$template->id)->assertRedirect('/vcard-templates');
        $this->assertDatabaseMissing('vcard_templates', ['id' => $template->id]);
        $this->assertNull($selectedUser->fresh()->vcard_template_id);
        $this->assertNull($organization->fresh()->vcard_template_id);
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_template_uploads_are_validated_and_management_is_superadmin_only(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/vcard-templates')->assertForbidden();
        $this->post('/vcard-templates', [])->assertForbidden();

        $superadmin = SuperAdmin::factory()->create();
        $this->actingAs($superadmin, 'superadmin');
        $this->get('/vcard-templates/create')
            ->assertOk()
            ->assertSee('data-template-validation', false)
            ->assertSee('data-validation-message="image"', false)
            ->assertSee('HTML code')
            ->assertSee('template-editor-preview', false)
            ->assertDontSee('Description');
        $this->post('/vcard-templates', [
            'name' => 'High resolution preview',
            'price' => '10',
            'html_code' => '<main>Template</main>',
            'status' => 'active',
            'image' => $this->pngWithDimensions('high-resolution.png', 600, 5000),
        ])->assertRedirect('/vcard-templates');
        $this->assertDatabaseHas('vcard_templates', ['name' => 'High resolution preview']);

        $this->from('/vcard-templates/create')->post('/vcard-templates', [
            'name' => 'Invalid preview',
            'price' => '10',
            'html_code' => '<main>Template</main>',
            'status' => 'active',
            'image' => UploadedFile::fake()->createWithContent('invalid.png', 'not an image')->mimeType('image/png'),
        ])->assertSessionHasErrors('image');
        $this->assertDatabaseMissing('vcard_templates', ['name' => 'Invalid preview']);

        $this->from('/vcard-templates/create')->post('/vcard-templates', [
            'name' => 'Invalid price and code',
            'price' => '-1',
            'html_code' => '',
            'status' => 'active',
            'image' => $this->png('valid-preview.png'),
        ])->assertSessionHasErrors(['price', 'html_code']);
    }

    public function test_template_image_url_uses_the_current_host_and_port(): void
    {
        Storage::fake('public');
        config(['app.url' => 'http://localhost']);
        $superadmin = SuperAdmin::factory()->create();
        $this->actingAs($superadmin, 'superadmin');

        VcardTemplate::create([
            'name' => 'URL preview',
            'html_code' => '<p>Preview contents</p>',
            'image_path' => 'vcard-templates/example.png',
            'status' => 'active',
        ]);

        $this->get('http://127.0.0.1:8000/vcard-templates')
            ->assertOk()
            ->assertSee('<th>PREVIEW URL</th>', false)
            ->assertSee('<a href="http://127.0.0.1:8000/vcard-templates/1/preview" target="_blank" rel="noopener noreferrer">Open preview</a>', false);
        $this->get('/vcard-templates/1/preview')
            ->assertOk()
            ->assertSee('sandbox=""', false)
            ->assertSee('Preview contents');
    }

    public function test_used_count_tracks_direct_user_selections_and_users_can_select_templates(): void
    {
        $template = VcardTemplate::create([
            'name' => 'Personal Choice',
            'price' => 15,
            'html_code' => '<main>Personal template</main>',
            'image_path' => 'vcard-templates/personal.png',
            'status' => 'active',
        ]);
        $user = User::factory()->create(['username' => 'selected_user']);

        $this->actingAs($user)->get('/account')
            ->assertOk()
            ->assertSee('vCard template')
            ->assertSee(strtoupper(config('app.currency', 'USD')).' 15.00');
        $this->put('/account', [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'vcard_template_id' => $template->id,
        ])->assertRedirect('/account')->assertSessionHasNoErrors();
        $this->assertSame($template->id, $user->fresh()->vcard_template_id);

        $organizationOwner = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $organization = Organization::create([
            'name' => 'Workspace Choice',
            'owner_user_id' => $organizationOwner->id,
        ]);
        $organization->users()->attach($organizationOwner, ['role' => 'owner', 'status' => 'active']);
        $this->actingAs($organizationOwner)
            ->get('/organizations/'.$organization->id)
            ->assertOk()
            ->assertSee('Organization vCard template');
        $this->patch('/organizations/'.$organization->id.'/template', [
            'vcard_template_id' => $template->id,
        ])->assertRedirect('/organizations/'.$organization->id);
        $this->assertSame($template->id, $organization->fresh()->vcard_template_id);

        $this->actingAs(SuperAdmin::factory()->create(), 'superadmin')
            ->get('/vcard-templates')
            ->assertViewHas('templates', fn ($templates) => $templates->firstWhere('id', $template->id)->users_count === 1);
    }

    private function png(string $name): UploadedFile
    {
        $content = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/rWQAAAAASUVORK5CYII=', true);

        return UploadedFile::fake()->createWithContent($name, $content)->mimeType('image/png');
    }

    private function pngWithDimensions(string $name, int $width, int $height): UploadedFile
    {
        $chunk = static function (string $type, string $data): string {
            return pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));
        };
        $imageData = '';
        for ($row = 0; $row < $height; $row++) {
            $imageData .= "\0".str_repeat("\0", $width * 4);
        }
        $contents = "\x89PNG\r\n\x1a\n"
            .$chunk('IHDR', pack('NNCCCCC', $width, $height, 8, 6, 0, 0, 0))
            .$chunk('IDAT', gzcompress($imageData))
            .$chunk('IEND', '');

        return UploadedFile::fake()->createWithContent($name, $contents)->mimeType('image/png');
    }
}
