<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserRoleService
{
    /**
     * Get paginated existing users and their assigned web roles.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedList(array $filters): LengthAwarePaginator
    {
        $query = User::query()->with([
            'roles' => static fn ($query) => $query
                ->where('guard_name', 'web')
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
            $user->setAttribute('role_ids', $user->roles->pluck('id')->values());

            return $user;
        });

        return $users;
    }

    /**
     * Return available web roles for assignment.
     *
     * @return array<int, array{id: int, name: string}>
     */
    public function getRoleOptions(): array
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(static fn (Role $role): array => [
                'id' => $role->id,
                'name' => $role->name,
            ])
            ->all();
    }

    /**
     * Return a user's current roles for the edit form.
     *
     * @return array{id: int, name: string, email: string, nrk: string, role_ids: array<int, int>}
     */
    public function getUserRoleData(User $user): array
    {
        $user->load([
            'roles' => static fn ($query) => $query
                ->where('guard_name', 'web')
                ->orderBy('name'),
        ]);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'nrk' => $user->nrk,
            'role_ids' => $user->roles->pluck('id')->map(static fn ($id): int => (int) $id)->all(),
        ];
    }

    /**
     * Replace the assigned web roles for an existing user.
     *
     * @param  array<int, int>  $roleIds
     */
    public function syncRoles(User $user, array $roleIds): User
    {
        return DB::transaction(function () use ($user, $roleIds): User {
            $roles = Role::query()
                ->where('guard_name', 'web')
                ->whereIn('id', $roleIds)
                ->get();

            $user->syncRoles($roles);

            return $user->fresh();
        });
    }
}
