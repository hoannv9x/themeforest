<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $landlord = $request->user()?->landlord;
        abort_unless($landlord, 403);

        $query = Room::query()
            ->whereHas('boardingHouse', function ($q) use ($landlord): void {
                $q->where('landlord_id', $landlord->id);
            })
            ->latest('id');

        if ($request->filled('boarding_house_id')) {
            $query->where('boarding_house_id', $request->integer('boarding_house_id'));
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $landlord = $request->user()?->landlord;
        abort_unless($landlord, 403);

        $data = $request->validate([
            'boarding_house_id' => ['required', 'integer', 'exists:boarding_houses,id'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:vacant,rented,deposit,maintenance'],
            'rent_amount' => ['required', 'integer', 'min:0'],
            'deposit_amount' => ['nullable', 'integer', 'min:0'],
            'electricity_rate' => ['nullable', 'integer', 'min:0'],
            'water_rate' => ['nullable', 'integer', 'min:0'],
            'wifi_fee' => ['nullable', 'integer', 'min:0'],
            'trash_fee' => ['nullable', 'integer', 'min:0'],
            'parking_fee' => ['nullable', 'integer', 'min:0'],
            'initial_electricity_reading' => ['nullable', 'integer', 'min:0'],
            'initial_water_reading' => ['nullable', 'integer', 'min:0'],
        ]);

        $boardingHouse = BoardingHouse::query()->whereKey($data['boarding_house_id'])->firstOrFail();
        abort_unless($boardingHouse->landlord_id === $landlord->id, 404);

        $room = Room::create($data);

        return response()->json([
            'data' => $room,
        ], 201);
    }

    public function show(Request $request, Room $room): JsonResponse
    {
        $landlord = $request->user()?->landlord;
        abort_unless($landlord, 403);
        abort_unless($room->boardingHouse?->landlord_id === $landlord->id, 404);

        return response()->json([
            'data' => $room->load(['boardingHouse']),
        ]);
    }

    public function update(Request $request, Room $room): JsonResponse
    {
        $landlord = $request->user()?->landlord;
        abort_unless($landlord, 403);
        abort_unless($room->boardingHouse?->landlord_id === $landlord->id, 404);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:vacant,rented,deposit,maintenance'],
            'rent_amount' => ['sometimes', 'integer', 'min:0'],
            'deposit_amount' => ['sometimes', 'integer', 'min:0'],
            'electricity_rate' => ['sometimes', 'integer', 'min:0'],
            'water_rate' => ['sometimes', 'integer', 'min:0'],
            'wifi_fee' => ['sometimes', 'integer', 'min:0'],
            'trash_fee' => ['sometimes', 'integer', 'min:0'],
            'parking_fee' => ['sometimes', 'integer', 'min:0'],
        ]);

        $room->fill($data)->save();

        return response()->json([
            'data' => $room,
        ]);
    }

    public function destroy(Request $request, Room $room): JsonResponse
    {
        $landlord = $request->user()?->landlord;
        abort_unless($landlord, 403);
        abort_unless($room->boardingHouse?->landlord_id === $landlord->id, 404);

        $room->delete();

        return response()->json([
            'ok' => true,
        ]);
    }
}

