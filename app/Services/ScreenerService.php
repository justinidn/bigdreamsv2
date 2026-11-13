<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Screener;
use App\Models\TransactionDaily;
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
        $query = Screener::query()
            ->with('company')
            ->addSelect([
                'daily_volume' => TransactionDaily::select('volume')
                    ->whereColumn('company_id', 't_stock_screening.company_id')
                    ->whereColumn('date', 't_stock_screening.date')
                    ->limit(1),
            ]);

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->whereHas('company', function ($q) use ($search): void {
                $q->where('company_code', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        // Filter Start Date dan End Date (selalu default ke tanggal data terakhir jika tanggal tidak diisi)
        $hasStart = ! empty($filters['start_date']);
        $hasEnd   = ! empty($filters['end_date']);

        if (! $hasStart && ! $hasEnd) {
            $latestDate = Screener::max('date');

            $defaultDate = $latestDate
                ? Carbon::parse($latestDate)->toDateString()
                : Carbon::today()->toDateString();

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

        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 10;

        return $query->orderBy('id', 'desc')->paginate($perPage);
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
}
