<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Department;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DepartmentService
{
    /**
     * Get paginated and filtered departments list.
     *
     * @param array<string, mixed> $filters
     */
    public function getPaginatedList(array $filters): LengthAwarePaginator
    {
        $query = Department::query();

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function ($q) use ($search): void {
                $q->where('department_name', 'like', "%{$search}%")
                    ->orWhere('department_alias', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', (bool) $filters['status']);
        }

        return $query->latest()->orderBy('id', 'desc')->paginate(10);
    }

    /**
     * Store a newly created department in storage.
     *
     * @param array<string, mixed> $data
     */
    public function store(array $data): Department
    {
        return DB::transaction(function () use ($data): Department {
            return Department::create($data);
        });
    }

    /**
     * Update the specified department in storage.
     *
     * @param array<string, mixed> $data
     */
    public function update(Department $department, array $data): Department
    {
        return DB::transaction(function () use ($department, $data): Department {
            $department->update($data);

            return $department->fresh();
        });
    }

    /**
     * Remove the specified department from storage.
     *
     * @throws Exception
     */
    public function delete(Department $department): bool
    {
        if ($department->users()->exists()) {
            throw new Exception('Data cannot be deleted because it is still assigned to active users.');
        }

        return DB::transaction(function () use ($department): bool {
            return (bool) $department->delete();
        });
    }

    /**
     * Get formatted paginated list for AJAX/Axios responses.
     *
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function getFormattedPaginatedList(array $filters): array
    {
        $departments = $this->getPaginatedList($filters);

        return [
            'status' => 'success',
            'data' => $departments->items(),
            'pagination' => [
                'current_page' => $departments->currentPage(),
                'last_page' => $departments->lastPage(),
                'per_page' => $departments->perPage(),
                'total' => $departments->total(),
                'next_page_url' => $departments->nextPageUrl(),
                'prev_page_url' => $departments->previousPageUrl(),
            ],
        ];
    }
}
