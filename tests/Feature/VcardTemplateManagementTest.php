<?php

namespace Tests\Feature;

use App\Models\Plan;
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
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($superadmin);

        $this->post('/vcard-templates', [
            'name' => 'Jewellery',
            'description' => 'A warm portrait layout',
            'status' => 'active',
            'image' => $this->png('jewellery.png'),
        ])->assertRedirect('/vcard-templates');

        $template = VcardTemplate::where('name', 'Jewellery')->firstOrFail();
        $oldImage = $template->image_path;
        Storage::disk('public')->assertExists($oldImage);
        $this->get('/vcard-templates')->assertOk()->assertSee('Jewellery');
        $this->get('/vcard-templates/'.$template->id.'/edit')->assertOk()->assertSee('A warm portrait layout');

        $this->put('/vcard-templates/'.$template->id, [
            'name' => 'Jewellery Deluxe',
            'description' => 'Updated portrait layout',
            'status' => 'active',
            'image' => $this->png('jewellery-deluxe.png'),
        ])->assertRedirect('/vcard-templates');
        $template->refresh();
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($template->image_path);
        $this->assertSame('Updated portrait layout', $template->description);

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
        $imagePath = $template->image_path;
        $this->delete('/vcard-templates/'.$template->id)->assertRedirect('/vcard-templates');
        $this->assertDatabaseMissing('vcard_templates', ['id' => $template->id]);
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_template_uploads_are_validated_and_management_is_superadmin_only(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/vcard-templates')->assertForbidden();
        $this->post('/vcard-templates', [])->assertForbidden();

        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($superadmin);
        $this->from('/vcard-templates/create')->post('/vcard-templates', [
            'name' => 'Invalid preview',
            'status' => 'active',
            'image' => UploadedFile::fake()->createWithContent('invalid.png', 'not an image')->mimeType('image/png'),
        ])->assertSessionHasErrors('image');
        $this->assertDatabaseMissing('vcard_templates', ['name' => 'Invalid preview']);
    }

    private function png(string $name): UploadedFile
    {
        $content = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/rWQAAAAASUVORK5CYII=', true);

        return UploadedFile::fake()->createWithContent($name, $content)->mimeType('image/png');
    }
}
