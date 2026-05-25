<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomTenant;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $landlord = $request->user()?->landlord;
        abort_unless($landlord, 403);

        $tenants = Tenant::query()
            ->whereHas('roomTenants.room.boardingHouse', function ($q) use ($landlord): void {
                $q->where('landlord_id', $landlord->id);
            })
            ->distinct()
            ->latest('tenants.id')
            ->get();

        return response()->json([
            'data' => $tenants,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $landlord = $request->user()?->landlord;
        abort_unless($landlord, 403);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:tenants,phone'],
            'cccd' => ['nullable', 'string', 'max:50', 'unique:tenants,cccd'],
            'moved_in_date' => ['nullable', 'date'],
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'start_date' => ['required_with:room_id', 'date'],
            'deposit_amount' => ['nullable', 'integer', 'min:0'],
        ]);

        $tenant = Tenant::create([
            'full_name' => $data['full_name'],
            'phone' => $data['phone'],
            'cccd' => $data['cccd'] ?? null,
            'moved_in_date' => $data['moved_in_date'] ?? null,
        ]);

        if (! empty($data['room_id'])) {
            $room = Room::query()->with('boardingHouse')->findOrFail($data['room_id']);
            abort_unless($room->boardingHouse?->landlord_id === $landlord->id, 404);

            RoomTenant::create([
                'room_id' => $room->id,
                'tenant_id' => $tenant->id,
                'start_date' => $data['start_date'],
                'deposit_amount' => $data['deposit_amount'] ?? 0,
                'status' => 'active',
            ]);

            $room->status = 'rented';
            $room->save();
        }

        return response()->json([
            'data' => $tenant,
        ], 201);
    }

    public function show(Request $request, Tenant $tenant): JsonResponse
    {
        $landlord = $request->user()?->landlord;
        abort_unless($landlord, 403);

        $allowed = $tenant->roomTenants()
            ->whereHas('room.boardingHouse', function ($q) use ($landlord): void {
                $q->where('landlord_id', $landlord->id);
            })
            ->exists();

        abort_unless($allowed, 404);

        return response()->json([
            'data' => $tenant->load(['roomTenants.room']),
        ]);
    }

    public function update(Request $request, Tenant $tenant): JsonResponse
    {
        $landlord = $request->user()?->landlord;
        abort_unless($landlord, 403);

        $allowed = $tenant->roomTenants()
            ->whereHas('room.boardingHouse', function ($q) use ($landlord): void {
                $q->where('landlord_id', $landlord->id);
            })
            ->exists();

        abort_unless($allowed, 404);

        $data = $request->validate([
            'full_name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:20', 'unique:tenants,phone,'.$tenant->id],
            'cccd' => ['sometimes', 'nullable', 'string', 'max:50', 'unique:tenants,cccd,'.$tenant->id],
            'moved_in_date' => ['sometimes', 'nullable', 'date'],
        ]);

        $tenant->fill($data)->save();

        return response()->json([
            'data' => $tenant,
        ]);
    }

    public function destroy(Request $request, Tenant $tenant): JsonResponse
    {
        $landlord = $request->user()?->landlord;
        abort_unless($landlord, 403);

        $allowed = $tenant->roomTenants()
            ->whereHas('room.boardingHouse', function ($q) use ($landlord): void {
                $q->where('landlord_id', $landlord->id);
            })
            ->exists();

        abort_unless($allowed, 404);

        $tenant->delete();

        return response()->json([
            'ok' => true,
        ]);
    }
}

