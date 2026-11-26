<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vcard_templates', function (Blueprint $table): void {
            $table->string('image_url')->nullable();
        });

        DB::table('vcard_templates')
            ->select('id', 'image_path')
            ->orderBy('id')
            ->chunkById(100, function ($templates): void {
                foreach ($templates as $template) {
                    DB::table('vcard_templates')
                        ->where('id', $template->id)
                        ->update(['image_url' => 'storage/'.ltrim($template->image_path, '/')]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('vcard_templates', function (Blueprint $table): void {
            $table->dropColumn('image_url');
        });
    }
};
