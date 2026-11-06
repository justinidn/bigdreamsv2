<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TransactionDailyRequest;
use App\Models\TransactionDaily;
use App\Services\TransactionDailyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionDailyController extends Controller
{
    public function __construct(
        protected TransactionDailyService $transactionDailyService
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
        return response()->json($this->transactionDailyService->getPaginatedList($filters));
    }

    return view('transaction-dailies.index');
}

}