<?php

namespace Tests\Feature;

use App\Models\Institute;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_seed_creates_usable_demo_data(): void
    {
        $this->seed();

        $this->assertSame(3, Institute::count());
        $this->assertSame(9, Teacher::count());
        $this->assertSame(13, User::count());
        $this->assertTrue(
            (bool) Role::where('name', User::ROLE_SUPER_ADMIN)->whereNull('institute_id')->value('is_protected')
        );
        $this->assertSame(
            0,
            User::whereNull('institute_id')->where('email', '!=', 'superadmin@example.com')->count()
        );
    }

    public function test_seeded_users_can_login(): void
    {
        $this->seed();

        foreach (['superadmin@example.com', 'admin.a@example.com', 'teacher1.a@example.com'] as $email) {
            $this->post(route('login.store'), ['email' => $email, 'password' => 'password'])
                ->assertRedirect(route('pages-home'));
            $this->post(route('logout'));
        }
    }
}