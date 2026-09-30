<?php

namespace App\Http\Requests\InstituteAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateInstituteAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('institute-admins.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('admin'))],
            'mobile_no' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ];
    }
}