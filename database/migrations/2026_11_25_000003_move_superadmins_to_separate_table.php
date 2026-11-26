<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('super_admins', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('contact_no', 30)->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('status')->default('active')->index();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('super_admin_login_otps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('super_admin_id')->unique()->constrained('super_admins')->cascadeOnDelete();
            $table->string('code_hash');
            $table->timestamp('expires_at');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamps();
        });

        $superAdmins = DB::table('users')->where('role', 'superadmin')->orderBy('id')->get();
        $legacyIds = $superAdmins->pluck('id')->all();

        DB::transaction(function () use ($superAdmins, $legacyIds): void {
            foreach ($superAdmins as $superAdmin) {
                DB::table('super_admins')->insert([
                    'name' => $superAdmin->name,
                    'email' => $superAdmin->email,
                    'email_verified_at' => $superAdmin->email_verified_at,
                    'password' => $superAdmin->password,
                    'contact_no' => $superAdmin->contact_no,
                    'address' => $superAdmin->address,
                    'city' => $superAdmin->city,
                    'country' => $superAdmin->country,
                    'postal_code' => $superAdmin->postal_code,
                    'profile_photo' => $superAdmin->profile_photo,
                    'status' => $superAdmin->status,
                    'remember_token' => $superAdmin->remember_token,
                    'created_at' => $superAdmin->created_at,
                    'updated_at' => $superAdmin->updated_at,
                ]);
            }

            if ($legacyIds !== []) {
                DB::table('organization_users')->whereIn('user_id', $legacyIds)->delete();
                DB::table('vcards')->whereIn('user_id', $legacyIds)->update(['user_id' => null]);
                DB::table('sessions')->whereIn('user_id', $legacyIds)->delete();
                DB::table('users')->whereIn('id', $legacyIds)->delete();
            }
        });
    }

    public function down(): void
    {
        $superAdmins = DB::table('super_admins')->orderBy('id')->get();

        DB::transaction(function () use ($superAdmins): void {
            foreach ($superAdmins as $superAdmin) {
                DB::table('users')->insert([
                    'name' => $superAdmin->name,
                    'email' => $superAdmin->email,
                    'email_verified_at' => $superAdmin->email_verified_at,
                    'password' => $superAdmin->password,
                    'contact_no' => $superAdmin->contact_no,
                    'address' => $superAdmin->address,
                    'city' => $superAdmin->city,
                    'country' => $superAdmin->country,
                    'postal_code' => $superAdmin->postal_code,
                    'profile_photo' => $superAdmin->profile_photo,
                    'role' => 'superadmin',
                    'status' => $superAdmin->status,
                    'remember_token' => $superAdmin->remember_token,
                    'plan_id' => null,
                    'created_at' => $superAdmin->created_at,
                    'updated_at' => $superAdmin->updated_at,
                ]);
            }
        });

        Schema::dropIfExists('super_admin_login_otps');
        Schema::dropIfExists('super_admins');
    }
};
