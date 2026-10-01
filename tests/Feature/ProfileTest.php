<?php

namespace Tests\Feature;

use App\Models\Institute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
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

    public function test_guest_is_redirected(): void
    {
        $this->get(route('profile.show'))->assertRedirect(route('login'));
        $this->put(route('profile.update'), [])->assertRedirect(route('login'));
        $this->put(route('profile.password'), [])->assertRedirect(route('login'));
    }

    public function test_every_role_can_view_profile(): void
    {
        foreach (['superadmin@example.com', 'admin.a@example.com', 'teacher1.a@example.com'] as $email) {
            $this->actingAs($this->user($email))
                ->get(route('profile.show'))
                ->assertOk()
                ->assertSee($email);
        }
    }

    public function test_user_can_update_own_profile(): void
    {
        $user = $this->user('teacher1.a@example.com');

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Updated Name',
                'email' => 'updated.teacher@example.com',
                'mobile_no' => '9999999999',
            ])
            ->assertRedirect(route('profile.show'));

        $fresh = $user->fresh();
        $this->assertSame('Updated Name', $fresh->name);
        $this->assertSame('updated.teacher@example.com', $fresh->email);
        $this->assertDatabaseHas('activity_logs', ['action' => 'profile.updated']);
    }

    public function test_duplicate_email_and_invalid_input_rejected(): void
    {
        $user = $this->user('teacher1.a@example.com');

        $this->actingAs($user)
            ->put(route('profile.update'), ['name' => 'X', 'email' => 'admin.a@example.com'])
            ->assertSessionHasErrors('email');

        $this->actingAs($user)
            ->put(route('profile.update'), ['name' => '', 'email' => 'bad', 'mobile_no' => 'abc'])
            ->assertSessionHasErrors(['name', 'email', 'mobile_no']);
    }

    public function test_keeping_own_email_is_allowed(): void
    {
        $user = $this->user('teacher1.a@example.com');

        $this->actingAs($user)
            ->put(route('profile.update'), ['name' => 'Same Email', 'email' => $user->email])
            ->assertSessionHasNoErrors();
    }

    public function test_institute_status_and_roles_cannot_be_changed_via_profile(): void
    {
        $user = $this->user('teacher1.a@example.com');
        $originalInstitute = $user->institute_id;
        $other = Institute::where('code', 'DEMO-B')->value('id');

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Sneaky',
            'email' => $user->email,
            'institute_id' => $other,
            'status' => 'inactive',
            'role' => 'Super Admin',
        ]);

        $fresh = $user->fresh();
        $this->assertSame($originalInstitute, $fresh->institute_id);
        $this->assertSame('active', $fresh->status);
        $this->assertFalse($fresh->isSuperAdmin());
    }

    public function test_user_cannot_edit_another_users_profile(): void
    {
        $actor = $this->user('teacher1.a@example.com');
        $victim = $this->user('teacher2.a@example.com');

        $this->actingAs($actor)->put(route('profile.update'), [
            'id' => $victim->id,
            'name' => 'Hijacked',
            'email' => $actor->email,
        ]);

        $this->assertNotSame('Hijacked', $victim->fresh()->name);
    }

    public function test_password_can_be_changed_with_correct_current_password(): void
    {
        $user = $this->user('teacher1.a@example.com');

        $this->actingAs($user)
            ->put(route('profile.password'), [
                'current_password' => 'password',
                'password' => 'NewSecret123!',
                'password_confirmation' => 'NewSecret123!',
            ])
            ->assertRedirect(route('profile.show'));

        $fresh = $user->fresh();
        $this->assertTrue(Hash::check('NewSecret123!', $fresh->password));
        $this->assertNotSame('NewSecret123!', $fresh->password);
        $this->assertDatabaseHas('activity_logs', ['action' => 'profile.password-changed']);
    }

    public function test_wrong_current_password_weak_or_unconfirmed_password_rejected(): void
    {
        $user = $this->user('teacher1.a@example.com');

        $this->actingAs($user)
            ->put(route('profile.password'), [
                'current_password' => 'wrong',
                'password' => 'NewSecret123!',
                'password_confirmation' => 'NewSecret123!',
            ])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($user)
            ->put(route('profile.password'), [
                'current_password' => 'password',
                'password' => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertSessionHasErrors('password');

        $this->actingAs($user)
            ->put(route('profile.password'), [
                'current_password' => 'password',
                'password' => 'NewSecret123!',
                'password_confirmation' => 'Different123!',
            ])
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_new_password_must_differ_from_current(): void
    {
        $this->actingAs($this->user('teacher1.a@example.com'))
            ->put(route('profile.password'), [
                'current_password' => 'password',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertSessionHasErrors('password');
    }
}