<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\MasterMenu;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class MasterMenuRequest extends FormRequest
{
    /**
     * Normalize a module key while retaining its display label.
     */
    protected function prepareForValidation(): void
    {
        if ($this->isMethod('post') && is_string($this->input('name'))) {
            $this->merge([
                'name' => Str::slug(trim($this->input('name'))),
            ]);
        }

        $customPermissions = $this->input('custom_permissions');

        if (is_array($customPermissions)) {
            foreach ($customPermissions as $index => $permission) {
                if (is_array($permission) && is_string($permission['action'] ?? null)) {
                    $customPermissions[$index]['action'] = Str::lower(trim($permission['action']));
                }
            }

            $this->merge(['custom_permissions' => $customPermissions]);
        }
    }

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
        $isCreate = $this->isMethod('post');

        $rules = [
            'name' => $isCreate
                ? ['required', 'string', 'alpha_dash', 'max:50', Rule::unique('master_menus', 'name')]
                : ['prohibited'],
            'display_name' => $isCreate
                ? ['required', 'string', 'max:50']
                : ['sometimes', 'required', 'string', 'max:50'],
            'custom_permissions' => ['sometimes', 'array', 'max:50'],
            'custom_permissions.*' => ['required', 'array:action,enabled'],
            'custom_permissions.*.action' => [
                'required',
                'string',
                'alpha_dash',
                'max:50',
                'distinct',
                Rule::notIn(MasterMenu::PERMISSION_ACTIONS),
            ],
            'custom_permissions.*.enabled' => ['required', 'boolean'],
        ];

        foreach (MasterMenu::PERMISSION_ACTIONS as $action) {
            $rules["can_{$action}"] = ['sometimes', 'boolean'];
        }

        return $rules;
    }

    /**
     * Validate that a permission update includes at least one switch.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->isMethod('post') || $validator->errors()->isNotEmpty()) {
                    return;
                }

                foreach (MasterMenu::PERMISSION_ACTIONS as $action) {
                    if ($this->exists("can_{$action}")) {
                        return;
                    }
                }

                if ($this->exists('display_name') || $this->exists('custom_permissions')) {
                    return;
                }

                $validator->errors()->add('permissions', 'Pilih minimal satu switch permission.');
            },
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
            'name' => 'Module',
            'display_name' => 'Nama module',
            'custom_permissions.*.action' => 'Nama permission custom',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [
            'required' => 'The :attribute field is required.',
            'unique' => 'Module tersebut sudah terdaftar.',
            'max' => 'The :attribute must not be greater than :max characters.',
        ];

        foreach (MasterMenu::PERMISSION_ACTIONS as $action) {
            $messages["can_{$action}.boolean"] = "Hak akses {$action} harus bernilai benar atau salah.";
        }

        $messages['custom_permissions.*.action.alpha_dash'] = 'Nama permission custom hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.';
        $messages['custom_permissions.*.action.distinct'] = 'Nama permission custom tidak boleh duplikat.';
        $messages['custom_permissions.*.action.not_in'] = 'Nama permission custom tidak boleh memakai permission bawaan.';
        $messages['custom_permissions.*.enabled.boolean'] = 'Status permission custom harus bernilai benar atau salah.';

        return $messages;
    }
}
