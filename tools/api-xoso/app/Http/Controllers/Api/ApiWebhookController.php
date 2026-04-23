<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiWebhook;
use Illuminate\Http\Request;

class ApiWebhookController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            ApiWebhook::where('user_id', $request->user()->id)->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048'],
            'event' => ['required', 'string', 'max:120'],
        ]);

        $webhook = ApiWebhook::create([
            ...$payload,
            'user_id' => $request->user()->id,
        ]);

        return response()->json($webhook, 201);
    }

    public function update(Request $request, ApiWebhook $apiWebhook)
    {
        abort_if($apiWebhook->user_id !== $request->user()->id, 403, 'Khong co quyen cap nhat webhook nay.');
        $payload = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'url' => ['sometimes', 'url', 'max:2048'],
            'event' => ['sometimes', 'string', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $apiWebhook->update($payload);

        return response()->json($apiWebhook->fresh());
    }

    public function destroy(Request $request, ApiWebhook $apiWebhook)
    {
        abort_if($apiWebhook->user_id !== $request->user()->id, 403, 'Khong co quyen xoa webhook nay.');
        $apiWebhook->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
