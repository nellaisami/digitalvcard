<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('username', 50)->nullable()->unique();
        });

        Schema::table('super_admins', function (Blueprint $table): void {
            $table->string('username', 50)->nullable()->unique();
        });

        $this->populateUsernames('users', 'u');
        $this->populateUsernames('super_admins', 'sa');
    }

    public function down(): void
    {
        Schema::table('super_admins', function (Blueprint $table): void {
            $table->dropUnique('super_admins_username_unique');
            $table->dropColumn('username');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique('users_username_unique');
            $table->dropColumn('username');
        });
    }

    private function populateUsernames(string $table, string $prefix): void
    {
        DB::table($table)->select(['id', 'name'])->orderBy('id')->each(function (object $account) use ($table, $prefix): void {
            $name = Str::slug($account->name, '_');
            $username = $prefix.'_'.substr($name !== '' ? $name : 'account', 0, 24).'_'.$account->id;

            DB::table($table)->where('id', $account->id)->update(['username' => $username]);
        });
    }
};
