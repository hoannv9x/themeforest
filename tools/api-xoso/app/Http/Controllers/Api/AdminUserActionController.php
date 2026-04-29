<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserActionController extends Controller
{
    public function updateRole(Request $request, User $user)
    {
        $payload = $request->validate([
            'role' => ['required', 'in:user,vip'],
            'days' => ['required', 'integer', 'min:1'],
        ]);

        $user->role = $payload['role'];
        if ($payload['role'] !== User::ROLE_VIP) {
            $user->vip_expired_at = null;
        } elseif (!$user->vip_expired_at) {
            $user->vip_expired_at = now()->addDays($payload['days']);
        }
        $user->save();

        return response()->json(['message' => 'Role updated', 'user' => $user]);
    }

    public function updatePermission(Request $request, User $user)
    {
        $payload = $request->validate([
            'permission' => ['required', 'in:user,developer'],
            'days' => ['required', 'integer', 'min:1'],
        ]);

        $user->permission = $payload['permission'];
        $user->api_expired_at = now()->addDays($payload['days']);
        $user->save();

        return response()->json(['message' => 'Permission updated', 'user' => $user]);
    }
}
