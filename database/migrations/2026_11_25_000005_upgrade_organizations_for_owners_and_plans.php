<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table): void {
            $table->string('email')->nullable()->unique()->after('name');
            $table->foreignId('owner_user_id')->nullable()->after('email')->constrained('users')->nullOnDelete();
            $table->foreignId('plan_id')->nullable()->after('owner_user_id')->constrained('plans')->nullOnDelete();
        });

        Schema::table('organization_users', function (Blueprint $table): void {
            $table->string('role')->default('member')->after('user_id');
            $table->unique(['organization_id', 'user_id']);
        });

        Schema::table('vcards', function (Blueprint $table): void {
            $table->foreignId('organization_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });

        DB::table('vcards')
            ->whereNull('organization_id')
            ->whereNotNull('user_id')
            ->orderBy('id')
            ->chunkById(100, function ($vcards): void {
                foreach ($vcards as $vcard) {
                    $organizationId = DB::table('organization_users')
                        ->where('user_id', $vcard->user_id)
                        ->orderBy('organization_id')
                        ->value('organization_id');
                    if ($organizationId) {
                        DB::table('vcards')
                            ->where('id', $vcard->id)
                            ->update(['organization_id' => $organizationId]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('vcards', function (Blueprint $table): void {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('organization_users', function (Blueprint $table): void {
            $table->dropUnique(['organization_id', 'user_id']);
            $table->dropColumn('role');
        });

        Schema::table('organizations', function (Blueprint $table): void {
            $table->dropForeign(['plan_id']);
            $table->dropForeign(['owner_user_id']);
            $table->dropUnique(['email']);
            $table->dropColumn(['plan_id', 'owner_user_id', 'email']);
        });
    }
};
