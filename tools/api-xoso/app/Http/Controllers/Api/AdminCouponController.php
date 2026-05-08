<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::query()
            ->with('user')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('email', 'like', '%' . $search . '%')
                            ->orWhere('name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($request->filled('source')) {
            $query->where('source', $request->input('source'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->boolean('is_active'));
        }

        return response()->json(
            $query->paginate(20)
        );
    }

    public function show(Coupon $coupon)
    {
        return response()->json($coupon->load('user'));
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'code' => ['nullable', 'string', 'max:64', 'unique:coupons,code'],
            'discount_type' => ['sometimes', Rule::in(['percent'])],
            'discount_percent' => ['required', 'integer', 'min:1', 'max:100'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
            'user_id' => ['nullable', 'exists:users,id'],
            'source' => ['sometimes', Rule::in(['manual', 'referral', 'system'])],
            'meta' => ['nullable', 'array'],
        ]);

        $code = $payload['code'] ?? null;
        if (!$code) {
            $code = Coupon::generateUniqueCode(10);
        }

        $coupon = Coupon::create([
            'code' => $code,
            'discount_type' => $payload['discount_type'] ?? 'percent',
            'discount_percent' => (int) $payload['discount_percent'],
            'max_uses' => $payload['max_uses'] ?? null,
            'used_count' => 0,
            'starts_at' => $payload['starts_at'] ?? null,
            'expires_at' => $payload['expires_at'] ?? null,
            'is_active' => array_key_exists('is_active', $payload) ? (bool) $payload['is_active'] : true,
            'user_id' => $payload['user_id'] ?? null,
            'source' => $payload['source'] ?? 'manual',
            'meta' => $payload['meta'] ?? null,
        ]);

        return response()->json([
            'message' => 'Created',
            'coupon' => $coupon->fresh('user'),
        ], 201);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $payload = $request->validate([
            'code' => ['sometimes', 'string', 'max:64', Rule::unique('coupons', 'code')->ignore($coupon->id)],
            'discount_type' => ['sometimes', Rule::in(['percent'])],
            'discount_percent' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'max_uses' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'starts_at' => ['sometimes', 'nullable', 'date'],
            'expires_at' => ['sometimes', 'nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
            'user_id' => ['sometimes', 'nullable', 'exists:users,id'],
            'source' => ['sometimes', Rule::in(['manual', 'referral', 'system'])],
            'meta' => ['sometimes', 'nullable', 'array'],
        ]);

        $coupon->fill($payload);
        $coupon->save();

        return response()->json([
            'message' => 'Updated',
            'coupon' => $coupon->fresh('user'),
        ]);
    }
}
