<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'institute_id' => null,
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => env('SEED_PASSWORD', 'password'),
        ]);

        $role = Role::where('name', User::ROLE_SUPER_ADMIN)->whereNull('institute_id')->firstOrFail();

        // Super Admin kisi institute ka nahi, isliye team id 0
        app(PermissionRegistrar::class)->setPermissionsTeamId(0);
        $user->assignRole($role);
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
    }
}