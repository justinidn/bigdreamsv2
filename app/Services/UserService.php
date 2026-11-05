<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Department;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class UserService
{
    /**
     * Get paginated and filtered users list.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedList(array $filters): LengthAwarePaginator
    {
        // $query = User::query();
        // Uncomment the line below if you want to eager load the 'department' relationship
        $query = User::query()
            ->with('department')
            ->withExists([
                'permissions as can_login' => static fn($query) => $query->where('name', 'system.login'),
            ]);

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    // Tambahkan relasi/kolom pencarian lain jika diperlukan, contoh:
                    ->orWhere('nrk', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', (bool) $filters['status']);
        }

        if (isset($filters['department_cari']) && $filters['department_cari'] !== '') {
            $query->where('department_id', $filters['department_cari']);
        }

        return $query->latest()->orderBy('id', 'desc')->paginate(10);
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $canLogin = (bool) ($data['can_login'] ?? false);
            unset($data['can_login']);

            $user = User::create($data);

            if ($canLogin) {
                $user->givePermissionTo('system.login');
            }

            return $user;
        });
    }

    /**
     * Update the specified user in storage.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $hasCanLogin = array_key_exists('can_login', $data);
            $canLogin = (bool) ($data['can_login'] ?? false);
            unset($data['can_login']);

            if (empty($data['password'])) {
                unset($data['password']);
            } else {

                $data['password'] = bcrypt($data['password']);
            }
            $user->update($data);

            if ($hasCanLogin) {
                if ($canLogin) {
                    $user->givePermissionTo('system.login');
                } else {
                    $user->revokePermissionTo('system.login');
                }
            }

            return $user->fresh();
        });
    }

    /**
     * Remove the specified user from storage.
     *
     * @throws Exception
     */
    public function delete(User $user): bool
    {
        // Contoh validasi relasi sebelum dihapus (sesuaikan dengan relasi model Anda jika ada)
        // if ($user->someRelation()->exists()) {
        //     throw new Exception('Data cannot be deleted because it is still related to other data.');
        // }

        return DB::transaction(function () use ($user): bool {
            return (bool) $user->delete();
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
        $users = $this->getPaginatedList($filters);

        return [
            'status' => 'success',
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'next_page_url' => $users->nextPageUrl(),
                'prev_page_url' => $users->previousPageUrl(),
            ],
        ];
    }

    /**
     * Get department options for dropdown.
     *
     * @return array<int, string>
     */
    public function getDepartmentDropdown(): array
    {
        return Department::getDropdownOptions();
    }
}
