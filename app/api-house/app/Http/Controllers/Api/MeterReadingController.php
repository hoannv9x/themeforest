<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\MeterReading;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MeterReadingController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $landlord = $request->user()?->landlord;
        abort_unless($landlord, 403);

        $data = $request->validate([
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'period' => ['required', 'string', 'size:7'],
            'electricity_reading' => ['required', 'integer', 'min:0'],
            'water_reading' => ['required', 'integer', 'min:0'],
            'read_at' => ['nullable', 'date'],
        ]);

        $room = Room::query()->with('boardingHouse')->findOrFail($data['room_id']);
        abort_unless($room->boardingHouse?->landlord_id === $landlord->id, 404);

        $period = $data['period'];

        return DB::transaction(function () use ($request, $room, $period, $data): JsonResponse {
            $previous = MeterReading::query()
                ->where('room_id', $room->id)
                ->where('period', '<', $period)
                ->orderByDesc('period')
                ->first();

            $previousElectricity = $previous?->electricity_reading ?? $room->initial_electricity_reading;
            $previousWater = $previous?->water_reading ?? $room->initial_water_reading;

            $electricityUsage = $data['electricity_reading'] - $previousElectricity;
            $waterUsage = $data['water_reading'] - $previousWater;

            abort_if($electricityUsage < 0, 422, 'Số điện mới nhỏ hơn số điện kỳ trước.');
            abort_if($waterUsage < 0, 422, 'Số nước mới nhỏ hơn số nước kỳ trước.');

            $meterReading = MeterReading::updateOrCreate(
                ['room_id' => $room->id, 'period' => $period],
                [
                    'electricity_reading' => $data['electricity_reading'],
                    'water_reading' => $data['water_reading'],
                    'read_at' => $data['read_at'] ?? now(),
                    'created_by_user_id' => $request->user()?->id,
                ],
            );

            $activeRoomTenant = $room->roomTenants()
                ->where('status', 'active')
                ->orderByDesc('start_date')
                ->first();

            $dueDate = Carbon::createFromFormat('Y-m', $period)->endOfMonth()->toDateString();

            $invoice = Invoice::updateOrCreate(
                ['room_id' => $room->id, 'period' => $period],
                [
                    'room_tenant_id' => $activeRoomTenant?->id,
                    'due_date' => $dueDate,
                    'status' => 'unpaid',
                    'created_by_user_id' => $request->user()?->id,
                ],
            );

            $invoice->items()->delete();

            $items = [];

            $items[] = [
                'name' => 'Tiền phòng',
                'type' => 'rent',
                'quantity' => 1,
                'unit_price' => $room->rent_amount,
                'amount' => $room->rent_amount,
            ];

            $electricityAmount = $electricityUsage * $room->electricity_rate;
            $items[] = [
                'name' => 'Tiền điện',
                'type' => 'electricity',
                'quantity' => $electricityUsage,
                'unit_price' => $room->electricity_rate,
                'amount' => $electricityAmount,
                'meta' => [
                    'previous' => $previousElectricity,
                    'current' => $data['electricity_reading'],
                ],
            ];

            $waterAmount = $waterUsage * $room->water_rate;
            $items[] = [
                'name' => 'Tiền nước',
                'type' => 'water',
                'quantity' => $waterUsage,
                'unit_price' => $room->water_rate,
                'amount' => $waterAmount,
                'meta' => [
                    'previous' => $previousWater,
                    'current' => $data['water_reading'],
                ],
            ];

            if ($room->wifi_fee > 0) {
                $items[] = [
                    'name' => 'Wifi',
                    'type' => 'wifi',
                    'quantity' => 1,
                    'unit_price' => $room->wifi_fee,
                    'amount' => $room->wifi_fee,
                ];
            }

            if ($room->trash_fee > 0) {
                $items[] = [
                    'name' => 'Rác',
                    'type' => 'trash',
                    'quantity' => 1,
                    'unit_price' => $room->trash_fee,
                    'amount' => $room->trash_fee,
                ];
            }

            if ($room->parking_fee > 0) {
                $items[] = [
                    'name' => 'Xe',
                    'type' => 'parking',
                    'quantity' => 1,
                    'unit_price' => $room->parking_fee,
                    'amount' => $room->parking_fee,
                ];
            }

            foreach ($items as $item) {
                InvoiceItem::create([
                    ...$item,
                    'invoice_id' => $invoice->id,
                ]);
            }

            $subtotal = $invoice->items()->sum('amount');
            $invoice->subtotal = $subtotal;
            $invoice->total = $subtotal;
            $invoice->save();

            return response()->json([
                'data' => [
                    'meter_reading' => $meterReading,
                    'invoice' => $invoice->load(['items', 'room']),
                ],
            ], 201);
        });
    }
}

