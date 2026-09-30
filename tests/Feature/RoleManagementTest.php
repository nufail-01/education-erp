<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
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

    private function createRole(string $name = 'Coordinator', array $permissions = ['teachers.view', 'teachers.create']): Role
    {
        $this->actingAs($this->user('admin.a@example.com'))
            ->post(route('roles.store'), ['name' => $name, 'permissions' => $permissions])
            ->assertRedirect(route('roles.index'));

        return Role::where('name', $name)
            ->where('institute_id', $this->user('admin.a@example.com')->institute_id)
            ->firstOrFail();
    }

    private function roleOfInstituteB(): Role
    {
        $b = $this->user('admin.b@example.com');

        return Role::where('name', 'Teacher')->where('institute_id', $b->institute_id)->firstOrFail();
    }

    public function test_institute_admin_can_create_role_with_delegable_permissions(): void
    {
        $admin = $this->user('admin.a@example.com');
        $role = $this->createRole();

        $this->assertSame($admin->institute_id, $role->institute_id);
        $this->assertFalse((bool) $role->is_protected);
        $this->assertTrue($role->hasPermissionTo('teachers.create'));
        $this->assertDatabaseHas('activity_logs', ['action' => 'role.created']);
    }

    public function test_non_delegable_permissions_are_rejected(): void
    {
        $admin = $this->user('admin.a@example.com');

        $this->actingAs($admin)
            ->post(route('roles.store'), ['name' => 'Sneaky', 'permissions' => ['institutes.create']])
            ->assertSessionHasErrors('permissions.0');

        $this->actingAs($admin)
            ->post(route('roles.store'), ['name' => 'Sneaky', 'permissions' => ['roles.update']])
            ->assertSessionHasErrors('permissions.0');

        $this->actingAs($admin)
            ->post(route('roles.store'), ['name' => 'Sneaky', 'permissions' => ['does.not.exist']])
            ->assertSessionHasErrors('permissions.0');

        $this->assertDatabaseMissing('roles', ['name' => 'Sneaky']);
    }

    public function test_reserved_and_duplicate_names_are_rejected(): void
    {
        $admin = $this->user('admin.a@example.com');

        $this->actingAs($admin)
            ->post(route('roles.store'), ['name' => 'Super Admin'])
            ->assertSessionHasErrors('name');
        $this->actingAs($admin)
            ->post(route('roles.store'), ['name' => 'Institute Admin'])
            ->assertSessionHasErrors('name');

        $this->createRole('Coordinator');
        $this->actingAs($admin)
            ->post(route('roles.store'), ['name' => 'Coordinator'])
            ->assertSessionHasErrors('name');

        // Dusre institute mein same naam chalta hai
        $this->actingAs($this->user('admin.b@example.com'))
            ->post(route('roles.store'), ['name' => 'Coordinator'])
            ->assertSessionHasNoErrors();
    }

    public function test_teacher_without_permission_gets_403(): void
    {
        $teacher = $this->user('teacher1.a@example.com');

        $this->actingAs($teacher)->get(route('roles.index'))->assertForbidden();
        $this->actingAs($teacher)->get(route('roles.create'))->assertForbidden();
        $this->actingAs($teacher)
            ->post(route('roles.store'), ['name' => 'Hack', 'permissions' => ['teachers.view']])
            ->assertForbidden();

        $this->assertDatabaseMissing('roles', ['name' => 'Hack']);
    }

    public function test_super_admin_can_view_but_not_create_custom_roles(): void
    {
        $sa = $this->user('superadmin@example.com');

        $this->actingAs($sa)->get(route('roles.index'))->assertOk()->assertSee('Super Admin');
        $this->actingAs($sa)->get(route('roles.create'))->assertForbidden();
        $this->actingAs($sa)
            ->post(route('roles.store'), ['name' => 'Global Custom'])
            ->assertForbidden();
    }

    public function test_institute_admin_only_sees_own_institute_roles(): void
    {
        $this->actingAs($this->user('admin.a@example.com'))
            ->get(route('roles.index'))
            ->assertOk()
            ->assertSee('DEMO-A')
            ->assertDontSee('DEMO-B')
            ->assertDontSee('Super Admin');
    }

    public function test_cross_institute_role_access_returns_404(): void
    {
        $admin = $this->user('admin.a@example.com');
        $other = $this->roleOfInstituteB();

        $this->actingAs($admin)->get(route('roles.edit', $other))->assertNotFound();
        $this->actingAs($admin)
            ->put(route('roles.update', $other), ['name' => 'Hijacked', 'permissions' => []])
            ->assertNotFound();
        $this->actingAs($admin)->delete(route('roles.destroy', $other))->assertNotFound();

        $this->assertSame('Teacher', $other->fresh()->name);
    }

    public function test_protected_roles_cannot_be_edited_or_deleted(): void
    {
        $adminRole = Role::where('name', User::ROLE_INSTITUTE_ADMIN)->whereNull('institute_id')->firstOrFail();

        $sa = $this->user('superadmin@example.com');
        $this->actingAs($sa)->get(route('roles.edit', $adminRole))->assertForbidden();
        $this->actingAs($sa)->delete(route('roles.destroy', $adminRole))->assertForbidden();

        $admin = $this->user('admin.a@example.com');
        $this->actingAs($admin)->get(route('roles.edit', $adminRole))->assertNotFound();
        $this->actingAs($admin)->delete(route('roles.destroy', $adminRole))->assertNotFound();

        $this->assertDatabaseHas('roles', ['id' => $adminRole->id]);
    }

    public function test_role_can_be_updated_and_permissions_change(): void
    {
        $admin = $this->user('admin.a@example.com');
        $role = $this->createRole();

        $this->actingAs($admin)
            ->put(route('roles.update', $role), ['name' => 'Coordinator 2', 'permissions' => ['teachers.view']])
            ->assertRedirect(route('roles.index'));

        $role = $role->fresh();
        $this->assertSame('Coordinator 2', $role->name);
        $this->assertTrue($role->hasPermissionTo('teachers.view'));
        $this->assertFalse($role->hasPermissionTo('teachers.create'));
    }

    public function test_unused_role_can_be_deleted_but_assigned_role_cannot(): void
    {
        $admin = $this->user('admin.a@example.com');
        $teacher = $this->user('teacher1.a@example.com');
        $role = $this->createRole();

        $this->actingAs($admin)
            ->put(route('users.roles.update', $teacher), ['roles' => [$role->id]]);

        $this->actingAs($admin)->delete(route('roles.destroy', $role));
        $this->assertDatabaseHas('roles', ['id' => $role->id]);

        $this->actingAs($admin)
            ->put(route('users.roles.update', $teacher), ['roles' => []]);

        $this->actingAs($admin)
            ->delete(route('roles.destroy', $role))
            ->assertRedirect(route('roles.index'));
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_custom_role_grants_and_revokes_access(): void
    {
        $admin = $this->user('admin.a@example.com');
        $teacherId = $this->user('teacher1.a@example.com')->id;
        $role = $this->createRole();

        // Assign se pehle teacher ke paas teachers.create nahi hai
        $this->actingAs(User::find($teacherId))->get(route('teachers.create'))->assertForbidden();

        $this->actingAs($admin)
            ->put(route('users.roles.update', $teacherId), ['roles' => [$role->id]])
            ->assertRedirect(route('users.index'));

        $this->actingAs(User::find($teacherId))->get(route('teachers.create'))->assertOk();

        // Revoke
        $this->actingAs($admin)
            ->put(route('users.roles.update', $teacherId), ['roles' => []])
            ->assertRedirect(route('users.index'));

        $this->actingAs(User::find($teacherId))->get(route('teachers.create'))->assertForbidden();
    }

    public function test_cannot_assign_foreign_or_super_admin_role(): void
    {
        $admin = $this->user('admin.a@example.com');
        $teacher = $this->user('teacher1.a@example.com');
        $foreign = $this->roleOfInstituteB();
        $super = Role::where('name', User::ROLE_SUPER_ADMIN)->whereNull('institute_id')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('users.roles.update', $teacher), ['roles' => [$foreign->id]])
            ->assertSessionHasErrors('roles.0');

        $this->actingAs($admin)
            ->put(route('users.roles.update', $teacher), ['roles' => [$super->id]])
            ->assertSessionHasErrors('roles.0');

        $this->assertFalse($this->user('teacher1.a@example.com')->isSuperAdmin());
        $this->assertDatabaseMissing('model_has_roles', [
            'role_id' => $super->id,
            'model_id' => $teacher->id,
        ]);
    }

    public function test_cannot_assign_roles_to_other_institute_user(): void
    {
        $admin = $this->user('admin.a@example.com');
        $otherUser = $this->user('teacher1.b@example.com');
        $role = $this->createRole();

        $this->actingAs($admin)->get(route('users.roles.edit', $otherUser))->assertNotFound();
        $this->actingAs($admin)
            ->put(route('users.roles.update', $otherUser), ['roles' => [$role->id]])
            ->assertNotFound();
    }

    public function test_teacher_cannot_assign_roles(): void
    {
        $teacher = $this->user('teacher1.a@example.com');
        $target = $this->user('teacher2.a@example.com');

        $this->actingAs($teacher)->get(route('users.roles.edit', $target))->assertForbidden();
        $this->actingAs($teacher)
            ->put(route('users.roles.update', $target), ['roles' => []])
            ->assertForbidden();
    }

    public function test_users_list_is_scoped(): void
    {
        $this->actingAs($this->user('admin.a@example.com'))
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('teacher1.a@example.com')
            ->assertDontSee('teacher1.b@example.com');

        $this->actingAs($this->user('superadmin@example.com'))
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('teacher1.b@example.com');

        $this->actingAs($this->user('teacher1.a@example.com'))
            ->get(route('users.index'))
            ->assertForbidden();
    }
}