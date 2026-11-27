<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('organizations')
            ->whereNull('plan_id')
            ->whereNotNull('owner_user_id')
            ->orderBy('id')
            ->chunkById(100, function ($organizations): void {
                foreach ($organizations as $organization) {
                    $planId = DB::table('users')
                        ->where('id', $organization->owner_user_id)
                        ->value('plan_id');
                    if ($planId !== null) {
                        DB::table('organizations')
                            ->where('id', $organization->id)
                            ->whereNull('plan_id')
                            ->update(['plan_id' => $planId]);
                    }
                }
            });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['plan_id']);
            $table->dropColumn('plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('plan_id')->nullable()->after('profile_photo')->constrained()->nullOnDelete();
        });
    }
};
