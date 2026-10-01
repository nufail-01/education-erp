<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Institute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function test_super_admin_sees_global_metrics(): void
    {
        $response = $this->actingAs($this->user('superadmin@example.com'))
            ->get(route('pages-home'))
            ->assertOk()
            ->assertViewIs('content.dashboard.super-admin')
            ->assertSee('Recent Institutes');

        $this->assertSame(3, $response->viewData('totalInstitutes'));
        $this->assertSame(2, $response->viewData('activeInstitutes'));
        $this->assertSame(13, $response->viewData('totalUsers'));
        $this->assertCount(3, $response->viewData('recentInstitutes'));
    }

    public function test_institute_admin_sees_only_own_institute_data(): void
    {
        $admin = $this->user('admin.a@example.com');
        $b = Institute::where('code', 'DEMO-B')->firstOrFail();

        ActivityLog::create(['institute_id' => $admin->institute_id, 'action' => 'x', 'description' => 'Own activity marker']);
        ActivityLog::create(['institute_id' => $b->id, 'action' => 'x', 'description' => 'Foreign activity marker']);

        $response = $this->actingAs($admin)
            ->get(route('pages-home'))
            ->assertOk()
            ->assertViewIs('content.dashboard.institute-admin')
            ->assertSee('Own activity marker')
            ->assertDontSee('Foreign activity marker');

        $this->assertSame(3, $response->viewData('teacherCount'));
        $this->assertSame(4, $response->viewData('userCount'));
    }

    public function test_teacher_sees_profile_and_permitted_actions(): void
    {
        $this->actingAs($this->user('teacher1.a@example.com'))
            ->get(route('pages-home'))
            ->assertOk()
            ->assertViewIs('content.dashboard.teacher')
            ->assertSee('teacher1.a@example.com')
            ->assertSee('EMP-A-001')
            ->assertSee('teachers.view')
            ->assertDontSee('institutes.create');
    }

    public function test_guest_is_redirected(): void
    {
        $this->get(route('pages-home'))->assertRedirect(route('login'));
    }
}