<?php

namespace App\Http\Requests\InstituteAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreInstituteAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('institute-admins.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'institute_id' => ['required', 'integer', 'exists:institutes,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'mobile_no' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }
}