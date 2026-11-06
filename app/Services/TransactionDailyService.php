<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TransactionDaily;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TransactionDailyService
{
    /**
     * Get paginated and filtered transactionDailies list.
     *
     * @param array<string, mixed> $filters
     */
  public function getPaginatedList(array $filters): LengthAwarePaginator
{
    $query = TransactionDaily::query()->with('company');

    // 1. Filter Pencarian (Search)
    if (!empty($filters['search'])) {
        $search = trim($filters['search']);
        $query->whereHas('company', function ($q) use ($search): void {
            $q->where('company_code', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%");
        });
    }

    // 2. Filter Start Date dan End Date
    if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
        // Menggunakan whereBetween untuk rentang tanggal
        $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
    } elseif (!empty($filters['start_date'])) {
        // Jika hanya start date yang diisi
        $query->whereDate('date', '>=', $filters['start_date']);
    } elseif (!empty($filters['end_date'])) {
        // Jika hanya end date yang diisi
        $query->whereDate('date', '<=', $filters['end_date']);
    }

    // 3. Pagination & Ordering
    return $query->orderBy('date', 'desc')->paginate(10);
}

  
    public function getFormattedPaginatedList(array $filters): array
    {
        $transactionDailies = $this->getPaginatedList($filters);

        return [
            'status' => 'success',
            'data' => $transactionDailies->items(),
            'pagination' => [
                'current_page' => $transactionDailies->currentPage(),
                'last_page' => $transactionDailies->lastPage(),
                'per_page' => $transactionDailies->perPage(),
                'total' => $transactionDailies->total(),
                'next_page_url' => $transactionDailies->nextPageUrl(),
                'prev_page_url' => $transactionDailies->previousPageUrl(),
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