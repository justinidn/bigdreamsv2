<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MCompany;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MCompanyService
{
    /**
     * Get paginated and filtered mCompanies list.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedList(array $filters): LengthAwarePaginator
    {
        $query = MCompany::query();
        // Uncomment the line below if you want to eager load the 'department' relationship
        // $query = MCompany::query()->with('relasi');

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
     * Store a newly created mCompany in storage.
     *
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): MCompany
    {
        return DB::transaction(function () use ($data): MCompany {
            return MCompany::create($data);
        });
    }

    /**
     * Update the specified mCompany in storage.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(MCompany $mCompany, array $data): MCompany
    {
        return DB::transaction(function () use ($mCompany, $data): MCompany {
            $mCompany->update($data);

            return $mCompany->fresh();
        });
    }

    /**
     * Remove the specified mCompany from storage.
     *
     * @throws Exception
     */
    public function delete(MCompany $mCompany): bool
    {
        // Contoh validasi relasi sebelum dihapus (sesuaikan dengan relasi model Anda jika ada)
        // if ($mCompany->someRelation()->exists()) {
        //     throw new Exception('Data cannot be deleted because it is still related to other data.');
        // }

        return DB::transaction(function () use ($mCompany): bool {
            return (bool) $mCompany->delete();
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
        $mCompanies = $this->getPaginatedList($filters);

        return [
            'status' => 'success',
            'data' => $mCompanies->items(),
            'pagination' => [
                'current_page' => $mCompanies->currentPage(),
                'last_page' => $mCompanies->lastPage(),
                'per_page' => $mCompanies->perPage(),
                'total' => $mCompanies->total(),
                'next_page_url' => $mCompanies->nextPageUrl(),
                'prev_page_url' => $mCompanies->previousPageUrl(),
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
