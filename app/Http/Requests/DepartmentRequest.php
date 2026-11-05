<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $department = $this->route('department');
        $departmentId = $department?->id;
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');

        return [
            'department_name' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:100',
                Rule::unique('departments', 'department_name')->ignore($departmentId),
            ],
            'department_alias' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:100',
                Rule::unique('departments', 'department_alias')->ignore($departmentId),
            ],
            'is_active' => ['sometimes', 'boolean'],
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
            'department_name' => 'Department Name',
            'department_alias' => 'Department Alias',
            'is_active' => 'Active Status',
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
