<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function __construct(
        protected DepartmentService $departmentService
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
            return response()->json($this->departmentService->getPaginatedList($filters));
        }

        return view('departments.index');
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(DepartmentRequest $request): JsonResponse
    {
        $department = $this->departmentService->store($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Data successfully created.',
            'data' => $department,
        ], 201);
    }

    /**
     * Show the specified resource for editing (JSON format).
     */
    public function edit(Department $department): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $department,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DepartmentRequest $request, Department $department): JsonResponse
    {
        $updatedDepartment = $this->departmentService->update($department, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Data successfully updated.',
            'data' => $updatedDepartment,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department): JsonResponse
    {
        try {
            $this->departmentService->delete($department);

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
