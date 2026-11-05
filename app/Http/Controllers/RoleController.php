<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $roleService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $filters = $request->validate([
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],

        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($this->roleService->getPaginatedList($filters));
        }

        return view('roles.index', [
            'permissionModules' => $this->roleService->getPermissionModules(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleRequest $request): JsonResponse
    {
        $role = $this->roleService->store($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Data successfully created.',
            'data' => $role,
        ], 201);
    }

    /**
     * Show the specified resource for editing (JSON format).
     */
    public function edit(Role $role): JsonResponse
    {
        $data = $role->toArray();
        $data['enabled_permissions'] = $this->roleService->getEnabledPermissions($role);

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleRequest $request, Role $role): JsonResponse
    {
        $updatedRole = $this->roleService->update($role, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Data successfully updated.',
            'data' => $updatedRole,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): JsonResponse
    {
        try {
            $this->roleService->delete($role);

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
