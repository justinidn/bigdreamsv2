<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');
        $userId = $user?->id;
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');

        return [
            'department_id' => ['required', 'exists:departments,id'],
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255', Rule::unique('users', 'name')->ignore($userId)],
            'email' => [$isUpdate ? 'sometimes' : 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'nrk' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255', Rule::unique('users', 'nrk')->ignore($userId)],
            'password' => [$isUpdate ? 'nullable' : 'required', 'string', 'min:7', 'confirmed'],
            'is_active' => ['sometimes', 'boolean'],
            'can_login' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'is_active' => 'Active Status',
            'can_login' => 'Login Access',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'unique' => 'The :attribute has already been taken.',
            'max' => 'The :attribute must not be greater than :max characters.',
        ];
    }
}
