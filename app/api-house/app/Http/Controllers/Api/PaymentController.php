<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $landlord = $request->user()?->landlord;
        abort_unless($landlord, 403);

        $data = $request->validate([
            'invoice_id' => ['required', 'integer', 'exists:invoices,id'],
            'amount' => ['required', 'integer', 'min:0'],
            'method' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'in:pending,paid,failed'],
            'reference' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
            'raw_payload' => ['nullable', 'array'],
        ]);

        $invoice = Invoice::query()
            ->with('room.boardingHouse')
            ->findOrFail($data['invoice_id']);

        abort_unless($invoice->room?->boardingHouse?->landlord_id === $landlord->id, 404);

        return DB::transaction(function () use ($invoice, $data): JsonResponse {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $data['amount'],
                'method' => $data['method'] ?? 'manual',
                'status' => $data['status'] ?? 'pending',
                'reference' => $data['reference'] ?? null,
                'paid_at' => $data['paid_at'] ?? null,
                'raw_payload' => $data['raw_payload'] ?? null,
            ]);

            if ($payment->status === 'paid') {
                $invoice->status = 'paid';
                $invoice->paid_at = $payment->paid_at ?? now();
                $invoice->save();
            }

            return response()->json([
                'data' => [
                    'payment' => $payment,
                    'invoice' => $invoice,
                ],
            ], 201);
        });
    }
}

