<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class UserPermissionRequest extends FormRequest
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
        $permissionNames = Permission::query()
            ->where('guard_name', 'web')
            ->where('name', '!=', 'system.login')
            ->pluck('name')
            ->all();

        return [
            'permissions' => ['present', 'array'],
            'permissions.*' => [
                'required',
                'string',
                'distinct',
                Rule::in($permissionNames),
            ],
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
            'permissions' => 'Permissions',
            'permissions.*' => 'Permission',
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
            'in' => 'The selected :attribute is invalid.',
        ];
    }
}
