<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('super_admins', function (Blueprint $table): void {
            $table->dropColumn(['address', 'city', 'country', 'postal_code']);
        });
    }

    public function down(): void
    {
        Schema::table('super_admins', function (Blueprint $table): void {
            $table->string('address')->nullable()->after('contact_no');
            $table->string('city')->nullable()->after('address');
            $table->string('country')->nullable()->after('city');
            $table->string('postal_code', 20)->nullable()->after('country');
        });
    }
};
