<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    public function plans()
    {
        return response()->json([
            'vip' => PaymentService::VIP_PLANS,
            'api' => PaymentService::API_PLANS,
        ]);
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'type' => ['required', 'in:vip,api'],
            'plan_key' => ['required', 'string'],
        ]);

        $payment = $this->paymentService->create($request->user(), $payload['type'], $payload['plan_key']);

        $qrPayload = "BANK|{$payment->bank_name}|{$payment->bank_account_number}|{$payment->amount}|{$payment->transfer_content}";

        return response()->json([
            'payment' => $payment,
            'qr_content' => $qrPayload,
        ], 201);
    }

    public function status(Payment $payment, Request $request)
    {
        abort_if($payment->user_id !== $request->user()->id, 403, 'Khong co quyen truy cap giao dich nay.');
        return response()->json($payment);
    }

    public function notifyBank(Request $request)
    {
        $payload = $request->validate([
            'transfer_content' => ['required', 'string'],
            'amount' => ['nullable', 'integer'],
            'bank_ref' => ['nullable', 'string'],
            'secret' => ['nullable', 'string'],
        ]);

        $expectedSecret = env('PAYMENT_BANK_SECRET');
        if ($expectedSecret && ($payload['secret'] ?? null) !== $expectedSecret) {
            return response()->json(['message' => 'Khong the thanh toan.'], 401);
        }

        $payment = Payment::where('transfer_content', $payload['transfer_content'])->firstOrFail();
        $this->paymentService->markPaid($payment, [
            'amount' => $payload['amount'] ?? null,
            'bank_ref' => $payload['bank_ref'] ?? null,
        ]);

        return response()->json(['message' => 'Thanh toan thanh cong.']);
    }
}
