<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\MasterMenuRequest;
use App\Models\MasterMenu;
use App\Services\MasterMenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterMenuController extends Controller
{
    public function __construct(
        protected MasterMenuService $masterMenuService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $filters = $request->validate([
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($this->masterMenuService->getPaginatedList($filters));
        }

        return view('master-menus.index');
    }

    public function store(MasterMenuRequest $request): JsonResponse
    {
        $masterMenu = $this->masterMenuService->store($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Module berhasil ditambahkan.',
            'data' => $masterMenu,
        ], 201);
    }

    public function edit(MasterMenu $masterMenu): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->masterMenuService->getMenuData($masterMenu),
        ]);
    }

    public function update(MasterMenuRequest $request, MasterMenu $masterMenu): JsonResponse
    {
        $updatedMenu = $this->masterMenuService->update($masterMenu, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Permission module berhasil diperbarui.',
            'data' => $updatedMenu,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MasterMenu $masterMenu): JsonResponse
    {
        try {
            $this->masterMenuService->delete($masterMenu);

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
