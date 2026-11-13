<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ScreenerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScreenerController extends Controller
{
    public function __construct(
        protected ScreenerService $screenerService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $filters = $request->validate([
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'per_page' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:500'],
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($this->screenerService->getPaginatedList($filters));
        }

        return view('screeners.index');
    }
}
