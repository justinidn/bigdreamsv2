<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\MCompanyRequest;
use App\Models\MCompany;
use App\Services\MCompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MCompanyController extends Controller
{
    public function __construct(
        protected MCompanyService $mCompanyService
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
            return response()->json($this->mCompanyService->getPaginatedList($filters));
        }

        return view('m-companies.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MCompanyRequest $request): JsonResponse
    {
        $mCompany = $this->mCompanyService->store($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Data successfully created.',
            'data' => $mCompany,
        ], 201);
    }

    /**
     * Show the specified resource for editing (JSON format).
     */
    public function edit(MCompany $mCompany): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $mCompany,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MCompanyRequest $request, MCompany $mCompany): JsonResponse
    {
        $updatedMCompany = $this->mCompanyService->update($mCompany, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Data successfully updated.',
            'data' => $updatedMCompany,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MCompany $mCompany): JsonResponse
    {
        try {
            $this->mCompanyService->delete($mCompany);

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