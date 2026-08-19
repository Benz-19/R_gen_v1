<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReconciliationService;
use Illuminate\Http\JsonResponse;

class ReconciliationController extends Controller
{
    public function __construct(
        protected ReconciliationService $reconciliationService
    ) {}

    public function match(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bank_record.id' => 'required|string',
            'bank_record.description' => 'required|string',
            'bank_record.amount' => 'required|numeric',
            'bank_record.date' => 'required|date_format:Y-m-d',
            'bank_record.reference' => 'required|string',
            
            'ledger_record.id' => 'required|string',
            'ledger_record.description' => 'required|string',
            'ledger_record.amount' => 'required|numeric',
            'ledger_record.date' => 'required|date_format:Y-m-d',
            'ledger_record.reference' => 'required|string',
        ]);

        $result = $this->reconciliationService->reconcile(
            $validated['bank_record'],
            $validated['ledger_record']
        );

        return response()->json($result);
    }
}