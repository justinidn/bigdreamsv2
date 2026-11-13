<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Company;
use App\Models\TransactionDaily;
use Illuminate\Support\Facades\DB;

class VolumeBreakoutService
{
    public const DEFAULT_PERIOD = 20;
    public const DEFAULT_MIN_SURGE = 100.0;

    /**
     * Hitung daftar kejadian volume breakout (standar 20-day VMA) untuk satu emiten.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCompanyBreakouts(
        Company $company,
        float $minSurgePercent = self::DEFAULT_MIN_SURGE,
        int $limit = 20,
        int $period = self::DEFAULT_PERIOD
    ): array {
        $rows = TransactionDaily::query()
            ->where('company_id', $company->id)
            ->orderBy('date', 'asc')
            ->get(['id', 'date', 'volume']);

        $count = $rows->count();
        if ($count <= $period) {
            return [];
        }

        $breakouts = [];
        $rollingSum = 0.0;

        for ($i = 0; $i < $period; $i++) {
            $rollingSum += (float) $rows[$i]->volume;
        }

        for ($i = $period; $i < $count; $i++) {
            $currentVolume = (float) $rows[$i]->volume;
            $avgVolume = $rollingSum / $period;

            if ($avgVolume > 0 && $currentVolume > 0) {
                $surgePercent = (($currentVolume - $avgVolume) / $avgVolume) * 100;

                if ($surgePercent >= $minSurgePercent) {
                    $breakouts[] = [
                        'id' => $rows[$i]->id,
                        'date' => substr((string) $rows[$i]->date, 0, 10),
                        'volume' => $rows[$i]->volume,
                        'surge_percent' => round($surgePercent, 1),
                    ];
                }
            }

            $rollingSum += $currentVolume - (float) $rows[$i - $period]->volume;
        }

        return array_slice(array_reverse($breakouts), 0, $limit);
    }

    /**
     * Pindai seluruh emiten yang mengalami volume breakout pada tanggal bursa terakhir.
     *
     * @return array{latest_date: string|null, data: array<int, array<string, mixed>>}
     */
    public function getLatestMarketBreakouts(
        float $minSurgePercent = self::DEFAULT_MIN_SURGE,
        string $search = '',
        int $period = self::DEFAULT_PERIOD
    ): array {
        $latestDateRow = TransactionDaily::query()
            ->selectRaw('DATE(date) as trade_date, COUNT(*) as total_rows')
            ->groupByRaw('DATE(date)')
            ->havingRaw('COUNT(*) >= 10')
            ->orderByDesc('trade_date')
            ->first();

        $latestDate = $latestDateRow
            ? (string) $latestDateRow->trade_date
            : (string) TransactionDaily::query()->selectRaw('DATE(MAX(date)) as max_date')->value('max_date');

        if ($latestDate === '') {
            return [
                'latest_date' => null,
                'data' => [],
            ];
        }

        // Ambil period + 5 tanggal bursa terakhir untuk mengantisipasi jeda data tiap emiten
        $recentDates = TransactionDaily::query()
            ->selectRaw('DISTINCT DATE(date) as trade_date')
            ->whereRaw('DATE(date) <= ?', [$latestDate])
            ->orderByDesc('trade_date')
            ->limit($period + 5)
            ->pluck('trade_date')
            ->map(fn($d): string => substr((string) $d, 0, 10))
            ->all();

        if (empty($recentDates)) {
            return [
                'latest_date' => $latestDate,
                'data' => [],
            ];
        }

        $query = TransactionDaily::query()
            ->select([
                't_stock_daily.id',
                't_stock_daily.company_id',
                't_stock_daily.open',
                't_stock_daily.close',
                't_stock_daily.volume',
                'm_company.company_code',
                'm_company.company_name',
            ])
            ->selectRaw('DATE(t_stock_daily.date) as trade_date')
            ->join('m_company', 'm_company.id', '=', 't_stock_daily.company_id')
            ->whereIn(DB::raw('DATE(t_stock_daily.date)'), $recentDates)
            ->orderBy('t_stock_daily.company_id')
            ->orderByDesc('t_stock_daily.date');

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('m_company.company_code', 'like', "%{$search}%")
                    ->orWhere('m_company.company_name', 'like', "%{$search}%");
            });
        }

        $grouped = $query->get()->groupBy('company_id');
        $breakouts = [];

        foreach ($grouped as $companyRows) {
            $latestRow = $companyRows->first();

            if (! $latestRow || substr((string) $latestRow->trade_date, 0, 10) !== $latestDate) {
                continue;
            }

            $previousRows = $companyRows->slice(1, $period);
            if ($previousRows->count() < $period) {
                continue;
            }

            $currentVolume = (float) $latestRow->volume;
            $avgVolume = (float) $previousRows->avg('volume');

            if ($avgVolume <= 0 || $currentVolume <= 0) {
                continue;
            }

            $surgePercent = (($currentVolume - $avgVolume) / $avgVolume) * 100;

            if ($surgePercent >= $minSurgePercent) {
                $prevDayRow = $previousRows->first();
                $prevClose = $prevDayRow ? (float) $prevDayRow->close : (float) $latestRow->open;
                $currentClose = (float) $latestRow->close;

                $priceDirection = 0;
                if ($currentClose > $prevClose) {
                    $priceDirection = 1;
                } elseif ($currentClose < $prevClose) {
                    $priceDirection = -1;
                }

                $breakouts[] = [
                    'id' => $latestRow->id,
                    'company_id' => $latestRow->company_id,
                    'company_code' => $latestRow->company_code,
                    'company_name' => $latestRow->company_name,
                    'date' => substr((string) $latestRow->trade_date, 0, 10),
                    'close_price' => $latestRow->close,
                    'volume' => $latestRow->volume,
                    'avg_volume_20d' => round($avgVolume),
                    'surge_percent' => round($surgePercent, 1),
                    'price_direction' => $priceDirection,
                ];
            }
        }

        usort($breakouts, fn(array $a, array $b): int => $b['surge_percent'] <=> $a['surge_percent']);

        return [
            'latest_date' => $latestDate,
            'data' => $breakouts,
        ];
    }
}
