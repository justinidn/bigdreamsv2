<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class UserPermissionService
{
    /**
     * Get paginated existing users and their direct web permissions.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedList(array $filters): LengthAwarePaginator
    {
        $query = User::query()->with([
            'permissions' => static fn ($query) => $query
                ->where('guard_name', 'web')
                ->where('name', '!=', 'system.login')
                ->orderBy('name'),
        ]);

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nrk', 'like', "%{$search}%");
            });
        }

        $users = $query->orderByDesc('id')->paginate(10);
        $users->getCollection()->transform(function (User $user): User {
            $user->setAttribute('permission_names', $user->permissions->pluck('name')->values());

            return $user;
        });

        return $users;
    }

    /**
     * Get permissions grouped by their module prefix.
     *
     * @return array<int, array{key: string, label: string, permissions: array<int, array{id: int, name: string, label: string}>}>
     */
    public function getPermissionModules(): array
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->where('name', '!=', 'system.login')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->groupBy(fn (Permission $permission): string => Str::before($permission->name, '.'))
            ->map(function ($permissions, string $module): array {
                return [
                    'key' => $module,
                    'label' => Str::of($module)->replace(['-', '_'], ' ')->title()->toString(),
                    'permissions' => $permissions
                        ->map(fn (Permission $permission): array => [
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
     * Return a user's direct web permissions for the edit form.
     *
     * @return array{id: int, name: string, email: string, nrk: ?string, permission_names: array<int, string>}
     */
    public function getUserPermissionData(User $user): array
    {
        $user->load([
            'permissions' => static fn ($query) => $query
                ->where('guard_name', 'web')
                ->where('name', '!=', 'system.login')
                ->orderBy('name'),
        ]);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'nrk' => $user->nrk,
            'permission_names' => $user->permissions->pluck('name')->values()->all(),
        ];
    }

    /**
     * Replace the user's direct web permissions managed by this page.
     *
     * @param  array<int, string>  $permissionNames
     */
    public function syncPermissions(User $user, array $permissionNames): User
    {
        return DB::transaction(function () use ($user, $permissionNames): User {
            $currentPermissions = $user->permissions()->get();
            $preservedPermissions = $currentPermissions->filter(
                static fn (Permission $permission): bool => $permission->guard_name !== 'web'
                    || $permission->name === 'system.login'
            );
            $selectedPermissions = Permission::query()
                ->where('guard_name', 'web')
                ->where('name', '!=', 'system.login')
                ->whereIn('name', $permissionNames)
                ->get();

            $user->syncPermissions($preservedPermissions->merge($selectedPermissions)->all());

            return $user->fresh();
        });
    }
}
