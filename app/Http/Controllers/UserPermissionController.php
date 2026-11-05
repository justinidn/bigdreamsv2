<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UserPermissionRequest;
use App\Models\User;
use App\Services\UserPermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserPermissionController extends Controller
{
    public function __construct(
        protected UserPermissionService $userPermissionService
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
            return response()->json($this->userPermissionService->getPaginatedList($filters));
        }

        return view('user-permissions.index', [
            'permissionModules' => $this->userPermissionService->getPermissionModules(),
        ]);
    }

    public function edit(User $user): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->userPermissionService->getUserPermissionData($user),
        ]);
    }

    public function update(UserPermissionRequest $request, User $user): JsonResponse
    {
        $updatedUser = $this->userPermissionService->syncPermissions($user, $request->validated('permissions'));

        return response()->json([
            'status' => 'success',
            'message' => 'Permission user berhasil diperbarui.',
            'data' => $this->userPermissionService->getUserPermissionData($updatedUser),
        ]);
    }
}
