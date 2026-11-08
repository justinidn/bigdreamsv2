<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Screener;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ScreenerService
{
    /**
     * Get paginated and filtered screeners list.
     *
     * @param array<string, mixed> $filters
     */
    public function getPaginatedList(array $filters): LengthAwarePaginator
    {
        //$query = Screener::query();
        // Uncomment the line below if you want to eager load the 'department' relationship
        $query = Screener::query()->with('company');

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
            $latestDate = Screener::max('date');

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

        return $query->orderBy('id', 'desc')->paginate(10);
    }

    /**
     * Store a newly created screener in storage.
     *
     * @param array<string, mixed> $data
     */
    public function store(array $data): Screener
    {
        return DB::transaction(function () use ($data): Screener {
            return Screener::create($data);
        });
    }

    /**
     * Update the specified screener in storage.
     *
     * @param array<string, mixed> $data
     */
    public function update(Screener $screener, array $data): Screener
    {
        return DB::transaction(function () use ($screener, $data): Screener {
            $screener->update($data);

            return $screener->fresh();
        });
    }

    /**
     * Remove the specified screener from storage.
     *
     * @throws Exception
     */
    public function delete(Screener $screener): bool
    {
        // Contoh validasi relasi sebelum dihapus (sesuaikan dengan relasi model Anda jika ada)
        // if ($screener->someRelation()->exists()) {
        //     throw new Exception('Data cannot be deleted because it is still related to other data.');
        // }

        return DB::transaction(function () use ($screener): bool {
            return (bool) $screener->delete();
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
        $screeners = $this->getPaginatedList($filters);

        return [
            'status' => 'success',
            'data' => $screeners->items(),
            'pagination' => [
                'current_page' => $screeners->currentPage(),
                'last_page' => $screeners->lastPage(),
                'per_page' => $screeners->perPage(),
                'total' => $screeners->total(),
                'next_page_url' => $screeners->nextPageUrl(),
                'prev_page_url' => $screeners->previousPageUrl(),
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
