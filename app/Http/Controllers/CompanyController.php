<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use App\Models\Screener;
use App\Models\TransactionDaily;
use App\Models\TransactionHourly;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    public function show(Request $request, Company $company): View|JsonResponse
    {
        if ($request->wantsJson() || $request->ajax()) {
            $validated = $request->validate([
                'timeframe' => ['sometimes', 'in:daily,hourly'],
            ]);

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
                        'time_label' => $row->date,
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

        $latestScreener = Screener::query()
            ->where('company_id', $company->id)
            ->orderByDesc('date')
            ->first();

        return view('companies.show', [
            'company' => $company,
        ]);
    }
}
