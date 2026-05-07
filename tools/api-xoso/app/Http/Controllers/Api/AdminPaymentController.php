<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::query()
            ->with('user')
            ->orderByDesc('id');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('manual_review_status')) {
            $query->where('manual_review_status', $request->input('manual_review_status'));
        }
        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('transfer_content', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('email', 'like', '%' . $search . '%')
                            ->orWhere('name', 'like', '%' . $search . '%');
                    });
            });
        }

        return response()->json(
            $query->paginate(20)
        );
    }

    public function show(Payment $payment)
    {
        return response()->json(
            $payment->load('user')
        );
    }

    public function approve(Request $request, Payment $payment, PaymentService $paymentService)
    {
        if ($payment->status === 'paid') {
            return response()->json([
                'message' => 'Payment already paid',
                'payment' => $payment->load('user'),
            ]);
        }

        $updated = $paymentService->markPaid($payment, [
            'manual_approved_via' => 'admin_panel',
            'manual_approved_by' => $request->user()?->id,
            'manual_approved_at' => now()->toDateTimeString(),
            'manual_review_requested_at' => $payment->manual_review_requested_at?->toDateTimeString(),
        ]);

        return response()->json([
            'message' => 'Approved',
            'payment' => $updated->load('user'),
        ]);
    }
}

