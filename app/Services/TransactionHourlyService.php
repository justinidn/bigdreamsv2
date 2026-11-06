<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TransactionHourly;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TransactionHourlyService
{
    /**
     * Get paginated and filtered transactionHourlies list.
     *
     * @param array<string, mixed> $filters
     */
   public function getPaginatedList(array $filters): LengthAwarePaginator
{
    // 1. Mulai query dan eager load relasi 'company'
    $query = TransactionHourly::query()->with('company');

    if (! empty($filters['search'])) {
        $search = trim($filters['search']);

        // 2. Gunakan whereHas untuk mencari di dalam tabel relasi
        $query->whereHas('company', function ($q) use ($search): void {
            $q->where('company_code', '=', "{$search}");
            
            // Jika ingin mencari di kolom lain pada tabel company juga, tambahkan di sini:
            // ->orWhere('company_name', 'like', "%{$search}%");
        });

        // 3. (Opsional) Jika Anda juga ingin mencari di kolom tabel utama (TransactionHourly)
        // $query->orWhere('nama_kolom_di_transaction', 'like', "%{$search}%");
    }

    // 4. Pastikan mengurutkan berdasarkan tabel utama untuk menghindari ambiguitas
    return $query->orderBy('timestamp', 'desc')->paginate(10);
}

    public function getFormattedPaginatedList(array $filters): array
    {
        $transactionHourlies = $this->getPaginatedList($filters);

        return [
            'status' => 'success',
            'data' => $transactionHourlies->items(),
            'pagination' => [
                'current_page' => $transactionHourlies->currentPage(),
                'last_page' => $transactionHourlies->lastPage(),
                'per_page' => $transactionHourlies->perPage(),
                'total' => $transactionHourlies->total(),
                'next_page_url' => $transactionHourlies->nextPageUrl(),
                'prev_page_url' => $transactionHourlies->previousPageUrl(),
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