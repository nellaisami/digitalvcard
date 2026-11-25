<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Vcard;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['Northstar Super Admin', 'superadmin@example.com', 'superadmin'],
            ['Alex Admin', 'admin@example.com', 'admin'],
            ['Morgan Manager', 'manager@example.com', 'manager'],
            ['Elliot Editor', 'editor@example.com', 'editor'],
            ['Sam Staff', 'staff@example.com', 'staff'],
        ] as [$name, $email, $role]) {
            User::updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => 'password123', 'role' => $role, 'status' => 'active', 'email_verified_at' => now()],
            );
        }

        User::factory(max(0, 20 - User::count()))->create();
        User::query()->whereNotIn('email', ['superadmin@example.com', 'admin@example.com', 'manager@example.com', 'editor@example.com', 'staff@example.com'])
            ->orderBy('id')
            ->limit(4)
            ->update(['status' => 'inactive']);
        $organizations = collect([
            ['name' => 'Brightside Studio', 'status' => 'active'],
            ['name' => 'Fieldwork Collective', 'status' => 'active'],
            ['name' => 'Juniper & Co.', 'status' => 'inactive'],
        ])->map(fn (array $organization) => Organization::updateOrCreate(['name' => $organization['name']], $organization));

        $organizations->each(function (Organization $organization): void {
            User::query()->inRandomOrder()->limit(4)->get()->each(function (User $user) use ($organization): void {
                DB::table('organization_users')->updateOrInsert(
                    ['organization_id' => $organization->id, 'user_id' => $user->id],
                    ['status' => fake()->randomElement(['active', 'active', 'inactive']), 'created_at' => now(), 'updated_at' => now()],
                );
            });
        });

        foreach (['Starter', 'Growth', 'Enterprise'] as $name) {
            Plan::updateOrCreate(
                ['name' => $name],
                ['price' => ['Starter' => 0, 'Growth' => 29, 'Enterprise' => 99][$name], 'status' => 'active'],
            );
        }

        User::where('email', 'superadmin@example.com')->update(['plan_id' => Plan::where('name', 'Enterprise')->value('id')]);
        User::whereIn('email', ['admin@example.com', 'manager@example.com'])->update(['plan_id' => Plan::where('name', 'Growth')->value('id')]);

        foreach (range(1, 24) as $index) {
            $user = User::query()->inRandomOrder()->first();
            Vcard::updateOrCreate(
                ['title' => 'Business card '.$index],
                ['user_id' => $user->id, 'status' => fake()->randomElement(['active', 'active', 'inactive'])],
            );
        }
    }
}
