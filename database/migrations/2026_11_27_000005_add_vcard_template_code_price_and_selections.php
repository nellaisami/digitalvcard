<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vcard_templates', function (Blueprint $table): void {
            $table->dropColumn('description');
            $table->decimal('price', 10, 2)->default(0);
            $table->longText('html_code')->nullable();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('vcard_template_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('organizations', function (Blueprint $table): void {
            $table->foreignId('vcard_template_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table): void {
            $table->dropForeign(['vcard_template_id']);
            $table->dropColumn('vcard_template_id');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['vcard_template_id']);
            $table->dropColumn('vcard_template_id');
        });

        Schema::table('vcard_templates', function (Blueprint $table): void {
            $table->dropColumn(['price', 'html_code']);
            $table->text('description')->nullable();
        });
    }
};
