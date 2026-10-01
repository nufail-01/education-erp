<?php

namespace Database\Seeders;

use App\Models\Institute;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Institute::all() as $institute) {
            $letter = strtolower(substr($institute->code, -1));

           
            $role = Role::firstOrCreate(
                ['name' => 'Teacher', 'guard_name' => 'web', 'institute_id' => $institute->id],
                ['is_protected' => false]
            );
            $role->syncPermissions(['teachers.view']);

            app(PermissionRegistrar::class)->setPermissionsTeamId($institute->id);

            for ($n = 1; $n <= 3; $n++) {
                $teacher = Teacher::factory()->forInstitute($institute)->create([
                    'employee_code' => sprintf('EMP-%s-%03d', strtoupper($letter), $n),
                ]);

                $teacher->user->forceFill([
                    'name' => "Teacher {$n} {$institute->code}",
                    'email' => "teacher{$n}.{$letter}@example.com",
                    'password' => env('SEED_PASSWORD', 'password'),
                ])->save();

                $teacher->user->assignRole($role);
            }
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
    }
}