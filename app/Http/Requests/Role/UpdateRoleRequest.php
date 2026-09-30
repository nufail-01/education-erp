<?php

namespace App\Http\Requests\Role;

use App\Models\User;
use App\Services\PrivilegeGuard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $role = $this->route('role');

        if ($user === null) {
            return false;
        }

        // Dusre institute ka role: 404
        if (! $user->isGlobalUser() && $role->institute_id !== $user->institute_id) {
            abort(404);
        }

        return $user->can('update', $role);
    }

    public function rules(): array
    {
        $role = $this->route('role');
        $allowed = app(PrivilegeGuard::class)->grantablePermissionNames($this->user());

        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::notIn([User::ROLE_SUPER_ADMIN, User::ROLE_INSTITUTE_ADMIN]),
                Rule::unique('roles', 'name')
                    ->where('institute_id', $role->institute_id)
                    ->where('guard_name', 'web')
                    ->ignore($role->id),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($allowed)],
        ];
    }
}