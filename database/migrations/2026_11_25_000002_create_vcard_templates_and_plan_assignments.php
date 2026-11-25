<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vcard_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('image_path');
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });

        Schema::create('plan_vcard_template', function (Blueprint $table): void {
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vcard_template_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['plan_id', 'vcard_template_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_vcard_template');
        Schema::dropIfExists('vcard_templates');
    }
};
