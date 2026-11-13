<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use App\Models\Screener;
use App\Models\TransactionDaily;
use App\Models\TransactionHourly;
use App\Services\CompanyService;
use App\Services\VolumeBreakoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function __construct(
        protected CompanyService $companyService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $filters = $request->validate([
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'nullable', 'in:0,1'],
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($this->companyService->getPaginatedList($filters));
        }

        return view('companies.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CompanyRequest $request): JsonResponse
    {
        $company = $this->companyService->store($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Data successfully created.',
            'data' => $company,
        ], 201);
    }

    /**
     * Show the specified resource for editing (JSON format).
     */
    public function edit(Company $company): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $company,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CompanyRequest $request, Company $company): JsonResponse
    {
        $updatedCompany = $this->companyService->update($company, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Data successfully updated.',
            'data' => $updatedCompany,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company): JsonResponse
    {
        try {
            $this->companyService->delete($company);

            return response()->json([
                'status' => 'success',
                'message' => 'Data successfully deleted.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    /**
     * Display the specified company along with its latest daily/hourly price history.
     */
    /**
     * Display the specified company along with its latest daily/hourly price history
     * and volume breakout events.
     */
    /**
     * Display the specified company along with its latest daily/hourly price history
     * and volume breakout events.
     */
    public function show(Request $request, Company $company, VolumeBreakoutService $breakoutService): View|JsonResponse
    {
        if ($request->wantsJson() || $request->ajax()) {
            $validated = $request->validate([
                'section' => ['sometimes', 'in:prices,volume_spikes'],
                'timeframe' => ['sometimes', 'in:daily,hourly'],
                'min_surge' => ['nullable', 'numeric', 'min:10', 'max:5000'],
            ]);

            $section = $validated['section'] ?? 'prices';

            if ($section === 'volume_spikes') {
                $minSurge = ! empty($validated['min_surge'])
                    ? (float) $validated['min_surge']
                    : VolumeBreakoutService::DEFAULT_MIN_SURGE;

                return response()->json([
                    'status' => 'success',
                    'min_surge' => $minSurge,
                    'data' => $breakoutService->getCompanyBreakouts($company, $minSurge, 20),
                ]);
            }

            $timeframe = $validated['timeframe'] ?? 'daily';

            if ($timeframe === 'hourly') {
                $prices = TransactionHourly::query()
                    ->where('company_id', $company->id)
                    ->orderByDesc('timestamp')
                    ->limit(20)
                    ->get()
                    ->map(fn(TransactionHourly $row): array => [
                        'id' => $row->id,
                        'time_label' => $row->timestamp,
                        'open' => $row->open,
                        'high' => $row->high,
                        'low' => $row->low,
                        'close' => $row->close,
                        'volume' => $row->volume,
                    ]);
            } else {
                $prices = TransactionDaily::query()
                    ->where('company_id', $company->id)
                    ->orderByDesc('date')
                    ->limit(20)
                    ->get()
                    ->map(fn(TransactionDaily $row): array => [
                        'id' => $row->id,
                        'time_label' => substr((string) $row->date, 0, 10),
                        'open' => $row->open,
                        'high' => $row->high,
                        'low' => $row->low,
                        'close' => $row->close,
                        'volume' => $row->volume,
                    ]);
            }

            return response()->json([
                'status' => 'success',
                'timeframe' => $timeframe,
                'data' => $prices,
            ]);
        }

        return view('companies.show', [
            'company' => $company,
        ]);
    }

    /**
     * Get all companies experiencing a 20-day VMA volume breakout on the latest trading date.
     */
    public function volumeBreakouts(Request $request, VolumeBreakoutService $breakoutService): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'min_surge' => ['nullable', 'numeric', 'min:10', 'max:5000'],
        ]);

        $minSurge = ! empty($validated['min_surge'])
            ? (float) $validated['min_surge']
            : VolumeBreakoutService::DEFAULT_MIN_SURGE;
        $search = isset($validated['search']) ? trim((string) $validated['search']) : '';

        $result = $breakoutService->getLatestMarketBreakouts($minSurge, $search);
        $total = count($result['data']);

        return response()->json([
            'status' => 'success',
            'latest_date' => $result['latest_date'],
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => max($total, 1),
            'total' => $total,
            'from' => $total > 0 ? 1 : 0,
            'to' => $total,
            'next_page_url' => null,
            'prev_page_url' => null,
            'data' => $result['data'],
        ]);
    }
}
