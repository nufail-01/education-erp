<?php

namespace Tests\Feature;

use App\Models\Institute;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeacherTest extends TestCase
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

    private function data(array $overrides = []): array
    {
        return array_merge([
            'name' => 'New Teacher',
            'email' => 'new.teacher@example.com',
            'mobile_no' => '9876543210',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'employee_code' => 'EMP-NEW-01',
            'qualification' => 'M.Sc',
            'joining_date' => '2024-06-01',
        ], $overrides);
    }

    private function teacherOf(string $code): Teacher
    {
        return Teacher::withoutGlobalScopes()
            ->where('employee_code', $code)
            ->firstOrFail();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('teachers.index'))->assertRedirect(route('login'));
    }

    public function test_institute_admin_can_create_teacher_in_own_institute(): void
    {
        $admin = $this->user('admin.a@example.com');

        $this->actingAs($admin)
            ->post(route('teachers.store'), $this->data())
            ->assertRedirect(route('teachers.index'));

        $user = User::where('email', 'new.teacher@example.com')->firstOrFail();
        $teacher = Teacher::withoutGlobalScopes()->where('user_id', $user->id)->firstOrFail();
        $role = Role::where('name', 'Teacher')->where('institute_id', $admin->institute_id)->firstOrFail();

        $this->assertSame($admin->institute_id, $user->institute_id);
        $this->assertSame($admin->institute_id, $teacher->institute_id);
        $this->assertNotSame('Secret123!', $user->password);
        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $role->id,
            'model_id' => $user->id,
            'institute_id' => $admin->institute_id,
        ]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'teacher.created']);
    }

    public function test_institute_id_from_form_is_ignored(): void
    {
        $admin = $this->user('admin.a@example.com');
        $other = Institute::where('code', 'DEMO-B')->value('id');

        $this->actingAs($admin)
            ->post(route('teachers.store'), $this->data(['institute_id' => $other]));

        $user = User::where('email', 'new.teacher@example.com')->firstOrFail();

        $this->assertSame($admin->institute_id, $user->institute_id);
        $this->assertNotSame($other, $user->institute_id);
    }

    public function test_new_teacher_can_login(): void
    {
        $this->actingAs($this->user('admin.a@example.com'))
            ->post(route('teachers.store'), $this->data());
        auth()->logout();

        $this->post(route('login.store'), [
            'email' => 'new.teacher@example.com',
            'password' => 'Secret123!',
        ])->assertRedirect(route('pages-home'));

        $this->assertAuthenticated();
    }

    public function test_super_admin_cannot_create_teacher(): void
    {
        $this->actingAs($this->user('superadmin@example.com'))
            ->post(route('teachers.store'), $this->data())
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'new.teacher@example.com']);
    }

    public function test_teacher_without_create_permission_gets_403(): void
    {
        $teacher = $this->user('teacher1.a@example.com');

        $this->actingAs($teacher)->get(route('teachers.create'))->assertForbidden();
        $this->actingAs($teacher)->post(route('teachers.store'), $this->data())->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'new.teacher@example.com']);
    }

    public function test_user_without_view_permission_gets_403_on_list(): void
    {
        $teacher = $this->user('teacher1.a@example.com');
        $role = Role::where('name', 'Teacher')->where('institute_id', $teacher->institute_id)->firstOrFail();
        $role->revokePermissionTo('teachers.view');

        $this->actingAs($this->user('teacher1.a@example.com'))
            ->get(route('teachers.index'))
            ->assertForbidden();
    }

    public function test_list_only_shows_own_institute_teachers(): void
    {
        $this->actingAs($this->user('admin.a@example.com'))
            ->get(route('teachers.index'))
            ->assertOk()
            ->assertSee('teacher1.a@example.com')
            ->assertDontSee('teacher1.b@example.com');
    }

    public function test_search_and_status_filter(): void
    {
        $admin = $this->user('admin.a@example.com');
        $target = $this->teacherOf('EMP-A-001');
        $target->update(['status' => Teacher::STATUS_INACTIVE]);

        $this->actingAs($admin)
            ->get(route('teachers.index', ['search' => 'EMP-A-002']))
            ->assertOk()
            ->assertSee('teacher2.a@example.com')
            ->assertDontSee('teacher3.a@example.com');

        $this->actingAs($admin)
            ->get(route('teachers.index', ['status' => 'inactive']))
            ->assertOk()
            ->assertSee('teacher1.a@example.com')
            ->assertDontSee('teacher2.a@example.com');
    }

    public function test_institute_admin_cannot_touch_other_institute_teacher(): void
    {
        $admin = $this->user('admin.a@example.com');
        $other = $this->teacherOf('EMP-B-001');

        $this->actingAs($admin)->get(route('teachers.edit', $other))->assertNotFound();
        $this->actingAs($admin)
            ->put(route('teachers.update', $other), $this->data(['email' => $other->user->email, 'employee_code' => 'EMP-B-001']))
            ->assertNotFound();
        $this->actingAs($admin)->patch(route('teachers.toggle-status', $other))->assertNotFound();
        $this->actingAs($admin)->delete(route('teachers.destroy', $other))->assertNotFound();

        $fresh = $this->teacherOf('EMP-B-001');
        $this->assertSame(Teacher::STATUS_ACTIVE, $fresh->status);
        $this->assertNotSame('New Teacher', $fresh->user->fresh()->name);
    }

    public function test_validation_rules(): void
    {
        $admin = $this->user('admin.a@example.com');

        $this->actingAs($admin)
            ->post(route('teachers.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'password', 'employee_code']);

        // duplicate email
        $this->actingAs($admin)
            ->post(route('teachers.store'), $this->data(['email' => 'teacher1.b@example.com']))
            ->assertSessionHasErrors('email');

        // duplicate employee code in same institute
        $this->actingAs($admin)
            ->post(route('teachers.store'), $this->data(['employee_code' => 'EMP-A-001']))
            ->assertSessionHasErrors('employee_code');

        // weak password, future joining date
        $this->actingAs($admin)
            ->post(route('teachers.store'), $this->data(['password' => 'short', 'password_confirmation' => 'short']))
            ->assertSessionHasErrors('password');
        $this->actingAs($admin)
            ->post(route('teachers.store'), $this->data(['joining_date' => now()->addDay()->format('Y-m-d')]))
            ->assertSessionHasErrors('joining_date');
    }

    public function test_same_employee_code_allowed_in_different_institute(): void
    {
        $this->actingAs($this->user('admin.a@example.com'))
            ->post(route('teachers.store'), $this->data(['employee_code' => 'EMP-B-001']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('teachers', ['employee_code' => 'EMP-B-001', 'institute_id' => $this->user('admin.a@example.com')->institute_id]);
    }

    public function test_institute_admin_can_update_teacher(): void
    {
        $admin = $this->user('admin.a@example.com');
        $teacher = $this->teacherOf('EMP-A-001');

        $this->actingAs($admin)
            ->put(route('teachers.update', $teacher), $this->data([
                'email' => $teacher->user->email,
                'employee_code' => 'EMP-A-001',
                'name' => 'Renamed Teacher',
                'password' => '',
                'password_confirmation' => '',
            ]))
            ->assertRedirect(route('teachers.index'));

        $this->assertSame('Renamed Teacher', $teacher->user->fresh()->name);
        $this->assertSame('M.Sc', $teacher->fresh()->qualification);
    }

    public function test_deactivated_teacher_cannot_login(): void
    {
        $admin = $this->user('admin.a@example.com');
        $teacher = $this->teacherOf('EMP-A-001');
        $email = $teacher->user->email;

        $this->actingAs($admin)->patch(route('teachers.toggle-status', $teacher));

        $this->assertSame(Teacher::STATUS_INACTIVE, $teacher->fresh()->status);
        $this->assertSame(User::STATUS_INACTIVE, $teacher->user->fresh()->status);

        auth()->logout();
        $this->post(route('login.store'), ['email' => $email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_institute_admin_can_delete_own_teacher(): void
    {
        $admin = $this->user('admin.a@example.com');
        $teacher = $this->teacherOf('EMP-A-001');
        $userId = $teacher->user_id;

        $this->actingAs($admin)
            ->delete(route('teachers.destroy', $teacher))
            ->assertRedirect(route('teachers.index'));

        $this->assertDatabaseMissing('teachers', ['id' => $teacher->id]);
        $this->assertDatabaseMissing('users', ['id' => $userId]);
        $this->assertDatabaseMissing('model_has_roles', ['model_id' => $userId]);
    }
}