<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class VipController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    public function status(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'vip_status' => $user->getVipStatus(),
        ]);
    }

    public function upsell(Request $request)
    {
        $user = $request->user();
        $vipStatus = $user->getVipStatus();
        
        return response()->json([
            'vip_status' => $vipStatus,
            'plans' => $this->paymentService->getPlansForType('vip'),
            'benefits' => [
                'Full bộ số + phân tích',
                'Không delay',
                'Heatmap số',
                'Chu kỳ lặp',
                'Thống kê xác suất',
                'Gợi ý chiến lược đánh',
                'Tracking lãi/lỗ',
            ],
        ]);
    }

    public function startTrial(Request $request)
    {
        $user = $request->user();
        
        if ($user->hasUsedTrial()) {
            return response()->json([
                'error' => 'Trial already used',
                'vip_status' => $user->getVipStatus(),
                'message' => 'You have already used your VIP trial.',
            ], 422);
        }

        $user->startVipTrial();
        $user->refresh();

        return response()->json([
            'message' => 'VIP trial started successfully! You have 3 days of VIP access.',
            'vip_status' => $user->getVipStatus(),
        ]);
    }
}
