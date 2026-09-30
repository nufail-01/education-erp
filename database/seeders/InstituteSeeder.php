<?php

namespace Database\Seeders;

use App\Models\Institute;
use Illuminate\Database\Seeder;

class InstituteSeeder extends Seeder
{
    public function run(): void
    {
        $institutes = [
            ['name' => 'Demo Public School', 'code' => 'DEMO-A', 'status' => Institute::STATUS_ACTIVE],
            ['name' => 'Demo Science College', 'code' => 'DEMO-B', 'status' => Institute::STATUS_ACTIVE],
            ['name' => 'Demo Inactive Academy', 'code' => 'DEMO-C', 'status' => Institute::STATUS_INACTIVE],
        ];

        foreach ($institutes as $data) {
            Institute::factory()->create($data);
        }
    }
}