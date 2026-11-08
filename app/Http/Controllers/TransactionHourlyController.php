<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TransactionHourly;
use App\Services\TransactionHourlyService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionHourlyController extends Controller
{
    public function __construct(
        protected TransactionHourlyService $transactionHourlyService
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
            return response()->json($this->transactionHourlyService->getPaginatedList($filters));
        }

        return view('transaction-hourlies.index');
    }
}
