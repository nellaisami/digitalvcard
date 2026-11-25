<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            $table->string('frequency')->default('monthly')->after('price');
            $table->unsignedInteger('no_of_vcards')->nullable()->after('frequency');
            $table->unsignedInteger('trial_days')->default(0)->after('no_of_vcards');
            $table->json('features')->nullable()->after('trial_days');
            $table->json('feature_limits')->nullable()->after('features');
            $table->boolean('is_default')->default(false)->index()->after('feature_limits');
        });

        Schema::create('taxes', function (Blueprint $table): void {
            $table->id();
            $table->string('tax_name')->unique();
            $table->decimal('tax_value', 7, 4);
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxes');
        Schema::table('plans', function (Blueprint $table): void {
            $table->dropIndex(['is_default']);
            $table->dropColumn([
                'frequency',
                'no_of_vcards',
                'trial_days',
                'features',
                'feature_limits',
                'is_default',
            ]);
        });
    }
};
