<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Institute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InstituteTest extends TestCase
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

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'New Test School',
            'code' => 'NEW-01',
            'email' => 'new@example.com',
            'phone' => '9876543210',
            'address' => 'Test address',
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('institutes.index'))->assertRedirect(route('login'));
    }

    public function test_super_admin_can_view_institute_list(): void
    {
        $this->actingAs($this->user('superadmin@example.com'))
            ->get(route('institutes.index'))
            ->assertOk()
            ->assertSee('Demo Public School');
    }

    public function test_super_admin_can_create_institute(): void
    {
        $this->actingAs($this->user('superadmin@example.com'))
            ->post(route('institutes.store'), $this->validData())
            ->assertRedirect();

        $institute = Institute::where('code', 'NEW-01')->first();

        $this->assertNotNull($institute);
        $this->assertSame(Institute::STATUS_ACTIVE, $institute->status);
        $this->assertTrue(
            ActivityLog::where('action', 'institute.created')->where('subject_id', $institute->id)->exists()
        );
    }

    public function test_super_admin_can_update_institute(): void
    {
        $institute = Institute::where('code', 'DEMO-A')->firstOrFail();

        $this->actingAs($this->user('superadmin@example.com'))
            ->put(route('institutes.update', $institute), $this->validData(['code' => 'DEMO-A']))
            ->assertRedirect(route('institutes.show', $institute));

        $this->assertSame('New Test School', $institute->fresh()->name);
    }

    public function test_super_admin_can_deactivate_and_activate_institute(): void
    {
        $institute = Institute::where('code', 'DEMO-A')->firstOrFail();
        $superAdmin = $this->user('superadmin@example.com');

        $this->actingAs($superAdmin)->patch(route('institutes.toggle-status', $institute));
        $this->assertSame(Institute::STATUS_INACTIVE, $institute->fresh()->status);

        $this->actingAs($superAdmin)->patch(route('institutes.toggle-status', $institute));
        $this->assertSame(Institute::STATUS_ACTIVE, $institute->fresh()->status);
    }

    public function test_institute_admin_cannot_access_institutes(): void
    {
        $admin = $this->user('admin.a@example.com');
        $institute = Institute::where('code', 'DEMO-B')->firstOrFail();

        $this->actingAs($admin)->get(route('institutes.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('institutes.show', $institute))->assertForbidden();
        $this->actingAs($admin)->get(route('institutes.create'))->assertForbidden();
        $this->actingAs($admin)->get(route('institutes.edit', $institute))->assertForbidden();
    }

    public function test_teacher_cannot_access_institutes(): void
    {
        $this->actingAs($this->user('teacher1.a@example.com'))
            ->get(route('institutes.index'))
            ->assertForbidden();
    }

    public function test_direct_post_bypass_by_institute_admin_fails(): void
    {
        $admin = $this->user('admin.a@example.com');
        $other = Institute::where('code', 'DEMO-B')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('institutes.store'), $this->validData())
            ->assertForbidden();
        $this->assertDatabaseMissing('institutes', ['code' => 'NEW-01']);

        $this->actingAs($admin)
            ->put(route('institutes.update', $other), $this->validData(['code' => 'DEMO-B']))
            ->assertForbidden();
        $this->assertNotSame('New Test School', $other->fresh()->name);

        $this->actingAs($admin)
            ->patch(route('institutes.toggle-status', $other))
            ->assertForbidden();
        $this->assertSame(Institute::STATUS_ACTIVE, $other->fresh()->status);
    }

    public function test_direct_post_bypass_by_teacher_fails(): void
    {
        $this->actingAs($this->user('teacher1.a@example.com'))
            ->post(route('institutes.store'), $this->validData())
            ->assertForbidden();

        $this->assertDatabaseMissing('institutes', ['code' => 'NEW-01']);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->user('superadmin@example.com'))
            ->post(route('institutes.store'), [])
            ->assertSessionHasErrors(['name', 'code']);
    }

    public function test_code_must_be_unique(): void
    {
        $this->actingAs($this->user('superadmin@example.com'))
            ->post(route('institutes.store'), $this->validData(['code' => 'DEMO-A']))
            ->assertSessionHasErrors('code');
    }

    public function test_update_allows_keeping_own_code_but_not_anothers(): void
    {
        $institute = Institute::where('code', 'DEMO-A')->firstOrFail();
        $superAdmin = $this->user('superadmin@example.com');

        $this->actingAs($superAdmin)
            ->put(route('institutes.update', $institute), $this->validData(['code' => 'DEMO-A']))
            ->assertSessionHasNoErrors();

        $this->actingAs($superAdmin)
            ->put(route('institutes.update', $institute), $this->validData(['code' => 'DEMO-B']))
            ->assertSessionHasErrors('code');
    }

    public function test_email_and_phone_are_validated(): void
    {
        $this->actingAs($this->user('superadmin@example.com'))
            ->post(route('institutes.store'), $this->validData(['email' => 'not-an-email', 'phone' => 'abc']))
            ->assertSessionHasErrors(['email', 'phone']);
    }

    public function test_logo_is_uploaded_and_validated(): void
    {
        Storage::fake('public');
        $superAdmin = $this->user('superadmin@example.com');

        $this->actingAs($superAdmin)
            ->post(route('institutes.store'), $this->validData([
                'logo' => UploadedFile::fake()->image('logo.png', 100, 100),
            ]))
            ->assertSessionHasNoErrors();

        $institute = Institute::where('code', 'NEW-01')->firstOrFail();
        Storage::disk('public')->assertExists($institute->logo);

        $this->actingAs($superAdmin)
            ->post(route('institutes.store'), $this->validData([
                'code' => 'NEW-02',
                'logo' => UploadedFile::fake()->create('logo.pdf', 100, 'application/pdf'),
            ]))
            ->assertSessionHasErrors('logo');
    }

    public function test_list_search_filters_results(): void
    {
        Institute::factory()->create(['name' => 'Zebra Unique School', 'code' => 'ZEB-01']);

        $this->actingAs($this->user('superadmin@example.com'))
            ->get(route('institutes.index', ['search' => 'Zebra']))
            ->assertOk()
            ->assertSee('Zebra Unique School')
            ->assertDontSee('Demo Public School');
    }

    public function test_list_status_filter_works(): void
    {
        $this->actingAs($this->user('superadmin@example.com'))
            ->get(route('institutes.index', ['status' => 'inactive']))
            ->assertOk()
            ->assertSee('Demo Inactive Academy')
            ->assertDontSee('Demo Public School');
    }

    public function test_list_is_paginated(): void
    {
        Institute::factory()->count(12)->create();

        $response = $this->actingAs($this->user('superadmin@example.com'))
            ->get(route('institutes.index'))
            ->assertOk();

        $this->assertCount(10, $response->viewData('institutes')->items());
        $this->assertSame(15, $response->viewData('institutes')->total());
    }
}