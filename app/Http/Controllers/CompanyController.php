<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Models\Company;
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
}
