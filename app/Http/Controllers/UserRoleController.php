<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UserRoleRequest;
use App\Models\User;
use App\Services\UserRoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserRoleController extends Controller
{
    public function __construct(
        protected UserRoleService $userRoleService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $filters = $request->validate([
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($this->userRoleService->getPaginatedList($filters));
        }

        return view('user-roles.index', [
            'roles' => $this->userRoleService->getRoleOptions(),
        ]);
    }

    public function edit(User $user): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->userRoleService->getUserRoleData($user),
        ]);
    }

    public function update(UserRoleRequest $request, User $user): JsonResponse
    {
        $updatedUser = $this->userRoleService->syncRoles($user, $request->validated('roles'));

        return response()->json([
            'status' => 'success',
            'message' => 'Role pengguna berhasil diperbarui.',
            'data' => $this->userRoleService->getUserRoleData($updatedUser),
        ]);
    }
}
