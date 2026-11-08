<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ScreenerRequest;
use App\Models\Screener;
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
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($this->screenerService->getPaginatedList($filters));
        }

        return view('screeners.index');
    }
}
