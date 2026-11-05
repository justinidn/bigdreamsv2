<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Role;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleService
{
    /**
     * Get paginated and filtered roles list.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedList(array $filters): LengthAwarePaginator
    {
        $query = Role::query();
        // Uncomment the line below if you want to eager load the 'department' relationship
        // $query = Role::query()->with('relasi');

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%");
                // Tambahkan relasi/kolom pencarian lain jika diperlukan, contoh:
                // ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', (bool) $filters['status']);
        }

        return $query->latest()->orderBy('id', 'desc')->paginate(10);
    }

    /**
     * Store a newly created role in storage.
     *
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): Role
    {
        return DB::transaction(function () use ($data): Role {
            $permissions = $data['permissions'] ?? [];
            unset($data['permissions']);

            $data['guard_name'] = 'web';
            $role = Role::create($data);

            $this->syncPermissions($role, $permissions);

            return $role;
        });
    }

    /**
     * Update the specified role in storage.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data): Role {
            $permissions = $data['permissions'] ?? null;
            unset($data['permissions']);

            $role->update($data);

            if ($permissions !== null) {
                $this->syncPermissions($role, $permissions);
            }

            return $role->fresh();
        });
    }

    /**
     * Get permissions grouped by their module prefix.
     *
     * @return array<int, array{key: string, label: string, permissions: array<int, array{name: string, label: string}>}>
     */
    public function getPermissionModules(): array
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->where('name', '!=', 'system.login')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->groupBy(fn(Permission $permission): string => Str::before($permission->name, '.'))
            ->map(function ($permissions, string $module): array {
                return [
                    'key' => $module,
                    'label' => Str::of($module)->replace(['-', '_'], ' ')->title()->toString(),
                    'permissions' => $permissions
                        ->map(fn(Permission $permission): array => [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'label' => Str::of(Str::after($permission->name, '.'))
                                ->replace(['-', '_', '.'], ' ')
                                ->title()
                                ->toString(),
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Get permission names assigned to the role.
     *
     * @return array<int, string>
     */
    public function getEnabledPermissions(Role $role): array
    {
        return DB::table('role_has_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->where('role_has_permissions.role_id', $role->id)
            ->where('permissions.guard_name', 'web')
            ->where('permissions.name', '!=', 'system.login')
            ->orderBy('permissions.name')
            ->pluck('permissions.name')
            ->all();
    }

    /**
     * Sync permission assignments submitted by the form.
     *
     * @param  array<int, string>  $selectedPermissions
     */
    private function syncPermissions(Role $role, array $selectedPermissions): void
    {
        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->where('name', '!=', 'system.login')
            ->get(['id', 'name']);
        $managedPermissionIds = $permissions->pluck('id')->all();
        $selectedPermissionIds = $permissions
            ->whereIn('name', $selectedPermissions)
            ->pluck('id')
            ->all();

        if ($managedPermissionIds === []) {
            return;
        }

        DB::table('role_has_permissions')
            ->where('role_id', $role->id)
            ->whereIn('permission_id', $managedPermissionIds)
            ->delete();

        if ($selectedPermissionIds !== []) {
            DB::table('role_has_permissions')->insert(
                collect($selectedPermissionIds)
                    ->unique()
                    ->map(fn(int $permissionId): array => [
                        'role_id' => $role->id,
                        'permission_id' => $permissionId,
                    ])
                    ->all()
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Remove the specified role from storage.
     *
     * @throws Exception
     */
    public function delete(Role $role): bool
    {
        // Contoh validasi relasi sebelum dihapus (sesuaikan dengan relasi model Anda jika ada)
        if ($role->someRelation()->exists()) {
            throw new Exception('Data cannot be deleted because it is still related to other data.');
        }

        return DB::transaction(function () use ($role): bool {
            return (bool) $role->delete();
        });
    }

    /**
     * Get formatted paginated list for AJAX/Axios responses.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getFormattedPaginatedList(array $filters): array
    {
        $roles = $this->getPaginatedList($filters);

        return [
            'status' => 'success',
            'data' => $roles->items(),
            'pagination' => [
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
                'next_page_url' => $roles->nextPageUrl(),
                'prev_page_url' => $roles->previousPageUrl(),
            ],
        ];
    }
    /**
     * Get department options for dropdown.
     *
     * @return array<int, string>
     */
    // public function getDepartmentDropdown(): array
    // {
    //   return Department::getDropdownOptions();
    // }
}
