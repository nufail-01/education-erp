<?php

namespace Database\Seeders;

use App\Models\Institute;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class InstituteAdminSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', User::ROLE_INSTITUTE_ADMIN)->whereNull('institute_id')->firstOrFail();

        foreach (Institute::all() as $institute) {
            $letter = strtolower(substr($institute->code, -1));

            $admin = User::factory()->forInstitute($institute)->create([
                'name' => "Admin {$institute->code}",
                'email' => "admin.{$letter}@example.com",
                'password' => env('SEED_PASSWORD', 'password'),
            ]);

            app(PermissionRegistrar::class)->setPermissionsTeamId($institute->id);
            $admin->assignRole($role);
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
    }
}