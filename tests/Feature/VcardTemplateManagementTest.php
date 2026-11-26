<?php

namespace Tests\Feature;

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
            'description' => 'A warm portrait layout',
            'status' => 'active',
            'image' => $this->png('jewellery.png'),
        ])->assertRedirect('/vcard-templates');

        $template = VcardTemplate::where('name', 'Jewellery')->firstOrFail();
        $oldImage = $template->image_path;
        Storage::disk('public')->assertExists($oldImage);
        $this->assertSame('storage/'.$oldImage, $template->getRawOriginal('image_url'));
        $this->get('/vcard-templates')
            ->assertOk()
            ->assertSee('Jewellery')
            ->assertDontSee('<th>DESCRIPTION</th>', false);
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
        $this->assertSame('storage/'.$template->image_path, $template->getRawOriginal('image_url'));
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

        $superadmin = SuperAdmin::factory()->create();
        $this->actingAs($superadmin, 'superadmin');
        $this->get('/vcard-templates/create')
            ->assertOk()
            ->assertSee('data-template-validation', false)
            ->assertSee('data-validation-message="image"', false);
        $this->post('/vcard-templates', [
            'name' => 'High resolution preview',
            'status' => 'active',
            'image' => $this->pngWithDimensions('high-resolution.png', 600, 5000),
        ])->assertRedirect('/vcard-templates');
        $this->assertDatabaseHas('vcard_templates', ['name' => 'High resolution preview']);

        $this->from('/vcard-templates/create')->post('/vcard-templates', [
            'name' => 'Invalid preview',
            'status' => 'active',
            'image' => UploadedFile::fake()->createWithContent('invalid.png', 'not an image')->mimeType('image/png'),
        ])->assertSessionHasErrors('image');
        $this->assertDatabaseMissing('vcard_templates', ['name' => 'Invalid preview']);
    }

    public function test_template_image_url_uses_the_current_host_and_port(): void
    {
        Storage::fake('public');
        config(['app.url' => 'http://localhost']);
        $superadmin = SuperAdmin::factory()->create();
        $this->actingAs($superadmin, 'superadmin');

        VcardTemplate::create([
            'name' => 'URL preview',
            'image_path' => 'vcard-templates/example.png',
            'status' => 'active',
        ]);

        $this->get('http://127.0.0.1:8000/vcard-templates')
            ->assertOk()
            ->assertSee('<th>PREVIEW URL</th>', false)
            ->assertSee('<a href="http://127.0.0.1:8000/storage/vcard-templates/example.png" target="_blank" rel="noopener noreferrer">http://127.0.0.1:8000/storage/vcard-templates/example.png</a>', false);
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
