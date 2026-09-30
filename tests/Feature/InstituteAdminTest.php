<?php

namespace Tests\Feature;

use App\Models\Institute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InstituteAdminTest extends TestCase
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
        $institute = Institute::where('code', 'DEMO-A')->firstOrFail();

        return array_merge([
            'institute_id' => $institute->id,
            'name' => 'New Admin',
            'email' => 'new.admin@example.com',
            'mobile_no' => '9876543210',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ], $overrides);
    }

    public function test_super_admin_can_create_institute_admin(): void
    {
        $this->actingAs($this->user('superadmin@example.com'))
            ->post(route('institute-admins.store'), $this->data())
            ->assertRedirect(route('institute-admins.index'));

        $admin = User::where('email', 'new.admin@example.com')->firstOrFail();
        $role = Role::where('name', User::ROLE_INSTITUTE_ADMIN)->whereNull('institute_id')->firstOrFail();

        $this->assertSame(Institute::where('code', 'DEMO-A')->value('id'), $admin->institute_id);
        $this->assertNotSame('Secret123!', $admin->password);
        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $role->id,
            'model_id' => $admin->id,
            'institute_id' => $admin->institute_id,
        ]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'institute-admin.created']);
    }

    public function test_new_admin_can_login(): void
    {
        $this->actingAs($this->user('superadmin@example.com'))
            ->post(route('institute-admins.store'), $this->data());
        auth()->logout();

        $this->post(route('login.store'), [
            'email' => 'new.admin@example.com',
            'password' => 'Secret123!',
        ])->assertRedirect(route('pages-home'));

        $this->assertAuthenticated();
    }

    public function test_institute_admin_and_teacher_get_403(): void
    {
        $target = $this->user('admin.b@example.com');

        foreach (['admin.a@example.com', 'teacher1.a@example.com'] as $email) {
            $u = $this->user($email);
            $this->actingAs($u)->get(route('institute-admins.index'))->assertForbidden();
            $this->actingAs($u)->get(route('institute-admins.create'))->assertForbidden();
            $this->actingAs($u)->get(route('institute-admins.edit', $target))->assertForbidden();
        }
    }

    public function test_direct_post_bypass_fails(): void
    {
        $admin = $this->user('admin.a@example.com');

        $this->actingAs($admin)
            ->post(route('institute-admins.store'), $this->data())
            ->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'new.admin@example.com']);

        $target = $this->user('admin.b@example.com');

        $this->actingAs($admin)
            ->put(route('institute-admins.update', $target), ['name' => 'Hacked', 'email' => $target->email])
            ->assertForbidden();
        $this->actingAs($admin)
            ->patch(route('institute-admins.toggle-status', $target))
            ->assertForbidden();

        $this->assertSame('active', $target->fresh()->status);
        $this->assertNotSame('Hacked', $target->fresh()->name);
    }

    public function test_duplicate_email_weak_password_and_bad_institute_rejected(): void
    {
        $sa = $this->user('superadmin@example.com');

        $this->actingAs($sa)
            ->post(route('institute-admins.store'), $this->data(['email' => 'admin.b@example.com']))
            ->assertSessionHasErrors('email');

        $this->actingAs($sa)
            ->post(route('institute-admins.store'), $this->data(['password' => 'short', 'password_confirmation' => 'short']))
            ->assertSessionHasErrors('password');

        $this->actingAs($sa)
            ->post(route('institute-admins.store'), $this->data(['institute_id' => 99999]))
            ->assertSessionHasErrors('institute_id');
    }

    public function test_super_admin_can_update_and_toggle_status(): void
    {
        $sa = $this->user('superadmin@example.com');
        $target = $this->user('admin.a@example.com');

        $this->actingAs($sa)
            ->put(route('institute-admins.update', $target), ['name' => 'Renamed', 'email' => $target->email])
            ->assertRedirect(route('institute-admins.index'));
        $this->assertSame('Renamed', $target->fresh()->name);

        $this->actingAs($sa)->patch(route('institute-admins.toggle-status', $target));
        $this->assertSame('inactive', $target->fresh()->status);
    }

    public function test_update_cannot_move_admin_to_other_institute(): void
    {
        $sa = $this->user('superadmin@example.com');
        $target = $this->user('admin.a@example.com');
        $original = $target->institute_id;
        $other = Institute::where('code', 'DEMO-B')->value('id');

        $this->actingAs($sa)->put(route('institute-admins.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'institute_id' => $other,
        ]);

        $this->assertSame($original, $target->fresh()->institute_id);
    }

    public function test_non_admin_user_cannot_be_managed_via_this_route(): void
    {
        $teacher = $this->user('teacher1.a@example.com');

        $this->actingAs($this->user('superadmin@example.com'))
            ->get(route('institute-admins.edit', $teacher))
            ->assertNotFound();
    }

    public function test_deactivated_admin_cannot_login(): void
    {
        $sa = $this->user('superadmin@example.com');
        $target = $this->user('admin.a@example.com');

        $this->actingAs($sa)->patch(route('institute-admins.toggle-status', $target));
        auth()->logout();

        $this->post(route('login.store'), ['email' => $target->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}