<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
     
    public const PERMISSIONS = [
       
        'institutes.view' => false,
        'institutes.create' => false,
        'institutes.update' => false,
        'institutes.delete' => false,
        'institutes.toggle-status' => false,
        'institute-admins.manage' => false,
        'users.view-all' => false,
        'global-roles.manage' => false,

         
        'teachers.view' => true,
        'teachers.create' => true,
        'teachers.update' => true,
        'teachers.delete' => true,
        'institute-users.view' => true,
        'activity-logs.view' => true,

 
        'roles.view' => false,
        'roles.create' => false,
        'roles.update' => false,
        'roles.delete' => false,
        'roles.assign' => false,
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $name => $isDelegable) {
            Permission::updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['is_delegable' => $isDelegable]
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}