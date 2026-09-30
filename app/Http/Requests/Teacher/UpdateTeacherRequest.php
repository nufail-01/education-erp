<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('teacher')) ?? false;
    }

    public function rules(): array
    {
        $teacher = $this->route('teacher');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($teacher->user_id)],
            'mobile_no' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'employee_code' => [
                'required', 'string', 'max:50',
                Rule::unique('teachers', 'employee_code')
                    ->where('institute_id', $teacher->institute_id)
                    ->ignore($teacher->id),
            ],
            'qualification' => ['nullable', 'string', 'max:255'],
            'joining_date' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }
}