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

        $qrPayload = "https://api.vietqr.io/image/970423-0352911113-GLTiEfe.jpg?accountName={$payment->bank_name}&amount={$payment->amount}&addInfo={$payment->transfer_content}";

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

    public function history(Request $request)
    {
        $payments = $this->paymentService->recentPayments($request->user(), 3);
        return response()->json($payments);
    }

    public function markPaid(Payment $payment, Request $request)
    {
        abort_if($payment->user_id !== $request->user()->id, 403, 'Bạn không có quyền truy cập giao dịch này.');
        abort_if($payment->status !== 'pending', 422, 'Giao dich này không ở trạng thái xử lý.');

        $updated = $this->paymentService->notifyManualTransferCompleted($payment);

        return response()->json([
            'message' => 'Đã gửi yêu cầu kiểm tra thanh toán. Chúng tôi sẽ xác nhận thanh toán trong vòng gian ngắn nhất.',
            'payment' => $updated,
        ]);
    }

    public function notifyBank(Request $request)
    {
        $payload = $request->validate([
            'transfer_content' => ['required', 'string'],
            'amount' => ['nullable', 'integer'],
            'bank_ref' => ['nullable', 'string'],
            'secret' => ['nullable', 'string'],
        ]);

        $payment = Payment::where('transfer_content', $payload['transfer_content'])->firstOrFail();
        $this->paymentService->markPaid($payment, [
            'amount' => $payload['amount'] ?? null,
            'bank_ref' => $payload['bank_ref'] ?? null,
        ]);

        return response()->json(['message' => 'Thanh toán thành công.']);
    }
}
