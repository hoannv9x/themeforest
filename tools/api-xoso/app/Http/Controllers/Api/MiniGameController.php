<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MiniGameService;
use Illuminate\Http\Request;

class MiniGameController extends Controller
{
    public function __construct(private readonly MiniGameService $miniGameService)
    {
    }

    public function index(Request $request)
    {
        return response()->json(
            $this->miniGameService->getOverview($request->user())
        );
    }

    public function me(Request $request)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'prediction' => $this->miniGameService->getMyPrediction($request->user()),
        ]);
    }

    public function predict(Request $request)
    {
        $request->validate([
            'numbers' => ['required', 'array', 'min:1', 'max:5'],
            'numbers.*' => ['required'],
        ]);

        try {
            $prediction = $this->miniGameService->submitPrediction(
                $request->user(),
                $request->input('numbers', [])
            );

            return response()->json([
                'message' => 'Du doan da duoc cap nhat.',
                'prediction' => $prediction,
            ]);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function submitPayoutRequest(Request $request)
    {
        $payload = $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_name' => ['required', 'string', 'max:120'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $record = $this->miniGameService->submitPayoutInfo($request->user(), $payload);
            return response()->json([
                'message' => 'Thong tin STK da duoc ghi nhan.',
                'data' => $record,
            ]);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
