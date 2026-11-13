<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Screener;
use Illuminate\Support\Collection;

class StockScreenerService
{
    public function getScreenedStocks(): Collection
    {
        // Mengambil data screening terbaru beserta relasi company
        return Screener::with('company:id,name') // Sesuaikan kolom jika namanya 'company_name' atau 'code'
            ->latest('date')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'stock_name' => $item->company?->name ?? 'Unknown',
                    'pattern' => $item->pattern_name,
                    'date' => $item->date,
                ];
            });
    }
}
