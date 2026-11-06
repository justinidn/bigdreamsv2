<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Company;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CompanyService
{
    /**
     * Get paginated and filtered companies list.
     *
     * @param array<string, mixed> $filters
     */
    public function getPaginatedList(array $filters): LengthAwarePaginator
    {
        $query = Company::query();
        // Uncomment the line below if you want to eager load the 'department' relationship
        //$query = Company::query()->with('relasi');

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function ($q) use ($search): void {
                $q->where('company_name', 'like', "%{$search}%")
                    // Tambahkan relasi/kolom pencarian lain jika diperlukan, contoh:
                    ->orWhere('company_code', 'like', "%{$search}%");
            });
        }



        return $query->orderBy('id', 'desc')->paginate(10);
    }

    /**
     * Store a newly created company in storage.
     *
     * @param array<string, mixed> $data
     */
    public function store(array $data): Company
    {
        return DB::transaction(function () use ($data): Company {
            return Company::create($data);
        });
    }

    /**
     * Update the specified company in storage.
     *
     * @param array<string, mixed> $data
     */
    public function update(Company $company, array $data): Company
    {
        return DB::transaction(function () use ($company, $data): Company {
            $company->update($data);

            return $company->fresh();
        });
    }

    /**
     * Remove the specified company from storage.
     *
     * @throws Exception
     */
    public function delete(Company $company): bool
    {
        // Contoh validasi relasi sebelum dihapus (sesuaikan dengan relasi model Anda jika ada)
        // if ($company->someRelation()->exists()) {
        //     throw new Exception('Data cannot be deleted because it is still related to other data.');
        // }

        return DB::transaction(function () use ($company): bool {
            return (bool) $company->delete();
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
        $companies = $this->getPaginatedList($filters);

        return [
            'status' => 'success',
            'data' => $companies->items(),
            'pagination' => [
                'current_page' => $companies->currentPage(),
                'last_page' => $companies->lastPage(),
                'per_page' => $companies->perPage(),
                'total' => $companies->total(),
                'next_page_url' => $companies->nextPageUrl(),
                'prev_page_url' => $companies->previousPageUrl(),
            ],
        ];
    }
    /**
     * Get department options for dropdown.
     *
     * @return array<int, string>
     */
    // public function getDepartmentDropdown(): array
    //{
    //   return Department::getDropdownOptions();
    //}
}
