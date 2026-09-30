<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Global roles: institute_id = NULL, protected
        $superAdmin = Role::firstOrCreate(
            ['name' => User::ROLE_SUPER_ADMIN, 'guard_name' => 'web', 'institute_id' => null],
            ['is_protected' => true]
        );
        $superAdmin->syncPermissions(Permission::pluck('name')->all());

        $instituteAdmin = Role::firstOrCreate(
            ['name' => User::ROLE_INSTITUTE_ADMIN, 'guard_name' => 'web', 'institute_id' => null],
            ['is_protected' => true]
        );
        $instituteAdmin->syncPermissions([
            'teachers.view',
            'teachers.create',
            'teachers.update',
            'teachers.delete',
            'institute-users.view',
            'activity-logs.view',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'roles.assign',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}