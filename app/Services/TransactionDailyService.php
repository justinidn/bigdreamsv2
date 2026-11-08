<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TransactionDaily;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransactionDailyService
{
    /**
     * Get paginated and filtered transactionDailies list.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedList(array $filters): LengthAwarePaginator
    {
        $query = TransactionDaily::query()->with('company');

        // 1. Filter Pencarian (Search)
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->whereHas('company', function ($q) use ($search): void {
                $q->where('company_code', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        // 2. Filter Start Date dan End Date (default: tanggal data terakhir / hari ini)
        $hasStart = ! empty($filters['start_date']);
        $hasEnd   = ! empty($filters['end_date']);

        if (! $hasStart && ! $hasEnd) {
            // Tidak ada filter tanggal -> pakai tanggal data terakhir
            $latestDate = TransactionDaily::max('date');

            $defaultDate = $latestDate
                ? Carbon::parse($latestDate)->toDateString()
                : Carbon::today()->toDateString(); // fallback jika tabel kosong

            $query->whereDate('date', $defaultDate);
        } elseif ($hasStart && $hasEnd) {
            $query->whereBetween('date', [
                Carbon::parse($filters['start_date'])->startOfDay(),
                Carbon::parse($filters['end_date'])->endOfDay(),
            ]);
        } elseif ($hasStart) {
            $query->whereDate('date', '>=', $filters['start_date']);
        } else {
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
    // {
    //   return Department::getDropdownOptions();
    // }
}
