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
            'vip' => $this->paymentService->getPlansForType('vip'),
            'api' => $this->paymentService->getPlansForType('api'),
        ]);
    }

    public function store(Request $request)
    {
        abort_if(!$request->user()->email_verified_at, 403, 'Vui lòng xác nhận email trước khi tạo giao dịch.');

        $payload = $request->validate([
            'type' => ['required', 'in:vip,api'],
            'plan_key' => ['required', 'string'],
            'coupon_code' => ['nullable', 'string', 'max:64'],
        ]);

        $payment = $this->paymentService->create(
            $request->user(),
            $payload['type'],
            $payload['plan_key'],
            $payload['coupon_code'] ?? null
        );

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

        $lastRequestedAt = $payment->manual_review_requested_at;
        if ($payment->manual_review_status === 'requested' && $lastRequestedAt) {
            $nextAllowed = $lastRequestedAt->copy()->addMinutes(5);
            if (now()->lt($nextAllowed)) {
                $remainingSeconds = now()->diffInSeconds($nextAllowed);
                return response()->json([
                    'message' => 'Vui lòng đợi trước khi gửi lại yêu cầu hoàn tất.',
                    'retry_after_seconds' => $remainingSeconds,
                    'manual_review_requested_at' => $lastRequestedAt,
                ], 429);
            }
        }

        $updated = $this->paymentService->notifyManualTransferCompleted($payment->fresh());

        return response()->json([
            'message' => 'Đã gửi yêu cầu kiểm tra thanh toán. Chúng tôi sẽ xác nhận thanh toán trong vòng gian ngắn nhất.',
            'payment' => $updated,
        ]);
    }

    public function cancel(Payment $payment, Request $request)
    {
        abort_if($payment->user_id !== $request->user()->id, 403, 'Bạn không có quyền truy cập giao dịch này.');

        $payload = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $updated = $this->paymentService->cancel($payment->fresh(), $request->user(), $payload['reason'] ?? null);

        return response()->json([
            'message' => 'Đã huỷ giao dịch.',
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
