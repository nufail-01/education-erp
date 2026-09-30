<?php

namespace App\Http\Requests\Role;

use App\Models\User;
use App\Services\PrivilegeGuard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        // Global user (Super Admin) custom role nahi banata, protected roles hi uske paas hain
        return $user !== null && ! $user->isGlobalUser() && $user->can('create', Role::class);
    }

    public function rules(): array
    {
        $allowed = app(PrivilegeGuard::class)->grantablePermissionNames($this->user());

        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::notIn([User::ROLE_SUPER_ADMIN, User::ROLE_INSTITUTE_ADMIN]),
                Rule::unique('roles', 'name')
                    ->where('institute_id', $this->user()->institute_id)
                    ->where('guard_name', 'web'),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($allowed)],
        ];
    }
}