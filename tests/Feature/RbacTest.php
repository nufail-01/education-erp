<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\User;
use App\Services\PrivilegeGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        Route::middleware(['web', 'auth', 'active', 'permission:institutes.view'])
            ->get('/_t/institutes', fn () => 'ok');
        Route::middleware(['web', 'auth', 'active', 'permission:teachers.view'])
            ->get('/_t/teachers', fn () => 'ok');
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    private function teamFor(User $user): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($user->institute_id ?? 0);
    }

    public function test_teacher_without_permission_gets_403(): void
    {
        $this->actingAs($this->user('teacher1.a@example.com'))
            ->get('/_t/institutes')
            ->assertForbidden();
    }

    public function test_super_admin_passes_permission_route(): void
    {
        $this->actingAs($this->user('superadmin@example.com'))
            ->get('/_t/institutes')
            ->assertOk();
    }

    public function test_custom_role_grants_and_revokes_access(): void
    {
        $teacher = $this->user('teacher1.a@example.com');
        $role = Role::where('name', 'Teacher')->where('institute_id', $teacher->institute_id)->firstOrFail();

        $this->actingAs($this->user('teacher1.a@example.com'))->get('/_t/teachers')->assertOk();

        $role->revokePermissionTo('teachers.view');
        $this->actingAs($this->user('teacher1.a@example.com'))->get('/_t/teachers')->assertForbidden();

        $role->givePermissionTo('teachers.view');
        $this->actingAs($this->user('teacher1.a@example.com'))->get('/_t/teachers')->assertOk();
    }

    public function test_institute_admin_cannot_access_other_institute_teacher(): void
    {
        $adminA = $this->user('admin.a@example.com');

        $teacherA = Teacher::withoutGlobalScopes()->where('institute_id', $adminA->institute_id)->firstOrFail();
        $teacherB = Teacher::withoutGlobalScopes()->where('institute_id', '!=', $adminA->institute_id)->firstOrFail();

        $this->teamFor($adminA);

        $this->assertTrue($adminA->can('view', $teacherA));
        $this->assertFalse($adminA->can('view', $teacherB));
        $this->assertFalse($adminA->can('update', $teacherB));
        $this->assertFalse($adminA->can('delete', $teacherB));

        $this->actingAs($adminA);
        $this->assertNull(Teacher::find($teacherB->id));
        $this->assertNotNull(Teacher::find($teacherA->id));
    }

    public function test_institute_admin_cannot_assign_super_admin_role(): void
    {
        $adminA = $this->user('admin.a@example.com');
        $target = $this->user('teacher1.a@example.com');
        $superRole = Role::where('name', User::ROLE_SUPER_ADMIN)->whereNull('institute_id')->firstOrFail();

        $this->teamFor($adminA);

        $this->assertFalse($adminA->can('assign', [$superRole, $target]));
        $this->assertFalse($adminA->can('assign', [$superRole, $adminA]));
    }

    public function test_institute_admin_can_assign_only_own_institute_role(): void
    {
        $adminA = $this->user('admin.a@example.com');
        $target = $this->user('teacher1.a@example.com');

        $roleA = Role::where('name', 'Teacher')->where('institute_id', $adminA->institute_id)->firstOrFail();
        $roleB = Role::where('name', 'Teacher')->where('institute_id', '!=', $adminA->institute_id)->firstOrFail();

        $this->teamFor($adminA);

        $this->assertTrue($adminA->can('assign', [$roleA, $target]));
        $this->assertFalse($adminA->can('assign', [$roleB, $target]));
    }

    public function test_only_delegable_permissions_can_be_granted(): void
    {
        $adminA = $this->user('admin.a@example.com');
        $this->teamFor($adminA);

        $guard = app(PrivilegeGuard::class);

        $this->assertTrue($guard->canGrantPermissions($adminA, ['teachers.view', 'teachers.create']));
        $this->assertFalse($guard->canGrantPermissions($adminA, ['institutes.create']));
        $this->assertFalse($guard->canGrantPermissions($adminA, ['roles.update']));
        $this->assertFalse($guard->canGrantPermissions($adminA, ['does.not.exist']));
    }

    public function test_protected_roles_cannot_be_modified_by_anyone(): void
    {
        $superRole = Role::where('name', User::ROLE_SUPER_ADMIN)->whereNull('institute_id')->firstOrFail();
        $adminRole = Role::where('name', User::ROLE_INSTITUTE_ADMIN)->whereNull('institute_id')->firstOrFail();

        $superAdmin = $this->user('superadmin@example.com');
        $this->teamFor($superAdmin);
        $this->assertFalse($superAdmin->can('update', $superRole));
        $this->assertFalse($superAdmin->can('delete', $adminRole));

        $adminA = $this->user('admin.a@example.com');
        $this->teamFor($adminA);
        $this->assertFalse($adminA->can('update', $adminRole));
    }

    public function test_user_deactivated_mid_session_is_logged_out(): void
    {
        $user = User::factory()->inactive()->create();

        $this->actingAs($user)
            ->get('/_t/teachers')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}