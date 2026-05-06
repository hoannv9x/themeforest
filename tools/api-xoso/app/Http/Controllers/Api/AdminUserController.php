<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->orderByDesc('id');

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('permission')) {
            $query->where('permission', $request->input('permission'));
        }

        return response()->json(
            $query->paginate(20)
        );
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $payload = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['sometimes', Rule::in([User::ROLE_USER, User::ROLE_VIP, User::ROLE_ADMIN])],
            'permission' => ['sometimes', Rule::in([User::PERMISSION_USER, User::PERMISSION_DEVELOPER])],
            'vip_days' => ['sometimes', 'integer', 'min:1'],
            'vip_expired_at' => ['sometimes', 'nullable', 'date'],
            'api_days' => ['sometimes', 'integer', 'min:1'],
            'api_expired_at' => ['sometimes', 'nullable', 'date'],
        ]);

        if (array_key_exists('name', $payload)) {
            $user->name = $payload['name'];
        }
        if (array_key_exists('email', $payload)) {
            $user->email = $payload['email'];
        }
        if (array_key_exists('permission', $payload)) {
            $user->permission = $payload['permission'];
        }

        if (array_key_exists('vip_days', $payload)) {
            $user->role = User::ROLE_VIP;
            $user->vip_expired_at = now()->addDays((int) $payload['vip_days']);
        }
        if (array_key_exists('vip_expired_at', $payload)) {
            if ($payload['vip_expired_at'] === null) {
                $user->vip_expired_at = null;
                if ($user->role === User::ROLE_VIP) {
                    $user->role = User::ROLE_USER;
                }
            } else {
                $user->role = User::ROLE_VIP;
                $user->vip_expired_at = $payload['vip_expired_at'];
            }
        }

        if (array_key_exists('api_days', $payload)) {
            $user->api_expired_at = now()->addDays((int) $payload['api_days']);
        }
        if (array_key_exists('api_expired_at', $payload)) {
            $user->api_expired_at = $payload['api_expired_at'];
        }

        if (array_key_exists('role', $payload)) {
            $user->role = $payload['role'];
            if ($user->role !== User::ROLE_VIP) {
                $user->vip_expired_at = null;
            }
        }

        $user->save();

        return response()->json([
            'message' => 'Updated',
            'user' => $user->fresh(),
        ]);
    }
}

