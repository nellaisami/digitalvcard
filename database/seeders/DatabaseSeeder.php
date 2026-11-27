<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Plan;
use App\Models\SuperAdmin;
use App\Models\User;
use App\Models\Vcard;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        SuperAdmin::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Northstar Super Admin',
                'password' => 'password123',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        );

        foreach ([
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
        User::query()->whereNotIn('email', ['admin@example.com', 'manager@example.com', 'editor@example.com', 'staff@example.com'])
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

        $organizations->first()->update(['plan_id' => Plan::where('name', 'Growth')->value('id')]);

        foreach (range(1, 24) as $index) {
            $user = User::query()->inRandomOrder()->first();
            $organizationId = DB::table('organization_users')
                ->where('user_id', $user->id)
                ->orderBy('organization_id')
                ->value('organization_id');
            Vcard::updateOrCreate(
                ['title' => 'Business card '.$index],
                ['user_id' => $user->id, 'organization_id' => $organizationId, 'status' => fake()->randomElement(['active', 'active', 'inactive'])],
            );
        }
    }
}
