<?php

namespace App\Http\Requests\Teacher;

use App\Models\Teacher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Teacher::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'mobile_no' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'employee_code' => [
                'required', 'string', 'max:50',
                Rule::unique('teachers', 'employee_code')->where('institute_id', $this->user()->institute_id),
            ],
            'qualification' => ['nullable', 'string', 'max:255'],
            'joining_date' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }
}