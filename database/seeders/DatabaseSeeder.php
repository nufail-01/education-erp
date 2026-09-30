<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Production mein default password ke saath seed nahi hoga
        if (app()->isProduction() && ! env('SEED_PASSWORD')) {
            throw new \RuntimeException('Set SEED_PASSWORD in .env before seeding in production.');
        }

        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            SuperAdminSeeder::class,
            InstituteSeeder::class,
            InstituteAdminSeeder::class,
            TeacherSeeder::class,
        ]);
    }
}