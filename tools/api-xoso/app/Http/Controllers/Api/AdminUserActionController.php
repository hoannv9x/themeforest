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
        ]);

        $user->role = $payload['role'];
        if ($payload['role'] !== User::ROLE_VIP) {
            $user->vip_expired_at = null;
        } elseif (!$user->vip_expired_at) {
            $user->vip_expired_at = now()->addDays(30);
        }
        $user->save();

        return response()->json(['message' => 'Role updated', 'user' => $user]);
    }

    public function updatePermission(Request $request, User $user)
    {
        $payload = $request->validate([
            'permission' => ['required', 'in:user,developer'],
        ]);

        $user->permission = $payload['permission'];
        $user->save();

        return response()->json(['message' => 'Permission updated', 'user' => $user]);
    }
}
