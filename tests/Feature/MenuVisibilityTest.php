<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function home(string $email)
    {
        $user = User::where('email', $email)->firstOrFail();

        return $this->actingAs($user)->get(route('pages-home'))->assertOk();
    }

    public function test_super_admin_menu(): void
    {
        $this->home('superadmin@example.com')
            ->assertSee('/institutes')
            ->assertSee('/institute-admins');
    }

    public function test_institute_admin_menu(): void
    {
        $this->home('admin.a@example.com')
            ->assertSee('/teachers')
            ->assertSee('/roles')
            ->assertSee('/users')
            ->assertDontSee('/institutes')
            ->assertDontSee('/institute-admins');
    }

    public function test_teacher_menu_shows_only_permitted_links(): void
    {
        $this->home('teacher1.a@example.com')
            ->assertSee('/teachers')
            ->assertDontSee('/roles')
            ->assertDontSee('/users')
            ->assertDontSee('/institutes')
            ->assertDontSee('/institute-admins');
    }
}