<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TransactionHourly;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransactionHourlyService
{
    /**
     * Get paginated and filtered transactionHourlies list.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedList(array $filters): LengthAwarePaginator
    {
        // 1. Mulai query dan eager load relasi 'company'
        $query = TransactionHourly::query()->with('company');

        // 2. Filter Pencarian (Search)
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->whereHas('company', function ($q) use ($search): void {
                $q->where('company_code', '=', $search);
            });
        }

        // 3. Filter Start Date dan End Date (default: hari data terakhir / hari ini)
        $hasStart = ! empty($filters['start_date']);
        $hasEnd   = ! empty($filters['end_date']);

        if (! $hasStart && ! $hasEnd) {
            // Tidak ada filter tanggal -> pakai hari dari data terakhir
            $latestTimestamp = TransactionHourly::max('timestamp');

            $defaultDay = $latestTimestamp
                ? Carbon::parse($latestTimestamp)
                : Carbon::today(); // fallback jika tabel kosong

            $query->whereBetween('timestamp', [
                $defaultDay->copy()->startOfDay(),
                $defaultDay->copy()->endOfDay(),
            ]);
        } elseif ($hasStart && $hasEnd) {
            $query->whereBetween('timestamp', [
                Carbon::parse($filters['start_date'])->startOfDay(),
                Carbon::parse($filters['end_date'])->endOfDay(),
            ]);
        } elseif ($hasStart) {
            $query->where('timestamp', '>=', Carbon::parse($filters['start_date'])->startOfDay());
        } else {
            $query->where('timestamp', '<=', Carbon::parse($filters['end_date'])->endOfDay());
        }

        // 4. Urutkan berdasarkan timestamp terbaru
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
    // {
    //   return Department::getDropdownOptions();
    // }
}
