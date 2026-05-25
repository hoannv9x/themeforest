<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BoardingHouseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $landlord = $request->user()?->landlord;
        abort_unless($landlord, 403, 'You are not a landlord');

        return response()->json([
            'data' => BoardingHouse::query()
                ->where('landlord_id', $landlord->id)
                ->latest('id')
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $landlord = $request->user()?->landlord;
        abort_unless($landlord, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ]);

        $boardingHouse = BoardingHouse::create([
            ...$data,
            'landlord_id' => $landlord->id,
        ]);

        return response()->json([
            'data' => $boardingHouse,
        ], 201);
    }

    public function show(Request $request, BoardingHouse $boardingHouse): JsonResponse
    {
        $landlord = $request->user()?->landlord;
        abort_unless($landlord, 403);
        abort_unless($boardingHouse->landlord_id === $landlord->id, 404);

        return response()->json([
            'data' => $boardingHouse,
        ]);
    }

    public function update(Request $request, BoardingHouse $boardingHouse): JsonResponse
    {
        $landlord = $request->user()?->landlord;
        abort_unless($landlord, 403);
        abort_unless($boardingHouse->landlord_id === $landlord->id, 404);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'note' => ['sometimes', 'nullable', 'string'],
        ]);

        $boardingHouse->fill($data)->save();

        return response()->json([
            'data' => $boardingHouse,
        ]);
    }

    public function destroy(Request $request, BoardingHouse $boardingHouse): JsonResponse
    {
        $landlord = $request->user()?->landlord;
        abort_unless($landlord, 403);
        abort_unless($boardingHouse->landlord_id === $landlord->id, 404);

        $boardingHouse->delete();

        return response()->json([
            'ok' => true,
        ]);
    }
}

